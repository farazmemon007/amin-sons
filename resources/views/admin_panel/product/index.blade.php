
@extends('admin_panel.layout.app')
@section('content')
@can('product.view')
    <style>
        div.dataTables_wrapper div.dataTables_length select {
    width: 75px !important
}
    </style>


<style>

/* Product View Modal Fields */
/* ===== Professional Product View Modal ===== */

#productViewModal {
    --label-size: 13px;
    --value-size: 16px;
    --heading-size: 17px;
}

/* Section headings */
#productViewModal h6 {
    font-size: var(--heading-size);
    font-weight: 600;
    margin-bottom: 14px;
}

/* Labels */
#productViewModal .label {
    display: block;
    font-size: var(--label-size);
    font-weight: 500;
    color: #6c757d;
    margin-bottom: 4px;
    text-transform: uppercase;
}

/* Values */
#productViewModal .value {
    display: block;
    font-size: var(--value-size);
    font-weight: 600;
    color: #212529;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 10px 12px;
    min-height: 42px;
}

/* Right panel emphasis */
#productViewModal #view_wholesale,
#productViewModal #view_retail {
    font-size: 17px;
}

/* Stock visibility */
#productViewModal #view_stock {
    font-size: 17px;
    font-weight: 700;
}

/* Card padding balance */
#productViewModal .card-body {
    padding: 18px;
}

/* Modal overall text comfort */
#productViewModal .modal-body {
    font-size: 15px;
}

/* custom drop down button */
.custom-dropdown {
    border-radius: 10px;
    padding: 6px;
    min-width: 190px;
}

.custom-dropdown .dropdown-item {
    border-radius: 6px;
    padding: 8px 12px;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
}

.custom-dropdown .dropdown-item:hover {
    background: #f1f3f5;
    transform: translateX(3px);
}


</style>


<div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">📦 Product List</h5>
            <small class="text-muted">Manage all products here</small>
        </div>
        
        <div class="d-flex justify-content-between align-items-end gap-1" >
            
         
            <a href="{{ route('discount.index') }}" class="btn btn-success btn-sm">
                View Discount
            </a>
          
            
          
                <a href="{{ url('create_prodcut') }}" class="btn btn-primary">
                    Add Product
                </a>
            
                
            <button id="createDiscountBtn" class="btn btn-success btn-sm">
                ➡ Create Discount
            </button>
           
        </div>
    </div>
    <div class="card-body">
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show">
                ✅ {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <div class="table-responsive">
            <table id="productTable" class="table table-striped table-bordered align-middle nowrap" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>#</th>
                        <th>Item Code</th>
                        <th>Image</th>
                        <th>Category<br>Sub-Category</th>
                        <th>Item Name</th>
                        <th>Model</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Alert Qty</th>
                        <th class="text-center">Brand Name</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $key => $product)
                    <tr>
                        <td><input type="checkbox" class="selectProduct" value="{{ $product->id }}"></td>
                        <td>{{ $key + 1 }}</td>
                        <td class="fw-bold">{{ $product->item_code }}</td>
                        <td>
                            @if($product->image)
                                <img src="{{ asset('uploads/products/' . $product->image) }}"
                                     alt="Product" width="50" height="50"
                                     class="rounded border">
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
                        <td>{{ $product->stock->qty ?? '- ' }}</td>
                        <td>{{ $product->alert_quantity }}</td>
                        <td>{{ $product->brand->name ?? '-' }}</td>
                        <td class="text-center">
                            <!-- VIEW BUTTON -->
                            <button 
                                type="button"
                                class="btn btn-sm btn-warning viewProductBtn"
                                data-id="{{ $product->id }}">
                                👁 View
                            </button>
                            <!-- MORE OPTIONS DROPDOWN -->
                            
                                
                            <div class="btn-group">
                                <button 
                                type="button" 
                                class="btn btn-sm btn-secondary dropdown-toggle"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                                More
                            </button>
                           
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg custom-dropdown">
                                    @if(auth()->user()->can('Edit Product') || auth()->user()->hasAnyRole(['super admin', 'admin']))
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2 btn btn btn-warning"
                                               href="{{ route('products.edit', $product->id) }}">
                                                ✏ Edit Product
                                            </a>
                                        </li>
                                    @endif
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2"
                                           href="{{ route('generate-barcode-image', $product->id) }}">
                                            🏷 Generate Barcode
                                        </a>
                                    </li>
                                    @if($product->is_assembled)
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2"
                                               href="{{ route('assembly.report.show', $product->id) }}">
                                                ⚙ Assembly Report
                                            </a>
                                        </li>
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

{{-- add product modal --}}

<div class="modal fade bd-example-modal-lg" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('store-product') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-control" name="category_id" id="categorySelect" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sub-Category</label>
                            <select class="form-control" name="sub_category_id" id="subCategorySelect" >
                                <option value="">Select Sub-Category</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" name="item_name" required>
                        </div>
                    </div>

                    {{-- <div class="row"> --}}
                        {{-- <div class="col-md-6 mb-3">
                            <label class="form-label">Size</label> --}}
                            {{-- <select class="form-control" name="size" id="sizeSelect" required>
                                <option value="">Select Size</option>

                            </select> --}}
                        {{-- </div> --}}
                        {{-- <div class="col-md-6 mb-3">
                            <label class="form-label">Carton Quantity</label>
                            <input type="number" class="form-control" name="carton_quantity" id="carton_quantity" required>
                        </div> --}}
                    {{-- </div> --}}
                    {{-- <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pieces per Carton</label>
                            <input type="number" class="form-control" name="pcs_in_carton" id="pieces_per_carton" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Initial Stock</label>
                            <input type="number" class="form-control" name="initial_stock" id="initial_stock">
                        </div>
                    </div> --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Alert Quantity</label>
                            <input type="number" class="form-control" name="alert_quantity" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" name="wholesale_price" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sale Price</label>
                            <input type="number" step="0.01" class="form-control" name="retail_price" required>
                        </div>
                    </div>


                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Product Detail View Modal -->
<div class="modal fade" id="productViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-3">

            <!-- Header -->
            <div class="modal-header bg-white border-bottom">
                <div>
                    <h5 class="modal-title fw-semibold mb-0">
                        <i class="fa-solid fa-cube text-primary me-1"></i> Product Details
                    </h5>
                    <small class="text-muted">Complete product information</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body py-4">
                <div class="row g-4">

                    <!-- LEFT -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">

                                <h6 class="fw-semibold text-primary mb-3">
                                    <i class="fa-solid fa-info-circle me-1"></i> Basic Information
                                </h6>

                                <div class="row g-3">

                                    <div class="col-sm-4">
                                        <span class="label">Item Description</span>
                                        <span class="value" id="view_item_name"></span>
                                    </div>

                                    <div class="col-sm-4">
                                        <span class="label">Category</span>
                                        <span class="value" id="view_category"></span>
                                    </div>

                                    <div class="col-sm-4">
                                        <span class="label">Sub Category</span>
                                        <span class="value" id="view_subcategory"></span>
                                    </div>

                                    <div class="col-sm-4">
                                        <span class="label">Brand</span>
                                        <span class="value" id="view_brand"></span>
                                    </div>

                                    <div class="col-sm-4">
                                        <span class="label">Barcode</span>
                                        <span class="value" id="view_barcode"></span>
                                    </div>

                                    <div class="col-sm-4">
                                        <span class="label">Model</span>
                                        <span class="value" id="view_model"></span>
                                    </div>

                                    <div class="col-sm-4">
                                        <span class="label">HS Code</span>
                                        <span class="value" id="view_hs_code"></span>
                                    </div>

                                    <div class="col-sm-4">
                                        <span class="label">Color</span>
                                        <span class="value" id="view_color"></span>
                                    </div>

                                    <div class="col-sm-4">
                                        <span class="label">Packaging Type</span>
                                        <span class="value" id="view_pack_type"></span>
                                    </div>

                                    <div class="col-sm-4">
                                        <span class="label">Packaging Quantity</span>
                                        <span class="value" id="view_pack_qty"></span>
                                    </div>

                                    <div class="col-sm-4">
                                        <span class="label">Unit per Packing</span>
                                        <span class="value" id="view_piece_per_pack"></span>
                                    </div>

                                    <div class="col-sm-4">
                                        <span class="label">Loose Piece</span>
                                        <span class="value" id="view_loose_piece"></span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">

                                <h6 class="fw-semibold text-success mb-3">
                                    <i class="fa-solid fa-chart-column me-1"></i> Stock & Pricing
                                </h6>

                                <div class="mb-3">
                                    <span class="label">Opening Stock</span>
                                    <span class="value text-primary" id="view_stock"></span>
                                </div>

                                <div class="mb-3">
                                    <span class="label">Alert Quantity</span>
                                    <span class="value text-danger" id="view_alert_qty"></span>
                                </div>

                                <hr>

                                <div class="mb-3">
                                    <span class="label">Wholesale Price</span>
                                    <span class="value" id="view_wholesale"></span>
                                </div>

                                <div>
                                    <span class="label">Retail Price</span>
                                    <span class="value fw-bold" id="view_retail"></span>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer bg-white border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm px-4"
                    data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>









<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- product model --}}
<script>

$(document).on('click', '.viewProductBtn', function () {

    let productId = $(this).data('id');

    $.ajax({
        url: "/productview/" + productId,
        type: "GET",
        success: function (product) {

            $('#view_item_name').text(product.item_name ?? '-');
            $('#view_item_code').text(product.item_code ?? '-');

            $('#view_category').text(product.category_relation?.name ?? '-');
            $('#view_subcategory').text(product.sub_category_relation?.name ?? '-');
            $('#view_brand').text(product.brand?.name ?? '-');

            $('#view_barcode').text(product.barcode_path ?? '-');
            $('#view_model').text(product.model ?? '-');
            $('#view_hs_code').text(product.hs_code ?? '-');

            $('#view_pack_type').text(product.pack_type ?? '-');
            $('#view_pack_qty').text(product.pack_qty ?? '-');
            $('#view_piece_per_pack').text(product.piece_per_pack ?? '-');
            $('#view_loose_piece').text(product.loose_piece ?? '-');

            $('#view_stock').text(product.stock?.qty ?? 0);
            $('#view_alert_qty').text(product.alert_quantity ?? 0);

            $('#view_wholesale').text('PKR ' + (product.wholesale_price ?? 0));
            $('#view_retail').text('PKR ' + (product.price ?? 0));

            // COLOR (JSON decode)
            if (product.color) {
                let colors = JSON.parse(product.color);
                $('#view_color').text(colors.join(', '));
            } else {
                $('#view_color').text('-');
            }

            // IMAGE (optional)
            if (product.image) {
                $('#view_image').attr('src', '/uploads/products/' + product.image);
            }

            $('#productViewModal').modal('show');
        }
    });
});

</script>


<script>
$(document).ready(function() {

    // Select/Deselect all checkboxes
    $('#selectAll').click(function() {
        $('.selectProduct').prop('checked', this.checked);
    });

    // On "Create Discount" click
    $('#createDiscountBtn').click(function() {
        var selected = [];
        $('.selectProduct:checked').each(function() {
            selected.push($(this).val());
        });

        if(selected.length === 0){
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Please select at least one product!",

            });
            return;
        }

        // Redirect with product IDs as query param
        window.location.href = "{{ route('discount.create') }}" + "?products=" + selected.join(',');
    });
});
</script>

<script>
$(document).ready(function() {
    $('#productTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        order: [[0, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search products..."
        }
    });
});
</script>

<!-- DataTables CSS -->

@else
    <div class="container py-4">
        <div class="alert alert-danger">You do not have permission to view Products.</div>
    </div>
@endcan
@endsection
<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let cartonQuantityInput = document.getElementById("carton_quantity");
        let piecesPerCartonInput = document.getElementById("pieces_per_carton");
        let initialStockInput = document.getElementById("initial_stock");

        function updateInitialStock() {
            let cartonQuantity = parseInt(cartonQuantityInput.value) || 0;
            let piecesPerCarton = parseInt(piecesPerCartonInput.value) || 0;
            initialStockInput.value = cartonQuantity * piecesPerCarton;
        }

        cartonQuantityInput.addEventListener("input", updateInitialStock);
        piecesPerCartonInput.addEventListener("input", updateInitialStock);
    });

 $(document).ready(function() {
        // Add Product Modal: Fetch Subcategories on Category Change
        $('#categorySelect').change(function() {
            var categoryId = $(this).val();

            $('#subCategorySelect').html('<option value="">Loading...</option>');

            if (categoryId) {
                $.ajax({
                   url: "/get-subcategories/" + categoryId,

                    type: "GET",
                    data: {
                        category_id: categoryId
                    },
                    success: function(data) {
                        $('#subCategorySelect').html('<option value="">Select Sub-Category</option>');
                        $.each(data, function(key, subCategory) {
                            $('#subCategorySelect').append('<option value="' + subCategory.id + '">' + subCategory.name + '</option>');
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                $('#subCategorySelect').html('<option value="">Select Sub-Category</option>');
            }
        });

        // Edit Product Modal: Fetch Subcategories when Category is Changed
        $('#edit_category').change(function() {
            var categoryId = $(this).val();
            $('#edit_sub_category').html('<option value="">Loading...</option>');

            if (categoryId) {
                $.ajax({
                   url: "/get-subcategories/" + categoryId,

                    type: "GET",
                    data: {
                        category_id: categoryId
                    },
                    success: function(data) {
                        $('#edit_sub_category').html('<option value="">Select Sub-Category</option>');
                        $.each(data, function(key, subCategory) {
                            $('#edit_sub_category').append('<option value="' + subCategory.sub_category_name + '">' + subCategory.sub_category_name + '</option>');
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                $('#edit_sub_category').html('<option value="">Select Sub-Category</option>');
            }
        });
    });
</script>
