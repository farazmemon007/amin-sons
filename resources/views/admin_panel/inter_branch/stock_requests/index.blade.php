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
                            <a class="nav-link active" data-bs-toggle="tab" href="#incoming" role="tab">
                                📥 Incoming Requests (To Approve)
                                <span class="badge bg-danger ms-2">{{ $incomingRequests->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#outgoing" role="tab">
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
                                                        @if ($request->status === 'pending')
                                                            <a href="{{ route('inter_branch_stock_requests.show', $request) }}" class="btn btn-sm btn-primary">
                                                                ✓ Review & Approve
                                                            </a>
                                                        @else
                                                            <span class="text-muted">{{ ucfirst($request->status) }}</span>
                                                        @endif
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
@endsection
