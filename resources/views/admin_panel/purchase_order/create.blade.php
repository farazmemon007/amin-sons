@extends('admin_panel.layout.app')

@section('content')
@can('purchase.order.create')

<style>
    .main-content { background-color: #f5f6fa; min-height: 100vh; }
    .card { border-radius: 12px; border: 1px solid #e0e3ea; box-shadow: 0 4px 12px rgba(0,0,0,.03); transition: 0.3s; }
    .card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.06); }
    .card-header { background: linear-gradient(90deg,#f8f9fc,#f1f5f9); font-weight: 700; font-size: 16px; color: #1e293b; border-bottom: 1px solid #e2e8f0; }
    
    label { font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 5px; }
    .form-control, .form-select { border-radius: 8px; font-size: 14px; border: 1.5px solid #e2e8f0; padding: 10px 12px; background: #fff; transition: 0.2s; }
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); outline: none; }
    
    .table thead th { background: #1e293b; color: #fff; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border: none; padding: 15px; }
    .table tbody td { vertical-align: middle; padding: 12px; background: #fff; border-bottom: 1px solid #f1f5f9; }
    
    .btn-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; padding: 10px 24px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2); }
    .btn-success { background: linear-gradient(135deg, #10b981, #059669); border: none; font-weight: 700; }
    
    /* Color Breakdown Styles */
    .color-breakdown-row td { background: #fdfdfd !important; padding: 0 !important; border-bottom: 2px solid #eef2f7 !important; }
    .breakdown-container { padding: 15px 25px; background: #fafafa; border-left: 4px solid #3b82f6; margin: 5px 0; }
    .breakdown-title { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; }
    .breakdown-item { display: flex; gap: 10px; align-items: center; margin-bottom: 8px; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    .breakdown-qty { width: 120px; }
    .btn-remove-color { color: #ef4444; cursor: pointer; padding: 5px; transition: 0.2s; }
    .btn-remove-color:hover { color: #b91c1c; transform: scale(1.1); }
    .qty-readonly { background-color: #f1f5f9 !important; font-weight: 700; color: #1e293b; }

    .po-status-tag { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; background: #dcfce7; color: #166534; }
</style>

<div class="main-content pb-5">
    <div class="container-fluid px-4 pt-4">
        
        <form action="{{ route('purchase_orders.store') }}" method="POST" id="poForm">
            @csrf

            {{-- Error Display --}}
            @if ($errors->any())
                <div class="alert alert-danger mb-4 shadow-sm border-0" style="border-left: 4px solid #ef4444;">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        <strong class="text-uppercase small">Validation Errors Found</strong>
                    </div>
                    <ul class="mb-0 small fw-bold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger mb-4 shadow-sm border-0" style="border-left: 4px solid #ef4444;">
                    <i class="fa fa-times-circle me-2"></i> {{ session('error') }}
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h3 class="fw-900 mb-1" style="letter-spacing: -0.5px; color: #1e293b;">New Purchase Order</h3>
                    <div class="d-flex align-items-center gap-2">
                        <span class="po-status-tag">Draft Mode</span>
                        <span class="text-muted small">Create an official procurement request for your vendor</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('purchase_orders.index') }}" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="fa fa-arrow-left me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fa fa-check-circle me-1"></i> GENERATE PO
                    </button>
                </div>
            </div>

            <div class="row g-4">
                <!-- Order Details -->
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header"><i class="fa fa-info-circle me-2"></i>Order Specification</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Order Date <span class="text-danger">*</span></label>
                                    <input type="date" name="order_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Expected Date <span class="text-danger">*</span></label>
                                    <input type="date" name="expected_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>PO Number <small class="text-primary">(Auto)</small></label>
                                    <input type="text" id="po_number" value="{{ $nextPO }}" class="form-control fw-bold text-primary" readonly style="background-color: #f0f7ff; border-color: #bfdbfe;">
                                </div>
                                @if($isSuperAdmin)
                                    <div class="col-md-4 mb-3">
                                        <label>Target Branch <span class="text-danger">*</span></label>
                                        <select name="branch_id" id="branch_select" class="form-select" required>
                                            @foreach($branches as $b)
                                                <option value="{{ $b->id }}" @selected($b->id == $currentBranch)>{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <input type="hidden" name="branch_id" id="branch_select" value="{{ $currentBranch }}">
                                @endif
                                <div class="col-md-4 mb-3">
                                    <label>Target Warehouse <span class="text-danger">*</span></label>
                                    <select name="warehouse_id" id="warehouse_select" class="form-select">
                                        <option value="">-- Select Warehouse --</option>
                                        @foreach($warehouses as $w)
                                            <option value="{{ $w->id }}">{{ $w->warehouse_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label>Procurement Note</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="Describe any specific quality or delivery instructions..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vendor Selection -->
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header"><i class="fa fa-truck me-2"></i>Vendor Intelligence</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label>Select Primary Vendor <span class="text-danger">*</span></label>
                                <select name="vendor_id" id="vendor_select" class="form-select select2" required>
                                    <option value="">-- Search Vendor --</option>
                                    @foreach($vendors as $v)
                                        @php
                                            $company = is_array($v->company_names) ? implode(', ', $v->company_names) : ($v->company_names ?? 'N/A');
                                        @endphp
                                        <option value="{{ $v->id }}" data-phone="{{ $v->phone }}" data-address="{{ $v->address }}" data-company="{{ $company }}">{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="vendor_details" class="mt-3 p-3 rounded" style="background: #f8fafc; border: 1.5px dashed #e2e8f0; display: none;">
                                <div class="mb-2">
                                    <div class="small text-muted text-uppercase fw-800" style="font-size: 9px;">Contact No</div>
                                    <div id="vendor_phone_txt" class="fw-bold text-slate-700">-</div>
                                </div>
                                <div class="mb-2">
                                    <div class="small text-muted text-uppercase fw-800" style="font-size: 9px;">Company</div>
                                    <div id="vendor_company_txt" class="fw-bold text-primary" style="font-size: 13px;">-</div>
                                </div>
                                <div>
                                    <div class="small text-muted text-uppercase fw-800" style="font-size: 9px;">Warehouse Address</div>
                                    <div id="vendor_address_txt" class="fw-bold text-slate-700" style="font-size: 13px;">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Table -->
            <div class="card mt-4 border-0 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center" style="background: #1e293b; color: #fff;">
                    <span><i class="fa fa-box-open me-2"></i>Line Items Allocation</span>
                    <button type="button" class="btn btn-sm btn-success shadow-sm" id="btnAddRow">
                        <i class="fa fa-plus-circle me-1"></i> ADD NEW PRODUCT
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th style="width:350px">Product Specification</th>
                                    <th style="width:140px">Item Code</th>
                                    <th style="width:140px">Brand</th>
                                    <th style="width:140px" class="text-end">Est. Unit Price</th>
                                    <th style="width:150px" class="text-center">Total Quantity</th>
                                    <th style="width:160px" class="text-end">Line Valuation</th>
                                    <th style="width:60px"></th>
                                </tr>
                            </thead>
                            <tbody id="poItems">
                                <!-- JS will render rows -->
                            </tbody>
                            <tfoot style="background: #f8fafc;">
                                <tr>
                                    <td colspan="4" class="text-end fw-900 py-3" style="font-size: 16px; color: #475569;">Total Order Valuation:</td>
                                    <td class="text-end py-3">
                                        <span id="grandTotal" class="fw-900 text-primary" style="font-size: 22px;">0.00</span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-5 text-center">
                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-lg">
                    <i class="fa fa-paper-plane me-2"></i> CONFIRM & AUTHORIZE PO
                </button>
            </div>
        </form>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        function initProductSelect2(selector) {
            $(selector).select2({
                ajax: {
                    url: '{{ route("search-products") }}', 
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term, vendor_id: $('#vendor_select').val() };
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(p => ({
                                id: p.id,
                                text: p.item_name + ' [' + (p.item_code || 'N/A') + ']',
                                item_code: p.item_code,
                                brand_name: p.brand_name,
                                price: p.price
                            }))
                        };
                    },
                    cache: true
                },
                placeholder: 'Search product...',
                width: '100%'
            }).on('select2:opening', function(e) {
                if (!$('#vendor_select').val()) {
                    e.preventDefault();
                    Swal.fire('Vendor Required', 'Please select a vendor first to filter authorized products.', 'info');
                }
            });
        }

        let rowIndex = 0;

        function addNewRow() {
            const idx = rowIndex++;
            const rowHtml = `
                <tr class="osm-row" data-index="${idx}">
                    <td>
                        <select name="items[${idx}][product_id]" class="form-select product-select" required>
                            <option value="">Search product...</option>
                        </select>
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm btn-add-breakdown" style="font-size: 10px; font-weight: 700;">
                                <i class="fa fa-palette me-1"></i> COLOR BREAKDOWN
                            </button>
                        </div>
                    </td>
                    <td><input type="text" class="form-control item_code" readonly style="background: #f8fafc;"></td>
                    <td><input type="text" class="form-control brand_name" readonly style="background: #f8fafc;"></td>
                    <td><input type="number" step="0.01" name="items[${idx}][price]" class="form-control text-end price" value="0" min="0" required></td>
                    <td>
                        <input type="number" step="0.01" name="items[${idx}][total_qty]" class="form-control text-center total-qty" value="1" min="0.01" required>
                    </td>
                    <td><input type="text" class="form-control text-end line_total fw-bold" readonly value="0.00" style="background: #f1f5f9; color: #1e293b;"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-link text-danger remove-row"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
                <tr class="color-breakdown-row d-none" id="breakdown_row_${idx}">
                    <td colspan="6">
                        <div class="breakdown-container">
                            <div class="breakdown-title">
                                <span><i class="fa fa-layer-group me-1"></i> Color Specification / Variant Split</span>
                                <button type="button" class="btn btn-success btn-xs btn-add-color-item" data-row-index="${idx}" style="font-size: 9px; padding: 2px 8px;">
                                    <i class="fa fa-plus me-1"></i> ADD COLOR
                                </button>
                            </div>
                            <div class="breakdown-list" id="breakdown_list_${idx}"></div>
                        </div>
                    </td>
                </tr>`;
            
            const $row = $(rowHtml);
            $('#poItems').append($row);
            initProductSelect2($row.find('.product-select'));
            calculateTotals();
        }

        // Initialize first row
        addNewRow();

        $('#btnAddRow').on('click', addNewRow);

        $(document).on('click', '.btn-add-breakdown', function() {
            const idx = $(this).closest('tr').data('index');
            $(`#breakdown_row_${idx}`).toggleClass('d-none');
            if (!$(`#breakdown_row_${idx}`).hasClass('d-none') && $(`#breakdown_list_${idx}`).children().length === 0) {
                addColorBreakdownItem(idx);
            }
            updateQtyLock(idx);
        });

        function addColorBreakdownItem(rowIdx, colorVal = '', qtyVal = '') {
            const colorHtml = `
                <div class="breakdown-item">
                    <div style="flex: 1;">
                        <select name="items[${rowIdx}][colors][]" class="form-select color-select-dynamic">
                            <option value=""></option>
                            @foreach($existingColors as $color)
                                <option value="{{ $color }}">{{ $color }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="breakdown-qty">
                        <input type="number" step="0.01" name="items[${rowIdx}][color_qtys][]" class="form-control color_qty" placeholder="Qty" value="${qtyVal}" required>
                    </div>
                    <div class="btn-remove-color"><i class="fa fa-times-circle"></i></div>
                </div>`;
            
            const $list = $(`#breakdown_list_${rowIdx}`);
            $list.append(colorHtml);
            $list.find('.color-select-dynamic').last().select2({ 
                tags: true, 
                multiple: false,
                placeholder: "Select or type color", 
                allowClear: true, 
                width: '100%' 
            });
            updateQtyLock(rowIdx);
        }

        $(document).on('click', '.btn-add-color-item', function() {
            addColorBreakdownItem($(this).data('row-index'));
        });

        $(document).on('click', '.btn-remove-color', function() {
            const rowIdx = $(this).closest('.breakdown-list').attr('id').split('_').pop();
            $(this).closest('.breakdown-item').remove();
            updateQtyLock(rowIdx);
        });

        $(document).on('input', '.color_qty', function() {
            const rowIdx = $(this).closest('.breakdown-list').attr('id').split('_').pop();
            updateQtyLock(rowIdx);
        });

        function updateQtyLock(rowIdx) {
            const $mainRow = $(`tr[data-index="${rowIdx}"]`);
            const $qtyInput = $mainRow.find('.total-qty');
            const $list = $(`#breakdown_list_${rowIdx}`);
            let sum = 0;
            let hasBreakdown = $list.children().length > 0 && !$(`#breakdown_row_${rowIdx}`).hasClass('d-none');
            
            if (hasBreakdown) {
                $list.find('.color_qty').each(function() { sum += parseFloat($(this).val()) || 0; });
                $qtyInput.val(sum).addClass('qty-readonly').prop('readonly', true);
            } else {
                $qtyInput.removeClass('qty-readonly').prop('readonly', false);
            }
            calculateTotals();
        }

        // Branch & Vendor Logic
        $('#branch_select').on('change', function() {
            const branchId = $(this).val();
            if (!branchId) return;

            $.ajax({
                url: "{{ url('purchase-orders/branch') }}/" + branchId + "/next-po",
                method: 'GET',
                success: function(res) { $('#po_number').val(res.next_po); }
            });

            // Fetch Warehouses
            $.ajax({
                url: "{{ route('warehouses-by-branch') }}",
                method: 'GET',
                data: { branch_id: branchId },
                success: function(res) {
                    const whSelect = $('#warehouse_select');
                    whSelect.empty().append('<option value="">-- Select Warehouse --</option>');
                    res.forEach(w => {
                        whSelect.append(`<option value="${w.id}">${w.warehouse_name}</option>`);
                    });
                }
            });

            $.ajax({
                url: "{{ url('purchase-orders/branch') }}/" + branchId + "/vendors",
                method: 'GET',
                success: function(vendors) {
                    const vendorSelect = $('#vendor_select');
                    // Destroy Select2 first, then repopulate, then reinit
                    if (vendorSelect.hasClass('select2-hidden-accessible')) {
                        vendorSelect.select2('destroy');
                    }
                    vendorSelect.empty().append('<option value="">-- Search Vendor --</option>');
                    vendors.forEach(v => {
                        const company = v.company_names
                            ? (Array.isArray(v.company_names) ? v.company_names.join(', ') : v.company_names)
                            : '';
                        vendorSelect.append(
                            $('<option>').val(v.id)
                                .text(v.name)
                                .attr('data-phone', v.phone || '')
                                .attr('data-address', v.address || '')
                                .attr('data-company', company)
                        );
                    });
                    vendorSelect.select2({ width: '100%' });
                    $('#vendor_details').hide();
                    vendorSelect.trigger('change');
                }
            });
        });

        $('#vendor_select').on('change', function() {
            const opt = $(this).find('option:selected');
            if (opt.val()) {
                $('#vendor_phone_txt').text(opt.data('phone') || 'N/A');
                $('#vendor_company_txt').text(opt.data('company') || 'N/A');
                $('#vendor_address_txt').text(opt.data('address') || 'N/A');
                $('#vendor_details').fadeIn();
            } else {
                $('#vendor_details').hide();
            }
        });

        // Calculations
        $(document).on('select2:select', '.product-select', function(e) {
            const data = e.params.data;
            const $row = $(this).closest('tr');
            $row.find('.item_code').val(data.item_code || '');
            $row.find('.brand_name').val(data.brand_name || '');
            $row.find('.price').val(data.price || 0);
            calculateTotals();
        });

        $(document).on('click', '.remove-row', function() {
            const idx = $(this).closest('tr').data('index');
            $(this).closest('tr').remove();
            $(`#breakdown_row_${idx}`).remove();
            calculateTotals();
        });

        $(document).on('input', '.total-qty, .price', function() {
            calculateTotals();
        });

        function calculateTotals() {
            let grandTotal = 0;
            $('.osm-row').each(function() {
                const qty = parseFloat($(this).find('.total-qty').val()) || 0;
                const price = parseFloat($(this).find('.price').val()) || 0;
                const total = qty * price;
                $(this).find('.line_total').val(total.toFixed(2));
                grandTotal += total;
            });
            $('#grandTotal').text(grandTotal.toFixed(2));
        }

        $('#poForm').on('submit', function(e) {
            if ($('.osm-row').length === 0) {
                e.preventDefault();
                Swal.fire('Order Empty', 'Please add at least one product line item.', 'warning');
                return false;
            }
            $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing PO...');
        });
    });
</script>

@endcan
@endsection
