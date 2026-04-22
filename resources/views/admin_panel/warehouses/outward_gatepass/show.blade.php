@extends('admin_panel.layout.app')

@section('style')
<style>
    /* ERP Style Enhancements */
    .gp-card { border-top: 4px solid #3c8dbc; border-radius: 8px; }
    .label-title { color: #666; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { color: #333; font-weight: 500; font-size: 1rem; border-bottom: 1px dashed #eee; display: block; padding-bottom: 2px; }
    .table-erp thead { background-color: #f8f9fa; border-top: 2px solid #dee2e6; }
    .table-erp th { text-transform: uppercase; font-size: 0.75rem; color: #555; }
    .section-divider { border-left: 3px solid #3c8dbc; padding-left: 10px; margin-bottom: 15px; background: #fcfcfc; padding-top: 5px; padding-bottom: 5px; }
    .notes-box { background: #fffdf5; border: 1px solid #ffecb3; }
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; shadow: none !important; }
        .gp-card { border-top: none !important; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    {{-- Alerts Section --}}
    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show">
            <strong><i class="fa fa-check-circle"></i></strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header Action Bar --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h3 class="fw-bold mb-0 text-dark">Outward Gate Pass</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('OutwardGatepass.home') }}">Logistics</a></li>
                    <li class="breadcrumb-item active">GP-{{ $gp->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group shadow-sm">
            <a href="{{ route('OutwardGatepass.list') }}" class="btn btn-white btn-sm border"><i class="fa fa-list me-1"></i> List</a>
            @can('outward.gatepass.print')
                <a href="{{ route('OutwardGatepass.pdf', $gp->id) }}" class="btn btn-white btn-sm border text-danger"><i class="fa fa-file-pdf me-1"></i> PDF</a>
            @endcan
            <button onclick="window.print()" class="btn btn-white btn-sm border"><i class="fa fa-print me-1"></i> Print</button>
            <a href="#" id="thermalBtn" class="btn btn-warning btn-sm"><i class="fa fa-receipt me-1"></i> Thermal</a>
        </div>
    </div>

    <div class="card gp-card shadow-sm">
        <div class="card-body p-4">
            {{-- Document Branding --}}
            <div class="row mb-4">
                <div class="col-6">
                    <h2 class="fw-bold text-primary mb-0">GATE PASS</h2>
                    <p class="text-muted small">Official Outward Document</p>
                </div>
                <div class="col-6 text-end">
                    <div class="bg-light bg-opacity-10 p-3 d-inline-block rounded border-2 border-primary">
                        <span class="label-title d-block text-primary">Gatepass Number</span>
                        <span class="h4 fw-bold mb-0 text-primary">{{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</span>
                        @if($gp->branch_id)
                            <span class="label-title d-block text-primary mt-1">{{ \App\Models\Branch::find($gp->branch_id)?->name ?? 'Branch' }}</span>
                        @endif
                    </div>
                    <div class="mt-3 text-muted small">
                        <strong>Date:</strong> {{ optional($gp->created_at)->format('d-M-Y h:i A') ?? '-' }}<br>
                        <strong>Invoice No:</strong> {{ $gp->invoice_no ?? '-' }}
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Left Column: Order Info --}}
                <div class="col-md-4">
                    <div class="section-divider"><h6 class="mb-0 fw-bold">Primary Details</h6></div>
                    <div class="mb-3">
                        <label class="label-title">Customer Name:</label>
                        <span class="info-value text-primary fw-bold">{{ $gp->customer_name ?? 'N/A' }}</span>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="label-title">Order ID:</label>
                            <span class="info-value">{{ $gp->order_id }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="label-title">DC Number:</label>
                            <span class="info-value">{{ $gp->dc_no ?? ($order->dc_no ?? '-') }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="label-title">Invoice No:</label>
                            <span class="info-value">{{ $gp->invoice_no ?? '-' }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="label-title">Delivery City:</label>
                            <span class="info-value">{{ $gp->delivery_city ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Middle Column: Transport --}}
                <div class="col-md-5 border-start border-end px-md-4">
                    <div class="section-divider"><h6 class="mb-0 fw-bold">Logistics & Transport:</h6></div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="label-title">Transporter:</label>
                            <span class="info-value">{{ $gp->transporter ?? '-' }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="label-title">Vehicle Type:</label>
                            <span class="info-value">{{ $gp->vehicle_type ?? '-' }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="label-title">Driver Name:</label>
                            <span class="info-value">{{ $gp->driver_name ?? '-' }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="label-title">Vehicle No:</label>
                            <span class="info-value text-uppercase">{{ $gp->vehicle_number ?? '-' }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="label-title">Bilty No:</label>
                            <span class="info-value">{{ $gp->billty_no ?? '-' }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="label-title">Bilty Date:</label>
                            <span class="info-value">{{ $gp->billty_date ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Warehouse/Issuer --}}
                <div class="col-md-3">
                    <div class="section-divider"><h6 class="mb-0 fw-bold">Origins</h6></div>
                    <div class="mb-3">
                        <label class="label-title">Warehouse</label>
                        <span class="info-value">
                            <i class="fa fa-warehouse small me-1"></i>
                            {{ optional(\App\Models\Warehouse::find($gp->warehouse_id))->warehouse_name ?? ($gp->warehouse_id ?? '-') }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <label class="label-title">Prepared By</label>
                        <span class="info-value">{{ $gp->prepared_by ?? '-' }}</span>
                    </div>
                    <div class="bg-light p-2 rounded">
                        <label class="label-title">Bilty Amount</label>
                        <span class="h6 mb-0 d-block fw-bold text-dark">Rs. {{ $gp->billty_amount ? number_format($gp->billty_amount, 2) : '0.00' }}</span>
                    </div>
                </div>
            </div>

            {{-- Table Section --}}
            <div class="mt-4">
                <div class="section-divider"><h6 class="mb-0 fw-bold">Itemized Manifest</h6></div>
                <div class="table-responsive">
                    @php
                        $items = $gp->items ?? [];
                        $totalQty = 0; $totalAmount = 0;
                    @endphp
                    <table class="table table-erp table-hover border">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:50px">#</th>
                                <th>Product Description</th>
                                <th>Item Code</th>
                                <th class="text-center">Unit</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Rate</th>
                                <th class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $k => $it)
                                @php
                                    $row = is_array($it) ? $it : (is_object($it) ? (array)$it : ['text' => $it]);
                                    $qty = (float)($row['qty'] ?? 0);
                                    $amt = isset($row['amount']) ? (float)$row['amount'] : (($row['retail_price'] ?? 0) * $qty);
                                    $totalQty += $qty;
                                    $totalAmount += $amt;
                                @endphp
                                <tr>
                                    <td class="text-center text-muted">{{ $k + 1 }}</td>
                                    <td class="fw-bold">{{ $row['product_name'] ?? $row['text'] ?? '-' }}</td>
                                    <td><code class="text-dark">{{ $row['item_code'] ?? '-' }}</code></td>
                                    <td class="text-center">
                                        @if(!empty($row['unit']))
                                            <span class=" text-dark">{{ $row['unit'] }}</span>
                                        @elseif(isset($row['unit']) && $row['unit'] === '')
                                            <span class="badge bg-warning text-dark">N/A</span>
                                        @else
                                            <span class="badge bg-light text-dark">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">{{ $qty > 0 ? number_format($qty, 2) : '-' }}</td>
                                    <td class="text-end text-muted">{{ isset($row['retail_price']) ? number_format($row['retail_price'], 2) : '-' }}</td>
                                    <td class="text-end fw-bold">{{ $amt > 0 ? number_format($amt, 2) : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-4">No items found.</td></tr>
                            @endforelse
                        </tbody>
                        @if(count($items))
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td colspan="4" class="text-end">GRAND TOTALS:</td>
                                    <td class="text-end text-primary">{{ number_format($totalQty, 2) }}</td>
                                    <td></td>
                                    <td class="text-end text-primary">{{ number_format($totalAmount, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Footer Notes & Signatures --}}
            <div class="row mt-4 align-items-end">
                <div class="col-md-7">
                    <div class="p-3 rounded notes-box border mb-3">
                        <h6 class="label-title mb-2"><i class="fa fa-pen-nib me-1"></i> Packing & Handling Notes</h6>
                        <textarea id="packingNotes" rows="3" class="form-control border-0 bg-transparent" placeholder="Enter packing instructions...">{{ old('packing_notes', $gp->packing_notes ?? '') }}</textarea>
                        <div class="mt-2 no-print">
                             <button id="savePacking" class="btn btn-sm btn-primary px-3">Update Notes</button>
                             <span id="packingStatus" class="ms-2 text-success small" style="display:none">✔ Updated</span>
                        </div>
                    </div>
                    <div class="p-2">
                        <label class="label-title">Remarks</label>
                        <p class="text-muted small italic">{{ $gp->remarks ?? 'No additional remarks provided.' }}</p>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="row text-center">
                        <div class="col-6">
                            <div style="height: 60px;"></div>
                            <div class="border-top pt-2">
                                <span class="label-title">Receiver's Sign</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="height: 60px;" class="d-flex align-items-center justify-content-center">
                                <span class="fw-bold text-dark">{{ $gp->issued_by ?? 'Authorized' }}</span>
                            </div>
                            <div class="border-top pt-2">
                                <span class="label-title">Issued By</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    // Thermal Print Window
    document.getElementById('thermalBtn')?.addEventListener('click', function(e){
        e.preventDefault();
        const w = window.open("{{ route('OutwardGatepass.thermal', $gp->id) }}", 'thermal','width=360,height=700');
        if(!w) { alert('Please allow popups for thermal print.'); }
    });

    // AJAX Save for Packing Notes
    document.getElementById('savePacking')?.addEventListener('click', function(e){
        e.preventDefault();
        const btn = this;
        const notes = document.getElementById('packingNotes').value;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

        fetch("{{ route('OutwardGatepass.updatePackingNotes', $gp->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ packing_notes: notes })
        })
        .then(r => r.json())
        .then(j => {
            if(j.status === 'ok'){
                const s = document.getElementById('packingStatus');
                s.style.display = 'inline';
                setTimeout(() => s.style.display = 'none', 3000);
            } else { alert('Error saving notes.'); }
        })
        .catch(e => { alert('Network error: ' + e.message); })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = 'Update Notes';
        });
    });
</script>
@endsection