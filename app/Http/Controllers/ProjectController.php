<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Project::with(['client'])
            ->withCount(['invoices'])
            ->withSum('invoices', 'total_amount')
            ->latest('created_at');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        if ($request->filled('budget_min')) {
            $query->where('budget', '>=', $request->budget_min);
        }

        if ($request->filled('budget_max')) {
            $query->where('budget', '<=', $request->budget_max);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('project_code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'LIKE', "%{$search}%")
                                  ->orWhere('company_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $projects = $query->paginate(15)->withQueryString();

        // Get filter options
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $statuses = collect([
            'planning', 'active', 'on_hold', 'completed', 'cancelled'
        ])->map(fn($status) => ['value' => $status, 'label' => ucfirst(str_replace('_', ' ', $status))]);

        return view('projects.index', compact('projects', 'clients', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();
        $nextProjectCode = $this->generateProjectCode();

        $statuses = [
            'planning' => 'Planning',
            'active' => 'Active',
            'on_hold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        return view('projects.create', compact('clients', 'nextProjectCode', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_code' => 'required|string|unique:projects,project_code|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:clients,id',
            'status' => ['required', Rule::in(['planning', 'active', 'on_hold', 'completed', 'cancelled'])],
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $project = Project::create($validated);

            return redirect()->route('projects.show', $project)
                ->with('success', 'Project created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to create project: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $project->load(['client', 'invoices.payments']);

        // Calculate project statistics
        $stats = [
            'total_invoices' => $project->invoices->count(),
            'total_invoiced' => $project->invoices->sum('total_amount'),
            'total_paid' => $project->invoices->sum('paid_amount'),
            'outstanding_balance' => $project->invoices->sum('balance_due'),
            'budget_utilization' => $project->budget > 0 ? 
                round(($project->invoices->sum('total_amount') / $project->budget) * 100, 2) : 0,
            'days_elapsed' => $project->start_date ? 
                Carbon::parse($project->start_date)->diffInDays(now()) : 0,
            'days_remaining' => $project->end_date ? 
                max(0, Carbon::parse($project->end_date)->diffInDays(now(), false)) : null,
            'completion_percentage' => $this->calculateCompletionPercentage($project),
        ];

        // Invoice status breakdown
        $invoiceStatusBreakdown = $project->invoices->groupBy('status')->map->count();

        // Recent invoices
        $recentInvoices = $project->invoices()
            ->with(['payments'])
            ->latest('invoice_date')
            ->limit(10)
            ->get();

        // Project timeline (invoices by month)
        $timeline = $project->invoices()
            ->select(
                DB::raw('DATE_FORMAT(invoice_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('projects.show', compact(
            'project', 
            'stats', 
            'invoiceStatusBreakdown', 
            'recentInvoices', 
            'timeline'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $clients = Client::where('is_active', true)->orderBy('name')->get();

        $statuses = [
            'planning' => 'Planning',
            'active' => 'Active',
            'on_hold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        return view('projects.edit', compact('project', 'clients', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'project_code' => ['required', 'string', 'max:50', Rule::unique('projects')->ignore($project->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:clients,id',
            'status' => ['required', Rule::in(['planning', 'active', 'on_hold', 'completed', 'cancelled'])],
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $project->update($validated);

            return redirect()->route('projects.show', $project)
                ->with('success', 'Project updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to update project: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        // Check if project has any invoices
        if ($project->invoices()->exists()) {
            return back()->with('error', 'Cannot delete project with existing invoices. Change project status instead.');
        }

        DB::beginTransaction();
        try {
            $project->delete();
            DB::commit();

            return redirect()->route('projects.index')
                ->with('success', 'Project deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete project: ' . $e->getMessage());
        }
    }

    /**
     * Show project invoices
     */
    public function invoices(Project $project, Request $request)
    {
        $query = $project->invoices()->with(['client', 'payments']);

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

        return view('projects.invoices', compact('project', 'invoices', 'statuses'));
    }

    /**
     * Project budget analysis
     */
    public function budgetAnalysis(Project $project)
    {
        $project->load(['invoices.payments']);

        $analysis = [
            'budget' => $project->budget ?? 0,
            'total_invoiced' => $project->invoices->sum('total_amount'),
            'total_paid' => $project->invoices->sum('paid_amount'),
            'outstanding' => $project->invoices->sum('balance_due'),
            'budget_remaining' => ($project->budget ?? 0) - $project->invoices->sum('total_amount'),
            'utilization_percentage' => $project->budget > 0 ? 
                round(($project->invoices->sum('total_amount') / $project->budget) * 100, 2) : 0,
        ];

        // Monthly breakdown
        $monthlyBreakdown = $project->invoices()
            ->select(
                DB::raw('DATE_FORMAT(invoice_date, "%Y-%m") as month'),
                DB::raw('SUM(total_amount) as invoiced'),
                DB::raw('SUM(paid_amount) as paid')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Budget vs actual by month
        $budgetVsActual = [];
        if ($project->start_date && $project->end_date && $project->budget) {
            $startDate = Carbon::parse($project->start_date);
            $endDate = Carbon::parse($project->end_date);
            $monthsTotal = $startDate->diffInMonths($endDate) + 1;
            $monthlyBudget = $project->budget / $monthsTotal;

            foreach ($monthlyBreakdown as $month) {
                $budgetVsActual[] = [
                    'month' => $month->month,
                    'budgeted' => $monthlyBudget,
                    'actual' => $month->invoiced,
                    'variance' => $month->invoiced - $monthlyBudget,
                ];
            }
        }

        return view('projects.budget-analysis', compact('project', 'analysis', 'monthlyBreakdown', 'budgetVsActual'));
    }

    /**
     * Project progress report
     */
    public function progressReport(Project $project)
    {
        $project->load(['client', 'invoices.payments']);

        $report = [
            'project_duration' => $this->calculateProjectDuration($project),
            'completion_percentage' => $this->calculateCompletionPercentage($project),
            'milestones' => $this->getProjectMilestones($project),
            'financial_summary' => [
                'budget' => $project->budget ?? 0,
                'invoiced' => $project->invoices->sum('total_amount'),
                'paid' => $project->invoices->sum('paid_amount'),
                'outstanding' => $project->invoices->sum('balance_due'),
            ],
            'performance_metrics' => [
                'on_time_delivery' => $this->calculateOnTimeDelivery($project),
                'budget_variance' => $this->calculateBudgetVariance($project),
                'invoice_efficiency' => $this->calculateInvoiceEfficiency($project),
            ],
        ];

        return view('projects.progress-report', compact('project', 'report'));
    }

    /**
     * Export projects to Excel
     */
    public function export(Request $request)
    {
        $query = Project::with(['client'])
            ->withCount(['invoices'])
            ->withSum('invoices', 'total_amount');

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $projects = $query->get();

        return Excel::download(new ProjectsExport($projects), 'projects-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Generate project code
     */
    private function generateProjectCode()
    {
        $year = date('Y');
        $prefix = "PRJ-{$year}";
        
        $lastProject = Project::where('project_code', 'LIKE', "{$prefix}%")
            ->orderBy('project_code', 'desc')
            ->first();

        if ($lastProject) {
            $lastNumber = (int) substr($lastProject->project_code, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate project duration
     */
    private function calculateProjectDuration(Project $project)
    {
        if (!$project->start_date) {
            return null;
        }

        $startDate = Carbon::parse($project->start_date);
        $endDate = $project->end_date ? Carbon::parse($project->end_date) : now();

        return [
            'total_days' => $startDate->diffInDays($endDate),
            'elapsed_days' => $startDate->diffInDays(now()),
            'remaining_days' => $project->end_date ? 
                max(0, now()->diffInDays(Carbon::parse($project->end_date))) : null,
        ];
    }

    /**
     * Calculate completion percentage
     */
    private function calculateCompletionPercentage(Project $project)
    {
        if (!$project->start_date || !$project->end_date) {
            return null;
        }

        $startDate = Carbon::parse($project->start_date);
        $endDate = Carbon::parse($project->end_date);
        $totalDays = $startDate->diffInDays($endDate);
        $elapsedDays = $startDate->diffInDays(now());

        if ($totalDays <= 0) {
            return 100;
        }

        return min(100, round(($elapsedDays / $totalDays) * 100, 2));
    }

    /**
     * Get project milestones based on invoices
     */
    private function getProjectMilestones(Project $project)
    {
        return $project->invoices()
            ->select('invoice_date', 'total_amount', 'status')
            ->orderBy('invoice_date')
            ->get()
            ->map(function ($invoice) {
                return [
                    'date' => $invoice->invoice_date,
                    'amount' => $invoice->total_amount,
                    'status' => $invoice->status,
                    'type' => 'invoice',
                ];
            });
    }

    /**
     * Calculate on-time delivery percentage
     */
    private function calculateOnTimeDelivery(Project $project)
    {
        if ($project->status !== 'completed' || !$project->end_date) {
            return null;
        }

        $plannedEndDate = Carbon::parse($project->end_date);
        $actualEndDate = $project->updated_at; // Assuming updated_at reflects completion

        return $actualEndDate <= $plannedEndDate ? 100 : 0;
    }

    /**
     * Calculate budget variance
     */
    private function calculateBudgetVariance(Project $project)
    {
        if (!$project->budget || $project->budget <= 0) {
            return null;
        }

        $actualCost = $project->invoices->sum('total_amount');
        $variance = (($actualCost - $project->budget) / $project->budget) * 100;

        return round($variance, 2);
    }

    /**
     * Calculate invoice efficiency
     */
    private function calculateInvoiceEfficiency(Project $project)
    {
        $totalInvoices = $project->invoices->count();
        $paidInvoices = $project->invoices->where('status', 'paid')->count();

        if ($totalInvoices === 0) {
            return null;
        }

        return round(($paidInvoices / $totalInvoices) * 100, 2);
    }
}
