@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid px-4">
            
            {{-- PAGE HEADER --}}
            <div class="row mb-3 align-items-center mt-3">
                <div class="col">
                    <h4 class="mb-0 fw-bold" style="color:#1a1a2e;">
                        <i class="fas fa-user-tie me-2" style="color:#ffc107;"></i>
                        Salesman Performance Report
                    </h4>
                    <small class="text-muted">ERP Standard &mdash; Monthly Productivity & Sales Analysis</small>
                </div>
            </div>

            {{-- FILTER CARD --}}
            <div class="card shadow-sm mb-3" style="border-radius:10px;border:none;">
                <div class="card-body py-3">
                    <form id="performanceFilterForm" class="row g-3 align-items-end">
                        @if($isSuper)
                        <div class="col-md-2">
                            <label class="form-label fw-semibold mb-1">Select Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control form-control-sm select2 branch-trigger">
                                <option value="">-- All Branches --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->branch_name ?? $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="branch_id" id="branch_id" value="{{ $userBranchId }}">
                        @endif

                        <div class="col-md-3">
                            <label class="form-label fw-semibold mb-1">Select Salesman</label>
                            <select name="salesman_id" id="salesman_id" class="form-control form-control-sm select2">
                                <option value="">-- All (Summary) --</option>
                                <option value="direct">Direct Sale (No Salesman)</option>
                                @foreach($salesmen as $sm)
                                    <option value="{{ $sm->id }}">{{ $sm->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-semibold mb-1">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold mb-1">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-1">
                            <button type="button" id="btnSearch" class="btn btn-primary btn-sm w-100" style="background:#0066cc;border-color:#0066cc;padding:7px;">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="btn-group shadow-sm" role="group">
                                <button type="button" onclick="showExportOptions()" class="btn btn-outline-info btn-sm" style="background: #fff; border-color: #17a2b8; color: #17a2b8; padding: 7px 10px;" title="Export">
                                    <i class="fas fa-file-export"></i>
                                </button>
                                <button type="button" onclick="shareOnWhatsApp()" class="btn btn-outline-success btn-sm" style="background: #fff; border-color: #28a745; color: #28a745; padding: 7px 10px;" title="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- REPORT TABLE --}}
            <div class="card shadow-sm border-0" style="border-radius:10px;" id="reportContent">
                <div class="card-body p-4">
                    
                    {{-- PDF HEADER (HIDDEN ON SCREEN) --}}
                    <div id="pdfHeader" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #1a1a2e; padding-bottom:10px;">
                        <h2 style="margin:0; color:#1a1a2e; text-transform:uppercase; letter-spacing:1px;">SALESMAN PERFORMANCE REPORT</h2>
                        <p style="margin:5px 0; font-size:14px; color:#333;">
                            <strong>Period:</strong> <span id="pdfPeriod"></span>
                        </p>
                        <p style="margin:0; font-size:12px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div id="loader" style="display:none;text-align:center;padding:40px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">Aggregating performance data...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="performanceTable" class="table table-bordered mb-0" style="font-size:13px;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#1a1a2e;color:#fff;">
                                    <th class="text-center" style="padding:15px 5px;">#</th>
                                    <th style="padding:15px 5px;">Salesman Name</th>
                                    <th style="padding:15px 5px;">Branch</th>
                                    <th style="padding:15px 5px;">Month</th>
                                    <th class="text-end" style="padding:15px 5px;">Total Invoices</th>
                                    <th class="text-end" style="padding:15px 5px;background:#1b5e20;">Total Sales (PKR)</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody"></tbody>
                            <tfoot id="tableFooter" style="display:none;">
                                <tr class="fw-bold bg-light">
                                    <td colspan="4" class="text-end" style="font-size:15px;">Totals:</td>
                                    <td class="text-end" id="footerInvoices" style="font-size:15px;">0</td>
                                    <td class="text-end text-success" id="footerTotalAmount" style="font-size:15px;">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- DETAILED LEDGER SECTION (Show only when salesman selected) --}}
            <div class="card shadow-sm border-0 mt-4 d-none" style="border-radius:10px;" id="ledgerContent">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-list-alt me-2"></i>
                        Detailed Sales Ledger: <span id="selectedSalesmanName"></span>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div id="ledgerLoader" style="display:none;text-align:center;padding:20px;">
                        <div class="spinner-border text-info" role="status"></div>
                        <p class="text-muted mt-2">Loading transaction history...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="ledgerTable" class="table table-hover table-sm" style="font-size:12px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Branch</th>
                                    <th class="text-end">Amount (PKR)</th>
                                </tr>
                            </thead>
                            <tbody id="ledgerBody"></tbody>
                            <tfoot>
                                <tr class="fw-bold table-active">
                                    <td colspan="4" class="text-end">Total Amount:</td>
                                    <td class="text-end text-primary" id="ledgerTotal">0.00</td>
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
    #performanceTable { border: 2px solid #1a1a2e; }
    #performanceTable th { 
        vertical-align: middle; 
        border: 1px solid #444; 
        text-transform: uppercase; 
        letter-spacing: 0.5px;
        font-weight: 700;
    }
    #performanceTable td { 
        vertical-align: middle; 
        border: 1px solid #ccc; 
        padding: 12px 10px; 
        font-weight: 500;
        color: #1a1a2e;
    }
    #performanceTable tbody tr:nth-child(even) { background-color: #f9f9f9; }
    #performanceTable tbody tr:hover { background-color: #f0f4ff !important; transition: 0.2s; }
    
    .salesman-name { font-weight: 700; color: #0066cc; font-size: 14px; }
    
    @media print {
        #performanceFilterForm, #btnSearch, .btn { display: none !important; }
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

        function n(v) { return parseFloat(v) || 0; }
        function fmt(v) { return n(v).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

        function renderRows(rows) {
            let tableContent = '';
            let grandInvoices = 0;
            let grandAmount = 0;

            if (rows.length === 0) {
                $('#reportBody').html('<tr><td colspan="6" class="text-center py-4 text-muted">No performance data found for this criteria</td></tr>');
                $('#tableFooter').hide();
                return;
            }

            rows.forEach(function(r, idx) {
                tableContent += `<tr>
                    <td class="text-center">${idx + 1}</td>
                    <td><div class="salesman-name">${r.salesman_name || 'N/A'}</div></td>
                    <td>${r.branch_name || '-'}</td>
                    <td>${r.month_year}</td>
                    <td class="text-end fw-bold">${r.total_invoices}</td>
                    <td class="text-end fw-bold text-success" style="background:#f0fff0; font-size:15px;">${fmt(r.total_amount)}</td>
                </tr>`;

                grandInvoices += n(r.total_invoices);
                grandAmount += n(r.total_amount);
            });

            // Footer Totals
            $('#footerInvoices').text(grandInvoices);
            $('#footerTotalAmount').text(fmt(grandAmount));
            $('#tableFooter').show();

            $('#reportBody').html(tableContent);
        }

        $('#btnSearch').on('click', function() {
            fetchReport();
        });

        // Auto-fetch on page load
        fetchReport();

        function fetchReport() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var branch_id = $('#branch_id').val() || '';
            var salesman_id = $('#salesman_id').val() || '';

            // Update PDF Header Info for Export
            let periodText = start_date + ' to ' + end_date;
            if (branch_id) {
                periodText += ' | Branch: ' + $('#branch_id option:selected').text();
            }
            $('#pdfPeriod').text(periodText);

            // If a specific salesman is selected, show their ledger instead of the summary
            if (salesman_id) {
                $('#reportContent').hide();
                $('#ledgerContent').removeClass('d-none').show();
                fetchSalesmanLedger(salesman_id, start_date, end_date);
                return;
            } else {
                $('#reportContent').show();
                $('#ledgerContent').hide();
            }

            $('#loader').show();

            $.ajax({
                url: "{{ route('report.salesman.performance.fetch') }}",
                type: "GET",
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    branch_id: branch_id
                },
                success: function(response) {
                    $('#loader').hide();
                    renderRows(response);
                },
                error: function(xhr) {
                    $('#loader').hide();
                    let msg = 'Error fetching performance report';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        msg += ': ' + xhr.responseJSON.error;
                    }
                    alert(msg);
                }
            });
        }

        function fetchSalesmanLedger(salesman_id, start_date, end_date) {
            $('#selectedSalesmanName').text($('#salesman_id option:selected').text());
            $('#ledgerLoader').show();
            $('#ledgerBody').empty();

            $.ajax({
                url: "{{ route('report.salesman.ledger.fetch') }}",
                type: "GET",
                data: {
                    salesman_id: salesman_id,
                    start_date: start_date,
                    end_date: end_date
                },
                success: function(response) {
                    $('#ledgerLoader').hide();
                    let html = '';
                    let total = 0;

                    if (response.length === 0) {
                        html = '<tr><td colspan="5" class="text-center py-3 text-muted">No transactions found for this salesman</td></tr>';
                    } else {
                        response.forEach(function(item) {
                            html += `<tr>
                                <td>${item.date}</td>
                                <td class="fw-bold text-primary">${item.invoice_no}</td>
                                <td>${item.customer}</td>
                                <td>${item.branch}</td>
                                <td class="text-end fw-bold">${fmt(item.total_amount)}</td>
                            </tr>`;
                            total += n(item.total_amount);
                        });
                    }

                    $('#ledgerBody').html(html);
                    $('#ledgerTotal').text(fmt(total));
                },
                error: function(xhr) {
                    $('#ledgerLoader').hide();
                    alert('Error fetching salesman ledger');
                }
            });
        }

        // ✅ NEW: Branch Change Trigger for Salesman Filtering
        $(document).on('change', '.branch-trigger', function() {
            const branchId = $(this).val();
            loadSalesmenByBranch(branchId);
        });

        function loadSalesmenByBranch(branchId) {
            const $smSelect = $('#salesman_id');
            $smSelect.html('<option selected disabled>Loading salesmen...</option>');

            $.get("{{ route('report.salesmen.byBranch') }}", { branch_id: branchId }, function(data) {
                let html = '<option value="">-- All (Summary) --</option>';
                html += '<option value="direct">Direct Sale (No Salesman)</option>';
                
                if (data.length > 0) {
                    data.forEach(sm => {
                        html += `<option value="${sm.id}">${sm.name}</option>`;
                    });
                }
                
                $smSelect.html(html).trigger('change');
            });
        }

        /* ---------- Export Options ---------- */
        window.showExportOptions = function() {
            Swal.fire({
                title: 'Export Performance Report',
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

            const isLedger = !$('#ledgerContent').hasClass('d-none');
            const element = isLedger ? document.getElementById('ledgerContent') : document.getElementById('reportContent');
            
            // Temporarily show PDF header if needed (though card-header might be enough)
            $('#pdfHeader').show(); 

            var opt = {
                margin:       0.2,
                filename:     (isLedger ? 'Salesman_Ledger_' : 'Salesman_Performance_') + new Date().toISOString().slice(0,10) + '.pdf',
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
            
            // Determine which table to export
            const isLedger = !$('#ledgerContent').hasClass('d-none');
            const tableSelector = isLedger ? "#ledgerTable" : "#performanceTable";
            
            $(tableSelector + " thead th").each(function() {
                headers.push('"' + $(this).text().trim() + '"');
            });
            csv.push(headers.join(","));

            $(tableSelector + " tbody tr").each(function() {
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
            a.download = (isLedger ? "salesman_ledger_" : "salesman_performance_") + new Date().toISOString().slice(0,10) + ".csv";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        };

        window.shareOnWhatsApp = function() {
            const isLedger = !$('#ledgerContent').hasClass('d-none');
            const salesman = isLedger ? $('#selectedSalesmanName').text() : 'All Salesmen';
            const period = $('#start_date').val() + ' to ' + $('#end_date').val();
            
            let text = `📊 *Salesman Performance Report*\n`;
            text += `👤 *Salesman:* ${salesman}\n`;
            text += `📅 *Period:* ${period}\n`;
            text += `━━━━━━━━━━━━━━━━━━━━\n`;

            if (isLedger) {
                text += `💰 *Total Sales:* ${$('#ledgerTotal').text()}\n`;
                text += `📄 _Details shared below_\n`;
                // Add a few recent invoices if any
                $('#ledgerBody tr').slice(0, 5).each(function() {
                    const date = $(this).find('td:eq(0)').text();
                    const inv = $(this).find('td:eq(1)').text();
                    const amt = $(this).find('td:eq(4)').text();
                    text += `• ${date} | ${inv} | ${amt}\n`;
                });
            } else {
                text += `📊 *Total Invoices:* ${$('#footerInvoices').text()}\n`;
                text += `💰 *Total Amount:* ${$('#footerTotalAmount').text()}\n`;
                text += `━━━━━━━━━━━━━━━━━━━━\n`;
                // Add top 5 salesmen
                $('#reportBody tr').slice(0, 5).each(function() {
                    const name = $(this).find('.salesman-name').text();
                    const amt = $(this).find('td:eq(5)').text();
                    text += `👤 ${name}: ${amt}\n`;
                });
            }

            text += `\n_Generated via ERP System_`;
            
            const waUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
            window.open(waUrl, '_blank');
        };
    });
</script>
@endsection
