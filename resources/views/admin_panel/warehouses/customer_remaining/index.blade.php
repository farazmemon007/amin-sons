@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid px-4 py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-truck-loading text-primary me-2"></i>Pending Deliveries</h4>
            <nav aria-label="breadcrumb">
                <small class="text-muted">Manage and dispatch customer orders awaiting delivery</small>
            </nav>
        </div>
        <a href="{{ route('OutwardGatepass.home') }}" class="btn btn-white border shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Gate Passes
        </a>
    </div>

    <div class="row mb-4">
        @foreach([
            ['Pending Items', $stats['totalPending'] ?? 0, 'text-warning', 'fa-hourglass-half'],
            ['Awaiting Customers', $stats['totalCustomers'] ?? 0, 'text-info', 'fa-users'],
            ['Partial Sales', $stats['totalSales'] ?? 0, 'text-primary', 'fa-file-invoice'],
            ['Total Qty Pending', number_format($stats['totalQtyPending'] ?? 0, 0), 'text-success', 'fa-boxes']
        ] as $stat)
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas {{ $stat[3] }} fa-2x {{ $stat[2] }} opacity-50"></i>
                    </div>
                    <div class="ms-3">
                        <p class="text-muted mb-0 small">{{ $stat[0] }}</p>
                        <h5 class="fw-bold mb-0">{{ $stat[1] }}</h5>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="mb-0 fw-bold">Order Queue</h6>
        </div>
        <div class="card-body p-0">
            @if($pendingDeliveries->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Item Code</th>
                                <th>Unit</th>
                                <th>Remaining Qty</th>
                                <th>Status</th>
                                <th>Delivery Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingDeliveries as $item)
                            <tr>
                                <td class="ps-4 text-muted">#{{ $item->id }}</td>
                                <td>
                                    <div class="fw-bold">{{ $item->customer?->customer_name ?? ($item->sub_customer_name ?? 'Walking Customer') }}</div>
                                    @if(!$item->customer_id)<span class="badge bg-light text-warning border">Walking</span>@endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item->product_name ?: $item->product?->item_name }}</span></td>
                                <td><code>{{ $item->item_code ?: $item->product?->item_code }}</code></td>
                                <td>{{ $item->unit ?: $item->product?->unit?->name }}</td>
                                <td><span class="text-danger fw-bold">{{ number_format($item->remaining_qty, 2) }}</span></td>
                                <td>
                                    @php $status = $item->status ?? 'pending'; @endphp
                                    <span class="badge rounded-pill bg-{{ $status == 'partial' ? 'info' : 'warning' }}-subtle text-{{ $status == 'partial' ? 'info' : 'warning' }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $dcId = $dcsByProduct["{$item->sale_id}_{$item->product_id}"] ?? null;
                                        $gatepassId = $dcId ? ($gatepassByDC[$dcId] ?? null) : null;
                                    @endphp
                                    
                                    @if($gatepassId)
                                        <span class="badge bg-success-subtle text-success border border-success">
                                            <i class="fas fa-check-circle"></i> Gatepass Created
                                        </span>
                                    @elseif($dcId)
                                        <span class="badge bg-info-subtle text-info border border-info">
                                            <i class="fas fa-file-invoice-dollar"></i> DC Created
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning">
                                            <i class="fas fa-hourglass-half"></i> Awaiting DC
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @php
                                        // Get DC ID for this item
                                        $dcId = $dcsByProduct["{$item->sale_id}_{$item->product_id}"] ?? null;
                                        $gatepassId = $dcId ? ($gatepassByDC[$dcId] ?? null) : null;
                                    @endphp
                                    
                                    <div class="d-flex gap-2 align-items-center justify-content-end">
                                        <!-- View Details -->
                                        <a href="{{ route('customer-remaining.show', $item->id) }}" class="btn btn-sm btn-light" title="View Details">
                                            <i class="fas fa-eye text-primary"></i>
                                        </a>
                                        
                                        <!-- If NO DC exists: Show Create DC button -->
                                        @if(!$dcId && ($item->status == 'pending' || $item->status == 'partial') && $item->remaining_qty > 0)
                                            <a href="{{ route('customer-remaining.create-dc', $item->id) }}" 
                                               class="btn btn-sm btn-warning" title="Create Delivery Challan">
                                                <i class="fas fa-file-invoice-dollar"></i> <span class="d-none d-lg-inline">DC</span>
                                            </a>
                                        @endif
                                        
                                        <!-- If DC exists but NO gatepass: Show Create Gatepass button -->
                                        @if($dcId && !$gatepassId && ($item->status == 'pending' || $item->status == 'partial') && $item->remaining_qty > 0)
                                            <a href="{{ route('OutwardGatepass.createFromRemaining', $item->id) }}" 
                                               class="btn btn-sm btn-success" title="Create Gate Pass">
                                                <i class="fas fa-truck"></i> <span class="d-none d-lg-inline">GP</span>
                                            </a>
                                        @endif
                                        
                                        <!-- If gatepass exists: Show View Gatepass button -->
                                        @if($gatepassId)
                                            <a href="{{ route('OutwardGatepass.show', $gatepassId) }}" 
                                               class="btn btn-sm btn-info" title="View Gate Pass">
                                                <i class="fas fa-check-circle text-success"></i> <span class="d-none d-lg-inline">View GP</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $pendingDeliveries->links() }}</div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-3x text-light mb-3"></i>
                    <p class="text-muted">No pending deliveries found.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection