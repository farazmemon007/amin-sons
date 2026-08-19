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

    .rpt-wrapper {
        padding: 12px 0 30px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .rpt-header-bar {
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

    .rpt-header-icon {
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

    .rpt-header-title {
        font-size: 18px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        line-height: 1.2;
    }

    .rpt-header-sub {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.85);
        margin-top: 3px;
    }

    .rpt-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    @media (max-width: 992px) {
        .rpt-kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .rpt-kpi-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 13px 16px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .rpt-kpi-card.highlight {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #a7f3d0;
    }

    .rpt-kpi-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .rpt-kpi-val {
        font-size: 18px;
        font-weight: 800;
        color: var(--coa-navy);
        font-family: monospace;
    }

    .rpt-kpi-val.emerald { color: #047857; }
    .rpt-kpi-val.purple { color: #7c3aed; }
    .rpt-kpi-val.amber { color: #d97706; }

    .rpt-kpi-icon {
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
    .kpi-icon-emerald { background: #d1fae5; color: #059669; }
    .kpi-icon-purple { background: #f3e8ff; color: #7c3aed; }
    .kpi-icon-amber { background: #fef3c7; color: #d97706; }

    .f-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.03em;
        margin-bottom: 4px;
        display: block;
    }

    #auditTable th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 8px;
        border: 1px solid #1e3a5f;
    }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">
            
            {{-- 1. Corporate Header Bar --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Stock Hold Audit Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-history mr-1" style="color: var(--coa-gold);"></i> Detailed audit trail of stock allocations, customer commitments & remaining balances &mdash; Ameen & Sons ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('report.stock.hold.audit.export', request()->query()) }}" class="btn btn-sm btn-light font-weight-bold text-dark border">
                        <i class="fas fa-file-excel mr-1 text-success"></i> Export CSV
                    </a>
                    <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-light font-weight-bold">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                </div>
            </div>

            {{-- 2. Summary KPI Cards --}}
            <div class="rpt-kpi-grid">
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Available</div>
                        <div class="rpt-kpi-val">{{ number_format($totalAvailableQty, 2) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-blue">
                        <i class="fas fa-cubes"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Delivered</div>
                        <div class="rpt-kpi-val amber">{{ number_format($totalDeliverQty, 2) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-amber">
                        <i class="fas fa-truck"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card highlight">
                    <div>
                        <div class="rpt-kpi-label" style="color: #047857;">Remaining Hold</div>
                        <div class="rpt-kpi-val emerald">{{ number_format($totalRemainingQty, 2) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-warehouse"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Inventory Value</div>
                        <div class="rpt-kpi-val purple">PKR {{ number_format($totalValue, 2) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-purple">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>

            {{-- 3. Filter Card --}}
            <div class="card shadow-sm border-0 mb-3" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('report.stock.hold.audit') }}" class="row g-2 align-items-end mb-0">
                        <div class="col-md-2">
                            <label class="f-label">Reference #</label>
                            <input type="text" name="invoice_no" class="form-control form-control-sm" placeholder="Invoice / DC" value="{{ $filters['invoice_no'] ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-3">
                            <label class="f-label">Client / Customer</label>
                            <select name="customer_id" class="form-control form-control-sm select2">
                                <option value="">All Customers</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" {{ ($filters['customer_id'] ?? '') == $cust->id ? 'selected' : '' }}>{{ $cust->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">From Date</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">To Date</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary flex-grow-1 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-filter mr-1"></i> Apply Filters
                            </button>
                            <a href="{{ route('report.stock.hold.audit') }}" class="btn btn-sm btn-light border font-weight-bold text-muted d-inline-flex align-items-center justify-content-center" style="height: 38px; border-radius: 6px; width: 38px;" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 4. Report Table Card --}}
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="auditTable" style="font-size: 12px;">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">REF DETAILS</th>
                                    <th>PRODUCT / ITEM</th>
                                    <th style="width: 11%;">WAREHOUSE</th>
                                    <th style="width: 13%;">CUSTOMER</th>
                                    <th class="text-end" style="width: 8%;">TOTAL QTY</th>
                                    <th class="text-end" style="width: 7%;">AVAIL.</th>
                                    <th class="text-end" style="width: 7%;">DELIVER</th>
                                    <th class="text-end" style="width: 8%;">REMAIN.</th>
                                    <th class="text-end" style="width: 8%;">UNIT PRICE</th>
                                    <th class="text-end" style="width: 10%;">TOTAL VALUE</th>
                                    <th class="text-center" style="width: 8%;">STATUS</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @forelse($stockHolds as $hold)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold text-primary">{{ $hold->invoice_no }}</div>
                                            <small class="text-muted font-monospace">DC: {{ $hold->dc_no }}</small>
                                        </td>
                                        <td>
                                            @php
                                                $pName = $hold->product_name ?: ($hold->product?->item_name ?? 'N/A');
                                                $pCode = $hold->product_code ?: ($hold->product?->item_code ?? '');
                                            @endphp
                                            <strong class="text-dark">{{ Str::limit($pName, 35) }}</strong>
                                            <small class="text-muted d-block font-monospace">CODE: {{ $pCode }}</small>
                                        </td>
                                        <td>
                                            <div class="badge badge-light border font-weight-normal text-dark">
                                                <i class="fas fa-map-marker-alt mr-1 text-danger"></i> {{ $hold->warehouse?->warehouse_name ?? 'Branch' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small font-weight-bold text-dark text-truncate" style="max-width: 150px;">
                                                {{ $hold->sale?->sub_customer ?? $hold->sale?->customer?->customer_name ?? 'Walking Customer' }}
                                            </div>
                                            <small class="text-muted" style="font-size: 10.5px;">
                                                <i class="far fa-clock mr-1"></i>{{ $hold->created_at->format('d-m-Y') }}
                                            </small>
                                        </td>
                                        <!-- Total Qty column -->
                                        <td class="text-end font-weight-bold font-monospace" style="background: #f8fafc; color: #1e293b;">
                                            @php
                                                $invoiceTotalQty = $hold->sale?->saleItems->sum(function ($item) {
                                                    return $item->sales_qty ?? $item->qty ?? 0;
                                                }) ?? 0;
                                            @endphp
                                            {{ number_format($invoiceTotalQty > 0 ? $invoiceTotalQty : $hold->qty, 2) }}
                                        </td>
                                        <td class="text-end font-monospace">{{ number_format($hold->available_qty, 2) }}</td>
                                        <td class="text-end font-monospace" style="color: #d97706;">{{ number_format($hold->deliver_qty, 2) }}</td>
                                        <td class="text-end font-weight-bold font-monospace text-success">{{ number_format($hold->remaining_qty, 2) }}</td>
                                        <td class="text-end font-monospace">PKR {{ number_format($hold->unit_price ?? $hold->price ?? 0, 2) }}</td>
                                        <td class="text-end font-weight-bold font-monospace text-primary">PKR {{ number_format($hold->total_price ?? (($hold->deliver_qty ?? 0) * ($hold->unit_price ?? $hold->price ?? 0)), 2) }}</td>
                                        <td class="text-center">
                                            @php
                                                $status = '';
                                                $statusClass = '';
                                                $statusIcon = '';
                                                
                                                if (isset($hold->has_gatepass) && $hold->has_gatepass) {
                                                    if ($hold->remaining_qty > 0) {
                                                        $status = 'Partial';
                                                        $statusClass = 'badge-warning';
                                                        $statusIcon = 'fa-hourglass-half';
                                                    } else {
                                                        $status = 'Delivered';
                                                        $statusClass = 'badge-success';
                                                        $statusIcon = 'fa-check-circle';
                                                    }
                                                } else {
                                                    $status = 'Hold Active';
                                                    $statusClass = 'badge-primary';
                                                    $statusIcon = 'fa-clock';
                                                }
                                            @endphp
                                            <span class="badge {{ $statusClass }} px-2 py-1" style="{{ $statusClass == 'badge-primary' ? 'background: var(--coa-navy);' : '' }}">
                                                <i class="fas {{ $statusIcon }} mr-1"></i>{{ $status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4 text-muted">No stock hold audit records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if(method_exists($stockHolds, 'links'))
                <div class="card-footer bg-white border-top py-3">
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <p class="text-muted small mb-0">Showing records {{ $stockHolds->firstItem() }} to {{ $stockHolds->lastItem() }} of {{ $stockHolds->total() }}</p>
                        </div>
                        <div class="col-sm-6 d-flex justify-content-end">
                            {{ $stockHolds->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection