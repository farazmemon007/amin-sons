@extends('admin_panel.layout.app')
@section('content')

<div class="card shadow-sm border-0">
    <div class="card-header">
        <h5>➕ Add Warehouse Stock</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('warehouse_stocks.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Warehouse</label>
                <select name="warehouse_id" class="form-control" required>
                    <option value="">Select Warehouse</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Product</label>
                <select name="product_id" class="form-control product-select" required style="width:100%">
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        @php $rem = $remainingByProduct[$product->id] ?? null; @endphp
                        <option value="{{ $product->id }}">{{ $product->item_name }}{{ $rem !== null ? ' (Remaining: '.$rem.')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Quantity</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>
            {{--  <div class="mb-3">
                <label>Price</label>
                <input type="number" step="0.01" name="price" class="form-control">
            </div>  --}}
            <div class="mb-3">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Add Stock</button>
        </form>
    </div>
</div>

@endsection

@section('js')
    <script>
        // Copy of initProductSelect2 used in sale add view — enables typing search + paged dropdown
        function initProductSelect2(
            selector = '.product-select',
            url = '/search-products-sale',
            searchUrl = '/search_products'
        ) {
            $(selector).select2({
                ajax: {
                    transport: function (params, success, failure) {
                        // prefer params.data.term which Select2 populates
                        let term = (params.data && (params.data.term || params.data.q)) || '';
                        let page = (params.data && (params.data.page || 1)) || 1;
                        let ajaxUrl = term && term.length > 0 ? searchUrl : url;
                        $.ajax({
                            url: ajaxUrl,
                            data: { q: term, page: page },
                            dataType: 'json',
                            success: function (data) { success(data); },
                            error: failure
                        });
                    },
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        let results = [];
                        if (Array.isArray(data)) {
                            results = data.map(function (p) {
                                return { id: p.id, text: p.item_name, stock: p.stock, price: p.retail_price || p.price };
                            });
                            return { results: results, pagination: { more: false } };
                        }

                        results = (data.products || []).map(function (p) {
                            return { id: p.id, text: p.item_name, stock: p.stock, price: p.retail_price || p.price };
                        });

                        return {
                            results: results,
                            pagination: { more: !!data.has_more }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                placeholder: 'Search product...',
                allowClear: true,
                width: 'resolve'
            });
        }

        $(document).ready(function () {
            initProductSelect2('.product-select', '/search-products-sale', '/search_products');
        });
    </script>
@endsection
