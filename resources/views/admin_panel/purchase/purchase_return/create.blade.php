{{-- Item Row Autocomplete + Add/Remove --}}
<!-- Make sure jQuery and Bootstrap Typeahead are included -->
@extends('admin_panel.layout.app')
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
</style>
@section('content')
@can('purchase.return.create')
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

                        <div class="body-wrapper">
                            <div class="bodywrapper__inner">
                                <div
                                    class="d-flex justify-content-between align-items-center mb-3 flex-nowrap overflow-auto">
                                    <!-- Title on the left -->
                                    <div class="flex-grow-1">
                                        <h6 class="page-title m-0">INWARDS GATE PASSES</h6>
                                    </div>

                                    <!-- Buttons on the right -->
                                    <div class="d-flex gap-4 justify-content-end flex-wrap">
                                        <button type="button" class="btn btn-outline-primary " style="margin-right: 5px"
                                            data-bs-toggle="modal" data-bs-target="#supplierModal">
                                            <i class="la la-truck-loading"></i> Add New Vendor
                                        </button>

                                        <button type="button" class="btn btn-outline-success " style="margin-right: 5px"
                                            data-bs-toggle="modal" data-bs-target="#transportModal">
                                            <i class="la la-truck"></i> Add New Transport
                                        </button>

                                        <button type="button" class="btn btn-outline-warning text-dark "
                                            style="margin-right: 5px" data-bs-toggle="modal"
                                            data-bs-target="#warehouseModal">
                                            <i class="la la-warehouse"></i> Add New Warehouse
                                        </button>

                                        <a href="#" class="btn btn-outline-info " style="margin-right: 5px">
                                            <i class="la la-plus"></i> Add Product
                                        </a>

                                        {{-- <button type="button" class="btn btn-outline-danger " id="cancelBtn">
            Cancel
        </button> --}}
                                        <a href="{{ route('Purchase.home') }}" class="btn btn-danger">Back </a>
                                    </div>



                                </div>



                                <div class="row gy-3">
                                    <div class="col-lg-12 col-md-12 mb-30">
                                        <div class="card">
                                            <div class="card-body">
                                                {{-- <form action="{{ route('store-Purchase') }}" method="POST"> --}}
                                                @if ($errors->any())
                                                    <div class="alert alert-danger">
                                                        <ul>
                                                            @foreach ($errors->all() as $error)
                                                                <li>{{ $error }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                                @if (session('success'))
                                                    <div class="alert alert-success alert-dismissible fade show"
                                                        role="alert">
                                                        <strong>Success!</strong> {{ session('success') }}
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                            aria-label="Close"></button>
                                                    </div>
                                                @endif

                                                <form action="{{ route('purchase.return.store') }}" method="POST">
                                                    @csrf
                                                    @php
                                                        $isReturn = isset($purchase);
                                                    @endphp

                                                    <div class="row mb-3 g-3 mt-4">
                                                        <div class="col-xl-12">
                                                            <div class="row g-3">
                                                                <!-- Purchase Date -->
                                                                <div class="col-xl-3 col-sm-6 mt-3">
                                                                    <label><i
                                                                            class="bi bi-calendar-date text-primary me-1"></i>
                                                                        Purchase Date</label>
                                                                    <input name="purchase_date" type="date"
                                                                        class="form-control"
                                                                        value="{{ $purchase->created_at ?? date('Y-m-d') }}">
                                                                </div>

                                                                <!-- Return Date -->
                                                                <div class="col-xl-3 col-sm-6 mt-3">
                                                                    <label><i
                                                                            class="bi bi-calendar-date text-primary me-1"></i>
                                                                        Return Date</label>
                                                                    <input name="return_date" type="date"
                                                                        class="form-control" value="{{ date('Y-m-d') }}">
                                                                </div>


                                                                <div class="col-xl-3 col-sm-6 mt-3">
                                                                    <label><i class="bi bi-receipt text-primary me-1"></i>
                                                                        Companies/Vendors</label>
                                                                    {{-- <input name="challan_no" type="text" class="form-control"> --}}
                                                                    <select name="vendor_id" class="form-control">
                                                                        <option disabled selected>Select One</option>
                                                                        @foreach ($Vendor as $item)
                                                                            <option value="{{ $item->id }}"
                                                                                {{ $isReturn && $purchase->vendor_id == $item->id ? 'selected' : '' }}>
                                                                                {{ $item->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>

                                                                </div>
                                                                <div class="col-xl-3 col-sm-6 mt-3">
                                                                    <label><i
                                                                            class="bi bi-file-earmark-text text-primary me-1"></i>
                                                                        Company Inv #</label>
                                                                    <input name="purchase_order_no" type="text"
                                                                        class="form-control"
                                                                        value="{{ $isReturn ? $purchase->invoice_no : '' }}">

                                                                </div>
                                                                <div class="col-xl-3 col-sm-6 mt-3">
                                                                    <label>
                                                                        <i class="bi bi-building text-primary me-1"></i>
                                                                        Warehouse
                                                                    </label>
                                                                    <select name="warehouse_id" class="form-control">
                                                                        <option disabled selected>Select One</option>
                                                                        @foreach ($Warehouse as $item)
                                                                            <option value="{{ $item->id }}"
                                                                                {{ $isReturn && $purchase->warehouse_id == $item->id ? 'selected' : '' }}>
                                                                                {{ $item->warehouse_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>

                                                                </div>


                                                                <div class="col-xl-6 col-sm-6 mt-3">
                                                                    <label><i class="bi bi-card-text text-primary me-1"></i>
                                                                        Job No & Description</label>
                                                                    <input name="note" type="text"
                                                                        class="form-control"
                                                                        value="{{ $isReturn ? $purchase->note : '' }}">
                                                                </div>

                                                                <div class="col-xl-6 col-sm-6 mt-3">
                                                                    <label><i class="bi bi-card-text text-primary me-1"></i>
                                                                        Transport Name</label>
                                                                    <input name="job_description" type="text"
                                                                        class="form-control"
                                                                        value="{{ $isReturn ? $purchase->job_description : '' }}">
                                                                </div>

                                                            </div>

                                                            <!-- Supplier Info Row -->
                                                            <div class="row mt-4">
                                                            </div>
                                                        </div>
                                                    </div>



                                                    <!-- Item Code Table -->

                                                    <div style="max-height: 300px; overflow-y: scroll; ">
                                                        <table class="table mt-3 table-bordered">
                                                            <thead>
                                                                <tr class="text-center">
                                                                    <th>product</th>
                                                                    <th>Item Code</th>

                                                                    <th>Brand</th>
                                                                    <th>Unit</th>
                                                                    <th>Price</th>
                                                                    <th>Discount</th>
                                                                    <th>Qty</th>
                                                                    <th>Total</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="purchaseItems">
                                                                @if ($isReturn)
                                                                    @foreach ($purchase->items as $item)
                                                                        <tr>
                                                                            <td>
                                                                                <input type="hidden" name="product_id[]"
                                                                                    class="product_id"
                                                                                    value="{{ $item->product_id }}">
                                                                                <input type="text" class="form-control"
                                                                                    value="{{ $item->product->name ?? '' }}"
                                                                                    readonly>
                                                                            </td>
                                                                            <td><input type="text" name="item_code[]"
                                                                                    class="form-control"
                                                                                    value="{{ $item->product->item_code ?? '' }}"
                                                                                    readonly></td>
                                                                            <td><input type="text" name="uom[]"
                                                                                    class="form-control"
                                                                                    value="{{ $item->product->uom ?? '' }}"
                                                                                    readonly></td>
                                                                            <td><input type="text" name="unit[]"
                                                                                    class="form-control"
                                                                                    value="{{ $item->unit }}" readonly>
                                                                            </td>
                                                                            <td><input type="number" name="price[]"
                                                                                    class="form-control price"
                                                                                    value="{{ $item->price }}"></td>
                                                                            <td><input type="number" name="item_disc[]"
                                                                                    class="form-control item_disc"
                                                                                    value="{{ $item->item_discount }}">
                                                                            </td>
                                                                            <td><input type="number" name="qty[]"
                                                                                    class="form-control quantity"
                                                                                    value="{{ $item->qty }}"></td>
                                                                            <td><input type="text" name="total[]"
                                                                                    class="form-control row-total"
                                                                                    value="{{ $item->line_total }}"
                                                                                    readonly></td>
                                                                            <td><button type="button"
                                                                                    class="btn btn-sm btn-danger remove-row">X</button>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @else
                                                                    <!-- Empty row for normal purchase entry -->
                                                                @endif
                                                            </tbody>



                                                        </table>
                                                    </div>

                                                    <div class="row g-3 mt-3">
                                                        <div class="col-md-3">
                                                            <label>Subtotal</label>
                                                            <input type="text" name="subtotal"
                                                                value="{{ $isReturn ? $purchase->subtotal : 0 }}" readonly
                                                                class="form-control">

                                                        </div>

                                                        <div class="col-md-3">
                                                            <label>Discount (Overall)</label>
                                                            <input type="number" step="0.01" id="overallDiscount"
                                                                class="form-control" name="discount" value="0">
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label>Extra Cost</label>
                                                            <input type="number" step="0.01" id="extraCost"
                                                                class="form-control" name="extra_cost" value="0">
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label>Net Amount</label>
                                                            <input type="text" id="netAmount" name="net_amount"
                                                                class="form-control fw-bold" value="0" readonly>
                                                        </div>
                                                    </div>
                                                    <!-- =========================== -->

                                                    <!-- ===== END SUMMARY ===== -->



                                                    {{-- 
                                                    <button type="submit" class="btn btn-primary w-100 mt-4">Submit
                                                        Purchase</button> --}}
                                                    <button type="submit" class="btn btn-primary mt-4">Submit
                                                        Return</button>


                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div><!-- bodywrapper__inner end -->
                        </div><!-- body-wrapper end -->
                </div>

                <!-- Warehouse Modal -->
                <div class="modal fade" id="warehouseModal">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Warehouse</h5>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="las la-times"></i>
                                </button>
                            </div>

                            <form action="" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Branch</label>
                                        <select name="branch_id" class="form-control select2">
                                            <option disabled selected>Select Branch</option>
                                            <option value="0">Main Super Admin</option>
                                            {{-- @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach --}}
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" class="form-control" name="address">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn--primary w-100 h-45">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Transport Modal -->
                <div class="modal fade" id="transportModal">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Transport</h5>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="las la-times"></i>
                                </button>
                            </div>

                            <form action="" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Company Name</label>
                                                <input type="text" name="company_name" class="form-control"
                                                    autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Name</label>
                                                <input type="text" name="name" class="form-control"
                                                    autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" class="form-control" name="email">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Mobile</label>
                                                <input type="number" name="mobile" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Address</label>
                                                <input type="text" name="address" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn--primary w-100 h-45">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Veondor Modal -->
                <div class="modal fade" id="supplierModal">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Supplier</h5>
                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="las la-times"></i>
                                </button>
                            </div>

                            <form action="" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Name</label>
                                                <input type="text" name="name" class="form-control"
                                                    autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label">E-Mail</label>
                                                <input type="email" class="form-control" name="email">
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="form-label">
                                                    Mobile
                                                    <i class="fa fa-info-circle text--primary"
                                                        title="Type the mobile number including the country code. Otherwise, SMS won't send to that number."></i>
                                                </label>
                                                <input type="number" name="mobile" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label>Company</label>
                                                <input type="text" name="company_name" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <label>Address</label>
                                                <input type="text" name="address" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn--primary w-100 h-45">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <div class="container py-4">
            <div class="alert alert-danger">You do not have permission to create Purchase Returns.</div>
        </div>
    @endcan
    @endsection
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form[action='{{ route('store.Purchase') }}']");
            const submitBtn = document.getElementById("submitBtn");

            // Enter key se form submit disable
            form.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                }
            });

            // Sirf button click pe submit
            submitBtn.addEventListener("click", function() {
                form.submit();
            });
        });
    </script>

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
                html: {!! json_encode(implode('<br>', $errors->all())) !!},
                confirmButtonColor: '#d33',
            });
        </script>
    @endif

    {{-- Cancel Button Confirmation --}}
    <script>
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
    </script>

    {{-- Item Row Autocomplete + Add/Remove --}}
    <!-- Make sure jQuery and Bootstrap Typeahead are included -->

    <script>
        $(document).ready(function() {

            // ---------- Helpers ----------
            function num(n) {
                return isNaN(parseFloat(n)) ? 0 : parseFloat(n);
            }

            function recalcRow($row) {
                const qty = num($row.find('.quantity').val());
                const price = num($row.find('.price').val());
                const disc = num($row.find('.item_disc').val()); // per-item discount
                let total = (qty * price) - (qty * disc); // ✅ correct formula
                if (total < 0) total = 0;
                $row.find('.row-total').val(total.toFixed(2));
            }


            function recalcSummary() {
                let sub = 0;
                $('#purchaseItems .row-total').each(function() {
                    sub += num($(this).val());
                });
                $('#subtotal').val(sub.toFixed(2));

                const oDisc = num($('#overallDiscount').val());
                const xCost = num($('#extraCost').val());
                const net = (sub - oDisc + xCost);
                $('#netAmount').val(net.toFixed(2));
            }

            function appendBlankRow() {
                const newRow = `
      <tr>
        
         <td>
        <input type="hidden" name="product_id[]" class="product_id">
        <input type="text" class="form-control productSearch" placeholder="Enter product name..." autocomplete="off">
        <ul class="searchResults list-group mt-1"></ul>
    </td>
        <td class="item_code border"><input type="text" name="item_code[]" class="form-control" readonly></td>
        <td class="uom border"><input type="text" name="uom[]" class="form-control" readonly></td>
        <td class="unit border"><input type="text" name="unit[]" class="form-control" readonly></td>
        <td><input type="number" step="0.01" name="price[]" class="form-control price" value="1" ></td>
        <td><input type="number" step="0.01" name="item_disc[]" class="form-control item_disc" value=""></td>
        <td class="qty"><input type="number" name="qty[]" class="form-control quantity" value="" min="1"></td>
        <td class="total border"><input type="text" name="total[]" class="form-control row-total" readonly></td>
        <td><button type="button" class="btn btn-sm btn-danger remove-row">X</button></td>
      </tr>`;
                $('#purchaseItems').append(newRow);
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
                                data-product-uom="${brand}"
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
                $row.find('.item_code input').val($li.data('product-code'));
                $row.find('.uom input').val($li.data('product-uom'));
                $row.find('.unit input').val($li.data('product-unit'));
                $row.find('.price').val($li.data('price'));

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
                $('#purchaseItems tr:last .productSearch').focus();
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
    </script>
