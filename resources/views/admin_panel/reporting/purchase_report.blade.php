@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid px-4">
            
            {{-- PAGE HEADER --}}
            <div class="row mb-3 align-items-center mt-3">
                <div class="col">
                    <h4 class="mb-0 fw-bold" style="color:#1a1a2e;">
                        <i class="fas fa-file-invoice-dollar me-2" style="color:#0066cc;"></i>
                        Purchase Report
                    </h4>
                    <small class="text-muted">ERP Standard &mdash; Detailed Purchase History & Analytical View</small>
                </div>
            </div>

            {{-- FILTER CARD --}}
            <div class="card shadow-sm mb-3" style="border-radius:10px;border:none;">
                <div class="card-body py-3">
                    <form id="purchaseFilterForm" class="row g-3 align-items-end">
                        <div class="col-md-2" id="branch_container" @if(!$user->hasRole('super admin')) style="display:none;" @endif>
                            <label class="form-label fw-semibold mb-1">Select Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control form-control-sm select2">
                                @if($user->hasRole('super admin'))
                                    <option value="all">-- All Branches --</option>
                                @endif
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $user->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold mb-1">Select Vendor</label>
                            <select name="vendor_id" id="vendor_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Vendors --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
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
                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-primary btn-sm w-100" style="background:#0066cc;border-color:#0066cc;padding:7px;">
                                <i class="fas fa-search me-1"></i> Search
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
                            <div class="lbl text-white opacity-75">Net Purchases</div>
                            <div id="stat_net" class="h5 fw-bold mb-0">0.00</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-success text-white" style="border-radius:10px;">
                        <div class="card-body p-3 text-center">
                            <div class="lbl text-white opacity-75">Total Paid</div>
                            <div id="stat_paid" class="h5 fw-bold mb-0">0.00</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-danger text-white" style="border-radius:10px;">
                        <div class="card-body p-3 text-center">
                            <div class="lbl text-white opacity-75">Total Balance</div>
                            <div id="stat_due" class="h5 fw-bold mb-0">0.00</div>
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
                        <h2 style="margin:0; color:#1a1a2e; text-transform:uppercase; letter-spacing:1px;">Purchase Report</h2>
                        <p style="margin:5px 0; font-size:14px; color:#333;">
                            <strong>Period:</strong> <span id="pdfPeriod"></span> | 
                            <strong>Vendor:</strong> <span id="pdfVendorName">All Vendors</span>
                        </p>
                        <p style="margin:0; font-size:12px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div id="loader" style="display:none;text-align:center;padding:40px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Aggregating purchase data...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="purchaseTable" class="table table-bordered mb-0" style="font-size:13px;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#1a1a2e;color:#fff;">
                                    <th class="text-center" style="padding:15px 5px;">#</th>
                                    <th style="padding:15px 5px;">Date</th>
                                    <th style="padding:15px 5px;">Invoice</th>
                                    <th style="padding:15px 5px;">Vendor</th>
                                    <th style="padding:15px 5px;">Item</th>
                                    <th class="text-end" style="padding:15px 5px;">Qty</th>
                                    <th class="text-end" style="padding:15px 5px;">Price</th>
                                    <th class="text-end" style="padding:15px 5px;">Item Disc</th>
                                    <th class="text-end" style="padding:15px 5px;background:#2d4a6e;">Net Item</th>
                                    <th class="text-end" style="padding:15px 5px;">Bill Disc</th>
                                    <th class="text-end" style="padding:15px 5px;background:#1b5e20;">Total Net</th>
                                    <th class="text-end" style="padding:15px 5px;">Paid</th>
                                    <th class="text-end" style="padding:15px 5px;">Due</th>
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
    #purchaseTable { border: 2px solid #1a1a2e; }
    #purchaseTable th { 
        vertical-align: middle; 
        border: 1px solid #444; 
        text-transform: uppercase; 
        letter-spacing: 0.5px;
        font-weight: 700;
    }
    #purchaseTable td { 
        vertical-align: middle; 
        border: 1px solid #ccc; 
        padding: 10px 8px; 
        font-weight: 500;
        color: #1a1a2e;
    }
    #purchaseTable tbody tr:nth-child(even) { background-color: #f9f9f9; }
    #purchaseTable tbody tr:hover { background-color: #f0f4ff !important; transition: 0.2s; }
    
    .invoice-no { color: #0066cc; font-weight: 700; font-size: 14px; }
    .item-name { font-weight: 700; color: #333; font-size: 14px; }
    
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
                            colorBadges += `<span class="badge bg-light text-primary border border-primary px-1 me-1" style="font-size:9px;">${color.toUpperCase()}</span>`;
                        }
                    });
                }

                tableContent += `<tr>
                    <td class="text-center">${idx + 1}</td>
                    <td style="white-space:nowrap;">${r.purchase_date}</td>
                    <td class="invoice-no">${r.invoice_no}</td>
                    <td class="fw-bold">${r.vendor_name}</td>
                    <td>
                        <div class="item-name">${r.item_name}</div>
                        <div class="mb-1">${colorBadges}</div>
                        <code class="text-muted" style="font-size:11px;">${r.item_code}</code>
                    </td>
                    <td class="text-end fw-bold" style="font-size:13px;">${n(r.qty).toFixed(2)} <small>${r.unit}</small></td>
                    <td class="text-end">${fmt(r.price)}</td>
                    <td class="text-end text-danger fw-bold">${fmt(r.item_discount)}</td>
                    <td class="text-end fw-bold text-primary" style="background:#f0f7ff; font-size:13px;">${fmt(r.line_total)}</td>
                    <td class="text-end text-danger fw-bold">${fmt(r.discount)}</td>
                    <td class="text-end fw-bold text-success" style="background:#f0fff0; font-size:14px;">${fmt(r.net_amount)}</td>
                    <td class="text-end">${fmt(r.paid_amount)}</td>
                    <td class="text-end fw-bold ${n(r.due_amount) > 0 ? 'text-danger' : 'text-success'}" style="font-size:13px;">${fmt(r.due_amount)}</td>
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
            tableContent += `<tr class="fw-bold bg-light">
                <td colspan="10" class="text-end" style="font-size:14px;">Total Summary (Unique Invoices):</td>
                <td class="text-end text-success" style="font-size:14px; background:#e8f5e9;">${fmt(grandNet)}</td>
                <td class="text-end" style="font-size:14px;">${fmt(grandPaid)}</td>
                <td class="text-end text-danger" style="font-size:14px;">${fmt(grandDue)}</td>
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