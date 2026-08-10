@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">💰 Branch Ledger - {{ $currentBranch->name ?? 'Branch #' . $currentBranch->id }}</h5>
        </div>
        <div class="card-body">
            <!-- Account Summary -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center border-0 bg-light">
                        <div class="card-body">
                            <h6 class="text-muted">Current Balance</h6>
                            <h3 class="text-primary font-weight-bold">
                                {{ number_format($account?->current_balance ?? 0, 2) }}
                            </h3>
                            @if (($account?->current_balance ?? 0) > 0)
                                <small class="text-success">✓ We're owed money</small>
                            @elseif (($account?->current_balance ?? 0) < 0)
                                <small class="text-danger">✗ We owe money</small>
                            @else
                                <small class="text-muted">Balanced</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-0 bg-light">
                        <div class="card-body">
                            <h6 class="text-muted">Total Credits</h6>
                            <h3 class="text-success">{{ number_format($totalCredit, 2) }}</h3>
                            <small class="text-muted">Money we're owed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-0 bg-light">
                        <div class="card-body">
                            <h6 class="text-muted">Total Debits</h6>
                            <h3 class="text-danger">{{ number_format($totalDebit, 2) }}</h3>
                            <small class="text-muted">Money we owe</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mb-4">
                <a href="{{ route('inter_branch_vouchers.create_payment') }}" class="btn btn-sm btn-danger">
                    💳 Record Payment
                </a>
                <a href="{{ route('inter_branch_vouchers.create_receipt') }}" class="btn btn-sm btn-success">
                    💵 Record Receipt
                </a>
                <a href="{{ route('branch_ledger_report') }}" class="btn btn-sm btn-info">
                    📊 Generate Report
                </a>
            </div>

            <!-- Transactions List -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                <td>
                                    {{ $transaction->display_description }}
                                    @if ($transaction->relatedBranch)
                                        <br>
                                        <small class="text-muted">{{ $transaction->relatedBranch->name ?? 'Branch #' . $transaction->related_branch_id }}</small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $transaction->reference_type }} #{{ $transaction->reference_id }}</small>
                                </td>
                                <td>
                                    @if ($transaction->type === 'debit')
                                        <span class="badge bg-danger">Debit</span>
                                    @else
                                        <span class="badge bg-success">Credit</span>
                                    @endif
                                </td>
                                <td>{{ number_format($transaction->display_amount, 2) }}</td>
                                <td><strong>{{ number_format($account?->current_balance ?? 0, 2) }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No transactions yet.
                                </td>
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
@endsection
