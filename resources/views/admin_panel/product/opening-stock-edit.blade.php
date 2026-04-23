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
                <small>SKU: {{ $product->item_code }} &nbsp;|&nbsp; Unit: {{ $product->unit?->name ?? 'PCS' }} &nbsp;|&nbsp; Branch: {{ $product->branch?->name ?? '—' }}</small>
            </div>
            <span style="background:rgba(99,102,241,.3);color:#c7d2fe;font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;">Edit Opening Stock</span>
        </div>

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
            <input type="hidden" name="branch_id" value="{{ $product->branch_id }}">

            {{-- Stock & Pricing --}}
            <div class="form-section">
                <h6>📦 Stock &amp; Valuation</h6>
                <div class="input-row">
                    <div class="fg">
                        <label>New Total Stock Qty</label>
                        <input type="number" id="new_qty" name="opening_qty" class="fi fi-num"
                               value="{{ $currentStock }}" step="0.01" min="0" required oninput="calcDelta()">
                        <div class="delta-info delta-zero" id="delta_info">No change from current stock</div>
                    </div>
                    <div class="fg">
                        <label>Alert Qty (Low Stock)</label>
                        <input type="number" name="alert_qty" class="fi fi-num"
                               value="{{ $product->alert_quantity ?? 0 }}" step="0.01" min="0">
                    </div>
                    <div class="fg">
                        <label>Wholesale Price ₨</label>
                        <input type="number" name="wholesale_price" class="fi fi-num"
                               value="{{ $product->wholesale_price ?? 0 }}" step="0.01" min="0">
                    </div>
                    <div class="fg">
                        <label>Retail Price ₨</label>
                        <input type="number" name="retail_price" class="fi fi-num"
                               value="{{ $product->price ?? 0 }}" step="0.01" min="0">
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
                        @forelse($currentAllocs->where('quantity', '>', 0) as $alloc)
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
                        @empty
                        {{-- No allocation yet — show one empty branch row --}}
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
                        @endforelse

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
        updateEditAllocStatus();
    };

    // ── Allocation Status ─────────────────────────────────────────────────
    window.updateEditAllocStatus = function() {
        var newQty = parseFloat($('#new_qty').val()) || 0;
        var total  = 0;
        $('#alloc_edit_rows .alloc-qty-in').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        var el = $('#edit_alloc_status');
        var rows = $('#alloc_edit_rows .alloc-grid-row').length;
        if (rows === 0) { el.text(''); return; }
        if (Math.abs(total - newQty) < 0.001) {
            el.text('✓ Allocations match (' + newQty + ')').css('color','#166534');
        } else if (total > newQty) {
            el.text('✗ Over-allocated: ' + total.toFixed(2) + ' / ' + newQty).css('color','#991b1b');
        } else {
            el.text('⚠ Under-allocated: ' + total.toFixed(2) + ' / ' + newQty).css('color','#92400e');
        }
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

    // ── Init ─────────────────────────────────────────────────────────────
    calcDelta();
    updateEditAllocStatus();
});
</script>

@else
    <div class="alert alert-danger m-4">You do not have permission to edit opening stocks.</div>
@endcan
@endsection
