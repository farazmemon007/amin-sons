@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid py-4" style="background-color: #f4f7f6; min-height: 100vh;">
    
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="h3 mb-1 text-dark fw-bold">Stock Hold Audit</h1>
            <p class="text-muted small mb-0">Detailed audit trail of stock allocations and remaining balances.</p>
        </div>
        <div class="col-md-5 text-end">
            <div class="btn-group shadow-sm">
                <button type="button" class="btn btn-white btn-sm border">
                    <i class="fas fa-print text-secondary me-1"></i> Print
                </button>
                <a href="{{ route('report.stock.hold.audit.export', request()->query()) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Export CSV
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @php
            $kpis = [
                ['label' => 'Total Available', 'value' => $totalAvailableQty, 'color' => '#4e73df', 'icon' => 'fa-cubes'],
                ['label' => 'Total Deliver', 'value' => $totalDeliverQty, 'color' => '#f6c23e', 'icon' => 'fa-shuttle-van'],
                ['label' => 'Remaining', 'value' => $totalRemainingQty, 'color' => '#1cc88a', 'icon' => 'fa-warehouse'],
                ['label' => 'Inventory Value', 'value' => $totalValue, 'color' => '#36b9cc', 'icon' => 'fa-wallet', 'is_money' => true],
            ];
        @endphp

        @foreach($kpis as $kpi)
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 p-3 rounded-3 opacity-10" style="background-color: {{ $kpi['color'] }};">
                            <i class="fas {{ $kpi['icon'] }} fa-lg" style="color: {{ $kpi['color'] }}; opacity: 1;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-uppercase fw-bold text-muted mb-0" style="font-size: 0.7rem; letter-spacing: 1px;">{{ $kpi['label'] }}</h6>
                            <span class="h4 fw-bold mb-0 text-dark">
                                {{ isset($kpi['is_money']) ? 'PKR ' : '' }}{{ number_format($kpi['value'], 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('report.stock.hold.audit') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Reference #</label>
                    <input type="text" name="invoice_no" class="form-control form-control-sm border-light-2" placeholder="Invoice / DC" value="{{ $filters['invoice_no'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Client / Customer</label>
                    <select name="customer_id" class="form-select form-select-sm border-light-2 select2">
                        <option value="">All Customers</option>
                        @foreach($customers as $cust)
                            <option value="{{ $cust->id }}" {{ ($filters['customer_id'] ?? '') == $cust->id ? 'selected' : '' }}>{{ $cust->customer_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm border-light-2" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm border-light-2" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1 fw-bold">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                    <a href="{{ route('report.stock.hold.audit') }}" class="btn btn-light btn-sm border fw-bold text-muted">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="auditTable">
                <thead class="bg-dark text-white">
                    <tr class="small">
                        <th class="ps-4 py-3">REF DETAILS</th>
                        <th class="py-3">PRODUCT / ITEM</th>
                        <th class="py-3">WAREHOUSE</th>
                        <th class="py-3">CUSTOMER</th>
                        <th class="text-end py-3" style="background: rgba(25,135,84,0.1); color: #155724;">TOTAL QTY</th>
                        <th class="text-end py-3">AVAIL.</th>
                        <th class="text-end py-3" style="background: rgba(255,193,7,0.1); color: #856404;">DELIVER</th>
                        <th class="text-end py-3 text-success">REMAIN.</th>
                        <th class="text-end py-3">UNIT PRICE</th>
                        <th class="text-end py-3">TOTAL VALUE</th>
                        <th class="text-center py-3" style="background: rgba(13,110,253,0.1); color: #0d6efd;">STATUS</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($stockHolds as $hold)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-primary mb-0">{{ $hold->invoice_no }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">DC: {{ $hold->dc_no }}</div>
                            </td>
                            <td>
                                @php
                                    $pName = $hold->product_name ?: ($hold->product?->item_name ?? 'N/A');
                                    $pCode = $hold->product_code ?: ($hold->product?->item_code ?? '');
                                @endphp
                                <div class="text-dark fw-600 mb-0">{{ Str::limit($pName, 35) }}</div>
                                <span class="text-muted" style="font-size: 0.7rem;">CODE: {{ $pCode }}</span>
                            </td>
                            <td>
                                <div class="badge bg-light text-secondary border fw-normal">
                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i> {{ $hold->warehouse?->warehouse_name ?? 'Branch' }}
                                </div>
                            </td>
                            <td>
                                <div class="small fw-bold text-dark text-truncate" style="max-width: 150px;">
                                    {{ $hold->sale?->sub_customer ?? $hold->sale?->customer?->customer_name ?? 'Walking Customer' }}
                                </div>
                                <div class="text-muted small" style="font-size: 0.65rem;">
                                    <i class="far fa-clock me-1"></i>{{ $hold->created_at->format('d-m-Y') }}
                                </div>
                            </td>
                            <!-- ✅ INTERNATIONAL ERP STANDARD: Total Qty column - sum of all items in the sale -->
                            <td class="text-end fw-bold" style="background: rgba(25,135,84,0.05); color: #155724;">
                                @php
                                    $invoiceTotalQty = $hold->sale?->saleItems->sum(function ($item) {
                                        return $item->sales_qty ?? $item->qty ?? 0;
                                    }) ?? 0;
                                @endphp
                                {{ number_format($invoiceTotalQty, 2) }}
                            </td>
                            <td class="text-end fw-bold">{{ number_format($hold->available_qty, 2) }}</td>
                            <td class="text-end fw-bold" style="background: rgba(255,193,7,0.05);">
                                <span class="text-warning-emphasis">{{ number_format($hold->deliver_qty, 2) }}</span>
                            </td>
                            <td class="text-end fw-bold text-success">{{ number_format($hold->remaining_qty, 2) }}</td>
                            <td class="text-end text-muted">{{ number_format($hold->unit_price, 2) }}</td>
                            <td class="text-end pe-4">
                                <span class="fw-bold text-dark font-monospace">{{ number_format(($hold->deliver_qty * $hold->unit_price), 2) }}</span>
                            </td>
                            <!-- ✅ INTERNATIONAL ERP STANDARD: Status column showing Gate Pass status -->
                            <td class="text-center">
                                @php
                                    $status = '';
                                    $statusClass = '';
                                    $statusIcon = '';
                                    
                                    // Check if Gate Pass exists (has_gatepass field added by LEFT JOIN)
                                    if ($hold->has_gatepass) {
                                        // Gate Pass exists
                                        if ($hold->remaining_qty > 0) {
                                            // Partial delivery - items still pending
                                            $status = 'Partial Delivery';
                                            $statusClass = 'bg-warning text-dark';
                                            $statusIcon = 'fa-hourglass-half';
                                        } else {
                                            // Complete delivery
                                            $status = 'Delivered';
                                            $statusClass = 'bg-success text-white';
                                            $statusIcon = 'fa-check-circle';
                                        }
                                    } else {
                                        // Gate Pass NOT created yet
                                        $status = 'Awaiting Gate Pass';
                                        $statusClass = 'bg-info text-white';
                                        $statusIcon = 'fa-clock';
                                    }
                                @endphp
                                <span class="badge {!! $statusClass !!} small">
                                    <i class="fas {!! $statusIcon !!} me-1"></i>{!! $status !!}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 bg-white">
                                <div class="py-4">
                                    <i class="fas fa-folder-open fa-3x text-light mb-3"></i>
                                    <h5 class="text-muted">No audit logs found for this period</h5>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white border-0 py-3">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <p class="text-muted small mb-0">Showing records {{ $stockHolds->firstItem() }} to {{ $stockHolds->lastItem() }} of {{ $stockHolds->total() }}</p>
                </div>
                <div class="col-sm-6 d-flex justify-content-end">
                    {{ $stockHolds->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium ERP Tweaks */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
    
    body { font-family: 'Inter', sans-serif; }
    .fw-600 { font-weight: 600; }
    .border-light-2 { border: 1px solid #e9ecef !important; }
    
    /* Table Styling */
    #auditTable thead th {
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        font-weight: 700;
        border: none;
    }
    #auditTable tbody td {
        border-bottom: 1px solid #f8f9fa;
        font-size: 0.82rem;
        padding-top: 14px;
        padding-bottom: 14px;
    }
    #auditTable tr:hover {
        background-color: #fcfdfe !important;
        transition: 0.2s;
    }
    
    /* Pagination Styling */
    .pagination { margin-bottom: 0; gap: 5px; }
    .page-link { 
        border: none; 
        border-radius: 8px !important; 
        color: #6c757d; 
        font-weight: 600; 
        font-size: 0.8rem;
    }
    .page-item.active .page-link { background-color: #4e73df; }

    /* Custom Scrollbar for Table */
    .table-responsive::-webkit-scrollbar { height: 6px; }
    .table-responsive::-webkit-scrollbar-thumb { background: #e2e2e2; border-radius: 10px; }
</style>
@endsection