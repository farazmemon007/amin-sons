@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <!-- Alert Section -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">💳 Inter-Branch Vouchers</h5>
                    <div class="d-flex gap-2">
                        @can('inter.branch.voucher.create')
                            <a href="{{ route('inter_branch_vouchers.create_payment') }}" class="btn btn-sm btn-success text-white">
                                💵 Create Payment Voucher
                            </a>
                            <a href="{{ route('inter_branch_vouchers.create_receipt') }}" class="btn btn-sm btn-info text-white">
                                🏦 Create Receipt Voucher
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    @if ($vouchers->isEmpty())
                        <div class="alert alert-info">
                            No vouchers found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Voucher ID</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>From Branch</th>
                                        <th>To Branch</th>
                                        <th class="text-end">Amount</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Created By</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($vouchers as $voucher)
                                        <tr>
                                            <td><strong>#{{ $voucher->id }}</strong></td>
                                            <td>{{ $voucher->created_at->format('M d, Y') }}</td>
                                            <td>
                                                @if ($voucher->type === 'payment')
                                                    <span class="badge bg-danger text-white">Payment</span>
                                                @else
                                                    <span class="badge bg-success text-white">Receipt</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $voucher->fromBranch->name ?? $voucher->fromBranch->branch_name ?? 'Branch #' . $voucher->from_branch_id }}
                                            </td>
                                            <td>
                                                {{ $voucher->toBranch->name ?? $voucher->toBranch->branch_name ?? 'Branch #' . $voucher->to_branch_id }}
                                            </td>
                                            <td class="text-end font-weight-bold text-success">
                                                {{ number_format($voucher->amount, 2) }}
                                            </td>
                                            <td>
                                                <span class="text-capitalize">{{ $voucher->method }}</span>
                                            </td>
                                            <td>{{ $voucher->reference ?? '-' }}</td>
                                            <td>{{ $voucher->createdBy->name ?? 'System' }}</td>
                                            <td><small class="text-muted">{{ $voucher->remarks ?? '-' }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $vouchers->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
