@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="mb-0">
                <i class="fas fa-box-open text-info"></i> Delivery Challans (DC)
            </h3>
            <small class="text-muted">Click on any DC to create or edit gate pass</small>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('OutwardGatepass.list') }}" class="btn btn-info btn-sm" title="View all gate passes">
                <i class="fa fa-receipt"></i> Gate Pass
            </a>
            <span class="badge bg-info">{{ $deliveryChallans->total() }} Total DCs</span>
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
        <div class="card-body">
            @if($deliveryChallans->count())
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th width="8%">#</th>
                                <th width="12%">DC No</th>
                                <th width="18%">Customer</th>
                                <th width="15%">Warehouse</th>
                                <th width="10%">Items</th>
                                <th width="12%">Gate Pass</th>
                                <th width="15%">Created Date</th>
                                <th width="20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deliveryChallans as $dc)
                                <tr style="cursor: pointer;" onclick="window.location='{{ route('outward_gatepass.create', $dc->id) }}'">
                                    <td>
                                        <span class="badge bg-secondary">{{ $dc->id }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ $dc->dc_no ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <span title="{{ $dc->display_customer_name ?? 'N/A' }}">
                                            {{ Str::limit($dc->display_customer_name ?? 'N/A', 20) }}
                                        </span>
                                        @if($dc->is_walking_customer)
                                            <br><small class="badge bg-warning text-dark">Walking Customer</small>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $dc->contact_person ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span title="{{ $dc->location_name ?? 'N/A' }}">
                                            <strong>{{ $dc->location_name ?? 'N/A' }}</strong>
                                        </span>
                                        <br>
                                        <small class="badge bg-secondary">{{ $dc->location_type ?? 'Unknown' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $dc->items_count ?? 0 }} Items</span>
                                    </td>
                                    <td>
                                        @if($dc->has_gatepass)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> Created
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-exclamation-circle"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $dc->created_at ? \Carbon\Carbon::parse($dc->created_at)->format('d-m-Y H:i') : 'N/A' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" onclick="event.stopPropagation();">
                                            @if($dc->has_gatepass)
                                                <!-- View Gate Pass -->
                                                <a href="{{ route('OutwardGatepass.show', $dc->gatepass_id) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="View Gate Pass">
                                                    <i class="fas fa-eye"></i> View
                                                </a>

                                                <!-- PDF -->
                                                <a href="{{ route('OutwardGatepass.pdf', $dc->gatepass_id) }}" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   title="Download PDF" 
                                                   target="_blank">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>

                                                <!-- Edit Gate Pass -->
                                                <a href="{{ route('outward_gatepass.create', $dc->id) }}" 
                                                   class="btn btn-sm btn-outline-warning" 
                                                   title="Edit Gate Pass">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            @else
                                                <!-- Create Gate Pass -->
                                                <a href="{{ route('outward_gatepass.create', $dc->id) }}" 
                                                   class="btn btn-sm btn-outline-success" 
                                                   title="Create Gate Pass">
                                                    <i class="fas fa-plus-circle"></i> Create Gate Pass
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $deliveryChallans->links() }}
                </div>
            @else
                <div class="alert alert-info text-center" role="alert">
                    <i class="fas fa-info-circle"></i>
                    <strong>No delivery challans found.</strong>
                    <p class="mb-0 small mt-2">Create a sale and delivery challan first to see them here.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h5 class="mb-0 text-info">{{ $deliveryChallans->total() ?? 0 }}</h5>
                    <small class="text-muted">Total DCs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    @php
                        $withGatepass = $deliveryChallans->getCollection()->where('has_gatepass', true)->count();
                    @endphp
                    <h5 class="mb-0 text-success">{{ $withGatepass }}</h5>
                    <small class="text-muted">Gate Passes Created</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    @php
                        $withoutGatepass = $deliveryChallans->getCollection()->where('has_gatepass', false)->count();
                    @endphp
                    <h5 class="mb-0 text-warning">{{ $withoutGatepass }}</h5>
                    <small class="text-muted">Pending Gate Pass</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    @php
                        $totalItems = $deliveryChallans->getCollection()->sum('items_count');
                    @endphp
                    <h5 class="mb-0 text-primary">{{ $totalItems }}</h5>
                    <small class="text-muted">Total Items</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    table tbody tr {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    table tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
