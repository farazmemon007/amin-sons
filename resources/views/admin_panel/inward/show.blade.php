@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --gp-primary: #0f172a;
        --gp-secondary: #475569;
        --gp-accent: #3b82f6;
        --gp-success: #10b981;
        --gp-border: #e2e8f0;
        --gp-bg: #f8fafc;
    }

    .osm-container { background-color: #f1f5f9; min-height: 100vh; padding: 2rem 0; font-family: 'Inter', sans-serif; }
    
    .gp-card { 
        background: #fff; 
        border: none; 
        border-radius: 12px; 
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); 
        overflow: hidden;
        max-width: 1000px;
        margin: 0 auto;
    }

    .gp-header-banner {
        background: var(--gp-primary);
        color: #fff;
        padding: 2.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 4px solid var(--gp-accent);
    }

    .gp-title h1 {
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -1px;
        margin: 0;
        text-transform: uppercase;
    }

    .gp-badge {
        padding: 0.4rem 1rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        padding: 2rem;
        background: #fff;
    }

    .info-box {
        padding: 1rem;
        border-radius: 8px;
        background: var(--gp-bg);
        border-left: 4px solid var(--gp-border);
    }

    .info-box.accent { border-left-color: var(--gp-accent); }
    .info-box.success { border-left-color: var(--gp-success); }

    .info-label {
        font-size: 0.65rem;
        font-weight: 800;
        color: var(--gp-secondary);
        text-transform: uppercase;
        margin-bottom: 0.25rem;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--gp-primary);
    }

    .transport-section {
        margin: 0 2rem;
        padding: 1.5rem;
        background: #fdfdfd;
        border: 1px solid var(--gp-border);
        border-radius: 8px;
    }

    .erp-table {
        width: 100%;
        margin-top: 2rem;
        border-collapse: collapse;
    }

    .erp-table thead th {
        background: #f1f5f9;
        color: var(--gp-primary);
        font-weight: 800;
        font-size: 0.7rem;
        text-transform: uppercase;
        padding: 1rem;
        border-bottom: 2px solid var(--gp-border);
        text-align: center;
    }

    .erp-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        vertical-align: middle;
    }

    .item-main { font-weight: 700; color: var(--gp-primary); }
    .item-sub { font-size: 0.75rem; color: var(--gp-secondary); font-family: monospace; }

    .gp-footer-valuation {
        background: var(--gp-primary);
        color: #fff;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sig-area {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 3rem;
        padding: 4rem 2rem 2rem;
        text-align: center;
    }

    .sig-line {
        border-top: 1px solid var(--gp-primary);
        margin-top: 2rem;
        padding-top: 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--gp-secondary);
    }

    @media print {
        .no-print { display: none !important; }
        .osm-container { padding: 0; background: #fff; }
        .gp-card { box-shadow: none; border: 1px solid #eee; }
    }
</style>

<div class="osm-container">
    <div class="container">
        
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('InwardGatepass.home') }}" class="btn btn-outline-dark fw-bold">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <h4 class="m-0 fw-800 text-slate-800">Inward Document View</h4>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-dark shadow-sm">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <button onclick="exportPDF()" class="btn btn-danger shadow-sm">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </button>
                <button onclick="shareWhatsApp()" class="btn btn-success shadow-sm" style="background: #25D366; border-color: #25D366;">
                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                </button>
            </div>
        </div>

        <div id="gp-content" class="gp-card">
            <!-- Header Banner -->
            <div class="gp-header-banner">
                <div class="gp-title">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <h1>INWARD GATEPASS</h1>
                        <span class="gp-badge" style="background: {{ $gatepass->display_status == 'completed' ? '#dcfce7' : '#fef3c7' }}; color: {{ $gatepass->display_status == 'completed' ? '#166534' : '#92400e' }};">
                            {{ $gatepass->display_status }}
                        </span>
                    </div>
                    <div class="opacity-75 small fw-bold">GRN DOCUMENT: #GP-{{ str_pad($gatepass->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div class="text-end">
                    <div class="d-inline-block px-3 py-2 rounded-3" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                        <div class="fw-900 fs-5 text-uppercase tracking-wider"><i class="fas fa-building me-2 text-accent"></i>{{ $gatepass->branch->name ?? 'NEW WIJDAN ERP' }}</div>
                        <div class="opacity-75 small text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;"> <i class="fas fa-map-marker-alt me-1"></i> {{ $gatepass->branch->address ?? 'Main Distribution Center' }}</div>
                    </div>
                </div>
            </div>

            <!-- Primary Info -->
            <div class="info-grid">
                <div class="info-box accent">
                    <div class="info-label">Arrival Information</div>
                    <div class="info-value mb-1">{{ \Carbon\Carbon::parse($gatepass->gatepass_date)->format('d M, Y') }}</div>
                    <div class="small text-muted">{{ \Carbon\Carbon::parse($gatepass->created_at)->format('h:i A') }} (Entry Time)</div>
                </div>
                <div class="info-box success">
                    <div class="info-label">Vendor / Supplier</div>
                    <div class="info-value text-primary mb-1">{{ $gatepass->vendor->name ?? 'N/A' }}</div>
                    <div class="small text-muted">{{ $gatepass->vendor->phone ?? 'Contact not listed' }}</div>
                </div>
                <div class="info-box">
                    <div class="info-label">Warehouse & Reference</div>
                    <div class="info-value mb-1">{{ $gatepass->warehouse->warehouse_name ?? 'Default WH' }}</div>
                    <div class="small fw-bold text-accent">
                        @if($gatepass->purchase_order_id)
                            Ref: PO #{{ $gatepass->purchaseOrder->po_number ?? $gatepass->purchase_order_id }}
                        @elseif($gatepass->purchase_id)
                            Ref: PI #{{ $gatepass->purchase->invoice_no ?? $gatepass->purchase_id }}
                        @else
                            Ref: Direct Stock Inward
                        @endif
                    </div>
                </div>
            </div>

            <!-- Transport Details -->
            <div class="transport-section">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="info-label">Logistics Provider</div>
                        <div class="info-value small">{{ $gatepass->transport_name ?? 'Self Transport' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-label">Vehicle & Driver</div>
                        <div class="info-value small">{{ $gatepass->vehicle_no ?? 'N/A' }} ({{ $gatepass->driver_name ?? 'N/A' }})</div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-label">Bilty / DO Number</div>
                        <div class="info-value small">{{ $gatepass->bilty_no ?? 'N/A' }} / {{ $gatepass->delivery_challan_no ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-3 text-end">
                        <div class="info-label">Freight Info</div>
                        <div class="info-value text-danger">PKR {{ number_format($gatepass->freight_charges, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="px-4">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="text-align: left;">Item Description</th>
                            <th style="width: 150px;">Brand</th>
                            <th style="width: 120px;">Packing</th>
                            <th style="width: 120px;">Unit</th>
                            <th style="width: 150px;">Received Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedItems = $gatepass->items->groupBy('product_id');
                            $srNo = 1;
                        @endphp
                        @forelse($groupedItems as $productId => $items)
                            @php
                                $firstItem = $items->first();
                                $totalProductQty = $items->sum('qty');
                            @endphp
                            <tr>
                                <td class="text-center fw-bold text-slate-400">{{ $srNo++ }}</td>
                                <td>
                                    <div class="item-main">{{ $firstItem->product->item_name ?? 'N/A' }}</div>
                                    <div class="item-sub">{{ $firstItem->product->item_code ?? 'N/A' }}</div>
                                    
                                    {{-- Color Breakdown --}}
                                    @if($items->count() > 1 || ($items->count() == 1 && $firstItem->color))
                                        <div class="mt-2 d-flex flex-wrap gap-1">
                                            @foreach($items as $subItem)
                                                @if($subItem->color)
                                                    <span class="badge bg-white text-primary border border-primary px-2 py-1" style="font-size: 10px; font-weight: 600;">
                                                        <i class="fas fa-palette me-1"></i> {{ strtoupper($subItem->color) }}: {{ number_format($subItem->qty) }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-white text-dark border px-3 py-2 fw-700">{{ $firstItem->product->brand->name ?? '-' }}</span>
                                </td>
                                <td class="text-center small fw-bold text-slate-600">
                                    {{ $firstItem->packing_type ?? 'Standard' }}
                                    @if($firstItem->packing_qty > 0)
                                        <div class="text-muted" style="font-size: 9px;">{{ $firstItem->packing_qty }} x {{ $firstItem->item_per_piece }}</div>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ $firstItem->unit ?? $firstItem->product->unit->name ?? 'PCS' }}</td>
                                <td class="text-center">
                                    <div class="fs-5 fw-900 text-primary">{{ number_format($totalProductQty) }}</div>
                                    @if($items->count() > 1)
                                        <div class="small text-muted" style="font-size: 10px;">Total Combined</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No items found in this gatepass.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Valuation -->
            <div class="gp-footer-valuation mt-4">
                <div class="small fw-bold opacity-75 text-uppercase letter-spacing-1">
                    Certified Goods Receipt Note Summary
                </div>
                <div class="text-end">
                    <div class="info-label text-white opacity-50 mb-1">Total Received Units</div>
                    <div class="fs-3 fw-900">{{ number_format($gatepass->items->sum('qty')) }} <small class="fs-6 opacity-75">Items</small></div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="sig-area">
                <div class="sig-line">Prepared & Verified</div>
                <div class="sig-line">Security Check In</div>
                <div class="sig-line">Warehouse Manager</div>
            </div>

            <div class="p-4 text-center border-top bg-light">
                <div class="small text-muted italic">"This is a system generated GRN and does not require a physical signature for digital audit."</div>
                <div class="mt-2 small fw-bold text-slate-400">TIMESTAMP: {{ now()->format('d M Y | h:i A') }}</div>
            </div>
        </div>

        @if($gatepass->purchase_id && $pendingItems->count() > 0)
            <div class="gp-card mt-4 p-4 no-print border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-800 text-warning m-0">PENDING SHIPMENT ALERT</h5>
                        <p class="m-0 text-slate-500">There are still <strong>{{ $pendingItems->sum('remaining_qty') }} units</strong> remaining from this purchase reference.</p>
                    </div>
                    <a href="{{ route('inward-gatepass.from-purchase', $gatepass->purchase_id) }}" class="btn btn-warning fw-bold px-4">Process Remaining</a>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function exportPDF() {
        Swal.fire({
            title: 'Generating Professional GRN...',
            text: 'Please wait while we prepare your ERP document.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const element = document.getElementById('gp-content');
        const opt = {
            margin: [0.2, 0.2, 0.2, 0.2],
            filename: 'GRN_#{{ str_pad($gatepass->id, 6, "0", STR_PAD_LEFT) }}.pdf',
            image: { type: 'jpeg', quality: 1.0 },
            html2canvas: { scale: 3, useCORS: true, letterRendering: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            Swal.close();
        });
    }

    function shareWhatsApp() {
        Swal.fire({
            title: 'Preparing Shareable Document...',
            text: 'Generating high-quality ERP PDF for WhatsApp.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const element = document.getElementById('gp-content');
        const opt = {
            margin: [0.1, 0.1, 0.1, 0.1],
            filename: 'GRN_#{{ str_pad($gatepass->id, 6, "0", STR_PAD_LEFT) }}.pdf',
            image: { type: 'jpeg', quality: 1.0 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then((pdfBlob) => {
            const file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
            
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'Inward Gatepass (GRN)',
                    text: 'Please find the attached Goods Receipt Note (GRN) #{{ str_pad($gatepass->id, 6, "0", STR_PAD_LEFT) }}.',
                    files: [file]
                }).then(() => Swal.close())
                .catch(() => fallbackWaShare(pdfBlob, opt.filename));
            } else {
                fallbackWaShare(pdfBlob, opt.filename);
            }
        });
    }

    function fallbackWaShare(pdfBlob, filename) {
        Swal.fire({
            icon: 'info',
            title: 'Manual Share Required',
            text: 'PDF generated. It will download now, then WhatsApp will open. Please attach the file manually.',
            confirmButtonText: 'Download & Open WA'
        }).then(() => {
            const url = URL.createObjectURL(pdfBlob);
            const a = document.createElement('a');
            a.href = url; a.download = filename;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            
            const msg = "*Inward Gatepass (GRN) #{{ str_pad($gatepass->id, 6, "0", STR_PAD_LEFT) }}*\nGenerated via New Wijdan ERP.";
            window.open("https://wa.me/?text=" + encodeURIComponent(msg), '_blank');
        });
    }
</script>
@endsection