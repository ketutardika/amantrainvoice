@extends('layouts.app')

@section('title', 'Clients')
@section('page-title', 'Clients')
@section('page-description', 'Manage your clients and customers')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Clients</li>
@endsection

@section('page-actions')
    <a href="{{ route('clients.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Add Client
    </a>
@endsection

@section('content')
<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('clients.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Name, company, or email...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>Individual</option>
                        <option value="company" {{ request('type') == 'company' ? 'selected' : '' }}>Company</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-select">
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>Created Date</option>
                        <option value="invoices_count" {{ request('sort') == 'invoices_count' ? 'selected' : '' }}>Invoice Count</option>
                        <option value="total_amount" {{ request('sort') == 'total_amount' ? 'selected' : '' }}>Total Amount</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Clients Table -->
<div class="card">
    <div class="card-body p-0">
        @if($clients->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Contact</th>
                            <th>Type</th>
                            <th>Payment Terms</th>
                            <th>Statistics</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            @if($client->avatar)
                                                <img src="{{ Storage::url($client->avatar) }}" alt="{{ $client->name }}" class="rounded-circle" width="40" height="40">
                                            @else
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($client->name, 0, 1)) }}{{ strtoupper(substr($client->name, strpos($client->name, ' ') + 1, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <strong>{{ $client->name }}</strong>
                                            @if($client->company_name)
                                                <br><small class="text-muted">{{ $client->company_name }}</small>
                                            @endif
                                            <br><small class="text-muted">ID: {{ $client->client_code }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="bi bi-envelope me-1"></i>{{ $client->email }}<br>
                                        @if($client->phone)
                                            <i class="bi bi-phone me-1"></i>{{ $client->phone }}<br>
                                        @endif
                                        @if($client->city)
                                            <i class="bi bi-geo-alt me-1"></i>{{ $client->city }}, {{ $client->country }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $client->client_type === 'company' ? 'primary' : 'secondary' }}">
                                        {{ ucfirst($client->client_type) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $client->payment_terms }} days<br>
                                    <small class="text-muted">Credit: Rp {{ number_format($client->credit_limit, 0, ',', '.') }}</small>
                                </td>
                                <td>
                                    <div class="small">
                                        <strong>{{ $client->invoices_count ?? 0 }}</strong> invoices<br>
                                        <strong class="text-success">Rp {{ number_format($client->invoices_sum_total_amount ?? 0, 0, ',', '.') }}</strong> total<br>
                                        <strong>{{ $client->payments_count ?? 0 }}</strong> payments
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $client->is_active ? 'success' : 'danger' }}">
                                        {{ $client->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="More Actions">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('invoices.create', ['client' => $client->id]) }}">
                                                    <i class="bi bi-receipt me-2"></i>Create Invoice
                                                </a></li>
                                                <li><a class="dropdown-item" href="{{ route('invoices.index', ['client' => $client->id]) }}">
                                                    <i class="bi bi-list me-2"></i>View Invoices
                                                </a></li>
                                                <li><a class="dropdown-item" href="{{ route('payments.index', ['client' => $client->id]) }}">
                                                    <i class="bi bi-credit-card me-2"></i>View Payments
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                @if($client->is_active)
                                                    <li>
                                                        <form method="POST" action="{{ route('clients.update', $client) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="is_active" value="0">
                                                            <button type="submit" class="dropdown-item text-warning">
                                                                <i class="bi bi-pause me-2"></i>Deactivate
                                                            </button>
                                                        </form>
                                                    </li>
                                                @else
                                                    <li>
                                                        <form method="POST" action="{{ route('clients.update', $client) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="is_active" value="1">
                                                            <button type="submit" class="dropdown-item text-success">
                                                                <i class="bi bi-play me-2"></i>Activate
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                @if($client->invoices_count == 0)
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="{{ route('clients.destroy', $client) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this client?')">
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
                        Showing {{ $clients->firstItem() }} to {{ $clients->lastItem() }} of {{ $clients->total() }} results
                    </div>
                    <div>
                        {{ $clients->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-people fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">No clients found</h5>
                <p class="text-muted mb-4">
                    @if(request()->hasAny(['search', 'type', 'status']))
                        No clients match your current filters. Try adjusting your search criteria.
                    @else
                        Add your first client to start creating invoices and managing your business relationships.
                    @endif
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('clients.create') }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Add Client
                    </a>
                    @if(request()->hasAny(['search', 'type', 'status']))
                        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Clear Filters
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Client Statistics -->
@if($clients->count() > 0)
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary">Total Clients</h5>
                <h3 class="text-primary">{{ $clients->total() }}</h3>
                <small class="text-muted">
                    {{ $clients->where('is_active', true)->count() }} active
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success">Companies</h5>
                <h3 class="text-success">{{ $clients->where('client_type', 'company')->count() }}</h3>
                <small class="text-muted">
                    {{ number_format(($clients->where('client_type', 'company')->count() / $clients->count()) * 100, 1) }}% of total
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-info">Individuals</h5>
                <h3 class="text-info">{{ $clients->where('client_type', 'individual')->count() }}</h3>
                <small class="text-muted">
                    {{ number_format(($clients->where('client_type', 'individual')->count() / $clients->count()) * 100, 1) }}% of total
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning">Total Revenue</h5>
                <h3 class="text-warning">
                    {{ number_format($clients->sum('invoices_sum_total_amount') / 1000000, 1) }}M
                </h3>
                <small class="text-muted">
                    Rp {{ number_format($clients->sum('invoices_sum_total_amount'), 0, ',', '.') }}
                </small>
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
    
    // Auto-submit on select change (except search input)
    selects.forEach(function(select) {
        select.addEventListener('change', function() {
            form.submit();
        });
    });
    
    // Search with Enter key or after typing stops
    const searchInput = form.querySelector('input[name="search"]');
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            if (searchInput.value.length >= 2 || searchInput.value.length === 0) {
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

// Confirmation for client activation/deactivation
document.addEventListener('DOMContentLoaded', function() {
    const activationForms = document.querySelectorAll('form[action*="clients"]');
    activationForms.forEach(function(form) {
        const isActivateAction = form.querySelector('input[name="is_active"][value="1"]');
        const isDeactivateAction = form.querySelector('input[name="is_active"][value="0"]');
        
        if (isActivateAction || isDeactivateAction) {
            form.addEventListener('submit', function(e) {
                const action = isActivateAction ? 'activate' : 'deactivate';
                if (!confirm(`Are you sure you want to ${action} this client?`)) {
                    e.preventDefault();
                }
            });
        }
    });
});

// Tooltip initialization for action buttons
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush

@push('styles')
<style>
.avatar-circle {
    flex-shrink: 0;
}

.avatar-circle img {
    object-fit: cover;
}

.table td {
    vertical-align: middle;
}

.card-body .small {
    line-height: 1.4;
}
</style>
@endpush