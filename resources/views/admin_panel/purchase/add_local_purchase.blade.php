@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        --accent-color: #f59e0b;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }

    .main-content { background-color: #f8fafc; min-height: 100vh; padding: 1.5rem 0; }
    .premium-card { border: none; border-radius: 1.5rem; box-shadow: var(--card-shadow); background: white; overflow: hidden; margin-bottom: 2rem; width: 100%; }
    .card-header-gradient { background: var(--primary-gradient); padding: 1.5rem 2rem; border: none; }
    .card-title-premium { color: white; font-weight: 800; font-size: 1.5rem; margin: 0; display: flex; align-items: center; gap: 0.75rem; }
    
    .section-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.75rem; display: block; }
    .fi { width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.95rem; transition: all 0.2s; background-color: #f8fafc; }
    .fi:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); background-color: white; }
    .fi[readonly] { background-color: #f1f5f9; color: #475569; cursor: not-allowed; }

    .table-premium { border-collapse: separate; border-spacing: 0 0.5rem; width: 100% !important; }
    .table-premium thead th { background: #f1f5f9; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.7rem; padding: 0.75rem 0.5rem; border: none; }
    .table-premium tbody tr { transition: transform 0.2s, box-shadow 0.2s; }
    .table-premium tbody td { padding: 0.75rem 0.5rem; vertical-align: middle; background: white; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }
    .table-premium tbody td:first-child { border-left: 1px solid #f1f5f9; border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem; }
    .table-premium tbody td:last-child { border-right: 1px solid #f1f5f9; border-top-right-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }

    /* Compact inputs inside premium table */
    .table-premium .fi { padding: 0.4rem 0.4rem !important; font-size: 0.8rem !important; border-radius: 0.5rem !important; }
    .table-premium .select2-container--default .select2-selection--single { height: 32px !important; border-radius: 0.5rem !important; }
    .table-premium .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 30px !important; padding-left: 10px !important; font-size: 0.8rem !important; }
    .table-premium .select2-container--default .select2-selection--single .select2-selection__arrow { height: 30px !important; }
    .table-premium .input-group-text { padding: 0.25rem 0.5rem !important; font-size: 0.8rem !important; border-radius: 0.5rem 0 0 0.5rem !important; }
    .table-premium .btn { padding: 0.25rem 0.5rem !important; font-size: 0.8rem !important; }
    .table-premium .disc-type-toggle { border-radius: 0 0.5rem 0.5rem 0 !important; height: 32px !important; }

    /* Advanced input-group alignment inside table cells */
    .table-premium .input-group { flex-wrap: nowrap !important; }
    .table-premium .input-group .fi { flex: 1 1 auto !important; width: 1% !important; }
    .table-premium .input-group-text + .fi { border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important; border-top-right-radius: 0.5rem !important; border-bottom-right-radius: 0.5rem !important; }
    .table-premium .fi:first-child { border-top-left-radius: 0.5rem !important; border-bottom-left-radius: 0.5rem !important; border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important; }

    .summary-card { background: #f8fafc; border-radius: 1rem; padding: 1.5rem; border: 1.5px dashed #cbd5e1; }
    .summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
    .summary-label { font-weight: 600; color: #64748b; }
    .summary-value { font-weight: 800; color: #1e293b; font-size: 1.1rem; }
    .summary-total { margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #e2e8f0; }
    .summary-total .summary-value { font-size: 1.5rem; color: #4f46e5; }

    .btn-premium { padding: 1rem 2rem; border-radius: 0.75rem; font-weight: 700; transition: all 0.2s; border: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
    .btn-submit { background: var(--primary-gradient); color: white; box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.39); width: 100%; margin-top: 1.5rem; }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79, 70, 229, 0.23); }

    .payment-badge { padding: 0.5rem 1rem; border-radius: 2rem; cursor: pointer; transition: all 0.2s; font-weight: 600; border: 2px solid transparent; background: #f1f5f9; color: #64748b; }
    .payment-badge.active { background: #eef2ff; color: #4f46e5; border-color: #4f46e5; }

    /* Select2 Custom Styling */
    .select2-container--default .select2-selection--single { height: 45px; border-radius: 0.75rem; border: 1.5px solid #e2e8f0; background: #f8fafc; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 43px; padding-left: 15px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 43px; }
</style>

<div class="main-content">
    <div class="container-fluid px-0">
        <form action="{{ route('purchase.storeLocal') }}" method="POST" id="localPurchaseForm">
            @csrf

            {{-- Error & Success Messages --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="border-radius: 1rem;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1">Please fix the following errors:</h6>
                            <ul class="mb-0 small">
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
                <div class="alert alert-warning alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="border-radius: 1rem;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-circle-fill fs-4 me-3"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            
            <div class="premium-card">
                <div class="card-header-gradient d-flex justify-content-between align-items-center">
                    <h2 class="card-title-premium">
                        <i class="bi bi-cart-plus"></i> Local Market Purchase
                    </h2>
                    <div class="d-flex align-items-center">
                        <a href="{{ route('store') }}" class="btn btn-md font-weight-bold mr-3 shadow-sm" style="background-color: #ffffff; color: #4f46e5; border: none; border-radius: 8px; padding: 6px 16px; transition: all 0.2s ease-in-out; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem;" onmouseover="this.style.backgroundColor='#f1f5f9'; this.style.transform='translateY(-1px)';" onmouseout="this.style.backgroundColor='#ffffff'; this.style.transform='none';">
                            <i class="fas fa-plus mr-2" style="font-size: 0.75rem;"></i> Item
                        </a>
                        <div class="text-white opacity-75 fw-bold">Direct Inventory Entry</div>
                    </div>
                </div>

                <div class="card-body p-3 p-lg-4">
                    <!-- Header Info -->
                    <div class="row g-3 mb-4">
                        {{-- ===== VENDOR SOURCE TOGGLE ===== --}}
                        <div class="col-12">
                            <label class="section-label">Purchase From</label>
                            <div class="d-flex gap-2 mb-3">
                                <label class="payment-badge active" id="badgeLocalMarket" style="font-size:0.85rem;">
                                    <input type="radio" name="_vendor_mode" value="local" class="d-none" checked>
                                    🛒 Local Market (Walk-In Shop)
                                </label>
                                <label class="payment-badge" id="badgeRegisteredVendor" style="font-size:0.85rem;">
                                    <input type="radio" name="_vendor_mode" value="vendor" class="d-none">
                                    🏢 Registered Vendor (Ledger Update)
                                </label>
                            </div>
                        </div>

                        {{-- LOCAL MARKET: free-text shop name --}}
                        <div class="col-md-3" id="localMarketField">
                            <label class="section-label">Vendor / Supplier / Shop <span class="text-danger">*</span></label>
                            <input type="text" name="vendor_name" id="vendor_name_text" class="fi" placeholder="Enter Local Market Shop Name">
                            <input type="hidden" name="vendor_id" id="vendor_id_hidden" value="">
                        </div>

                        {{-- REGISTERED VENDOR: select2 dropdown --}}
                        <div class="col-md-3" id="registeredVendorField" style="display:none;">
                            <label class="section-label">Select Registered Vendor <span class="text-danger">*</span></label>
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
                            {{-- These hidden fields get populated on vendor select --}}
                            <input type="hidden" name="vendor_name" id="vendor_name_hidden" value="">
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle"></i>
                                Vendor ka ledger automatically update hoga (Credit entry)
                            </small>
                        </div>

                        <div class="col-md-3">
                            <label class="section-label">Branch <span class="text-danger">*</span></label>
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
                            <label class="section-label">Warehouse / Destination <span class="text-danger">*</span></label>
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
                            <label class="section-label">Invoice Date</label>
                            <input type="date" name="purchase_date" class="fi" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-premium" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Product Details <span class="text-danger">*</span></th>
                                    <th style="width: 10%;">Packing Type</th>
                                    <th style="width: 20%; text-align: center;">Packing Details</th>
                                    <th style="width: 8%; text-align: center;">Total Qty <span class="text-danger">*</span></th>
                                    <th style="width: 12%;">Cost Price <span class="text-danger">*</span></th>
                                    <th style="width: 10%;">Disc</th>
                                    <th style="width: 8%;">Disc Amt</th>
                                    <th style="width: 13%; text-align: right;">Line Total</th>
                                    <th style="width: 4%;"></th>
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
                                            <input type="text" class="fi text-center" value="Piece" readonly style="background-color: #f1f5f9;">
                                        </div>
                                        <!-- Customize View -->
                                        <div class="customize-packing-view gap-2" style="display: none;">
                                            <div class="flex-grow-1 text-center" style="width: 33%;">
                                                <div style="font-size: 0.6rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 2px;">Packs</div>
                                                <input type="number" name="packing_qty[]" class="fi text-center pack-qty-input" step="1" min="0" value="0" placeholder="Packs">
                                            </div>
                                            <div class="flex-grow-1 text-center" style="width: 33%;">
                                                <div style="font-size: 0.6rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 2px;">Pcs/Pk</div>
                                                <input type="number" name="item_per_piece[]" class="fi text-center ipp-input" step="1" min="0" value="0" placeholder="Pcs/Pack">
                                            </div>
                                            <div class="flex-grow-1 text-center" style="width: 33%;">
                                                <div style="font-size: 0.6rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 2px;">Loose</div>
                                                <input type="number" name="loose_piece[]" class="fi text-center loose-pcs-input" step="1" min="0" value="0" placeholder="Loose">
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <input type="number" name="qty[]" class="fi text-center qty-input fw-bold text-primary" value="1" min="1" step="0.01" required>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-tag text-primary"></i></span>
                                            <input type="number" name="price[]" class="fi price-input border-start-0" style="border-left: none; background:#fff; color:#1e293b; cursor:text;" step="0.01" min="0" value="0" required placeholder="Enter price">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group" style="flex-wrap: nowrap;">
                                            <input type="number" class="fi form-control disc-input-visual" style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: 0;" step="0.01" min="0" value="0">
                                            <button class="btn btn-outline-secondary disc-type-toggle" type="button" data-type="amount" style="border-top-right-radius: 0.75rem; border-bottom-right-radius: 0.75rem; border: 1.5px solid #e2e8f0; border-left: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold; width: 45px; color: #4f46e5; transition: all 0.2s;">Rs</button>
                                            <input type="hidden" class="disc-type-input" value="amount">
                                            <input type="hidden" name="item_discount[]" class="disc-input-hidden" value="0">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="fi text-end fw-bold disc-amt-display" value="0.00" readonly style="background:#f8fafc; color:#64748b; font-size:0.85rem;">
                                    </td>
                                    <td class="text-end">
                                        <input type="text" class="fi text-end fw-bold line-total" value="0.00" readonly style="background:#f0fdf4; color:#15803d; font-size:0.85rem;">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row" style="border-radius: 8px; margin-top: 5px;"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold mb-5" id="addRow">
                        <i class="fa fa-plus me-1"></i> ADD ANOTHER ITEM
                    </button>

                    <div class="row g-4">
                        <!-- Left: Notes & Payment -->
                        <div class="col-lg-7">
                            <label class="section-label">Additional Notes</label>
                            <textarea name="note" class="fi mb-4" rows="3" placeholder="Enter any internal remarks or terms..."></textarea>
                            
                            <label class="section-label">Payment Information</label>
                            <div class="d-flex gap-3 mb-4">
                                <label class="payment-badge active" id="badgeLater">
                                    <input type="radio" name="payment_type" value="pay_later" class="d-none" checked> 💳 Pay Later (Credit)
                                </label>
                                <label class="payment-badge" id="badgeNow">
                                    <input type="radio" name="payment_type" value="pay_now" class="d-none"> 💵 Pay Now (Cash/Bank)
                                </label>
                            </div>

                            <div id="paymentFields" style="display: none;" class="bg-light p-4 rounded-4 border border-info border-opacity-25 animated fadeIn">
                                <label class="section-label mb-3">Payment Accounts & Amounts <span class="text-danger">*</span></label>
                                <div id="rvWrapper">
                                    <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                                        <div class="flex-grow-1">
                                            <select class="form-select fi rv-account" name="payment_account_id[]">
                                                <option value="" disabled selected>Select Source...</option>
                                                @foreach($bankAccounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->title }} (Rs. {{ number_format($acc->opening_balance, 2) }})</option>
                                                @endforeach
                                            </select>
                                            <div class="account-balance-wrapper mt-1 ms-2" style="display:none; font-size: 0.8rem;">
                                                <span class="text-muted">Available Balance:</span> 
                                                <span class="fw-bold text-info balance-amt">0.00</span>
                                            </div>
                                        </div>
                                        <div style="width: 160px;">
                                            <input type="number" name="payment_amount[]" class="fi border-info rv-amount text-end" step="0.01" placeholder="0.00">
                                        </div>
                                        <div style="width: 80px;">
                                            <button type="button" class="btn btn-outline-primary btn-sm w-100" id="btnAddRV" style="height: 48px; border-radius: 0.75rem;">Add</button>
                                        </div>
                                    </div>
                                    <div class="text-end mt-3 pt-2 border-top">
                                        <span class="me-2 text-muted fw-bold">Total Paid:</span>
                                        <span class="fw-bold text-success" id="totalPaidDisplay" style="font-size: 1.2rem;">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Totals Summary -->
                        <div class="col-lg-5">
                            <div class="summary-card shadow-sm h-100">
                                <div class="summary-row">
                                    <span class="summary-label">Items Subtotal</span>
                                    <span class="summary-value" id="dispSubtotal">0.00</span>
                                </div>
                                <div class="summary-row mt-3">
                                    <span class="summary-label">Additional Discount</span>
                                    <div style="width: 120px;">
                                        <input type="number" name="discount" id="overallDiscount" class="fi text-end py-1" step="0.01" value="0">
                                    </div>
                                </div>
                                <div class="summary-row mt-3">
                                    <span class="summary-label">Extra Charges (Freight/Misc)</span>
                                    <div style="width: 120px;">
                                        <input type="number" name="extra_cost" id="extraCost" class="fi text-end py-1" step="0.01" value="0">
                                    </div>
                                </div>
                                <div class="summary-row summary-total mt-4">
                                    <span class="summary-label text-primary">Invoice Net Total</span>
                                    <span class="summary-value" id="dispNet">0.00</span>
                                    <input type="hidden" name="net_amount" id="netAmount" value="0">
                                </div>
                                <div class="summary-row outstanding-row mt-2" style="display: none;">
                                    <span class="summary-label text-danger">Outstanding Balance</span>
                                    <span class="summary-value text-danger" id="dispOutstanding">0.00</span>
                                </div>

                                <button type="submit" class="btn btn-premium btn-submit">
                                    <i class="fa fa-check-double me-2"></i> POST PURCHASE & ADD STOCK
                                </button>
                                <p class="text-center small text-muted mt-3 mb-0">Stock will be updated across selected warehouse and branch immediately.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js')
<script>
$(document).ready(function() {
    function initSelect2() {
        $('.select2').select2({ width: '100%' });
    }
    initSelect2();

    // ===== VENDOR MODE TOGGLE =====
    function switchVendorMode(mode) {
        if (mode === 'local') {
            $('#localMarketField').show();
            $('#registeredVendorField').hide();
            // Enable local text, disable vendor select from validation
            $('#vendor_name_text').prop('required', true);
            $('#vendor_id_select').prop('required', false);
            // Clear registered vendor data
            $('#vendor_id_hidden').val('');
            $('#vendor_name_hidden').val('');
            $('#vendor_id_select').val('').trigger('change');
        } else {
            $('#localMarketField').hide();
            $('#registeredVendorField').show();
            // Disable local text required, enable vendor dropdown required
            $('#vendor_name_text').prop('required', false).val('');
            $('#vendor_id_select').prop('required', true);
            $('#vendor_id_hidden').val('');
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

    // When registered vendor is selected → populate hidden fields
    $('#vendor_id_select').on('change', function() {
        const selected = $(this).find(':selected');
        const vid  = selected.val() || '';
        const name = selected.data('name') || '';
        const phone = selected.data('phone') || '';

        // Populate the actual form fields that backend reads
        $('#vendor_id_hidden').val(vid);
        $('#vendor_name_hidden').val(name);

        // Also try to populate the phone/tel field if it exists
        $('input[name="tel"]').val(phone);
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
        
        // Also update standard packing view "Piece" text to the actual unit
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
            
            // Standard allows direct Qty edit
            row.find('.qty-input').prop('readonly', false).css('background-color', '#fff');
            
            // Clear packing inputs
            row.find('.pack-qty-input, .ipp-input, .loose-pcs-input').val(0);
        } else {
            row.find('.standard-packing-view').hide();
            row.find('.customize-packing-view').addClass('d-flex').show();
            
            // Customize makes Qty readonly (auto-calculated)
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
            
            // Set the calculated Rs discount to hidden field so backend gets correct value
            $(this).find('.disc-input-hidden').val(discAmt.toFixed(2));
            $(this).find('.disc-amt-display').val(discAmt.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            const lineTotal = (qty * price) - discAmt;
            $(this).find('.line-total').val(lineTotal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            subtotal += lineTotal;
        });

        $('#dispSubtotal').text(subtotal.toLocaleString('en-PK', {minimumFractionDigits: 2}));

        const overDisc = parseFloat($('#overallDiscount').val()) || 0;
        const extra    = parseFloat($('#extraCost').val())        || 0;
        const net      = (subtotal - overDisc) + extra;

        $('#dispNet').text(net.toLocaleString('en-PK', {minimumFractionDigits: 2}));
        $('#netAmount').val(net.toFixed(2));
        
        // Update the payment amount if paying now
        if ($('input[name="payment_type"][value="pay_now"]').is(':checked')) {
            let firstAmount = $('.rv-amount').first();
            if(!firstAmount.val() || parseFloat(firstAmount.val()) > 0) {
                firstAmount.val(net.toFixed(2));
            }
        }
        
        calcPayments();
    }

    // Run on any price / disc / overhead change
    $(document).on('input', '.qty-input, .price-input, .disc-input-visual, #overallDiscount, #extraCost', recalc);

    // Discount Type Toggle (Rs / %)
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
        // focus back on input for fast typing
        $(this).siblings('.disc-input-visual').focus();
        recalc();
    });

    // Payment Toggles
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

    // Accounts logic for multiple payments
    window.PAYMENT_ACCOUNTS = @json($bankAccounts);

    function loadAccountsInto($select) {
        const currentVal = $select.val(); // preserve selection
        let usedAccounts = [];

        $('.rv-account').each(function() {
            const val = $(this).val();
            if (val && this !== $select[0]) {
                usedAccounts.push(String(val));
            }
        });

        let html = '<option value="" disabled selected>Select Source...</option>';

        window.PAYMENT_ACCOUNTS.forEach(function(acc) {
            const accId = String(acc.id);
            if (!usedAccounts.includes(accId) || accId === String(currentVal)) {
                html += `<option value="${accId}">${acc.title} (Rs. ${parseFloat(acc.opening_balance).toLocaleString('en-PK', {minimumFractionDigits: 2})})</option>`;
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
                    wrapper.find('.balance-amt').text(bal.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
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
        
        $('#totalPaidDisplay').text(totalPaid.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        
        const netAmount = parseFloat($('#netAmount').val()) || 0;
        const outstanding = netAmount - totalPaid;
        $('#dispOutstanding').text(outstanding.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
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

    // ✅ ERP: Dynamic Warehouse Loading by Branch
    $('#branch_id').on('change', function() {
        const branchId = $(this).val();
        if (!branchId) return;

        const $warehouseSelect = $('#warehouse_id');
        
        // Show loading state
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

    // If Super Admin, trigger initial load for the selected branch
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

        // ===== Vendor mode final sync =====
        const mode = $('input[name="_vendor_mode"]:checked').val();
        if (mode === 'vendor') {
            const vid = $('#vendor_id_select').val();
            if (!vid) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Vendor Required', text: 'Please select a registered vendor.' });
                return false;
            }
            $('#vendor_id_hidden').val(vid);
            $('#vendor_name_hidden').val($('#vendor_id_select').find(':selected').data('name') || '');
        } else {
            $('#vendor_id_hidden').val('');
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
