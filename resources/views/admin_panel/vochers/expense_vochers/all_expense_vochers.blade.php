@extends('admin_panel.layout.app')
@section('content')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
    .custom-compact-table { font-size: 13px; }
    .custom-compact-table th, .custom-compact-table td {
        padding: 8px 10px !important;
        vertical-align: middle !important;
        white-space: normal !important;
        word-break: break-word;
    }
    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
        justify-content: flex-start;
    }
    .action-buttons form { margin: 0; }
</style>

<div class="main-content">
    <div class="container-fluid">
        @if (session()->has('success'))
            <div class="alert alert-success mt-2"><strong>Success!</strong> {{ session('success') }}</div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger mt-2"><strong>Error!</strong> {{ session('error') }}</div>
        @endif

        <div class="card-header mt-2 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">📋 Expense Vouchers</h4>
            @can('expense.voucher.create')
            <a class="btn btn-primary" href="{{ route('expense-vochers') }}"><i class="bi bi-plus-lg me-1"></i> Add Expense Voucher</a>
            @endcan
        </div>
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive mt-4 mb-4">
                    <table id="expenseTable" class="table table-bordered table-striped custom-compact-table" style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Voucher No</th>
                                <th>Expense Date</th>
                                <th>Source Type</th>
                                <th>Source Account</th>
                                <th>Reference No</th>
                                <th>Remarks</th>
                                <th>Total Amount</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receipts as $item)
                            @php
                            $amounts = json_decode($item->amount, true);
                            $amount = is_array($amounts) ? (float)($amounts[0] ?? 0) : (float)$item->amount;

                            $refs = json_decode($item->reference_no, true);
                            $reference = is_array($refs) ? implode(', ', $refs) : $item->reference_no;

                            $isVoided = ($item->status === 'voided');
                            @endphp
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td><strong>{{ $item->evid }}</strong></td>
                                <td>{{ $item->entry_date }}</td>
                                <td><span class="badge bg-secondary text-white">{{ $item->type_label }}</span></td>
                                <td><strong>{{ $item->party_name }}</strong></td>
                                <td>{{ $reference ?: '-' }}</td>
                                <td>{{ $item->remarks ?: '-' }}</td>
                                <td>
                                    <strong style="color: {{ $isVoided ? '#94a3b8; text-decoration: line-through;' : '#dc2626;' }};">
                                        PKR {{ number_format((float)$item->total_amount, 2) }}
                                    </strong>
                                </td>
                                <td>{{ $item->created_at }}</td>
                                <td>
                                    <div class="action-buttons">
                                        @if(!$isVoided)
                                            <a href="{{ route('expenseVoucher.print', $item->id) }}"
                                                target="_blank"
                                                class="btn btn-sm btn-danger" title="Print Voucher">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            @can('expense.voucher.create')
                                                <a href="{{ route('expense-vouchers.edit', $item->id) }}"
                                                    class="btn btn-sm btn-primary" title="Edit Voucher">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <form action="{{ route('expense-vouchers.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to VOID this expense voucher? All ledger entries will be reversed.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Void Expense">
                                                        <i class="bi bi-slash-circle"></i> Void
                                                    </button>
                                                </form>
                                            @endcan
                                        @else
                                            <span class="badge bg-danger">Voided</span>
                                            <a href="{{ route('expenseVoucher.print', $item->id) }}"
                                                target="_blank"
                                                class="btn btn-sm btn-secondary" title="Print Voided Voucher">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#expenseTable').DataTable({
        "order": [[ 0, "desc" ]]
    });
});
</script>
@endsection
