@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Overview of your invoice system')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="row mb-4">
    <!-- Stats Cards -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-muted mb-1">Total Clients</h6>
                    <div class="stats-number">{{ $stats['total_clients'] }}</div>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-people fs-1 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-muted mb-1">Total Invoices</h6>
                    <div class="stats-number">{{ $stats['total_invoices'] }}</div>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-receipt fs-1 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-muted mb-1">Total Revenue</h6>
                    <div class="stats-number">{{ number_format($stats['total_revenue'] / 1000000, 1) }}M</div>
                    <small class="text-muted">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</small>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-currency-dollar fs-1 text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-muted mb-1">Pending Amount</h6>
                    <div class="stats-number">{{ number_format($stats['pending_amount'] / 1000000, 1) }}M</div>
                    <small class="text-muted">Rp {{ number_format($stats['pending_amount'], 0, ',', '.') }}</small>
                </div>
                <div class="flex-shrink-0">
                    <i class="bi bi-clock fs-1 text-danger"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Revenue Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Monthly Revenue</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Quick Stats</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>This Month Revenue</span>
                    <strong class="text-success">Rp {{ number_format($stats['this_month_revenue'], 0, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span>Overdue Invoices</span>
                    <span class="badge bg-danger">{{ $stats['overdue_invoices'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Active Clients</span>
                    <strong class="text-primary">{{ $stats['total_clients'] }}</strong>
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Create Invoice
                    </a>
                    <a href="{{ route('clients.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus me-2"></i>Add Client
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Invoices -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Recent Invoices</h5>
                <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-light">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recent_invoices->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recent_invoices as $invoice)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $invoice->invoice_number }}</h6>
                                    <small class="text-muted">{{ $invoice->client->name }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $invoice->status_badge }}">{{ ucfirst($invoice->status) }}</span>
                                    <div class="small text-muted">{{ $invoice->formatted_total }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-receipt fs-1 text-muted mb-3"></i>
                        <p class="text-muted">No invoices yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Pending Payments -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Pending Payments</h5>
                <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-light">View All</a>
            </div>
            <div class="card-body p-0">
                @if($pending_payments->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($pending_payments as $payment)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $payment->payment_number }}</h6>
                                    <small class="text-muted">{{ $payment->client->name }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $payment->status_badge }}">{{ ucfirst($payment->status) }}</span>
                                    <div class="small text-muted">{{ $payment->formatted_amount }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-credit-card fs-1 text-muted mb-3"></i>
                        <p class="text-muted">No pending payments</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($overdue_invoices->count() > 0)
<div class="row">
    <!-- Overdue Invoices Alert -->
    <div class="col-12 mb-4">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Overdue Invoices</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($overdue_invoices as $invoice)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ $invoice->invoice_number }}</h6>
                                <small class="text-muted">{{ $invoice->client->name }} - Due: {{ $invoice->due_date->format('d M Y') }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger">{{ $invoice->days_overdue }} days overdue</span>
                                <div class="small text-muted">{{ $invoice->formatted_balance }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($monthly_revenue->pluck('month')) !!},
        datasets: [{
            label: 'Revenue',
            data: {!! json_encode($monthly_revenue->pluck('revenue')) !!},
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#667eea',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                borderColor: '#667eea',
                borderWidth: 1,
                callbacks: {
                    label: function(context) {
                        return 'Revenue: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                },
                ticks: {
                    callback: function(value) {
                        if (value >= 1000000) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                        } else if (value >= 1000) {
                            return 'Rp ' + (value / 1000).toFixed(1) + 'K';
                        }
                        return 'Rp ' + value;
                    }
                }
            },
            x: {
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});
</script>
@endpush