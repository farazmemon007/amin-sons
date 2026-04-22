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
                <label>Branch</label>
                @if(auth()->user()->hasRole('super admin'))
                    <select name="branch_id" id="branch_select" class="form-control mb-2">
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name ?? 'Branch '.$branch->id }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                    <div class="form-control mb-2">{{ $branches->firstWhere('id', auth()->user()->branch_id)?->name ?? 'Branch '.auth()->user()->branch_id }}</div>
                @endif

                <label>Warehouse</label>
                <select name="warehouse_id" id="warehouse_select" class="form-control">
                    <option value="">Select Warehouse</option>
                    @foreach($warehouses as $warehouse)
                        @php $bids = $warehouse->branches->pluck('id')->toArray(); @endphp
                        <option value="{{ $warehouse->id }}" data-branches="{{ implode(',', $bids) }}">{{ $warehouse->warehouse_name }}</option>
                    @endforeach
                </select>
                @if(!auth()->user()->hasRole('super admin'))
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" value="1" id="branch_only" name="branch_only">
                        <label class="form-check-label" for="branch_only">Add to branch only (no warehouse)</label>
                    </div>
                @endif
            </div>
            <div class="mb-3">
                <label>Product</label>
                <select name="product_id" id="product_id" class="form-control product-select" required style="width:100%">
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        @php $rem = $remainingByProduct[$product->id] ?? null; @endphp
                        <option value="{{ $product->id }}" data-remaining="{{ $rem ?? 0 }}">{{ $product->item_name }}{{ $rem !== null ? ' (Remaining: '.$rem.')' : '' }}</option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1">
                    <strong>Available Stock: <span id="available_stock">0</span> units</strong>
                </small>
            </div>
            <div class="mb-3">
                <label>Quantity to Allocate</label>
                <input type="number" name="quantity" id="quantity_input" class="form-control" required min="1">
                <small id="quantity_warning" class="text-danger d-none mt-1">
                    <strong>⚠️ Quantity exceeds available stock!</strong>
                </small>
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
            // ✅ ERP STOCK VALIDATION: Store remaining stock data from blade
            const remainingByProduct = {!! json_encode($remainingByProduct) !!};

            initProductSelect2('.product-select', '/search-products-sale', '/search_products');
            
            // ✅ Update available stock display when product is selected
            $('#product_id').on('change', function() {
                const productId = $(this).val();
                const availableQty = remainingByProduct[productId] || 0;
                $('#available_stock').text(availableQty);
                
                // Clear quantity input and warning
                $('#quantity_input').val('').removeClass('is-invalid');
                $('#quantity_warning').addClass('d-none');
                
                console.log('Product selected:', productId, 'Available:', availableQty);
            });

            // ✅ Validate quantity on input change
            $('#quantity_input').on('input change', function() {
                const productId = $('#product_id').val();
                const enteredQty = parseInt($(this).val()) || 0;
                const availableQty = remainingByProduct[productId] || 0;

                if (enteredQty > availableQty && availableQty > 0) {
                    $(this).addClass('is-invalid');
                    $('#quantity_warning').removeClass('d-none');
                } else {
                    $(this).removeClass('is-invalid');
                    $('#quantity_warning').addClass('d-none');
                }
            });

            // ✅ Prevent form submission if quantity exceeds available stock
            $('form').on('submit', function(e) {
                const productId = $('#product_id').val();
                const enteredQty = parseInt($('#quantity_input').val()) || 0;
                const availableQty = remainingByProduct[productId] || 0;

                if (!productId) {
                    e.preventDefault();
                    alert('Please select a product');
                    return false;
                }

                if (enteredQty <= 0) {
                    e.preventDefault();
                    alert('Quantity must be greater than 0');
                    return false;
                }

                if (enteredQty > availableQty) {
                    e.preventDefault();
                    alert(`Cannot allocate ${enteredQty} units.\n\nOnly ${availableQty} units available for this product.\n\nPlease enter a quantity equal to or less than ${availableQty}.`);
                    return false;
                }
            });
            
            // Function to filter warehouses by branch
            function filterWarehousesByBranch() {
                var bid = $('#branch_select').val();
                console.log('Filtering warehouses for branch:', bid);
                
                $('#warehouse_select option').each(function () {
                    var bids = $(this).data('branches') ? String($(this).data('branches')).split(',') : [];
                    if (!bid) {
                        $(this).show();
                        return;
                    }
                    if (bids.indexOf(String(bid)) !== -1 || $(this).val() === '') {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                
                // Clear selection if current warehouse is hidden
                if ($('#warehouse_select option:selected').is(':hidden')) {
                    $('#warehouse_select').val('');
                }
            }
            
            // Filter on branch change
            $('#branch_select').on('change', function () {
                filterWarehousesByBranch();
            });
            
            // Filter on initial page load
            filterWarehousesByBranch();
        });
    </script>
@endsection
