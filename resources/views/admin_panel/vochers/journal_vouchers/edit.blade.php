@extends('admin_panel.layout.app')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

    .jv-page { background: #f0f4f8; min-height: 100vh; padding: 1.5rem; }

    /* -- Header -- */
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

    /* -- Double-Entry Info Banner -- */
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

    /* -- Cards -- */
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

    /* -- Form Controls -- */
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

    /* -- Balance Strip -- */
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

    /* -- Amount Hero Box -- */
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

    /* -- Buttons -- */
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

    /* -- Alert -- */
    .jv-alert { border-radius: 10px; padding: 0.85rem 1.2rem; font-size: 0.875rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .jv-alert.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .jv-alert.error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .jv-alert.warning { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
</style>

<div class="jv-page">
    <div class="container-fluid" style="max-width: 1200px;">

        {{-- Header --}}
        <div class="jv-header">
            <div>
                <h3><i class="bi bi-pencil-square me-2"></i>Edit Journal Voucher</h3>
                <p>Transfer balance between parties or adjust accounts with automatic ledger reversal and re-posting.</p>
            </div>
            <div class="jv-badge">#{{ $jv->jvid }}</div>
        </div>

        @if(session('success'))
            <div class="jv-alert success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="jv-alert error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
        @endif

        <div class="jv-alert warning">
            <i class="bi bi-info-circle-fill"></i>
            <span><strong>ERP Double-Entry Sync:</strong> Updating this voucher will reverse previously posted debit and credit ledger entries for voucher #{{ $jv->jvid }} and re-apply new entries.</span>
        </div>

        <form action="{{ route('journal.vouchers.update', $jv->id) }}" method="POST" id="jvForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="jvid" value="{{ $jv->jvid }}">

            {{-- -- 1. META CARD -- --}}
            <div class="jv-card">
                <div class="jv-card-header meta">
                    <i class="bi bi-calendar3"></i> Voucher Details
                </div>
                <div class="jv-card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Voucher Date <span class="text-danger">*</span></label>
                            <input type="date" name="voucher_date" class="form-control" value="{{ $jv->voucher_date }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Narration <span class="text-danger">*</span></label>
                            <select name="narration_id[]" id="narration_select" class="form-select" required>
                                <option value="">— Select Narration —</option>
                                @foreach ($narrations as $id => $nar)
                                    <option value="{{ $id }}" {{ (string)$selectedNarration === (string)$id ? 'selected' : '' }}>{{ $nar }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference / Cheque #</label>
                            @php
                                $refs = json_decode($jv->reference_no, true) ?? [];
                                $firstRef = is_array($refs) ? ($refs[0] ?? '') : $refs;
                            @endphp
                            <input type="text" name="reference_no[]" class="form-control" value="{{ $firstRef }}" placeholder="Optional ref #">
                        </div>
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
                        <div class="col-12">
                            <label class="form-label">Remarks / Description</label>
                            <input type="text" name="remarks" class="form-control" value="{{ $jv->remarks }}" placeholder="e.g. Customer payment adjustment against Vendor bill...">
                        </div>
                    </div>
                </div>
            </div>

            {{-- -- 2. DEBIT SIDE (Vendor Debit) -- --}}
            <div class="jv-card">
                <div class="jv-card-header debit">
                    <i class="bi bi-arrow-down-left-circle-fill"></i> DEBIT SIDE &mdash; Party Debited
                </div>
                <div class="jv-card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Debit Party Type <span class="text-danger">*</span></label>
                            <select name="debit_party_type" id="debit_party_type" class="form-select" required>
                                <option value="vendor"   {{ $jv->debit_party_type === 'vendor' ? 'selected' : '' }}>Vendor (Payable Reduced)</option>
                                <option value="customer" {{ $jv->debit_party_type === 'customer' ? 'selected' : '' }}>Customer (Receivable Increased)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" id="debit_party_label">Select Party <span class="text-danger">*</span></label>
                            <select name="debit_party_id" id="debit_party_id" class="form-select" required data-selected="{{ $jv->debit_party_id }}">
                                <option value="{{ $jv->debit_party_id }}" selected>{{ $debitPartyName ?: 'Selected Party' }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Mobile</label>
                            <input type="text" id="debit_mobile" class="form-control" readonly placeholder="Auto-filled">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Current Balance</label>
                            <input type="number" id="debit_balance" class="form-control" readonly placeholder="0.00">
                        </div>
                    </div>

                    <div class="balance-strip" id="debitBalanceStrip" style="{{ $debitPartyName ? '' : 'display:none;' }}">
                        <div class="bl-item">
                            <span class="bl-label">Party Name</span>
                            <span class="bl-value" id="strip_debit_name">{{ $debitPartyName ?: '—' }}</span>
                        </div>
                        <div class="bl-item">
                            <span class="bl-label">Current Balance</span>
                            <span class="bl-value" id="strip_debit_bal">—</span>
                        </div>
                        <div class="bl-item">
                            <span class="bl-label">Mobile</span>
                            <span class="bl-value" id="strip_debit_mobile">—</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- -- 3. CREDIT SIDE (Customer Credit) -- --}}
            <div class="jv-card">
                <div class="jv-card-header credit">
                    <i class="bi bi-arrow-up-right-circle-fill"></i> CREDIT SIDE &mdash; Party Credited
                </div>
                <div class="jv-card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Credit Party Type <span class="text-danger">*</span></label>
                            <select name="credit_party_type" id="credit_party_type" class="form-select" required>
                                <option value="customer" {{ $jv->credit_party_type === 'customer' ? 'selected' : '' }}>Customer (Receivable Reduced)</option>
                                <option value="vendor"   {{ $jv->credit_party_type === 'vendor' ? 'selected' : '' }}>Vendor (Payable Increased)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" id="credit_party_label">Select Party <span class="text-danger">*</span></label>
                            <select name="credit_party_id" id="credit_party_id" class="form-select" required data-selected="{{ $jv->credit_party_id }}">
                                <option value="{{ $jv->credit_party_id }}" selected>{{ $creditPartyName ?: 'Selected Party' }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Mobile</label>
                            <input type="text" id="credit_mobile" class="form-control" readonly placeholder="Auto-filled">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Current Balance</label>
                            <input type="number" id="credit_balance" class="form-control" readonly placeholder="0.00">
                        </div>
                    </div>

                    <div class="balance-strip" id="creditBalanceStrip" style="{{ $creditPartyName ? '' : 'display:none;' }}">
                        <div class="bl-item">
                            <span class="bl-label">Party Name</span>
                            <span class="bl-value" id="strip_credit_name">{{ $creditPartyName ?: '—' }}</span>
                        </div>
                        <div class="bl-item">
                            <span class="bl-label">Current Balance</span>
                            <span class="bl-value" id="strip_credit_bal">—</span>
                        </div>
                        <div class="bl-item">
                            <span class="bl-label">Mobile</span>
                            <span class="bl-value" id="strip_credit_mobile">—</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- -- 4. AMOUNT HERO + ACTIONS -- --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="amount-hero">
                        <label><i class="bi bi-cash-stack me-1"></i> Transfer Amount (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0.01" value="{{ $jv->amount }}" placeholder="0.00" required>
                    </div>
                </div>
                <div class="col-md-6 d-flex flex-column justify-content-center gap-2">
                    <button type="submit" class="btn-save">
                        <i class="bi bi-check2-all"></i> Save Changes &amp; Synchronize Ledgers
                    </button>
                    <a href="{{ route('journal.vouchers.index') }}" class="btn-outline-link">
                        <i class="bi bi-arrow-left"></i> Cancel &amp; Back to Journal Vouchers
                    </a>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {

    function loadPartyOptions(side, selectedId) {
        var type     = $('#' + side + '_party_type').val();
        var branchId = $('#branch_id').val() || '';
        var $select  = $('#' + side + '_party_id');

        if (!type) {
            $select.html('<option value="">— First select type —</option>');
            return;
        }

        $select.html('<option value="">Loading...</option>');

        $.ajax({
            url: "{{ route('journal.party.list') }}",
            type: "GET",
            data: { type: type, branch_id: branchId },
            success: function (data) {
                var options = '<option value="">— Select Party —</option>';
                $.each(data, function (idx, item) {
                    var isSel = (selectedId && String(selectedId) === String(item.id)) ? 'selected' : '';
                    options += `<option value="${item.id}" data-mobile="${item.mobile || ''}" data-balance="${item.closing_balance || 0}" ${isSel}>${item.text}</option>`;
                });
                $select.html(options);

                if (selectedId) {
                    $select.trigger('change');
                }
            }
        });
    }

    // Bind change events
    $('#debit_party_type').on('change', function () {
        loadPartyOptions('debit', null);
    });

    $('#credit_party_type').on('change', function () {
        loadPartyOptions('credit', null);
    });

    $('#branch_id').on('change', function () {
        loadPartyOptions('debit', $('#debit_party_id').val());
        loadPartyOptions('credit', $('#credit_party_id').val());
    });

    // Party select change updates balance & strip
    function handlePartyChange(side) {
        var $opt    = $('#' + side + '_party_id').find(':selected');
        var mobile  = $opt.data('mobile') || '';
        var balance = parseFloat($opt.data('balance') || 0);

        $('#' + side + '_mobile').val(mobile);
        $('#' + side + '_balance').val(balance.toFixed(2));

        if ($('#' + side + '_party_id').val()) {
            $('#strip_' + side + '_name').text($opt.text());
            $('#strip_' + side + '_bal').text('Rs. ' + balance.toLocaleString('en-US', { minimumFractionDigits: 2 }));
            $('#strip_' + side + '_mobile').text(mobile || 'N/A');
            $('#' + side + 'BalanceStrip').slideDown(200);
        } else {
            $('#' + side + 'BalanceStrip').slideUp(200);
        }
    }

    $('#debit_party_id').on('change', function () { handlePartyChange('debit'); });
    $('#credit_party_id').on('change', function () { handlePartyChange('credit'); });

    // Initial load
    var initDebitId = $('#debit_party_id').data('selected');
    var initCreditId = $('#credit_party_id').data('selected');
    if (initDebitId) loadPartyOptions('debit', initDebitId);
    if (initCreditId) loadPartyOptions('credit', initCreditId);
});
</script>
@endsection
