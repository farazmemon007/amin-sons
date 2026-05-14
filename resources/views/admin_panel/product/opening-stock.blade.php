@extends('admin_panel.layout.app')

@section('content')
    @can('product.edit')
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

            .main-content {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: #f1f5f9;
                height: auto; /* Allow scrolling for multiple rows */
                padding: 1.5rem;
            }

            /* Compact Header */
            .page-header-alt {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
            }

            /* The "Command Center" Card */
            .command-card {
                background: white;
                border-radius: 12px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                display: flex;
                flex-direction: column;
                min-height: auto;
            }

            /* Left Info Panel (Sticky Side) */
            .product-side-panel {
                background: #1e293b;
                color: white;
                padding: 1.5rem;
                border-radius: 12px 0 0 0;
                width: 280px;
                flex-shrink: 0;
            }

            .info-label { font-size: 10px; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; margin-bottom: 2px; }
            .info-val { font-size: 14px; font-weight: 600; margin-bottom: 1.2rem; display: block; }

            /* Main Form Area */
            .form-main-area {
                flex-grow: 1;
                padding: 1.5rem;
                overflow-y: auto;
                max-height: calc(100vh - 200px);
            }

            /* Compact Grid */
            .input-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 1.2rem;
            }

            .form-group-compact { margin-bottom: 0; }
            .form-group-compact label { 
                font-weight: 600; 
                font-size: 13px; 
                color: #475569; 
                margin-bottom: 6px; 
                display: flex; 
                align-items: center; 
                gap: 6px;
            }

            .form-control-modern {
                background: #f8fafc;
                border: 1.5px solid #e2e8f0;
                padding: 10px 14px;
                font-size: 14px;
                border-radius: 8px;
                transition: all 0.2s;
            }

            .form-control-modern:focus {
                background: white;
                border-color: #6366f1;
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
                outline: none;
            }

            /* Location Toggle - Compact */
            .location-selector {
                display: flex;
                gap: 10px;
                background: #f1f5f9;
                padding: 5px;
                border-radius: 10px;
                margin-top: 5px;
            }

            .loc-option { flex: 1; text-align: center; }
            .loc-option input { display: none; }
            .loc-option label {
                padding: 8px;
                border-radius: 7px;
                cursor: pointer;
                font-size: 13px;
                font-weight: 600;
                transition: 0.2s;
                margin-bottom: 0;
                display: block;
                color: #64748b;
            }

            .loc-option input:checked + label {
                background: white;
                color: #6366f1;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }

            /* Allocation Rows Table */
            .allocation-rows-container {
                background: #f8fafc;
                border: 1.5px solid #e2e8f0;
                border-radius: 8px;
                padding: 0;
                margin-top: 1rem;
            }

            .allocation-row {
                display: grid;
                grid-template-columns: 120px 180px 100px auto 50px;
                gap: 10px;
                align-items: end;
                padding: 12px;
                border-bottom: 1px solid #e2e8f0;
            }

            .allocation-row:last-child {
                border-bottom: none;
            }

            .allocation-row-header {
                display: grid;
                grid-template-columns: 120px 180px 100px auto 50px;
                gap: 10px;
                padding: 8px 12px;
                background: #e2e8f0;
                font-weight: 700;
                font-size: 12px;
                color: #475569;
            }

            /* Position containers in same grid cell */
            .location-display-wrapper {
                grid-column: 2;
                grid-row: 1;
                display: contents;
            }

            .warehouse-select-container,
            .shop-label-container {
                grid-column: 2;
                grid-row: 1;
            }

            .location-type-select {
                grid-column: 1;
                padding: 8px;
                border: 1.5px solid #e2e8f0;
                border-radius: 6px;
                font-size: 13px;
            }

            .warehouse-select-mini {
                padding: 8px;
                border: 1.5px solid #e2e8f0;
                border-radius: 6px;
                font-size: 13px;
                width: 100%;
            }

            .qty-input-mini {
                grid-column: 3;
                padding: 8px;
                border: 1.5px solid #e2e8f0;
                border-radius: 6px;
                font-size: 13px;
                width: 100%;
            }

            .qty-display {
                grid-column: 4;
                padding: 8px;
                background: white;
                border: 1.5px solid #e2e8f0;
                border-radius: 6px;
                font-size: 13px;
                color: #6366f1;
                font-weight: 600;
            }

            .btn-delete-row {
                grid-column: 5;
                background: #ef4444;
                color: white;
                border: none;
                border-radius: 6px;
                padding: 6px 8px;
                cursor: pointer;
                font-size: 12px;
                transition: 0.2s;
            }

            .btn-delete-row:hover {
                background: #dc2626;
            }

            .btn-add-row {
                background: #6366f1;
                color: white;
                border: none;
                border-radius: 6px;
                padding: 10px 16px;
                cursor: pointer;
                font-size: 13px;
                font-weight: 600;
                margin-top: 10px;
                transition: 0.2s;
            }

            .btn-add-row:hover {
                background: #4f46e5;
            }

            .qty-sync-status {
                padding: 8px 12px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 600;
                margin-top: 10px;
                display: none;
            }

            .qty-sync-status.ok {
                background: #dcfce7;
                color: #166534;
                display: block;
            }

            .qty-sync-status.warning {
                background: #fef3c7;
                color: #92400e;
                display: block;
            }

            .qty-sync-status.error {
                background: #fee2e2;
                color: #991b1b;
                display: block;
            }

            /* Action Bar */
            .bottom-actions {
                border-top: 1px solid #e2e8f0;
                padding: 1rem 1.5rem;
                display: flex;
                justify-content: flex-end;
                gap: 12px;
                background: #f8fafc;
                border-radius: 0 0 12px 12px;
            }

            .btn-primary-modern {
                background: #6366f1;
                color: white;
                border: none;
                padding: 10px 24px;
                border-radius: 8px;
                font-weight: 700;
                font-size: 14px;
            }

            .badge-step {
                background: rgba(99, 102, 241, 0.1);
                color: #6366f1;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 700;
            }

            /* Invalid input styling */
            .form-control-modern.is-invalid {
                border-color: #dc2626 !important;
                background-color: #fef2f2;
            }

            .form-control-modern.is-invalid:focus {
                border-color: #dc2626 !important;
                box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1) !important;
            }
        </style>

        <div class="main-content">
            <div class="page-header-alt">
                <div>
                    <h4 class="fw-bold mb-0">Inventory Setup <span class="badge-step ms-2">STEP 2 OF 3</span></h4>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('product') }}" class="btn btn-sm btn-outline-secondary">Save Draft</a>
                    <button type="submit" form="openingStockForm" class="btn btn-sm btn-primary-modern shadow-sm">Finalize Product</button>
                </div>
            </div>

            <div class="command-card flex-row">
                <div class="product-side-panel">
                    <div class="mb-4">
                        <i class="las la-cube la-3x text-indigo-400 mb-3" style="color: #818cf8;"></i>
                        <h5 class="fw-bold text-white mb-1">{{ $product->item_name }}</h5>
                        <p class="small text-muted mb-0">SKU: {{ $product->item_code }}</p>
                    </div>
                    <hr style="border-color: rgba(255,255,255,0.1)">
                    
                    <span class="info-label">Category</span>
                    <span class="info-val">{{ $product->category_relation?->name ?? 'Uncategorized' }}</span>

                    <span class="info-label">Current Unit (UOM)</span>
                    <span class="info-val"><span class="badge bg-primary">{{ $product->unit?->name ?? 'PCS' }}</span></span>

                    <div class="alert alert-info border-0 p-3 mt-4" style="background: rgba(99, 102, 241, 0.1);">
                        <p class="small mb-0 text-white opacity-75">
                            <i class="las la-lightbulb"></i> Tip: Ensure Wholesale price is not less than the Retail price.
                        </p>
                    </div>
                </div>

                <div class="form-main-area">
                    <form id="openingStockForm" action="{{ route('store-product') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="phase" value="phase2">

                        <h6 class="fw-bold mb-3 text-dark"><i class="las la-boxes me-2"></i>Stock & Valuation</h6>
                        <div class="input-grid mb-4">
                            <div class="form-group-compact">
                                <label><i class="las la-sort-amount-up"></i> Opening Stock</label>
                                <div class="input-group">
                                    <input type="number" name="opening_stock" class="form-control-modern w-100" placeholder="0.00" step="0.01" required>
                                </div>
                            </div>
                            <div class="form-group-compact">
                                <label class="text-danger"><i class="las la-bell"></i> Alert Level</label>
                                <input type="number" name="alert_quantity" class="form-control-modern w-100" placeholder="Low stock limit" step="0.01">
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold mb-3 text-dark"><i class="las la-tags me-2"></i>Pricing Structure</h6>
                        <div class="input-grid mb-4">
                            <div class="form-group-compact">
                                <label>Wholesale (B2B)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-0 ps-0 text-muted">₨</span>
                                    <input type="number" name="wholesale_price" class="form-control-modern w-100" placeholder="Rate per unit" required>
                                </div>
                            </div>                            <div class="form-group-compact">
                                <label class="text-primary">Retail Price (B2C)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white border-primary">₨</span>
                                    <input type="number" name="retail_price" class="form-control-modern" 
                                           value="{{ $product->price ?? 0 }}" step="0.01" min="0" required>
                                </div>
                                <div id="price-validation-warning" style="display:none; color:#dc2626; font-size:12px; font-weight:700; margin-top:5px; background:#fff1f2; padding:8px; border-radius:6px; border:1px solid #fca5a5;">
                                    ⚠️ Warning: Retail price should not be less than wholesale
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold mb-2 text-dark"><i class="las la-map-marker me-2"></i>Stock Allocation</h6>
                        <p class="small text-muted mb-2">Distribute opening stock across multiple warehouses/branches</p>
                        
                        <!-- Allocation Rows Table -->
                        <div class="allocation-rows-container">
                            <div class="allocation-row-header">
                                <div>Location Type</div>
                                <div>Warehouse/Branch</div>
                                <div>Qty</div>
                                <div>Total %</div>
                                <div></div>
                            </div>
                            
                            <div id="allocation_rows_list">
                                <!-- Rows will be added here -->
                            </div>
                        </div>
                        
                        <button type="button" id="btn_add_allocation_row" class="btn-add-row">
                            <i class="las la-plus"></i> Add Allocation Row
                        </button>
                        
                        <div id="qty_sync_status" class="qty-sync-status">
                            ✓ Allocations match opening stock
                        </div>
                    </form>
                </div>
            </div>

            <div class="bottom-actions">
                <span class="me-auto text-muted small align-self-center">Last updated: Just now</span>
                <button type="submit" form="openingStockForm" class="btn btn-primary-modern px-5">Finalize & Save</button>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('openingStockForm');
                const openingStockInput = form.querySelector('input[name="opening_stock"]');
                const allocationList = document.getElementById('allocation_rows_list');
                const addRowBtn = document.getElementById('btn_add_allocation_row');
                const qtyStatusDiv = document.getElementById('qty_sync_status');
                const warehouseOptions = @json($warehouses);
                
                // Price validation
                const wholesalePriceInput = form.querySelector('input[name="wholesale_price"]');
                const retailPriceInput = form.querySelector('input[name="retail_price"]');
                const priceWarningDiv = document.getElementById('price-validation-warning');

                function validatePrices() {
                    const wholesale = parseFloat(wholesalePriceInput.value) || 0;
                    const retail = parseFloat(retailPriceInput.value) || 0;
                    
                    if (wholesale > 0 && retail > 0 && retail < wholesale) {
                        wholesalePriceInput.style.borderColor = '#dc2626';
                        retailPriceInput.style.borderColor = '#dc2626';
                        priceWarningDiv.style.display = 'block';
                        return false;
                    } else {
                        wholesalePriceInput.style.borderColor = '';
                        retailPriceInput.style.borderColor = '';
                        priceWarningDiv.style.display = 'none';
                        return true;
                    }
                }

                if (wholesalePriceInput && retailPriceInput) {
                    wholesalePriceInput.addEventListener('input', validatePrices);
                    retailPriceInput.addEventListener('input', validatePrices);
                    validatePrices();
                }

                // Generate warehouse dropdown HTML with available options
                function getWarehouseOptions(excludeRowIndex = null) {
                    let html = '<option value="">-- Select Warehouse --</option>';
                    warehouseOptions.forEach(wh => {
                        html += `<option value="${wh.id}">${wh.warehouse_name}</option>`;
                    });
                    return html;
                }

                // Get currently selected warehouses and shops
                function getSelectedItems() {
                    const items = new Set();
                    const rows = document.querySelectorAll('.allocation-row');
                    rows.forEach(row => {
                        const locType = row.querySelector('.location-type').value;
                        const warehouseSelect = row.querySelector('.warehouse-select');
                        
                        if (locType === 'shop') {
                            items.add('shop');
                        } else if (locType === 'warehouse' && warehouseSelect && warehouseSelect.value) {
                            items.add('warehouse_' + warehouseSelect.value);
                        }
                    });
                    return items;
                }

                // Generate location type options (with filtering for shop)
                function getLocationTypeOptions(currentValue = '') {
                    const selectedItems = getSelectedItems();
                    const isShopSelected = selectedItems.has('shop');
                    
                    let html = '';
                    
                    // Only show Shop option if not already selected elsewhere OR it's the current value
                    if (!isShopSelected || currentValue === 'shop') {
                        html += '<option value="shop">🏪 Shop</option>';
                    }
                    
                    html += '<option value="warehouse">📦 Warehouse</option>';
                    return html;
                }

                // Update all location type dropdowns to filter out selected shops
                function updateLocationTypeDropdowns() {
                    const rows = document.querySelectorAll('.allocation-row');
                    const selectedItems = getSelectedItems();
                    const isShopSelected = selectedItems.has('shop');
                    
                    rows.forEach(row => {
                        const locTypeSelect = row.querySelector('.location-type');
                        const currentValue = locTypeSelect.value;
                        
                        // Rebuild location type options
                        let html = '';
                        
                        // Only show Shop option if not selected elsewhere OR it's current value
                        if (!isShopSelected || currentValue === 'shop') {
                            html += '<option value="shop">🏪 Shop</option>';
                        }
                        
                        html += '<option value="warehouse">📦 Warehouse</option>';
                        locTypeSelect.innerHTML = html;
                        locTypeSelect.value = currentValue;
                    });
                }

                // Update all warehouse dropdowns to filter out selected items
                function updateWarehouseDropdowns() {
                    const rows = document.querySelectorAll('.allocation-row');
                    const selectedItems = getSelectedItems();
                    
                    rows.forEach(row => {
                        const locType = row.querySelector('.location-type').value;
                        const warehouseSelect = row.querySelector('.warehouse-select');
                        const currentValue = warehouseSelect ? warehouseSelect.value : '';
                        
                        if (warehouseSelect && locType === 'warehouse') {
                            // Rebuild options
                            let html = '<option value="">-- Select Warehouse --</option>';
                            warehouseOptions.forEach(wh => {
                                const isSelected = selectedItems.has('warehouse_' + wh.id);
                                const isCurrentValue = wh.id == currentValue; // Keep current value
                                
                                // Show option if: not selected OR it's the current value
                                if (!isSelected || isCurrentValue) {
                                    html += `<option value="${wh.id}">${wh.warehouse_name}</option>`;
                                }
                            });
                            warehouseSelect.innerHTML = html;
                            warehouseSelect.value = currentValue;
                        }
                    });
                }

                // Create allocation row HTML
                function createAllocationRow(rowIndex) {
                    return `
                    <div class="allocation-row" data-row-index="${rowIndex}">
                        <select class="location-type-select location-type" data-row="${rowIndex}">
                            ${getLocationTypeOptions()}
                        </select>
                        
                        <div class="location-display-wrapper">
                            <div class="warehouse-select-container" style="display:none;">
                                <select class="warehouse-select-mini warehouse-select" data-row="${rowIndex}">
                                    ${getWarehouseOptions()}
                                </select>
                            </div>
                            
                            <div class="shop-label-container" style="padding:8px; background:white; border:1.5px solid #e2e8f0; border-radius:6px; font-size:13px;">
                                Branch Stock
                            </div>
                        </div>
                        
                        <input type="number" class="qty-input-mini qty-input" data-row="${rowIndex}" placeholder="0.00" step="0.01" min="0" value="0">
                        
                        <div class="qty-display qty-percent" data-row="${rowIndex}">0%</div>
                        
                        <button type="button" class="btn-delete-row" onclick="deleteAllocationRow(${rowIndex})">
                            ✕
                        </button>
                    </div>
                    `;
                }

                // Delete allocation row
                window.deleteAllocationRow = function(rowIndex) {
                    const row = document.querySelector(`[data-row-index="${rowIndex}"]`);
                    if (row) {
                        row.remove();
                    }
                    updateLocationTypeDropdowns();
                    updateWarehouseDropdowns();
                    updateQtyStatus();
                };


                // Update quantity percentage
                function updateQtyStatus() {
                    const openingQty = parseFloat(openingStockInput.value) || 0;
                    const rows = document.querySelectorAll('.allocation-row');
                    let totalAllocated = 0;

                    rows.forEach(row => {
                        const qtyInput = row.querySelector('.qty-input');
                        const qtyDisplay = row.querySelector('.qty-percent');
                        const qty = parseFloat(qtyInput.value) || 0;
                        totalAllocated += qty;
                        
                        if (openingQty > 0) {
                            const percent = ((qty / openingQty) * 100).toFixed(1);
                            qtyDisplay.textContent = percent + '%';
                        } else {
                            qtyDisplay.textContent = '0%';
                        }
                    });

                    // Update status message
                    if (totalAllocated === openingQty && openingQty > 0) {
                        qtyStatusDiv.className = 'qty-sync-status ok';
                        qtyStatusDiv.innerHTML = '✓ Allocations match opening stock (' + openingQty + ' units)';
                    } else if (totalAllocated > openingQty) {
                        qtyStatusDiv.className = 'qty-sync-status error';
                        qtyStatusDiv.innerHTML = '✗ Over-allocated: ' + totalAllocated + ' of ' + openingQty;
                    } else if (totalAllocated > 0 && totalAllocated < openingQty) {
                        qtyStatusDiv.className = 'qty-sync-status warning';
                        qtyStatusDiv.innerHTML = '⚠ Under-allocated: ' + totalAllocated + ' of ' + openingQty;
                    } else {
                        qtyStatusDiv.className = 'qty-sync-status';
                    }
                }

                // Add event listeners to row
                function attachRowListeners(newRow, rowIndex) {
                    const locTypeSelect = newRow.querySelector('.location-type');
                    const qtyInput = newRow.querySelector('.qty-input');
                    const warehouseContainer = newRow.querySelector('.warehouse-select-container');
                    const shopLabel = newRow.querySelector('.shop-label-container');
                    const warehouseSelect = newRow.querySelector('.warehouse-select');
                    
                    // Function to update display based on current location type
                    function updateDisplayBasedOnLocationType() {
                        const currentLocType = locTypeSelect.value;
                        
                        if (currentLocType === 'warehouse') {
                            warehouseContainer.style.display = 'block';
                            shopLabel.style.display = 'none';
                            
                            // Immediately rebuild warehouse options
                            if (warehouseSelect) {
                                const selectedItems = getSelectedItems();
                                let html = '<option value="">-- Select Warehouse --</option>';
                                warehouseOptions.forEach(wh => {
                                    const isSelected = selectedItems.has('warehouse_' + wh.id);
                                    if (!isSelected) {
                                        html += `<option value="${wh.id}">${wh.warehouse_name}</option>`;
                                    }
                                });
                                warehouseSelect.innerHTML = html;
                            }
                        } else {
                            warehouseContainer.style.display = 'none';
                            shopLabel.style.display = 'block';
                            if (warehouseSelect) {
                                warehouseSelect.value = '';
                            }
                        }
                    }
                    
                    // Location type change
                    locTypeSelect.addEventListener('change', function() {
                        updateDisplayBasedOnLocationType();
                        updateLocationTypeDropdowns();
                        updateWarehouseDropdowns();
                        updateQtyStatus();
                    });
                    
                    // Warehouse selection change
                    if (warehouseSelect) {
                        warehouseSelect.addEventListener('change', function() {
                            updateLocationTypeDropdowns();
                            updateWarehouseDropdowns();
                            updateQtyStatus();
                        });
                    }
                    
                    // Quantity input change
                    qtyInput.addEventListener('input', updateQtyStatus);
                    
                    // Initialize display based on current location type value
                    updateDisplayBasedOnLocationType();
                }

                // Add row button
                addRowBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const rowIndex = Date.now();
                    const rowHTML = createAllocationRow(rowIndex);
                    allocationList.insertAdjacentHTML('beforeend', rowHTML);
                    
                    const newRow = document.querySelector(`[data-row-index="${rowIndex}"]`);
                    attachRowListeners(newRow, rowIndex);
                    updateLocationTypeDropdowns();
                    updateWarehouseDropdowns();
                    updateQtyStatus();
                });

                // Opening stock change listener
                openingStockInput.addEventListener('change', updateQtyStatus);

                // Initialize with one default row
                allocationList.innerHTML = createAllocationRow(1);
                const firstRow = allocationList.querySelector('.allocation-row');
                attachRowListeners(firstRow, 1);
                updateLocationTypeDropdowns();
                updateWarehouseDropdowns();
                updateQtyStatus();

                // Form submission validation
                form.addEventListener('submit', function(event) {
                    // Check price validation first: Retail >= Wholesale
                    if (!validatePrices()) {
                        event.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Price Mismatch',
                            text: 'Retail price should not be less than wholesale price.'
                        });
                        retailPriceInput.focus();
                        return false;
                    }

                    const openingQty = parseFloat(openingStockInput.value) || 0;
                    const rows = document.querySelectorAll('.allocation-row');
                    let totalAllocated = 0;
                    let warehouseSelected = true;
                    let duplicateShop = false;

                    // Build allocation data
                    let allocationData = [];
                    let shopCount = 0;
                    
                    rows.forEach((row, index) => {
                        const locType = row.querySelector('.location-type').value;
                        const warehouseSelect = row.querySelector('.warehouse-select');
                        const qtyInput = row.querySelector('.qty-input');
                        const qty = parseFloat(qtyInput.value) || 0;
                        
                        totalAllocated += qty;

                        if (locType === 'shop') {
                            shopCount++;
                            if (shopCount > 1) {
                                duplicateShop = true;
                            }
                        }

                        allocationData.push({
                            location_type: locType,
                            warehouse_id: locType === 'warehouse' ? warehouseSelect.value : null,
                            quantity: qty
                        });

                        if (locType === 'warehouse' && !warehouseSelect.value) {
                            warehouseSelected = false;
                        }
                    });

                    if (duplicateShop) {
                        event.preventDefault();
                        alert('⚠️ Only one Shop/Branch allocation is allowed');
                        return false;
                    }

                    if (totalAllocated !== openingQty) {
                        event.preventDefault();
                        alert('⚠️ Total allocated quantity must equal opening stock (' + openingQty + ' units)');
                        return false;
                    }

                    if (!warehouseSelected) {
                        event.preventDefault();
                        alert('⚠️ Please select a warehouse for all warehouse location rows');
                        return false;
                    }

                    if (rows.length === 0) {
                        event.preventDefault();
                        alert('⚠️ Please add at least one allocation row');
                        return false;
                    }

                    // Add hidden input for allocation data
                    const existingInput = form.querySelector('input[name="allocation_data"]');
                    if (existingInput) {
                        existingInput.remove();
                    }
                    
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'allocation_data';
                    hiddenInput.value = JSON.stringify(allocationData);
                    form.appendChild(hiddenInput);

                    return true;
                });
            });
        </script>
    @else
        <div class="alert alert-danger m-5">Access Denied</div>
    @endcan
@endsection