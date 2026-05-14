@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid px-4">
            
            {{-- PAGE HEADER --}}
            <div class="row mb-3 align-items-center mt-3">
                <div class="col">
                    <h4 class="mb-0 fw-bold" style="color:#1a1a2e;">
                        <i class="fas fa-shopping-cart me-2" style="color:#28a745;"></i>
                        Sale Report
                    </h4>
                    <small class="text-muted">ERP Standard &mdash; Detailed Sales Analysis & Business Intelligence</small>
                </div>
            </div>

            {{-- FILTER CARD --}}
            <div class="card shadow-sm mb-3" style="border-radius:10px;border:none;">
                <div class="card-body py-3">
                    <form id="saleFilterForm" class="row g-3 align-items-end">
                        @if(auth()->user()->hasRole('super admin'))
                        <div class="col-md-2">
                            <label class="form-label fw-semibold mb-1">Select Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control form-control-sm select2">
                                <option value="">-- All Branches --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label fw-semibold mb-1">Select Customer</label>
                            <select name="customer_id" id="customer_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Customers --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold mb-1">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold mb-1">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}">
                        </div>
                        <div class="col-md-1">
                            <button type="button" id="btnSearch" class="btn btn-primary btn-sm w-100" style="background:#0066cc;border-color:#0066cc;padding:7px;">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" id="waShareBtn" onclick="shareWhatsApp()" class="btn btn-outline-success btn-sm shadow-sm me-1" style="border-color:#25D366; color:#25D366; background: #fff;">
                                <i class="fab fa-whatsapp me-1"></i> WhatsApp
                            </button>
                            <button type="button" onclick="showExportOptions()" class="btn btn-outline-info btn-sm shadow-sm" style="background: #fff; border-color: #17a2b8; color: #17a2b8;">
                                <i class="fas fa-download me-1"></i> Export
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- SUMMARY CARDS --}}
            <div id="summaryCards" class="row mb-3 g-3" style="display:none;">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-primary text-white" style="border-radius:10px;">
                        <div class="card-body p-3 text-center">
                            <div class="lbl text-white opacity-75">Gross Sales</div>
                            <div id="stat_gross" class="h5 fw-bold mb-0">0.00</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-success text-white" style="border-radius:10px;">
                        <div class="card-body p-3 text-center">
                            <div class="lbl text-white opacity-75">Net Revenue</div>
                            <div id="stat_net" class="h5 fw-bold mb-0">0.00</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-danger text-white" style="border-radius:10px;">
                        <div class="card-body p-3 text-center">
                            <div class="lbl text-white opacity-75">Sales Returns</div>
                            <div id="stat_return" class="h5 fw-bold mb-0">0.00</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-info text-white" style="border-radius:10px;">
                        <div class="card-body p-3 text-center">
                            <div class="lbl text-white opacity-75">Total Discount</div>
                            <div id="stat_disc" class="h5 fw-bold mb-0">0.00</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- REPORT TABLE --}}
            <div class="card shadow-sm border-0" style="border-radius:10px;" id="reportContent">
                <div class="card-body p-4">
                    
                    {{-- PDF HEADER (HIDDEN ON SCREEN) --}}
                    <div id="pdfHeader" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #1a1a2e; padding-bottom:10px;">
                        <h2 style="margin:0; color:#1a1a2e; text-transform:uppercase; letter-spacing:1px;">SALE REPORT</h2>
                        <p style="margin:5px 0; font-size:14px; color:#333;">
                            <strong>Period:</strong> <span id="pdfPeriod"></span> | 
                            <strong>Customer:</strong> <span id="pdfCustomerName">All Customers</span>
                        </p>
                        <p style="margin:0; font-size:12px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div id="loader" style="display:none;text-align:center;padding:40px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Processing sales data...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="saleTable" class="table table-bordered mb-0" style="font-size:13px;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#1a1a2e;color:#fff;">
                                    <th class="text-center" style="padding:15px 5px;">#</th>
                                    <th style="padding:15px 5px;">Date</th>
                                    <th style="padding:15px 5px;">Invoice</th>
                                    <th style="padding:15px 5px;">Customer</th>
                                    <th style="padding:15px 5px;">Products / Items</th>
                                    <th class="text-end" style="padding:15px 5px;">Qty</th>
                                    <th class="text-end" style="padding:15px 5px;">Disc</th>
                                    <th class="text-end" style="padding:15px 5px;background:#2d4a6e;">Item Total</th>
                                    <th class="text-end" style="padding:15px 5px;background:#1b5e20;">Total Net</th>
                                    <th class="text-end" style="padding:15px 5px;background:#b71c1c;">Returns</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody"></tbody>
                            <tfoot id="tableFooter" style="display:none;">
                                <tr class="fw-bold bg-light">
                                    <td colspan="5" class="text-end" style="font-size:15px;">Totals:</td>
                                    <td class="text-end" id="footerQty" style="font-size:15px;">0.00</td>
                                    <td class="text-end text-danger" id="footerDisc" style="font-size:15px;">0.00</td>
                                    <td class="text-end text-primary" id="footerGross" style="font-size:15px;">0.00</td>
                                    <td class="text-end text-success" id="footerNet" style="font-size:15px;">0.00</td>
                                    <td class="text-end text-danger" id="footerReturn" style="font-size:15px;">0.00</td>
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
    #saleTable { border: 2px solid #1a1a2e; }
    #saleTable th { 
        vertical-align: middle; 
        border: 1px solid #444; 
        text-transform: uppercase; 
        letter-spacing: 0.5px;
        font-weight: 700;
    }
    #saleTable td { 
        vertical-align: middle; 
        border: 1px solid #ccc; 
        padding: 12px 10px; 
        font-weight: 500;
        color: #1a1a2e;
    }
    #saleTable tbody tr:nth-child(even) { background-color: #f9f9f9; }
    #saleTable tbody tr:hover { background-color: #f0f4ff !important; transition: 0.2s; }
    
    .invoice-no { color: #0066cc; font-weight: 700; font-size: 14px; }
    .customer-name { font-weight: 700; color: #333; font-size: 14px; }
    .product-item { padding: 4px 0; border-bottom: 1px dashed #eee; }
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
                        <span class="fw-bold text-dark" style="font-size:13px;">${item.product_name}</span> 
                        <code class="text-muted">(${item.product_code})</code><br>
                        <span class="text-muted small">Qty: <strong>${item.qty}</strong> x ${fmt(item.price)} = <strong>${fmt(item.amount)}</strong></span>
                        ${item.discount_amount > 0 ? `<span class="text-danger ms-2 fw-bold">Disc: ${fmt(item.discount_amount)}</span>` : ''}
                    </div>`;
                });

                let returnsHtml = "-";
                if (s.returns && s.returns.length > 0) {
                    returnsHtml = "";
                    s.returns.forEach(r => {
                        returnsHtml += `<div class="text-danger fw-bold small border-bottom border-danger-subtle pb-1 mb-1">
                            ${r.product} (${r.qty}) = ${fmt(r.total_net)}
                        </div>`;
                    });
                }

                tableContent += `<tr>
                    <td class="text-center">${idx + 1}</td>
                    <td style="white-space:nowrap;">${new Date(s.created_at).toLocaleDateString('en-GB')}</td>
                    <td class="invoice-no">${s.invoice_no}</td>
                    <td>
                        <div class="customer-name">${s.customer_name}</div>
                        <small class="text-muted d-block">${s.address || '-'}</small>
                    </td>
                    <td>${itemsHtml}</td>
                    <td class="text-end fw-bold" style="font-size:14px;">${n(s.total_qty).toFixed(2)}</td>
                    <td class="text-end text-danger fw-bold">${fmt(s.discount_amount)}</td>
                    <td class="text-end fw-bold text-primary" style="background:#f0f7ff; font-size:14px;">${fmt(s.total_items_amount)}</td>
                    <td class="text-end fw-bold text-success" style="background:#f0fff0; font-size:15px;">${fmt(s.total_net)}</td>
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