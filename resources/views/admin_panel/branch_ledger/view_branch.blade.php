@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid">
    <!-- Authorization Check Alert -->
    @if (!auth()->user()->hasRole('super admin') && auth()->user()->branch_id == $branch->id)
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-shield-alt"></i>
            <strong>Your Branch Ledger:</strong> You are viewing ledger entries for your branch.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif (auth()->user()->hasRole('super admin'))
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-crown"></i>
            <strong>Super Admin Access:</strong> You can view all branch ledgers.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header with Back Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4>
                <i class="fas fa-book"></i> Branch Ledger - {{ $branch->name ?? 'Branch #' . $branch->id }}
            </h4>
            <small class="text-muted">Detailed transaction history</small>
        </div>
        {{-- <a href="{{ route('branch_ledger_all_branches') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Branches
        </a> --}}
    </div>

    <!-- Account Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Current Balance</h6>
                    @if ($balance > 0)
                        <h3 class="text-success">+{{ number_format($balance, 2) }}</h3>
                        <small class="text-success"><i class="fas fa-check"></i> We're owed</small>
                    @elseif ($balance < 0)
                        <h3 class="text-danger">{{ number_format($balance, 2) }}</h3>
                        <small class="text-danger"><i class="fas fa-times"></i> We owe</small>
                    @else
                        <h3 class="text-secondary">0.00</h3>
                        <small class="text-secondary"><i class="fas fa-balance-scale"></i> Balanced</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Credits</h6>
                    <h3 class="text-success">{{ number_format($totalCredit, 2) }}</h3>
                    <small class="text-muted">Money owed to us</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Debits</h6>
                    <h3 class="text-danger">{{ number_format($totalDebit, 2) }}</h3>
                    <small class="text-muted">Money we owe</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Transactions</h6>
                    <h3 class="text-primary">{{ $transactions->total() }}</h3>
                    <small class="text-muted">All entries</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-3">
        <a href="{{ route('branch_ledger_transfer_details', $branch->id) }}" 
           class="btn btn-info btn-sm">
            <i class="fas fa-exchange-alt"></i> View Transfer Details
        </a>
        <a href="{{ route('branch_ledger_report') }}?branch_id={{ $branch->id }}" 
           class="btn btn-primary btn-sm">
            <i class="fas fa-file-pdf"></i> Generate Report
        </a>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0">Transaction History</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Related Branch</th>
                        <th>Reference</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th>Created By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>
                                <small class="text-muted">
                                    {{ $transaction->created_at->format('M d, Y H:i') }}
                                </small>
                            </td>
                            <td>
                                <strong>{{ $transaction->display_description }}</strong>
                            </td>
                            <td>
                                @if ($transaction->relatedBranch)
                                    <span class="badge bg-light text-dark">
                                        {{ $transaction->relatedBranch->name ?? 'Branch #' . $transaction->related_branch_id }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ ucfirst(str_replace('_', ' ', $transaction->reference_type)) }}
                                    #{{ $transaction->reference_id }}
                                </small>
                            </td>
                            <td>
                                @if ($transaction->type === 'debit')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-arrow-up"></i> Debit
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="fas fa-arrow-down"></i> Credit
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($transaction->type === 'debit')
                                    <span class="text-danger font-weight-bold">
                                        -{{ number_format($transaction->display_amount, 2) }}
                                    </span>
                                @else
                                    <span class="text-success font-weight-bold">
                                        +{{ number_format($transaction->display_amount, 2) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    @if ($transaction->createdBy)
                                        {{ $transaction->createdBy->name ?? 'System' }}
                                    @else
                                        <span class="text-muted">System</span>
                                    @endif
                                </small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox text-muted" style="font-size: 2em;"></i>
                                <p class="text-muted mt-2">No transactions found for this branch.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($transactions->total() > 0)
            <div class="card-footer bg-light">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

</div>

<style>
    .table-hover tbody tr:hover {
        background-color: #f5f5f5 !important;
    }
</style>
@endsection
