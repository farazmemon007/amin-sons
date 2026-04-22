{{-- Item Row Autocomplete + Add/Remove with Select2 --}}
<!-- Make sure jQuery and Bootstrap Typeahead are included -->
@extends('admin_panel.layout.app')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .searchResults {
        position: absolute;
        z-index: 9999;
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        background: #fff;
        /* border: 1px solid #ddd; */
    }

    .search-result-item.active {
        background: #007bff;
        color: white;
    }

    /* Select2 dropdown styling for consistency */
    .select2-results__options {
        max-height: 200px;
        overflow-y: auto;
    }

    .purchase-product-select {
        width: 100%;
    }

    /* === DISCOUNT WRAPPER STYLING (ERP Standard) === */
    .discount-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        gap: 4px;
        flex-wrap: nowrap;
    }

    /* Helper text for discount validation errors */
    .discount-help {
        position: absolute;
        left: 0;
        bottom: -36px;
        font-size: 0.85rem;
        line-height: 1;
        color: #dc3545;
        background: rgba(255, 255, 255, 0.9);
        padding: 0 4px;
        border-radius: 3px;
        white-space: nowrap;
    }

    /* Increase row height for discount helper text */
    .table tbody tr {
        min-height: 70px;
    }

    .table tbody td {
        padding-top: 1.2rem;
        padding-bottom: 1.4rem;
        vertical-align: middle;
    }

    /* Discount input styling */
    .discount-wrapper .discount-value {
        width: 65px;
        min-width: 65px;
        font-size: 0.85rem;
        padding: 4px 6px;
    }

    /* Discount toggle button */
    .discount-wrapper .discount-toggle {
        width: 45px;
        height: 32px;
        padding: 4px 8px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Invalid state styling - matching add_sale222 pattern */
    .invalid-cell {
        background-color: #fff5f5 !important;
        border: 1px solid #e3342f !important;
    }

    .invalid-input,
    .invalid-select {
        border-color: #e3342f !important;
        box-shadow: none !important;
    }

    /* === WAREHOUSE VALIDATION ERROR MESSAGE === */
    .warehouse-error {
        position: absolute;
        bottom: -28px;
        left: 0;
        font-size: 0.75rem;
        color: #dc3545;
        background: rgba(255, 255, 255, 0.95);
        padding: 2px 6px;
        border-radius: 3px;
        white-space: nowrap;
        display: none;
        z-index: 10;
    }

    .warehouse-error.show {
        display: block;
    }

    .warehouse-wrapper {
        position: relative;
        padding-bottom: 30px;
    }
</style>
@section('content')
@can('purchase.create')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container">
            <div class="row">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
                    rel="stylesheet">

                <style>
                    .table-scroll tbody {
                        display: block;
                        max-height: calc(60px * 5);
                        /* Assuming each row is ~40px tall */
                        overflow-y: auto;
                    }

                    .table-scroll thead,
                    .table-scroll tbody tr {
                        display: table;
                        width: 100%;
                        table-layout: fixed;
                    }

                    /* Optional: Hide scrollbar width impact */
                    .table-scroll thead {
                        width: calc(100% - 1em);
                    }

                    .table-scroll .icon-col {
                        width: 51px;
                        /* Ya jitni chhoti chahiye */
                        min-width: 51px;
                        max-width: 40px;
                    }

                    .table-scroll {
                        max-height: none !important;
                        overflow-y: visible !important;
                    }


                    .disabled-row input {
                        background-color: #f8f9fa;
                        pointer-events: none;
                    }
                </style>

                <body>
                    <!-- page-wrapper start -->

                   <form action="{{ route('store.Purchase') }}" method="POST">
@csrf

<style>
    body { background:#f5f6fa; }

    .card {
        border-radius: 10px;
        border: 1px solid #e0e3ea;
        box-shadow: 0 2px 8px rgba(0,0,0,.05);
    }

    .card-header {
        background: linear-gradient(90deg,#f8f9fc,#eef1f7);
        font-weight: 600;
        font-size: 17px;
        color: #2c3e50;
    }

    label {
        font-size: 14px;
        font-weight: 500;
        color: #495057;
    }

    .form-control, .form-select {
        border-radius: 6px;
        font-size: 14px;
    }

    .table thead th {
        background: #f1f3f5;
        font-weight: 600;
        font-size: 14px;
        text-align: center;
        white-space: nowrap;
    }

    .table tbody td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .btn-primary {
        padding: 8px 26px;
        border-radius: 6px;
    }
</style>
<style>
     .gp-actions-center {
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        /* .gp-action-btn {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 6px 12px;
      border-radius: 6px;
      background: #f8f9fa;
      text-decoration: none;
      color: #333;
      font-size: 14px;
    } */

        .gp-action-btn:hover {
            background: #e9ecef;
        }

        .gp-action-btn.danger {
            color: #dc3545;
        }

        .gp-action-btn {
            width: 60px;
            /* width kam */
            height: 60px;
            /* height zyada */
            padding: 10px;

            display: flex;
            flex-direction: column;
            /* icon upar, text neeche */
            align-items: center;
            justify-content: center;
            gap: 6px;

            background-color: #f1f3f5;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            font-size: 13px;
        }

        .gp-action-btn i {
            font-size: 20px;
        }
</style>
   <div class="gp-header row align-items-center mb-2">

            <!-- Left : Title -->
            <div class="col-md-3">
                <div class="gp-title">
                    <h5 class="mb-0 fw-semibold" style="font-size:20px">Purchase Product</h5>
                    {{-- <small class="text-muted">Create & manage inward stock entries</small> --}}
                </div>
            </div>

            {{-- <div class="row"> --}}
            <div class="col-7">
                <div class="gp-actions-center text-center">

                    <a href="{{ url('vendorlist') }}" class="gp-action-btn" >
                        <i class="fa fa-user-plus"></i>
                        <span>Vendor</span>
                    </a>


                    <a href="#" class="gp-action-btn">
                        <i class="fa fa-box"></i>
                        <span>Item</span>
                    </a>

                    {{-- <a href="#" class="gp-action-btn danger" onclick="return confirm('Delete this gatepass?')">
                        <i class="fa fa-trash"></i>
                        <span>Delete</span>
                    </a> --}}

                </div>
            </div>
            {{-- </div> --}}

            <!-- Right : Back Button -->
            <div class="col-md-2 text-end">
                <a href="{{ route('Purchase.home') }}" class="btn btn-outline-danger btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>

        </div>
<div class="container-fluid "style="background-color:white;padding:20px 20px;">
   

    <!-- ================= TOP TWO COLUMNS ================= -->
    <div class="row g-4 mt-3">

        <!-- LEFT : PURCHASE DETAILS -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header" style="font-size:20px">Purchase Details</div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label>Current Date</label>
                            <input type="date" name="purchase_date"
                                   value="{{ date('Y-m-d') }}"
                                   class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Purchase Invoice # <span class="text-danger">*</span> <small class="text-success">(ERP Auto)</small></label>
                            <div class="input-group">
                                <span class="input-group-text" style="background-color: #e8f4f8;">📋</span>
                                <input type="text" name="invoice_no" class="form-control" 
                                       value="{{ $nextInvoice ?? 'P-INV-0001' }}"
                                       style="background-color: #f0f8ff; font-weight: bold;"
                                       readonly title="Auto-generated purchase invoice number for Branch {{ $currentBranch ?? 1 }}">
                                <span class="input-group-text" style="background-color: #e8f4f8; font-size: 0.75rem;">Branch {{ $currentBranch ?? 1 }}</span>
                            </div>
                            <small class="text-muted">✅ Series: P-INV-{Sequence} | Each branch maintains separate counter | Same as Sales format</small>
                        </div>

                        <div class="col-md-6">
                            <label>Company Invoice #</label>
                            <input type="text" name="purchase_order_no" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label>Transport Name</label>
                            <input type="text" name="transport_name" class="form-control">
                        </div>

                        @if($isSuperAdmin)
                            {{-- ✅ ERP STANDARD: Super admin can select any branch --}}
                            <div class="col-md-6">
                                <label>Branch <span class="text-danger">*</span></label>
                                <select name="branch_id" id="branch_id" class="form-select" required>
                                    <option value="">Select Branch</option>
                                    @foreach($Branch as $b)
                                        <option value="{{ $b->id }}" @selected($b->id == $currentBranch)>
                                            {{ $b->name ?? $b->branch_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            {{-- ✅ ERP STANDARD: Simple users have their branch auto-locked --}}
                            <div class="col-md-6">
                                <label>Branch</label>
                                <input type="text" class="form-control" value="{{ $currentBranch }}" readonly style="background-color: #f0f8ff; cursor: not-allowed;">
                                <input type="hidden" name="branch_id" value="{{ $currentBranch }}">
                                <small class="text-muted">Your assigned branch</small>
                            </div>
                        @endif

                        <!-- ✅ Warehouse Selection (stored in purchase.warehouse_id header) -->
                        <div class="col-md-6">
                            <label>Warehouse <span class="text-danger">*</span></label>
                            <select name="warehouse_id" id="warehouse_id" class="form-select" required>
                                <option value="">Select Warehouse</option>
                                @foreach($Warehouse as $wh)
                                    <option value="{{ $wh->id }}">
                                        {{ $wh->warehouse_name }} - {{ $wh->location ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label>Job / Description</label>
                            <input type="text" name="note" class="form-control">
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT : VENDOR DETAILS -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header" style="font-size:20px">Vendor Details</div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-12">
                            <label>Vendor</label>
                            <select name="vendor_id" id="vendor_select" class="form-control">
                                <option disabled selected>Select Vendor</option>
                                @foreach ($Vendor as $v)
                                    <option value="{{ $v->id }}"
                                        data-phone="{{ $v->phone }}"
                                        data-email="{{ $v->email }}"
                                        data-address="{{ $v->address }}">
                                        {{ $v->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Phone</label>
                            <input type="text" id="vendor_phone" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Email</label>
                            <input type="text" id="vendor_email" class="form-control" readonly>
                        </div>

                        <div class="col-md-12">
                            <label>Address</label>
                            <input type="text" id="vendor_address" class="form-control" readonly>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ================= PRODUCT TABLE ================= -->
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center" style="font-size:20px">
            <span>Product Details</span>
            <button type="button" class="btn btn-sm btn-primary" id="btnAddRow">
                <i class="fa fa-plus"></i> Add Row
            </button>
        </div>
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-bordered mb-0" style="table-layout:fixed;">
                    <thead>
                        <tr>
                            <th style="width:220px">Product</th>
                            <th style="width:120px">Item Code</th>
                            <th style="width:120px">Brand</th>
                            <th style="width:90px">Unit</th>
                            <th style="width:140px">Warehouse</th>
                            <th style="width:110px">Price</th>
                            <th style="width:110px">Discount</th>
                            <th style="width:80px">Qty</th>
                            <th style="width:120px">Total</th>
                            <th style="width:70px">Action</th>
                        </tr>
                    </thead>

                    <tbody id="purchaseItems">
                        <tr>
                            <td>
                                <select class="form-select purchase-product-select product-select" name="product_id[]" style="width:100%">
                                    <option value="">Search product...</option>
                                </select>
                            </td>
                            <td><input type="text" name="item_code[]" class="form-control item_code" readonly></td>
                            <td><input type="text" name="brand_display[]" class="form-control brand_display" readonly></td>
                            <td><input type="text" name="unit[]" class="form-control unit" readonly></td>
                            <td>
                                <div class="warehouse-wrapper">
                                    <select name="line_warehouse_id[]" class="form-select warehouse_select">
                                        <option value="">Select Warehouse</option>
                                        @foreach ($Warehouse as $w)
                                            <option value="{{ $w->id }}">{{ $w->warehouse_name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="warehouse-error">Location is needed to purchase</div>
                                </div>
                            </td>
                            <td><input type="number" step="0.01" name="price[]" class="form-control price" value="1"></td>
                            <td>
                                <div class="discount-wrapper">
                                    <input type="number" step="0.01" name="item_disc[]" class="form-control discount-value text-end" value="0" placeholder="0" data-discount-type="percent">
                                    <button type="button" class="btn btn-outline-secondary btn-sm discount-toggle" data-type="percent">%</button>
                                </div>
                            </td>
                            <td><input type="number" name="qty[]" class="form-control quantity" value="1" min="1"></td>
                            <td><input type="text" name="total[]" class="form-control row-total" readonly></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-danger remove-row">×</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- ================= SUMMARY ================= -->
    <div class="card mt-3">
        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-3">
                    <label>Subtotal</label>
                    <input type="text" id="subtotal" class="form-control" readonly>
                </div>

                <div class="col-md-3">
                    <label>Overall Discount</label>
                    <input type="number" id="overallDiscount" class="form-control" value="0">
                </div>

                <div class="col-md-3">
                    <label>Extra Cost</label>
                    <input type="number" id="extraCost" class="form-control" value="0">
                </div>

                <div class="col-md-3">
                    <label>Net Amount</label>
                    <input type="text" id="netAmount" class="form-control fw-bold text-success" readonly>
                </div>

            </div>
        </div>
    </div>

    <!-- ================= PAYMENT SECTION ================= -->
    <div class="card mt-4">
        <div class="card-header" style="font-size:18px; font-weight:bold;">
            Payment Information
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Payment Method Selection -->
                <div class="col-md-4">
                    <label class="form-label fw-bold">Payment Method</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check payment-method-radio" name="payment_type" id="payLater" value="pay_later" checked>
                        <label class="btn btn-outline-primary" for="payLater">Pay Later</label>

                        <input type="radio" class="btn-check payment-method-radio" name="payment_type" id="payNow" value="pay_now">
                        <label class="btn btn-outline-success" for="payNow">Pay Now</label>
                    </div>
                </div>

                <!-- Account Selection (shown only if Pay Now) -->
                <div class="col-md-4" id="accountSelectDiv" style="display:none;">
                    <label class="form-label fw-bold">Payment Account</label>
                    <select name="payment_account_id" id="paymentAccount" class="form-select">
                        <option value="">-- Select Account --</option>
                        @forelse($bankAccounts ?? [] as $account)
                            <option value="{{ $account->id }}" data-type="{{ $account->type }}">
                                {{ $account->title }} ({{ $account->account_code }})
                            </option>
                        @empty
                            <option value="" disabled>No active accounts available</option>
                        @endforelse
                    </select>
                    @if(empty($bankAccounts) || $bankAccounts->isEmpty())
                        <small class="text-warning">⚠️ No active accounts found. Please create bank/cash accounts in Accounting module.</small>
                    @endif
                </div>

                <!-- Payment Amount (shown only if Pay Now) -->
                <div class="col-md-4" id="paymentAmountDiv" style="display:none;">
                    <label class="form-label fw-bold">Payment Amount <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="payment_amount" id="paymentAmount" class="form-control" placeholder="0.00">
                    <small class="text-info">💡 Enter exact amount to pay now. Partial payments supported - remaining amount tracked as due to vendor.</small>
                </div>

                <!-- Outstanding/Due Amount (calculated in real-time) -->
<div class="col-md-4 ms-auto" id="outstandingAmountDiv" style="display:none;">                    <label class="form-label fw-bold">Outstanding Amount (Vendor Ledger)</label>
                    <div class="input-group">
                        <span class="input-group-text">PKR</span>
                        <input type="text" id="outstandingAmount" class="form-control" readonly style="background-color: #f0f8ff; font-weight: bold; font-size: 1.1rem;">
                    </div>
                    <small class="text-success">✅ This amount will be added to vendor ledger as outstanding balance</small>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= SUBMIT ================= -->
    <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fa fa-save"></i> Save Purchase
        </button>
    </div>

</div>
</form>
</body>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Success & Error Messages --}}
    @if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: @json(session('success')),
            confirmButtonColor: '#3085d6',
        });
    </script>
    @endif


    @if ($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            html: {
                !!json_encode(implode('<br>', $errors - > all())) !!
            },
            confirmButtonColor: '#d33',
        });
    </script>
    @endif

    {{-- Cancel Button Confirmation --}}
    <script>
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
    </script>

    {{-- Item Row Autocomplete + Add/Remove --}}
    <!-- Make sure jQuery and Bootstrap Typeahead are included -->

    <script>
        // ✅ ERP STANDARD: All warehouses data (with branch mapping for filtering)
        const allWarehouses = {!! json_encode(\App\Models\Warehouse::with('branches')->get()->map(fn($w) => [
            'id' => $w->id, 
            'name' => $w->warehouse_name,
            'branch_ids' => $w->branches->pluck('id')->toArray()
        ])->toArray()) !!};
        
        // ✅ Current branch ID for filtering
        let currentBranchId = parseInt($('#branch_id').val() || {{ $currentBranch }});
        
        // ✅ Get warehouses filtered by branch
        function getFilteredWarehouses(branchId) {
            return allWarehouses.filter(w => w.branch_ids.includes(branchId));
        }
        
        // Helper function to build warehouse select HTML
        function buildWarehouseSelect(branchId = null) {
            const branch = branchId || currentBranchId;
            const filtered = getFilteredWarehouses(branch);
            let html = '<option value="">Use Header</option>';
            filtered.forEach(function(w) {
                html += '<option value="' + w.id + '">' + w.name + '</option>';
            });
            return html;
        }

        $(document).ready(function() {
            
            // ✅ UPDATE WAREHOUSE OPTIONS when branch changes
            $('#branch_id').on('change', function() {
                currentBranchId = parseInt($(this).val());
                // Update all warehouse dropdowns
                $('.warehouse_select').html(buildWarehouseSelect(currentBranchId));
            });

            // ---------- Helpers ----------
            function num(n) {
                return isNaN(parseFloat(n)) ? 0 : parseFloat(n);
            }

            function recalcRow($row) {
                const qty = num($row.find('.quantity').val());
                const price = num($row.find('.price').val());
                const $discInput = $row.find('.discount-value');
                const $discToggle = $row.find('.discount-toggle');
                const discType = $discToggle.data('type') || 'percent'; // percent | pkr
                const rawDisc = Math.max(0, $discInput.val());
                let discValue = rawDisc;
                
                // Calculate row gross amount (before discount)
                const rowGross = qty * price;
                
                let discountAmount = 0;
                
                // ✅ AUTO DISCOUNT VALIDATION (like add_sale222.blade.php)
                if (discValue > 0) {
                    if (discType === 'percent') {
                        // If user entered >100%, mark invalid and show helper text
                        if (rawDisc > 100) {
                            markInvalid($discInput);
                            const $wrapper = $row.find('.discount-wrapper');
                            if ($wrapper.find('.discount-help').length === 0) {
                                $wrapper.append('<div class="discount-help">Discount cannot exceed 100%</div>');
                            } else {
                                $wrapper.find('.discount-help').text('Discount cannot exceed 100%');
                            }
                            // Use 100 for calculation but keep warning
                            discValue = 100;
                        } else {
                            clearInvalid($discInput);
                            $row.find('.discount-help').remove();
                            discValue = Math.min(discValue, 100);
                        }
                        discountAmount = (rowGross * discValue) / 100;
                    } else {
                        // PKR discount should not exceed gross per row
                        if (discValue > rowGross) {
                            markInvalid($discInput);
                            const $wrapper = $row.find('.discount-wrapper');
                            if ($wrapper.find('.discount-help').length === 0) {
                                $wrapper.append('<div class="discount-help">Discount cannot exceed row total</div>');
                            } else {
                                $wrapper.find('.discount-help').text('Discount cannot exceed row total');
                            }
                            // Cap discount but keep user's input unchanged
                            discountAmount = rowGross;
                        } else {
                            clearInvalid($discInput);
                            $row.find('.discount-help').remove();
                            discountAmount = discValue;
                        }
                    }
                } else {
                    // No discount - clear invalid state
                    clearInvalid($discInput);
                    $row.find('.discount-help').remove();
                    discountAmount = 0;
                }
                
                // Row total = Gross - Discount
                let total = rowGross - discountAmount;
                if (total < 0) total = 0;
                $row.find('.row-total').val(total.toFixed(2));
            }


            function recalcSummary() {
                let sub = 0;
                let totalLineDiscount = 0; // Sum of all line-item discounts (informational)
                
                // Sum all row totals and calculate total discounts
                $('#purchaseItems tr').each(function() {
                    const qty = num($(this).find('.quantity').val());
                    const price = num($(this).find('.price').val());
                    const $discInput = $(this).find('.discount-value');
                    const $discToggle = $(this).find('.discount-toggle');
                    const discType = $discToggle.data('type') || 'percent';
                    const rawDisc = Math.max(0, $discInput.val());
                    
                    const rowGross = qty * price;
                    let discountAmount = 0;
                    
                    // Calculate discount for this row (matching recalcRow logic)
                    if (rawDisc > 0) {
                        if (discType === 'percent') {
                            const discPercent = Math.min(rawDisc, 100);
                            discountAmount = (rowGross * discPercent) / 100;
                        } else {
                            discountAmount = Math.min(rawDisc, rowGross);
                        }
                    }
                    
                    totalLineDiscount += discountAmount;
                    
                    const rowTotal = num($(this).find('.row-total').val());
                    sub += rowTotal;
                });
                
                $('#subtotal').val(sub.toFixed(2));
                
                // ✅ Display total line discounts in Overall Discount field (informational)
                $('#overallDiscount').val(totalLineDiscount.toFixed(2));
                
                // Get extra cost with default
                const xCost = num($('#extraCost').val() || 0);
                
                // ERP Formula: Subtotal is already net after line discounts, so just add extra cost
                // Net = Subtotal (which = Gross - Line Discounts) + Extra Cost
                const net = (sub + xCost);
                $('#netAmount').val(net.toFixed(2));
            }

            function appendBlankRow() {
                const newRow = `
      <tr>
        <td>
          <select class="form-select purchase-product-select product-select" name="product_id[]" style="width:100%">
            <option value="">Search product...</option>
          </select>
        </td>
        <td><input type="text" name="item_code[]" class="form-control item_code" readonly></td>
        <td><input type="text" name="brand_display[]" class="form-control brand_display" readonly></td>
        <td><input type="text" name="unit[]" class="form-control unit" readonly></td>
        <td>
          <div class="warehouse-wrapper">
            <select name="line_warehouse_id[]" class="form-select warehouse_select">
              ${buildWarehouseSelect()}
            </select>
            <div class="warehouse-error">Location is needed to purchase</div>
          </div>
        </td>
        <td><input type="number" step="0.01" name="price[]" class="form-control price" value="1"></td>
        <td>
          <div class="discount-wrapper">
            <input type="number" step="0.01" name="item_disc[]" class="form-control discount-value text-end" value="0" placeholder="0" data-discount-type="percent">
            <button type="button" class="btn btn-outline-secondary btn-sm discount-toggle" data-type="percent">%</button>
          </div>
        </td>
        <td><input type="number" name="qty[]" class="form-control quantity" value="1" min="1"></td>
        <td><input type="text" name="total[]" class="form-control row-total" readonly></td>
        <td><button type="button" class="btn btn-sm btn-danger remove-row">×</button></td>
      </tr>`;
                $('#purchaseItems').append(newRow);
                
                // Initialize Select2 for the newly added product select
                initProductSelect2('#purchaseItems tr:last-child .product-select', '/search-products-sale', '/search_products');
            }

            // ---------- SELECT2 Product Select Initialization ----------
            function initProductSelect2(
                selector = '.product-select',
                url = '/search-products-sale',
                searchUrl = '/search_products'
            ) {
                $(selector).select2({
                    ajax: {
                        transport: function(params, success, failure) {
                            let term = (params.data && (params.data.term || params.data.q)) || '';
                            let page = (params.data && (params.data.page || 1)) || 1;
                            let ajaxUrl = term && term.length > 0 ? searchUrl : url;
                            $.ajax({
                                url: ajaxUrl,
                                data: {
                                    q: term,
                                    page: page
                                },
                                dataType: 'json',
                                success: function(data) {
                                    success(data);
                                },
                                error: failure
                            });
                        },
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term || '',
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            
                            // Get all already-selected product IDs to prevent duplicates (ERP Standard)
                            const selectedIds = [];
                            $('#purchaseItems .product-select').each(function() {
                                const val = $(this).val();
                                if (val) {
                                    selectedIds.push(parseInt(val));
                                }
                            });
                            
                            let results = [];
                            if (Array.isArray(data)) {
                                results = data.filter(function(p) {
                                    return selectedIds.indexOf(p.id) === -1; // Exclude already selected
                                }).map(function(p) {
                                    return {
                                        id: p.id,
                                        text: p.item_name + (p.item_code ? ' [' + p.item_code + ']' : ''),
                                        item_code: p.item_code,
                                        brand_name: p.brand_name || '',
                                        unit_name: p.unit_name || '',
                                        unit_id: p.unit_id,
                                        price: p.price || 0,
                                        is_primary: p.is_primary || false
                                    };
                                });
                                return {
                                    results: results,
                                    pagination: {
                                        more: false
                                    }
                                };
                            }

                            results = (data.products || []).filter(function(p) {
                                return selectedIds.indexOf(p.id) === -1; // Exclude already selected
                            }).map(function(p) {
                                return {
                                    id: p.id,
                                    text: p.item_name + (p.item_code ? ' [' + p.item_code + ']' : ''),
                                    item_code: p.item_code,
                                    brand_name: p.brand_name || '',
                                    unit_name: p.unit_name || '',
                                    unit_id: p.unit_id,
                                    price: p.price || 0,
                                    is_primary: p.is_primary || false
                                };
                            });

                            return {
                                results: results,
                                pagination: {
                                    more: !!data.has_more
                                }
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0,
                    placeholder: 'Search product...',
                    allowClear: true,
                    width: 'resolve',
                    // ✅ Custom template to render Primary/Secondary status with HTML
                    templateResult: function(data) {
                        if (!data.id) return data.text;
                        const statusBadge = data.is_primary 
                            ? '<span style="color: #28a745; font-weight: 600; margin-left: 8px;">🟢 PRIMARY</span>'
                            : '<span style="color: #ffc107; font-weight: 600; margin-left: 8px;">🟡 SECONDARY</span>';
                        return $('<span>' + data.text + statusBadge + '</span>');
                    },
                    templateSelection: function(data) {
                        // In selection (after click), show only text without status badge
                        return data.text;
                    }
                });
            }

            // ---------- Product Search (SELECT2 EVENT) ----------
            $(document).on('select2:select', '.product-select', function(e) {
                if (e && e.params && e.params.data && e.params.data.id) {
                    const $row = $(this).closest('tr');
                    const productData = e.params.data;
                    
                    // Populate product fields from Select2 result into respective columns
                    // Item Code column
                    $row.find('.item_code').val(productData.item_code || '');
                    
                    // Brand column (display only) - use brand_name from API response
                    $row.find('.brand_display').val(productData.brand_name || 'NULL');
                    
                    // Unit column - use unit_name from API response
                    $row.find('.unit').val(productData.unit_name || '');
                    
                    // Price column (wholesale price)
                    $row.find('.price').val(productData.price || 0);
                    
                    // Reset qty and discount for fresh calculation
                    $row.find('.quantity').val(1);
                    $row.find('.item_disc').val(0);
                    
                    // Calculate row totals
                    recalcRow($row);
                    recalcSummary();
                    
                    // Refresh all Select2 dropdowns to hide selected products (ERP Standard - duplicate prevention)
                    const self = this;
                    $('.product-select').each(function() {
                        // Only trigger if it's not the current dropdown and has data already
                        if (this !== self && $(this).val()) {
                            $(this).trigger('change');
                        }
                    });
                    
                    // ✅ DISABLED: Auto-append removed - user must click "Add Row" button
                    // New row only appears when "Add Row" button is explicitly clicked
                }
            });

            // Row calculations
            $('#purchaseItems').on('input', '.quantity, .price, .discount-value', function() {
                const $row = $(this).closest('tr');
                recalcRow($row);
                recalcSummary();
            });

            // ---------- HELPER FUNCTIONS (matching add_sale222) ----------
            function markInvalid($el) {
                $el.addClass('invalid-input invalid-select');
                $el.closest('td').addClass('invalid-cell');
            }

            function clearInvalid($el) {
                $el.removeClass('invalid-input invalid-select');
                $el.closest('td').removeClass('invalid-cell');
            }

            // ---------- DISCOUNT TOGGLE HANDLER (ERP Standard - % vs PKR) ----------
            $(document).on('click', '.discount-toggle', function() {
                const $btn = $(this);
                const currentType = $btn.data('type');
                const $row = $btn.closest('tr');
                const $discInput = $row.find('.discount-value');
                
                // Toggle between percent and pkr
                if (currentType === 'percent') {
                    $btn.data('type', 'pkr').text('PKR');
                    $discInput.attr('data-discount-type', 'pkr');
                } else {
                    $btn.data('type', 'percent').text('%');
                    $discInput.attr('data-discount-type', 'percent');
                }
                
                // Recalculate row with new discount type (validation happens inside recalcRow)
                recalcRow($row);
                recalcSummary();
            });

            // Remove row
            $('#purchaseItems').on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                recalcSummary();
                
                // Refresh Select2 dropdowns to show removed product again (ERP Standard)
                $('.product-select').each(function() {
                    if ($(this).val()) {
                        $(this).trigger('change');
                    }
                });
            });

            // Add new row button (ERP Standard)
            $('#btnAddRow').on('click', function() {
                appendBlankRow();
                // Focus on the newly added product select
                $('#purchaseItems tr:last-child .product-select').select2('focus');
            });

            // Summary inputs
            $('#overallDiscount, #extraCost').on('input', function() {
                recalcSummary();
            });

            // ---------- VENDOR SELECTION HANDLER (ERP STANDARD) ----------
            $(document).on('change', '#vendor_select', function() {
                const $selected = $(this).find('option:selected');
                const vendorId = $(this).val();

                if (!vendorId) {
                    // Clear all vendor details if no vendor selected
                    $('#vendor_phone').val('');
                    $('#vendor_email').val('');
                    $('#vendor_address').val('');
                    return;
                }

                // Populate vendor details from data attributes (efficient approach)
                const phone = $selected.data('phone') || '';
                const email = $selected.data('email') || '';
                const address = $selected.data('address') || '';

                $('#vendor_phone').val(phone);
                $('#vendor_email').val(email);
                $('#vendor_address').val(address);
            });

            // ========== PAYMENT METHOD TOGGLE HANDLER ==========
            $(document).on('change', '.payment-method-radio', function() {
                const paymentType = $(this).val();
                
                if (paymentType === 'pay_now') {
                    // Show payment account and amount fields
                    $('#accountSelectDiv').slideDown();
                    $('#paymentAmountDiv').slideDown();
                } else {
                    // Hide payment fields
                    $('#accountSelectDiv').slideUp();
                    $('#paymentAmountDiv').slideUp();
                    // Clear payment fields
                    $('#paymentAccount').val('');
                    $('#paymentAmount').val('');
                }
            });

            // ✅ Payment Amount - REQUIRED (Supports Partial Payments)
            // DO NOT auto-fill - user must explicitly specify payment amount
            $(document).on('click', '#payNow', function() {
                $('#paymentAmount').val(''); // Clear any existing value
                $('#outstandingAmountDiv').slideDown(); // Show outstanding amount field
                $('#paymentAmount').focus(); // Focus on input to prompt user
                console.log('✅ Please enter the exact payment amount. Partial payments are supported.');
            });

            // Clear payment amount when switching to Pay Later
            $(document).on('click', '#payLater', function() {
                $('#paymentAmount').val('');
                $('#outstandingAmountDiv').slideUp(); // Hide outstanding amount field
                $('#outstandingAmount').val(''); // Clear outstanding amount value
            });

            // ✅ Calculate Outstanding Amount (Vendor Ledger) in Real-Time
            // Outstanding = Net Amount - Payment Amount
            function calculateOutstanding() {
                const netAmount = num($('#netAmount').val() || 0);
                const paymentAmount = num($('#paymentAmount').val() || 0);
                const outstanding = netAmount - paymentAmount;
                
                // Format and display
                if (outstanding < 0) {
                    $('#outstandingAmount').val('0.00 ⚠️ Overpayment!');
                    $('#outstandingAmount').css('color', '#e74c3c');
                } else if (outstanding === 0) {
                    $('#outstandingAmount').val('0.00 ✅ Fully Paid');
                    $('#outstandingAmount').css('color', '#27ae60');
                } else {
                    $('#outstandingAmount').val(outstanding.toFixed(2));
                    $('#outstandingAmount').css('color', '#2c3e50');
                }
                
                console.log(`🔄 Outstanding: ${outstanding.toFixed(2)}`);
            }

            // Recalculate when payment amount changes
            $(document).on('input', '#paymentAmount', function() {
                calculateOutstanding();
            });

            // Recalculate when net amount changes
            $(document).on('change', '#netAmount', function() {
                if ($('#payNow').is(':checked')) {
                    calculateOutstanding();
                }
            });

            // init first row values
            initProductSelect2('.product-select', '/search-products-sale', '/search_products');
            recalcRow($('#purchaseItems tr:first'));
            recalcSummary();

            // ✅ FORM VALIDATION: Check if Payment Amount is required when "Pay Now" is selected
            $('form').on('submit', function(e) {
                const isPayNow = $('#payNow').is(':checked');
                const paymentAmount = parseFloat($('#paymentAmount').val()) || 0;

                if (isPayNow && paymentAmount <= 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Payment Amount Required',
                        text: 'Please enter a payment amount greater than 0 for "Pay Now" option.',
                        confirmButtonColor: '#3085d6',
                    });
                    $('#paymentAmount').focus();
                    return false;
                }

                // ✅ WAREHOUSE VALIDATION: Check if any row has product but no warehouse selected
                let warehouseValidationError = false;
                $('#purchaseItems tr').each(function() {
                    const $row = $(this);
                    const productId = $row.find('.product-select').val();
                    const warehouseId = $row.find('.warehouse_select').val();
                    const $warehouseError = $row.find('.warehouse-error');
                    
                    // If product is selected but warehouse is empty, show error
                    if (productId && !warehouseId) {
                        $warehouseError.addClass('show');
                        $row.find('.warehouse_select').addClass('invalid-select');
                        warehouseValidationError = true;
                    } else {
                        $warehouseError.removeClass('show');
                        $row.find('.warehouse_select').removeClass('invalid-select');
                    }
                });

                if (warehouseValidationError) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Warehouse Location Required',
                        text: 'Please select a warehouse location for all products, or leave empty to use header warehouse.',
                        confirmButtonColor: '#3085d6',
                    });
                    return false;
                }

                // ✅ DISABLE SUBMIT BUTTON ON CLICK (Prevent duplicate submissions)
                const submitBtn = $('button[type="submit"]');
                if (!submitBtn.prop('disabled')) {
                    submitBtn.prop('disabled', true);
                    submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...');
                }
            });
        });
    </script>
@else
    <div class="container py-4">
        <div class="alert alert-danger">You do not have permission to create Purchases.</div>
    </div>
@endcan
@endsection