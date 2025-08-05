<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tax::query()->latest('created_at');

        // Apply filters
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('rate_min')) {
            $query->where('rate', '>=', $request->rate_min);
        }

        if ($request->filled('rate_max')) {
            $query->where('rate', '<=', $request->rate_max);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $taxes = $query->paginate(15)->withQueryString();

        // Get filter options
        $types = Tax::select('type')
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('taxes.index', compact('taxes', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $taxTypes = [
            'vat' => 'VAT (Value Added Tax)',
            'sales_tax' => 'Sales Tax',
            'service_tax' => 'Service Tax',
            'gst' => 'GST (Goods and Services Tax)',
            'excise' => 'Excise Tax',
            'customs' => 'Customs Duty',
            'other' => 'Other',
        ];

        return view('taxes.create', compact('taxTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:taxes,code|max:50',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => ['required', Rule::in(['vat', 'sales_tax', 'service_tax', 'gst', 'excise', 'customs', 'other'])],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $tax = Tax::create($validated);

            return redirect()->route('taxes.show', $tax)
                ->with('success', 'Tax created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to create tax: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tax $tax)
    {
        // Get usage statistics from invoices
        $stats = [
            'total_usage_count' => $this->getTaxUsageCount($tax),
            'total_tax_amount' => $this->getTotalTaxAmount($tax),
            'active_invoices' => $this->getActiveInvoicesWithTax($tax),
            'usage_by_month' => $this->getTaxUsageByMonth($tax),
            'usage_by_client' => $this->getTaxUsageByClient($tax),
            'average_invoice_amount' => $this->getAverageInvoiceAmountWithTax($tax),
        ];

        // Recent invoices using this tax
        $recentInvoices = $this->getRecentInvoicesWithTax($tax);

        return view('taxes.show', compact('tax', 'stats', 'recentInvoices'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tax $tax)
    {
        $taxTypes = [
            'vat' => 'VAT (Value Added Tax)',
            'sales_tax' => 'Sales Tax',
            'service_tax' => 'Service Tax',
            'gst' => 'GST (Goods and Services Tax)',
            'excise' => 'Excise Tax',
            'customs' => 'Customs Duty',
            'other' => 'Other',
        ];

        return view('taxes.edit', compact('tax', 'taxTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('taxes')->ignore($tax->id)],
            'rate' => 'required|numeric|min:0|max:100',
            'type' => ['required', Rule::in(['vat', 'sales_tax', 'service_tax', 'gst', 'excise', 'customs', 'other'])],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $tax->update($validated);

            return redirect()->route('taxes.show', $tax)
                ->with('success', 'Tax updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to update tax: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tax $tax)
    {
        // Check if tax is being used in any invoices
        $usageCount = $this->getTaxUsageCount($tax);
        
        if ($usageCount > 0) {
            return back()->with('error', "Cannot delete tax that is used in {$usageCount} invoice(s). Deactivate the tax instead.");
        }

        DB::beginTransaction();
        try {
            $tax->delete();
            DB::commit();

            return redirect()->route('taxes.index')
                ->with('success', 'Tax deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete tax: ' . $e->getMessage());
        }
    }

    /**
     * Activate a tax
     */
    public function activate(Tax $tax)
    {
        $tax->update(['is_active' => true]);

        return back()->with('success', 'Tax activated successfully.');
    }

    /**
     * Deactivate a tax
     */
    public function deactivate(Tax $tax)
    {
        $tax->update(['is_active' => false]);

        return back()->with('success', 'Tax deactivated successfully.');
    }

    /**
     * Tax usage report
     */
    public function usageReport(Tax $tax, Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $dateFrom = $validated['date_from'] ? Carbon::parse($validated['date_from']) : Carbon::now()->subYear();
        $dateTo = $validated['date_to'] ? Carbon::parse($validated['date_to']) : Carbon::now();

        // Build base query for invoices using this tax
        $baseQuery = Invoice::with(['client', 'project'])
            ->whereBetween('invoice_date', [$dateFrom, $dateTo])
            ->where('tax_amount', '>', 0);

        // Filter invoices that likely use this tax (basic approximation)
        // In a real system, you'd have a pivot table linking invoices to specific taxes
        $invoices = $baseQuery->get()->filter(function ($invoice) use ($tax) {
            // Simple check: if calculated tax matches our tax rate
            if ($invoice->subtotal > 0) {
                $calculatedRate = ($invoice->tax_amount / $invoice->subtotal) * 100;
                return abs($calculatedRate - $tax->rate) < 0.01; // Allow small rounding differences
            }
            return false;
        });

        $report = [
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'summary' => [
                'total_invoices' => $invoices->count(),
                'total_tax_collected' => $invoices->sum('tax_amount'),
                'total_base_amount' => $invoices->sum('subtotal'),
                'average_tax_per_invoice' => $invoices->count() > 0 ? $invoices->avg('tax_amount') : 0,
            ],
            'monthly_breakdown' => $this->getMonthlyTaxBreakdown($invoices),
            'client_breakdown' => $this->getClientTaxBreakdown($invoices),
            'status_breakdown' => $invoices->groupBy('status')->map->count(),
        ];

        return view('taxes.usage-report', compact('tax', 'report', 'invoices', 'dateFrom', 'dateTo'));
    }

    /**
     * Bulk activate/deactivate taxes
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate'])],
            'tax_ids' => 'required|array|min:1',
            'tax_ids.*' => 'exists:taxes,id',
        ]);

        $isActive = $validated['action'] === 'activate';
        $affected = Tax::whereIn('id', $validated['tax_ids'])
            ->update(['is_active' => $isActive]);

        $action = $validated['action'] === 'activate' ? 'activated' : 'deactivated';
        
        return back()->with('success', "{$affected} tax(es) {$action} successfully.");
    }

    /**
     * Export taxes to Excel
     */
    public function export(Request $request)
    {
        $query = Tax::query();

        // Apply same filters as index
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $taxes = $query->get();

        return Excel::download(new TaxesExport($taxes), 'taxes-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Get tax usage count
     */
    private function getTaxUsageCount(Tax $tax)
    {
        // This is a simplified approach. In a real system, you'd have a proper
        // relationship between taxes and invoices
        return Invoice::where('tax_amount', '>', 0)
            ->get()
            ->filter(function ($invoice) use ($tax) {
                if ($invoice->subtotal > 0) {
                    $calculatedRate = ($invoice->tax_amount / $invoice->subtotal) * 100;
                    return abs($calculatedRate - $tax->rate) < 0.01;
                }
                return false;
            })
            ->count();
    }

    /**
     * Get total tax amount collected
     */
    private function getTotalTaxAmount(Tax $tax)
    {
        return Invoice::where('tax_amount', '>', 0)
            ->get()
            ->filter(function ($invoice) use ($tax) {
                if ($invoice->subtotal > 0) {
                    $calculatedRate = ($invoice->tax_amount / $invoice->subtotal) * 100;
                    return abs($calculatedRate - $tax->rate) < 0.01;
                }
                return false;
            })
            ->sum('tax_amount');
    }

    /**
     * Get active invoices with this tax
     */
    private function getActiveInvoicesWithTax(Tax $tax)
    {
        return Invoice::whereIn('status', ['draft', 'sent', 'viewed', 'partial_paid'])
            ->where('tax_amount', '>', 0)
            ->get()
            ->filter(function ($invoice) use ($tax) {
                if ($invoice->subtotal > 0) {
                    $calculatedRate = ($invoice->tax_amount / $invoice->subtotal) * 100;
                    return abs($calculatedRate - $tax->rate) < 0.01;
                }
                return false;
            })
            ->count();
    }

    /**
     * Get tax usage by month
     */
    private function getTaxUsageByMonth(Tax $tax)
    {
        $invoices = Invoice::where('tax_amount', '>', 0)
            ->where('invoice_date', '>=', now()->subYear())
            ->get()
            ->filter(function ($invoice) use ($tax) {
                if ($invoice->subtotal > 0) {
                    $calculatedRate = ($invoice->tax_amount / $invoice->subtotal) * 100;
                    return abs($calculatedRate - $tax->rate) < 0.01;
                }
                return false;
            });

        return $invoices->groupBy(function ($invoice) {
            return Carbon::parse($invoice->invoice_date)->format('Y-m');
        })->map(function ($monthlyInvoices) {
            return [
                'count' => $monthlyInvoices->count(),
                'tax_amount' => $monthlyInvoices->sum('tax_amount'),
            ];
        });
    }

    /**
     * Get tax usage by client
     */
    private function getTaxUsageByClient(Tax $tax)
    {
        $invoices = Invoice::with('client')
            ->where('tax_amount', '>', 0)
            ->get()
            ->filter(function ($invoice) use ($tax) {
                if ($invoice->subtotal > 0) {
                    $calculatedRate = ($invoice->tax_amount / $invoice->subtotal) * 100;
                    return abs($calculatedRate - $tax->rate) < 0.01;
                }
                return false;
            });

        return $invoices->groupBy('client.name')->map(function ($clientInvoices) {
            return [
                'count' => $clientInvoices->count(),
                'tax_amount' => $clientInvoices->sum('tax_amount'),
            ];
        })->take(10); // Top 10 clients
    }

    /**
     * Get average invoice amount with this tax
     */
    private function getAverageInvoiceAmountWithTax(Tax $tax)
    {
        $invoices = Invoice::where('tax_amount', '>', 0)
            ->get()
            ->filter(function ($invoice) use ($tax) {
                if ($invoice->subtotal > 0) {
                    $calculatedRate = ($invoice->tax_amount / $invoice->subtotal) * 100;
                    return abs($calculatedRate - $tax->rate) < 0.01;
                }
                return false;
            });

        return $invoices->count() > 0 ? $invoices->avg('total_amount') : 0;
    }

    /**
     * Get recent invoices with this tax
     */
    private function getRecentInvoicesWithTax(Tax $tax)
    {
        return Invoice::with(['client', 'project'])
            ->where('tax_amount', '>', 0)
            ->latest('invoice_date')
            ->take(50) // Get more to filter
            ->get()
            ->filter(function ($invoice) use ($tax) {
                if ($invoice->subtotal > 0) {
                    $calculatedRate = ($invoice->tax_amount / $invoice->subtotal) * 100;
                    return abs($calculatedRate - $tax->rate) < 0.01;
                }
                return false;
            })
            ->take(10); // Return top 10 after filtering
    }

    /**
     * Get monthly tax breakdown
     */
    private function getMonthlyTaxBreakdown($invoices)
    {
        return $invoices->groupBy(function ($invoice) {
            return Carbon::parse($invoice->invoice_date)->format('Y-m');
        })->map(function ($monthlyInvoices) {
            return [
                'count' => $monthlyInvoices->count(),
                'tax_amount' => $monthlyInvoices->sum('tax_amount'),
                'base_amount' => $monthlyInvoices->sum('subtotal'),
            ];
        })->sortKeys();
    }

    /**
     * Get client tax breakdown
     */
    private function getClientTaxBreakdown($invoices)
    {
        return $invoices->groupBy('client.name')->map(function ($clientInvoices) {
            return [
                'count' => $clientInvoices->count(),
                'tax_amount' => $clientInvoices->sum('tax_amount'),
                'base_amount' => $clientInvoices->sum('subtotal'),
            ];
        })->sortByDesc('tax_amount');
    }
}
