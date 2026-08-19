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

    .table thead th {
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
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Customer Ledger</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-file-invoice-dollar mr-1" style="color: var(--coa-gold);"></i> Account statement and running balances by date range &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button onclick="window.print()" class="btn btn-sm btn-outline-light font-weight-bold">
                        <i class="fas fa-print mr-1"></i> Print
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
                                <select name="branch_id" id="branch_id" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name ?? $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="f-label">Customer</label>
                                <select name="customer_id" id="customer_id" class="form-control form-control-sm select2" required>
                                    <option value="">Select Customer</option>
                                </select>
                            </div>
                        @else
                            <div class="col-md-4">
                                <label class="f-label">Customer</label>
                                <select name="customer_id" id="customer_id" class="form-control form-control-sm select2" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $c)
                                    <option value="{{ $c->id }}" data-credit="{{ $c->credit_limit }}" data-type="{{ $c->customer_type }}">
                                        {{ $c->customer_name }} ({{ $c->customer_type }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-2">
                            <label class="f-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;" required>
                        </div>
                        <div class="col-md-2">
                            <label class="f-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-search mr-1"></i> Search Ledger
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <div id="loader" style="display:none;text-align:center;padding:30px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Loading ledger data...</p>
                    </div>

                    <div id="ledgerBox" style="display:none;">
                        <div class="card shadow-sm mb-3 border-0" style="border-radius: 9px; border: 1.5px solid #cbd5e1 !important; background: #ffffff;">
                            <div class="card-body p-3">
                                <h6 class="font-weight-bold mb-3" style="color: var(--coa-navy); text-transform: uppercase; letter-spacing: 0.05em;">
                                    <i class="fas fa-user-circle mr-1" style="color: var(--coa-gold);"></i> Customer Account Statement
                                </h6>
                                
                                <!-- Customer Details -->
                                <div id="ledgerHeader" style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 12px; font-size: 13px;">
                                    <div>
                                        <div class="f-label">Customer Name</div>
                                        <span id="cust_name" class="font-weight-bold text-dark">-</span>
                                    </div>
                                    <div>
                                        <div class="f-label">Customer Type</div>
                                        <span id="cust_type" class="badge badge-info">-</span>
                                    </div>
                                    <div>
                                        <div class="f-label">Mobile</div>
                                        <span id="cust_mobile" class="font-weight-bold font-monospace">-</span>
                                    </div>
                                    <div style="grid-column: span 2;">
                                        <div class="f-label">Address</div>
                                        <span id="cust_addr" class="text-muted small">-</span>
                                    </div>
                                    <div>
                                        <div class="f-label">Credit Limit</div>
                                        <span id="cust_limit" class="badge badge-warning font-monospace">-</span>
                                    </div>
                                </div>
                                <div class="mt-2 pt-2 border-top">
                                    <span class="f-label d-inline mr-1">Statement Period:</span>
                                    <span id="cust_period" class="font-weight-bold" style="color: var(--coa-navy);">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" style="font-size: 12.5px;">
                                <thead>
                                    <tr>
                                        <th style="width: 12%;">Date</th>
                                        <th style="width: 15%;">Invoice/Ref</th>
                                        <th>Description</th>
                                        <th style="width: 12%;" class="text-end">Debit</th>
                                        <th style="width: 12%;" class="text-end">Credit</th>
                                        <th style="width: 14%;" class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody id="ledgerBody"></tbody>
                                <tfoot id="ledgerFooter"></tfoot>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
<style>
    .ledger-box {
        border: 1px solid #333;
        padding: 15px;
        margin: 20px auto;
        width: 100%;
        background: #fff;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
    }

    .ledger-title {
        text-align: center;
        font-weight: 700;
        font-size: 20px;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #222;
    }

    .ledger-header {
        padding: 8px 10px;
        border: 1px solid #333;
        margin-bottom: 15px;
        background: #f8f9fa;
        font-size: 14px;
    }

    .ledger-header strong {
        font-weight: 600;
    }

    table {
        font-size: 14px;
        border: 1px solid #333;
        border-collapse: collapse;
        width: 100%;
    }

    table thead th {
        background: #444;
        color: #000;
        font-weight: 600;
        border: 1px solid #333;
        text-align: center;
        padding: 8px;
    }

    table tbody td {
        border: 1px solid #333;
        text-align: center;
        padding: 7px;
    }

    .text-left {
        text-align: left !important;
    }

    .opening-balance {
        font-weight: 600;
    }

    .balance-positive {
        color: #198754;
        /* green */
        font-weight: 600;
    }

    .balance-negative {
        color: #dc3545;
        /* red */
        font-weight: 600;
    }

    .balance-neutral {
        color: #0d6efd;
        /* blue */
        font-weight: 600;
    }

    .totals-row td {
        font-weight: 700;
        background: #e9ecef;
        border-top: 2px solid #333;
    }
</style>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        // ✅ ERP STANDARD: Auto-fetch report if customer is pre-selected (for non-admin users)
        let autoCustomerId = $('#customer_id').val();
        if (autoCustomerId) {
            // Customer is pre-selected, auto-fetch after short delay
            setTimeout(function() {
                $('#btnSearch').trigger('click');
            }, 300);
        }
        
        $(document).on('click', '#btnSearch', function() {
            let cid = $("#customer_id").val();
            let start = $("#start_date").val();
            let end = $("#end_date").val();

            if (!cid || !start || !end) {
                alert("Please select all fields");
                return;
            }

            $("#loader").show();
            $.get("{{ route('report.customer.ledger.fetch') }}", {
                customer_id: cid,
                start_date: start,
                end_date: end
            }, function(res) {
                $("#loader").hide();
                $("#ledgerBox").show();

                // ============= POPULATE CUSTOMER DETAILS =============
                $("#cust_name").text(res.customer.customer_name);
                $("#cust_type").text(res.customer.customer_type);
                $("#cust_addr").text(res.customer.address || '-');
                $("#cust_mobile").text(res.customer.mobile || '-');
                $("#cust_limit").text('Rs. ' + parseFloat(res.customer.credit_limit).toFixed(2));
                $("#cust_period").text(start + ' to ' + end);

                let totalDebit = 0;
                let totalCredit = 0;
                let lastBalance = res.opening_balance;

                // ============= OPENING BALANCE ROW =============
                let html = `
                    <tr class="opening-balance">
                        <td colspan="3" style="text-align: left; font-weight: 600;">Opening Balance</td>
                        <td class="text-end">-</td>
                        <td class="text-end">-</td>
                        <td class="text-end ${lastBalance > 0 ? 'balance-positive' : (lastBalance < 0 ? 'balance-negative' : 'balance-neutral')}" style="font-weight: 600;">
                            Rs. ${parseFloat(lastBalance).toFixed(2)}
                        </td>
                    </tr>
                `;

                // ============= TRANSACTION ROWS =============
                res.transactions.forEach((t) => {
                    let debit = parseFloat(t.debit || 0);
                    let credit = parseFloat(t.credit || 0);
                    totalDebit += debit;
                    totalCredit += credit;
                    lastBalance = parseFloat(t.balance);

                    html += `
                        <tr>
                            <td>${t.date}</td>
                            <td><strong>${t.invoice}</strong></td>
                            <td>${t.description}</td>
                            <td class="text-end">${debit > 0 ? 'Rs. ' + debit.toFixed(2) : '-'}</td>
                            <td class="text-end">${credit > 0 ? 'Rs. ' + credit.toFixed(2) : '-'}</td>
                            <td class="text-end ${lastBalance > 0 ? 'balance-positive' : (lastBalance < 0 ? 'balance-negative' : 'balance-neutral')}">
                                Rs. ${lastBalance.toFixed(2)}
                            </td>
                        </tr>
                    `;
                });

                // ============= TOTALS FOOTER ROW =============
                let footerHtml = `
                    <tr class="totals-row">
                        <td colspan="3" style="text-align: left; font-weight: 700;">Period Totals:</td>
                        <td class="text-end">Rs. ${totalDebit.toFixed(2)}</td>
                        <td class="text-end">Rs. ${totalCredit.toFixed(2)}</td>
                        <td class="text-end ${lastBalance > 0 ? 'balance-positive' : (lastBalance < 0 ? 'balance-negative' : 'balance-neutral')}" style="font-weight: 700;">
                            Rs. ${lastBalance.toFixed(2)}
                        </td>
                    </tr>
                `;

                $("#ledgerBody").html(html);
                $("#ledgerFooter").html(footerHtml);

            }).fail(function(xhr) {
                $("#loader").hide();
                alert('Error loading ledger: ' + (xhr.responseJSON?.message || xhr.statusText));
            });
        });

        // Load customers when branch changes (for super admin)
        $(document).on('change', '#branch_id', function() {
            let bid = $(this).val();
            let $cust = $("#customer_id");
            $cust.empty().append('<option value="">Select Customer</option>');
            if (!bid) return;

            $.get("{{ route('report.customers.byBranch') }}", { branch_id: bid }, function(list) {
                list.forEach(function(c) {
                    let opt = $('<option/>').attr('value', c.id).text(c.customer_name + ' (' + c.customer_type + ')').attr('data-credit', c.credit_limit || 0).attr('data-type', c.customer_type || '');
                    $cust.append(opt);
                });
            }).fail(function() {
                alert('Failed to load customers for branch');
            });
        });
    });
</script>