@extends('admin_panel.layout.app')

@section('content')
    <style>
        .osm-wrap { font-family:'Inter',sans-serif; background:#f1f5f9; min-height:100vh; padding:1.5rem; }
        .osm-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem; }
        .osm-title  { font-size:20px;font-weight:800;color:#1e293b; }
        .osm-title small { font-size:12px;font-weight:500;color:#64748b;margin-left:8px; }

        .branch-card { background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:1.2rem;margin-bottom:1.5rem;box-shadow:0 1px 3px rgba(0,0,0,.06); }
        .branch-card h6 { font-weight:700;font-size:14px;color:#1e293b;margin-bottom:1rem; border-bottom:1px solid #eef2f7; padding-bottom:10px; }

        .table-card { background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(0,0,0,.07);overflow:hidden; }
        .osm-thead { background:linear-gradient(135deg,#1e293b,#334155);color:#fff; }
        .osm-thead th { padding:12px 10px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;border:none;white-space:nowrap; }

        .osm-row td { vertical-align:middle;padding:10px 8px;border-bottom:1px solid #f1f5f9;background:#fff; }
        .osm-row:hover td { background:#fafbff; }

        .fi { border:1.5px solid #e2e8f0;border-radius:7px;padding:8px 10px;font-size:13px;width:100%;transition:.2s;background:#f8fafc; }
        .fi:focus { border-color:#6366f1;background:#fff;outline:none;box-shadow:0 0 0 3px rgba(99,102,241,.1); }
        .fi-label { font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:3px; }

        /* Select2 */
        .select2-container--default .select2-selection--single { border:1.5px solid #e2e8f0;border-radius:7px;height:38px;background:#f8fafc; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height:36px;font-size:13px;color:#1e293b;padding-left:10px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height:36px; }
        .select2-container--default.select2-container--focus .select2-selection--single { border-color:#6366f1;background:#fff;box-shadow:0 0 0 3px rgba(99,102,241,.1); }

        .osm-footer { background:#f8fafc;border-top:1px solid #e2e8f0;padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;border-radius:0 0 14px 14px; }
        .btn-add-row { background:#fff;border:2px dashed #6366f1;color:#6366f1;border-radius:9px;padding:9px 20px;font-size:13px;font-weight:700;cursor:pointer;transition:.2s; }
        .btn-add-row:hover { background:#eef2ff; }
        .btn-save-all { background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border:none;border-radius:9px;padding:10px 30px;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(99,102,241,.3);transition:.2s; }
        .btn-save-all:hover { transform:translateY(-1px); }
        
        .btn-del-row { background:#fee2e2;color:#dc2626;border:none;border-radius:7px;padding:6px 10px;cursor:pointer;font-size:13px;font-weight:700;transition:.2s; }
        .btn-del-row:hover { background:#dc2626;color:#fff; }

        /* Color Breakdown Styles */
        .color-breakdown-row td { background: #fdfdfd !important; padding: 0 !important; border-bottom: 2px solid #eef2f7 !important; }
        .breakdown-container { padding: 15px 25px; background: #fafafa; border-left: 4px solid #6366f1; margin: 5px 0; }
        .breakdown-title { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; }
        .breakdown-item { display: flex; gap: 10px; align-items: center; margin-bottom: 8px; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
        .breakdown-qty { width: 100px; }
        .btn-remove-color { color: #ef4444; cursor: pointer; padding: 5px; transition: 0.2s; }
        .btn-remove-color:hover { color: #b91c1c; transform: scale(1.1); }
        .qty-readonly { background-color: #f1f5f9 !important; font-weight: 700; color: #475569; }
        
        .unit-price-col, .unit-price-cell, .line-total-col, .line-total-cell {
            display: none !important;
        }
    </style>

    <div class="osm-wrap">

        <div class="osm-header">
            <div>
                <div class="osm-title">📦 Edit Inward Gatepass <small>#{{ $gatepass->id }}</small></div>
                <div style="font-size:12px;color:#94a3b8;margin-top:2px;">Update & manage stock entries for this gatepass</div>
            </div>
            <a href="{{ route('InwardGatepass.home') }}" class="btn btn-sm btn-outline-secondary">← Back</a>
        </div>

        <div class="gp-body">
            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-2">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('InwardGatepass.update',$gatepass->id) }}" method="POST" id="gatepassForm">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-5">
                        <div class="branch-card h-100 mb-0">
                            <h6>📑 Bill / Gatepass Info</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="fi-label">Date</div>
                                    <input type="date" name="gatepass_date" class="fi" value="{{ old('gatepass_date', $gatepass->gatepass_date) }}">
                                </div>
                                @if($isSuperAdmin)
                                    <div class="col-md-6">
                                        <div class="fi-label">Branch</div>
                                        <select name="branch_id" class="form-select select2">
                                            @foreach ($branches as $item)
                                                <option value="{{ $item->id }}" {{ $gatepass->branch_id==$item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <input type="hidden" name="branch_id" value="{{ $gatepass->branch_id }}">
                                @endif
                                <div class="col-md-6">
                                    <div class="fi-label">Warehouse</div>
                                    <select name="warehouse_id" class="form-select select2">
                                        @foreach ($warehouses as $item)
                                            <option value="{{ $item->id }}" {{ $gatepass->warehouse_id==$item->id ? 'selected' : '' }}>{{ $item->warehouse_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="fi-label">Delivery Challan No</div>
                                    <input type="text" name="delivery_challan_no" class="fi" value="{{ old('delivery_challan_no', $gatepass->delivery_challan_no) }}">
                                </div>
                                <div class="col-md-6">
                                    <div class="fi-label">Bilty No</div>
                                    <input type="text" name="bilty_no" class="fi" value="{{ old('bilty_no', $gatepass->bilty_no) }}">
                                </div>
                                <div class="col-md-6">
                                    <div class="fi-label">Freight Charges</div>
                                    <input type="number" step="0.01" name="freight_charges" class="fi" value="{{ old('freight_charges', $gatepass->freight_charges) }}">
                                </div>
                                <div class="col-md-6">
                                    <div class="fi-label">Freight Provider (Audit)</div>
                                    <select name="freight_vendor_id" class="form-select select2">
                                        <option value="">Select Transporter/Vendor</option>
                                        @foreach ($vendors as $item)
                                            <option value="{{ $item->id }}" {{ old('freight_vendor_id', $gatepass->freight_vendor_id) == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <div class="fi-label">Note</div>
                                    <input type="text" name="note" class="fi" value="{{ old('note', $gatepass->note) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="branch-card h-100 mb-0">
                            <h6>🚚 Vendor / Transport Info</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="fi-label">Vendor</div>
                                    <select name="vendor_id" class="form-select select2">
                                        @foreach ($vendors as $item)
                                            <option value="{{ $item->id }}" {{ $gatepass->vendor_id==$item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="fi-label">Transport Name</div>
                                    <input type="text" name="transport_name" class="fi" value="{{ old('transport_name', $gatepass->transport_name) }}">
                                </div>
                                <div class="col-md-4">
                                    <div class="fi-label">Vehicle Type</div>
                                    <input type="text" name="vehicle_type" class="fi" value="{{ old('vehicle_type', $gatepass->vehicle_type) }}">
                                </div>
                                <div class="col-md-4">
                                    <div class="fi-label">Vehicle No</div>
                                    <input type="text" name="vehicle_no" class="fi" value="{{ old('vehicle_no', $gatepass->vehicle_no) }}">
                                </div>
                                <div class="col-md-4">
                                    <div class="fi-label">Dispatch Date</div>
                                    <input type="date" name="dispatch_date" class="fi" value="{{ old('dispatch_date', $gatepass->dispatch_date) }}">
                                </div>
                                <div class="col-md-4">
                                    <div class="fi-label">Driver Name</div>
                                    <input type="text" name="driver_name" class="fi" value="{{ old('driver_name', $gatepass->driver_name) }}">
                                </div>
                                <div class="col-md-8">
                                    <div class="fi-label">Driver Contact No</div>
                                    <input type="text" name="driver_no" class="fi" value="{{ old('driver_no', $gatepass->driver_no) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-card">
                    <table class="table mb-0">
                        <thead class="osm-thead">
                            <tr>
                                <th style="min-width:280px;">Product Specification</th>
                                <th style="min-width:120px;">Item Code</th>
                                <th style="min-width:120px;">Brand</th>
                                <th style="min-width:140px;">Packing Type</th>
                                <th style="min-width:140px;">Unit/Pack</th>
                                <th class="pack-qty-col" style="display:none;">Pack Qty</th>
                                <th class="item-per-piece-col" style="display:none;">Item/Pc</th>
                                <th class="loose-pcs-col" style="display:none;">Loose</th>
                                <th style="min-width:120px;" class="unit-price-col text-end">Unit Price</th>
                                <th style="min-width:100px;" class="text-center">Received Qty</th>
                                <th style="min-width:150px;" class="line-total-col text-end">Line Total</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="gatepassItems">
                            <!-- JS will render rows -->
                        </tbody>
                    </table>
                    <tfoot style="background: #f8fafc; display:none;">
                        <tr>
                            <td colspan="10" class="text-end fw-900 py-3" style="font-size: 14px; color: #475569;">Inward Total Valuation:</td>
                            <td class="text-end py-3">
                                <span id="grandTotal" class="fw-900 text-primary" style="font-size: 18px;">0.00</span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </div>
                <div class="osm-footer">
                    <button type="button" class="btn-add-row" id="addRowBtn">+ Add Product Row</button>
                    <button type="submit" class="btn-save-all">Update Gatepass</button>
                </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
<script>
$(document).ready(function(){

    function initProductSelect2($selector) {
        $selector.select2({
            width: '100%',
            placeholder: 'Search Product...',
            allowClear: true,
            ajax: {
                url: '{{ route("search-products") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term, vendor_id: $('select[name="vendor_id"]').val() };
                },
                processResults: function (data) {
                    return {
                        results: data.map(p => ({
                            id: p.id,
                            text: p.item_name + ' (' + p.item_code + ')',
                            code: p.item_code,
                            brand: p.brand_name || '',
                            unit: p.unit_name
                        }))
                    };
                },
                cache: true
            }
        });
    }

    function checkGlobalColumns() {
        let anyCustomize = false;
        $('.packing-type').each(function() {
            if ($(this).val() === 'Customize') anyCustomize = true;
        });
        if (anyCustomize) {
            $('.pack-qty-col, .item-per-piece-col, .loose-pcs-col').show();
            $('.pack-qty-cell, .item-per-piece-cell, .loose-pcs-cell').show();
        } else {
            $('.pack-qty-col, .item-per-piece-col, .loose-pcs-col').hide();
            $('.pack-qty-cell, .item-per-piece-cell, .loose-pcs-cell').hide();
        }
    }

    let rowIndex = 0;

    function addNewRow(preData = null) {
        const idx = rowIndex++;
        const row = `
            <tr class="osm-row" data-index="${idx}">
                <td>
                    <select name="items[${idx}][product_id]" class="form-select select2 product-select" required>
                        <option value="">Search Product...</option>
                    </select>
                    <div class="mt-2">
                        <button type="button" class="btn btn-outline-info btn-sm btn-add-breakdown" style="font-size: 10px;">
                            <i class="fa fa-palette"></i> Color Breakdown
                        </button>
                    </div>
                </td>
                <td><input type="text" name="items[${idx}][item_code]" class="fi item_code" readonly></td>
                <td><input type="text" name="items[${idx}][brand]" class="fi brand" readonly></td>
                <td>
                    <select name="items[${idx}][packing_type]" class="form-select packing-type" required>
                        <option value="">Select</option>
                        <option value="Standard">Standard</option>
                        <option value="Customize">Customize</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="items[${idx}][unit]" class="fi unit-readonly" style="display:none;" value="Piece" readonly disabled>
                    <div class="unit-select-wrapper">
                        <select name="items[${idx}][unit]" class="form-select select2 unit-select" required>
                            <option value="">Select Unit</option>
                            @foreach($allUnits as $u)
                                <option value="{{ $u->name }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                <td class="pack-qty-cell" style="display:none;"><input type="number" name="items[${idx}][packing_qty]" class="fi packing-qty"></td>
                <td class="item-per-piece-cell" style="display:none;"><input type="number" name="items[${idx}][item_per_piece]" class="fi item-per-piece"></td>
                <td class="loose-pcs-cell" style="display:none;"><input type="number" name="items[${idx}][loose_piece]" class="fi loose-piece"></td>
                <td class="unit-price-cell">
                    <input type="number" step="0.01" name="items[${idx}][unit_price]" class="fi text-end unit_price" value="${preData ? (preData[0].unit_price || 0) : 0}" min="0">
                </td>
                <td>
                    <input type="number" name="items[${idx}][received_qty]" class="fi text-center received-qty" min="0" step="0.01" value="${preData ? (preData[0].qty || 0) : 0}" required>
                </td>
                <td class="line-total-cell">
                    <input type="text" name="items[${idx}][line_total]" class="fi text-end line_total fw-bold" readonly value="0.00" style="background: #f8fafc;">
                </td>
                <td class="text-center"><button type="button" class="btn-del-row remove-row">✕</button></td>
            </tr>
            <tr class="color-breakdown-row d-none" id="breakdown_row_${idx}">
                <td colspan="10">
                    <div class="breakdown-container">
                        <div class="breakdown-title">
                            <i class="fa fa-layer-group"></i> Color Breakdown
                            <button type="button" class="btn btn-success btn-sm btn-add-color-item" data-row-index="${idx}">
                                <i class="fa fa-plus"></i> Add Color
                            </button>
                        </div>
                        <div class="breakdown-list" id="breakdown_list_${idx}"></div>
                    </div>
                </td>
            </tr>`;
        
        $('#gatepassItems').append(row);
        const $tr = $(`tr[data-index="${idx}"]`);
        initProductSelect2($tr.find('.product-select'));
        $tr.find('.unit-select').select2({ width: '100%' });

        if (preData) {
            // preData is an array of items for this product
            const first = preData[0];
            const $prodSelect = $tr.find('.product-select');
            const newOption = new Option(first.product.item_name + ' (' + first.product.item_code + ')', first.product_id, true, true);
            $prodSelect.append(newOption).trigger('change');
            $tr.find('.item_code').val(first.product.item_code);
            $tr.find('.brand').val(first.product.brand ? (first.product.brand.name || '') : '');
            
            $tr.find('.packing-type').val(first.packing_type).trigger('change');
            $tr.find('.unit-select').val(first.unit).trigger('change');
            $tr.find('.unit-readonly').val(first.unit);
            $tr.find('.packing-qty').val(first.packing_qty);
            $tr.find('.item-per-piece').val(first.item_per_piece);
            $tr.find('.loose-piece').val(first.loose_piece);
            $tr.find('.unit_price').val(first.unit_price || 0);
            
            // Handle breakdown
            let hasColors = false;
            preData.forEach(item => {
                if (item.color) {
                    hasColors = true;
                    if ($(`#breakdown_row_${idx}`).hasClass('d-none')) {
                        $(`#breakdown_row_${idx}`).removeClass('d-none');
                    }
                    addColorBreakdownItem(idx, item.color, item.qty);
                }
            });

            if (!hasColors) {
                $tr.find('.received-qty').val(first.qty);
            }
        }

        checkGlobalColumns();
    }

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
                <div class="btn-remove-color"><i class="fa fa-trash"></i></div>
            </div>`;
        
        const $list = $(`#breakdown_list_${rowIdx}`);
        $list.append(colorHtml);
        $list.find('.color-select-dynamic').last().select2({ 
            tags: true, 
            multiple: false,
            placeholder: "Select Color", 
            allowClear: true, 
            width: '100%' 
        });
        
        if (colorVal) {
            const $s = $list.find('.color-select-dynamic').last();
            if ($s.find(`option[value="${colorVal}"]`).length === 0) {
                $s.append(new Option(colorVal, colorVal, true, true)).trigger('change');
            } else {
                $s.val(colorVal).trigger('change');
            }
        }
        updateQtyLock(rowIdx);
    }

    $(document).on('click', '.btn-add-breakdown', function() {
        const idx = $(this).closest('tr').data('index');
        $(`#breakdown_row_${idx}`).toggleClass('d-none');
        if (!$(`#breakdown_row_${idx}`).hasClass('d-none') && $(`#breakdown_list_${idx}`).children().length === 0) {
            addColorBreakdownItem(idx);
        }
        updateQtyLock(idx);
    });

    $(document).on('click', '.btn-add-color-item', function() {
        addColorBreakdownItem($(this).data('row-index'));
    });

    $(document).on('click', '.btn-remove-color', function() {
        const rowIdx = $(this).closest('.breakdown-list').attr('id').split('_').pop();
        $(this).closest('.breakdown-item').remove();
        updateQtyLock(rowIdx);
    });

    $(document).on('input', '.color_qty', function() {
        updateQtyLock($(this).closest('.breakdown-list').attr('id').split('_').pop());
    });

    // ── Financial Calculations ─────────────────────────────────────
    function calculateTotals() {
        let grandTotal = 0;
        $('.osm-row').each(function() {
            const $tr = $(this);
            const price = parseFloat($tr.find('.unit_price').val()) || 0;
            const qty = parseFloat($tr.find('.received-qty').val()) || 0;
            const lineTotal = price * qty;
            $tr.find('.line_total').val(lineTotal.toFixed(2));
            grandTotal += lineTotal;
        });
        $('#grandTotal').text(grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2}));
    }

    $(document).on('keyup change', '.unit_price, .received-qty', function() {
        calculateTotals();
    });

    $(document).on('select2:select', '.product-select', function(e) {
        const data = e.params.data;
        const $tr  = $(this).closest('tr');
        if (data) {
            $tr.find('.item_code').val(data.code);
            $tr.find('.brand').val(data.brand);
            if (data.price) $tr.find('.unit_price').val(data.price);
            if (data.unit) {
                $tr.find('.unit-select').val(data.unit).trigger('change');
                $tr.find('.unit-readonly').val(data.unit);
            }
            calculateTotals();
        }
    });

    function calculateQtyFromPacking(tr) {
        var packingType = tr.find('.packing-type').val();
        var packQty = parseFloat(tr.find('.packing-qty').val()) || 0;
        var itemPerPiece = parseFloat(tr.find('.item-per-piece').val()) || 0;
        var loosePcs = parseFloat(tr.find('.loose-piece').val()) || 0;
        if (packingType === 'Customize') {
            var qty = (packQty * itemPerPiece) + loosePcs;
            tr.find('.received-qty').val(qty);
            calculateTotals();
        }
    }

    $(document).on('keyup change', '.packing-qty, .item-per-piece, .loose-piece', function() {
        calculateQtyFromPacking($(this).closest('tr'));
    });

    $(document).on('change', '.packing-type', function() {
        var tr = $(this).closest('tr');
        var val = $(this).val();
        if (val === 'Customize') {
            tr.find('.pack-qty-cell, .item-per-piece-cell, .loose-pcs-cell').show();
            tr.find('.unit-readonly').hide().prop('disabled', true);
            tr.find('.unit-select-wrapper').show();
            tr.find('.unit-select').prop('disabled', false);
        } else {
            tr.find('.pack-qty-cell, .item-per-piece-cell, .loose-pcs-cell').hide();
            tr.find('.unit-readonly').show().prop('disabled', false).css('background', '#fff');
            tr.find('.unit-select-wrapper').hide();
            tr.find('.unit-select').prop('disabled', true);
        }
        checkGlobalColumns();
        calculateQtyFromPacking(tr);
    });

    $(document).on('click', '.remove-row', function() {
        const idx = $(this).closest('tr').data('index');
        $(this).closest('tr').remove();
        $(`#breakdown_row_${idx}`).remove();
        checkGlobalColumns();
        calculateTotals();
    });

    function updateQtyLock(rowIdx) {
        const $mainRow = $(`tr[data-index="${rowIdx}"]`);
        const $qtyInput = $mainRow.find('.received-qty');
        const $list = $(`#breakdown_list_${rowIdx}`);
        let sum = 0;
        let hasB = $list.children().length > 0 && !$(`#breakdown_row_${rowIdx}`).hasClass('d-none');
        if (hasB) {
            $list.find('.color_qty').each(function() { sum += parseFloat($(this).val()) || 0; });
            $qtyInput.val(sum).addClass('qty-readonly').prop('readonly', true);
        } else {
            $qtyInput.removeClass('qty-readonly').prop('readonly', false);
        }
        calculateTotals();
    }

    // Pre-load
    const rawItems = @json($gatepass->items);
    const grouped = {};
    rawItems.forEach(item => {
        const key = item.product_id + '_' + item.packing_type + '_' + (item.unit || '');
        if (!grouped[key]) grouped[key] = [];
        grouped[key].push(item);
    });

    Object.values(grouped).forEach(items => {
        addNewRow(items);
    });
    calculateTotals();

    $('#addRowBtn').on('click', () => addNewRow());

    $('#gatepassForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');
    });
});
</script>
@endsection
