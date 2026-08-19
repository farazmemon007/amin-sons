@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --coa-navy: #1e3a5f;
        --coa-navy-dark: #0f1f38;
        --coa-navy-light: #2c5282;
        --coa-gold: #c8973a;
        --coa-emerald: #0d9f6e;
        --coa-border: #e2e8f0;
    }

    .rpt-wrapper {
        padding: 12px 0 30px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .rpt-header-bar {
        background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 60%, var(--coa-navy-light) 100%);
        border-radius: 10px;
        padding: 16px 22px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        box-shadow: 0 4px 15px rgba(15, 31, 56, 0.15);
        margin-bottom: 18px;
    }

    .rpt-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--coa-gold);
        border: 1px solid rgba(200, 151, 58, 0.3);
        flex-shrink: 0;
    }

    .rpt-header-title {
        font-size: 18px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        line-height: 1.2;
    }

    .rpt-header-sub {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.85);
        margin-top: 3px;
    }

    .rpt-kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    @media (max-width: 992px) {
        .rpt-kpi-grid {
            grid-template-columns: 1fr;
        }
    }

    .rpt-kpi-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 13px 16px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .rpt-kpi-card.highlight {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: #a7f3d0;
    }

    .rpt-kpi-label {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .rpt-kpi-val {
        font-size: 18px;
        font-weight: 800;
        color: var(--coa-navy);
        font-family: monospace;
    }

    .rpt-kpi-val.emerald { color: #047857; }
    .rpt-kpi-val.crimson { color: #dc2626; }

    .rpt-kpi-icon {
        width: 38px;
        height: 38px;
        border-radius: 7px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .kpi-icon-blue { background: #e0f2fe; color: #0284c7; }
    .kpi-icon-emerald { background: #d1fae5; color: #059669; }
    .kpi-icon-red { background: #fee2e2; color: #dc2626; }

    .f-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.03em;
        margin-bottom: 4px;
        display: block;
    }

    #reportTable th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 8px;
        border: 1px solid #1e3a5f;
    }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">
            
            {{-- 1. Corporate Header Bar --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">PO vs Gatepass Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-clipboard-check mr-1" style="color: var(--coa-gold);"></i> Track Procurement Variance (Purchase Order vs. Inward Gatepass) &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="waShareBtn" onclick="shareWhatsApp()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(37, 211, 102, 0.2); border-color: #25D366; color: #25D366;">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </button>
                    <button type="button" onclick="showExportOptions()" class="btn btn-sm btn-light font-weight-bold text-dark border">
                        <i class="fas fa-download mr-1 text-primary"></i> Export
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-light font-weight-bold">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                </div>
            </div>

            {{-- 2. Filter Card --}}
            <div class="card shadow-sm mb-3 border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form id="filterForm" class="row g-2 align-items-end mb-0">
                        @if(Auth::user()->hasRole('super admin'))
                        <div class="col-md-3">
                            <label class="f-label">Select Branch</label>
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

                        <div class="col-md-{{ Auth::user()->hasRole('super admin') ? '3' : '4' }}">
                            <label class="f-label">Select Vendor</label>
                            <select name="vendor_id" id="vendor_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Vendors --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">PO Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">PO End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-search mr-1"></i> Generate
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. Summary Cards --}}
            <div id="summaryCards" class="rpt-kpi-grid" style="display:none;">
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Ordered Units</div>
                        <div id="stat_ordered" class="rpt-kpi-val">0</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-blue">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card highlight">
                    <div>
                        <div class="rpt-kpi-label" style="color: #047857;">Total Received Units</div>
                        <div id="stat_received" class="rpt-kpi-val emerald">0</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Pending Variance</div>
                        <div id="stat_variance" class="rpt-kpi-val crimson">0</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-red">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Report Table --}}
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="reportContent">
                <div class="card-body p-3">
                    
                    {{-- PDF HEADER (HIDDEN ON SCREEN) --}}
                    <div id="pdfHeader" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #0f1f38; padding-bottom:10px;">
                        <h2 style="margin:0; color:#0f1f38; text-transform:uppercase; letter-spacing:1px;">PO vs Gatepass Report</h2>
                        <p style="margin:5px 0; font-size:14px; color:#333;">
                            <strong>Period:</strong> <span id="pdfPeriod"></span> | 
                            <strong>Vendor:</strong> <span id="pdfVendorName">All Vendors</span>
                        </p>
                        <p style="margin:0; font-size:12px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div id="loader" style="display:none;text-align:center;padding:40px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Loading PO variance data...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="reportTable" class="table table-bordered mb-0" style="font-size:12.5px; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%;">#</th>
                                    <th style="width: 13%;">PO Details</th>
                                    <th style="width: 15%;">Vendor / Branch</th>
                                    <th>Product Details</th>
                                    <th class="text-center" style="width: 9%;">Ordered Qty</th>
                                    <th class="text-center" style="width: 9%;">Received Qty</th>
                                    <th class="text-center" style="width: 8%;">Variance</th>
                                    <th style="width: 140px;">Receipt Progress</th>
                                    <th class="text-center" style="width: 10%;">Status</th>
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
