<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_clients' => Client::count(),
            'total_invoices' => Invoice::count(),
            'total_revenue' => Invoice::where('status', 'paid')->sum('total_amount'),
            'pending_amount' => Invoice::whereIn('status', ['sent', 'viewed', 'partial_paid'])->sum('balance_due'),
            'overdue_invoices' => Invoice::overdue()->count(),
            'this_month_revenue' => Invoice::paid()->thisMonth()->sum('total_amount'),
        ];

        $recent_invoices = Invoice::with(['client'])
            ->latest()
            ->take(5)
            ->get();

        $pending_payments = Payment::with(['invoice', 'client'])
            ->pending()
            ->latest()
            ->take(5)
            ->get();

        $overdue_invoices = Invoice::with(['client'])
            ->overdue()
            ->latest()
            ->take(5)
            ->get();

        // Monthly revenue chart data
        $monthly_revenue = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = Invoice::paid()
                ->whereMonth('paid_at', $date->month)
                ->whereYear('paid_at', $date->year)
                ->sum('total_amount');
            
            $monthly_revenue->push([
                'month' => $date->format('M Y'),
                'revenue' => $revenue
            ]);
        }

        return view('dashboard', compact(
            'stats', 
            'recent_invoices', 
            'pending_payments', 
            'overdue_invoices',
            'monthly_revenue'
        ));
    }
}