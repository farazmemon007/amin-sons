@extends('admin_panel.layout.app')

@section('content')
<style>
    .dc-form-container {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 2rem;
    }
    
    .item-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .form-section {
        margin-bottom: 2rem;
    }
    
    .badge-custom {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .quantity-input {
        padding: 0.8rem;
        border: 2px solid #ced4da;
        border-radius: 4px;
        font-size: 1rem;
        width: 100%;
        transition: all 0.3s ease;
    }
    
    .quantity-input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    .info-box {
        background: #e7f3ff;
        border-left: 4px solid #007bff;
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1rem;
        color: #0066cc;
    }
    
    .warning-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1rem;
        color: #856404;
    }
    
    .success-box {
        background: #d4edda;
        border-left: 4px solid #28a745;
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1rem;
        color: #155724;
    }
    
    .btn {
        padding: 0.8rem 1.5rem;
        font-weight: 600;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: #007bff;
        color: white;
    }
    
    .btn-primary:hover {
        background: #0056b3;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #545b62;
    }
    
    .form-header h3 {
        color: #333;
        margin-bottom: 1rem;
        font-weight: 600;
    }
    
    .remainder-display {
        font-weight: 600;
        font-size: 1.1rem;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="dc-form-container">
                
                <!-- Header -->
                <div class="form-header mb-3">
                    <h3>📋 Create Delivery Challan (DC) from Remaining Item</h3>
                    <p class="text-muted">Select warehouse and delivery quantity for this remaining item</p>
                </div>

                <!-- Info Box -->
                <div class="info-box">
                    <strong>ℹ️ Step 1 of 2:</strong> Create DC → Then create Gate Pass
                    <br>
                    <small>After DC creation, you can create a physical gate pass for delivery to the customer</small>
                </div>

                <!-- Item Summary -->
                <div class="item-card mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div>
                                <label style="font-weight: 600; color: #666; font-size: 0.9rem;">Product</label>
                                <p style="font-size: 1.1rem; color: #333; margin: 0.3rem 0 0 0;">
                                    <strong>{{ $remaining->product_name }}</strong>
                                </p>
                                <p style="font-size: 0.9rem; color: #666; margin: 0.3rem 0 0 0;">
                                    Code: <code>{{ $remaining->item_code }}</code>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div style="text-align: center;">
                                <label style="font-weight: 600; color: #666; font-size: 0.9rem; display: block;">Remaining</label>
                                <div class="badge-custom" style="background: #dc3545; color: white; display: inline-block;">
                                    {{ number_format($remaining->remaining_qty, 2) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div style="text-align: center;">
                                <label style="font-weight: 600; color: #666; font-size: 0.9rem; display: block;">Current Warehouse</label>
                                <div style="font-size: 1rem; color: #333; font-weight: 600;">
                                    {{ $remaining->warehouse->warehouse_name ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form id="dcForm" method="POST" action="{{ route('customer-remaining.store-dc', $remaining->id) }}">
                    @csrf
                    
                    <div class="form-section">
                        <label style="font-weight: 600; margin-bottom: 0.5rem; display: block;">
                            🏭 Select Warehouse (with available stock):
                        </label>
                        <select name="warehouse_id" id="warehouseSelect" class="quantity-input" required>
                            <option value="" disabled selected>-- Choose warehouse --</option>
                            @forelse($warehousesWithStock as $wh)
                                <option value="{{ $wh->warehouse_id }}" data-stock="{{ $wh->quantity }}" @if($wh->warehouse_id == $remaining->warehouse_id) selected @endif>
                                    {{ $wh->warehouse_name }} ({{ number_format($wh->quantity, 2) }} available)
                                </option>
                            @empty
                                <option value="" disabled style="color: red; font-weight: bold;">
                                    ❌ No warehouses have this product in stock
                                </option>
                            @endforelse
                        </select>
                        <small class="text-muted d-block mt-2" id="warehouseInfo">
                            📌 Only warehouses with this product in stock are shown
                        </small>
                    </div>

                    <div class="form-section">
                        <label style="font-weight: 600; margin-bottom: 0.5rem; display: block;">
                            📦 Delivery Quantity:
                        </label>
                        <div style="display: flex; gap: 1rem; align-items: flex-end;">
                            <input type="number" 
                                   name="delivery_qty" 
                                   id="deliveryQty"
                                   class="quantity-input"
                                   placeholder="Enter qty to deliver"
                                   min="0.01"
                                   step="0.01"
                                   value="{{ number_format($remaining->remaining_qty, 4, '.', '') }}"
                                   max="{{ number_format($remaining->remaining_qty, 4, '.', '') }}"
                                   required
                                   style="flex: 1;">
                            <div style="white-space: nowrap; font-weight: 600;">
                                / {{ number_format($remaining->remaining_qty, 2) }}
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Max available: {{ number_format($remaining->remaining_qty, 2) }} units
                        </small>
                    </div>

                    <div class="form-section">
                        <div>
                            <label style="font-weight: 600; color: #666; font-size: 0.9rem; display: block; margin-bottom: 0.5rem;">
                                Remainder After Delivery:
                            </label>
                            <div class="info-box" style="margin: 0;">
                                <strong>Remaining for next delivery: <span id="remainderDisplay" class="remainder-display">
                                    {{ number_format($remaining->remaining_qty, 2) }}
                                </span> units</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Related Pending Items -->
                    @if($pendingItems->count() > 1)
                        <div class="form-section">
                            <div class="warning-box">
                                <strong>⚠️ Other Pending Items from Same Sale:</strong>
                                <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                                    @foreach($pendingItems as $item)
                                        @if($item->id != $remaining->id)
                                            • {{ $item->product_name }}: {{ number_format($item->remaining_qty, 2) }} units remaining
                                            <br>
                                        @endif
                                    @endforeach
                                </p>
                                <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: #999;">
                                    You'll need to create DC for other items as well
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Buttons -->
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <a href="{{ route('customer-remaining.show', $remaining->id) }}" 
                           class="btn btn-secondary" style="flex: 1; text-align: center; text-decoration: none;">
                            ← Back
                        </a>
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            ✓ Create DC & Continue
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
// Handle warehouse selection - show stock info
document.getElementById('warehouseSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const warehouseStock = parseFloat(selectedOption.dataset.stock || 0);
    const remainingQty = parseFloat({{ $remaining->remaining_qty }});
    const maxQty = Math.min(warehouseStock, remainingQty);
    
    // Update warehouse info
    if (warehouseStock > 0) {
        document.getElementById('warehouseInfo').innerHTML = `
            ✅ <strong>${selectedOption.text}</strong><br>
            <small>Warehouse stock: ${warehouseStock.toFixed(2)} | Max to deliver: ${maxQty.toFixed(2)} units</small>
        `;
        document.getElementById('warehouseInfo').style.color = '#28a745';
        document.getElementById('warehouseInfo').style.fontWeight = '600';
    }
});

// Handle delivery qty input - calculate remainder in real-time
document.getElementById('deliveryQty').addEventListener('input', function() {
    const maxQty = parseFloat({{ $remaining->remaining_qty }});
    const deliveryQty = parseFloat(this.value || 0);
    const remainder = maxQty - deliveryQty;
    
    // Update remainder display
    const remainderDisplay = document.getElementById('remainderDisplay');
    remainderDisplay.textContent = remainder.toFixed(2);
    
    // Color code based on delivery amount
    if (remainder > 0) {
        remainderDisplay.style.color = '#ffc107';  // Yellow - has remainder
    } else if (remainder === 0) {
        remainderDisplay.style.color = '#28a745';  // Green - full delivery
    } else {
        remainderDisplay.style.color = '#dc3545';  // Red - over delivery (error)
        this.style.borderColor = '#dc3545';
        this.style.backgroundColor = '#fff5f5';
        return;
    }
    
    // Reset styling if valid
    this.style.borderColor = '#ced4da';
    this.style.backgroundColor = 'white';
});

// Form submission validation
document.getElementById('dcForm').addEventListener('submit', function(e) {
    const deliveryQty = parseFloat(document.getElementById('deliveryQty').value || 0);
    const maxQty = parseFloat({{ $remaining->remaining_qty }});
    
    if (isNaN(deliveryQty) || deliveryQty <= 0) {
        e.preventDefault();
        alert('❌ Delivery quantity must be greater than 0');
        return false;
    }
    
    if (deliveryQty > maxQty) {
        e.preventDefault();
        alert(`❌ Delivery quantity (${deliveryQty.toFixed(2)}) cannot exceed remaining (${maxQty.toFixed(2)})`);
        return false;
    }
});
</script>

@endsection
