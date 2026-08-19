@extends('admin_panel.layout.app')

@section('css')
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
    .kpi-icon-purple { background: #ede9fe; color: #7c3aed; }

    .f-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.03em;
        margin-bottom: 4px;
        display: block;
    }

    #transferTable {
        border-collapse: collapse;
        width: 100%;
    }

    #transferTable thead th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 12px;
        border: 1px solid #1e3a5f;
    }

    #transferTable tbody td {
        padding: 9px 12px;
        vertical-align: middle;
        border: 1px solid #e2e8f0;
    }

    #transferTable tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    #transferTable tbody tr:hover {
        background-color: #f1f5f9 !important;
    }

    @media print {
        #filterForm, .rpt-header-bar a, .rpt-header-bar button, .pagination, .alert { display: none !important; }
        .main-content { padding: 0 !important; margin-top:0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        #reportContent { overflow: visible !important; }
        .rpt-header-bar { background: #0f1f38 !important; color: #fff !important; }
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">
            
            <!-- Authorization Check Alert -->
            @if (!auth()->user()->hasRole('super admin') && auth()->user()->branch_id == $branch->id)
                <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                    <i class="fas fa-shield-alt mr-1"></i>
                    <strong>Your Branch Transfers:</strong> You are viewing stock transfers for your branch.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- 1. Corporate Header Bar -->
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Stock Transfer Details &mdash; {{ $branch->name ?? $branch->branch_name ?? 'Branch #' . $branch->id }}</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-boxes mr-1" style="color: var(--coa-gold);"></i> Complete incoming and outgoing transfer movement logs &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm font-weight-bold" onclick="shareWhatsApp()" style="background:#25D366; color:#fff; border:none; border-radius:6px; padding:6px 14px; box-shadow:0 2px 6px rgba(37,211,102,0.25);">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </button>
                    <button type="button" class="btn btn-sm font-weight-bold" onclick="showExportOptions()" style="background:#7c3aed; color:#fff; border:none; border-radius:6px; padding:6px 14px; box-shadow:0 2px 6px rgba(124,58,237,0.25);">
                        <i class="fas fa-download mr-1"></i> Export
                    </button>
                    <button type="button" class="btn btn-sm font-weight-bold" onclick="window.print()" style="background:rgba(255,255,255,0.15); color:#fff; border:1px solid rgba(255,255,255,0.3); border-radius:6px; padding:6px 14px;">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                    <a href="{{ route('branch_ledger_view_branch', $branch->id) }}" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.3);">
                        <i class="fas fa-book mr-1"></i> Branch Ledger
                    </a>
                    <a href="{{ route('branch_ledger_all_branches') }}" class="btn btn-sm btn-light font-weight-bold text-muted border">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Overview
                    </a>
                </div>
            </div>

            <!-- 2. Date Range Filter Form -->
            <div class="card shadow-sm border-0 mb-3" id="filterForm" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('branch_ledger_transfer_details', $branch->id) }}" class="row g-2 align-items-end mb-0">
                        <div class="col-md-4">
                            <label class="f-label">From Date</label>
                            <input type="date" id="from_date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>

                        <div class="col-md-4">
                            <label class="f-label">To Date</label>
                            <input type="date" id="to_date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>

                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary flex-grow-1 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('branch_ledger_transfer_details', $branch->id) }}" class="btn btn-sm btn-light border font-weight-bold text-muted d-inline-flex align-items-center justify-content-center" style="height: 38px; border-radius: 6px; width: 38px;" title="Clear Filters">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- REPORT PRINT & EXPORT WRAPPER -->
            <div id="reportContent">

                {{-- PDF HEADER (ONLY DISPLAYED IN EXPORT / PRINT) --}}
                <div id="pdfHeader" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #0f1f38; padding-bottom:10px;">
                    <h2 style="margin:0; color:#0f1f38; text-transform:uppercase; letter-spacing:1px;">Stock Transfer Details &mdash; {{ $branch->name ?? $branch->branch_name ?? 'Branch #' . $branch->id }}</h2>
                    <p style="margin:5px 0; font-size:13px; color:#333;">Complete incoming and outgoing transfer movement logs &mdash; Ameen & Sons Corporate ERP</p>
                    <p style="margin:0; font-size:11.5px; color:#666;">
                        Report Generated on: {{ date('d-M-Y H:i') }}
                        @if(request('from_date') || request('to_date'))
                            | Period: {{ request('from_date', 'Start') }} to {{ request('to_date', 'Current') }}
                        @endif
                    </p>
                </div>

                <!-- 3. Summary Statistics -->
                <div class="rpt-kpi-grid">
                    <div class="rpt-kpi-card">
                        <div>
                            <div class="rpt-kpi-label">Total Transfers</div>
                            <div class="rpt-kpi-val" id="stat_transfers">{{ $transfers->total() ?? $transfers->count() }}</div>
                            <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Movement entries</small>
                        </div>
                        <div class="rpt-kpi-icon kpi-icon-blue">
                            <i class="fas fa-truck-loading"></i>
                        </div>
                    </div>
                    <div class="rpt-kpi-card">
                        <div>
                            <div class="rpt-kpi-label">Total Quantity</div>
                            <div class="rpt-kpi-val font-monospace" id="stat_qty">{{ number_format($totalQuantity, 0) }} <span style="font-size: 12px; font-weight: 600; color: #64748b;">Units</span></div>
                            <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Transferred items</small>
                        </div>
                        <div class="rpt-kpi-icon kpi-icon-purple">
                            <i class="fas fa-cubes"></i>
                        </div>
                    </div>
                    <div class="rpt-kpi-card highlight">
                        <div>
                            <div class="rpt-kpi-label" style="color: #047857;">Total Transfer Value</div>
                            <div class="rpt-kpi-val emerald font-monospace" id="stat_val">Rs. {{ number_format($totalValue, 2) }}</div>
                            <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Cumulative monetary value</small>
                        </div>
                        <div class="rpt-kpi-icon kpi-icon-emerald">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>

                <!-- 4. Transfers Table -->
                <div class="card shadow-sm border-0 mb-3" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 font-weight-bold" style="color: var(--coa-navy);">
                            <i class="fas fa-list-alt mr-1" style="color: var(--coa-gold);"></i> Transfer Transactions
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table id="transferTable" class="table table-bordered align-middle mb-0" style="font-size: 12.5px;">
                            <thead>
                                <tr>
                                    <th style="width: 130px;">Date</th>
                                    <th style="width: 110px;" class="text-center">Direction</th>
                                    <th style="width: 140px;">From Branch</th>
                                    <th style="width: 140px;">To Branch</th>
                                    <th>Product / Item Details</th>
                                    <th class="text-center" style="width: 80px;">Qty</th>
                                    <th class="text-end" style="width: 110px;">Unit Price</th>
                                    <th class="text-end" style="width: 130px;">Total Value</th>
                                    <th style="width: 100px;" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="transferTableBody">
                                @forelse ($transfers as $transfer)
                                    <tr>
                                        <td style="white-space: nowrap; font-size: 11.5px;">
                                            <i class="far fa-calendar-alt text-muted mr-1"></i>
                                            {{ $transfer->created_at->format('d-M-Y H:i') }}
                                        </td>
                                        <td class="text-center">
                                            @if ($transfer->from_branch_id == $branch->id)
                                                <span class="badge" style="background:#fee2e2; color:#991b1b; border:1px solid #fecaca; font-size:11px; font-weight:700; padding:4px 8px; border-radius:4px;">
                                                    <i class="fas fa-arrow-right mr-1"></i> Outgoing
                                                </span>
                                            @else
                                                <span class="badge" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; font-size:11px; font-weight:700; padding:4px 8px; border-radius:4px;">
                                                    <i class="fas fa-arrow-left mr-1"></i> Incoming
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-dark" style="font-size: 12.5px;">
                                                {{ $transfer->fromBranch->name ?? $transfer->fromBranch->branch_name ?? 'Branch #' . $transfer->from_branch_id }}
                                            </strong>
                                        </td>
                                        <td>
                                            <strong class="text-dark" style="font-size: 12.5px;">
                                                {{ $transfer->toBranch->name ?? $transfer->toBranch->branch_name ?? 'Branch #' . $transfer->to_branch_id }}
                                            </strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong class="text-dark" style="font-size: 12.5px;">{{ $transfer->product->item_name ?? 'Product #' . $transfer->product_id }}</strong>
                                                <div class="text-muted small mt-1" style="font-size: 11px;">
                                                    <i class="fas fa-warehouse mr-1 text-muted"></i>
                                                    {{ $transfer->fromWarehouse->warehouse_name ?? 'WH' }} 
                                                    <span class="mx-1 text-primary">&rarr;</span> 
                                                    {{ $transfer->toWarehouse->warehouse_name ?? 'WH' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center font-monospace font-weight-bold" style="font-size: 12.5px;">
                                            {{ number_format($transfer->quantity, 0) }}
                                        </td>
                                        <td class="text-end font-monospace" style="font-size: 12.5px;">
                                            Rs. {{ number_format($transfer->unit_price, 2) }}
                                        </td>
                                        <td class="text-end font-monospace font-weight-bold text-success" style="font-size: 13px;">
                                            Rs. {{ number_format($transfer->total_value, 2) }}
                                        </td>
                                        <td class="text-center">
                                            @if ($transfer->status === 'approved')
                                                <span class="badge badge-success px-2 py-1" style="font-size: 10.5px;">
                                                    <i class="fas fa-check-circle mr-1"></i> Approved
                                                </span>
                                            @elseif ($transfer->status === 'pending')
                                                <span class="badge badge-warning text-dark px-2 py-1" style="font-size: 10.5px;">
                                                    <i class="fas fa-clock mr-1"></i> Pending
                                                </span>
                                            @else
                                                <span class="badge badge-secondary px-2 py-1" style="font-size: 10.5px;">
                                                    {{ ucfirst($transfer->status) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox text-muted" style="font-size: 2em;"></i>
                                            <p class="text-muted mt-2 mb-0">
                                                @if (request('from_date') || request('to_date'))
                                                    No transfers found for the selected date range.
                                                @else
                                                    No transfers found for this branch.
                                                @endif
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot id="transferFooter">
                                <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                                    <td colspan="5" class="text-end font-weight-bold" style="font-family: sans-serif;">Cumulative Grand Totals:</td>
                                    <td class="text-center font-weight-bold text-dark">{{ number_format($totalQuantity, 0) }}</td>
                                    <td class="text-end text-muted">
                                        @php $avgPrice = $totalQuantity > 0 ? $totalValue / $totalQuantity : 0; @endphp
                                        Rs. {{ number_format($avgPrice, 2) }}
                                    </td>
                                    <td class="text-end text-success font-weight-bold">Rs. {{ number_format($totalValue, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($transfers->hasPages())
                        <div class="card-footer bg-white border-top p-3 d-flex justify-content-end">
                            {{ $transfers->links() }}
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
$(document).ready(function() {

    const branchName = "{{ addslashes($branch->name ?? $branch->branch_name ?? 'Branch') }}";

    /* ---------- WhatsApp Share ---------- */
    window.shareWhatsApp = function() {
        Swal.fire({
            title: 'Preparing WhatsApp Share...',
            text: 'Generating PDF document to share.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        var element = document.getElementById('reportContent');
        $('#pdfHeader').show();

        var opt = {
            margin:       0.3,
            filename:     'Stock_Transfer_Details_' + branchName.replace(/[^a-zA-Z0-9]/g, '_') + '_' + new Date().toISOString().slice(0,10) + '.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
            $('#pdfHeader').hide();
            var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
            
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'Stock Transfer Details — ' + branchName,
                    text: 'Please find the attached Stock Transfer Details report for ' + branchName + '.',
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
            
            var msg = "*Stock Transfer Details — " + branchName + "*\nPlease find the attached PDF report.";
            var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
            window.open(waUrl, '_blank');
        });
    }

    /* ---------- Export Options & PDF ---------- */
    window.showExportOptions = function() {
        Swal.fire({
            title: 'Export Stock Transfer Details',
            text: 'Choose your preferred export format:',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-file-excel mr-1"></i> Excel (CSV)',
            cancelButtonText: '<i class="fas fa-file-pdf mr-1"></i> PDF',
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
            didOpen: () => {
                Swal.showLoading();
            }
        });

        var element = document.getElementById('reportContent');
        $('#pdfHeader').show();

        var opt = {
            margin:       0.3,
            filename:     'Stock_Transfer_Details_' + branchName.replace(/[^a-zA-Z0-9]/g, '_') + '_' + new Date().toISOString().slice(0,10) + '.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).save().then(function() {
            $('#pdfHeader').hide();
            Swal.close();
        });
    };

    /* ---------- CSV Export ---------- */
    window.exportCSV = function () {
        var rows = [['Date', 'Direction', 'From Branch', 'To Branch', 'Product Details', 'Warehouse Movement', 'Quantity', 'Unit Price (PKR)', 'Total Value (PKR)', 'Status']];
        
        $('#transferTableBody tr').each(function () {
            var $tr = $(this);
            var date = $tr.find('td:nth-child(1)').text().replace(/\s+/g, ' ').trim();
            var direction = $tr.find('td:nth-child(2)').text().replace(/\s+/g, ' ').trim();
            var fromBranch = $tr.find('td:nth-child(3)').text().replace(/\s+/g, ' ').trim();
            var toBranch = $tr.find('td:nth-child(4)').text().replace(/\s+/g, ' ').trim();
            var prodName = $tr.find('td:nth-child(5) strong').text().replace(/\s+/g, ' ').trim();
            var whMovement = $tr.find('td:nth-child(5) .text-muted').text().replace(/\s+/g, ' ').trim();
            var qty = $tr.find('td:nth-child(6)').text().replace(/,/g, '').trim();
            var price = $tr.find('td:nth-child(7)').text().replace(/Rs\./g, '').replace(/,/g, '').trim();
            var total = $tr.find('td:nth-child(8)').text().replace(/Rs\./g, '').replace(/,/g, '').trim();
            var status = $tr.find('td:nth-child(9)').text().replace(/\s+/g, ' ').trim();

            if (date && !date.includes('No transfers found')) {
                rows.push([
                    '"' + date + '"',
                    '"' + direction + '"',
                    '"' + fromBranch + '"',
                    '"' + toBranch + '"',
                    '"' + prodName + '"',
                    '"' + whMovement + '"',
                    '"' + qty + '"',
                    '"' + price + '"',
                    '"' + total + '"',
                    '"' + status + '"'
                ]);
            }
        });

        // Add Summary Row
        var totQty = "{{ number_format($totalQuantity, 0, '', '') }}";
        var totVal = "{{ number_format($totalValue, 2, '.', '') }}";
        var avgPr = "{{ $totalQuantity > 0 ? number_format($totalValue / $totalQuantity, 2, '.', '') : '0.00' }}";

        rows.push([
            '"Cumulative Grand Totals"',
            '""',
            '""',
            '""',
            '""',
            '""',
            '"' + totQty + '"',
            '"' + avgPr + '"',
            '"' + totVal + '"',
            '""'
        ]);

        var csv = rows.map(function(r) { return r.join(','); }).join('\n');
        var blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'Stock_Transfer_Details_' + branchName.replace(/[^a-zA-Z0-9]/g, '_') + '_' + new Date().toISOString().slice(0,10) + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    };

});
</script>
@endsection
