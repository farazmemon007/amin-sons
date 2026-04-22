@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --primary: #6366f1;
        --success: #22c55e;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #0ea5e9;
        --light: #f8fafc;
        --dark: #1e293b;
        --muted: #64748b;
        --border: #e2e8f0;
    }

    .page-header {
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        color: white;
        padding: 40px 0;
        margin-bottom: 40px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
    }

    .page-title-section {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .page-icon {
        font-size: 2.5rem;
        opacity: 0.9;
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    .page-subtitle {
        font-size: 0.95rem;
        opacity: 0.85;
        margin-top: 5px;
    }

    .page-header-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .page-header-actions .btn {
        background: rgba(255, 255, 255, 0.95) !important;
        color: #333 !important;
        border: 2px solid white !important;
        font-weight: 600;
        padding: 12px 20px;
        border-radius: 6px;
        transition: all 0.2s;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none !important;
    }

    .page-header-actions .btn:hover {
        background: white !important;
        border-color: white !important;
        color: #6366f1 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .branches-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }

    .branch-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .branch-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        border-color: var(--primary);
    }

    .branch-card-header {
        background: linear-gradient(135deg, var(--primary), #8b5cf6);
        color: white;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .branch-card-icon {
        font-size: 2rem;
        opacity: 0.8;
    }

    .branch-card-title {
        flex: 1;
    }

    .branch-name {
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
    }

    .branch-number {
        font-size: 0.85rem;
        opacity: 0.85;
        margin-top: 3px;
    }

    .branch-card-body {
        padding: 20px;
    }

    .branch-address {
        font-size: 0.9rem;
        color: var(--muted);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .branch-address i {
        color: var(--info);
    }

    .branch-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--border);
    }

    .stat-item {
        text-align: center;
    }

    .stat-label {
        font-size: 0.75rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
        font-weight: 600;
    }

    .stat-value {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--dark);
    }

    .stat-value.balance {
        color: var(--success);
    }

    .branch-actions {
        display: flex;
        gap: 10px;
    }

    .btn-view-accounts {
        flex: 1;
        background: var(--primary);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 0.9rem;
    }

    .btn-view-accounts:hover {
        background: #5558e3;
        color: white;
        text-decoration: none;
    }

    .btn-view-accounts i {
        font-size: 1rem;
    }

    .branch-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
    }

    .branch-status.active {
        background: rgba(34, 197, 94, 0.1);
        color: var(--success);
    }

    .branch-status.inactive {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: var(--muted);
    }

    .empty-state-icon {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.3;
    }

    .empty-state-text {
        font-size: 1.1rem;
        margin: 0;
    }

    .summary-section {
        background: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 40px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }

    .summary-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .summary-card {
        text-align: center;
        padding: 20px;
        background: var(--light);
        border-radius: 8px;
        border-left: 4px solid var(--primary);
    }

    .summary-label {
        font-size: 0.85rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .summary-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--dark);
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 30px 0;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .branches-grid {
            grid-template-columns: 1fr;
        }

        .branch-stats {
            grid-template-columns: 1fr 1fr;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px;">
                <div class="page-title-section">
                    <i class="fas fa-sitemap page-icon"></i>
                    <div>
                        <h1 class="page-title">Chart of Accounts</h1>
                        <p class="page-subtitle">View and manage accounts across all branches</p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                @can('chart.of.accounts.create')
                <div class="page-header-actions">
                    <!-- Create Account Head Button -->
                    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#createHeadModal">
                        <i class="fas fa-plus"></i> Create Account Head
                    </button>
                    
                    <!-- Create Account Button -->
                    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                        <i class="fas fa-plus"></i> Create Account
                    </button>
                </div>
                @endcan
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Summary Section -->
        <div class="summary-section">
            <h3 class="summary-title">
                <i class="fas fa-chart-bar"></i> Organization Overview
            </h3>
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-label">Total Branches</div>
                    <div class="summary-value">{{ count((array)$branchesWithTotals) }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Total Accounts</div>
                    <div class="summary-value">{{ $branchesWithTotals->sum('accounts_count') }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Organization Balance</div>
                    <div class="summary-value">
                        PKR {{ number_format($branchesWithTotals->sum('total_balance'), 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Branches Grid -->
        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--dark); margin-bottom: 20px;">
            <i class="fas fa-sitemap" style="margin-right: 10px; color: var(--primary);"></i> Branches
        </h2>

        @if(count((array)$branchesWithTotals) > 0)
            <div class="branches-grid">
                @foreach($branchesWithTotals as $branch)
                    <div class="branch-card">
                        <!-- Card Header -->
                        <div class="branch-card-header">
                            <i class="fas fa-building branch-card-icon"></i>
                            <div class="branch-card-title">
                                <h3 class="branch-name">{{ $branch['name'] }}</h3>
                                <p class="branch-number">{{ $branch['number'] ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="branch-card-body">
                            <!-- Address -->
                            <div class="branch-address">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ $branch['address'] ?? 'No address' }}</span>
                            </div>

                            <!-- Statistics -->
                            <div class="branch-stats">
                                <div class="stat-item">
                                    <div class="stat-label">Accounts</div>
                                    <div class="stat-value">{{ $branch['accounts_count'] }}</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-label">Balance</div>
                                    <div class="stat-value balance">
                                        PKR {{ number_format($branch['total_balance'], 0) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Status & Actions -->
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                                <span class="branch-status {{ $branch['status'] == 'active' || $branch['status'] == 1 || $branch['status'] == true ? 'active' : 'inactive' }}">
                                    <i class="fas {{ $branch['status'] == 'active' || $branch['status'] == 1 || $branch['status'] == true ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                    {{ $branch['status'] == 'active' || $branch['status'] == 1 || $branch['status'] == true ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <!-- View Accounts Button -->
                            <div class="branch-actions" style="margin-top: 15px;">
                                <a href="{{ route('branch.accounts', $branch['id']) }}" class="btn-view-accounts">
                                    <i class="fas fa-list"></i> View Accounts
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="branches-grid">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <p class="empty-state-text">No active branches found</p>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal: Create Account Head -->
<div class="modal fade" id="createHeadModal" tabindex="-1" aria-labelledby="createHeadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createHeadLabel"><i class="fas fa-plus-circle"></i> Create Account Head</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('coa.head.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="headName" class="form-label fw-bold">Account Head Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="headName" name="name" placeholder="e.g., Bank, Cash, Asset" required>
                        <small class="text-muted">Account head is a category for grouping similar accounts</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Head</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Create Account -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-labelledby="createAccountLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createAccountLabel"><i class="fas fa-plus-circle"></i> Create Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('coa.account.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="accountBranch" class="form-label fw-bold">Branch <span class="text-danger">*</span></label>
                            <select class="form-select" id="accountBranch" name="branch_id" required>
                                <option value="">-- Select Branch --</option>
                                @foreach($branches ?? [] as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="accountHead" class="form-label fw-bold">Account Head <span class="text-danger">*</span></label>
                            <select class="form-select" id="accountHead" name="head_id" required>
                                <option value="">-- Select Head --</option>
                                @foreach($heads ?? [] as $head)
                                    <option value="{{ $head->id }}">{{ $head->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="accountTitle" class="form-label fw-bold">Account Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="accountTitle" name="title" placeholder="e.g., Main Bank Account" required>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle"></i> Account code will be auto-generated based on head and branch
                            </small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="accountType" class="form-label fw-bold">Account Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="accountType" name="type" required>
                                <option value="">-- Select Type --</option>
                                <option value="Debit">Debit</option>
                                <option value="Credit">Credit</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="accountOpeningBalance" class="form-label fw-bold">Opening Balance</label>
                            <input type="number" step="0.01" class="form-control" id="accountOpeningBalance" name="opening_balance" placeholder="0.00" value="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
