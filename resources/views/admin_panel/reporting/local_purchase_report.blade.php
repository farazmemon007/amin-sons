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
        letter-spacing: 0.04em;
        margin-bottom: 5px;
        display: block;
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

    #reportTable {
        border-collapse: collapse;
        width: 100%;
    }

    #reportTable thead th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 12px;
        border: 1px solid #1e3a5f;
    }

    #reportTable tbody td {
        padding: 9px 12px;
        vertical-align: middle;
        border: 1px solid #e2e8f0;
    }

    #reportTable tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    #reportTable tbody tr:hover {
        background-color: #f1f5f9 !important;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        display: inline-block;
    }

    .badge-paid {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .badge-partial {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .badge-due {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Corporate Header Bar --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-store-alt"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Local Purchase & Market Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-receipt mr-1" style="color: var(--coa-gold);"></i> Direct Market Purchases & Spot Settlement Records &mdash; Ameen & Sons Corporate ERP</span>
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
                        @php $user = Auth::user(); @endphp

                        @if($user && $user->hasRole('super admin'))
                            <div class="col-md-3">
                                <label class="f-label"><i class="fas fa-building mr-1 text-muted"></i> Select Branch</label>
                                <select id="branch_id" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                                    <option value="">-- All Branches --</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name ?? $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="f-label"><i class="fas fa-store mr-1 text-muted"></i> Shop / Market Name</label>
                                <select id="shop_name" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                                    <option value="">-- All Shops / Markets --</option>
                                    @foreach($shops as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="f-label">From Date</label>
                                <input type="date" id="start_date" class="form-control form-control-sm" value="{{ $startDate }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                            </div>
                            <div class="col-md-2">
                                <label class="f-label">To Date</label>
                                <input type="date" id="end_date" class="form-control form-control-sm" value="{{ $endDate }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                    <i class="fas fa-filter mr-1"></i> Apply Filters
                                </button>
                            </div>
                        @else
                            <input type="hidden" id="branch_id" value="{{ $user->branch_id }}">
                            <div class="col-md-5">
                                <label class="f-label"><i class="fas fa-store mr-1 text-muted"></i> Select Shop / Market</label>
                                <select id="shop_name" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                                    <option value="">-- All Shops / Markets --</option>
                                    @foreach($shops as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="f-label">From Date</label>
                                <input type="date" id="start_date" class="form-control form-control-sm" value="{{ $startDate }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                            </div>
                            <div class="col-md-2">
                                <label class="f-label">To Date</label>
                                <input type="date" id="end_date" class="form-control form-control-sm" value="{{ $endDate }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                    <i class="fas fa-filter mr-1"></i> Apply Filters
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- LOADER --}}
            <div id="loader" class="text-center py-4" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2 small font-weight-bold">Analyzing market transactions...</p>
            </div>

            {{-- REPORT OUTPUT CONTAINER --}}
            <div id="reportBox" style="display:none;">
                
                {{-- 3. Summary Stats --}}
                <div class="rpt-kpi-grid">
                    <div class="rpt-kpi-card highlight">
                        <div>
                            <div class="rpt-kpi-label" style="color: #047857;">Total Net Purchases</div>
                            <div class="rpt-kpi-val emerald" id="stat_net">Rs. 0.00</div>
                        </div>
                        <div class="rpt-kpi-icon kpi-icon-emerald">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="rpt-kpi-card">
                        <div>
                            <div class="rpt-kpi-label">Total Amount Paid</div>
                            <div class="rpt-kpi-val" id="stat_paid">Rs. 0.00</div>
                        </div>
                        <div class="rpt-kpi-icon kpi-icon-blue">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                    <div class="rpt-kpi-card">
                        <div>
                            <div class="rpt-kpi-label">Outstanding Balance</div>
                            <div class="rpt-kpi-val crimson" id="stat_due">Rs. 0.00</div>
                        </div>
                        <div class="rpt-kpi-icon kpi-icon-red">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="reportContent">
                    <div class="card-body p-3">

                        {{-- PDF HEADER (HIDDEN ON SCREEN) --}}
                        <div id="pdfHeader" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #0f1f38; padding-bottom:10px;">
                            <h2 style="margin:0; color:#0f1f38; text-transform:uppercase; letter-spacing:1px;">Local Purchase & Market Report</h2>
                            <p style="margin:5px 0; font-size:13px; color:#333;" id="pdfFilterInfo">Direct Market Purchases & Spot Settlement Records</p>
                            <p style="margin:0; font-size:11.5px; color:#666;">Report Generated on: {{ date('d-M-Y H:i') }}</p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0" id="reportTable" style="font-size: 12.5px;">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 90px;">Date</th>
                                        <th style="width: 110px;">Invoice #</th>
                                        <th>Shop / Market</th>
                                        <th class="text-end" style="width: 130px;">Net Amount</th>
                                        <th class="text-end" style="width: 130px;">Paid</th>
                                        <th class="text-end" style="width: 130px;">Balance</th>
                                        <th class="text-center" style="width: 100px;">Status</th>
                                        <th class="text-center action-col" style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="reportTableBody" style="border-top: 0;">
                                    <!-- AJAX content -->
                                </tbody>
                                <tfoot id="reportFooter" style="display:none;">
                                    <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                                        <td colspan="3" class="text-end font-weight-bold" style="font-family: sans-serif;">Grand Totals:</td>
                                        <td class="text-end font-weight-bold text-dark" id="footerTotalNet">Rs. 0.00</td>
                                        <td class="text-end text-success font-weight-bold" id="footerTotalPaid">Rs. 0.00</td>
                                        <td class="text-end text-danger font-weight-bold" id="footerTotalDue">Rs. 0.00</td>
                                        <td colspan="2" class="action-col"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

{{-- PAYMENT MODAL --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:12px; overflow:hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #0f1f38 0%, #1e3a5f 100%); color: #fff; padding: 14px 20px;">
                <h5 class="modal-title font-weight-bold text-white mb-0" id="pay_title" style="font-size: 15px;">
                    <i class="fas fa-money-bill-wave mr-2" style="color: var(--coa-gold);"></i> Make Payment
                </h5>
                <button type="button" class="close text-white" onclick="closePaymentModal()" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" style="opacity: 0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info py-2 px-3 border-0 small mb-3" style="background:#ecfdf5; color:#065f46; border: 1px solid #a7f3d0; border-radius: 6px;">
                    <i class="fas fa-info-circle mr-1 text-success"></i> 
                    Outstanding Balance: <strong class="font-monospace" style="font-size: 13px;">Rs. <span id="pay_due_display">0</span></strong>
                </div>
                
                <input type="hidden" id="pay_purchase_id">
                
                {{-- 1. ACCOUNT HEAD DROPDOWN --}}
                <div class="mb-3">
                    <label class="f-label"><i class="fas fa-sitemap mr-1 text-muted"></i> Select Account Head <span class="text-danger">*</span></label>
                    <select id="pay_head_id" class="form-control fi select2">
                        <option value="">-- Select Account Head --</option>
                        @foreach($accountHeads as $h)
                            <option value="{{ $h->id }}">{{ $h->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. ACCOUNT DROPDOWN (CASCADING) --}}
                <div class="mb-3">
                    <label class="f-label"><i class="fas fa-wallet mr-1 text-muted"></i> Select Payment Account <span class="text-danger">*</span></label>
                    <select id="pay_account_id" class="form-control fi select2" disabled>
                        <option value="">-- Choose Head First --</option>
                    </select>
                    
                    {{-- Account Balance Badge --}}
                    <div id="pay_balance_badge_container" class="mt-2" style="display: none;">
                        <span id="pay_balance_badge" class="badge bg-light text-dark border font-monospace" style="font-size: 11.5px; padding: 6px 10px; border-radius: 6px; display: inline-flex; align-items: center; gap: 5px;">
                            <i class="fas fa-wallet text-success"></i> Available Balance: <strong id="pay_acc_balance_val" style="font-size: 12px;">Rs. 0.00</strong>
                        </span>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="f-label"><i class="fas fa-coins mr-1 text-muted"></i> Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0" style="font-size: 12px; font-weight: bold;">Rs.</span>
                            <input type="number" id="pay_amount" class="form-control fi border-start-0 font-weight-bold" style="font-family: monospace;" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="f-label"><i class="fas fa-calendar-alt mr-1 text-muted"></i> Payment Date</label>
                        <input type="date" id="pay_date" class="form-control fi" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="mb-0">
                    <label class="f-label"><i class="fas fa-sticky-note mr-1 text-muted"></i> Note / Cheque Ref (Optional)</label>
                    <textarea id="pay_note" class="form-control fi" rows="2" placeholder="e.g. Paid via Online Transfer / Cash / Cheque #123"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-top pt-2 pb-2">
                <button type="button" class="btn btn-sm btn-secondary font-weight-bold" onclick="closePaymentModal()" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                <button type="button" id="btnConfirmPayment" class="btn btn-sm btn-primary font-weight-bold px-4" style="background:#0f1f38; border-color:#0f1f38;">
                    <i class="fas fa-check-circle mr-1 text-success"></i> Confirm Payment
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    @media print {
        #filterForm, .btn, .rpt-header-bar, .rpt-kpi-grid, .action-col { display: none !important; }
        .main-content { padding: 0 !important; margin-top:0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        #reportContent { overflow: visible !important; }
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
$(document).ready(function() {
    
    function fmt(v) {
        return parseFloat(v || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Dynamic Shop Loading on Branch Change
    $('#branch_id').on('change', function() {
        const bId = $(this).val();
        const shopSelect = $('#shop_name');
        
        shopSelect.prop('disabled', true).html('<option value="">Loading shops...</option>');
        
        $.get("{{ route('report.local_purchase.shops') }}", { branch_id: bId })
        .done(function(shops) {
            shopSelect.empty().append('<option value="">-- All Shops / Markets --</option>');
            if (shops && shops.length > 0) {
                shops.forEach(function(s) {
                    shopSelect.append(`<option value="${s}">${s}</option>`);
                });
            }
            shopSelect.prop('disabled', false);
            $('#btnSearch').trigger('click');
        })
        .fail(function() {
            shopSelect.empty().append('<option value="">-- All Shops / Markets --</option>').prop('disabled', false);
            $('#btnSearch').trigger('click');
        });
    });

    // Auto-search on Shop selection change
    $('#shop_name').on('change', function() {
        $('#btnSearch').trigger('click');
    });

    $('#btnSearch').click(function() {
        const data = {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            branch_id: $('#branch_id').val(),
            shop_name: $('#shop_name').val()
        };

        if (!data.start_date || !data.end_date) {
            Swal.fire('Error', 'Please select date range', 'error');
            return;
        }

        $('#loader').show();
        $('#reportBox').hide();

        $.get("{{ route('report.local_purchase.fetch') }}", data)
        .done(function(res) {
            $('#loader').hide();
            renderReport(res);
        })
        .fail(function() {
            $('#loader').hide();
            Swal.fire('Error', 'Failed to fetch report data', 'error');
        });
    });

    function renderReport(res) {
        const tbody = $('#reportTableBody');
        tbody.empty();

        if (!res.data || res.data.length === 0) {
            tbody.append('<tr><td colspan="8" class="text-center py-5 text-muted">No local purchases found for the selected criteria.</td></tr>');
            $('#reportFooter').hide();
        } else {
            res.data.forEach(p => {
                let statusClass = 'badge-due';
                if (p.status === 'Paid') statusClass = 'badge-paid';
                else if (p.status === 'Partial') statusClass = 'badge-partial';

                tbody.append(`
                    <tr>
                        <td class="text-center text-muted" style="white-space:nowrap; font-size:11.5px;">${p.date}</td>
                        <td><span class="font-monospace font-weight-bold" style="color:var(--coa-navy); font-size:12px;">${p.invoice_no}</span></td>
                        <td>
                            <div class="font-weight-bold text-dark" style="font-size:12.5px;">${p.shop_name}</div>
                            <small class="text-muted"><i class="fas fa-building mr-1"></i>${p.branch}</small>
                        </td>
                        <td class="text-end font-monospace font-weight-bold" style="font-size:12.5px;">Rs. ${fmt(p.net_amount)}</td>
                        <td class="text-end font-monospace font-weight-bold text-success" style="font-size:12.5px;">Rs. ${fmt(p.paid_amount)}</td>
                        <td class="text-end font-monospace font-weight-bold ${parseFloat(p.due_amount) > 0 ? 'text-danger' : 'text-muted'}" style="font-size:12.5px;">Rs. ${fmt(p.due_amount)}</td>
                        <td class="text-center">
                            <span class="status-badge ${statusClass}">${p.status}</span>
                        </td>
                        <td class="text-center action-col">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <a href="{{ url('purchase') }}/${p.id}/invoice" target="_blank" class="btn btn-sm btn-light border" title="View / Print Receipt" style="padding: 2px 6px;">
                                    <i class="fas fa-file-invoice text-primary"></i>
                                </a>
                                ${parseFloat(p.due_amount) > 0 ? `
                                    <button type="button" class="btn btn-sm btn-success btn-pay" 
                                        data-id="${p.id}" 
                                        data-invoice="${p.invoice_no}" 
                                        data-shop="${p.shop_name}" 
                                        data-due="${p.due_amount}"
                                        data-branch-id="${p.branch_id || ''}"
                                        title="Make Payment" style="padding: 2px 6px; font-size: 11px;">
                                        <i class="fas fa-money-bill-wave mr-1"></i> Pay
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `);
            });

            // Footer Totals
            $('#footerTotalNet').text('Rs. ' + fmt(res.summary.total_net));
            $('#footerTotalPaid').text('Rs. ' + fmt(res.summary.total_paid));
            $('#footerTotalDue').text('Rs. ' + fmt(res.summary.total_due));
            $('#reportFooter').show();
        }

        // Summary Stats
        $('#stat_net').text('Rs. ' + fmt(res.summary.total_net));
        $('#stat_paid').text('Rs. ' + fmt(res.summary.total_paid));
        $('#stat_due').text('Rs. ' + fmt(res.summary.total_due));

        // Update PDF Header Filter Text
        let filterParts = [];
        let branchText = $('#branch_id option:selected').text();
        if ($('#branch_id').val()) {
            filterParts.push('Branch: ' + branchText.trim());
        }
        if ($('#shop_name').val()) {
            filterParts.push('Shop: ' + $('#shop_name').val().trim());
        }
        let dateText = $('#start_date').val() + ' to ' + $('#end_date').val();
        filterParts.push('Period: ' + dateText);
        $('#pdfFilterInfo').text(filterParts.join(' | '));

        $('#reportBox').fadeIn();
    }

    // --- CASCADING PAYMENT MODAL LOGIC ---
    window.ALL_HEADS = @json($accountHeads);
    window.ALL_ACCOUNTS = @json($bankAccounts);
    let activePaymentBranchId = null;

    window.openPaymentModal = function() {
        $('#paymentModal').modal('show');
    };

    window.closePaymentModal = function() {
        $('#paymentModal').modal('hide');
        setTimeout(function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
        }, 150);
    };

    $(document).on('click', '.btn-pay', function() {
        const d = $(this).data();
        activePaymentBranchId = d.branchId || null;

        $('#pay_purchase_id').val(d.id);
        $('#pay_title').html(`<i class="fas fa-money-bill-wave mr-2" style="color: var(--coa-gold);"></i> Pay to: ${d.shop} (${d.invoice})`);
        $('#pay_amount').val(d.due).attr('max', d.due);
        $('#pay_due_display').text(fmt(d.due));
        
        // Filter Heads by branch accounts (fallback to all accounts if branch has none)
        let branchAccounts = window.ALL_ACCOUNTS.filter(acc => String(acc.branch_id) === String(activePaymentBranchId));
        if (branchAccounts.length === 0) {
            branchAccounts = window.ALL_ACCOUNTS;
        }

        const headIdsWithAccounts = [...new Set(branchAccounts.map(a => a.head_id))];

        let headOptions = '<option value="">-- Select Account Head --</option>';
        window.ALL_HEADS.forEach(h => {
            if (headIdsWithAccounts.length === 0 || headIdsWithAccounts.includes(h.id)) {
                headOptions += `<option value="${h.id}">${h.name}</option>`;
            }
        });
        $('#pay_head_id').html(headOptions).val('');

        $('#pay_account_id').html('<option value="">-- Choose Head First --</option>').prop('disabled', true);
        $('#pay_balance_badge_container').hide();

        openPaymentModal();
    });

    // When Account Head changes -> populate Accounts
    $('#pay_head_id').on('change', function() {
        const headId = $(this).val();
        const $accSelect = $('#pay_account_id');
        $('#pay_balance_badge_container').hide();

        if (!headId) {
            $accSelect.html('<option value="">-- Choose Head First --</option>').prop('disabled', true);
            return;
        }

        let branchAccounts = window.ALL_ACCOUNTS.filter(acc => String(acc.branch_id) === String(activePaymentBranchId));
        if (branchAccounts.length === 0) {
            branchAccounts = window.ALL_ACCOUNTS;
        }

        const filteredAccounts = branchAccounts.filter(acc => String(acc.head_id) === String(headId));

        let accOptions = '<option value="">-- Select Account --</option>';
        filteredAccounts.forEach(acc => {
            const bal = parseFloat(acc.opening_balance) || 0;
            accOptions += `<option value="${acc.id}" data-balance="${bal}">${acc.title} (${acc.account_code || 'Acc #' + acc.id})</option>`;
        });

        $accSelect.html(accOptions).prop('disabled', false);

        // If only 1 account in this head, auto-select it
        if (filteredAccounts.length === 1) {
            $accSelect.val(filteredAccounts[0].id).trigger('change');
        }
    });

    // When Account changes -> show balance badge
    $('#pay_account_id').on('change', function() {
        const accId = $(this).val();
        if (!accId) {
            $('#pay_balance_badge_container').hide();
            return;
        }

        const acc = window.ALL_ACCOUNTS.find(a => String(a.id) === String(accId));
        if (acc) {
            const bal = parseFloat(acc.opening_balance) || 0;
            const isNegative = bal < 0;
            $('#pay_acc_balance_val').text('Rs. ' + fmt(bal));
            
            const badge = $('#pay_balance_badge');
            if (isNegative) {
                badge.removeClass('border-success text-success').addClass('border-danger text-danger');
            } else {
                badge.removeClass('border-danger text-danger').addClass('border-success text-success');
            }
            $('#pay_balance_badge_container').slideDown(150);
        } else {
            $('#pay_balance_badge_container').hide();
        }
    });

    $('#btnConfirmPayment').click(function() {
        const btn = $(this);
        const data = {
            _token: "{{ csrf_token() }}",
            purchase_id: $('#pay_purchase_id').val(),
            account_id: $('#pay_account_id').val(),
            amount: $('#pay_amount').val(),
            date: $('#pay_date').val(),
            note: $('#pay_note').val()
        };

        if (!data.account_id || !data.amount || parseFloat(data.amount) <= 0) {
            Swal.fire('Error', 'Please select head, account, and enter a valid amount', 'error');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

        $.post("{{ route('report.local_purchase.pay') }}", data)
        .done(function(res) {
            btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1 text-success"></i> Confirm Payment');
            closePaymentModal();
            
            Swal.fire({
                icon: 'success',
                title: 'Paid!',
                text: res.success,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                $('#btnSearch').trigger('click');
            });
        })
        .fail(function(xhr) {
            btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1 text-success"></i> Confirm Payment');
            let msg = xhr.responseJSON?.error || 'Payment failed';
            Swal.fire('Error', msg, 'error');
        });
    });

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
        $('.action-col').hide();

        var opt = {
            margin:       0.3,
            filename:     'Local_Purchase_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
            $('#pdfHeader').hide();
            $('.action-col').show();
            var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
            
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'Local Purchase Report',
                    text: 'Please find the attached Local Purchase & Market Report.',
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
            
            var msg = "*Local Purchase & Market Report*\nPlease find the attached PDF document.";
            var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
            window.open(waUrl, '_blank');
        });
    }

    /* ---------- Export Options & PDF ---------- */
    window.showExportOptions = function() {
        Swal.fire({
            title: 'Export Local Purchase Report',
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
            didOpen: () => {
                Swal.showLoading();
            }
        });

        var element = document.getElementById('reportContent');
        $('#pdfHeader').show();
        $('.action-col').hide();

        var opt = {
            margin:       0.3,
            filename:     'Local_Purchase_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).save().then(function() {
            $('#pdfHeader').hide();
            $('.action-col').show();
            Swal.close();
        });
    };

    /* ---------- CSV Export ---------- */
    window.exportCSV = function () {
        var rows = [['Date', 'Invoice #', 'Shop / Market Name', 'Branch', 'Net Amount', 'Paid Amount', 'Due Balance', 'Status']];
        
        $('#reportTableBody tr').each(function () {
            var cells = [];
            var $tr = $(this);
            var date = $tr.find('td:nth-child(1)').text().trim();
            var invoice = $tr.find('td:nth-child(2)').text().trim();
            var shop = $tr.find('td:nth-child(3) .font-weight-bold').text().trim();
            var branch = $tr.find('td:nth-child(3) small').text().trim();
            var net = $tr.find('td:nth-child(4)').text().trim().replace(/Rs\./g, '').replace(/,/g, '').trim();
            var paid = $tr.find('td:nth-child(5)').text().trim().replace(/Rs\./g, '').replace(/,/g, '').trim();
            var due = $tr.find('td:nth-child(6)').text().trim().replace(/Rs\./g, '').replace(/,/g, '').trim();
            var status = $tr.find('td:nth-child(7)').text().trim();

            if (date) {
                rows.push([
                    '"' + date + '"',
                    '"' + invoice + '"',
                    '"' + shop + '"',
                    '"' + branch + '"',
                    '"' + net + '"',
                    '"' + paid + '"',
                    '"' + due + '"',
                    '"' + status + '"'
                ]);
            }
        });

        // Add Summary/Footer Row
        rows.push([
            '"Grand Totals"',
            '""',
            '""',
            '""',
            '"' + $('#footerTotalNet').text().replace(/Rs\./g, '').replace(/,/g, '').trim() + '"',
            '"' + $('#footerTotalPaid').text().replace(/Rs\./g, '').replace(/,/g, '').trim() + '"',
            '"' + $('#footerTotalDue').text().replace(/Rs\./g, '').replace(/,/g, '').trim() + '"',
            '""'
        ]);

        var csv = rows.map(function(r) { return r.join(','); }).join('\n');
        var blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'Local_Purchase_Report_' + new Date().toISOString().slice(0,10) + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    };

    // Auto-search on start
    $('#btnSearch').trigger('click');
});
</script>
@endsection
