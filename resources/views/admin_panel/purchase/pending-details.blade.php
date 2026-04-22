@extends('admin_panel.layout.app')

@section('content')
@can('purchase.view')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0">Purchase Details - {{ $purchase->formatted_invoice }}</h3>
                    <small class="text-muted">Status: 
                        @if($purchase->receipt_status === 'pending')
                            <span class="badge bg-warning text-dark">🟡 Awaiting Receipt</span>
                        @elseif($purchase->receipt_status === 'partial')
                            <span class="badge bg-info">🔵 Partial Received</span>
                        @else
                            <span class="badge bg-success">🟢 Received</span>
                        @endif
                    </small>
                </div>
                <a href="{{ route('Purchase.home') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Purchases
                </a>
            </div>

            <!-- Main Content Row -->
            <div class="row">
                <!-- Left: Purchase Header Info -->
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">📋 Purchase Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">Invoice Number</label>
                                    <p class="fw-bold">{{ $purchase->formatted_invoice }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Purchase Date</label>
                                    <p class="fw-bold">{{ $purchase->purchase_date->format('d-M-Y') }}</p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">Vendor</label>
                                    <p class="fw-bold">{{ $purchase->vendor->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Branch</label>
                                    <p class="fw-bold">{{ $purchase->branch->name ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="text-muted small">Warehouse (Destination)</label>
                                    <p class="fw-bold">{{ $purchase->warehouse->warehouse_name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small">Note</label>
                                    <p>{{ $purchase->note ?? '-' }}</p>
                                </div>
                            </div>

                            <hr>

                            <!-- Financial Summary -->
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="text-muted small">Subtotal</label>
                                    <p class="fw-bold">{{ number_format($purchase->subtotal, 2) }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted small">Discount</label>
                                    <p class="fw-bold text-danger">- {{ number_format($purchase->discount, 2) }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted small">Extra Cost</label>
                                    <p class="fw-bold text-info">+ {{ number_format($purchase->extra_cost, 2) }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted small">Net Amount</label>
                                    <p class="fw-bold text-success fs-5">{{ number_format($purchase->net_amount, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">📦 Ordered Products</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item Code</th>
                                            <th>Product Name</th>
                                            <th class="text-end">Ordered Qty</th>
                                            <th class="text-end">Received Qty</th>
                                            <th class="text-end">Remaining</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($purchase->items as $item)
                                            @php
                                                // Get vendor remaining for this product
                                                $vendorRemaining = $vendorRemainingMap[$item->product_id] ?? null;
                                                $receivedQty = $vendorRemaining->received_qty ?? 0;
                                                $remainingQty = $vendorRemaining->remaining_qty ?? $item->qty;
                                                $status = $vendorRemaining->status ?? 'pending';
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $item->product->item_code ?? 'N/A' }}</strong></td>
                                                <td>
                                                    {{ $item->product->item_name ?? 'N/A' }}
                                                    <br>
                                                    <small class="text-muted">{{ $item->unit }}</small>
                                                </td>
                                                <td class="text-end"><span class="badge bg-primary">{{ $item->qty }}</span></td>
                                                <td class="text-end text-success fw-bold">{{ $receivedQty }}</td>
                                                <td class="text-end">
                                                    @if($remainingQty > 0)
                                                        <span class="badge bg-warning text-dark">{{ $remainingQty }}</span>
                                                    @else
                                                        <span class="badge bg-success">0</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($status === 'pending')
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    @elseif($status === 'partial')
                                                        <span class="badge bg-info">Partial</span>
                                                    @else
                                                        <span class="badge bg-success">Completed</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No products in this purchase</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Actions & Status -->
                <div class="col-lg-4">
                    <!-- Status Card -->
                    <div class="card shadow-sm mb-4 border-left-4" style="border-left: 4px solid #ffc107;">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">⚡ Status Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small d-block">Current Receipt Status</label>
                                @if($purchase->receipt_status === 'pending')
                                    <div class="alert alert-warning">
                                        <strong>🟡 Awaiting Receipt</strong>
                                        <p class="mb-0 small mt-2">No goods have been received yet. Click the "Receive Products" button to begin.</p>
                                    </div>
                                @elseif($purchase->receipt_status === 'partial')
                                    <div class="alert alert-info">
                                        <strong>🔵 Partial Received</strong>
                                        <p class="mb-0 small mt-2">Some products have been received. More deliveries expected.</p>
                                    </div>
                                @else
                                    <div class="alert alert-success">
                                        <strong>🟢 Fully Received</strong>
                                        <p class="mb-0 small mt-2">All products have been received successfully.</p>
                                    </div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="text-muted small">Inward Gatepasses Created</label>
                                <p class="fw-bold fs-5">{{ $purchase->inwardGatepasses->count() }}</p>
                            </div>

                            @if($remainingTotal > 0)
                                <div class="mb-3">
                                    <label class="text-muted small">Products Awaiting Receipt</label>
                                    <p class="fw-bold fs-5 text-warning">{{ $remainingTotal }} units</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">📝 Actions</h5>
                        </div>
                        <div class="card-body">
                            @if($purchase->receipt_status !== 'received')
                                <!-- Main: Receive Products Button -->
                                <a href="{{ route('inward-gatepass.from-purchase', $purchase->id) }}" 
                                   class="btn btn-success w-100 mb-3">
                                    <i class="fa fa-inbox"></i> Receive Products
                                </a>
                                <small class="text-muted d-block mb-3">
                                    Click to create Inward Gatepass. You can receive partial quantities if needed.
                                </small>
                            @endif

                            <!-- View Inward Gatepasses -->
                            @if($purchase->inwardGatepasses->count() > 0)
                                <div class="mb-3">
                                    <label class="text-muted small d-block mb-2">Previous Receipts</label>
                                    <div class="list-group list-group-sm">
                                        @foreach($purchase->inwardGatepasses as $gatepass)
                                            <a href="{{ route('InwardGatepass.show', $gatepass->id) }}" 
                                               class="list-group-item list-group-item-action">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong>Inward GP #{{ $gatepass->id }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $gatepass->gatepass_date->format('d-M-Y') }}</small>
                                                    </div>
                                                    <span class="badge bg-info">{{ $gatepass->items->sum('qty') }} units</span>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Edit Purchase -->
                            @can('purchase.edit')
                                <a href="{{ route('purchase.edit', $purchase->id) }}" 
                                   class="btn btn-outline-primary w-100 btn-sm">
                                    <i class="fa fa-edit"></i> Edit Purchase
                                </a>
                            @endcan
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">ℹ️ Instructions</h5>
                        </div>
                        <div class="card-body">
                            <ol class="small mb-0">
                                <li>Review the ordered products above</li>
                                <li>Click <strong>"Receive Products"</strong> button</li>
                                <li>In the form, you can:
                                    <ul>
                                        <li>✅ ONLY decrease received qty (due to less items)</li>
                                        <li>❌ CANNOT increase qty beyond ordered</li>
                                    </ul>
                                </li>
                                <li>Submit to create Inward Gatepass</li>
                                <li>Stock updates in warehouse</li>
                                <li>Remaining items tracked automatically</li>
                                <li>For multiple shipments, create multiple Inward Gatepasses</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection
