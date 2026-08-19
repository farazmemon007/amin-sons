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

    #ledgerTable th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 6px;
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
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Customer Account Ledger</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-file-invoice-dollar mr-1" style="color: var(--coa-gold);"></i> Complete Transaction History with Running Balance &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2" id="printBtnWrap" style="display:none;">
                    <button id="waShareBtn" onclick="shareWhatsApp()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(37, 211, 102, 0.2); border-color: #25D366; color: #25D366;">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </button>
                    <button id="toggleDetailsBtn" onclick="toggleInvoiceDetails()" class="btn btn-sm btn-light font-weight-bold border">
                        <i class="fas fa-eye-slash mr-1"></i> Hide Details
                    </button>
                    <button onclick="window.print()" class="btn btn-sm btn-outline-light font-weight-bold">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                    <button onclick="showExportOptions()" class="btn btn-sm btn-light font-weight-bold text-dark border">
                        <i class="fas fa-download mr-1 text-primary"></i> Export
                    </button>
                </div>
            </div>

            {{-- 2. Filter Card --}}
            <div class="card shadow-sm mb-3 border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form id="ledgerForm" class="row g-2 align-items-end mb-0">
                        @php $user = Auth::user(); @endphp

                        @if($user && $user->hasRole('super admin'))
                            <div class="col-md-3">
                                <label class="f-label">Select Branch</label>
                                <select id="branch_id" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;" onchange="loadCustomersForBranch(this.value)">
                                    <option value="">-- Select Branch --</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name ?? $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="f-label">
                                    Customer <small id="customerCountHint" class="text-primary ml-1"></small>
                                </label>
                                <select id="customer_id" class="form-control form-control-sm select2" style="width: 100%;">
                                    <option value="">-- Select Branch First --</option>
                                </select>
                            </div>
                        @else
                            <div class="col-md-4">
                                <label class="f-label">
                                    Customer
                                    <small class="text-muted ml-1">({{ count($customers) }} records)</small>
                                </label>
                                <select id="customer_id" class="form-control form-control-sm select2" style="width: 100%;">
                                    <option value="">-- Select Customer --</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">
                                            {{ $c->customer_name }}
                                            @if($c->customer_type) ({{ ucfirst($c->customer_type) }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-2">
                            <label class="f-label">From Date</label>
                            <input type="date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">To Date</label>
                            <input type="date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch"
                                onclick="generateLedger()"
                                class="btn btn-sm btn-primary w-100 font-weight-bold"
                                style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-file-invoice mr-1"></i> Generate Ledger
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- LOADER --}}
            <div id="loader" class="text-center py-4" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2 small font-weight-bold">Loading ledger data&hellip;</p>
            </div>

            {{-- LEDGER OUTPUT --}}
            <div id="ledgerBox" style="display:none;">

                {{-- 3. Customer Summary Card --}}
                <div class="card shadow-sm mb-3 border-0" style="border-radius: 9px; border: 1.5px solid #cbd5e1 !important; background: #ffffff;">
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-3">
                                <div class="f-label" style="margin-bottom: 2px;">Customer</div>
                                <div id="cust_name" style="color: var(--coa-navy); font-size: 15px; font-weight: 800;">-</div>
                            </div>
                            <div class="col-md-2">
                                <div class="f-label" style="margin-bottom: 2px;">Type</div>
                                <span id="cust_type" class="badge badge-info text-dark" style="font-size: 11px; padding: 4px 8px;">-</span>
                            </div>
                            <div class="col-md-2">
                                <div class="f-label" style="margin-bottom: 2px;">Mobile</div>
                                <div id="cust_mobile" class="font-weight-bold font-monospace text-dark">-</div>
                            </div>
                            <div class="col-md-3">
                                <div class="f-label" style="margin-bottom: 2px;">Address</div>
                                <div id="cust_addr" class="text-muted small text-truncate">-</div>
                            </div>
                            <div class="col-md-2">
                                <div class="f-label" style="margin-bottom: 2px;">Credit Limit</div>
                                <div id="cust_limit" class="font-weight-bold font-monospace" style="color: #ea580c;">-</div>
                            </div>
                        </div>
                        <hr class="my-2" style="border-top: 1px dashed #cbd5e1;">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <span class="f-label d-inline mr-1">Period: </span>
                                <span id="cust_period" class="font-weight-bold" style="color: var(--coa-navy);">-</span>
                            </div>
                            <div class="col-md-8 text-right font-monospace" style="font-size: 13px;">
                                <span class="mr-3"><span class="text-muted">Opening: </span><span id="s_open" class="font-weight-bold text-dark">-</span></span>
                                <span class="mr-3"><span class="text-muted">Total Debit: </span><span id="s_debit" class="font-weight-bold text-danger">-</span></span>
                                <span class="mr-3"><span class="text-muted">Total Credit: </span><span id="s_credit" class="font-weight-bold text-success">-</span></span>
                                <span><span class="text-muted">Closing: </span><span id="s_close" class="font-weight-bold text-primary" style="font-size: 15px;">-</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. LEDGER TABLE --}}
                <div id="printArea">
                    <div class="table-responsive" style="border: 1px solid var(--coa-border); border-radius: 9px; overflow: hidden;">
                        <table id="ledgerTable" class="table table-bordered mb-0" style="font-size: 12.5px; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 75px;">Date</th>
                                    <th class="text-center" style="width: 100px;">Tran. NO</th>
                                    <th class="text-center" style="width: 55px;">M</th>
                                    <th class="text-center" style="width: 75px;">DC No</th>
                                    <th class="text-center" style="width: 75px;">Gate Pass</th>
                                    <th>Description / Item</th>
                                    <th class="text-right" style="width: 60px;">Qty</th>
                                    <th class="text-right" style="width: 75px;">Rate</th>
                                    <th class="text-right"  style="width:90px;padding:9px 5px;">Debit</th>
                                    <th class="text-right"  style="width:90px;padding:9px 5px;">Credit</th>
                                    <th class="text-right"  style="width:105px;padding:9px 5px;">Total</th>
                                </tr>
                            </thead>
                            <tbody id="ledgerBody"></tbody>
                            <tfoot id="ledgerFooter"></tfoot>
                        </table>
                    </div>
                </div>

            </div>{{-- /ledgerBox --}}

        </div>
    </div>
</div>

<!-- Scripts for Export options -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
.lbl  { font-size:11px;color:#666;font-weight:600;text-transform:uppercase;letter-spacing:.4px; }
.val  { font-size:13px;font-weight:600;color:#1a1a2e; }

/* Row colors */
tr.r-open    td { background:#d4edff !important; font-weight:700; border-color:#aad4f5 !important; }
tr.r-sale    td { background:#fff8e1 !important; font-weight:600; border-color:#ffe082 !important; }
tr.r-item    td { background:#fffdf0 !important; font-size:12px;  color:#555; border-color:#f0e6b0 !important; }
tr.r-receipt td { background:#e8f5e9 !important; border-color:#a5d6a7 !important; }
tr.r-pv      td { background:#f3e5f5 !important; border-color:#ce93d8 !important; }
tr.r-return  td { background:#fce4ec !important; border-color:#f48fb1 !important; }
tr.r-discount td { background:#fff3e0 !important; border-color:#ffb74d !important; font-style:italic; }
tr.r-total   td { background:#e9ecef !important; font-weight:700; border-top:2px solid #1a1a2e !important; font-size:13px; }
tr.r-close   td { background:#1a1a2e !important; color:#fff !important; font-weight:700; font-size:14px; }
tr.r-grand   td { background:#0a3060 !important; color:#fff !important; font-weight:700; font-size:13px; }

/* Premium Input Styling */
.fi-premium {
    border-radius: 8px !important;
    border: 1.5px solid #e3e6f0 !important;
    background-color: #ffffff !important;
    padding: 0.6rem 0.75rem !important;
    font-size: 0.9rem !important;
    transition: all 0.2s ease-in-out;
}
.fi-premium:focus {
    border-color: #0066cc !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.1) !important;
    background-color: #fff !important;
}
.select2-container--default .select2-selection--single {
    border: 1.5px solid #e3e6f0 !important;
    border-radius: 8px !important;
    height: 45px !important;
    padding-top: 8px !important;
}

/* Balance colors */
.b-dr   { color:#c62828; font-weight:700; }
.b-cr   { color:#2e7d32; font-weight:700; }
.b-zero { color:#0066cc; font-weight:700; }
tr.r-close .b-dr { color:#ff8a80; }
tr.r-close .b-cr { color:#69f0ae; }
tr.r-grand .b-dr { color:#ff8a80; }
tr.r-grand .b-cr { color:#69f0ae; }

#ledgerTable td,
#ledgerTable th { vertical-align:middle; padding:5px 6px; }
#ledgerTable tbody tr:hover td { filter:brightness(.97); }

/* Toggle Details CSS */
#ledgerTable.hide-details .detail-row { display: none !important; }

@media print {
    .card, button, form, #printBtnWrap { display:none !important; }
    #ledgerBox, #printArea { display:block !important; }
    /* Print will respect the .hide-details class because it applies display:none!important */
}
</style>

<script>

    /* ═══════════════════════════════════════════════════
     * INITIALIZE SELECT2 (Safely waiting for jQuery to load)
     * ═══════════════════════════════════════════════════ */
    window.addEventListener('load', function() {
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            $('#customer_id').select2({
                width: '100%',
                placeholder: '-- Select Customer --',
                allowClear: true
            });
        }
    });

    /* ═══════════════════════════════════════════════════
     * GLOBAL: Load customers by branch (called from onchange)
     * ═══════════════════════════════════════════════════ */
    function loadCustomersForBranch(bid) {
        var $c = $('#customer_id');
        var $hint = $('#customerCountHint');

        // Safely destroy Select2 if it exists, then reset options
        if ($c.hasClass('select2-hidden-accessible')) {
            $c.select2('destroy');
        }
        $c.html('<option value="">Loading customers\u2026</option>');
        $c.prop('disabled', true);
        if ($hint.length) $hint.text('');

        if (!bid) {
            $c.html('<option value="">-- Select Branch First --</option>');
            $c.prop('disabled', false);
            $c.select2({ width: '100%' });
            return;
        }

        // Use jQuery AJAX since it is guaranteed to be loaded when this is triggered
        if (typeof jQuery === 'undefined') {
            console.error("jQuery is not loaded yet.");
            return;
        }

        $.ajax({
            url: "{{ route('report.customers.byBranch') }}",
            type: 'GET',
            data: { branch_id: bid },
            dataType: 'json',
            success: function(list) {
                $c.html('<option value="">-- Select Customer --</option>');
                if (list.length === 0) {
                    $c.append('<option disabled>No customers found for this branch</option>');
                } else {
                    $.each(list, function(i, c) {
                        var label = c.customer_name + (c.customer_type ? ' (' + c.customer_type + ')' : '');
                        $c.append($('<option>', { value: c.id, text: label }));
                    });
                    if ($hint.length) $hint.text(list.length + ' customer(s)');
                }
                $c.prop('disabled', false);
                $c.select2({ width: '100%' });
            },
            error: function(xhr) {
                $c.html('<option value="">-- Error loading customers --</option>');
                $c.prop('disabled', false);
                $c.select2({ width: '100%' });
                console.error('Failed to load customers. Status:', xhr.status, xhr.responseText);
            }
        });
    }

    /* ═══════════════════════════════════════════════════
     * GLOBAL: Generate customer ledger report
     * ═══════════════════════════════════════════════════ */
    function generateLedger() {
        var cid   = document.getElementById('customer_id').value;
        var start = document.getElementById('start_date').value;
        var end   = document.getElementById('end_date').value;

        if (!cid || !start || !end) {
            alert('Please select a customer and choose the date range.');
            return;
        }

        document.getElementById('loader').style.display = 'block';
        document.getElementById('ledgerBox').style.display = 'none';
        var pwrap = document.getElementById('printBtnWrap');
        if (pwrap) pwrap.style.display = 'none';

        var url = "{{ route('report.customer.ledger.fetch.new') }}?customer_id=" + encodeURIComponent(cid)
                + '&start_date=' + encodeURIComponent(start)
                + '&end_date='   + encodeURIComponent(end);

        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function() {
            document.getElementById('loader').style.display = 'none';
            if (xhr.status === 200) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.error) { alert(res.error); return; }
                    renderLedger(res, start, end);
                } catch(e) {
                    alert('Parse error. Check console.');
                    console.error('JSON parse error:', e, xhr.responseText);
                }
            } else {
                try {
                    var err = JSON.parse(xhr.responseText);
                    alert(err.error || 'Server error ' + xhr.status);
                } catch(e) {
                    alert('Server error ' + xhr.status + '. Check console.');
                }
                console.error('Ledger fetch failed:', xhr.status, xhr.responseText);
            }
        };

        xhr.onerror = function() {
            document.getElementById('loader').style.display = 'none';
            alert('Network error. Please try again.');
        };

        xhr.send();
    }

    /* ---------- helpers ---------- */
    function n(v) { return parseFloat(v) || 0; }

    function fmt(v) {
        v = n(v);
        return v.toLocaleString('en-PK', {minimumFractionDigits:0, maximumFractionDigits:0});
    }

    function balHtml(b) {
        b = n(b);
        var cls   = b > 0 ? 'b-dr' : (b < 0 ? 'b-cr' : 'b-zero');
        var label = b > 0 ? ' Dr'  : (b < 0 ? ' Cr'  : '');
        return '<span class="' + cls + '">' + fmt(Math.abs(b)) + label + '</span>';
    }

    function dash() { return '<span style="color:#ccc;">&#8212;</span>'; }

    /* 11 visible columns only */
    function td(txt, align, attrs) {
        align = align || 'center';
        attrs = attrs || '';
        var val = (txt !== null && txt !== undefined && txt !== '') ? txt : dash();
        return '<td style="text-align:' + align + ';border:1px solid #ddd;" ' + attrs + '>' + val + '</td>';
    }

    /* ---------- Live customer search/filter removed (using Select2 instead) ---------- */

    /* ---------- render ---------- */
    function renderLedger(res, start, end) {
        var c = res.customer;

        /* customer header */
        $('#cust_name').text(c.name);
        $('#cust_type').text(c.type ? c.type.toUpperCase() : '-');
        $('#cust_mobile').text(c.mobile || '-');
        $('#cust_addr').text(c.address || '-');
        $('#cust_limit').text(c.credit_limit ? 'Rs. ' + fmt(c.credit_limit) : 'Unlimited');
        $('#cust_period').text(start + '  to  ' + end);
        $('#s_open').html(balHtml(res.opening_balance));
        $('#s_debit').text('Rs. ' + fmt(res.total_debit));
        $('#s_credit').text('Rs. ' + fmt(res.total_credit));
        $('#s_close').html(balHtml(res.closing_balance));

        var ob          = n(res.opening_balance);
        var txns        = res.transactions || [];
        var grandDr     = 0;
        var grandCr     = 0;
        var grandQty    = 0;   /* total qty across all sale items */
        var curInvDebit      = 0;   /* current invoice debit  */
        var curInvCredit     = 0;   /* current invoice credit */
        var curInvLineTotal  = 0;   /* current invoice items amount sum */

        /* ── body HTML ── */
        var bodyHtml = '';

        /* Pre-scan: build invQtyMap { invoice_no -> total_qty }
           Each sale_total immediately follows its sale_header + sale_items */
        var invQtyMap = {};
        var _lastInvNo = null;
        $.each(txns, function (i, t) {
            if (t.row_type === 'sale_header')  { _lastInvNo = t.vno; }
            else if (t.row_type === 'sale_total' && _lastInvNo) {
                invQtyMap[_lastInvNo] = n(t.total_qty);
                _lastInvNo = null;
            }
        });

        /* Opening Balance Row — 11 cols */
        bodyHtml += '<tr class="r-open">';
        bodyHtml += '<td colspan="6" style="text-align:left;border:1px solid #aad4f5;padding:6px 8px;"><strong>Opening Balance</strong></td>';
        bodyHtml += td('', 'right');  /* qty */
        bodyHtml += td('', 'right');  /* rate */
        bodyHtml += td('', 'right');  /* debit */
        bodyHtml += td('', 'right');  /* credit */
        bodyHtml += td(balHtml(ob), 'right');
        bodyHtml += '</tr>';

        /* Transaction Rows */
        $.each(txns, function (i, t) {
            var rc = 'r-txn';
            if      (t.row_type === 'sale_header')      rc = 'r-sale';
            else if (t.row_type === 'sale_item')        rc = 'r-item';
            else if (t.row_type === 'receipt')          rc = 'r-receipt';
            else if (t.row_type === 'payment_voucher')  rc = 'r-pv';
            else if (t.row_type === 'return')           rc = 'r-return';
            else if (t.row_type === 'discount')         rc = 'r-discount';

            /* ── SALE HEADER ── */
            if (t.row_type === 'sale_header') {
                var debit  = n(t.debit);
                var credit = n(t.credit);
                if (debit  > 0) grandDr += debit;
                if (credit > 0) grandCr += credit;
                curInvDebit      = debit;
                curInvCredit     = credit;
                curInvLineTotal  = 0;   /* reset per invoice */

                var dcH = (t.dc_no && t.dc_no !== '-') ? '<span style="color:#0044aa;font-weight:600;">' + t.dc_no + '</span>' : '';
                var gpH = (t.gp_no && t.gp_no !== '-') ? '<span style="color:#006633;font-weight:600;">' + t.gp_no + '</span>' : '';

                /* Invoice summary row */
                bodyHtml += '<tr class="' + rc + '">';
                bodyHtml += td(t.date || '', 'center');
                bodyHtml += td(t.vno ? '<strong>' + t.vno + '</strong>' : '', 'center');
                bodyHtml += td(t.bill && t.bill !== '-' ? t.bill : '', 'center');
                bodyHtml += td(dcH, 'center');
                bodyHtml += td(gpH, 'center');
                bodyHtml += td('<strong>' + (t.description || 'SALE') + '</strong>', 'left');
                /* Qty: show invoice total qty from pre-scan map */
                var hdrQty = invQtyMap[t.vno] || 0;
                bodyHtml += td(hdrQty > 0 ? '<strong style="color:#1a1a2e;">' + fmt(hdrQty) + '</strong> <small style="font-size:10px;color:#777;">pcs</small>' : '', 'right');  /* qty */
                bodyHtml += td('', 'right');  /* rate */
                bodyHtml += td(debit  > 0 ? '<strong style="color:#c62828;">' + fmt(debit)  + '</strong>' : '', 'right');
                bodyHtml += td(credit > 0 ? '<strong style="color:#2e7d32;">' + fmt(credit) + '</strong>' : '', 'right');
                bodyHtml += td(t.balance !== null && t.balance !== undefined ? balHtml(t.balance) : '', 'right');
                bodyHtml += '</tr>';

                /* Sub-header strip for item columns */
                bodyHtml += '<tr class="detail-row">';
                bodyHtml += '<td colspan="5" style="background:#dce6f7;border:1px solid #c5cfe0;padding:0;"></td>';
                bodyHtml += '<td style="background:#dce6f7;border:1px solid #c5cfe0;font-size:10px;font-weight:700;color:#1a2e5e;padding:3px 6px;">&#9658; Item / Product</td>';
                bodyHtml += '<td style="background:#dce6f7;border:1px solid #c5cfe0;font-size:10px;font-weight:700;color:#1a2e5e;padding:3px 5px;text-align:right;">Qty</td>';
                bodyHtml += '<td style="background:#dce6f7;border:1px solid #c5cfe0;font-size:10px;font-weight:700;color:#1a2e5e;padding:3px 5px;text-align:right;">Rate</td>';
                bodyHtml += '<td colspan="2" style="background:#e8f5e9;border:1px solid #a5d6a7;font-size:10px;font-weight:700;color:#1b5e20;padding:3px 5px;text-align:right;">Amount</td>';
                bodyHtml += '<td style="background:#dce6f7;border:1px solid #c5cfe0;padding:0;"></td>';
                bodyHtml += '</tr>';
                return;
            }

            /* ── SALE ITEM ── */
            if (t.row_type === 'sale_item') {
                var disc = n(t.item_discount);
                var amt  = n(t.line_amount);
                curInvLineTotal += amt;
                grandQty        += n(t.qty);   /* accumulate grand total qty */

                // Item name + discount below (if any) + optional product name
                var itemNameHtml = '<span style="padding-left:14px;color:#1a1a2e;font-weight:700;">' + (t.item_name || '-') + '</span>';
                if (disc > 0) {
                    itemNameHtml += '<br><span style="padding-left:22px;color:#e65100;font-size:11px;font-weight:600;">&#8627; Disc: &minus;' + fmt(disc) + '</span>';
                }
                if (t.product_name && t.product_name !== t.item_name) {
                    itemNameHtml += '<br><span style="padding-left:22px;color:#888;font-size:11px;">' + t.product_name + '</span>';
                }

                bodyHtml += '<tr class="' + rc + ' detail-row">';
                bodyHtml += '<td colspan="5" style="background:#fafcff;border:1px solid #e8e8e8;"></td>';
                bodyHtml += '<td style="background:#fafcff;border:1px solid #e8e8e8;padding:5px 6px;">' + itemNameHtml + '</td>';
                bodyHtml += '<td style="background:#fafcff;border:1px solid #e8e8e8;padding:5px;text-align:right;font-weight:600;">' + (t.qty !== null && t.qty !== undefined ? fmt(t.qty) : dash()) + '</td>';
                bodyHtml += '<td style="background:#fafcff;border:1px solid #e8e8e8;padding:5px;text-align:right;color:#0044aa;font-weight:700;">' + (t.rate !== null && t.rate !== undefined ? fmt(t.rate) : dash()) + '</td>';
                bodyHtml += '<td colspan="2" style="background:#f0fff0;border:1px solid #a5d6a7;padding:5px;text-align:right;color:#1b5e20;font-weight:700;">' + (amt > 0 ? fmt(amt) : dash()) + '</td>';
                bodyHtml += '<td style="background:#fafcff;border:1px solid #e8e8e8;"></td>';
                bodyHtml += '</tr>';
                return;
            }

            /* ── SALE TOTAL ROW ── */
            if (t.row_type === 'sale_total') {
                var tQty     = n(t.total_qty);
                var addDisc  = n(t.add_disc);
                var extraChg = n(t.extra_chg);

                /* Combined: Total Qty + Invoice Grand Total in ONE row */
                bodyHtml += '<tr class="detail-row">';
                bodyHtml += '<td colspan="5" style="background:#eaf4ff;border:1px solid #b8d8f0;padding:0;"></td>';
                bodyHtml += '<td style="background:#eaf4ff;border:1px solid #b8d8f0;padding:5px 8px;font-size:11px;font-weight:700;color:#004080;text-align:right;">&#9654; Total Qty</td>';
                bodyHtml += '<td style="background:#eaf4ff;border:1px solid #b8d8f0;padding:5px;text-align:right;font-size:13px;font-weight:800;color:#1a1a2e;">' + fmt(tQty) + ' pcs</td>';
                bodyHtml += '<td style="background:#eaf4ff;border:1px solid #b8d8f0;padding:5px 8px;font-size:11px;font-weight:700;color:#004080;text-align:right;">Invoice Total</td>';
                bodyHtml += '<td colspan="2" style="background:#1a3a5e;border:1px solid #0a2040;padding:5px 8px;text-align:right;font-size:13px;color:#69f0ae;font-weight:800;">' + fmt(curInvDebit > 0 ? curInvDebit : curInvLineTotal) + '</td>';
                bodyHtml += '<td style="background:#eaf4ff;border:1px solid #b8d8f0;padding:0;"></td>';
                bodyHtml += '</tr>';

                /* Additional Discount (only if > 0) */
                if (addDisc > 0) {
                    bodyHtml += '<tr class="detail-row">';
                    bodyHtml += '<td colspan="7" style="background:#fff3e0;border:1px solid #ffb74d;padding:4px 10px;text-align:right;font-size:11px;font-weight:700;color:#e65100;">&#8595; Additional Discount</td>';
                    bodyHtml += '<td style="background:#fff3e0;border:1px solid #ffb74d;padding:4px 6px;text-align:right;font-size:11px;font-weight:700;color:#e65100;">Discount</td>';
                    bodyHtml += '<td colspan="2" style="background:#fff3e0;border:1px solid #ffb74d;padding:4px 8px;text-align:right;font-size:12px;color:#bf360c;font-weight:800;">&minus; ' + fmt(addDisc) + '</td>';
                    bodyHtml += '<td style="background:#fff3e0;border:1px solid #ffb74d;"></td>';
                    bodyHtml += '</tr>';
                }

                /* Extra Charges (only if > 0) */
                if (extraChg > 0) {
                    bodyHtml += '<tr class="detail-row">';
                    bodyHtml += '<td colspan="7" style="background:#e8f5e9;border:1px solid #a5d6a7;padding:4px 10px;text-align:right;font-size:11px;font-weight:700;color:#1b5e20;">&#8593; Extra Charges (Freight)</td>';
                    bodyHtml += '<td style="background:#e8f5e9;border:1px solid #a5d6a7;padding:4px 6px;text-align:right;font-size:11px;font-weight:700;color:#1b5e20;">Freight</td>';
                    bodyHtml += '<td colspan="2" style="background:#e8f5e9;border:1px solid #a5d6a7;padding:4px 8px;text-align:right;font-size:12px;color:#1b5e20;font-weight:800;">+ ' + fmt(extraChg) + '</td>';
                    bodyHtml += '<td style="background:#e8f5e9;border:1px solid #a5d6a7;"></td>';
                    bodyHtml += '</tr>';
                }

                return;
            }

            /* ── REGULAR ROWS: Receipt, Return, Payment Voucher ── */
            var debit  = n(t.debit);
            var credit = n(t.credit);
            if (debit  > 0) grandDr += debit;
            if (credit > 0) grandCr += credit;

            var descHtml = t.description ? '<strong>' + t.description + '</strong>' : '';
            if (t.item_name && t.item_name !== '-') {
                descHtml += '<br><small style="color:#555;">' + t.item_name + '</small>';
            }
            var dcHtml = (t.dc_no && t.dc_no !== '-') ? '<span style="color:#0044aa;font-weight:600;">' + t.dc_no + '</span>' : '';
            var gpHtml = (t.gp_no && t.gp_no !== '-') ? '<span style="color:#006633;font-weight:600;">' + t.gp_no + '</span>' : '';

            bodyHtml += '<tr class="' + rc + '">';
            bodyHtml += td(t.date  || '', 'center');
            bodyHtml += td(t.vno ? '<strong>' + t.vno + '</strong>' : '', 'center');
            bodyHtml += td(t.bill && t.bill !== '-' ? t.bill : '', 'center');
            bodyHtml += td(dcHtml, 'center');
            bodyHtml += td(gpHtml, 'center');
            bodyHtml += td(descHtml, 'left');
            bodyHtml += td(t.qty  && n(t.qty)  > 0 ? fmt(t.qty)  : '', 'right');
            bodyHtml += td(t.rate && n(t.rate) > 0 ? fmt(t.rate) : '', 'right');
            bodyHtml += td(debit  > 0 ? '<strong style="color:#c62828;">' + fmt(debit)  + '</strong>' : '', 'right');
            bodyHtml += td(credit > 0 ? '<strong style="color:#2e7d32;">' + fmt(credit) + '</strong>' : '', 'right');
            bodyHtml += td(t.balance !== null && t.balance !== undefined ? balHtml(t.balance) : '', 'right');
            bodyHtml += '</tr>';
        });

        /* ── footer HTML ── */
        var finalBal = n(res.closing_balance);

        /* Overall Sum row (top to bottom) */
        var footHtml = '<tr class="r-total">';
        footHtml += '<td colspan="6" style="text-align:right;border:1px solid #ccc;padding:6px 10px;font-size:13px;"><strong>Total Sum (All Transactions)</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;font-size:14px;font-weight:800;color:#1a1a2e;background:#eaf4ff;">' + fmt(grandQty) + ' <small style="font-size:10px;color:#555;">pcs</small></td>';
        footHtml += '<td style="border:1px solid #ccc;padding:6px 8px;"></td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;"><strong style="color:#c62828;">' + fmt(grandDr) + '</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;"><strong style="color:#2e7d32;">' + fmt(grandCr) + '</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #ccc;padding:6px 8px;">' + balHtml(finalBal) + '</td>';
        footHtml += '</tr>';

        /* Closing Balance row */
        footHtml += '<tr class="r-close">';
        footHtml += '<td colspan="10" style="text-align:right;border:1px solid #333;padding:8px 12px;font-size:13px;letter-spacing:.4px;"><strong>CLOSING BALANCE</strong></td>';
        footHtml += '<td style="text-align:right;border:1px solid #333;padding:8px 10px;font-size:15px;">' + balHtml(finalBal) + '</td>';
        footHtml += '</tr>';

        /* ── inject into DOM ── */
        $('#ledgerBody').html(bodyHtml);
        $('#ledgerFooter').html(footHtml);

        $('#ledgerBox').show();
        $('#printBtnWrap').show();
    }

    /* ---------- Toggle Invoice Details ---------- */
    window.toggleInvoiceDetails = function() {
        var $table = $('#ledgerTable');
        var $btn = $('#toggleDetailsBtn');
        var $icon = $btn.find('i');
        
        if ($table.hasClass('hide-details')) {
            $table.removeClass('hide-details');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
            $btn.html('<i class="fas fa-eye-slash me-1"></i> Hide Details');
        } else {
            $table.addClass('hide-details');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
            $btn.html('<i class="fas fa-eye me-1"></i> Show Details');
        }
    };

    /* ---------- WhatsApp Share ---------- */
    window.shareWhatsApp = function() {
        Swal.fire({
            title: 'Preparing WhatsApp Share...',
            text: 'Generating PDF document to share.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        var element = document.getElementById('ledgerBox');
        var opt = {
          margin:       0.3,
          filename:     'customer_ledger_' + new Date().toISOString().slice(0,10) + '.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
            var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
            
            // Try Web Share API first for direct file sharing
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'Customer Ledger',
                    text: 'Please find the attached customer ledger.',
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
            // Download the file
            var url = URL.createObjectURL(pdfBlob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            
            // Open WhatsApp without a specific phone number to allow chat selection
            var msg = "*Customer Ledger*\nPlease find the attached PDF document.";
            var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
            window.open(waUrl, '_blank');
        });
    }
    /* ---------- Export Options & PDF ---------- */
    window.showExportOptions = function() {
        Swal.fire({
            title: 'Export Customer Ledger',
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

        var element = document.getElementById('ledgerBox');
        var opt = {
          margin:       0.3,
          filename:     'customer_ledger_' + new Date().toISOString().slice(0,10) + '.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).save().then(function() {
            Swal.close();
        });
    };

    /* ---------- CSV Export ---------- */
    window.exportCSV = function () {
        var rows = [['Date','V NO','Bill','DC No','Gate Pass','Description / Item','Qty','Rate','Debit','Credit','Balance']];
        // Only export visible rows (respects toggle details state)
        $('#ledgerTable tbody tr:visible, #ledgerTable tfoot tr:visible').each(function () {
            var cells = [];
            $(this).find('td').each(function () {
                var $td = $(this);
                // Sanitize text: replace special arrows/symbols with plain ascii
                var text = $td.text()
                            .replace(/►/g, '>')
                            .replace(/↳/g, '->')
                            .replace(/−/g, '-')
                            .replace(/↓/g, 'v')
                            .replace(/↑/g, '^')
                            .trim()
                            .replace(/"/g, '""');
                cells.push('"' + text + '"');

                // Handle colspan to keep columns aligned
                var colspan = parseInt($td.attr('colspan')) || 1;
                for (var i = 1; i < colspan; i++) {
                    cells.push('""');
                }
            });
            if (cells.length) rows.push(cells);
        });
        var csv  = rows.map(function(r){return r.join(',');}).join('\n');
        // Prepend UTF-8 BOM (\uFEFF) so Excel opens it with proper character encoding
        var blob = new Blob(["\uFEFF" + csv], {type:'text/csv;charset=utf-8;'});
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href   = url;
        a.download = 'customer_ledger_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
    };
</script>
@endsection