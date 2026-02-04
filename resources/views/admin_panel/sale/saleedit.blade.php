@extends('admin_panel.layout.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* ================= RESPONSIVE SALES UI ================= */

        * {
            box-sizing: border-box;
        }

        /* table container - no scroll on mobile */
        .table-responsive {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 360px;
            -webkit-overflow-scrolling: touch;
        }

        /* base table width - responsive */
        .sales-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* 🔹 DISCOUNT COLUMN – THORI SI BARI */
        .sales-table td.large-col {
            min-width: 95px;
            padding: 4px;
        }

        /* 🔹 DISCOUNT LAYOUT */
        .discount-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: nowrap;
        }

        /* helper text for discount input — absolutely positioned to avoid layout shift */
        .discount-help {
            position: absolute;
            left: 0;
            bottom: -36px;
            font-size: 0.85rem;
            line-height: 1;
            color: #dc3545; /* Bootstrap danger */
            background: rgba(255,255,255,0.9);
            padding: 0 4px;
            border-radius: 3px;
        }

        /* Further increased row height and cell padding so helper text fits comfortably */
        .sales-table td {
            padding-top: 1.2rem;
            padding-bottom: 1.4rem;
            vertical-align: middle;
        }

        .sales-table tbody tr {
            min-height: 86px;
        }

        /* 🔹 INPUT – NOT TOO SMALL */
        .discount-wrapper .discount-value {
            width: 60px;
            min-width: 60px;
            font-size: 0.8rem;
            padding: 4px 6px;
        }

        /* 🔹 PLUS ICON – NEAT & SMALL */
        .discount-wrapper .discount-plus {
            width: 22px;
            height: 22px;
            padding: 0;
            font-size: 13px;
            line-height: 1;
        }

        /* 🔹 DROPDOWN */
        .discount-wrapper .discount-type {
            position: absolute;
            right: 0;
            top: 115%;
            width: 65px;
            font-size: 0.75rem;
            z-index: 30;
        }

        /* ---------- DESKTOP (>= 1200px) ---------- */
        @media (min-width: 1200px) {
            .sales-table {
                width: 100%;
            }
        }

        /* ---------- TABLET (992px - 1199px) ---------- */
        @media (max-width: 1199px) and (min-width: 992px) {
            .main-container {
                max-width: 100%;
                margin: 0 auto;
                padding: 1rem;
            }

            .sales-table {
                width: 100%;
            }

            .sales-table td.product-col {
                min-width: 130px;
            }

            .sales-table td.small-col {
                width: 80px;
            }

            .sales-table td.medium-col {
                width: 90px;
            }

            .minw-350 {
                min-width: 100%;
            }

            .d-flex.gap-3 {
                flex-direction: column;
                gap: 1rem !important;
            }

            .items-panel {
                width: 100%;
                min-width: 0;
            }
        }

        /* ---------- MOBILE (768px - 991px) ---------- */
        @media (max-width: 991px) {
            .main-container {
                max-width: 100%;
                margin: 0 auto;
                padding: 1rem;
            }

            .header-text {
                font-size: 1rem;
            }

            .btn {
                padding: .35rem .5rem;
            }

            /* stack header buttons */
            .d-flex.justify-content-between.align-items-center {
                flex-wrap: wrap;
                gap: 8px;
            }

            /* customer + invoice panel full width */
            .minw-350 {
                width: 100%;
                min-width: 0;
            }

            /* reduce input font */
            .form-control,
            .form-select {
                font-size: .8rem;
            }

            /* CRITICAL: Make table responsive */
            .sales-table {
                width: 100%;
                font-size: 0.75rem;
            }

            .sales-table td.product-col {
                min-width: 100px;
            }

            .sales-table td.small-col {
                width: 60px;
            }

            .sales-table td.medium-col {
                width: 70px;
            }

            .sales-table td.action-col {
                width: 50px;
            }

            .d-flex.gap-3 {
                flex-direction: column;
                gap: 1rem !important;
            }

            .items-panel {
                width: 100%;
                min-width: 0;
                flex-grow: 1;
            }
        }

        /* ---------- SMALL PHONES (<= 576px) ---------- */
        @media (max-width: 576px) {
            .main-container {
                max-width: 100%;
                margin: 0 auto;
                padding: 0.75rem;
            }

            .sales-table {
                font-size: 0.65rem;
                width: 100%;
            }

            .table {
                --bs-table-padding-y: 0.2rem;
                --bs-table-padding-x: 0.3rem;
            }

            .sales-table td.product-col {
                min-width: 90px;
            }

            .sales-table td.small-col {
                width: 50px;
            }

            .sales-table td.medium-col {
                width: 60px;
            }

            .discount-wrapper .discount-value {
                width: 50px;
                min-width: 50px;
            }

            .minw-350 {
                min-width: 0;
                width: 100%;
            }

            .items-panel {
                width: 100%;
                min-width: 0;
            }

            .p-3 {
                padding: 0.75rem !important;
            }
        }
    </style>
    <style>
        /* ====== REMOVE SCROLL BAR STYLING ====== */

        /* Disable ALL scrolling for table */
        .table-responsive {
            overflow: auto;
            max-height: 360px;
        }

        .items-panel {
            overflow: visible;
            width: 100%;
            min-width: 0;
        }

        .main-container {
            font-size: .85rem;
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
            padding: 1rem;
        }

        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .container-fluid {
            padding-left: 0;
            padding-right: 0;
            width: 100%;
            max-width: 100%;
        }

        .header-text {
            font-size: 1.1rem;
        }

        .form-control,
        .form-select,
        .btn {
            font-size: .85rem;
            padding: .4rem .6rem;
            height: auto;
        }

        .invalid-cell {
            background-color: #fff5f5 !important;
            /* soft red */
            border: 1px solid #e3342f !important;
            /* red border */
        }

        .invalid-select,
        .invalid-input {
            border-color: #e3342f !important;
            box-shadow: none !important;
        }

        .input-readonly {
            background: #f9fbff;
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
            width: 100%;
        }

        .table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8f9fa;
            text-align: center;
        }

        .minw-350 {
            min-width: 360px;
        }

        .w-70 {
            width: 70px
        }

        .w-90 {
            width: 90px
        }

        .w-110 {
            width: 110px
        }

        .w-120 {
            width: 120px
        }

        .w-150 {
            width: 150px
        }

        .totals-card {
            background: #fcfcfe;
            border: 1px solid #eee;
            border-radius: .5rem;
        }

        .totals-card .row+.row {
            border-top: 1px dashed #e5e7eb;
        }

        .badge-soft {
            background: #eef2ff;
            color: #3730a3;
        }
    </style>
    <style>
        /* ===== Sales Table UI Fix ===== */
        .sales-table td.product-col {
            min-width: 180px;
        }

        .sales-table td.small-col {
            width: 110px;
        }

        .sales-table td.medium-col {
            width: 120px;
        }

        .sales-table td.action-col {
            width: 100px;
            text-align: center;
        }

        .input-readonly {
            background: #f1f3f5;
            font-weight: 600;
        }

        /* 🔥 FIX: Items panel overflow + Add Row cut issue */
        .items-panel {
            min-width: 0;        /* allow flex shrink */
            width: 100%;
            overflow: visible;
        }

        .items-panel > .d-flex {
            flex-wrap: wrap;
            gap: 8px;
        }

        @media (max-width: 768px) {
            #btnAdd {
                width: 100%;
            }
        }

        /* Select2 dropdown height + scroll */
        .select2-results__options {
            max-height: 200px;
            overflow-y: auto;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="main-container bg-white border shadow-sm mx-auto p-3 rounded-3">

            <div id="alertBox" class="alert d-none mb-3" role="alert"></div>

            <form id="saleForm" action="{{ route('sales.update', $sale->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')
                <input type="hidden" id="sale_id" name="sale_id" value="{{ $sale->id }}">

                {{-- HEADER --}}
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <div>
                        <small class="text-secondary" id="entryDateTime">Entry Date_Time: --</small> <br>
                        <a href="{{ route('sale.index') }}" target="_blank" rel="noopener"
                            class="btn btn-sm btn-outline-secondary" title="Sales List (opens new tab)">
                            Sales List
                        </a>
                    </div>

                    <h2 class="header-text text-secondary fw-bold mb-0">Sales Edit</h2>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-secondary me-2" id="entryDate">Date: --</small>
                        <button type="button" class="btn btn-sm btn-light border" id="btnHeaderPosted"
                            disabled>Posted</button>
                    </div>
                </div>

                <div class="d-flex gap-3 align-items-start border-bottom py-3">
                    {{-- LEFT: Invoice & Customer --}}
                    <div class="p-3 border rounded-3 minw-350">
                        <div class="section-title mb-3">Invoice & Customer</div>

                        <div class="mb-2 d-flex align-items-center gap-2">
                            <label class="form-label fw-bold mb-0">Invoice No.</label>
                            <input type="text" class="form-control input-readonly" name="invoice_no" style="width:150px"
                                value="{{ $sale->invoice_no }}" readonly>
                            <label class="form-label fw-bold mb-0">M. Inv#</label>
                            <input type="text" class="form-control" name="manual_invoice" placeholder="Manual invoice" value="{{ $sale->manual_invoice }}">
                        </div>

                        <!-- CUSTOMER SELECT -->
                        <div class="mb-2">
                            <label class="form-label fw-bold mb-1">Select Customer</label>
                            <select class="form-select" id="customerSelect">
                                <option selected disabled>Loading…</option>
                            </select>
                            <small class="text-muted" id="customerCountHint"></small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold mb-1">Customer</label>
                            <input type="hidden" id="customer_id" name="customer_id" value="{{ $sale->customer_id }}">
                            <input type="text" class="form-control" id="customerDisplay" name="customer_display" value="" readonly>
                            <small class="text-muted" id="customerCountHint"></small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold">Address</label>
                            <textarea class="form-control" id="address" name="address">{{ $sale->address }}</textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold">Tel</label>
                            <input type="text" class="form-control" id="tel" name="tel" value="{{ $sale->tel }}">
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks">{{ $sale->remarks }}</textarea>
                        </div>

                        <div class="mb-2 d-flex justify-content-between">
                            <span>Previous Balance</span>
                            <input type="text" class="form-control w-25 text-end" id="previousBalance" value="0">
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span>Credit Limit</span>
                            <input type="text" class="form-control w-25 text-end" id="creditLimit" value="0">
                        </div>

                        <div class="text-end mt-3">
                            <button id="clearCustomerData" type="button" class="btn btn-sm btn-secondary">Clear</button>
                        </div>
                    </div>

                    {{-- RIGHT: Items --}}
                    <div class="flex-grow-1 items-panel">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="section-title mb-0">Items</div>
                            <button type="button" class="btn btn-sm btn-primary" id="btnAdd">Add Row</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered sales-table mb-0">

                                <thead>
                                    <tr>
                                        <th style="width:10px">Product</th>
                                        <th style="width:10px">Stock</th>
                                        <th style="width:10px">Qty</th>
                                        <th style="width:10px">Retail Price</th>
                                        <th style="width:10px">Disc %</th>
                                        <th style="width:10px">Disc Amt</th>
                                        <th style="width:10px">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="salesTableBody">

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Total:</td>
                                        <td class="text-end fw-bold"><span id="totalAmount">0.00</span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Totals + Receipts --}}
                <div class="row g-3 mt-3">
                    <div class="col-lg-7">
                        <div class="section-title mb-2">Receipt Vouchers</div>
                        <div id="rvWrapper" class="border rounded-3 p-2">
                            <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                                <select class="form-select rv-account" name="receipt_account_id[]" style="max-width: 320px">
                                    @foreach ($accounts as $acc)
                                        <option value="" disabled>Select account</option>
                                        <option value="{{ $acc->id }}">{{ $acc->title }}</option>
                                    @endforeach
                                </select>
                                <input type="text" class="form-control text-end rv-amount" name="receipt_amount[]"
                                    placeholder="0.00" style="max-width:160px">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddRV">Add more</button>
                            </div>
                            <div class="text-end">
                                <span class="me-2">Receipts Total:</span>
                                <span class="fw-bold" id="receiptsTotal">0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="section-title mb-2">Totals</div>
                        <div class="totals-card p-3">
                            <div class="row py-1">
                                <div class="col-7 text-muted">Total Qty</div>
                                <div class="col-5 text-end"><span id="tQty">0</span></div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7 text-muted">Invoice Gross (Σ Sales Price × Qty)</div>
                                <div class="col-5 text-end"><span id="tGross">0.00</span></div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7 text-muted">Line Discount (on Retail)</div>
                                <div class="col-5 text-end"><span id="tLineDisc">0.00</span></div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7 fw-semibold">Sub-Total</div>
                                <div class="col-5 text-end fw-semibold"><span id="tSub">0.00</span></div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7">Additional Discount</div>
                                <div class="col-5 text-end d-flex justify-content-end align-items-center">
                                    <input type="text" class="form-control text-end me-2" name="discountPercent"
                                        id="discountPercent" value="0" style="max-width:120px;">
                                    <button type="button" id="orderDiscountToggle" class="btn btn-outline-secondary btn-sm"
                                        data-type="pkr">PKR</button>
                                </div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7 text-muted">Additional Discount Rs</div>
                                <div class="col-5 text-end"><span id="tOrderDisc">0.00</span></div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7 text-danger">Previous Balance</div>
                                <div class="col-5 text-end text-danger"><span id="tPrev">0.00</span></div>
                            </div>
                            <div class="row py-2">
                                <div class="col-7 fw-bold text-primary">Payable / Total Balance</div>
                                <div class="col-5 text-end fw-bold text-primary"><span id="tPayable">0.00</span></div>
                            </div>

                            {{-- hidden mirrors for backend --}}
                            <input type="hidden" name="subTotal1" id="subTotal1" value="0">
                            <input type="hidden" name="subTotal2" id="subTotal2" value="0">
                            <input type="hidden" name="discountAmount" id="discountAmount" value="0">
                            <input type="hidden" name="totalBalance" id="totalBalance" value="0">
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="d-flex flex-wrap gap-2 justify-content-center p-3 mt-3 border-top">
                    <button type="button" class="btn btn-sm btn-warning" id="btnRevert">Revert</button>
                    <button type="submit" class="btn btn-sm btn-success" id="btnSave">Update</button>
                    <button type="button" class="btn btn-sm btn-secondary" id="btnPrint">Print</button>
                    <button type="button" class="btn btn-sm btn-danger" id="btnDelete">Delete</button>
                    <button type="button" class="btn btn-sm btn-dark" id="btnExit">Exit</button>
                </div>
            </form>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const alertBox = document.getElementById('alertBox');
        if (!alertBox) return;

        const message = alertBox.innerText.trim();
        if (message === '') return;

        let icon = 'info';
        if (alertBox.classList.contains('alert-success')) icon = 'success';
        if (alertBox.classList.contains('alert-danger')) icon = 'error';
        if (alertBox.classList.contains('alert-warning')) icon = 'warning';

        Swal.fire({
            icon: icon,
            title: 'Message',
            text: message
        });

        alertBox.classList.add('d-none');
    });
</script>

<script>
    /* ---------- helpers ---------- */
    function pad(n) {
        return n < 10 ? '0' + n : n
    }

    function toNum(n) {
        return isNaN(parseFloat(n)) ? 0 : parseFloat(n);
    }

    function setNowStamp() {
        const d = new Date();
        const dt = `${pad(d.getDate())}-${pad(d.getMonth() + 1)}-${String(d.getFullYear()).slice(-2)} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
        const dOnly = `${pad(d.getDate())}-${pad(d.getMonth() + 1)}-${String(d.getFullYear()).slice(-2)}`;
        $('#entryDateTime').text('Entry Date_Time: ' + dt);
        $('#entryDate').text('Date: ' + dOnly);
    }
    setNowStamp();
    setInterval(setNowStamp, 60 * 1000);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
    });

    function showAlert(type, msg) {
        const el = $('#alertBox');
        el.removeClass('d-none alert-success alert-danger alert-warning alert-info').addClass('alert-' + type).text(msg);
        console.log('Showing alert:', msg);

        let icon = 'info';
        if (type === 'success') icon = 'success';
        else if (type === 'danger' || type === 'error') icon = 'error';
        else if (type === 'warning') icon = 'warning';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: icon,
                title: icon === 'success' ? 'Success' : (icon === 'error' ? 'Error' : 'Notice'),
                text: msg,
                timer: 3000,
                showConfirmButton: false
            });
        }

        setTimeout(() => el.addClass('d-none'), 2500);
    }

    function addNewRow() {
        $('#salesTableBody').append(`
      <tr>
        <input type="hidden" class="product-id" name="product_id[]">
        <input type="hidden" class="warehouse-id" name="warehouse_id[]">

        <td class="product-col">
          <input type="text" class="form-control product-search" placeholder="Search product..." autocomplete="off">
          <ul class="searchResults list-group mt-1"></ul>
        </td>

        <td class="small-col">
          <input type="text" class="form-control stock text-center input-readonly" readonly>
        </td>

        <td class="small-col">
          <input type="text" class="form-control sales-qty text-end" name="sales_qty[]">
        </td>

        <td class="medium-col">
          <input type="text" class="form-control retail-price text-end input-readonly" value="0" readonly name="retail_price[]">
        </td>

        <td class="large-col">
          <div class="discount-wrapper">
            <input type="text" class="form-control discount-value text-end" placeholder="0.00" name="discount_percentage[]">
            <button type="button" class="btn btn-outline-secondary discount-toggle" data-type="percent">%</button>
          </div>
        </td>

        <td class="medium-col">
          <input type="text" class="form-control discount-amount text-end" name="discount_amount[]">
        </td>

        <td class="medium-col">
          <input type="text" class="form-control sales-amount text-end input-readonly" name="sales_amount[]" value="0" readonly>
        </td>

        <td class="action-col">
          <button type="button" class="btn btn-sm btn-outline-danger del-row">&times;</button>
        </td>
      </tr>
      `);
    }

    function computeRow($row) {
        const qty = toNum($row.find('.sales-qty').val());
        const retailPrice = toNum($row.find('.retail-price').val());
        const discToggle = $row.find('.discount-toggle');
        const discType = discToggle.data('type');
        const discValue = toNum($row.find('.discount-value').val());

        let discAmount = 0;
        let discPercent = 0;

        if (discType === 'percent') {
            discPercent = discValue;
            discAmount = (retailPrice * qty * discPercent) / 100;
        } else {
            discAmount = discValue;
            discPercent = retailPrice > 0 ? (discAmount / (retailPrice * qty)) * 100 : 0;
        }

        const salesAmount = (retailPrice * qty) - discAmount;

        $row.find('.discount-percentage').val(discPercent.toFixed(2));
        $row.find('.discount-amount').val(discAmount.toFixed(2));
        $row.find('.sales-amount').val(Math.max(0, salesAmount).toFixed(2));
    }

    function updateGrandTotals() {
        let tQty = 0;
        let tGross = 0;
        let tLineDisc = 0;
        let tSub = 0;

        $('#salesTableBody tr').each(function () {
            const qty = toNum($(this).find('.sales-qty').val());
            const retailPrice = toNum($(this).find('.retail-price').val());
            const discAmount = toNum($(this).find('.discount-amount').val());

            tQty += qty;
            tGross += (qty * retailPrice);
            tLineDisc += discAmount;
        });

        tSub = tGross - tLineDisc;

        const orderDisc = toNum($('#discountPercent').val());
        const orderDiscToggle = $('#orderDiscountToggle');
        const orderDiscType = orderDiscToggle.data('type');

        let tOrderDisc = 0;
        if (orderDiscType === 'pkr') {
            tOrderDisc = orderDisc;
        } else {
            tOrderDisc = (tSub * orderDisc) / 100;
        }

        const tPrev = toNum($('#previousBalance').val());
        const tPayable = tSub - tOrderDisc + tPrev;

        $('#tQty').text(tQty.toFixed(0));
        $('#tGross').text(tGross.toFixed(2));
        $('#tLineDisc').text(tLineDisc.toFixed(2));
        $('#tSub').text(tSub.toFixed(2));
        $('#tOrderDisc').text(tOrderDisc.toFixed(2));
        $('#tPrev').text(tPrev.toFixed(2));
        $('#tPayable').text(tPayable.toFixed(2));
        $('#totalAmount').text(tSub.toFixed(2));

        // hidden mirrors
        $('#subTotal1').val(tGross.toFixed(2));
        $('#subTotal2').val(tSub.toFixed(2));
        $('#discountAmount').val(tOrderDisc.toFixed(2));
        $('#totalBalance').val(tPayable.toFixed(2));
    }

    $(document).ready(function () {
        function init() {
            // Load existing items
            loadSaleItems();
            loadCustomerData();
            loadReceipts();
            updateGrandTotals();
        }

        init();

        // Change discount type
        $(document).on('click', '.discount-toggle', function () {
            const $btn = $(this);
            const currentType = $btn.data('type');

            if (currentType === 'percent') {
                $btn.data('type', 'pkr').text('PKR');
            } else {
                $btn.data('type', 'percent').text('%');
            }

            const $row = $btn.closest('tr');
            computeRow($row);
            updateGrandTotals();
        });

        // Change order discount type
        $(document).on('click', '#orderDiscountToggle', function () {
            const $btn = $(this);
            const currentType = $btn.data('type');

            if (currentType === 'pkr') {
                $btn.data('type', 'percent').text('%');
            } else {
                $btn.data('type', 'pkr').text('PKR');
            }

            updateGrandTotals();
        });

        // Add row
        $('#btnAdd').on('click', function () {
            addNewRow();
        });

        // ✅ Add Receipt Voucher Row
        $('#btnAddRV').on('click', function () {
            const $firstRow = $('#rvWrapper .rv-row:first');
            const $newRow = $firstRow.clone();
            $newRow.find('input').val('');
            $newRow.appendTo('#rvWrapper');
        });

        // ✅ Receipt amount input - update total
        $(document).on('input', '.rv-amount', function () {
            recalcReceiptTotal();
        });

        // Delete row
        $(document).on('click', '.del-row', function () {
            $(this).closest('tr').remove();
            updateGrandTotals();
        });

        // Row calculations
        $(document).on('input', '.sales-qty, .retail-price, .discount-value', function () {
            const $row = $(this).closest('tr');
            computeRow($row);
            updateGrandTotals();
        });

        // Order discount
        $('#discountPercent').on('input', function () {
            updateGrandTotals();
        });

        // Customer selection
        $('#customerSelect').on('change', function () {
            const id = $(this).val();
            $('#customer_id').val(id);
            if (!id) return;

            $.get(
                '{{ route("salecustomers.show", "__ID__") }}'.replace('__ID__', id),
                function (d) {
                    $('#address').val(d.address || '');
                    $('#tel').val(d.mobile || '');
                    $('#remarks').val(d.remarks || '');
                    $('#creditLimit').val(d.credit_limit || '0');
                    $('#customerDisplay').val((d.customer_name || '') + ' — ' + (d.customer_id || ''));
                    let previousBalance = parseFloat(d.closing_balance || d.opening_balance || 0);
                    $('#previousBalance').val(previousBalance.toFixed(2));
                    updateGrandTotals();
                }
            );
        });

        // Clear customer
        $('#clearCustomerData').on('click', function () {
            $('#customerSelect').val('');
            $('#customer_id').val('');
            $('#customerDisplay').val('');
            $('#address, #tel, #remarks').val('');
            $('#previousBalance').val('0');
            $('#creditLimit').val('0');
            updateGrandTotals();
        });

        // Exit button
        $('#btnExit').on('click', function () {
            window.location.href = '{{ route("sale.index") }}';
        });

        // Revert button
        $('#btnRevert').on('click', function () {
            location.reload();
        });

        // Delete button
        $('#btnDelete').on('click', function () {
            if (confirm('Are you sure you want to delete this sale?')) {
                const form = $('<form method="POST" action="{{ route("sales.destroy", $sale->id) }}"></form>');
                form.append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
                form.append('<input type="hidden" name="_method" value="DELETE">');
                form.appendTo('body').submit();
            }
        });
    });

    function loadSaleItems() {
        const saleItems = @json($saleItems);
        saleItems.forEach(item => {
            addNewRow();
            const $row = $('#salesTableBody tr:last');
            $row.find('.product-id').val(item.product_id);
            $row.find('.product-search').val(item.item_name);
            $row.find('.stock').val(item.onhand_qty || '0');
            $row.find('.sales-qty').val(item.qty);
            $row.find('.retail-price').val(item.price.toFixed(2));
            $row.find('[name="discount_percentage[]"]').val(item.discount_percent || '0');
            $row.find('[name="discount_amount[]"]').val(item.discount_amount || '0');
            $row.find('.sales-amount').val(item.total.toFixed(2));
            computeRow($row);
        });
        updateGrandTotals();
    }

    function loadReceipts() {
        const receipts = @json($receipts ?? []);
        if (!receipts || receipts.length === 0) return;

        // Clear existing rows except first one
        $('#rvWrapper .rv-row:not(:first)').remove();
        
        // Keep track of which account+amount combinations we've added
        let rowIndex = 0;
        
        receipts.forEach(rv => {
            // Get the account ID - it's stored as a number or JSON
            let accountId = rv.row_account_id;
            if (typeof accountId === 'string' && accountId.startsWith('[')) {
                try {
                    accountId = JSON.parse(accountId)[0];
                } catch (e) {
                    accountId = parseInt(accountId);
                }
            }
            accountId = parseInt(accountId);

            // Get the amount - it's stored in amount or total_amount field
            let amount = rv.amount || rv.total_amount || 0;
            if (typeof amount === 'string' && amount.startsWith('[')) {
                try {
                    amount = JSON.parse(amount)[0];
                } catch (e) {
                    amount = parseFloat(amount);
                }
            }
            amount = parseFloat(amount);

            // Get the first row or clone it
            let $row;
            if (rowIndex === 0) {
                $row = $('#rvWrapper .rv-row:first');
            } else {
                $row = $('#rvWrapper .rv-row:first').clone();
                $row.appendTo('#rvWrapper');
            }

            // Set values
            $row.find('.rv-account').val(accountId || '');
            $row.find('.rv-amount').val(amount > 0 ? amount.toFixed(2) : '');

            rowIndex++;
        });

        recalcReceiptTotal();
    }

    function recalcReceiptTotal() {
        let total = 0;
        $('#rvWrapper .rv-amount').each(function() {
            let val = parseFloat($(this).val()) || 0;
            if (val > 0) total += val;
        });
        $('#receiptsTotal').text(total.toFixed(2));
    }

    function loadCustomerData() {
        const customerId = $('#customer_id').val();
        if (!customerId) return;

        $.get(
            '{{ route("salecustomers.show", "__ID__") }}'.replace('__ID__', customerId),
            function (d) {
                $('#customerDisplay').val((d.customer_name || '') + ' — ' + (d.customer_id || ''));
                $('#address').val(d.address || '');
                $('#tel').val(d.mobile || '');
                $('#remarks').val(d.remarks || '');
                $('#creditLimit').val(d.credit_limit || '0');
                let previousBalance = parseFloat(d.closing_balance || d.opening_balance || 0);
                $('#previousBalance').val(previousBalance.toFixed(2));
            }
        );

        // Load customer list for dropdown
        $.get('{{ route("salecustomers.index") }}', { type: 'Main Customer' }, function (data) {
            let html = '<option value="">-- Select --</option>';
            if (data.length > 0) {
                data.forEach(row => {
                    const label = (row.customer_name || '(No name)') + ' — ' + (row.customer_id || '');
                    html += `<option value="${row.id}" ${row.id == customerId ? 'selected' : ''}>` + label + `</option>`;
                });
            }
            $('#customerSelect').html(html);
        });
    }
</script>
                            id="extraDiscount" value="0"></td>
                    <td><input type="text" name="total_net" class="form-control form-control-sm text-center"
                            id="netAmount" readonly></td>
                    <td><input type="number" name="cash" class="form-control form-control-sm text-center"
                            id="cash" value="0"></td>
                    <td><input type="number" name="card" class="form-control form-control-sm text-center"
                            id="card" value="0"></td>
                    <td><input type="text" name="change" class="form-control form-control-sm text-center"
                            id="change" readonly></td>
                </tr>
            </table>

            {{-- Buttons --}}
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <strong>TOTAL PIECES : </strong> <span id="totalPieces">0</span>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Helper
        function num(n) {
            return isNaN(parseFloat(n)) ? 0 : parseFloat(n);
        }

        function numberToWords(num) {
            const a = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten",
                "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen",
                "Eighteen", "Nineteen"
            ];
            const b = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];
            if ((num = num.toString()).length > 9) return "Overflow";
            const n = ("000000000" + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{3})$/);
            if (!n) return;
            let str = "";
            str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + " " + a[n[1][1]]) + " Crore " : "";
            str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + " " + a[n[2][1]]) + " Lakh " : "";
            str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + " " + a[n[3][1]]) + " Thousand " : "";
            str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + " " + a[n[4][1]]) + " " : "";
            return str.trim() + " Rupees Only";
        }

        function recalcRow($row) {
            const qty = num($row.find('.quantity').val());
            const price = num($row.find('.price').val());
            const disc = num($row.find('.item_disc').val()); // per item discount (not total)

            // total = (qty × price) – (qty × disc)
            let total = (qty * price) - (qty * disc);
            if (total < 0) total = 0;

            $row.find('.row-total').val(total.toFixed(2));
        }


        function recalcSummary() {
            let billAmount = 0; // sum of all row totals
            let itemDiscount = 0; // total of (qty × disc) from all rows
            let totalQty = 0;

            $('#saleItems tr').each(function() {
                const qty = num($(this).find('.quantity').val());
                const price = num($(this).find('.price').val());
                const disc = num($(this).find('.item_disc').val());

                billAmount += (qty * price);
                itemDiscount += (qty * disc);
                totalQty += qty;
            });

            const extraDiscount = num($('#extraDiscount').val());
            const cash = num($('#cash').val());
            const card = num($('#card').val());

            const net = billAmount - itemDiscount - extraDiscount;
            const change = (cash + card) - net;

            $('#billAmount').val(billAmount.toFixed(2));
            $('#itemDiscount').val(itemDiscount.toFixed(2));
            $('#netAmount').val(net.toFixed(2));
            $('#change').val(change.toFixed(2));
            $('#amountInWords').val(numberToWords(Math.round(net)));

            $('#totalPieces').text(totalQty);
        }


        // Events
        // Row inputs change → recalc
        $(document).on('input', '#saleItems .quantity, #saleItems .price, #saleItems .item_disc', function() {
            const $row = $(this).closest('tr');
            recalcRow($row);
            recalcSummary();
        });
        // Initialize
        // Remove row
        $(document).on('click', '#saleItems .remove-row', function() {
            $(this).closest('tr').remove();
            recalcSummary();
        });

        // Extra discount, cash, card change
        $('#extraDiscount, #cash, #card').on('input', function() {
            recalcSummary();
        });

        // Init on page load
        $('#saleItems tr').each(function() {
            recalcRow($(this));
        });
        recalcSummary();
    });
</script>
<script>
    $(document).ready(function() {
        // Prevent Enter key from submitting form in product search
        $(document).on('keydown', '.productSearch', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // stops form submission
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const cancelBtn = document.getElementById('cancelBtn');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This will cancel your changes!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, go back!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '';
                        }
                    });
                });
            }
        });

        $(document).ready(function() {

            // Helper
            function num(n) {
                return isNaN(parseFloat(n)) ? 0 : parseFloat(n);
            }

            // Row calculation
            function recalcRow($row) {
                const qty = num($row.find('.quantity').val());
                const price = num($row.find('.price').val());
                const disc = num($row.find('.item_disc').val()); // per item discount

                let total = (qty * price) - (qty * disc);
                if (total < 0) total = 0;

                $row.find('.row-total').val(total.toFixed(2));
            }

            function recalcSummary() {
                let billAmount = 0;
                let itemDiscount = 0;
                let totalQty = 0;

                $('#saleItems tr').each(function() {
                    const qty = num($(this).find('.quantity').val());
                    const price = num($(this).find('.price').val());
                    const disc = num($(this).find('.item_disc').val());

                    billAmount += qty * price;
                    itemDiscount += qty * disc;
                    totalQty += qty;
                });

                const extraDiscount = num($('#extraDiscount').val());
                const cash = num($('#cash').val());
                const card = num($('#card').val());

                const net = billAmount - itemDiscount - extraDiscount;
                const change = (cash + card) - net;

                $('#billAmount').val(billAmount.toFixed(2));
                $('#itemDiscount').val(itemDiscount.toFixed(2));
                $('#netAmount').val(net.toFixed(2));
                $('#change').val(change.toFixed(2));
                $('#amountInWords').val(numberToWords(Math.round(net)));

                $('#totalPieces').text(totalQty);
            }

            function numberToWords(num) {
                if (num === 0) return "Zero Rupees Only";
                return num + " Rupees Only"; // aap apna full converter rakh sakte ho
            }


            $('#overallDiscount, #extraCost, #paidAmount').on('input', function() {
                recalcSummary();
            });



            function appendBlankRow() {
                const newRow = `
    <tr>
        <td>
            <input type="hidden" name="product_id[]" class="product_id">
            <input type="text" class="form-control productSearch" placeholder="Enter product name..." autocomplete="off">
            <ul class="searchResults list-group mt-1"></ul>
        </td>
        <td><input type="text" name="item_code[]" class="form-control item_code" readonly></td>
        <td>
            <select name="color[new][]" class="form-control select2-color" multiple></select>
        </td>
        <td><input type="text" name="brand[]" class="form-control brand" readonly></td>
        <td><input type="text" name="unit[]" class="form-control unit" readonly></td>
        <td><input type="number" step="0.01" name="price[]" class="form-control price" value="1"></td>
        <td><input type="number" step="0.01" name="item_disc[]" class="form-control item_disc" value="0"></td>
        <td><input type="number" name="qty[]" class="form-control quantity" value="1" min="1"></td>
        <td><input type="text" name="total[]" class="form-control row-total" readonly></td>
        <td><button type="button" class="btn btn-sm btn-danger remove-row">X</button></td>
    </tr>`;
                $('#saleItems').append(newRow);
            }

            // Edit form me bhi ek default blank row ho
            if ($("#saleItems tr").length > 0) {
                appendBlankRow();
            }


            // ---------- Product Search (AJAX) ----------
            $(document).on('keyup', '.productSearch', function(e) {
                const $input = $(this);
                const q = $input.val().trim();
                const $row = $input.closest('tr');
                const $box = $row.find('.searchResults');

                // Keyboard navigation (Arrow Up/Down + Enter)
                const isNavKey = ['ArrowDown', 'ArrowUp', 'Enter'].includes(e.key);
                if (isNavKey && $box.children('.search-result-item').length) {
                    const $items = $box.children('.search-result-item');
                    let idx = $items.index($items.filter('.active'));
                    if (e.key === 'ArrowDown') {
                        idx = (idx + 1) % $items.length;
                        $items.removeClass('active');
                        $items.eq(idx).addClass('active');
                        e.preventDefault();
                        return;
                    }
                    if (e.key === 'ArrowUp') {
                        idx = (idx <= 0 ? $items.length - 1 : idx - 1);
                        $items.removeClass('active');
                        $items.eq(idx).addClass('active');
                        e.preventDefault();
                        return;
                    }
                    if (e.key === 'Enter') {
                        if (idx >= 0) {
                            $items.eq(idx).trigger('click');
                        } else if ($items.length === 1) {
                            $items.eq(0).trigger('click');
                        }
                        e.preventDefault();
                        return;
                    }
                }

                // Normal fetch
                if (q.length === 0) {
                    $box.empty();
                    return;
                }

                $.ajax({
                    url: "{{ route('search-products') }}",
                    type: 'GET',
                    data: {
                        q
                    },
                    success: function(data) {
                        let html = '';
                        (data || []).forEach(p => {
                            const brand = (p.brand && p.brand.name) ? p.brand.name : '';
                            const unit = (p.unit_id ?? '');
                            const price = (p.wholesale_price ?? 0);
                            const code = (p.item_code ?? '');
                            const name = (p.item_name ?? '');
                            const id = (p.id ?? '');
                            html += `
<li class="list-group-item search-result-item"
    tabindex="0"
    data-product-id="${id}"
    data-product-name="${name}"
    data-product-brand="${brand}"
    data-product-unit="${unit}"
    data-product-code="${code}"
    data-price="${price}">
    ${name} - ${code} - Rs. ${price}
</li>`;
                        });
                        $box.html(html);

                        // first item active for quick Enter
                        $box.children('.search-result-item').first().addClass('active');
                    },
                    error: function() {
                        $box.empty();
                    }
                });
            });

            // Click/Enter on suggestion
            $(document).on('click', '.search-result-item', function() {
                const $li = $(this);
                const $row = $li.closest('tr');

                $row.find('.productSearch').val($li.data('product-name'));
                $row.find('.item_code').val($li.data('product-code'));
                $row.find('.brand').val($li.data('product-brand'));
                $row.find('.unit').val($li.data('product-unit'));
                $row.find('.price').val($li.data('price'));
                $row.find('.product_id').val($li.data('product-id'));

                $row.find('.product_id').val($li.data('product-id'));

                // reset qty & discount for fresh calc
                $row.find('.quantity').val(1);
                $row.find('.item_disc').val(0);

                recalcRow($row);
                recalcSummary();

                // clear results
                $row.find('.searchResults').empty();

                // append new blank row and focus its search
                appendBlankRow();
                $('#saleItems tr:last .productSearch').focus();
            });

            // Also allow keyboard Enter selection when list focused
            $(document).on('keydown', '.searchResults .search-result-item', function(e) {
                if (e.key === 'Enter') {
                    $(this).trigger('click');
                }
            });

            // Row calculations
            $('#purchaseItems').on('input', '.quantity, .price, .item_disc', function() {
                const $row = $(this).closest('tr');
                recalcRow($row);
                recalcSummary();
            });

            // Remove row
            $('#purchaseItems').on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                recalcSummary();
            });

            // Summary inputs
            $('#overallDiscount, #extraCost').on('input', function() {
                recalcSummary();
            });

            // init first row values
            recalcRow($('#purchaseItems tr:first'));
            recalcSummary();
        });




    });
</script>

@endsection