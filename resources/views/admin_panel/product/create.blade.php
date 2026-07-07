@extends('admin_panel.layout.app')

@section('content')
    @can('product.create')
        @section('css')
        <style>
            /* Layout Matching User's Image */
            .main-content-inner { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
            .form-section { display: flex; flex-wrap: wrap; gap: 30px; align-items: flex-start; }
            .image-col { width: 320px; flex-shrink: 0; }
            .fields-col { flex: 1; min-width: 600px; }
            
            /* Field Styling */
            .field-group { margin-bottom: 20px; flex: 1; }
            .field-label { display: block; font-weight: 600; color: #444; margin-bottom: 8px; font-size: 14px; }
            .field-label i { color: #4e73df; margin-right: 5px; }
            
            .custom-input, .custom-select {
                width: 100%;
                border: 1px solid #ddd !important; /* Explicit border for all fields */
                border-radius: 4px;
                padding: 6px 12px;
                height: 40px;
                font-size: 14px;
                background-color: #fff;
                transition: border-color 0.2s;
            }
            .custom-input:focus, .custom-select:focus { border-color: #2563eb !important; outline: none; box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1); }
            
            /* Action Buttons (+ and Gen) */
            .btn-plus-sm { 
                background: #2563eb; color: #fff; border: none; width: 32px; height: 24px; 
                border-radius: 4px; display: flex; align-items: center; justify-content: center; 
                margin-top: 5px; cursor: pointer; font-size: 12px; transition: 0.2s;
            }
            .btn-plus-sm:hover { background: #1d4ed8; }
            
            .btn-plus-inline {
                background: #2563eb; color: #fff; border: none; width: 40px; height: 40px; 
                border-radius: 4px; display: flex; align-items: center; justify-content: center; 
                cursor: pointer; font-size: 14px; margin-left: 5px; flex-shrink: 0;
            }
            
            .btn-gen { 
                background: #2563eb; color: #fff; border: none; padding: 0 15px; 
                border-radius: 0 4px 4px 0; height: 40px; cursor: pointer; font-size: 13px;
                transition: 0.2s;
            }
            .btn-gen:hover { background: #1d4ed8; }
            
            /* Row Layout */
            .field-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 5px; }
            
            /* Image Preview Area */
            .image-box { 
                width: 100%; height: 320px; background: #fff; 
                border: 2px dashed #ddd; border-radius: 12px; 
                position: relative; overflow: hidden; display: flex; 
                align-items: center; justify-content: center; margin-bottom: 15px;
            }
            #preview { max-width: 100%; max-height: 100%; object-fit: contain; }
            .close-img { 
                position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.6); 
                color: #fff; width: 28px; height: 28px; border-radius: 50%; 
                display: flex; align-items: center; justify-content: center; cursor: pointer; border: none;
                transition: 0.2s;
            }
            .close-img:hover { background: #ef4444; }

            /* Select2 Customization */
            .select2-container--default .select2-selection--multiple { border: 1px solid #ddd !important; min-height: 40px; border-radius: 4px; }
            .select2-container--default.select2-container--focus .select2-selection--multiple { border-color: #2563eb !important; }
            
            /* Add arrow to multi-select as requested */
            .select2-container--default .select2-selection--multiple { position: relative; padding-right: 30px; }
            .select2-container--default .select2-selection--multiple::after {
                content: "\f107"; font-family: "Line Awesome Free"; font-weight: 900;
                position: absolute; top: 50%; right: 10px; transform: translateY(-50%);
                color: #888; pointer-events: none;
            }

            /* Checkboxes */
            .check-group { display: flex; flex-direction: column; gap: 12px; margin-top: 20px; }
            .check-row { display: flex; align-items: center; gap: 10px; font-size: 14px; color: #555; }
            .check-row input { width: 18px; height: 18px; cursor: pointer; }
            
            .btn-bom { 
                border: 1px solid #2563eb; color: #2563eb; background: #fff; 
                padding: 6px 20px; border-radius: 4px; font-size: 14px;
                transition: 0.2s; cursor: pointer; margin-left: 15px;
            }
            .btn-bom:hover:not(:disabled) { background: #2563eb; color: #fff; }
            .btn-bom:disabled { border-color: #ccc; color: #ccc; cursor: not-allowed; }
            
            .btn-save-main {
                background: #2563eb; color: #fff; border: none; padding: 12px 60px;
                border-radius: 6px; font-weight: 700; font-size: 16px; 
                cursor: pointer; transition: 0.3s; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
            }
            .btn-save-main:hover { background: #1d4ed8; transform: translateY(-1px); }
        </style>
        @endsection

        <div class="main-content">
            <div class="main-content-inner">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <h5 class="m-0 font-weight-bold" style="color: #1e293b;">Add New Product</h5>
                    <a href="{{ route('product') }}" class="btn btn-sm btn-outline-primary px-3">
                        <i class="las la-arrow-left"></i> Back
                    </a>
                </div>

                @if (session('swal_error'))
                    <script>Swal.fire({ icon: 'error', title: 'Error', text: "{{ session('swal_error') }}" });</script>
                @endif

                <form id="productForm" action="{{ route('store-product') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="phase" value="phase1">

                    <div class="form-section">
                        <!-- Left: Image Area -->
                        <div class="image-col">
                            <div class="image-box">
                                <img id="preview" src="{{ asset('assets/images/placeholder-img.png') }}" alt="Product Image">
                                <button type="button" class="close-img" id="clearImageBtn" title="Remove Image">&times;</button>
                            </div>
                            <label class="field-label">Product Image</label>
                            <input type="file" id="imageInput" name="image" class="custom-input mb-2" accept="image/*">
                            <p class="small text-muted mb-0">📌 Drag and drop or click to upload</p>
                        </div>

                        <!-- Right: Fields Area -->
                        <div class="fields-col">
                            <!-- Row 1: Branch, Category, SubCategory, Type -->
                            <div class="field-row">
                                <div class="field-group">
                                    <label class="field-label">🏢 Branch</label>
                                    @if($isSuperAdmin)
                                        <select name="branch_id" class="custom-select" required>
                                            <option value="">-- Select Branch --</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" class="custom-input bg-light" value="{{ $branches->first()?->name ?? 'Default Branch' }}" readonly>
                                        <input type="hidden" name="branch_id" value="{{ $user->branch_id ?? 1 }}">
                                    @endif
                                    <p class="small text-danger mt-1 mb-0" style="font-size: 11px;">📌 Select the branch this product belongs to</p>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Category</label>
                                    <select id="category-dropdown" name="category_id" class="custom-select" required>
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn-plus-sm" data-toggle="modal" data-target="#categoryModal">+</button>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Sub Category</label>
                                    <select id="subcategory-dropdown" name="sub_category_id" class="custom-select" required>
                                        <option value="">Select Sub category</option>
                                    </select>
                                    <button type="button" class="btn-plus-sm" data-toggle="modal" data-target="#subcategoryModal">+</button>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Type</label>
                                    <div class="d-flex align-items-center">
                                        <select name="type_id" class="custom-select">
                                            <option value="">Select Type</option>
                                            @foreach ($types as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-plus-inline" data-toggle="modal" data-target="#typeModal">+</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Brand, Barcode, Item Description, Model -->
                            <div class="field-row mt-3">
                                <div class="field-group">
                                    <label class="field-label">Brand</label>
                                    <div class="d-flex align-items-center">
                                        <select name="brand_id" class="custom-select">
                                            <option value="">Select One</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-plus-inline" data-toggle="modal" data-target="#brandcategoryModal">+</button>
                                    </div>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Barcode</label>
                                    <div class="d-flex align-items-center">
                                        <input type="text" id="barcodeInput" name="barcode_path" class="custom-input" value="{{ $nextBarcode ?? '' }}" placeholder="Barcode / SKU" style="border-radius: 4px 0 0 4px !important;">
                                        <button class="btn-gen" type="button" id="generateBarcodeBtn" title="Generate Custom SKU">Gen</button>
                                    </div>
                                </div>

                                <div class="field-group" style="grid-column: span 1;">
                                    <label class="field-label">Item Description</label>
                                    <input type="text" id="product_name" name="product_name" class="custom-input" placeholder="Product Name" required>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Model</label>
                                    <input type="text" id="model" name="model" class="custom-input" placeholder="Model No.">
                                </div>
                            </div>

                            <!-- Row 3: HS Code, Color, Packaging Type -->
                            <div class="field-row mt-3">
                                <div class="field-group">
                                    <label class="field-label">HS Code</label>
                                    <input type="text" id="hs_code" name="hs_code" class="custom-input" placeholder="HS Code" required>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Color</label>
                                    <select name="color[]" id="color-select" class="custom-select" multiple="multiple">
                                        <option value="Black">Black</option>
                                        <option value="White">White</option>
                                        <option value="Red">Red</option>
                                        <option value="Blue">Blue</option>
                                        <option value="Silver">Silver</option>
                                        <option value="Golden">Golden</option>
                                    </select>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Packaging Type</label>
                                    <select id="packing_type" name="packing_type" class="custom-select" required>
                                        <option value="">Select Packaging Type</option>
                                        <option value="Standard">Standard</option>
                                        <option value="Customize">Customize</option>
                                    </select>
                                </div>

                                <div class="field-group" id="unitSection" style="display: none;">
                                    <label class="field-label">Unit</label>
                                    <div class="d-flex align-items-center">
                                        <input type="text" id="unit_readonly" class="custom-input bg-light" value="Piece" readonly style="display:none;">
                                        <input type="hidden" name="unit" id="unit_hidden" disabled>
                                        <select id="unit_select" name="unit" class="custom-select">
                                            <option value="">Select Unit</option>
                                            @foreach ($units as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-plus-inline" id="unit_add_btn" data-toggle="modal" data-target="#unitModal">+</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 4: Advanced Packing (Hidden by default) -->
                            <div class="field-row mt-3" id="advancedPackingRow" style="display: none;">
                                <div class="field-group">
                                    <label class="field-label">Pack Qty</label>
                                    <input type="number" id="packing_qty" name="packing_qty" class="custom-input">
                                </div>
                                <div class="field-group">
                                    <label class="field-label">Unit/Pack</label>
                                    <input type="number" id="piece_per_pack" name="piece_per_pack" class="custom-input">
                                </div>
                                <div class="field-group">
                                    <label class="field-label">Loose Pcs</label>
                                    <input type="number" id="loose_piece" name="loose_piece" class="custom-input">
                                </div>
                                <div class="field-group"></div>
                            </div>

                            <!-- Toggles Section -->
                            <div class="check-group mt-4">
                                <div class="check-row">
                                    <input type="checkbox" id="isPart" name="is_part" value="1">
                                    <label for="isPart" class="m-0">This is a Part (not a full product)</label>
                                </div>
                                <div class="check-row">
                                    <input type="checkbox" id="isAssembled" name="is_assembled" value="1">
                                    <label for="isAssembled" class="m-0">This product is assembled from parts?</label>
                                    <button type="button" class="btn-bom" id="openPartsModal" disabled>Define Parts (BOM)</button>
                                    <span class="badge badge-secondary ml-2 d-none" id="bomBadge">0 parts</span>
                                </div>
                            </div>

                            <!-- Form Submit -->
                            <div class="mt-5 pt-4 border-top text-center">
                                <button type="submit" class="btn-save-main">SAVE PRODUCT</button>
                                <p class="text-muted small mt-2">Next step: Setup opening stock and pricing</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modals (Inline as per original structure) -->
        <div id="categoryModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Category</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('store.category') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary w-100">Submit</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div id="subcategoryModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Subcategory</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('store.subcategory') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id" class="form-control">
                                    @foreach ($categories as $item) <option value="{{ $item->id }}">{{ $item->name }}</option> @endforeach
                                </select>
                            </div>
                            <div class="form-group"><label>Sub-Category Name</label><input type="text" name="name" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary w-100">Submit</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div id="typeModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Type</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('store.type') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="form-group"><label>Type Name</label><input type="text" name="name" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary w-100">Submit</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div id="brandcategoryModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Brand</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('store.Brand') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary w-100">Submit</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div id="unitModal" class="modal fade" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Unit</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form action="{{ route('store.Unit') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="form-group"><label>Unit Name</label><input type="text" name="name" class="form-control" required></div>
                        </div>
                        <div class="modal-footer"><button type="submit" class="btn btn-primary w-100">Submit</button></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- BOM Modal -->
        <div class="modal fade" id="partsModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="las la-cubes"></i> Define Parts (BOM)</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Part</th>
                                        <th>Required / Unit</th>
                                        <th>Available</th>
                                        <th>Needed</th>
                                        <th>Shortage</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="bomRows"></tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addBomRow">Add Part</button>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" id="saveBom">Save Parts</button>
                    </div>
                </div>
            </div>
        </div>

        @section('js')
        <script>
            // 1. Image Preview
            const imageInput = document.getElementById('imageInput');
            const preview = document.getElementById('preview');
            const clearBtn = document.getElementById('clearImageBtn');
            const placeholder = "{{ asset('assets/images/placeholder-img.png') }}";

            if(imageInput) {
                imageInput.onchange = function() {
                    const [file] = imageInput.files;
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = e => { preview.src = e.target.result; };
                        reader.readAsDataURL(file);
                    }
                };
            }
            if(clearBtn) {
                clearBtn.onclick = function() {
                    imageInput.value = '';
                    preview.src = placeholder;
                };
            }

            // 2. SKU / Barcode Gen
            $('#generateBarcodeBtn').on('click', function() {
                // Generate sequential barcode if clicked manually
                const nextBarcode = "{{ $nextBarcode ?? '' }}";
                if(nextBarcode) {
                    $('#barcodeInput').val(nextBarcode);
                } else {
                    // Fallback just in case
                    const rand = Math.floor(1000000 + Math.random() * 9000000);
                    $('#barcodeInput').val(`100${rand}`);
                }
            });

            // 3. Category Sync
            $('#category-dropdown').on('change', function () {
                const categoryId = $(this).val();
                const $sub = $('#subcategory-dropdown');
                if (categoryId) {
                    $.ajax({
                        url: "/get-subcategories/" + categoryId,
                        type: 'GET',
                        success: function (data) {
                            $sub.empty().append('<option selected disabled>Select Sub category</option>');
                            $.each(data, function (_, v) { $sub.append(`<option value="${v.id}">${v.name}</option>`); });
                        }
                    });
                } else {
                    $sub.empty().append('<option value="">Select Sub category</option>');
                }
            });

            // 4. Packaging Toggles
            $('#packing_type').on('change', function () {
                const type = $(this).val();
                if (type === 'Standard') {
                    $('#unitSection').show();
                    $('#advancedPackingRow').hide();
                    const pieceId = $('#unit_select option').filter(function() { 
                        const t = $(this).text().toLowerCase(); return t.includes('piece') || t.includes('pcs'); 
                    }).val();
                    $('#unit_readonly').show();
                    $('#unit_hidden').val(pieceId).prop('disabled', false);
                    $('#unit_select').hide().prop('disabled', true);
                    $('#unit_add_btn').hide();
                } else if (type === 'Customize') {
                    $('#unitSection, #advancedPackingRow').show();
                    $('#unit_readonly').hide();
                    $('#unit_hidden').prop('disabled', true);
                    $('#unit_select').show().prop('disabled', false);
                    $('#unit_add_btn').show();
                } else {
                    $('#unitSection, #advancedPackingRow').hide();
                }
            });

            // 5. Initialize Select2
            $(document).ready(function () {
                $('#color-select').select2({
                    tags: true,
                    placeholder: "Select or type color(s)",
                    allowClear: true,
                    width: '100%'
                });
                $('[data-toggle="tooltip"]').tooltip();
            });

            // 6. BOM (Bill of Materials) Logic
            let bomItems = [];
            const num = n => isNaN(parseFloat(n)) ? 0 : parseFloat(n);

            $('#isAssembled').on('change', function() {
                $('#openPartsModal').prop('disabled', !this.checked);
                if(!this.checked) { bomItems = []; $('#bom_json').val('[]'); $('#bomBadge').addClass('d-none'); }
            });

            $('#openPartsModal').on('click', function () {
                renderBomTable();
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
                          <td><select class="form-control form-control-sm part-select" style="width:100%"></select></td>
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

            $('#addBomRow').on('click', function() {
                bomItems.push({part_id: null, code: '', name: '', unit: '', required_per_unit: 1, available_qty: 0, needed_for_row: 0, shortage: 0});
                renderBomTable();
            });

            $(document).on('click', '.del-bom-row', function() {
                const idx = $(this).closest('tr').data('index');
                bomItems.splice(idx, 1);
                renderBomTable();
            });

            function initPartSelect2($el, presetOption = null) {
                $el.select2({
                    placeholder: 'Search part...', width: '100%', dropdownParent: $('#partsModal'),
                    ajax: {
                        delay: 200, url: "{{ route('search-part-name') }}", dataType: 'json',
                        data: params => ({ q: params.term || '' }),
                        processResults: data => ({
                            results: (data || []).map(p => ({
                                id: p.id, text: p.item_name + ' - ' + p.item_code, code: p.item_code, name: p.item_name, unit: p.unit ?? '', available_qty: Number(p.available_qty || 0)
                            }))
                        }), cache: true
                    }
                });
                if (presetOption) $el.append(new Option(presetOption.text, presetOption.id, true, true)).trigger('change');
                $el.on('select2:select', function (e) {
                    const d = e.params.data;
                    const $tr = $(this).closest('tr');
                    const idx = $tr.data('index');
                    bomItems[idx] = { ...bomItems[idx], part_id: d.id, code: d.code, name: d.name, unit: d.unit, available_qty: Number(d.available_qty || 0) };
                    $tr.find('.available').val(bomItems[idx].available_qty);
                    recalcRow($tr);
                });
            }

            $(document).on('input', '.req-per-unit', function () {
                const $tr = $(this).closest('tr');
                const idx = $tr.data('index');
                bomItems[idx].required_per_unit = num($(this).val());
                recalcRow($tr);
            });

            function recalcRow($tr) {
                const idx = $tr.data('index');
                const rpu = num($tr.find('.req-per-unit').val());
                const avail = num($tr.find('.available').val());
                const needed = Math.max(0, rpu);
                const shortage = Math.max(0, needed - avail);
                $tr.find('.needed').val(needed);
                $tr.find('.shortage').val(shortage);
                bomItems[idx].needed_for_row = needed;
                bomItems[idx].shortage = shortage;
            }

            $('#saveBom').on('click', function () {
                const cleaned = bomItems.filter(x => x.part_id);
                $('#bom_json').val(JSON.stringify(cleaned));
                if (cleaned.length) $('#bomBadge').removeClass('d-none').text(cleaned.length + ' parts');
                else $('#bomBadge').addClass('d-none').text('0 parts');
                $('#partsModal').modal('hide');
            });

            // 7. Form Submission
            $('#productForm').on('submit', function (e) {
                e.preventDefault();
                const name = $('#product_name').val().trim();
                const cat = $('#category-dropdown').val();
                const hsCode = $('#hs_code').val() ? $('#hs_code').val().trim() : '';
                if (!name || !cat) {
                    Swal.fire({ icon: 'warning', title: 'Missing Info', text: 'Product name and category are required.' });
                    return false;
                }
                if (!hsCode) {
                    Swal.fire({ icon: 'warning', title: 'Missing HS Code', text: 'Product cannot be stored without HS Code.' });
                    return false;
                }
                this.submit();
            });
        </script>
        @endsection
    @else
        <div class="container py-5 text-center">
            <div class="alert alert-danger">Access Denied: Product Creation is restricted.</div>
        </div>
    @endcan
@endsection
