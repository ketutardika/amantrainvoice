@extends('layouts.app')

@section('title', 'Invoice Details')
@section('page-title', 'Invoice ' . $invoice->invoice_number)
@section('page-description', 'View invoice details and manage payments')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Invoices</a></li>
    <li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
@endsection

@section('page-actions')
    <div class="btn-group" role="group">
        @if($invoice->status === 'draft')
            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-2"></i>Edit
            </a>
            <form method="POST" action="{{ route('invoices.send', $invoice) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-send me-2"></i>Send Invoice
                </button>
            </form>
        @endif
        
        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-primary" target="_blank">
            <i class="bi bi-file-pdf me-2"></i>Download PDF
        </a>
        
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots me-2"></i>More
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('invoices.pdf.preview', $invoice) }}" target="_blank">
                    <i class="bi bi-eye me-2"></i>Preview PDF
                </a></li>
                <li>
                    <form method="POST" action="{{ route('invoices.duplicate', $invoice) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="bi bi-copy me-2"></i>Duplicate Invoice
                        </button>
                    </form>
                </li>
                @if($invoice->balance_due > 0 && $invoice->status !== 'draft')
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}">
                        <i class="bi bi-credit-card me-2"></i>Record Payment
                    </a></li>
                @endif
            </ul>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Invoice Details -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Invoice Details</h5>
                <span class="badge bg-{{ $invoice->status_badge }} fs-6">
                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                </span>
            </div>
            <div class="card-body">
                <!-- Invoice Header Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Bill To:</h6>
                        <strong>{{ $invoice->client->full_name }}</strong><br>
                        @if($invoice->client->company_name)
                            {{ $invoice->client->company_name }}<br>
                        @endif
                        {{ $invoice->client->formatted_address }}<br>
                        <i class="bi bi-envelope me-1"></i>{{ $invoice->client->email }}<br>
                        @if($invoice->client->phone)
                            <i class="bi bi-phone me-1"></i>{{ $invoice->client->phone }}
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="text-muted mb-2">Invoice Information:</h6>
                        <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                        <strong>Date:</strong> {{ $invoice->invoice_date->format('d M Y') }}<br>
                        <strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}
                        @if($invoice->is_overdue)
                            <span class="badge bg-danger ms-2">{{ $invoice->days_overdue }} days overdue</span>
                        @endif
                        <br>
                        <strong>Currency:</strong> {{ $invoice->currency }}
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Unit</th>
                                <th class="text-end">Rate</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->name }}</strong>
                                        @if($item->description)
                                            <br><small class="text-muted">{{ $item->description }}</small>
                                        @endif
                                        @if($item->item_type !== 'service')
                                            <br><span class="badge bg-secondary">{{ ucfirst($item->item_type) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-center">{{ $item->unit }}</td>
                                    <td class="text-end">{{ $item->formatted_unit_price }}</td>
                                    <td class="text-end">{{ $item->formatted_total_price }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end"><strong>Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</strong></td>
                            </tr>
                            @if($invoice->discount_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end">Discount:</td>
                                    <td class="text-end text-success">-Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            @if($invoice->tax_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end">Tax:</td>
                                    <td class="text-end">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            <tr class="table-primary">
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>{{ $invoice->formatted_total }}</strong></td>
                            </tr>
                            @if($invoice->paid_amount > 0)
                                <tr class="table-success">
                                    <td colspan="4" class="text-end">Paid Amount:</td>
                                    <td class="text-end">-Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="table-warning">
                                    <td colspan="4" class="text-end"><strong>Balance Due:</strong></td>
                                    <td class="text-end"><strong>{{ $invoice->formatted_balance }}</strong></td>
                                </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>

                <!-- Notes & Terms -->
                @if($invoice->notes || $invoice->terms_conditions)
                    <div class="row mt-4">
                        @if($invoice->notes)
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Notes:</h6>
                                <p class="mb-0">{{ $invoice->notes }}</p>
                            </div>
                        @endif
                        @if($invoice->terms_conditions)
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Terms & Conditions:</h6>
                                <p class="mb-0">{{ $invoice->terms_conditions }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment History -->
        @if($invoice->payments->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Payment #</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_number }}</td>
                                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                        <td>{{ $payment->payment_method_label }}</td>
                                        <td>{{ $payment->formatted_amount }}</td>
                                        <td>
                                            <span class="badge bg-{{ $payment->status_badge }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Invoice Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Invoice Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Amount:</span>
                    <strong>{{ $invoice->formatted_total }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Paid Amount:</span>
                    <span class="text-success">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Balance Due:</span>
                    <strong class="text-danger">{{ $invoice->formatted_balance }}</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span>Created:</span>
                    <span>{{ $invoice->created_at->format('d M Y H:i') }}</span>
                </div>
                @if($invoice->sent_at)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sent:</span>
                        <span>{{ $invoice->sent_at->format('d M Y H:i') }}</span>
                    </div>
                @endif
                @if($invoice->viewed_at)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Viewed:</span>
                        <span>{{ $invoice->viewed_at->format('d M Y H:i') }}</span>
                    </div>
                @endif
                @if($invoice->paid_at)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Paid:</span>
                        <span>{{ $invoice->paid_at->format('d M Y H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($invoice->balance_due > 0 && $invoice->status !== 'draft')
                        <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success">
                            <i class="bi bi-credit-card me-2"></i>Record Payment
                        </a>
                    @endif
                    
                    <a href="{{ route('invoices.pdf.preview', $invoice) }}" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-eye me-2"></i>Preview PDF
                    </a>
                    
                    <form method="POST" action="{{ route('invoices.duplicate', $invoice) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-copy me-2"></i>Duplicate Invoice
                        </button>
                    </form>
                    
                    <a href="{{ route('clients.show', $invoice->client) }}" class="btn btn-outline-info">
                        <i class="bi bi-person me-2"></i>View Client
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Log -->
        @if($invoice->statusLogs->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Status History</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($invoice->statusLogs->take(5) as $log)
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="timeline-marker bg-primary"></div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ ucfirst(str_replace('_', ' ', $log->to_status)) }}</h6>
                                        <p class="text-muted small mb-1">
                                            {{ $log->created_at->format('d M Y H:i') }}
                                            @if($log->user)
                                                by {{ $log->user->name }}
                                            @endif
                                        </p>
                                        @if($log->notes)
                                            <p class="text-muted small mb-0">{{ $log->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline-marker {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-top: 5px;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 20px;
    height: calc(100% - 15px);
    width: 2px;
    background-color: #dee2e6;
}

.timeline-item {
    position: relative;
}
</style>
@endpush