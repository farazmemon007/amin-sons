@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">🔍 Detailed Ledger</h5>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" action="{{ route('branch_ledger_detail') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="debit" @if(request()->input('type') === 'debit') selected @endif>Debit (Payable)</option>
                            <option value="credit" @if(request()->input('type') === 'credit') selected @endif>Credit (Receivable)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reference Type</label>
                        <select name="reference_type" class="form-control">
                            <option value="">All References</option>
                            <option value="transfer" @if(request()->input('reference_type') === 'transfer') selected @endif>Stock Transfer</option>
                            <option value="payment" @if(request()->input('reference_type') === 'payment') selected @endif>Payment</option>
                            <option value="receipt" @if(request()->input('reference_type') === 'receipt') selected @endif>Receipt</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request()->input('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request()->input('to_date') }}">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        🔍 Filter
                    </button>
                    <a href="{{ route('branch_ledger_detail') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </form>

            <!-- Summary -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center border-0 bg-light">
                        <div class="card-body">
                            <h6 class="text-muted">Total Debits</h6>
                            <h3 class="text-danger">{{ number_format($summary['totalDebit'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-0 bg-light">
                        <div class="card-body">
                            <h6 class="text-muted">Total Credits</h6>
                            <h3 class="text-success">{{ number_format($summary['totalCredit'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-0 bg-light">
                        <div class="card-body">
                            <h6 class="text-muted">Net Balance</h6>
                            <h3 class="text-primary">{{ number_format($summary['totalCredit'] - $summary['totalDebit'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Related Branch</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $transaction->description }}</td>
                                <td>
                                    {{ $transaction->relatedBranch?->name ?? 'N/A' }}
                                </td>
                                <td>
                                    @if ($transaction->type === 'debit')
                                        <span class="badge bg-danger">Debit</span>
                                    @else
                                        <span class="badge bg-success">Credit</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $transaction->reference_type }} #{{ $transaction->reference_id }}</small>
                                </td>
                                <td class="text-end">
                                    <strong class="@if ($transaction->type === 'debit') text-danger @else text-success @endif">
                                        {{ number_format($transaction->amount, 2) }}
                                    </strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if (method_exists($transactions, 'links'))
                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
