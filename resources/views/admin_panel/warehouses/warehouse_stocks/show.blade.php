@extends('admin_panel.layout.app')

@section('content')
{{-- ✅ Initialize variable for safety --}}
@php
    $isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
@endphp

<style>
    :root {
        --primary: #6366f1;
        --success: #22c55e;
        --warning: #f59e0b;
        --info: #0ea5e9;
        --light: #f8fafc;
        --dark: #1e293b;
        --muted: #64748b;
        --border: #e2e8f0;
    }

    .product-header {
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        color: white;
        padding: 40px 0;
        margin-bottom: 40px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: white;
        text-decoration: none;
        margin-bottom: 20px;
        opacity: 0.9;
        transition: opacity 0.2s;
    }

    .back-link:hover {
        opacity: 1;
        color: white;
    }

    .product-header-content {
        display: flex;
        gap: 30px;
        align-items: center;
    }

    .product-image-container {
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .product-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-image-container i {
        font-size: 3rem;
        color: rgba(255, 255, 255, 0.5);
    }

    .product-details {
        flex: 1;
    }

    .product-name {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .product-meta {
        display: flex;
        gap: 20px;
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .stats-grid {
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
    }

    .stat-card.available {
        border-left-color: var(--success);
    }

    .stat-card.reserved {
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

    .warehouses-section {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .section-header {
        background: var(--light);
        padding: 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
    }

    .warehouse-item {
        padding: 15px;
        border-bottom: 1px solid var(--border);
        transition: background 0.2s;
    }

    .warehouse-item:hover {
        background: var(--light);
    }

    .warehouse-item:last-child {
        border-bottom: none;
    }

    .warehouse-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .warehouse-name i {
        color: var(--info);
    }

    .warehouse-stock-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 15px;
        margin-top: 10px;
    }

    .stock-metric {
        display: flex;
        flex-direction: column;
    }

    .metric-label {
        font-size: 0.65rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
        font-weight: 600;
    }

    .metric-value {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--dark);
    }

    .metric-value.available {
        color: var(--success);
    }

    .metric-value.reserved {
        color: var(--warning);
    }

    .warehouse-remarks {
        margin-top: 10px;
        padding: 10px;
        background: var(--light);
        border-radius: 6px;
        font-size: 0.9rem;
        color: var(--muted);
        font-style: italic;
    }

    .progress-bar-container {
        width: 100%;
        height: 8px;
        background: var(--border);
        border-radius: 4px;
        overflow: hidden;
        margin-top: 10px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--success), #16a34a);
        transition: width 0.3s ease;
        border-radius: 4px;
    }

    .actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn-back {
        background: var(--light);
        color: var(--dark);
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-back:hover {
        background: var(--border);
        color: var(--dark);
    }

    /* Customer Reserved Details Styling */
    .customer-reserved-section {
        margin-top: 15px;
        padding: 12px;
        background: rgba(249, 158, 11, 0.05);
        border-left: 3px solid var(--warning);
        border-radius: 6px;
    }

    .customer-reserved-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 10px;
        cursor: pointer;
        user-select: none;
    }

    .customer-reserved-header i {
        color: var(--warning);
        transition: transform 0.2s;
    }

    .customer-reserved-header.expanded i {
        transform: rotate(90deg);
    }

    .customer-reserved-list {
        display: none;
        padding-top: 10px;
        border-top: 1px solid rgba(249, 158, 11, 0.2);
    }

    .customer-reserved-list.show {
        display: block;
    }

    .customer-order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        font-size: 0.8rem;
        border-bottom: 1px solid rgba(249, 158, 11, 0.1);
    }

    .customer-order-item:last-child {
        border-bottom: none;
    }

    .customer-order-name {
        font-weight: 600;
        color: var(--dark);
        flex: 1;
    }

    .customer-order-qty {
        background: rgba(249, 158, 11, 0.1);
        color: var(--warning);
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 700;
        margin: 0 8px;
        min-width: 50px;
        text-align: center;
    }

    .customer-order-status {
        padding: 2px 6px;
        border-radius: 3px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .customer-order-status.pending {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
    }

    .customer-order-status.partial {
        background: rgba(249, 158, 11, 0.1);
        color: var(--warning);
    }

    @media (max-width: 768px) {
        .product-header-content {
            flex-direction: column;
            text-align: center;
        }

        .product-name {
            font-size: 1.5rem;
        }

        .product-meta {
            flex-direction: column;
            gap: 10px;
        }

        .warehouse-stock-info {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid">
    <!-- Product Header -->
    <div class="product-header">
        <div class="container">
            <a href="{{ route('warehouse_stocks.index') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Inventory
            </a>

            <div class="product-header-content">
                <div class="product-image-container">
                    @if($product->image)
                        <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->item_name }}">
                    @else
                        <i class="fas fa-cube"></i>
                    @endif
                </div>

                <div class="product-details">
                    <div class="product-name">{{ $product->item_name }}</div>
                    <div class="product-meta">
                        <div class="meta-item">
                            <i class="fas fa-barcode"></i>
                            <strong>{{ $product->item_code }}</strong>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            {{ optional($product->category_relation)->name ?? 'Uncategorized' }}
                        </div>
                        @if($product->price)
                        <div class="meta-item">
                            <i class="fas fa-money-bill"></i>
                            <strong>PKR {{ number_format($product->price) }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Summary Statistics -->
        <div class="stats-grid">
            <div class="stat-card available">
                <div class="stat-label"><i class="fas fa-boxes"></i> Total Quantity</div>
                <div class="stat-value">{{ number_format($totalQty, 2) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label"><i class="fas fa-check-circle"></i> Available</div>
                <div class="stat-value">{{ number_format($totalAvailable, 2) }}</div>
            </div>
            <div class="stat-card reserved">
                <div class="stat-label"><i class="fas fa-user-tie"></i> Committed from Orders</div>
                <div class="stat-value">{{ number_format($totalCustomerReserved, 2) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label"><i class="fas fa-lock"></i> System Reserved</div>
                <div class="stat-value">{{ number_format($totalReserved, 2) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label"><i class="fas fa-building"></i> Warehouses</div>
                <div class="stat-value">{{ count($warehouses) }}</div>
            </div>
        </div>

        <!-- Warehouse Distribution -->
        <div class="warehouses-section">
            <div class="section-header">
                <i class="fas fa-warehouse"></i>
                <h3 class="section-title">Warehouse Distribution</h3>
                <span style="margin-left: auto; color: var(--muted);">{{ count($warehouses) }} location{{ count($warehouses) != 1 ? 's' : '' }}</span>
            </div>

            @forelse($warehouses as $warehouse)
                <div class="warehouse-item">
                    <div class="warehouse-name">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $warehouse['warehouse_name'] }}
                        @if($isSuperAdmin && isset($warehouse['branch_name']))
                            <span style="margin-left: 10px; background: #e0e7ff; color: #4f46e5; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                🏢 {{ $warehouse['branch_name'] }}
                            </span>
                        @endif
                    </div>

                    <div class="warehouse-stock-info">
                        <div class="stock-metric">
                            <span class="metric-label">Total Qty</span>
                            <span class="metric-value available">{{ number_format($warehouse['quantity'], 2) }}</span>
                        </div>
                        <div class="stock-metric">
                            <span class="metric-label">Available</span>
                            <span class="metric-value available">{{ number_format($warehouse['available_qty'], 2) }}</span>
                        </div>
                        <div class="stock-metric">
                            <span class="metric-label">Committed</span>
                            <span class="metric-value reserved">{{ number_format($warehouse['customer_reserved'], 2) }}</span>
                        </div>
                        <div class="stock-metric">
                            <span class="metric-label">System Reserved</span>
                            <span class="metric-value reserved">{{ number_format($warehouse['reserved_qty'], 2) }}</span>
                        </div>
                        <div class="stock-metric">
                            <span class="metric-label">Last Updated</span>
                            <span class="metric-value" style="font-size: 0.9rem;">{{ $warehouse['updated_at']->format('d-m-Y H:i') }}</span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="progress-bar-container">
                        <div class="progress-fill" style="width: {{ ($warehouse['quantity'] / $totalQty) * 100 }}%"></div>
                    </div>

                    <!-- Customer Reserved Details -->
                    @if(!empty($warehouse['customer_reserved_details']) && collect($warehouse['customer_reserved_details'])->isNotEmpty() && $warehouse['customer_reserved'] > 0)
                        <div class="customer-reserved-section">
                            <div class="customer-reserved-header" onclick="toggleCustomerReserved(this)">
                                <i class="fas fa-chevron-right"></i>
                                <span>{{ collect($warehouse['customer_reserved_details'])->count() }} Customer Order{{ collect($warehouse['customer_reserved_details'])->count() != 1 ? 's' : '' }} Reserved</span>
                            </div>
                            <div class="customer-reserved-list">
                                @foreach($warehouse['customer_reserved_details'] as $reserved)
                                    <div class="customer-order-item">
                                        <span class="customer-order-name">{{ $reserved['customer_name'] }} (Sale #{{ $reserved['sale_id'] }})</span>
                                        <span class="customer-order-qty">{{ number_format($reserved['remaining_qty'], 2) }}</span>
                                        <span class="customer-order-status {{ strtolower($reserved['status']) }}">
                                            {{ ucfirst($reserved['status']) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($warehouse['remarks'])
                        <div class="warehouse-remarks">
                            <i class="fas fa-sticky-note"></i> {{ $warehouse['remarks'] }}
                        </div>
                    @endif
                </div>
            @empty
                <div style="padding: 40px; text-align: center; color: var(--muted);">
                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.5;"></i>
                    <p>No warehouse inventory found for this product</p>
                </div>
            @endforelse
        </div>

        <!-- Actions -->
        <div class="actions">
            <a href="{{ route('warehouse_stocks.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Inventory
            </a>
        </div>
    </div>
</div>

<script>
function toggleCustomerReserved(element) {
    const list = element.nextElementSibling;
    element.classList.toggle('expanded');
    list.classList.toggle('show');
}
</script>

@endsection
