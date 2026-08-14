@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📦 Inter-Branch Stock Requests</h5>
                </div>
                <div class="card-body">
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" data-bs-toggle="tab" href="#incoming" role="tab">
                                📥 Incoming Requests (To Approve)
                                <span class="badge bg-danger ms-2">{{ $incomingRequests->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" data-bs-toggle="tab" href="#outgoing" role="tab">
                                📤 Outgoing Requests (Sent)
                                <span class="badge bg-info ms-2">{{ $outgoingRequests->count() }}</span>
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Incoming Requests -->
                        <div id="incoming" class="tab-pane fade show active">
                            @if ($incomingRequests->isEmpty())
                                <div class="alert alert-info">
                                    No incoming requests at the moment.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>From Branch</th>
                                                <th>Items</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($incomingRequests as $request)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $request->fromBranch->name ?? $request->fromBranch->branch_name ?? 'Branch #' . $request->from_branch_id }}</strong>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            @foreach ($request->items as $item)
                                                                {{ $item->product->item_name ?? 'Product #' . $item->product_id }} ({{ $item->requested_qty }}) <br>
                                                            @endforeach
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning text-dark">{{ ucfirst($request->status) }}</span>
                                                    </td>
                                                    <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                                                    <td>
                                                         <div class="d-flex gap-1">
                                                             @if ($request->status === 'pending')
                                                                 <a href="{{ route('inter_branch_stock_requests.show', $request) }}" class="btn btn-sm btn-primary">
                                                                     ✓ Review & Approve
                                                                 </a>
                                                             @endif
                                                             <button type="button" class="btn btn-sm btn-info text-white view-request-btn" data-id="{{ $request->id }}">
                                                                 <i class="fas fa-eye"></i> View
                                                             </button>
                                                         </div>
                                                     </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <!-- Outgoing Requests -->
                        <div id="outgoing" class="tab-pane fade">
                            @if ($outgoingRequests->isEmpty())
                                <div class="alert alert-info">
                                    No outgoing requests yet. <a href="{{ route('inter_branch_stock_requests.create') }}" class="alert-link">Create one now</a>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>To Branch</th>
                                                <th>Items</th>
                                                <th>Status</th>
                                                 <th>Approved By</th>
                                                 <th>Created</th>
                                                 <th>Action</th>
                                             </tr>
                                         </thead>
                                        <tbody>
                                            @foreach ($outgoingRequests as $request)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $request->toBranch->name ?? $request->toBranch->branch_name ?? 'Branch #' . $request->to_branch_id }}</strong>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            @foreach ($request->items as $item)
                                                                {{ $item->product->item_name ?? 'Product #' . $item->product_id }} ({{ $item->approved_qty ?? $item->requested_qty }}) <br>
                                                            @endforeach
                                                        </small>
                                                    </td>
                                                    <td>
                                                        @if ($request->status === 'pending')
                                                            <span class="badge bg-warning text-dark">Waiting</span>
                                                        @elseif ($request->status === 'approved')
                                                            <span class="badge bg-success">Approved</span>
                                                        @else
                                                            <span class="badge bg-danger">Rejected</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $request->approvedBy?->name ?? '-' }}
                                                    </td>
                                                     <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                                                     <td>
                                                         <button type="button" class="btn btn-sm btn-info text-white view-request-btn" data-id="{{ $request->id }}">
                                                             <i class="fas fa-eye"></i> View
                                                         </button>
                                                     </td>
                                                 </tr>
                                             @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create New Request Button -->
            <div class="mt-3">
                <a href="{{ route('inter_branch_stock_requests.create') }}" class="btn btn-success">
                    ➕ New Stock Request
                </a>
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" role="dialog" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewRequestModalLabel">📋 Stock Request Details</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="requestDetailsBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $('.view-request-btn').on('click', function() {
        const requestId = $(this).data('id');
        const $body = $('#requestDetailsBody');
        
        // Show loading spinner
        $body.html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        `);
        
        // Open modal
        $('#viewRequestModal').modal('show');
        
        // Fetch details via AJAX
        $.ajax({
            url: `/inter-branch/stock-requests/${requestId}/details`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const req = response.request;
                    
                    let itemsHtml = '';
                    let tableHeadersHtml = '';
                    
                    if (req.status === 'approved') {
                        // Approved Headers
                        tableHeadersHtml = `
                            <tr>
                                <th>#</th>
                                <th>Product Details</th>
                                <th class="text-center">Req Qty</th>
                                <th class="text-center">App Qty</th>
                                <th>Src Warehouse</th>
                                <th>Dest Warehouse</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total Amount</th>
                            </tr>
                        `;
                        
                        req.items.forEach(function(item, index) {
                            itemsHtml += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td><strong>${item.product_name}</strong><br><small class="text-muted">Code: ${item.product_code}</small></td>
                                    <td class="text-center">${item.requested_qty}</td>
                                    <td class="text-center">${item.approved_qty}</td>
                                    <td>${item.from_warehouse}</td>
                                    <td>${item.to_warehouse}</td>
                                    <td class="text-end">${item.unit_price}</td>
                                    <td class="text-end font-weight-bold text-success">${item.total_price}</td>
                                </tr>
                            `;
                        });
                    } else {
                        // Pending or Rejected Headers (Hide approved details)
                        tableHeadersHtml = `
                            <tr>
                                <th>#</th>
                                <th>Product Details</th>
                                <th class="text-center">Requested Qty</th>
                            </tr>
                        `;
                        
                        req.items.forEach(function(item, index) {
                            itemsHtml += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td><strong>${item.product_name}</strong><br><small class="text-muted">Code: ${item.product_code}</small></td>
                                    <td class="text-center">${item.requested_qty}</td>
                                </tr>
                            `;
                        });
                    }

                    let statusClass = 'bg-warning text-dark';
                    if (req.status === 'approved') statusClass = 'bg-success text-white';
                    if (req.status === 'rejected') statusClass = 'bg-danger text-white';

                    // Processed details block (only show if approved/rejected)
                    let processedHtml = '';
                    if (req.status !== 'pending') {
                        processedHtml = `
                            <tr>
                                <td><strong>Processed By:</strong></td>
                                <td>${req.approved_by}</td>
                            </tr>
                            <tr>
                                <td><strong>Processed At:</strong></td>
                                <td>${req.approved_at}</td>
                            </tr>
                        `;
                    }

                    let detailsHtml = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>Request ID:</strong></td>
                                        <td>#${req.id}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>From Branch:</strong></td>
                                        <td>${req.from_branch}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>To Branch:</strong></td>
                                        <td>${req.to_branch}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td><span class="badge ${statusClass}">${req.status.toUpperCase()}</span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>Created At:</strong></td>
                                        <td>${req.created_at}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Created By:</strong></td>
                                        <td>${req.created_by}</td>
                                    </tr>
                                    ${processedHtml}
                                </table>
                            </div>
                        </div>

                        <div class="mb-3">
                            <strong>Remarks:</strong>
                            <p class="bg-light p-2 rounded small">${req.remarks}</p>
                        </div>

                        <h6 class="mt-4 mb-2">📦 Requested Products</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-striped">
                                <thead class="table-light">
                                    ${tableHeadersHtml}
                                </thead>
                                <tbody>
                                    ${itemsHtml}
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    $body.html(detailsHtml);
                } else {
                    $body.html('<div class="alert alert-danger">Error loading request details.</div>');
                }
            },
            error: function(error) {
                console.error(error);
                $body.html('<div class="alert alert-danger">Failed to fetch request details.</div>');
            }
        });
    });
});
</script>
@endsection
