@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="mb-0">
                <i class="fas fa-clipboard-list text-info"></i> Create Gate Pass from DC
            </h3>
            <small class="text-muted">Select a delivery challan to create gate pass</small>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('OutwardGatepass.home') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Gate Passes
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Available Delivery Challans</h6>
            <span class="badge bg-primary">{{ $deliveryChallans->total() }} Ready for Gate Pass</span>
        </div>
        <div class="card-body">
            @if($deliveryChallans->count())
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="8%">DC ID</th>
                                <th width="12%">DC No</th>
                                <th width="18%">Customer</th>
                                <th width="12%">Warehouse</th>
                                <th width="15%">Items</th>
                                <th width="15%">Date Created</th>
                                <th width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deliveryChallans as $dc)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $dc->id }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ $dc->dc_no }}</strong>
                                    </td>
                                    <td>
                                        <span title="{{ optional($dc->customer)->customer_name ?? 'N/A' }}">
                                            {{ Str::limit(optional($dc->customer)->customer_name ?? 'N/A', 22) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ optional($dc->customer)->contact_person ?? '' }}
                                        </small>
                                    </td>
                                    <td>
                                        {{ optional($dc->warehouse)->warehouse_name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        @php
                                            $itemCount = $dc->saleItems ? $dc->saleItems()->count() : 0;
                                        @endphp
                                        <span class="badge bg-info">{{ $itemCount }} Items</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $dc->created_at ? $dc->created_at->format('d-m-Y H:i') : 'N/A' }}
                                        </small>
                                    </td>
                                    <td>
                                        <a href="{{ route('outward_gatepass.create', $dc->id) }}" 
                                           class="btn btn-sm btn-success"
                                           title="Create Gate Pass for this DC">
                                            <i class="fas fa-plus-circle"></i> Create Gate Pass
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav class="d-flex justify-content-center mt-4">
                    {{ $deliveryChallans->links() }}
                </nav>
            @else
                <div class="alert alert-info text-center" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <strong>No pending delivery challans!</strong>
                    <p class="mb-0 small mt-2">All DCs either have gate passes or are being prepared.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h5 class="mb-0 text-success">{{ $stats['totalDCs'] ?? 0 }}</h5>
                    <small class="text-muted">Delivery Challans Ready</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h5 class="mb-0 text-primary">{{ $stats['totalCustomers'] ?? 0 }}</h5>
                    <small class="text-muted">Unique Customers</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h5 class="mb-0 text-info">{{ $stats['totalItems'] ?? 0 }}</h5>
                    <small class="text-muted">Total Items Ready</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
