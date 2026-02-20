<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $companyId = Filament::getTenant()?->id;

        $totalRevenue = Invoice::where('company_id', $companyId)->sum('paid_amount');
        $paidRevenue = Invoice::where('company_id', $companyId)->sum('paid_amount');
        $outstandingRevenue = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'viewed', 'partial_paid', 'overdue'])
            ->sum('balance_due');

        $thisMonthRevenue = Invoice::where('company_id', $companyId)
            ->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->sum('paid_amount');

        $lastMonthRevenue = Invoice::where('company_id', $companyId)
            ->whereMonth('invoice_date', now()->subMonth()->month)
            ->whereYear('invoice_date', now()->subMonth()->year)
            ->sum('paid_amount');

        $revenueChange = $lastMonthRevenue > 0 ?
            round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;

        return [
            Stat::make('Total Revenue', 'IDR ' . number_format($totalRevenue, 0, ',', '.'))
                ->description($revenueChange >= 0 ? "{$revenueChange}% increase" : "{$revenueChange}% decrease")
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger'),

            Stat::make('Outstanding Amount', 'IDR ' . number_format($outstandingRevenue, 0, ',', '.'))
                ->description('Amount pending collection')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Collection Rate', round($totalRevenue > 0 ? ($paidRevenue / $totalRevenue) * 100 : 0, 1) . '%')
                ->description('Payment collection efficiency')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Invoice', Invoice::where('company_id', $companyId)->count())
                ->description('Total number of invoices')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Total Clients', Client::where('company_id', $companyId)->where('is_active', true)->count())
                ->description('Active clients')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Overdue Invoices', Invoice::where('company_id', $companyId)->where('status', 'overdue')->count())
                ->description('Requires immediate attention')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
