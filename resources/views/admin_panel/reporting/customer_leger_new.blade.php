@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid px-4">

            {{-- PAGE HEADER --}}
            <div class="row mb-3 align-items-center">
                <div class="col">
                    <h4 class="mb-0 fw-bold" style="color:#1a1a2e;">
                        <i class="fas fa-book-open me-2" style="color:#0066cc;"></i>
                        Customer Account Ledger
                    </h4>
                    <small class="text-muted">ERP Standard &mdash; Complete Transaction History with Running Balance</small>
                </div>
                <div class="col-auto" id="printBtnWrap" style="display:none;">
                    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary me-2">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                    <button onclick="exportCSV()" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-csv me-1"></i> Export CSV
                    </button>
                </div>
            </div>

            {{-- FILTER CARD --}}
            <div class="card shadow-sm mb-3" style="border-radius:10px;border:none;">
                <div class="card-body py-3">
                    <form id="ledgerForm" class="row g-3 align-items-end">
                        @php $user = Auth::user(); @endphp

                        @if($user && $user->hasRole('super admin'))
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1">Branch</label>
                                <select id="branch_id" class="form-select form-select-sm">
                                    <option value="">-- Select Branch --</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name ?? $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1">Customer</label>
                                <select id="customer_id" class="form-select form-select-sm">
                                    <option value="">-- Select Customer --</option>
                                </select>
                            </div>
                        @else
                            <div class="col-md-4">
                                <label class="form-label fw-semibold mb-1">Customer</label>
                                <select id="customer_id" class="form-select form-select-sm">
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
                            <label class="form-label fw-semibold mb-1">From Date</label>
                            <input type="date" id="start_date" class="form-control form-control-sm"
                                value="{{ $startDate ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold mb-1">To Date</label>
                            <input type="date" id="end_date" class="form-control form-control-sm"
                                value="{{ $endDate ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch"
                                class="btn btn-primary btn-sm w-100"
                                style="background:#0066cc;border-color:#0066cc;padding:7px;">
                                <i class="fas fa-search me-1"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- LOADER --}}
            <div id="loader" class="text-center py-4" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2">Loading ledger data&hellip;</p>
            </div>

            {{-- LEDGER OUTPUT --}}
            <div id="ledgerBox" style="display:none;">

                {{-- Customer Summary Card --}}
                <div class="card shadow-sm mb-3" style="border:2px solid #0066cc;border-radius:10px;background:#f0f6ff;">
                    <div class="card-body py-3">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <div class="lbl">Customer</div>
                                <div id="cust_name" class="val" style="color:#0066cc;font-size:16px;font-weight:700;">-</div>
                            </div>
                            <div class="col-md-2">
                                <div class="lbl">Type</div>
                                <span id="cust_type" class="badge bg-info text-dark" style="font-size:12px;padding:5px 10px;">-</span>
                            </div>
                            <div class="col-md-2">
                                <div class="lbl">Mobile</div>
                                <div id="cust_mobile" class="val">-</div>
                            </div>
                            <div class="col-md-3">
                                <div class="lbl">Address</div>
                                <div id="cust_addr" class="val" style="font-size:12px;">-</div>
                            </div>
                            <div class="col-md-2">
                                <div class="lbl">Credit Limit</div>
                                <div id="cust_limit" class="val fw-bold" style="color:#e65c00;">-</div>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <span class="lbl">Period: </span>
                                <span id="cust_period" class="fw-bold" style="color:#0066cc;">-</span>
                            </div>
                            <div class="col-md-8 text-end">
                                <span class="me-3"><span class="lbl">Opening: </span><span id="s_open" class="fw-bold">-</span></span>
                                <span class="me-3"><span class="lbl">Total Debit: </span><span id="s_debit" class="fw-bold text-danger">-</span></span>
                                <span class="me-3"><span class="lbl">Total Credit: </span><span id="s_credit" class="fw-bold text-success">-</span></span>
                                <span><span class="lbl">Closing: </span><span id="s_close" class="fw-bold" style="font-size:15px;">-</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- LEDGER TABLE --}}
                <div id="printArea">
                    <div class="table-responsive" style="border:2px solid #1a1a2e;border-radius:8px;overflow:hidden;">
                        <table id="ledgerTable" class="table table-bordered mb-0" style="font-size:12.5px;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#1a1a2e;color:#fff;">
                                    <th class="text-center" style="width:75px;padding:9px 6px;">Date</th>
                                    <th class="text-center" style="width:115px;padding:9px 6px;">V NO</th>
                                    <th class="text-center" style="width:70px;padding:9px 6px;">Bill</th>
                                    <th class="text-center" style="width:85px;padding:9px 6px;">DC No</th>
                                    <th class="text-center" style="width:85px;padding:9px 6px;">Gate Pass</th>
                                    <th class="text-left"   style="padding:9px 6px;">Description / Item</th>
                                    <th class="text-right"  style="width:55px;padding:9px 6px;">Qty</th>
                                    <th class="text-right"  style="width:85px;padding:9px 6px;">Rate</th>
                                    <th class="text-right"  style="width:105px;padding:9px 6px;">Debit</th>
                                    <th class="text-right"  style="width:105px;padding:9px 6px;">Credit</th>
                                    <th class="text-right"  style="width:120px;padding:9px 6px;">Total</th>
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

<style>
.lbl  { font-size:11px;color:#666;font-weight:600;text-transform:uppercase;letter-spacing:.4px; }
.val  { font-size:13px;font-weight:600;color:#1a1a2e; }

/* Row colors */
tr.r-open    td { background:#d4edff !important; font-weight:700; border-color:#aad4f5 !important; }
tr.r-sale    td { background:#fff8e1 !important; font-weight:600; border-color:#ffe082 !important; }
tr.r-item    td { background:#fffdf0 !important; font-size:12px;  color:#555;       border-color:#f0e6b0 !important; }
tr.r-receipt td { background:#e8f5e9 !important; border-color:#a5d6a7 !important; }
tr.r-pv      td { background:#f3e5f5 !important; border-color:#ce93d8 !important; }
tr.r-return  td { background:#fce4ec !important; border-color:#f48fb1 !important; }
tr.r-total   td { background:#e9ecef !important; font-weight:700; border-top:2px solid #1a1a2e !important; font-size:13px; }
tr.r-close   td { background:#1a1a2e !important; color:#fff !important; font-weight:700; font-size:14px; }

/* Balance colors */
.b-dr   { color:#c62828; font-weight:700; }
.b-cr   { color:#2e7d32; font-weight:700; }
.b-zero { color:#0066cc; font-weight:700; }
tr.r-close .b-dr { color:#ff8a80; }
tr.r-close .b-cr { color:#69f0ae; }

#ledgerTable td { vertical-align:middle; padding:5px 6px; }
#ledgerTable tr:hover td { filter:brightness(.97); }

@media print {
    .card, button, form, #printBtnWrap { display:none !important; }
    #ledgerBox, #printArea { display:block !important; }
}
</style>

<script>
$(document).ready(function () {

    /* ---------- helpers ---------- */
    function n(v) { return parseFloat(v) || 0; }

    function fmt(v) {
        v = n(v);
        return v.toLocaleString('en-PK', {minimumFractionDigits:0, maximumFractionDigits:0});
    }

    function balHtml(b, cls_override) {
        b = n(b);
        var cls   = b > 0 ? 'b-dr' : (b < 0 ? 'b-cr' : 'b-zero');
        var label = b > 0 ? ' Dr'  : (b < 0 ? ' Cr'  : '');
        if (cls_override) cls = cls_override;
        return '<span class="' + cls + '">' + fmt(Math.abs(b)) + label + '</span>';
    }

    function dash() { return '<span style="color:#ccc;">&#8212;</span>'; }

    function td(txt, align, attrs) {
        align = align || 'center';
        attrs = attrs || '';
        return '<td style="text-align:' + align + ';border:1px solid #ddd;" ' + attrs + '>' + (txt !== null && txt !== undefined && txt !== '' ? txt : dash()) + '</td>';
    }

    /* ---------- branch -> customer loader (super admin) ---------- */
    $(document).on('change', '#branch_id', function () {
        var bid = $(this).val();
        var $c  = $('#customer_id');
        $c.html('<option value="">-- Select Customer --</option>');
        if (!bid) return;
        $.get("{{ route('report.customers.byBranch') }}", {branch_id: bid}, function (list) {
            $.each(list, function (i, c) {
                $c.append($('<option/>').val(c.id).text(c.customer_name + (c.customer_type ? ' (' + c.customer_type + ')' : '')));
            });
        });
    });

    /* ---------- search ---------- */
    $('#btnSearch').on('click', function () {
        var cid   = $('#customer_id').val();
        var start = $('#start_date').val();
        var end   = $('#end_date').val();

        if (!cid || !start || !end) { alert('Please fill all fields.'); return; }

        $('#loader').show();
        $('#ledgerBox').hide();
        $('#printBtnWrap').hide();

        $.get("{{ route('report.customer.ledger.fetch.new') }}", {
            customer_id: cid,
            start_date : start,
            end_date   : end
        })
        .done(function (res) {
            $('#loader').hide();
            if (res.error) { alert(res.error); return; }
            render(res, start, end);
        })
        .fail(function (xhr) {
            $('#loader').hide();
            var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Server error. Check console.';
            alert(msg);
        });
    });

    /* ---------- render ---------- */
    function render(res, start, end) {
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

        var ob      = n(res.opening_balance);
        var txns    = res.transactions || [];
        var grandDr = 0;
        var grandCr = 0;

        /* ── body HTML ── */
        var bodyHtml = '';

        /* Opening Balance Row */
        bodyHtml += '<tr class="r-open">';
        bodyHtml += td('<strong>Opening Balance</strong>', 'left', 'colspan="5"');
        bodyHtml += td('', 'center');   /* description */
        bodyHtml += td('', 'right');    /* qty */
        bodyHtml += td('', 'right');    /* rate */
        bodyHtml += td('', 'right');    /* debit */
        bodyHtml += td('', 'right');    /* credit */
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

            /* Sale item sub-row (no amounts, no balance) */
            if (t.row_type === 'sale_item') {
                bodyHtml += '<tr class="' + rc + '">';
                bodyHtml += td('', 'center');   /* date */
                bodyHtml += td('', 'center');   /* vno */
                bodyHtml += td('', 'center');   /* bill */
                bodyHtml += td('', 'center');   /* dc */
                bodyHtml += td('', 'center');   /* gp */
                bodyHtml += td('<span style="padding-left:18px;color:#555;">&#8627; ' + (t.item_name || '-') + '</span>', 'left');
                bodyHtml += td(t.qty  && n(t.qty)  > 0 ? fmt(t.qty)  : '', 'right');
                bodyHtml += td(t.rate && n(t.rate) > 0 ? fmt(t.rate) : '', 'right');
                bodyHtml += td('', 'right');
                bodyHtml += td('', 'right');
                bodyHtml += td('', 'right');
                bodyHtml += '</tr>';
                return; /* continue forEach */
            }

            /* Regular row */
            var debit  = n(t.debit);
            var credit = n(t.credit);
            if (debit  > 0) grandDr += debit;
            if (credit > 0) grandCr += credit;

            /* Description cell */
            var descHtml = t.description ? '<strong>' + t.description + '</strong>' : '';
            if (t.item_name && t.item_name !== '-' && t.row_type !== 'sale_header') {
                descHtml += '<br><small style="color:#555;">' + t.item_name + '</small>';
            }

            /* DC / GP cells */
            var dcHtml = (t.dc_no && t.dc_no !== '-')
                ? '<span style="color:#0044aa;font-weight:600;">' + t.dc_no + '</span>'
                : '';
            var gpHtml = (t.gp_no && t.gp_no !== '-')
                ? '<span style="color:#006633;font-weight:600;">' + t.gp_no + '</span>'
                : '';

            bodyHtml += '<tr class="' + rc + '">';
            bodyHtml += td(t.date  || '', 'center');
            bodyHtml += td(t.vno   ? '<strong>' + t.vno + '</strong>' : '', 'center');
            bodyHtml += td(t.bill  && t.bill  !== '-' ? t.bill  : '', 'center');
            bodyHtml += td(dcHtml,  'center');
            bodyHtml += td(gpHtml,  'center');
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

        var footHtml  = '<tr class="r-total">';
        footHtml += td('<strong>Sum :</strong>', 'right', 'colspan="8"');
        footHtml += td('<strong style="color:#c62828;">' + fmt(grandDr) + '</strong>', 'right');
        footHtml += td('<strong style="color:#2e7d32;">' + fmt(grandCr) + '</strong>', 'right');
        footHtml += td(balHtml(finalBal), 'right');
        footHtml += '</tr>';

        footHtml += '<tr class="r-close">';
        footHtml += td('<strong>CLOSING BALANCE</strong>', 'right', 'colspan="10"');
        footHtml += td('<span style="font-size:14px;">' + balHtml(finalBal) + '</span>', 'right');
        footHtml += '</tr>';

        /* ── inject into DOM ── */
        $('#ledgerBody').html(bodyHtml);
        $('#ledgerFooter').html(footHtml);

        $('#ledgerBox').show();
        $('#printBtnWrap').show();
    }

    /* ---------- CSV Export ---------- */
    window.exportCSV = function () {
        var rows = [['Date','V NO','Bill','DC No','Gate Pass','Description','Item','Qty','Rate','Debit','Credit','Balance']];
        $('#ledgerTable tbody tr, #ledgerTable tfoot tr').each(function () {
            var cells = [];
            $(this).find('td').each(function () {
                cells.push('"' + $(this).text().trim().replace(/"/g,'""') + '"');
            });
            if (cells.length) rows.push(cells);
        });
        var csv  = rows.map(function(r){return r.join(',');}).join('\n');
        var blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href   = url;
        a.download = 'customer_ledger_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
    };
});
</script>
@endsection