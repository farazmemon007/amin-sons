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

    /* ERP Modal Styling - Premium Look */
    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        overflow: hidden;
    }
    .modal-header {
        padding: 22px 30px;
        border-bottom: none;
    }
    .modal-title {
        font-weight: 700;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .modal-body {
        padding: 35px 30px;
        background: #fff;
    }
    .form-label {
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-label i {
        color: var(--primary);
        font-size: 1rem;
        width: 20px;
        text-align: center;
    }
    .form-control, .form-select {
        border: 2px solid #edf2f7;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 0.95rem;
        color: #2d3748;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        background-color: #f8fafc;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        outline: none;
    }
    .modal-footer {
        padding: 20px 30px 30px;
        border-top: 1px solid #f1f5f9;
        background: #f8fafc;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    .modal-footer .btn {
        padding: 12px 24px;
        font-weight: 700;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
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
                    <button type="button" class="btn" data-toggle="modal" data-target="#createHeadModal">
                        <i class="fas fa-plus"></i> Create Account Head
                    </button>
                    
                    <button type="button" class="btn" data-toggle="modal" data-target="#createAccountModal">
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
                                             <td style="width: 15%; text-align: center;">
                                                <div style="display: flex; gap: 6px; justify-content: center;">
                                                    <a href="{{ route('account.ledger', $account->id) }}"
                                                       class="btn btn-sm"
                                                       style="background:linear-gradient(135deg,#6366f1,#8b5cf6); color:white; border-radius:6px; font-size:0.78rem; font-weight:600; padding:5px 12px; white-space:nowrap; text-decoration:none;"
                                                       title="View Account Ledger">
                                                        <i class="fas fa-book-open"></i> Ledger
                                                    </a>
                                                    
                                                    <button type="button" 
                                                            class="btn btn-sm btn-edit-account"
                                                            style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; border-radius:6px; font-size:0.78rem; font-weight:600; padding:5px 12px;"
                                                            data-account="{{ json_encode($account) }}"
                                                            data-toggle="modal" 
                                                            data-target="#editAccountModal">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                </div>
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

    // ✅ Account Edit Logic
    $('.btn-edit-account').on('click', function() {
        const account = $(this).data('account');
        const $modal = $('#editAccountModal');
        
        // Set Form Action
        $modal.find('form').attr('action', `{{ url('coa/account') }}/${account.id}`);
        
        // Fill Fields
        $modal.find('[name="head_id"]').val(account.head_id);
        $modal.find('[name="title"]').val(account.title);
        $modal.find('[name="type"]').val(account.type);
        $modal.find('[name="opening_balance"]').val(account.opening_balance);
        
        // Set Status Switch
        if (account.status == 1 || account.status == 'active') {
            $modal.find('[name="status"]').prop('checked', true);
        } else {
            $modal.find('[name="status"]').prop('checked', false);
        }
        
        console.log('✏️ Editing Account:', account);
    });
});
</script>

<!-- Modal: Create Account Head -->
<div class="modal fade" id="createHeadModal" tabindex="-1" aria-labelledby="createHeadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createHeadLabel"><i class="fas fa-plus-circle"></i> Create Account Head</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('coa.head.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="headName" class="form-label"><i class="fas fa-tag"></i> Account Head Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="headName" name="name" placeholder="e.g., Bank, Cash, Asset" required>
                        <p class="text-muted mt-2 mb-0" style="font-size: 0.8rem;">
                            <i class="fas fa-info-circle"></i> Account head is a high-level category used to group similar accounts.
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Create Head</button>
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
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('coa.account.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label"><i class="fas fa-building"></i> Branch</label>
                            <input type="text" class="form-control bg-light" value="{{ $branch->name }}" readonly>
                            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="accountHead" class="form-label"><i class="fas fa-sitemap"></i> Account Head <span class="text-danger">*</span></label>
                            <select class="form-select" id="accountHead" name="head_id" required>
                                <option value="">-- Select Head --</option>
                                @foreach($heads ?? [] as $head)
                                    <option value="{{ $head->id }}">{{ $head->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-4">
                            <label for="accountTitle" class="form-label"><i class="fas fa-font"></i> Account Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="accountTitle" name="title" placeholder="e.g., Main Bank Account" required>
                            <div class="mt-2 d-flex align-items-center gap-2" style="font-size: 0.8rem; color: #64748b;">
                                <i class="fas fa-magic text-warning"></i> 
                                <span>Account code will be auto-generated sequentially for this branch.</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="accountType" class="form-label"><i class="fas fa-exchange-alt"></i> Account Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="accountType" name="type" required>
                                <option value="">-- Select Type --</option>
                                <option value="Debit">Debit (Increase Asset/Expense)</option>
                                <option value="Credit">Credit (Increase Liability/Income)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="accountOpeningBalance" class="form-label"><i class="fas fa-wallet"></i> Opening Balance</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light fw-bold" style="border: 2px solid #edf2f7; border-right: none; border-radius: 10px 0 0 10px;">PKR</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="accountOpeningBalance" name="opening_balance" placeholder="0.00" value="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="accountStatus" name="status" checked>
                                <label class="custom-control-label fw-bold" for="accountStatus" style="cursor: pointer;">Set as Active Account</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Account -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="editAccountLabel"><i class="fas fa-edit"></i> Edit Account</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label"><i class="fas fa-building"></i> Branch</label>
                            <input type="text" class="form-control bg-light" value="{{ $branch->name }}" readonly disabled>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="editAccountHead" class="form-label"><i class="fas fa-sitemap"></i> Account Head <span class="text-danger">*</span></label>
                            <select class="form-select" id="editAccountHead" name="head_id" required>
                                @foreach($heads ?? [] as $head)
                                    <option value="{{ $head->id }}">{{ $head->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-warning fw-bold d-block mt-1" style="font-size: 0.75rem;">
                                <i class="fas fa-exclamation-triangle"></i> Changing head will regenerate account code!
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-4">
                            <label for="editAccountTitle" class="form-label"><i class="fas fa-font"></i> Account Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editAccountTitle" name="title" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="editAccountType" class="form-label"><i class="fas fa-exchange-alt"></i> Account Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="editAccountType" name="type" required>
                                <option value="Debit">Debit</option>
                                <option value="Credit">Credit</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="editAccountOpeningBalance" class="form-label"><i class="fas fa-wallet"></i> Opening Balance</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light fw-bold" style="border: 2px solid #edf2f7; border-right: none; border-radius: 10px 0 0 10px;">PKR</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="editAccountOpeningBalance" name="opening_balance">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="editAccountStatus" name="status">
                                <label class="custom-control-label fw-bold" for="editAccountStatus" style="cursor: pointer;">Account Active Status</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
