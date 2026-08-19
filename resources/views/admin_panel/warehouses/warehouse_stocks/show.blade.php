@extends('admin_panel.layout.app')

@section('content')
@php
    $isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
    $totalQtySafe = ($totalQty > 0) ? $totalQty : 1;
@endphp

{{-- SweetAlert: Show warning when product stock is zero --}}
@if($totalQty <= 0)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(!$hasEverHadStock)
        Swal.fire({
            icon: 'info',
            title: 'No Opening Stock',
            html: '<p style="color:#64748b;font-size:14px;">This product has <strong>never been added to opening stock</strong>.<br>Please add opening stock first before viewing warehouse distribution.</p>',
            confirmButtonText: '<i class="fas fa-plus-circle"></i> Add Opening Stock',
            cancelButtonText: 'Close',
            showCancelButton: true,
            confirmButtonColor: '#1e3a5f',
            cancelButtonColor: '#94a3b8',
        }).then(function(result) {
            if (result.isConfirmed) {
                window.location.href = '{{ route("opening.stocks.index") }}';
            }
        });
        @else
        Swal.fire({
            icon: 'warning',
            title: 'Zero Stock',
            html: '<p style="color:#64748b;font-size:14px;">This product currently has <strong>0 quantity</strong> in all warehouses.<br>Stock may have been fully sold or transferred out.</p>',
            confirmButtonText: 'OK',
            confirmButtonColor: '#1e3a5f',
        });
        @endif
    });
</script>
@endif

<style>
    :root {
        --coa-navy: #1e3a5f;
        --coa-navy-dark: #0f1f38;
        --coa-navy-light: #2c5282;
        --coa-gold: #c8973a;
        --coa-emerald: #0d9f6e;
        --coa-amber: #d97706;
        --coa-crimson: #dc2626;
        --coa-border: #e2e8f0;
    }

    .wh-show-wrapper {
        padding: 12px 0 30px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* ── 1. Corporate Header Bar ── */
    .product-header-bar {
        background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 60%, var(--coa-navy-light) 100%);
        border-radius: 10px;
        padding: 18px 22px;
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(15, 31, 56, 0.15);
        margin-bottom: 18px;
    }

    .back-btn-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #ffffff !important;
        background: rgba(255, 255, 255, 0.12);
        padding: 5px 12px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        text-decoration: none !important;
        margin-bottom: 14px;
        transition: all 0.15s;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .back-btn-link:hover {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff !important;
    }

    .product-header-content {
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .product-image-container {
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px solid rgba(200, 151, 58, 0.4);
        flex-shrink: 0;
    }

    .product-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-details {
        flex: 1;
    }

    .product-name-title {
        font-size: 19px;
        font-weight: 800;
        color: #ffffff !important;
        margin-bottom: 6px;
        line-height: 1.2;
    }

    .product-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 12px;
    }

    .meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.12);
        padding: 3px 10px;
        border-radius: 5px;
        color: #ffffff;
        font-size: 11.5px;
    }

    .meta-chip.gold {
        background: rgba(200, 151, 58, 0.25);
        color: #fef08a;
        font-weight: 700;
        border: 1px solid rgba(200, 151, 58, 0.5);
    }

    /* ── 2. KPI Summary Grid ── */
    .show-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }

    .show-kpi-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 12px 15px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .show-kpi-card.highlight {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #a7f3d0;
    }

    .show-kpi-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .show-kpi-val {
        font-size: 18px;
        font-weight: 800;
        color: var(--coa-navy);
        font-family: monospace;
    }

    .show-kpi-val.emerald {
        color: #047857;
    }

    .show-kpi-val.amber {
        color: #b45309;
    }

    .show-kpi-icon {
        width: 36px;
        height: 36px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .kpi-icon-blue { background: #e0f2fe; color: #0284c7; }
    .kpi-icon-emerald { background: #d1fae5; color: #059669; }
    .kpi-icon-amber { background: #fef3c7; color: #d97706; }
    .kpi-icon-purple { background: #f3e8ff; color: #9333ea; }
    .kpi-icon-gold { background: #fef9c3; color: #ca8a04; }

    /* ── 3. Branch Filter Bar ── */
    .filter-branch-bar {
        background: #ffffff;
        border: 1px solid var(--coa-border);
        border-radius: 8px;
        padding: 10px 16px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    /* ── 4. Warehouse Distribution Cards ── */
    .distribution-card {
        background: #ffffff;
        border-radius: 9px;
        border: 1px solid var(--coa-border);
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        margin-bottom: 18px;
    }

    .distribution-header {
        background: #f8fafc;
        padding: 12px 18px;
        border-bottom: 1.5px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .distribution-title {
        font-size: 14px;
        font-weight: 800;
        color: var(--coa-navy);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .warehouse-item-row {
        padding: 14px 18px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s;
    }

    .warehouse-item-row:hover {
        background: #f8fafc;
    }

    .warehouse-item-row:last-child {
        border-bottom: none;
    }

    .wh-name-title {
        font-size: 13.5px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .wh-stock-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 12px;
        background: #f8fafc;
        padding: 10px 14px;
        border-radius: 6px;
        border: 1px solid #f1f5f9;
    }

    .wh-stock-metric {
        display: flex;
        flex-direction: column;
    }

    .wh-metric-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 2px;
    }

    .wh-metric-val {
        font-family: monospace;
        font-size: 13.5px;
        font-weight: 800;
        color: #0f172a;
    }

    .wh-metric-val.emerald { color: #047857; }
    .wh-metric-val.amber { color: #b45309; }

    .progress-bar-wrap {
        width: 100%;
        height: 6px;
        background: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 10px;
    }

    .progress-fill-bar {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        border-radius: 3px;
    }

    /* Customer Reserved Section */
    .cust-reserved-wrap {
        margin-top: 10px;
        padding: 10px 14px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 6px;
    }

    .cust-reserved-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
        font-weight: 700;
        color: #92400e;
        cursor: pointer;
        user-select: none;
    }

    .cust-reserved-btn i {
        transition: transform 0.2s;
    }

    .cust-reserved-btn.expanded i {
        transform: rotate(90deg);
    }

    .cust-reserved-body {
        display: none;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 1px dashed #fde68a;
    }

    .cust-reserved-body.show {
        display: block;
    }
</style>

<div class="main-content">
    <div class="wh-show-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Corporate Header Bar --}}
            <div class="product-header-bar">
                <a href="{{ route('warehouse_stocks.index') }}" class="back-btn-link">
                    <i class="fas fa-arrow-left"></i> Back to Inventory
                </a>

                <div class="product-header-content">
                    <div class="product-image-container">
                        @if($product->image)
                            <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->item_name }}">
                        @else
                            <i class="fas fa-cube fa-2x" style="color: var(--coa-gold);"></i>
                        @endif
                    </div>

                    <div class="product-details">
                        <h3 class="product-name-title">{{ $product->item_name }}</h3>
                        <div class="product-meta-row">
                            <span class="meta-chip">
                                <i class="fas fa-barcode text-warning"></i> Code: <strong>{{ $product->item_code }}</strong>
                            </span>
                            <span class="meta-chip">
                                <i class="fas fa-tag text-info"></i> {{ optional($product->category_relation)->name ?? 'Uncategorized' }}
                            </span>
                            @if($product->price)
                            <span class="meta-chip gold">
                                <i class="fas fa-coins mr-1"></i> PKR {{ number_format($product->price, 2) }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Branch Filter Bar --}}
            @if($showBranchFilter)
            <div class="filter-branch-bar">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-building" style="color: var(--coa-navy);"></i>
                    <span class="font-weight-bold text-dark small text-uppercase">Filter by Branch:</span>
                    <select id="branch_filter_sel" class="form-select form-select-sm" style="min-width: 200px; height: 34px; border-radius: 6px; border: 1.5px solid #cbd5e1; font-weight: 700; font-size: 12px;">
                        <option value="0" {{ $selectedBranchId == 0 ? 'selected' : '' }}>🌐 All Branches</option>
                        @foreach($availableBranches as $br)
                            <option value="{{ $br->id }}" {{ $br->id == $selectedBranchId ? 'selected' : '' }}>
                                🏢 {{ $br->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <small class="text-muted font-weight-bold">
                    Showing: <span class="text-primary font-weight-bold">{{ $selectedBranchId == 0 ? 'All Branches' : ($availableBranches->firstWhere('id', $selectedBranchId)?->name ?? 'All') }}</span>
                </small>
            </div>
            @endif

            {{-- 3. KPI Summary Statistics Grid --}}
            <div class="show-kpi-grid">
                <div class="show-kpi-card highlight">
                    <div>
                        <div class="show-kpi-label" style="color: #047857;">Total Quantity</div>
                        <div class="show-kpi-val emerald">{{ number_format($totalQty, 2) }}</div>
                    </div>
                    <div class="show-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
                <div class="show-kpi-card">
                    <div>
                        <div class="show-kpi-label">Available for Sale</div>
                        <div class="show-kpi-val emerald">{{ number_format($totalAvailable, 2) }}</div>
                    </div>
                    <div class="show-kpi-icon kpi-icon-blue">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="show-kpi-card">
                    <div>
                        <div class="show-kpi-label">Customer Committed</div>
                        <div class="show-kpi-val amber">{{ number_format($totalCustomerReserved, 2) }}</div>
                    </div>
                    <div class="show-kpi-icon kpi-icon-amber">
                        <i class="fas fa-user-tag"></i>
                    </div>
                </div>
                <div class="show-kpi-card">
                    <div>
                        <div class="show-kpi-label">System Reserved</div>
                        <div class="show-kpi-val">{{ number_format($totalReserved, 2) }}</div>
                    </div>
                    <div class="show-kpi-icon kpi-icon-purple">
                        <i class="fas fa-lock"></i>
                    </div>
                </div>
                <div class="show-kpi-card">
                    <div>
                        <div class="show-kpi-label">Stock Locations</div>
                        <div class="show-kpi-val">{{ count($warehouses) }}</div>
                    </div>
                    <div class="show-kpi-icon kpi-icon-gold">
                        <i class="fas fa-warehouse"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Warehouse Distribution Details --}}
            <div class="distribution-card">
                <div class="distribution-header">
                    <h5 class="distribution-title">
                        <i class="fas fa-cubes" style="color: var(--coa-gold);"></i> Multi-Location Warehouse Distribution
                    </h5>
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; padding: 4px 10px; border-radius: 4px;">
                        {{ count($warehouses) }} Location{{ count($warehouses) != 1 ? 's' : '' }} Found
                    </span>
                </div>

                <div class="distribution-body">
                    @forelse($warehouses as $warehouse)
                        <div class="warehouse-item-row">
                            <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                                <div class="wh-name-title mb-0">
                                    <i class="fas fa-warehouse text-primary"></i>
                                    <span>{{ $warehouse['warehouse_name'] }}</span>
                                    @if(isset($warehouse['branch_name']) && $warehouse['branch_name'])
                                        <span class="badge" style="background: #f1f5f9; color: var(--coa-navy); border: 1px solid #cbd5e1; font-size: 11px; font-weight: 700; padding: 3px 8px;">
                                            🏢 {{ $warehouse['branch_name'] }}
                                        </span>
                                    @endif
                                </div>
                                <small class="text-muted font-weight-bold" style="font-size: 11px;">
                                    <i class="far fa-clock mr-1"></i> Updated: {{ $warehouse['updated_at']->format('d M Y, H:i') }}
                                </small>
                            </div>

                            <div class="wh-stock-grid">
                                <div class="wh-stock-metric">
                                    <span class="wh-metric-label">Total Qty</span>
                                    <span class="wh-metric-val emerald">{{ number_format($warehouse['quantity'], 2) }}</span>
                                </div>
                                <div class="wh-stock-metric">
                                    <span class="wh-metric-label">Available</span>
                                    <span class="wh-metric-val emerald">{{ number_format($warehouse['available_qty'], 2) }}</span>
                                </div>
                                <div class="wh-stock-metric">
                                    <span class="wh-metric-label">Committed (Orders)</span>
                                    <span class="wh-metric-val amber">{{ number_format($warehouse['customer_reserved'], 2) }}</span>
                                </div>
                                <div class="wh-stock-metric">
                                    <span class="wh-metric-label">System Reserved</span>
                                    <span class="wh-metric-val">{{ number_format($warehouse['reserved_qty'], 2) }}</span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="progress-bar-wrap">
                                <div class="progress-fill-bar" style="width: {{ $totalQty > 0 ? round(($warehouse['quantity'] / $totalQty) * 100, 2) : 0 }}%"></div>
                            </div>

                            <!-- Customer Reserved Details -->
                            @if(!empty($warehouse['customer_reserved_details']) && collect($warehouse['customer_reserved_details'])->isNotEmpty() && $warehouse['customer_reserved'] > 0)
                                <div class="cust-reserved-wrap">
                                    <div class="cust-reserved-btn" onclick="toggleCustomerReserved(this)">
                                        <i class="fas fa-chevron-right"></i>
                                        <span>{{ collect($warehouse['customer_reserved_details'])->count() }} Customer Order{{ collect($warehouse['customer_reserved_details'])->count() != 1 ? 's' : '' }} Committed / Reserved</span>
                                    </div>
                                    <div class="cust-reserved-body">
                                        @foreach($warehouse['customer_reserved_details'] as $reserved)
                                            <div class="d-flex justify-content-between align-items-center py-1 font-size-12 border-bottom">
                                                <span class="font-weight-bold text-dark">{{ $reserved['customer_name'] }} (Sale #{{ $reserved['sale_id'] }})</span>
                                                <div>
                                                    <span class="badge badge-warning font-weight-bold mr-2">{{ number_format($reserved['remaining_qty'], 2) }} Units</span>
                                                    <span class="badge badge-secondary">{{ ucfirst($reserved['status']) }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($warehouse['remarks'])
                                <div class="mt-2 text-muted small font-italic">
                                    <i class="fas fa-sticky-note mr-1"></i> {{ $warehouse['remarks'] }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-5 text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-2 opacity-50"></i>
                            <h6 class="font-weight-bold text-dark">No warehouse inventory found for this product</h6>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Back Button Action -->
            <div class="mt-3">
                <a href="{{ route('warehouse_stocks.index') }}" class="btn btn-sm btn-secondary font-weight-bold px-3" style="border-radius: 6px;">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Inventory
                </a>
            </div>

        </div>
    </div>
</div>

<script>
function toggleCustomerReserved(element) {
    const list = element.nextElementSibling;
    element.classList.toggle('expanded');
    list.classList.toggle('show');
}

// Branch filter: reload page with ?branch_id=X
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('branch_filter_sel');
    if (sel) {
        sel.addEventListener('change', function() {
            var url = new URL(window.location.href);
            url.searchParams.set('branch_id', this.value);
            window.location.href = url.toString();
        });
    }
});
</script>

@endsection
