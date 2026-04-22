@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid py-3">
    <!-- Header Section -->
    <div class="row mb-3">
        <div class="col-md-8">
            <h4 class="mb-1">
                <i class="fa fa-hourglass-half text-warning"></i> Pending Vendor Deliveries
            </h4>
            <small class="text-muted">
                Track and manage items awaiting delivery from vendors
            </small>
        </div>
        
        <!-- Summary Stats -->
        <div class="col-md-4">
            <div class="row text-center">
                <div class="col">
                    <div class="bg-light p-2 rounded">
                        <h6 class="mb-0 text-warning">{{ $totalRemaining }}</h6>
                        <small class="text-muted">Units Pending</small>
                    </div>
                </div>
                <div class="col">
                    <div class="bg-light p-2 rounded">
                        <h6 class="mb-0 text-info">{{ $totalVendors }}</h6>
                        <small class="text-muted">Vendors</small>
                    </div>
                </div>
                <div class="col">
                    <div class="bg-light p-2 rounded">
                        <h6 class="mb-0 text-primary">{{ $totalPurchases }}</h6>
                        <small class="text-muted">Purchases</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('Purchase.home') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to Purchases
        </a>
    </div>

    <!-- No Data Message -->
    @if($remainingItems->count() === 0)
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle"></i> 
            <strong>All Clear!</strong> No pending vendor deliveries at the moment.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @else
        <!-- Pending Items Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h6 class="mb-0">
                    <i class="fa fa-list"></i> Pending Items ({{ $remainingItems->total() }})
                </h6>
            </div>
            
            <div class="card-body table-responsive p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;"></th>
                            <th>Purchase</th>
                            <th>Vendor</th>
                            <th>Product</th>
                            <th class="text-center" style="width: 12%;">Ordered</th>
                            <th class="text-center" style="width: 12%;">Received</th>
                            <th class="text-center" style="width: 12%; background: #fff3cd;">Remaining</th>
                            <th class="text-center" style="width: 10%;">Status</th>
                            <th class="text-center" style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($remainingItems as $item)
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-warning">{{ $loop->index + 1 }}</span>
                                </td>
                                
                                <!-- Purchase -->
                                <td>
                                    <strong>#{{ $item->purchase_id }}</strong>
                                    @if($item->purchase?->purchase_date)
                                        <br>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($item->purchase->purchase_date)->format('d-M-Y') }}
                                        </small>
                                    @endif
                                </td>
                                
                                <!-- Vendor -->
                                <td>
                                    {{ $item->vendor?->name ?? 'N/A' }}
                                </td>
                                
                                <!-- Product -->
                                <td>
                                    {{ $item->product?->name ?? $item->product?->item_name ?? 'Product #' . $item->product_id }}
                                    @if($item->product?->code)
                                        <br>
                                        <small class="text-muted">{{ $item->product->code }}</small>
                                    @endif
                                </td>
                                
                                <!-- Ordered Qty -->
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $item->ordered_qty }}</span>
                                </td>
                                
                                <!-- Received Qty -->
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $item->received_qty }}</span>
                                </td>
                                
                                <!-- Remaining Qty (highlight) -->
                                <td class="text-center" style="background: #fff3cd;">
                                    <strong class="text-danger" style="font-size: 1.1em;">{{ $item->remaining_qty }}</strong>
                                </td>
                                
                                <!-- Status -->
                                <td class="text-center">
                                    @if($item->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($item->status === 'partial')
                                        <span class="badge bg-info">Partial</span>
                                    @else
                                        <span class="badge bg-success">Completed</span>
                                    @endif
                                </td>
                                
                                <!-- Actions -->
                                <td class="text-center">
                                    <!-- View Button -->
                                    @can('purchase.view')
                                        <a href="{{ route('vendor-remaining.show', $item->id) }}" 
                                           class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" 
                                           title="View Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endcan
                                    
                                    <!-- Create Gatepass Button -->
                                    @can('inward.gatepass.create')
                                        <a href="{{ route('inward-gatepass.from-purchase', $item->purchase_id) }}" 
                                           class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" 
                                           title="Receive More Items">
                                            <i class="fa fa-plus"></i>
                                        </a>
                                    @endcan
                                    
                                    <!-- Mark Completed (if admin) -->
                                    @can('purchase.edit')
                                        <form action="{{ route('vendor-remaining.mark-completed', $item->id) }}" 
                                              method="POST" style="display: inline;"
                                              onsubmit="return confirm('Mark as completed?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                    data-bs-toggle="tooltip" title="Mark Completed">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        </form>
                                    @endcan
                                    
                                    <!-- Delete Button (if admin) -->
                                    @can('purchase.delete')
                                        <form action="{{ route('vendor-remaining.delete', $item->id) }}" 
                                              method="POST" style="display: inline;"
                                              onsubmit="return confirm('Remove this pending item?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="tooltip" title="Remove">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-3 d-flex justify-content-center">
            {{ $remainingItems->links() }}
        </div>
    @endif

</div>

<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endsection
