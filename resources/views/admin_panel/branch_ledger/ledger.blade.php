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
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    @media (max-width: 992px) {
        .rpt-kpi-grid {
            grid-template-columns: 1fr;
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
    .rpt-kpi-val.crimson { color: #dc2626; }

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
    .kpi-icon-red { background: #fee2e2; color: #dc2626; }

    .f-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.03em;
        margin-bottom: 4px;
        display: block;
    }

    .table thead th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
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

            <!-- 1. Corporate Header Bar -->
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Detailed Branch Ledger</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-search-dollar mr-1" style="color: var(--coa-gold);"></i> Full inter-branch transaction history and audit trail &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Filter Card -->
            <div class="card shadow-sm border-0 mb-3" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('branch_ledger_detail') }}" class="row g-2 align-items-end mb-0">
                        <div class="col-md-3">
                            <label class="f-label">Type</label>
                            <select name="type" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                                <option value="">All Types</option>
                                <option value="debit" @if(request()->input('type') === 'debit') selected @endif>Debit (Payable)</option>
                                <option value="credit" @if(request()->input('type') === 'credit') selected @endif>Credit (Receivable)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="f-label">Reference Type</label>
                            <select name="reference_type" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                                <option value="">All References</option>
                                <option value="transfer" @if(request()->input('reference_type') === 'transfer') selected @endif>Stock Transfer</option>
                                <option value="payment" @if(request()->input('reference_type') === 'payment') selected @endif>Payment</option>
                                <option value="receipt" @if(request()->input('reference_type') === 'receipt') selected @endif>Receipt</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">From Date</label>
                            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request()->input('from_date') }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">To Date</label>
                            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request()->input('to_date') }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary flex-grow-1 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('branch_ledger_detail') }}" class="btn btn-sm btn-light border font-weight-bold text-muted d-inline-flex align-items-center justify-content-center" style="height: 38px; border-radius: 6px; width: 38px;" title="Reset Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 3. Summary KPI Cards -->
            <div class="rpt-kpi-grid">
                <div class="rpt-kpi-card highlight">
                    <div>
                        <div class="rpt-kpi-label" style="color: #047857;">Total Credits (Receivable)</div>
                        <div class="rpt-kpi-val emerald">{{ number_format($summary['totalCredit'], 2) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Debits (Payable)</div>
                        <div class="rpt-kpi-val crimson">{{ number_format($summary['totalDebit'], 2) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-red">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Net Balance</div>
                        @php $net = $summary['totalCredit'] - $summary['totalDebit']; @endphp
                        <div class="rpt-kpi-val" style="color: {{ $net > 0 ? '#047857' : ($net < 0 ? '#dc2626' : 'var(--coa-navy)') }};">
                            {{ $net > 0 ? '+' : '' }}{{ number_format($net, 2) }}
                        </div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-blue">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>

            <!-- 4. Transactions Table -->
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" style="font-size: 12.5px;">
                            <thead>
                                <tr>
                                    <th style="width: 14%;">Date</th>
                                    <th>Description</th>
                                    <th style="width: 18%;">Related Branch</th>
                                    <th class="text-center" style="width: 10%;">Type</th>
                                    <th style="width: 15%;">Reference</th>
                                    <th class="text-end" style="width: 15%;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                        <td>{{ $transaction->display_description }}</td>
                                        <td>
                                            <span class="font-weight-bold text-dark">{{ $transaction->relatedBranch?->name ?? 'N/A' }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if ($transaction->type === 'debit')
                                                <span class="badge badge-danger px-2 py-1">Debit</span>
                                            @else
                                                <span class="badge badge-success px-2 py-1">Credit</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted font-monospace text-uppercase">{{ $transaction->reference_type }} #{{ $transaction->reference_id }}</small>
                                        </td>
                                        <td class="text-end font-monospace font-weight-bold @if ($transaction->type === 'debit') text-danger @else text-success @endif">
                                            {{ number_format($transaction->display_amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No transactions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if (method_exists($transactions, 'links'))
                        <div class="mt-3">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
