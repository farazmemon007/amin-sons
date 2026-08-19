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

    #branchesTable {
        border-collapse: collapse;
        width: 100%;
    }

    #branchesTable thead th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 12px;
        border: 1px solid #1e3a5f;
    }

    #branchesTable tbody td {
        padding: 9px 12px;
        vertical-align: middle;
        border: 1px solid #e2e8f0;
    }

    #branchesTable tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    #branchesTable tbody tr:hover {
        background-color: #f1f5f9 !important;
    }

    .badge-owed {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
    }

    .badge-owing {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
    }

    .badge-balanced {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
    }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">
            <!-- Authorization Alert -->
            @if (!auth()->user()->hasRole('super admin'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Note:</strong> You are viewing your branch ledger only. Super Admin can view all branches.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- 1. Corporate Header Bar -->
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">
                            @if (auth()->user()->hasRole('super admin'))
                                Branch Ledger Overview (All Branches)
                            @else
                                My Branch Ledger
                            @endif
                        </h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-network-wired mr-1" style="color: var(--coa-gold);"></i>
                                @if (auth()->user()->hasRole('super admin'))
                                    Inter-branch transactions, running balances & settlements &mdash; Ameen & Sons Corporate ERP
                                @else
                                    Your branch inter-branch transaction balance & history &mdash; Ameen & Sons Corporate ERP
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Summary Statistics -->
            <div class="rpt-kpi-grid">
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Branches</div>
                        <div class="rpt-kpi-val">{{ count($branches) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-blue">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card highlight">
                    <div>
                        <div class="rpt-kpi-label" style="color: #047857;">Owed To Us</div>
                        <div class="rpt-kpi-val emerald">{{ count(array_filter($branches, fn($b) => $b['status'] === 'owed')) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">We Owe</div>
                        <div class="rpt-kpi-val crimson">{{ count(array_filter($branches, fn($b) => $b['status'] === 'owing')) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-red">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Balanced</div>
                        <div class="rpt-kpi-val" style="color: #64748b;">{{ count(array_filter($branches, fn($b) => $b['status'] === 'balanced')) }}</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-gray">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                </div>
            </div>

            <!-- 3. Branches Table -->
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="branchesTable" class="table table-bordered align-middle mb-0" style="font-size: 12.5px;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 45px;">#</th>
                                    <th style="min-width: 180px;">Branch Name</th>
                                    <th style="width: 15%;" class="text-end">Total Credit</th>
                                    <th style="width: 15%;" class="text-end">Total Debit</th>
                                    <th style="width: 16%;" class="text-end">Balance</th>
                                    <th style="width: 12%;" class="text-center">Status</th>
                                    <th style="width: 180px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($branches as $branch)
                                    <tr>
                                        <td class="text-center text-muted font-weight-bold" style="font-size: 11.5px;">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fas fa-store-alt text-muted" style="font-size: 12px;"></i>
                                                <strong class="text-dark" style="font-size: 13px;">{{ $branch['name'] }}</strong>
                                            </div>
                                        </td>
                                        <td class="text-end font-monospace text-success font-weight-bold" style="font-size: 12.5px;">
                                            {{ number_format($branch['totalCredit'], 2) }}
                                        </td>
                                        <td class="text-end font-monospace text-danger font-weight-bold" style="font-size: 12.5px;">
                                            {{ number_format($branch['totalDebit'], 2) }}
                                        </td>
                                        <td class="text-end font-monospace font-weight-bold" style="font-size: 13px;">
                                            @if ($branch['balance'] > 0)
                                                <span class="text-success">+{{ number_format($branch['balance'], 2) }}</span>
                                                <small class="text-muted d-block" style="font-size: 10px; font-family: sans-serif;">(We're owed)</small>
                                            @elseif ($branch['balance'] < 0)
                                                <span class="text-danger">{{ number_format($branch['balance'], 2) }}</span>
                                                <small class="text-muted d-block" style="font-size: 10px; font-family: sans-serif;">(We owe)</small>
                                            @else
                                                <span class="text-muted">0.00</span>
                                                <small class="text-muted d-block" style="font-size: 10px; font-family: sans-serif;">(Balanced)</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($branch['status'] === 'owed')
                                                <span class="badge-owed">
                                                    <i class="fas fa-check-circle mr-1"></i> Owed
                                                </span>
                                            @elseif ($branch['status'] === 'owing')
                                                <span class="badge-owing">
                                                    <i class="fas fa-times-circle mr-1"></i> Owing
                                                </span>
                                            @else
                                                <span class="badge-balanced">
                                                    <i class="fas fa-balance-scale mr-1"></i> Balanced
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <a href="{{ route('branch_ledger_view_branch', $branch['id']) }}" 
                                                   class="btn btn-sm btn-primary font-weight-bold" style="background: var(--coa-navy); border-color: var(--coa-navy); padding: 4px 10px; font-size: 11.5px; border-radius: 5px;" title="View Ledger">
                                                    <i class="fas fa-list mr-1"></i> Ledger
                                                </a>
                                                <a href="{{ route('branch_ledger_transfer_details', $branch['id']) }}" 
                                                   class="btn btn-sm btn-outline-info font-weight-bold" style="padding: 4px 10px; font-size: 11.5px; border-radius: 5px;" title="View Transfers">
                                                    <i class="fas fa-exchange-alt mr-1"></i> Transfers
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox text-muted" style="font-size: 2em;"></i>
                                            <p class="text-muted mt-2 mb-0">No branches found in the system.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Table Footer Summary -->
                <div class="card-footer bg-light border-top p-3">
                    <div class="row align-items-center g-2 text-center text-md-start">
                        <div class="col-md-4">
                            <span class="text-muted small text-uppercase font-weight-bold d-block">Total Credits (Money Owed to Us)</span>
                            <span class="text-success font-monospace font-weight-bold" style="font-size: 15px;">
                                Rs. {{ number_format(array_sum(array_column($branches, 'totalCredit')), 2) }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small text-uppercase font-weight-bold d-block">Total Debits (Money We Owe)</span>
                            <span class="text-danger font-monospace font-weight-bold" style="font-size: 15px;">
                                Rs. {{ number_format(array_sum(array_column($branches, 'totalDebit')), 2) }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small text-uppercase font-weight-bold d-block">Net Balance Across All Branches</span>
                            @php
                                $netBalance = array_sum(array_column($branches, 'balance'));
                            @endphp
                            <span class="font-monospace font-weight-bold @if ($netBalance > 0) text-success @elseif ($netBalance < 0) text-danger @else text-muted @endif" style="font-size: 15px;">
                                {{ $netBalance > 0 ? '+' : '' }}Rs. {{ number_format($netBalance, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
