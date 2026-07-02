@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --primary: #6366f1;
        --success: #22c55e;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #0ea5e9;
        --light: #f8fafc;
        --dark: #1e293b;
        --muted: #64748b;
        --border: #e2e8f0;
    }

    .warehouse-header {
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        color: white;
        padding: 40px 0;
        margin-bottom: 40px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
    }

    .warehouse-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .warehouse-title i {
        font-size: 1.4rem;
    }

    .warehouse-subtitle {
        font-size: 0.75rem;
        opacity: 0.9;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: white;
        padding: 18px;
        border-radius: 12px;
        border-left: 4px solid var(--primary);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-card.success {
        border-left-color: var(--success);
    }

    .stat-card.warning {
        border-left-color: var(--warning);
    }

    .stat-label {
        font-size: 0.65rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
        font-weight: 600;
    }

    .stat-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--dark);
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 18px;
        margin-bottom: 40px;
    }

    .product-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border);
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        border-color: var(--primary);
    }

    .product-image {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, var(--light), var(--border));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: var(--muted);
        overflow: hidden;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-image.no-image {
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        color: white;
        font-size: 2.5rem;
    }

    .product-info {
        padding: 15px;
    }

    .product-code {
        font-size: 0.65rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 4px;
    }

    .product-name {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 6px;
        line-height: 1.3;
        min-height: 30px;
    }

    .product-category {
        font-size: 0.75rem;
        color: var(--muted);
        margin-bottom: 10px;
        display: inline-block;
        background: var(--light);
        padding: 3px 8px;
        border-radius: 4px;
    }

    .product-stock {
        background: var(--light);
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 12px;
    }

    .stock-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .stock-row:last-child {
        margin-bottom: 0;
    }

    .stock-label {
        color: var(--muted);
        font-weight: 500;
        font-size: 0.8rem;
    }

    .stock-value {
        font-weight: 700;
        color: var(--dark);
        font-size: 0.95rem;
    }

    .stock-value.quantity {
        color: var(--success);
    }

    .stock-value.warehouses {
        color: var(--info);
    }

    .product-price {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 10px;
    }

    .product-actions {
        display: flex;
        gap: 8px;
    }

    .btn-view {
        flex: 1;
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        font-size: 0.8rem;
    }

    .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(99, 102, 241, 0.3);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--muted);
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        color: var(--border);
    }

    .empty-state p {
        font-size: 1.1rem;
        margin-bottom: 20px;
    }

    .empty-state a {
        background: var(--primary);
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
    }

    .search-filter {
        display: flex;
        gap: 16px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }

    .search-box input {
        width: 100%;
        padding: 12px 16px 12px 40px;
        border: 2px solid var(--border);
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--muted);
    }

    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .warehouse-title {
            font-size: 1.5rem;
        }

        .stats-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="warehouse-header">
        <div class="container">
            <div class="warehouse-title">
                <i class="fas fa-warehouse"></i>
                <div>
                    <div>Warehouse Inventory Management</div>
                    <small class="warehouse-subtitle">Real-time stock levels across all warehouses</small>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-label"><i class="fas fa-cube"></i> Total Products</div>
                <div class="stat-value">{{ $stats['totalProducts'] }}</div>
            </div>
            <div class="stat-card success">
                <div class="stat-label"><i class="fas fa-boxes"></i> Total Quantity</div>
                <div class="stat-value">{{ number_format($stats['totalQuantity']) }}</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-label"><i class="fas fa-building"></i> Warehouses</div>
                <div class="stat-value">{{ $stats['warehouses'] }}</div>
            </div>
        </div>

        <!-- View Toggle Buttons -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-primary active" id="btnProductView" onclick="switchView('product')">
                    <i class="fas fa-cube me-2"></i> Product View
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnWarehouseView" onclick="switchView('warehouse')">
                    <i class="fas fa-warehouse me-2"></i> Warehouse View
                </button>
                <button type="button" class="btn btn-outline-danger" id="btnDamagedView" onclick="switchView('damaged')">
                    <i class="fas fa-dumpster me-2"></i> Damaged Stock View
                </button>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <!-- ✅ ERP STOCK FILTERS -->
                <form action="{{ route('warehouse_stocks.index') }}" method="GET" class="d-flex gap-2 align-items-center me-2">
                    @if($isSuperAdmin)
                        <select name="branch_id" id="filter_branch_id" class="form-select form-select-sm" style="min-width: 150px; height: 38px; border-radius: 8px;">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    
                    <select name="warehouse_id" id="filter_warehouse_id" class="form-select form-select-sm" style="min-width: 180px; height: 38px; border-radius: 8px;">
                        <option value="">All Locations</option>
                        @if($hasDirectStock)
                            <option value="shop" {{ $selectedWarehouseId === 'shop' ? 'selected' : '' }}>Direct Branch/Shop</option>
                        @endif
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ $selectedWarehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->warehouse_name }}</option>
                        @endforeach
                    </select>
                    
                    <button type="submit" class="btn btn-primary btn-sm" style="height: 38px; width: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-filter"></i>
                    </button>
                    
                    @if($selectedBranchId || $selectedWarehouseId)
                        <a href="{{ route('warehouse_stocks.index') }}" class="btn btn-light border btn-sm" style="height: 38px; width: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center;" title="Clear Filters">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>

                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchProducts" placeholder="Search product or code...">
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
                            <div class="product-code">{{ $product['product_code'] }}</div>
                            <div class="product-name">{{ $product['product_name'] }}</div>
                            <span class="product-category">{{ $product['category'] }}</span>
                            <div class="product-stock">
                                <div class="stock-row">
                                    <span class="stock-label">Total Qty:</span>
                                    <span class="stock-value quantity">{{ number_format($product['total_quantity'], 2) }}</span>
                                </div>
                                <div class="stock-row">
                                    <span class="stock-label">In Warehouses:</span>
                                    <span class="stock-value warehouses">{{ $product['warehouse_count'] }}</span>
                                </div>
                            </div>
                            @if($product['price'] > 0)
                                <div class="product-price">PKR {{ number_format($product['price']) }}</div>
                            @endif
                            <div class="product-actions">
                                <a href="{{ route('warehouse_stocks.show', $product['product_id']) }}" class="btn-view">
                                    <i class="fas fa-eye"></i> View Distribution
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state" style="grid-column: 1/-1;">
                        <i class="fas fa-inbox"></i>
                        <p>No products found in warehouse inventory</p>

                    </div>
                @endforelse
            </div>
        </div>

        <!-- WAREHOUSE VIEW -->
        <div id="warehouseViewSection" style="display:none;">
            @forelse($warehouseGroups as $whIdx => $wh)
            <div class="card mb-4" style="border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); overflow: hidden; background: white;">
                <!-- Warehouse Header -->
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); background: var(--light); display: flex; justify-content: space-between; align-items: center;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="background: white; border: 1px solid var(--border); border-radius: 8px; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--primary);">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div>
                            <h5 class="mb-1" style="font-weight: 700; color: var(--dark); font-size: 1.1rem;">{{ $wh['warehouse_name'] }}</h5>
                            <small style="color: var(--muted);"><i class="fas fa-map-marker-alt me-1"></i>{{ $wh['branch_name'] }}</small>
                        </div>
                    </div>
                    <div class="d-flex gap-4 align-items-center">
                        <div class="text-center">
                            <div style="font-size: 1.2rem; font-weight: 700; color: var(--dark);">{{ number_format($wh['total_quantity']) }}</div>
                            <small style="color: var(--muted); font-size: 0.75rem; text-transform: uppercase;">Total Units</small>
                        </div>
                        <div class="text-center">
                            <div style="font-size: 1.2rem; font-weight: 700; color: var(--dark);">{{ $wh['product_count'] }}</div>
                            <small style="color: var(--muted); font-size: 0.75rem; text-transform: uppercase;">Products</small>
                        </div>
                        <button class="btn" onclick="toggleWarehouse(this, 'wh-body-{{ $whIdx }}')" style="border: none; background: transparent; color: var(--muted); font-size: 1.2rem; transition: transform 0.3s; padding: 0;">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                </div>

                <!-- Products Body -->
                <div id="wh-body-{{ $whIdx }}" class="card-body p-0">
                    <table class="table mb-0" style="font-size: 0.9rem;">
                        <thead style="background: white;">
                            <tr>
                                <th style="padding: 14px 24px; color: var(--muted); font-weight: 600; font-size: 0.75rem; text-transform: uppercase; border-bottom: 1px solid var(--border); border-top: none;">Product</th>
                                <th style="padding: 14px; color: var(--muted); font-weight: 600; font-size: 0.75rem; text-transform: uppercase; border-bottom: 1px solid var(--border); border-top: none;">Code</th>
                                <th style="padding: 14px; color: var(--muted); font-weight: 600; font-size: 0.75rem; text-transform: uppercase; border-bottom: 1px solid var(--border); border-top: none; text-align: right;">Quantity</th>
                                <th style="padding: 14px 24px; color: var(--muted); font-weight: 600; font-size: 0.75rem; text-transform: uppercase; border-bottom: 1px solid var(--border); border-top: none; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wh['products'] as $p)
                            <tr style="transition: background 0.2s;" onmouseover="this.style.background='var(--light)'" onmouseout="this.style.background=''">
                                <td style="padding: 16px 24px; vertical-align: middle; border-bottom: 1px solid var(--border);">
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="width: 36px; height: 36px; border-radius: 8px; background: var(--light); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 0.85rem; font-weight: 700;">
                                            {{ strtoupper(substr($p['product_name'], 0, 1)) }}
                                        </div>
                                        <span style="font-weight: 600; color: var(--dark);">{{ $p['product_name'] }}</span>
                                    </div>
                                </td>
                                <td style="padding: 16px; vertical-align: middle; border-bottom: 1px solid var(--border); color: var(--muted);">{{ $p['product_code'] }}</td>
                                <td style="padding: 16px; vertical-align: middle; border-bottom: 1px solid var(--border); text-align: right;">
                                    <span style="background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">{{ number_format($p['quantity']) }}</span>
                                </td>
                                <td style="padding: 16px 24px; vertical-align: middle; border-bottom: 1px solid var(--border); text-align: center;">
                                    <a href="{{ route('warehouse_stocks.show', $p['product_id']) }}" class="btn-view" style="display: inline-flex; width: auto; padding: 4px 12px; font-size: 0.75rem;">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No warehouse stock data available</p>
            </div>
            @endforelse
        </div>
        </div>

        <!-- DAMAGED STOCK VIEW (ERP STANDARD) -->
        <div id="damagedViewSection" style="display:none;">
            <div class="card shadow-sm" style="border: 1px solid var(--border); border-radius: 12px; overflow: hidden; background: white; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" style="font-size: 0.9rem;">
                            <thead style="background: linear-gradient(135deg, #c0392b, #d35400); color: white;">
                                <tr>
                                    <th style="padding: 14px 24px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase;">Branch</th>
                                    <th style="padding: 14px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase;">Location Status</th>
                                    <th style="padding: 14px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase;">Defective Item</th>
                                    <th style="padding: 14px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase;">Item Code</th>
                                    <th style="padding: 14px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; text-align: right;">Quantity</th>
                                    <th style="padding: 14px 24px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; text-align: right;">Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($damagedStocksList as $stock)
                                @if((float)$stock->quantity > 0)
                                <tr class="damaged-stock-row" data-product-name="{{ strtolower($stock->product->item_name ?? '') }}" data-product-code="{{ strtolower($stock->product->item_code ?? '') }}" data-part-name="{{ strtolower($stock->part_name ?? '') }}" data-branch-name="{{ strtolower($stock->branch->name ?? '') }}" data-warehouse-name="{{ strtolower($stock->warehouse->warehouse_name ?? '') }}" style="transition: background 0.2s;" onmouseover="this.style.background='var(--light)'" onmouseout="this.style.background=''">
                                    <td style="padding: 16px 24px; vertical-align: middle; border-bottom: 1px solid var(--border); font-weight: bold;">
                                        {{ $stock->branch->name ?? 'Head Office' }}
                                    </td>
                                    <td style="padding: 16px; vertical-align: middle; border-bottom: 1px solid var(--border);">
                                        @if($stock->warehouse_id === null)
                                            <span class="badge badge-warning py-1 px-3" style="border-radius: 20px; font-weight: 700; font-size: 0.8rem;"><i class="fas fa-store mr-1"></i>Held at Shop</span>
                                        @else
                                            <span class="badge badge-danger py-1 px-3" style="border-radius: 20px; font-weight: 700; font-size: 0.8rem; background-color: #c0392b;"><i class="fas fa-warehouse mr-1"></i>Warehouse: {{ $stock->warehouse->warehouse_name ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td style="padding: 16px; vertical-align: middle; border-bottom: 1px solid var(--border);">
                                        <strong>{{ $stock->product->item_name ?? 'N/A' }}</strong>
                                        @if($stock->is_part && $stock->part_name)
                                            <span class="badge badge-warning d-block mt-1 font-weight-bold text-dark" style="max-width: fit-content;"><i class="fas fa-puzzle-piece mr-1"></i>Part: {{ $stock->part_name }}</span>
                                        @else
                                            <span class="badge badge-success d-block mt-1 font-weight-bold text-white" style="max-width: fit-content;"><i class="fas fa-box mr-1"></i>Complete Product</span>
                                        @endif
                                    </td>
                                    <td style="padding: 16px; vertical-align: middle; border-bottom: 1px solid var(--border); color: var(--muted);">
                                        {{ $stock->product->item_code ?? 'N/A' }}
                                    </td>
                                    <td style="padding: 16px; vertical-align: middle; border-bottom: 1px solid var(--border); text-align: right;">
                                        <span style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">{{ number_format($stock->quantity, 2) }}</span>
                                    </td>
                                    <td style="padding: 16px 24px; vertical-align: middle; border-bottom: 1px solid var(--border); text-align: right; color: var(--muted); font-size: 0.8rem;">
                                        {{ $stock->updated_at->format('d M Y, H:i') }}
                                    </td>
                                </tr>
                                @endif
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-dumpster" style="font-size:40px; opacity:0.3;"></i>
                                        <div class="mt-2">No damaged stock recorded in these locations.</div>
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
