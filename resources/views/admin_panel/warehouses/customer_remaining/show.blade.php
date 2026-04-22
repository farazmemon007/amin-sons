@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Increase all font sizes */
    .form-label {
        font-size: 0.95rem !important;
        font-weight: 600;
    }
    
    .card-body p {
        font-size: 1.05rem !important;
    }
    
    .card-header h6 {
        font-size: 1.1rem !important;
        font-weight: 600;
    }
    
    .badge {
        font-size: 0.95rem !important;
        padding: 0.6rem 0.8rem !important;
    }
    
    .table {
        font-size: 1rem !important;
    }
    
    .table th {
        font-size: 1.05rem !important;
        font-weight: 600;
    }
    
    .table td {
        font-size: 1rem !important;
    }
    
    code {
        font-size: 1rem !important;
    }
    
    .btn {
        font-size: 1rem !important;
    }
    
    h3 {
        font-size: 1.8rem !important;
    }
    
    .alert {
        font-size: 1rem !important;
    }
</style>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="mb-0">
                <i class="fas fa-box-open text-info"></i> Remaining Delivery Details
            </h3>
            <small class="text-muted">Item pending delivery to customer</small>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('customer-remaining.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Item Details -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Item Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">Product Name</label>
                            @php
                                $displayProductName = $item->product_name ?: ($item->product?->item_name ?? 'N/A');
                            @endphp
                            <p class="mb-0"><strong>{{ $displayProductName }}</strong></p>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">Item Code</label>
                            @php
                                $displayItemCode = $item->item_code ?: ($item->product?->item_code ?? 'N/A');
                            @endphp
                            <p class="mb-0"><code>{{ $displayItemCode }}</code></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">Unit</label>
                            @php
                                $displayUnit = $item->unit ?: ($item->product?->unit?->name ?? 'N/A');
                            @endphp
                            <p class="mb-0">{{ $displayUnit }}</p>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">Remaining Quantity</label>
                            <p class="mb-0"><span class="badge bg-danger">{{ number_format($item->remaining_qty, 4) }}</span></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">Warehouse</label>
                            <p class="mb-0">{{ optional($item->warehouse)->warehouse_name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">Status</label>
                            <p class="mb-0">
                                @php
                                    $statusColor = match($item->status ?? 'pending') {
                                        'pending' => 'warning',
                                        'partial' => 'info',
                                        'completed' => 'success',
                                        default => 'secondary'
                                    };
                                    $statusLabel = match($item->status ?? 'pending') {
                                        'pending' => 'Pending',
                                        'partial' => 'Partial',
                                        'completed' => 'Completed',
                                        default => 'Unknown'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                            </p>
                        </div>
                    </div>

                    @if($item->notes)
                        <div class="mb-3">
                            <label class="form-label text-muted small">Notes</label>
                            <p class="mb-0">{{ $item->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sale Information -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Sale Invoice Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">Sale Invoice No</label>
                            <p class="mb-0"><code class="bg-light px-2 py-1 rounded">Sale #{{ $item->sale_id }}</code></p>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">Invoice Date</label>
                            <p class="mb-0">{{ optional($item->sale)?->created_at?->format('d-m-Y') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    @if($saleItem)
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label text-muted small">Total Qty Sold</label>
                                <p class="mb-0"><span class="badge bg-primary">{{ number_format($saleItem->sales_qty, 4) }}</span></p>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small">Total Sale Items</label>
                                <p class="mb-0"><strong>{{ optional($item->sale)->saleItems()->count() ?? 0 }}</strong></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label text-muted small">Total Received Qty</label>
                                <p class="mb-0">
                                    @php
                                        $totalReceivedQty = $deliveries->sum('product_qty');
                                    @endphp
                                    <span class="badge bg-success">{{ number_format($totalReceivedQty, 4) }}</span>
                                </p>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small">Pending Qty</label>
                                <p class="mb-0">
                                    <span class="badge bg-warning">{{ number_format($item->remaining_qty, 4) }}</span>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Details -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Customer Information</h6>
                </div>
                <div class="card-body">
                    @php
                        $isWalkingCustomer = $item->customer_id === null;
                        $customerName = $isWalkingCustomer 
                            ? $item->sub_customer_name ?? $item->sale?->sub_customer ?? 'Walking Customer'
                            : optional($item->customer)->customer_name;
                    @endphp
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Customer Name</label>
                                <p class="mb-0">
                                    <strong>{{ $customerName }}</strong>
                                    @if($isWalkingCustomer)
                                        <br><small class="badge bg-warning text-dark">Walking Customer (No Account)</small>
                                    @endif
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small">Contact Person</label>
                                <p class="mb-0">{{ optional($item->customer)->contact_person ?? ($isWalkingCustomer ? 'N/A' : 'N/A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label text-muted small">Phone</label>
                                    <p class="mb-0">{{ optional($item->customer)->phone ?? 'N/A' }}</p>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small">City</label>
                                    <p class="mb-0">{{ optional($item->customer)->city ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small">Address</label>
                                <p class="mb-0">{{ optional($item->customer)->address ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if($relatedItems->count())
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">Other Pending Items from Same Sale</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Item Code</th>
                                <th>Unit</th>
                                <th>Remaining Qty</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($relatedItems as $related)
                                <tr>
                                    <!-- ✅ INTERNATIONAL ERP STANDARD: Product details with fallback to Product model -->
                                    @php
                                        $displayRelatedProductName = $related->product_name ?: ($related->product?->item_name ?? 'N/A');
                                        $displayRelatedItemCode = $related->item_code ?: ($related->product?->item_code ?? 'N/A');
                                        $displayRelatedUnit = $related->unit ?: ($related->product?->unit?->name ?? 'N/A');
                                    @endphp
                                    <td>{{ $displayRelatedProductName }}</td>
                                    <td><code>{{ $displayRelatedItemCode }}</code></td>
                                    <td>{{ $displayRelatedUnit }}</td>
                                    <td>{{ number_format($related->remaining_qty, 4) }}</td>
                                    <td>
                                        @php
                                            $statusColor = match($related->status ?? 'pending') {
                                                'pending' => 'warning',
                                                'partial' => 'info',
                                                'completed' => 'success',
                                                default => 'secondary'
                                            };
                                            $statusLabel = match($related->status ?? 'pending') {
                                                'pending' => 'Pending',
                                                'partial' => 'Partial',
                                                'completed' => 'Completed',
                                                default => 'Unknown'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('customer-remaining.show', $related->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Delivery History -->
    @if($deliveries->count())
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">Delivery History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Gatepass No</th>
                                <th>Qty Delivered</th>
                                <th>Delivery Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deliveries as $delivery)
                                <tr>
                                    <td><code>{{ $delivery['gatepass_no'] }}</code></td>
                                    <td><span class="badge bg-info">{{ number_format($delivery['product_qty'], 4) }}</span></td>
                                    <td>{{ $delivery['delivered_date']?->format('d-m-Y H:i') ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle"></i> No deliveries recorded yet for this item.
        </div>
    @endif

    <!-- Actions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <!-- Status Messages - Based on ERP Workflow Step -->
            @switch($actionStep ?? null)
                @case('completed')
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-check-circle"></i> <strong>Item Delivered!</strong> This item has been fully delivered to the customer. No further actions required.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @break

                @case('all_completed')
                    <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-check"></i> <strong>All Delivered!</strong> All items from this sale have been delivered to the customer. No further gate passes required.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @break

                @case('create_dc')
                    <!-- ✅ STEP 1: DC can be created for remaining quantity (supports multiple DCs for partial deliveries) -->
                    <div class="alert alert-primary alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-file-invoice"></i> <strong>Create Delivery Challan (DC):</strong> 
                        <br>Create a Delivery Challan for <strong>{{ $item->remaining_qty }}</strong> remaining units. You can create multiple DCs if delivering in batches.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @break

                @case('create_gatepass')
                    <!-- ✅ STEP 2: DC exists - Gate Pass can now be created -->
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-check-circle"></i> <strong>Step 2 - Create Gate Pass:</strong> 
                        <br>Delivery Challan (DC) has been created. Ready to create gate pass for physical delivery to customer.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @break

                @default
                    @if($gatePassRestriction)
                        <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> {{ $gatePassRestriction }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
            @endswitch

            <div class="d-flex gap-2 justify-content-between">
                <div>
                    <a href="{{ route('customer-remaining.index') }}"
                       class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <!-- ✅ ACTION 1: CREATE DC (if DC doesn't exist and item not completed) -->
                    @if($canCreateDC)
                        <a href="{{ route('customer-remaining.create-dc', $item->id) }}"
                           class="btn btn-primary btn-lg">
                            <i class="fas fa-file-invoice"></i> Create Delivery Challan
                        </a>
                    @elseif(!$canCreateGatePass && !$canCreateDC)
                        <!-- ✅ Disabled state for DC button if not allowed -->
                        <button type="button" class="btn btn-primary btn-lg" disabled 
                                title="{{ $gatePassRestriction ?? 'Cannot create DC at this time' }}">
                            <i class="fas fa-file-invoice"></i> Create Delivery Challan
                        </button>
                    @endif

                    <!-- ✅ ACTION 2: CREATE GATE PASS (only if DC exists) -->
                    @if($canCreateGatePass)
                        <a href="{{ route('OutwardGatepass.createFromRemaining', $item->id) }}"
                           class="btn btn-success btn-lg">
                            <i class="fas fa-truck"></i> Create Gate Pass
                        </a>
                    @elseif(!$canCreateGatePass && $dcExists && $item->remaining_qty > 0)
                        <!-- ✅ Disabled state for Gate Pass button -->
                        <button type="button" class="btn btn-success btn-lg" disabled 
                                title="{{ $gatePassRestriction ?? 'Cannot create gate pass at this time' }}">
                            <i class="fas fa-truck"></i> Create Gate Pass
                        </button>
                    @endif

                    <!-- Mark as Completed -->
                    <form action="{{ route('customer-remaining.markCompleted', $item->id) }}"
                          method="POST" style="display: inline;"
                          onsubmit="return confirm('Mark this item as completed?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fas fa-check"></i> Mark Completed
                        </button>
                    </form>

                    <!-- Delete -->
                    <form action="{{ route('customer-remaining.delete', $item->id) }}"
                          method="POST" style="display: inline;"
                          onsubmit="return confirm('Remove this pending item?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">Timeline</h6>
        </div>
        <div class="card-body">
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker bg-info"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Created</h6>
                        <p class="text-muted small mb-0">
                            {{ $item->created_at ? $item->created_at->format('d-m-Y H:i:s') : 'N/A' }}
                        </p>
                    </div>
                </div>
                @if($item->updated_at && $item->updated_at != $item->created_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Last Updated</h6>
                            <p class="text-muted small mb-0">
                                {{ $item->updated_at->format('d-m-Y H:i:s') }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }

    .timeline-item {
        display: flex;
        margin-bottom: 30px;
        position: relative;
    }

    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 15px;
        top: 45px;
        width: 2px;
        height: calc(100% + 30px);
        background-color: #e9ecef;
    }

    .timeline-marker {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-right: 20px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .timeline-content {
        flex-grow: 1;
    }
</style>
@endsection
