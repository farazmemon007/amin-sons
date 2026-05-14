@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --erp-primary: #1e293b;
        --erp-secondary: #64748b;
        --erp-accent: #3b82f6;
        --erp-success: #10b981;
        --erp-warning: #f59e0b;
        --erp-danger: #ef4444;
        --erp-bg: #f8fafc;
    }

    .po-container { background-color: var(--erp-bg); min-height: 100vh; padding: 2rem 0; }
    .po-card { 
        background: #fff; 
        border: none; 
        border-radius: 12px; 
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .po-header-bar {
        background: var(--erp-primary);
        color: #fff;
        padding: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .po-status-badge {
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    .info-card {
        background: #f1f5f9;
        border-radius: 8px;
        padding: 1.5rem;
        height: 100%;
        border-left: 4px solid var(--erp-accent);
    }

    .info-label {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--erp-secondary);
        margin-bottom: 0.25rem;
        letter-spacing: 0.025em;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--erp-primary);
    }

    .erp-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .erp-table thead th {
        background: #f8fafc;
        color: var(--erp-secondary);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.7rem;
        padding: 1rem;
        border-bottom: 2px solid #e2e8f0;
        letter-spacing: 0.05em;
    }
    .erp-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .total-section {
        background: var(--erp-primary);
        color: #fff;
        padding: 2rem;
        border-radius: 0 0 12px 12px;
    }

    .btn-erp {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s;
    }

    @media print {
        .no-print { display: none !important; }
        .po-container { padding: 0; background: #fff; }
        .po-card { box-shadow: none; border: 1px solid #e2e8f0; }
    }
</style>

<div class="po-container">
    <div class="container">
        <!-- Actions Top Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('purchase_orders.index') }}" class="btn btn-outline-secondary shadow-sm btn-erp mr-3">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
                <h4 class="m-0 fw-bold text-slate-800">PO Details</h4>
            </div>
            <div class="d-flex gap-2">
                <button onclick="showExportOptions()" class="btn btn-dark shadow-sm btn-erp mr-2">
                    <i class="fas fa-file-pdf mr-1"></i> Export
                </button>
                <button onclick="shareWhatsApp()" class="btn btn-success shadow-sm btn-erp mr-2" style="background-color: #25d366; border-color: #25d366; color: #fff;">
                    <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                </button>
                @if($order->status !== 'cancelled')
                    <a href="{{ route('inward-gatepass.from-po', $order->id) }}" class="btn btn-primary shadow-sm btn-erp">
                        <i class="fas fa-file-import"></i> Generate Gatepass
                    </a>
                @endif
            </div>
        </div>

        <div class="po-card shadow-lg">
            <!-- ERP Header -->
            <div class="po-header-bar">
                <div>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <h2 class="m-0 fw-800" style="letter-spacing: -0.5px;">PURCHASE ORDER</h2>
                        @php
                            $statusMap = [
                                'pending' => ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => 'Pending Approval'],
                                'partially_received' => ['bg' => '#e0f2fe', 'color' => '#075985', 'label' => 'Partial Intake'],
                                'received' => ['bg' => '#dcfce7', 'color' => '#166534', 'label' => 'Completed'],
                                'cancelled' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => 'Voided']
                            ];
                            $s = $statusMap[$order->status] ?? ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => $order->status];
                        @endphp
                        <span class="po-status-badge" style="background: {{ $s['bg'] }}; color: {{ $s['color'] }};">
                            {{ $s['label'] }}
                        </span>
                    </div>
                    <p class="m-0 opacity-75 small fw-bold">Official Document: #{{ $order->po_number }}</p>
                </div>
                <div class="text-end">
                    <h3 class="m-0 fw-bold">{{ $order->branch->name ?? 'NEW WIJDAN ERP' }}</h3>
                    <p class="m-0 small opacity-75">{{ $order->branch->address ?? 'Main Operations Center' }}</p>
                </div>
            </div>

            <!-- Content Body -->
            <div class="p-4 p-md-5">
                <div class="row g-4 mb-5">
                    <!-- PO Summary -->
                    <div class="col-md-3">
                        <div class="info-card">
                            <div class="mb-2">
                                <div class="info-label text-accent">Order Date</div>
                                <div class="info-value small">{{ \Carbon\Carbon::parse($order->order_date)->format('d M, Y') }}</div>
                            </div>
                            <div class="mb-2">
                                <div class="info-label text-danger">Exp. Delivery</div>
                                <div class="info-value text-danger small">{{ $order->expected_date ? \Carbon\Carbon::parse($order->expected_date)->format('d M, Y') : 'TBD' }}</div>
                            </div>
                            <div class="mb-0">
                                <div class="info-label text-primary">Warehouse</div>
                                <div class="info-value text-primary small">{{ $order->warehouse->warehouse_name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Vendor Info -->
                    <div class="col-md-5">
                        <div class="info-card" style="border-left-color: var(--erp-primary);">
                            <div class="info-label">Vendor / Supplier</div>
                            <div class="info-value mb-2" style="font-size: 1.25rem;">{{ $order->vendor->name ?? 'N/A' }}</div>
                            
                            @if(!empty($order->vendor->company_names))
                                <div class="mb-2">
                                    @foreach($order->vendor->company_names as $company)
                                        <span class="badge bg-white text-dark border me-1 small fw-bold">{{ $company }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="small text-muted d-flex align-items-start gap-2">
                                <i class="fas fa-map-marker-alt mt-1"></i>
                                <span>{{ $order->vendor->address ?? 'No address registered' }}</span>
                            </div>
                            @if($order->vendor->phone)
                                <div class="small text-muted mt-1 d-flex align-items-center gap-2">
                                    <i class="fas fa-phone-alt"></i>
                                    <span>{{ $order->vendor->phone }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Brand / Note Info -->
                    <div class="col-md-4">
                        <div class="info-card" style="border-left-color: var(--erp-success);">
                            @if(!empty($brandNames))
                                <div class="mb-3">
                                    <div class="info-label">Deals In (Brands)</div>
                                    <div class="text-success fw-bold small">
                                        <i class="fas fa-check-circle me-1"></i> {{ implode(', ', $brandNames) }}
                                    </div>
                                </div>
                            @endif
                            <div class="mb-0">
                                <div class="info-label">Instructions</div>
                                <div class="small text-slate-600 italic">"{{ $order->note ?? 'Standard procurement terms apply.' }}"</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive">
                    <table class="erp-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 60px;">ID</th>
                                <th>Product Details</th>
                                <th class="text-center">Brand</th>
                                <th class="text-center">UoM</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-center">Ordered</th>
                                <th class="text-center">Pending</th>
                                <th class="text-end">Extension</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $groupedItems = [];
                                foreach($order->items as $item) {
                                    $key = $item->product_id . '_' . $item->unit_price;
                                    if (!isset($groupedItems[$key])) {
                                        $groupedItems[$key] = [
                                            'product' => $item->product,
                                            'colors' => [],
                                            'unit' => $item->unit,
                                            'unit_price' => $item->unit_price,
                                            'qty' => 0,
                                            'received_qty' => 0,
                                            'line_total' => 0,
                                        ];
                                    }
                                    if ($item->color) {
                                        $groupedItems[$key]['colors'][] = strtoupper($item->color) . ' (' . $item->qty . ')';
                                    }
                                    $groupedItems[$key]['qty'] += $item->qty;
                                    $groupedItems[$key]['received_qty'] += $item->received_qty;
                                    $groupedItems[$key]['line_total'] += $item->line_total;
                                }
                            @endphp
                            @foreach(array_values($groupedItems) as $i => $item)
                                <tr>
                                    <td class="text-center fw-bold text-slate-400">{{ $i+1 }}</td>
                                    <td>
                                        <div class="fw-bold text-slate-800">{{ $item['product']->item_name ?? 'N/A' }}</div>
                                        <div class="text-slate-400 mb-1" style="font-size: 0.75rem; font-family: monospace;">{{ $item['product']->item_code ?? 'NO-CODE' }}</div>
                                        @if(count($item['colors']) > 0)
                                            <div class="mt-1">
                                                @foreach($item['colors'] as $c)
                                                    <span class="badge bg-white text-primary border border-primary fw-bold mb-1" style="font-size: 0.65rem; display: inline-block;">{{ $c }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item['product'] && $item['product']->brand)
                                            <span class="badge bg-light text-dark border fw-bold" style="font-size: 0.75rem;">{{ $item['product']->brand->name }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center small fw-bold text-slate-600">{{ strtoupper($item['unit'] ?? $item['product']->unit->name ?? 'PCS') }}</td>
                                    <td class="text-end fw-600">{{ number_format($item['unit_price'], 2) }}</td>
                                    <td class="text-center fw-bold">{{ number_format($item['qty']) }}</td>
                                    <td class="text-center">
                                        @php $pending = $item['qty'] - $item['received_qty']; @endphp
                                        <span class="{{ $pending > 0 ? 'text-danger fw-bold' : 'text-slate-300' }}">
                                            {{ number_format($pending) }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-800 text-slate-900">{{ number_format($item['line_total'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ERP Footer / Total -->
            <div class="total-section">
                <div class="row align-items-center">
                    <div class="col-md-7 mb-3 mb-md-0">
                        <div class="p-3 rounded" style="background: rgba(255,255,255,0.05); border-left: 4px solid var(--erp-accent);">
                            <div class="info-label text-white opacity-50 mb-1">Total Payable (In Words)</div>
                            <div class="fw-bold text-white" style="font-size: 1.1rem; font-family: serif; letter-spacing: 0.05em;">
                                {{ $order->amountInWords() }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <div class="info-label text-white opacity-50">Grand Total Amount</div>
                        <div class="display-6 fw-900">Rs. {{ number_format($order->total_amount, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authorization Section -->
        <div class="row mt-5 pt-4 text-center">
            <div class="col-md-4 mb-4">
                <div class="mx-auto mb-3" style="width: 150px; border-bottom: 1px solid #cbd5e1; height: 40px;"></div>
                <div class="info-label">Prepared By</div>
                <div class="small fw-bold">{{ $order->creator->name ?? 'System Gen' }}</div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="mx-auto mb-3" style="width: 150px; border-bottom: 1px solid #cbd5e1; height: 40px;"></div>
                <div class="info-label">Verified By</div>
                <div class="small text-muted">Internal Audit Dept</div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="mx-auto mb-3" style="width: 150px; border-bottom: 2px solid var(--erp-primary); height: 40px;"></div>
                <div class="info-label">Authorized Signature</div>
                <div class="small text-muted">Operations Manager</div>
            </div>
        </div>

        <div class="mt-5 text-center no-print">
            <p class="small text-muted">
                <i class="fas fa-history"></i> System Log: PO Created at {{ $order->created_at->format('d-M-Y h:i A') }} | Last Modified: {{ $order->updated_at->format('d-M-Y h:i A') }}
            </p>
        </div>
    </div>
</div>

<!-- Scripts for Export & WhatsApp -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    // Export Options (PDF/Excel)
    function showExportOptions() {
        Swal.fire({
            title: 'Export Purchase Order',
            text: 'Choose your preferred format:',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-file-excel me-1"></i> Excel (CSV)',
            cancelButtonText: '<i class="fas fa-file-pdf me-1"></i> PDF',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                exportCSV();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                exportPDF();
            }
        });
    }

    // PDF Export via html2pdf
    function exportPDF() {
        Swal.fire({
            title: 'Generating PDF...',
            text: 'Please wait while your document is being prepared.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const element = document.querySelector('.po-card');
        const opt = {
            margin: 0.2,
            filename: 'PO_{{ $order->po_number }}.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            Swal.close();
        });
    }

    // CSV Export (Excel)
    function exportCSV() {
        let rows = [
            ['Purchase Order', '#{{ $order->po_number }}'],
            ['Date', '{{ $order->order_date->format("d-M-Y") }}'],
            ['Vendor', '{{ $order->vendor->name ?? "N/A" }}'],
            ['Total Amount', '{{ $order->total_amount }}'],
            [''],
            ['ID', 'Product', 'Brand', 'Color', 'Qty', 'Rate', 'Total']
        ];

        @foreach(array_values($groupedItems) as $i => $item)
            rows.push([
                '{{ $i + 1 }}',
                '{{ $item['product']->item_name ?? "N/A" }}',
                '{{ $item['product']->brand->name ?? "-" }}',
                '{{ count($item['colors']) > 0 ? implode(" | ", $item['colors']) : "-" }}',
                '{{ $item['qty'] }}',
                '{{ $item['unit_price'] }}',
                '{{ $item['line_total'] }}'
            ]);
        @endforeach

        let csv = rows.map(r => r.map(cell => `"${cell}"`).join(',')).join('\n');
        let blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
        let url = URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = 'PO_{{ $order->po_number }}.csv';
        a.click();
    }

    // WhatsApp Sharing Logic
    function shareWhatsApp() {
        Swal.fire({
            title: 'Preparing WhatsApp Share...',
            text: 'Generating document.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const element = document.querySelector('.po-card');
        const opt = {
            margin: 0.2,
            filename: 'PO_{{ $order->po_number }}.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
            const file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
            
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'Purchase Order #{{ $order->po_number }}',
                    text: 'Please find the attached Purchase Order from {{ $order->branch->name ?? "New Wijdan" }}.',
                    files: [file]
                }).then(() => {
                    Swal.close();
                }).catch((error) => {
                    fallbackWaShare(pdfBlob, opt.filename);
                });
            } else {
                fallbackWaShare(pdfBlob, opt.filename);
            }
        });
    }

    function fallbackWaShare(pdfBlob, filename) {
        Swal.fire({
            icon: 'info',
            title: 'Share via WhatsApp',
            text: 'The PDF will be downloaded. Please attach it manually in WhatsApp.',
            confirmButtonText: 'Download & Open WhatsApp'
        }).then(() => {
            const url = URL.createObjectURL(pdfBlob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            
            const msg = "*Purchase Order #{{ $order->po_number }}*\nTotal: Rs. {{ number_format($order->total_amount, 2) }}\nFrom: {{ $order->branch->name ?? 'New Wijdan' }}";
            
            // Smart WhatsApp Link: Detect mobile vs desktop
            const isMobile = /iPhone|Android|iPad/i.test(navigator.userAgent);
            let waUrl = "";
            
            if (isMobile) {
                // Use protocol for mobile apps
                waUrl = "whatsapp://send?text=" + encodeURIComponent(msg);
            } else {
                // Use api.whatsapp for desktop (handles web fallback better)
                waUrl = "https://api.whatsapp.com/send?text=" + encodeURIComponent(msg);
            }
            
            window.open(waUrl, '_blank');
        });
    }
</script>
@endsection
