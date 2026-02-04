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

        /* .sales-table td.warehouse-col {
          min-width: 170px;
      } */
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
            max-height: 200px;   /* yahan height set karo */
            overflow-y: auto;   /* 🔥 scroll enable */
        }
    </style>



    <div class="container-fluid py-4">
        <div class="main-container bg-white border shadow-sm mx-auto p-3 rounded-3">

            {{-- <div id="alertBox" class="alert d-none mb-3" role="alert"></div> --}}
            <div id="alertBox" class="alert d-none mb-3" role="alert"></div>


            <form id="saleForm" autocomplete="off">
                @csrf
                <input type="hidden" id="booking_id" name="booking_id" value="">
                <input type="hidden" id="action" name="action" value="save">

                {{-- HEADER --}}
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <div>
                        <small class="text-secondary" id="entryDateTime">Entry Date_Time: --</small> <br>
                        <a href="{{ route('sale.index') }}" target="_blank" rel="noopener"
                            class="btn btn-sm btn-outline-secondary" title="Sales List (opens new tab)">
                            Sales List
                        </a>
                    </div>


                    <h2 class="header-text text-secondary fw-bold mb-0">Sales</h2>


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
                            <input type="text" class="form-control input-readonly" name="Invoice_no" style="width:150px"
                                value="{{ $nextInvoiceNumber }}" readonly>
                            <label class="form-label fw-bold mb-0">M. Inv#</label>
                            <input type="text" class="form-control" name="Invoice_main" placeholder="Manual invoice">
                        </div>

                        {{-- Type toggle --}}
                        <div class="mb-2">
                            <label class="form-label fw-bold mb-1 d-block">Type</label>
                            <div class="btn-group" role="group" id="partyTypeGroup">
                                <input type="radio" class="btn-check" name="partyType" id="typeCustomers"
                                    value="Main Customer" checked>
                                <label class="btn btn-outline-primary btn-sm" for="typeCustomers">Customers</label>

                                <input type="radio" class="btn-check" name="partyType" id="typeWalkin"
                                    value="Walking Customer">
                                <label class="btn btn-outline-primary btn-sm" for="typeWalkin">Walk-in</label>

                                {{-- <input type="radio" class="btn-check" name="partyType" id="typeVendors" value="vendor">
                                <label class="btn btn-outline-primary btn-sm" for="typeVendors">Vendors</label> --}}
                            </div>
                        </div>

                        <!-- CUSTOMER SELECT -->
                        <div class="mb-2">
                            <label class="form-label fw-bold mb-1">Select Customer</label>
                            <select class="form-select" id="customerSelect">
                                <option selected disabled>Loading…</option>
                            </select>
                            <small class="text-muted" id="customerCountHint"></small>
                        </div>
                        {{-- ///////////////////// --}}
                        <div class="mb-2">
                            <label class="form-label fw-bold mb-1">Customer</label>
                            <input type="hidden" id="customer_id" name="customer_id" value="">
                            <input type="hidden" id="customer" name="customer" value="">
                            <input type="text" class="form-control" id="customerDisplay" name="customer_display" value="" readonly>
                            <small class="text-muted" id="customerCountHint"></small>
                        </div>


                        <div class="mb-2">
                            <label class="form-label fw-bold">Address</label>
                            <textarea class="form-control" id="address" name="address"></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold">Tel</label>
                            <input type="text" class="form-control" id="tel" name="tel">
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks"></textarea>
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
                    <div class="flex-grow-1 items-panel" >

                    <!-- <div class="flex-grow-1" style="border:2px solid red;"> -->
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
                                        {{-- <td></td> --}}
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
                                <div class="col-7">Aditional Discount</div>
                                <div class="col-5 text-end d-flex justify-content-end align-items-center">
                                    <input type="text" class="form-control text-end me-2" name="discountPercent"
                                        id="discountPercent" value="0" style="max-width:120px;">
                                    <button type="button" id="orderDiscountToggle" class="btn btn-outline-secondary btn-sm"
                                        data-type="pkr">PKR</button>
                                </div>
                            </div>
                            <div class="row py-1">
                                <div class="col-7 text-muted">Aditional Discount Rs</div>
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

                            {{-- Notify Me (optional days) --}}
                            <div class="row py-2 border-top mt-2">
                                <div class="col-7 text-muted medium">Notify Me (Days - Optional)</div>
                                <div class="col-5">
                                    <input type="number" name="notify_me" id="notify_me"  class="form-control form-control-sm" 
                                           placeholder="Enter payment Expected days" min="0" max="365" value="">
                                </div>
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
                    <button type="button" class="btn btn-sm btn-primary" id="btnEdit">Edit</button>
                    <button type="button" class="btn btn-sm btn-warning" id="btnRevert">Revert</button>

                    <button type="button" class="btn btn-sm btn-success" id="btnSave">Save</button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="faraz()" id="btnPosted"
                        disabled>Posted</button>

                    <button type="button" class="btn btn-sm btn-secondary" id="btnPrint">Print</button>
                    <button type="button" class="btn btn-sm btn-secondary" id="btnPrint2">Print-2</button>
                    <button type="button" class="btn btn-sm btn-secondary" id="btnDCPrint">DC Print</button>

                    <button type="button" class="btn btn-sm btn-danger" id="btnDelete">Delete</button>
                    <button type="button" class="btn btn-sm btn-dark" id="btnExit">Exit</button>
                </div>
            </form>
        </div>
    </div>

    {{-- product search model --}}

    <div class="modal fade" id="productSearchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Search Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- Search input -->
                    <input type="text" id="productSearchInput" class="form-control mb-3"
                        placeholder="Search product by name...">

                    <!-- Product list -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="productSearchResults">
                                <tr>
                                    <td colspan="2" class="text-center">Type to search...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Warehouse Selection Modal -->
    <div class="modal fade" id="warehouseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="warehouseModalBody">
                    <!-- Warehouses will be loaded here -->
                </div>
            </div>
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
        function faraz() {
            const productIds = [];
            $('#salesTableBody tr').each(function () {
                const pid = $(this).find('.product-select').val();
                if (pid) productIds.push(pid);
            });
            if (productIds.length === 0) {
                alert('Please select at least one product!');
                return;
            }
            $.ajax({
                url: '/get-warehouses/',
                type: 'GET',
                data: { product_ids: productIds },
                success: function (res) {
                    // Build per-product rows with warehouse dropdowns
                    if (!Array.isArray(res) || res.length === 0) {
                        $('#warehouseModalBody').html('<div class="text-center">No warehouses available for selected products</div>');
                        $('#warehouseModal').modal('show');
                        return;
                    }

                    let html = '<form id="warehouseSelectForm">';
                    html += '<table class="table table-sm"><thead><tr><th>Product</th><th>Warehouse</th></tr></thead><tbody>';
                    res.forEach(function (row) {
                        const pid = row.product_id;
                        const pname = row.product_name;
                        const whs = row.warehouses || [];

                        html += `<tr data-product-id="${pid}">`;
                        html += `<td>${pname}</td>`;
                        html += `<td>`;
                        html += `<select class="form-control form-select warehouse-for-product" data-product-id="${pid}">`;
                        if (whs.length === 0) {
                            html += `<option value="">No stock in any warehouse</option>`;
                        } else {
                            html += `<option value="">Select warehouse</option>`;
                            whs.forEach(function (w) {
                                html += `<option value="${w.warehouse_id}" data-qty="${w.quantity}">${w.warehouse_name} (qty: ${w.quantity})</option>`;
                            });
                        }
                        html += `</select>`;
                        html += `</td>`;
                        html += `</tr>`;
                    });
                    html += '</tbody></table>';
                    html += '<div class="text-end"><button type="button" id="warehouseApplyBtn" class="btn btn-primary">Apply</button></div>';
                    html += '</form>';

                    $('#warehouseModalBody').html(html);
                    $('#warehouseModal').modal('show');
                },
                error: function (err) {
                    console.error(err);
                    alert('Something went wrong fetching warehouses!');
                }
            });
        }

        // Apply per-product warehouse selections
        $(document).on('click', '#warehouseApplyBtn', function () {
            const mapping = {};
            $('.warehouse-for-product').each(function () {
                const pid = $(this).data('product-id');
                const wid = $(this).val();
                if (pid && wid) mapping[pid] = wid;
            });

            // write mapping into hidden inputs in table rows
            $('#salesTableBody tr').each(function () {
                const pid = $(this).find('.product-select').val();
                if (!pid) return;
                const wid = mapping[pid] || '';
                const $warehouseInput = $(this).find('.warehouse-id');
                $warehouseInput.attr('name', `warehouse_id[${pid}]`).val(wid);
            });

            $('#warehouseModal').modal('hide');
            ensureSaved().then(postNow);
        });


    </script>








    {{--faarz memon --}}
    <script>
        let CURRENT_PRODUCT_ROW = null;

        // faraz memon



        $(document).on('click', '.select-product', function () {

            if (!CURRENT_PRODUCT_ROW) return;

            const id = $(this).data('id');
            const name = $(this).data('name');
            const stock = $(this).data('stock');
            const price = $(this).data('price');

            // product dropdown me add + select
            const $productSelect = CURRENT_PRODUCT_ROW.find('.product-select');

            if ($productSelect.find(`option[value="${id}"]`).length === 0) {
                $productSelect.append(`<option value="${id}">${name}</option>`);
            }

            $productSelect.val(id).trigger('change');

            // stock & price set
            CURRENT_PRODUCT_ROW.find('.stock').val(stock);
            CURRENT_PRODUCT_ROW.find('.retail-price').val(price);

            // modal close
            $('#productSearchModal').modal('hide');

            // qty par focus
            setTimeout(() => {
                CURRENT_PRODUCT_ROW.find('.sales-qty').focus();
            }, 200);
        });
    </script>










    {{--faarz memon --}}


    <script>
        window.RECEIPT_ACCOUNTS = @json($accounts);
    </script>
    <script>
        function loadAccountsInto($select) {

            const currentVal = $select.val(); // 🔒 preserve selection
            let usedAccounts = [];

            $('.rv-account').each(function () {
                const val = $(this).val();
                if (val && this !== $select[0]) {
                    usedAccounts.push(String(val));
                }
            });

            let html = '<option value="">Select account</option>';

            window.RECEIPT_ACCOUNTS.forEach(function (acc) {
                const accId = String(acc.id);

                if (!usedAccounts.includes(accId) || accId === String(currentVal)) {
                    html += `<option value="${accId}">${acc.title}</option>`;
                }
            });

            $select.html(html);

            // 🔥 restore selected value
            if (currentVal) {
                $select.val(currentVal);
            }
        }
    </script>


    <script>
        $(document).ready(function () {
            loadAccountsInto($('.rv-account').first());
        });

    </script>

    <!--fgdffhjkjkhgkhkh  -->

    <script>
        $(document).ready(function () {
            function init() {
                addNewRow();
                loadCustomersByType('Main Customer');
                // loadAccountsInto($('.rv-account').first());
                updateGrandTotals();
                refreshPostedState();
            }

            init();
            // 🔹 Load customers on page load
            // loadCustomersByType('customer');

            // 🔹 Change customer type (radio)
            $(document).on('change', 'input[name="partyType"]', function () {
                $('#customerSelect').val('');

                $('#address,#tel,#remarks').val('');
                $('#previousBalance').val('0');
                $('#creditLimit').val('0');
                loadCustomersByType(this.value);
            });

            // 🔹 Load customers list
            function loadCustomersByType(type) {
                //alert('loadCustomersByType CALLED → ' + type);
                $('#customerSelect')
                    .prop('disabled', true)
                    .html('<option selected disabled>Loading…</option>');

                $.get('{{ route("salecustomers.index") }}', { type: type }, function (data) {

                    let html = '<option value="">-- Select --</option>';

                    if (data.length > 0) {
                        data.forEach(row => {
                            // show customer name first for clarity
                            const label = (row.customer_name || '(No name)') + ' — ' + (row.customer_id || '');
                            html += `<option value="${row.id}">` + label + `</option>`;
                        });
                        $('#customerCountHint').text(data.length + ' record(s) found');
                    } else {
                        html += '<option disabled>No record found</option>';
                        $('#customerCountHint').text('No record found');
                    }

                    $('#customerSelect').html(html).prop('disabled', false);
                });
            }

            // 🔹 When customer selected → load detail
        $(document).on('change', '#customerSelect', function () {
    const id = $(this).val();
    $('#customer_id').val(id);
    $('#customer').val(id);
    if (!id) return;

    $.get(
        '{{ route("salecustomers.show", "__ID__") }}'.replace('__ID__', id),
        function (d) {

            // basic customer info
            $('#address').val(d.address || '');
            $('#tel').val(d.mobile || '');
            $('#remarks').val(d.remarks || d.status || '');
            $('#creditLimit').val(d.credit_limit || '0');


                    // Display selected customer's id + name in the visible field
                    $('#customerDisplay').val((d.customer_name || '') + ' — ' + (d.customer_id || ''));

            // 🔹 Use closing_balance attribute (automatically gets latest ledger balance)
            // Fallback to opening_balance if no closing_balance exists
            let previousBalance = parseFloat(d.closing_balance || d.opening_balance || 0);
            
            // show balance (positive / negative as-it-is)
            $('#previousBalance').val(previousBalance.toFixed(2));

        }
    );
});

            // 🔹 Clear button
            $('#clearCustomerData').on('click', function () {
                $('#customerSelect').val('');
                $('#customer_id, #customer, #customerDisplay').val('');
                $('#address,#tel,#remarks').val('');
                $('#previousBalance').val('0');
            });

        });
    </script>










    <script>
        /* ---------- helpers ---------- */
        function pad(n) {
            return n < 10 ? '0' + n : n
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
        $('.js-customer').select2();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });

        function showAlert(type, msg) {
            const el = $('#alertBox');
            el.removeClass('d-none alert-success alert-danger alert-warning alert-info').addClass('alert-' + type).text(msg);
            console.log('Showing alert:', msg);

            // Map our types to SweetAlert icons
            let icon = 'info';
            if (type === 'success') icon = 'success';
            else if (type === 'danger' || type === 'error') icon = 'error';
            else if (type === 'warning') icon = 'warning';

            // Show SweetAlert2 popup with the message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: icon,
                    title: icon === 'success' ? 'Success' : (icon === 'error' ? 'Error' : 'Notice'),
                    text: msg,
                    timer: 3000,
                    showConfirmButton: false
                });
            }

            // keep the inline alert as a short-lived mirror
            setTimeout(() => el.addClass('d-none'), 2500);
        }

        function addNewRow() {
            $('#salesTableBody').append(`
      <tr>
        <!-- hidden warehouse -->
    <input type="hidden"  class="warehouse-id" >

        <!-- PRODUCT -->
    <td class="product-col">
      <div class="input-group">


       <select class="form-select product-select" name="product_id[]" style="width:100%">
    <option value="">Search product...</option>
</select>

      </div>
    </td>

 





        <!-- STOCK -->
        <td class="small-col">
          <input type="text"  class="form-control stock text-center input-readonly" readonly>
        </td>



        <!-- QTY -->
        <td class="small-col">
          <input type="text" class="form-control sales-qty text-end" id="sales-qty" name="sales_qty[]">
        </td>

        <!-- RETAIL PRICE -->
        <td class="medium-col">
          <input type="text" id="retail-price" class="form-control retail-price text-end input-readonly" value="0" readonly name="retail_price[]">
        </td>

    <!-- DISCOUNT -->
    <!-- DISCOUNT % / PKR -->
    <td class="large-col">
      <div class="discount-wrapper">
        <input type="text"
               class="form-control discount-value text-end"
               placeholder="" name="discount_percentage[]" >

        <button type="button"
                class="btn btn-outline-secondary discount-toggle"
                data-type="percent">%</button>
      </div>
    </td>



        <!-- DISCOUNT AMOUNT -->
        <td class="medium-col">
          <input type="text" class="form-control discount-amount text-end" name="discount_amount[]">
        </td>

        <!-- NET AMOUNT -->
        <td class="medium-col">
          <input type="text" class="form-control sales-amount text-end input-readonly" name="sales_amount[]" value="0" readonly>
        </td>

        <!-- ACTION -->
        <td class="action-col">
          <button type="button" class="btn btn-sm btn-outline-danger del-row">&times;</button>
        </td>
      </tr>
      `);

                        // initialize select2 on the newly appended product-select
                        initProductSelect2('#salesTableBody tr:last-child .product-select', '/search-products-sale', '/search_products');

        }


        // discunt % field
        $(document).on('click', '.discount-toggle', function () {

            const $btn = $(this);
            const currentType = $btn.data('type');

            if (currentType === 'percent') {
                $btn.data('type', 'pkr').text('PKR');
            } else {
                $btn.data('type', 'percent').text('%');
            }

            // re-calc row
            const $row = $btn.closest('tr');
            computeRow($row);
            updateGrandTotals();
        });








        function canPost() {
            let ok = false;
            $('#salesTableBody tr').each(function () {
                const pid = $(this).find('.product-select').val();
                const qty = parseFloat($(this).find('.sales-qty').val() || '0');
                if (pid && qty > 0) {
                    ok = true;
                    return false;
                }
            });
            return ok;
        }

        function refreshPostedState() {
            const state = canPost();
            $('#btnPosted, #btnHeaderPosted').prop('disabled', !state);
        }

        /* ---------- SAVE/POST ---------- */
        function serializeForm() {
            return $('#saleForm').serialize();
        }

        function ensureSaved() {
            console.log('ensureSaved called');
            return new Promise(function (resolve, reject) {

                const existing = $('#booking_id').val();
                if (existing) return resolve(existing);

                // Recompute every row so discount % and discount amount fields are populated
                $('#salesTableBody tr').each(function () {
                    try {
                        computeRow($(this));
                    } catch (e) {
                        // continue if computeRow fails for a row
                        console.warn('computeRow error', e);
                    }
                });
                updateGrandTotals();

                // � Ensure discount fields have values (not empty strings)
                $('#salesTableBody tr').each(function () {
                    const $discValue = $(this).find('.discount-value');
                    const $discAmount = $(this).find('.discount-amount');
                    
                    // Set default 0.00 if empty
                    if (!$discValue.val()) {
                        $discValue.val('0.00');
                    }
                    if (!$discAmount.val()) {
                        $discAmount.val('0.00');
                    }
                });

                // �🔴 TESTING: form ka data console me print
                const formData = serializeForm();
                console.log('🚀 DATA GOING TO sale.ajax.save:', formData);

                $('#btnSave, #btnHeaderPosted, #btnPosted').prop('disabled', true);

                $.post('{{ route("sale.ajax.save") }}', formData)

                    .done(function (res) {
                        console.log('✅ RESPONSE FROM SERVER:', res);

                        $('#btnSave, #btnHeaderPosted, #btnPosted').prop('disabled', false);

                        if (res?.ok) {
                            $('#booking_id').val(res.booking_id);
                            showAlert('success', 'Saved (Booking #' + res.booking_id + ')');
                            resolve(res.booking_id);
                        } else {
                            showAlert('danger', res.msg || 'Save failed');
                            reject(res);
                        }
                    })

                    .fail(function (xhr) {
                        console.error('❌ AJAX ERROR RESPONSE:', xhr.responseText);

                        $('#btnSave, #btnHeaderPosted, #btnPosted').prop('disabled', false);

                        // Attempt to extract a useful message from the server response
                        let msg = 'Save error';
                        try {
                            // Prefer parsed JSON if available
                            const json = xhr.responseJSON || JSON.parse(xhr.responseText || '{}');
                            if (json && (json.message || json.msg)) {
                                msg = json.message || json.msg;
                            } else if (typeof xhr.responseText === 'string' && xhr.responseText.trim()) {
                                // fallback: take first non-empty line
                                msg = xhr.responseText.split('\n').find(l => l.trim()) || msg;
                            }
                        } catch (err) {
                            if (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.msg)) {
                                msg = xhr.responseJSON.message || xhr.responseJSON.msg;
                            } else if (typeof xhr.responseText === 'string' && xhr.responseText.trim()) {
                                msg = xhr.responseText.split('\n').find(l => l.trim()) || msg;
                            }
                        }

                        showAlert('danger', msg);
                        reject(xhr);
                    });
            });
        }


        function postNow() {

            let bookingId = $('#booking_id').val();

            // 🔹 Ensure all discount fields have values before posting
            $('#salesTableBody tr').each(function () {
                const $discValue = $(this).find('.discount-value');
                const $discAmount = $(this).find('.discount-amount');
                if (!$discValue.val()) $discValue.val('0.00');
                if (!$discAmount.val()) $discAmount.val('0.00');
            });

            let data = $('#saleForm').serializeArray();

            // Validate receipts before posting — if invalid, show message and abort
            const receiptValidation = validateReceipts();
            // If there are any receipt amounts > 0 but receipts are invalid, prevent post
            let hasReceiptAmount = false;
            $('.rv-amount').each(function() { if (toNum($(this).val()) > 0) hasReceiptAmount = true; });
            if (hasReceiptAmount && !receiptValidation.ok) {
                showAlert('danger', receiptValidation.firstMessage || 'Please fix receipt rows before posting');
                if (receiptValidation.firstEl) receiptValidation.firstEl.focus();
                return;
            }

            // warehouse_id[product_id] build karo
            $('#salesTableBody tr').each(function () {
                let productId = $(this).find('.product-select').val();
                let warehouseId = $(this).find('.warehouse-id').val();

                if (productId && warehouseId) {
                    data.push({
                        name: `warehouse_id[${productId}]`,
                        value: warehouseId
                    });
                }
            });

            // booking id ensure
            data.push({
                name: 'booking_id',
                value: bookingId
            });

            // Include receipt rows (if any) so ajaxPost can create/process them
            $('.rv-account').each(function (i) {
                const acc = $(this).val();
                const amt = $('.rv-amount').eq(i).val() || '';
                data.push({ name: 'receipt_account_id[]', value: acc });
                data.push({ name: 'receipt_amount[]', value: amt });
            });

            // 🔹 GET request ke liye data query string me convert karo
            let queryString = $.param(data);

            console.log('GET Request URL:', '{{ route("sale.ajax.post") }}?' + queryString);

            // 🔹 AJAX GET request
            $.get('{{ route("sale.ajax.post") }}', queryString)

                .done(function (res) {
                    console.log('Response:', res);

                    if (res && res.ok) {
                        showAlert('success', 'Posted successfully');
                        $('#btnPosted, #btnHeaderPosted, #btnSave').prop('disabled', true);

                        if (res.invoice_url) {
                            window.open(res.invoice_url, '_blank');
                        }
                    } else {
                        $('#btnPosted, #btnHeaderPosted').prop('disabled', false);
                        showAlert('danger', res.msg || 'Post failed');
                    }
                })

                .fail(function (xhr) {
                    console.error('Server Error:', xhr.responseText);
                    $('#btnPosted, #btnHeaderPosted').prop('disabled', false);

                    // Try to extract a useful message from server JSON or responseText
                    let msg = 'Server error while posting';
                    try {
                        const json = xhr.responseJSON || JSON.parse(xhr.responseText || '{}');
                        if (json && (json.message || json.msg)) {
                            msg = json.message || json.msg;
                        } else if (typeof xhr.responseText === 'string' && xhr.responseText.trim()) {
                            msg = xhr.responseText.split('\n').find(l => l.trim()) || msg;
                        }
                    } catch (err) {
                        if (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.msg)) {
                            msg = xhr.responseJSON.message || xhr.responseJSON.msg;
                        } else if (typeof xhr.responseText === 'string' && xhr.responseText.trim()) {
                            msg = xhr.responseText.split('\n').find(l => l.trim()) || msg;
                        }
                    }

                    showAlert('danger', msg);
                });
        }


        /* ---------- Events top buttons ---------- */
        $('#btnAdd').on('click', addNewRow);
        $('#btnEdit').on('click', () => alert('Edit mode activated'));
        $('#btnRevert').on('click', () => location.reload());
        $('#btnDelete').on('click', function () {
            if (!confirm('Reset all fields?')) return;
            $('#saleForm')[0].reset();
            $('#booking_id').val('');
            $('#salesTableBody').html('');
            addNewRow();
            $('#totalAmount').text('0.00');
            updateGrandTotals();
            refreshPostedState();
            showAlert('success', 'Form cleared');
        });
        $('#btnSave').on('click', function () {
            // alert();
            ensureSaved();
        });
        $('#btnPrint').on('click', function () {
            ensureSaved().then(id => window.open('{{ url("booking/print") }}/' + id, '_blank'));
        });
        $('#btnPrint2').on('click', function () {
            ensureSaved().then(id => window.open('{{ url("booking/print2") }}/' + id, '_blank'));
        });
        $('#btnDCPrint').off('click').on('click', function () {

            ensureSaved().then(function (id) {

                // alert(id); // ✅ ab zaroor chalega

                window.open('{{ url("booking/dc") }}/' + id, '_blank');

            }).catch(function () {
                alert('Save failed');
            });

        });

        $('#btnExit').on('click', function () {
            ensureSaved().finally(() => {
                window.location.href = "{{ route('sale.index') }}";
            });
        });

        //     $('#btnPosted, #btnHeaderPosted').on('click', function () {
        //     ensureSaved().faraz().then(postNow);
        // });



        /* ---------- Row compute ---------- */
  function toNum(v) {
    return parseFloat(v || 0) || 0;
}

function computeRow($row, manualAmount = false, formatDiscount = true) {

    const rp = toNum($row.find('.retail-price').val());
    // console.log("retail price",rp);
    const qty = toNum($row.find('.sales-qty').val());
// console.log("qty:",qty);

    // 🔹 Safe discount value (never negative)
    const $discInput = $row.find('.discount-value');
    const rawDisc = Math.max(0, $discInput.val());
    let discValue = rawDisc;

    const discType = $row.find('.discount-toggle').data('type'); // percent | pkr
     console.log("percent discount:",discType) ;
    let dam = toNum($row.find('.discount-amount').val());

    // 🔹 GROSS
    const gross = rp * qty;

    /* ===== AUTO DISCOUNT ===== */
    if (discValue > 0) {

        if (discType === 'percent') {
           
            // If user entered >100%, mark invalid and show helper text
            const $help = $row.find('.discount-help');
                if (rawDisc > 101) {
                markInvalid($discInput);
                const $wrapper = $row.find('.discount-wrapper');
                if ($wrapper.find('.discount-help').length === 0) {
                    $wrapper.append('<div class="discount-help">Discount never be <= 100%</div>');
                } else {
                    $wrapper.find('.discount-help').text('Discount must be <= 100%');
                }
                // use 100 for calculation but keep visual warning
                discValue = 100;
            } else {
                
                clearInvalid($discInput);
                $row.find('.discount-help').remove();
                discValue = Math.min(discValue, 100);
            }

            dam = (gross * discValue) / 100;   // % from retail

        } else {
            // PKR discount should not exceed gross per row
            const totalPKR = discValue * qty;
            if (totalPKR > gross) {
                markInvalid($discInput);
                const $wrapper = $row.find('.discount-wrapper');
                if ($wrapper.find('.discount-help').length === 0) {
                    $wrapper.append('<div class="discount-help">Discount cannot exceed row gross</div>');
                } else {
                    $wrapper.find('.discount-help').text('Discount cannot exceed row gross');
                }
                // cap discount amount to gross but keep user's per-unit input unchanged
                dam = gross;
            } else {
                clearInvalid($discInput);
                $row.find('.discount-help').remove();
                dam = totalPKR;
            }
        }

        if (formatDiscount) {
            $discInput.val(discValue.toFixed(2));
        }
        $row.find('.discount-amount').val(dam.toFixed(2));

    } else {
        // 🔹 Discount empty or 0
        dam = 0;
        $row.find('.discount-amount').val('0.00');
        // clear any helper/invalid state when input is empty
        clearInvalid($discInput);
        $row.find('.discount-help').remove();
        // Don't clear discount-value, keep it empty or with user's last input
        if (!$discInput.val()) {
            $discInput.val('');
        }
    }

    /* ===== NET ===== */
    const net = Math.max(0, gross - dam);
    $row.find('.sales-amount').val(net.toFixed(2));
}







        $(document).on('input', '.sales-qty, .discount-value', function (e) {
            const $row = $(this).closest('tr');
            // If typing in discount input, do not reformat it while typing
            if ($(this).hasClass('discount-value')) {
                computeRow($row, false, false); // manualAmount=false, formatDiscount=false
            } else {
                computeRow($row);
            }
            updateGrandTotals();
            refreshPostedState();
        });

        // On blur of discount input, format and validate
        $(document).on('blur', '.discount-value', function () {
            const $row = $(this).closest('tr');
            computeRow($row, false, true); // now format the discount input
            updateGrandTotals();
            refreshPostedState();
        });

        $(document).on('input', '.discount-amount', function () {
            const $row = $(this).closest('tr');
            computeRow($row, true); // manual amount respected
            updateGrandTotals();
            refreshPostedState();
        });

        /* ---------- Delete row ---------- */
        $(document).on('click', '.del-row', function () {
            const $tr = $(this).closest('tr');
            const $tbody = $('#salesTableBody');
            if ($tbody.find('tr').length > 1) {
                $tr.remove();
                updateGrandTotals();
                refreshPostedState();
            }
        });

        /* ---------- Totals ---------- */
        function updateGrandTotals() {

            let tQty = 0;
            let tGross = 0;
            let tLineDisc = 0;
            let tNet = 0;

            $('#salesTableBody tr').each(function () {

                const $r = $(this);

                const rp = toNum($r.find('.retail-price').val());
                const qty = toNum($r.find('.sales-qty').val());
                const dam = toNum($r.find('.discount-amount').val());

                const gross = rp * qty;
                const net = Math.max(0, gross - dam);

                tQty += qty;
                tGross += gross;
                tLineDisc += dam;
                tNet += net;   // ✅ NET TOTAL
            });

            // ===== ORDER LEVEL =====
            const orderRaw = toNum($('#discountPercent').val());
            const orderType = $('#orderDiscountToggle').data('type') || 'percent'; // percent | pkr
            let orderDisc = 0;
            if (orderType === 'percent') {
                orderDisc = (tNet * orderRaw) / 100;
            } else {
                // treat input as absolute PKR amount
                orderDisc = orderRaw;
            }

            const prev = toNum($('#previousBalance').val());
            const receipts = toNum($('#receiptsTotal').text());

            const payable = Math.max(0, tNet - orderDisc + prev - receipts);

            // ===== UI UPDATE =====
            $('#tQty').text(tQty.toFixed(0));
            $('#tGross').text(tGross.toFixed(2));
            $('#tLineDisc').text(tLineDisc.toFixed(2));
            $('#tSub').text(tNet.toFixed(2));
            $('#tOrderDisc').text(orderDisc.toFixed(2));
            $('#tPrev').text(prev.toFixed(2));
            $('#tPayable').text(payable.toFixed(2));

            // 🔥 TABLE FOOTER TOTAL
            $('#totalAmount').text(tNet.toFixed(2));

            // ===== BACKEND MIRRORS =====
            $('#subTotal1').val(tGross.toFixed(2));
            $('#subTotal2').val(tNet.toFixed(2));
            $('#discountAmount').val(orderDisc.toFixed(2));
            $('#totalBalance').val(payable.toFixed(2));
        }

        // recalc when discount/toggle/previous balance change
        $(document).on('input', '#previousBalance, #discountPercent', updateGrandTotals);
        $(document).on('click', '#orderDiscountToggle', function () {
            const $btn = $(this);
            const current = $btn.data('type');
            if (current === 'percent') {
                $btn.data('type', 'pkr').text('PKR');
            } else {
                $btn.data('type', 'percent').text('%');
            }
            updateGrandTotals();
        });

        /* ---------- Row auto-add ---------- */
        $('#salesTableBody').on('input', '.sales-qty', function () {
            const $row = $(this).closest('tr');
            computeRow($row);
            updateGrandTotals();
            refreshPostedState();
        });

        /* ---------- Add new row when user presses Enter in Disc % (only on last row) ---------- */
        $('#salesTableBody').on('keydown', '.discount-value', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault(); // prevent accidental form submit
                const $current = $(this).closest('tr');

                // compute current row first (in case user typed value and pressed Enter)
                computeRow($current);
                updateGrandTotals();
                refreshPostedState();

                // only add new row when this is the last row AND discount has some value OR qty > 0 or product selected
                const isLast = $current.is(':last-child');
                const discVal = parseFloat($(this).val() || '0') || 0;
                const qtyVal = parseFloat($current.find('.sales-qty').val() || '0') || 0;
                const prodSelected = !!$current.find('.product').val();

                // require at least one 'meaningful' value so blank Enter doesn't create rows
                if (isLast && (discVal !== 0 || qtyVal > 0 || prodSelected)) {
                    addNewRow();
                    // focus on new row product for quick entry
                    const $newRow = $('#salesTableBody tr:last-child');
                    // setTimeout(() => $newRow.find('.warehouse').focus(), 0);
                }
            }
        });



        function recomputeReceipts() {
            let sum = 0;
            // Calculate the total receipt amount
            $('.rv-amount').each(function () {
                sum += toNum($(this).val()); // Sum up all the receipt amounts
            });
            $('#receiptsTotal').text(sum.toFixed(2)); // Display total in the respective element
            updateGrandTotals(); // Update other totals if needed
            updatePostButtonState();
            // Live-validate receipt rows and highlight invalid fields
            validateReceipts();
        }

        $('#btnAddRV').on('click', function () {

            const $row = $(`
            <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                <select class="form-select rv-account" name="receipt_account_id[]" style="max-width:320px"></select>
                <input type="text" class="form-control text-end rv-amount" name="receipt_amount[]" placeholder="0.00" style="max-width:160px">
                <button type="button" class="btn btn-outline-danger btn-sm btnRemRV">&times;</button>
            </div>
        `);

            $('#rvWrapper').append($row);

            loadAccountsInto($row.find('.rv-account'));
            // keep post button state updated when a new row is added
            updatePostButtonState();
        });


      
        $(document).on('click', '.btnRemRV', function () {
            $(this).closest('.rv-row').remove();
            recomputeReceipts();

            $('.rv-account').each(function () {
                loadAccountsInto($(this));
            });
            updatePostButtonState();
        });

        // Recompute total receipt amounts when input changes
        $(document).on('input', '.rv-amount', recomputeReceipts);

        // Update post button and validate when account selection changes
        // Also refresh other account dropdowns so the selected account is
        // removed from the other rows (prevents selecting same account twice).
        $(document).on('change', '.rv-account', function() {
            updatePostButtonState();
            validateReceipts();
            $('.rv-account').each(function () { loadAccountsInto($(this)); });
        });

     

        function markInvalid($el) {
            // add visuals; $el can be input/select/td
            $el.addClass('invalid-input invalid-select');
            // also add class to closest td for table cells
            $el.closest('td').addClass('invalid-cell');
        }

        function clearInvalid($el) {
            $el.removeClass('invalid-input invalid-select');
            $el.closest('td').removeClass('invalid-cell');
        }

        function clearAllInvalids() {
            $('.invalid-input, .invalid-select').removeClass('invalid-input invalid-select');
            $('.invalid-cell').removeClass('invalid-cell');
        }

        $(document).on('input change', 'select, input, textarea', function () {
            clearInvalid($(this));
        });

        function validateRows() {
            let ok = true;
            let firstMessage = null;
            let firstEl = null;

            $('#salesTableBody tr').each(function (rowIndex) {
                const $row = $(this);
                // const $wh = $row.find('.warehouse');
                const $prod = $row.find('.product-select');
                const $qty = $row.find('.sales-qty');
            
                // Product / Item
                if (!$prod.val()) {
                    ok = false;
                    if (!firstMessage) {
                        firstMessage = 'Please select Item for row ' + (rowIndex + 1);
                        firstEl = $prod;
                    }
                    markInvalid($prod);
                }

                // Qty > 0
                const qtyVal = parseFloat($qty.val() || '0') || 0;
                if (qtyVal <= 0) {
                    ok = false;
                    if (!firstMessage) {
                        firstMessage = 'Please enter Item qty (> 0) for row ' + (rowIndex + 1);
                        firstEl = $qty;
                    }
                    markInvalid($qty);
                }
            });

            return {
                ok,
                firstMessage,
                firstEl
            };
        }

      
        function validateReceipts() {
            let ok = true,
                firstMessage = null,
                firstEl = null;
            $('#rvWrapper .rv-row').each(function (i) {
                const $row = $(this);
                const $acc = $row.find('.rv-account');
                const $amt = $row.find('.rv-amount');
                const amtVal = parseFloat($amt.val() || '0') || 0;

                if (amtVal > 0 && (!$acc.val() || $acc.val() === "")) {
                    ok = false;
                    if (!firstMessage) {
                        firstMessage = 'Please select Account for receipt row ' + (i + 1);
                        firstEl = $acc;
                    }
                    markInvalid($acc);
                }
            });
            return {
                ok,
                firstMessage,
                firstEl
            };
        }

        // Enable/disable Post button depending on receipts validity and row validation
        function updatePostButtonState() {
            const postBtn = $('#btnPosted');
            const headerPostBtn = $('#btnHeaderPosted');

            const rowsValid = validateRows().ok;
            const receiptsValid = validateReceipts().ok;

            // If there are any receipt amount inputs with value > 0, require receiptsValid
            let hasReceiptAmount = false;
            $('.rv-amount').each(function() { if (toNum($(this).val()) > 0) hasReceiptAmount = true; });

            const enable = rowsValid && (!hasReceiptAmount || receiptsValid);
            postBtn.prop('disabled', !enable);
            headerPostBtn.prop('disabled', !enable);
        }

        /**
        * validateHeader() -> Type & Party mandatory
        */
        function validateHeader() {
            let ok = true,
                firstMessage = null,
                firstEl = null;
            // Type (partyType) - we expect a radio selected
            const partyType = $('input[name="partyType"]:checked').val();
            if (!partyType) {
                ok = false;
                firstMessage = 'Please select Type';
                firstEl = $('input[name="partyType"]').first();
                // mark buttons visually
                $('#partyTypeGroup').addClass('invalid-cell');
            } else {
                $('#partyTypeGroup').removeClass('invalid-cell');
            }

            // Party / Customer
            const cust = $('#customerSelect').val();
            if (!cust) {
                ok = false;
                if (!firstMessage) {
                    firstMessage = 'Please select Party (Customer / Vendor)';
                    firstEl = $('#customerSelect');
                }
                markInvalid($('#customerSelect'));
            }

            return {
                ok,
                firstMessage,
                firstEl
            };
        }

        /**
        * validateFormAll() -> run header, rows, receipts
        * returns { ok, message, el }
        */
        function validateFormAll() {
            clearAllInvalids();

            // header
            const h = validateHeader();
            if (!h.ok) {
                return {
                    ok: false,
                    message: h.firstMessage,
                    el: h.firstEl
                };
            }

            // rows
            const r = validateRows();
            if (!r.ok) {
                return {
                    ok: false,
                    message: r.firstMessage,
                    el: r.firstEl
                };
            }

            // receipts
            const rec = validateReceipts();
            if (!rec.ok) {
                return {
                    ok: false,
                    message: rec.firstMessage,
                    el: rec.firstEl
                };
            }

            // if all ok
            return {
                ok: true
            };
        }

        /* ---------- Hook validation into Save / Post ---------- */

        // override Save button to validate first and check credit limit
        $('#btnSave').off('click').on('click', function () {
            cleanupEmptyRows(); // remove empty rows
            updateGrandTotals(); // recompute totals after cleanup
            refreshPostedState();

            // run the existing validation pipeline
            const v = validateFormAll();
            if (!v.ok) {
                showAlert('danger', v.message);
                if (v.el && v.el.length) {
                    v.el.focus();
                    if (v.el.hasClass('js-customer')) v.el.select2?.('open');
                }
                return;
            }

            // CHECK CREDIT LIMIT BEFORE SAVING
            const cust = $('#customerSelect').val();
            const payable = parseFloat($('#totalBalance').val() || $('#tPayable').text() || 0) || 0;

            if (cust) {
                $.get('/get-customer/' + cust)
                    .done(function (res) {
                        const credit = parseFloat(res.credit_limit || 0) || 0;
                        if (credit > 0 && payable > credit) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Credit limit exceeded',
                                html: `Customer credit limit is <b>${credit.toFixed(2)}</b>.<br>Payable amount is <b>${payable.toFixed(2)}</b>.`,
                            });
                            return;
                        }
                        // proceed to save
                        ensureSaved();
                    })
                    .fail(function () {
                        // If customer lookup fails, proceed with save but log
                        ensureSaved();
                    });
            } else {
                ensureSaved();
            }
        });


        // override Post buttons to validate first
        $('#btnHeaderPosted, #btnPosted').off('click').on('click', function () {
            cleanupEmptyRows();
            updateGrandTotals();
            refreshPostedState();
            // console.log('👉 Post button clicked');
            const v = validateFormAll();
            if (!v.ok) {
                showAlert('danger', v.message);
                if (v.el && v.el.length) {
                    v.el.focus();
                    if (v.el.hasClass('js-customer')) v.el.select2?.('open');
                }
                return;
            }

            if (!canPost()) {
                showAlert('danger', 'No valid item lines to post');
                return;
            }

            ensureSaved().then(function () {
                console.log('✅ ensureSaved resolved');
                //postNow();
            });
        });


        function isRowMeaningful($row) {
            const prod = $row.find('.product-select').val();
            const wh = $row.find('.warehouse-id').val();
            const qty = parseFloat($row.find('.sales-qty').val() || '0') || 0;
            const discPct = parseFloat($row.find('.discount-value').val() || '0') || 0;
            const discAmt = parseFloat($row.find('.discount-amount').val() || '0') || 0;

            // consider row meaningful if product selected OR qty > 0 OR discount entered OR warehouse selected
            return !!prod || !!wh || qty > 0 || discPct !== 0 || discAmt !== 0;
        }

        function cleanupEmptyRows() {
            $('#salesTableBody tr').each(function () {
                const $r = $(this);
                const prod = $r.find('.product-select').val();
                // const wh = $r.find('.warehouse').val();
                const qty = parseFloat($r.find('.sales-qty').val() || '0') || 0;

                // Remove row when qty is zero or product is empty
                if ((qty <= 0) || (!prod || prod === '')) {
                    // ensure we keep at least one row in UI
                    if ($('#salesTableBody tr').length > 1) {
                        $r.remove();
                    } else {
                        // if only one row left, clear its fields instead of removing (keeps UI stable)
                        $r.find('select').val('');
                        $r.find('input').val('');
                        $r.find('.stock').val('');
                        $r.find('.sales-amount').val('0');
                        // Set discount fields to 0
                        $r.find('.discount-value').val('0.00');
                        $r.find('.discount-amount').val('0.00');
                    }
                }
            });

            // Ensure all remaining rows have discount fields populated
            $('#salesTableBody tr').each(function () {
                const $discValue = $(this).find('.discount-value');
                const $discAmount = $(this).find('.discount-amount');
                if (!$discValue.val()) $discValue.val('0.00');
                if (!$discAmount.val()) $discAmount.val('0.00');
            });

            // ensure at least one blank row exists
            if ($('#salesTableBody tr').length === 0) addNewRow();
        }
    </script>

    <script>
        // Product dropdown infinite scroll with Select2
    function initProductSelect2(
    selector = '.product-select',
    url = '/search-products-sale',
    searchUrl = '/search_products'
) {
    $(selector).select2({
        ajax: {
            transport: function (params, success, failure) {
                // prefer params.data.term which Select2 populates
                let term = (params.data && (params.data.term || params.data.q)) || '';
                let page = (params.data && (params.data.page || 1)) || 1;
                let ajaxUrl = term && term.length > 0 ? searchUrl : url;
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
                return {
                    q: params.term || '',
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                let results = [];
                if (Array.isArray(data)) {
                    results = data.map(function (p) {
                        return { id: p.id, text: p.item_name, stock: p.stock, price: p.retail_price || p.price };
                    });
                    return { results: results, pagination: { more: false } };
                }

                results = (data.products || []).map(function (p) {
                    return { id: p.id, text: p.item_name, stock: p.stock, price: p.retail_price || p.price };
                });

                return {
                    results: results,
                    pagination: { more: !!data.has_more }
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        placeholder: 'Search product...',
        allowClear: true,
        width: 'resolve'
    });
}



$(document).ready(function () {
    initProductSelect2('.product-select', '/search-products-sale', '/search_products');
    // Log selected product id on selection
    $(document).on('select2:select', '.product-select', function (e) {
        if (e && e.params && e.params.data && e.params.data.id) {
            $.get('/get-product-details/' + e.params.data.id, function (data) {
                const $row = $(e.target).closest('tr');
                // console.log('Product details loaded:', data);
                if (data && data.product) {
                    // price field may be `price` or `retail_price` depending on model
                    const price = parseFloat(data.product.retail_price ?? data.product.price ?? 0).toFixed(2);
                    const stockQty = (data.product.stock && (data.product.stock.qty ?? data.product.stock)) || 0;
                    $row.find('.retail-price').val(price);
                    $row.find('.stock').val(stockQty);
                    // set the underlying select value (product-select) so validation/serialize picks it up
                    $row.find('.product-select').val(data.product.id).trigger('change');
                    // Recompute row and totals
                    computeRow($row);
                    updateGrandTotals();
                    refreshPostedState();
                }
            });
        }
    });
});
    </script>
@endsection
