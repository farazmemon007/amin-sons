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
    .kpi-icon-gray { background: #f1f5f9; color: #64748b; }

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
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Branch Ledger Summary</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-chart-pie mr-1" style="color: var(--coa-gold);"></i> Account overview, inter-branch receivables & payables &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Account Overview (KPI Cards) -->
            <div class="rpt-kpi-grid">
                <div class="rpt-kpi-card highlight">
                    <div>
                        <div class="rpt-kpi-label" style="color: #047857;">Total Credits (Receivable)</div>
                        <div class="rpt-kpi-val emerald">{{ number_format($totalCredit, 2) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Debits (Payable)</div>
                        <div class="rpt-kpi-val crimson">{{ number_format($totalDebit, 2) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-red">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Current Net Balance</div>
                        <div class="rpt-kpi-val font-weight-bold" style="color: {{ $balance > 0 ? '#047857' : ($balance < 0 ? '#dc2626' : 'var(--coa-navy)') }};">
                            {{ $balance > 0 ? '+' : '' }}{{ number_format($balance, 2) }}
                        </div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-blue">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Movements / Entries</div>
                        <div class="rpt-kpi-val">{{ $recentTransactions->count() }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-gray">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
            </div>

            <!-- 3. Breakdown Cards: Payable vs Receivable -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                        <div class="card-header bg-white border-bottom py-2">
                            <h6 class="mb-0 font-weight-bold text-danger">
                                <i class="fas fa-arrow-circle-up mr-1"></i> We Owe To (Payables)
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            @if ($owingTransactions->isEmpty())
                                <p class="text-muted small mb-0">No outstanding payables.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0" style="font-size: 12px;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Branch</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($owingTransactions as $branchId => $transactions)
                                                @php $branch = $transactions->first()?->relatedBranch; @endphp
                                                <tr>
                                                    <td>{{ $branch?->name ?? 'Branch #' . $branchId }}</td>
                                                    <td class="text-end text-danger font-monospace font-weight-bold">
                                                        {{ number_format($transactions->sum('amount'), 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Branches That Owe To Us -->
                <div class="col-md-6">
                    <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                        <div class="card-header bg-white border-bottom py-2">
                            <h6 class="mb-0 font-weight-bold text-success">
                                <i class="fas fa-arrow-circle-down mr-1"></i> They Owe Us (Receivables)
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            @if ($dueTransactions->isEmpty())
                                <p class="text-muted small mb-0">No outstanding receivables.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0" style="font-size: 12px;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Branch</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($dueTransactions as $branchId => $transactions)
                                                @php $branch = $transactions->first()?->relatedBranch; @endphp
                                                <tr>
                                                    <td>{{ $branch?->name ?? 'Branch #' . $branchId }}</td>
                                                    <td class="text-end text-success font-monospace font-weight-bold">
                                                        {{ number_format($transactions->sum('amount'), 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Recent Transactions -->
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-header bg-white border-bottom py-2">
                    <h6 class="mb-0 font-weight-bold" style="color: var(--coa-navy);">
                        <i class="fas fa-history mr-1" style="color: var(--coa-gold);"></i> Recent Transactions
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" style="font-size: 12.5px;">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th class="text-center" style="width: 100px;">Type</th>
                                    <th class="text-end" style="width: 150px;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                        <td>{{ $transaction->display_description }}</td>
                                        <td class="text-center">
                                            @if ($transaction->type === 'debit')
                                                <span class="badge badge-danger px-2 py-1">Debit</span>
                                            @else
                                                <span class="badge badge-success px-2 py-1">Credit</span>
                                            @endif
                                        </td>
                                        <td class="text-end font-monospace font-weight-bold {{ $transaction->type === 'debit' ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($transaction->display_amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No transactions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
