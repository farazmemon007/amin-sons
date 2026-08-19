@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --coa-navy: #1e3a5f;
        --coa-navy-dark: #0f1f38;
        --coa-navy-light: #2c5282;
        --coa-gold: #c8973a;
        --coa-emerald: #0d9f6e;
        --coa-border: #e2e8f0;
    }

    .rpt-wrapper {
        padding: 12px 0 30px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .rpt-header-bar {
        background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 60%, var(--coa-navy-light) 100%);
        border-radius: 10px;
        padding: 16px 22px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        box-shadow: 0 4px 15px rgba(15, 31, 56, 0.15);
        margin-bottom: 18px;
    }

    .rpt-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--coa-gold);
        border: 1px solid rgba(200, 151, 58, 0.3);
        flex-shrink: 0;
    }

    .rpt-header-title {
        font-size: 18px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        line-height: 1.2;
    }

    .rpt-header-sub {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.85);
        margin-top: 3px;
    }

    .f-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.04em;
        margin-bottom: 5px;
        display: block;
    }

    .rpt-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    @media (max-width: 992px) {
        .rpt-kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .rpt-kpi-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 13px 16px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .rpt-kpi-card.highlight {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #a7f3d0;
    }

    .rpt-kpi-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .rpt-kpi-val {
        font-size: 18px;
        font-weight: 800;
        color: var(--coa-navy);
        font-family: monospace;
    }

    .rpt-kpi-val.emerald { color: #047857; }
    .rpt-kpi-val.purple { color: #7c3aed; }
    .rpt-kpi-val.amber { color: #d97706; }

    .rpt-kpi-icon {
        width: 38px;
        height: 38px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .kpi-icon-blue { background: #e0f2fe; color: #0284c7; }
    .kpi-icon-emerald { background: #d1fae5; color: #059669; }
    .kpi-icon-purple { background: #f3e8ff; color: #7c3aed; }
    .kpi-icon-amber { background: #fef3c7; color: #d97706; }

    #onhandTable {
        border-collapse: collapse;
        width: 100%;
    }

    #onhandTable thead th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 10px;
        border: 1px solid #1e3a5f;
    }

    #onhandTable tbody td {
        padding: 8px 10px;
        vertical-align: middle;
        border: 1px solid #e2e8f0;
        font-size: 12px;
    }

    #onhandTable tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    #onhandTable tbody tr:hover {
        background-color: #f1f5f9 !important;
    }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">
            
            {{-- 1. Corporate Header Bar --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Inventory On-Hand Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-warehouse mr-1" style="color: var(--coa-gold);"></i> Real-time Stock Valuation & Availability Status &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" onclick="shareWhatsApp()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(37, 211, 102, 0.2); border-color: #25D366; color: #25D366;">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </button>
                    <button type="button" onclick="showExportOptions()" class="btn btn-sm btn-light font-weight-bold text-dark border">
                        <i class="fas fa-download mr-1 text-primary"></i> Export
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-light font-weight-bold">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                </div>
            </div>

            {{-- 2. ERP Standard Filter Card --}}
            <div class="card shadow-sm border-0 mb-3" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form id="onhandFilterForm" class="row g-2 align-items-end mb-0">
                        @if($isSuper)
                            {{-- SUPER ADMIN: 3 FILTERS (BRANCH, WAREHOUSE, PRODUCT) --}}
                            <div class="col-md-3">
                                <label class="f-label"><i class="fas fa-building mr-1 text-primary"></i> Select Branch</label>
                                <select id="filter_branch" name="branch_id" class="form-control form-control-sm select2">
                                    <option value="all">-- All Branches --</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ (isset($branchId) && $branchId == $b->id) ? 'selected' : '' }}>
                                            {{ $b->name ?? $b->branch_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="f-label"><i class="fas fa-warehouse mr-1 text-info"></i> Select Warehouse</label>
                                <select id="filter_warehouse" name="warehouse_id" class="form-control form-control-sm select2">
                                    <option value="all">-- All Warehouses --</option>
                                    @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}" {{ (isset($warehouseId) && $warehouseId == $w->id) ? 'selected' : '' }}>
                                            {{ $w->warehouse_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="f-label"><i class="fas fa-box-open mr-1 text-secondary"></i> Select Product</label>
                                <select id="filter_product" name="product_id" class="form-control form-control-sm select2">
                                    <option value="all">-- All Products --</option>
                                    @foreach($allProducts as $p)
                                        <option value="{{ $p->id }}" {{ (isset($productId) && $productId == $p->id) ? 'selected' : '' }}>
                                            {{ $p->item_name }} ({{ $p->item_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 d-flex gap-2">
                                <button type="button" id="btnSearch" class="btn btn-sm btn-primary flex-grow-1 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                    <i class="fas fa-search mr-1"></i> Filter
                                </button>
                                <button type="button" id="btnReset" class="btn btn-sm btn-light border font-weight-bold text-muted d-inline-flex align-items-center justify-content-center" style="height: 38px; border-radius: 6px; width: 38px;" title="Reset Filters">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        @else
                            {{-- BRANCH USER: 2 FILTERS (WAREHOUSE, PRODUCT) --}}
                            <input type="hidden" id="filter_branch" name="branch_id" value="{{ $branchId }}">

                            <div class="col-md-5">
                                <label class="f-label"><i class="fas fa-warehouse mr-1 text-info"></i> Select Warehouse</label>
                                <select id="filter_warehouse" name="warehouse_id" class="form-control form-control-sm select2">
                                    <option value="all">-- All Warehouses --</option>
                                    @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}" {{ (isset($warehouseId) && $warehouseId == $w->id) ? 'selected' : '' }}>
                                            {{ $w->warehouse_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-5">
                                <label class="f-label"><i class="fas fa-box-open mr-1 text-secondary"></i> Select Product</label>
                                <select id="filter_product" name="product_id" class="form-control form-control-sm select2">
                                    <option value="all">-- All Products --</option>
                                    @foreach($allProducts as $p)
                                        <option value="{{ $p->id }}" {{ (isset($productId) && $productId == $p->id) ? 'selected' : '' }}>
                                            {{ $p->item_name }} ({{ $p->item_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 d-flex gap-2">
                                <button type="button" id="btnSearch" class="btn btn-sm btn-primary flex-grow-1 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                    <i class="fas fa-search mr-1"></i> Filter
                                </button>
                                <button type="button" id="btnReset" class="btn btn-sm btn-light border font-weight-bold text-muted d-inline-flex align-items-center justify-content-center" style="height: 38px; border-radius: 6px; width: 38px;" title="Reset Filters">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- 3. Summary KPI Cards --}}
            @php
                $totalItems = $rows->count();
                $totalQty = $rows->sum('onhand_qty');
                $partsCount = $rows->where('is_part', 1)->count();
                $assembledCount = $rows->where('is_assembled', 1)->count();
            @endphp
            <div class="rpt-kpi-grid">
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Products</div>
                        <div class="rpt-kpi-val" id="stat_total_items">{{ number_format($totalItems) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-blue">
                        <i class="fas fa-cube"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card highlight">
                    <div>
                        <div class="rpt-kpi-label" style="color: #047857;">Total On-Hand Qty</div>
                        <div class="rpt-kpi-val emerald" id="stat_total_qty">{{ number_format($totalQty, 2) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-cubes"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Parts Items</div>
                        <div class="rpt-kpi-val purple" id="stat_parts_count">{{ number_format($partsCount) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-purple">
                        <i class="fas fa-cogs"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Assembled Items</div>
                        <div class="rpt-kpi-val amber" id="stat_assembled_count">{{ number_format($assembledCount) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-amber">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Report Table --}}
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="reportContent">
                <div class="card-body p-3">
                    
                    {{-- PDF HEADER (HIDDEN ON SCREEN) --}}
                    <div id="pdfHeader" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #0f1f38; padding-bottom:10px;">
                        <h2 style="margin:0; color:#0f1f38; text-transform:uppercase; letter-spacing:1px;">Inventory On-Hand Report</h2>
                        <p style="margin:5px 0; font-size:13px; color:#333;" id="pdfFilterInfo">Real-time Stock Availability Status</p>
                        <p style="margin:0; font-size:11.5px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div id="loader" style="display:none;text-align:center;padding:30px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Updating inventory balance...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="onhandTable" class="table table-bordered mb-0" style="font-size:12.5px; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 40px;">#</th>
                                    <th style="width: 120px; white-space: nowrap;">Item Code</th>
                                    <th>Product Name</th>
                                    <th style="width: 140px;">Brand</th>
                                    <th style="width: 100px;">UOM</th>
                                    <th class="text-center" style="width: 110px;">Status</th>
                                    <th class="text-end" style="width: 140px; white-space: nowrap;">On-Hand Qty</th>
                                </tr>
                            </thead>
                            <tbody id="onhandBody">
                                @forelse($rows as $i => $r)
                                <tr>
                                    <td class="text-center text-muted" style="font-size: 11px;">{{ $i + 1 }}</td>
                                    <td style="font-family: monospace; font-weight: 700; color: #1e3a5f; font-size: 12px;">{{ $r->item_code }}</td>
                                    <td class="font-weight-bold text-dark" style="font-size: 12.5px;">{{ $r->item_name }}</td>
                                    <td>{{ $r->brand_name ?: '—' }}</td>
                                    <td>{{ $r->unit_name ?: 'Piece' }}</td>
                                    <td class="text-center">
                                        @if($r->is_part)
                                            <span class="badge" style="background:#ede9fe; color:#6d28d9; border:1px solid #ddd6fe; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:4px;">Part</span>
                                        @elseif($r->is_assembled)
                                            <span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:4px;">Assembled</span>
                                        @else
                                            <span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:4px;">Simple</span>
                                        @endif
                                    </td>
                                    <td class="text-end font-monospace font-weight-bold" style="font-size: 13px; color: {{ $r->onhand_qty > 0 ? '#047857' : '#dc2626' }}; background: {{ $r->onhand_qty > 0 ? 'rgba(13, 159, 110, 0.06)' : 'rgba(220, 38, 38, 0.03)' }};">
                                        {{ number_format($r->onhand_qty, 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No stock items found for selected filter criteria.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                                    <td colspan="6" class="text-end font-weight-bold" style="font-family: sans-serif;">Total On-Hand:</td>
                                    <td class="text-end text-success font-weight-bold" id="footerTotalQty" style="font-size: 13.5px; background: rgba(13, 159, 110, 0.12);">
                                        {{ number_format($totalQty, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .lbl { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
    
    @media print {
        #onhandFilterForm, .btn, .rpt-header-bar, .rpt-kpi-grid { display: none !important; }
        .main-content { padding: 0 !important; margin-top:0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        #reportContent { overflow: visible !important; }
    }
</style>
@endsection

@section('js')
<script>
    const isSuperAdmin = {{ $isSuper ? 'true' : 'false' }};

    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            dropdownParent: $('body')
        });

        // Dynamic Warehouse Loading when Branch changes (for Super Admin)
        $('#filter_branch').on('change', function() {
            let branchId = $(this).val();
            let warehouseSelect = $('#filter_warehouse');

            warehouseSelect.html('<option value="all">Loading...</option>');

            if (branchId === 'all' || !branchId) {
                // Fetch all warehouses
                $.ajax({
                    url: "{{ route('warehouses-by-branch') }}",
                    type: "GET",
                    data: { branch_id: '' },
                    success: function(res) {
                        let html = '<option value="all">-- All Warehouses --</option>';
                        res.forEach(function(w) {
                            html += `<option value="${w.id}">${w.warehouse_name}</option>`;
                        });
                        warehouseSelect.html(html).trigger('change');
                    },
                    error: function() {
                        warehouseSelect.html('<option value="all">-- All Warehouses --</option>').trigger('change');
                    }
                });
            } else {
                $.ajax({
                    url: "{{ route('warehouses-by-branch') }}",
                    type: "GET",
                    data: { branch_id: branchId },
                    success: function(res) {
                        let html = '<option value="all">-- All Warehouses --</option>';
                        res.forEach(function(w) {
                            html += `<option value="${w.id}">${w.warehouse_name}</option>`;
                        });
                        warehouseSelect.html(html).trigger('change');
                    },
                    error: function() {
                        warehouseSelect.html('<option value="all">-- All Warehouses --</option>').trigger('change');
                    }
                });
            }
        });

        // Filter Click
        $('#btnSearch').on('click', function() {
            fetchOnhandReport();
        });

        // Reset Click
        $('#btnReset').on('click', function() {
            if (isSuperAdmin) {
                $('#filter_branch').val('all').trigger('change');
            }
            $('#filter_warehouse').val('all').trigger('change');
            $('#filter_product').val('all').trigger('change');
            setTimeout(fetchOnhandReport, 200);
        });

        function fetchOnhandReport() {
            let branchId = $('#filter_branch').val() || '';
            let warehouseId = $('#filter_warehouse').val() || '';
            let productId = $('#filter_product').val() || '';

            $('#loader').show();

            $.ajax({
                url: "{{ route('reports.onhand') }}",
                type: "GET",
                data: {
                    branch_id: branchId,
                    warehouse_id: warehouseId,
                    product_id: productId
                },
                success: function(res) {
                    $('#loader').hide();
                    renderOnhandTable(res);
                },
                error: function() {
                    $('#loader').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch inventory report data.'
                    });
                }
            });
        }

        function renderOnhandTable(data) {
            // Update KPIs
            $('#stat_total_items').text(Number(data.totalItems).toLocaleString());
            $('#stat_total_qty').text(Number(data.totalQty).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#stat_parts_count').text(Number(data.partsCount).toLocaleString());
            $('#stat_assembled_count').text(Number(data.assembledCount).toLocaleString());
            $('#footerTotalQty').text(Number(data.totalQty).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            let rows = data.rows || [];
            let tbodyHtml = '';

            if (rows.length === 0) {
                tbodyHtml = '<tr><td colspan="7" class="text-center py-4 text-muted">No stock items found for selected filter criteria.</td></tr>';
            } else {
                rows.forEach((r, idx) => {
                    let qty = parseFloat(r.onhand_qty) || 0;
                    let qtyColor = qty > 0 ? '#047857' : '#dc2626';
                    let qtyBg = qty > 0 ? 'rgba(13, 159, 110, 0.06)' : 'rgba(220, 38, 38, 0.03)';

                    let statusBadge = '';
                    if (r.is_part == 1) {
                        statusBadge = '<span class="badge" style="background:#ede9fe; color:#6d28d9; border:1px solid #ddd6fe; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:4px;">Part</span>';
                    } else if (r.is_assembled == 1) {
                        statusBadge = '<span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:4px;">Assembled</span>';
                    } else {
                        statusBadge = '<span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; font-size:10.5px; font-weight:700; padding:3px 8px; border-radius:4px;">Simple</span>';
                    }

                    tbodyHtml += `<tr>
                        <td class="text-center text-muted" style="font-size: 11px;">${idx + 1}</td>
                        <td style="font-family: monospace; font-weight: 700; color: #1e3a5f; font-size: 12px;">${r.item_code}</td>
                        <td class="font-weight-bold text-dark" style="font-size: 12.5px;">${r.item_name}</td>
                        <td>${r.brand_name || '—'}</td>
                        <td>${r.unit_name || 'Piece'}</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-end font-monospace font-weight-bold" style="font-size: 13px; color: ${qtyColor}; background: ${qtyBg};">
                            ${qty.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                        </td>
                    </tr>`;
                });
            }

            $('#onhandBody').html(tbodyHtml);

            // Update PDF filter subtitle
            let filterDesc = [];
            if (isSuperAdmin && $('#filter_branch').val() !== 'all') {
                filterDesc.push('Branch: ' + $('#filter_branch option:selected').text().trim());
            }
            if ($('#filter_warehouse').val() !== 'all') {
                filterDesc.push('Warehouse: ' + $('#filter_warehouse option:selected').text().trim());
            }
            if ($('#filter_product').val() !== 'all') {
                filterDesc.push('Product: ' + $('#filter_product option:selected').text().trim());
            }
            $('#pdfFilterInfo').text(filterDesc.length > 0 ? filterDesc.join(' | ') : 'All Branches & Warehouses');
        }
        
        /* ---------- WhatsApp Share ---------- */
        window.shareWhatsApp = function() {
            Swal.fire({
                title: 'Preparing WhatsApp Share...',
                text: 'Generating PDF document to share.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            var element = document.getElementById('reportContent');
            $('#pdfHeader').show(); 

            var opt = {
                margin:       0.2,
                filename:     'Onhand_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
                $('#pdfHeader').hide(); 
                var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
                
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    navigator.share({
                        title: 'Inventory Report',
                        text: 'Please find the attached Inventory On-Hand Report.',
                        files: [file]
                    }).then(() => {
                        Swal.close();
                    }).catch((error) => {
                        fallbackWaShare(pdfBlob, opt.filename);
                    });
                } else {
                    fallbackWaShare(pdfBlob, opt.filename);
                }
            });
        };

        function fallbackWaShare(pdfBlob, filename) {
            Swal.fire({
                icon: 'info',
                title: 'Share PDF via WhatsApp',
                text: 'The PDF will be downloaded. Please attach it manually to your WhatsApp chat.',
                confirmButtonText: 'Download & Open WhatsApp'
            }).then(() => {
                var url = URL.createObjectURL(pdfBlob);
                var a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                
                var msg = "*Inventory On-Hand Report*\nPlease find the attached PDF document.";
                var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
                window.open(waUrl, '_blank');
            });
        }

        /* ---------- Export Options ---------- */
        window.showExportOptions = function() {
            Swal.fire({
                title: 'Export Inventory Report',
                text: 'Choose your format:',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                confirmButtonText: '<i class="fas fa-file-excel me-1"></i> Excel (CSV)',
                cancelButtonText: '<i class="fas fa-file-pdf me-1"></i> PDF',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    exportCSV();
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    exportPDF();
                }
            });
        };

        window.exportPDF = function() {
            Swal.fire({
                title: 'Generating PDF...',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            var element = document.getElementById('reportContent');
            $('#pdfHeader').show(); 

            var opt = {
                margin:       0.2,
                filename:     'Onhand_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                $('#pdfHeader').hide(); 
                Swal.close();
            });
        };

        window.exportCSV = function () {
            let csv = [];
            let headers = [];
            $("#onhandTable thead th").each(function() {
                headers.push('"' + $(this).text().trim() + '"');
            });
            csv.push(headers.join(","));

            $("#onhandTable tbody tr").each(function() {
                let row = [];
                $(this).find('td').each(function() {
                    let cellText = $(this).text().trim();
                    row.push('"' + cellText.replace(/"/g, '""') + '"');
                });
                csv.push(row.join(","));
            });

            // Add footer
            let footer = [];
            $("#onhandTable tfoot td").each(function() {
                footer.push('"' + $(this).text().trim() + '"');
            });
            csv.push(footer.join(","));

            let csvString = "\uFEFF" + csv.join("\n");
            let blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            let url = URL.createObjectURL(blob);
            let a = document.createElement("a");
            a.href = url;
            a.download = "onhand_report_" + new Date().toISOString().slice(0,10) + ".csv";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        };
    });
</script>
@endsection
