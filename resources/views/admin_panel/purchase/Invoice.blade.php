@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --pi-primary: #0f172a;
        --pi-secondary: #475569;
        --pi-accent: #2563eb;
        --pi-success: #059669;
        --pi-border: #e2e8f0;
        --pi-bg: #f8fafc;
    }

    .pi-container { background-color: #f1f5f9; min-height: 100vh; padding: 2rem 0; font-family: 'Inter', sans-serif; }
    
    .pi-card { 
        background: #fff; 
        border: none; 
        border-radius: 12px; 
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); 
        overflow: hidden;
        max-width: 1000px;
        margin: 0 auto;
    }

    .pi-header-banner {
        background: var(--pi-primary);
        color: #fff;
        padding: 2.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 4px solid var(--pi-accent);
    }

    .pi-title h1 {
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -1px;
        margin: 0;
        text-transform: uppercase;
    }

    .pi-badge {
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
        background: var(--pi-bg);
        border-left: 4px solid var(--pi-border);
    }

    .info-box.accent { border-left-color: var(--pi-accent); }
    .info-box.success { border-left-color: var(--pi-success); }

    .info-label {
        font-size: 0.65rem;
        font-weight: 800;
        color: var(--pi-secondary);
        text-transform: uppercase;
        margin-bottom: 0.25rem;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--pi-primary);
    }

    .erp-table {
        width: 100%;
        border-collapse: collapse;
    }

    .erp-table thead th {
        background: #f1f5f9;
        color: var(--pi-primary);
        font-weight: 800;
        font-size: 0.7rem;
        text-transform: uppercase;
        padding: 1rem;
        border-bottom: 2px solid var(--pi-border);
        text-align: center;
    }

    .erp-table tbody td {
        padding: 1.25rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        vertical-align: middle;
    }

    .item-main { font-weight: 700; color: var(--pi-primary); }
    .item-sub { font-size: 0.75rem; color: var(--pi-secondary); }

    .pi-footer-summary {
        background: #fff;
        padding: 2rem;
        border-top: 1px solid var(--pi-border);
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .summary-total {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 2px solid var(--pi-primary);
        font-size: 1.25rem;
        font-weight: 900;
        color: var(--pi-primary);
    }

    .sig-area {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 3rem;
        padding: 4rem 2rem 2rem;
        text-align: center;
    }

    .sig-line {
        border-top: 1px solid var(--pi-primary);
        margin-top: 2rem;
        padding-top: 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--pi-secondary);
    }

    @media print {
        .no-print { display: none !important; }
        .pi-container { padding: 0; background: #fff; }
        .pi-card { box-shadow: none; border: 1px solid #eee; }
    }
</style>

<div class="pi-container">
    <div class="container">
        
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('Purchase.home') }}" class="btn btn-outline-dark fw-bold">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <h4 class="m-0 fw-800 text-slate-800">Purchase Invoice View</h4>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-dark shadow-sm me-2" style="margin-right: 8px;">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <button onclick="exportPDF()" class="btn btn-danger shadow-sm me-2" style="margin-right: 8px;">
                    <i class="fas fa-file-pdf me-1"></i> PDF
                </button>
                <button onclick="shareWhatsApp()" class="btn btn-success shadow-sm" style="background: #25D366; border-color: #25D366;">
                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                </button>
            </div>
        </div>

        <div id="pi-content" class="pi-card">
            <!-- Header Banner -->
            <div class="pi-header-banner">
                <div class="pi-title">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <h1>PURCHASE INVOICE</h1>
                        <span class="pi-badge" style="background: #dcfce7; color: #166534;">
                            RECEIVED
                        </span>
                    </div>
                    <div class="opacity-75 small fw-bold text-uppercase tracking-wider">Invoice No: {{ $purchase->invoice_no }}</div>
                </div>
                <div class="text-end">
                    <div class="d-inline-block px-3 py-2 rounded-3" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                        <div class="fw-900 fs-5 text-uppercase tracking-wider"><i class="fas fa-building me-2 text-accent"></i>{{ $purchase->branch->name ?? 'NEW WIJDAN ERP' }}</div>
                        <div class="opacity-75 small text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;"> <i class="fas fa-map-marker-alt me-1"></i> {{ $purchase->branch->address ?? 'Main Distribution Center' }}</div>
                    </div>
                </div>
            </div>

            <!-- Primary Info -->
            <div class="info-grid">
                <div class="info-box accent">
                    <div class="info-label">Invoice Details</div>
                    <div class="info-value mb-1">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') }}</div>
                    <div class="small text-muted text-uppercase" style="font-size: 10px;">Type: {{ ucfirst($purchase->purchase_type) }} Purchase</div>
                </div>
                <div class="info-box success">
                    <div class="info-label">Supplier / Vendor</div>
                    <div class="info-value text-primary mb-1 text-uppercase">{{ $purchase->vendor->name ?? $purchase->vendor_name ?? 'Local Market' }}</div>
                    <div class="small text-muted">{{ $purchase->vendor->phone ?? 'Contact N/A' }}</div>
                </div>
                <div class="info-box">
                    <div class="info-label">Delivery Destination</div>
                    <div class="info-value mb-1">{{ $purchase->warehouse->warehouse_name ?? 'Branch Direct' }}</div>
                    <div class="small text-muted text-uppercase" style="font-size: 10px;">{{ $purchase->warehouse->location ?? 'Shop Stock' }}</div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="px-0">
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="text-align: left;">Item Description</th>
                            <th>Packing</th>
                            <th style="width: 100px;">Qty</th>
                            <th style="width: 120px;">Rate</th>
                            <th style="width: 100px;">Disc</th>
                            <th style="width: 150px; text-align: right;" class="pe-4">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Group items by product, packing type, and unit for professional look
                            $groupedItems = $purchase->items->groupBy(function($item) {
                                return $item->product_id . '-' . $item->packing_type . '-' . $item->unit;
                            });
                            $srNo = 1;
                        @endphp
                        @foreach($groupedItems as $groupKey => $items)
                            @php
                                $first = $items->first();
                                $totalQty = $items->sum('qty');
                                $totalLine = $items->sum('line_total');
                                $totalDisc = $items->sum('item_discount');
                            @endphp
                            <tr>
                                <td class="text-center fw-bold text-slate-400">{{ $srNo++ }}</td>
                                <td>
                                    <div class="item-main">{{ $first->product->item_name ?? 'N/A' }}</div>
                                    <div class="item-sub text-muted small text-uppercase">{{ $first->product->brand_name ?? $first->product->brand->name ?? '' }}</div>
                                    
                                    {{-- Color Breakdown Badges --}}
                                    @if($items->count() > 1 || ($items->count() == 1 && $first->color))
                                        <div class="mt-2 d-flex flex-wrap gap-1">
                                            @foreach($items as $sub)
                                                @if($sub->color)
                                                    <span class="badge bg-white text-primary border border-primary px-2 py-1" style="font-size: 10px; font-weight: 600;">
                                                        {{ strtoupper($sub->color) }}: {{ (float)$sub->qty }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1 small" style="font-size: 10px;">{{ strtoupper($first->packing_type ?? 'Standard') }}</span>
                                    @if($first->packing_qty > 0)
                                        <div class="text-muted mt-1" style="font-size: 10px;">{{ (float)$first->packing_qty }} x {{ (float)$first->item_per_piece }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold">{{ (float)$totalQty }}</div>
                                    <div class="small text-muted text-uppercase" style="font-size: 9px;">{{ $first->unit }}</div>
                                </td>
                                <td class="text-center fw-bold">{{ number_format($first->price, 2) }}</td>
                                <td class="text-center text-danger small">-{{ number_format($totalDisc, 2) }}</td>
                                <td class="text-end pe-4">
                                    <div class="fw-900 text-primary">{{ number_format($totalLine, 2) }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Financial Summary -->
            <div class="pi-footer-summary">
                <div class="row">
                    <div class="col-md-7">
                        <div class="p-3 rounded-3" style="background: #f1f5f9; border: 1px dashed var(--pi-secondary);">
                            <h6 class="fw-bold text-uppercase small mb-2 text-secondary">Note / Remarks</h6>
                            <p class="small text-muted m-0 italic">{{ $purchase->note ?? 'No additional remarks for this invoice.' }}</p>
                        </div>
                        
                        {{-- Payment Breakdown if any --}}
                        @if($purchase->paid_amount > 0)
                            <div class="mt-3 p-3 rounded-3 border">
                                <h6 class="fw-bold text-uppercase small mb-2 text-success"><i class="fas fa-check-circle me-1"></i> Payment Settlement</h6>
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted">Paid via Cash/Bank</span>
                                    <span class="fw-bold">PKR {{ number_format($purchase->paid_amount, 2) }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-5">
                        <div class="summary-item">
                            <span class="text-muted uppercase fw-bold small">Gross Subtotal</span>
                            <span class="fw-bold">{{ number_format($purchase->subtotal, 2) }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="text-muted uppercase fw-bold small">Extra Cost (+)</span>
                            <span class="fw-bold text-info">+{{ number_format($purchase->extra_cost, 2) }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="text-muted uppercase fw-bold small">Overall Discount (-)</span>
                            <span class="fw-bold text-danger">-{{ number_format($purchase->discount, 2) }}</span>
                        </div>
                        <div class="summary-total d-flex justify-content-between align-items-center">
                            <span class="text-uppercase tracking-tighter">Net Payable</span>
                            <span>PKR {{ number_format($purchase->net_amount, 2) }}</span>
                        </div>
                        
                        <div class="mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between text-success fw-bold small">
                                <span class="text-uppercase">Amount Paid</span>
                                <span>{{ number_format($purchase->paid_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between text-danger fw-black fs-5 mt-1">
                                <span class="text-uppercase small align-self-center">Due Balance</span>
                                <span>{{ number_format($purchase->due_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="sig-area">
                <div class="sig-line">Purchase Officer</div>
                <div class="sig-line">Warehouse Receiver</div>
                <div class="sig-line">Accounts Approved</div>
            </div>

            <div class="p-4 text-center border-top bg-light">
                <div class="small text-muted italic">"This is a system generated Purchase Invoice and does not require a physical signature for digital audit."</div>
                <div class="mt-2 small fw-bold text-slate-400 text-uppercase tracking-widest" style="font-size: 9px;">Powered by New Wijdan ERP | Generated: {{ now()->format('d M Y | h:i A') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function exportPDF() {
        Swal.fire({
            title: 'Generating PDF...',
            text: 'Preparing professional purchase invoice.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const element = document.getElementById('pi-content');
        const opt = {
            margin: [0.2, 0.2, 0.2, 0.2],
            filename: 'Purchase_Invoice_{{ $purchase->invoice_no }}.pdf',
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
            title: 'Preparing Share...',
            text: 'Generating ERP PDF for WhatsApp.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const element = document.getElementById('pi-content');
        const opt = {
            margin: [0.1, 0.1, 0.1, 0.1],
            filename: 'Purchase_Invoice_{{ $purchase->invoice_no }}.pdf',
            image: { type: 'jpeg', quality: 1.0 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then((pdfBlob) => {
            const file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
            
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'Purchase Invoice',
                    text: 'Please find the attached Purchase Invoice #{{ $purchase->invoice_no }}.',
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
            title: 'Share via WhatsApp',
            text: 'PDF generated. It will download now, then WhatsApp will open. Please attach the file manually.',
            confirmButtonText: 'Download & Open WA'
        }).then(() => {
            const url = URL.createObjectURL(pdfBlob);
            const a = document.createElement('a');
            a.href = url; a.download = filename;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            
            const msg = "*Purchase Invoice #{{ $purchase->invoice_no }}*\nGenerated via New Wijdan ERP.";
            window.open("https://wa.me/?text=" + encodeURIComponent(msg), '_blank');
        });
    }
</script>
@endsection

