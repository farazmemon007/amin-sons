@extends('admin_panel.layout.app')
@section('content')
<style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #858796;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --dark-bg: #1a1c23;
        --light-bg: #f8f9fc;
        --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .main-content {
        background-color: var(--light-bg);
        min-height: 100vh;
        padding: 1.5rem;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        background: #fff;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: var(--card-shadow);
    }

    .page-title {
        font-weight: 700;
        color: #333;
        margin: 0;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .page-title i {
        color: var(--primary-color);
    }

    .btn-action-group {
        display: flex;
        gap: 0.5rem;
    }

    .card-filter {
        border: none;
        border-radius: 0.75rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 1.5rem;
        background: #fff;
    }

    .card-filter .card-body {
        padding: 1.25rem;
    }

    .filter-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--secondary-color);
        text-transform: uppercase;
        margin-bottom: 0.4rem;
    }

    /* Table Styling */
    .table-container {
        background: #fff;
        border-radius: 0.75rem;
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background: #f1f4f9;
        color: #444;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.025em;
        padding: 1rem 0.75rem;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }

    .table tbody td {
        padding: 0.85rem 0.75rem;
        vertical-align: middle;
        font-size: 0.85rem;
        color: #555;
        border-bottom: 1px solid #edf2f7;
    }

    .table tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.03);
    }

    /* Badges */
    .badge-erp {
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 600;
        border-radius: 0.35rem;
        text-transform: uppercase;
    }

    .badge-cash { background-color: #d1ecf1; color: #0c5460; }
    .badge-credit { background-color: #fff3cd; color: #856404; }
    .badge-active { background-color: rgba(28, 200, 138, 0.1); color: var(--success-color); }
    .badge-inactive { background-color: rgba(231, 74, 59, 0.1); color: var(--danger-color); }

    .balance-val {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 700;
        text-align: right;
    }

    .balance-positive { color: var(--success-color); }
    .balance-negative { color: var(--danger-color); }

    /* Action Buttons */
    .btn-erp {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        transition: all 0.2s;
        border: none;
    }

    .btn-edit { background: rgba(78, 115, 223, 0.1); color: var(--primary-color); }
    .btn-edit:hover { background: var(--primary-color); color: #fff; }

    .btn-toggle { background: rgba(54, 185, 204, 0.1); color: var(--info-color); }
    .btn-toggle:hover { background: var(--info-color); color: #fff; }

    .btn-delete { background: rgba(231, 74, 59, 0.1); color: var(--danger-color); }
    .btn-delete:hover { background: var(--danger-color); color: #fff; }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
        .main-content {
            padding: 1rem;
        }
    }
</style>

    <div class="main-content">
        <div class="page-header">
            <h3 class="page-title"><i class="fa-solid fa-users-rectangle"></i> Customer Management</h3>
            <div class="btn-action-group">
                <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm"><i class="fa fa-plus me-1"></i> Add New</a>
                <a href="{{ route('customers.ledger') }}" class="btn btn-info btn-sm rounded-pill px-3 shadow-sm"><i class="fa fa-book me-1"></i> Ledger</a>
                <a href="{{ route('customer.payments') }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm"><i class="fa fa-wallet me-1"></i> Payment</a>
                <a href="{{ route('customers.inactive') }}" class="btn btn-secondary btn-sm rounded-pill px-3 shadow-sm"><i class="fa fa-user-slash me-1"></i> Inactive</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            </div>
        @endif

        {{-- ✅ Enhanced Search Card --}}
        <div class="card card-filter">
            <div class="card-body">
                <form action="{{ route('customers.index') }}" method="GET" class="row g-3">
                    @if(Auth::user()->hasRole('super admin'))
                        <div class="col-md-2">
                            <label class="filter-label">Branch</label>
                            <select name="branch_id" id="branch_filter" class="form-control select2">
                                <option value="">All Branches</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-2">
                        <label class="filter-label">Type</label>
                        <select name="customer_type" id="type_filter" class="form-control select2">
                            <option value="">All Types</option>
                            <option value="Cash" {{ request('customer_type') == 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Credit" {{ request('customer_type') == 'Credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="filter-label">Search Customer</label>
                        <select name="customer_id" id="customer_filter" class="form-control select2">
                            <option value="">All Customers</option>
                            @foreach($allCustomers as $c)
                                <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->customer_name }} ({{ $c->customer_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 col-6">
                        <label class="filter-label">From</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>

                    <div class="col-md-2 col-6">
                        <label class="filter-label">To</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>

                    <div class="col-md-1 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100 shadow-sm" title="Search"><i class="fa fa-search"></i></button>
                        <a href="{{ route('customers.index') }}" class="btn btn-light w-100 shadow-sm border" title="Reset"><i class="fa fa-refresh"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            @if(Auth::check() && Auth::user()->hasRole('super admin'))
                                <th>Branch</th>
                            @endif
                            <th>Customer ID</th>
                            <th>Customer Name</th>
                            <th>Contact</th>
                            <th>Zone/Area</th>
                            <th>Type</th>
                            <th>Opening</th>
                            <th>Closing</th>
                            <th>Credit Limit</th>
                            <th>Filer</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                @if(Auth::check() && Auth::user()->hasRole('super admin'))
                                    <td><span class="fw-600 text-dark">{{ $customer->branch->name }}</span></td>
                                @endif
                                <td><span class="text-primary fw-bold">{{ $customer->customer_id }}</span></td>
                                <td>
                                    <div class="fw-bold">{{ $customer->customer_name }}</div>
                                    <small class="text-muted">{{ $customer->customer_type }} Customer</small>
                                </td>
                                <td>{{ $customer->mobile }}</td>
                                <td><i class="fa fa-location-dot me-1 text-secondary"></i> {{ $customer->address }}</td>
                                <td>
                                    <span class="badge-erp {{ $customer->customer_type == 'Cash' ? 'badge-cash' : 'badge-credit' }}">
                                        {{ $customer->customer_type }}
                                    </span>
                                </td>
                                <td class="balance-val">{{ number_format($customer->opening_balance, 2) }}</td>
                                <td class="balance-val {{ $customer->closing_balance < 0 ? 'balance-negative' : 'balance-positive' }}">
                                    {{ number_format($customer->closing_balance, 2) }}
                                </td>
                                <td class="balance-val text-info">{{ number_format($customer->credit_limit, 2) }}</td>
                                <td>{{ $customer->filer_type }}</td>
                                <td>
                                    <span class="badge-erp {{ $customer->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $customer->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn-erp btn-edit" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <a href="{{ route('customers.toggleStatus', $customer->id) }}" class="btn-erp btn-toggle" title="Toggle Status">
                                            <i class="fa-solid {{ $customer->status === 'active' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                        </a>

                                        <a href="{{ route('customers.destroy', $customer->id) }}" class="btn-erp btn-delete" 
                                           onclick="return confirm('Delete this customer?')" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center py-5">
                                    <img src="https://illustrations.popsy.co/amber/no-data.svg" style="height: 150px;" class="mb-3">
                                    <h5 class="text-muted">No customers found matching your criteria.</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%',
        allowClear: true,
        placeholder: 'Select One'
    });

    // ✅ Dynamic Branch & Type -> Customer Filter
    function fetchCustomers() {
        const branchId = $('#branch_filter').val();
        const type     = $('#type_filter').val();
        const $customerFilter = $('#customer_filter');

        // Only auto-fetch if we have enough context or if we want to reset
        // If super admin hasn't selected branch yet, and we are super admin, wait (or show nothing)
        @if(Auth::user()->hasRole('super admin'))
            if (!branchId && !type) {
                // $customerFilter.empty().append('<option value="">All Customers</option>').trigger('change');
                // return;
            }
        @endif

        $customerFilter.empty().append('<option value="">Loading...</option>').trigger('change');

        $.ajax({
            url: "{{ url('sale/customers') }}",
            type: "GET",
            data: { 
                branch_id: branchId,
                type: type
            },
            success: function(data) {
                $customerFilter.empty().append('<option value="">All Customers</option>');
                $.each(data, function(index, customer) {
                    $customerFilter.append(`<option value="${customer.id}">${customer.customer_name} (${customer.customer_id})</option>`);
                });
                $customerFilter.trigger('change');
            },
            error: function() {
                // Swal.fire('Error', 'Failed to load customers.', 'error');
                $customerFilter.empty().append('<option value="">All Customers</option>').trigger('change');
            }
        });
    }

    $('#branch_filter, #type_filter').on('change', function() {
        fetchCustomers();
    });
});
</script>
@endsection
