@extends('admin_panel.layout.app')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

    .pv-page { background: #f0f4f8; min-height: 100vh; padding: 1.5rem; }

    /* Header */
    .pv-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        border-radius: 14px;
        padding: 1.25rem 1.75rem;
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 6px 20px rgba(37,99,235,0.25);
    }
    .pv-header h3 { color: #fff; font-weight: 700; font-size: 1.3rem; margin: 0; }
    .pv-header p  { color: rgba(255,255,255,0.75); margin: 0; font-size: 0.82rem; }
    .pv-badge {
        background: rgba(255,255,255,0.18);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 8px;
        padding: 0.5rem 1.25rem;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 1px;
    }

    /* Card */
    .pv-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.07);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .pv-card-header {
        padding: 0.9rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 600;
        font-size: 0.92rem;
        border-bottom: 1px solid #f0f4f8;
    }
    .pv-card-header.green  { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .pv-card-header.blue   { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .pv-card-header.gray   { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
    .pv-card-body { padding: 1.5rem; }

    /* Form Controls */
    .form-label { font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.35rem; display: block; }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        padding: 0.55rem 0.75rem;
        font-size: 0.875rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        width: 100%;
        background: #fff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        outline: none;
    }
    .form-control[readonly] { background: #f8fafc; color: #94a3b8; }

    /* Balance Info Strip */
    .balance-strip {
        background: linear-gradient(90deg, #eff6ff, #f0fdf4);
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 0.75rem 1.25rem;
        margin-top: 0.75rem;
        display: flex;
        gap: 2rem;
        align-items: center;
        flex-wrap: wrap;
        font-size: 0.83rem;
    }
    .balance-strip .bl-item { display: flex; flex-direction: column; gap: 2px; }
    .balance-strip .bl-label { color: #64748b; font-weight: 500; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; }
    .balance-strip .bl-value { font-weight: 700; font-size: 1rem; color: #1e293b; }
    .balance-strip .text-green { color: #15803d; }
    .balance-strip .text-red   { color: #dc2626; }

    /* TO Table */
    .to-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .to-table thead th {
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.7rem 0.75rem;
        border-bottom: 2px solid #bfdbfe;
    }
    .to-table tbody td { padding: 0.5rem 0.5rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
    .to-table tbody tr:last-child td { border-bottom: none; }

    /* Row Number */
    .row-num {
        width: 28px; height: 28px;
        background: #eff6ff; color: #2563eb;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
    }

    /* Add Row Button */
    .add-row-btn {
        border: 2px dashed #93c5fd;
        background: #eff6ff;
        color: #2563eb;
        border-radius: 10px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        width: 100%;
        margin-top: 0.75rem;
        transition: all 0.2s;
        display: flex; align-items: center; justify-content: center; gap: 0.4rem;
    }
    .add-row-btn:hover { background: #dbeafe; border-color: #3b82f6; transform: translateY(-1px); }

    /* Total Box */
    .total-box {
        background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 100%);
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        color: #fff;
    }
    .total-box .total-label { font-size: 0.82rem; opacity: 0.8; margin-bottom: 0.2rem; }
    .total-box .total-value { font-size: 2rem; font-weight: 800; letter-spacing: -0.02em; }

    /* Buttons */
    .btn-save {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff; border: none;
        border-radius: 10px; padding: 0.75rem 2rem;
        font-weight: 700; font-size: 0.95rem;
        cursor: pointer; width: 100%;
        box-shadow: 0 4px 12px rgba(37,99,235,0.35);
        transition: all 0.2s;
        display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(37,99,235,0.4); }
    .btn-outline-link {
        color: #64748b; text-decoration: none; border: 1.5px solid #e2e8f0;
        border-radius: 10px; padding: 0.7rem 1.5rem;
        font-weight: 600; font-size: 0.875rem; width: 100%;
        display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        transition: all 0.2s; background: #fff;
    }
    .btn-outline-link:hover { background: #f8fafc; border-color: #cbd5e1; }

    .btn-remove-row { border: none; background: none; color: #dc2626; cursor: pointer; padding: 0.3rem; border-radius: 6px; transition: background 0.15s; }
    .btn-remove-row:hover { background: #fee2e2; }

    /* Loading spinner inside select */
    .select-loading { color: #94a3b8; font-style: italic; }

    /* Alert */
    .pv-alert { border-radius: 10px; padding: 0.85rem 1.2rem; font-size: 0.875rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .pv-alert.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .pv-alert.error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

    /* Responsive */
    @media (max-width: 768px) {
        .pv-header { flex-direction: column; gap: 0.75rem; text-align: center; }
        .to-table thead th, .to-table tbody td { font-size: 0.75rem; padding: 0.4rem; }
    }
</style>

<div class="pv-page">
    <div class="container-fluid" style="max-width: 1200px;">

        {{-- Header --}}
        <div class="pv-header">
            <div>
                <h3><i class="bi bi-wallet2 me-2"></i>Payment Voucher</h3>
                <p>Record payments made to customers, vendors or other accounts</p>
            </div>
            <div class="pv-badge">{{ $nextPVID }}</div>
        </div>

        @if(session('success'))
            <div class="pv-alert success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="pv-alert error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
        @endif

        <form action="{{ route('Payment.vochers.store') }}" method="POST" id="paymentForm">
            @csrf
            <input type="hidden" name="pvid" value="{{ $nextPVID }}">

            {{-- ─── VOUCHER META ─── --}}
            <div class="pv-card">
                <div class="pv-card-header gray">
                    <i class="bi bi-calendar3"></i> Voucher Information
                </div>
                <div class="pv-card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Receipt Date <span class="text-danger">*</span></label>
                            <input type="date" name="receipt_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Entry Date</label>
                            <input type="date" name="entry_date" class="form-control" value="{{ now()->toDateString() }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Voucher Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="e.g. Payment made to vendor for raw materials...">
                        </div>
                        {{-- Branch selection (Super Admin only or hidden input) --}}
                        @php
                            $isSuperAdmin = auth()->user()->hasRole('super admin') || auth()->user()->id == 1;
                            $currentBranch = session('branch_id') ?? auth()->user()->branch_id;
                            $Branch = \App\Models\Branch::get();
                        @endphp
                        @if($isSuperAdmin)
                        <div class="col-md-3">
                            <label class="form-label">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id" class="form-select" required>
                                <option value="">— Select Branch —</option>
                                @foreach ($Branch as $b)
                                    <option value="{{ $b->id }}" {{ $currentBranch == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="branch_id" id="branch_id" value="{{ $currentBranch }}">
                        @endif
                    </div>
                </div>
            </div>

            {{-- ─── TO (Paid To Party) ─── --}}
            <div class="pv-card">
                <div class="pv-card-header green">
                    <i class="bi bi-box-arrow-right"></i> TO &mdash; Paid To Party
                    <span style="font-weight: 400; font-size: 0.78rem; margin-left: auto; opacity: 0.8;">Select type first, then the party</span>
                </div>
                <div class="pv-card-body">
                    <div class="row g-3 align-items-end">
                        {{-- Party Type --}}
                        <div class="col-md-3">
                            <label class="form-label">Party / Account Type <span class="text-danger">*</span></label>
                            <select name="vendor_type" id="vendor_type" class="form-select" required>
                                <option value="">— Select Type —</option>
                                @foreach ($AccountHeads as $head)
                                    <option value="{{ $head->id }}" data-kind="account">{{ $head->name }}</option>
                                @endforeach
                                <option value="customer"  data-kind="party">Customer</option>
                                <!-- <option value="walkin"    data-kind="party">Walkin Customer</option> -->
                                <option value="vendor"    data-kind="party">Vendor</option>
                            </select>
                        </div>

                        {{-- Party Name --}}
                        <div class="col-md-4" id="party_name_container">
                            <label class="form-label" id="party_label">Select Party / Account <span class="text-danger">*</span></label>
                            <select name="vendor_id" id="vendor_id" class="form-select" required>
                                <option value="">— First select type above —</option>
                            </select>
                        </div>

                        {{-- Walk-in Name (Hidden by default) --}}
                        <div class="col-md-4" id="walkin_name_container" style="display: none;">
                            <label class="form-label">Walk-in Customer Name</label>
                            <input type="text" name="walking_customer_name" id="walking_customer_name" class="form-control" placeholder="Enter specific name">
                        </div>

                        {{-- Mobile / Code --}}
                        <div class="col-md-2">
                            <label class="form-label">Mobile / Code</label>
                            <input type="text" name="tel" id="tel" class="form-control" readonly placeholder="Auto-filled">
                        </div>

                        {{-- Previous Balance --}}
                        <div class="col-md-3" id="balance_container">
                            <label class="form-label">Previous Balance</label>
                            <input type="number" name="bal" id="bal" class="form-control" readonly placeholder="0.00">
                        </div>
                    </div>

                    {{-- Balance Info Strip --}}
                    <div class="balance-strip" id="balanceStrip" style="display:none;">
                        <div class="bl-item">
                            <span class="bl-label">Party</span>
                            <span class="bl-value" id="strip_name">—</span>
                        </div>
                        <div class="bl-item">
                            <span class="bl-label">Current Outstanding Balance</span>
                            <span class="bl-value" id="strip_bal">0.00</span>
                        </div>
                        <div class="bl-item">
                            <span class="bl-label">Mobile</span>
                            <span class="bl-value" id="strip_mobile">—</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── FROM (Paid From Accounts) ─── --}}
            <div class="pv-card">
                <div class="pv-card-header blue">
                    <i class="bi bi-bank"></i> FROM &mdash; Paid From Accounts
                    <span style="font-weight: 400; font-size: 0.78rem; margin-left: auto; opacity: 0.8;">You can split the amount into multiple accounts</span>
                </div>
                <div class="pv-card-body" style="padding: 1rem;">
                    <div class="table-responsive">
                        <table class="to-table" id="voucherTable">
                            <thead>
                                <tr>
                                    <th style="width:3%">#</th>
                                    <th style="width:22%">Narration</th>
                                    <th style="width:12%">Reference #</th>
                                    <th style="width:20%">Account Head</th>
                                    <th style="width:22%">Account Name</th>
                                    <th style="width:10%">Discount</th>
                                    <th style="width:10%">Amount (Rs.)</th>
                                    <th style="width:3%"></th>
                                </tr>
                            </thead>
                            <tbody id="voucherRows">
                                {{-- Row 1 (default) --}}
                                <tr class="voucher-row">
                                    <td><div class="row-num">1</div></td>
                                    <td>
                                        <div class="d-flex flex-nowrap gap-1">
                                            <select name="narration_id[]" class="form-select narrationSelect" required>
                                                <option value="">— Select Narration —</option>
                                                @foreach ($narrations as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-outline-primary addNarrationBtn" type="button" title="Create New Narration" style="flex-shrink: 0;">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td><input name="reference_no[]" type="text" class="form-control" placeholder="Ref / Cheque #"></td>
                                    <td>
                                        <select name="row_account_head[]" class="form-select rowAccountHead" required>
                                            <option value="">— Select Head —</option>
                                            @foreach ($AccountHeads as $head)
                                                <option value="{{ $head->id }}">{{ $head->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="row_account_id[]" class="form-select rowAccountSub" required>
                                            <option value="">— Select Account —</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input name="discount_value[]" type="number" step="0.01" min="0" class="form-control discount" value="0">
                                    </td>
                                    <td><input name="amount[]" type="number" step="0.01" min="0" class="form-control amount" placeholder="0.00" required></td>
                                    <td>
                                        <button type="button" class="btn-remove-row removeRow" title="Remove row">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="add-row-btn" id="addRowBtn">
                        <i class="bi bi-plus-lg"></i> Add Another Account Row
                    </button>
                </div>
            </div>

            {{-- ─── FOOTER: Tips + Total + Actions ─── --}}
            <div class="row g-3">
                <div class="col-md-7">
                    <div class="pv-card">
                        <div class="pv-card-header gray"><i class="bi bi-lightbulb"></i> Quick Tips</div>
                        <div class="pv-card-body" style="padding: 1rem 1.5rem;">
                            <ul style="margin: 0; padding-left: 1.25rem; color: #64748b; font-size: 0.82rem; line-height: 1.8;">
                                <li>Select the <strong>Party Type</strong> first — the party list below will update automatically.</li>
                                <li>A payment to a <strong>Vendor</strong> reduces their outstanding outstanding balance.</li>
                                <li>You can pay from <strong>multiple accounts</strong> (e.g. part Cash, part Bank).</li>
                                <li>New narrations typed here are <strong>saved for future use</strong>.</li>
                                <li>Total of all rows must be <strong>greater than zero</strong> to save.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="total-box mb-3">
                        <div class="total-label"><i class="bi bi-cash-stack me-1"></i> Total Paid Amount</div>
                        <div class="total-value">Rs. <span id="totalAmountDisplay">0.00</span></div>
                        <input type="hidden" name="total_amount" id="totalAmount" value="0">
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" class="btn-save">
                            <i class="bi bi-check2-all"></i> Confirm &amp; Save Voucher
                        </button>
                        <a href="{{ route('all-Payment-vochers') }}" class="btn-outline-link">
                            <i class="bi bi-list-ul"></i> View All Payments
                        </a>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {

    // ─── NARRATION TOGGLE ───────────────────────────────────────────────────
    $(document).on('change', '.narrationSelect', function () {
        var $input = $(this).siblings('.narrationInput');
        if ($(this).val() === '') {
            $input.show().attr('required', true).focus();
        } else {
            $input.hide().removeAttr('required').val('');
        }
    });

    // ─── PARTY TYPE → LOAD PARTY LIST (AJAX) ────────────────────────────────
    $('#vendor_type, #branch_id').on('change', function () {
        var type = $('#vendor_type').val();
        var branchId = $('#branch_id').val() || '';
        var kind = $('#vendor_type').find(':selected').data('kind'); // "party" or "account"
        var $partySelect = $('#vendor_id');

        // Toggle Walk-in Name Field
        if (type === 'walkin') {
            $('#party_name_container').hide();
            $('#vendor_id').prop('required', false);
            
            $('#walkin_name_container').show();
            $('#walking_customer_name').prop('required', true);

            // Hide balance, make mobile editable
            $('#balance_container').hide();
            $('#tel').prop('readonly', false).attr('placeholder', 'Enter mobile');
        } else {
            $('#walkin_name_container').hide();
            $('#walking_customer_name').val('').prop('required', false);
            
            $('#party_name_container').show();
            $('#vendor_id').prop('required', true);

            // Show balance, make mobile readonly
            $('#balance_container').show();
            $('#tel').prop('readonly', true).attr('placeholder', 'Auto-filled');
        }

        // Reset fields
        $('#tel').val('');
        $('#bal').val('');
        $('#balanceStrip').hide();
        $('#party_label').html('Select Party / Account <span class="text-danger">*</span>');
        
        // Reset row account subs if branch changed
        if ($(this).attr('id') === 'branch_id') {
            $('.rowAccountHead').trigger('change');
        }

        $partySelect.html('<option value="" class="select-loading">Loading...</option>');

        if (!type) {
            $partySelect.html('<option value="">— First select type above —</option>');
            return;
        }

        if (kind === 'party') {
            // Fetch branch-aware customers / vendors
            $.getJSON('{{ route("receipt.party.list") }}', { type: type, branch_id: branchId }, function (data) {
                var html = '<option value="">— Select ' + (type === 'vendor' ? 'Vendor' : 'Customer') + ' —</option>';
                data.forEach(function (item) {
                    html += '<option value="' + item.id + '" '
                        + 'data-bal="' + (item.closing_balance || 0) + '" '
                        + 'data-mobile="' + (item.mobile || '') + '">'
                        + item.text + '</option>';
                });
                $partySelect.html(html);
                
                // Auto-select walk-in so the voucher links to the correct Walk-in account internally
                if (type === 'walkin' && data.length > 0) {
                    $partySelect.val(data[0].id).trigger('change');
                }
            }).fail(function () {
                $partySelect.html('<option value="">Error loading data</option>');
            });
        } else if (kind === 'account') {
            // Fetch accounts under this head
            $.getJSON('{{ url("get-accounts-by-head") }}/' + type, { branch_id: branchId }, function (data) {
                var html = '<option value="">— Select Account —</option>';
                data.forEach(function (acc) {
                    html += '<option value="' + acc.id + '" '
                        + 'data-bal="' + (acc.opening_balance || 0) + '" '
                        + 'data-mobile="' + (acc.account_code || '') + '">'
                        + acc.title + ' (' + (acc.account_code || '') + ')</option>';
                });
                $partySelect.html(html);
            }).fail(function () {
                $partySelect.html('<option value="">Error loading accounts</option>');
            });
        }
    });

    // ─── PARTY SELECTED → SHOW BALANCE STRIP ────────────────────────────────
    $('#vendor_id').on('change', function () {
        var $opt = $(this).find(':selected');
        var bal  = parseFloat($opt.data('bal'))    || 0;
        var mob  = $opt.data('mobile') || '—';
        var name = $opt.text();

        $('#bal').val(bal.toFixed(2));
        $('#tel').val(mob);

        $('#strip_name').text(name);
        $('#strip_mobile').text(mob);

        var $balEl = $('#strip_bal');
        $balEl.text(formatNum(bal));
        $balEl.removeClass('text-green text-red');
        $balEl.addClass(bal >= 0 ? 'text-green' : 'text-red');

        if ($opt.val()) {
            $('#balanceStrip').show();
        } else {
            $('#balanceStrip').hide();
        }
    });

    // ─── ACCOUNT HEAD CHANGE → LOAD SUB-ACCOUNTS ────────────────────────────
    $(document).on('change', '.rowAccountHead', function () {
        var headId     = $(this).val();
        var branchId   = $('#branch_id').val() || '';
        var $subSelect = $(this).closest('tr').find('.rowAccountSub');

        $subSelect.html('<option value="" class="select-loading">Loading...</option>');

        if (!headId) {
            $subSelect.html('<option value="">— Select Account —</option>');
            return;
        }

        $.getJSON('{{ url("get-accounts-by-head") }}/' + headId, { branch_id: branchId }, function (res) {
            var html = '<option value="">— Select Account —</option>';
            res.forEach(function (acc) {
                html += '<option value="' + acc.id + '">' + acc.title + '</option>';
            });
            $subSelect.html(html);
        }).fail(function () {
            $subSelect.html('<option value="">Error loading</option>');
        });
    });

    // ─── AMOUNT CALCULATION ──────────────────────────────────────────────────
    $(document).on('input', '.amount, .discount', function () {
        calculateTotal();
    });

    function calculateTotal() {
        var total = 0;
        $('.amount').each(function () {
            total += parseFloat($(this).val()) || 0;
        });
        $('#totalAmount').val(total.toFixed(2));
        $('#totalAmountDisplay').text(formatNum(total));
    }

    function formatNum(n) {
        return parseFloat(n).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ─── ADD ROW ─────────────────────────────────────────────────────────────
    $('#addRowBtn').on('click', function () {
        var rowCount = $('.voucher-row').length + 1;
        var narrationOptions = '';
        @foreach ($narrations as $id => $name)
            narrationOptions += '<option value="{{ $id }}">{{ $name }}</option>';
        @endforeach

        var accountHeadOptions = '';
        @foreach ($AccountHeads as $head)
            accountHeadOptions += '<option value="{{ $head->id }}">{{ $head->name }}</option>';
        @endforeach

        var $newRow = $(`
            <tr class="voucher-row">
                <td><div class="row-num">${rowCount}</div></td>
                <td>
                    <div class="d-flex flex-nowrap gap-1">
                        <select name="narration_id[]" class="form-select narrationSelect" required>
                            <option value="">— Select Narration —</option>
                            ${narrationOptions}
                        </select>
                        <button class="btn btn-outline-primary addNarrationBtn" type="button" title="Create New Narration" style="flex-shrink: 0;">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </td>
                <td><input name="reference_no[]" type="text" class="form-control" placeholder="Ref / Cheque #"></td>
                <td>
                    <select name="row_account_head[]" class="form-select rowAccountHead" required>
                        <option value="">— Select Head —</option>
                        ${accountHeadOptions}
                    </select>
                </td>
                <td>
                    <select name="row_account_id[]" class="form-select rowAccountSub" required>
                        <option value="">— Select Account —</option>
                    </select>
                </td>
                <td>
                    <input name="discount_value[]" type="number" step="0.01" min="0" class="form-control discount" value="0">
                </td>
                <td><input name="amount[]" type="number" step="0.01" min="0" class="form-control amount" placeholder="0.00" required></td>
                <td>
                    <button type="button" class="btn-remove-row removeRow" title="Remove row">
                        <i class="bi bi-trash3"></i>
                    </button>
                </td>
            </tr>
        `);

        $('#voucherRows').append($newRow);
        renumberRows();
    });

    // ─── REMOVE ROW ──────────────────────────────────────────────────────────
    $(document).on('click', '.removeRow', function () {
        if ($('.voucher-row').length > 1) {
            $(this).closest('tr').remove();
            renumberRows();
            calculateTotal();
        } else {
            alert('At least one account row is required.');
        }
    });

    function renumberRows() {
        $('.voucher-row').each(function (i) {
            $(this).find('.row-num').text(i + 1);
        });
    }

    // ─── FORM SUBMIT VALIDATION ──────────────────────────────────────────────
    $('#paymentForm').on('submit', function () {
        var total = parseFloat($('#totalAmount').val()) || 0;
        if (total <= 0) {
            alert('Total amount must be greater than zero to save the voucher.');
            return false;
        }
        return true;
    });

    // ─── AJAX NARRATION MODAL ────────────────────────────────────────────────
    var activeNarrationSelect = null;
    $(document).on('click', '.addNarrationBtn', function () {
        activeNarrationSelect = $(this).siblings('.narrationSelect');
        $('#new_narration_text').val('');
        $('#addNarrationModal').modal('show');
    });

    $('#addNarrationForm').on('submit', function (e) {
        e.preventDefault();
        var text = $('#new_narration_text').val();
        var $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: '{{ route("store.narration.ajax") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                narration: text
            },
            success: function (res) {
                if (res.success) {
                    var newOption = '<option value="' + res.id + '">' + res.text + '</option>';
                    $('.narrationSelect').append(newOption);
                    if (activeNarrationSelect) {
                        activeNarrationSelect.val(res.id).trigger('change');
                    }
                    $('#addNarrationModal').modal('hide');
                } else {
                    alert('Error adding narration');
                }
            },
            error: function () {
                alert('Server error while adding narration.');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Save Narration');
            }
        });
    });

});
</script>

{{-- Add Narration Modal --}}
<div class="modal fade" id="addNarrationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="addNarrationForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Narration</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Narration Text <span class="text-danger">*</span></label>
                    <input type="text" id="new_narration_text" class="form-control" placeholder="Enter Narration Text" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Narration</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection