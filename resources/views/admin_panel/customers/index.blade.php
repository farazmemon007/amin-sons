@extends('admin_panel.layout.app')
@section('content')
<style>
    .btn-sm i.fa-toggle-on {
        color: green;
        font-size: 20px;
    }

    .btn-sm i.fa-toggle-off {
        color: gray;
        font-size: 20px;
    }
</style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
  <h3>Customer List</h3>
  <a href="{{ route('customers.inactive') }}" class="btn btn-secondary mb-3 float-end">View Inactive Customers</a>

    <a href="{{ route('customers.create') }}" class="btn btn-primary mb-3">+ Add New Customer</a>
    <a href="{{ route('customers.ledger') }}" class="btn btn-primary mb-3">Ledger</a>
    <a href="{{ route('customer.payments') }}" class="btn btn-primary mb-3">payment</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Customer ID</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Zone</th>
                <th>Dabit <br> Credit</th>
                <th>Closing Balance</th>
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
                <td><span> {{ $customer->customer_type}}  </span></td>
                <td>{{ number_format($customer->closing_balance, 2) }}</td>
                <td>{{ $customer->filer_type }}</td>
                <td>{{ $customer->status }}</td>
                <td>
    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-warning">Edit</a>

    <a href="{{ route('customers.toggleStatus', $customer->id) }}" 
       class="btn btn-sm {{ $customer->status === 'active' ? 'btn-dark' : 'btn-secondary' }}"
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
