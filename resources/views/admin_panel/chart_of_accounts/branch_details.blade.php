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
        padding: 35px 0;
        margin-bottom: 35px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
    }

    .page-header-top {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: white;
        text-decoration: none;
        opacity: 0.9;
        transition: opacity 0.2s;
        font-size: 0.95rem;
    }

    .back-link:hover {
        opacity: 1;
        color: white;
    }

    .page-title-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .page-icon {
        font-size: 2.2rem;
    }

    .page-title-text h1 {
        font-size: 1.8rem;
        font-weight: 700;
        margin: 0;
    }

    .page-title-text p {
        font-size: 0.9rem;
        opacity: 0.9;
        margin: 5px 0 0 0;
    }

    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 25px;
    }

    .summary-card {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        padding: 18px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .summary-card-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        opacity: 0.85;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .summary-card-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    /* Content Section */
    .content-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .section-header {
        background: linear-gradient(135deg, var(--light), rgba(248, 250, 252, 0.5));
        padding: 20px 25px;
        border-bottom: 2px solid var(--border);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        flex: 1;
    }

    .section-count {
        background: var(--primary);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Account Head Groups */
    .account-head-group {
        border-bottom: 1px solid var(--border);
    }

    .account-head-group:last-child {
        border-bottom: none;
    }

    .head-title-bar {
        background: linear-gradient(90deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.05));
        padding: 15px 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        user-select: none;
        transition: background 0.2s;
    }

    .head-title-bar:hover {
        background: linear-gradient(90deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
    }

    .head-toggle-icon {
        color: var(--primary);
        transition: transform 0.2s;
        font-size: 1rem;
    }

    .head-toggle-icon.collapsed {
        transform: rotate(-90deg);
    }

    .head-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--dark);
        flex: 1;
    }

    .head-account-count {
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary);
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .head-balance {
        font-weight: 700;
        color: var(--dark);
        font-size: 0.95rem;
        min-width: 140px;
        text-align: right;
    }

    /* Accounts List */
    .accounts-table {
        width: 100%;
        border-collapse: collapse;
    }

    .accounts-table tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background 0.15s;
    }

    .accounts-table tbody tr:hover {
        background: var(--light);
    }

    .accounts-table tbody tr:last-child {
        border-bottom: none;
    }

    .account-row td {
        padding: 14px 25px;
        font-size: 0.95rem;
    }

    .account-code {
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: var(--primary);
        font-size: 0.9rem;
    }

    .account-title {
        color: var(--dark);
        font-weight: 500;
    }

    .account-type {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .account-type.debit {
        background: rgba(34, 197, 94, 0.1);
        color: var(--success);
    }

    .account-type.credit {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .account-balance {
        font-weight: 700;
        text-align: right;
        min-width: 120px;
    }

    .account-balance.debit {
        color: var(--success);
    }

    .account-balance.credit {
        color: var(--danger);
    }

    .account-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 4px;
    }

    .account-status.active {
        background: rgba(34, 197, 94, 0.1);
        color: var(--success);
    }

    .account-status.inactive {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: var(--muted);
    }

    .empty-state-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
        opacity: 0.3;
    }

    .empty-state-text {
        font-size: 1rem;
        margin: 0;
    }

    /* Branch Selector */
    .branch-selector {
        display: flex;
        align-items: center;
        gap: 10px;
        background: white;
        padding: 12px 16px;
        border-radius: 8px;
        border: 1px solid var(--border);
    }

    .branch-selector label {
        margin: 0;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.9rem;
    }

    .branch-selector select {
        border: 1px solid var(--border);
        padding: 6px 10px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9rem;
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

    @media (max-width: 768px) {
        .page-header {
            padding: 25px 0;
        }

        .page-title-text h1 {
            font-size: 1.3rem;
        }

        .summary-cards {
            grid-template-columns: 1fr;
        }

        .account-row td {
            padding: 12px 12px;
            font-size: 0.85rem;
        }

        .account-code {
            font-size: 0.8rem;
        }

        .account-balance {
            min-width: 80px;
            font-size: 0.9rem;
        }

        .head-balance {
            min-width: 100px;
            font-size: 0.85rem;
        }
    }

    .hidden-accounts {
        display: none;
    }

    .shown-accounts {
        display: table-row;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="page-header-top">
                <a href="{{ route('view_all') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Branches
                </a>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                <div class="page-title-section">
                    <i class="fas fa-list-ul page-icon"></i>
                    <div class="page-title-text">
                        <h1>{{ $branch->name }}</h1>
                        <p><i class="fas fa-map-marker-alt"></i> {{ $branch->address ?? 'No address on file' }}</p>
                    </div>
                </div>
                
                @can('chart.of.accounts.create')
                <div class="page-header-actions">
                    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#createHeadModal">
                        <i class="fas fa-plus"></i> Create Account Head
                    </button>
                    
                    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                        <i class="fas fa-plus"></i> Create Account
                    </button>
                </div>
                @endcan
            </div>

            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-card-label">Total Accounts</div>
                    <div class="summary-card-value">{{ $branch->accounts()->count() }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-card-label">Account Heads</div>
                    <div class="summary-card-value">{{ count((array)$accountsByHead) }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-card-label">Total Balance</div>
                    <div class="summary-card-value">PKR {{ number_format($totalBalance, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Branch Selector (for super admin to switch branches) -->
        @if($isSuperAdmin && count($branches) > 1)
            <div style="margin-bottom: 25px;">
                <div class="branch-selector">
                    <label for="branch-select">Switch to Branch:</label>
                    <select id="branch-select" onchange="if(this.value) window.location.href = '{{ url('branch-accounts') }}/' + this.value;">
                        <option value="">-- Select a branch --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $b->id == $branch->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        <!-- Accounts by Head -->
        @if(count($heads) > 0)
            <div class="content-section">
                <div class="section-header">
                    <i class="fas fa-book" style="color: var(--primary); font-size: 1.2rem;"></i>
                    <h2 class="section-title">Chart of Accounts</h2>
                    <span class="section-count">{{ $branch->accounts()->count() }} Accounts</span>
                </div>

                @if(count((array)$accountsByHead) > 0)
                    <table class="accounts-table">
                        <tbody>
                            @foreach($accountsByHead as $headName => $accounts)
                                <!-- Account Head Row (Expandable) -->
                                <tr class="head-title-bar" onclick="toggleAccountHead(this)">
                                    <td colspan="6" style="padding: 0;">
                                        <div style="display: flex; align-items: center; gap: 12px; padding: 15px 25px;">
                                            <i class="fas fa-chevron-right head-toggle-icon"></i>
                                            <span class="head-title">{{ $headName }}</span>
                                            <span class="head-account-count">
                                                @if(count($accounts) > 0)
                                                    {{ count($accounts) }} {{ count($accounts) == 1 ? 'Account' : 'Accounts' }}
                                                @else
                                                    <span style="color: #f59e0b;">No Accounts</span>
                                                @endif
                                            </span>
                                            <span class="head-balance" style="margin-left: auto;">
                                                PKR {{ number_format($accounts->sum('opening_balance'), 2) }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Accounts under this head -->
                                @if(count($accounts) > 0)
                                    @foreach($accounts as $account)
                                        <tr class="account-row shown-accounts" data-head="{{ $loop->parent->index }}">
                                            <td style="width: 15%; padding-left: 50px;">
                                                <span class="account-code">{{ $account->account_code ?? 'N/A' }}</span>
                                            </td>
                                            <td style="width: 30%;">
                                                <span class="account-title">{{ $account->title }}</span>
                                            </td>
                                            <td style="width: 15%;">
                                                <span class="account-type {{ strtolower($account->type) }}">
                                                    <i class="fas {{ strtolower($account->type) == 'debit' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                                    {{ ucfirst($account->type ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <td style="width: 18%;">
                                                <span class="account-balance {{ strtolower($account->type) }}">
                                                    PKR {{ number_format($account->opening_balance ?? 0, 2) }}
                                                </span>
                                            </td>
                                            <td style="width: 12%;">
                                                <span class="account-status {{ $account->status == 'active' ? 'active' : 'inactive' }}">
                                                    <i class="fas {{ $account->status == 'active' ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                                    {{ ucfirst($account->status ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <td style="width: 10%; text-align: center;">
                                                <i class="fas fa-chevron-right" style="color: var(--muted); font-size: 0.9rem;"></i>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <p class="empty-state-text">No accounts created. <br><strong>Create an Account</strong> under one of the account heads above.</p>
                    </div>
                @endif
            </div>
        @else
            <div class="content-section">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <p class="empty-state-text">No account heads found. Create one first!</p>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function toggleAccountHead(element) {
    const headIndex = Array.from(document.querySelectorAll('.head-title-bar')).indexOf(element);
    const accounts = document.querySelectorAll(`.account-row[data-head="${headIndex}"]`);
    const icon = element.querySelector('.head-toggle-icon');

    accounts.forEach(account => {
        account.classList.toggle('hidden-accounts');
        account.classList.toggle('shown-accounts');
    });

    icon.classList.toggle('collapsed');
}

// Initially show all accounts
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.account-row').forEach(row => {
        row.classList.add('shown-accounts');
        row.classList.remove('hidden-accounts');
    });
});
</script>

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
                    <!-- Branch Info (auto-filled for branch users) -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="accountBranch" class="form-label fw-bold">Branch <span class="text-danger">*</span></label>
                            <select class="form-select" id="accountBranch" name="branch_id" required>
                                <option value="{{ $branch->id }}" selected>{{ $branch->name }}</option>
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

                    <!-- Account Details -->
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="accountTitle" class="form-label fw-bold">Account Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="accountTitle" name="title" placeholder="e.g., Main Bank Account" required>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle"></i> Account code will be auto-generated based on head and branch
                            </small>
                        </div>
                    </div>

                    <!-- Account Type & Balance -->
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
