@extends('admin_panel.layout.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* ================= RESPONSIVE SALES UI ================= */

        /* table container - no scroll on mobile */
        .table-responsive {
            overflow-x: visible;
            max-height: 360px;
            overflow-y: auto;
        }

        /* base table width - responsive */
        .sales-table {
            width: 100%;
        }

        /* 🔹 DISCOUNT COLUMN – THORI SI BARI */
        .sales-table td.large-col {
            min-width: 95px;
            width: 95px;
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

        /* Ensure table fits without scroll bars */
        .sales-table {
            width: 100%;
        }

        /* ---------- DESKTOP (>= 1200px) ---------- */
        @media (min-width: 1200px) {
            .sales-table {
                min-width: 1000px;
                width: 100%;
            }
        }

        /* ---------- TABLET (992px - 1199px) ---------- */
        @media (max-width: 1199px) and (min-width: 992px) {
            .main-container {
                max-width: 100%;
            }

            .sales-table {
                min-width: auto;
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
        }

        /* ---------- MOBILE (768px - 991px) ---------- */
        @media (max-width: 991px) {
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
            }

            /* reduce input font */
            .form-control,
            .form-select {
                font-size: .8rem;
            }

            /* CRITICAL: Make table responsive */
            .sales-table {
                min-width: auto;
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
        }

        /* ---------- SMALL PHONES (<= 576px) ---------- */
        @media (max-width: 576px) {
            .sales-table {
                font-size: 0.65rem;
                min-width: auto;
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
        }
    </style>
    <style>
        /* ====== REMOVE SCROLL BAR STYLING ====== */

        /* Disable ALL scrolling for table */
        .table-responsive {
            overflow: visible !important;
            max-height: none;
        }

        .items-panel {
            overflow: hidden;
            width: 100%;
        }

        .main-container {
            font-size: .85rem;
            max-width: 1400px;
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
        }

        .table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8f9fa;
            text-align: center;
        }

        .table-responsive {
            max-height: 360px;
            overflow: auto;
            border: 1px solid #eee;
            border-radius: .5rem;
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
                            <label class="form-label fw-bold mb-1">Customer id & name</label>
                            <input type="text" class="form-control" id="customer_id" name="customer_id" value="">
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
                                        <td></td>
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
                                <div class="col-7">Aditional Discount %</div>
                                <div class="col-5 text-end">
                                    <input type="text" class="form-control text-end" name="discountPercent"
                                        id="discountPercent" value="0" style="max-width:120px; margin-left:auto">
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


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        function faraz() {
            const productIds = [];
            $('#salesTableBody tr').each(function () {
                const pid = $(this).find('.product').val();
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
                    // Build warehouse list
                    let html = '<table class="table"><thead><tr><th>Name</th><th>Action</th></tr></thead><tbody>';
                    if (Array.isArray(res) && res.length > 0) {
                        res.forEach(function (wh) {
                            html += `<tr>
                        <td>${wh.name}</td>
                        <td><button class="btn btn-primary btn-sm select-warehouse" data-id="${wh.id}">Select</button></td>
                      </tr>`;
                        });
                    } else {
                        html += '<tr><td colspan="2" class="text-center">No warehouses found</td></tr>';
                    }
                    html += '</tbody></table>';
                    $('#warehouseModalBody').html(html);
                    $('#warehouseModal').modal('show');
                },
                error: function (err) {
                    console.error(err);
                    alert('Something went wrong fetching warehouses!');
                }
            });
        }

        //Handle warehouse select button/
        $(document).on('click', '.select-warehouse', function () {

            const warehouseId = $(this).data('id');
            console.log('Selected Warehouse ID:', warehouseId);

            $('#warehouseModal').modal('hide');

            $('#salesTableBody tr').each(function () {

                const productId = $(this).find('.product').val();

                if (productId) {

                    // ✅ pehle variable define karo
                    const $warehouseInput = $(this).find('.warehouse-id');

                    // ✅ phir value set karo
                    $warehouseInput
                        .attr('name', `warehouse_id[${productId}]`)
                        .val(warehouseId);

                    // ✅ ab console me safely dekh sakte ho
                    console.log(
                        'Product ID:', productId,
                        'Warehouse Value:', $warehouseInput.val()
                    );
                }

            });

            ensureSaved().then(postNow);
        });


    </script>








    {{--faarz memon --}}
    <script>
        let CURRENT_PRODUCT_ROW = null;



        // $(document).on('keydown', function (e) {

        //     if (e.key === 'F2') {
        //         e.preventDefault();

        //         // jis row me cursor hai wo detect karo
        //         CURRENT_PRODUCT_ROW = $(':focus').closest('tr');

        //         if (!CURRENT_PRODUCT_ROW.length) {
        //             alert('Please focus on a product row first');
        //             return;
        //         }

        //         $('#productSearchInput').val('');
        //         $('#productSearchResults').html(
        //             '<tr><td colspan="2" class="text-center">Type to search...</td></tr>'
        //         );

        //         $('#productSearchModal').modal('show');

        //         setTimeout(() => $('#productSearchInput').focus(), 300);
        //     }

        // });







        // $('#productSearchInput').on('keyup', function () {

        //     const keyword = $(this).val().trim();

        //     if (keyword.length < 2) {
        //         $('#productSearchResults').html(
        //             '<tr><td colspan="2" class="text-center">Type at least 2 characters</td></tr>'
        //         );
        //         return;
        //     }

        //     $.get('{{ route("search_products") }}', { q: keyword }, function (products) {

        //         let html = '';

        //         if (products.length === 0) {
        //             html = '<tr><td colspan="2" class="text-center">No product found</td></tr>';
        //         } else {
        //             products.forEach(p => {
        //                 html += `
        //                 <tr>
        //                     <td>${p.item_name}</td>
        //                     <td class="text-center">
        //                         <button class="btn btn-sm btn-primary select-product"
        //                             data-id="${p.id}"
        //                             data-name="${p.item_name}"
        //                             data-stock="${p.stock}"
        //                             data-price="${p.retail_price}">
        //                             Select
        //                         </button>
        //                     </td>
        //                 </tr>
        //             `;
        //             });
        //         }

        //         $('#productSearchResults').html(html);
        //     });

        // });

        // faraz memon



        $(document).on('click', '.select-product', function () {

            if (!CURRENT_PRODUCT_ROW) return;

            const id = $(this).data('id');
            const name = $(this).data('name');
            const stock = $(this).data('stock');
            const price = $(this).data('price');

            // product dropdown me add + select
            const $productSelect = CURRENT_PRODUCT_ROW.find('.product');

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
                            html += `<option value="${row.id}">
                            ${row.customer_id} -- ${row.customer_name}
                          </option>`;
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
                if (!id) return;

                $.get(
                    '{{ route("salecustomers.show", "__ID__") }}'.replace('__ID__', id),
                    function (d) {
                        $('#address').val(d.address || '');
                        $('#tel').val(d.mobile || '');
                        $('#remarks').val(d.status || '');
                        $('#previousBalance').val((+d.opening_balance || 0).toFixed(2));
                    }
                );
            });

            // 🔹 Clear button
            $('#clearCustomerData').on('click', function () {
                $('#customerSelect').val('');
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
            el.removeClass('d-none alert-success alert-danger').addClass('alert-' + type).text(msg);
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


        }
// '  <tr data-last-id="1">'

$(document).ready(function () {

    let last_id = 1;

    $('.product-select').select2();

    // Infinite scroll and product load
    $('.product-select').on('select2:open', function () {
        let selectEl = this;   // 🔥 VERY IMPORTANT
        $.get('/search-products-sale', {
            last_id: last_id
        }, function (res) {
            console.log('Products loaded:', res);
            res.products.forEach(p => {

 $(document).on('select2:select', '.product-select', function (e) {

        const $row = $(this).closest('tr');
        // Use the actual <option> element for data attributes
            
            const $option = $(e.params.data.element);
            console.log("option",$option);
            console.log("row",$row);
            const stock = $option.data('stock') || '';
            console.log("stock",stock);
        const price = $option.data('price') || '';
        $row.find('.stock').val(stock);
        $row.find('.retail-price').val(price);
    });




                // If option not already present, add it with data attributes
                if (!$(selectEl).find('option[value="' + p.id + '"]').length) {

                    let option = new Option(p.item_name, p.id, false, false);
                    // Attach data attributes for later use
                    $(option).attr({
                        'data-id': p.id,
                        'data-name': p.item_name,
                        'data-stock': p.stock,
                        'data-price': p.retail_price
                    });
                    selectEl.appendChild(option);
                }
            });
            // 🔥 Select2 refresh
            $(selectEl).trigger('change');
            // next request ke liye
            last_id = res.last_id;
        });
    });
});
    // When a product is selected, fill related fields in the same row
    // $(document).on('select2:select', '.product-select', function (e) {

    //     const $row = $(this).closest('tr');
    //     // Use the actual <option> element for data attributes
            
    //         const $option = $(e.params.data.element);
    //         console.log("option",$option);
    //         console.log("row",$row);
    //         const stock = $option.data('stock') || '';
    //         console.log("stock",stock);
    //     const price = $option.data('price') || '';
    //     $row.find('.stock').val(stock);
    //     $row.find('.retail-price').val(price);
    // });





       

        // let test = document.querySelectorAll('.product_field');
        // console.log(test);

// let last_id = 0;
// function stackproduct(selectEl) {
//     // alert(selectEl);
//     // agar already loaded hai to dobara call na ho
//     if (selectEl.dataset.loaded === "1") {
//         return;
//     }
//     fetchProducts(selectEl, 0);
// }

// function fetchProducts(selectEl, startId) {
//     fetch(`/search-products-sale?last_id=${startId}`)
//         .then(response => response.json())
//         .then(res => {
//             if (startId === 0) {
//                 // Remove all except first option
//                 selectEl.options.length = 1;
//             }
//             res.products.forEach(p => {
//                 let option = document.createElement('option');
//                 option.value = p.id;
//                 option.text  = p.item_name;
//                 option.classList.add('product_field')
//                 selectEl.appendChild(option);
//             });
//             // Save last_id for next fetch
//             selectEl.dataset.lastId = res.last_id;
            
//             // Mark as loaded if no more
//             if (!res.has_more) {
//                 selectEl.dataset.loaded = "1";
//             } else {
//                 selectEl.dataset.loaded = "0";
//             }
//         })
//         .catch(err => console.error(err));
// }

// // Infinite scroll for select
// document.addEventListener('DOMContentLoaded', function() {
//     document.addEventListener('scroll', function(e) {
//         const el = e.target;
//         if (el.classList && el.classList.contains('product')) {
//             // Check if near bottom
//             if (el.scrollTop + el.clientHeight >= el.scrollHeight - 5) {
//                 if (el.dataset.loaded !== "1") {
//                     const nextId = el.dataset.lastId || 0;
//                     fetchProducts(el, nextId);
//                 }
//             }
//         }
//     }, true);
// });

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
                const pid = $(this).find('.product').val();
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
            return new Promise(function (resolve, reject) {

                const existing = $('#booking_id').val();
                if (existing) return resolve(existing);

                // 🔴 TESTING: form ka data console me print
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
                        showAlert('danger', 'Save error');
                        reject(xhr);
                    });
            });
        }


        function postNow() {

            let bookingId = $('#booking_id').val();

            let data = $('#saleForm').serializeArray();

            // warehouse_id[product_id] build karo
            $('#salesTableBody tr').each(function () {
                let productId = $(this).find('.product').val();
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
                    showAlert('danger', 'Server error while posting');
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
        function computeRow($row) {

            const rp = toNum($row.find('.retail-price').val());
            const qty = toNum($row.find('.sales-qty').val());

            const discValue = toNum($row.find('.discount-value').val());
            const discType = $row.find('.discount-toggle').data('type'); // percent | pkr

            let dam = toNum($row.find('.discount-amount').val());

            // 🔹 GROSS
            const gross = rp * qty;

            /* ===== AUTO DISCOUNT ===== */
            if (discValue > 0) {

                if (discType === 'percent') {
                    dam = (gross * discValue) / 100;   // % from retail
                } else {
                    dam = discValue * qty;            // PKR × qty
                }

                $row.find('.discount-amount').val(dam.toFixed(2));

            } else {
                // ✅ NEW FEATURE → jab disc% / PKR empty ya 0 ho
                dam = 0;
                $row.find('.discount-amount').val('0.00');
            }

            /* ===== NET ===== */
            const net = Math.max(0, gross - dam);
            $row.find('.sales-amount').val(net.toFixed(2));
        }







        $(document).on('input', '.sales-qty, .discount-value', function () {
            const $row = $(this).closest('tr');
            computeRow($row);
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
            const orderPct = toNum($('#discountPercent').val());
            const orderDisc = (tNet * orderPct) / 100;

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

        $(document).on('input', '#previousBalance, #discountPercent', updateGrandTotals);

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


        /* ---------- Receipts (accounts) ---------- */
        // function loadAccountsInto($select) {
        //   $select.prop('disabled', true).empty().append('<option value="">Loading...</option>');

        //   // Get the list of accounts
        // //   $.get(, {
        // //     scope: 'cashbank'
        // //   }, function(rows) {
        // //     $select.empty().append('<option value="">Select account</option>');
        // //     (rows || []).forEach(function(a) {
        // //       $select.append('<option value="' + a.id + '">' + a.title + '</option>'); // Add account options
        // //     });
        // //     $select.prop('disabled', false); // Enable the select input after loading
        // //   }).fail(function() {
        // //     // If there's an error, display an error message
        // //     $select.empty().append('<option value="">Error loading</option>').prop('disabled', false);
        // //   });
        // }

        function recomputeReceipts() {
            let sum = 0;
            // Calculate the total receipt amount
            $('.rv-amount').each(function () {
                sum += toNum($(this).val()); // Sum up all the receipt amounts
            });
            $('#receiptsTotal').text(sum.toFixed(2)); // Display total in the respective element
            updateGrandTotals(); // Update other totals if needed
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
        });


        // $(document).on('click', '.btnRemRV', function() {
        //  $(this).closest('.rv-row').remove();
        //  recomputeReceipts(); // Recompute total receipts after removal
        //});
        $(document).on('click', '.btnRemRV', function () {
            $(this).closest('.rv-row').remove();
            recomputeReceipts();

            $('.rv-account').each(function () {
                loadAccountsInto($(this));
            });
        });

        // Recompute total receipt amounts when input changes
        $(document).on('input', '.rv-amount', recomputeReceipts);

        /* ---------- init ---------- */
        // function init() {
        //   addNewRow();
        //   loadCustomersByType('customer');
        //   // loadAccountsInto($('.rv-account').first());
        //   updateGrandTotals();
        //   refreshPostedState();
        // }

        // init();

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
                const $prod = $row.find('.product');
                const $qty = $row.find('.sales-qty');

                // Warehouse
                // if (!$wh.val()) {
                //   ok = false;
                //   if (!firstMessage) {
                //     firstMessage = 'Please select Warehouse for row ' + (rowIndex + 1);
                //     firstEl = $wh;
                //   }
                //   markInvalid($wh);
                // }

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

        /**
        * validateReceipts() -> if any receipt amount > 0 then account must be selected
        * returns { ok, firstMessage, firstEl }
        */
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

        // override Save button to validate first
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

            // proceed to save
            ensureSaved();
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
            const prod = $row.find('.product').val();
            // const wh = $row.find('.warehouse').val();
            const qty = parseFloat($row.find('.sales-qty').val() || '0') || 0;
            const discPct = parseFloat($row.find('.discount-value.discount-percent').val() || '0') || 0;
            const discAmt = parseFloat($row.find('.discount-amount').val() || '0') || 0;

            // consider row meaningful if product selected OR qty > 0 OR discount entered OR warehouse selected
            return !!prod || !!wh || qty > 0 || discPct !== 0 || discAmt !== 0;
        }

        function cleanupEmptyRows() {
            $('#salesTableBody tr').each(function () {
                const $r = $(this);
                const prod = $r.find('.product').val();
                // const wh = $r.find('.warehouse').val();
                const qty = parseFloat($r.find('.sales-qty').val() || '0') || 0;

                // Remove row when qty is zero or (product empty AND warehouse empty)
                // We want to remove:
                //  - rows where qty <= 0 (user didn't enter qty) because they are meaningless,
                //  - or rows that are fully empty.
                if ((qty <= 0) || ((!prod || prod === '') && (!wh || wh === ''))) {
                    // ensure we keep at least one row in UI
                    if ($('#salesTableBody tr').length > 1) {
                        $r.remove();
                    } else {
                        // if only one row left, clear its fields instead of removing (keeps UI stable)
                        $r.find('select').val('');
                        $r.find('input').val('');
                        $r.find('.stock').val('');
                        $r.find('.sales-amount').val('0');
                    }
                }
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
                // If search term is present, use search route
                let q = params.data.q || '';
                let ajaxUrl = q.length > 0 ? searchUrl : url;
                let ajaxData = q.length > 0 ? { q: q } : { last_id: params.data.last_id || 0 };
                $.ajax({
                    url: ajaxUrl,
                    data: ajaxData,
                    dataType: 'json',
                    success: function (data) {
                        success(data);
                    },
                    error: failure
                });
            },
            delay: 250,
            data: function (params) {
                return {
                    q: params.term || '',
                    last_id: params.last_id || 0
                };
            },
            processResults: function (data, params) {
                // If search, expect array; if stack, expect {products:[], has_more}
                let results = [];
                let more = false;
                if (Array.isArray(data)) {
                    // Search route
                    results = data.map(function (p) {
                        return { id: p.id, text: p.item_name };
                    });
                    more = false;
                } else if (data.products) {
                    results = (data.products || []).map(function (p) {
                        return { id: p.id, text: p.item_name };
                    });
                    more = !!data.has_more;
                    // Save last_id for next fetch
                    if (results.length > 0) {
                        params.last_id = data.last_id;
                    }
                }
                return {
                    results: results,
                    pagination: { more: more }
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        placeholder: 'Search product...',
        allowClear: true,
        width: 'resolve'
    });

    // Infinite scroll on dropdown scroll
    $(document).on('select2:open', selector, function () {
        let results = document.querySelector('.select2-results__options');
        if (!results) return;
        results.addEventListener('scroll', function onScroll() {
            if (results.scrollTop + results.clientHeight >= results.scrollHeight - 5) {
                // Trigger Select2 to fetch more using last_id
                let $select = $(selector);
                let data = $select.data('select2');
                if (data && data.isOpen() && !$select.data('select2').options.get('ajax').transport.isSearching) {
                    $select.select2('open');
                }
            }
        }, { once: true });
    });
}

$(document).ready(function () {
    initProductSelect2('.product-select', '/search-products-sale', '/search_products');
});
    </script>
@endsection
