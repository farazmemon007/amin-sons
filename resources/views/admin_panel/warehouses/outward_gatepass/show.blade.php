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
        <div class="d-flex gap-2">
            <button type="button" onclick="shareWhatsApp()" class="btn btn-outline-success btn-sm shadow-sm" style="border-color:#25D366; color:#25D366; background: #fff;">
                <i class="fab fa-whatsapp me-1"></i> WhatsApp
            </button>
            <button type="button" onclick="showExportOptions()" class="btn btn-outline-info btn-sm shadow-sm" style="background: #fff;">
                <i class="fas fa-download me-1"></i> Export
            </button>
            <div class="btn-group shadow-sm">
                <a href="{{ route('OutwardGatepass.list') }}" class="btn btn-white btn-sm border"><i class="fa fa-list me-1"></i> List</a>
                @can('outward.gatepass.print')
                    <a href="{{ route('OutwardGatepass.pdf', $gp->id) }}" class="btn btn-white btn-sm border text-danger"><i class="fa fa-file-pdf me-1"></i> PDF</a>
                @endcan
                <button onclick="window.print()" class="btn btn-white btn-sm border"><i class="fa fa-print me-1"></i> Print</button>
                <a href="#" id="thermalBtn" class="btn btn-warning btn-sm"><i class="fa fa-receipt me-1"></i> Thermal</a>
            </div>
        </div>
    </div>

    <div class="card gp-card shadow-sm" id="gpContent">
        <div class="card-body p-4">
            {{-- Document Branding --}}
            <div class="row mb-4">
                <div class="col-6">
                    <h2 class="fw-bold text-primary mb-0">GATE PASS</h2>
                    <p class="text-muted small">Official Outward Document</p>
                </div>
                <div class="col-md-6 text-end" style="position: relative;">
                    <div class="p-3 d-inline-block rounded shadow-sm border border-primary border-opacity-10 text-center" 
                        style="background: #f8fbff; min-width: 230px; position: absolute; right: 0; top: -10px; z-index: 10; border-top: 4px solid #3c8dbc;">
                        <span class="label-title d-block text-primary mb-1" style="font-size: 13px; font-weight: 700;">Gatepass Number</span>
                        <div class="h4 fw-bold mb-1 text-primary" style="letter-spacing: -0.5px;">{{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</div>
                        @if($gp->branch_id)
                            <div class="text-primary fw-bold small opacity-75" style="font-size: 11px; margin-bottom: 5px;">{{ \App\Models\Branch::find($gp->branch_id)?->name ?? 'amin$sons' }}</div>
                        @else
                            <div class="text-primary fw-bold small opacity-75" style="font-size: 11px; margin-bottom: 5px;">amin$sons</div>
                        @endif
                        
                        <div style="border-top: 1px dashed #3c8dbc; opacity: 0.3; margin: 8px 0;"></div>
                        
                        <div class="text-muted small text-start px-2">
                            <div class="d-flex justify-content-between">
                                <strong>Date:</strong> 
                                <span>{{ optional($gp->created_at)->format('d-M-Y') }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <strong>Invoice:</strong> 
                                <span>{{ $gp->invoice_no ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 pt-2" style="margin-top: 75px;">
                {{-- Left Column: Order Info --}}
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm" style="background: #f1f5f9; border: 1px solid #cbd5e1 !important; border-radius: 12px;">
                        <div class="card-body p-3">
                            <div class="section-divider mb-3" style="border-left-color: #1e293b;"><h6 class="mb-0 fw-bold text-dark">Primary Details</h6></div>
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
                    </div>
                </div>

                {{-- Middle Column: Transport --}}
                <div class="col-md-5">
                    <div class="card h-100 border-0 shadow-sm" style="background: #f1f5f9; border: 1px solid #cbd5e1 !important; border-radius: 12px;">
                        <div class="card-body p-3">
                            <div class="section-divider mb-3" style="border-left-color: #1e293b;"><h6 class="mb-0 fw-bold text-dark">Logistics & Transport</h6></div>
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
                                @if(!empty($gp->transport_receipt_path))
                                <div class="col-12 mt-2">
                                    <div class="mb-2 text-center">
                                        <img src="{{ route('OutwardGatepass.receiptFile', $gp->id) }}" 
                                             onclick="viewTransportReceipt('{{ $gp->id }}', '{{ route('OutwardGatepass.receiptFile', $gp->id) }}')"
                                             alt="Receipt Preview" 
                                             style="max-width: 100%; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1; cursor: pointer; transition: all 0.2s;"
                                             onmouseover="this.style.borderColor='#2563eb'; this.style.transform='scale(1.02)';"
                                             onmouseout="this.style.borderColor='#cbd5e1'; this.style.transform='scale(1)';"
                                             title="Click to Zoom">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-success w-100 shadow-sm" onclick="viewTransportReceipt('{{ $gp->id }}', '{{ route('OutwardGatepass.receiptFile', $gp->id) }}')">
                                        <i class="fas fa-image me-1"></i> View Full Image
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Warehouse/Issuer --}}
                <div class="col-md-3">
                    <div class="card h-100 border-0 shadow-sm" style="background: #f1f5f9; border: 1px solid #cbd5e1 !important; border-radius: 12px;">
                        <div class="card-body p-3">
                            <div class="section-divider mb-3" style="border-left-color: #1e293b;"><h6 class="mb-0 fw-bold text-dark">Origins</h6></div>
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
                            <div class="bg-white p-2 rounded mt-4 border border-secondary border-opacity-25 shadow-sm">
                                <label class="label-title">Bilty Amount</label>
                                <span class="h6 mb-0 d-block fw-bold text-primary">Rs. {{ $gp->billty_amount ? number_format($gp->billty_amount, 2) : '0.00' }}</span>
                            </div>
                        </div>
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

{{-- ✅ VIEW TRANSPORT RECEIPT MODAL --}}
<div class="modal fade" id="viewReceiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 90vw;">
        <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                <h5 class="modal-title"><i class="fas fa-image me-2"></i> Transport Receipt Image</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light" onclick="rotateReceiptImage(-90)"><i class="fas fa-redo"></i></button>
                    <button type="button" class="btn btn-sm btn-light" onclick="rotateReceiptImage(90)"><i class="fas fa-undo"></i></button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body text-center bg-light" style="max-height: 80vh; overflow: auto;">
                <div id="receiptImageContainer" style="display: inline-block; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <img id="fullReceiptImage" src="" style="max-width: 100%; transition: transform 0.3s ease;">
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <small class="text-muted"><i class="fas fa-info-circle"></i> Use rotation buttons if image is sideways</small>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a id="downloadReceiptLink" href="#" class="btn btn-success" download>
                        <i class="fas fa-download me-1"></i> Download
                    </a>
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

    /* ---------- WhatsApp Share ---------- */
    window.shareWhatsApp = function() {
        Swal.fire({
            title: 'Preparing WhatsApp Share...',
            text: 'Generating PDF document to share.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        var element = document.getElementById('gpContent');
        var opt = {
          margin:       [0.3, 0.3, 0.3, 0.3],
          filename:     'Outward_Gatepass_{{ $gp->id }}.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
            var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
            
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'Outward Gatepass',
                    text: 'Please find the attached Outward Gatepass #{{ $gp->gatepass_number ?? $gp->id }}.',
                    files: [file]
                }).then(() => {
                    Swal.close();
                }).catch((error) => {
                    console.log('Error sharing', error);
                    fallbackWaShare(pdfBlob, opt.filename);
                });
            } else {
                fallbackWaShare(pdfBlob, opt.filename);
            }
        });
    };

    function fallbackWaShare(pdfBlob, filename) {
        Swal.fire({
            icon: 'info',
            title: 'Share PDF via WhatsApp',
            text: 'The PDF will be downloaded now. WhatsApp will open allowing you to choose any chat. Please attach the downloaded PDF manually.',
            confirmButtonText: 'Download & Open WhatsApp'
        }).then(() => {
            var url = URL.createObjectURL(pdfBlob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            
            var msg = "*Outward Gatepass #{{ $gp->gatepass_number ?? $gp->id }}*\nPlease find the attached PDF document.";
            var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
            window.open(waUrl, '_blank');
        });
    }

    /* ---------- Export Options & PDF ---------- */
    window.showExportOptions = function() {
        Swal.fire({
            title: 'Export Gatepass',
            text: 'Choose your preferred export format:',
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
    };

    window.exportPDF = function() {
        Swal.fire({
            title: 'Generating PDF...',
            text: 'Please wait while your PDF is being prepared.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        var element = document.getElementById('gpContent');
        var opt = {
          margin:       [0.3, 0.3, 0.3, 0.3],
          filename:     'Outward_Gatepass_{{ $gp->id }}.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save().then(function() {
            Swal.close();
        });
    };

    window.exportCSV = function () {
        var rows = [['#', 'Product Description', 'Item Code', 'Unit', 'Qty', 'Rate', 'Total Amount']];
        
        $('.table-erp tbody tr').each(function () {
            var cells = [];
            $(this).find('td').each(function () {
                var text = $(this).text().trim().replace(/"/g, '""');
                cells.push('"' + text + '"');
            });
            if (cells.length > 1) rows.push(cells);
        });
        
        rows.push([]);
        rows.push(['', '', '', 'GRAND TOTALS', '{{ number_format($totalQty, 2) }}', '', '{{ number_format($totalAmount, 2) }}']);

        var csv  = rows.map(function(r){return r.join(',');}).join('\n');
        var blob = new Blob(["\uFEFF" + csv], {type:'text/csv;charset=utf-8;'});
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href   = url;
        a.download = 'Outward_Gatepass_{{ $gp->id }}.csv';
        a.click();
    };
    // View Receipt Logic
    let currentReceiptRotation = 0;
    let viewReceiptModalInstance = null;

    window.viewTransportReceipt = function(gpId, receiptUrl) {
        if (!viewReceiptModalInstance) {
            viewReceiptModalInstance = new bootstrap.Modal(document.getElementById('viewReceiptModal'));
        }
        
        const img = document.getElementById('fullReceiptImage');
        img.src = receiptUrl;
        img.style.transform = 'rotate(0deg)';
        currentReceiptRotation = 0;
        
        document.getElementById('downloadReceiptLink').href = receiptUrl;
        viewReceiptModalInstance.show();
    };

    window.rotateReceiptImage = function(angle) {
        currentReceiptRotation += angle;
        document.getElementById('fullReceiptImage').style.transform = `rotate(${currentReceiptRotation}deg)`;
    };
</script>
@endsection