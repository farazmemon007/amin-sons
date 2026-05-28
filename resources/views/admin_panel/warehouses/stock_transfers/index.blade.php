@extends('admin_panel.layout.app')
@section('content')

<!-- Page Header -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h4 class="h4 fw-bold text-dark mt-2 mb-2 ml-2">
                <i class="fas fa-exchange-alt text-primary"></i> Stock Transfers
            </h4>
            <p class="text-muted mb-0 ml-2">Manage inventory movement between warehouses and branches</p>
        </div>
        @can('stock.transfer.create')
        <a href="{{ route('stock_transfers.create') }}" class="btn btn-primary btn-sm mt-2 mr-2 shadow-sm">
            <i class="fas fa-plus-circle me-2"></i> New Transfer
        </a>
        @endcan
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4 mx-2">
    <div class="col-12 col-sm-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="position-absolute" style="top: -15px; right: -15px; opacity: 0.15; font-size: 4rem; color: white;">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="card-body position-relative" style="color: white;">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-exchange-alt me-2" style="font-size: 1.5rem; color: white;"></i>
                    <span class="fw-bold small">Total Transfers</span>
                </div>
                <h3 class="fw-bold mb-3" style="font-size: 2.5rem; color: white;">{{ $transfers->count() }}</h3>
                <small style="color: rgba(255,255,255,0.8);">All transfers completed</small>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="position-absolute" style="top: -15px; right: -15px; opacity: 0.15; font-size: 4rem; color: white;">
                <i class="fas fa-warehouse"></i>
            </div>
            <div class="card-body position-relative" style="color: white;">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-warehouse me-2" style="font-size: 1.5rem; color: white;"></i>
                    <span class="fw-bold small">From Warehouse</span>
                </div>
                <h3 class="fw-bold mb-3" style="font-size: 2.5rem; color: white;">{{ $transfers->where('from_warehouse_id', '!=', null)->count() }}</h3>
                <small style="color: rgba(255,255,255,0.8);">Warehouse transfers</small>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="position-absolute" style="top: -15px; right: -15px; opacity: 0.15; font-size: 4rem; color: white;">
                <i class="fas fa-store"></i>
            </div>
            <div class="card-body position-relative" style="color: white;">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-store me-2" style="font-size: 1.5rem; color: white;"></i>
                    <span class="fw-bold small">From Branch</span>
                </div>
                <h3 class="fw-bold mb-3" style="font-size: 2.5rem; color: white;">{{ $transfers->where('from_branch_id', '!=', null)->count() }}</h3>
                <small style="color: rgba(255,255,255,0.8);">Branch transfers</small>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3 mb-3">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="position-absolute" style="top: -15px; right: -15px; opacity: 0.15; font-size: 4rem; color: white;">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="card-body position-relative" style="color: white;">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-cubes me-2" style="font-size: 1.5rem; color: white;"></i>
                    <span class="fw-bold small">Total Quantity</span>
                </div>
                <h3 class="fw-bold mb-3" style="font-size: 2.5rem; color: white;">{{ $transfers->sum('quantity') }}</h3>
                <small style="color: rgba(255,255,255,0.8);">Units transferred</small>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Error:</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Success:</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Validation Errors:</strong>
        <ul class="mb-0 mt-2 ms-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Main Content Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-list text-primary me-2"></i> Transfer Records
                </h5>
            </div>
            <div class="col-auto">
                <span class="badge bg-secondary">{{ $transfers->count() }} transfers</span>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @if($transfers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="transferTable">
                    <thead class="bg-light border-top border-bottom">
                        <tr>
                            <th class="ps-4" style="width: 5%">
                                <i class="fas fa-hashtag text-muted"></i>
                            </th>
                            <th style="width: 16%">📤 Source</th>
                            <th style="width: 16%">📥 Destination</th>
                            <th style="width: 20%">📦 Product</th>
                            <th style="width: 10%">
                                <i class="fas fa-shopping-cart text-primary me-1"></i> Quantity
                            </th>
                            <th style="width: 16%">📅 Date</th>
                            <th style="width: 12%; text-align: center;">
                                <i class="fas fa-cog text-muted"></i> Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transfers as $transfer)
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <span class="badge bg-light text-dark fw-bold">{{ $loop->iteration }}</span>
                            </td>
                            
                            <!-- Source -->
                            <td>
                                @if($transfer->fromWarehouse)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-warehouse text-info me-2"></i>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $transfer->fromWarehouse->warehouse_name }}</div>
                                            <small class="text-muted">Warehouse</small>
                                        </div>
                                    </div>
                                @elseif($transfer->fromBranch)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-store text-warning me-2"></i>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $transfer->fromBranch->name ?? $transfer->fromBranch->branch_name ?? 'Branch #' . $transfer->fromBranch->id }}</div>
                                            <small class="text-muted">Branch</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            
                            <!-- Destination -->
                            <td>
                                @if($transfer->toWarehouse)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-warehouse text-info me-2"></i>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $transfer->toWarehouse->warehouse_name }}</div>
                                            <small class="text-muted">Warehouse</small>
                                        </div>
                                    </div>
                                @elseif($transfer->toBranch)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-store text-warning me-2"></i>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $transfer->toBranch->name ?? $transfer->toBranch->branch_name ?? 'Branch #' . $transfer->toBranch->id }}</div>
                                            <small class="text-muted">Branch</small>
                                        </div>
                                    </div>
                                @elseif($transfer->to_shop)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-shopping-bag text-success me-2"></i>
                                        <div>
                                            <div class="fw-bold text-dark">Shop/Branch</div>
                                            <small class="text-muted">Transfer to Shop</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            
                            <!-- Product -->
                            <td>
                                <div>
                                    <div class="fw-bold text-dark">{{ $transfer->product->item_name }}</div>
                                    <small class="text-muted">ID: {{ $transfer->product->id }}</small>
                                </div>
                            </td>
                            
                            <!-- Quantity -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary text-white fw-bold d-flex align-items-center" style="font-size: 0.9rem; padding: 0.5rem 0.8rem; gap: 0.5rem; border-radius: 20px;">
                                        <i class="fas fa-cubes"></i>
                                        <span>{{ $transfer->quantity }} units</span>
                                    </span>
                                </div>
                            </td>
                            
                            <!-- Date & Time -->
                            <td>
                                <div>
                                    <div class="fw-bold text-dark">{{ $transfer->created_at->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $transfer->created_at->format('H:i:s') }}</small>
                                </div>
                            </td>
                            
                            <td style="text-align: center;">
                                <button type="button" class="btn btn-info btn-sm text-white shadow-sm" 
                                        data-toggle="modal" data-target="#transferModal{{ $transfer->id }}"
                                        title="View Details">
                                    <i class="fas fa-info-circle"></i> Details
                                </button>
                                <!-- Modal for Transfer Details -->
                                <div class="modal fade" id="transferModal{{ $transfer->id }}" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title fw-bold text-dark">
                                                    <i class="fas fa-exchange-alt text-primary me-2"></i> Transfer Details
                                                </h5>
                                                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row mb-2">
                                                    <div class="col-sm-5 text-muted fw-bold">Product:</div>
                                                    <div class="col-sm-7 text-dark">{{ $transfer->product->item_name ?? 'N/A' }}</div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-sm-5 text-muted fw-bold">Item Code:</div>
                                                    <div class="col-sm-7 text-dark">{{ $transfer->product->item_code ?? 'N/A' }}</div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-sm-5 text-muted fw-bold">Model No:</div>
                                                    <div class="col-sm-7 text-dark">{{ $transfer->product->model ?? 'N/A' }}</div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-sm-5 text-muted fw-bold">Brand:</div>
                                                    <div class="col-sm-7 text-dark">{{ $transfer->product->brand->brand_name ?? $transfer->product->brand->name ?? 'N/A' }}</div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-sm-5 text-muted fw-bold">Quantity:</div>
                                                    <div class="col-sm-7">
                                                        <span class="badge bg-primary px-3 py-2" style="font-size: 0.9rem;">{{ $transfer->quantity }} units</span>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row mb-3">
                                                    <div class="col-sm-5 text-muted fw-bold">From (Source):</div>
                                                    <div class="col-sm-7 text-dark">
                                                        @if($transfer->fromWarehouse)
                                                            <i class="fas fa-warehouse text-info me-1"></i> {{ $transfer->fromWarehouse->warehouse_name }}
                                                        @elseif($transfer->fromBranch)
                                                            <i class="fas fa-store text-warning me-1"></i> {{ $transfer->fromBranch->name ?? $transfer->fromBranch->branch_name ?? 'Branch #' . $transfer->fromBranch->id }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-sm-5 text-muted fw-bold">To (Destination):</div>
                                                    <div class="col-sm-7 text-dark">
                                                        @if($transfer->toWarehouse)
                                                            <i class="fas fa-warehouse text-info me-1"></i> {{ $transfer->toWarehouse->warehouse_name }}
                                                        @elseif($transfer->toBranch)
                                                            <i class="fas fa-store text-warning me-1"></i> {{ $transfer->toBranch->name ?? $transfer->toBranch->branch_name ?? 'Branch #' . $transfer->toBranch->id }}
                                                        @elseif($transfer->to_shop)
                                                            <i class="fas fa-shopping-bag text-success me-1"></i> Branch Main Shop
                                                        @else
                                                            N/A
                                                        @endif
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row mb-3">
                                                    <div class="col-sm-5 text-muted fw-bold">Date:</div>
                                                    <div class="col-sm-7 text-dark">{{ $transfer->created_at->format('d M Y, h:i A') }}</div>
                                                </div>
                                                @if($transfer->remarks)
                                                <div class="row">
                                                    <div class="col-sm-5 text-muted fw-bold">Remarks:</div>
                                                    <div class="col-sm-7 text-dark">{{ $transfer->remarks }}</div>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <div style="font-size: 4rem; color: #e9ecef; margin-bottom: 20px;">
                    <i class="fas fa-box-open"></i>
                </div>
                <h5 class="text-muted fw-bold mb-2">No Stock Transfers Yet</h5>
                <p class="text-muted mb-4">Get started by creating your first stock transfer to manage inventory movement between locations.</p>
                @can('stock.transfer.create')
                <a href="{{ route('stock_transfers.create') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle me-2"></i> Create First Transfer
                </a>
                @endcan
            </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('#transferTable').DataTable({
            responsive: true,
            paging: true,
            pageLength: 25,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            order: [[5, 'desc']], // Sort by date descending
            language: {
                search: '<i class="fas fa-search me-2"></i>',
                searchPlaceholder: 'Search transfers...',
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ transfers",
                infoEmpty: "No transfers found",
                zeroRecords: "No matching transfers found",
                emptyTable: "No data available"
            },
            dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            initComplete: function() {
                // Enhance search box styling
                $('.dataTables_filter input').addClass('form-control form-control-sm').css('width', '250px');
                $('.dataTables_length select').addClass('form-select form-select-sm').css('width', 'auto');
            }
        });
    });
</script>
@endsection
