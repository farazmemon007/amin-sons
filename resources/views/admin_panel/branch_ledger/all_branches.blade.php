@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid">
    <!-- Authorization Alert -->
    @if (!auth()->user()->hasRole('super admin'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i>
            <strong>Note:</strong> You are viewing your branch ledger only. Super Admin can view all branches.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Header Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-gradient-primary text-white">
            <h4 class="mb-0">
                <i class="fas fa-building"></i> 
                @if (auth()->user()->hasRole('super admin'))
                    Branch Ledger Overview (All Branches)
                @else
                    My Branch Ledger
                @endif
            </h4>
            <small>
                @if (auth()->user()->hasRole('super admin'))
                    View all branches and their inter-branch transaction balances
                @else
                    View your branch's inter-branch transaction balance and history
                @endif
            </small>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">📊 Total Branches</h6>
                    <h3 class="text-primary">{{ count($branches) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">✓ Owed To Us</h6>
                    <h3 class="text-success">{{ count(array_filter($branches, fn($b) => $b['status'] === 'owed')) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">✗ We Owe</h6>
                    <h3 class="text-danger">{{ count(array_filter($branches, fn($b) => $b['status'] === 'owing')) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">⚖️ Balanced</h6>
                    <h3 class="text-secondary">{{ count(array_filter($branches, fn($b) => $b['status'] === 'balanced')) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Branches Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light border-bottom">
            <h5 class="mb-0">Branch Balances</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 25%">Branch Name</th>
                        <th style="width: 15%">Total Credit</th>
                        <th style="width: 15%">Total Debit</th>
                        <th style="width: 15%">Balance</th>
                        <th style="width: 10%">Status</th>
                        <th style="width: 15%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($branches as $branch)
                        <tr>
                            <td>
                                <span class="badge bg-secondary">{{ $branch['id'] }}</span>
                            </td>
                            <td>
                                <strong>{{ $branch['name'] }}</strong>
                            </td>
                            <td>
                                <span class="text-success">
                                    <i class="fas fa-arrow-down"></i>
                                    {{ number_format($branch['totalCredit'], 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-danger">
                                    <i class="fas fa-arrow-up"></i>
                                    {{ number_format($branch['totalDebit'], 2) }}
                                </span>
                            </td>
                            <td>
                                <h6 class="mb-0">
                                    @if ($branch['balance'] > 0)
                                        <span class="text-success font-weight-bold">
                                            +{{ number_format($branch['balance'], 2) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">(We're owed)</small>
                                    @elseif ($branch['balance'] < 0)
                                        <span class="text-danger font-weight-bold">
                                            {{ number_format($branch['balance'], 2) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">(We owe)</small>
                                    @else
                                        <span class="text-secondary font-weight-bold">
                                            0.00
                                        </span>
                                        <br>
                                        <small class="text-muted">(Balanced)</small>
                                    @endif
                                </h6>
                            </td>
                            <td>
                                @if ($branch['status'] === 'owed')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Owed
                                    </span>
                                @elseif ($branch['status'] === 'owing')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Owing
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-balance-scale"></i> Balanced
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('branch_ledger_view_branch', $branch['id']) }}" 
                                   class="btn btn-sm btn-primary" title="View Ledger">
                                    <i class="fas fa-list"></i> Ledger
                                </a>
                                <a href="{{ route('branch_ledger_transfer_details', $branch['id']) }}" 
                                   class="btn btn-sm btn-info" title="View Transfers">
                                    <i class="fas fa-exchange-alt"></i> Transfers
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox text-muted" style="font-size: 2em;"></i>
                                <p class="text-muted mt-2">No branches found in the system.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer with Summary -->
        <div class="card-footer bg-light">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="text-muted">Total Credits (Money Owed to Us):</h6>
                    <h5 class="text-success">
                        {{ number_format(array_sum(array_column($branches, 'totalCredit')), 2) }}
                    </h5>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Total Debits (Money We Owe):</h6>
                    <h5 class="text-danger">
                        {{ number_format(array_sum(array_column($branches, 'totalDebit')), 2) }}
                    </h5>
                </div>
                <div class="col-md-4">
                    <h6 class="text-muted">Net Balance:</h6>
                    @php
                        $netBalance = array_sum(array_column($branches, 'balance'));
                    @endphp
                    <h5 class="@if ($netBalance > 0) text-success @elseif ($netBalance < 0) text-danger @else text-secondary @endif">
                        {{ number_format($netBalance, 2) }}
                    </h5>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .table-hover tbody tr:hover {
        background-color: #f5f5f5 !important;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
    }
</style>
@endsection
