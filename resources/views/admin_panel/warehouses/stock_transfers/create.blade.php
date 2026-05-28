@extends('admin_panel.layout.app')

@section('content')
<style>
/* ── ERP Stock Transfer Form ── */
.st-wrapper { max-width: 1200px; margin: 0 auto; }
.st-card { border-radius: 14px; border: 1px solid #e5e7eb; box-shadow: 0 2px 12px rgba(0,0,0,.08); background:#fff; overflow:hidden; }
.st-header { background: linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%); color:#fff; padding:22px 28px; }
.st-header h4 { margin:0; font-weight:700; font-size:1.15rem; letter-spacing:.01em; }
.st-header p  { margin:4px 0 0; opacity:.85; font-size:.85rem; }

/* section boxes */
.st-section { padding:22px 28px; border-bottom:1px solid #f0f0f0; }
.st-section:last-child { border-bottom:none; }
.st-section-title { font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin-bottom:18px; display:flex; align-items:center; gap:10px; }
.st-section-title .badge-num { width:22px; height:22px; border-radius:50%; background:#4f46e5; color:#fff; font-size:.7rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; }

/* form controls */
.f-label { font-size:.82rem; font-weight:600; color:#374151; margin-bottom:5px; display:block; }
.f-control { border:1px solid #d1d5db; border-radius:8px; padding:9px 12px; font-size:.88rem; width:100%; background:#fff; transition:border-color .2s,box-shadow .2s; }
.f-control:focus { outline:none; border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,.12); }
select.f-control { cursor:pointer; }
textarea.f-control { resize:vertical; }

/* radio pills */
.type-pills { display:flex; gap:10px; }
.type-pill { padding:9px 16px; border:2px solid #e5e7eb; border-radius:8px; cursor:pointer; font-size:.88rem; display:flex; align-items:center; gap:7px; transition:all .2s; background:#fff; user-select:none; }
.type-pill:hover { border-color:#4f46e5; background:#f5f4ff; }
.type-pill input[type=radio] { accent-color:#4f46e5; }
.type-pill.selected { border-color:#4f46e5; background:#eef2ff; color:#4338ca; font-weight:600; }

/* items table */
.items-table { width:100%; border-collapse:collapse; }
.items-table thead tr { background:#f8fafc; }
.items-table th { padding:10px 12px; text-align:left; font-size:.74rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; border-bottom:2px solid #e5e7eb; }
.items-table td { padding:9px 10px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
.items-table tr:last-child td { border-bottom:none; }
.items-table input,.items-table select { border:1px solid #d1d5db; border-radius:6px; padding:7px 10px; font-size:.86rem; width:100%; background:#fff; }
.items-table input:focus,.items-table select:focus { outline:none; border-color:#4f46e5; background:#f9f8ff; }
.cell-stock input { background:#f8fafc !important; font-weight:700; text-align:right; cursor:default; color:#374151; }
.cell-stock input.has-stock { background:#dcfce7 !important; color:#166534 !important; border-color:#86efac !important; }
.cell-stock input.no-stock  { background:#fee2e2 !important; color:#991b1b !important; border-color:#fca5a5 !important; }

/* add row btn */
.btn-add-row { padding:10px 18px; background:#fff; border:2px dashed #4f46e5; border-radius:8px; color:#4f46e5; cursor:pointer; font-weight:600; font-size:.88rem; width:100%; margin-top:12px; transition:all .2s; }
.btn-add-row:hover { background:#eef2ff; }
.btn-del { padding:5px 10px; border:1px solid #fecaca; border-radius:6px; cursor:pointer; background:#fff; color:#dc2626; transition:all .2s; }
.btn-del:hover { background:#fee2e2; }

/* alerts */
.st-alert { border-radius:8px; padding:11px 14px; font-size:.87rem; display:none; margin-bottom:14px; }
.st-alert-warn { background:#fef3c7; border:1px solid #fcd34d; color:#92400e; }
.st-alert-err  { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; }
.st-alert-info { background:#e0f2fe; border:1px solid #7dd3fc; color:#0c4a6e; }

/* submit area */
.btn-submit { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; padding:11px 32px; border:none; border-radius:8px; font-weight:700; cursor:pointer; font-size:.92rem; transition:opacity .2s; }
.btn-submit:hover { opacity:.88; }
.btn-cancel { background:#fff; color:#6b7280; padding:11px 28px; border:1px solid #d1d5db; border-radius:8px; font-weight:600; cursor:pointer; font-size:.92rem; text-decoration:none; display:inline-flex; align-items:center; }
.btn-cancel:hover { background:#f9fafb; color:#374151; }

/* select2 overrides */
.select2-container--default .select2-selection--single { border:1px solid #d1d5db; border-radius:6px; height:36px; }
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height:36px; font-size:.86rem; padding-left:10px; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height:34px; }
.select2-container { width:100% !important; }
.select2-container--default .select2-results__option--highlighted { background:#4f46e5; }
.select2-dropdown { border:1px solid #d1d5db; border-radius:8px; box-shadow:0 4px 16px rgba(0,0,0,.12); }
</style>

<div class="container-fluid py-4">
<div class="st-wrapper">
<div class="st-card">

    {{-- HEADER --}}
    <div class="st-header">
        <h4><i class="fas fa-exchange-alt me-2"></i> New Stock Transfer</h4>
        <p>Transfer inventory between warehouses and branch shops &mdash; ERP controlled</p>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('error'))
    <div class="st-alert st-alert-err" style="display:block; margin:16px 28px 0;">
        <i class="fas fa-times-circle me-1"></i> {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('stock_transfers.store') }}" method="POST" id="transferForm" autocomplete="off">
    @csrf

    {{-- ══════════ SECTION 1: BRANCH (Super Admin only) ══════════ --}}
    @if($isSuperAdmin)
    <div class="st-section">
        <div class="st-section-title">
            <span class="badge-num">1</span> Branch Selection
        </div>
        <div class="row g-3">
            <div class="col-md-5">
                <label class="f-label"><i class="fas fa-code-branch me-1 text-indigo-500"></i> Select Branch <span class="text-danger">*</span></label>
                <select name="branch_id" id="branch_id_select" class="f-control" required>
                    <option value="">— Select Branch —</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">🏪 {{ $b->name ?? $b->branch_name ?? 'Branch #'.$b->id }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-7 d-flex align-items-end">
                <div class="st-alert st-alert-info w-100" id="branch_hint" style="display:block; margin-bottom:0;">
                    <i class="fas fa-info-circle me-1"></i> Select a branch first — product source locations will load based on that branch's stock.
                </div>
            </div>
        </div>
    </div>
    @else
    <input type="hidden" name="branch_id" value="{{ $currentBranchId }}">
    @endif

    {{-- ══════════ SECTION 2: ITEMS TO TRANSFER ══════════ --}}
    <div class="st-section">
        <div class="st-section-title">
            <span class="badge-num">{{ $isSuperAdmin ? 2 : 1 }}</span> Items to Transfer
            <span style="font-size:.75rem; color:#6b7280; font-weight:500; text-transform:none;">Select product → choose source location → enter quantity</span>
        </div>

        <div class="st-alert st-alert-err" id="stock_alert"></div>

        <div class="table-responsive">
        <table class="items-table" id="product_table">
            <thead>
                <tr>
                    <th style="width:30%">Product / Item</th>
                    <th style="width:28%">Transfer From (Source)</th>
                    <th style="width:14%; text-align:right;">Available Qty</th>
                    <th style="width:18%; text-align:right;">Transfer Qty</th>
                    <th style="width:10%; text-align:center;">&nbsp;</th>
                </tr>
            </thead>
            <tbody id="product_body">
                <tr class="product_row">
                    <td>
                        <select name="product_id[]" class="product-select">
                            <option value="">— Search & Select Item —</option>
                            @foreach($primaryProducts as $p)
                                <option value="{{ $p->id }}" data-group="primary">✓ {{ $p->item_name }} ({{ $p->item_code }})</option>
                            @endforeach
                            @foreach($secondaryProducts as $p)
                                <option value="{{ $p->id }}" data-group="secondary">{{ $p->item_name }} ({{ $p->item_code }})</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="from_location[]" class="source-select">
                            <option value="">— Select Product First —</option>
                        </select>
                    </td>
                    <td class="cell-stock">
                        <input type="text" class="avail-stock" readonly value="—" style="text-align:right;">
                    </td>
                    <td>
                        <input type="number" name="quantity[]" class="transfer-qty" min="0.01" step="0.01" placeholder="0.00" style="text-align:right;">
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn-del remove-row" title="Remove row"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>

        <button type="button" class="btn-add-row" id="add_row_btn">
            <i class="fas fa-plus me-1"></i> Add Another Item
        </button>
    </div>

    {{-- ══════════ SECTION 3: DESTINATION ══════════ --}}
    <div class="st-section">
        <div class="st-section-title">
            <span class="badge-num">{{ $isSuperAdmin ? 3 : 2 }}</span> Transfer Destination
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="f-label">Destination Type <span class="text-danger">*</span></label>
                <div class="type-pills mt-1">
                    <label class="type-pill selected" id="pill_warehouse">
                        <input type="radio" name="to_type" value="warehouse" checked> 📦 Warehouse
                    </label>
                    <label class="type-pill" id="pill_branch">
                        <input type="radio" name="to_type" value="branch"> 🏪 Branch Shop
                    </label>
                </div>
            </div>
            <div class="col-md-4" id="dest_warehouse_col">
                <label class="f-label">Destination Warehouse <span class="text-danger">*</span></label>
                <select id="to_location_id" class="f-control dest-select2">
                    <option value="">— Select Warehouse —</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">📦 {{ $wh->warehouse_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4" id="dest_branch_col" style="display:none;">
                <label class="f-label">Destination</label>
                <div style="padding:10px 14px; background:#f0fdf4; border:1px solid #86efac; border-radius:8px; color:#166534; font-size:.88rem;">
                    <i class="fas fa-store me-1"></i> Branch main shop stock
                </div>
            </div>
        </div>

        {{-- hidden destination fields --}}
        <input type="hidden" name="to_warehouse_id" id="to_warehouse_id_field" value="">
        <input type="hidden" name="to_shop" id="to_shop" value="0">
    </div>

    {{-- ══════════ SECTION 4: REMARKS ══════════ --}}
    <div class="st-section">
        <div class="st-section-title">
            <span class="badge-num">{{ $isSuperAdmin ? 4 : 3 }}</span> Remarks / Notes
        </div>
        <textarea name="remarks" rows="3" class="f-control" placeholder="Transfer reason, instructions, or any internal notes…"></textarea>
    </div>

    {{-- ══════════ ACTIONS ══════════ --}}
    <div class="st-section" style="padding:18px 28px;">
        <div class="d-flex justify-content-end gap-3">
            <a href="{{ route('stock_transfers.index') }}" class="btn-cancel">
                <i class="fas fa-times me-2"></i> Discard
            </a>
            <button type="submit" class="btn-submit" id="submitBtn">
                <i class="fas fa-paper-plane me-2"></i> Save Transfer
            </button>
        </div>
    </div>

    </form>
</div>{{-- st-card --}}
</div>{{-- st-wrapper --}}
</div>{{-- container --}}

{{-- ── Row Template ── --}}
<template id="row_tpl">
<tr class="product_row">
    <td>
        <select name="product_id[]" class="product-select">
            <option value="">— Search & Select Item —</option>
            @foreach($primaryProducts as $p)
                <option value="{{ $p->id }}" data-group="primary">✓ {{ $p->item_name }} ({{ $p->item_code }})</option>
            @endforeach
            @foreach($secondaryProducts as $p)
                <option value="{{ $p->id }}" data-group="secondary">{{ $p->item_name }} ({{ $p->item_code }})</option>
            @endforeach
        </select>
    </td>
    <td>
        <select name="from_location[]" class="source-select">
            <option value="">— Select Product First —</option>
        </select>
    </td>
    <td class="cell-stock">
        <input type="text" class="avail-stock" readonly value="—" style="text-align:right;">
    </td>
    <td>
        <input type="number" name="quantity[]" class="transfer-qty" min="0.01" step="0.01" placeholder="0.00" style="text-align:right;">
    </td>
    <td style="text-align:center;">
        <button type="button" class="btn-del remove-row"><i class="fas fa-trash-alt"></i></button>
    </td>
</tr>
</template>
@endsection

@section('js')
<script>
$(function () {

    /* ─── Constants ─── */
    const IS_SUPER = {{ $isSuperAdmin ? 'true' : 'false' }};
    const CURRENT_BRANCH = '{{ $currentBranchId ?? "" }}';

    /* ─── Helpers ─── */
    function activeBranch() {
        return IS_SUPER ? $('#branch_id_select').val() : CURRENT_BRANCH;
    }

    function initProductSelect2($sel) {
        $sel.select2({ placeholder: 'Search item…', width: '100%', allowClear: false });
    }

    function initSourceSelect2($sel) {
        $sel.select2({ placeholder: 'Select source…', width: '100%', allowClear: false });
    }

    /* ─── Init first row ─── */
    initProductSelect2($('.product-select'));
    initSourceSelect2($('.source-select'));
    $('.dest-select2').select2({ placeholder: '— Select Warehouse —', width: '100%' });
    if (IS_SUPER) {
        $('#branch_id_select').select2({ placeholder: '— Select Branch —', width: '100%' });
    }

    /* ─── AJAX: load source locations for a row ─── */
    function loadSourceLocations($row) {
        const pid     = $row.find('.product-select').val();
        const bid     = activeBranch();
        const $src    = $row.find('.source-select');
        const $avail  = $row.find('.avail-stock');
        const $qty    = $row.find('.transfer-qty');

        /* reset */
        $avail.val('—').removeClass('has-stock no-stock');
        $qty.removeAttr('max').val('');

        if (!pid) {
            $src.html('<option value="">— Select Product First —</option>').trigger('change');
            return;
        }
        if (!bid) {
            $src.html('<option value="">— Select Branch First —</option>').trigger('change');
            return;
        }

        $src.html('<option value="">⏳ Loading…</option>').trigger('change');

        $.ajax({
            url  : '/product-locations-stock',
            type : 'GET',
            data : { product_id: pid, branch_id: bid },
            success: function (data) {
                $src.empty().append('<option value="">— Choose Source Location —</option>');

                if (!data || data.length === 0) {
                    $src.html('<option value="">⚠ No stock locations found</option>').trigger('change');
                    return;
                }

                $.each(data, function (i, loc) {
                    const qty  = parseFloat(loc.quantity) || 0;
                    const icon = qty > 0 ? '✅' : '⚠';
                    const disabled = qty <= 0 ? 'disabled' : '';
                    $src.append(
                        `<option value="${loc.value}" data-qty="${qty}" ${disabled}>
                            ${icon} ${loc.label} — Qty: ${qty.toFixed(2)}
                        </option>`
                    );
                });

                /* auto-select if only one valid option */
                const $valid = $src.find('option:not([disabled]):not([value=""])');
                if ($valid.length === 1) {
                    $src.val($valid.first().val()).trigger('change');
                } else {
                    $src.trigger('change');
                }
            },
            error: function () {
                $src.html('<option value="">⚠ Error loading locations</option>').trigger('change');
            }
        });
    }

    /* ─── Product selected → load sources ─── */
    $(document).on('select2:select', '.product-select', function () {
        loadSourceLocations($(this).closest('tr'));
    });

    /* ─── Source selected → show available qty ─── */
    $(document).on('change', '.source-select', function () {
        const $row   = $(this).closest('tr');
        const $opt   = $(this).find('option:selected');
        const qty    = parseFloat($opt.data('qty')) || 0;
        const $avail = $row.find('.avail-stock');
        const $qty   = $row.find('.transfer-qty');

        if ($opt.val()) {
            $avail.val(qty.toFixed(2));
            if (qty > 0) {
                $avail.addClass('has-stock').removeClass('no-stock');
                $qty.attr('max', qty);
            } else {
                $avail.addClass('no-stock').removeClass('has-stock');
                $qty.removeAttr('max');
            }
        } else {
            $avail.val('—').removeClass('has-stock no-stock');
            $qty.removeAttr('max').val('');
        }
    });

    /* ─── Qty input: cap at max ─── */
    $(document).on('input', '.transfer-qty', function () {
        const max = parseFloat($(this).attr('max')) || 0;
        const val = parseFloat($(this).val()) || 0;
        if (max > 0 && val > max) {
            $(this).val(max.toFixed(2));
            $('#stock_alert').html('<i class="fas fa-exclamation-triangle me-1"></i> Quantity capped at available stock.').show();
            setTimeout(() => $('#stock_alert').fadeOut(), 3000);
        }
    });

    /* ─── Branch changed (super admin) ─── */
    if (IS_SUPER) {
        $(document).on('change', '#branch_id_select', function () {
            /* reload all row source locations */
            $('#product_body .product_row').each(function () {
                if ($(this).find('.product-select').val()) {
                    loadSourceLocations($(this));
                }
            });
            /* update branch hint */
            if ($(this).val()) {
                $('#branch_hint').hide();
            } else {
                $('#branch_hint').show();
            }
        });
    }

    /* ─── Destination type radio ─── */
    $('input[name="to_type"]').on('change', function () {
        const type = $(this).val();

        /* update pill styles */
        $('.type-pill').removeClass('selected');
        $(this).closest('.type-pill').addClass('selected');

        if (type === 'branch') {
            $('#dest_warehouse_col').hide();
            $('#dest_branch_col').show();
            $('#to_location_id').prop('required', false);
            $('#to_shop').val(1);
            $('#to_warehouse_id_field').val('');
        } else {
            $('#dest_warehouse_col').show();
            $('#dest_branch_col').hide();
            $('#to_location_id').prop('required', true);
            $('#to_shop').val(0);
        }
    });

    /* sync dest warehouse hidden field */
    $(document).on('change', '#to_location_id', function () {
        $('#to_warehouse_id_field').val($(this).val());
    });

    /* ─── Add / remove rows ─── */
    $('#add_row_btn').on('click', function () {
        const clone = document.getElementById('row_tpl').content.cloneNode(true);
        $('#product_body').append(clone);
        const $newRow = $('#product_body .product_row:last-child');
        initProductSelect2($newRow.find('.product-select'));
        initSourceSelect2($newRow.find('.source-select'));
    });

    $(document).on('click', '.remove-row', function () {
        if ($('#product_body .product_row').length > 1) {
            $(this).closest('tr').remove();
        }
    });

    /* ─── Form validation ─── */
    $('#transferForm').on('submit', function (e) {

        @if($isSuperAdmin)
        if (!$('#branch_id_select').val()) {
            e.preventDefault();
            alert('❌ Please select a branch before saving the transfer.');
            $('#branch_id_select').focus();
            return false;
        }
        @endif

        const toType = $('input[name="to_type"]:checked').val();
        const toWarehouse = $('#to_location_id').val();

        if (toType === 'warehouse' && !toWarehouse) {
            e.preventDefault();
            alert('❌ Please select a destination warehouse.');
            return false;
        }

        /* make sure to_warehouse_id is set */
        if (toType === 'warehouse') {
            $('#to_warehouse_id_field').val(toWarehouse);
        }

        let hasValid   = false;
        let missSrc    = false;
        let zeroQty    = false;

        $('#product_body .product_row').each(function () {
            const pid = $(this).find('.product-select').val();
            const src = $(this).find('.source-select').val();
            const qty = parseFloat($(this).find('.transfer-qty').val()) || 0;

            if (pid) {
                if (!src)    missSrc  = true;
                if (qty > 0) hasValid = true;
                if (qty <= 0 && src) zeroQty = true;
            }
        });

        if (!hasValid) {
            e.preventDefault();
            alert('❌ Please add at least one item with a valid quantity.');
            return false;
        }
        if (missSrc) {
            e.preventDefault();
            alert('❌ Please select a source location for each product row.');
            return false;
        }
    });

});
</script>
@endsection