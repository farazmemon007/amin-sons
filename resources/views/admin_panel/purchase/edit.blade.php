@extends('admin_panel.layout.app')

@section('content')
@can('purchase.edit')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container">
            <div class="row">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
                    rel="stylesheet">

                <div class="body-wrapper">
                    <div class="bodywrapper__inner">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-nowrap overflow-auto">
                            <div class="flex-grow-1">
                                <h6 class="page-title m-0">Edit Purchase</h6>
                            </div>
                            <div class="d-flex gap-4 justify-content-end flex-wrap">
                                <a href="{{ route('Purchase.home') }}" class="btn btn-danger">Back</a>
                            </div>
                        </div>

                        <div class="row gy-3">
                            <div class="col-lg-12 col-md-12 mb-30">
                                <div class="card">
                                    <div class="card-body">

                                        <form action="{{ route('purchase.update', $purchase->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')

                                            <div class="row mb-3 g-3 mt-4">
                                                <div class="col-xl-3 col-sm-6 mt-3">
                                                    <label><i class="bi bi-calendar-date text-primary me-1"></i>
                                                        Current Date</label>
                                                    <input name="purchase_date" type="date"
                                                        value="{{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d') : '' }}"
                                                        class="form-control">
                                                </div>

                                                <div class="col-xl-3 col-sm-6 mt-3">
                                                    <label><i class="bi bi-receipt text-primary me-1"></i>
                                                        Companies/Vendors</label>
                                                    <select name="vendor_id" class="form-control">
                                                        <option disabled>Select One</option>
                                                        @foreach ($Vendor as $item)
                                                        <option value="{{ $item->id }}"
                                                            {{ $item->id == $purchase->vendor_id ? 'selected' : '' }}>
                                                            {{ $item->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-xl-3 col-sm-6 mt-3">
                                                    <label><i class="bi bi-file-earmark-text text-primary me-1"></i>
                                                        Company Inv #</label>
                                                    <input name="invoice_no" type="text"
                                                        value="{{ $purchase->invoice_no }}" class="form-control">
                                                </div>

                                                <div class="col-xl-3 col-sm-6 mt-3">
                                                    <label><i class="bi bi-building text-primary me-1"></i>
                                                        Warehouse</label>
                                                    <select name="warehouse_id" class="form-control">
                                                        <option disabled>Select One</option>
                                                        @foreach ($Warehouse as $item)
                                                        <option value="{{ $item->id }}"
                                                            {{ $item->id == $purchase->warehouse_id ? 'selected' : '' }}>
                                                            {{ $item->warehouse_name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-xl-6 col-sm-6 mt-3">
                                                    <label><i class="bi bi-card-text text-primary me-1"></i>
                                                        Job No & Description</label>
                                                    <input name="note" type="text" value="{{ $purchase->note }}"
                                                        class="form-control">
                                                </div>
                                            </div>

                                            <!-- Items Table -->
                                            <div style="max-height: 300px; overflow-y: scroll;">
                                                <table class="table mt-3 table-bordered">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th>Product</th>
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
                                                        @foreach($purchase->items as $item)
                                                        <tr>
                                                            <!-- Product -->
                                                            <td>
                                                                <input type="hidden" name="product_id[]" value="{{ $item->product_id }}">
                                                                <input type="text" class="form-control"
                                                                    value="{{ $item->product->item_name ?? 'NULL' }}" readonly>
                                                            </td>

                                                            <!-- Item Code -->
                                                            <td>
                                                                <input type="text" class="form-control"
                                                                    value="{{ $item->product->item_code ?? 'NULL' }}" readonly>
                                                            </td>

                                                            <!-- Brand -->
                                                            <td>
                                                                <input type="text" class="form-control"
                                                                    value="{{ $item->product->brand->name ?? 'NULL' }}" readonly>
                                                            </td>

                                                            <!-- Unit -->
                                                            <td>
                                                                <input type="text" name="unit[]" class="form-control"
                                                                    value="{{ $item->unit ?? 'NULL' }}">
                                                            </td>

                                                            <!-- Price -->
                                                            <td>
                                                                <input type="number" step="0.01" name="price[]" class="form-control price"
                                                                    value="{{ $item->price ?? 0 }}">
                                                            </td>

                                                            <!-- Discount -->
                                                            <td>
                                                                <input type="number" step="0.01" name="price[]" class="form-control price"
                                                                    value="{{ $item->price ?? 0 }}">
                                                            </td>

                                                            <!-- Qty -->
                                                            <td>
                                                                <input type="number" name="qty[]" class="form-control quantity"
                                                                    value="{{ $item->qty ?? 0 }}">
                                                            </td>


                                                            <!-- Total -->
                                                            <td>
                                                                <input type="text" name="line_total[]" class="form-control row-total"
                                                                    value="{{ $item->line_total ?? 0 }}" readonly>
                                                            </td>


                                                            <!-- Action -->
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-danger remove-row">X</button>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>

                                            </div>

                                            <div class="row g-3 mt-3">
                                                <div class="col-md-3">
                                                    <label>Subtotal</label>
                                                    <input type="text" class="form-control" id="subtotal"
                                                        value="{{ $purchase->subtotal }}" name="subtotal" readonly>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Discount</label>
                                                    <input type="number" step="0.01" class="form-control"
                                                        name="discount" id="overallDiscount" value="{{ $purchase->discount }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Extra Cost</label>
                                                    <input type="number" step="0.01" class="form-control"
                                                        name="extra_cost" id="extraCost" value="{{ $purchase->extra_cost }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Net Amount</label>
                                                    <input type="text" name="net_amount" id="netAmount"
                                                        class="form-control fw-bold"
                                                        value="{{ $purchase->net_amount }}" readonly>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-success w-100 mt-4">Update
                                                Purchase</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
    <div class="container py-4">
        <div class="alert alert-danger">You do not have permission to edit Purchases.</div>
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
        html: {
            !!json_encode(implode('<br>', $errors - > all())) !!
        },
        confirmButtonColor: '#d33',
    });
</script>
@endif
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

                const paid = num($('#paidAmount').val());
                const due = net - paid;
                $('#dueAmount').val(due.toFixed(2));
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
            <td class="item_code border"><input type="text" name="item_code[]" class="form-control" readonly></td>
            <td class="uom border"><input type="text" name="uom[]" class="form-control" readonly></td>
            <td class="unit border"><input type="text" name="unit[]" class="form-control" readonly></td>
            <td><input type="number" step="0.01" name="price[]" class="form-control price" value="1"></td>
            <td><input type="number" step="0.01" name="item_disc[]" class="form-control item_disc" value=""></td>
            <td class="qty"><input type="number" name="qty[]" class="form-control quantity" value="" min="1"></td>
            <td class="total border"><input type="text" name="total[]" class="form-control row-total" readonly></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-row">X</button></td>
        </tr>`;
                $('#purchaseItems').append(newRow);
            }


            // Edit form me bhi ek extra blank row ho, taake user new product search kare
            if ($("#purchaseItems tr").length > 0) {
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




    });
</script>