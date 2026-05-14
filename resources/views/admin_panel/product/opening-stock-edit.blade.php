@extends('admin_panel.layout.app')

@section('content')
@can('product.edit')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .edit-wrap { font-family:'Inter',sans-serif; background:#f1f5f9; min-height:100vh; padding:1.5rem; }

    .edit-card { background:#fff; border-radius:14px; border:1px solid #e2e8f0; box-shadow:0 2px 10px rgba(0,0,0,.07); max-width:960px; margin:0 auto; }

    /* Banner */
    .prod-banner { background:linear-gradient(135deg,#1e293b,#334155); color:#fff; padding:1.2rem 1.5rem; border-radius:14px 14px 0 0; display:flex; align-items:center; gap:1rem; }
    .prod-banner h5 { margin:0; font-size:16px; font-weight:800; }
    .prod-banner small { font-size:12px; color:#94a3b8; }

    /* Stock info bar */
    .stock-info-bar { background:#f0fdf4; border-bottom:1px solid #bbf7d0; padding:.8rem 1.5rem; display:flex; gap:2rem; flex-wrap:wrap; }
    .sib-item { text-align:center; }
    .sib-val  { font-size:18px; font-weight:800; color:#166534; }
    .sib-lbl  { font-size:10px; font-weight:700; color:#4ade80; text-transform:uppercase; }

    /* Sections */
    .form-section { padding:1.5rem; border-bottom:1px solid #f1f5f9; }
    .form-section h6 { font-weight:800; color:#1e293b; font-size:12px; text-transform:uppercase; letter-spacing:.5px; margin-bottom:1rem; }
    .input-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:1rem; }
    .fg label { font-size:12px; font-weight:700; color:#475569; margin-bottom:4px; display:block; }
    .fi { border:1.5px solid #e2e8f0; border-radius:8px; padding:9px 12px; font-size:14px; width:100%; background:#f8fafc; transition:.2s; }
    .fi:focus { border-color:#6366f1; background:#fff; outline:none; box-shadow:0 0 0 3px rgba(99,102,241,.1); }
    .fi-num { text-align:right; }
    #new_qty { border-color:#6366f1; font-size:15px; font-weight:700; }

    .delta-info { font-size:11px; margin-top:4px; font-weight:600; }
    .delta-pos  { color:#166534; }
    .delta-neg  { color:#991b1b; }
    .delta-zero { color:#92400e; }

    /* Allocation table */
    .alloc-panel { background:#eef2ff; border:1.5px dashed #a5b4fc; border-radius:10px; padding:14px; }
    .alloc-grid-head { display:grid; grid-template-columns:1fr 110px 36px; gap:8px; font-size:10px; font-weight:800; color:#4f46e5; text-transform:uppercase; margin-bottom:6px; padding:0 2px; }
    .alloc-grid-row  { display:grid; grid-template-columns:1fr 110px 36px; gap:8px; align-items:center; margin-bottom:8px; }
    .alloc-grid-row select { font-size:13px; border:1.5px solid #c7d2fe; border-radius:7px; padding:7px 10px; background:#fff; font-weight:600; width:100%; }
    .alloc-grid-row input  { font-size:14px; border:1.5px solid #c7d2fe; border-radius:7px; padding:7px 10px; text-align:right; background:#fff; font-weight:700; width:100%; }
    .btn-del-alloc { background:#fecaca; color:#dc2626; border:none; border-radius:6px; width:32px; height:34px; cursor:pointer; font-size:14px; font-weight:700; display:flex; align-items:center; justify-content:center; }
    .btn-del-alloc:hover { background:#dc2626; color:#fff; }
    .btn-add-alloc { background:#6366f1; color:#fff; border:none; border-radius:7px; padding:7px 16px; font-size:12px; font-weight:700; cursor:pointer; margin-top:6px; }
    .btn-add-alloc:hover { background:#4f46e5; }
    .alloc-status { font-size:11px; font-weight:700; margin-top:8px; }

    /* Action bar */
    .action-bar { padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center; border-radius:0 0 14px 14px; background:#f8fafc; }
    .btn-cancel { border:1.5px solid #e2e8f0; background:#fff; color:#475569; border-radius:8px; padding:9px 20px; font-size:13px; font-weight:600; text-decoration:none; }
    .btn-save   { background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; border:none; border-radius:9px; padding:10px 28px; font-size:14px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(99,102,241,.3); }

    .flash-success { background:#dcfce7; border:1px solid #86efac; color:#166534; padding:12px; border-radius:8px; margin-bottom:1rem; font-weight:600; }
</style>

<div class="edit-wrap">
    @if(session('success'))
        <div class="flash-success">✅ {{ session('success') }}</div>
    @endif

    <div class="edit-card">

        {{-- Product Banner --}}
        <div class="prod-banner">
            <i class="las la-box" style="font-size:28px;color:#818cf8;"></i>
            <div style="flex:1;">
                <h5>{{ $product->item_name }}</h5>
                <small>SKU: {{ $product->item_code }} &nbsp;|&nbsp; Unit: {{ $product->unit?->name ?? 'PCS' }} &nbsp;|&nbsp; Created in: {{ $product->branch?->name ?? '—' }}</small>
            </div>
            <span style="background:rgba(99,102,241,.3);color:#c7d2fe;font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;">Edit Opening Stock</span>
        </div>

        {{-- ✅ SUPER ADMIN: Branch Selector --}}
        @if($isSuperAdmin)
        <div style="background:linear-gradient(135deg,#1a3a6e,#1e40af);padding:1rem 1.5rem;border-bottom:1px solid #1e3a8a;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:18px;">&#127968;</span>
                <span style="color:#93c5fd;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;">Editing Stock For Branch:</span>
            </div>
            <select id="branch_switcher"
                    style="border:2px solid #3b82f6;border-radius:8px;padding:7px 14px;font-size:14px;font-weight:700;color:#1e293b;background:#eff6ff;min-width:220px;cursor:pointer;">
                @foreach($availableBranches as $br)
                    <option value="{{ $br->id }}" {{ $br->id == $selectedBranchId ? 'selected' : '' }}>
                        {{ $br->name }}
                    </option>
                @endforeach
            </select>
            @if($availableBranches->count() > 1)
            <span style="color:#fbbf24;font-size:11px;font-weight:700;">&#9888; This product has stock in {{ $availableBranches->count() }} branches &mdash; select the one you want to edit.</span>
            @endif
            <a href="{{ route('opening.stocks.edit', $product->id) }}" style="color:#93c5fd;font-size:11px;margin-left:auto;text-decoration:underline;">Reset</a>
        </div>
        @if($isSuperAdmin && count($globalStockSummary) > 0)
        <div style="background:#f8fafc; padding:1rem 1.5rem; border-bottom:1px solid #e2e8f0;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:0.8rem;">
                <i class="las la-globe" style="color:#6366f1; font-size:18px;"></i>
                <span style="color:#475569; font-size:11px; font-weight:800; text-transform:uppercase;">System-Wide Stock Distribution:</span>
            </div>
            <div style="display:flex; gap:1.5rem; flex-wrap:wrap;">
                @foreach($globalStockSummary as $row)
                    <div style="display:flex; align-items:center; gap:6px; background:#fff; border:1px solid #e2e8f0; padding:4px 10px; border-radius:6px; font-size:12px;">
                        <span style="color:#64748b; font-weight:600;">{{ $row->branch_name }}:</span>
                        <span style="color:#1e293b; font-weight:800;">{{ number_format($row->qty, 2) }}</span>
                        @if($row->branch_id != $selectedBranchId)
                            <a href="{{ route('opening.stocks.edit', $product->id) }}?branch_id={{ $row->branch_id }}" style="margin-left:4px; font-size:10px; color:#6366f1; text-decoration:underline;">Switch</a>
                        @else
                            <span class="badge bg-success" style="font-size:9px; padding:2px 6px;">Active Branch</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        @endif


        {{-- Current Stock Summary --}}
        <div class="stock-info-bar">
            <div class="sib-item">
                <div class="sib-val">{{ number_format($currentStock, 2) }}</div>
                <div class="sib-lbl">Current Stock</div>
            </div>
            <div class="sib-item">
                <div class="sib-val">₨ {{ number_format($product->wholesale_price ?? 0, 2) }}</div>
                <div class="sib-lbl">Wholesale</div>
            </div>
            <div class="sib-item">
                <div class="sib-val">₨ {{ number_format($product->price ?? 0, 2) }}</div>
                <div class="sib-lbl">Retail</div>
            </div>
            <div class="sib-item">
                <div class="sib-val">{{ number_format($product->alert_quantity ?? 0, 2) }}</div>
                <div class="sib-lbl">Alert Qty</div>
            </div>
        </div>

        {{-- Form --}}
        <form id="editForm" method="POST" action="{{ route('opening.stocks.update', $product->id) }}">
            @csrf
            {{-- Use selectedBranchId (chosen by super admin) not product->branch_id --}}
            <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">

            {{-- Stock & Pricing --}}
            <div class="form-section">
                <h6>📦 Stock &amp; Valuation</h6>
                <div class="input-row">
                    <div class="fg">
                        <label>New Total Stock Qty <small style="font-size:10px;color:#6366f1;">(auto from allocations)</small></label>
                        <input type="number" id="new_qty" name="opening_qty" class="fi fi-num"
                               value="{{ $currentStock }}" step="0.01" min="0" required
                               readonly
                               style="background:#eef2ff;border-color:#a5b4fc;color:#4f46e5;font-weight:800;cursor:not-allowed;"
                               title="Auto-calculated from warehouse allocations below">
                        <div class="delta-info delta-zero" id="delta_info">No change from current stock</div>
                    </div>
                    <div class="fg">
                        <label>Alert Qty (Low Stock)</label>
                        <input type="number" name="alert_qty" class="fi fi-num"
                               value="{{ $product->alert_quantity ?? 0 }}" step="0.01" min="0">
                    </div>
                    <div class="fg">
                        <label>Wholesale Price ₨</label>
                        <input type="number" id="wholesale_price" name="wholesale_price" class="fi fi-num"
                               value="{{ $product->wholesale_price ?? 0 }}" step="0.01" min="0">
                    </div>
                    <div class="fg">
                        <label>Retail Price ₨</label>
                        <input type="number" id="retail_price" name="retail_price" class="fi fi-num"
                               value="{{ $product->price ?? 0 }}" step="0.01" min="0">
                        <div id="price_warning" style="display:none; color:#dc2626; font-size:11px; font-weight:700; margin-top:5px;">
                            ⚠ Retail price should not be less than wholesale
                        </div>
                    </div>
                </div>
            </div>

            {{-- Warehouse Allocation --}}
            <div class="form-section">
                <h6>📍 Warehouse Allocation</h6>
                <p style="font-size:12px;color:#64748b;margin-bottom:1rem;">
                    Edit how stock is distributed across locations. These will <strong>replace</strong> the existing allocations.
                </p>
                <input type="hidden" name="allocation_data" id="alloc_data_edit" value="[]">

                <div class="alloc-panel">
                    <div class="alloc-grid-head">
                        <span>Location</span>
                        <span style="text-align:right;">Qty</span>
                        <span></span>
                    </div>
                    <div id="alloc_edit_rows">

                        {{-- Pre-fill: show rows where quantity > 0 --}}
                        @php $hasAllocations = false; @endphp
                        @foreach($currentAllocs as $alloc)
                            @if($alloc->quantity > 0)
                                @php $hasAllocations = true; @endphp
                                <div class="alloc-grid-row">
                                    <select class="alloc-type-sel" onchange="updateEditAllocStatus()">
                                        <option value="shop" {{ is_null($alloc->warehouse_id) ? 'selected' : '' }}>
                                            🏪 Branch / Shop Stock
                                        </option>
                                        @foreach($warehouses as $wh)
                                        <option value="wh_{{ $wh->id }}"
                                            {{ (string)$alloc->warehouse_id === (string)$wh->id ? 'selected' : '' }}>
                                            📦 {{ $wh->warehouse_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <input type="number" class="alloc-qty-in fi-num"
                                           value="{{ number_format((float)$alloc->quantity, 2, '.', '') }}"
                                           step="0.01" min="0"
                                           oninput="updateEditAllocStatus()">
                                    <button type="button" class="btn-del-alloc"
                                            onclick="$(this).closest('.alloc-grid-row').remove();updateEditAllocStatus();">✕</button>
                                </div>
                            @endif
                        @endforeach

                        @if(!$hasAllocations)
                        {{-- No allocation yet — show one default row with total stock --}}
                        <div class="alloc-grid-row">
                            <select class="alloc-type-sel" onchange="updateEditAllocStatus()">
                                <option value="shop" selected>🏪 Branch / Shop Stock</option>
                                @foreach($warehouses as $wh)
                                <option value="wh_{{ $wh->id }}">📦 {{ $wh->warehouse_name }}</option>
                                @endforeach
                            </select>
                            <input type="number" class="alloc-qty-in fi-num"
                                   value="{{ $currentStock }}"
                                   step="0.01" min="0"
                                   oninput="updateEditAllocStatus()">
                            <button type="button" class="btn-del-alloc"
                                    onclick="$(this).closest('.alloc-grid-row').remove();updateEditAllocStatus();">✕</button>
                        </div>
                        @endif

                    </div>

                    <button type="button" class="btn-add-alloc" id="btn_add_edit_alloc">+ Add Location</button>
                    <div class="alloc-status" id="edit_alloc_status"></div>
                </div>
            </div>

            {{-- Action Bar --}}
            <div class="action-bar">
                <a href="{{ route('opening.stocks.index') }}" class="btn-cancel">← Cancel</a>
                <button type="submit" class="btn-save">
                    <i class="las la-save me-1"></i> Update Opening Stock
                </button>
            </div>
        </form>
    </div>
</div>

{{-- JS: jQuery already in layout --}}
<script>
$(document).ready(function() {

    var currentStock = {{ $currentStock }};
    var warehouses   = @json(collect($warehouses)->map(fn($w) => ['id' => $w->id, 'name' => $w->warehouse_name]));

    // ── Delta Calculation ────────────────────────────────────────────────
    window.calcDelta = function() {
        var newQty = parseFloat($('#new_qty').val()) || 0;
        var delta  = newQty - currentStock;
        var el     = $('#delta_info');
        if (Math.abs(delta) < 0.001) {
            el.text('No change from current stock (' + currentStock + ')').attr('class','delta-info delta-zero');
        } else if (delta > 0) {
            el.text('▲ +' + delta.toFixed(2) + ' units will be ADDED to stock').attr('class','delta-info delta-pos');
        } else {
            el.text('▼ ' + delta.toFixed(2) + ' units will be REMOVED from stock').attr('class','delta-info delta-neg');
        }
    };

    // ── Allocation Status ─────────────────────────────────────────────────
    window.updateEditAllocStatus = function() {
        /* 1. Sum all allocation qty inputs */
        var total = 0;
        $('#alloc_edit_rows .alloc-qty-in').each(function() {
            total += parseFloat($(this).val()) || 0;
        });

        /* 2. Push sum into the readonly #new_qty field */
        $('#new_qty').val(total > 0 ? total.toFixed(2) : '0.00');

        /* 3. Recompute delta label against currentStock */
        var delta = total - currentStock;
        var deltaEl = $('#delta_info');
        if (Math.abs(delta) < 0.001) {
            deltaEl.text('No change from current stock (' + currentStock + ')').attr('class','delta-info delta-zero');
        } else if (delta > 0) {
            deltaEl.text('▲ +' + delta.toFixed(2) + ' units will be ADDED to stock').attr('class','delta-info delta-pos');
        } else {
            deltaEl.text('▼ ' + delta.toFixed(2) + ' units will be REMOVED from stock').attr('class','delta-info delta-neg');
        }

        /* 4. Show allocation total in status bar */
        var rows = $('#alloc_edit_rows .alloc-grid-row').length;
        var el = $('#edit_alloc_status');
        if (rows === 0) { el.text(''); return; }
        el.text('📦 Allocated Total: ' + total.toFixed(2) + ' units').css('color', total > 0 ? '#166534' : '#92400e');
    };

    // ── Add allocation row ────────────────────────────────────────────────
    $('#btn_add_edit_alloc').on('click', function() {
        var opts = '<option value="shop">🏪 Branch / Shop Stock</option>';
        warehouses.forEach(function(w) {
            opts += '<option value="wh_' + w.id + '">📦 ' + w.name + '</option>';
        });
        var row = '<div class="alloc-grid-row">' +
            '<select class="alloc-type-sel" onchange="updateEditAllocStatus()">' + opts + '</select>' +
            '<input type="number" class="alloc-qty-in fi-num" value="" step="0.01" min="0" placeholder="0" oninput="updateEditAllocStatus()">' +
            '<button type="button" class="btn-del-alloc" onclick="$(this).closest(\'.alloc-grid-row\').remove();updateEditAllocStatus()">✕</button>' +
            '</div>';
        $('#alloc_edit_rows').append(row);
        updateEditAllocStatus();
    });

    // ── Build allocation JSON before submit ───────────────────────────────
    $('#editForm').on('submit', function() {
        // Price validation: Retail >= Wholesale
        var wholesale = parseFloat($('#wholesale_price').val()) || 0;
        var retail    = parseFloat($('#retail_price').val()) || 0;
        if (wholesale > 0 && retail > 0 && retail < wholesale) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Pricing',
                text: 'Retail price cannot be less than Wholesale price.',
                confirmButtonColor: '#6366f1'
            });
            return false;
        }

        var data = [];
        $('#alloc_edit_rows .alloc-grid-row').each(function() {
            var type = $(this).find('.alloc-type-sel').val();
            var qty  = parseFloat($(this).find('.alloc-qty-in').val()) || 0;
            if (type === 'shop') {
                data.push({ location_type: 'shop', quantity: qty });
            } else if (type && type.indexOf('wh_') === 0) {
                data.push({ location_type: 'warehouse', warehouse_id: type.replace('wh_',''), quantity: qty });
            }
        });
        $('#alloc_data_edit').val(JSON.stringify(data));
        console.log('Edit alloc_data:', JSON.stringify(data));
    });

    // ── Branch Switcher (Super Admin) ─────────────────────────────────────
    // When the branch selector changes, reload the page with ?branch_id=X
    // so the controller returns the correct stock & allocation data for that branch.
    $('#branch_switcher').on('change', function() {
        var branchId = $(this).val();
        if (!branchId) return;
        var url = new URL(window.location.href);
        url.searchParams.set('branch_id', branchId);
        window.location.href = url.toString();
    });

    // ── Price Validation ────────────────────────────────────────────────
    function validatePrices() {
        var wholesale = parseFloat($('#wholesale_price').val()) || 0;
        var retail    = parseFloat($('#retail_price').val()) || 0;
        var warning   = $('#price_warning');

        if (wholesale > 0 && retail > 0 && retail < wholesale) {
            $('#wholesale_price, #retail_price').css('border-color', '#dc2626').css('background', '#fff1f2');
            warning.show();
            return false;
        } else {
            $('#wholesale_price, #retail_price').css('border-color', '').css('background', '');
            warning.hide();
            return true;
        }
    }

    $('#wholesale_price, #retail_price').on('input change', validatePrices);

    // ── Init ─────────────────────────────────────────────────────────────
    calcDelta();
    updateEditAllocStatus();
    validatePrices();
});
</script>

@else
    <div class="alert alert-danger m-4">You do not have permission to edit opening stocks.</div>
@endcan
@endsection
