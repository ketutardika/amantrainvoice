<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Client::withCount(['invoices', 'projects', 'payments'])
            ->withSum('invoices', 'total_amount')
            ->withSum('payments', 'amount')
            ->latest('created_at');

        // Apply filters
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('client_type')) {
            $query->where('client_type', $request->client_type);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('client_code', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $clients = $query->paginate(15)->withQueryString();

        // Get filter options
        $countries = Client::select('country')
            ->whereNotNull('country')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        $clientTypes = Client::select('client_type')
            ->whereNotNull('client_type')
            ->distinct()
            ->orderBy('client_type')
            ->pluck('client_type');

        return view('clients.index', compact('clients', 'countries', 'clientTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $nextClientCode = $this->generateClientCode();
        
        $countries = [
            'ID' => 'Indonesia',
            'US' => 'United States',
            'SG' => 'Singapore',
            'MY' => 'Malaysia',
            'TH' => 'Thailand',
            'AU' => 'Australia',
            'JP' => 'Japan',
        ];

        $clientTypes = [
            'individual' => 'Individual',
            'company' => 'Company',
            'government' => 'Government',
            'non_profit' => 'Non-Profit',
        ];

        return view('clients.create', compact('nextClientCode', 'countries', 'clientTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_code' => 'required|string|unique:clients,client_code|max:50',
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:clients,email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:2',
            'tax_number' => 'nullable|string|max:50',
            'client_type' => ['required', Rule::in(['individual', 'company', 'government', 'non_profit'])],
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'is_active' => 'boolean',
            'custom_fields' => 'nullable|array',
        ]);

        try {
            $client = Client::create($validated);

            return redirect()->route('clients.show', $client)
                ->with('success', 'Client created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to create client: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        $client->load(['invoices.payments', 'projects', 'payments.invoice']);

        // Calculate client statistics
        $stats = [
            'total_invoices' => $client->invoices->count(),
            'total_invoiced' => $client->invoices->sum('total_amount'),
            'total_paid' => $client->payments->sum('amount'),
            'outstanding_balance' => $client->invoices->sum('balance_due'),
            'overdue_invoices' => $client->invoices->where('status', 'overdue')->count(),
            'draft_invoices' => $client->invoices->where('status', 'draft')->count(),
            'paid_invoices' => $client->invoices->where('status', 'paid')->count(),
            'average_payment_time' => $this->calculateAveragePaymentTime($client),
        ];

        // Recent activity
        $recentInvoices = $client->invoices()
            ->with(['payments'])
            ->latest('invoice_date')
            ->limit(10)
            ->get();

        $recentPayments = $client->payments()
            ->with(['invoice'])
            ->latest('payment_date')
            ->limit(10)
            ->get();

        return view('clients.show', compact('client', 'stats', 'recentInvoices', 'recentPayments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        $countries = [
            'ID' => 'Indonesia',
            'US' => 'United States',
            'SG' => 'Singapore',
            'MY' => 'Malaysia',
            'TH' => 'Thailand',
            'AU' => 'Australia',
            'JP' => 'Japan',
        ];

        $clientTypes = [
            'individual' => 'Individual',
            'company' => 'Company',
            'government' => 'Government',
            'non_profit' => 'Non-Profit',
        ];

        return view('clients.edit', compact('client', 'countries', 'clientTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'client_code' => ['required', 'string', 'max:50', Rule::unique('clients')->ignore($client->id)],
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('clients')->ignore($client->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:2',
            'tax_number' => 'nullable|string|max:50',
            'client_type' => ['required', Rule::in(['individual', 'company', 'government', 'non_profit'])],
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'is_active' => 'boolean',
            'custom_fields' => 'nullable|array',
        ]);

        try {
            $client->update($validated);

            return redirect()->route('clients.show', $client)
                ->with('success', 'Client updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to update client: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        // Check if client has any invoices
        if ($client->invoices()->exists()) {
            return back()->with('error', 'Cannot delete client with existing invoices. Archive the client instead.');
        }

        // Check if client has any projects
        if ($client->projects()->exists()) {
            return back()->with('error', 'Cannot delete client with existing projects. Archive the client instead.');
        }

        // Check if client has any payments
        if ($client->payments()->exists()) {
            return back()->with('error', 'Cannot delete client with existing payments. Archive the client instead.');
        }

        DB::beginTransaction();
        try {
            $client->delete();
            DB::commit();

            return redirect()->route('clients.index')
                ->with('success', 'Client deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete client: ' . $e->getMessage());
        }
    }

    /**
     * Archive/Deactivate a client
     */
    public function archive(Client $client)
    {
        $client->update(['is_active' => false]);

        return back()->with('success', 'Client archived successfully.');
    }

    /**
     * Restore/Activate a client
     */
    public function restore(Client $client)
    {
        $client->update(['is_active' => true]);

        return back()->with('success', 'Client restored successfully.');
    }

    /**
     * Show client invoices
     */
    public function invoices(Client $client, Request $request)
    {
        $query = $client->invoices()->with(['payments', 'project']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $invoices = $query->latest('invoice_date')->paginate(15)->withQueryString();

        $statuses = collect([
            'draft', 'sent', 'viewed', 'partial_paid', 'paid', 'overdue', 'cancelled'
        ])->map(fn($status) => ['value' => $status, 'label' => ucfirst(str_replace('_', ' ', $status))]);

        return view('clients.invoices', compact('client', 'invoices', 'statuses'));
    }

    /**
     * Show client payments
     */
    public function payments(Client $client, Request $request)
    {
        $query = $client->payments()->with(['invoice', 'user']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();

        $paymentMethods = Payment::select('payment_method')
            ->whereNotNull('payment_method')
            ->distinct()
            ->pluck('payment_method');

        return view('clients.payments', compact('client', 'payments', 'paymentMethods'));
    }

    /**
     * Generate client statement
     */
    public function statement(Client $client, Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $dateFrom = Carbon::parse($validated['date_from']);
        $dateTo = Carbon::parse($validated['date_to']);

        $invoices = $client->invoices()
            ->with(['payments'])
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->orderBy('invoice_date')
            ->get();

        $payments = $client->payments()
            ->with(['invoice'])
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->orderBy('payment_date')
            ->get();

        return view('clients.statement', compact('client', 'invoices', 'payments', 'dateFrom', 'dateTo'));
    }

    /**
     * Export clients to Excel
     */
    public function export(Request $request)
    {
        $query = Client::withCount(['invoices', 'projects', 'payments'])
            ->withSum('invoices', 'total_amount')
            ->withSum('payments', 'amount');

        // Apply same filters as index
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('client_type')) {
            $query->where('client_type', $request->client_type);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        $clients = $query->get();

        return Excel::download(new ClientsExport($clients), 'clients-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Generate client code
     */
    private function generateClientCode()
    {
        $year = date('Y');
        $prefix = "CL-{$year}";
        
        $lastClient = Client::where('client_code', 'LIKE', "{$prefix}%")
            ->orderBy('client_code', 'desc')
            ->first();

        if ($lastClient) {
            $lastNumber = (int) substr($lastClient->client_code, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate average payment time for client
     */
    private function calculateAveragePaymentTime(Client $client)
    {
        $paidInvoices = $client->invoices()
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->get();

        if ($paidInvoices->isEmpty()) {
            return null;
        }

        $totalDays = $paidInvoices->sum(function ($invoice) {
            return Carbon::parse($invoice->paid_at)->diffInDays(Carbon::parse($invoice->invoice_date));
        });

        return round($totalDays / $paidInvoices->count(), 1);
    }
}
