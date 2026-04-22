@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <div class="page-header row mb-3">
                <div class="page-title col-lg-6">
                    <h4>Customer Ledger</h4>
                    <h6>View ledger by date range</h6>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form id="ledgerForm" class="row g-2 align-items-end">
                        @php $user = Auth::user(); @endphp
                        @if($user && $user->hasRole('super admin'))
                            <div class="col-md-3">
                                <label class="form-label">Branch</label>
                                <select name="branch_id" id="branch_id" class="form-control">
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name ?? $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Customer</label>
                                <select name="customer_id" id="customer_id" class="form-control" required>
                                    <option value="">Select Customer</option>
                                </select>
                            </div>
                        @else
                            <div class="col-md-4">
                                <label class="form-label">Customer</label>
                                <select name="customer_id" id="customer_id" class="form-control" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $c)
                                    <option value="{{ $c->id }}" data-credit="{{ $c->credit_limit }}" data-type="{{ $c->customer_type }}">
                                        {{ $c->customer_name }} ({{ $c->customer_type }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate ?? '' }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate ?? '' }}" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-primary w-100">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="loader" style="display:none;text-align:center;margin-bottom:10px;">
                        <div class="spinner-border" role="status"></div>
                    </div>

                    <div id="ledgerBox" style="display:none;">
                        <div class="ledger-box">
                            <div class="ledger-title">CUSTOMER LEDGER</div>
                            
                            <!-- Customer Details -->
                            <div id="ledgerHeader" class="ledger-header mb-3" style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div>
                                    <strong>Customer Name:</strong> <span id="cust_name"></span>
                                </div>
                                <div>
                                    <strong>Type:</strong> <span id="cust_type"></span>
                                </div>
                                <div>
                                    <strong>Address:</strong> <span id="cust_addr" style="font-size: 12px;"></span>
                                </div>
                                <div>
                                    <strong>Mobile:</strong> <span id="cust_mobile"></span>
                                </div>
                                <div>
                                    <strong>Credit Limit:</strong> <span id="cust_limit" class="badge bg-info"></span>
                                </div>
                                <div>
                                    <strong>Period:</strong> <span id="cust_period"></span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="text-dark bg-light">
                                        <tr>
                                            <th width="10%">Date</th>
                                            <th width="12%">Invoice/Ref</th>
                                            <th width="30%">Description</th>
                                            <th width="12%" class="text-end">Debit</th>
                                            <th width="12%" class="text-end">Credit</th>
                                            <th width="15%" class="text-end">Balance</th>
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