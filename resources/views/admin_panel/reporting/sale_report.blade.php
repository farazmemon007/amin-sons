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

    #saleTable th {
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
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Sale Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-chart-line mr-1" style="color: var(--coa-gold);"></i> Detailed Sales Analysis & Business Intelligence &mdash; Ameen & Sons Corporate ERP</span>
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
                    <form id="saleFilterForm" class="row g-2 align-items-end mb-0">
                        @if(auth()->user()->hasRole('super admin'))
                        <div class="col-md-3">
                            <label class="f-label">Select Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control form-control-sm select2">
                                <option value="">-- All Branches --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-{{ auth()->user()->hasRole('super admin') ? '3' : '4' }}">
                            <label class="f-label">Select Customer</label>
                            <select name="customer_id" id="customer_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Customers --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
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

            {{-- 3. KPI Summary Cards --}}
            <div id="summaryCards" class="rpt-kpi-grid" style="display:none;">
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Gross Sales</div>
                        <div id="stat_gross" class="rpt-kpi-val">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-blue">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card highlight">
                    <div>
                        <div class="rpt-kpi-label" style="color: #047857;">Net Revenue</div>
                        <div id="stat_net" class="rpt-kpi-val emerald">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-emerald">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="rpt-kpi-card">
                    <div>
                        <div class="rpt-kpi-label">Sales Returns</div>
                        <div id="stat_return" class="rpt-kpi-val crimson">0.00</div>
                    </div>
                    <div class="rpt-kpi-icon kpi-icon-red">
                        <i class="fas fa-undo"></i>
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
                        <h2 style="margin:0; color:#0f1f38; text-transform:uppercase; letter-spacing:1px;">SALE REPORT</h2>
                        <p style="margin:5px 0; font-size:14px; color:#333;">
                            <strong>Period:</strong> <span id="pdfPeriod"></span> | 
                            <strong>Customer:</strong> <span id="pdfCustomerName">All Customers</span>
                        </p>
                        <p style="margin:0; font-size:12px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div id="loader" style="display:none;text-align:center;padding:40px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Processing sales data...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="saleTable" class="table table-bordered mb-0" style="font-size:12px; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 35px;">#</th>
                                    <th style="width: 90px; white-space: nowrap;">Date</th>
                                    <th style="width: 105px; white-space: nowrap;">Invoice</th>
                                    <th style="min-width: 150px; width: 16%;">Customer</th>
                                    <th style="min-width: 250px; width: 26%;">Products / Items</th>
                                    <th class="text-end" style="width: 75px; white-space: nowrap;">Qty</th>
                                    <th class="text-end" style="width: 80px; white-space: nowrap;">Disc</th>
                                    <th class="text-end" style="width: 100px; white-space: nowrap;">Gross Total</th>
                                    <th class="text-end" style="width: 105px; white-space: nowrap;">Total Net</th>
                                    <th class="text-end" style="width: 95px; white-space: nowrap;">Returns</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody"></tbody>
                            <tfoot id="tableFooter" style="display:none;">
                                <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                                    <td colspan="5" class="text-end font-weight-bold" style="font-family: sans-serif;">Grand Totals:</td>
                                    <td class="text-end font-weight-bold text-dark" id="footerQty">0.00</td>
                                    <td class="text-end text-danger font-weight-bold" id="footerDisc">0.00</td>
                                    <td class="text-end text-primary font-weight-bold" id="footerGross">0.00</td>
                                    <td class="text-end text-success font-weight-bold" style="background:rgba(13, 159, 110, 0.12);" id="footerNet">0.00</td>
                                    <td class="text-end text-danger font-weight-bold" id="footerReturn">0.00</td>
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
    #saleTable { border: 1px solid #cbd5e1; }
    #saleTable th { 
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
    #saleTable td { 
        vertical-align: middle; 
        border: 1px solid #e2e8f0; 
        padding: 8px 8px; 
        font-weight: 500;
        color: #1e293b;
    }
    #saleTable tbody tr:nth-child(even) { background-color: #f8fafc; }
    #saleTable tbody tr:hover { background-color: #f1f5f9 !important; transition: 0.15s; }
    
    .invoice-no { color: #1e3a5f; font-weight: 700; font-family: monospace; font-size: 12px; }
    .customer-name { font-weight: 700; color: #0f172a; font-size: 12.5px; }
    .product-item { padding: 3px 0; border-bottom: 1px dashed #e2e8f0; }
    .product-item:last-child { border-bottom: none; }
    
    @media print {
        #saleFilterForm, #btnSearch, .btn { display: none !important; }
        .main-content { padding: 0 !important; margin-top:0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        #reportContent { overflow: visible !important; }
    }
</style>
@endsection

@section('js')
<script>
    const isSuper = {{ (auth()->user() && auth()->user()->hasRole('super admin')) ? 'true' : 'false' }};

    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: '-- Select Option --'
        });

        // ✅ DYNAMIC: Fetch customers when branch changes (for Super Admin)
        $('#branch_id').on('change', function() {
            let branchId = $(this).val();
            let customerDropdown = $('#customer_id');

            // Show loading state
            customerDropdown.html('<option value="all">Loading...</option>');

            $.ajax({
                url: "{{ route('report.customers.byBranch') }}",
                type: "GET",
                data: { branch_id: branchId },
                success: function(res) {
                    let html = '<option value="all">-- All Customers --</option>';
                    res.forEach(function(c) {
                        html += `<option value="${c.id}">${c.customer_name}</option>`;
                    });
                    customerDropdown.html(html).trigger('change');
                },
                error: function() {
                    customerDropdown.html('<option value="all">-- Error loading --</option>');
                }
            });
        });
        
        function n(v) { return parseFloat(v) || 0; }
        function fmt(v) { return n(v).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

        function renderRows(rows) {
            let tableContent = '';
            let grandQty = 0;
            let grandGross = 0;
            let grandDisc = 0;
            let grandNet = 0;
            let grandReturn = 0;

            if (rows.length === 0) {
                $('#reportBody').html('<tr><td colspan="10" class="text-center py-4 text-muted">No sales found for this criteria</td></tr>');
                $('#summaryCards').hide();
                $('#tableFooter').hide();
                return;
            }

            rows.forEach(function(s, idx) {
                let itemsHtml = "";
                s.items.forEach(item => {
                    itemsHtml += `<div class="product-item">
                        <span class="font-weight-bold text-dark" style="font-size:12.5px;">${item.product_name}</span> 
                        <code class="text-muted" style="font-size:10.5px;">(${item.product_code})</code><br>
                        <span class="text-muted small" style="font-size:11px;">Qty: <strong>${item.qty}</strong> x ${fmt(item.price)} = <strong class="text-dark">${fmt(item.amount)}</strong></span>
                        ${item.discount_amount > 0 ? `<span class="text-danger ms-2 font-weight-bold" style="font-size:11px;">Disc: ${fmt(item.discount_amount)}</span>` : ''}
                    </div>`;
                });

                let returnsHtml = '<span class="text-muted font-monospace">&#8212;</span>';
                if (s.returns && s.returns.length > 0) {
                    returnsHtml = "";
                    s.returns.forEach(r => {
                        returnsHtml += `<div class="text-danger font-weight-bold small border-bottom border-danger-subtle pb-1 mb-1 font-monospace" style="font-size:11.5px;">
                            ${r.product} (${r.qty}) = ${fmt(r.total_net)}
                        </div>`;
                    });
                }

                let branchBadge = '';
                if (isSuper && s.branch_name) {
                    branchBadge = `<div class="text-muted mt-1" style="font-size: 10.5px; opacity: 0.85;"><i class="fas fa-building mr-1" style="font-size: 9.5px; color: #64748b;"></i><span style="color: #475569; font-weight: 600;">${s.branch_name}</span></div>`;
                }

                let addressHtml = s.address ? `<small class="text-muted d-block" style="font-size: 11px;">${s.address}</small>` : '';

                tableContent += `<tr>
                    <td class="text-center text-muted" style="font-size:11px;">${idx + 1}</td>
                    <td style="white-space:nowrap; font-size:11.5px;">${new Date(s.created_at).toLocaleDateString('en-GB')}</td>
                    <td><span class="invoice-no">${s.invoice_no}</span></td>
                    <td>
                        <div class="customer-name">${s.customer_name}</div>
                        ${addressHtml}
                        ${branchBadge}
                    </td>
                    <td>${itemsHtml}</td>
                    <td class="text-end font-monospace font-weight-bold" style="font-size:12px; white-space:nowrap;">${n(s.total_qty).toFixed(2)}</td>
                    <td class="text-end font-monospace ${n(s.discount_amount) > 0 ? 'text-danger font-weight-bold' : 'text-muted'}" style="font-size:12px;">${fmt(s.discount_amount)}</td>
                    <td class="text-end font-monospace font-weight-bold" style="background:rgba(30, 58, 95, 0.04); color:#1e3a5f; font-size:12px;">${fmt(s.total_items_amount)}</td>
                    <td class="text-end font-monospace font-weight-bold" style="background:rgba(13, 159, 110, 0.08); color:#047857; font-size:12.5px;">${fmt(s.total_net)}</td>
                    <td class="text-end" style="background:#fff5f5;">${returnsHtml}</td>
                </tr>`;

                grandQty += n(s.total_qty);
                grandGross += n(s.total_items_amount);
                grandDisc += n(s.discount_amount);
                grandNet += n(s.total_net);
                grandReturn += n(s.total_returns_amount);
            });

            // Summary Stats
            $('#stat_gross').text('Rs. ' + fmt(grandGross));
            $('#stat_net').text('Rs. ' + fmt(grandNet));
            $('#stat_return').text('Rs. ' + fmt(grandReturn));
            $('#stat_disc').text('Rs. ' + fmt(grandDisc));
            $('#summaryCards').fadeIn();

            // Footer Totals
            $('#footerQty').text(grandQty.toFixed(2));
            $('#footerDisc').text(fmt(grandDisc));
            $('#footerGross').text(fmt(grandGross));
            $('#footerNet').text(fmt(grandNet));
            $('#footerReturn').text(fmt(grandReturn));
            $('#tableFooter').show();

            $('#reportBody').html(tableContent);
        }

        $('#btnSearch').on('click', function() {
            fetchReport();
        });

        // ✅ ERP STANDARD: Auto-fetch report on page load
        fetchReport();

        function fetchReport() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var customer_id = $('#customer_id').val();
            var branch_id = $('#branch_id').val() || '';

            // Update PDF Header Info for Export
            let periodText = start_date + ' to ' + end_date;
            if (branch_id) {
                periodText += ' | Branch: ' + $('#branch_id option:selected').text();
            }
            $('#pdfPeriod').text(periodText);
            $('#pdfCustomerName').text($('#customer_id option:selected').text());

            $('#loader').show();

            $.ajax({
                url: "{{ route('report.sale.fetch') }}",
                type: "GET",
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    customer_id: customer_id,
                    branch_id: branch_id
                },
                success: function(response) {
                    $('#loader').hide();
                    renderRows(response);
                },
                error: function() {
                    $('#loader').hide();
                    alert('Error fetching sale report');
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
            $('#pdfHeader').show(); 

            var opt = {
                margin:       0.2,
                filename:     'Sale_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a3', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
                $('#pdfHeader').hide(); 
                var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
                
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    navigator.share({
                        title: 'Sale Report',
                        text: 'Please find the attached Sale Report.',
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
                text: 'The PDF will be downloaded now. Please attach it manually to your WhatsApp chat.',
                confirmButtonText: 'Download & Open WhatsApp'
            }).then(() => {
                var url = URL.createObjectURL(pdfBlob);
                var a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                
                var msg = "*Sale Report*\nPlease find the attached PDF document.";
                var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
                window.open(waUrl, '_blank');
            });
        }

        /* ---------- Export Options ---------- */
        window.showExportOptions = function() {
            Swal.fire({
                title: 'Export Sale Report',
                text: 'Choose format:',
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
                filename:     'Sale_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a3', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                $('#pdfHeader').hide(); 
                Swal.close();
            });
        };

        window.exportCSV = function () {
            let csv = [];
            let headers = [];
            $("#saleTable thead th").each(function() {
                headers.push('"' + $(this).text().trim() + '"');
            });
            csv.push(headers.join(","));

            $("#saleTable tbody tr").each(function() {
                let row = [];
                $(this).find('td').each(function() {
                    let cellText = $(this).text().replace(/<[^>]*>/g, "").replace(/\s+/g, " ").trim();
                    row.push('"' + cellText.replace(/"/g, '""') + '"');
                });
                csv.push(row.join(","));
            });

            let csvString = "\uFEFF" + csv.join("\n");
            let blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            let url = URL.createObjectURL(blob);
            let a = document.createElement("a");
            a.href = url;
            a.download = "sale_report_" + new Date().toISOString().slice(0,10) + ".csv";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        };
    });
</script>
@endsection