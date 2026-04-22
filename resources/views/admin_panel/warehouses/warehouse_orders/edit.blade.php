@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Edit Warehouse Order #{{ $order->id }}</h3>

    <form method="POST" action="{{ route('admin.warehouse_orders.update', $order->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Status</label>
            <input type="text" name="status" class="form-control" value="{{ $order->status }}">
        </div>

        <div class="mb-3">
            <label>Remarks</label>
            <textarea name="remarks" class="form-control">{{ $order->remarks }}</textarea>
        </div>

        <h5>Items</h5>
        <table class="table table-sm" id="items_table">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Code</th>
                    <th>Qty</th>
                    <th>Retail</th>
                    <th>Amount</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->itemsRelation as $it)
                <tr>
                    <td><input name="product_id[]" class="form-control" value="{{ $it->product_id }}"></td>
                    <td><input name="product_name[]" class="form-control" value="{{ $it->product_name }}"></td>
                    <td><input name="item_code[]" class="form-control" value="{{ $it->item_code }}"></td>
                    <td><input name="qty[]" class="form-control" value="{{ $it->qty }}"></td>
                    <td><input name="retail_price[]" class="form-control" value="{{ $it->retail_price }}"></td>
                    <td><input name="amount[]" class="form-control" value="{{ $it->amount }}"></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-row">x</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" id="add_row" class="btn btn-sm btn-secondary">Add Row</button>

        <div class="mt-3">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.warehouse_orders.index') }}" class="btn btn-link">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('add_row').addEventListener('click', function(){
    const tbody = document.querySelector('#items_table tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input name="product_id[]" class="form-control"></td>
        <td><input name="product_name[]" class="form-control"></td>
        <td><input name="item_code[]" class="form-control"></td>
        <td><input name="qty[]" class="form-control"></td>
        <td><input name="retail_price[]" class="form-control"></td>
        <td><input name="amount[]" class="form-control"></td>
        <td><button type="button" class="btn btn-sm btn-danger remove-row">x</button></td>
    `;
    tbody.appendChild(tr);
});

document.addEventListener('click', function(e){
    if(e.target.classList.contains('remove-row')){
        e.target.closest('tr').remove();
    }
});
</script>
@endpush

@endsection
