@extends('admin_panel.layout.app')

@section('css')
<style>
    :root {
        --coa-navy: #1e3a5f;
        --coa-navy-dark: #0f1f38;
        --coa-navy-light: #2c5282;
        --coa-gold: #c8973a;
        --coa-emerald: #059669;
        --coa-emerald-dark: #047857;
        --coa-border: #cbd5e1;
    }

    .pur-wrapper {
        padding: 10px 0 40px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* 1. Header Banner */
    .pur-header-bar {
        background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 60%, var(--coa-navy-light) 100%);
        border-radius: 10px;
        padding: 16px 22px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        box-shadow: 0 4px 15px rgba(15, 31, 56, 0.15);
        margin-bottom: 20px;
    }

    .pur-header-icon {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        color: var(--coa-gold);
        border: 1px solid rgba(200, 151, 58, 0.3);
        flex-shrink: 0;
    }

    .pur-header-title {
        font-size: 18px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        line-height: 1.2;
    }

    .pur-header-sub {
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
        margin-bottom: 6px;
        display: block;
    }

    .fi {
        width: 100%;
        height: 38px;
        padding: 6px 12px;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13px;
        transition: all 0.2s;
        background-color: #ffffff;
    }

    .fi:focus {
        outline: none;
        border-color: var(--coa-navy);
        box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
    }

    .fi[readonly] {
        background-color: #f1f5f9;
        color: #475569;
    }

    .payment-badge {
        padding: 6px 14px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 700;
        font-size: 12px;
        border: 1.5px solid #cbd5e1;
        background: #f8fafc;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .payment-badge.active {
        background: #ecfdf5;
        color: #065f46;
        border-color: #059669;
    }

    .payment-badge.active-navy {
        background: #e0f2fe;
        color: #0369a1;
        border-color: #0284c7;
    }

    /* Table Styling */
    #itemsTable {
        border-collapse: collapse;
        width: 100%;
    }

    #itemsTable thead th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 8px;
        border: 1px solid #1e3a5f;
    }

    #itemsTable tbody td {
        padding: 6px 8px;
        vertical-align: middle;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }

    #itemsTable .fi {
        height: 34px !important;
        padding: 4px 8px !important;
        font-size: 12.5px !important;
        border-radius: 5px !important;
    }

    .summary-card {
        background: #ffffff;
        border-radius: 9px;
        padding: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .summary-label {
        font-size: 12.5px;
        font-weight: 700;
        color: #64748b;
    }

    .summary-value {
        font-weight: 800;
        color: var(--coa-navy-dark);
        font-size: 14px;
        font-family: monospace;
    }

    .summary-total {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 2px dashed #cbd5e1;
    }

    .summary-total .summary-value {
        font-size: 18px;
        color: #047857;
    }

    .btn-submit-pur {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: white;
        border: none;
        border-radius: 7px;
        padding: 11px 20px;
        font-weight: 800;
        font-size: 13.5px;
        width: 100%;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
        transition: all 0.2s;
    }

    .btn-submit-pur:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.35);
        color: #ffffff;
    }

    /* Select2 Tweaks */
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 10px !important;
        font-size: 12.5px !important;
        background-color: #ffffff !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b !important;
        line-height: 36px !important;
        padding-left: 0 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }

    #itemsTable .select2-container .select2-selection--single {
        height: 34px !important;
        border-radius: 5px !important;
    }

    #itemsTable .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
    }

    #itemsTable .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 32px !important;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <div class="pur-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Corporate Header Bar --}}
            <div class="pur-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="pur-header-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <h4 class="pur-header-title">Local Market Purchase</h4>
                        <div class="pur-header-sub">
                            <span><i class="fas fa-receipt mr-1" style="color: var(--coa-gold);"></i> Direct Market Purchases & Spot Stock Addition &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('store') }}" class="btn btn-sm btn-light font-weight-bold text-dark border">
                        <i class="fas fa-plus mr-1 text-primary"></i> Create Item
                    </a>
                    <a href="{{ route('Purchase.home') }}" class="btn btn-sm btn-outline-light font-weight-bold">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Purchases
                    </a>
                </div>
            </div>

            <form action="{{ route('purchase.storeLocal') }}" method="POST" id="localPurchaseForm">
                @csrf

                {{-- Error & Success Messages --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-3 border-0 shadow-sm" role="alert" style="border-radius: 8px;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <div>
                                <strong class="mb-1">Please fix the following errors:</strong>
                                <ul class="mb-0 small pl-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-warning alert-dismissible fade show mb-3 border-0 shadow-sm" role="alert" style="border-radius: 8px;">
                        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                {{-- 2. Form Card --}}
                <div class="card shadow-sm border-0 mb-3" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                    <div class="card-body p-3 p-lg-4">

                        <!-- Header Row: Purchase From & Metadata -->
                        <div class="row g-3 mb-3">
                            {{-- ===== VENDOR SOURCE TOGGLE ===== --}}
                            <div class="col-12 mb-2">
                                <label class="f-label">Purchase From Source</label>
                                <div class="d-flex gap-2">
                                    <label class="payment-badge active" id="badgeLocalMarket">
                                        <input type="radio" name="_vendor_mode" value="local" class="d-none" checked>
                                        <i class="fas fa-store mr-1 text-success"></i> Local Market (Walk-In Shop)
                                    </label>
                                    <label class="payment-badge" id="badgeRegisteredVendor">
                                        <input type="radio" name="_vendor_mode" value="vendor" class="d-none">
                                        <i class="fas fa-building mr-1 text-primary"></i> Registered Vendor (Ledger Update)
                                    </label>
                                </div>
                            </div>

                            {{-- LOCAL MARKET: free-text shop name --}}
                            <div class="col-md-3" id="localMarketField">
                                <label class="f-label"><i class="fas fa-store mr-1 text-muted"></i> Vendor / Supplier / Shop <span class="text-danger">*</span></label>
                                <input type="text" name="local_vendor_name" id="vendor_name_text" class="fi" placeholder="Enter Local Market Shop Name">
                            </div>

                            {{-- REGISTERED VENDOR: select2 dropdown --}}
                            <div class="col-md-3" id="registeredVendorField" style="display:none;">
                                <label class="f-label"><i class="fas fa-building mr-1 text-muted"></i> Select Registered Vendor <span class="text-danger">*</span></label>
                                <select name="vendor_id_select" id="vendor_id_select" class="fi select2">
                                    <option value="">— Select Vendor —</option>
                                    @foreach($Vendor as $v)
                                        <option value="{{ $v->id }}"
                                            data-name="{{ $v->name }}"
                                            data-phone="{{ $v->phone ?? '' }}">
                                            {{ $v->name }} @if($v->phone) ({{ $v->phone }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                    <i class="fas fa-info-circle mr-1"></i> Vendor ledger will be credited automatically
                                </small>
                            </div>

                            <div class="col-md-3">
                                <label class="f-label"><i class="fas fa-code-branch mr-1 text-muted"></i> Branch <span class="text-danger">*</span></label>
                                @if($isSuperAdmin)
                                    <select name="branch_id" id="branch_id" class="fi select2" required>
                                        @foreach($Branch as $b)
                                            <option value="{{ $b->id }}" {{ $currentBranch == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" class="fi" value="{{ Auth::user()->branch->name ?? 'N/A' }}" readonly>
                                    <input type="hidden" name="branch_id" value="{{ $currentBranch }}">
                                @endif
                            </div>

                            <div class="col-md-3">
                                <label class="f-label"><i class="fas fa-warehouse mr-1 text-muted"></i> Warehouse / Destination <span class="text-danger">*</span></label>
                                <select name="warehouse_id" id="warehouse_id" class="fi select2">
                                    <option value="">🏢 Direct to Shop (Branch Display)</option>
                                    @foreach($Warehouse as $w)
                                        <option value="{{ $w->id }}">
                                            @php $br = $w->branches->first(); @endphp
                                            [{{ $br->name ?? 'Global' }}] - {{ $w->warehouse_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="f-label"><i class="fas fa-calendar-alt mr-1 text-muted"></i> Invoice Date</label>
                                <input type="date" name="purchase_date" class="fi" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered align-middle mb-0" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 22%;">Product Details <span class="text-danger">*</span></th>
                                        <th style="width: 11%;">Packing Type</th>
                                        <th style="width: 20%; text-align: center;">Packing Details</th>
                                        <th style="width: 9%; text-align: center;">Total Qty <span class="text-danger">*</span></th>
                                        <th style="width: 12%; text-align: right;">Cost Price <span class="text-danger">*</span></th>
                                        <th style="width: 10%;">Disc</th>
                                        <th style="width: 9%; text-align: right;">Disc Amt</th>
                                        <th style="width: 13%; text-align: right;">Line Total</th>
                                        <th style="width: 4%; text-align: center;"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsList">
                                    <tr class="item-row">
                                        <td>
                                            <select name="product_id[]" class="fi select2 product-select" required>
                                                <option value="">Select Product</option>
                                                @foreach($Products as $p)
                                                    <option value="{{ $p->id }}" data-price="{{ $p->last_purchase_price }}" data-unit="{{ $p->unit->name ?? 'unit' }}">{{ $p->item_name }} ({{ $p->item_code }})</option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="unit[]" class="unit-input" value="unit">
                                        </td>
                                        
                                        <!-- PACKING TYPE -->
                                        <td>
                                            <select name="packing_type[]" class="fi packing-type-select">
                                                <option value="Standard" selected>Standard</option>
                                                <option value="Customize">Customize</option>
                                            </select>
                                        </td>
                                        
                                        <!-- PACKING DETAILS -->
                                        <td>
                                            <!-- Standard View -->
                                            <div class="standard-packing-view text-center">
                                                <input type="text" class="fi text-center" value="Piece" readonly style="background-color: #f1f5f9; font-size: 11.5px;">
                                            </div>
                                            <!-- Customize View -->
                                            <div class="customize-packing-view gap-1" style="display: none;">
                                                <div class="flex-grow-1 text-center" style="width: 33%;">
                                                    <div style="font-size: 9.5px; color: #64748b; font-weight: 700; text-transform: uppercase;">Packs</div>
                                                    <input type="number" name="packing_qty[]" class="fi text-center pack-qty-input" step="1" min="0" value="0" placeholder="Packs">
                                                </div>
                                                <div class="flex-grow-1 text-center" style="width: 33%;">
                                                    <div style="font-size: 9.5px; color: #64748b; font-weight: 700; text-transform: uppercase;">Pcs/Pk</div>
                                                    <input type="number" name="item_per_piece[]" class="fi text-center ipp-input" step="1" min="0" value="0" placeholder="Pcs/Pack">
                                                </div>
                                                <div class="flex-grow-1 text-center" style="width: 33%;">
                                                    <div style="font-size: 9.5px; color: #64748b; font-weight: 700; text-transform: uppercase;">Loose</div>
                                                    <input type="number" name="loose_piece[]" class="fi text-center loose-pcs-input" step="1" min="0" value="0" placeholder="Loose">
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="text-center">
                                            <input type="number" name="qty[]" class="fi text-center qty-input font-weight-bold" style="font-family: monospace;" value="1" min="1" step="0.01" required>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0" style="padding: 2px 6px; font-size: 11px;">Rs.</span>
                                                <input type="number" name="price[]" class="fi price-input border-start-0 text-end font-weight-bold" style="font-family: monospace;" step="0.01" min="0" value="0" required placeholder="0.00">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group" style="flex-wrap: nowrap;">
                                                <input type="number" class="fi form-control disc-input-visual text-end" style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: 0;" step="0.01" min="0" value="0">
                                                <button class="btn btn-outline-secondary disc-type-toggle" type="button" data-type="amount" style="border-top-right-radius: 5px; border-bottom-right-radius: 5px; border: 1.5px solid #cbd5e1; border-left: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold; font-size: 11px; width: 36px; padding: 0;">Rs</button>
                                                <input type="hidden" class="disc-type-input" value="amount">
                                                <input type="hidden" name="item_discount[]" class="disc-input-hidden" value="0">
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" class="fi text-end font-weight-bold disc-amt-display" value="0.00" readonly style="background:#f8fafc; color:#64748b; font-family: monospace;">
                                        </td>
                                        <td class="text-end">
                                            <input type="text" class="fi text-end font-weight-bold line-total text-success" value="0.00" readonly style="background:#f0fdf4; font-family: monospace;">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row" style="padding: 2px 6px; border-radius: 5px;"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold mb-4" id="addRow" style="border-radius: 6px;">
                            <i class="fas fa-plus mr-1"></i> ADD ANOTHER ITEM
                        </button>

                        <div class="row g-4">
                            <!-- Left: Notes & Payment -->
                            <div class="col-lg-7">
                                <label class="f-label">Internal Remarks / Note</label>
                                <textarea name="note" class="fi mb-4" rows="2" placeholder="Enter purchase remarks, bill reference number, or terms..."></textarea>
                                
                                <label class="f-label">Payment Information</label>
                                <div class="d-flex gap-2 mb-3">
                                    <label class="payment-badge active" id="badgeLater">
                                        <input type="radio" name="payment_type" value="pay_later" class="d-none" checked>
                                        <i class="fas fa-credit-card mr-1 text-danger"></i> Pay Later (Credit Balance)
                                    </label>
                                    <label class="payment-badge" id="badgeNow">
                                        <input type="radio" name="payment_type" value="pay_now" class="d-none">
                                        <i class="fas fa-money-bill-wave mr-1 text-success"></i> Pay Now (Instant Settlement)
                                    </label>
                                </div>

                                <div id="paymentFields" style="display: none;" class="bg-light p-3 rounded border mb-3">
                                    <label class="f-label mb-2 text-primary">Payment Accounts & Amounts <span class="text-danger">*</span></label>
                                    <div id="rvWrapper">
                                        <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                                            <div class="flex-grow-1">
                                                <select class="form-control fi rv-account" name="payment_account_id[]">
                                                    <option value="" disabled selected>Select Payment Source Account...</option>
                                                    @foreach($bankAccounts as $acc)
                                                        <option value="{{ $acc->id }}">{{ $acc->title }} (Bal: Rs. {{ number_format($acc->opening_balance, 2) }})</option>
                                                    @endforeach
                                                </select>
                                                <div class="account-balance-wrapper mt-1 ml-1" style="display:none; font-size: 11px;">
                                                    <span class="text-muted">Available Balance:</span> 
                                                    <span class="font-weight-bold text-success balance-amt">0.00</span>
                                                </div>
                                            </div>
                                            <div style="width: 140px;">
                                                <input type="number" name="payment_amount[]" class="fi rv-amount text-end font-weight-bold" style="font-family: monospace;" step="0.01" placeholder="0.00">
                                            </div>
                                            <div style="width: 70px;">
                                                <button type="button" class="btn btn-sm btn-outline-primary w-100 font-weight-bold" id="btnAddRV" style="height: 38px;">+ Add</button>
                                            </div>
                                        </div>
                                        <div class="text-end mt-2 pt-2 border-top">
                                            <span class="mr-2 text-muted font-weight-bold" style="font-size: 12px;">Total Paid:</span>
                                            <span class="font-weight-bold text-success" id="totalPaidDisplay" style="font-size: 14px; font-family: monospace;">Rs. 0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Totals Summary -->
                            <div class="col-lg-5">
                                <div class="summary-card shadow-sm h-100">
                                    <div class="summary-row">
                                        <span class="summary-label">Items Subtotal</span>
                                        <span class="summary-value" id="dispSubtotal">Rs. 0.00</span>
                                    </div>
                                    <div class="summary-row mt-2">
                                        <span class="summary-label">Overall Discount</span>
                                        <div style="width: 120px;">
                                            <input type="number" name="discount" id="overallDiscount" class="fi text-end py-1 font-weight-bold" style="font-family: monospace;" step="0.01" value="0">
                                        </div>
                                    </div>
                                    <div class="summary-row mt-2">
                                        <span class="summary-label">Extra Charges (Freight/Misc)</span>
                                        <div style="width: 120px;">
                                            <input type="number" name="extra_cost" id="extraCost" class="fi text-end py-1 font-weight-bold" style="font-family: monospace;" step="0.01" value="0">
                                        </div>
                                    </div>
                                    <div class="summary-row summary-total mt-3">
                                        <span class="summary-label font-weight-bold text-dark" style="font-size: 13.5px;">Invoice Net Total</span>
                                        <span class="summary-value" id="dispNet">Rs. 0.00</span>
                                        <input type="hidden" name="net_amount" id="netAmount" value="0">
                                    </div>
                                    <div class="summary-row outstanding-row mt-2" style="display: none;">
                                        <span class="summary-label text-danger">Outstanding Due Balance</span>
                                        <span class="summary-value text-danger" id="dispOutstanding">Rs. 0.00</span>
                                    </div>

                                    <button type="submit" class="btn btn-submit-pur mt-3">
                                        <i class="fas fa-check-double mr-2"></i> POST PURCHASE & ADD STOCK
                                    </button>
                                    <p class="text-center text-muted mt-2 mb-0" style="font-size: 11px;">
                                        Stock will be updated across selected warehouse/branch immediately.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    function initSelect2() {
        if ($.fn.select2) {
            $('.select2').select2({ width: '100%' });
        }
    }
    initSelect2();

    // ===== VENDOR MODE TOGGLE =====
    function switchVendorMode(mode) {
        if (mode === 'local') {
            $('#localMarketField').show();
            $('#registeredVendorField').hide();
            $('#vendor_name_text').prop('required', true);
            $('#vendor_id_select').prop('required', false);
        } else {
            $('#localMarketField').hide();
            $('#registeredVendorField').show();
            $('#vendor_name_text').prop('required', false).val('');
            $('#vendor_id_select').prop('required', true);
        }
    }

    $('#badgeLocalMarket').click(function() {
        $('input[name="_vendor_mode"][value="local"]').prop('checked', true);
        $(this).addClass('active');
        $('#badgeRegisteredVendor').removeClass('active');
        switchVendorMode('local');
    });

    $('#badgeRegisteredVendor').click(function() {
        $('input[name="_vendor_mode"][value="vendor"]').prop('checked', true);
        $(this).addClass('active');
        $('#badgeLocalMarket').removeClass('active');
        switchVendorMode('vendor');
    });

    $('#addRow').click(function() {
        var newRow = $('.item-row:first').clone();
        newRow.find('input').not('.disc-type-input, .disc-input-hidden, .unit-input, .standard-packing-view input').val(0);
        newRow.find('.qty-input').val(1);
        newRow.find('.line-total').val('0.00');
        newRow.find('.disc-amt-display').val('0.00');
        newRow.find('.unit-input').val('unit');
        
        // Reset packing type to Standard and clear customize inputs
        newRow.find('.packing-type-select').val('Standard');
        newRow.find('.standard-packing-view').show();
        newRow.find('.customize-packing-view').removeClass('d-flex').hide();
        newRow.find('.qty-input').prop('readonly', false).css('background-color', '#fff');
        
        // Reset discount toggle
        const toggleBtn = newRow.find('.disc-type-toggle');
        toggleBtn.attr('data-type', 'amount').text('Rs');
        newRow.find('.disc-type-input').val('amount');
        newRow.find('.disc-input-hidden').val(0);

        newRow.find('.select2-container').remove();
        $('#itemsList').append(newRow);
        initSelect2();
    });

    // Remove Row
    $(document).on('click', '.remove-row', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            recalc();
        }
    });

    // Product Selection -> Auto Price
    $(document).on('change', '.product-select', function() {
        var price = $(this).find(':selected').data('price') || 0;
        var unit = $(this).find(':selected').data('unit') || 'unit';
        $(this).closest('tr').find('.price-input').val(price);
        $(this).closest('tr').find('.unit-input').val(unit);
        $(this).closest('tr').find('.standard-packing-view input').val(unit);
        recalc();
    });

    // --- Packing Logic ---
    $(document).on('change', '.packing-type-select', function() {
        const row = $(this).closest('tr');
        const type = $(this).val().toLowerCase();
        
        if (type === 'standard') {
            row.find('.standard-packing-view').show();
            row.find('.customize-packing-view').removeClass('d-flex').hide();
            row.find('.qty-input').prop('readonly', false).css('background-color', '#fff');
            row.find('.pack-qty-input, .ipp-input, .loose-pcs-input').val(0);
        } else {
            row.find('.standard-packing-view').hide();
            row.find('.customize-packing-view').addClass('d-flex').show();
            row.find('.qty-input').prop('readonly', true).css('background-color', '#eef2ff');
        }
        recalc();
    });

    $(document).on('input', '.pack-qty-input, .ipp-input, .loose-pcs-input', function() {
        const row = $(this).closest('tr');
        const packingType = row.find('.packing-type-select').val().toLowerCase();
        
        if (packingType === 'customize') {
            const packQty = parseFloat(row.find('.pack-qty-input').val()) || 0;
            const ipp = parseFloat(row.find('.ipp-input').val()) || 0;
            const loose = parseFloat(row.find('.loose-pcs-input').val()) || 0;
            const totalQty = (packQty * ipp) + loose;
            row.find('.qty-input').val(totalQty);
        }
        recalc();
    });

    function recalc() {
        let subtotal = 0;

        $('.item-row').each(function() {
            const qty   = parseFloat($(this).find('.qty-input').val())   || 0;
            const price = parseFloat($(this).find('.price-input').val()) || 0;
            const discVal = parseFloat($(this).find('.disc-input-visual').val()) || 0;
            const discType = $(this).find('.disc-type-input').val() || 'amount';

            let discAmt = 0;
            if (discType === 'percent') {
                discAmt = (qty * price) * (discVal / 100);
            } else {
                discAmt = discVal;
            }
            
            $(this).find('.disc-input-hidden').val(discAmt.toFixed(2));
            $(this).find('.disc-amt-display').val(discAmt.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            const lineTotal = (qty * price) - discAmt;
            $(this).find('.line-total').val(lineTotal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            subtotal += lineTotal;
        });

        $('#dispSubtotal').text('Rs. ' + subtotal.toLocaleString('en-PK', {minimumFractionDigits: 2}));

        const overDisc = parseFloat($('#overallDiscount').val()) || 0;
        const extra    = parseFloat($('#extraCost').val())        || 0;
        const net      = (subtotal - overDisc) + extra;

        $('#dispNet').text('Rs. ' + net.toLocaleString('en-PK', {minimumFractionDigits: 2}));
        $('#netAmount').val(net.toFixed(2));
        
        if ($('input[name="payment_type"][value="pay_now"]').is(':checked')) {
            let firstAmount = $('.rv-amount').first();
            if(!firstAmount.val() || parseFloat(firstAmount.val()) > 0) {
                firstAmount.val(net.toFixed(2));
            }
        }
        
        calcPayments();
    }

    $(document).on('input', '.qty-input, .price-input, .disc-input-visual, #overallDiscount, #extraCost', recalc);

    $(document).on('click', '.disc-type-toggle', function() {
        let type = $(this).attr('data-type');
        if(type === 'amount') {
            $(this).attr('data-type', 'percent');
            $(this).text('%');
            $(this).siblings('.disc-type-input').val('percent');
        } else {
            $(this).attr('data-type', 'amount');
            $(this).text('Rs');
            $(this).siblings('.disc-type-input').val('amount');
        }
        $(this).siblings('.disc-input-visual').focus();
        recalc();
    });

    $('#badgeLater').click(function() {
        $('input[name="payment_type"][value="pay_later"]').prop('checked', true);
        $(this).addClass('active');
        $('#badgeNow').removeClass('active');
        $('#paymentFields').slideUp();
        $('.outstanding-row').slideUp();
        $('.rv-amount').val('');
        calcPayments();
    });

    $('#badgeNow').click(function() {
        $('input[name="payment_type"][value="pay_now"]').prop('checked', true);
        $(this).addClass('active');
        $('#badgeLater').removeClass('active');
        $('#paymentFields').slideDown();
        $('.outstanding-row').slideDown();
        
        const net = parseFloat($('#netAmount').val()) || 0;
        let firstAmount = $('.rv-amount').first();
        if(!firstAmount.val()) {
            firstAmount.val(net.toFixed(2));
            calcPayments();
        }
        firstAmount.focus();
    });

    window.PAYMENT_ACCOUNTS = @json($bankAccounts);

    function loadAccountsInto($select) {
        const currentVal = $select.val();
        let usedAccounts = [];

        $('.rv-account').each(function() {
            const val = $(this).val();
            if (val && this !== $select[0]) {
                usedAccounts.push(String(val));
            }
        });

        let html = '<option value="" disabled selected>Select Source Account...</option>';

        window.PAYMENT_ACCOUNTS.forEach(function(acc) {
            const accId = String(acc.id);
            if (!usedAccounts.includes(accId) || accId === String(currentVal)) {
                html += `<option value="${accId}">${acc.title} (Bal: Rs. ${parseFloat(acc.opening_balance).toLocaleString('en-PK', {minimumFractionDigits: 2})})</option>`;
            }
        });

        $select.html(html);
        if (currentVal) $select.val(currentVal);
    }

    $(document).on('change', '.rv-account', function() {
        $('.rv-account').each(function() {
            loadAccountsInto($(this));
        });
        updateBalances();
    });

    function updateBalances() {
        $('.rv-account').each(function() {
            const val = $(this).val();
            const wrapper = $(this).siblings('.account-balance-wrapper');
            if (val) {
                const acc = window.PAYMENT_ACCOUNTS.find(a => String(a.id) === String(val));
                if (acc) {
                    const bal = parseFloat(acc.opening_balance) || 0;
                    wrapper.find('.balance-amt').text('Rs. ' + bal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    wrapper.slideDown(150);
                } else {
                    wrapper.hide();
                }
            } else {
                wrapper.hide();
            }
        });
    }

    function calcPayments() {
        let totalPaid = 0;
        $('.rv-amount').each(function() {
            totalPaid += parseFloat($(this).val()) || 0;
        });
        
        $('#totalPaidDisplay').text('Rs. ' + totalPaid.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        
        const netAmount = parseFloat($('#netAmount').val()) || 0;
        const outstanding = netAmount - totalPaid;
        $('#dispOutstanding').text('Rs. ' + outstanding.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    $(document).on('input', '.rv-amount', calcPayments);

    $('#btnAddRV').click(function() {
        const $row = $('.rv-row').first().clone();
        $row.find('.rv-account').val('');
        $row.find('.rv-amount').val('');
        
        const $btn = $row.find('button');
        $btn.attr('id', '');
        $btn.removeClass('btn-outline-primary').addClass('btn-outline-danger btn-remove-rv').text('Remove');
        
        const netAmount = parseFloat($('#netAmount').val()) || 0;
        let currentPaid = 0;
        $('.rv-amount').each(function() { currentPaid += parseFloat($(this).val()) || 0; });
        const remaining = Math.max(0, netAmount - currentPaid);
        if (remaining > 0) $row.find('.rv-amount').val(remaining.toFixed(2));
        
        $row.insertBefore($('#totalPaidDisplay').closest('.text-end'));
        
        $('.rv-account').each(function() { loadAccountsInto($(this)); });
        calcPayments();
        updateBalances();
    });

    $(document).on('click', '.btn-remove-rv', function() {
        $(this).closest('.rv-row').remove();
        $('.rv-account').each(function() { loadAccountsInto($(this)); });
        calcPayments();
        updateBalances();
    });

    // Initial calc
    recalc();

    // Dynamic Warehouse Loading by Branch
    $('#branch_id').on('change', function() {
        const branchId = $(this).val();
        if (!branchId) return;

        const $warehouseSelect = $('#warehouse_id');
        $warehouseSelect.prop('disabled', true);
        
        $.ajax({
            url: "{{ route('warehouses-by-branch') }}",
            type: "GET",
            data: { branch_id: branchId },
            success: function(res) {
                let html = '<option value="">🏢 Direct to Shop (Branch Display)</option>';
                if (res && res.length > 0) {
                    res.forEach(function(w) {
                        html += `<option value="${w.id}">🏢 ${w.warehouse_name}</option>`;
                    });
                }
                $warehouseSelect.html(html).prop('disabled', false).trigger('change');
            },
            error: function() {
                $warehouseSelect.prop('disabled', false);
                Swal.fire('Error', 'Warehouses load karne mein masla hua.', 'error');
            }
        });
    });

    @if($isSuperAdmin)
        if($('#branch_id').val()) {
            $('#branch_id').trigger('change');
        }
    @endif

    // Guard: warn if any price is 0
    $('#localPurchaseForm').on('submit', function(e) {
        let emptyPrice = false;
        $('.price-input').each(function() {
            if(parseFloat($(this).val()) <= 0) emptyPrice = true;
        });
        if(emptyPrice) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Check Prices!',
                text: 'Kuch items ka unit price 0 ya khaali hai. Please check karein.'
            });
            return false;
        }

        const mode = $('input[name="_vendor_mode"]:checked').val();
        if (mode === 'vendor') {
            const vid = $('#vendor_id_select').val();
            if (!vid) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Vendor Required', text: 'Please select a registered vendor.' });
                return false;
            }
        } else {
            if (!$('#vendor_name_text').val().trim()) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Shop Name Required', text: 'Please enter vendor/shop name.' });
                return false;
            }
        }
    });
});
</script>
@endsection
