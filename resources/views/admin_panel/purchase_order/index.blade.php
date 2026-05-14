@extends('admin_panel.layout.app')

@section('content')
@can('purchase.order.view')

<style>
    .main-content { background-color: #f8f9fa; min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .card { border-radius: 12px; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    .table thead th { background-color: #f1f4f9; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: #555; border-bottom: none; padding: 15px 10px; }
    .table tbody td { padding: 14px 10px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
    .bg-soft-success { background-color: #d1f7e8 !important; color: #008d50 !important; }
    .bg-soft-warning { background-color: #fff3cd !important; color: #856404 !important; }
    .bg-soft-info { background-color: #e0f7fa !important; color: #00acc1 !important; }
    .bg-soft-danger { background-color: #ffebee !important; color: #c62828 !important; }
    .dropdown-menu { border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 10px; padding: 8px; z-index: 9999 !important; }
    .dropdown-item { border-radius: 6px; padding: 8px 12px; font-size: 13px; transition: all 0.2s; }
    .dropdown-item:hover { background-color: #f0f7ff; color: #007bff; }
    .dropdown-item i { width: 20px; }
</style>

<div class="main-content">
    <div class="container-fluid px-4 pt-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Purchase Orders (Requests)</h4>
                <small class="text-muted">Draft purchase requests sent to vendors</small>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('purchase_orders.index') }}" method="GET" class="d-flex gap-2 align-items-center">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search PO Number..." value="{{ request('search') }}" style="min-width: 250px; border-radius: 8px 0 0 8px;">
                        <button type="submit" class="btn btn-secondary" style="border-radius: 0 8px 8px 0;">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                    @if(request('search'))
                        <a href="{{ route('purchase_orders.index') }}" class="btn btn-outline-danger shadow-sm" style="border-radius: 8px;">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </form>
                <a href="{{ route('purchase_orders.create') }}" class="btn btn-primary px-4 fw-bold shadow-sm" style="border-radius: 8px;">
                    <i class="fa fa-plus me-2"></i> CREATE PO
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
                <strong><i class="fa fa-check-circle me-2"></i>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card p-0 overflow-hidden">
            <div class="table-responsive">
                <table id="po-table" class="table table-hover mb-0 w-100">
                    <thead>
                        <tr class="text-center">
                            <th>ID</th>
                            @if($showBranchColumn) <th>Branch</th> @endif
                            <th>Warehouse</th>
                            <th class="text-start">Vendor</th>
                            <th>PO Number</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @foreach ($orders as $order)
                        <tr>
                            <td class="fw-bold text-muted">#{{ $order->id }}</td>
                            
                            @if($showBranchColumn)
                                <td><span class="badge bg-light text-dark border">{{ $order->branch->name ?? 'N/A' }}</span></td>
                            @endif

                            <td><span class="badge bg-light text-primary border">{{ $order->warehouse->warehouse_name ?? 'N/A' }}</span></td>

                            <td class="text-start">
                                <div class="fw-bold text-dark">{{ $order->vendor->name ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ $order->vendor->phone ?? '' }}</div>
                            </td>

                            <td>
                                <div class="fw-bold text-primary">{{ $order->po_number }}</div>
                            </td>

                            <td>
                                <div>{{ $order->order_date->format('d-M-Y') }}</div>
                                @if($order->expected_date)
                                    <small class="text-muted">Exp: {{ $order->expected_date->format('d-M-Y') }}</small>
                                @endif
                            </td>

                            <td class="fw-bold text-dark">{{ number_format($order->total_amount, 2) }}</td>

                            <td>
                                @php
                                    $statusClass = 'bg-soft-warning';
                                    $statusText = ucfirst($order->receipt_status);
                                    if($order->receipt_status == 'received') $statusClass = 'bg-soft-success';
                                    if($order->receipt_status == 'partial') $statusClass = 'bg-soft-info';
                                    if($order->status == 'cancelled') {
                                        $statusClass = 'bg-soft-danger';
                                        $statusText = 'Cancelled';
                                    }
                                @endphp
                                <span class="badge {{ $statusClass }} px-3 py-2">{{ $statusText }}</span>
                            </td>

                            <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('inward-gatepass.from-po', $order->id) }}" class="btn btn-success btn-sm shadow-sm" title="Convert to Gate Pass">
                                            <i class="fa fa-truck"></i>
                                        </a>
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Manage
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('purchase_orders.show', $order->id) }}"><i class="fa fa-eye text-info"></i> View Details</a>
                                                <a class="dropdown-item" href="{{ route('purchase_orders.print', $order->id) }}" target="_blank"><i class="fa fa-print text-warning"></i> Print PO</a>
                                                
                                                <div class="dropdown-divider"></div>

                                                @if($order->status !== 'cancelled')
                                                    @can('purchase.order.edit')
                                                        <a class="dropdown-item" href="{{ route('purchase_orders.edit', $order->id) }}"><i class="fa fa-edit text-primary"></i> Edit PO</a>
                                                    @endcan
                                                @endif

                                        @can('purchase.order.delete')
                                            <div class="dropdown-divider"></div>
                                            <form action="{{ route('purchase_orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger"><i class="fa fa-trash"></i> Delete</button>
                                            </form>
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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#po-table').DataTable({
            "pageLength": 10,
            "order": [[0, 'desc']],
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Filter orders..."
            }
        });
    });
</script>

@else
    <div class="container py-5 text-center">
        <div class="alert alert-danger shadow">Access Denied: You do not have 'purchase.order.view' permission.</div>
    </div>
@endcan
@endsection
