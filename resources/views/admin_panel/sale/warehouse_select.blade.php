@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Compact & Professional ERP Design System */
    .erp-compact-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
    }
    .erp-card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 0.65rem 1.2rem;
        border-radius: 8px 8px 0 0;
    }
    .erp-card-title {
        color: #1e293b;
        font-weight: 700;
        margin: 0;
        font-size: 0.95rem;
        letter-spacing: 0.3px;
    }
    .erp-card-body {
        padding: 1rem 1.2rem;
    }

    /* Summary Bar */
    .summary-item {
        padding: 0.5rem 0.75rem;
    }
    .summary-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 2px;
    }
    .summary-val {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
    }

    /* Delivery Method Selection Cards */
    .method-card {
        border: 1.5px solid #e2e8f0;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        background: #ffffff;
        height: 100%;
        margin-bottom: 0;
    }
    .method-card:hover {
        border-color: #94a3b8;
        background-color: #f8fafc;
    }
    .method-card .method-icon-wrap {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #475569;
        font-size: 1.2rem;
        flex-shrink: 0;
        transition: all 0.2s ease-in-out;
    }
    input[name="delivery_method"]:checked + .method-card {
        border-color: #3b82f6;
        background-color: #eff6ff;
        box-shadow: 0 0 0 1px #3b82f6;
    }
    input[name="delivery_method"]:checked + .method-card .method-icon-wrap {
        background: #3b82f6;
        color: #ffffff;
    }
    .method-card .method-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 2px;
    }
    .method-card .method-desc {
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 0;
    }

    /* Form Controls */
    .compact-select {
        height: 38px;
        font-size: 0.88rem;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
    }
    .compact-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
    }

    /* Product Allocation Row */
    .delivery-item-row {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #94a3b8;
        border-radius: 6px;
        padding: 0.75rem 1rem;
        margin-bottom: 0.6rem;
        transition: all 0.15s ease-in-out;
    }
    .delivery-item-row:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        background-color: #fcfdfe;
    }
    .product-title {
        font-size: 0.92rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 3px;
    }
    .product-meta-badge {
        font-size: 0.75rem;
        padding: 2px 7px;
        border-radius: 4px;
        font-weight: 600;
    }
    .qty-input-wrap {
        max-width: 130px;
    }
    .qty-input {
        height: 36px;
        font-weight: 700;
        font-size: 0.95rem;
        text-align: center;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        color: #1e293b;
    }
    .qty-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
    }
    .remaining-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.35rem 0.65rem;
        display: inline-block;
        min-width: 120px;
    }
    .fade-in { animation: fadeIn 0.25s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="container-fluid py-3 px-3">
    {{-- Top Summary Bar --}}
    <div class="erp-compact-card">
        <div class="erp-card-body p-2">
            <div class="row no-gutters align-items-center text-center text-md-left">
                <div class="col-6 col-md-3 summary-item border-right">
                    <div class="summary-label"><i class="fas fa-file-invoice text-muted mr-1"></i> Invoice No.</div>
                    <div class="summary-val text-primary">#{{ $sale->invoice_no }}</div>
                </div>
                <div class="col-6 col-md-3 summary-item border-right">
                    <div class="summary-label"><i class="fas fa-user text-muted mr-1"></i> Customer</div>
                    <div class="summary-val text-truncate" title="{{ optional($sale->customer)->customer_name ?? $sale->sub_customer ?? 'N/A' }}">
                        {{ optional($sale->customer)->customer_name ?? $sale->sub_customer ?? 'N/A' }}
                    </div>
                </div>
                <div class="col-6 col-md-3 summary-item border-right">
                    <div class="summary-label"><i class="fas fa-coins text-muted mr-1"></i> Grand Total</div>
                    <div class="summary-val text-success">PKR {{ number_format($sale->total_net, 2) }}</div>
                </div>
                <div class="col-6 col-md-3 summary-item">
                    <div class="summary-label"><i class="fas fa-boxes text-muted mr-1"></i> Items Count</div>
                    <div class="summary-val">{{ $sale->saleItems->count() }} Product{{ $sale->saleItems->count() > 1 ? 's' : '' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 1: Delivery Method & Location --}}
    <div class="erp-compact-card">
        <div class="erp-card-header d-flex justify-content-between align-items-center">
            <h6 class="erp-card-title"><i class="fas fa-truck text-primary mr-2"></i> 1. Delivery Method & Source Location</h6>
        </div>
        <div class="erp-card-body">
            <div class="row">
                <div class="col-md-6 mb-2 mb-md-0">
                    <input type="radio" name="delivery_method" value="branch" id="m_branch" class="delivery-method-radio d-none">
                    <label for="m_branch" class="method-card">
                        <div class="method-icon-wrap">
                            <i class="fas fa-store"></i>
                        </div>
                        <div>
                            <div class="method-title">Shop / Branch</div>
                            <p class="method-desc">Deliver items from a specific shop branch stock.</p>
                        </div>
                    </label>
                </div>
                <div class="col-md-6">
                    <input type="radio" name="delivery_method" value="warehouse" id="m_warehouse" class="delivery-method-radio d-none">
                    <label for="m_warehouse" class="method-card">
                        <div class="method-icon-wrap">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div>
                            <div class="method-title">Main Warehouse</div>
                            <p class="method-desc">Deliver items directly from the central storage.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div id="branchSelectorDiv" class="mt-3 fade-in" style="display:none">
                <div class="form-group mb-0">
                    <label class="small font-weight-bold text-secondary mb-1">Select Source Branch <span class="text-danger">*</span></label>
                    <select id="branchSelect" class="form-control compact-select custom-select">
                        <option value="">-- Search & Select Branch --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ ((string)($selectedBranchId ?? '') === (string)$branch->id) ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="warehouseSelectorDiv" class="mt-3 fade-in" style="display:none">
                <div class="form-group mb-0">
                    <label class="small font-weight-bold text-secondary mb-1">Select Source Warehouse <span class="text-danger">*</span></label>
                    <select id="warehouseSelect" class="form-control compact-select custom-select">
                        <option value="">-- Search & Select Warehouse --</option>
                        @foreach($uniqueWarehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->warehouse_name ?? $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 2: Product Allocation & Quantities --}}
    <form id="warehouseForm">
        @csrf
        <input type="hidden" name="sale_id" value="{{ $sale->id }}">
        <input type="hidden" id="forceSaleField" name="force_sale" value="0">
        
        <div class="erp-compact-card">
            <div class="erp-card-header d-flex justify-content-between align-items-center">
                <h6 class="erp-card-title"><i class="fas fa-boxes text-primary mr-2"></i> 2. Product Allocation & Quantities</h6>
                <span class="badge badge-light text-primary border px-2 py-1" style="font-size: 0.75rem; font-weight:600;">
                    <i class="fas fa-sync-alt mr-1"></i> Live Inventory Sync
                </span>
            </div>
            <div class="erp-card-body">
                <div id="errorMessage" class="alert alert-danger mb-3" style="display:none"></div>
                <div id="successMessage" class="alert alert-success mb-3" style="display:none"></div>

                @foreach($sale->saleItems as $item)
                    @php
                        $productStocks = $warehouseStocks->where('product_id', $item->product_id);
                        $maxAvailable = $productStocks->isNotEmpty() ? $productStocks->max('quantity') : 0;
                        $shortage = ($maxAvailable < $item->sales_qty);
                    @endphp

                    <div class="delivery-item-row" style="border-left-color: {{ $shortage ? '#f59e0b' : '#10b981' }}">
                        <div class="row align-items-center">
                            {{-- Product Details --}}
                            <div class="col-md-5 mb-2 mb-md-0">
                                <div class="product-title">{{ optional($item->product)->item_name }}</div>
                                <div class="d-flex align-items-center flex-wrap gap-2 small">
                                    <span class="text-muted mr-2">Code: <strong class="text-dark">{{ optional($item->product)->item_code }}</strong></span>
                                    <span class="badge badge-light text-primary border mr-2 product-meta-badge">
                                        Required: {{ number_format($item->sales_qty, 2) }}
                                    </span>
                                    <span class="badge {{ $maxAvailable > 0 ? 'badge-light text-success' : 'badge-light text-danger' }} border product-meta-badge">
                                        Stock: {{ number_format($maxAvailable, 2) }}
                                    </span>
                                </div>
                            </div>
                            
                            {{-- Delivery Qty --}}
                            <div class="col-6 col-md-3 text-md-center">
                                <label class="summary-label d-block mb-1">Delivery Qty</label>
                                <div class="qty-input-wrap mx-md-auto">
                                    <input type="number" 
                                           name="delivery_qty[{{ $item->product_id }}]"
                                           class="form-control qty-input delivery-qty-input"
                                           data-product-id="{{ $item->product_id }}"
                                           data-required-qty="{{ $item->sales_qty }}"
                                           data-max-qty="{{ $maxAvailable }}"
                                           step="0.01"
                                           value="{{ $item->sales_qty }}">
                                </div>
                            </div>

                            {{-- Remaining & Status --}}
                            <div class="col-6 col-md-4 text-right">
                                <div class="remaining-box text-right">
                                    <div class="summary-label">Remaining</div>
                                    <span class="remaining-display font-weight-bold text-primary" style="font-size: 0.95rem;">0.00</span>
                                </div>
                                <div class="delivery-qty-message small mt-1 font-weight-bold" style="display:none"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{-- Footer Action Buttons --}}
            <div class="erp-card-header d-flex justify-content-end align-items-center py-2">
                <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-sm btn-outline-secondary px-3 mr-2 font-weight-bold">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Sale
                </a>
                <button type="submit" id="submitBtn" class="btn btn-sm btn-primary px-4 font-weight-bold shadow-sm">
                    <i class="fas fa-check-circle mr-1"></i> Confirm & Generate Challan
                </button>
            </div>
        </div>
    </form>

    <div id="loadingState" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.85); z-index:9999; flex-direction:column; align-items:center; justify-content:center;">
        <div class="spinner-border text-primary" style="width: 2.5rem; height: 2.5rem;" role="status"></div>
        <h6 class="mt-3 font-weight-bold text-dark">Processing Delivery Challan...</h6>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            // Get total quantity in the selected branch for this product
            const stocksForBranch = branchStockData.filter(s => 
                parseInt(s.product_id) === parseInt(productId) && 
                parseInt(s.branch_id) === parseInt(locationId)
            );
            availableQty = stocksForBranch.reduce((acc, s) => acc + parseFloat(s.quantity || 0), 0);
            debugInfo.branch_stock_found = stocksForBranch.length > 0;
        } else if (locationType === 'warehouse') {
            // Get quantity from specific warehouse
            const stock = warehouseStockData.find(s => 
                parseInt(s.product_id) === parseInt(productId) && 
                parseInt(s.warehouse_id) === parseInt(locationId)
            );
            availableQty = stock ? parseFloat(stock.quantity) : 0;
            debugInfo.warehouse_stock_found = !!stock;
        }

        console.log('Stock Update Debug:', debugInfo, 'Available:', availableQty);

        // Update stock badge
        const stockBadge = row.querySelector('.badge:last-of-type') || row.querySelector('.badge');
        if (stockBadge) {
            const badgeClass = availableQty > 0 ? 'badge-light text-success' : 'badge-light text-danger';
            stockBadge.className = 'badge ' + badgeClass + ' border product-meta-badge';
            stockBadge.textContent = `Stock: ${availableQty.toFixed(2)}`;
        }

        // Update input constraints and auto-fill
        if (qtyInput) {
            qtyInput.setAttribute('data-max-qty', availableQty);
            
            const requiredQty = parseFloat(qtyInput.getAttribute('data-required-qty') || 0);
            if (!qtyInput.value || parseFloat(qtyInput.value) === 0) {
                qtyInput.value = requiredQty;
            }
            
            // Trigger validation update
            qtyInput.dispatchEvent(new Event('input'));
        }
    });
}

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const branchRadio = document.getElementById('m_branch');
    if (branchRadio) {
        branchRadio.checked = true;
        branchRadio.dispatchEvent(new Event('change'));
    }

    const branchSelect = document.getElementById('branchSelect');
    if (branchSelect && branchSelect.value) {
        branchSelect.dispatchEvent(new Event('change'));
    }
});

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

    // ── STOCK SHORTAGE CHECK ────────────────────────────────────────────────
    const shortageItems = [];
    document.querySelectorAll('.delivery-qty-input').forEach(function(input) {
        const deliveryQty = parseFloat(input.value) || 0;
        const availableQty = parseFloat(input.getAttribute('data-max-qty') || 0);
        const requiredQty  = parseFloat(input.getAttribute('data-required-qty') || 0);
        // Only flag if user actually wants to deliver more than available
        if (deliveryQty > availableQty && deliveryQty > 0) {
            const row = input.closest('.delivery-item-row');
            const name = row ? (row.querySelector('h6')?.textContent?.trim() || 'Product') : 'Product';
            shortageItems.push({ name, deliveryQty, availableQty });
        }
    });

    if (shortageItems.length > 0 && document.getElementById('forceSaleField').value !== '1') {
        const listHtml = shortageItems.map(p =>
            `<li><strong>${p.name}</strong>: Available <strong>${p.availableQty}</strong>, You want to deliver <strong>${p.deliveryQty}</strong></li>`
        ).join('');

        const result = await Swal.fire({
            icon: 'warning',
            title: 'Stock Limit Exceeded',
            html: `<p>The following items have insufficient stock:</p><ul style="text-align:left">${listHtml}</ul><p>Do you want to proceed and let stock go <strong style="color:red">negative</strong>?</p>`,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Proceed',
            cancelButtonText: 'No, Cancel',
        });

        if (!result.isConfirmed) {
            return; // User cancelled
        }
        // Mark as force sale
        document.getElementById('forceSaleField').value = '1';
    }
    // ── END STOCK SHORTAGE CHECK ────────────────────────────────────────────

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