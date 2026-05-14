@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid px-4">
            
            {{-- PAGE HEADER --}}
            <div class="row mb-3 align-items-center mt-3">
                <div class="col">
                    <h4 class="mb-0 fw-bold" style="color:#1a1a2e;">
                        <i class="fas fa-tasks me-2" style="color:#0066cc;"></i>
                        PO vs Gatepass Report
                    </h4>
                    <small class="text-muted">ERP Standard &mdash; Track Procurement Variance (Order vs. Receipt)</small>
                </div>
                <div class="col-auto">
                    <button type="button" id="waShareBtn" onclick="shareWhatsApp()" class="btn btn-outline-success btn-sm shadow-sm me-1" style="border-color:#25D366; color:#25D366; background: #fff;">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp
                    </button>
                    <button type="button" onclick="showExportOptions()" class="btn btn-outline-info btn-sm shadow-sm" style="background: #fff; border-color: #17a2b8; color: #17a2b8;">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm shadow-sm">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>

            {{-- FILTER CARD --}}
            <div class="card shadow-sm mb-3" style="border-radius:10px;border:none;">
                <div class="card-body py-3">
                    <form id="filterForm" class="row g-3 align-items-end">
                        @if(Auth::user()->hasRole('super admin'))
                        <div class="col-md-2">
                            <label class="form-label fw-semibold mb-1">Select Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Branches --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ Auth::user()->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                            <input type="hidden" name="branch_id" id="branch_id" value="{{ Auth::user()->branch_id }}">
                        @endif

                        <div class="col-md-3">
                            <label class="form-label fw-semibold mb-1">Select Vendor</label>
                            <select name="vendor_id" id="vendor_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Vendors --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold mb-1">PO Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold mb-1">PO End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-primary btn-sm w-100" style="background:#0066cc;border-color:#0066cc;padding:7px;">
                                <i class="fas fa-search me-1"></i> Generate
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- SUMMARY CARDS --}}
            <div id="summaryCards" class="row mb-3 g-3" style="display:none;">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-primary text-white" style="border-radius:10px;">
                        <div class="card-body p-3 text-center">
                            <div class="lbl text-white opacity-75">Total Items Ordered</div>
                            <div id="stat_ordered" class="h5 fw-bold mb-0">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-success text-white" style="border-radius:10px;">
                        <div class="card-body p-3 text-center">
                            <div class="lbl text-white opacity-75">Total Items Received</div>
                            <div id="stat_received" class="h5 fw-bold mb-0">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 bg-danger text-white" style="border-radius:10px;">
                        <div class="card-body p-3 text-center">
                            <div class="lbl text-white opacity-75">Pending Variance</div>
                            <div id="stat_variance" class="h5 fw-bold mb-0">0</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- REPORT TABLE --}}
            <div class="card shadow-sm border-0" style="border-radius:10px;" id="reportContent">
                <div class="card-body p-4">
                    
                    {{-- PDF HEADER (HIDDEN ON SCREEN) --}}
                    <div id="pdfHeader" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #1a1a2e; padding-bottom:10px;">
                        <h2 style="margin:0; color:#1a1a2e; text-transform:uppercase; letter-spacing:1px;">PO vs Gatepass Report</h2>
                        <p style="margin:5px 0; font-size:14px; color:#333;">
                            <strong>Period:</strong> <span id="pdfPeriod"></span> | 
                            <strong>Vendor:</strong> <span id="pdfVendorName">All Vendors</span>
                        </p>
                        <p style="margin:0; font-size:12px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div id="loader" style="display:none;text-align:center;padding:40px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Loading PO variance data...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="reportTable" class="table table-bordered mb-0" style="font-size:13px;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#1a1a2e;color:#fff;">
                                    <th class="text-center">#</th>
                                    <th>PO Details</th>
                                    <th>Vendor / Branch</th>
                                    <th>Product Details</th>
                                    <th class="text-center">Ordered Qty</th>
                                    <th class="text-center">Received Qty</th>
                                    <th class="text-center">Variance</th>
                                    <th style="width:150px;">Receipt Progress</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody"></tbody>
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
    #reportTable { border: 2px solid #1a1a2e; }
    #reportTable th { 
        vertical-align: middle; 
        border: 1px solid #444; 
        text-transform: uppercase; 
        letter-spacing: 0.5px;
        font-weight: 700;
        padding: 12px 8px;
    }
    #reportTable td { 
        vertical-align: middle; 
        border: 1px solid #ccc; 
        padding: 10px 8px; 
        font-weight: 500;
        color: #1a1a2e;
    }
    #reportTable tbody tr:nth-child(even) { background-color: #f9f9f9; }
    #reportTable tbody tr:hover { background-color: #f0f4ff !important; transition: 0.2s; }
    
    .po-no { color: #0066cc; font-weight: 700; font-size: 14px; }
    .item-name { font-weight: 700; color: #333; font-size: 13px; }
    
    .progress { height: 8px; border-radius: 4px; background-color: #e9ecef; }
    .progress-bar { transition: width 0.6s ease; }

    @media print {
        #filterForm, #btnSearch, .btn, .main-footer { display: none !important; }
        .main-content { padding: 0 !important; margin-top:0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        #reportContent { overflow: visible !important; }
        body { background: white !important; }
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        const currentUserIsSuperAdmin = {{ Auth::user()->hasRole('super admin') ? 'true' : 'false' }};
        
        function n(v) { return parseFloat(v) || 0; }
        
        function fetchReport() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var vendor_id = $('#vendor_id').val();
            var branch_id = $('#branch_id').val();

            // Update PDF Header Info
            $('#pdfPeriod').text(start_date + ' to ' + end_date);
            $('#pdfVendorName').text($('#vendor_id option:selected').text());

            $('#loader').show();
            $('#reportBody').empty();
            $('#summaryCards').hide();

            $.ajax({
                url: "{{ route('report.po_vs_gatepass.fetch') }}",
                type: "GET",
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    vendor_id: vendor_id,
                    branch_id: branch_id
                },
                success: function(response) {
                    $('#loader').hide();
                    renderRows(response.data);
                },
                error: function() {
                    $('#loader').hide();
                    alert('Error fetching report data');
                }
            });
        }

        // Fetch vendors when branch changes
        $('#branch_id').on('change', function() {
            var branchId = $(this).val();
            
            // If "All Branches" is selected, we could either show all vendors or clear the list.
            // For ERP standards, we usually show All Vendors.
            if (branchId === 'all') {
                // Optionally fetch ALL vendors here if needed, but for now we reset to all
                location.reload(); // Simplest way to reset to initial state with all vendors
                return;
            }

            $.ajax({
                url: "{{ route('report.get_vendors_by_branch') }}",
                type: "GET",
                data: { branch_id: branchId },
                success: function(data) {
                    var options = '<option value="all">-- All Vendors --</option>';
                    if (data && data.length > 0) {
                        data.forEach(function(v) {
                            options += `<option value="${v.id}">${v.name}</option>`;
                        });
                    }
                    // Update HTML and trigger Select2 refresh
                    $('#vendor_id').html(options).trigger('change');
                },
                error: function(xhr) {
                    console.error('Vendor Fetch Error:', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Fetch Error',
                        text: 'Could not load vendors for this branch.'
                    });
                }
            });
        });

        // Trigger change on load if a branch is already selected (for Super Admin pre-selection)
        if ($('#branch_id').val() !== 'all' && currentUserIsSuperAdmin) {
            // $('#branch_id').trigger('change'); 
            // Actually, the controller already passes the vendors for the user's branch by default.
        }

        function renderRows(rows) {
            let tableContent = '';
            let totalOrdered = 0;
            let totalReceived = 0;

            if (!rows || rows.length === 0) {
                $('#reportBody').html('<tr><td colspan="9" class="text-center py-4 text-muted">No procurement data found for the selected criteria</td></tr>');
                return;
            }

            rows.forEach(function(r, idx) {
                let ordered = n(r.ordered_qty);
                let received = n(r.received_qty);
                let variance = ordered - received;
                let percent = ordered > 0 ? (received / ordered) * 100 : 0;
                
                totalOrdered += ordered;
                totalReceived += received;

                let progressClass = 'bg-danger';
                if (percent >= 100) progressClass = 'bg-success';
                else if (percent >= 50) progressClass = 'bg-warning';

                let statusBadge = '';
                if (percent >= 100) {
                    statusBadge = '<span class="badge bg-success">Fully Received</span>';
                } else if (percent > 0) {
                    statusBadge = '<span class="badge bg-warning text-dark">Partial</span>';
                } else {
                    statusBadge = '<span class="badge bg-danger">Pending</span>';
                }

                let colorBreakdownHtml = '';
                if (r.color_breakdown) {
                    let variations = r.color_breakdown.split('||');
                    variations.forEach(v => {
                        let parts = v.split(': ');
                        let colorName = parts[0];
                        let qtys = parts[1]; // "Ordered / Received"
                        
                        if (colorName !== 'Default' || variations.length > 1) {
                            colorBreakdownHtml += `
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-light text-primary border border-primary px-1 me-2" style="font-size:9px; min-width:50px;">${colorName.toUpperCase()}</span>
                                    <span class="text-muted fw-bold" style="font-size:11px;">${qtys}</span>
                                </div>`;
                        }
                    });
                }

                tableContent += `<tr>
                    <td class="text-center">${idx + 1}</td>
                    <td>
                        <div class="po-no">${r.po_number}</div>
                        <div class="text-muted" style="font-size:11px;">${r.order_date}</div>
                    </td>
                    <td>
                        <div class="fw-bold">${r.vendor_name}</div>
                        <div class="text-muted small">${r.branch_name}</div>
                    </td>
                    <td>
                        <div class="item-name">${r.item_name}</div>
                        <div class="mt-1">${colorBreakdownHtml}</div>
                        <code class="text-muted small">${r.item_code}</code>
                    </td>
                    <td class="text-center fw-bold">${ordered.toFixed(2)}</td>
                    <td class="text-center fw-bold text-success">${received.toFixed(2)}</td>
                    <td class="text-center fw-bold ${variance > 0 ? 'text-danger' : 'text-muted'}">${variance.toFixed(2)}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2">
                                <div class="progress-bar ${progressClass}" role="progressbar" style="width: ${percent}%"></div>
                            </div>
                            <span class="small fw-bold">${percent.toFixed(0)}%</span>
                        </div>
                    </td>
                    <td class="text-center">${statusBadge}</td>
                </tr>`;
            });

            // Summary Stats
            $('#stat_ordered').text(totalOrdered.toFixed(2));
            $('#stat_received').text(totalReceived.toFixed(2));
            $('#stat_variance').text((totalOrdered - totalReceived).toFixed(2));
            $('#summaryCards').fadeIn();

            $('#reportBody').html(tableContent);
        }

        $('#btnSearch').on('click', fetchReport);
        fetchReport(); // Load initially

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
                margin:       0.3,
                filename:     'PO_Variance_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
                $('#pdfHeader').hide();
                var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
                
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    navigator.share({
                        title: 'PO vs Gatepass Report',
                        text: 'Please find the attached procurement variance report.',
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
                text: 'The PDF will be downloaded now. Please attach it manually in WhatsApp.',
                confirmButtonText: 'Download & Open WhatsApp'
            }).then(() => {
                var url = URL.createObjectURL(pdfBlob);
                var a = document.createElement('a');
                a.href = url;
                a.download = filename;
                a.click();
                
                var msg = "*PO vs Gatepass Report*\nPlease find the attached procurement report.";
                window.open("https://wa.me/?text=" + encodeURIComponent(msg), '_blank');
            });
        }

        /* ---------- Export Options ---------- */
        window.showExportOptions = function() {
            Swal.fire({
                title: 'Export Report',
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
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            var element = document.getElementById('reportContent');
            $('#pdfHeader').show();

            var opt = {
                margin:       0.3,
                filename:     'PO_Variance_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                $('#pdfHeader').hide();
                Swal.close();
            });
        };

        window.exportCSV = function () {
            let csv = 'PO Number,Date,Vendor,Branch,Item Code,Item Name,Ordered Qty,Received Qty,Variance,Status\n';
            $('#reportBody tr').each(function() {
                let row = [];
                $(this).find('td').each(function(idx) {
                    if (idx === 0 || idx === 7) return;
                    row.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
                });
                if (row.length > 0) csv += row.join(',') + '\n';
            });
            
            let blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
            let url = URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'PO_Variance_Report_' + new Date().toISOString().slice(0,10) + '.csv';
            a.click();
        };
    });
</script>
@endsection
