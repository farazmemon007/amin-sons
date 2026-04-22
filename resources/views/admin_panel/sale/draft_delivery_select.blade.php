@extends('admin_panel.layout.app')
@section('content')

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fe fe-truck"></i> Select Warehouse for Delivery Challan
                    </h5>
                </div>
                <div class="card-body">
                    
                    <!-- Sale Header Info -->
                    <div class="row mb-4 p-3 bg-light border rounded">
                        <div class="col-md-2">
                            <strong>Invoice No:</strong><br>
                            <span class="text-primary font-weight-bold">{{ $sale->invoice_no }}</span>
                        </div>
                        <div class="col-md-2">
                            <strong>Customer Type:</strong><br>
                            <span class="text-dark">
                                @if($sale->customer && $sale->customer->customer_type)
                                    {{ $sale->customer->customer_type }}
                              
                                @endif
                            </span>
                        </div>
                        <div class="col-md-2">
                            <strong>Customer:</strong><br>
                            <span class="text-dark">
                                @if($sale->customer && $sale->customer->customer_name)
                                    {{ $sale->customer->customer_name }}
                                @elseif($sale->sub_customer)
                                    {{ $sale->sub_customer }}
                                @else
                                    Walking Customer
                                @endif
                            </span>
                        </div>
                        <div class="col-md-4">
                            <strong>Total Amount:</strong><br>
                            <span class="text-success font-weight-bold">{{ number_format($sale->total_net, 2) }}</span>
                        </div>
                        <div class="col-md-2">
                            <strong>Status:</strong><br>
                            <span class="badge badge-info">Not Delivered</span>
                        </div>
                    </div>

                    <!-- Warehouse Selection Form -->
                    <form id="warehouseSelectionForm" action="{{ route('sale.process_draft_delivery', $sale->id) }}" method="POST">
                        @csrf
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">Product Name</th>
                                        <th width="10%">SKU</th>
                                        <th width="10%">Qty Needed</th>
                                        <th width="50%">Select Warehouse</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($warehouseOptions as $index => $option)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $option['product_name'] }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $option['item_code'] }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ number_format($option['qty_needed'], 2) }}</span>
                                        </td>
                                        <td>
                                            <!-- Warehouse Selection Dropdown -->
                                            <div class="form-group mb-0">
                                                <select 
                                                    name="warehouse_id[{{ $index }}]" 
                                                    class="form-control form-control-sm warehouse-select"
                                                    data-product-id="{{ $option['product_id'] }}"
                                                    data-item-index="{{ $index }}"
                                                    required>
                                                    <option value="">-- Select Warehouse --</option>

                                                    <!-- Branch Stock Option -->
                                                    <option value="branch_stock">
                                                        <i class="fe fe-home"></i> 
                                                        Branch Stock 
                                                        (Available: {{ number_format($option['branch_stock_qty'], 2) }})
                                                    </option>

                                                    <!-- Warehouse Options -->
                                                    @forelse($option['warehouses'] as $warehouse)
                                                    <option value="{{ $warehouse['warehouse_id'] }}">
                                                        <i class="fe fe-layers"></i> 
                                                        {{ $warehouse['warehouse_name'] }} 
                                                        (Available: {{ number_format($warehouse['available_qty'], 2) }})
                                                    </option>
                                                    @empty
                                                    <option value="" disabled>
                                                        ⚠️ No warehouse stock available
                                                    </option>
                                                    @endforelse
                                                </select>

                                                <!-- Validation Error -->
                                                @if($errors->has("warehouse_id.$index"))
                                                <span class="text-danger small">
                                                    <i class="fe fe-alert-circle"></i>
                                                    {{ $errors->first("warehouse_id.$index") }}
                                                </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No items found in this sale
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="history.back()">
                                    <i class="fe fe-arrow-left"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-success btn-sm float-right">
                                    <i class="fe fe-check"></i> Process & Generate DC
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="spinner-border text-white" role="status">
        <span class="sr-only">Processing...</span>
    </div>
</div>

<script>
document.getElementById('warehouseSelectionForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validate all warehouses are selected
    let allSelected = true;
    document.querySelectorAll('.warehouse-select').forEach(select => {
        if (!select.value) {
            allSelected = false;
            select.classList.add('is-invalid');
        } else {
            select.classList.remove('is-invalid');
        }
    });

    if (!allSelected) {
        alert('⚠️ Please select a warehouse for each product');
        return;
    }

    // Show loading overlay
    document.getElementById('loadingOverlay').style.display = 'flex';

    // Submit form via AJAX
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.ok && data.redirect_url) {
            // Redirect immediately without alert
            window.location.href = data.redirect_url;
        } else {
            document.getElementById('loadingOverlay').style.display = 'none';
            alert('❌ Error: ' + (data.error || data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        document.getElementById('loadingOverlay').style.display = 'none';
        console.error('Error:', error);
        alert('❌ Error: ' + error.message);
    });
});

// Add visual feedback to dropdown selects
document.querySelectorAll('.warehouse-select').forEach(select => {
    select.addEventListener('change', function() {
        if (this.value) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.add('is-invalid');
        }
    });
});

// Prevent form submission via Enter key (use button instead)
document.getElementById('warehouseSelectionForm').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
    }
});
</script>

<style>
.warehouse-select {
    transition: border-color 0.3s ease;
}

.warehouse-select.is-valid {
    border-color: #28a745;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.5 4.91c-.3-.3-.3-.77 0-1.06.3-.3.77-.3 1.06 0L3 5.24l3.44-3.44c.3-.3.77-.3 1.06 0 .3.29.3.77 0 1.06L4.07 7.3c-.3.3-.77.3-1.07-.21z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.3rem center;
    background-size: 0.8em 0.8em;
    padding-right: 2.2rem;
}

.warehouse-select.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.9h-.6z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.3rem center;
    background-size: 0.8em 0.8em;
    padding-right: 2.2rem;
}

#loadingOverlay {
    animation: fadeIn 0.2s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.table-bordered td {
    vertical-align: middle;
}

.badge {
    font-size: 0.85rem;
    padding: 0.4rem 0.6rem;
}
</style>

@endsection
