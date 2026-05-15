@extends('admin_panel.layout.app')

@section('content')
<style>
    .main-content { background-color: #f8f9fa; min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .card { border-radius: 12px; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    .table thead th { background-color: #f1f4f9; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: #555; border-bottom: none; padding: 15px 10px; }
    .table tbody td { padding: 14px 10px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; font-size: 13px; }
    .bg-soft-success { background-color: #d1f7e8 !important; color: #008d50 !important; }
    .bg-soft-warning { background-color: #fff3cd !important; color: #856404 !important; }
    .bg-soft-info { background-color: #e0f7fa !important; color: #00acc1 !important; }
    .bg-soft-primary { background-color: #e0e7ff !important; color: #3730a3 !important; }
    .bg-soft-danger { background-color: #ffebee !important; color: #c62828 !important; }
    .dropdown-menu { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 10px; padding: 8px; z-index: 9999 !important; }
    .dropdown-item { border-radius: 6px; padding: 8px 12px; font-size: 13px; transition: all 0.2s; }
    .dropdown-item:hover { background-color: #f0f7ff; color: #007bff; }
    .dropdown-item i { width: 20px; }
    .summary-card { transition: transform 0.3s ease; }
    .summary-card:hover { transform: translateY(-5px); }
</style>

<div class="main-content">
    <div class="container-fluid px-4 pt-4">

        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0 text-dark"><i class="fas fa-box-open text-primary mr-2"></i> Delivery Challans (DC)</h4>
                <small class="text-muted">Manage out-going stock and generate outward gate passes</small>
            </div>
            <div class="d-flex">
                <a href="{{ route('OutwardGatepass.list') }}" class="btn btn-primary px-4 fw-bold shadow-sm" style="border-radius: 8px;">
                    <i class="fa fa-receipt mr-2"></i> VIEW ALL GATE PASSES
                </a>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
                <strong><i class="fa fa-check-circle mr-2"></i>Success!</strong> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" role="alert">
                <strong><i class="fa fa-exclamation-triangle mr-2"></i>Error!</strong> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card summary-card bg-primary text-white shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-boxes fa-2x opacity-50"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold">{{ $deliveryChallans->total() ?? 0 }}</h4>
                            <small class="opacity-75">Total DCs</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card bg-success text-white shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-check-double fa-2x opacity-50"></i>
                        </div>
                        <div>
                            @php
                                $withGatepass = $deliveryChallans->getCollection()->where('has_gatepass', true)->count();
                            @endphp
                            <h4 class="mb-0 fw-bold">{{ $withGatepass }}</h4>
                            <small class="opacity-75">Generated Gate Passes</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card bg-warning text-dark shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-hourglass-half fa-2x opacity-50"></i>
                        </div>
                        <div>
                            @php
                                $withoutGatepass = $deliveryChallans->getCollection()->where('has_gatepass', false)->count();
                            @endphp
                            <h4 class="mb-0 fw-bold">{{ $withoutGatepass }}</h4>
                            <small class="opacity-75">Pending Gate Passes</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card summary-card bg-info text-white shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-cubes fa-2x opacity-50"></i>
                        </div>
                        <div>
                            @php
                                $totalItems = $deliveryChallans->getCollection()->sum('items_count');
                            @endphp
                            <h4 class="mb-0 fw-bold">{{ $totalItems }}</h4>
                            <small class="opacity-75">Total Items in DCs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card p-0 overflow-hidden shadow-sm">
            @if($deliveryChallans->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0 w-100">
                        <thead>
                            <tr class="text-center">
                                <th># ID</th>
                                <th>DC No</th>
                                <th class="text-left">Customer Details</th>
                                <th>Location (Warehouse/Branch)</th>
                                <th>Items Count</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @foreach($deliveryChallans as $dc)
                                <tr style="cursor: pointer;" onclick="window.location='{{ route('outward_gatepass.create', $dc->id) }}'">
                                    <td class="fw-bold text-muted">#{{ $dc->id }}</td>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $dc->dc_no ?? 'N/A' }}</div>
                                    </td>
                                    <td class="text-left">
                                        <div class="fw-bold text-dark">{{ Str::limit($dc->display_customer_name ?? 'N/A', 25) }}</div>
                                        @if($dc->is_walking_customer)
                                            <span class="badge bg-soft-warning px-2 py-1 mt-1">Walking Customer</span>
                                        @else
                                            <div class="small text-muted">{{ $dc->contact_person ?? '' }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $dc->location_name ?? 'N/A' }}</div>
                                        <span class="badge bg-soft-info mt-1 px-2 py-1">{{ $dc->location_type ?? 'Unknown' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1">{{ $dc->items_count ?? 0 }} Items</span>
                                    </td>
                                    <td>
                                        @if($dc->has_gatepass)
                                            <span class="badge bg-soft-success px-3 py-2">
                                                <i class="fas fa-check-circle mr-1"></i> Generated
                                            </span>
                                        @else
                                            <span class="badge bg-soft-warning px-3 py-2">
                                                <i class="fas fa-hourglass-half mr-1"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $dc->created_at ? \Carbon\Carbon::parse($dc->created_at)->format('d-M-Y') : 'N/A' }}</div>
                                        <small class="text-muted">{{ $dc->created_at ? \Carbon\Carbon::parse($dc->created_at)->format('H:i A') : '' }}</small>
                                    </td>
                                    <td onclick="event.stopPropagation();">
                                        <div class="d-flex justify-content-center gap-1">
                                            @if($dc->has_gatepass)
                                                <!-- Action dropdown if generated -->
                                                <div class="dropdown">
                                                    <button class="btn btn-light btn-sm border dropdown-toggle fw-bold" type="button" data-toggle="dropdown">
                                                        Manage
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item text-primary" href="{{ route('OutwardGatepass.show', $dc->gatepass_id) }}">
                                                            <i class="fas fa-eye text-primary"></i> View Gate Pass
                                                        </a>
                                                        <a class="dropdown-item text-danger" href="{{ route('OutwardGatepass.pdf', $dc->gatepass_id) }}" target="_blank">
                                                            <i class="fas fa-file-pdf text-danger"></i> Download PDF
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-warning" href="{{ route('outward_gatepass.create', $dc->id) }}">
                                                            <i class="fas fa-edit text-warning"></i> Edit Details
                                                        </a>
                                                    </div>
                                                </div>
                                            @else
                                                <a href="{{ route('outward_gatepass.create', $dc->id) }}" class="btn btn-success btn-sm shadow-sm" title="Create Gate Pass">
                                                    <i class="fa fa-truck mr-1"></i> Generate
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $deliveryChallans->links() }}
                </div>
            @else
                <div class="p-5 text-center">
                    <div class="mb-3">
                        <i class="fas fa-box-open fa-3x text-muted opacity-50"></i>
                    </div>
                    <h5 class="text-muted fw-bold">No Delivery Challans Found</h5>
                    <p class="text-muted mb-0">Create a sale and delivery challan first to see them here.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
