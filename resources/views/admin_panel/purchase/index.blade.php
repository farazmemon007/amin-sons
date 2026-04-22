@extends('admin_panel.layout.app')

@section('content')
@can('purchase.view')

<style>
    .main-content { background-color: #f8f9fa; min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .card { border-radius: 12px; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    .table thead th { background-color: #f1f4f9; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: #555; border-bottom: none; padding: 15px 10px; }
    .table tbody td { padding: 14px 10px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
    .bg-soft-success { background-color: #d1f7e8 !important; color: #008d50 !important; }
    .bg-soft-warning { background-color: #fff3cd !important; color: #856404 !important; }
    .dropdown-menu { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 10px; padding: 8px; z-index: 9999 !important; }
    .dropdown-item { border-radius: 6px; padding: 8px 12px; font-size: 13px; transition: all 0.2s; }
    .dropdown-item:hover { background-color: #f0f7ff; color: #007bff; }
    .dropdown-item i { width: 20px; }
    .dataTables_wrapper .dataTables_filter input { border-radius: 8px; border: 1px solid #ddd; padding: 6px 12px; margin-bottom: 10px; }
</style>

<div class="main-content">
    <div class="container-fluid px-4 pt-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Purchase Inventory</h4>
                <small class="text-muted">Manage your stock acquisitions and vendor payments</small>
            </div>
            <a href="{{ route('add_purchase') }}" class="btn btn-primary px-4 fw-bold shadow-sm">
                <i class="fa fa-plus me-2"></i> ADD PURCHASE
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
                <strong><i class="fa fa-check-circle me-2"></i>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card p-0 overflow-hidden">
            <div class="table-responsive">
                <table id="purchase-table" class="table table-hover mb-0 w-100">
                    <thead>
                        <tr class="text-center">
                            <th>ID</th>
                            @if($showBranchColumn) <th>Branch</th> @endif
                            <th class="text-start">Vendor / Warehouse</th>
                            <th>Invoice No</th>
                            <th>Net Amount</th>
                            <th>Due</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @foreach ($Purchase as $purchase)
                        <tr>
                            <td class="fw-bold text-muted">#{{ $purchase->id }}</td>
                            
                            @if($showBranchColumn)
                                <td><span class="badge bg-light text-dark border">{{ $purchase->branch->name ?? 'N/A' }}</span></td>
                            @endif

                            <td class="text-start">
                                <div class="fw-bold text-dark">{{ $purchase->vendor->name ?? 'N/A' }}</div>
                                <div class="small text-muted"><i class="fa fa-warehouse me-1"></i> 
                                    @php
                                        if ($purchase->warehouse_id && $purchase->warehouse) {
                                            echo $purchase->warehouse->warehouse_name;
                                        } elseif ($purchase->items && $purchase->items->count() > 0) {
                                            echo $purchase->items->pluck('warehouse.warehouse_name')->unique()->filter()->implode(', ');
                                        } else { echo 'Default'; }
                                    @endphp
                                </div>
                            </td>

                            <td>
                                <div class="fw-bold">{{ $purchase->formatted_invoice }}</div>
                                <small class="text-muted">{{ $purchase->purchase_date }}</small>
                            </td>

                            <td class="fw-bold text-dark">{{ number_format($purchase->net_amount, 2) }}</td>

                            <td class="text-danger fw-bold">{{ number_format($purchase->due_amount, 2) }}</td>

                            <td>
                                @if($purchase->receipt_status === 'pending')
                                    <span class="badge bg-soft-warning px-3 py-2">Pending</span>
                                @else
                                    <span class="badge bg-soft-success px-3 py-2">Received</span>
                                @endif
                            </td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Manage
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        @can('purchase.invoice')
                                            <a class="dropdown-item" href="{{ route('purchase.invoice', $purchase->id) }}"><i class="fa fa-print text-warning"></i> Invoice</a>
                                        @endcan
                                        
                                        @can('purchase.edit')
                                            <a class="dropdown-item" href="{{ route('purchase.edit', $purchase->id) }}"><i class="fa fa-edit text-primary"></i> Edit</a>
                                        @endcan

                                        @can('inward.gatepass.create')
                                            <a class="dropdown-item" href="{{ route('inward-gatepass.from-purchase', $purchase->id) }}"><i class="fa fa-truck text-success"></i> Receive</a>
                                        @endcan

                                        @can('purchase.return')
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="{{ route('purchase.return.show', $purchase->id) }}"><i class="fa fa-undo"></i> Return</a>
                                        @endcan
                                    </div>
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // 1. Force Dropdown Fix (Agar bootstrap automatic na chale)
        $('.dropdown-toggle').on('click', function (e) {
            var $el = $(this).next('.dropdown-menu');
            $('.dropdown-menu').not($el).hide(); // Dusre band karo
            $el.toggle(); // Isko kholo
            e.stopPropagation();
        });

        // Click outside band karne ke liye
        $(document).on('click', function (e) {
            if (!$('.dropdown').has(e.target).length) {
                $('.dropdown-menu').hide();
            }
        });

        // 2. DataTable Initialize
        if ($.fn.DataTable.isDataTable('#purchase-table')) {
            $('#purchase-table').DataTable().destroy();
        }

        $('#purchase-table').DataTable({
            "pageLength": 10,
            "order": [[0, 'desc']],
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Filter records..."
            }
        });
    });
</script>

@else
    <div class="container py-5 text-center">
        <div class="alert alert-danger shadow">Access Denied: You do not have 'purchase.view' permission.</div>
    </div>
@endcan
@endsection