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

    .f-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.03em;
        margin-bottom: 4px;
        display: block;
    }

    #performanceTable th {
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
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Salesman Performance Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-chart-line mr-1" style="color: var(--coa-gold);"></i> Monthly productivity, commission & sales target analysis &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" onclick="shareOnWhatsApp()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(37, 211, 102, 0.2); border-color: #25D366; color: #25D366;">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </button>
                    <button type="button" onclick="showExportOptions()" class="btn btn-sm btn-light font-weight-bold text-dark border">
                        <i class="fas fa-file-export mr-1 text-primary"></i> Export
                    </button>
                </div>
            </div>

            {{-- 2. Filter Card --}}
            <div class="card shadow-sm mb-3 border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form id="performanceFilterForm" class="row g-2 align-items-end mb-0">
                        @if($isSuper)
                        <div class="col-md-3">
                            <label class="f-label">Select Branch</label>
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

                        <div class="col-md-{{ $isSuper ? '3' : '4' }}">
                            <label class="f-label">Select Salesman</label>
                            <select name="salesman_id" id="salesman_id" class="form-control form-control-sm select2">
                                <option value="">-- All (Summary) --</option>
                                <option value="direct">Direct Sale (No Salesman)</option>
                                @foreach($salesmen as $sm)
                                    <option value="{{ $sm->id }}">{{ $sm->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="f-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ date('Y-m-01') }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>

                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-search mr-1"></i> Analyze
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. REPORT TABLE --}}
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="reportContent">
                <div class="card-body p-3">
                    
                    {{-- PDF HEADER (HIDDEN ON SCREEN) --}}
                    <div id="pdfHeader" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #0f1f38; padding-bottom:10px;">
                        <h2 style="margin:0; color:#0f1f38; text-transform:uppercase; letter-spacing:1px;">SALESMAN PERFORMANCE REPORT</h2>
                        <p style="margin:5px 0; font-size:14px; color:#333;">
                            <strong>Period:</strong> <span id="pdfPeriod"></span>
                        </p>
                        <p style="margin:0; font-size:12px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                    </div>

                    <div id="loader" style="display:none;text-align:center;padding:40px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Aggregating performance data...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="performanceTable" class="table table-bordered mb-0" style="font-size:12.5px; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%;">#</th>
                                    <th>Salesman Name</th>
                                    <th style="width: 18%;">Branch</th>
                                    <th style="width: 12%;">Month</th>
                                    <th class="text-end" style="width: 14%;">Total Invoices</th>
                                    <th class="text-end" style="width: 18%;">Total Sales (PKR)</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody"></tbody>
                            <tfoot id="tableFooter" style="display:none;">
                                <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                                    <td colspan="4" class="text-end font-weight-bold" style="font-family: sans-serif;">Totals:</td>
                                    <td class="text-end font-weight-bold text-dark" id="footerInvoices">0</td>
                                    <td class="text-end font-weight-bold text-success" id="footerTotalAmount">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 4. DETAILED LEDGER SECTION (Show only when salesman selected) --}}
            <div class="card shadow-sm border-0 mt-3 d-none" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="ledgerContent">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 font-weight-bold" style="color: var(--coa-navy);">
                        <i class="fas fa-list-alt mr-1" style="color: var(--coa-gold);"></i>
                        Detailed Sales Ledger: <span id="selectedSalesmanName" class="text-dark"></span>
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div id="ledgerLoader" style="display:none;text-align:center;padding:20px;">
                        <div class="spinner-border text-info" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Loading transaction history...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="ledgerTable" class="table table-hover table-bordered mb-0" style="font-size:12px;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 12%;">Date</th>
                                    <th style="width: 16%;">Invoice #</th>
                                    <th>Customer</th>
                                    <th style="width: 18%;">Branch</th>
                                    <th class="text-end" style="width: 16%;">Amount (PKR)</th>
                                </tr>
                            </thead>
                            <tbody id="ledgerBody"></tbody>
                            <tfoot>
                                <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                                    <td colspan="4" class="text-end font-weight-bold" style="font-family: sans-serif;">Total Amount:</td>
                                    <td class="text-end text-success font-weight-bold" id="ledgerTotal">0.00</td>
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
