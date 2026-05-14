@extends('admin_panel.layout.app')

@section('content')
@section('css')
@endsection

    <style>
        .image-preview-wrapper {
            position: relative;
            display: inline-block
        }

        .image-preview-wrapper img {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .06)
        }

        .clear-image-btn {
            position: absolute;
            top: 8px;
            right: 10px;
            width: 30px;
            height: 30px;
            background: rgba(0, 0, 0, .6);
            color: #fff;
            border: none;
            border-radius: 50%;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: .2s
        }

        .clear-image-btn:hover {
            background: rgba(220, 53, 69, .9)
        }

        #preview {
            width: 395px;
            height: 325px;
            border: 2px dashed #d9dfe7;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #f8fafc
        }

        #preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block
        }

        .page-title {
            font-weight: 700;
            letter-spacing: .3px
        }

        .btn-outline--primary {
            border-color: #3b82f6;
            color: #3b82f6
        }

        .btn-outline--primary:hover {
            background: #3b82f6;
            border-color: #3b82f6;
            color: #fff
        }

        .card {
            border-radius: 14px;
            border: 1px solid #eef1f5
        }

        .form-label {
            font-weight: 600
        }

        .select2-container--default .select2-selection--multiple {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            min-height: 38px;
            max-height: 38px;
            white-space: nowrap;
            scrollbar-width: thin
        }

        .select2-selection__choice {
            white-space: nowrap;
            margin-right: 4px;
            font-size: 11px;
            padding: 2px 6px
        }

        .badge-note {
            font-size: .75rem
        }

        .small-help {
            font-size: .8rem;
            color: #6b7280
        }

        .modal-wide {
            max-width: 1100px
        }

        .bom-table thead th,
        .bom-table tbody td {
            vertical-align: middle
        }

        .bom-table input[readonly] {
            background: #f8fafc
        }

        .toolbar-gap>* {
            margin-right: .4rem
        }
    </style>
    <style>
        .add-btn {
            width: 40px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;

            border-left: 0;
        }

        .add-btn i {
            font-size: 13px;
            transition: transform 0.2s ease;
        }

        /* Hover polish */
        .add-btn:hover i {
            transform: scale(1.15);
        }

        /* Focus consistency */
        .category-group .form-select:focus {
            box-shadow: none;
        }

        .add-btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid">
                <div class="body-wrapper">
                    <div class="bodywrapper__inner">
                        {{-- <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <h6 class="page-title mb-0">Add Product</h6> --}}

                        {{-- <div class="d-flex justify-content-center flex-wrap gap-2 flex-grow-1 toolbar-gap">
              <button type="button" class="btn btn-sm btn-outline--primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="la la-plus-circle"></i> Add Category
              </button>
              <button type="button" class="btn btn-sm btn-outline--primary" data-bs-toggle="modal" data-bs-target="#subcategoryModal">
                <i class="las la-plus"></i> Add Subcategory
              </button>
              <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="Add New Model" data-bs-toggle="modal" data-bs-target="#modelModal">
                <i class="las la-plus"></i> Add Models
              </button>
              <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn" data-modal_title="Add New Brand" data-bs-toggle="modal" data-bs-target="#cuModal">
                <i class="las la-plus"></i> Add Brand
              </button>
              <a class="btn btn-sm btn-outline--primary" href="{{ url('/home') }}">
                <i class="la la-tachometer-alt"></i> Go To Dashboard
              </a>
            </div> --}}
                        @if (session('swal_error'))
                            <script>
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: "{{ session('swal_error') }}"
                                });
                            </script>
                        @elseif(session('catagory_swal_error'))
                            <script>
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: "{{ session('catagory_swal_error') }}"
                                });
                            </script>
                        @elseif(session('subcatagory_swal_error'))
                            <script>
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: "{{ session('catagory_swal_error') }}"
                                });
                            </script>
                        @endif









                        {{-- ////////////////////////////////////// --}}

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="page-title mb-0">📋 Edit Product Profile</h5>
                            <a href="{{ route('product') }}" class="btn btn-sm btn-outline--primary">
                                <i class="la la-undo"></i> Back to List
                            </a>
                        </div>
                    </div>

                    <div class="row mb-none-30">
                        <div class="col-lg-12 col-md-12 mb-30">
                            <div class="card">
                                <div class="card-body">
                                    @if (session()->has('success'))
                                        <div class="alert alert-success">
                                            <strong>Success!</strong> {{ session('success') }}.
                                        </div>
                                    @endif

                                    <form id="productForm" action="{{ route('product.update', $product->id) }}"
                                        method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="row g-4">
                                            <!-- Image -->
                                            <div class="col-md-4">
                                                <div class="card shadow-sm border-0 p-3">
                                                    <div class="image-preview-wrapper w-100">
                                                        <img id="preview"
                                                            src="{{ $product->image ? asset('uploads/products/' . $product->image) : '' }}"
                                                            alt="No Image Selected">
                                                        <button type="button" class="clear-image-btn"
                                                            id="clearImageBtn">&times;</button>
                                                    </div>
                                                    <div class="mt-3">
                                                        <label class="form-label">Product Image</label>
                                                        <input type="file" id="imageInput" name="image"
                                                            class="form-control">
                                                        <div class="small-help mt-1">PNG/JPG up to 2MB.</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Product Info -->
                                            <div class="col-md-8">
                                                <div class="row g-3">


                                                    <div class="col-sm-4">
                                                        <label class="form-label">Category</label>
                                                        <div class="input-group">
                                                            <select id="category-dropdown" name="category_id"
                                                                class="form-select">
                                                                <option value="">Select Category</option>
                                                                @foreach ($categories as $cat)
                                                                    <option value="{{ $cat->id }}"
                                                                        {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                                                                        {{ $cat->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>

                                                            <button type="button" class="btn btn-primary add-btn"
                                                                data-toggle="modal" data-target="#categoryModal"
                                                                title="Add New Category">
                                                                <i class="fa-solid fa-plus"></i>
                                                            </button>

                                                        </div>
                                                    </div>


                                                    <div class="col-sm-4">
                                                        <label class="form-label">Sub Category</label>
                                                        <div class="input-group">


                                                            <select id="subcategory-dropdown" name="sub_category_id"
                                                                class="form-select">
                                                                <option value="">Select Sub category</option>
                                                                 @foreach ($subcategories as $cat)
                                                                    <option value="{{ $cat->id }}"
                                                                        {{ $product->sub_category_id == $cat->id ? 'selected' : '' }}>
                                                                        {{ $cat->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" class="btn btn-primary add-btn"
                                                                data-toggle="modal" data-target="#subcategoryModal"
                                                                title="Add New Category">
                                                                <i class="fa-solid fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-4">
                                                        <label class="form-label">Item Code</label>
                                                        <input type="text" value="{{ $product->item_code }}" class="form-control" readonly style="background-color: #e9ecef; cursor: not-allowed;">
                                                    </div>

                                                    <div class="col-sm-4">
                                                        <label class="form-label">Item Description</label>
                                                        <input type="text" id="product_name"
                                                            value="{{ $product->item_name }}" name="product_name"
                                                            class="form-control" required>
                                                    </div>

                                                    <div class="col-sm-4">
                                                        <label class="form-label">Brand</label>
                                                        <div class="input-group">


                                                            <select name="brand_id" class="form-select" required>
                                                                <option value="" disabled>Select One</option>
                                                                @foreach ($brands as $brand)
                                                                    <option value="{{ $brand->id }}"
                                                                        {{ $product->brand_id == $brand->id ? 'selected' : '' }}>
                                                                        {{ $brand->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" class="btn btn-primary add-btn"
                                                                data-toggle="modal" data-target="#brandcategoryModal"
                                                                title="Add New Category">
                                                                <i class="fa-solid fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <label for="barcodeInput" class="form-label">Barcode</label>
                                                        <div class="input-group">
                                                            <input type="text" id="barcodeInput" name="barcode_path"
                                                                class="form-control" placeholder="Enter or Generate Barcode"
                                                                value="{{ $product->barcode_path }}">

                                                            <button type="button" id="generateBarcodeBtn"
                                                                class="btn btn-primary px-1"
                                                                style="font-size:11px; height:32px; line-height:2;">
                                                                Gen
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-4">
                                                        <label class="form-label">Model</label>
                                                        <div class="input-group">
                                                            <input type="text" id="model"
                                                                value="{{ $product->model }}" name="model"
                                                                class="form-control" required>


                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <label class="form-label">HS Code</label>
                                                        <div class="input-group">
                                                            <input type="text" id="hs_code"
                                                                value="{{ $product->hs_code }}" name="hs_code"
                                                                class="form-control" required>


                                                        </div>
                                                    </div>


                                                    <div class="col-sm-4">
                                                        <label class="form-label">Color</label>
                                                        <select name="color[]" id="color-select" class="form-select"
                                                            multiple="multiple" style="width:100%">
                                                            <option value="Black">Black</option>
                                                            <option value="White">White</option>
                                                            <option value="Red">Red</option>
                                                            <option value="Blue">Blue</option>
                                                        </select>
                                                    </div>

                                                    {{-- Packaging Type --}}
                                                    <div class="col-sm-4">
                                                        <label class="form-label">Packaging Type</label>
                                                        <div class="input-group">
                                                            <select id="packing_type" name="packing_type"
                                                                class="form-select" required>
                                                                <option value="">Select Packaging Type</option>
                                                                <option value="Standard"
                                                                    {{ $product->pack_type == 'Standard' ? 'selected' : '' }}>
                                                                    Standard</option>
                                                                <option value="Customize"
                                                                    {{ $product->pack_type == 'Customize' ? 'selected' : '' }}>
                                                                    Customize</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    {{-- Unit field (shown for Standard) --}}
                                                    <div class="col-sm-4" id="unitSection" style="display: none;">
                                                        <label class="form-label">Unit</label>
                                                        <div class="input-group">
                                                            <input type="text" id="unit_readonly" class="form-control" style="display:none;" value="Piece" readonly>
                                                            <input type="hidden" name="unit" id="unit_hidden" disabled>
                                                            <select id="unit_select" name="unit" class="form-select" value="">
                                                                <option value="">Select Unit</option>
                                                                @foreach ($units as $u)
                                                                    <option value="{{ $u->id }}"
                                                                        {{ $product->unit_id == $u->id ? 'selected' : '' }}>
                                                                        {{ $u->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" class="btn btn-primary add-btn" id="unit_add_btn"
                                                                data-toggle="modal" data-target="#unitModal"
                                                                title="Add New Unit" style="display:none;">
                                                                <i class="fa-solid fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    {{-- Packaging Quantity (shown for Customize) --}}
                                                    <div class="col-sm-4" id="packingQtySection" style="display: none;">
                                                        <label class="form-label">Packaging Quantity</label>
                                                        <div class="input-group">
                                                            <input type="text" id="packing_qty"
                                                                value="{{ $product->pack_qty ?? 0 }}" name="packing_qty"
                                                                class="form-control">
                                                        </div>
                                                    </div>

                                                    {{-- Unit per Packing (shown for Customize) --}}
                                                    <div class="col-sm-4" id="unitPerPackingSection"
                                                        style="display: none;">
                                                        <label class="form-label">Unit per Packing</label>
                                                        <input id="piece_per_pack" type="text"
                                                            value="{{ $product->piece_per_pack ?? 0 }}" name="piece_per_pack"
                                                            class="form-control">
                                                    </div>

                                                    {{-- Loose Pieces (shown for Customize) --}}
                                                    <div class="col-sm-4" id="loosepiece_section" style="display: none;">
                                                        <label class="form-label">Loose Pieces</label>
                                                        <input id="loose_piece" type="text"
                                                            value="{{ $product->loose_piece ?? 0 }}" name="loose_piece"
                                                            class="form-control">
                                                    </div>


                                                     {{-- Pricing and Stock fields removed - handled in Opening Stock Edit --}}
                                                 </div>

                                                <hr class="my-4">

                                                {{-- Is Part toggle --}}
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" id="isPart"
                                                        name="is_part" value="1">
                                                    <label class="form-check-label" for="isPart">This is a Part (not a
                                                        full product)</label>
                                                </div>

                                                {{-- Assembled toggle + modal trigger --}}
                                                <div class="row g-3 align-items-center">
                                                    <div class="col-md-4">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="isAssembled" name="is_assembled" value="1">
                                                            <label class="form-check-label" for="isAssembled">This product
                                                                is assembled from parts?</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 d-flex align-items-center gap-2">
                                                        <button type="button" class="btn btn-outline--primary btn-sm"
                                                            id="openPartsModal" disabled>
                                                            <i class="las la-tools"></i> Define Parts (BOM)
                                                        </button>
                                                        <span class="badge bg-secondary badge-note d-none"
                                                            id="bomBadge">0 parts</span>
                                                    </div>
                                                </div>

                                                {{-- Hidden BOM JSON holder --}}
                                                <input type="hidden" name="bom_json" id="bom_json" value="[]">

                                            </div>
                                        </div>

                                        <div class="mt-4">
                                             <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                                                 <i class="las la-save"></i> UPDATE PRODUCT PROFILE
                                             </button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- bodywrapper__inner end -->
            </div><!-- body-wrapper end -->
        </div>

        {{-- category modal --}}
        <div id="categoryModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                   

                    <div class="modal-header">
                        <h5 class="modal-title"><span class="type"></span> <span>Add Category</span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('store.category') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                 <input type="hidden" name="page" value="product_edit">
                                 <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary h-45 w-100">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- subcategory modal --}}
        <div id="subcategoryModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><span class="type"></span> <span>Add Subcategory</span></h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('store.subcategory') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_edit">
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <div class="form-group">
                                <label>Category Name</label>
                                <select name="category_id" class="form-select">
                                    @foreach ($categories as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Sub-Category Name</label>
                                <input type="text" id="sub_category" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary h-45 w-100">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- model modal --}}
        {{-- <div id="modelModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><span class="type"></span> <span>Add Models</span></h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('store.Unit') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_edit">
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="unit" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary h-45 w-100">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}

        {{-- brand modal --}}
        <div id="brandcategoryModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                   
                    <div class="modal-header">
                        <h5 class="modal-title"><span class="type"></span> <span>Add Brand</span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('store.Brand') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                             <input type="hidden" name="page" value="product_edit">
                          <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn--primary h-45 w-100">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- unit modal --}}
        <div id="unitModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <input type="hidden" name="page" value="product_edit">
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <h5 class="modal-title">Add Unit</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form id="unitForm" action="{{ route('store.Unit') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Unit Name</label>
                                <input type="text" name="name" id="unitName" class="form-control" required>
                                
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn--primary h-45">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- Pakagetype model --}}
        {{-- <div id="PackageTypeModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><span class="type"></span> <span>Add Package type</span></h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('package-type.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page" class="form-control" required>
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary h-45 w-100">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}

        {{-- Parts/BOM Modal --}}
        <div class="modal fade" id="partsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-xl modal-wide">
                <div class="modal-content">
                    <input type="hidden" name="page" value="product_edit">
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <div class="modal-header">
                        <h6 class="modal-title"><i class="las la-cubes me-1"></i> Define Parts (BOM)</h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="small-help mb-3">
                            Add parts and set <b>Required / Unit</b>. System shows current <b>Available</b> and calculates
                            <b>Needed</b> from “Opening Stock (pcs)” plus any <b>Shortage</b>.
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <div class="p-2 border rounded">
                                    <div class="text-muted small">Reference</div>
                                    <div>Stock in (pieces): <b id="bomStockPieces">0</b></div>
                                    <div>Assemble possible from parts: <b id="assemblePossible">0</b></div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm bom-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width:320px;">Part</th>
                                        <th style="min-width:120px;">Required / Unit</th>
                                        <th style="min-width:120px;">Available</th>
                                        <th style="min-width:120px;">Needed</th>
                                        <th style="min-width:120px;">Shortage</th>
                                        <th style="min-width:90px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="bomRows"></tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm" id="addBomRow">
                            <i class="las la-plus-circle"></i> Add Part
                        </button>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" id="saveBom"><i class="las la-save"></i> Save Parts</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

@section('js')
    <script>
        function calculateOpeningStock() {
            let packingQty = parseFloat(document.getElementById('packing_qty').value) || 0;
            let unitPerPackage = parseFloat(document.getElementById('piece_per_pack').value) || 0;
            let loosePiece = parseFloat(document.getElementById('loose_piece').value) || 0;
            let packedStock = 0;
            if (packingQty > 0 && unitPerPackage > 0) {
                packedStock = packingQty * unitPerPackage;
            } else {
                packedStock = packingQty + unitPerPackage;
            }
            let totalStock = packedStock + loosePiece;
            if(document.getElementById('opening_stock')) {
                document.getElementById('opening_stock').value = totalStock;
            }
        }
        if(document.getElementById('packing_qty')) document.getElementById('packing_qty').addEventListener('input', calculateOpeningStock);
        if(document.getElementById('piece_per_pack')) document.getElementById('piece_per_pack').addEventListener('input', calculateOpeningStock);
        if(document.getElementById('loose_piece')) document.getElementById('loose_piece').addEventListener('input', calculateOpeningStock);

        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('barcodeInput');
            const btn = document.getElementById('generateBarcodeBtn');
            let manualMode = false;
            if (input && input.value.trim() === '') {
                fetch('{{ route('generate-barcode-image') }}')
                    .then(res => res.json())
                    .then(data => {
                        if (!manualMode) {
                            input.value = data.barcode_number;
                        }
                    });
            }
            if(btn) {
                btn.addEventListener('click', function() {
                    manualMode = true;
                    input.value = '';
                    input.focus();
                });
            }
        });

        $(document).on('click', '#generateBarcodeBtn', function() {
            let currentValue = document.getElementById('barcodeInput').value.trim();
            const hit = (url) => fetch(url).then(r => r.json()).then(data => {
                document.getElementById('barcodeInput').value = data.barcode_number;
            });
            if (currentValue !== "") {
                hit('/generate-barcode-image?code=' + currentValue);
            } else {
                hit('{{ route('generate-barcode-image') }}');
            }
        });

        const imageInput = document.getElementById('imageInput');
        const preview = document.getElementById('preview');
        const clearImageBtn = document.getElementById('clearImageBtn');
        if(imageInput) {
            imageInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        if(clearImageBtn) {
            clearImageBtn.addEventListener('click', function() {
                preview.src = "";
                imageInput.value = "";
            });
        }

        function updatePackingTypeDisplay() {
            var packingType = $('#packing_type').val();
            if (packingType === 'Standard') {
                $('#unitSection').show();
                var pieceOptionId = $('#unit_select option').filter(function() { 
                    var txt = $(this).text().toLowerCase();
                    return txt === 'piece' || txt === 'pcs' || txt === 'pieces'; 
                }).first().val();
                $('#unit_readonly').val('Piece').show().prop('disabled', false);
                $('#unit_hidden').val(pieceOptionId).prop('disabled', false);
                $('#unit_select').prop('disabled', true).hide();
                $('#unit_select').closest('.input-group').find('.select2-container').hide();
                $('#unit_add_btn').hide();
                $('#packingQtySection').hide();
                $('#unitPerPackingSection').hide();
                $('#loosepiece_section').hide();
                $('#packing_qty').val('0');
                $('#piece_per_pack').val('0');
                $('#loose_piece').val('0');
            } else if (packingType === 'Customize') {
                $('#unitSection').show();
                $('#unit_readonly').hide().prop('disabled', true);
                $('#unit_hidden').prop('disabled', true);
                $('#unit_select').prop('disabled', false).show();
                $('#unit_select').closest('.input-group').find('.select2-container').show();
                $('#unit_add_btn').show();
                $('#packingQtySection').show();
                $('#unitPerPackingSection').show();
                $('#loosepiece_section').show();
            } else {
                $('#unitSection').hide();
                $('#packingQtySection').hide();
                $('#unitPerPackingSection').hide();
                $('#loosepiece_section').hide();
                $('#packing_qty').val('0');
                $('#piece_per_pack').val('0');
                $('#loose_piece').val('0');
            }
        }
        $('#packing_type').on('change', updatePackingTypeDisplay);

        $('#productForm').on('submit', function(e) {
            if ($('#packing_qty').val().trim() === '') $('#packing_qty').val('0');
            if ($('#piece_per_pack').val().trim() === '') $('#piece_per_pack').val('0');
            if ($('#loose_piece').val().trim() === '') $('#loose_piece').val('0');
        });

        $('#category-dropdown').on('change', function() {
            var categoryId = $(this).val();
            if (categoryId) {
                $.ajax({
                    url: '/get-subcategories/' + categoryId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#subcategory-dropdown').empty().append('<option selected disabled>Select Subcategory</option>');
                        $.each(data, function(_, v) {
                            $('#subcategory-dropdown').append('<option value="' + v.id + '">' + v.name + '</option>');
                        });
                    }
                });
            } else {
                $('#subcategory-dropdown').empty().append('<option value="">Select Subcategory</option>');
            }
        });

        $(document).ready(function() {
            var categoryId = $('#category-dropdown').val();
            if (categoryId) {
                $.ajax({
                    url: '/get-subcategories/' + categoryId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('#subcategory-dropdown').empty();
                        $.each(data, function(_, v) {
                            var selected = {{ $product->sub_category_id }} == v.id ? 'selected' : '';
                            $('#subcategory-dropdown').append('<option value="' + v.id + '" ' + selected + '>' + v.name + '</option>');
                        });
                    }
                });
            }
            updatePackingTypeDisplay();
            var colors = {!! json_encode($product->color ? json_decode($product->color) : []) !!};
            if (colors.length > 0) $('#color-select').val(colors).trigger('change');
            if ({{ $product->is_part ?? 0 }}) $('#isPart').prop('checked', true);
            if ({{ $product->is_assembled ?? 0 }}) $('#isAssembled').prop('checked', true).trigger('change');
        });

        $(document).ready(function() {
            $('#color-select').select2({
                tags: true,
                placeholder: "Select or type color(s)",
                allowClear: true,
                width: 'resolve'
            });
        });

        $('#unitForm').on('submit', function(e) {
            e.preventDefault();
            const unitName = $('#unitName').val().trim();
            if (!unitName) {
                Swal.fire({ icon: 'warning', title: 'Required Field', text: 'Please enter unit name' });
                return false;
            }
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    $('#unitModal').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('#unitForm')[0].reset();
                    $.ajax({
                        url: "{{ route('get-units') }}",
                        type: 'GET',
                        dataType: 'json',
                        success: function(units) {
                            let unitOptions = '<option value="">Select Unit</option>';
                            if (units && units.length > 0) {
                                units.forEach(function(unit) {
                                    unitOptions += '<option value="' + unit.id + '">' + unit.name + '</option>';
                                });
                            }
                            $('#unit_select').html(unitOptions);
                        }
                    });
                    setTimeout(function() {
                        Swal.fire({ icon: 'success', title: 'Success', text: 'Unit created successfully!' });
                    }, 100);
                }
            });
        });

        let bomItems = []; 
        const num = n => isNaN(parseFloat(n)) ? 0 : parseFloat(n);

        function toggleBomUI() {
            const on = $('#isAssembled').is(':checked');
            $('#openPartsModal').prop('disabled', !on);
            if (!on) {
                bomItems = [];
                $('#bom_json').val('[]');
                $('#bomBadge').addClass('d-none').text('0 parts');
            }
        }
        $('#isAssembled').on('change', toggleBomUI);
        toggleBomUI();

        $('#openPartsModal').on('click', function() {
            const stockPieces = num($('input[name="Stock"]').val());
            $('#bomStockPieces').text(stockPieces);
            renderBomTable();
            recalcAssemblePossible();
            $('#partsModal').modal('show');
        });

        function renderBomTable() {
            const $tb = $('#bomRows');
            $tb.empty();
            if (!bomItems.length) {
                bomItems.push({part_id: null, code: '', name: '', unit: '', required_per_unit: 1, available_qty: 0, needed_for_row: 0, shortage: 0});
            }
            bomItems.forEach((it, idx) => {
                const tr = $(`
                    <tr data-index="${idx}">
                      <td>
                        <select class="form-control form-control-sm part-select" style="width:100%"></select>
                        <div class="small text-muted mt-1">Search part by name/code</div>
                      </td>
                      <td><input type="number" step="0.01" class="form-control form-control-sm req-per-unit" value="${num(it.required_per_unit)}" min="0"></td>
                      <td><input type="number" class="form-control form-control-sm available" value="${num(it.available_qty)}" readonly></td>
                      <td><input type="number" class="form-control form-control-sm needed" value="${num(it.needed_for_row)}" readonly></td>
                      <td><input type="number" class="form-control form-control-sm shortage" value="${num(it.shortage)}" readonly></td>
                      <td><button type="button" class="btn btn-sm btn-outline-danger del-bom-row">X</button></td>
                    </tr>`);
                $tb.append(tr);
                initPartSelect2(tr.find('.part-select'), it.part_id ? {id: it.part_id, text: it.name + ' - ' + it.code} : null);
                recalcRow(tr);
            });
        }

        function initPartSelect2($el, presetOption = null) {
            $el.select2({
                placeholder: 'Search part...',
                width: '100%',
                dropdownParent: $('#partsModal'),
                ajax: {
                    delay: 200,
                    url: "{{ route('search-part-name') }}",
                    dataType: 'json',
                    data: params => ({ q: params.term || '' }),
                    processResults: data => ({
                        results: (data || []).map(p => ({
                            id: p.id, text: p.item_name + ' - ' + p.item_code, code: p.item_code, name: p.item_name, unit: p.unit ?? '', available_qty: Number(p.available_qty || 0)
                        }))
                    }),
                    cache: true
                }
            });
            if (presetOption) {
                $el.append(new Option(presetOption.text, presetOption.id, true, true)).trigger('change');
            }
            $el.on('select2:select', function(e) {
                const d = e.params.data;
                const $tr = $(this).closest('tr');
                const idx = $tr.data('index');
                bomItems[idx] = { ...bomItems[idx], part_id: d.id, code: d.code, name: d.name, unit: d.unit, available_qty: Number(d.available_qty || 0) };
                $tr.find('.available').val(bomItems[idx].available_qty);
                recalcRow($tr);
                recalcAssemblePossible();
            });
        }

        $(document).on('input', '.req-per-unit', function() {
            const $tr = $(this).closest('tr');
            const idx = $tr.data('index');
            bomItems[idx].required_per_unit = num($(this).val());
            recalcRow($tr);
            recalcAssemblePossible();
        });

        function recalcRow($tr) {
            const idx = $tr.data('index');
            const stockPieces = num($('input[name="Stock"]').val());
            const rpu = num($tr.find('.req-per-unit').val());
            const avail = num($tr.find('.available').val());
            const needed = Math.max(0, rpu * stockPieces);
            const shortage = Math.max(0, needed - avail);
            $tr.find('.needed').val(needed);
            $tr.find('.shortage').val(shortage);
            bomItems[idx].needed_for_row = needed;
            bomItems[idx].shortage = shortage;
        }

        function recalcAssemblePossible() {
            let minPossible = Infinity;
            $('#bomRows tr').each(function() {
                const rpu = num($(this).find('.req-per-unit').val());
                const avail = num($(this).find('.available').val());
                if (rpu > 0) minPossible = Math.min(minPossible, Math.floor(avail / rpu));
            });
            if (minPossible === Infinity) minPossible = 0;
            $('#assemblePossible').text(minPossible);
        }

        $('#saveBom').on('click', function() {
            const cleaned = bomItems.filter(x => x.part_id);
            $('#bom_json').val(JSON.stringify(cleaned));
            if (cleaned.length) $('#bomBadge').removeClass('d-none').text(cleaned.length + ' parts');
            else $('#bomBadge').addClass('d-none').text('0 parts');
            $('#partsModal').modal('hide');
        });

        document.querySelectorAll('[data-toggle="tooltip"]').forEach(el => {
            $(el).tooltip();
        });
    </script>
@endsection
@endsection
