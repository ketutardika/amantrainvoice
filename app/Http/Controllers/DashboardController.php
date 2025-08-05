<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\InvoiceStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard.
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'month'); // month, quarter, year
        $dateRange = $this->getDateRange($period);

        // Get cached dashboard data
        $cacheKey = "dashboard_data_{$period}_" . auth()->id();
        $dashboardData = Cache::remember($cacheKey, 300, function () use ($dateRange) {
            return $this->getDashboardData($dateRange['start'], $dateRange['end']);
        });

        return view('dashboard.index', compact('dashboardData', 'period', 'dateRange'));
    }

    /**
     * Get dashboard analytics data
     */
    public function analytics(Request $request)
    {
        $period = $request->get('period', 'month');
        $dateRange = $this->getDateRange($period);
        
        $data = [
            'financial_overview' => $this->getFinancialOverview($dateRange['start'], $dateRange['end']),
            'invoice_trends' => $this->getInvoiceTrends($dateRange['start'], $dateRange['end']),
            'payment_analytics' => $this->getPaymentAnalytics($dateRange['start'], $dateRange['end']),
            'client_performance' => $this->getClientPerformance($dateRange['start'], $dateRange['end']),
            'project_insights' => $this->getProjectInsights($dateRange['start'], $dateRange['end']),
        ];

        return response()->json($data);
    }

    /**
     * Get recent activity
     */
    public function recentActivity(Request $request)
    {
        $limit = $request->get('limit', 10);

        $activities = collect();

        // Recent invoices
        $recentInvoices = Invoice::with(['client'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($invoice) {
                return [
                    'type' => 'invoice',
                    'action' => 'created',
                    'data' => $invoice,
                    'timestamp' => $invoice->created_at,
                    'description' => "Invoice {$invoice->invoice_number} created for {$invoice->client->name}",
                ];
            });

        // Recent payments
        $recentPayments = Payment::with(['client', 'invoice'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'payment',
                    'action' => 'recorded',
                    'data' => $payment,
                    'timestamp' => $payment->created_at,
                    'description' => "Payment {$payment->payment_number} recorded from {$payment->client->name}",
                ];
            });

        // Recent status changes
        $recentStatusChanges = InvoiceStatusLog::with(['invoice', 'user'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'type' => 'status_change',
                    'action' => 'updated',
                    'data' => $log,
                    'timestamp' => $log->created_at,
                    'description' => "Invoice {$log->invoice->invoice_number} status changed to {$log->status}",
                ];
            });

        $activities = $activities
            ->merge($recentInvoices)
            ->merge($recentPayments)
            ->merge($recentStatusChanges)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values();

        return response()->json($activities);
    }

    /**
     * Get dashboard widgets data
     */
    public function widgets(Request $request)
    {
        $widgets = $request->get('widgets', []);
        $data = [];

        foreach ($widgets as $widget) {
            switch ($widget) {
                case 'revenue_chart':
                    $data[$widget] = $this->getRevenueChartData();
                    break;
                case 'invoice_status_chart':
                    $data[$widget] = $this->getInvoiceStatusChartData();
                    break;
                case 'payment_methods_chart':
                    $data[$widget] = $this->getPaymentMethodsChartData();
                    break;
                case 'top_clients':
                    $data[$widget] = $this->getTopClientsData();
                    break;
                case 'overdue_invoices':
                    $data[$widget] = $this->getOverdueInvoicesData();
                    break;
                case 'upcoming_payments':
                    $data[$widget] = $this->getUpcomingPaymentsData();
                    break;
            }
        }

        return response()->json($data);
    }

    /**
     * Export dashboard data
     */
    public function export(Request $request)
    {
        $period = $request->get('period', 'month');
        $format = $request->get('format', 'pdf'); // pdf, excel
        $dateRange = $this->getDateRange($period);
        
        $data = $this->getDashboardData($dateRange['start'], $dateRange['end']);

        if ($format === 'excel') {
            return Excel::download(new DashboardExport($data), 'dashboard-report-' . now()->format('Y-m-d') . '.xlsx');
        }

        $pdf = Pdf::loadView('dashboard.export', compact('data', 'dateRange'));
        return $pdf->download('dashboard-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Get comprehensive dashboard data
     */
    private function getDashboardData($startDate, $endDate)
    {
        return [
            'overview' => $this->getOverviewStats($startDate, $endDate),
            'financial' => $this->getFinancialStats($startDate, $endDate),
            'invoices' => $this->getInvoiceStats($startDate, $endDate),
            'payments' => $this->getPaymentStats($startDate, $endDate),
            'clients' => $this->getClientStats($startDate, $endDate),
            'projects' => $this->getProjectStats($startDate, $endDate),
            'charts' => $this->getChartData($startDate, $endDate),
            'recent_activity' => $this->getRecentActivityData(10),
            'alerts' => $this->getSystemAlerts(),
        ];
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats($startDate, $endDate)
    {
        $totalRevenue = Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total_amount');

        $paidRevenue = Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('paid_amount');

        $outstandingRevenue = Invoice::whereIn('status', ['sent', 'viewed', 'partial_paid', 'overdue'])
            ->sum('balance_due');

        $totalInvoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->count();
        $paidInvoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'paid')->count();

        return [
            'total_revenue' => $totalRevenue,
            'paid_revenue' => $paidRevenue,
            'outstanding_revenue' => $outstandingRevenue,
            'collection_rate' => $totalRevenue > 0 ? ($paidRevenue / $totalRevenue) * 100 : 0,
            'total_invoices' => $totalInvoices,
            'paid_invoices' => $paidInvoices,
            'payment_rate' => $totalInvoices > 0 ? ($paidInvoices / $totalInvoices) * 100 : 0,
        ];
    }

    /**
     * Get financial statistics
     */
    private function getFinancialStats($startDate, $endDate)
    {
        return [
            'monthly_revenue' => $this->getMonthlyRevenue($startDate, $endDate),
            'revenue_by_client' => $this->getRevenueByClient($startDate, $endDate),
            'average_invoice_value' => Invoice::whereBetween('invoice_date', [$startDate, $endDate])
                ->avg('total_amount') ?? 0,
            'largest_invoice' => Invoice::whereBetween('invoice_date', [$startDate, $endDate])
                ->max('total_amount') ?? 0,
            'average_payment_time' => $this->getAveragePaymentTime($startDate, $endDate),
        ];
    }

    /**
     * Get invoice statistics
     */
    private function getInvoiceStats($startDate, $endDate)
    {
        $invoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate]);

        return [
            'by_status' => $invoices->clone()->groupBy('status')->selectRaw('status, count(*) as count')->pluck('count', 'status'),
            'overdue_count' => Invoice::where('status', 'overdue')->count(),
            'draft_count' => Invoice::where('status', 'draft')->count(),
            'sent_count' => Invoice::where('status', 'sent')->count(),
            'conversion_rate' => $this->getInvoiceConversionRate($startDate, $endDate),
        ];
    }

    /**
     * Get payment statistics
     */
    private function getPaymentStats($startDate, $endDate)
    {
        $payments = Payment::whereBetween('payment_date', [$startDate, $endDate]);

        return [
            'total_payments' => $payments->clone()->count(),
            'total_amount' => $payments->clone()->sum('amount'),
            'by_method' => $payments->clone()->groupBy('payment_method')
                ->selectRaw('payment_method, sum(amount) as total')
                ->pluck('total', 'payment_method'),
            'by_status' => $payments->clone()->groupBy('status')
                ->selectRaw('status, count(*) as count')
                ->pluck('count', 'status'),
            'average_payment_amount' => $payments->clone()->avg('amount') ?? 0,
        ];
    }

    /**
     * Get client statistics
     */
    private function getClientStats($startDate, $endDate)
    {
        return [
            'total_active' => Client::where('is_active', true)->count(),
            'new_clients' => Client::whereBetween('created_at', [$startDate, $endDate])->count(),
            'top_clients' => $this->getTopClients($startDate, $endDate),
            'client_retention_rate' => $this->getClientRetentionRate($startDate, $endDate),
        ];
    }

    /**
     * Get project statistics
     */
    private function getProjectStats($startDate, $endDate)
    {
        return [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'completed_projects' => Project::whereBetween('updated_at', [$startDate, $endDate])
                ->where('status', 'completed')->count(),
            'project_revenue' => $this->getProjectRevenue($startDate, $endDate),
        ];
    }

    /**
     * Get chart data for dashboard
     */
    private function getChartData($startDate, $endDate)
    {
        return [
            'revenue_trend' => $this->getRevenueTrendData($startDate, $endDate),
            'invoice_status_pie' => $this->getInvoiceStatusPieData(),
            'payment_methods_bar' => $this->getPaymentMethodsBarData($startDate, $endDate),
            'monthly_comparison' => $this->getMonthlyComparisonData(),
        ];
    }

    /**
     * Get recent activity data
     */
    private function getRecentActivityData($limit)
    {
        $activities = collect();

        // Recent invoices
        $recentInvoices = Invoice::with(['client'])
            ->latest()
            ->limit($limit / 3)
            ->get();

        // Recent payments
        $recentPayments = Payment::with(['client', 'invoice'])
            ->latest()
            ->limit($limit / 3)
            ->get();

        // Recent status changes
        $recentStatusChanges = InvoiceStatusLog::with(['invoice', 'user'])
            ->latest()
            ->limit($limit / 3)
            ->get();

        return [
            'invoices' => $recentInvoices,
            'payments' => $recentPayments,
            'status_changes' => $recentStatusChanges,
        ];
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts()
    {
        $alerts = [];

        // Overdue invoices alert
        $overdueCount = Invoice::where('status', 'overdue')->count();
        if ($overdueCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Overdue Invoices',
                'message' => "You have {$overdueCount} overdue invoice(s) requiring attention.",
                'action_url' => route('invoices.index', ['status' => 'overdue']),
                'action_text' => 'View Overdue Invoices',
            ];
        }

        // Pending payments alert
        $pendingPayments = Payment::where('status', 'pending')->count();
        if ($pendingPayments > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Pending Payments',
                'message' => "You have {$pendingPayments} payment(s) pending verification.",
                'action_url' => route('payments.index', ['status' => 'pending']),
                'action_text' => 'Review Payments',
            ];
        }

        // Low collection rate alert
        $collectionRate = $this->getCollectionRate();
        if ($collectionRate < 80) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Low Collection Rate',
                'message' => "Your collection rate is {$collectionRate}%. Consider following up on outstanding invoices.",
                'action_url' => route('invoices.index', ['status' => 'sent']),
                'action_text' => 'View Outstanding Invoices',
            ];
        }

        return $alerts;
    }

    /**
     * Helper methods for specific calculations
     */
    private function getDateRange($period)
    {
        switch ($period) {
            case 'week':
                return [
                    'start' => now()->startOfWeek(),
                    'end' => now()->endOfWeek(),
                ];
            case 'month':
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth(),
                ];
            case 'quarter':
                return [
                    'start' => now()->startOfQuarter(),
                    'end' => now()->endOfQuarter(),
                ];
            case 'year':
                return [
                    'start' => now()->startOfYear(),
                    'end' => now()->endOfYear(),
                ];
            default:
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth(),
                ];
        }
    }

    private function getMonthlyRevenue($startDate, $endDate)
    {
        return Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(invoice_date, "%Y-%m") as month, SUM(total_amount) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('revenue', 'month');
    }

    private function getRevenueByClient($startDate, $endDate)
    {
        return Invoice::with('client')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->selectRaw('client_id, SUM(total_amount) as revenue')
            ->groupBy('client_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->client->name => $item->revenue];
            });
    }

    private function getAveragePaymentTime($startDate, $endDate)
    {
        $paidInvoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->get();

        if ($paidInvoices->isEmpty()) {
            return 0;
        }

        $totalDays = $paidInvoices->sum(function ($invoice) {
            return Carbon::parse($invoice->paid_at)->diffInDays(Carbon::parse($invoice->invoice_date));
        });

        return round($totalDays / $paidInvoices->count(), 1);
    }

    private function getInvoiceConversionRate($startDate, $endDate)
    {
        $totalInvoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->count();
        $paidInvoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'paid')->count();

        return $totalInvoices > 0 ? round(($paidInvoices / $totalInvoices) * 100, 2) : 0;
    }

    private function getTopClients($startDate, $endDate)
    {
        return Client::withSum(['invoices' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('invoice_date', [$startDate, $endDate]);
            }], 'total_amount')
            ->orderByDesc('invoices_sum_total_amount')
            ->limit(5)
            ->get();
    }

    private function getClientRetentionRate($startDate, $endDate)
    {
        // Simplified retention rate calculation
        $totalClients = Client::where('is_active', true)->count();
        $activeClients = Client::whereHas('invoices', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('invoice_date', [$startDate, $endDate]);
        })->count();

        return $totalClients > 0 ? round(($activeClients / $totalClients) * 100, 2) : 0;
    }

    private function getProjectRevenue($startDate, $endDate)
    {
        return Project::withSum(['invoices' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('invoice_date', [$startDate, $endDate]);
            }], 'total_amount')
            ->get()
            ->sum('invoices_sum_total_amount');
    }

    private function getRevenueTrendData($startDate, $endDate)
    {
        return Invoice::whereBetween('invoice_date', [$startDate, $endDate])
            ->selectRaw('DATE(invoice_date) as date, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('revenue', 'date');
    }

    private function getInvoiceStatusPieData()
    {
        return Invoice::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
    }

    private function getPaymentMethodsBarData($startDate, $endDate)
    {
        return Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->pluck('total', 'payment_method');
    }

    private function getMonthlyComparisonData()
    {
        $currentMonth = now()->startOfMonth();
        $previousMonth = now()->subMonth()->startOfMonth();

        $current = Invoice::whereBetween('invoice_date', [$currentMonth, now()])
            ->sum('total_amount');

        $previous = Invoice::whereBetween('invoice_date', [$previousMonth, $currentMonth])
            ->sum('total_amount');

        return [
            'current' => $current,
            'previous' => $previous,
            'change' => $previous > 0 ? round((($current - $previous) / $previous) * 100, 2) : 0,
        ];
    }

    private function getCollectionRate()
    {
        $totalInvoiced = Invoice::sum('total_amount');
        $totalPaid = Invoice::sum('paid_amount');

        return $totalInvoiced > 0 ? round(($totalPaid / $totalInvoiced) * 100, 2) : 0;
    }

    // Additional widget-specific methods
    private function getRevenueChartData()
    {
        return $this->getRevenueTrendData(now()->subDays(30), now());
    }

    private function getInvoiceStatusChartData()
    {
        return $this->getInvoiceStatusPieData();
    }

    private function getPaymentMethodsChartData()
    {
        return $this->getPaymentMethodsBarData(now()->subDays(30), now());
    }

    private function getTopClientsData()
    {
        return $this->getTopClients(now()->subDays(30), now());
    }

    private function getOverdueInvoicesData()
    {
        return Invoice::with(['client'])
            ->where('status', 'overdue')
            ->orderBy('due_date')
            ->limit(10)
            ->get();
    }

    private function getUpcomingPaymentsData()
    {
        return Invoice::with(['client'])
            ->whereIn('status', ['sent', 'viewed', 'partial_paid'])
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(7))
            ->orderBy('due_date')
            ->limit(10)
            ->get();
    }

    // Financial overview methods
    private function getFinancialOverview($startDate, $endDate)
    {
        return [
            'revenue' => $this->getOverviewStats($startDate, $endDate),
            'trends' => $this->getRevenueTrendData($startDate, $endDate),
            'projections' => $this->getRevenueProjections(),
        ];
    }

    private function getInvoiceTrends($startDate, $endDate)
    {
        return [
            'created' => $this->getInvoiceCreationTrend($startDate, $endDate),
            'paid' => $this->getInvoicePaymentTrend($startDate, $endDate),
            'overdue' => $this->getOverdueTrend($startDate, $endDate),
        ];
    }

    private function getPaymentAnalytics($startDate, $endDate)
    {
        return [
            'volume' => $this->getPaymentVolumeData($startDate, $endDate),
            'methods' => $this->getPaymentMethodsBarData($startDate, $endDate),
            'timing' => $this->getPaymentTimingAnalysis($startDate, $endDate),
        ];
    }

    private function getClientPerformance($startDate, $endDate)
    {
        return [
            'top_clients' => $this->getTopClients($startDate, $endDate),
            'new_clients' => $this->getNewClientsData($startDate, $endDate),
            'client_retention' => $this->getClientRetentionData($startDate, $endDate),
        ];
    }

    private function getProjectInsights($startDate, $endDate)
    {
        return [
            'active_projects' => Project::where('status', 'active')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'project_revenue' => $this->getProjectRevenue($startDate, $endDate),
            'budget_utilization' => $this->getProjectBudgetUtilization(),
        ];
    }

    // Additional helper methods for analytics
    private function getRevenueProjections()
    {
        // Simple projection based on current month's trend
        $currentMonthRevenue = Invoice::whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->sum('total_amount');

        $daysInMonth = now()->daysInMonth;
        $daysPassed = now()->day;

        $projectedRevenue = $daysPassed > 0 ? ($currentMonthRevenue / $daysPassed) * $daysInMonth : 0;

        return [
            'current_month' => $currentMonthRevenue,
            'projected_month' => $projectedRevenue,
            'difference' => $projectedRevenue - $currentMonthRevenue,
        ];
    }

    private function getInvoiceCreationTrend($startDate, $endDate)
    {
        return Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');
    }

    private function getInvoicePaymentTrend($startDate, $endDate)
    {
        return Invoice::whereBetween('paid_at', [$startDate, $endDate])
            ->whereNotNull('paid_at')
            ->selectRaw('DATE(paid_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');
    }

    private function getOverdueTrend($startDate, $endDate)
    {
        return Invoice::where('status', 'overdue')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->selectRaw('DATE(due_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');
    }

    private function getPaymentVolumeData($startDate, $endDate)
    {
        return Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->selectRaw('DATE(payment_date) as date, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getPaymentTimingAnalysis($startDate, $endDate)
    {
        $payments = Payment::with('invoice')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();

        $timingData = $payments->map(function ($payment) {
            $invoiceDate = Carbon::parse($payment->invoice->invoice_date);
            $paymentDate = Carbon::parse($payment->payment_date);
            return $paymentDate->diffInDays($invoiceDate);
        });

        return [
            'average_days' => $timingData->avg(),
            'median_days' => $timingData->median(),
            'distribution' => $timingData->groupBy(function ($days) {
                if ($days <= 7) return '0-7 days';
                if ($days <= 14) return '8-14 days';
                if ($days <= 30) return '15-30 days';
                return '30+ days';
            })->map->count(),
        ];
    }

    private function getNewClientsData($startDate, $endDate)
    {
        return Client::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');
    }

    private function getClientRetentionData($startDate, $endDate)
    {
        // Clients who had invoices in both current and previous periods
        $previousPeriodStart = Carbon::parse($startDate)->subDays($startDate->diffInDays($endDate));
        
        $currentPeriodClients = Client::whereHas('invoices', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('invoice_date', [$startDate, $endDate]);
        })->pluck('id');

        $previousPeriodClients = Client::whereHas('invoices', function ($query) use ($previousPeriodStart, $startDate) {
            $query->whereBetween('invoice_date', [$previousPeriodStart, $startDate]);
        })->pluck('id');

        $retainedClients = $currentPeriodClients->intersect($previousPeriodClients)->count();
        $previousTotal = $previousPeriodClients->count();

        return [
            'retained_clients' => $retainedClients,
            'previous_period_clients' => $previousTotal,
            'retention_rate' => $previousTotal > 0 ? round(($retainedClients / $previousTotal) * 100, 2) : 0,
        ];
    }

    private function getProjectBudgetUtilization()
    {
        return Project::whereNotNull('budget')
            ->where('budget', '>', 0)
            ->with(['invoices'])
            ->get()
            ->map(function ($project) {
                $totalInvoiced = $project->invoices->sum('total_amount');
                $utilization = $project->budget > 0 ? ($totalInvoiced / $project->budget) * 100 : 0;
                
                return [
                    'project' => $project->name,
                    'budget' => $project->budget,
                    'invoiced' => $totalInvoiced,
                    'utilization' => round($utilization, 2),
                ];
            })
            ->sortByDesc('utilization')
            ->values();
    }
}
