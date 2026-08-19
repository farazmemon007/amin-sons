@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --coa-navy: #1e3a5f;
        --coa-navy-dark: #0f1f38;
        --coa-navy-light: #2c5282;
        --coa-gold: #c8973a;
        --coa-emerald: #0d9f6e;
        --coa-emerald-light: #ecfdf5;
        --coa-crimson: #dc2626;
        --coa-crimson-light: #fee2e2;
        --coa-bg: #f8fafc;
        --coa-border: #e2e8f0;
    }

    .wh-wrapper {
        padding: 12px 0 30px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* ── 1. Corporate Header Bar ── */
    .wh-header-bar {
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

    .wh-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .wh-header-icon {
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

    .wh-header-title {
        font-size: 18px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        letter-spacing: -0.01em;
        line-height: 1.2;
    }

    .wh-header-sub {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.82);
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ── 2. KPI Summary Grid ── */
    .wh-kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    @media (max-width: 768px) {
        .wh-kpi-grid {
            grid-template-columns: 1fr;
        }
    }

    .wh-kpi-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 13px 16px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.15s, box-shadow 0.15s;
    }

    .wh-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .wh-kpi-card.highlight {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #a7f3d0;
    }

    .wh-kpi-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .wh-kpi-val {
        font-size: 19px;
        font-weight: 800;
        color: var(--coa-navy);
        line-height: 1.2;
    }

    .wh-kpi-val.emerald {
        color: #047857;
        font-family: monospace;
        font-size: 20px;
    }

    .wh-kpi-icon {
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
    .kpi-icon-gold { background: #fef3c7; color: #d97706; }

    /* ── 3. Toolbar & View Tabs ── */
    .wh-toolbar {
        background: #ffffff;
        border-radius: 8px;
        padding: 10px 16px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        margin-bottom: 18px;
    }

    .wh-tab-btn {
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .wh-tab-btn.active {
        background: var(--coa-navy);
        color: #ffffff;
        border-color: var(--coa-navy);
    }

    .wh-tab-btn.damaged.active {
        background: var(--coa-crimson);
        color: #ffffff;
        border-color: var(--coa-crimson);
    }

    /* ── 4. Products Grid & Cards ── */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }

    .product-card {
        background: #ffffff;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--coa-border);
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }

    .product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);
        border-color: #cbd5e1;
    }

    .product-image {
        width: 100%;
        height: 150px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        color: #94a3b8;
        overflow: hidden;
        border-bottom: 1px solid var(--coa-border);
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-image.no-image {
        background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 100%);
        color: var(--coa-gold);
    }

    .product-info {
        padding: 12px 14px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .product-code {
        font-family: monospace;
        font-size: 11px;
        font-weight: 700;
        color: var(--coa-navy);
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-block;
        margin-bottom: 5px;
        border: 1px solid #cbd5e1;
    }

    .product-name {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
        line-height: 1.3;
        min-height: 34px;
    }

    .product-category {
        font-size: 11px;
        font-weight: 600;
        color: #0284c7;
        margin-bottom: 10px;
        display: inline-block;
        background: #e0f2fe;
        padding: 2px 7px;
        border-radius: 4px;
    }

    .product-stock {
        background: #f8fafc;
        padding: 10px 12px;
        border-radius: 6px;
        border: 1px solid #f1f5f9;
        margin-bottom: 10px;
    }

    .stock-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
        font-size: 12px;
    }

    .stock-row:last-child {
        margin-bottom: 0;
    }

    .stock-label {
        color: #64748b;
        font-weight: 600;
    }

    .stock-value {
        font-family: monospace;
        font-weight: 800;
        font-size: 13px;
    }

    .stock-value.quantity {
        color: #047857;
    }

    .stock-value.warehouses {
        color: #0284c7;
    }

    .product-price {
        font-family: monospace;
        font-size: 12.5px;
        font-weight: 800;
        color: var(--coa-navy);
        margin-bottom: 10px;
    }

    .btn-view {
        background: var(--coa-navy);
        color: #ffffff !important;
        border: 1px solid var(--coa-navy);
        padding: 6px 12px;
        border-radius: 5px;
        font-weight: 700;
        font-size: 11.5px;
        text-align: center;
        text-decoration: none !important;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        transition: all 0.15s;
    }

    .btn-view:hover {
        background: var(--coa-navy-dark);
        color: #ffffff !important;
        transform: translateY(-1px);
    }
</style>

<div class="main-content">
    <div class="wh-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Corporate Header Bar --}}
            <div class="wh-header-bar">
                <div class="wh-header-left">
                    <div class="wh-header-icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div>
                        <h4 class="wh-header-title">Warehouse Inventory Management</h4>
                        <div class="wh-header-sub">
                            <span><i class="fas fa-cubes" style="color: var(--coa-gold);"></i> Real-time multi-location stock levels & product distribution</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. KPI Summary Grid --}}
            <div class="wh-kpi-grid">
                <div class="wh-kpi-card">
                    <div>
                        <div class="wh-kpi-label">Total Products</div>
                        <div class="wh-kpi-val">{{ $stats['totalProducts'] }}</div>
                    </div>
                    <div class="wh-kpi-icon kpi-icon-blue">
                        <i class="fas fa-box-open"></i>
                    </div>
                </div>
                <div class="wh-kpi-card highlight">
                    <div>
                        <div class="wh-kpi-label" style="color: #047857;">Total Stock Quantity</div>
                        <div class="wh-kpi-val emerald">{{ number_format($stats['totalQuantity']) }} <small style="font-size: 11px;">Units</small></div>
                    </div>
                    <div class="wh-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-cubes"></i>
                    </div>
                </div>
                <div class="wh-kpi-card">
                    <div>
                        <div class="wh-kpi-label">Active Warehouses</div>
                        <div class="wh-kpi-val">{{ $stats['warehouses'] }}</div>
                    </div>
                    <div class="wh-kpi-icon kpi-icon-gold">
                        <i class="fas fa-warehouse"></i>
                    </div>
                </div>
            </div>

            {{-- 3. Toolbar: View Switcher Tabs + Search & Filters --}}
            <div class="wh-toolbar">
                <div class="row g-2 align-items-center">
                    <!-- Left: View Switcher Tabs -->
                    <div class="col-xl-4 col-lg-5 col-md-12">
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <button type="button" class="wh-tab-btn active" id="btnProductView" onclick="switchView('product')">
                                <i class="fas fa-cube mr-1"></i> Products
                            </button>
                            <button type="button" class="wh-tab-btn" id="btnWarehouseView" onclick="switchView('warehouse')">
                                <i class="fas fa-warehouse mr-1"></i> Warehouses
                            </button>
                            <button type="button" class="wh-tab-btn damaged" id="btnDamagedView" onclick="switchView('damaged')">
                                <i class="fas fa-dumpster mr-1"></i> Damaged
                            </button>
                        </div>
                    </div>

                    <!-- Right: Search & Branch/Warehouse Filters -->
                    <div class="col-xl-8 col-lg-7 col-md-12">
                        <div class="d-flex align-items-center flex-wrap flex-md-nowrap justify-content-lg-end" style="gap: 10px;">
                            <!-- Search Box -->
                            <div class="flex-grow-1" style="min-width: 170px; position: relative;">
                                <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; z-index: 2;"></i>
                                <input type="text" id="searchProducts" class="form-control" placeholder="Search product or code..." style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1; padding-left: 34px !important; font-size: 12.5px;">
                            </div>

                            <!-- Filter Form -->
                            <form action="{{ route('warehouse_stocks.index') }}" method="GET" class="d-flex align-items-center mb-0 flex-shrink-0" style="gap: 8px;">
                                @if(!empty($showBranchFilter) || $isSuperAdmin || (isset($branches) && $branches->count() > 1))
                                    <select name="branch_id" id="filter_branch_id" class="form-control form-control-sm" style="min-width: 140px; height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1; font-size: 12.5px; padding: 4px 10px;">
                                        <option value="">All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                @endif

                                <select name="warehouse_id" id="filter_warehouse_id" class="form-control form-control-sm" style="min-width: 145px; height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1; font-size: 12.5px; padding: 4px 10px;">
                                    <option value="">All Locations</option>
                                    @if($hasDirectStock)
                                        <option value="shop" {{ $selectedWarehouseId === 'shop' ? 'selected' : '' }}>Direct Branch/Shop</option>
                                    @endif
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ $selectedWarehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->warehouse_name }}</option>
                                    @endforeach
                                </select>

                                <button type="submit" class="btn btn-sm btn-primary px-3 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy); font-size: 12.5px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;" title="Apply Filter">
                                    <i class="fas fa-filter"></i>
                                </button>

                                @if($selectedBranchId || $selectedWarehouseId)
                                    <a href="{{ route('warehouse_stocks.index') }}" class="btn btn-sm btn-light border px-3" style="height: 38px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 12.5px; color: #64748b;" title="Clear Filters">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRODUCT VIEW -->
            <div id="productViewSection">
                <div class="products-grid" id="productsGrid">
                    @forelse($products as $product)
                        <div class="product-card" data-product-name="{{ strtolower($product['product_name']) }}" data-product-code="{{ strtolower($product['product_code']) }}">
                            <div class="product-image {{ !$product['image'] ? 'no-image' : '' }}">
                                @if($product['image'])
                                    <img src="{{ asset('uploads/products/' . $product['image']) }}" alt="{{ $product['product_name'] }}">
                                @else
                                    <i class="fas fa-cube"></i>
                                @endif
                            </div>
                            <div class="product-info">
                                <div>
                                    <div class="product-code">{{ $product['product_code'] }}</div>
                                    <div class="product-name">{{ $product['product_name'] }}</div>
                                    <span class="product-category">{{ $product['category'] }}</span>
                                </div>
                                <div>
                                    <div class="product-stock">
                                        <div class="stock-row">
                                            <span class="stock-label">Total Quantity:</span>
                                            <span class="stock-value quantity">{{ number_format($product['total_quantity'], 2) }}</span>
                                        </div>
                                        <div class="stock-row">
                                            <span class="stock-label">In Warehouses:</span>
                                            <span class="stock-value warehouses">{{ $product['warehouse_count'] }} Locations</span>
                                        </div>
                                    </div>
                                    @if($product['price'] > 0)
                                        <div class="product-price">PKR {{ number_format($product['price'], 2) }}</div>
                                    @endif
                                    <a href="{{ route('warehouse_stocks.show', $product['product_id']) }}" class="btn-view">
                                        <i class="fas fa-eye"></i> View Distribution
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-5 text-center bg-white rounded border" style="grid-column: 1/-1;">
                            <i class="fas fa-inbox fa-3x text-muted mb-3 opacity-50"></i>
                            <h6 class="font-weight-bold text-dark mb-1">No products found in warehouse inventory</h6>
                            <small class="text-muted">Ensure opening stock or purchase orders are recorded.</small>
                        </div>
                    @endforelse
                </div>
            </div>

                <!-- WAREHOUSE VIEW -->
            <div id="warehouseViewSection" style="display:none;">
                @forelse($warehouseGroups as $whIdx => $wh)
                <div class="card mb-3" style="border: 1px solid var(--coa-border); border-radius: 9px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03); overflow: hidden; background: white;">
                    <!-- Warehouse Header -->
                    <div style="padding: 14px 18px; border-bottom: 1px solid var(--coa-border); background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 100%); color: #ffffff; display: flex; justify-content: space-between; align-items: center; cursor: pointer;" onclick="toggleWarehouse(this.querySelector('.btn-wh-toggle'), 'wh-body-{{ $whIdx }}')">
                        <div class="d-flex align-items-center gap-3">
                            <div style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(200, 151, 58, 0.3); border-radius: 6px; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; font-size: 16px; color: var(--coa-gold);">
                                <i class="fas fa-warehouse"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 font-weight-bold" style="color: #ffffff !important; font-size: 14px;">{{ $wh['warehouse_name'] }}</h5>
                                <small style="color: rgba(255, 255, 255, 0.8); font-size: 11px;"><i class="fas fa-map-marker-alt me-1" style="color: var(--coa-gold);"></i>{{ $wh['branch_name'] }}</small>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <div class="text-right mr-3">
                                <div style="font-size: 15px; font-weight: 800; color: #34d399; font-family: monospace;">{{ number_format($wh['total_quantity']) }}</div>
                                <small style="color: rgba(255, 255, 255, 0.7); font-size: 10px; text-transform: uppercase;">Total Units</small>
                            </div>
                            <div class="text-right mr-3">
                                <div style="font-size: 15px; font-weight: 800; color: #ffffff;">{{ $wh['product_count'] }}</div>
                                <small style="color: rgba(255, 255, 255, 0.7); font-size: 10px; text-transform: uppercase;">Products</small>
                            </div>
                            <button class="btn btn-wh-toggle p-0 text-white" style="border: none; background: transparent; font-size: 14px; transition: transform 0.2s;">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Products Body -->
                    <div id="wh-body-{{ $whIdx }}" class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0" style="font-size: 12.5px;">
                                <thead style="background: #f8fafc;">
                                    <tr>
                                        <th style="padding: 10px 18px; color: #475569; font-weight: 700; font-size: 11px; text-transform: uppercase; border-bottom: 1.5px solid #cbd5e1;">Product</th>
                                        <th style="padding: 10px 14px; color: #475569; font-weight: 700; font-size: 11px; text-transform: uppercase; border-bottom: 1.5px solid #cbd5e1;">Code</th>
                                        <th style="padding: 10px 14px; color: #475569; font-weight: 700; font-size: 11px; text-transform: uppercase; border-bottom: 1.5px solid #cbd5e1; text-align: right;">Quantity</th>
                                        <th style="padding: 10px 18px; color: #475569; font-weight: 700; font-size: 11px; text-transform: uppercase; border-bottom: 1.5px solid #cbd5e1; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($wh['products'] as $p)
                                    <tr style="transition: background 0.15s; border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 11px 18px; vertical-align: middle;">
                                            <div class="d-flex align-items-center gap-2">
                                                <div style="width: 28px; height: 28px; border-radius: 6px; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: var(--coa-navy); font-size: 11px; font-weight: 800;">
                                                    {{ strtoupper(substr($p['product_name'], 0, 1)) }}
                                                </div>
                                                <span style="font-weight: 700; color: #0f172a;">{{ $p['product_name'] }}</span>
                                            </div>
                                        </td>
                                        <td style="padding: 11px 14px; vertical-align: middle;">
                                            <span style="font-family: monospace; font-weight: 700; font-size: 11px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; border: 1px solid #cbd5e1; color: var(--coa-navy);">
                                                {{ $p['product_code'] }}
                                            </span>
                                        </td>
                                        <td style="padding: 11px 14px; vertical-align: middle; text-align: right;">
                                            <span style="background: #ecfdf5; color: #047857; padding: 3px 10px; border-radius: 4px; font-weight: 800; font-family: monospace; font-size: 13px; border: 1px solid #a7f3d0;">
                                                {{ number_format($p['quantity']) }}
                                            </span>
                                        </td>
                                        <td style="padding: 11px 18px; vertical-align: middle; text-align: center;">
                                            <a href="{{ route('warehouse_stocks.show', $p['product_id']) }}" class="btn btn-sm btn-primary" style="background: var(--coa-navy); border-color: var(--coa-navy); padding: 3px 10px; font-size: 11px; font-weight: 700; border-radius: 4px;">
                                                <i class="fas fa-eye me-1"></i> View Breakdown
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-5 text-center bg-white rounded border">
                    <i class="fas fa-warehouse fa-3x text-muted mb-2 opacity-50"></i>
                    <p class="text-muted mb-0 font-weight-bold">No warehouse stock data available</p>
                </div>
                @endforelse
            </div>

            <!-- DAMAGED STOCK VIEW (ERP STANDARD) -->
            <div id="damagedViewSection" style="display:none;">
                <div class="card shadow-sm" style="border: 1px solid var(--coa-border); border-radius: 9px; overflow: hidden; background: white;">
                    <div class="card-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 font-weight-bold" style="color: #ffffff !important;">
                                <i class="fas fa-dumpster mr-2"></i> Damaged / Defective Stock Inventory
                            </h6>
                            <small class="text-white opacity-80">Track damaged goods across all locations</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0" style="font-size: 12.5px;">
                                <thead style="background: #f8fafc;">
                                    <tr>
                                        <th style="padding: 11px 18px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">Branch</th>
                                        <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">Location Status</th>
                                        <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">Defective Item</th>
                                        <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">Item Code</th>
                                        <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1; text-align: right;">Quantity</th>
                                        <th style="padding: 11px 18px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1; text-align: right;">Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($damagedStocksList as $stock)
                                    @if((float)$stock->quantity > 0)
                                    <tr class="damaged-stock-row" data-product-name="{{ strtolower($stock->product->item_name ?? '') }}" data-product-code="{{ strtolower($stock->product->item_code ?? '') }}" data-part-name="{{ strtolower($stock->part_name ?? '') }}" data-branch-name="{{ strtolower($stock->branch->name ?? '') }}" data-warehouse-name="{{ strtolower($stock->warehouse->warehouse_name ?? '') }}" style="transition: background 0.15s; border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 12px 18px; vertical-align: middle; font-weight: 700; color: #0f172a;">
                                            {{ $stock->branch->name ?? 'Head Office' }}
                                        </td>
                                        <td style="padding: 12px 14px; vertical-align: middle;">
                                            @if($stock->warehouse_id === null)
                                                <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; border-radius: 4px; font-weight: 700; font-size: 11px; padding: 3px 8px;">
                                                    <i class="fas fa-store mr-1"></i>Held at Shop
                                                </span>
                                            @else
                                                <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; border-radius: 4px; font-weight: 700; font-size: 11px; padding: 3px 8px;">
                                                    <i class="fas fa-warehouse mr-1"></i>Warehouse: {{ $stock->warehouse->warehouse_name ?? '-' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td style="padding: 12px 14px; vertical-align: middle;">
                                            <strong style="color: #0f172a;">{{ $stock->product->item_name ?? 'N/A' }}</strong>
                                            @if($stock->is_part && $stock->part_name)
                                                <span class="badge d-block mt-1 font-weight-bold" style="background: #fef3c7; color: #92400e; max-width: fit-content; font-size: 10.5px;">
                                                    <i class="fas fa-puzzle-piece mr-1"></i>Part: {{ $stock->part_name }}
                                                </span>
                                            @else
                                                <span class="badge d-block mt-1 font-weight-bold" style="background: #ecfdf5; color: #065f46; max-width: fit-content; font-size: 10.5px;">
                                                    <i class="fas fa-box mr-1"></i>Complete Product
                                                </span>
                                            @endif
                                        </td>
                                        <td style="padding: 12px 14px; vertical-align: middle;">
                                            <span style="font-family: monospace; font-weight: 700; font-size: 11px; color: var(--coa-navy);">
                                                {{ $stock->product->item_code ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td style="padding: 12px 14px; vertical-align: middle; text-align: right;">
                                            <span style="background: #fee2e2; color: #b91c1c; padding: 3px 10px; border-radius: 4px; font-weight: 800; font-family: monospace; font-size: 13px; border: 1px solid #fca5a5;">
                                                {{ number_format($stock->quantity, 2) }}
                                            </span>
                                        </td>
                                        <td style="padding: 12px 18px; vertical-align: middle; text-align: right; color: #64748b; font-size: 11.5px;">
                                            {{ $stock->updated_at->format('d M Y, H:i') }}
                                        </td>
                                    </tr>
                                    @endif
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-dumpster" style="font-size:36px; opacity:0.3;"></i>
                                            <div class="mt-2 font-weight-bold">No damaged stock recorded in these locations.</div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // View Toggle
    function switchView(view) {
        const productSection = document.getElementById('productViewSection');
        const warehouseSection = document.getElementById('warehouseViewSection');
        const damagedSection = document.getElementById('damagedViewSection');
        const btnProduct = document.getElementById('btnProductView');
        const btnWarehouse = document.getElementById('btnWarehouseView');
        const btnDamaged = document.getElementById('btnDamagedView');
        const searchBox = document.getElementById('searchProducts');

        if (view === 'product') {
            productSection.style.display = '';
            warehouseSection.style.display = 'none';
            damagedSection.style.display = 'none';
            btnProduct.classList.add('active');
            btnProduct.classList.replace('btn-outline-secondary', 'btn-primary');
            btnProduct.classList.replace('btn-outline-danger', 'btn-primary');
            btnWarehouse.classList.remove('active');
            btnWarehouse.classList.replace('btn-primary', 'btn-outline-secondary');
            if (btnDamaged) {
                btnDamaged.classList.remove('active');
                btnDamaged.classList.replace('btn-danger', 'btn-outline-danger');
            }
            searchBox.placeholder = 'Search product or code...';
        } else if (view === 'warehouse') {
            productSection.style.display = 'none';
            warehouseSection.style.display = '';
            damagedSection.style.display = 'none';
            btnWarehouse.classList.add('active');
            btnWarehouse.classList.replace('btn-outline-secondary', 'btn-primary');
            btnProduct.classList.remove('active');
            btnProduct.classList.replace('btn-primary', 'btn-outline-secondary');
            if (btnDamaged) {
                btnDamaged.classList.remove('active');
                btnDamaged.classList.replace('btn-danger', 'btn-outline-danger');
            }
            searchBox.placeholder = 'Search warehouse...';
        } else {
            productSection.style.display = 'none';
            warehouseSection.style.display = 'none';
            damagedSection.style.display = '';
            if (btnDamaged) {
                btnDamaged.classList.add('active');
                btnDamaged.classList.replace('btn-outline-danger', 'btn-danger');
            }
            btnProduct.classList.remove('active');
            btnProduct.classList.replace('btn-primary', 'btn-outline-secondary');
            btnWarehouse.classList.remove('active');
            btnWarehouse.classList.replace('btn-primary', 'btn-outline-secondary');
            searchBox.placeholder = 'Search damaged stock...';
        }
        localStorage.setItem('whStockView', view);
    }

    // Product & Damaged Stock search
    document.getElementById('searchProducts').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        
        // Filter Product Cards
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            const name = card.dataset.productName || '';
            const code = card.dataset.productCode || '';
            card.style.display = (name.includes(query) || code.includes(query)) ? '' : 'none';
        });

        // Filter Damaged Stock Table Rows
        const damagedRows = document.querySelectorAll('.damaged-stock-row');
        damagedRows.forEach(row => {
            const prodName = row.dataset.productName || '';
            const prodCode = row.dataset.productCode || '';
            const partName = row.dataset.partName || '';
            const branchName = row.dataset.branchName || '';
            const whName = row.dataset.warehouseName || '';
            const matches = prodName.includes(query) || 
                            prodCode.includes(query) || 
                            partName.includes(query) || 
                            branchName.includes(query) || 
                            whName.includes(query);
            row.style.display = matches ? '' : 'none';
        });
    });

    // Restore last view
    document.addEventListener('DOMContentLoaded', function() {
        const lastView = localStorage.getItem('whStockView');
        if (lastView === 'warehouse') switchView('warehouse');
    });

    // Collapse/expand individual warehouse cards
    function toggleWarehouse(btn, bodyId) {
        const body = document.getElementById(bodyId);
        if (body.style.display === 'none') {
            body.style.display = '';
            btn.classList.remove('collapsed');
        } else {
            body.style.display = 'none';
            btn.classList.add('collapsed');
        }
    }

    // ✅ Dynamic Warehouse Loading for Filters
    $(document).on('change', '#filter_branch_id', function() {
        const branchId = $(this).val();
        const warehouseSelect = $('#filter_warehouse_id');
        
        // Show loading state
        warehouseSelect.html('<option value="">Loading...</option>');
        
        $.ajax({
            url: '{{ route("warehouse_stocks.filter_warehouses") }}',
            type: 'GET',
            data: { branch_id: branchId },
            success: function(response) {
                let html = '<option value="">All Locations</option>';
                
                if (response.hasDirectStock) {
                    html += '<option value="shop">Direct Branch/Shop</option>';
                }
                
                if (response.warehouses && response.warehouses.length > 0) {
                    response.warehouses.forEach(wh => {
                        html += `<option value="${wh.id}">${wh.warehouse_name}</option>`;
                    });
                }
                
                warehouseSelect.html(html);
            },
            error: function() {
                warehouseSelect.html('<option value="">Error loading warehouses</option>');
            }
        });
    });
</script>

@endsection
