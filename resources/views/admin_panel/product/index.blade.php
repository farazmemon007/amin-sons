@extends('admin_panel.layout.app')

@section('content')
@can('product.view')

<style>
    div.dataTables_wrapper div.dataTables_length select { width: 75px !important; }

    /* ===== Product View Modal Styles ===== */
    .pvm-label {
        font-size: 11px; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.5px;
        color: #6c757d; margin-bottom: 2px;
    }
    .pvm-value { font-size: 14px; font-weight: 600; color: #212529; }
    #productViewModal .standard-field,
    #productViewModal .customize-field { display: none; }
    #productViewModal .standard-field.d-show,
    #productViewModal .customize-field.d-show { display: block; }
    .color-badge {
        display: inline-block; padding: 3px 10px;
        background: #e9ecef; border-radius: 20px;
        font-size: 12px; font-weight: 500; margin: 2px;
    }
    .custom-dropdown { border-radius: 10px; padding: 6px; min-width: 190px; }
    .custom-dropdown .dropdown-item {
        border-radius: 6px; padding: 8px 12px;
        font-weight: 500; transition: all 0.2s;
    }
    .custom-dropdown .dropdown-item:hover {
        background: #f1f3f5; transform: translateX(3px);
    }
</style>

@php $isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false; @endphp

{{-- ==================== PRODUCT TABLE ==================== --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">📦 Product List</h5>
            <small class="text-muted">Manage all products here</small>
        </div>
        <a href="{{ url('create_prodcut') }}" class="btn btn-primary">Add Product</a>
    </div>
    <div class="card-body">
        @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                ✅ {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div class="table-responsive">
            <table id="productTable" class="table table-striped table-bordered align-middle nowrap" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>#</th>
                        <th>Item Code</th>
                        @if($isSuperAdmin)
                            <th>Branch</th>
                            <th>Stock Status</th>
                        @else
                            <th>Status</th>
                        @endif
                        <th>Image</th>
                        <th>Category / Sub</th>
                        <th>Item Name</th>
                        <th>Model</th>
                        <th>Price</th>
                        @if($isSuperAdmin)
                            <th>Stock By Branch</th>
                        @else
                            <th>Stock</th>
                        @endif
                        <th>Alert Qty</th>
                        <th>Brand</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $key => $product)
                    <tr @if(!$isSuperAdmin && $product->is_secondary) style="background-color:#fff3cd;" @endif>
                        <td><input type="checkbox" class="selectProduct" value="{{ $product->id }}"></td>
                        <td>{{ $key + 1 }}</td>
                        <td class="fw-bold">
                            @if($isSuperAdmin)
                                {{ $product->item_code }}
                            @else
                                {{ $product->branch_item_code ?? $product->item_code }}
                            @endif
                        </td>

                        @if($isSuperAdmin)
                            <td><span class="badge bg-primary">{{ $product->branch->name ?? 'Unknown' }}</span></td>
                            <td>
                                @if($product->all_warehouse_stocks && count($product->all_warehouse_stocks) > 0)
                                    <span class="badge bg-success">✓ In Stock</span>
                                @else
                                    <span class="badge bg-warning text-dark">◯ No Stock</span>
                                @endif
                            </td>
                        @else
                            <td>
                                @if($product->is_primary)
                                    <span class="badge bg-success">✓ PRIMARY</span>
                                @else
                                    <span class="badge bg-warning text-dark">◯ SECONDARY</span>
                                @endif
                            </td>
                        @endif

                        <td>
                            @if($product->image)
                                <img src="{{ asset('uploads/products/' . $product->image) }}" width="48" height="48" class="rounded border">
                            @else
                                <span class="badge bg-secondary">No Img</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $product->category_relation->name ?? '-' }}</strong><br>
                            <small class="text-muted">{{ $product->sub_category_relation->name ?? '-' }}</small>
                        </td>
                        <td>{{ $product->item_name }}</td>
                        <td>{{ $product->model ?? '-' }}</td>
                        <td>PKR {{ number_format($product->price) }}</td>

                        @if($isSuperAdmin)
                            <td>
                                @if($product->all_warehouse_stocks && count($product->all_warehouse_stocks) > 0)
                                    @foreach($product->all_warehouse_stocks as $stock)
                                        <div class="mb-2" style="font-size:18px;">
                                            <span class="fw-bold text-dark">{{ $stock['branch_name'] }}:</span> 
                                            <span class="badge bg-info text-dark shadow-sm" style="font-size:18px; padding: 8px 12px; font-weight: bold;">{{ $stock['quantity'] }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="badge bg-danger">📭 No Stock</span>
                                @endif
                            </td>
                        @else
                            <td>
                                @if($product->branch_stock_qty > 0)
                                    <span class="badge bg-success">📦 {{ $product->branch_stock_qty }}</span>
                                @else
                                    <span class="badge bg-danger">📭 Out</span>
                                @endif
                            </td>
                        @endif

                        <td>{{ $product->alert_quantity }}</td>
                        <td>{{ $product->brand->name ?? '-' }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-warning viewProductBtn" data-id="{{ $product->id }}">
                                👁 View
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">More</button>
                                <ul class="dropdown-menu dropdown-menu-end shadow custom-dropdown">
                                    @if(auth()->user()->can('product.edit') || auth()->user()->can('edit product') || auth()->user()->hasAnyRole(['super admin', 'admin']))
                                        <li><a class="dropdown-item" href="{{ route('products.edit', $product->id) }}">📋 Edit Profile</a></li>
                                        <li><a class="dropdown-item" href="{{ route('opening.stocks.edit', $product->id) }}">💰 Edit Stock & Pricing</a></li>
                                    @endif
                                    <li><a class="dropdown-item" href="{{ route('generate-barcode-image', $product->id) }}">🏷 Generate Barcode</a></li>
                                    @if($product->is_assembled)
                                        <li><a class="dropdown-item" href="{{ route('assembly.report.show', $product->id) }}">⚙ Assembly Report</a></li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ==================== PRODUCT VIEW MODAL (ERP Standard) ==================== --}}
<div class="modal fade" id="productViewModal" tabindex="-1" role="dialog" aria-labelledby="pvmLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg">

            {{-- HEADER --}}
            <div class="modal-header text-white border-0 px-4 py-3" style="background: linear-gradient(135deg, #1a73e8, #0d47a1);">
                <div class="d-flex align-items-center" style="gap:12px;">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;flex-shrink:0;">
                        <span style="font-size:18px;">📦</span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="pvmLabel">Product Details</h5>
                        <small class="opacity-75" id="pvm_header_sub">Loading...</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-0 bg-light">
                <div class="row no-gutters">

                    {{-- LEFT: Image + Stock + Price --}}
                    <div class="col-md-3 bg-white border-right p-3 d-flex flex-column" style="gap:12px;">

                        {{-- Image --}}
                        <div class="border rounded d-flex align-items-center justify-content-center bg-light overflow-hidden" style="height:200px;">
                            <img id="pvm_image" src="" alt="Product" class="img-fluid" style="max-height:100%;object-fit:contain;display:none;">
                            <div id="pvm_no_image" class="text-center text-muted">
                                <div style="font-size:3rem;">🖼️</div>
                                <small>No Image</small>
                            </div>
                        </div>

                        {{-- Stock --}}
                        <div class="p-3 rounded border" style="background:#e8f0fe;">
                            <div class="pvm-label text-primary">Current Stock</div>
                            <div class="d-flex align-items-baseline mt-1" style="gap:6px;">
                                <span class="fw-bolder text-primary" style="font-size:2rem;" id="pvm_stock">0</span>
                                <span class="text-muted" id="pvm_unit_label">pcs</span>
                            </div>
                            <div id="pvm_low_stock_alert" class="mt-2 p-2 rounded small fw-semibold text-danger" style="background:rgba(220,53,69,0.1);display:none;">
                                ⚠️ Low Stock! Min: <span id="pvm_alert_qty">0</span>
                            </div>
                        </div>

                        {{-- Pricing --}}
                        <div class="p-3 rounded border bg-white">
                            <div class="pvm-label mb-2">Pricing</div>
                            <div class="d-flex justify-content-between pb-2 mb-2" style="border-bottom:1px solid #eee;">
                                <small class="text-muted">Wholesale:</small>
                                <span class="fw-bold" id="pvm_wholesale">PKR 0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Retail:</small>
                                <span class="fw-bolder text-success" style="font-size:1.1rem;" id="pvm_retail">PKR 0</span>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: All Details --}}
                    <div class="col-md-9 p-4">

                        <h4 class="fw-bold text-dark pb-3 mb-4" id="pvm_item_name" style="border-bottom:2px solid #e9ecef;">-</h4>

                        {{-- Classification --}}
                        <div class="mb-4">
                            <h6 class="fw-bold text-primary mb-3">🏷️ Classification</h6>
                            <div class="row bg-white rounded border p-3 mx-0" style="row-gap:16px;">
                                <div class="col-6 col-md-3">
                                    <div class="pvm-label">Item Code</div>
                                    <div class="pvm-value" id="pvm_item_code">-</div>
                                </div>
                                @if(auth()->user() && auth()->user()->hasRole('super admin'))
                                <div class="col-6 col-md-3">
                                    <div class="pvm-label">Branch</div>
                                    <div class="pvm-value" id="pvm_branch">-</div>
                                </div>
                                @endif
                                <div class="col-6 col-md-3">
                                    <div class="pvm-label">Category</div>
                                    <div class="pvm-value" id="pvm_category">-</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="pvm-label">Sub Category</div>
                                    <div class="pvm-value" id="pvm_subcategory">-</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="pvm-label">Brand</div>
                                    <div class="pvm-value" id="pvm_brand">-</div>
                                </div>
                            </div>
                        </div>

                        {{-- Identification --}}
                        <div class="mb-4">
                            <h6 class="fw-bold text-primary mb-3">🔍 Identification</h6>
                            <div class="row bg-white rounded border p-3 mx-0" style="row-gap:16px;">
                                <div class="col-6 col-md-3">
                                    <div class="pvm-label">Barcode</div>
                                    <div class="pvm-value" style="font-size:12px;font-family:monospace;" id="pvm_barcode">-</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="pvm-label">Model</div>
                                    <div class="pvm-value" id="pvm_model">-</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="pvm-label">HS Code</div>
                                    <div class="pvm-value" id="pvm_hs_code">-</div>
                                </div>
                                <div class="col-12">
                                    <div class="pvm-label mb-1">Colors</div>
                                    <div id="pvm_color">-</div>
                                </div>
                            </div>
                        </div>

                        {{-- Packaging --}}
                        <div class="mb-2">
                            <h6 class="fw-bold text-primary mb-3">📦 Packaging</h6>
                            <div class="row bg-white rounded border p-3 mx-0" style="row-gap:16px;">
                                <div class="col-6 col-md-3">
                                    <div class="pvm-label">Pack Type</div>
                                    <div class="pvm-value"><span class="badge badge-info" id="pvm_pack_type">-</span></div>
                                </div>
                                <div class="col-6 col-md-3 standard-field">
                                    <div class="pvm-label">Base Unit</div>
                                    <div class="pvm-value" id="pvm_unit">-</div>
                                </div>
                                <div class="col-6 col-md-3 customize-field">
                                    <div class="pvm-label">Pack Qty</div>
                                    <div class="pvm-value" id="pvm_pack_qty">-</div>
                                </div>
                                <div class="col-6 col-md-3 customize-field">
                                    <div class="pvm-label">Pcs per Pack</div>
                                    <div class="pvm-value" id="pvm_piece_per_pack">-</div>
                                </div>
                                <div class="col-6 col-md-3 customize-field">
                                    <div class="pvm-label">Loose Pcs</div>
                                    <div class="pvm-value" id="pvm_loose_piece">-</div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- end col-md-9 --}}
                </div>{{-- end row --}}
            </div>{{-- end modal-body --}}

            {{-- FOOTER --}}
            <div class="modal-footer bg-white py-2 px-4">
                <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">Close</button>
                @if(auth()->user()->can('product.edit') || auth()->user()->can('edit product') || auth()->user()->hasAnyRole(['super admin', 'admin']))
                    <a href="#" id="pvm_edit_btn" class="btn btn-primary px-4">✏ Edit Product</a>
                @endif
            </div>

        </div>{{-- end modal-content --}}
    </div>{{-- end modal-dialog --}}
</div>{{-- end modal --}}

<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

@else
    <div class="container py-4">
        <div class="alert alert-danger">You do not have permission to view Products.</div>
    </div>
@endcan
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

    // ===== DataTable =====
    if ($.fn.DataTable) {
        $('#productTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            language: { search: "_INPUT_", searchPlaceholder: "Search products..." }
        });
    }

    // ===== Select All =====
    $('#selectAll').on('click', function () {
        $('.selectProduct').prop('checked', this.checked);
    });

});

// ===== Product View Modal (Bootstrap 4 compatible) =====
$(document).on('click', '.viewProductBtn', function () {
    var productId = $(this).data('id');

    // Reset modal state
    $('#pvm_header_sub').text('Loading...');
    $('#pvm_item_name').text('...');
    $('#pvm_image').hide();
    $('#pvm_no_image').show();
    $('#pvm_color').html('-');
    $('#pvm_low_stock_alert').hide();
    $('#productViewModal .standard-field').removeClass('d-show');
    $('#productViewModal .customize-field').removeClass('d-show');

    // ✅ Bootstrap 4: Use data-dismiss="modal" and jQuery .modal()
    $('#productViewModal').modal('show');

    $.ajax({
        url: '{{ url("productview") }}/' + productId,
        type: 'GET',
        success: function (p) {

            // Header
            $('#pvm_header_sub').text((p.item_code || '-') + ' | ' + (p.brand ? p.brand.name : 'No Brand'));
            $('#pvm_item_name').text(p.item_name || '-');

            // Stock
            var stock    = parseFloat(p.stock ? p.stock.qty : 0);
            var alertQty = parseFloat(p.alert_quantity || 0);
            $('#pvm_stock').text(stock);
            $('#pvm_alert_qty').text(alertQty);
            $('#pvm_unit_label').text(p.unit ? p.unit.name : 'pcs');
            if (alertQty > 0 && stock <= alertQty) { $('#pvm_low_stock_alert').show(); }

            // Pricing
            $('#pvm_wholesale').text('PKR ' + parseFloat(p.wholesale_price || 0).toLocaleString());
            $('#pvm_retail').text('PKR ' + parseFloat(p.price || 0).toLocaleString());

            // Image
            if (p.image) {
                $('#pvm_image').attr('src', '{{ asset("uploads/products") }}/' + p.image).show();
                $('#pvm_no_image').hide();
            }

            // Classification
            $('#pvm_item_code').text(p.item_code || '-');
            $('#pvm_branch').text(p.branch ? (p.branch.name + ' (ID:' + p.branch_id + ')') : '-');
            $('#pvm_category').text(p.category_relation ? p.category_relation.name : '-');
            $('#pvm_subcategory').text(p.sub_category_relation ? p.sub_category_relation.name : '-');
            $('#pvm_brand').text(p.brand ? p.brand.name : '-');

            // Identification
            $('#pvm_barcode').text(p.barcode_path || '-');
            $('#pvm_model').text(p.model || '-');
            $('#pvm_hs_code').text(p.hs_code || '-');

            // Colors (safe parse)
            if (p.color) {
                try {
                    var cols = (typeof p.color === 'string') ? JSON.parse(p.color) : p.color;
                    var html = Array.isArray(cols)
                        ? cols.map(function(c){ return '<span class="color-badge">'+c+'</span>'; }).join('')
                        : '<span class="color-badge">'+p.color+'</span>';
                    $('#pvm_color').html(html);
                } catch(e) { $('#pvm_color').text(p.color); }
            } else {
                $('#pvm_color').text('-');
            }

            // Packaging
            $('#pvm_pack_type').text(p.pack_type || '-');
            $('#pvm_pack_qty').text(p.pack_qty || '-');
            $('#pvm_piece_per_pack').text(p.piece_per_pack || '-');
            $('#pvm_loose_piece').text(p.loose_piece || '-');

            if (p.pack_type === 'Standard') {
                $('#productViewModal .standard-field').addClass('d-show');
                $('#pvm_unit').text('Piece');
            } else if (p.pack_type === 'Customize') {
                $('#productViewModal .customize-field').addClass('d-show');
                $('#pvm_unit').text(p.unit ? p.unit.name : '-');
            } else {
                $('#pvm_unit').text(p.unit ? p.unit.name : '-');
            }

            // Edit link — opens the Opening Stock Edit page for this product
            $('#pvm_edit_btn').attr('href', '{{ url("opening-stocks") }}/' + p.id + '/edit');
        },
        error: function (xhr) {
            $('#productViewModal').modal('hide');
            Swal.fire('Error', 'Could not load product. (Status: ' + xhr.status + ')', 'error');
        }
    });
});
</script>
@endsection
