@extends('admin_panel.layout.app')

@section('content')
<style>
    /* ERP Dashboard Professional Styles */
    .erp-card {
        background: #ffffff;
        border: 1px solid #e3e6f0;
        border-radius: 0.75rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        margin-bottom: 1.5rem;
    }
    .erp-header {
        background: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem 0.75rem 0 0;
    }
    .erp-title {
        color: #4e73df;
        font-weight: 700;
        margin: 0;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .erp-body { padding: 1.5rem; }

    /* Info Badges */
    .info-label { font-size: 0.75rem; font-weight: 700; color: #858796; text-transform: uppercase; display: block; }
    .info-value { font-size: 1rem; color: #3a3b45; font-weight: 600; }

    /* Product Row Styling */
    .delivery-item-row {
        background: #fff;
        border: 1px solid #eaecf4;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        transition: transform 0.2s;
        border-left: 5px solid #d1d3e2;
    }
    .delivery-item-row:hover { transform: scale(1.01); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    
    /* Quantity Inputs */
    .qty-input-group {
        position: relative;
        max-width: 200px;
    }
    .qty-input {
        height: 45px;
        font-weight: 700;
        font-size: 1.1rem;
        text-align: center;
        border: 2px solid #d1d3e2;
        color: #4e73df;
    }
    .qty-input:focus { border-color: #4e73df; box-shadow: none; }

    /* Custom Radio Buttons */
    .method-card {
        border: 2px solid #eaecf4;
        padding: 1rem;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s;
        display: block;
        height: 100%;
    }
    input[name="delivery_method"]:checked + .method-card {
        border-color: #4e73df;
        background-color: #f0f3ff;
    }

    /* Status Animations */
    .fade-in { animation: fadeIn 0.4s ease-in; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<div class="container-fluid py-4">
    <div class="erp-card">
        <div class="erp-body">
            <div class="row align-items-center text-center text-md-left">
                <div class="col-md-3 border-right">
                    <span class="info-label">Invoice Number</span>
                    <span class="info-value text-primary">#{{ $sale->invoice_no }}</span>
                </div>
                <div class="col-md-3 border-right">
                    <span class="info-label">Customer Name</span>
                    <span class="info-value">{{ optional($sale->customer)->customer_name ?? $sale->sub_customer ?? 'N/A' }}</span>
                </div>
                <div class="col-md-3 border-right">
                    <span class="info-label">Grand Total</span>
                    <span class="info-value text-success">PKR {{ number_format($sale->total_net, 2) }}</span>
                </div>
                <div class="col-md-3">
                    <span class="info-label">Items Count</span>
                    <span class="info-value">{{ $sale->saleItems->count() }} Products</span>
                </div>
            </div>
        </div>
    </div>

    <div class="erp-card">
        <div class="erp-header">
            <h6 class="erp-title"><i class="fas fa-truck mr-2"></i> 1. Select Delivery Method & Location</h6>
        </div>
        <div class="erp-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <input type="radio" name="delivery_method" value="branch" id="m_branch" class="delivery-method-radio d-none">
                    <label for="m_branch" class="method-card text-center">
                        <i class="fas fa-store fa-2x mb-2 text-secondary"></i>
                        <h6 class="font-weight-bold">Shop / Branch</h6>
                        <p class="small text-muted mb-0">Deliver items from a specific shop branch stock.</p>
                    </label>
                </div>
                <div class="col-md-6 mb-3">
                    <input type="radio" name="delivery_method" value="warehouse" id="m_warehouse" class="delivery-method-radio d-none">
                    <label for="m_warehouse" class="method-card text-center">
                        <i class="fas fa-warehouse fa-2x mb-2 text-secondary"></i>
                        <h6 class="font-weight-bold">Main Warehouse</h6>
                        <p class="small text-muted mb-0">Deliver items directly from the central storage.</p>
                    </label>
                </div>
            </div>

            <div id="branchSelectorDiv" class="mt-4 fade-in" style="display:none">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Select Source Branch <span class="text-danger">*</span></label>
                    <select id="branchSelect" class="form-control form-control-lg custom-select">
                        <option value="">-- Search & Select Branch --</option>
                        @php
                            $availableBranches = $branchOwnStocks->pluck('branch_id')->unique();
                            $branches = \App\Models\Branch::whereIn('id', $availableBranches)->get();
                        @endphp
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="warehouseSelectorDiv" class="mt-4 fade-in" style="display:none">
                <div class="form-group">
                    <label class="font-weight-bold text-dark">Select Source Warehouse <span class="text-danger">*</span></label>
                    <select id="warehouseSelect" class="form-control form-control-lg custom-select">
                        <option value="">-- Search & Select Warehouse --</option>
                        @foreach($uniqueWarehouses as $stock)
                            @if($stock->warehouse)
                                <option value="{{ $stock->warehouse_id }}">{{ $stock->warehouse->warehouse_name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <form id="warehouseForm">
        @csrf
        <input type="hidden" name="sale_id" value="{{ $sale->id }}">
        
        <div class="erp-card">
            <div class="erp-header d-flex justify-content-between align-items-center">
                <h6 class="erp-title"><i class="fas fa-boxes mr-2"></i> 2. Product Allocation & Quantities</h6>
                <span class="badge badge-info px-3 py-2">Real-time Inventory Check Enabled</span>
            </div>
            <div class="erp-body">
                <div id="errorMessage" class="alert alert-danger mb-4" style="display:none"></div>
                <div id="successMessage" class="alert alert-success mb-4" style="display:none"></div>

                @foreach($sale->saleItems as $item)
                    @php
                        // Get max available for this product across all warehouses
                        $productStocks = $warehouseStocks->where('product_id', $item->product_id);
                        $maxAvailable = $productStocks->isNotEmpty() ? $productStocks->max('quantity') : 0;
                        $shortage = ($maxAvailable < $item->sales_qty);
                    @endphp

                    <div class="delivery-item-row" style="border-left-color: {{ $shortage ? '#f6c23e' : '#1cc88a' }}">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <h6 class="mb-1 font-weight-bold text-dark">{{ optional($item->product)->item_name }}</h6>
                                <div class="d-flex small">
                                    <span class="mr-3">SKU: <strong>{{ optional($item->product)->item_code }}</strong></span>
                                    <span class="text-primary">Required: <strong>{{ number_format($item->sales_qty, 2) }}</strong></span>
                                </div>
                                <div class="mt-2">
                                    <span class="badge {{ $maxAvailable > 0 ? 'badge-light text-success' : 'badge-light text-danger' }} border">
                                        Current Stock: {{ number_format($maxAvailable, 2) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="col-md-3 text-center">
                                <label class="info-label">Delivery Qty</label>
                                <div class="qty-input-group mx-auto">
                                    <input type="number" 
                                           name="delivery_qty[{{ $item->product_id }}]"
                                           class="form-control qty-input delivery-qty-input"
                                           data-product-id="{{ $item->product_id }}"
                                           data-required-qty="{{ $item->sales_qty }}"
                                           data-max-qty="{{ $maxAvailable }}"
                                           step="0.01"
                                           value="{{ min($maxAvailable, $item->sales_qty) }}">
                                </div>
                            </div>

                            <div class="col-md-4 text-right">
                                <div class="p-2 rounded bg-light border">
                                    <span class="info-label">Backlog / Remaining</span>
                                    <span class="remaining-display h5 mb-0 font-weight-bold text-primary">0.00</span>
                                </div>
                                <div class="delivery-qty-message small mt-2 font-weight-bold" style="display:none"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="erp-header text-right">
                <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-secondary px-4 mr-2">
                    <i class="fas fa-times mr-1"></i> Cancel
                </a>
                <button type="submit" id="submitBtn" class="btn btn-primary px-5 shadow">
                    <i class="fas fa-check-circle mr-1"></i> Confirm & Generate Challan
                </button>
            </div>
        </div>
    </form>

    <div id="loadingState" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.8); z-index:9999; flex-direction:column; align-items:center; justify-content:center;">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
        <h5 class="mt-3 font-weight-bold text-dark">Processing ERP Transaction...</h5>
    </div>
</div>

<script>
// Store warehouse stock data in JavaScript for filtering
const warehouseStockData = {!! json_encode($warehouseStocks instanceof \Illuminate\Support\Collection ? $warehouseStocks->toArray() : []) !!};
const branchStockData = {!! json_encode($branchOwnStocks instanceof \Illuminate\Support\Collection ? $branchOwnStocks->toArray() : []) !!};
const uniqueWarehousesData = {!! json_encode($uniqueWarehouses instanceof \Illuminate\Support\Collection ? $uniqueWarehouses->toArray() : []) !!};

// Debug: Log all available data
console.log('=== WAREHOUSE STOCK DATA ===');
console.log('Total warehouse stocks:', warehouseStockData.length);
warehouseStockData.forEach(stock => {
    console.log(`Product ${stock.product_id}, Warehouse ${stock.warehouse_id}: ${stock.quantity} units`);
});

console.log('=== BRANCH STOCK DATA ===');
console.log('Total branch stocks:', branchStockData.length);
branchStockData.forEach(stock => {
    console.log(`Product ${stock.product_id}: ${stock.quantity} units (Branch ${stock.branch_id})`);
});

console.log('=== UNIQUE WAREHOUSES ===');
console.log('Available warehouses:', uniqueWarehousesData);

// UI Interactions for Selection
document.querySelectorAll('.delivery-method-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        const branchDiv = document.getElementById('branchSelectorDiv');
        const warehouseDiv = document.getElementById('warehouseSelectorDiv');
        
        // Visual feedback for method selection
        document.querySelectorAll('.method-card').forEach(c => c.classList.remove('border-primary'));
        this.nextElementSibling.classList.add('border-primary');

        if (this.value === 'branch') {
            branchDiv.style.display = 'block';
            warehouseDiv.style.display = 'none';
        } else {
            warehouseDiv.style.display = 'block';
            branchDiv.style.display = 'none';
        }
    });
});

// Update product quantities when warehouse/branch is selected
document.getElementById('branchSelect').addEventListener('change', function() {
    console.log('Branch selected:', {value: this.value, text: this.options[this.selectedIndex].text});
    updateProductQuantities('branch', this.value);
});

document.getElementById('warehouseSelect').addEventListener('change', function() {
    console.log('Warehouse selected:', {value: this.value, type: typeof this.value, text: this.options[this.selectedIndex].text});
    console.log('WarehouseStockData available for this warehouse:', warehouseStockData.filter(s => parseInt(s.warehouse_id) === parseInt(this.value)));
    updateProductQuantities('warehouse', this.value);
});

// Update quantities based on selected location
function updateProductQuantities(locationType, locationId) {
    if (!locationId) return;

    document.querySelectorAll('.delivery-item-row').forEach(row => {
        const qtyInput = row.querySelector('.delivery-qty-input');
        const productId = qtyInput?.getAttribute('data-product-id');
        
        if (!productId) return;

        let availableQty = 0;
        let debugInfo = {productId, locationType, locationId};
        
        if (locationType === 'branch') {
            // Get quantity from branch's own stock (warehouse_id IS NULL or empty)
            const stock = branchStockData.find(s => 
                parseInt(s.product_id) === parseInt(productId) && 
                (s.warehouse_id === null || s.warehouse_id === undefined || s.warehouse_id === '' || s.warehouse_id === 0)
            );
            availableQty = stock ? parseFloat(stock.quantity) : 0;
            debugInfo.branch_stock_found = !!stock;
        } else if (locationType === 'warehouse') {
            // Get quantity from specific warehouse - ensure both are converted to int
            const stock = warehouseStockData.find(s => 
                parseInt(s.product_id) === parseInt(productId) && 
                parseInt(s.warehouse_id) === parseInt(locationId)
            );
            availableQty = stock ? parseFloat(stock.quantity) : 0;
            debugInfo.warehouse_stock_found = !!stock;
        }

        console.log('Stock Update Debug:', debugInfo, 'Available:', availableQty);

        // Update stock badge
        const stockBadge = row.querySelector('.badge');
        if (stockBadge) {
            const badgeClass = availableQty > 0 ? 'badge-light text-success' : 'badge-light text-danger';
            stockBadge.className = 'badge ' + badgeClass + ' border';
            stockBadge.textContent = `Current Stock: ${availableQty.toFixed(2)}`;
        }

        // Update input constraints and auto-fill
        if (qtyInput) {
            qtyInput.setAttribute('data-max-qty', availableQty);
            qtyInput.max = availableQty;
            
            // Auto-fill with min of available qty and required qty
            const requiredQty = parseFloat(qtyInput.getAttribute('data-required-qty') || 0);
            const defaultQty = availableQty >= requiredQty ? requiredQty : availableQty;
            qtyInput.value = defaultQty;
            
            console.log('Input Updated:', {productId, availableQty, requiredQty, defaultQty});
            
            // Trigger validation update
            qtyInput.dispatchEvent(new Event('input'));
        }
    });
}

// ERP Quantity Logic & Validation
document.querySelectorAll('.delivery-qty-input').forEach(input => {
    input.addEventListener('input', function() {
        const req = parseFloat(this.dataset.requiredQty) || 0;
        const del = parseFloat(this.value) || 0;
        const stock = parseFloat(this.dataset.maxQty) || 0;
        const productId = this.getAttribute('data-product-id');
        
        console.log('Validation Check:', {productId, required: req, delivery: del, stock: stock});
        
        const row = this.closest('.delivery-item-row');
        const msg = row.querySelector('.delivery-qty-message');
        const remDisplay = row.querySelector('.remaining-display');
        
        let remaining = (req - del).toFixed(2);
        remDisplay.textContent = remaining;

        // Reset Styles
        row.style.borderLeftColor = "#4e73df";
        msg.style.display = "none";

        if (del > req) {
            row.style.borderLeftColor = "#e74a3b";
            msg.textContent = "⚠ Exceeds Required Qty";
            msg.className = "delivery-qty-message text-danger small mt-1";
            msg.style.display = "block";
        } else if (del > stock) {
            row.style.borderLeftColor = "#f6c23e";
            msg.textContent = "⚠ Shortage in Warehouse Stock";
            msg.className = "delivery-qty-message text-warning small mt-1";
            msg.style.display = "block";
        } else if (remaining == 0) {
            row.style.borderLeftColor = "#1cc88a";
            msg.textContent = "✓ Ready for Full Delivery";
            msg.className = "delivery-qty-message text-success small mt-1";
            msg.style.display = "block";
        }
    });
    // Trigger on load
    input.dispatchEvent(new Event('input'));
});

// Final ERP Submission
document.getElementById('warehouseForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const loading = document.getElementById('loadingState');
    const errDiv = document.getElementById('errorMessage');
    
    // Quick Validation
    const method = document.querySelector('input[name="delivery_method"]:checked');
    const branchVal = document.getElementById('branchSelect').value;
    const warehouseVal = document.getElementById('warehouseSelect').value;

    if(!method) {
        alert("Please select a Delivery Method (Shop or Warehouse)");
        return;
    }

    if((method.value === 'branch' && !branchVal) || (method.value === 'warehouse' && !warehouseVal)) {
        alert("Please select a specific location");
        return;
    }

    loading.style.display = 'flex';
    errDiv.style.display = 'none';

    try {
        const formData = new FormData(this);
        formData.append('delivery_location_id', (method.value === 'branch' ? branchVal : warehouseVal));
        formData.append('delivery_method', method.value);

        const submissionData = {
            delivery_method: method.value,
            delivery_location_id: (method.value === 'branch' ? branchVal : warehouseVal),
            delivery_location_id_type: typeof (method.value === 'branch' ? branchVal : warehouseVal),
            deliveryQtys: Object.fromEntries(formData.entries())
        };
        
        console.log('Submitting form data:', submissionData);
        
        if (method.value === 'warehouse') {
            console.log('DEBUG: Looking for warehouse_id =', parseInt(warehouseVal));
            console.log('DEBUG: Warehouse stocks that match:', warehouseStockData.filter(s => parseInt(s.warehouse_id) === parseInt(warehouseVal)));
        }

        const response = await fetch("{{ route('sale.warehouse.select.store', $sale->id) }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        });

        console.log('Response status:', response.status);
        const data = await response.json();
        
        console.log('Response data:', data);
        
        if (data.ok) {
            window.location.href = data.dc_data.redirect_url;
        } else {
            throw new Error(data.error || data.message || "Transaction Failed");
        }
    } catch (err) {
        loading.style.display = 'none';
        console.error('Form submission error:', err);
        errDiv.textContent = err.message;
        errDiv.style.display = 'block';
    }
});

</script>
@endsection