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

        <!-- Search & Filter -->
        <div class="search-filter">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchProducts" placeholder="Search by product name or code...">
            </div>
            @can('warehouse.stock.create')
            <a href="{{ route('warehouse_stocks.create') }}" class="btn btn-view">
                <i class="fas fa-plus"></i> Add Stock
            </a>
            @endcan
        </div>

        <!-- Products Grid -->
        <div class="products-grid" id="productsGrid">
            @forelse($products as $product)
                <div class="product-card" data-product-name="{{ strtolower($product['product_name']) }}" data-product-code="{{ strtolower($product['product_code']) }}">
                    <!-- Product Image -->
                    <div class="product-image {{ !$product['image'] ? 'no-image' : '' }}">
                        @if($product['image'])
                            <img src="{{ asset('uploads/products/' . $product['image']) }}" alt="{{ $product['product_name'] }}">
                        @else
                            <i class="fas fa-cube"></i>
                        @endif
                    </div>

                    <!-- Product Info -->
                    <div class="product-info">
                        <div class="product-code">{{ $product['product_code'] }}</div>
                        <div class="product-name">{{ $product['product_name'] }}</div>
                        <span class="product-category">{{ $product['category'] }}</span>

                        <!-- Stock Info -->
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

                        <!-- Actions -->
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
                    @can('warehouse.stock.create')
                    <a href="{{ route('warehouse_stocks.create') }}">Add First Stock</a>
                    @endcan
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    document.getElementById('searchProducts').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        
        cards.forEach(card => {
            const name = card.dataset.productName;
            const code = card.dataset.productCode;
            const matches = name.includes(query) || code.includes(query);
            card.style.display = matches ? '' : 'none';
        });
    });
</script>

@endsection
