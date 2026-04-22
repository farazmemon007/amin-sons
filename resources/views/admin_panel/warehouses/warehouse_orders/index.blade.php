@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Warehouse Orders</h3>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Order No</th>
                <th>Customer</th>
                <th>Warehouse</th>
                <th>Items</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->order_no ?? 'DC-'.$order->id }}</td>
                <td>{{ $order->customer->name ?? '-' }}</td>
                <td>{{ $order->warehouse->warehouse_name ?? '-' }}</td>
                <td>{{ $order->itemsRelation->count() }} items</td>
                <td>{{ $order->status }}</td>
                <td>
                    <a href="{{ route('admin.warehouse_orders.edit',$order->id) }}" class="btn btn-sm btn-primary">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $orders->links() }}
</div>
@endsection
