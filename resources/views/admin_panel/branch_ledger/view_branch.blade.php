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
    .kpi-icon-gray { background: #f1f5f9; color: #64748b; }

    .f-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.03em;
        margin-bottom: 4px;
        display: block;
    }

    #txTable {
        border-collapse: collapse;
        width: 100%;
    }

    #txTable thead th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 12px;
        border: 1px solid #1e3a5f;
    }

    #txTable tbody td {
        padding: 9px 12px;
        vertical-align: middle;
        border: 1px solid #e2e8f0;
    }

    #txTable tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    #txTable tbody tr:hover {
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
                    <strong>Your Branch Ledger:</strong> You are viewing ledger entries for your branch.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- 1. Corporate Header Bar -->
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Branch Ledger &mdash; {{ $branch->name ?? $branch->branch_name ?? 'Branch #' . $branch->id }}</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-history mr-1" style="color: var(--coa-gold);"></i> Detailed inter-branch transaction history and running statements &mdash; Ameen & Sons Corporate ERP</span>
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
                    <a href="{{ route('branch_ledger_transfer_details', $branch->id) }}" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.3);">
                        <i class="fas fa-exchange-alt mr-1"></i> Transfers
                    </a>
                    <a href="{{ route('branch_ledger_all_branches') }}" class="btn btn-sm btn-light font-weight-bold text-muted border">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>

            <!-- 2. Filter Form -->
            <div class="card shadow-sm border-0 mb-3" id="filterForm" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('branch_ledger_view_branch', $branch->id) }}" class="row g-2 align-items-end mb-0">
                        <div class="col-md-3">
                            <label class="f-label">From Date</label>
                            <input type="date" id="from_date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>

                        <div class="col-md-3">
                            <label class="f-label">To Date</label>
                            <input type="date" id="to_date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>

                        <div class="col-md-3">
                            <label class="f-label">Transaction Type</label>
                            <select name="type" id="type" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                                <option value="">-- All Types --</option>
                                <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Credit (Receivable / Inflow)</option>
                                <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Debit (Payable / Outflow)</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary flex-grow-1 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('branch_ledger_view_branch', $branch->id) }}" class="btn btn-sm btn-light border font-weight-bold text-muted d-inline-flex align-items-center justify-content-center" style="height: 38px; border-radius: 6px; width: 38px;" title="Clear Filters">
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
                    <h2 style="margin:0; color:#0f1f38; text-transform:uppercase; letter-spacing:1px;">Branch Ledger &mdash; {{ $branch->name ?? $branch->branch_name ?? 'Branch #' . $branch->id }}</h2>
                    <p style="margin:5px 0; font-size:13px; color:#333;">Detailed Inter-Branch Transaction History & Running Balance &mdash; Ameen & Sons Corporate ERP</p>
                    <p style="margin:0; font-size:11.5px; color:#666;">
                        Report Generated on: {{ date('d-M-Y H:i') }}
                        @if(request('from_date') || request('to_date'))
                            | Period: {{ request('from_date', 'Start') }} to {{ request('to_date', 'Current') }}
                        @endif
                        @if(request('type'))
                            | Type: {{ ucfirst(request('type')) }}
                        @endif
                    </p>
                </div>

                <!-- 3. Account Summary Cards -->
                <div class="rpt-kpi-grid">
                    <div class="rpt-kpi-card highlight">
                        <div>
                            <div class="rpt-kpi-label" style="color: #047857;">Current Balance</div>
                            <div class="rpt-kpi-val @if ($balance > 0) emerald @elseif ($balance < 0) crimson @else text-muted @endif">
                                {{ $balance > 0 ? '+' : '' }}{{ number_format($balance, 2) }}
                            </div>
                            <small class="d-block mt-1 font-weight-bold" style="font-size: 10.5px; color: {{ $balance > 0 ? '#047857' : ($balance < 0 ? '#dc2626' : '#64748b') }};">
                                @if ($balance > 0)
                                    <i class="fas fa-check-circle mr-1"></i> We're owed
                                @elseif ($balance < 0)
                                    <i class="fas fa-arrow-circle-up mr-1"></i> We owe
                                @else
                                    <i class="fas fa-balance-scale mr-1"></i> Balanced
                                @endif
                            </small>
                        </div>
                        <div class="rpt-kpi-icon kpi-icon-emerald">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    <div class="rpt-kpi-card">
                        <div>
                            <div class="rpt-kpi-label">Total Credits (Receivable)</div>
                            <div class="rpt-kpi-val emerald">{{ number_format($totalCredit, 2) }}</div>
                            <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Money owed to us</small>
                        </div>
                        <div class="rpt-kpi-icon kpi-icon-blue">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                    </div>
                    <div class="rpt-kpi-card">
                        <div>
                            <div class="rpt-kpi-label">Total Debits (Payable)</div>
                            <div class="rpt-kpi-val crimson">{{ number_format($totalDebit, 2) }}</div>
                            <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Money we owe</small>
                        </div>
                        <div class="rpt-kpi-icon kpi-icon-red">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                    <div class="rpt-kpi-card">
                        <div>
                            <div class="rpt-kpi-label">Total Transactions</div>
                            <div class="rpt-kpi-val">{{ $transactions->total() }}</div>
                            <small class="text-muted d-block mt-1" style="font-size: 10.5px;">All recorded entries</small>
                        </div>
                        <div class="rpt-kpi-icon kpi-icon-gray">
                            <i class="fas fa-list"></i>
                        </div>
                    </div>
                </div>

                <!-- 4. Transactions Table -->
                <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 font-weight-bold" style="color: var(--coa-navy);">
                                <i class="fas fa-history mr-1" style="color: var(--coa-gold);"></i> Transaction History
                            </h6>
                            <small class="text-muted">Showing {{ $transactions->count() }} of {{ $transactions->total() }} entries</small>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table id="txTable" class="table table-bordered align-middle mb-0" style="font-size: 12.5px;">
                                <thead>
                                    <tr>
                                        <th style="width: 140px;">Date</th>
                                        <th>Description</th>
                                        <th style="width: 160px;">Related Branch</th>
                                        <th style="width: 140px;">Reference</th>
                                        <th style="width: 100px;" class="text-center">Type</th>
                                        <th style="width: 150px;" class="text-end">Amount</th>
                                        <th style="width: 120px;">Created By</th>
                                    </tr>
                                </thead>
                                <tbody id="txTableBody">
                                    @forelse ($transactions as $transaction)
                                        <tr>
                                            <td style="white-space: nowrap; font-size: 11.5px;">
                                                <i class="far fa-calendar-alt text-muted mr-1"></i>
                                                {{ $transaction->created_at->format('d-M-Y H:i') }}
                                            </td>
                                            <td>
                                                <strong class="text-dark" style="font-size: 12.5px;">{{ $transaction->display_description }}</strong>
                                            </td>
                                            <td>
                                                @if ($transaction->relatedBranch)
                                                    <span class="badge" style="background:#f1f5f9; color:#334155; font-size: 11px; font-weight:600; padding:4px 8px; border: 1px solid #e2e8f0;">
                                                        <i class="fas fa-building mr-1 text-muted"></i>{{ $transaction->relatedBranch->name ?? 'Branch #' . $transaction->related_branch_id }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">&#8212;</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted font-monospace text-uppercase" style="font-size: 11px;">
                                                    {{ ucfirst(str_replace('_', ' ', $transaction->reference_type)) }} #{{ $transaction->reference_id }}
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                @if ($transaction->type === 'debit')
                                                    <span class="badge badge-danger px-2 py-1" style="font-size: 10.5px;">
                                                        <i class="fas fa-arrow-up mr-1"></i> Debit
                                                    </span>
                                                @else
                                                    <span class="badge badge-success px-2 py-1" style="font-size: 10.5px;">
                                                        <i class="fas fa-arrow-down mr-1"></i> Credit
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-end font-monospace font-weight-bold @if ($transaction->type === 'debit') text-danger @else text-success @endif" style="font-size: 13px;">
                                                {{ $transaction->type === 'debit' ? '-' : '+' }}{{ number_format($transaction->display_amount, 2) }}
                                            </td>
                                            <td style="font-size: 11.5px;">
                                                <span class="text-muted">
                                                    <i class="fas fa-user-circle mr-1 text-muted"></i>
                                                    {{ $transaction->createdBy->name ?? 'System' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox text-muted" style="font-size: 2em;"></i>
                                                <p class="text-muted mt-2 mb-0">No transactions found for this branch.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid var(--coa-navy);">
                                        <td colspan="4" class="text-end text-uppercase" style="font-size: 11.5px; color: var(--coa-navy);">Summary Totals:</td>
                                        <td class="text-center" style="font-size: 11px;">
                                            Credits: <span class="text-success font-monospace">+{{ number_format($totalCredit, 2) }}</span><br>
                                            Debits: <span class="text-danger font-monospace">-{{ number_format($totalDebit, 2) }}</span>
                                        </td>
                                        <td class="text-end font-monospace" style="font-size: 13px; color: {{ $balance > 0 ? '#047857' : ($balance < 0 ? '#dc2626' : '#64748b') }};">
                                            Net: {{ $balance > 0 ? '+' : '' }}{{ number_format($balance, 2) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if ($transactions->hasPages())
                            <div class="mt-3 d-flex justify-content-end">
                                {{ $transactions->links() }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
<script>
$(document).ready(function() {

    const branchName = "{{ addslashes($branch->name ?? $branch->branch_name ?? 'Branch') }}";

    function getReportUrl() {
        var fromDate = $('#from_date').val() || '';
        var toDate = $('#to_date').val() || '';
        var type = $('#type').val() || '';

        var url = "{{ route('branch_ledger_report') }}?branch_id={{ $branch->id }}";
        if (fromDate) url += '&from_date=' + encodeURIComponent(fromDate);
        if (toDate) url += '&to_date=' + encodeURIComponent(toDate);
        if (type) url += '&type=' + encodeURIComponent(type);
        return url;
    }

    /* ---------- WhatsApp Share ---------- */
    window.shareWhatsApp = function() {
        var reportUrl = getReportUrl();

        Swal.fire({
            icon: 'info',
            title: 'Share PDF via WhatsApp',
            text: 'Click below to download the Statement PDF and open WhatsApp.',
            confirmButtonText: '<i class="fab fa-whatsapp mr-1"></i> Download & Open WhatsApp',
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#25D366'
        }).then((result) => {
            if (result.isConfirmed) {
                // Download statement PDF
                window.location.href = reportUrl;

                // Open WhatsApp
                var msg = "*Branch Ledger Statement — " + branchName + "*\nPlease find the attached Statement PDF.";
                var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
                setTimeout(function() {
                    window.open(waUrl, '_blank');
                }, 600);
            }
        });
    };

    /* ---------- Export Options & PDF / Excel (.xlsx) ---------- */
    window.showExportOptions = function() {
        Swal.fire({
            title: 'Export Branch Ledger',
            text: 'Choose your preferred export format:',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-file-excel mr-1"></i> Excel (.xlsx)',
            cancelButtonText: '<i class="fas fa-file-pdf mr-1"></i> PDF',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                exportXLSX();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                exportPDF();
            }
        });
    };

    window.exportPDF = function() {
        // Fetch and download the Statement PDF
        window.location.href = getReportUrl();
    };

    /* ---------- Premium Formatted Excel (.xlsx) Export ---------- */
    window.exportXLSX = function () {
        var fromDate = $('#from_date').val();
        var toDate = $('#to_date').val();
        var filterType = $('#type').val();

        var periodText = "All Time";
        if (fromDate && toDate) {
            periodText = fromDate + " to " + toDate;
        } else if (fromDate) {
            periodText = "From " + fromDate;
        } else if (toDate) {
            periodText = "Up to " + toDate;
        }
        if (filterType) {
            periodText += " (" + filterType.toUpperCase() + " only)";
        }

        var workbook = new ExcelJS.Workbook();
        workbook.creator = "Ameen & Sons Corporate ERP";
        workbook.created = new Date();

        var ws = workbook.addWorksheet('Branch Ledger', {
            views: [{ showGridLines: true }]
        });

        // Set column widths
        ws.columns = [
            { key: 'date', width: 20 },
            { key: 'description', width: 48 },
            { key: 'branch', width: 24 },
            { key: 'reference', width: 28 },
            { key: 'type', width: 16 },
            { key: 'amount', width: 22 },
            { key: 'created_by', width: 18 }
        ];

        var borderThin = {
            top: { style: 'thin', color: { argb: 'FFCBD5E1' } },
            left: { style: 'thin', color: { argb: 'FFCBD5E1' } },
            bottom: { style: 'thin', color: { argb: 'FFCBD5E1' } },
            right: { style: 'thin', color: { argb: 'FFCBD5E1' } }
        };

        // 1. Company Title Header (Row 1)
        ws.mergeCells('A1:G1');
        var r1 = ws.getCell('A1');
        r1.value = "AMEEN & SONS CORPORATE ERP — BRANCH FINANCIAL LEDGER";
        r1.font = { name: 'Arial', size: 13, bold: true, color: { argb: 'FFFFFFFF' } };
        r1.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F1F38' } };
        r1.alignment = { vertical: 'middle', horizontal: 'center' };
        ws.getRow(1).height = 32;

        // 2. Subheader Banner (Row 2)
        ws.mergeCells('A2:G2');
        var r2 = ws.getCell('A2');
        r2.value = "Branch: " + branchName + "   |   Period: " + periodText + "   |   Generated: " + new Date().toLocaleString();
        r2.font = { name: 'Arial', size: 10, bold: true, color: { argb: 'FFE2E8F0' } };
        r2.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1E3A5F' } };
        r2.alignment = { vertical: 'middle', horizontal: 'center' };
        ws.getRow(2).height = 22;

        // 3. Spacer (Row 3)
        ws.getRow(3).height = 8;

        // 4. KPI Summary Cards (Row 4 & 5)
        var totCred = parseFloat("{{ $totalCredit }}") || 0;
        var totDeb = parseFloat("{{ $totalDebit }}") || 0;
        var netBal = parseFloat("{{ $balance }}") || 0;
        var txCount = $('#txTableBody tr').length;

        // Card 1: Current Balance
        ws.mergeCells('A4:B4');
        ws.getCell('A4').value = "CURRENT BALANCE";
        ws.getCell('A4').font = { name: 'Arial', size: 8.5, bold: true, color: { argb: 'FF64748B' } };
        ws.getCell('A4').fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };
        ws.getCell('A4').alignment = { vertical: 'middle', horizontal: 'center' };
        ws.getCell('A4').border = borderThin;

        ws.mergeCells('A5:B5');
        ws.getCell('A5').value = (netBal >= 0 ? '+' : '') + netBal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " (" + (netBal > 0 ? "Owed" : (netBal < 0 ? "Owing" : "Balanced")) + ")";
        ws.getCell('A5').font = { name: 'Arial', size: 12, bold: true, color: { argb: netBal >= 0 ? 'FF047857' : 'FFDC2626' } };
        ws.getCell('A5').fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: netBal >= 0 ? 'FFF0FDF4' : 'FFFEE2E2' } };
        ws.getCell('A5').alignment = { vertical: 'middle', horizontal: 'center' };
        ws.getCell('A5').border = borderThin;

        // Card 2: Total Credits
        ws.mergeCells('C4:D4');
        ws.getCell('C4').value = "TOTAL CREDITS (RECEIVABLE)";
        ws.getCell('C4').font = { name: 'Arial', size: 8.5, bold: true, color: { argb: 'FF64748B' } };
        ws.getCell('C4').fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };
        ws.getCell('C4').alignment = { vertical: 'middle', horizontal: 'center' };
        ws.getCell('C4').border = borderThin;

        ws.mergeCells('C5:D5');
        ws.getCell('C5').value = "+" + totCred.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        ws.getCell('C5').font = { name: 'Arial', size: 12, bold: true, color: { argb: 'FF047857' } };
        ws.getCell('C5').fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFECFDF5' } };
        ws.getCell('C5').alignment = { vertical: 'middle', horizontal: 'center' };
        ws.getCell('C5').border = borderThin;

        // Card 3: Total Debits
        ws.getCell('E4').value = "TOTAL DEBITS (PAYABLE)";
        ws.getCell('E4').font = { name: 'Arial', size: 8.5, bold: true, color: { argb: 'FF64748B' } };
        ws.getCell('E4').fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };
        ws.getCell('E4').alignment = { vertical: 'middle', horizontal: 'center' };
        ws.getCell('E4').border = borderThin;

        ws.getCell('E5').value = "-" + totDeb.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        ws.getCell('E5').font = { name: 'Arial', size: 12, bold: true, color: { argb: 'FFDC2626' } };
        ws.getCell('E5').fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFEE2E2' } };
        ws.getCell('E5').alignment = { vertical: 'middle', horizontal: 'center' };
        ws.getCell('E5').border = borderThin;

        // Card 4: Transactions Count
        ws.mergeCells('F4:G4');
        ws.getCell('F4').value = "RECORDED ENTRIES";
        ws.getCell('F4').font = { name: 'Arial', size: 8.5, bold: true, color: { argb: 'FF64748B' } };
        ws.getCell('F4').fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };
        ws.getCell('F4').alignment = { vertical: 'middle', horizontal: 'center' };
        ws.getCell('F4').border = borderThin;

        ws.mergeCells('F5:G5');
        ws.getCell('F5').value = "{{ $transactions->total() }} Total Transactions";
        ws.getCell('F5').font = { name: 'Arial', size: 11, bold: true, color: { argb: 'FF0F1F38' } };
        ws.getCell('F5').fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FAFC' } };
        ws.getCell('F5').alignment = { vertical: 'middle', horizontal: 'center' };
        ws.getCell('F5').border = borderThin;

        ws.getRow(4).height = 18;
        ws.getRow(5).height = 24;

        // 5. Spacer (Row 6)
        ws.getRow(6).height = 10;

        // 6. Table Headers (Row 7)
        var headers = ['Date', 'Description', 'Related Branch', 'Reference', 'Type', 'Amount (PKR)', 'Created By'];
        var headerRow = ws.getRow(7);
        headerRow.values = headers;
        headerRow.height = 26;

        headerRow.eachCell(function(cell) {
            cell.font = { name: 'Arial', size: 10.5, bold: true, color: { argb: 'FFFFFFFF' } };
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F1F38' } };
            cell.alignment = { vertical: 'middle', horizontal: 'center' };
            cell.border = {
                top: { style: 'medium', color: { argb: 'FF0F1F38' } },
                left: { style: 'thin', color: { argb: 'FF1E3A5F' } },
                bottom: { style: 'medium', color: { argb: 'FF0F1F38' } },
                right: { style: 'thin', color: { argb: 'FF1E3A5F' } }
            };
        });

        // 7. Table Data Rows (Row 8 onwards)
        var currentRowIdx = 8;
        $('#txTableBody tr').each(function() {
            var $tr = $(this);
            var date = $tr.find('td:nth-child(1)').text().replace(/\s+/g, ' ').trim();
            var desc = $tr.find('td:nth-child(2)').text().replace(/\s+/g, ' ').trim();
            var relBranch = $tr.find('td:nth-child(3)').text().replace(/\s+/g, ' ').trim();
            var ref = $tr.find('td:nth-child(4)').text().replace(/\s+/g, ' ').trim();
            var typeText = $tr.find('td:nth-child(5)').text().replace(/\s+/g, ' ').trim();
            var amountText = $tr.find('td:nth-child(6)').text().replace(/,/g, '').trim();
            var createdBy = $tr.find('td:nth-child(7)').text().replace(/\s+/g, ' ').trim();

            if (date && !date.includes('No transactions found')) {
                var isDebit = typeText.toLowerCase().includes('debit') || amountText.startsWith('-');
                var rawAmount = parseFloat(amountText.replace(/[^0-9.-]/g, '')) || 0;
                var finalAmount = isDebit ? -Math.abs(rawAmount) : Math.abs(rawAmount);

                var row = ws.getRow(currentRowIdx);
                row.values = [
                    date,
                    desc,
                    relBranch || '—',
                    ref,
                    isDebit ? 'Debit' : 'Credit',
                    finalAmount,
                    createdBy || 'System'
                ];
                row.height = 22;

                var isEven = (currentRowIdx % 2 === 0);
                var rowBgColor = isEven ? 'FFF8FAFC' : 'FFFFFFFF';

                row.eachCell({ includeEmpty: true }, function(cell, colNumber) {
                    cell.border = borderThin;
                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: rowBgColor } };
                    cell.font = { name: 'Arial', size: 9.5, color: { argb: 'FF1E293B' } };

                    if (colNumber === 1) {
                        cell.alignment = { vertical: 'middle', horizontal: 'center' };
                    } else if (colNumber === 2) {
                        cell.alignment = { vertical: 'middle', horizontal: 'left' };
                        cell.font = { name: 'Arial', size: 9.5, bold: true, color: { argb: 'FF0F1F38' } };
                    } else if (colNumber === 3) {
                        cell.alignment = { vertical: 'middle', horizontal: 'center' };
                    } else if (colNumber === 4) {
                        cell.alignment = { vertical: 'middle', horizontal: 'left' };
                        cell.font = { name: 'Arial', size: 9, color: { argb: 'FF475569' } };
                    } else if (colNumber === 5) {
                        cell.alignment = { vertical: 'middle', horizontal: 'center' };
                        cell.font = { name: 'Arial', size: 9.5, bold: true, color: { argb: isDebit ? 'FFDC2626' : 'FF16A34A' } };
                        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: isDebit ? 'FFFEE2E2' : 'FFDCFCE7' } };
                    } else if (colNumber === 6) {
                        cell.alignment = { vertical: 'middle', horizontal: 'right' };
                        cell.numFmt = '+#,##0.00;-#,##0.00;0.00';
                        cell.font = { name: 'Arial', size: 10, bold: true, color: { argb: isDebit ? 'FFDC2626' : 'FF16A34A' } };
                    } else if (colNumber === 7) {
                        cell.alignment = { vertical: 'middle', horizontal: 'center' };
                        cell.font = { name: 'Arial', size: 9, color: { argb: 'FF64748B' } };
                    }
                });

                currentRowIdx++;
            }
        });

        // 8. Summary Grand Totals Rows
        // Spacer row
        ws.getRow(currentRowIdx).height = 6;
        currentRowIdx++;

        // Summary 1: Total Credits
        ws.mergeCells('A' + currentRowIdx + ':E' + currentRowIdx);
        var sc1 = ws.getCell('A' + currentRowIdx);
        sc1.value = "TOTAL CREDITS (RECEIVABLE):";
        sc1.font = { name: 'Arial', size: 10, bold: true, color: { argb: 'FF0F1F38' } };
        sc1.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };
        sc1.alignment = { vertical: 'middle', horizontal: 'right' };
        sc1.border = borderThin;

        var sc1Val = ws.getCell('F' + currentRowIdx);
        sc1Val.value = totCred;
        sc1Val.numFmt = '+#,##0.00';
        sc1Val.font = { name: 'Arial', size: 10.5, bold: true, color: { argb: 'FF047857' } };
        sc1Val.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFECFDF5' } };
        sc1Val.alignment = { vertical: 'middle', horizontal: 'right' };
        sc1Val.border = borderThin;

        var sc1Unit = ws.getCell('G' + currentRowIdx);
        sc1Unit.value = "PKR";
        sc1Unit.font = { name: 'Arial', size: 9, bold: true, color: { argb: 'FF64748B' } };
        sc1Unit.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };
        sc1Unit.alignment = { vertical: 'middle', horizontal: 'center' };
        sc1Unit.border = borderThin;
        ws.getRow(currentRowIdx).height = 24;
        currentRowIdx++;

        // Summary 2: Total Debits
        ws.mergeCells('A' + currentRowIdx + ':E' + currentRowIdx);
        var sc2 = ws.getCell('A' + currentRowIdx);
        sc2.value = "TOTAL DEBITS (PAYABLE):";
        sc2.font = { name: 'Arial', size: 10, bold: true, color: { argb: 'FF0F1F38' } };
        sc2.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };
        sc2.alignment = { vertical: 'middle', horizontal: 'right' };
        sc2.border = borderThin;

        var sc2Val = ws.getCell('F' + currentRowIdx);
        sc2Val.value = -totDeb;
        sc2Val.numFmt = '-#,##0.00';
        sc2Val.font = { name: 'Arial', size: 10.5, bold: true, color: { argb: 'FFDC2626' } };
        sc2Val.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFEE2E2' } };
        sc2Val.alignment = { vertical: 'middle', horizontal: 'right' };
        sc2Val.border = borderThin;

        var sc2Unit = ws.getCell('G' + currentRowIdx);
        sc2Unit.value = "PKR";
        sc2Unit.font = { name: 'Arial', size: 9, bold: true, color: { argb: 'FF64748B' } };
        sc2Unit.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };
        sc2Unit.alignment = { vertical: 'middle', horizontal: 'center' };
        sc2Unit.border = borderThin;
        ws.getRow(currentRowIdx).height = 24;
        currentRowIdx++;

        // Summary 3: Net Balance
        var borderThick = {
            top: { style: 'medium', color: { argb: 'FF0F1F38' } },
            left: { style: 'thin', color: { argb: 'FF0F1F38' } },
            bottom: { style: 'double', color: { argb: 'FF0F1F38' } },
            right: { style: 'thin', color: { argb: 'FF0F1F38' } }
        };

        ws.mergeCells('A' + currentRowIdx + ':E' + currentRowIdx);
        var sc3 = ws.getCell('A' + currentRowIdx);
        sc3.value = "NET RUNNING BALANCE (" + (netBal > 0 ? "WE'RE OWED" : (netBal < 0 ? "WE OWE" : "BALANCED")) + "):";
        sc3.font = { name: 'Arial', size: 11, bold: true, color: { argb: 'FFFFFFFF' } };
        sc3.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F1F38' } };
        sc3.alignment = { vertical: 'middle', horizontal: 'right' };
        sc3.border = borderThick;

        var sc3Val = ws.getCell('F' + currentRowIdx);
        sc3Val.value = netBal;
        sc3Val.numFmt = '+#,##0.00;-#,##0.00;0.00';
        sc3Val.font = { name: 'Arial', size: 12, bold: true, color: { argb: netBal >= 0 ? 'FF047857' : 'FFDC2626' } };
        sc3Val.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: netBal >= 0 ? 'FFF0FDF4' : 'FFFEE2E2' } };
        sc3Val.alignment = { vertical: 'middle', horizontal: 'right' };
        sc3Val.border = borderThick;

        var sc3Unit = ws.getCell('G' + currentRowIdx);
        sc3Unit.value = "PKR";
        sc3Unit.font = { name: 'Arial', size: 10, bold: true, color: { argb: 'FFFFFFFF' } };
        sc3Unit.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F1F38' } };
        sc3Unit.alignment = { vertical: 'middle', horizontal: 'center' };
        sc3Unit.border = borderThick;
        ws.getRow(currentRowIdx).height = 28;

        // Generate and download .xlsx
        workbook.xlsx.writeBuffer().then(function(buffer) {
            var blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'Branch_Ledger_' + branchName.replace(/[^a-zA-Z0-9]/g, '_') + '_' + new Date().toISOString().slice(0,10) + '.xlsx';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }).catch(function(err) {
            console.error('Excel export error:', err);
            Swal.fire('Error', 'Failed to generate formatted Excel spreadsheet.', 'error');
        });
    };

});
</script>
@endsection
