@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoices')
@section('page-description', 'Manage your invoices')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Invoices</li>
@endsection

@section('page-actions')
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Create Invoice
    </a>
@endsection

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('invoices.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Invoice number or client...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="viewed" {{ request('status') == 'viewed' ? 'selected' : '' }}>Viewed</option>
                        <option value="partial_paid" {{ request('status') == 'partial_paid' ? 'selected' : '' }}>Partial Paid</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Client</label>
                    <select name="client" class="form-select">
                        <option value="">All Clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Invoices Table -->
<div class="card">
    <div class="card-body p-0">
        @if($invoices->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td>
                                    <strong>{{ $invoice->invoice_number }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $invoice->client->name }}</strong>
                                        @if($invoice->client->company_name)
                                            <br><small class="text-muted">{{ $invoice->client->company_name }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                                <td>
                                    {{ $invoice->due_date->format('d M Y') }}
                                    @if($invoice->is_overdue)
                                        <br><small class="text-danger">{{ $invoice->days_overdue }} days overdue</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $invoice->formatted_total }}</strong>
                                    @if($invoice->balance_due > 0)
                                        <br><small class="text-muted">Balance: {{ $invoice->formatted_balance }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $invoice->status_badge }}">
                                        {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        
                                        @if($invoice->status === 'draft')
                                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        
                                        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-success" target="_blank" title="Download PDF">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                        
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="More Actions">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if($invoice->status === 'draft')
                                                    <li>
                                                        <form method="POST" action="{{ route('invoices.send', $invoice) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="bi bi-send me-2"></i>Send Invoice
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                <li>
                                                    <form method="POST" action="{{ route('invoices.duplicate', $invoice) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="bi bi-copy me-2"></i>Duplicate
                                                        </button>
                                                    </form>
                                                </li>
                                                @if($invoice->balance_due > 0 && $invoice->status !== 'draft')
                                                    <li><a class="dropdown-item" href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}">
                                                        <i class="bi bi-credit-card me-2"></i>Record Payment
                                                    </a></li>
                                                @endif
                                                <li><a class="dropdown-item" href="{{ route('invoices.pdf.preview', $invoice) }}" target="_blank">
                                                    <i class="bi bi-eye me-2"></i>Preview PDF
                                                </a></li>
                                                @if($invoice->status === 'draft')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this invoice?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash me-2"></i>Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} results
                    </div>
                    <div>
                        {{ $invoices->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-receipt fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">No invoices found</h5>
                <p class="text-muted mb-4">
                    @if(request()->hasAny(['search', 'status', 'client', 'date_from', 'date_to']))
                        No invoices match your current filters. Try adjusting your search criteria.
                    @else
                        Create your first invoice to get started with billing your clients.
                    @endif
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Create Invoice
                    </a>
                    @if(request()->hasAny(['search', 'status', 'client', 'date_from', 'date_to']))
                        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Clear Filters
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Invoice Summary Cards -->
@if($invoices->count() > 0)
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary">Total Invoices</h5>
                <h3 class="text-primary">{{ $invoices->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success">Total Amount</h5>
                <h3 class="text-success">Rp {{ number_format($invoices->sum('total_amount'), 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning">Pending</h5>
                <h3 class="text-warning">Rp {{ number_format($invoices->sum('balance_due'), 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-info">Paid</h5>
                <h3 class="text-info">Rp {{ number_format($invoices->sum('paid_amount'), 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// Auto-submit form when filters change
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[method="GET"]');
    const selects = form.querySelectorAll('select');
    const dateInputs = form.querySelectorAll('input[type="date"]');
    
    // Auto-submit on select change (except search input)
    selects.forEach(function(select) {
        select.addEventListener('change', function() {
            form.submit();
        });
    });
    
    // Auto-submit on date change
    dateInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            form.submit();
        });
    });
    
    // Search with Enter key or after typing stops
    const searchInput = form.querySelector('input[name="search"]');
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            if (searchInput.value.length >= 3 || searchInput.value.length === 0) {
                form.submit();
            }
        }, 500);
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            form.submit();
        }
    });
});

// Confirmation for dangerous actions
document.addEventListener('DOMContentLoaded', function() {
    const dangerousForms = document.querySelectorAll('form[onsubmit*="confirm"]');
    dangerousForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to perform this action?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush