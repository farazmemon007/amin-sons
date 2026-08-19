@extends('admin_panel.layout.app')

@section('content')
<style>
    /* ═══════════════════════════════════════════════════════════
       AMEEN & SONS ERP — SIDE-BY-SIDE 2-COLUMN STOCK REQUEST
       ═══════════════════════════════════════════════════════════ */
    :root {
        --theme-navy: #1e3a5f;
        --theme-navy-light: #2c5282;
        --theme-gold: #c8973a;
        --theme-gold-light: #f0a500;
        --theme-border: #e2e8f0;
        --theme-bg: #f1f5f9;
    }

    .sr-split-wrapper {
        padding: 12px 14px 24px;
        background-color: var(--theme-bg);
    }

    /* 1. Header Bar */
    .sr-top-bar {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        border-radius: 10px;
        padding: 10px 18px;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(30, 58, 95, 0.15);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .sr-top-title {
        font-size: 15px;
        font-weight: 800;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        letter-spacing: -0.2px;
    }

    /* 2. Side-by-Side Cards */
    .sr-side-card {
        background: #ffffff;
        border: 1px solid var(--theme-border);
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        padding: 16px 18px;
        height: calc(100vh - 175px);
        min-height: 480px;
        display: flex;
        flex-direction: column;
    }

    .sr-card-heading {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #1e293b;
        padding-bottom: 10px;
        border-bottom: 1.5px solid #f1f5f9;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Form Controls */
    .f-label-xs {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #475569;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .f-input-xs {
        height: 36px !important;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 13px;
        color: #1e293b;
        background: #ffffff;
        width: 100%;
        transition: all 0.15s ease;
    }

    .f-input-xs:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    .branch-badge-box {
        height: 36px;
        background: #f0fdf4;
        border: 1.5px solid #86efac;
        border-radius: 6px;
        padding: 0 12px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 700;
        color: #166534;
    }

    /* Summary Stats Grid (Left Column Bottom) */
    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin: 12px 0;
    }

    .stat-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 12px;
        text-align: center;
    }

    .stat-box-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.4px;
    }

    .stat-box-val {
        font-size: 18px;
        font-weight: 800;
        color: #1e3a5f;
        margin-top: 2px;
    }

    /* Action Buttons */
    .btn-submit-side {
        background: linear-gradient(135deg, #0d9f6e 0%, #059669 100%);
        color: #ffffff;
        border: none;
        border-radius: 6px;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-shadow: 0 2px 6px rgba(13, 159, 110, 0.25);
        cursor: pointer;
        width: 100%;
        transition: all 0.15s ease;
    }

    .btn-submit-side:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .btn-cancel-side {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        color: #475569;
        border-radius: 6px;
        padding: 8px 14px;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none;
        width: 100%;
        margin-top: 6px;
        transition: all 0.15s ease;
    }

    .btn-cancel-side:hover {
        background: #f1f5f9;
        color: #1e293b;
    }

    /* Right Column: Products Table */
    .products-table-wrapper {
        flex: 1;
        overflow-y: auto;
        border: 1px solid var(--theme-border);
        border-radius: 8px;
        background: #ffffff;
        margin-bottom: 8px;
    }

    .products-table-wrapper::-webkit-scrollbar {
        width: 6px;
    }

    .products-table-wrapper::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .table-side {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .table-side thead th {
        background: #1e3a5f;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 8px 10px;
        position: sticky;
        top: 0;
        z-index: 10;
        border: none;
    }

    .table-side tbody td {
        padding: 6px 8px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        background: #ffffff;
    }

    .table-side tbody tr:hover td {
        background: #f8fafc;
    }

    .row-badge-side {
        width: 22px;
        height: 22px;
        border-radius: 4px;
        background: #e2e8f0;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
    }

    .btn-del-side {
        background: #fff;
        border: 1px solid #fecaca;
        color: #ef4444;
        border-radius: 6px;
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
        font-size: 11px;
    }

    .btn-del-side:hover {
        background: #fee2e2;
        color: #b91c1c;
    }

    .btn-add-side {
        background: #f8fafc;
        border: 1.5px dashed #3b82f6;
        color: #2563eb;
        border-radius: 6px;
        padding: 4px 12px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s ease;
    }

    .btn-add-side:hover {
        background: #eff6ff;
        border-color: #1d4ed8;
        color: #1d4ed8;
    }

    /* Select2 Compact */
    .select2-container--default .select2-selection--single {
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 6px !important;
        height: 36px !important;
        background-color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        padding-left: 10px !important;
        line-height: 34px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px !important;
        right: 8px !important;
    }

    .select2-dropdown {
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 6px !important;
        font-size: 12px !important;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1) !important;
        z-index: 9999 !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #1e3a5f !important;
    }
</style>

<div class="main-content">
    <div class="sr-split-wrapper">
        <div class="container-fluid px-1">

            {{-- Top Compact Bar --}}
            <div class="sr-top-bar">
                <div class="d-flex align-items-center">
                    <h1 class="sr-top-title">
                        <i class="fas fa-boxes-packing" style="color: var(--theme-gold);"></i>
                        Inter-Branch Stock Request
                    </h1>
                </div>
                <div>
                    <a href="{{ route('inter_branch_stock_requests.index') }}" class="btn btn-outline-light btn-sm" style="font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 6px;">
                        <i class="fas fa-list mr-1"></i> Requests List
                    </a>
                </div>
            </div>

            {{-- Compact Alert if any --}}
            @if (session('error') || session('success') || $errors->any())
                <div class="mb-2">
                    @if (session('error'))
                        <div class="alert alert-danger py-1 px-3 mb-1 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #ef4444 !important; border-radius: 6px;">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success py-1 px-3 mb-1 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #10b981 !important; border-radius: 6px;">
                            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger py-1 px-3 mb-1 small border-0 shadow-sm" style="border-left: 4px solid #ef4444 !important; border-radius: 6px;">
                            <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Validation Errors:</strong>
                            <ul class="mb-0 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ══════════════════════════════════════════════════
                 SIDE-BY-SIDE 2-COLUMN FORM
            ══════════════════════════════════════════════════ --}}
            <form action="{{ route('inter_branch_stock_requests.store') }}" method="POST" id="stockRequestForm">
                @csrf

                <div class="row">

                    {{-- ══════════════════════════════════════════
                         LEFT COLUMN (Requisition Info, Routing & Actions)
                    ══════════════════════════════════════════ --}}
                    <div class="col-lg-4 col-md-5 mb-3 mb-md-0">
                        <div class="sr-side-card">
                            <div class="sr-card-heading">
                                <span><i class="fas fa-sliders-h text-primary mr-1"></i> Requisition Info</span>
                                <span class="badge badge-light" style="font-size: 10px; color: #64748b;">Step 1</span>
                            </div>

                            <div style="flex: 1;">
                                {{-- Origin Branch (From) --}}
                                <div class="form-group mb-2">
                                    @if(auth()->user()->hasRole('super admin'))
                                        <label class="f-label-xs" for="from_branch_id">
                                            <i class="fas fa-store text-primary"></i> Origin Branch (From) <span class="text-danger">*</span>
                                        </label>
                                        <select name="from_branch_id" id="from_branch_id" class="form-control select2-custom" required>
                                            <option value="">-- Select Origin Branch --</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ old('from_branch_id') == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name ?? 'Branch #' . $branch->id }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <label class="f-label-xs">
                                            <i class="fas fa-store text-success"></i> Origin Branch (Your Branch)
                                        </label>
                                        <div class="branch-badge-box">
                                            <i class="fas fa-building text-success"></i>
                                            <span>{{ auth()->user()->branch->name ?? 'My Assigned Branch' }}</span>
                                        </div>
                                        <input type="hidden" name="from_branch_id" id="from_branch_id" value="{{ auth()->user()->branch_id }}">
                                    @endif
                                </div>

                                {{-- Supplier Branch (To) --}}
                                <div class="form-group mb-2">
                                    <label class="f-label-xs" for="to_branch_id">
                                        <i class="fas fa-truck-moving text-info"></i> Supplier Branch (To) <span class="text-danger">*</span>
                                    </label>
                                    <select name="to_branch_id" id="to_branch_id" class="form-control select2-custom" required>
                                        <option value="">-- Select Supplier Branch --</option>
                                        @foreach ($branches as $branch)
                                            @if(!auth()->user()->hasRole('super admin') && $branch->id == auth()->user()->branch_id)
                                                @continue
                                            @endif
                                            <option value="{{ $branch->id }}" {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name ?? 'Branch #' . $branch->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Remarks / Instructions --}}
                                <div class="form-group mb-2">
                                    <label class="f-label-xs" for="remarks">
                                        <i class="fas fa-comment-dots text-secondary"></i> Remarks & Dispatch Notes
                                    </label>
                                    <textarea name="remarks" id="remarks" rows="2" class="form-control f-input-xs" placeholder="Urgent priority, dispatch instructions... (Optional)" style="height: auto !important; resize: none;"></textarea>
                                </div>

                                {{-- Live Statistics Grid --}}
                                <div class="stats-grid">
                                    <div class="stat-box">
                                        <div class="stat-box-label">Total Items</div>
                                        <div class="stat-box-val" id="total_items_count">1</div>
                                    </div>
                                    <div class="stat-box">
                                        <div class="stat-box-label">Total Quantity</div>
                                        <div class="stat-box-val text-success" id="total_qty_count">1</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="pt-2 border-top">
                                <button type="submit" class="btn-submit-side" id="submitBtn">
                                    <i class="fas fa-paper-plane"></i> Submit Stock Request
                                </button>
                                <a href="{{ route('inter_branch_stock_requests.index') }}" class="btn-cancel-side">
                                    <i class="fas fa-times"></i> Cancel Requisition
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- ══════════════════════════════════════════
                         RIGHT COLUMN (Products Table & Dynamic Rows)
                    ══════════════════════════════════════════ --}}
                    <div class="col-lg-8 col-md-7">
                        <div class="sr-side-card">
                            <div class="sr-card-heading">
                                <div class="d-flex align-items-center" style="gap: 8px;">
                                    <span><i class="fas fa-box-open text-primary mr-1"></i> Requested Products</span>
                                    <span id="loadingProducts" style="display: none;">
                                        <span class="badge badge-primary py-1 px-2" style="background:#e0e7ff; color:#3730a3; font-size:10px;">
                                            <i class="fas fa-spinner fa-spin mr-1"></i> Syncing...
                                        </span>
                                    </span>
                                </div>
                                <button type="button" class="btn-add-side" id="add_row_btn">
                                    <i class="fas fa-plus"></i> Add Product Row
                                </button>
                            </div>

                            <div class="products-table-wrapper">
                                <table class="table-side" id="product_table">
                                    <thead>
                                        <tr>
                                            <th style="width: 6%; text-align: center;">#</th>
                                            <th style="width: 64%;">Product Name / Code</th>
                                            <th style="width: 22%;">Requested Qty</th>
                                            <th style="width: 8%; text-align: center;">Del</th>
                                        </tr>
                                    </thead>
                                    <tbody id="product_body">
                                        <tr class="product_row">
                                            <td style="text-align: center;">
                                                <span class="row-badge-side">1</span>
                                            </td>
                                            <td>
                                                <select name="product_id[]" class="form-control product-select" required style="width: 100%;">
                                                    <option value="">-- Select Supplier Branch First --</option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="number" name="quantity[]" class="form-control f-input-xs quantity-input" required min="1" value="1" placeholder="Qty">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text py-0 px-2" style="height: 36px; font-size: 11px; font-weight: 700; background: #f8fafc; border-color: #cbd5e1; color: #64748b;">
                                                            Qty
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="text-align: center;">
                                                <button type="button" class="btn-del-side remove-row" title="Remove">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 11px; padding-top: 4px;">
                                <span><i class="fas fa-info-circle text-info"></i> Products are sorted by in-stock availability</span>
                                <span>Press <strong>+ Add Product Row</strong> to append more</span>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {

    // 1. Initialize static Select2
    $('.select2-custom').select2({
        width: '100%'
    });

    // 2. Initialize Product Select2
    function initProductSelect2(selector) {
        $(selector).select2({
            placeholder: "-- Select Product --",
            width: '100%',
            allowClear: true
        });
    }

    initProductSelect2('.product-select');

    let branchProductsOptions = '<option value="">-- Select Supplier Branch First --</option>';

    // 3. Handle Supplier Branch Change
    $('#to_branch_id').on('change', function() {
        const branchId = $(this).val();
        const fromBranchId = $('#from_branch_id').val();

        if (branchId && fromBranchId && branchId === fromBranchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Branch Selection',
                text: 'The supplier branch cannot be the same as the origin branch.',
                confirmButtonColor: '#1e3a5f'
            });
            $(this).val('').trigger('change');
            return;
        }

        if (!branchId) {
            branchProductsOptions = '<option value="">-- Select Supplier Branch First --</option>';
            $('.product-select').html(branchProductsOptions).trigger('change').prop('disabled', true);
            return;
        }

        $('#loadingProducts').fadeIn(150);

        const apiUrl = "{{ url('api/branch-products') }}/" + branchId;

        $.ajax({
            url: apiUrl,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#loadingProducts').fadeOut(150);

                let productHtml = '<option value="">-- Choose Product --</option>';

                if (response.products && response.products.length > 0) {
                    response.products.forEach(function(product) {
                        const code = product.item_code ? ` [${product.item_code}]` : '';
                        const statusBadge = product.is_primary ? ' (In-Stock)' : ' (Catalog)';
                        productHtml += `<option value="${product.id}">${product.item_name}${code}${statusBadge}</option>`;
                    });
                } else {
                    productHtml = '<option value="">No products in this branch</option>';
                }

                branchProductsOptions = productHtml;

                $('.product-select').each(function() {
                    $(this).html(branchProductsOptions).prop('disabled', false).trigger('change');
                });
            },
            error: function(error) {
                $('#loadingProducts').fadeOut(150);
                console.error('Error fetching catalog:', error);
                branchProductsOptions = '<option value="">Error loading products</option>';
                $('.product-select').html(branchProductsOptions).prop('disabled', true).trigger('change');
            }
        });
    });

    // 4. Super admin source branch change
    $('#from_branch_id').on('change', function() {
        const fromBranchId = $(this).val();
        const toBranchId = $('#to_branch_id').val();

        if (fromBranchId && toBranchId && fromBranchId === toBranchId) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Branch',
                text: 'Origin branch cannot be the same as supplier branch.',
                confirmButtonColor: '#1e3a5f'
            });
            $('#to_branch_id').val('').trigger('change');
        }
    });

    // 5. Add Row
    $('#add_row_btn').on('click', function() {
        const toBranchId = $('#to_branch_id').val();

        if (!toBranchId) {
            Swal.fire({
                icon: 'info',
                title: 'Select Supplier Branch First',
                text: 'Please select the supplier branch before adding items.',
                confirmButtonColor: '#1e3a5f'
            });
            $('#to_branch_id').focus();
            return;
        }

        const rowCount = $('#product_body tr').length + 1;

        const rowHtml = `
            <tr class="product_row">
                <td style="text-align: center;">
                    <span class="row-badge-side">${rowCount}</span>
                </td>
                <td>
                    <select name="product_id[]" class="form-control product-select" required style="width: 100%;">
                        ${branchProductsOptions}
                    </select>
                </td>
                <td>
                    <div class="input-group">
                        <input type="number" name="quantity[]" class="form-control f-input-xs quantity-input" required min="1" value="1" placeholder="Qty">
                        <div class="input-group-append">
                            <span class="input-group-text py-0 px-2" style="height: 36px; font-size: 11px; font-weight: 700; background: #f8fafc; border-color: #cbd5e1; color: #64748b;">
                                Qty
                            </span>
                        </div>
                    </div>
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn-del-side remove-row" title="Remove">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;

        const $newRow = $(rowHtml);
        $('#product_body').append($newRow);
        initProductSelect2($newRow.find('.product-select'));
        updateSummary();

        // Auto-scroll table container to the new row
        $('.products-table-wrapper').animate({ scrollTop: $('.products-table-wrapper')[0].scrollHeight }, 200);
    });

    // 6. Remove Row
    $(document).on('click', '.remove-row', function() {
        if ($('#product_body tr').length === 1) {
            const $onlyRow = $('#product_body tr:first');
            $onlyRow.find('.product-select').val('').trigger('change');
            $onlyRow.find('.quantity-input').val('1');
            updateSummary();
            return;
        }

        $(this).closest('tr').remove();
        reindexRows();
        updateSummary();
    });

    function reindexRows() {
        $('#product_body tr').each(function(index) {
            $(this).find('.row-badge-side').text(index + 1);
        });
    }

    function updateSummary() {
        const rowCount = $('#product_body tr').length;
        let totalQty = 0;

        $('.quantity-input').each(function() {
            const val = parseFloat($(this).val()) || 0;
            totalQty += val;
        });

        $('#total_items_count').text(rowCount);
        $('#total_qty_count').text(totalQty);
    }

    $(document).on('input change', '.quantity-input', function() {
        updateSummary();
    });

    // 7. Form Submit Validation
    $('#stockRequestForm').on('submit', function(e) {
        const toBranch = $('#to_branch_id').val();
        if (!toBranch) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Required Field Missing',
                text: 'Please select a supplier branch.',
                confirmButtonColor: '#1e3a5f'
            });
            return false;
        }

        let hasEmptyProduct = false;
        $('.product-select').each(function() {
            if (!$(this).val()) {
                hasEmptyProduct = true;
            }
        });

        if (hasEmptyProduct) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Missing Product Selection',
                text: 'Please select a product in every row.',
                confirmButtonColor: '#1e3a5f'
            });
            return false;
        }

        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Submitting Request...');
    });

    if ($('#to_branch_id').val()) {
        $('#to_branch_id').trigger('change');
    }
});
</script>
@endsection
