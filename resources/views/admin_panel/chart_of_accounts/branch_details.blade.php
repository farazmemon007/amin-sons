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
        gap: 10px;
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

    .btn-coa-back {
        background: rgba(255, 255, 255, 0.08);
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.15);
        padding: 7px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
        text-decoration: none !important;
    }

    .btn-coa-back:hover {
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff !important;
    }

    /* ── 2. KPI Summary Grid ── */
    .coa-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 18px;
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
        padding: 13px 16px;
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
        font-size: 20px;
    }

    .coa-kpi-val.opening {
        color: #475569;
        font-family: monospace;
        font-size: 17px;
    }

    .coa-kpi-icon {
        width: 36px;
        height: 36px;
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

    /* ── 3. Branch Selector & Filter Bar ── */
    .coa-toolbar {
        background: #ffffff;
        border-radius: 8px;
        padding: 10px 16px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .coa-filter-pills {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .filter-pill {
        padding: 4px 10px;
        border-radius: 5px;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
        transition: all 0.15s;
        user-select: none;
    }

    .filter-pill:hover {
        background: #e2e8f0;
        color: var(--coa-navy);
    }

    .filter-pill.active {
        background: var(--coa-navy);
        color: #ffffff;
        border-color: var(--coa-navy);
    }

    .coa-branch-switcher {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--coa-navy);
    }

    .coa-branch-select {
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        padding: 5px 10px;
        font-size: 12px;
        font-weight: 600;
        color: var(--coa-navy);
        background: #ffffff;
        cursor: pointer;
        outline: none;
    }

    .coa-branch-select:focus {
        border-color: var(--coa-navy);
    }

    /* ── 4. Main Accounts Card & Table ── */
    .coa-card {
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    .coa-card-header {
        background: #f8fafc;
        padding: 12px 18px;
        border-bottom: 1px solid var(--coa-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .coa-card-title {
        font-size: 13.5px;
        font-weight: 800;
        color: var(--coa-navy);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .coa-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .coa-table thead th {
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 14px;
        border-bottom: 1.5px solid #cbd5e1;
        white-space: nowrap;
    }

    .coa-table tbody td {
        padding: 11px 14px;
        font-size: 12.5px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #1e293b;
    }

    .coa-table tbody tr:hover {
        background: #f8fafc;
    }

    .badge-code {
        font-family: monospace;
        font-weight: 700;
        font-size: 11.5px;
        background: #f1f5f9;
        color: var(--coa-navy);
        padding: 3px 8px;
        border-radius: 5px;
        border: 1px solid #cbd5e1;
        display: inline-block;
    }

    .badge-head {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 5px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        text-transform: capitalize;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-type-debit {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    .badge-type-credit {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    .current-bal-badge {
        font-family: monospace;
        font-size: 13.5px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        letter-spacing: -0.01em;
    }

    .current-bal-badge.positive {
        background: #ecfdf5;
        color: #047857;
        border: 1.5px solid #a7f3d0;
    }

    .current-bal-badge.negative {
        background: #fee2e2;
        color: #b91c1c;
        border: 1.5px solid #fca5a5;
    }

    .badge-status-active {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #86efac;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    .badge-status-inactive {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    .btn-action-ledger {
        background: var(--coa-navy);
        color: #ffffff !important;
        border: 1px solid var(--coa-navy);
        font-size: 11.5px;
        font-weight: 700;
        padding: 4px 9px;
        border-radius: 5px;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.15s;
    }

    .btn-action-ledger:hover {
        background: var(--coa-navy-dark);
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    .btn-action-edit {
        background: #ffffff;
        color: #475569 !important;
        border: 1px solid #cbd5e1;
        font-size: 11.5px;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.15s;
    }

    .btn-action-edit:hover {
        background: #f1f5f9;
        color: var(--coa-navy) !important;
        border-color: #94a3b8;
    }

    /* ── Empty State ── */
    .coa-empty-box {
        text-align: center;
        padding: 45px 20px;
    }

    .coa-empty-icon {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 12px;
    }

    .coa-empty-title {
        font-size: 14px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
    }

    .coa-empty-desc {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 14px;
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
                        <h4 class="coa-header-title">{{ $branch->name }}</h4>
                        <div class="coa-header-sub">
                            <span><i class="fas fa-map-marker-alt" style="color: var(--coa-gold);"></i> {{ $branch->address ?? 'Branch Code #' . $branch->id }}</span>
                            <span>•</span>
                            <span><i class="fas fa-book" style="color: var(--coa-gold);"></i> Chart of Accounts & Head Balances</span>
                        </div>
                    </div>
                </div>
                <div class="coa-header-actions">
                    <a href="{{ route('view_all') }}" class="btn-coa-back">
                        <i class="fas fa-arrow-left"></i> Back to Branches
                    </a>
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

            {{-- 3. KPI Summary Grid --}}
            <div class="coa-kpi-grid">
                <!-- Card 1: Total Accounts -->
                <div class="coa-kpi-card">
                    <div>
                        <div class="coa-kpi-label">Total Accounts</div>
                        <div class="coa-kpi-val">{{ $allAccounts->count() }}</div>
                    </div>
                    <div class="coa-kpi-icon kpi-icon-blue">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>

                <!-- Card 2: Account Heads -->
                <div class="coa-kpi-card">
                    <div>
                        <div class="coa-kpi-label">Active Heads</div>
                        <div class="coa-kpi-val">{{ $accountsByHead->count() }}</div>
                    </div>
                    <div class="coa-kpi-icon kpi-icon-gold">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>

                <!-- Card 3: Total Opening Balance -->
                <div class="coa-kpi-card">
                    <div>
                        <div class="coa-kpi-label">Opening Balance</div>
                        <div class="coa-kpi-val opening">PKR {{ number_format($totalOpeningBalance, 2) }}</div>
                    </div>
                    <div class="coa-kpi-icon kpi-icon-slate">
                        <i class="fas fa-history"></i>
                    </div>
                </div>

                <!-- Card 4: Current Net Balance (Highlighted) -->
                <div class="coa-kpi-card highlight">
                    <div>
                        <div class="coa-kpi-label" style="color: #047857;">Current Total Balance</div>
                        <div class="coa-kpi-val emerald">PKR {{ number_format($totalCurrentBalance, 2) }}</div>
                    </div>
                    <div class="coa-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-check-double"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Toolbar: Category Filters + Branch Switcher --}}
            <div class="coa-toolbar">
                <div class="coa-filter-pills">
                    <span class="filter-pill active" onclick="filterByHead('all', this)">
                        <i class="fas fa-th-large mr-1"></i> All Accounts ({{ $allAccounts->count() }})
                    </span>
                    @foreach($accountsByHead as $headName => $accounts)
                        <span class="filter-pill" onclick="filterByHead('{{ Str::slug($headName) }}', this)">
                            {{ $headName }} ({{ count($accounts) }})
                        </span>
                    @endforeach
                </div>

                @if($isSuperAdmin && count($branches) > 1)
                    <div class="coa-branch-switcher">
                        <span><i class="fas fa-store"></i> Switch Branch:</span>
                        <select class="coa-branch-select" onchange="if(this.value) window.location.href = '{{ url('branch-accounts') }}/' + this.value;">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $b->id == $branch->id ? 'selected' : '' }}>
                                    🏬 {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            {{-- 5. Main Accounts Card --}}
            <div class="coa-card">
                <div class="coa-card-header">
                    <div class="coa-card-title">
                        <i class="fas fa-list-alt text-primary"></i>
                        <span>Branch Chart of Accounts & Real-Time Balances</span>
                    </div>
                    <small class="text-muted font-weight-bold">{{ $allAccounts->count() }} Accounts Configured</small>
                </div>

                @if($allAccounts->isEmpty())
                    <div class="coa-empty-box">
                        <div class="coa-empty-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="coa-empty-title">No Accounts Created Yet for {{ $branch->name }}</div>
                        <div class="coa-empty-desc">
                            This branch currently has no active ledger accounts. Click the button below to add bank or cash accounts.
                        </div>
                        @can('chart.of.accounts.create')
                            <button type="button" class="btn btn-sm btn-success px-3 fw-bold" data-toggle="modal" data-target="#createAccountModal">
                                <i class="fas fa-plus-circle mr-1"></i> Add First Account
                            </button>
                        @endcan
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="coa-table" id="accountsMainTable">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 14%;">Account Code</th>
                                    <th style="width: 22%;">Account Title</th>
                                    <th style="width: 14%;">Account Head</th>
                                    <th style="width: 10%; text-align: center;">Nature</th>
                                    <th style="width: 12%; text-align: right;">Opening Balance</th>
                                    <th style="width: 15%; text-align: right;">Current Balance</th>
                                    <th style="width: 8%; text-align: center;">Status</th>
                                    <th style="width: 10%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allAccounts as $index => $account)
                                    @php
                                        $headSlug = Str::slug($account->head->name ?? 'general');
                                        $currBal = (float)($account->current_balance ?? $account->opening_balance ?? 0);
                                    @endphp
                                    <tr class="account-item-row" data-head-slug="{{ $headSlug }}">
                                        <td style="color: #94a3b8; font-weight: 600;">
                                            {{ $index + 1 }}
                                        </td>
                                        <td>
                                            <span class="badge-code">{{ $account->account_code ?? 'B' . $branch->id . '-ACC-' . $account->id }}</span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: #0f172a; font-size: 13px;">
                                                {{ $account->title }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge-head">
                                                <i class="fas fa-tag"></i> {{ $account->head->name ?? 'General' }}
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            @if(strtolower($account->type) === 'debit')
                                                <span class="badge-type-debit">
                                                    <i class="fas fa-arrow-up"></i> Debit (Dr)
                                                </span>
                                            @else
                                                <span class="badge-type-credit">
                                                    <i class="fas fa-arrow-down"></i> Credit (Cr)
                                                </span>
                                            @endif
                                        </td>
                                        <td style="text-align: right; font-family: monospace; color: #64748b; font-weight: 600;">
                                            PKR {{ number_format($account->opening_balance ?? 0, 2) }}
                                        </td>
                                        <td style="text-align: right;">
                                            <span class="current-bal-badge {{ $currBal >= 0 ? 'positive' : 'negative' }}">
                                                PKR {{ number_format(abs($currBal), 2) }}
                                                <small style="font-size: 10px; font-weight: 800;">{{ $currBal < 0 ? 'Dr' : 'Cr' }}</small>
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            @if($account->status == 'active' || $account->status == 1 || $account->status === null)
                                                <span class="badge-status-active">
                                                    <i class="fas fa-check-circle"></i> Active
                                                </span>
                                            @else
                                                <span class="badge-status-inactive">
                                                    <i class="fas fa-pause-circle"></i> Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="d-inline-flex gap-1" style="gap: 4px;">
                                                <a href="{{ route('account.ledger', $account->id) }}"
                                                   class="btn-action-ledger"
                                                   title="View Account Ledger">
                                                    <i class="fas fa-book-open"></i> Ledger
                                                </a>
                                                <button type="button" 
                                                        class="btn-action-edit btn-edit-account"
                                                        data-account="{{ json_encode($account) }}"
                                                        data-toggle="modal" 
                                                        data-target="#editAccountModal"
                                                        title="Edit Account Details">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </button>
                                            </div>
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

{{-- Category Filter Script --}}
<script>
    function filterByHead(slug, element) {
        $('.filter-pill').removeClass('active');
        $(element).addClass('active');

        if (slug === 'all') {
            $('.account-item-row').show();
        } else {
            $('.account-item-row').hide();
            $(`.account-item-row[data-head-slug="${slug}"]`).show();
        }
    }

    $(document).ready(function() {
        // Edit Account Modal Handler
        $('.btn-edit-account').on('click', function() {
            const account = $(this).data('account');
            const $modal = $('#editAccountModal');
            
            $modal.find('form').attr('action', `{{ url('coa/account') }}/${account.id}`);
            $modal.find('[name="head_id"]').val(account.head_id);
            $modal.find('[name="title"]').val(account.title);
            $modal.find('[name="type"]').val(account.type);
            $modal.find('[name="opening_balance"]').val(account.opening_balance);
            
            if (account.status == 1 || account.status == 'active' || account.status === null) {
                $modal.find('[name="status"]').prop('checked', true);
            } else {
                $modal.find('[name="status"]').prop('checked', false);
            }
        });
    });
</script>

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
                    <i class="fas fa-plus-circle mr-1"></i> Create Account for {{ $branch->name }}
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
                            <label class="font-weight-bold text-dark small mb-1"><i class="fas fa-building mr-1"></i> Branch</label>
                            <input type="text" class="form-control bg-light font-weight-bold" value="{{ $branch->name }}" readonly style="border-radius: 6px;">
                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
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
                            <input type="text" class="form-control" id="accountTitle" name="title" placeholder="e.g., Meezan Bank / Main Cash Counter" required style="border-radius: 6px;">
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-magic text-warning mr-1"></i> Account code will be automatically generated sequentially for this branch.
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
                                <input type="checkbox" class="custom-control-input" id="accountStatus" name="status" checked>
                                <label class="custom-control-label font-weight-bold text-dark small" for="accountStatus" style="cursor: pointer;">Set as Active Account</label>
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

<!-- Modal: Edit Account -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 10px; overflow: hidden; border: none;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);">
                <h6 class="modal-title font-weight-bold mb-0" id="editAccountLabel">
                    <i class="fas fa-edit mr-1"></i> Edit Account
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4 bg-white">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-dark small mb-1"><i class="fas fa-building mr-1"></i> Branch</label>
                            <input type="text" class="form-control bg-light font-weight-bold" value="{{ $branch->name }}" readonly disabled style="border-radius: 6px;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editAccountHead" class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-sitemap mr-1"></i> Account Head <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="editAccountHead" name="head_id" required style="border-radius: 6px;">
                                @foreach($heads ?? [] as $head)
                                    <option value="{{ $head->id }}">{{ $head->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="editAccountTitle" class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-font mr-1"></i> Account Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="editAccountTitle" name="title" required style="border-radius: 6px;">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editAccountType" class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-exchange-alt mr-1"></i> Account Nature <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="editAccountType" name="type" required style="border-radius: 6px;">
                                <option value="Debit">Debit</option>
                                <option value="Credit">Credit</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editAccountOpeningBalance" class="font-weight-bold text-dark small mb-1">
                                <i class="fas fa-wallet mr-1"></i> Opening Balance
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light font-weight-bold" style="border-radius: 6px 0 0 6px;">PKR</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="editAccountOpeningBalance" name="opening_balance" style="border-radius: 0 6px 6px 0;">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="editAccountStatus" name="status">
                                <label class="custom-control-label font-weight-bold text-dark small" for="editAccountStatus" style="cursor: pointer;">Account Active Status</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-3" data-dismiss="modal" style="border-radius: 5px;">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3" style="background: #1e3a5f; border-color: #1e3a5f; border-radius: 5px;">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
