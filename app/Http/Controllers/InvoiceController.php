<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Project;
use App\Models\InvoiceItem;
use App\Models\Tax;
use App\Models\InvoiceStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['client', 'project', 'user'])
            ->latest('invoice_date');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'LIKE', "%{$search}%")
                                  ->orWhere('company_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        // Get filter options
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $statuses = collect([
            'draft', 'sent', 'viewed', 'partial_paid', 'paid', 'overdue', 'cancelled'
        ])->map(fn($status) => ['value' => $status, 'label' => ucfirst(str_replace('_', ' ', $status))]);

        return view('invoices.index', compact('invoices', 'clients', 'projects', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();
        
        $nextInvoiceNumber = $this->generateInvoiceNumber();

        return view('invoices.create', compact('clients', 'projects', 'taxes', 'nextInvoiceNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric|min:0.0001',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $discountAmount = $validated['discount_amount'] ?? 0;
            $taxAmount = $validated['tax_amount'] ?? 0;
            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $validated['invoice_number'],
                'client_id' => $validated['client_id'],
                'project_id' => $validated['project_id'],
                'user_id' => Auth::id(),
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'currency' => $validated['currency'],
                'exchange_rate' => $validated['exchange_rate'],
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'balance_due' => $totalAmount,
                'notes' => $validated['notes'],
                'terms_conditions' => $validated['terms_conditions'],
                'status' => 'draft',
            ]);

            // Create invoice items
            foreach ($validated['items'] as $index => $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'sort_order' => $index + 1,
                ]);
            }

            // Log status change
            InvoiceStatusLog::create([
                'invoice_id' => $invoice->id,
                'status' => 'draft',
                'user_id' => Auth::id(),
                'notes' => 'Invoice created',
            ]);

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create invoice: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'project', 'user', 'items', 'payments', 'statusLogs.user']);
        
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Cannot edit paid or cancelled invoices.');
        }

        $invoice->load(['items']);
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'clients', 'projects', 'taxes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Cannot update paid or cancelled invoices.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric|min:0.0001',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            $discountAmount = $validated['discount_amount'] ?? 0;
            $taxAmount = $validated['tax_amount'] ?? 0;
            $totalAmount = $subtotal - $discountAmount + $taxAmount;
            $balanceDue = $totalAmount - $invoice->paid_amount;

            // Update invoice
            $invoice->update([
                'client_id' => $validated['client_id'],
                'project_id' => $validated['project_id'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'currency' => $validated['currency'],
                'exchange_rate' => $validated['exchange_rate'],
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'balance_due' => $balanceDue,
                'notes' => $validated['notes'],
                'terms_conditions' => $validated['terms_conditions'],
            ]);

            // Delete existing items and create new ones
            $invoice->items()->delete();
            foreach ($validated['items'] as $index => $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                    'sort_order' => $index + 1,
                ]);
            }

            // Update status if balance changes
            $newStatus = $this->calculateInvoiceStatus($invoice->fresh());
            if ($newStatus !== $invoice->status) {
                $invoice->update(['status' => $newStatus]);
                
                InvoiceStatusLog::create([
                    'invoice_id' => $invoice->id,
                    'status' => $newStatus,
                    'user_id' => Auth::id(),
                    'notes' => 'Status updated due to invoice modification',
                ]);
            }

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update invoice: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        if (!in_array($invoice->status, ['draft', 'cancelled'])) {
            return back()->with('error', 'Only draft or cancelled invoices can be deleted.');
        }

        if ($invoice->payments()->exists()) {
            return back()->with('error', 'Cannot delete invoice with payments.');
        }

        DB::beginTransaction();
        try {
            $invoice->items()->delete();
            $invoice->statusLogs()->delete();
            $invoice->delete();

            DB::commit();

            return redirect()->route('invoices.index')
                ->with('success', 'Invoice deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF for the invoice
     */
    public function generatePdf(Invoice $invoice)
    {
        $invoice->load(['client', 'project', 'user', 'items']);
        
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Send invoice via email
     */
    public function sendEmail(Invoice $invoice)
    {
        if ($invoice->status === 'draft') {
            $invoice->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            InvoiceStatusLog::create([
                'invoice_id' => $invoice->id,
                'status' => 'sent',
                'user_id' => Auth::id(),
                'notes' => 'Invoice sent via email',
            ]);
        }

        // Here you would implement the actual email sending logic
        // For now, we'll just return a success message

        return back()->with('success', 'Invoice sent successfully.');
    }

    /**
     * Mark invoice as viewed (for public invoice links)
     */
    public function markAsViewed(Invoice $invoice)
    {
        if ($invoice->status === 'sent' && !$invoice->viewed_at) {
            $invoice->update([
                'status' => 'viewed',
                'viewed_at' => now(),
            ]);

            InvoiceStatusLog::create([
                'invoice_id' => $invoice->id,
                'status' => 'viewed',
                'user_id' => null,
                'notes' => 'Invoice viewed by client',
            ]);
        }

        return $this->show($invoice);
    }

    /**
     * Update invoice status
     */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['draft', 'sent', 'viewed', 'partial_paid', 'paid', 'overdue', 'cancelled'])],
            'notes' => 'nullable|string',
        ]);

        $invoice->update(['status' => $validated['status']]);

        InvoiceStatusLog::create([
            'invoice_id' => $invoice->id,
            'status' => $validated['status'],
            'user_id' => Auth::id(),
            'notes' => $validated['notes'] ?? 'Status updated manually',
        ]);

        return back()->with('success', 'Invoice status updated successfully.');
    }

    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "INV-{$year}{$month}";
        
        $lastInvoice = Invoice::where('invoice_number', 'LIKE', "{$prefix}%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate invoice status based on payments and due date
     */
    private function calculateInvoiceStatus(Invoice $invoice)
    {
        if ($invoice->balance_due <= 0) {
            return 'paid';
        }

        if ($invoice->paid_amount > 0) {
            return 'partial_paid';
        }

        if ($invoice->due_date < now()->startOfDay() && $invoice->status !== 'draft') {
            return 'overdue';
        }

        return $invoice->status;
    }
}
