@extends('admin_panel.layout.app')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

    .jv-page { background: #f0f4f8; min-height: 100vh; padding: 1.5rem; }

    /* ── Header ── */
    .jv-header {
        background: linear-gradient(135deg, #3730a3 0%, #6d28d9 60%, #8b5cf6 100%);
        border-radius: 16px;
        padding: 1.4rem 2rem;
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 8px 24px rgba(109,40,217,0.30);
        position: relative;
        overflow: hidden;
    }
    .jv-header::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 200px; height: 200px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .jv-header h3  { color: #fff; font-weight: 800; font-size: 1.35rem; margin: 0; letter-spacing: -0.01em; }
    .jv-header p   { color: rgba(255,255,255,0.75); margin: 0; font-size: 0.82rem; margin-top: 3px; }
    .jv-badge {
        background: rgba(255,255,255,0.18);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.3);
        border-radius: 10px;
        padding: 0.6rem 1.4rem;
        font-size: 1.05rem;
        font-weight: 800;
        letter-spacing: 2px;
        backdrop-filter: blur(4px);
    }

    /* ── Double-Entry Info Banner ── */
    .jv-info-banner {
        background: linear-gradient(90deg, #ede9fe, #f5f3ff);
        border: 1px solid #c4b5fd;
        border-left: 4px solid #7c3aed;
        border-radius: 10px;
        padding: 0.85rem 1.25rem;
        margin-bottom: 1.25rem;
        font-size: 0.83rem;
        color: #4c1d95;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
    }
    .jv-info-banner i { font-size: 1.1rem; margin-top: 1px; flex-shrink: 0; color: #7c3aed; }

    /* ── Cards ── */
    .jv-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.07);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .jv-card-header {
        padding: 0.9rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 700;
        font-size: 0.9rem;
        border-bottom: 2px solid transparent;
    }
    .jv-card-header.debit  { background: #fef3c7; color: #92400e; border-color: #fde68a; }
    .jv-card-header.credit { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
    .jv-card-header.meta   { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
    .jv-card-body { padding: 1.5rem; }

    /* ── Form Controls ── */
    .form-label { font-size: 0.79rem; font-weight: 700; color: #475569; margin-bottom: 0.35rem; display: block; text-transform: uppercase; letter-spacing: 0.04em; }
    .form-control, .form-select {
        border-radius: 9px;
        border: 1.5px solid #e2e8f0;
        padding: 0.6rem 0.85rem;
        font-size: 0.875rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        width: 100%;
        background: #fff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #7c3aed;
        box-shadow: 0 0 0 3px rgba(124,58,237,0.12);
        outline: none;
    }
    .form-control[readonly] { background: #f8fafc; color: #94a3b8; }

    /* ── Balance Strip ── */
    .balance-strip {
        background: linear-gradient(90deg, #f5f3ff, #ede9fe);
        border: 1px solid #c4b5fd;
        border-radius: 10px;
        padding: 0.75rem 1.25rem;
        margin-top: 0.85rem;
        display: flex;
        gap: 2.5rem;
        align-items: center;
        flex-wrap: wrap;
        font-size: 0.83rem;
    }
    .balance-strip .bl-item { display: flex; flex-direction: column; gap: 2px; }
    .balance-strip .bl-label { color: #6d28d9; font-weight: 600; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; }
    .balance-strip .bl-value { font-weight: 800; font-size: 1rem; color: #1e293b; }
    .text-green { color: #059669 !important; }
    .text-red   { color: #dc2626 !important; }

    /* ── Amount Hero Box ── */
    .amount-hero {
        background: linear-gradient(135deg, #3730a3 0%, #7c3aed 100%);
        border-radius: 14px;
        padding: 1.5rem 2rem;
        color: #fff;
        text-align: center;
        box-shadow: 0 8px 24px rgba(109,40,217,0.25);
    }
    .amount-hero label { font-size: 0.78rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 0.06em; display: block; margin-bottom: 0.5rem; }
    .amount-hero input {
        background: rgba(255,255,255,0.15);
        border: 2px solid rgba(255,255,255,0.35);
        border-radius: 10px;
        color: #fff;
        font-size: 2.2rem;
        font-weight: 800;
        text-align: center;
        width: 100%;
        padding: 0.5rem 1rem;
        outline: none;
        transition: border-color 0.2s;
    }
    .amount-hero input::placeholder { color: rgba(255,255,255,0.5); }
    .amount-hero input:focus { border-color: rgba(255,255,255,0.7); }

    /* ── Double Entry Diagram ── */
    .jv-diagram {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        margin-bottom: 1.25rem;
    }
    .jv-box {
        background: #fff;
        border-radius: 10px;
        padding: 0.85rem 1.25rem;
        font-size: 0.82rem;
        font-weight: 600;
        text-align: center;
        min-width: 140px;
        border: 2px solid transparent;
        flex: 1;
    }
    .jv-box.debit-box  { border-color: #fde68a; background: #fffbeb; color: #92400e; }
    .jv-box.credit-box { border-color: #6ee7b7; background: #ecfdf5; color: #065f46; }
    .jv-box .box-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 4px; opacity: 0.8; }
    .jv-box .box-name  { font-size: 0.95rem; font-weight: 800; }
    .jv-arrow { font-size: 1.5rem; color: #7c3aed; font-weight: 900; }

    /* ── Buttons ── */
    .btn-save {
        background: linear-gradient(135deg, #6d28d9, #4f46e5);
        color: #fff; border: none;
        border-radius: 10px; padding: 0.8rem 2rem;
        font-weight: 700; font-size: 0.95rem;
        cursor: pointer; width: 100%;
        box-shadow: 0 4px 14px rgba(109,40,217,0.35);
        transition: all 0.2s;
        display: flex; align-items: center; justify-content: center; gap: 0.5rem;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(109,40,217,0.45); }
    .btn-outline-link {
        color: #64748b; text-decoration: none; border: 1.5px solid #e2e8f0;
        border-radius: 10px; padding: 0.75rem 1.5rem;
        font-weight: 600; font-size: 0.875rem; width: 100%;
        display: flex; align-items: center; justify-content: center; gap: 0.4rem;
        transition: all 0.2s; background: #fff;
    }
    .btn-outline-link:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }

    /* ── Alert ── */
    .jv-alert { border-radius: 10px; padding: 0.85rem 1.2rem; font-size: 0.875rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .jv-alert.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .jv-alert.error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .jv-header { flex-direction: column; gap: 0.75rem; text-align: center; }
        .jv-diagram { flex-direction: column; }
    }
</style>

<div class="jv-page">
    <div class="container-fluid" style="max-width: 1100px;">

        {{-- ── Header ── --}}
        <div class="jv-header">
            <div>
                <h3><i class="bi bi-journal-bookmark-fill me-2"></i>Journal Voucher</h3>
                <p>Transfer outstanding balance — Customer ledger credited, Vendor/Party ledger debited</p>
            </div>
            <div class="jv-badge">{{ $nextJVID }}</div>
        </div>

        @if(session('success'))
            <div class="jv-alert success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="jv-alert error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="jv-alert error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>@foreach($errors->all() as $e) <div>• {{ $e }}</div> @endforeach</div>
            </div>
        @endif

        {{-- ── Info Banner ── --}}
        <div class="jv-info-banner">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                <strong>Journal Voucher (JV)</strong> — Used when a customer's outstanding payment is transferred directly to a vendor.
                The <strong>Credit Side</strong> reduces the customer's receivable balance. The <strong>Debit Side</strong> reduces the vendor's payable balance.
                Both ledgers are updated simultaneously.
            </div>
        </div>

        <form action="{{ route('journal.vouchers.store') }}" method="POST" id="jvForm">
            @csrf

            {{-- ── Voucher Meta ── --}}
            <div class="jv-card">
                <div class="jv-card-header meta">
                    <i class="bi bi-calendar3"></i> Voucher Information
                </div>
                <div class="jv-card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Voucher Date <span class="text-danger">*</span></label>
                            <input type="date" name="voucher_date" id="voucher_date" class="form-control"
                                   value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Entry Date</label>
                            <input type="date" class="form-control" value="{{ now()->toDateString() }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Voucher Remarks / Narration</label>
                            <input type="text" name="remarks" class="form-control"
                                   placeholder="e.g. Customer ABC's dues transferred to Vendor XYZ...">
                        </div>
                        @if($isSuperAdmin)
                        <div class="col-md-4">
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

            {{-- ── Live Diagram ── --}}
            <div class="jv-diagram">
                <div class="jv-box credit-box">
                    <div class="box-label">✅ Credit Side</div>
                    <div class="box-name" id="diagram_credit">— Select Party —</div>
                    <div style="font-size: 0.7rem; margin-top: 4px; opacity: 0.75;">Balance Reduced</div>
                </div>
                <div class="jv-arrow">→</div>
                <div style="text-align:center; flex-shrink:0;">
                    <div style="font-size: 0.68rem; color: #7c3aed; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;">Amount</div>
                    <div id="diagram_amount" style="font-size: 1.5rem; font-weight: 800; color: #4f46e5;">PKR 0</div>
                </div>
                <div class="jv-arrow">→</div>
                <div class="jv-box debit-box">
                    <div class="box-label">📊 Debit Side</div>
                    <div class="box-name" id="diagram_debit">— Select Party —</div>
                    <div style="font-size: 0.7rem; margin-top: 4px; opacity: 0.75;">Balance Reduced</div>
                </div>
            </div>

            <div class="row g-3 mb-3">

                {{-- ── CREDIT SIDE — Customer (money comes from) ── --}}
                <div class="col-md-6">
                    <div class="jv-card h-100 mb-0">
                        <div class="jv-card-header credit">
                            <i class="bi bi-person-circle"></i>
                            CREDIT SIDE — Received From (Receivable Reduced)
                        </div>
                        <div class="jv-card-body">
                            <div class="mb-3">
                                <label class="form-label">Party Type <span class="text-danger">*</span></label>
                                <select name="credit_party_type" id="credit_party_type" class="form-select" required>
                                    <option value="">— Select Type —</option>
                                    <option value="customer">Customer</option>
                                    <option value="vendor">Vendor</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Party Name <span class="text-danger">*</span></label>
                                <select name="credit_party_id" id="credit_party_id" class="form-select" required>
                                    <option value="">— Select Party —</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mobile / Code</label>
                                <input type="text" id="credit_tel" class="form-control" readonly placeholder="Auto-filled">
                            </div>

                            {{-- Balance Strip --}}
                            <div class="balance-strip" id="credit_balance_strip" style="display:none;">
                                <div class="bl-item">
                                    <span class="bl-label">Party</span>
                                    <span class="bl-value" id="credit_strip_name">—</span>
                                </div>
                                <div class="bl-item">
                                    <span class="bl-label">Outstanding Balance</span>
                                    <span class="bl-value" id="credit_strip_bal">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── DEBIT SIDE — Vendor (money goes to) ── --}}
                <div class="col-md-6">
                    <div class="jv-card h-100 mb-0">
                        <div class="jv-card-header debit">
                            <i class="bi bi-building"></i>
                            DEBIT SIDE — Paid To (Payable Reduced)
                        </div>
                        <div class="jv-card-body">
                            <div class="mb-3">
                                <label class="form-label">Party Type <span class="text-danger">*</span></label>
                                <select name="debit_party_type" id="debit_party_type" class="form-select" required>
                                    <option value="">— Select Type —</option>
                                    <option value="vendor">Vendor</option>
                                    <option value="customer">Customer</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Party Name <span class="text-danger">*</span></label>
                                <select name="debit_party_id" id="debit_party_id" class="form-select" required>
                                    <option value="">— Select Party —</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mobile / Code</label>
                                <input type="text" id="debit_tel" class="form-control" readonly placeholder="Auto-filled">
                            </div>

                            {{-- Balance Strip --}}
                            <div class="balance-strip" id="debit_balance_strip" style="display:none;">
                                <div class="bl-item">
                                    <span class="bl-label">Party</span>
                                    <span class="bl-value" id="debit_strip_name">—</span>
                                </div>
                                <div class="bl-item">
                                    <span class="bl-label">Outstanding Balance</span>
                                    <span class="bl-value" id="debit_strip_bal">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Reference / Narration ── --}}
            <div class="jv-card">
                <div class="jv-card-header meta">
                    <i class="bi bi-card-list"></i> Additional Details
                </div>
                <div class="jv-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Narration / Description</label>
                            <div class="d-flex gap-2">
                                <select name="narration_id[]" class="form-select narrationSelect">
                                    <option value="">— Select Narration —</option>
                                    @foreach ($narrations as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="text" name="narration_text[]" class="form-control mt-2 narrationInput" style="display:none;" placeholder="Or type new narration...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference No.</label>
                            <input type="text" name="reference_no[]" class="form-control" placeholder="e.g. Cheque #, Invoice #...">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Amount + Submit ── --}}
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="amount-hero">
                        <label><i class="bi bi-cash-stack me-1"></i> Journal Amount (PKR)</label>
                        <input type="number" name="amount" id="amount_field" step="0.01" min="0.01"
                               placeholder="0.00" required autocomplete="off">
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="jv-card h-100 mb-0">
                        <div class="jv-card-header meta"><i class="bi bi-lightbulb"></i> How Journal Voucher Works</div>
                        <div class="jv-card-body" style="padding: 1rem 1.5rem;">
                            <ul style="margin:0; padding-left:1.25rem; color:#64748b; font-size:0.82rem; line-height:1.9;">
                                <li><strong>Credit Side</strong> — The customer who owes money. Their ledger balance is <strong>reduced</strong> (payment received).</li>
                                <li><strong>Debit Side</strong> — The vendor to whom money is now owed. Their payable balance is <strong>reduced</strong>.</li>
                                <li>No physical cash moves. This is a <strong>book adjustment</strong> only.</li>
                                <li>Both ledgers are updated <strong>simultaneously</strong> in a single transaction.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn-save" id="submitBtn">
                            <i class="bi bi-check2-all"></i> Confirm & Post Journal Voucher
                        </button>
                        <a href="{{ route('journal.vouchers.index') }}" class="btn-outline-link" style="width:auto; white-space:nowrap;">
                            <i class="bi bi-list-ul"></i> View All JVs
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

    function formatNum(n) {
        return parseFloat(n || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function loadPartyList(side, type, branchId) {
        var $select = $('#' + side + '_party_id');
        $select.html('<option value="" class="text-muted">Loading...</option>');

        if (!type) {
            $select.html('<option value="">— Select Party —</option>');
            return;
        }

        $.getJSON('{{ route("journal.party.list") }}', { type: type, branch_id: branchId }, function (data) {
            var html = '<option value="">— Select ' + (type === 'vendor' ? 'Vendor' : 'Customer') + ' —</option>';
            data.forEach(function (item) {
                html += '<option value="' + item.id + '" data-bal="' + (item.closing_balance || 0) + '" data-mobile="' + (item.mobile || '') + '">' + item.text + '</option>';
            });
            $select.html(html);
        }).fail(function () {
            $select.html('<option value="">Error loading data</option>');
        });
    }

    function updateBalanceStrip(side) {
        var $opt = $('#' + side + '_party_id').find(':selected');
        var val  = $opt.val();
        var bal  = parseFloat($opt.data('bal')) || 0;
        var mob  = $opt.data('mobile') || '—';
        var name = $opt.text();

        if (!val) {
            $('#' + side + '_balance_strip').hide();
            $('#' + side + '_tel').val('');
            // Update diagram
            var diagId = side === 'credit' ? '#diagram_credit' : '#diagram_debit';
            $(diagId).text('— Select Party —');
            return;
        }

        $('#' + side + '_tel').val(mob);
        $('#' + side + '_strip_name').text(name);

        var $balEl = $('#' + side + '_strip_bal');
        $balEl.text('PKR ' + formatNum(bal));
        $balEl.removeClass('text-green text-red').addClass(bal >= 0 ? 'text-green' : 'text-red');
        $('#' + side + '_balance_strip').show();

        // Update live diagram
        var diagId = side === 'credit' ? '#diagram_credit' : '#diagram_debit';
        $(diagId).text(name);
    }

    // ── Party Type Change ──
    $('#credit_party_type, #debit_party_type').on('change', function () {
        var side     = $(this).attr('id').replace('_party_type', '');
        var type     = $(this).val();
        var branchId = $('#branch_id').val() || '';
        loadPartyList(side, type, branchId);
        $('#' + side + '_balance_strip').hide();
        $('#' + side + '_tel').val('');
    });

    // ── Branch Change → Reload both sides ──
    $('#branch_id').on('change', function () {
        var branchId = $(this).val() || '';
        ['credit', 'debit'].forEach(function (side) {
            var type = $('#' + side + '_party_type').val();
            if (type) loadPartyList(side, type, branchId);
        });
    });

    // ── Party Selected ──
    $('#credit_party_id, #debit_party_id').on('change', function () {
        var side = $(this).attr('id').replace('_party_id', '');
        updateBalanceStrip(side);
    });

    // ── Amount → Diagram ──
    $('#amount_field').on('input', function () {
        var val = parseFloat($(this).val()) || 0;
        $('#diagram_amount').text('PKR ' + formatNum(val));
    });

    // ── Narration Toggle ──
    $('.narrationSelect').on('change', function () {
        var $input = $(this).closest('.mb-3, .col-md-6').find('.narrationInput');
        if ($(this).val() === '') {
            $input.show().attr('required', true).focus();
        } else {
            $input.hide().removeAttr('required').val('');
        }
    });

    // ── Form Validation ──
    $('#jvForm').on('submit', function () {
        var amount = parseFloat($('#amount_field').val()) || 0;
        if (amount <= 0) {
            alert('Amount must be greater than zero.');
            return false;
        }

        var creditType = $('#credit_party_type').val();
        var debitType  = $('#debit_party_type').val();
        var creditId   = $('#credit_party_id').val();
        var debitId    = $('#debit_party_id').val();

        if (creditType === debitType && creditId === debitId) {
            alert('Credit and Debit parties cannot be the same.');
            return false;
        }

        $('#submitBtn').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Processing...');
        return true;
    });
});
</script>
@endsection
