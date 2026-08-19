@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --coa-navy: #1e3a5f;
        --coa-navy-dark: #0f1f38;
        --coa-navy-light: #2c5282;
        --coa-gold: #c8973a;
        --coa-emerald: #0d9f6e;
        --coa-emerald-light: #ecfdf5;
        --coa-crimson: #dc2626;
        --coa-crimson-light: #fee2e2;
        --coa-bg: #f8fafc;
        --coa-border: #e2e8f0;
    }

    .coa-wrapper {
        padding: 12px 0 30px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* ── 1. Corporate Header Bar ── */
    .coa-header-bar {
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

    .coa-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .coa-header-icon {
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

    .coa-header-title {
        font-size: 18px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        letter-spacing: -0.01em;
        line-height: 1.2;
    }

    .coa-header-sub {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.82);
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .coa-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-coa-head {
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.25);
        padding: 7px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        text-decoration: none !important;
        cursor: pointer;
    }

    .btn-coa-head:hover {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    .btn-coa-account {
        background: linear-gradient(135deg, #0d9f6e 0%, #059669 100%);
        color: #ffffff !important;
        border: 1px solid #059669;
        padding: 7px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        text-decoration: none !important;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(13, 159, 110, 0.3);
    }

    .btn-coa-account:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    /* ── 2. KPI Summary Grid ── */
    .coa-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 22px;
    }

    @media (max-width: 992px) {
        .coa-kpi-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .coa-kpi-grid {
            grid-template-columns: 1fr;
        }
    }

    .coa-kpi-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 14px 16px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.15s, box-shadow 0.15s;
    }

    .coa-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .coa-kpi-card.highlight {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #a7f3d0;
    }

    .coa-kpi-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .coa-kpi-val {
        font-size: 19px;
        font-weight: 800;
        color: var(--coa-navy);
        line-height: 1.2;
    }

    .coa-kpi-val.emerald {
        color: #047857;
        font-family: monospace;
        font-size: 19px;
    }

    .coa-kpi-val.opening {
        color: #475569;
        font-family: monospace;
        font-size: 16px;
    }

    .coa-kpi-icon {
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
    .kpi-icon-gold { background: #fef3c7; color: #d97706; }
    .kpi-icon-slate { background: #f1f5f9; color: #475569; }
    .kpi-icon-emerald { background: #d1fae5; color: #059669; }

    /* ── 3. Branches Cards Grid ── */
    .branches-header-title {
        font-size: 14px;
        font-weight: 800;
        color: var(--coa-navy);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .branches-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 16px;
    }

    .branch-card {
        background: #ffffff;
        border-radius: 9px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
    }

    .branch-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .branch-card-header {
        background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 100%);
        color: #ffffff;
        padding: 13px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .branch-card-icon {
        width: 34px;
        height: 34px;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        color: var(--coa-gold);
        border: 1px solid rgba(200, 151, 58, 0.3);
        flex-shrink: 0;
    }

    .branch-name {
        font-size: 14px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        line-height: 1.2;
    }

    .branch-number {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.75);
        margin-top: 2px;
    }

    .branch-card-body {
        padding: 14px 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .branch-address {
        font-size: 11.5px;
        color: #64748b;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .branch-stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 6px;
        padding: 8px 10px;
        margin-bottom: 12px;
    }

    .bstat-label {
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 2px;
    }

    .bstat-val {
        font-size: 14px;
        font-weight: 800;
        color: var(--coa-navy);
    }

    .bstat-val.balance {
        color: #047857;
        font-family: monospace;
    }

    .btn-branch-view {
        background: var(--coa-navy);
        color: #ffffff !important;
        border: 1px solid var(--coa-navy);
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none !important;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.15s;
    }

    .btn-branch-view:hover {
        background: var(--coa-navy-dark);
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    .badge-branch-status {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    .badge-status-active {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #86efac;
    }

    .badge-status-inactive {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
    }
</style>

<div class="main-content">
    <div class="coa-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Corporate Header Bar --}}
            <div class="coa-header-bar">
                <div class="coa-header-left">
                    <div class="coa-header-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <div>
                        <h4 class="coa-header-title">Chart of Accounts</h4>
                        <div class="coa-header-sub">
                            <span><i class="fas fa-building" style="color: var(--coa-gold);"></i> Central Financial Overview & Multi-Branch Accounts</span>
                        </div>
                    </div>
                </div>
                <div class="coa-header-actions">
                    @can('chart.of.accounts.create')
                        <button type="button" class="btn-coa-head" data-toggle="modal" data-target="#createHeadModal">
                            <i class="fas fa-folder-plus"></i> + Create Account Head
                        </button>
                        <button type="button" class="btn-coa-account" data-toggle="modal" data-target="#createAccountModal">
                            <i class="fas fa-plus-circle"></i> + Create Account
                        </button>
                    @endcan
                </div>
            </div>

            {{-- 2. Alerts if any --}}
            @if (session('success'))
                <div class="alert alert-success py-2 px-3 mb-3 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #10b981 !important; border-radius: 6px;">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger py-2 px-3 mb-3 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #ef4444 !important; border-radius: 6px;">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                </div>
            @endif

            {{-- 3. Organization Overview KPI Grid --}}
            <div class="coa-kpi-grid">
                <!-- Card 1: Total Branches -->
                <div class="coa-kpi-card">
                    <div>
                        <div class="coa-kpi-label">Total Branches</div>
                        <div class="coa-kpi-val">{{ $totalOrgBranches ?? count((array)$branchesWithTotals) }}</div>
                    </div>
                    <div class="coa-kpi-icon kpi-icon-blue">
                        <i class="fas fa-store-alt"></i>
                    </div>
                </div>

                <!-- Card 2: Total Accounts -->
                <div class="coa-kpi-card">
                    <div>
                        <div class="coa-kpi-label">Total Accounts</div>
                        <div class="coa-kpi-val">{{ $totalOrgAccounts ?? collect($branchesWithTotals)->sum('accounts_count') }}</div>
                    </div>
                    <div class="coa-kpi-icon kpi-icon-gold">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>

                <!-- Card 3: Total Opening Balance -->
                <div class="coa-kpi-card">
                    <div>
                        <div class="coa-kpi-label">Opening Balance</div>
                        <div class="coa-kpi-val opening">PKR {{ number_format($totalOrgOpening ?? collect($branchesWithTotals)->sum('opening_balance'), 2) }}</div>
                    </div>
                    <div class="coa-kpi-icon kpi-icon-slate">
                        <i class="fas fa-history"></i>
                    </div>
                </div>

                <!-- Card 4: Organization Current Net Balance (Highlighted) -->
                <div class="coa-kpi-card highlight">
                    <div>
                        <div class="coa-kpi-label" style="color: #047857;">Organization Balance</div>
                        <div class="coa-kpi-val emerald">PKR {{ number_format($totalOrgBalance ?? collect($branchesWithTotals)->sum('total_balance'), 2) }}</div>
                    </div>
                    <div class="coa-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Branches Section --}}
            <div class="branches-header-title">
                <i class="fas fa-cubes" style="color: var(--coa-gold);"></i>
                <span>Operating Branches & Individual Account Ledgers</span>
            </div>

            @if(count((array)$branchesWithTotals) > 0)
                <div class="branches-grid">
                    @foreach($branchesWithTotals as $branch)
                        <div class="branch-card">
                            <!-- Card Header -->
                            <div class="branch-card-header">
                                <div class="branch-card-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h5 class="branch-name">{{ $branch['name'] }}</h5>
                                    <div class="branch-number">
                                        <i class="fas fa-phone-alt mr-1"></i> {{ $branch['number'] ?? 'Branch #' . $branch['id'] }}
                                    </div>
                                </div>
                                <span class="badge-branch-status {{ $branch['status'] == 'active' || $branch['status'] == 1 || $branch['status'] == true ? 'badge-status-active' : 'badge-status-inactive' }}">
                                    <i class="fas {{ $branch['status'] == 'active' || $branch['status'] == 1 || $branch['status'] == true ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                    {{ $branch['status'] == 'active' || $branch['status'] == 1 || $branch['status'] == true ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <!-- Card Body -->
                            <div class="branch-card-body">
                                <div class="branch-address">
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    <span>{{ $branch['address'] ?? 'No address registered' }}</span>
                                </div>

                                <div class="branch-stats-grid">
                                    <div>
                                        <div class="bstat-label">Accounts</div>
                                        <div class="bstat-val">{{ $branch['accounts_count'] }} Accounts</div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div class="bstat-label">Current Balance</div>
                                        <div class="bstat-val balance">
                                            PKR {{ number_format($branch['total_balance'], 2) }}
                                        </div>
                                    </div>
                                </div>

                                <a href="{{ route('branch.accounts', $branch['id']) }}" class="btn-branch-view">
                                    <i class="fas fa-book-open mr-1"></i> View Branch Accounts & Ledger
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white p-4 rounded text-center border">
                    <p class="text-muted mb-0 font-weight-bold">No active branches found.</p>
                </div>
            @endif

        </div>
    </div>
</div>

<!-- Modal: Create Account Head -->
<div class="modal fade" id="createHeadModal" tabindex="-1" aria-labelledby="createHeadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 10px; overflow: hidden; border: none;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);">
                <h6 class="modal-title font-weight-bold mb-0" id="createHeadLabel">
                    <i class="fas fa-folder-plus mr-1"></i> Create Account Head
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('coa.head.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-white">
                    <div class="form-group mb-0">
                        <label for="headName" class="font-weight-bold text-dark small mb-1">
                            Account Head Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="headName" name="name" placeholder="e.g., Bank, Cash, Asset" required style="border-radius: 6px;">
                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle mr-1"></i> Account head is a general category used across all branches to group accounts.
                        </small>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-3" data-dismiss="modal" style="border-radius: 5px;">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3" style="background: #1e3a5f; border-color: #1e3a5f; border-radius: 5px;">
                        <i class="fas fa-check-circle mr-1"></i> Save Head
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Create Account -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-labelledby="createAccountLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 10px; overflow: hidden; border: none;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #0d9f6e 0%, #059669 100%);">
                <h6 class="modal-title font-weight-bold mb-0" id="createAccountLabel">
                    <i class="fas fa-plus-circle mr-1"></i> Create Account
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('coa.account.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-white">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="accountBranch" class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-building mr-1"></i> Select Branch <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="accountBranch" name="branch_id" required style="border-radius: 6px;">
                                <option value="">-- Select Branch --</option>
                                @foreach($branches ?? [] as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="accountHead" class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-sitemap mr-1"></i> Account Head <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="accountHead" name="head_id" required style="border-radius: 6px;">
                                <option value="">-- Select Head --</option>
                                @foreach($heads ?? [] as $head)
                                    <option value="{{ $head->id }}">{{ $head->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="accountTitle" class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-font mr-1"></i> Account Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="accountTitle" name="title" placeholder="e.g., Main Bank Account" required style="border-radius: 6px;">
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-magic text-warning mr-1"></i> Account code will be automatically generated sequentially for the selected branch.
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="accountType" class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-exchange-alt mr-1"></i> Account Nature <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="accountType" name="type" required style="border-radius: 6px;">
                                <option value="Debit">Debit (Asset / Expense / Cash / Bank)</option>
                                <option value="Credit">Credit (Liability / Capital / Revenue)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="accountOpeningBalance" class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-wallet mr-1"></i> Opening Balance
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light font-weight-bold" style="border-radius: 6px 0 0 6px;">PKR</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="accountOpeningBalance" name="opening_balance" placeholder="0.00" value="0.00" style="border-radius: 0 6px 6px 0;">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="accountStatusOverview" name="status" checked>
                                <label class="custom-control-label font-weight-bold text-dark small" for="accountStatusOverview" style="cursor: pointer;">Set as Active Account</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-3" data-dismiss="modal" style="border-radius: 5px;">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success font-weight-bold px-3" style="border-radius: 5px;">
                        <i class="fas fa-save mr-1"></i> Save Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
