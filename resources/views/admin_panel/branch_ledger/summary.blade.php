@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📊 Branch Ledger Summary</h5>
        </div>
        <div class="card-body">
            <!-- Account Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center border-0 bg-success text-white">
                        <div class="card-body">
                            <h6>Total Credits</h6>
                            <h2>{{ number_format($totalCredit, 2) }}</h2>
                            <small>Money we're owed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-0 bg-danger text-white">
                        <div class="card-body">
                            <h6>Total Debits</h6>
                            <h2>{{ number_format($totalDebit, 2) }}</h2>
                            <small>Money we owe</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-0 @if ($balance > 0) bg-info @elseif ($balance < 0) bg-warning @else bg-secondary @endif text-white">
                        <div class="card-body">
                            <h6>Current Balance</h6>
                            <h2>{{ number_format($balance, 2) }}</h2>
                            <small>
                                @if ($balance > 0) Net Receivable @elseif ($balance < 0) Net Payable @else Balanced @endif
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-0 bg-light">
                        <div class="card-body">
                            <h6>Transactions</h6>
                            <h2>{{ $recentTransactions->count() }}</h2>
                            <small>Total movements</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branches We Owe To -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0">🔴 We Owe To (Payable)</h6>
                        </div>
                        <div class="card-body">
                            @if ($owingTransactions->isEmpty())
                                <p class="text-muted">No outstanding payables.</p>
                            @else
                                <table class="table table-sm">
                                    <thead class="table-light">
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
                                                <td class="text-end text-danger">
                                                    <strong>{{ number_format($transactions->sum('amount'), 2) }}</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Branches That Owe To Us -->
                <div class="col-md-6">
                    <div class="card border-0">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">🟢 They Owe Us (Receivable)</h6>
                        </div>
                        <div class="card-body">
                            @if ($dueTransactions->isEmpty())
                                <p class="text-muted">No outstanding receivables.</p>
                            @else
                                <table class="table table-sm">
                                    <thead class="table-light">
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
                                                <td class="text-end text-success">
                                                    <strong>{{ number_format($transactions->sum('amount'), 2) }}</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card border-0">
                <div class="card-header bg-light">
                    <h6 class="mb-0">📜 Recent Transactions</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            {{ $transaction->description }}
                                            @if ($transaction->relatedBranch)
                                                <br>
                                                <small class="text-muted">{{ $transaction->relatedBranch->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($transaction->type === 'debit')
                                                <span class="badge bg-danger">Debit</span>
                                            @else
                                                <span class="badge bg-success">Credit</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <strong class="@if ($transaction->type === 'debit') text-danger @else text-success @endif">
                                                {{ number_format($transaction->amount, 2) }}
                                            </strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            No transactions yet.
                                        </td>
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
