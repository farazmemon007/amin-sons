@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid px-4">
            
            {{-- PAGE HEADER --}}
            <div class="row mb-3 align-items-center mt-3">
                <div class="col">
                    <h4 class="mb-0 fw-bold" style="color:#1a1a2e;">
                        <i class="fas fa-boxes me-2" style="color:#6f42c1;"></i>
                        Inventory On-Hand Report
                    </h4>
                    <small class="text-muted">ERP Standard &mdash; Real-time Stock Valuation & Availability</small>
                </div>
                <div class="col-auto">
                    <button type="button" onclick="shareWhatsApp()" class="btn btn-outline-success btn-sm shadow-sm me-1" style="border-color:#25D366; color:#25D366; background: #fff;">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp
                    </button>
                    <button type="button" onclick="showExportOptions()" class="btn btn-outline-info btn-sm shadow-sm" style="background: #fff; border-color: #17a2b8; color: #17a2b8;">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                </div>
            </div>

            {{-- SUMMARY CARDS --}}
            @php
                $totalItems = $rows->count();
                $totalQty = $rows->sum('onhand_qty');
                $partsCount = $rows->where('is_part', 1)->count();
                $assembledCount = $rows->where('is_assembled', 1)->count();
            @endphp
            <div class="row mb-3 g-3">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-primary text-white" style="border-radius:10px;">
                        <div class="card-body p-3 text-center">
                            <div class="lbl text-white opacity-75">Total Products</div>
                            <div class="h5 fw-bold mb-0">{{ number_format($totalItems) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-success text-white" style="border-radius:10px;">
                        <div class="card-body p-3 text-center">
                            <div class="lbl text-white opacity-75">Total On-Hand Qty</div>
                            <div class="h5 fw-bold mb-0">{{ number_format($totalQty, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-info text-white" style="border-radius:10px;">
                        <div class="card-body p-3 text-center">
                            <div class="lbl text-white opacity-75">Parts Items</div>
                            <div class="h5 fw-bold mb-0">{{ number_format($partsCount) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-warning text-white" style="border-radius:10px;">
                        <div class="card-body p-3 text-center">
                            <div class="lbl text-white opacity-75">Assembled Items</div>
                            <div class="h5 fw-bold mb-0">{{ number_format($assembledCount) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- REPORT TABLE --}}
            <div class="card shadow-sm border-0" style="border-radius:10px;" id="reportContent">
                <div class="card-body p-4">
                    
                    {{-- PDF HEADER (HIDDEN ON SCREEN) --}}
                    <div id="pdfHeader" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #1a1a2e; padding-bottom:10px;">
                        <h2 style="margin:0; color:#1a1a2e; text-transform:uppercase; letter-spacing:1px;">Inventory On-Hand Report</h2>
                        <p style="margin:5px 0; font-size:14px; color:#333;">Real-time Stock Availability Status</p>
                        <p style="margin:0; font-size:12px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div class="table-responsive">
                        <table id="onhandTable" class="table table-bordered mb-0" style="font-size:14px;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#1a1a2e;color:#fff;">
                                    <th class="text-center" style="padding:15px 5px;">#</th>
                                    <th style="padding:15px 5px;">Item Code</th>
                                    <th style="padding:15px 5px;">Product Name</th>
                                    <th style="padding:15px 5px;">Brand</th>
                                    <th style="padding:15px 5px;">UOM</th>
                                    <th class="text-center" style="padding:15px 5px;">Status</th>
                                    <th class="text-end" style="padding:15px 5px; background:#2d4a6e;">On-Hand Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $i => $r)
                                <tr>
                                    <td class="text-center">${idx + 1}</td>
                                    <td class="item-code">${r.item_code}</td>
                                    <td class="item-name">${r.item_name}</td>
                                    <td class="fw-bold">${r.brand_name}</td>
                                    <td>${r.unit_name}</td>
                                    <td class="text-center">
                                        @if($r->is_part)
                                            <span class="badge rounded-pill bg-info px-2" style="font-size:11px;">Part</span>
                                        @elseif($r->is_assembled)
                                            <span class="badge rounded-pill bg-primary px-2" style="font-size:11px;">Assembled</span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary px-2" style="font-size:11px;">Simple</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold" style="background:#f0f7ff; font-size:15px;">
                                        {{ number_format($r->onhand_qty, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="fw-bold bg-light">
                                    <td colspan="6" class="text-end" style="font-size:15px;">Total On-Hand:</td>
                                    <td class="text-end text-primary" style="font-size:16px;">{{ number_format($totalQty, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .lbl { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
    #onhandTable { border: 2px solid #1a1a2e; }
    #onhandTable th { 
        vertical-align: middle; 
        border: 1px solid #444; 
        text-transform: uppercase; 
        letter-spacing: 0.5px;
        font-weight: 700;
    }
    #onhandTable td { 
        vertical-align: middle; 
        border: 1px solid #ccc; 
        padding: 12px 10px; 
        font-weight: 500;
        color: #1a1a2e;
    }
    #onhandTable tbody tr:nth-child(even) { background-color: #f9f9f9; }
    #onhandTable tbody tr:hover { background-color: #f0f4ff !important; transition: 0.2s; }
    
    .item-code { color: #0066cc; font-weight: 700; font-size: 14px; }
    .item-name { font-weight: 700; color: #333; font-size: 15px; }
    
    @media print {
        .btn, .summary-cards { display: none !important; }
        .main-content { padding: 0 !important; margin-top:0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        #reportContent { overflow: visible !important; }
    }
</style>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        
        /* ---------- WhatsApp Share ---------- */
        window.shareWhatsApp = function() {
            Swal.fire({
                title: 'Preparing WhatsApp Share...',
                text: 'Generating PDF document to share.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            var element = document.getElementById('reportContent');
            $('#pdfHeader').show(); 

            var opt = {
                margin:       0.2,
                filename:     'Onhand_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
                $('#pdfHeader').hide(); 
                var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
                
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    navigator.share({
                        title: 'Inventory Report',
                        text: 'Please find the attached Inventory On-Hand Report.',
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
        };

        function fallbackWaShare(pdfBlob, filename) {
            Swal.fire({
                icon: 'info',
                title: 'Share PDF via WhatsApp',
                text: 'The PDF will be downloaded. Please attach it manually to your WhatsApp chat.',
                confirmButtonText: 'Download & Open WhatsApp'
            }).then(() => {
                var url = URL.createObjectURL(pdfBlob);
                var a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                
                var msg = "*Inventory On-Hand Report*\nPlease find the attached PDF document.";
                var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
                window.open(waUrl, '_blank');
            });
        }

        /* ---------- Export Options ---------- */
        window.showExportOptions = function() {
            Swal.fire({
                title: 'Export Inventory Report',
                text: 'Choose your format:',
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
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            var element = document.getElementById('reportContent');
            $('#pdfHeader').show(); 

            var opt = {
                margin:       0.2,
                filename:     'Onhand_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                $('#pdfHeader').hide(); 
                Swal.close();
            });
        };

        window.exportCSV = function () {
            let csv = [];
            let headers = [];
            $("#onhandTable thead th").each(function() {
                headers.push('"' + $(this).text().trim() + '"');
            });
            csv.push(headers.join(","));

            $("#onhandTable tbody tr").each(function() {
                let row = [];
                $(this).find('td').each(function() {
                    let cellText = $(this).text().trim();
                    row.push('"' + cellText.replace(/"/g, '""') + '"');
                });
                csv.push(row.join(","));
            });

            // Add footer
            let footer = [];
            $("#onhandTable tfoot td").each(function() {
                footer.push('"' + $(this).text().trim() + '"');
            });
            csv.push(footer.join(","));

            let csvString = "\uFEFF" + csv.join("\n");
            let blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            let url = URL.createObjectURL(blob);
            let a = document.createElement("a");
            a.href = url;
            a.download = "onhand_report_" + new Date().toISOString().slice(0,10) + ".csv";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        };
    });
</script>
@endsection
