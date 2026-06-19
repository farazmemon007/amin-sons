@extends('admin_panel.layout.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
  .main-container {
    font-size: .85rem;
    max-width: 1200px;
  }
  .header-text {
    font-size: 1.1rem;
  }
  .form-control, .form-select, .btn {
    font-size: .85rem;
    padding: .4rem .6rem;
    height: auto;
  }
  .input-readonly {
    background: #f9fbff;
    font-weight: 600;
  }
  .section-title {
    font-weight: 700;
    color: #6c757d;
    letter-spacing: .3px;
  }
  .table {
    --bs-table-padding-y: .35rem;
    --bs-table-padding-x: .5rem;
    font-size: .85rem;
  }
  .table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #f8f9fa;
    text-align: center;
  }
  .table-responsive {
    max-height: 400px;
    overflow: auto;
    border: 1px solid #eee;
    border-radius: .5rem;
  }
  .totals-card {
    background: #fcfcfe;
    border: 1px solid #eee;
    border-radius: .5rem;
  }
  .badge-info-custom {
    background: #e7f3ff;
    color: #004085;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
  }
  .invalid-input {
    border-color: #e3342f !important;
  }
</style>

<div class="container-fluid py-4">
  <div class="main-container bg-white border shadow-sm mx-auto p-3 rounded-3">

    {{-- Show validation errors --}}
    @if ($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>⚠️ Validation Errors:</strong>
        <ul class="mb-0 mt-2">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    {{-- Show success message --}}
    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div id="alertBox" class="alert d-none mb-3" role="alert"></div>

    <form id="saleReturnForm" method="POST" action="{{ route('sales.return.store') }}" autocomplete="off">
      @csrf
      <input type="hidden" name="sale_id" value="{{ $sale->id }}">
      <input type="hidden" name="customer_id" value="{{ $sale->customer_id }}">
      <input type="hidden" id="branch_id" name="branch_id" value="{{ $sale->branch_id }}">
      <input type="hidden" id="warehouse_id" name="warehouse_id" value="{{ $sale->saleItems->first()->warehouse_id ?? 1 }}">

      {{-- HEADER --}}
      <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
        <div>
          <small class="text-secondary" id="entryDateTime">Entry Date_Time: --</small><br>
          <a href="{{ route('sale.index') }}" class="btn btn-sm btn-outline-secondary">Back to Sales</a>
        </div>
        <h2 class="header-text text-secondary fw-bold mb-0">Sale Return</h2>
        <div class="d-flex align-items-center gap-2">
          <small class="text-secondary me-2" id="entryDate">Date: --</small>
        </div>
      </div>

      {{-- ORIGINAL SALE INFO --}}
      <div class="row mt-3 mb-3">
        <div class="col-md-6">
          <div class="card p-3">
            <div class="section-title mb-2">Original Sale Details</div>
            <div class="mb-2">
              <label class="form-label fw-bold mb-0">Invoice No:</label>
              <input type="text" class="form-control input-readonly" value="{{ $sale->invoice_no }}" readonly>
            </div>
            <div class="mb-2">
              <label class="form-label fw-bold mb-0">Customer:</label>
              <input type="text" class="form-control input-readonly" value="{{ $Customer->find($sale->customer_id)->customer_name ?? 'N/A' }}" readonly>
            </div>
            <div class="mb-2">
              <label class="form-label fw-bold mb-0">Original Total:</label>
              <input type="text" class="form-control input-readonly text-end fw-bold" value="Rs. {{ number_format($sale->total_net, 2) }}" readonly>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card p-3">
            <div class="section-title mb-2">Return Information</div>
            <div class="mb-2">
              <label class="form-label fw-bold">Return Reference</label>
              <input type="text" class="form-control" name="reference" placeholder="e.g, RET-001" value="RET-{{ $nextInvoiceNumber ?? date('YmdHis') }}">
            </div>
            <div class="mb-2">
              <label class="form-label fw-bold">Return Note</label>
              <textarea class="form-control" name="return_note" placeholder="Reason for return..." rows="2"></textarea>
            </div>
          </div>
        </div>
      </div>

      {{-- RETURN ITEMS TABLE --}}
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="section-title mb-0">Items to Return</div>
          <button type="button" class="btn btn-sm btn-primary" id="btnAddReturnRow">+ Add Row</button>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered mb-0">
            <thead>
              <tr class="table-light">
                <th style="width: 35%">Product</th>
                <th style="width: 12%">Remaining Qty</th>
                <th style="width: 12%">Return Qty</th>
                <th style="width: 12%">Price</th>
                <th style="width: 12%">Discount</th>
                <th style="width: 12%">Total</th>
                <th style="width: 5%" class="text-center">Action</th>
              </tr>
            </thead>
            <tbody id="returnItemsBody">
            </tbody>
          </table>
        </div>
      </div>

      {{-- PAYMENT HANDLING --}}
      <div class="row mt-3">
        <div class="col-md-6">
          <div class="card p-3">
            <div class="section-title mb-2">Payment Method</div>
            <div class="mb-2">
              <label class="form-label fw-bold mb-1 d-block">Refund Type</label>
              <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="refund_type" id="refund_credit" value="credit" checked>
                <label class="btn btn-outline-primary btn-sm" for="refund_credit">Credit Note</label>

                <input type="radio" class="btn-check" name="refund_type" id="refund_cash" value="cash">
                <label class="btn btn-outline-primary btn-sm" for="refund_cash">Cash Refund</label>
              </div>
            </div>

            {{-- CASH REFUND OPTION --}}
            <div id="cashRefundSection" style="display: none;" class="mt-3 p-3 border rounded-2 bg-light">
              <div class="section-title mb-2">Cash Distribution</div>
              <div id="accountRefundWrapper" class="mb-2">
                <div class="d-flex gap-2 align-items-start mb-3 account-row">
                  <div class="d-flex flex-column" style="max-width: 300px; width: 100%;">
                    <select class="form-select refund-account" name="refund_account_id[]">
                      <option value="" data-balance="0">Select Account</option>
                      @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}" data-balance="{{ $acc->opening_balance }}">{{ $acc->title }}</option>
                      @endforeach
                    </select>
                    <small class="text-muted account-balance-label mt-1" style="display: none; font-size: 0.75rem;">Balance: Rs. <span class="account-balance-val">0.00</span></small>
                  </div>
                  <input type="text" class="form-control text-end refund-amount"
                    name="refund_amount[]" placeholder="0.00" style="max-width: 150px">
                  <button type="button" class="btn btn-outline-danger btn-sm btnRemoveAccount">&times;</button>
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRefundAccount">+ Add Account</button>
            </div>
          </div>
        </div>

        {{-- TOTALS --}}
        <div class="col-md-6">
          <div class="card totals-card p-3">
            <div class="section-title mb-2">Return Summary</div>

            <div class="row py-2 border-bottom">
              <div class="col-7 text-muted">Total Items Returned</div>
              <div class="col-5 text-end"><span id="totalItemsReturn">0</span></div>
            </div>

            <div class="row py-2 border-bottom">
              <div class="col-7 text-muted">Return Subtotal</div>
              <div class="col-5 text-end"><span id="returnSubtotal">Rs. 0.00</span></div>
            </div>

            <div class="row py-2 border-bottom">
              <div class="col-7 text-muted">Original Discount Deduction</div>
              <div class="col-5 text-end"><span id="discountDeduction">Rs. 0.00</span></div>
            </div>

            <div class="row py-3 bg-light border-bottom">
              <div class="col-7 fw-bold text-primary">Net Return Amount</div>
              <div class="col-5 text-end fw-bold text-primary"><span id="netReturnAmount">Rs. 0.00</span></div>
            </div>

            {{-- AMOUNT WORDS --}}
            <div class="row py-2">
              <div class="col-12">
                <small class="text-muted">In Words:</small><br>
                <span id="amountInWords" class="badge badge-info-custom">Zero</span>
              </div>
            </div>

            {{-- Hidden inputs for backend --}}
            <input type="hidden" name="total_subtotal" id="total_subtotal" value="0">
            <input type="hidden" name="total_extra_cost" id="total_extra_cost" value="0">
            <input type="hidden" name="total_net" id="total_net" value="0">
            <input type="hidden" name="cash" id="cash" value="0">
            <input type="hidden" name="card" id="card" value="0">
            <input type="hidden" name="change" id="change" value="0">
            <input type="hidden" name="total_amount_Words" id="total_amount_Words" value="Zero">
          </div>
        </div>
      </div>

      {{-- ACTION BUTTONS --}}
      <div class="d-flex flex-wrap gap-2 justify-content-center p-3 mt-3 border-top">
        <button type="submit" class="btn btn-sm btn-success" id="btnSubmit">Process Return</button>
        <button type="reset" class="btn btn-sm btn-warning">Clear</button>
        <button type="button" class="btn btn-sm btn-secondary" id="btnPrint">Print</button>
        <a href="{{ route('sale.index') }}" class="btn btn-sm btn-danger">Exit</a>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Pass sale items from PHP to JavaScript
const SALE_ITEMS = {!! json_encode($saleItems ?? []) !!};

// Initialize Select2 for return items product
function initProductSelect2Return(selector = '.product-select-return') {
  $(selector).select2({
    ajax: {
      transport: function (params, success, failure) {
        let term = (params.data && (params.data.term || params.data.q)) || '';
        let page = (params.data && (params.data.page || 1)) || 1;
        let ajaxUrl = term && term.length > 0 ? '/search_products' : '/search-products-sale';
        $.ajax({
          url: ajaxUrl,
          data: { q: term, page: page },
          dataType: 'json',
          success: function (data) { success(data); },
          error: failure
        });
      },
      delay: 250,
      data: function (params) {
        return { q: params.term || '', page: params.page || 1 };
      },
      processResults: function (data, params) {
        params.page = params.page || 1;
        let results = [];
        if (Array.isArray(data)) {
          results = data.map(function (p) {
            // Extract stock quantity from object or use direct value
            let stockQty = (typeof p.stock === 'object' && p.stock !== null) ? (p.stock.qty || 0) : (p.stock || 0);
            return { id: p.id, text: p.item_name, stock: stockQty, price: p.retail_price || p.price };
          });
          return { results: results, pagination: { more: false } };
        }
        results = (data.products || []).map(function (p) {
          // Extract stock quantity from object or use direct value
          let stockQty = (typeof p.stock === 'object' && p.stock !== null) ? (p.stock.qty || 0) : (p.stock || 0);
          return { id: p.id, text: p.item_name, stock: stockQty, price: p.retail_price || p.price };
        });
        return { results: results, pagination: { more: !!data.has_more } };
      },
      cache: true
    },
    minimumInputLength: 0,
    placeholder: 'Search product...',
    allowClear: true,
    width: 'resolve'
  });
}

function addNewReturnRow(itemData = null) {
  const rowHtml = `
    <tr>
      <td class="product-col">
        <select class="form-select product-select-return" name="product_id[]" style="width:100%">
          <option value="">Search product...</option>
        </select>
        <input type="hidden" name="warehouse_id[]" class="warehouse-id-field">
        <input type="hidden" name="product[]" class="product-name-field">
        <input type="hidden" name="item_code[]" class="product-code-field">
        <input type="hidden" name="uom[]" class="product-brand-field">
        <input type="hidden" name="unit[]" class="product-unit-field">
        <input type="hidden" name="total[]" class="product-total-field">
        <input type="hidden" name="color[]" class="product-color-field">
        <input type="hidden" class="remaining-qty-field">
      </td>
      <td class="text-center">
        <input type="text" class="form-control text-center input-readonly stock-return" readonly>
      </td>
      <td class="text-center">
        <input type="number" class="form-control text-center return-qty" name="qty[]" min="0" placeholder="0">
      </td>
      <td class="text-end">
        <input type="text" class="form-control text-end input-readonly retail-price-return" value="0" readonly name="price[]">
      </td>
      <td class="text-end">
        <input type="text" class="form-control text-end input-readonly discount-return" value="0" readonly name="item_disc[]">
      </td>
      <td class="text-end">
        <input type="text" class="form-control text-end input-readonly line-total-return" value="Rs. 0.00" readonly>
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger del-return-row">&times;</button>
      </td>
    </tr>
  `;
  $('#returnItemsBody').append(rowHtml);
  const $lastRow = $('#returnItemsBody tr:last-child');

  // If item data provided (from original sale), pre-fill the row
  if (itemData) {
    const $selectBox = $lastRow.find('.product-select-return');
    const option = new Option(itemData.item_name, itemData.product_id, true, true);
    $selectBox.append(option).trigger('change');

    // Pre-fill all fields
    $lastRow.find('.stock-return').val(itemData.remaining_qty);
    $lastRow.find('.remaining-qty-field').val(itemData.remaining_qty);
    $lastRow.find('input[name="qty[]"]').val(itemData.remaining_qty);  // Pre-fill return qty with original qty
    $lastRow.find('input[name="price[]"]').val(parseFloat(itemData.price).toFixed(2));
    $lastRow.find('.retail-price-return').val(parseFloat(itemData.price).toFixed(2));
    $lastRow.find('input[name="item_disc[]"]').val(parseFloat(itemData.discount).toFixed(2));
    $lastRow.find('.discount-return').val(parseFloat(itemData.discount).toFixed(2));

    // Hidden meta fields for legacy SalesReturn format
    $lastRow.find('.warehouse-id-field').val(itemData.warehouse_id);
    $lastRow.find('.product-name-field').val(itemData.item_name);
    $lastRow.find('.product-code-field').val(itemData.item_code);
    $lastRow.find('.product-brand-field').val(itemData.brand);
    $lastRow.find('.product-unit-field').val(itemData.unit);
    $lastRow.find('.product-color-field').val(JSON.stringify(itemData.color || []));

    // Calculate and set line total
    const lineTotal = (itemData.qty * itemData.price) - itemData.discount;
    $lastRow.find('.line-total-return').val('Rs. ' + lineTotal.toFixed(2));
    $lastRow.find('.product-total-field').val(lineTotal.toFixed(2));
  }

  initProductSelect2Return('#returnItemsBody tr:last-child .product-select-return');

  // Load product details on selection
  $lastRow.find('.product-select-return').on('select2:select', function(e) {
    if (e && e.params && e.params.data) {
      const $row = $(this).closest('tr');
      // Properly extract stock value (handle both object and numeric types)
      let stockValue = e.params.data.stock || 0;
      if (typeof stockValue === 'object' && stockValue !== null) {
        stockValue = stockValue.qty || 0;
      }
      $row.find('.stock-return').val(stockValue);

      // Properly extract price value (handle both object and numeric types)
      let priceValue = e.params.data.price || 0;
      if (typeof priceValue === 'object' && priceValue !== null) {
        priceValue = priceValue.price || priceValue.retail_price || 0;
      }
      $row.find('.retail-price-return').val(parseFloat(priceValue).toFixed(2));
      $row.find('input[name="price[]"]').val(parseFloat(priceValue).toFixed(2));
    }
  });
}

$(document).ready(function() {
  console.log('🟢 DOCUMENT READY FIRED - Sale Return Form JS Loading');

  // Set current date/time
  function setNowStamp() {
    const d = new Date();
    const pad = (n) => n < 10 ? '0' + n : n;
    const dt = `${pad(d.getDate())}-${pad(d.getMonth()+1)}-${String(d.getFullYear()).slice(-2)} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
    const dOnly = `${pad(d.getDate())}-${pad(d.getMonth()+1)}-${String(d.getFullYear()).slice(-2)}`;
    $('#entryDateTime').text('Entry Date_Time: ' + dt);
    $('#entryDate').text('Date: ' + dOnly);
  }
  setNowStamp();

  // Alert function - IMPROVED FOR VISIBILITY
  function showAlert(type, msg) {
    const el = $('#alertBox');
    el.removeClass('d-none alert-success alert-danger alert-warning alert-info')
      .addClass('alert-' + type)
      .html(msg)
      .show();

    // Scroll to alert box so user sees it
    $('html, body').animate({
      scrollTop: el.offset().top - 100
    }, 300);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      el.fadeOut(300, function() {
        el.addClass('d-none');
      });
    }, 5000);

    console.log('🚨 Alert shown:', type, msg);
  }

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('input[name="_token"]').val()
    }
  });

  // Toggle refund type
  $('input[name="refund_type"]').on('change', function() {
    if ($(this).val() === 'cash') {
      $('#cashRefundSection').show();
    } else {
      $('#cashRefundSection').hide();
    }
  });

  // Calculate line total when qty changes
  $(document).on('input', '.return-qty', function() {
    const $row = $(this).closest('tr');
    const returnQtyInput = $(this);
    const qty = parseFloat(returnQtyInput.val()) || 0;
    const remainingQty = parseFloat($row.find('.remaining-qty-field').val()) || 0;
    
    // Validation
    if (qty > remainingQty) {
      returnQtyInput.addClass('invalid-input');
      showAlert('danger', 'Return quantity cannot exceed remaining quantity (' + remainingQty + ')');
      $('#btnSubmit').prop('disabled', true);
    } else {
      returnQtyInput.removeClass('invalid-input');
      // Re-enable if no other invalid inputs
      if ($('.invalid-input').length === 0) {
        $('#btnSubmit').prop('disabled', false);
      }
    }

    const price = parseFloat($row.find('input[name="price[]"]').val()) || 0;
    const discount = parseFloat($row.find('input[name="item_disc[]"]').val()) || 0;

    const lineTotal = (price * qty) - discount;

    $row.find('.line-total-return').val('Rs. ' + lineTotal.toFixed(2));
    $row.find('.product-total-field').val(lineTotal.toFixed(2));

    updateGrandTotal();
  });

  // Delete return row
  $(document).on('click', '.del-return-row', function() {
    const $tr = $(this).closest('tr');
    const $tbody = $('#returnItemsBody');
    if ($tbody.find('tr').length > 1) {
      $tr.remove();
    }
    updateGrandTotal();
  });

  // Add row button
  $('#btnAddReturnRow').on('click', function() {
    addNewReturnRow();
  });

  // Update grand totals
  function updateGrandTotal() {
    let totalQty = 0;
    let totalSubtotal = 0;
    let totalDiscount = 0;

    $('#returnItemsBody tr').each(function() {
      const qty = parseFloat($(this).find('.return-qty').val()) || 0;
      const price = parseFloat($(this).find('input[name="price[]"]').val()) || 0;
      const discount = parseFloat($(this).find('input[name="item_disc[]"]').val()) || 0;

      totalQty += qty;
      totalSubtotal += (price * qty);
      totalDiscount += discount;
    });

    const netReturn = totalSubtotal - totalDiscount;

    $('#totalItemsReturn').text(totalQty.toFixed(0));
    $('#returnSubtotal').text('Rs. ' + totalSubtotal.toFixed(2));
    $('#discountDeduction').text('Rs. ' + totalDiscount.toFixed(2));
    $('#netReturnAmount').text('Rs. ' + netReturn.toFixed(2));

    // Update hidden fields
    $('#total_subtotal').val(totalSubtotal.toFixed(2));
    $('#total_extra_cost').val(totalDiscount.toFixed(2));
    $('#total_net').val(netReturn.toFixed(2));

    // Convert to words
    $('#total_amount_Words').val(numberToWords(netReturn));
    $('#amountInWords').text(numberToWords(netReturn));

    // Update cash fields
    $('#cash').val(netReturn.toFixed(2));
  }

  // Add refund account row
  $('#btnAddRefundAccount').on('click', function() {
    const newRow = `
      <div class="d-flex gap-2 align-items-start mb-3 account-row">
        <div class="d-flex flex-column" style="max-width: 300px; width: 100%;">
          <select class="form-select refund-account" name="refund_account_id[]">
            <option value="" data-balance="0">Select Account</option>
            @foreach ($accounts as $acc)
              <option value="{{ $acc->id }}" data-balance="{{ $acc->opening_balance }}">{{ $acc->title }}</option>
            @endforeach
          </select>
          <small class="text-muted account-balance-label mt-1" style="display: none; font-size: 0.75rem;">Balance: Rs. <span class="account-balance-val">0.00</span></small>
        </div>
        <input type="text" class="form-control text-end refund-amount"
          name="refund_amount[]" placeholder="0.00" style="max-width: 150px">
        <button type="button" class="btn btn-outline-danger btn-sm btnRemoveAccount">&times;</button>
      </div>
    `;
    $('#accountRefundWrapper').append(newRow);
  });

  // Update account balance display on select change
  $(document).on('change', '.refund-account', function() {
    const $row = $(this).closest('.account-row');
    const selectedOption = $(this).find('option:selected');
    const balance = parseFloat(selectedOption.data('balance')) || 0;
    
    const $balanceLabel = $row.find('.account-balance-label');
    const $balanceVal = $row.find('.account-balance-val');
    
    if ($(this).val()) {
      $balanceVal.text(balance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
      $balanceLabel.show();
    } else {
      $balanceLabel.hide();
    }
  });

  // Remove refund account row
  $(document).on('click', '.btnRemoveAccount', function() {
    $(this).closest('.account-row').remove();
  });

  // Form validation and submission
  $('#saleReturnForm').on('submit', function(e) {
    console.log('🔵 FORM SUBMIT CLICKED');
    e.preventDefault();

    // Validate at least one item selected
    let hasItems = false;
    $('#returnItemsBody tr').each(function() {
      const qty = parseFloat($(this).find('.return-qty').val()) || 0;
      console.log('Row qty check:', qty);
      if (qty > 0) hasItems = true;
    });

    console.log('Has items:', hasItems);
    if (!hasItems) {
      console.log('❌ NO ITEMS VALIDATION FAILED');
      showAlert('danger', 'Please select at least one item to return');
      return;
    }
    console.log('✅ VALIDATION PASSED');

    // If cash refund, validate accounts
    const isCashRefund = $('#refund_cash').is(':checked');
    console.log('🔍 Is Cash Refund?', isCashRefund);

    if (isCashRefund) {
      let totalAmount = parseFloat($('#total_net').val()) || 0;
      let allocatedAmount = 0;

      console.log('💰 Total Return Amount:', totalAmount);

      $('.refund-amount').each(function() {
        const amt = parseFloat($(this).val()) || 0;
        console.log('   Refund account amount:', amt);
        allocatedAmount += amt;
      });

      console.log('💵 Total Allocated:', allocatedAmount);

      // ✅ ALLOW PARTIAL REFUNDS: Allocated can be less than or equal to total
      // Remaining balance auto-converts to credit note
      if (allocatedAmount > totalAmount) {
        console.log('❌ ACCOUNTS VALIDATION FAILED - Allocated exceeds total');
        showAlert('danger', `Refund accounts total (Rs. ${allocatedAmount.toFixed(2)}) cannot exceed return amount (Rs. ${totalAmount.toFixed(2)})`);
        return;
      }

      if (allocatedAmount === 0 && totalAmount > 0) {
        console.log('❌ ACCOUNTS VALIDATION FAILED - No amount allocated');
        showAlert('danger', `Please enter refund amount. Total to refund: Rs. ${totalAmount.toFixed(2)}`);
        return;
      }

      const creditBalance = totalAmount - allocatedAmount;
      if (creditBalance > 0) {
        console.log('ℹ️ INFO: Partial refund detected');
        console.log(`   Cash: Rs. ${allocatedAmount.toFixed(2)}`);
        console.log(`   Credit Note: Rs. ${creditBalance.toFixed(2)}`);
      } else {
        console.log('✅ FULL CASH REFUND');
      }

      console.log('✅ ACCOUNTS VALIDATION PASSED');
    }

    // ✅ All validations passed - submit form via AJAX
    console.log('📤 AJAX SUBMISSION STARTING');
    const formData = $(this).serialize();
    console.log('Form data:', formData);

    $.ajax({
      url: '{{ route("sales.return.store") }}',
      type: 'POST',
      dataType: 'json',
      data: formData,
      success: function(response) {
        console.log('✅ AJAX SUCCESS:', response);
        showAlert('success', '✅ Sale return processed successfully!');
        setTimeout(() => {
          window.location.href = '{{ route("sale.index") }}';
        }, 1500);
      },
      error: function(xhr) {
        console.error('❌ AJAX ERROR:', xhr);
        console.error('Full Response:', xhr.responseJSON); // Log full response
        let msg = 'Server error occurred';

        // Check for validation errors
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
          msg = 'Validation errors:\n';
          $.each(xhr.responseJSON.errors, function(key, val) {
            msg += '- ' + val[0] + '\n';
          });
        } else if (xhr.responseJSON?.message) {
          msg = xhr.responseJSON.message;
        } else if (xhr.responseText) {
          msg = xhr.responseText;
        }

        console.error('Error message:', msg);
        showAlert('danger', '❌ ' + msg);
      }
    });
  });

  // Initialize with sale items on page load
  if (SALE_ITEMS && SALE_ITEMS.length > 0) {
    // Create a row for each sale item
    SALE_ITEMS.forEach(function(item) {
      addNewReturnRow(item);
    });
  } else {
    // If no sale items, add one empty row
    addNewReturnRow();
  }
  updateGrandTotal();
});

// Number to words conversion
function numberToWords(num) {
  const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
  const teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
  const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
  const scales = ['', 'Thousand', 'Million', 'Billion'];

  if (num === 0) return 'Zero';

  let parts = [];
  let scaleIndex = 0;

  while (num > 0) {
    const part = num % 1000;
    if (part !== 0) {
      parts.unshift(convertHundreds(part) + (scaleIndex > 0 ? ' ' + scales[scaleIndex] : ''));
    }
    num = Math.floor(num / 1000);
    scaleIndex++;
  }

  return parts.join(' ');

  function convertHundreds(n) {
    let result = '';
    const hundreds = Math.floor(n / 100);
    const remainder = n % 100;

    if (hundreds > 0) result += ones[hundreds] + ' Hundred';
    if (remainder > 0) {
      if (result) result += ' ';
      if (remainder < 10) result += ones[remainder];
      else if (remainder < 20) result += teens[remainder - 10];
      else {
        result += tens[Math.floor(remainder / 10)];
        if (remainder % 10 > 0) result += ' ' + ones[remainder % 10];
      }
    }
    return result;
  }
}
</script>

@endsection

















