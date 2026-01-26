@extends('admin_panel.layout.app')
@section('content')
    <div class="card shadow-sm border-0">
        <div class="card-header">
            <h5>➕ New Stock Transfer</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('stock_transfers.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label>From Warehouse</label>
                    <select name="from_warehouse_id" id="from_warehouse_id" class="form-control" required>
                        <option value="">Select Warehouse</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <div class="row">
                        <div class="col-lg-6">
                        <label>To</label>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-check-label" for="toShop">Transfer to Shop</label>

                        </div>

                        <div class="col-6">
                            <select name="to_warehouse_id" class="form-control">
                                <option value="">Select Warehouse</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <input class="form-check-input form-control" type="checkbox" name="to_shop" value="1"
                                id="toShop">


                        </div>
                    </div>
                </div>

                <table class="w-100 border text-center" id="product_table">
                    <thead>
                        <tr class="bg-light">
                            <th>Product</th>
                            <th>Stock</th>
                            <th>Qty</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="product_body">
                        <tr class="product_row">
                            <td>
                                <select name="product_id[]" class="form-control product-select" required style="width:100%">
                                    <option value="">Select Product</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="available_stock[]" class="form-control stock" readonly>
                            </td>
                            <td>
                                <input type="number" name="quantity[]" class="form-control quantity" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger remove-row">Remove</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="mb-3">
                    <label>Remarks</label>
                    <textarea name="remarks" class="form-control"></textarea>
                </div>

                <button type="submit" class="btn btn-success">Transfer Stock</button>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function initProductSelect2(
            selector = '.product-select',
            url = '/search-products-sale',
            searchUrl = '/search_products'
        ) {
            $(selector).select2({
                ajax: {
                    transport: function (params, success, failure) {
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
                        return { q: params.term || '', page: params.page || 1 };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        let results = [];
                        if (Array.isArray(data)) {
                            results = data.map(function (p) { return { id: p.id, text: p.item_name }; });
                            return { results: results, pagination: { more: false } };
                        }

                        results = (data.products || []).map(function (p) { return { id: p.id, text: p.item_name }; });
                        return { results: results, pagination: { more: !!data.has_more } };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                placeholder: 'Search product...',
                allowClear: true,
                width: 'resolve'
            });
        }

        $(document).ready(function() {
            initProductSelect2('.product-select', '/search-products-sale', '/search_products');

            function fetchStock(warehouseId, productId, currentRow) {
                if (warehouseId && productId) {
                    $.ajax({
                        url: '/warehouse-stock-quantity',
                        method: 'GET',
                        data: { warehouse_id: warehouseId, product_id: productId },
                        success: function(response) {
                            if (currentRow) {
                                currentRow.find('.stock').val(response.quantity);
                                currentRow.find('.quantity').attr('max', response.quantity);
                            }
                        }
                    });
                }
            }

            $('#from_warehouse_id').on('change', function() {
                var warehouseId = $(this).val();
                $('#product_body tr').each(function() {
                    var row = $(this);
                    var pid = row.find('.product-select').val();
                    if (pid) fetchStock(warehouseId, pid, row);
                });
            });

            $(document).on('change', '.product-select', function() {
                var currentRow = $(this).closest('tr');
                var selectedProduct = $(this).val();
                var fromWarehouse = $('#from_warehouse_id').val();

                if (selectedProduct && fromWarehouse) {
                    fetchStock(fromWarehouse, selectedProduct, currentRow);
                }

                if ($('#product_body tr:last').is(currentRow)) {
                    addNewRow();
                }
            });

            $(document).on('input', '.quantity', function() {
                var entered = parseInt($(this).val());
                var max = parseInt($(this).attr('max'));
                if (entered > max) { alert('Cannot transfer more than available stock!'); $(this).val(max); }
            });

            $(document).on('click', '.remove-row', function() { $(this).closest('tr').remove(); });

            function addNewRow() {
                var row = `
                    <tr class="product_row">
                        <td>
                            <select name="product_id[]" class="form-control product-select" required style="width:100%">
                                <option value="">Select Product</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="available_stock[]" class="form-control stock" readonly>
                        </td>
                        <td>
                            <input type="number" name="quantity[]" class="form-control quantity" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger remove-row">Remove</button>
                        </td>
                    </tr>
                `;
                $('#product_body').append(row);
                initProductSelect2('#product_body tr:last .product-select', '/search-products-sale', '/search_products');
            }
        });
    </script>
@endsection
