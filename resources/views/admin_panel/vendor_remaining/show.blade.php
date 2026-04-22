@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-md-8">
            <h4 class="mb-1">
                <i class="fa fa-tasks text-warning"></i> Pending Delivery Details
            </h4>
            <small class="text-muted">Monitor and manage this pending item</small>
        </div>
        
        <!-- Status Badge -->
        <div class="col-md-4 text-end">
            @if($remaining->status === 'pending')
                <span class="badge bg-warning text-dark" style="font-size: 1.1em;">
                    <i class="fa fa-hourglass-half"></i> Pending
                </span>
            @elseif($remaining->status === 'partial')
                <span class="badge bg-info" style="font-size: 1.1em;">
                    <i class="fa fa-hourglass-start"></i> Partial
                </span>
            @else
                <span class="badge bg-success" style="font-size: 1.1em;">
                    <i class="fa fa-check-circle"></i> Completed
                </span>
            @endif
        </div>
    </div>

    <!-- Back Buttons -->
    <div class="mb-3">
        <a href="{{ route('vendor-remaining.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to Pending Items
        </a>
        @can('purchase.view')
            <a href="{{ route('Purchase.show', $remaining->purchase_id) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa fa-file-text"></i> View Purchase
            </a>
        @endcan
    </div>

    <div class="row">
        <!-- Left: Item Details -->
        <div class="col-md-8 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fa fa-info-circle"></i> Item Information
                    </h6>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Purchase Order</label>
                            <div class="alert alert-light border mb-0">
                                <strong>#{{ $remaining->purchase_id }}</strong>
                                @if($remaining->purchase?->purchase_date)
                                    <br>
                                    <small>{{ \Carbon\Carbon::parse($remaining->purchase->purchase_date)->format('d-M-Y') }}</small>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Vendor</label>
                            <div class="alert alert-light border mb-0">
                                <strong>{{ $remaining->vendor?->name ?? 'N/A' }}</strong>
                                @if($remaining->vendor?->phone)
                                    <br>
                                    <small>{{ $remaining->vendor->phone }}</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label text-muted small">Product</label>
                            <div class="alert alert-light border mb-0">
                                <strong>{{ $remaining->product?->name ?? $remaining->product?->item_name ?? 'Product #' . $remaining->product_id }}</strong>
                                @if($remaining->product?->code)
                                    <br>
                                    <small>Code: {{ $remaining->product->code }}</small>
                                @endif
                                @if($remaining->product?->brand)
                                    <br>
                                    <small>Brand: {{ $remaining->product->brand->name ?? 'N/A' }}</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Warehouse</label>
                            <div class="alert alert-light border mb-0">
                                {{ $remaining->warehouse?->warehouse_name ?? 'Default' }}
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Updated</label>
                            <div class="alert alert-light border mb-0">
                                <small>{{ $remaining->updated_at?->format('d-M-Y H:i') ?? 'N/A' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Quantities & Progression -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fa fa-chart-line"></i> Delivery Progress
                    </h6>
                </div>
                
                <div class="card-body">
                    <!-- Ordered Qty -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <label class="text-muted small">Ordered Qty</label>
                            <strong class="text-dark">{{ $remaining->ordered_qty }}</strong>
                        </div>
                        <div class="progress bg-light" style="height: 8px;">
                            <div class="progress-bar bg-secondary" style="width: 100%;"></div>
                        </div>
                    </div>

                    <!-- Received Qty (Progress) -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <label class="text-muted small">Received Qty</label>
                            <strong class="text-info">{{ $remaining->received_qty }}</strong>
                        </div>
                        @php
                            $percentage = ($remaining->received_qty / $remaining->ordered_qty) * 100;
                        @endphp
                        <div class="progress bg-light" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{ $percentage }}%;"></div>
                        </div>
                        <small class="text-muted">{{ round($percentage, 1) }}% delivered</small>
                    </div>

                    <!-- Remaining Qty (Critical) -->
                    <div class="mb-3 p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded">
                        <div class="d-flex justify-content-between mb-2">
                            <label class="text-muted small">Still Pending</label>
                            <strong class="text-danger" style="font-size: 1.3em;">{{ $remaining->remaining_qty }}</strong>
                        </div>
                        <small class="text-muted">
                            Awaiting {{ $remaining->remaining_qty }} units from vendor
                        </small>
                    </div>

                    <!-- Status Timeline -->
                    <div class="mt-4">
                        <label class="text-muted small mb-2">Delivery Status</label>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker {{ $remaining->received_qty > 0 ? 'bg-success' : 'bg-secondary' }}"></div>
                                <div class="timeline-content">
                                    <small>First Receipt</small>
                                    <br>
                                    {{ $remaining->received_qty > 0 ? '✓ Received' : '⏳ Awaiting' }}
                                </div>
                            </div>
                            
                            <div class="timeline-item">
                                <div class="timeline-marker {{ $remaining->remaining_qty === 0 ? 'bg-success' : 'bg-secondary' }}"></div>
                                <div class="timeline-content">
                                    <small>Final Receipt</small>
                                    <br>
                                    {{ $remaining->remaining_qty === 0 ? '✓ Completed' : '⏳ Awaiting' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0">
                <i class="fa fa-tasks"></i> Actions
            </h6>
        </div>
        
        <div class="card-body">
            <div class="row gap-2">
                <!-- Receive More Items Button -->
                @can('inward.gatepass.create')
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('inward-gatepass.from-purchase', $remaining->purchase_id) }}" 
                           class="btn btn-success btn-block w-100">
                            <i class="fa fa-plus-circle"></i> Receive Remaining Items
                        </a>
                        <small class="text-muted d-block mt-1">Create inward gatepass for remaining qty</small>
                    </div>
                @endcan

                <!-- Mark Completed -->
                @can('purchase.edit')
                    <div class="col-md-3 mb-2">
                        <form action="{{ route('vendor-remaining.mark-completed', $remaining->id) }}" 
                              method="POST" style="display: inline-block; width: 100%;"
                              onsubmit="return confirm('Mark this item as completed?');">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-block w-100">
                                <i class="fa fa-check-circle"></i> Mark as Completed
                            </button>
                        </form>
                        <small class="text-muted d-block mt-1">Set remaining qty to 0</small>
                    </div>
                @endcan

                <!-- Delete Button -->
                @can('purchase.delete')
                    <div class="col-md-3 mb-2">
                        <form action="{{ route('vendor-remaining.delete', $remaining->id) }}" 
                              method="POST" style="display: inline-block; width: 100%;"
                              onsubmit="return confirm('Delete this pending item?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block w-100">
                                <i class="fa fa-trash"></i> Delete Item
                            </button>
                        </form>
                        <small class="text-muted d-block mt-1">Remove from pending list</small>
                    </div>
                @endcan

                <!-- View Purchase -->
                @can('purchase.view')
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('Purchase.show', $remaining->purchase_id) }}" 
                           class="btn btn-info btn-block w-100">
                            <i class="fa fa-file-text"></i> View Purchase
                        </a>
                        <small class="text-muted d-block mt-1">See full purchase details</small>
                    </div>
                @endcan
            </div>
        </div>
    </div>

</div>

<style>
    .timeline-item {
        display: flex;
        margin-bottom: 1.5rem;
    }

    .timeline-marker {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        margin-right: 12px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .timeline-content small {
        font-size: 0.85rem;
        color: #999;
    }

    .btn-block {
        display: block;
        width: 100%;
    }
</style>
@endsection
