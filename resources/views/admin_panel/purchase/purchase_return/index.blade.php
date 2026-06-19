@extends('admin_panel.layout.app')

@section('content')
@can('purchase.return.view')
<style>
    .main-content { background-color: #f8f9fa; min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .card { border-radius: 12px; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    .table thead th { background-color: #f1f4f9; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: #555; border-bottom: none; padding: 15px 10px; }
    .table tbody td { padding: 14px 10px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
    
    .dataTables_wrapper .dataTables_filter input { border-radius: 8px; border: 1px solid #ddd; padding: 6px 12px; margin-bottom: 10px; }

    /* Modern Thin Scrollbar for ERP */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .table-responsive {
        overflow-x: auto;
        overflow-y: visible !important;
        padding-bottom: 15px;
    }
</style>

<div class="main-content">
    <div class="container-fluid px-4 pt-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Purchase Returns</h4>
                <small class="text-muted">Browse historical debit notes and goods returned to suppliers</small>
            </div>
            <a href="{{ route('Purchase.home') }}" class="btn btn-primary px-4 fw-bold shadow-sm">
                <i class="fa fa-arrow-left me-2"></i> Back to Purchases
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
                <strong><i class="fa fa-check-circle me-2"></i>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card p-0" style="overflow: visible !important;">
            <div class="table-responsive" style="overflow: visible !important;">
                <table id="return-table" class="table table-hover mb-0 w-100">
                    <thead>
                        <tr class="text-center">
                            <th>ID</th>
                            <th>Invoice #</th>
                            <th class="text-start">Vendor / Warehouse</th>
                            <th>Return Date</th>
                            <th>Bill Amount</th>
                            <th>Item Discount</th>
                            <th>Extra Discount</th>
                            <th>Net Return</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @foreach ($returns as $return)
                        <tr>
                            <td class="fw-bold text-muted">#{{ $return->id }}</td>
                            <td>
                                <span class="badge bg-light text-primary border px-3 py-2 fw-bold" style="font-size: 11px;">
                                    {{ $return->return_invoice }}
                                </span>
                            </td>
                            <td class="text-start">
                                <div class="fw-bold text-dark">{{ $return->vendor->name ?? 'N/A' }}</div>
                                <div class="small text-muted"><i class="fa fa-warehouse me-1"></i> 
                                    {{ $return->warehouse->warehouse_name ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold">{{ \Carbon\Carbon::parse($return->return_date)->format('Y-m-d') }}</div>
                            </td>
                            <td class="text-dark">{{ number_format($return->bill_amount, 2) }}</td>
                            <td class="text-muted">{{ number_format($return->item_discount, 2) }}</td>
                            <td class="text-danger">{{ number_format($return->extra_discount, 2) }}</td>
                            <td class="fw-bold text-success" style="font-size: 14px;">
                                Rs. {{ number_format($return->net_amount, 2) }}
                            </td>
                            <td class="text-muted small text-start" style="max-width: 150px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                {{ $return->remarks ?? 'N/A' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#return-table').DataTable({
            "pageLength": 10,
            "lengthMenu": [5, 10, 25, 50, 100],
            "order": [[0, 'desc']],
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Filter returns...",
                "lengthMenu": "Show _MENU_ entries"
            }
        });
    });
</script>
@else
    <div class="container py-5 text-center">
        <div class="alert alert-danger shadow">Access Denied: You do not have 'purchase.return.view' permission.</div>
    </div>
@endcan
@endsection
