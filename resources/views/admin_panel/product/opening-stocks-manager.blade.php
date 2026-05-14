@extends('admin_panel.layout.app')

@section('content')
@can('product.edit')

{{-- Select2 CSS only (jQuery already in layout) --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<style>
    .osm-wrap { font-family:'Inter',sans-serif; background:#f1f5f9; min-height:100vh; padding:1.5rem; }
    .osm-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem; }
    .osm-title  { font-size:20px;font-weight:800;color:#1e293b; }
    .osm-title small { font-size:12px;font-weight:500;color:#64748b;margin-left:8px; }

    .branch-card { background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:.9rem 1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1rem;box-shadow:0 1px 3px rgba(0,0,0,.06); }
    .branch-card label { font-weight:700;font-size:13px;color:#374151;white-space:nowrap; }

    .table-card { background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(0,0,0,.07);overflow:hidden; }
    .osm-thead { background:linear-gradient(135deg,#1e293b,#334155);color:#fff; }
    .osm-thead th { padding:12px 10px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;border:none;white-space:nowrap; }

    .osm-row td { vertical-align:top;padding:10px 8px;border-bottom:1px solid #f1f5f9;background:#fff; }
    .osm-row:hover td { background:#fafbff; }
    .osm-row-num { width:40px;text-align:center;font-weight:700;color:#94a3b8;font-size:13px;padding-top:16px !important; }

    .fi { border:1.5px solid #e2e8f0;border-radius:7px;padding:8px 10px;font-size:13px;width:100%;transition:.2s;background:#f8fafc; }
    .fi:focus { border-color:#6366f1;background:#fff;outline:none;box-shadow:0 0 0 3px rgba(99,102,241,.1); }
    .fi-num { text-align:right; }
    .fi-label { font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:3px; }

    /* Fix Select2 height to match .fi */
    .select2-container--default .select2-selection--single { border:1.5px solid #e2e8f0;border-radius:7px;height:38px;background:#f8fafc; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height:36px;font-size:13px;color:#1e293b;padding-left:10px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height:36px; }
    .select2-container--default.select2-container--focus .select2-selection--single { border-color:#6366f1;background:#fff;box-shadow:0 0 0 3px rgba(99,102,241,.1); }
    .select2-dropdown { border:1.5px solid #6366f1;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.1); }

    .stock-badge { display:inline-block;background:#dcfce7;color:#166534;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;margin-top:3px; }
    .stock-badge.zero { background:#fee2e2;color:#991b1b; }

    /* Allocation */
    .alloc-panel { background:#eef2ff;border:1.5px dashed #a5b4fc;border-radius:10px;padding:12px;margin-top:8px;display:none; }
    .alloc-panel.open { display:block; }
    .alloc-toggle { background:none;border:none;font-size:11px;color:#6366f1;font-weight:700;cursor:pointer;padding:2px 0;margin-top:5px;display:block;text-decoration:underline dotted; }
    .alloc-header { display:grid;grid-template-columns:190px 90px 32px;gap:6px;font-size:10px;font-weight:800;color:#4f46e5;text-transform:uppercase;padding:0 2px;margin-bottom:5px; }
    .alloc-row-item { display:grid;grid-template-columns:190px 90px 32px;gap:6px;align-items:center;margin-bottom:6px; }
    .alloc-row-item select { font-size:12px;border:1.5px solid #c7d2fe;border-radius:6px;padding:6px 8px;background:#fff;color:#1e293b;font-weight:600;width:100%; }
    .alloc-row-item input  { font-size:13px;border:1.5px solid #c7d2fe;border-radius:6px;padding:6px 8px;text-align:right;background:#fff;font-weight:700;width:100%; }
    .btn-del-alloc { background:#fecaca;color:#dc2626;border:none;border-radius:5px;width:28px;height:28px;cursor:pointer;font-size:13px;font-weight:700; }
    .btn-del-alloc:hover { background:#dc2626;color:#fff; }
    .btn-add-alloc { background:#6366f1;color:#fff;border:1.5px solid #4f46e5;border-radius:6px;padding:6px 14px;font-size:12px;font-weight:700;cursor:pointer;margin-top:6px; }
    .btn-add-alloc:hover { background:#4f46e5; }

    .btn-del-row { background:#fee2e2;color:#dc2626;border:none;border-radius:7px;padding:6px 10px;cursor:pointer;font-size:13px;font-weight:700;transition:.2s; }
    .btn-del-row:hover { background:#dc2626;color:#fff; }

    /* Inline allocation row */
    .alloc-inline-row { display:flex;align-items:center;gap:6px;background:#f8faff;border:1.5px solid #c7d2fe;border-radius:8px;padding:5px 8px; }
    .alloc-inline-row select { flex:1.4;font-size:12px;border:none;background:transparent;color:#1e293b;font-weight:600;min-width:0;padding:4px 6px;cursor:pointer; }
    .alloc-inline-row select:focus { outline:1px solid #6366f1;border-radius:4px; }
    .alloc-inline-row input  { flex:1;font-size:13px;border:none;background:transparent;color:#1e293b;font-weight:700;text-align:right;min-width:0;padding:4px 6px; }
    .alloc-inline-row input:focus { outline:1px solid #6366f1;border-radius:4px; }
    .alloc-inline-row .btn-del-alloc { background:none;color:#cbd5e1;border:none;font-size:14px;cursor:pointer;padding:2px 4px;flex-shrink:0;line-height:1; }
    .alloc-inline-row .btn-del-alloc:hover { color:#dc2626; }

    .osm-footer { background:#f8fafc;border-top:1px solid #e2e8f0;padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;border-radius:0 0 14px 14px; }
    .btn-add-row { background:#fff;border:2px dashed #6366f1;color:#6366f1;border-radius:9px;padding:9px 20px;font-size:13px;font-weight:700;cursor:pointer;transition:.2s; }
    .btn-add-row:hover { background:#eef2ff; }
    .btn-save-all { background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border:none;border-radius:9px;padding:10px 30px;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(99,102,241,.3);transition:.2s; }
    .btn-save-all:hover { transform:translateY(-1px); }

    .flash-success { background:#dcfce7;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:8px;margin-bottom:1rem;font-weight:600; }
    .flash-error   { background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:1rem;font-weight:600; }
</style>

<div class="osm-wrap">
    @if(session('success'))
        <div class="flash-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash-error">❌ {{ session('error') }}</div>
    @endif

    <div class="osm-header">
        <div>
            <div class="osm-title">📦 Opening Stock Manager <small>ERP Standard</small></div>
            <div style="font-size:12px;color:#94a3b8;margin-top:2px;">Add opening stock — products are global, stock stored per branch</div>
        </div>
        <a href="{{ route('product') }}" class="btn btn-sm btn-outline-secondary">← Back to Products</a>
    </div>

    {{-- Branch Selector (Super Admin Only) --}}
    @if($isSuperAdmin)
    <div class="branch-card">
        <i class="las la-code-branch" style="font-size:20px;color:#6366f1;"></i>
        <label>Branch:</label>
        <select id="branch_selector" style="border:1.5px solid #6366f1;border-radius:8px;padding:7px 12px;font-size:14px;min-width:220px;">
            <option value="">-- Select Branch --</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ $b->id == $userBranchId ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
        </select>
        <span style="font-size:12px;color:#94a3b8;">Stock will be stored in selected branch</span>
    </div>
    @endif

    <form id="osmForm" method="POST" action="{{ route('opening.stocks.store') }}">
        @csrf
        <input type="hidden" name="branch_id" id="form_branch_id" value="{{ $userBranchId }}">

        <div class="table-card">
            <table class="table mb-0">
                <thead class="osm-thead">
                    <tr>
                        <th style="width:34px;">#</th>
                        <th style="min-width:220px;">Product</th>
                        <th style="min-width:300px;">Warehouse / Location + Qty</th>
                        <th style="width:110px;">Wholesale ₨</th>
                        <th style="width:110px;">Retail ₨</th>
                        <th style="width:90px;">Alert Qty</th>
                        <th style="width:110px;">Opening Qty<br><span style="font-size:9px;font-weight:400;color:#a5b4fc;">(auto)</span></th>
                        <th style="width:36px;"></th>
                    </tr>
                </thead>
                <tbody id="osm_rows"></tbody>
            </table>
            <div class="osm-footer">
                <button type="button" class="btn-add-row" id="btn_add_row">
                    <i class="las la-plus-circle"></i> Add Product Row
                </button>
                <button type="submit" class="btn-save-all">
                    <i class="las la-save"></i> Save All Opening Stocks
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Load Select2 AFTER jQuery (jQuery is in layout footer) --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {

    var searchUrl    = "{{ route('opening.stocks.search') }}";
    var warehouseUrl = "{{ route('opening.stocks.warehouses') }}";
    var warehousesData = @json(collect($warehouses)->map(fn($w) => ['id' => $w->id, 'warehouse_name' => $w->warehouse_name, 'name' => $w->warehouse_name]));
    var rowCounter   = 0;

    /* ── Build location options ── */
    function buildLocOpts() {
        var opts = '<option value="">-- Location --</option>';
        opts += '<option value="shop">&#127978; Branch / Shop Stock</option>';
        if (warehousesData.length > 0) {
            opts += '<optgroup label="── Warehouses ──">';
            warehousesData.forEach(function(w) {
                opts += '<option value="wh_' + w.id + '">&#128230; ' + (w.warehouse_name || w.name) + '</option>';
            });
            opts += '</optgroup>';
        }
        return opts;
    }

    /* ── Build one allocation sub-row ── */
    function buildAllocLine(idx, rid) {
        rid = rid || ('al' + Date.now() + Math.random().toString(36).slice(2,5));
        return '<div class="alloc-inline-row" id="' + rid + '" data-parent="' + idx + '">' +
            '<select class="alloc-type fi" style="flex:1.4;font-size:12px;padding:6px 8px;" onchange="calcTotal(' + idx + ')">' +
            buildLocOpts() +
            '</select>' +
            '<input type="number" class="alloc-qty fi fi-num" placeholder="Qty" step="0.01" min="0" style="flex:1;font-size:13px;font-weight:700;" ' +
            'oninput="calcTotal(' + idx + ')" ' +
            'onkeydown="handleAllocEnter(event,' + idx + ')">' +
            '<button type="button" class="btn-del-alloc" onclick="delAllocLine(this,' + idx + ')" title="Remove">&#10005;</button>' +
            '</div>';
    }

    /* ── Build HTML for one product row ── */
    function buildRow(idx) {
        return `
        <tr class="osm-row" id="row_${idx}" data-row="${idx}">
            <td class="osm-row-num" style="vertical-align:top;padding-top:14px;">${idx}</td>
            <td style="vertical-align:top;">
                <div class="fi-label">Product</div>
                <select class="product-sel" id="prod_${idx}" name="rows[${idx}][product_id]" style="width:100%;"></select>
                <span class="stock-badge zero" id="stk_${idx}" style="display:none;margin-top:4px;">Stock: 0</span>
                <input type="hidden" name="rows[${idx}][allocation_data]" id="alloc_data_${idx}" value="[]">
            </td>
            <td style="vertical-align:top;">
                <div class="fi-label" style="margin-bottom:4px;">Location &amp; Qty</div>
                <div id="alloc_rows_${idx}" style="display:flex;flex-direction:column;gap:5px;"></div>
                <button type="button" class="btn-add-alloc" style="margin-top:6px;" onclick="addAllocLine(${idx})">+ Add Location</button>
                <div id="oqty_label_${idx}" style="font-size:11px;color:#6366f1;font-weight:700;margin-top:4px;display:none;">
                    &#9432; Total: <span id="oqty_display_${idx}">0</span>
                </div>
            </td>
            <td style="vertical-align:top;">
                <div class="fi-label">Wholesale</div>
                <input type="number" class="fi fi-num" name="rows[${idx}][wholesale_price]" placeholder="0.00" step="0.01" min="0">
            </td>
            <td style="vertical-align:top;">
                <div class="fi-label">Retail</div>
                <input type="number" class="fi fi-num" name="rows[${idx}][retail_price]" placeholder="0.00" step="0.01" min="0">
            </td>
            <td style="vertical-align:top;">
                <div class="fi-label">Alert Qty</div>
                <input type="number" class="fi fi-num" name="rows[${idx}][alert_qty]" placeholder="0" step="0.01" min="0">
            </td>
            <td style="vertical-align:top;">
                <div class="fi-label">Opening Qty</div>
                <input type="number" class="fi fi-num" id="oqty_${idx}"
                       name="rows[${idx}][opening_qty]" placeholder="0.00" step="0.01" min="0"
                       readonly
                       style="background:#eef2ff;border-color:#a5b4fc;color:#4f46e5;font-weight:800;cursor:not-allowed;"
                       title="Auto-calculated from locations">
            </td>
            <td style="vertical-align:top;padding-top:22px;">
                <button type="button" class="btn-del-row" onclick="delRow(${idx})">✕</button>
            </td>
        </tr>`;
    }

    /* ── Add / delete product rows ── */
    function addRow() {
        rowCounter++;
        $('#osm_rows').append(buildRow(rowCounter));
        initSelect2(rowCounter);
        renumber();
        // Auto-add first location row
        addAllocLine(rowCounter);
    }

    window.delRow = function(idx) {
        if ($('#osm_rows tr').length <= 1) { alert('At least one row required.'); return; }
        $('#row_' + idx).remove();
        renumber();
    };

    function renumber() {
        $('#osm_rows tr').each(function(i) { $(this).find('.osm-row-num').text(i + 1); });
    }

    /* ── Select2 init (global products — no branch filter) ── */
    function initSelect2(idx) {
        $('#prod_' + idx).select2({
            placeholder: '🔍 Click to search or type product name...',
            allowClear: true,
            minimumInputLength: 0,
            width: '100%',
            ajax: {
                url: searchUrl,
                dataType: 'json',
                delay: 200,
                cache: false,
                data: function(params) {
                    return {
                        q: params.term || '',
                        branch_id: $('#form_branch_id').val() || ''
                    };
                },
                processResults: function(data) {
                    return { results: data };
                }
            }
        }).on('select2:select', function(e) {
            var d   = e.params.data;
            var row = $(this).closest('tr');
            var idx = row.data('row');
            row.find('[name$="[wholesale_price]"]').val(d.wholesale_price || '');
            row.find('[name$="[retail_price]"]').val(d.retail_price || '');
            row.find('[name$="[alert_qty]"]').val(d.alert_quantity || '');
            var stk = parseFloat(d.current_stock || 0);
            $('#stk_' + idx).text('Current Stock: ' + stk).css('display','inline-block')
                            .toggleClass('zero', stk <= 0);
            $('#cur_stk_' + idx).text(stk.toFixed(2));
        });
    }

    /* ── Add / delete inline allocation rows ── */
    window.addAllocLine = function(idx) {
        var line = buildAllocLine(idx);
        $('#alloc_rows_' + idx).append(line);
        // Focus the location select of new row
        $('#alloc_rows_' + idx + ' .alloc-inline-row:last select').focus();
        calcTotal(idx);
    };

    window.delAllocLine = function(btn, idx) {
        $(btn).closest('.alloc-inline-row').remove();
        calcTotal(idx);
    };

    /* Enter key in qty → add next alloc row */
    window.handleAllocEnter = function(e, idx) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addAllocLine(idx);
        }
    };

    /* Recalculate Opening Qty total */
    window.calcTotal = function(idx) {
        var total = 0;
        $('#alloc_rows_' + idx + ' .alloc-inline-row').each(function() {
            total += parseFloat($(this).find('.alloc-qty').val()) || 0;
        });
        $('#oqty_' + idx).val(total > 0 ? total.toFixed(2) : '');
        var lbl = $('#oqty_label_' + idx);
        if (total > 0) {
            $('#oqty_display_' + idx).text(total.toFixed(2));
            lbl.show();
        } else {
            lbl.hide();
        }
        buildAllocJson(idx);
    };

    function buildAllocJson(idx) {
        var data = [];
        var container = document.getElementById('alloc_rows_' + idx);
        if (!container) { $('#alloc_data_' + idx).val('[]'); return; }

        $(container).find('.alloc-inline-row').each(function() {
            var type = $(this).find('.alloc-type').val();
            var qty  = parseFloat($(this).find('.alloc-qty').val()) || 0;
            if (type === 'shop') {
                data.push({ location_type: 'shop', quantity: qty });
            } else if (type && type.indexOf('wh_') === 0) {
                data.push({ location_type: 'warehouse', warehouse_id: type.replace('wh_',''), quantity: qty });
            }
        });

        $('#alloc_data_' + idx).val(JSON.stringify(data));
    }

    /* ── Branch change (super admin) ── */
    $('#branch_selector').on('change', function() {
        var bid = $(this).val();
        $('#form_branch_id').val(bid);

        if (bid) {
            $.getJSON(warehouseUrl, {branch_id: bid}, function(data) {
                warehousesData = data;
                // Rebuild ALL existing location dropdowns with new warehouse options
                rebuildAllocDropdowns();
            });
        } else {
            warehousesData = [];
            rebuildAllocDropdowns();
        }
        // Reset all product selects
        $('#osm_rows .product-sel').val(null).trigger('change');
    });

    /* Rebuild every alloc-type select to reflect current warehousesData */
    function rebuildAllocDropdowns() {
        var newOpts = buildLocOpts();
        $('#osm_rows .alloc-type').each(function() {
            var currentVal = $(this).val();
            $(this).html(newOpts);
            // Try to restore previous selection (it will fall back to empty if not available)
            if (currentVal) { $(this).val(currentVal); }
        });
    }

    /* ── Form submit validation ── */
    /* ── Inline error/success display ── */
    function showRowError(idx, msg) {
        $('#row_err_' + idx).remove();
        var errHtml = '<div id="row_err_' + idx + '" style="color:#dc2626;font-size:11px;font-weight:700;margin-top:5px;padding:4px 8px;background:#fee2e2;border-radius:6px;border:1px solid #fca5a5;">⚠ ' + msg + '</div>';
        $('#row_' + idx + ' td:nth-child(2)').append(errHtml);
        $('#row_' + idx).css('outline','2px solid #fca5a5');
    }
    function clearRowErrors() {
        $('.row-inline-err').remove();
        $('#osm_rows tr').css('outline','');
        $('[id^="row_err_"]').remove();
    }
    function showGlobalMsg(msg, type) {
        var cls  = type === 'success' ? 'flash-success' : 'flash-error';
        var icon = type === 'success' ? '✅' : '❌';
        var el = $('<div class="' + cls + '">' + icon + ' ' + msg + '</div>');
        $('.osm-wrap').prepend(el);
        setTimeout(function() { el.fadeOut(400, function(){ $(this).remove(); }); }, 4000);
    }

    /* ── AJAX Form Submit ── */
    $('#osmForm').on('submit', function(e) {
        e.preventDefault();
        clearRowErrors();

        /* Client-side validate */
        var ok = true;
        $('#osm_rows tr').each(function() {
            var idx = $(this).data('row');
            buildAllocJson(idx);
            var pid = $('#prod_' + idx).val();
            var qty = parseFloat($('#oqty_' + idx).val()) || 0;
            if (!pid) {
                showRowError(idx, 'Please select a product.');
                ok = false; return false;
            }
            if (qty <= 0) {
                showRowError(idx, 'Add at least one location with qty > 0.');
                ok = false; return false;
            }
        });
        if (!ok) return;

        var $btn = $('.btn-save-all');
        $btn.prop('disabled', true).text('Saving…');

        $.ajax({
            url   : $(this).attr('action'),
            method: 'POST',
            data  : $(this).serialize(),
            success: function(res) {
                $btn.prop('disabled', false).text('Save All Opening Stocks');
                if (res && res.success) {
                    showGlobalMsg(res.message || 'Opening stocks saved! Redirecting...', 'success');
                    // Redirect to the products page after a short delay
                    setTimeout(function() {
                        window.location.href = "{{ route('product') }}";
                    }, 1200);
                } else if (res && res.error) {
                    showGlobalMsg(res.error, 'error');
                    if (res.row_idx) showRowError(res.row_idx, res.error);
                } else {
                    showGlobalMsg('Saved successfully! Redirecting...', 'success');
                    setTimeout(function() {
                        window.location.href = "{{ route('product') }}";
                    }, 1200);
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text('Save All Opening Stocks');
                var msg = 'Server error. Please try again.';
                try {
                    var r = JSON.parse(xhr.responseText);
                    if (r.error)   msg = r.error;
                    if (r.message) msg = r.message;
                    if (r.row_idx) showRowError(r.row_idx, msg);
                } catch(ex) {}
                showGlobalMsg(msg, 'error');
            }
        });
    });

    /* ── Init ── */
    $('#btn_add_row').on('click', addRow);
    addRow(); // first row on load
});
</script>

@else
    <div class="alert alert-danger m-4">You do not have permission to manage opening stocks.</div>
@endcan
@endsection
