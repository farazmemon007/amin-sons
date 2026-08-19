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
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }

    @media (max-width: 992px) {
        .rpt-kpi-grid {
            grid-template-columns: repeat(2, 1fr);
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
    .rpt-kpi-val.amber { color: #d97706; }

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
    .kpi-icon-amber { background: #fef3c7; color: #d97706; }

    .f-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.03em;
        margin-bottom: 4px;
        display: block;
    }

    #purchaseTable th {
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
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Purchase Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-truck-loading mr-1" style="color: var(--coa-gold);"></i> Detailed Purchase History & Analytical View &mdash; Ameen & Sons Corporate ERP</span>
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
                    <form id="purchaseFilterForm" class="row g-2 align-items-end mb-0">
                        <div class="col-md-3" id="branch_container" @if(!$user->hasRole('super admin')) style="display:none;" @endif>
                            <label class="f-label">Select Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control form-control-sm select2">
                                @if($user->hasRole('super admin'))
                                    <option value="all">-- All Branches --</option>
                                @endif
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $user->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-{{ $user->hasRole('super admin') ? '3' : '4' }}">
                            <label class="f-label">Select Vendor</label>
                            <select name="vendor_id" id="vendor_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Vendors --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. Summary Cards --}}
            <div id="summaryCards" class="rpt-kpi-grid" style="display:none;">
                <div class="rpt-kpi-card highlight">
                    <div>
                        <div class="rpt-kpi-label" style="color: #047857;">Net Purchases</div>
                        <div id="stat_net" class="rpt-kpi-val emerald">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Paid</div>
                        <div id="stat_paid" class="rpt-kpi-val">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-blue">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Balance</div>
                        <div id="stat_due" class="rpt-kpi-val crimson">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-red">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Total Discount</div>
                        <div id="stat_disc" class="rpt-kpi-val amber">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-amber">
                        <i class="fas fa-tag"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Report Table --}}
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="reportContent">
                <div class="card-body p-3">
                    
                    {{-- PDF HEADER (HIDDEN ON SCREEN) --}}
                    <div id="pdfHeader" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #0f1f38; padding-bottom:10px;">
                        <h2 style="margin:0; color:#0f1f38; text-transform:uppercase; letter-spacing:1px;">Purchase Report</h2>
                        <p style="margin:5px 0; font-size:14px; color:#333;">
                            <strong>Period:</strong> <span id="pdfPeriod"></span> | 
                            <strong>Vendor:</strong> <span id="pdfVendorName">All Vendors</span>
                        </p>
                        <p style="margin:0; font-size:12px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div id="loader" style="display:none;text-align:center;padding:40px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Aggregating purchase data...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="purchaseTable" class="table table-bordered mb-0" style="font-size:12px; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 35px;">#</th>
                                    <th style="width: 90px; white-space: nowrap;">Date</th>
                                    <th style="width: 105px; white-space: nowrap;">Invoice</th>
                                    <th style="min-width: 130px; width: 13%;">Vendor</th>
                                    <th style="min-width: 260px; width: 25%;">Item / Product Details</th>
                                    <th class="text-end" style="width: 75px; white-space: nowrap;">Qty</th>
                                    <th class="text-end" style="width: 85px; white-space: nowrap;">Price</th>
                                    <th class="text-end" style="width: 80px; white-space: nowrap;">Item Disc</th>
                                    <th class="text-end" style="width: 95px; white-space: nowrap;">Net Item</th>
                                    <th class="text-end" style="width: 80px; white-space: nowrap;">Bill Disc</th>
                                    <th class="text-end" style="width: 105px; white-space: nowrap;">Total Net</th>
                                    <th class="text-end" style="width: 85px; white-space: nowrap;">Paid</th>
                                    <th class="text-end" style="width: 95px; white-space: nowrap;">Due</th>
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
    #purchaseTable { border: 1px solid #cbd5e1; }
    #purchaseTable th { 
        vertical-align: middle; 
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase; 
        letter-spacing: 0.04em;
        padding: 10px 8px;
        border: 1px solid #1e3a5f;
    }
    #purchaseTable td { 
        vertical-align: middle; 
        border: 1px solid #e2e8f0; 
        padding: 8px 8px; 
        font-weight: 500;
        color: #1e293b;
    }
    #purchaseTable tbody tr:nth-child(even) { background-color: #f8fafc; }
    #purchaseTable tbody tr:hover { background-color: #f1f5f9 !important; transition: 0.15s; }
    
    .invoice-no { color: #1e3a5f; font-weight: 700; font-family: monospace; font-size: 12px; }
    .item-name { font-weight: 700; color: #0f172a; font-size: 12.5px; line-height: 1.35; }
    
    @media print {
        #purchaseFilterForm, #btnSearch, .btn { display: none !important; }
        .main-content { padding: 0 !important; margin-top:0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        #reportContent { overflow: visible !important; }
    }
</style>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        
        function n(v) { return parseFloat(v) || 0; }
        function fmt(v) { return n(v).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

        function renderRows(rows) {
            let tableContent = '';
            let grandDiscount = 0;
            let grandNet = 0;
            let grandPaid = 0;
            let grandDue = 0;

            let processedInvoices = new Set(); // To avoid double-counting invoice-level totals

            if (rows.length === 0) {
                $('#reportBody').html('<tr><td colspan="13" class="text-center py-4 text-muted">No data found</td></tr>');
                $('#summaryCards').hide();
                return;
            }

            rows.forEach(function(r, idx) {
                let colorBadges = '';
                if (r.colors) {
                    let colorList = r.colors.split(', ');
                    colorList.forEach(color => {
                        if (color && color.trim() !== '') {
                            colorBadges += `<span class="badge" style="background:#e2e8f0; color:#334155; font-size:9.5px; font-weight:600; padding:2px 5px; border-radius:3px;">${color.toUpperCase()}</span>`;
                        }
                    });
                }

                tableContent += `<tr>
                    <td class="text-center text-muted" style="font-size:11px;">${idx + 1}</td>
                    <td style="white-space:nowrap; font-size:11.5px;">${r.purchase_date}</td>
                    <td><span class="invoice-no">${r.invoice_no}</span></td>
                    <td><strong class="text-dark" style="font-size:12px;">${r.vendor_name}</strong></td>
                    <td>
                        <div class="item-name mb-1">${r.item_name}</div>
                        <div class="d-flex align-items-center flex-wrap gap-1">
                            ${colorBadges}
                            <span class="badge badge-light border text-muted" style="font-size:9.5px; font-family:monospace; padding:1px 4px;">${r.item_code}</span>
                        </div>
                    </td>
                    <td class="text-end font-monospace" style="font-weight:700; font-size:12px; white-space:nowrap;">${n(r.qty).toFixed(2)} <small class="text-muted font-weight-normal">${r.unit || ''}</small></td>
                    <td class="text-end font-monospace" style="font-size:12px;">${fmt(r.price)}</td>
                    <td class="text-end font-monospace ${n(r.item_discount) > 0 ? 'text-danger font-weight-bold' : 'text-muted'}" style="font-size:12px;">${fmt(r.item_discount)}</td>
                    <td class="text-end font-monospace font-weight-bold" style="background:rgba(30, 58, 95, 0.04); color:#1e3a5f; font-size:12px;">${fmt(r.line_total)}</td>
                    <td class="text-end font-monospace ${n(r.discount) > 0 ? 'text-danger font-weight-bold' : 'text-muted'}" style="font-size:12px;">${fmt(r.discount)}</td>
                    <td class="text-end font-monospace font-weight-bold" style="background:rgba(13, 159, 110, 0.08); color:#047857; font-size:12.5px;">${fmt(r.net_amount)}</td>
                    <td class="text-end font-monospace text-dark" style="font-size:12px;">${fmt(r.paid_amount)}</td>
                    <td class="text-end font-monospace font-weight-bold ${n(r.due_amount) > 0 ? 'text-danger' : 'text-success'}" style="font-size:12.5px;">${fmt(r.due_amount)}</td>
                </tr>`;

                // Add item-level discount to grand total
                grandDiscount += n(r.item_discount);

                // Invoice-level totals (count only once per unique invoice)
                if (!processedInvoices.has(r.invoice_no)) {
                    grandNet += n(r.net_amount);
                    grandPaid += n(r.paid_amount);
                    grandDue += n(r.due_amount);
                    grandDiscount += n(r.discount); // Add invoice-level discount
                    processedInvoices.add(r.invoice_no);
                }
            });

            // Summary Stats
            $('#stat_net').text('Rs. ' + fmt(grandNet));
            $('#stat_paid').text('Rs. ' + fmt(grandPaid));
            $('#stat_due').text('Rs. ' + fmt(grandDue));
            $('#stat_disc').text('Rs. ' + fmt(grandDiscount));
            $('#summaryCards').fadeIn();

            // Grand total row
            tableContent += `<tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                <td colspan="10" class="text-end font-weight-bold" style="font-family: sans-serif;">Total Summary (Unique Invoices):</td>
                <td class="text-end text-success font-weight-bold" style="background:rgba(13, 159, 110, 0.12);">${fmt(grandNet)}</td>
                <td class="text-end text-dark font-weight-bold">${fmt(grandPaid)}</td>
                <td class="text-end text-danger font-weight-bold">${fmt(grandDue)}</td>
            </tr>`;

            $('#reportBody').html(tableContent);
        }

        $('#btnSearch').on('click', function() {
            fetchReport();
        });

        // ✅ NEW: Branch change listener to fetch vendors dynamically
        $('#branch_id').on('change', function() {
            let branchId = $(this).val();
            if (branchId === 'all') {
                // If all branches, maybe show all vendors or clear
                // For now, let's clear and show "Select Branch"
                $('#vendor_id').html('<option value="all">-- All Vendors --</option>').trigger('change');
                return;
            }

            $.ajax({
                url: "{{ route('vendors-by-branch') }}", // Existing route from ReportingController
                type: "GET",
                data: { branch_id: branchId },
                success: function(response) {
                    let options = '<option value="all">-- All Vendors --</option>';
                    response.forEach(v => {
                        // The controller returns {id, customer_name, customer_type} for compatibility
                        options += `<option value="${v.id}">${v.customer_name}</option>`;
                    });
                    $('#vendor_id').html(options).trigger('change');
                }
            });
        });

        // ✅ ERP STANDARD: Auto-fetch report on page load
        fetchReport();

        function fetchReport() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var vendor_id = $('#vendor_id').val();

            // Update PDF Header Info for Export
            $('#pdfPeriod').text(start_date + ' to ' + end_date);
            $('#pdfVendorName').text($('#vendor_id option:selected').text());

            $('#loader').show();

            $.ajax({
                url: "{{ route('report.purchase.fetch') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    start_date: start_date,
                    end_date: end_date,
                    vendor_id: vendor_id,
                    branch_id: $('#branch_id').val()
                },
                success: function(response) {
                    $('#loader').hide();
                    renderRows(response.data);
                },
                error: function() {
                    $('#loader').hide();
                    alert('Error fetching purchase report');
                }
            });
        }

        /* ---------- WhatsApp Share ---------- */
        window.shareWhatsApp = function() {
            Swal.fire({
                title: 'Preparing WhatsApp Share...',
                text: 'Generating PDF document to share.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            var element = document.getElementById('reportContent');
            $('#pdfHeader').show(); // Show header for capture

            var opt = {
                margin:       0.2,
                filename:     'Purchase_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a3', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
                $('#pdfHeader').hide(); // Hide after capture
                var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
                
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    navigator.share({
                        title: 'Purchase Report',
                        text: 'Please find the attached Purchase Report.',
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
                
                var msg = "*Purchase Report*\nPlease find the attached PDF document.";
                var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
                window.open(waUrl, '_blank');
            });
        }

        /* ---------- Export Options & PDF ---------- */
        window.showExportOptions = function() {
            Swal.fire({
                title: 'Export Purchase Report',
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

            var element = document.getElementById('reportContent');
            $('#pdfHeader').show(); // Show header for capture

            var opt = {
                margin:       0.2,
                filename:     'Purchase_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a3', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                $('#pdfHeader').hide(); // Hide after capture
                Swal.close();
            });
        };

        window.exportCSV = function () {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            $('#loader').show();

            $.ajax({
                url: "{{ route('report.purchase.fetch') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    start_date: start_date,
                    end_date: end_date,
                    vendor_id: $('#vendor_id').val(),
                    branch_id: $('#branch_id').val()
                },
                success: function(response) {
                    $('#loader').hide();
                    if (!response.data || !response.data.length) {
                        alert('No data to export');
                        return;
                    }

                    var csv = 'Purchase Date,Invoice No,Vendor,Item Code,Item Name,Qty,Unit,Price,Item Discount,Line Total,Subtotal,Discount,Extra Cost,Net Amount,Paid Amount,Due Amount\n';
                    response.data.forEach(function(r) {
                        csv += `"${r.purchase_date}","${r.invoice_no}","${r.vendor_name}","${r.item_code}","${r.item_name}",${r.qty},${r.unit},${r.price},${r.item_discount},${r.line_total},${r.subtotal},${r.discount},${r.extra_cost},${r.net_amount},${r.paid_amount},${r.due_amount}\n`;
                    });

                    var blob = new Blob(["\uFEFF" + csv], {type:'text/csv;charset=utf-8;'});
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = 'purchase_report_' + new Date().toISOString().slice(0,10) + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                },
                error: function() {
                    $('#loader').hide();
                    alert('Export failed');
                }
            });
        };
    });
</script>
@endsection