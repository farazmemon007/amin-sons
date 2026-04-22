@extends('admin_panel.layout.app')

@section('content')
<style>
    /* UI Enhancements */
    .gp-card { border: 2px solid #eee; border-radius: 10px; background: #fff; }
    .table-thead-dark { background: #2c3e50 !important; color: white !important; }
    .info-label { color: #666; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
    .info-value { color: #222; font-weight: 700; font-size: 1rem; }
    
    /* Print Professional Styling */
    @media print {
        @page { margin: 10mm; size: A4; }
        .no-print, .btn, .navbar, .footer { display: none !important; }
        .gp-card { border: 1px solid #000 !important; border-radius: 0; box-shadow: none !important; }
        .main-content { padding: 0 !important; margin: 0 !important; }
        .badge { border: 1px solid #000 !important; color: #000 !important; background: transparent !important; }
        .table-thead-dark { background: #f2f2f2 !important; color: #000 !important; border-bottom: 2px solid #000 !important; }
        .text-primary, .text-success { color: #000 !important; }
    }

    /* Signature Box */
    .sig-box { margin-top: 50px; padding-top: 10px; border-top: 1px solid #333; text-align: center; font-size: 0.9rem; }
</style>

<div class="main-content py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <a href="{{ route('InwardGatepass.home') }}" class="btn btn-dark shadow-sm">
                        <i class="bi bi-chevron-left"></i> Exit
                    </a>
                    <div class="btn-group">
                        <button onclick="window.print()" class="btn btn-success shadow-sm">
                            <i class="bi bi-printer"></i> Print Gatepass
                        </button>
                        <a href="{{ route('InwardGatepass.pdf', $gatepass->id) }}" class="btn btn-danger shadow-sm">
                            <i class="bi bi-file-pdf"></i> Save PDF
                        </a>
                        <a href="{{ route('InwardGatepass.thermal', $gatepass->id) }}" class="btn btn-warning shadow-sm">
                            <i class="bi bi-receipt"></i> Thermal Print
                        </a>
                    </div>
                </div>

                <div class="gp-card shadow-sm p-4">
                    <div class="row mb-4 align-items-center">
                        <div class="col-6">
                            <h1 class="fw-bold text-uppercase m-0" style="letter-spacing: 2px; color: #2c3e50;">Inward Gatepass</h1>
                            <p class="text-muted m-0">Official Goods Receipt Note (GRN)</p>
                        </div>
                        <div class="col-6 text-end">
                            <h4 class="fw-bold m-0">{{ Auth::user()->branch->name ?? Auth::user()->branch_id ?? 'Head Office' }}</h4>
                            <p class="small text-muted m-0">Branch Location</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row mb-4">
                        <div class="col-md-4 border-end">
                            <div class="mb-3">
                                <div class="info-label">Gatepass Number</div>
                                <div class="info-value">#GP-{{ str_pad($gatepass->id, 6, '0', STR_PAD_LEFT) }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Arrival Date</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($gatepass->gatepass_date)->format('d-M-Y') }}</div>
                            </div>
                            <div class="mb-0">
                                <div class="info-label">Current Status</div>
                                <div>
                                    <span class="badge {{ $gatepass->display_status == 'completed' ? 'bg-success' : 'bg-warning' }} text-uppercase">
                                        {{ $gatepass->display_status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 border-end">
                            <div class="mb-3">
                                <div class="info-label">Vendor / Supplier</div>
                                <div class="info-value text-primary">{{ $gatepass->vendor->name ?? 'N/A' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Warehouse Location</div>
                                <div class="info-value">{{ $gatepass->warehouse->warehouse_name ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <div class="info-label">Purchase Order Reference</div>
                                <div class="info-value">{{ $gatepass->purchase_id ? '#PO-'.$gatepass->purchase_id : 'Direct Inward' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Remarks / Note</div>
                                <div class="info-value small text-muted">{{ $gatepass->note ?? 'No special instructions.' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-thead-dark">
                                <tr class="text-center">
                                    <th style="width: 80px;">Sr. No</th>
                                    <th class="text-start">Item Description & Specification</th>
                                    <th style="width: 150px;">Unit Qty</th>
                                    <th style="width: 150px;">Condition</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gatepass->items as $i => $item)
                                    <tr>
                                        <td class="text-center fw-bold">{{ $i+1 }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $item->product->item_name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $item->product->sku ?? '' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="h6 fw-bold m-0">{{ number_format($item->qty) }}</span>
                                        </td>
                                        <td class="text-center text-muted">Good</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">No items recorded.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-5 pt-4">
                        <div class="col-4">
                            <div class="sig-box">Prepared By (Data Entry)</div>
                        </div>
                        <div class="col-4">
                            <div class="sig-box">Security Officer (Check)</div>
                        </div>
                        <div class="col-4">
                            <div class="sig-box">Warehouse Manager (Receive)</div>
                        </div>
                    </div>

                    <div class="mt-5 text-center no-print">
                        <p class="small text-muted italic">System Timestamp: {{ now()->format('d-M-Y H:i A') }}</p>
                    </div>
                </div>

                @if($gatepass->purchase_id && $pendingItems->count() > 0)
                    <div class="alert alert-warning mt-4 no-print border-0 shadow-sm">
                        <h5 class="fw-bold"><i class="bi bi-exclamation-triangle"></i> Pending Items from Purchase #{{ $gatepass->purchase_id }}</h5>
                        <p>Total <strong>{{ $pendingItems->sum('remaining_qty') }}</strong> units are still pending for this PO.</p>
                        <a href="{{ route('inward-gatepass.from-purchase', $gatepass->purchase_id) }}" class="btn btn-warning btn-sm fw-bold">
                            Process Remaining Items
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection