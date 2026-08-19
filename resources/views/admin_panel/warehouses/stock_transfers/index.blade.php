@extends('admin_panel.layout.app')
@section('content')

<style>
    :root {
        --coa-navy: #1e3a5f;
        --coa-navy-dark: #0f1f38;
        --coa-navy-light: #2c5282;
        --coa-gold: #c8973a;
        --coa-emerald: #0d9f6e;
        --coa-border: #e2e8f0;
    }

    .st-wrapper {
        padding: 12px 0 30px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .st-header-bar {
        background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 60%, var(--coa-navy-light) 100%);
        border-radius: 10px;
        padding: 16px 22px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        box-shadow: 0 4px 15px rgba(15, 31, 56, 0.15);
        margin-bottom: 18px;
    }

    .st-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--coa-gold);
        border: 1px solid rgba(200, 151, 58, 0.3);
        flex-shrink: 0;
    }

    .st-header-title {
        font-size: 18px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        line-height: 1.2;
    }

    .st-header-sub {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.82);
        margin-top: 3px;
    }

    .btn-st-add {
        background: linear-gradient(135deg, var(--coa-emerald) 0%, #059669 100%);
        color: #ffffff !important;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(13, 159, 110, 0.3);
        text-decoration: none !important;
        transition: all 0.15s;
    }

    .btn-st-add:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        color: #ffffff !important;
    }

    .st-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    @media (max-width: 992px) {
        .st-kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .st-kpi-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 13px 16px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .st-kpi-card.highlight {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #a7f3d0;
    }

    .st-kpi-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .st-kpi-val {
        font-size: 19px;
        font-weight: 800;
        color: var(--coa-navy);
        font-family: monospace;
    }

    .st-kpi-val.emerald {
        color: #047857;
    }

    .st-kpi-icon {
        width: 38px;
        height: 38px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .kpi-icon-blue { background: #e0f2fe; color: #0284c7; }
    .kpi-icon-purple { background: #f3e8ff; color: #9333ea; }
    .kpi-icon-amber { background: #fef3c7; color: #d97706; }
    .kpi-icon-emerald { background: #d1fae5; color: #059669; }
</style>

<div class="main-content">
    <div class="st-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Corporate Header Bar --}}
            <div class="st-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="st-header-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div>
                        <h4 class="st-header-title">Stock Transfers</h4>
                        <div class="st-header-sub">
                            <span><i class="fas fa-truck" style="color: var(--coa-gold);"></i> Manage inventory movement between warehouses, branches, and retail points</span>
                        </div>
                    </div>
                </div>
                <div>
                    @can('stock.transfer.create')
                    <a href="{{ route('stock_transfers.create') }}" class="btn-st-add">
                        <i class="fas fa-plus-circle"></i> + New Transfer
                    </a>
                    @endcan
                </div>
            </div>

            {{-- 2. Statistics Cards --}}
            <div class="st-kpi-grid">
                <div class="st-kpi-card">
                    <div>
                        <div class="st-kpi-label">Total Transfers</div>
                        <div class="st-kpi-val">{{ $transfers->count() }}</div>
                    </div>
                    <div class="st-kpi-icon kpi-icon-blue">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
                <div class="st-kpi-card">
                    <div>
                        <div class="st-kpi-label">From Warehouse</div>
                        <div class="st-kpi-val">{{ $transfers->where('from_warehouse_id', '!=', null)->count() }}</div>
                    </div>
                    <div class="st-kpi-icon kpi-icon-purple">
                        <i class="fas fa-warehouse"></i>
                    </div>
                </div>
                <div class="st-kpi-card">
                    <div>
                        <div class="st-kpi-label">From Branch</div>
                        <div class="st-kpi-val">{{ $transfers->where('from_branch_id', '!=', null)->count() }}</div>
                    </div>
                    <div class="st-kpi-icon kpi-icon-amber">
                        <i class="fas fa-store"></i>
                    </div>
                </div>
                <div class="st-kpi-card highlight">
                    <div>
                        <div class="st-kpi-label" style="color: #047857;">Total Units Moved</div>
                        <div class="st-kpi-val emerald">{{ number_format($transfers->sum('quantity')) }}</div>
                    </div>
                    <div class="st-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
            </div>

            {{-- 3. Alerts --}}
            @if(session('error'))
                <div class="alert alert-danger py-2 px-3 mb-3 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #ef4444 !important; border-radius: 6px;">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success py-2 px-3 mb-3 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #10b981 !important; border-radius: 6px;">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif

            {{-- 4. Main Table Card --}}
            <div class="card shadow-sm border-0" style="border-radius: 9px; overflow: hidden; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    @if($transfers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 datanew" style="font-size: 12.5px;" id="transferTable">
                                <thead style="background: #f8fafc;">
                                    <tr>
                                        <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">#</th>
                                        <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">Source Location</th>
                                        <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">Destination</th>
                                        <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">Product Item</th>
                                        <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1; text-align: right;">Quantity</th>
                                        <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">Date</th>
                                        <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transfers as $transfer)
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 12px 14px; vertical-align: middle; font-weight: 700; color: #64748b;">{{ $loop->iteration }}</td>
                                        
                                        <!-- Source -->
                                        <td style="padding: 12px 14px; vertical-align: middle;">
                                            @if($transfer->fromWarehouse)
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-warehouse text-primary"></i>
                                                    <div>
                                                        <strong style="color: #0f172a;">{{ $transfer->fromWarehouse->warehouse_name }}</strong>
                                                        <small class="text-muted d-block" style="font-size: 10.5px;">Warehouse</small>
                                                    </div>
                                                </div>
                                            @elseif($transfer->fromBranch)
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-store text-warning"></i>
                                                    <div>
                                                        <strong style="color: #0f172a;">{{ $transfer->fromBranch->name ?? $transfer->fromBranch->branch_name ?? 'Branch #' . $transfer->fromBranch->id }}</strong>
                                                        <small class="text-muted d-block" style="font-size: 10.5px;">Branch</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        
                                        <!-- Destination -->
                                        <td style="padding: 12px 14px; vertical-align: middle;">
                                            @if($transfer->toWarehouse)
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-warehouse text-primary"></i>
                                                    <div>
                                                        <strong style="color: #0f172a;">{{ $transfer->toWarehouse->warehouse_name }}</strong>
                                                        <small class="text-muted d-block" style="font-size: 10.5px;">Warehouse</small>
                                                    </div>
                                                </div>
                                            @elseif($transfer->toBranch)
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-store text-warning"></i>
                                                    <div>
                                                        <strong style="color: #0f172a;">{{ $transfer->toBranch->name ?? $transfer->toBranch->branch_name ?? 'Branch #' . $transfer->toBranch->id }}</strong>
                                                        <small class="text-muted d-block" style="font-size: 10.5px;">Branch</small>
                                                    </div>
                                                </div>
                                            @elseif($transfer->to_shop)
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-shopping-bag text-success"></i>
                                                    <div>
                                                        <strong style="color: #0f172a;">Shop/Branch</strong>
                                                        <small class="text-muted d-block" style="font-size: 10.5px;">Direct Main Shop</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        
                                        <!-- Product -->
                                        <td style="padding: 12px 14px; vertical-align: middle;">
                                            <strong style="color: #0f172a;">{{ $transfer->product->item_name }}</strong>
                                            <small class="text-muted d-block" style="font-family: monospace; font-size: 11px;">Code: {{ $transfer->product->item_code }}</small>
                                        </td>
                                        
                                        <!-- Quantity -->
                                        <td style="padding: 12px 14px; vertical-align: middle; text-align: right;">
                                            <span style="background: #ecfdf5; color: #047857; padding: 4px 10px; border-radius: 4px; font-weight: 800; font-family: monospace; font-size: 13px; border: 1px solid #a7f3d0;">
                                                {{ number_format($transfer->quantity) }} Units
                                            </span>
                                        </td>
                                        
                                        <!-- Date & Time -->
                                        <td style="padding: 12px 14px; vertical-align: middle;">
                                            <div class="font-weight-bold text-dark">{{ $transfer->created_at->format('d M Y') }}</div>
                                            <small class="text-muted">{{ $transfer->created_at->format('H:i') }}</small>
                                        </td>
                                        
                                        <td style="padding: 12px 14px; vertical-align: middle; text-align: center;">
                                            <button type="button" class="btn btn-sm btn-info text-white font-weight-bold px-2 py-1" 
                                                    data-toggle="modal" data-target="#transferModal{{ $transfer->id }}"
                                                    title="View Details" style="font-size: 11px; border-radius: 4px;">
                                                <i class="fas fa-info-circle mr-1"></i> Details
                                            </button>

                                            <!-- Modal for Transfer Details -->
                                            <div class="modal fade" id="transferModal{{ $transfer->id }}" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow" style="border-radius: 10px; overflow: hidden;">
                                                        <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 100%);">
                                                            <h6 class="modal-title font-weight-bold mb-0">
                                                                <i class="fas fa-exchange-alt mr-1"></i> Transfer Details (#{{ $transfer->id }})
                                                            </h6>
                                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body p-4 bg-white">
                                                            <div class="row mb-2">
                                                                <div class="col-sm-5 text-muted font-weight-bold small">Product:</div>
                                                                <div class="col-sm-7 text-dark font-weight-bold">{{ $transfer->product->item_name ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="row mb-2">
                                                                <div class="col-sm-5 text-muted font-weight-bold small">Item Code:</div>
                                                                <div class="col-sm-7 text-dark font-monospace">{{ $transfer->product->item_code ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="row mb-2">
                                                                <div class="col-sm-5 text-muted font-weight-bold small">Brand:</div>
                                                                <div class="col-sm-7 text-dark">{{ $transfer->product->brand->brand_name ?? $transfer->product->brand->name ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-sm-5 text-muted font-weight-bold small">Quantity Transferred:</div>
                                                                <div class="col-sm-7">
                                                                    <span class="badge badge-success font-weight-bold px-3 py-1" style="font-size: 12px;">{{ number_format($transfer->quantity) }} units</span>
                                                                </div>
                                                            </div>
                                                            <hr class="my-2">
                                                            <div class="row mb-2">
                                                                <div class="col-sm-5 text-muted font-weight-bold small">From (Source):</div>
                                                                <div class="col-sm-7 text-dark font-weight-bold">
                                                                    @if($transfer->fromWarehouse)
                                                                        <i class="fas fa-warehouse text-info mr-1"></i> {{ $transfer->fromWarehouse->warehouse_name }}
                                                                    @elseif($transfer->fromBranch)
                                                                        <i class="fas fa-store text-warning mr-1"></i> {{ $transfer->fromBranch->name ?? $transfer->fromBranch->branch_name ?? 'Branch #' . $transfer->fromBranch->id }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <div class="row mb-2">
                                                                <div class="col-sm-5 text-muted font-weight-bold small">To (Destination):</div>
                                                                <div class="col-sm-7 text-dark font-weight-bold">
                                                                    @if($transfer->toWarehouse)
                                                                        <i class="fas fa-warehouse text-info mr-1"></i> {{ $transfer->toWarehouse->warehouse_name }}
                                                                    @elseif($transfer->toBranch)
                                                                        <i class="fas fa-store text-warning mr-1"></i> {{ $transfer->toBranch->name ?? $transfer->toBranch->branch_name ?? 'Branch #' . $transfer->toBranch->id }}
                                                                    @elseif($transfer->to_shop)
                                                                        <i class="fas fa-shopping-bag text-success mr-1"></i> Branch Main Shop
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <hr class="my-2">
                                                            <div class="row mb-2">
                                                                <div class="col-sm-5 text-muted font-weight-bold small">Transfer Date:</div>
                                                                <div class="col-sm-7 text-dark">{{ $transfer->created_at->format('d M Y, h:i A') }}</div>
                                                            </div>
                                                            @if($transfer->remarks)
                                                            <div class="row">
                                                                <div class="col-sm-5 text-muted font-weight-bold small">Remarks:</div>
                                                                <div class="col-sm-7 text-dark font-italic">{{ $transfer->remarks }}</div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                        <div class="modal-footer bg-light p-2">
                                                            <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-3" data-dismiss="modal">Close</button>
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
                            <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-50"></i>
                            <h6 class="text-dark font-weight-bold mb-1">No Stock Transfers Yet</h6>
                            <p class="text-muted small mb-3">Get started by creating your first stock transfer to move inventory.</p>
                            @can('stock.transfer.create')
                            <a href="{{ route('stock_transfers.create') }}" class="btn btn-sm btn-primary font-weight-bold px-3" style="background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-plus-circle mr-1"></i> Create First Transfer
                            </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        if (!$.fn.DataTable.isDataTable('#transferTable')) {
            $('#transferTable').DataTable({
                responsive: true,
                order: [[5, 'desc']]
            });
        }
    });
</script>
@endsection
