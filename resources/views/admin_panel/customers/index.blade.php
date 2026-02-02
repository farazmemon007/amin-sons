@extends('admin_panel.layout.app')
@section('content')
<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }

    .main-content {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .main-content-inner {
        width: 100%;
        padding: 0;
        margin: 0;
    }

    .container {
        padding: 0 1rem;
        width: 100%;
        max-width: 100%;
    }

    .table {
        width: 100%;
        margin-bottom: 0;
    }

    .table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8f9fa;
        vertical-align: middle;
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        padding: 0.5rem 0.35rem !important;
    }

    .table td {
        vertical-align: middle;
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        padding: 0.4rem 0.35rem;
    }

    /* Column width management */
    .table th:nth-child(1),
    .table td:nth-child(1) {
        min-width: 60px;
        width: 8%;
    }

    .table th:nth-child(2),
    .table td:nth-child(2) {
        min-width: 100px;
        width: 12%;
    }

    .table th:nth-child(3),
    .table td:nth-child(3) {
        min-width: 80px;
        width: 10%;
    }

    .table th:nth-child(4),
    .table td:nth-child(4) {
        min-width: 80px;
        width: 10%;
    }

    .table th:nth-child(5),
    .table td:nth-child(5) {
        min-width: 70px;
        width: 8%;
    }

    .table th:nth-child(6),
    .table td:nth-child(6) {
        min-width: 85px;
        width: 10%;
    }

    .table th:nth-child(7),
    .table td:nth-child(7) {
        min-width: 85px;
        width: 10%;
    }

    .table th:nth-child(8),
    .table td:nth-child(8) {
        min-width: 80px;
        width: 10%;
    }

    .table th:nth-child(9),
    .table td:nth-child(9) {
        min-width: 70px;
        width: 8%;
    }

    .table th:nth-child(10),
    .table td:nth-child(10) {
        min-width: 70px;
        width: 7%;
    }

    .table th:nth-child(11),
    .table td:nth-child(11) {
        min-width: 120px;
        width: 17%;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        overflow-y: visible;
    }

    .table-responsive::-webkit-scrollbar {
        height: 0;
        display: none;
    }

    .table-responsive {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .btn {
        font-size: 0.85rem;
        padding: 0.35rem 0.5rem;
    }

    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.4rem;
    }

    .btn-sm i.fa-toggle-on {
        color: green;
        font-size: 18px;
    }

    .btn-sm i.fa-toggle-off {
        color: gray;
        font-size: 18px;
    }

    h3 {
        font-size: 1.3rem;
        margin-bottom: 1rem;
    }

    /* Mobile Responsive */
    @media (max-width: 992px) {
        .table {
            font-size: 0.8rem;
        }

        .table thead th {
            font-size: 0.75rem;
            padding: 0.3rem 0.25rem !important;
        }

        .table td {
            font-size: 0.75rem;
            padding: 0.3rem 0.25rem;
        }

        .btn {
            font-size: 0.7rem;
            padding: 0.3rem 0.4rem;
        }

        .btn-sm {
            font-size: 0.65rem;
            padding: 0.15rem 0.3rem;
        }
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 0.5rem;
        }

        h3 {
            font-size: 1.1rem;
            margin-bottom: 0.8rem;
        }

        .btn {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }

        .table {
            font-size: 0.7rem;
        }

        .table thead th {
            font-size: 0.65rem;
            padding: 0.25rem 0.2rem !important;
        }

        .table td {
            font-size: 0.65rem;
            padding: 0.25rem 0.2rem;
        }

        .btn-sm {
            font-size: 0.6rem;
            padding: 0.1rem 0.25rem;
        }

        .btn-sm i.fa-toggle-on {
            font-size: 14px;
        }

        .btn-sm i.fa-toggle-off {
            font-size: 14px;
        }
    }

    @media (max-width: 576px) {
        .container {
            padding: 0 0.25rem;
        }

        h3 {
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }

        .table {
            font-size: 0.6rem;
        }

        .table thead th {
            font-size: 0.55rem;
            padding: 0.2rem 0.15rem !important;
        }

        .table td {
            font-size: 0.55rem;
            padding: 0.2rem 0.15rem;
        }

        .btn {
            font-size: 0.6rem;
            padding: 0.15rem 0.25rem;
        }

        .btn-sm {
            font-size: 0.55rem;
            padding: 0.1rem 0.2rem;
        }

        .btn-sm i.fa-toggle-on {
            font-size: 12px;
        }

        .btn-sm i.fa-toggle-off {
            font-size: 12px;
        }

        .mb-3 {
            margin-bottom: 0.25rem !important;
        }

        .alert {
            font-size: 0.65rem;
            padding: 0.3rem;
        }

        /* Allow button text to wrap on small screens */
        .btn {
            white-space: normal;
            word-break: break-word;
        }

        .table th:nth-child(11),
        .table td:nth-child(11) {
            min-width: 100px;
        }
    }
</style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <h3>Customer List</h3>
                <div class="mb-3 d-flex gap-2 flex-wrap">
                    <a href="{{ route('customers.create') }}" class="btn btn-primary">+ Add New Customer</a>
                    <a href="{{ route('customers.ledger') }}" class="btn btn-primary">Ledger</a>
                    <a href="{{ route('customer.payments') }}" class="btn btn-primary">Payment</a>
                    <a href="{{ route('customers.inactive') }}" class="btn btn-secondary ms-auto">View Inactive</a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Customer ID</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Zone</th>
                <th>Dabit <br> Credit</th>
                <th>Opening Balance</th>
                <th>Closing Balance</th>
                <th>Credit Limit</th>
                <th>Filer Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td>{{ $customer->customer_id }}</td>
                <td>{{ $customer->customer_name }}</td>
                <td>{{ $customer->mobile }}</td>
                <td>{{ $customer->address }}</td>
                <td><span>{{ $customer->customer_type }}</span></td>
                <td>{{ number_format($customer->opening_balance, 2) }}</td>
                <td>{{ number_format($customer->closing_balance, 2) }}</td>
                <td>{{ number_format($customer->credit_limit, 2) }}</td>
                <td>{{ $customer->filer_type }}</td>
                <td>{{ $customer->status }}</td>
                <td class="text-center">
                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-warning me-1">Edit</a>

                    <a href="{{ route('customers.toggleStatus', $customer->id) }}" 
                       class="btn btn-sm {{ $customer->status === 'active' ? 'btn-dark' : 'btn-secondary' }} me-1"
                       title="Toggle Status">
                        <i class="fa-solid {{ $customer->status === 'active' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                    </a>

                    <a href="{{ route('customers.destroy', $customer->id) }}" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm('Are you sure?')">
                        Delete
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
