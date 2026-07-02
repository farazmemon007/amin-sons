@extends('admin_panel.layout.app')

@section('css')
<style>
    .damaged-card { border-radius: 10px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .stats-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    .loc-shop { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .loc-warehouse { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .filter-card { background: #f8f9fa; border-left: 4px solid #e74c3c; border-radius: 6px; padding: 15px 20px; margin-bottom: 20px; }
    .stats-box { border-radius: 8px; padding: 15px 20px; color: white; text-align: center; margin-bottom: 15px; }
    .box-shop { background: linear-gradient(135deg, #e67e22, #d35400); }
    .box-wh { background: linear-gradient(135deg, #27ae60, #219d52); }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 text-dark font-weight-bold">
                <i class="fas fa-dumpster text-danger mr-2"></i>Damaged Stock Management
            </h4>
            <small class="text-muted">Track defective customer returns and coordinate warehouse transfers</small>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mb-3">
        @php
            $shopQty = \App\Models\DamagedStock::whereNull('warehouse_id')
                ->when(!$isSuperAdmin, fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                ->sum('quantity');
            $whQty = \App\Models\DamagedStock::whereNotNull('warehouse_id')
                ->when(!$isSuperAdmin, fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                ->sum('quantity');
        @endphp
        <div class="col-md-3 col-6">
            <div class="stats-box box-shop">
                <div style="font-size:24px; font-weight:700;">{{ (float)$shopQty }}</div>
                <div style="font-size:12px;">Retained at Shop/Branches</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stats-box box-wh">
                <div style="font-size:24px; font-weight:700;">{{ (float)$whQty }}</div>
                <div style="font-size:12px;">Stored in Warehouses</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('complaints.damaged-stock.index') }}">
            <div class="row align-items-end">
                @if($isSuperAdmin)
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold mb-1">Branch</label>
                    <select name="branch_id" class="form-control form-control-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold mb-1">Product</label>
                    <select name="product_id" class="form-control form-control-sm select2">
                        <option value="">All Products</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->item_name }} ({{ $p->item_code }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search mr-1"></i>Filter</button>
                    <a href="{{ route('complaints.damaged-stock.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times mr-1"></i>Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Main Inventory Table --}}
    <div class="card shadow-sm damaged-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead style="background: linear-gradient(135deg, #c0392b, #d35400); color: white;">
                        <tr>
                            <th class="py-2 px-3">Branch</th>
                            <th class="py-2 px-3">Location Status</th>
                            <th class="py-2 px-3">Defective Item</th>
                            <th class="py-2 px-3">Item Code</th>
                            <th class="py-2 px-3">Quantity</th>
                            <th class="py-2 px-3">Last Updated</th>
                            <th class="py-2 px-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($damagedStocks as $stock)
                        @if((float)$stock->quantity > 0)
                        <tr>
                            <td class="px-3 py-2 font-weight-bold">{{ $stock->branch->name ?? 'Head Office' }}</td>
                            <td class="px-3 py-2">
                                @if($stock->warehouse_id === null)
                                    <span class="stats-badge loc-shop"><i class="fas fa-store mr-1"></i>Held at Shop</span>
                                @else
                                    <span class="stats-badge loc-warehouse"><i class="fas fa-warehouse mr-1"></i>Warehouse: {{ $stock->warehouse->warehouse_name ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <strong>{{ $stock->product->item_name ?? 'N/A' }}</strong>
                                @if($stock->is_part && $stock->part_name)
                                    <span class="badge badge-warning d-block mt-1 font-weight-bold text-dark" style="max-width: fit-content;"><i class="fas fa-puzzle-piece mr-1"></i>Part: {{ $stock->part_name }}</span>
                                @else
                                    <span class="badge badge-success d-block mt-1 font-weight-bold text-white" style="max-width: fit-content;"><i class="fas fa-box mr-1"></i>Complete Product</span>
                                @endif
                             </td>
                            <td class="px-3 py-2 text-muted small">{{ $stock->product->item_code ?? 'N/A' }}</td>
                            <td class="px-3 py-2 font-weight-bold text-danger">{{ (float)$stock->quantity }}</td>
                            <td class="px-3 py-2 text-muted small">{{ $stock->updated_at->format('d M Y, H:i') }}</td>
                            <td class="px-3 py-2">
                                @if($stock->warehouse_id === null)
                                    @can('stock.transfer.create')
                                    <button class="btn btn-danger btn-sm py-1 px-3 transfer-btn" 
                                            data-id="{{ $stock->id }}"
                                            data-product="{{ $stock->product->item_name }}"
                                            data-is-part="{{ $stock->is_part ? '1' : '0' }}"
                                            data-part-name="{{ $stock->part_name ?? '' }}"
                                            data-branch="{{ $stock->branch->name }}"
                                            data-qty="{{ $stock->quantity }}"
                                            data-toggle="modal" 
                                            data-target="#transferModal">
                                        <i class="fas fa-exchange-alt mr-1"></i>Transfer to Warehouse
                                    </button>
                                    @endcan
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox" style="font-size:40px; opacity:0.3;"></i>
                                <div class="mt-2">No damaged stock recorded.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($damagedStocks->hasPages())
        <div class="card-footer">
            {{ $damagedStocks->links() }}
        </div>
        @endif
    </div>

</div>

{{-- Transfer Damaged Stock Modal --}}
@can('stock.transfer.create')
<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('complaints.damaged-stock.transfer') }}" method="POST" id="transferForm">
                @csrf
                <input type="hidden" name="damaged_stock_id" id="modal_stock_id">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold text-white"><i class="fas fa-exchange-alt mr-1"></i>Transfer Damaged Pieces to Warehouse</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <div class="alert alert-warning py-2 small">
                        <i class="fas fa-info-circle mr-1"></i> This moves defective items from the branch shop to a selected warehouse as damage pieces.
                    </div>

                    <div class="mb-3">
                        <label class="small font-weight-bold d-block">Branch Shop</label>
                        <input type="text" id="modal_branch_name" class="form-control form-control-sm" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="small font-weight-bold d-block">Product</label>
                        <input type="text" id="modal_product_name" class="form-control form-control-sm" readonly>
                    </div>

                    <div class="mb-3" id="modal_part_wrapper" style="display:none;">
                        <label class="small font-weight-bold d-block text-danger">Defective Part Name</label>
                        <input type="text" id="modal_part_name" class="form-control form-control-sm text-danger font-weight-bold" readonly>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small font-weight-bold d-block">Available Shop Qty</label>
                            <input type="text" id="modal_available_qty" class="form-control form-control-sm" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small font-weight-bold d-block">Quantity to Transfer *</label>
                            <input type="number" name="transfer_qty" id="modal_transfer_qty" class="form-control form-control-sm" required min="1" step="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small font-weight-bold">Target Warehouse *</label>
                        <select name="to_warehouse_id" class="form-control form-control-sm" required>
                            <option value="">-- Select Destination Warehouse --</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->warehouse_name }} ({{ $wh->location }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="small font-weight-bold">Transfer Remarks / Notes</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="e.g. Batch transfer of damaged fan motors"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4" id="submitTransferBtn">
                        <i class="fas fa-check mr-1"></i>Confirm Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@endsection

@section('js')
<script>
$(document).ready(function() {
    $('.select2').select2({ placeholder: '-- Search --', allowClear: true });

    // Handle Transfer button click to fill modal details
    $('.transfer-btn').on('click', function() {
        const id = $(this).data('id');
        const product = $(this).data('product');
        const isPart = $(this).data('is-part');
        const partName = $(this).data('part-name');
        const branch = $(this).data('branch');
        const qty = parseFloat($(this).data('qty') || 0);

        $('#modal_stock_id').val(id);
        $('#modal_product_name').val(product);
        $('#modal_branch_name').val(branch);
        $('#modal_available_qty').val(qty);
        $('#modal_transfer_qty').val(qty).attr('max', qty);

        if (parseInt(isPart) === 1 && partName) {
            $('#modal_part_wrapper').show();
            $('#modal_part_name').val(partName);
        } else {
            $('#modal_part_wrapper').hide();
            $('#modal_part_name').val('');
        }
    });

    // Handle form submit verification
    $('#transferForm').on('submit', function(e) {
        const available = parseFloat($('#modal_available_qty').val() || 0);
        const request = parseFloat($('#modal_transfer_qty').val() || 0);

        if (request > available) {
            e.preventDefault();
            Swal.fire('Error', 'Transfer quantity cannot exceed available quantity.', 'error');
            return false;
        }

        $('#submitTransferBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i>Transferring...').prop('disabled', true);
    });
});
</script>
@endsection
