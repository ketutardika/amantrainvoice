<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\InvoiceStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'client', 'user', 'verifiedBy'])
            ->latest('payment_date');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'LIKE', "%{$search}%")
                  ->orWhere('reference_number', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'LIKE', "%{$search}%")
                                  ->orWhere('company_name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('invoice', function ($invoiceQuery) use ($search) {
                      $invoiceQuery->where('invoice_number', 'LIKE', "%{$search}%");
                  });
            });
        }

        $payments = $query->paginate(15)->withQueryString();

        // Get filter options
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $statuses = collect(['pending', 'verified', 'failed', 'cancelled'])
            ->map(fn($status) => ['value' => $status, 'label' => ucfirst($status)]);
        $paymentMethods = Payment::select('payment_method')
            ->whereNotNull('payment_method')
            ->distinct()
            ->orderBy('payment_method')
            ->pluck('payment_method');

        // Calculate summary stats
        $stats = [
            'total_payments' => $payments->total(),
            'total_amount' => Payment::sum('amount'),
            'verified_amount' => Payment::where('status', 'verified')->sum('amount'),
            'pending_amount' => Payment::where('status', 'pending')->sum('amount'),
        ];

        return view('payments.index', compact('payments', 'clients', 'statuses', 'paymentMethods', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $invoice = null;
        $client = null;

        // Pre-populate if invoice_id is provided
        if ($request->filled('invoice_id')) {
            $invoice = Invoice::with('client')->find($request->invoice_id);
            $client = $invoice?->client;
        }

        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $invoices = $client ? 
            $client->invoices()->whereIn('status', ['sent', 'viewed', 'partial_paid', 'overdue'])->get() :
            Invoice::whereIn('status', ['sent', 'viewed', 'partial_paid', 'overdue'])->with('client')->get();

        $paymentMethods = [
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'cash' => 'Cash',
            'check' => 'Check',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'other' => 'Other',
        ];

        $nextPaymentNumber = $this->generatePaymentNumber();

        return view('payments.create', compact('clients', 'invoices', 'paymentMethods', 'nextPaymentNumber', 'invoice', 'client'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_number' => 'required|string|unique:payments,payment_number|max:255',
            'invoice_id' => 'required|exists:invoices,id',
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => ['required', Rule::in(['bank_transfer', 'credit_card', 'cash', 'check', 'paypal', 'stripe', 'other'])],
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        // Validate payment amount against invoice balance
        $invoice = Invoice::find($validated['invoice_id']);
        if ($validated['amount'] > $invoice->balance_due) {
            return back()->withInput()
                ->withErrors(['amount' => 'Payment amount cannot exceed invoice balance due.']);
        }

        DB::beginTransaction();
        try {
            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('payments', 'public');
                $validated['attachment'] = $attachmentPath;
            }

            // Create payment
            $payment = Payment::create([
                'payment_number' => $validated['payment_number'],
                'invoice_id' => $validated['invoice_id'],
                'client_id' => $validated['client_id'],
                'user_id' => Auth::id(),
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'notes' => $validated['notes'],
                'attachment' => $attachmentPath,
                'status' => 'pending',
            ]);

            // Update invoice amounts
            $this->updateInvoiceAmounts($invoice, $validated['amount']);

            DB::commit();

            return redirect()->route('payments.show', $payment)
                ->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if payment creation failed
            if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                Storage::disk('public')->delete($attachmentPath);
            }

            return back()->withInput()
                ->withErrors(['error' => 'Failed to record payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $payment->load(['invoice.items', 'client', 'user', 'verifiedBy']);

        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        if ($payment->status === 'verified') {
            return redirect()->route('payments.show', $payment)
                ->with('error', 'Cannot edit verified payments.');
        }

        $payment->load(['invoice', 'client']);

        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $invoices = Invoice::whereIn('status', ['sent', 'viewed', 'partial_paid', 'overdue'])
            ->with('client')
            ->get();

        $paymentMethods = [
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'cash' => 'Cash',
            'check' => 'Check',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'other' => 'Other',
        ];

        return view('payments.edit', compact('payment', 'clients', 'invoices', 'paymentMethods'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        if ($payment->status === 'verified') {
            return redirect()->route('payments.show', $payment)
                ->with('error', 'Cannot update verified payments.');
        }

        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => ['required', Rule::in(['bank_transfer', 'credit_card', 'cash', 'check', 'paypal', 'stripe', 'other'])],
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        // Validate payment amount against invoice balance (considering current payment)
        $invoice = Invoice::find($validated['invoice_id']);
        $availableBalance = $invoice->balance_due + $payment->amount; // Add back current payment amount
        if ($validated['amount'] > $availableBalance) {
            return back()->withInput()
                ->withErrors(['amount' => 'Payment amount cannot exceed available invoice balance.']);
        }

        DB::beginTransaction();
        try {
            $oldAmount = $payment->amount;
            $oldInvoiceId = $payment->invoice_id;

            // Handle file upload
            if ($request->hasFile('attachment')) {
                // Delete old attachment
                if ($payment->attachment && Storage::disk('public')->exists($payment->attachment)) {
                    Storage::disk('public')->delete($payment->attachment);
                }
                
                $attachmentPath = $request->file('attachment')->store('payments', 'public');
                $validated['attachment'] = $attachmentPath;
            }

            // Update payment
            $payment->update($validated);

            // Update invoice amounts
            if ($oldInvoiceId !== $validated['invoice_id']) {
                // Different invoice - revert old and apply to new
                $oldInvoice = Invoice::find($oldInvoiceId);
                $this->updateInvoiceAmounts($oldInvoice, -$oldAmount); // Remove old payment
                $this->updateInvoiceAmounts($invoice, $validated['amount']); // Add new payment
            } else {
                // Same invoice - adjust amount difference
                $amountDifference = $validated['amount'] - $oldAmount;
                $this->updateInvoiceAmounts($invoice, $amountDifference);
            }

            DB::commit();

            return redirect()->route('payments.show', $payment)
                ->with('success', 'Payment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update payment: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        if ($payment->status === 'verified') {
            return back()->with('error', 'Cannot delete verified payments.');
        }

        DB::beginTransaction();
        try {
            // Revert invoice amounts
            $this->updateInvoiceAmounts($payment->invoice, -$payment->amount);

            // Delete attachment file
            if ($payment->attachment && Storage::disk('public')->exists($payment->attachment)) {
                Storage::disk('public')->delete($payment->attachment);
            }

            $payment->delete();

            DB::commit();

            return redirect()->route('payments.index')
                ->with('success', 'Payment deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete payment: ' . $e->getMessage());
        }
    }

    /**
     * Verify a payment
     */
    public function verify(Payment $payment)
    {
        if ($payment->status === 'verified') {
            return back()->with('info', 'Payment is already verified.');
        }

        DB::beginTransaction();
        try {
            $payment->update([
                'status' => 'verified',
                'verified_at' => now(),
                'verified_by' => Auth::id(),
            ]);

            // Update invoice status if fully paid
            $invoice = $payment->invoice;
            if ($invoice->balance_due <= 0) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                // Log status change
                InvoiceStatusLog::create([
                    'invoice_id' => $invoice->id,
                    'status' => 'paid',
                    'user_id' => Auth::id(),
                    'notes' => 'Invoice fully paid - Payment verified: ' . $payment->payment_number,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Payment verified successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to verify payment: ' . $e->getMessage());
        }
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Revert invoice amounts
            $this->updateInvoiceAmounts($payment->invoice, -$payment->amount);

            $payment->update([
                'status' => 'failed',
                'notes' => $payment->notes . "\n\nMarked as failed: " . $validated['notes'],
            ]);

            DB::commit();

            return back()->with('success', 'Payment marked as failed.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark payment as failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate payment receipt PDF
     */
    public function generateReceipt(Payment $payment)
    {
        $payment->load(['invoice.items', 'client', 'user']);

        $pdf = Pdf::loadView('payments.receipt', compact('payment'));
        
        return $pdf->download('payment-receipt-' . $payment->payment_number . '.pdf');
    }

    /**
     * Download payment attachment
     */
    public function downloadAttachment(Payment $payment)
    {
        if (!$payment->attachment || !Storage::disk('public')->exists($payment->attachment)) {
            return back()->with('error', 'Attachment not found.');
        }

        return Storage::disk('public')->download($payment->attachment);
    }

    /**
     * Get invoices for a specific client (AJAX)
     */
    public function getClientInvoices($clientId)
    {
        $invoices = Invoice::where('client_id', $clientId)
            ->whereIn('status', ['sent', 'viewed', 'partial_paid', 'overdue'])
            ->where('balance_due', '>', 0)
            ->select('id', 'invoice_number', 'total_amount', 'balance_due', 'due_date')
            ->get();

        return response()->json($invoices);
    }

    /**
     * Payment reports
     */
    public function reports(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfMonth());

        $payments = Payment::with(['client', 'invoice'])
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->get();

        $report = [
            'period' => [
                'from' => Carbon::parse($dateFrom),
                'to' => Carbon::parse($dateTo),
            ],
            'summary' => [
                'total_payments' => $payments->count(),
                'total_amount' => $payments->sum('amount'),
                'verified_amount' => $payments->where('status', 'verified')->sum('amount'),
                'pending_amount' => $payments->where('status', 'pending')->sum('amount'),
                'failed_amount' => $payments->where('status', 'failed')->sum('amount'),
            ],
            'by_method' => $payments->groupBy('payment_method')->map->sum('amount'),
            'by_status' => $payments->groupBy('status')->map->count(),
            'by_client' => $payments->groupBy('client.name')->map->sum('amount')->sortDesc()->take(10),
            'daily_totals' => $payments->groupBy(function ($payment) {
                return Carbon::parse($payment->payment_date)->format('Y-m-d');
            })->map->sum('amount')->sortKeys(),
        ];

        return view('payments.reports', compact('report', 'payments'));
    }

    /**
     * Export payments to Excel
     */
    public function export(Request $request)
    {
        $query = Payment::with(['invoice', 'client', 'user']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $payments = $query->get();

        return Excel::download(new PaymentsExport($payments), 'payments-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Generate payment number
     */
    private function generatePaymentNumber()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "PAY-{$year}{$month}";
        
        $lastPayment = Payment::where('payment_number', 'LIKE', "{$prefix}%")
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Update invoice amounts after payment changes
     */
    private function updateInvoiceAmounts(Invoice $invoice, $amountChange)
    {
        $newPaidAmount = $invoice->paid_amount + $amountChange;
        $newBalanceDue = $invoice->total_amount - $newPaidAmount;

        // Determine new status
        $newStatus = $this->calculateInvoiceStatus($invoice, $newPaidAmount, $newBalanceDue);

        $invoice->update([
            'paid_amount' => max(0, $newPaidAmount),
            'balance_due' => max(0, $newBalanceDue),
            'status' => $newStatus,
        ]);

        // Log status change if status changed
        if ($newStatus !== $invoice->getOriginal('status')) {
            InvoiceStatusLog::create([
                'invoice_id' => $invoice->id,
                'status' => $newStatus,
                'user_id' => Auth::id(),
                'notes' => 'Status updated due to payment change',
            ]);
        }
    }

    /**
     * Calculate invoice status based on payment amounts
     */
    private function calculateInvoiceStatus(Invoice $invoice, $paidAmount, $balanceDue)
    {
        if ($balanceDue <= 0) {
            return 'paid';
        }

        if ($paidAmount > 0) {
            return 'partial_paid';
        }

        if ($invoice->due_date < now()->startOfDay() && !in_array($invoice->status, ['draft', 'cancelled'])) {
            return 'overdue';
        }

        return $invoice->status;
    }
}
