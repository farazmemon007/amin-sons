@extends('admin_panel.layout.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Warehouse Delivery Challans</h2>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <strong>Warehouse Delivery Challans</strong>
                    <div class="small text-muted">Manage DCs, create outward gate passes and review packing details</div>
                </div>
                <!-- <div>
                    <a href="{{ url('warehouse_orders/create') }}" class="btn btn-sm btn-primary">New DC</a> 
                </div> -->
            </div>

            <table class="table table-bordered table-striped" id="warehouseOrdersTable">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>DC No</th>
                        <th>Warehouse</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Prepared By</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $i => $order)
                    @php
                        // Normalize items whether $order is array or object and whether items is JSON/string
                        if (is_array($order)) {
                            $rawItems = $order['items'] ?? [];
                            $orderId = $order['id'] ?? $i;
                        } else {
                            $rawItems = $order->items ?? [];
                            $orderId = $order->id;
                        }

                        if (is_string($rawItems)) {
                            $items = json_decode($rawItems, true) ?: [];
                        } elseif ($rawItems instanceof \Illuminate\Support\Collection || is_array($rawItems)) {
                            $items = (array) $rawItems;
                        } else {
                            $items = [];
                        }

                        $itemsCount = count($items);
                        $totalAmount = array_reduce($items, function($carry, $it){
                            $amt = is_array($it) ? ($it['amount'] ?? 0) : ($it->amount ?? 0);
                            return $carry + (float) $amt;
                        }, 0);
                    @endphp
                    <tr id="order-row-{{ $orderId }}" class="{{ isset($highlightOrderId) && $highlightOrderId == (is_array($order) ? ($order['id'] ?? null) : ($order->id ?? null)) ? 'highlighted-row' : '' }}" data-items='@json($items)'>
                        <td>{{ $i+1 }}</td>
                        <td>{{ is_array($order) ? ($order['dc_no'] ?? '-') : ($order->dc_no ?? '-') }}</td>
                        <td>{{ is_array($order) ? (optional((object)($order['warehouse'] ?? null))->warehouse_name ?? '-') : (optional($order->warehouse)->warehouse_name ?? '-') }}</td>
                        <td>{{ is_array($order) ? (optional((object)($order['customer'] ?? null))->customer_name ?? '-') : (optional($order->customer)->customer_name ?? '-') }}</td>

                        <td class="text-center">{{ $itemsCount }}</td>
                        <td class="text-right">{{ number_format($totalAmount, 2) }}</td>

                        <td>
                            @php $status = is_array($order) ? ($order['status'] ?? '') : ($order->status ?? ''); @endphp
                            @if($status == 'delivered')
                                <span class="badge badge-success">Delivered</span>
                            @elseif($status == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($status) }}</span>
                            @endif
                        </td>

                        <td>{{ is_array($order) ? ($order['prepared_by'] ?? '-') : ($order->prepared_by ?? '-') }}</td>
                        <td>{{ is_array($order) ? (isset($order['created_at']) ? \Carbon\Carbon::parse($order['created_at'])->format('d-m-Y H:i') : '-') : ($order->created_at ? $order->created_at->format('d-m-Y H:i') : '-') }}</td>
                        <td>{{ is_array($order) ? (isset($order['updated_at']) ? \Carbon\Carbon::parse($order['updated_at'])->format('d-m-Y H:i') : '-') : ($order->updated_at ? $order->updated_at->format('d-m-Y H:i') : '-') }}</td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="moreActions-{{ $orderId }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    More
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="moreActions-{{ $orderId }}">
                                    @php $currentStatus = is_array($order) ? ($order['status'] ?? '') : ($order->status ?? ''); @endphp
                                    @if($currentStatus == 'pending')
                                        <a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('warehouse_orders.status', $orderId) }}" onclick="return confirm('Are you sure you want to mark this order as delivered?')">Delivered <span class="badge badge-success ml-2">Delivered</span></a>
                                    @endif

                                    @can('outward.gatepass.create')
                                        @php
                                            $existingGp = null;
                                            if(!empty($orderId)) {
                                                $existingGp = \Illuminate\Support\Facades\DB::table('outward_gatepasses')->where('order_id', $orderId)->orderByDesc('id')->first();
                                            }
                                        @endphp
                                        @if($existingGp)
                                            <a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('OutwardGatepass.show', $existingGp->id) }}">View GatePass <span class="badge badge-primary ml-2">GP</span></a>
                                        @else
                                            <a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('outward_gatepass.create', $orderId) }}" onclick="return confirm('Create outward gate pass for this order?')">Create Outward GP <span class="badge badge-primary ml-2">GP</span></a>
                                        @endif
                                    @endcan

                                    @if($itemsCount > 0)
                                        <button type="button" class="dropdown-item d-flex justify-content-between align-items-center show-details" data-orderid="{{ $orderId }}">View Items <span class="badge badge-info ml-2">{{ $itemsCount }}</span></button>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center">No warehouse orders found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .highlighted-row {
        background-color: #fff3cd !important;
        animation: highlight-pulse 2s ease-in-out 3;
    }
    
    @keyframes highlight-pulse {
        0%, 100% { background-color: #fff3cd; }
        50% { background-color: #ffecb5; }
    }

    /* Button beautification (non-invasive) */
    .btn {
        border-radius: 6px !important;
        padding: .375rem .65rem !important;
        font-weight: 600;
        letter-spacing: .2px;
        transition: transform .08s ease, box-shadow .12s ease, opacity .12s ease;
        box-shadow: 0 1px 2px rgba(16,24,40,0.04);
        border: 1px solid rgba(16,24,40,0.06);
    }

    .btn-sm {
        padding: .25rem .5rem !important;
        font-size: .85rem !important;
    }

    .btn-primary {
        background-color: #0d6efd !important;
        color: #fff !important;
        border-color: rgba(13,110,253,0.15) !important;
    }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(13,110,253,0.12); }

    .btn-success {
        background-color: #198754 !important;
        color: #fff !important;
        border-color: rgba(25,135,84,0.12) !important;
    }
    .btn-success:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(25,135,84,0.12); }

    .btn-info {
        background-color: #0dcaf0 !important;
        color: #042028 !important;
        border-color: rgba(13,202,240,0.12) !important;
    }
    .btn-info:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(13,202,240,0.10); }

    .btn-outline-secondary {
        background-color: transparent !important;
        border-color: rgba(108,117,125,0.18) !important;
        color: #495057 !important;
    }
    .btn-outline-secondary:hover { background-color: rgba(108,117,125,0.06) !important; }

    /* Small spacing between adjacent buttons */
    .btn + .btn { margin-left: .4rem; }

    /* Ensure action column buttons wrap nicely on small screens */
    td .btn { white-space: nowrap; }

    /* Force Action column buttons into a single inline row */
    #warehouseOrdersTable td:last-child {
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: .4rem;
        align-items: center;
        justify-content: flex-end;
        white-space: nowrap;
    }
    /* Ensure anchor/button children are inline-flex for consistent alignment */
    #warehouseOrdersTable td:last-child > * {
        display: inline-flex !important;
        align-items: center;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if there's a highlighted row
    const highlightedRow = document.querySelector('.highlighted-row');
    if (highlightedRow) {
        // Scroll to the highlighted row
        highlightedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Remove highlight after 6 seconds (3 animations)
        setTimeout(function() {
            highlightedRow.classList.remove('highlighted-row');
            // Clean up URL parameter without reloading
            const url = new URL(window.location);
            url.searchParams.delete('highlight');
            window.history.replaceState({}, document.title, url.toString());
        }, 6000);
    }
});
</script>
@foreach($orders as $i => $order)
    @php
        // prepare items for modal
        if (is_array($order)) {
            $rawItems = $order['items'] ?? [];
            $orderId = $order['id'] ?? $i;
        } else {
            $rawItems = $order->items ?? [];
            $orderId = $order->id;
        }
        if (is_string($rawItems)) {
            $modalItems = json_decode($rawItems, true) ?: [];
        } elseif ($rawItems instanceof \Illuminate\Support\Collection) {
            $modalItems = $rawItems->toArray();
        } elseif (is_array($rawItems)) {
            $modalItems = $rawItems;
        } else {
            $modalItems = [];
        }
    @endphp

    <div class="modal fade" id="itemsModal-{{ $orderId }}" tabindex="-1" role="dialog" aria-labelledby="itemsModalLabel-{{ $orderId }}" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="itemsModalLabel-{{ $orderId }}">Items for DC #{{ is_array($order) ? ($order['dc_no'] ?? $orderId) : ($order->dc_no ?? $orderId) }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Code</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($modalItems as $miIndex => $mi)
                        <tr>
                            <td>{{ $miIndex+1 }}</td>
                            <td>{{ is_array($mi) ? ($mi['product_name'] ?? ($mi['name'] ?? '-')) : ($mi->product_name ?? $mi->name ?? '-') }}</td>
                            <td>{{ is_array($mi) ? ($mi['item_code'] ?? '-') : ($mi->item_code ?? '-') }}</td>
                            <td class="text-center">{{ is_array($mi) ? ($mi['qty'] ?? 0) : ($mi->qty ?? 0) }}</td>
                            <td class="text-right">{{ number_format(is_array($mi) ? ($mi['retail_price'] ?? 0) : ($mi->retail_price ?? 0), 2) }}</td>
                            <td class="text-right">{{ number_format(is_array($mi) ? ($mi['amount'] ?? 0) : ($mi->amount ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No items</td></tr>
                    @endforelse
                </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
@endforeach

    <!-- Product details panel (populated when user clicks View Items) -->
    <div class="container mt-4" id="productDetailsPanel" style="display:none">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong id="pdTitle">Products for DC</strong>
                    <div class="small text-muted" id="pdSub"></div>
                </div>
                <div>
                    <button id="pdClose" class="btn btn-sm btn-outline-secondary">Close</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="pdTable">
                        <thead>
                            <tr><th>#</th><th>Product</th><th>Code</th><th class="text-center">Unit</th><th class="text-end">Qty</th><th class="text-end">Rate</th><th class="text-end">Amount</th></tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr><th colspan="4" class="text-end">Totals</th><th id="pdTotalQty" class="text-end"></th><th></th><th id="pdTotalAmount" class="text-end"></th></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Open details panel when 'View Items' clicked
    document.querySelectorAll('.show-details').forEach(function(btn){
        btn.addEventListener('click', function(){
            const orderId = this.dataset.orderid;
            // find row
            const row = document.getElementById('order-row-'+orderId);
            if(!row) return;
            const items = row.dataset.items ? JSON.parse(row.dataset.items) : [];
            const title = row.querySelector('td:nth-child(2)') ? row.querySelector('td:nth-child(2)').innerText : 'DC #'+orderId;
            document.getElementById('pdTitle').innerText = 'Products for ' + title;
            document.getElementById('pdSub').innerText = 'Order ID: ' + orderId;

            const tbody = document.querySelector('#pdTable tbody'); tbody.innerHTML = '';
            let totalQty = 0, totalAmount = 0;
            (items||[]).forEach(function(it, idx){
                const rowData = (typeof it === 'object') ? it : { product_name: it, item_code:'', unit:'', qty:0, retail_price:0, amount:0 };
                const qty = parseFloat(rowData.qty) || 0;
                const amt = parseFloat(rowData.amount) || (parseFloat(rowData.retail_price||0) * qty) || 0;
                totalQty += qty; totalAmount += amt;
                const tr = document.createElement('tr');
                tr.innerHTML = '<td>'+ (idx+1) +'</td><td>'+ (rowData.product_name||rowData.name||'-') +'</td><td>'+ (rowData.item_code||'-') +'</td><td class="text-center">'+ (rowData.unit||'-') +'</td><td class="text-end">'+ (qty? qty : '-') +'</td><td class="text-end">'+ (rowData.retail_price? parseFloat(rowData.retail_price).toFixed(2) : '-') +'</td><td class="text-end">'+ (amt? amt.toFixed(2) : '-') +'</td>';
                tbody.appendChild(tr);
            });
            document.getElementById('pdTotalQty').innerText = totalQty ? (Math.round(totalQty*100)/100) : '-';
            document.getElementById('pdTotalAmount').innerText = totalAmount ? totalAmount.toFixed(2) : '-';
            document.getElementById('productDetailsPanel').style.display = 'block';
            document.getElementById('productDetailsPanel').scrollIntoView({behavior:'smooth'});
        });
    });

    document.getElementById('pdClose').addEventListener('click', function(){
        document.getElementById('productDetailsPanel').style.display = 'none';
    });
});
</script>
@endsection
@endsection
