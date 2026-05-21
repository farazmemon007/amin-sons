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

    .main-content { background-color: #f8fafc; min-height: 100vh; padding: 1.5rem 0.75rem; }
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
    .fi-table { padding: 0.6rem 0.4rem !important; font-size: 0.85rem !important; }
</style>

<div class="main-content">
    <div class="container-fluid px-2">
        <form action="{{ route('store.bill', $gatepass->id) }}" method="POST" id="billForm">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $gatepass->branch_id }}">
            <input type="hidden" name="warehouse_id" value="{{ $gatepass->warehouse_id }}">

            <div class="premium-card">
                <div class="card-header-gradient d-flex justify-content-between align-items-center">
                    <h2 class="card-title-premium">
                        <i class="bi bi-receipt-cutoff"></i> Create Purchase Invoice
                    </h2>
                    <div class="text-white opacity-75 fw-bold">GRN Ref: #{{ $gatepass->id }}</div>
                </div>

                <div class="card-body p-3 p-lg-4">
                    <!-- Header Info -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="section-label">Vendor / Supplier</label>
                            <input type="text" class="fi" value="{{ $gatepass->vendor->name ?? 'N/A' }}" readonly>
                            <input type="hidden" name="vendor_id" value="{{ $gatepass->vendor_id }}">
                        </div>
                        <div class="col-md-3">
                            <label class="section-label">Warehouse (Header)</label>
                            <input type="text" class="fi" value="{{ $gatepass->warehouse->warehouse_name ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="section-label">Branch</label>
                            <input type="text" class="fi" value="{{ $gatepass->branch->name ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="section-label">Invoice Date</label>
                            <input type="date" name="purchase_date" class="fi" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-premium">
                            <thead>
                                <tr>
                                    <th style="width: 18%;">Product Details</th>
                                    <th style="width: 10%;">Packing Type</th>
                                    <th style="width: 20%; text-align: center;">Packing Details</th>
                                    <th style="width: 8%; text-align: center;">Total Qty</th>
                                    <th style="width: 12%;">Cost Price</th>
                                    <th style="width: 10%;">Disc</th>
                                    <th style="width: 7%;">Disc Amt</th>
                                    <th style="width: 15%; text-align: right;">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Group items by Product, Packing Type, and Unit to prevent repetition in UI
                                    $groupedItems = $gatepass->items->groupBy(function($item) {
                                        return $item->product_id . '_' . ($item->packing_type ?? 'Standard') . '_' . ($item->unit ?? 'Piece');
                                    });
                                @endphp

                                @foreach($groupedItems as $groupKey => $items)
                                @php
                                    $first = $items->first();
                                    $totalQty = $items->sum('qty');
                                    $product = $first->product;
                                    $groupId = "group_" . $loop->index;
                                @endphp
                                <tr class="item-group-row" data-group-id="{{ $groupId }}">
                                    <td>
                                        <div class="fw-bold text-dark mb-1" style="font-size: 1.1rem; line-height: 1.2;">{{ $product->item_name }}</div>
                                        <div class="d-flex align-items-center flex-wrap gap-1 mb-2 mt-2">
                                            <span class="badge bg-white text-muted border px-2 py-1" style="font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                                <i class="bi bi-qr-code me-1"></i>{{ $product->item_code }}
                                            </span>
                                            @if($product->brand)
                                                <span class="badge text-primary px-2 py-1" style="background: #eef2ff; font-size: 0.65rem; font-weight: 800; border: 1px solid #c7d2fe; text-transform: uppercase;">
                                                    <i class="bi bi-tag-fill me-1"></i>{{ $product->brand->name }}
                                                </span>
                                            @endif
                                            @if($product->model)
                                                <span class="badge text-secondary px-2 py-1" style="background: #f8fafc; font-size: 0.65rem; font-weight: 700; border: 1px solid #e2e8f0;">
                                                    <i class="bi bi-cpu me-1"></i>{{ $product->model }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Color Breakdown Badges -->
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @foreach($items as $subItem)
                                                @if($subItem->color)
                                                    <span class="badge rounded-pill" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-size: 0.7rem; padding: 0.35rem 0.65rem;">
                                                        <i class="bi bi-circle-fill me-1" style="color: {{ strtolower($subItem->color) == 'white' ? '#cbd5e1' : strtolower($subItem->color) }}"></i>
                                                        {{ $subItem->color }}: <strong>{{ $subItem->qty }}</strong>
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>

                                        <!-- Hidden Inputs for Backend (One set per original item) -->
                                        @foreach($items as $subItem)
                                            <div class="hidden-item-inputs" data-item-id="{{ $subItem->id }}">
                                                <input type="hidden" name="product_id[]" value="{{ $subItem->product_id }}">
                                                <input type="hidden" name="color[]" value="{{ $subItem->color }}">
                                                <input type="hidden" name="unit[]" value="{{ $subItem->unit ?? ($product->unit->name ?? 'Piece') }}">
                                                <input type="hidden" name="qty[]" class="sub-item-qty" value="{{ $subItem->qty }}">
                                                <input type="hidden" name="packing_type[]" value="{{ $subItem->packing_type }}">
                                                <input type="hidden" name="packing_qty[]" value="{{ $subItem->packing_qty }}">
                                                <input type="hidden" name="item_per_piece[]" value="{{ $subItem->item_per_piece }}">
                                                <input type="hidden" name="loose_piece[]" value="{{ $subItem->loose_piece }}">
                                                <input type="hidden" name="price[]" class="sub-item-price-hidden" value="{{ $first->last_purchase_price }}">
                                                <input type="hidden" name="item_discount[]" class="sub-item-disc-hidden" value="0">
                                            </div>
                                        @endforeach
                                    </td>
                                    
                                    <td>
                                        <span class="badge {{ strtolower($first->packing_type ?? 'standard') === 'standard' ? 'bg-light text-primary' : 'bg-light text-warning' }} px-3 py-2 border">
                                            {{ $first->packing_type ?? 'Standard' }}
                                        </span>
                                    </td>
                                    
                                    <td class="text-center">
                                        @if(strtolower($first->packing_type ?? 'standard') === 'customize')
                                            <div class="d-flex justify-content-center gap-2">
                                                <div class="text-center">
                                                    <div class="small text-muted" style="font-size: 0.65rem;">PACKS</div>
                                                    <div class="fw-bold">{{ $first->packing_qty ?? 0 }}</div>
                                                </div>
                                                <div class="vr mx-1"></div>
                                                <div class="text-center">
                                                    <div class="small text-muted" style="font-size: 0.65rem;">PCS/PK</div>
                                                    <div class="fw-bold">{{ $first->item_per_piece ?? 0 }}</div>
                                                </div>
                                                <div class="vr mx-1"></div>
                                                <div class="text-center">
                                                    <div class="small text-muted" style="font-size: 0.65rem;">LOOSE</div>
                                                    <div class="fw-bold">{{ $first->loose_piece ?? 0 }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted small">Standard Packing ({{ $first->unit ?? 'Piece' }})</span>
                                        @endif
                                    </td>
                                    
                                    <td class="text-center">
                                        <div class="fw-900 text-primary" style="font-size: 1.1rem;">{{ $totalQty }}</div>
                                        <div class="small text-muted text-uppercase" style="font-size: 0.65rem;">{{ $first->unit ?? 'Piece' }}s</div>
                                        <input type="hidden" class="group-total-qty" value="{{ $totalQty }}">
                                    </td>
                                    
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 px-2"><i class="fas fa-tag text-primary" style="font-size: 0.8rem;"></i></span>
                                            <input type="number" class="fi fi-table master-price-input border-start-0" style="border-left: none; background:#fff; color:#1e293b; font-weight: 700;" step="0.01" min="0" value="{{ $first->last_purchase_price }}" required>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="input-group" style="flex-wrap: nowrap;">
                                            <input type="number" class="fi fi-table master-disc-input-visual" style="border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: 0;" step="0.01" min="0" value="0">
                                            <button class="btn btn-outline-secondary disc-type-toggle px-2" type="button" data-type="amount" style="border-top-right-radius: 0.75rem; border-bottom-right-radius: 0.75rem; border: 1.5px solid #e2e8f0; border-left: 1px solid #cbd5e1; background: #f8fafc; font-weight: bold; width: 40px; color: #4f46e5; font-size: 0.8rem;">Rs</button>
                                            <input type="hidden" class="master-disc-type-input" value="amount">
                                        </div>
                                    </td>

                                    <td>
                                        <input type="text" class="fi fi-table text-end fw-bold group-disc-amt-display" value="0.00" readonly style="background:#f8fafc; color:#64748b; font-size:0.9rem;">
                                    </td>

                                    <td class="text-end">
                                        <input type="text" class="fi fi-table text-end fw-900 group-line-total" value="0.00" readonly style="background:#f0fdf4; color:#15803d; font-size:1.1rem; min-width: 120px;">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Section -->
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

                            <div id="paymentFields" style="display: none;" class="bg-light p-4 rounded-4 border border-info border-opacity-25">
                                <label class="section-label mb-3">Payment Accounts & Amounts <span class="text-danger">*</span></label>
                                <div id="rvWrapper">
                                    <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                                        <div class="flex-grow-1">
                                            <select class="form-select fi rv-account" name="payment_account_id[]">
                                                <option value="" disabled selected>Select Source...</option>
                                                @foreach($bankAccounts as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->head->title ?? '' }})</option>
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
                            <div class="summary-card shadow-sm">
                                <div class="summary-row">
                                    <span class="summary-label">Items Subtotal</span>
                                    <span class="summary-value" id="dispSubtotal">0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Additional Discount</span>
                                    <div style="width: 120px;">
                                        <input type="number" name="discount" id="overallDiscount" class="fi text-end py-1" step="0.01" value="0">
                                    </div>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Extra Charges (Freight/Misc)</span>
                                    <div style="width: 120px;">
                                        <input type="number" name="extra_cost" id="extraCost" class="fi text-end py-1" step="0.01" value="0">
                                    </div>
                                </div>
                                <div class="summary-row summary-total">
                                    <span class="summary-label text-primary">Invoice Net Total</span>
                                    <span class="summary-value" id="dispNet">0.00</span>
                                    <input type="hidden" name="net_amount" id="netAmount" value="0">
                                </div>
                                <div class="summary-row outstanding-row" style="display: none;">
                                    <span class="summary-label text-danger">Outstanding Balance</span>
                                    <span class="summary-value text-danger" id="dispOutstanding">0.00</span>
                                </div>
                            </div>

                            <button type="submit" class="btn-premium btn-submit mt-4">
                                <i class="bi bi-check2-circle"></i> Complete & Post Invoice
                            </button>
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

    // --- Recalculation Logic (Grouped) ---
    function recalc() {
        let subtotal = 0;

        $('.item-group-row').each(function() {
            const $groupRow = $(this);
            const masterPrice = parseFloat($groupRow.find('.master-price-input').val()) || 0;
            const masterDiscVal = parseFloat($groupRow.find('.master-disc-input-visual').val()) || 0;
            const masterDiscType = $groupRow.find('.master-disc-type-input').val() || 'amount';
            const totalGroupQty = parseFloat($groupRow.find('.group-total-qty').val()) || 0;

            let groupDiscAmt = 0;
            if (masterDiscType === 'percent') {
                groupDiscAmt = (totalGroupQty * masterPrice) * (masterDiscVal / 100);
            } else {
                groupDiscAmt = masterDiscVal;
            }

            // Sync values to sub-items
            const $subItems = $groupRow.find('.hidden-item-inputs');
            const subItemCount = $subItems.length;
            
            $subItems.each(function() {
                const $sub = $(this);
                const subQty = parseFloat($sub.find('.sub-item-qty').val()) || 0;
                
                // Set price for this sub-item
                $sub.find('.sub-item-price-hidden').val(masterPrice.toFixed(2));
                
                // Distribute discount proportionally by quantity if it's a fixed amount, 
                // or just use percent if it's percent.
                let subDiscAmt = 0;
                if (masterDiscType === 'percent') {
                    subDiscAmt = (subQty * masterPrice) * (masterDiscVal / 100);
                } else {
                    // Fixed amount distributed by qty share
                    if (totalGroupQty > 0) {
                        subDiscAmt = (subQty / totalGroupQty) * masterDiscVal;
                    }
                }
                $sub.find('.sub-item-disc-hidden').val(subDiscAmt.toFixed(4));
            });

            // Update UI for the group
            $groupRow.find('.group-disc-amt-display').val(groupDiscAmt.toFixed(2));
            
            const groupLineTotal = (totalGroupQty * masterPrice) - groupDiscAmt;
            $groupRow.find('.group-line-total').val(groupLineTotal.toFixed(2));
            
            subtotal += groupLineTotal;
        });

        $('#dispSubtotal').text(subtotal.toLocaleString('en-PK', {minimumFractionDigits: 2}));

        const overDisc = parseFloat($('#overallDiscount').val()) || 0;
        const extra    = parseFloat($('#extraCost').val())        || 0;
        const net      = (subtotal - overDisc) + extra;

        $('#dispNet').text(net.toLocaleString('en-PK', {minimumFractionDigits: 2}));
        $('#netAmount').val(net.toFixed(2));
        calcPayments();
    }

    // Run on any price / disc / overhead change
    $(document).on('input', '.master-price-input, .master-disc-input-visual, #overallDiscount, #extraCost', recalc);

    // Discount Type Toggle (Rs / %)
    $(document).on('click', '.disc-type-toggle', function() {
        let type = $(this).attr('data-type');
        const $groupRow = $(this).closest('.item-group-row');
        
        if(type === 'amount') {
            $(this).attr('data-type', 'percent');
            $(this).text('%');
            $groupRow.find('.master-disc-type-input').val('percent');
        } else {
            $(this).attr('data-type', 'amount');
            $(this).text('Rs');
            $groupRow.find('.master-disc-type-input').val('amount');
        }
        $groupRow.find('.master-disc-input-visual').focus();
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
        const currentVal = $select.val();
        let usedAccounts = [];
        $('.rv-account').each(function() {
            const val = $(this).val();
            if (val && this !== $select[0]) usedAccounts.push(String(val));
        });

        let html = '<option value="" disabled selected>-- Select Account --</option>';
        window.PAYMENT_ACCOUNTS.forEach(function(acc) {
            const accId = String(acc.id);
            if (!usedAccounts.includes(accId) || accId === String(currentVal)) {
                html += `<option value="${accId}">${acc.title} (${acc.account_code})</option>`;
            }
        });
        $select.html(html);
        if (currentVal) $select.val(currentVal);
    }

    $(document).on('change', '.rv-account', function() {
        $('.rv-account').each(function() { loadAccountsInto($(this)); });
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
                } else { wrapper.hide(); }
            } else { wrapper.hide(); }
        });
    }

    function calcPayments() {
        let totalPaid = 0;
        $('.rv-amount').each(function() { totalPaid += parseFloat($(this).val()) || 0; });
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
        $btn.attr('id', '').removeClass('btn-outline-primary').addClass('btn-outline-danger btn-remove-rv').text('Remove');
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

    recalc();

    $('#billForm').on('submit', function(e) {
        let emptyPrice = false;
        $('.master-price-input').each(function() {
            if(parseFloat($(this).val()) <= 0) emptyPrice = true;
        });
        if(emptyPrice) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Check Prices!', text: 'Kuch items ka cost price 0 ya khaali hai. Please check karein.' });
        }
    });
});
</script>
@endsection
