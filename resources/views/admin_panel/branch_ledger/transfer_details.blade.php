@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid">
    <!-- Authorization Check Alert -->
    @if (!auth()->user()->hasRole('super admin') && auth()->user()->branch_id == $branch->id)
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-shield-alt"></i>
            <strong>Your Branch Transfers:</strong> You are viewing stock transfers for your branch.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif (auth()->user()->hasRole('super admin'))
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-crown"></i>
            <strong>Super Admin Access:</strong> You can view all branch stock transfers.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header with Back Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4>
                <i class="fas fa-exchange-alt"></i> Stock Transfer Details - {{ $branch->name ?? $branch->branch_name ?? 'Branch #' . $branch->id }}
            </h4>
            <small class="text-muted">View all inter-branch stock transfers with date filtering</small>
        </div>
        <a href="{{ route('branch_ledger_all_branches') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Branches
        </a>
    </div>

    <!-- Date Range Filter Form -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-filter"></i> Filter by Date Range
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('branch_ledger_transfer_details', $branch->id) }}" class="row g-3">
                <div class="col-md-3">
                    <label for="from_date" class="form-label">From Date</label>
                    <input 
                        type="date" 
                        id="from_date" 
                        name="from_date" 
                        class="form-control"
                        value="{{ request('from_date') }}">
                </div>

                <div class="col-md-3">
                    <label for="to_date" class="form-label">To Date</label>
                    <input 
                        type="date" 
                        id="to_date" 
                        name="to_date" 
                        class="form-control"
                        value="{{ request('to_date') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Apply Filter
                    </button>
                </div>

                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <a href="{{ route('branch_ledger_transfer_details', $branch->id) }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Transfers</h6>
                    <h3 class="text-primary">{{ $transfers->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Quantity</h6>
                    <h3 class="text-secondary">{{ number_format($totalQuantity, 0) }} Units</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Value</h6>
                    <h3 class="text-success">{{ number_format($totalValue, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfers Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0">Transfer Transactions</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Direction</th>
                        <th>From Branch</th>
                        <th>To Branch</th>
                        <th>Product Name</th>
                        <th class="text-center">Quantity</th>
                        <th>Unit Price</th>
                        <th class="text-end">Total Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transfers as $transfer)
                        <tr>
                            <td>
                                <small class="text-muted">
                                    {{ $transfer->created_at->format('M d, Y H:i') }}
                                </small>
                            </td>
                            <td>
                                @if ($transfer->from_branch_id == $branch->id)
                                    <span class="badge bg-warning">
                                        <i class="fas fa-arrow-right"></i> Outgoing
                                    </span>
                                @else
                                    <span class="badge bg-info">
                                        <i class="fas fa-arrow-left"></i> Incoming
                                    </span>
                                @endif
                            </td>
                            <td>
                                <strong>
                                    {{ $transfer->fromBranch->name ?? $transfer->fromBranch->branch_name ?? 'Branch #' . $transfer->from_branch_id }}
                                </strong>
                            </td>
                            <td>
                                <strong>
                                    {{ $transfer->toBranch->name ?? $transfer->toBranch->branch_name ?? 'Branch #' . $transfer->to_branch_id }}
                                </strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $transfer->product->item_name ?? 'Product #' . $transfer->product_id }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $transfer->fromWarehouse->warehouse_name ?? 'WH' }} 
                                        → 
                                        {{ $transfer->toWarehouse->warehouse_name ?? 'WH' }}
                                    </small>
                                </div>
                            </td>
                            <td class="text-center">
                                <strong>{{ number_format($transfer->quantity, 0) }}</strong>
                            </td>
                            <td>
                                {{ number_format($transfer->product->price ?? 0, 2) }}
                            </td>
                            <td class="text-end">
                                <strong class="text-success">
                                    {{ number_format($transfer->quantity * ($transfer->product->price ?? 0), 2) }}
                                </strong>
                            </td>
                            <td>
                                @if ($transfer->status === 'approved')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Approved
                                    </span>
                                @elseif ($transfer->status === 'pending')
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        {{ ucfirst($transfer->status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-inbox text-muted" style="font-size: 2em;"></i>
                                <p class="text-muted mt-2">
                                    @if (request('from_date') || request('to_date'))
                                        No transfers found for the selected date range.
                                    @else
                                        No transfers found for this branch.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div class="card-footer bg-light">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="text-muted mb-2">Total Quantity:</h6>
                    <h5 class="text-secondary">{{ number_format($totalQuantity, 0) }} Units</h5>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted mb-2">Average Unit Price:</h6>
                    @php
                        $avgPrice = $totalQuantity > 0 ? $totalValue / $totalQuantity : 0;
                    @endphp
                    <h5 class="text-secondary">{{ number_format($avgPrice, 2) }}</h5>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted mb-2">Total Transfer Value:</h6>
                    <h5 class="text-success font-weight-bold">{{ number_format($totalValue, 2) }}</h5>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if ($transfers->total() > 0)
            <div class="card-footer bg-white border-top">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>

</div>

<style>
    .table-hover tbody tr:hover {
        background-color: #f5f5f5 !important;
    }
</style>
@endsection
