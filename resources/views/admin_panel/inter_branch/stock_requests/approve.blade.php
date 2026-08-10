@extends('admin_panel.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">✓ Approve Stock Request - Stock Request #{{ $stockRequest->id }}</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Validation Errors:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Request Summary -->
                <div class="alert alert-info">
                    <strong>Request from:</strong>
                    {{ $stockRequest->fromBranch->name ?? 'Branch #' . $stockRequest->from_branch_id }} <br>
                    <strong>Requested on:</strong> {{ $stockRequest->created_at->format('M d, Y H:i') }} <br>
                    <strong>Remarks:</strong> {{ $stockRequest->remarks ?? 'None' }}
                </div>

                <form id="approveForm" action="{{ route('inter_branch_stock_requests.approve', $stockRequest) }}"
                    method="POST">
                    @csrf

                    <!-- Approval Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%">Product</th>
                                    <th width="8%">Requested Qty</th>
                                    <th width="12%">Select Warehouse</th>
                                    <th width="5%">Available Stock</th>
                                    <th width="9%">Approve Qty</th>
                                    <th width="7%">Unit Price</th>
                                    <th width="8%">Delivery Charges</th>
                                    <th width="12%">Destination branch Warehouse</th>
                                    <th width="10%">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product->item_name ?? 'Product #' . $item->product_id }}</strong>
                                            <br>
                                            <small class="text-muted">(Code:
                                                {{ $item->product->item_code ?? 'N/A' }})</small>
                                            <input type="hidden" name="item_id[]" value="{{ $item->id }}">
                                            <input type="hidden" name="requested_branch"
                                                value="{{ $stockRequest->from_branch_id }}">
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $item->requested_qty }}</span>
                                        </td>

                                        <td>
                                           
 <select name="warehouse_id[]"
                                                class="form-control form-control-sm warehouse-select" required
                                                data-product-id="{{ $item->product_id }}"
                                                data-item-index="{{ $loop->index }}">
                                                <option value="">-- Select Destination --</option>
                                                @foreach ($sourceWarehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}
                                                    </option>
                                                @endforeach




                                        </td>

                                        <td>
                                            <input type="number"
                                                class="form-control form-control-sm warehouse-stock text-center" readonly
                                                value="0"
                                                style="background-color: #e8f4f8; border-color: #80deea; font-weight: 600;">
                                        </td>

                                        <td>
                                            <input type="text" name="approved_qty[]"
                                                class="form-control form-control-sm approved-qty text-center" required
                                                min="0" value="" placeholder="Enter Qty"
                                                data-item-id="{{ $item->id }}">
                                        </td>

                                        <td>
                                            <input type="number" name="unit_price[]"
                                                class="form-control form-control-sm unit-price text-end"
                                                step="0.01" min="0" value="{{ number_format($item->product->wholesale_price ?: $item->product->price ?: 0, 2, '.', '') }}"
                                                style="background-color: #fff3cd; border-color: #ffc107; font-weight: 600;">
                                        </td>

                                        <td class="text-end">
                                            <input type="text" name="delivery_charges[]"
                                                class="form-control form-control-sm delivery-charges text-end"
                                                min="0" step="0.01" value="" placeholder="Enter Charges"
                                                style="background-color: #f0f0f0; border-color: #ff9800; font-weight: 600;">


                                        </td>



                                        <td>
                                             <select name="destination_warehouse_id[]"
                                                class="form-control form-control-sm destination-warehouse-select" required
                                                style="background-color: #e8f9f5; border-color: #4caf50; font-weight: 500;">
                                                <option value="">-- Select Warehouse --</option>
                                                @foreach ($destinationWarehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}
                                                    </option>
                                                @endforeach
                                            </select>



                                                                                       </select>
                                        </td>
                                        <td>
                                            <strong class="total-amount"
                                                style="font-size: 1.15em; color: #28a745;">0.00</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f9f9f9; border-top: 2px solid #dee2e6;">
                                    <td colspan="8" class="text-end" style="font-weight: 600; padding-right: 15px;">Items
                                        Total:</td>
                                    <td class="text-end"
                                        style="background-color: #f0f8f6; border-right: 2px solid #dee2e6;">
                                        <strong id="items-total" style="font-size: 1.15em; color: #28a745;">0.00</strong>
                                    </td>
                                </tr>
                                <tr style="background-color: #f9f9f9;">
                                    <td colspan="8" class="text-end" style="font-weight: 600; padding-right: 15px;">
                                        Delivery Charges:</td>
                                    <td class="text-end"
                                        style="background-color: #fff8f0; border-right: 2px solid #dee2e6;">
                                        <strong id="charges-total" style="font-size: 1.15em; color: #ff9800;">0.00</strong>
                                    </td>
                                </tr>
                                <tr
                                    style="font-weight: bold; background-color: #e8f5e9; border-top: 2px solid #4caf50; border-bottom: 2px solid #4caf50;">
                                    <td colspan="8" class="text-end"
                                        style="color: #1b5e20; font-weight: 600; padding-right: 15px;">GRAND TOTAL:</td>
                                    <td class="text-end"
                                        style="background-color: #dcefd9; border-right: 2px solid #4caf50;">
                                        <strong id="grand-total" style="font-size: 1.25em; color: #1b5e20;">0.00</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="fas fa-check-circle"></i> Approve Request
                        </button>
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal" data-bs-toggle="modal"
                            data-bs-target="#rejectModal">
                            <i class="fas fa-times-circle"></i> Reject Request
                        </button>
                        <a href="{{ route('inter_branch_stock_requests.index') }}" class="btn btn-secondary">
                            Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Request</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('inter_branch_stock_requests.reject', $stockRequest) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason</label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required
                                placeholder="Why are you rejecting this request?"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Calculate total amount for a specific row
            function calculateRowTotal($row) {
                const qty = parseInt($row.find('.approved-qty').val()) || 0;
                const price = parseFloat($row.find('.unit-price').val()) || 0;
                const total = qty * price;
                $row.find('.total-amount').text(total.toFixed(2));
            }

            // Calculate items total and grand total with charges
            function calculateGrandTotal() {
                let itemsTotal = 0;
                let chargesTotal = 0;

                // Calculate items total
                $('.table tbody tr').each(function() {
                    const qty = parseInt($(this).find('.approved-qty').val()) || 0;
                    const price = parseFloat($(this).find('.unit-price').val()) || 0;
                    itemsTotal += (qty * price);

                    // Calculate delivery charges total
                    const charges = parseFloat($(this).find('.delivery-charges').val()) || 0;
                    chargesTotal += charges;
                });

                const grandTotal = itemsTotal + chargesTotal;

                // Update display
                $('#items-total').text(itemsTotal.toFixed(2));
                $('#charges-total').text(chargesTotal.toFixed(2));
                $('#grand-total').text(grandTotal.toFixed(2));
            }

            // Validate form before submission
            $('#submitBtn').on('click', function(e) {
                e.preventDefault();

                let isValid = true;
                let errorMsg = [];

                // Check each row
                $('.table tbody tr').each(function(index) {
                    const warehouse = $(this).find('.warehouse-select').val();
                    const destWarehouse = $(this).find('.destination-warehouse-select').val();
                    const qty = $(this).find('.approved-qty').val();
                    const product = $(this).find('strong').text().trim();

                    if (!warehouse) {
                        isValid = false;
                        errorMsg.push(
                            `Row ${index + 1} (${product}): Please select source warehouse`);
                    }

                    if (!destWarehouse) {
                        isValid = false;
                        errorMsg.push(
                            `Row ${index + 1} (${product}): Please select destination warehouse`
                            );
                    }

                    if (!qty || parseInt(qty) <= 0) {
                        isValid = false;
                        errorMsg.push(
                            `Row ${index + 1} (${product}): Please enter approval quantity (must be greater than 0)`
                            );
                    }
                });

                if (!isValid) {
                    alert('Please fill all required fields:\n\n' + errorMsg.join('\n'));
                    return false;
                }

                // Check total items quantity
                let totalApprovedQty = 0;
                $('.table tbody tr').each(function() {
                    const qty = parseInt($(this).find('.approved-qty').val()) || 0;
                    totalApprovedQty += qty;
                });

                if (totalApprovedQty <= 0) {
                    alert('Please enter approval quantity greater than 0 for at least one item');
                    return false;
                }

                // If valid, submit the form
                $('#approveForm').submit();
            });

            // When warehouse is selected
            $(document).on('change', '.warehouse-select', function() {
                const $row = $(this).closest('tr');
                const warehouseId = $(this).val();
                const productId = $(this).data('product-id');
                const $stockField = $row.find('.warehouse-stock');
                const $priceField = $row.find('.unit-price');
                const $approveQtyField = $row.find('.approved-qty');

                // Clear approve qty when warehouse is deselected
                if (!warehouseId) {
                    $stockField.val('0');
                    $priceField.val('0.00');
                    $approveQtyField.val('').attr('max', '0').attr('placeholder', '0');
                    calculateRowTotal($row);
                    calculateGrandTotal();
                    return;
                }

                // Fetch stock and price from server
                const baseUrl = "{{ url('inter-branch/stock-requests/warehouse-stock') }}";
                const url = `${baseUrl}/${warehouseId}/${productId}`;

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        const quantity = parseInt(response.quantity) || 0;
                        const price = parseFloat(response.price) || 0;

                        // Update warehouse stock display
                        $stockField.val(quantity);

                        // Update unit price
                        $priceField.val(price.toFixed(2));

                        // Set max limit for approve qty to warehouse stock
                        $approveQtyField.attr('max', quantity).attr('placeholder', '0').val('');

                        // Clear approve qty and totals for user to enter new value
                        calculateRowTotal($row);
                        calculateGrandTotal();
                        updateTransferDestinations(); // ✅ Update destination display
                    },
                    error: function(xhr, status, error) {
                        $stockField.val('0');
                        $priceField.val('0.00');
                        $approveQtyField.val('').attr('max', '0');
                        console.error('Error loading warehouse stock:', error);
                        calculateRowTotal($row);
                        calculateGrandTotal();
                        updateTransferDestinations(); // ✅ Update destination display
                    }
                });
            });

            // When user enters approve qty
            $(document).on('change keyup', '.approved-qty', function() {
                const $row = $(this).closest('tr');
                const enteredQty = parseInt($(this).val()) || 0;
                const maxQty = parseInt($(this).attr('max')) || 0;

                // Validate qty doesn't exceed warehouse stock
                if (enteredQty > maxQty && maxQty > 0) {
                    $(this).val(maxQty);
                }

                calculateRowTotal($row);
                calculateGrandTotal();
                updateTransferDestinations(); // ✅ Update destination display
            });

            // When user enters/updates unit price
            $(document).on('change keyup', '.unit-price', function() {
                const $row = $(this).closest('tr');
                calculateRowTotal($row);
                calculateGrandTotal();
            });

            // When user enters delivery charges
            $(document).on('change keyup', '.delivery-charges', function() {
                calculateGrandTotal();
            });

            // ✅ Initialize destination warehouses
            const destinationWarehouses = {!! json_encode($destinationWarehouses ?? []) !!};

            // ✅ Initialize warehouse cards
            function initializeDestinationWarehouses() {
                if (destinationWarehouses.length === 0) {
                    return;
                }

                const container = $('#destinationWarehouses');
                container.empty();

                destinationWarehouses.forEach(function(warehouse) {
                    const warehouseId = warehouse.id;
                    const warehoseName = warehouse.warehouse_name;

                    const card = `
                <div class="col-md-6 col-lg-4">
                    <div class="card border-info h-100" style="background-color: #f0f7ff;">
                        <div class="card-header bg-info text-white" style="padding: 10px 15px;">
                            <h6 class="mb-0" style="font-size: 0.95rem;">
                                <i class="fas fa-warehouse"></i> ${warehoseName}
                            </h6>
                        </div>
                        <div class="card-body" style="padding: 12px 15px;">
                            <div class="transfer-list-${warehouseId}" style="max-height: 250px; overflow-y: auto;">
                                <!-- Products will be listed here -->
                            </div>
                            <div class="no-products-${warehouseId}" class="text-muted small" style="padding: 20px; text-align: center;">
                                <i class="fas fa-inbox"></i> No products assigned
                            </div>
                            <div class="warehouse-total-${warehouseId}" style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #ccc; display: none;">
                                <small class="fw-bold">Total Units: <span class="total-units-${warehouseId}">0</span></small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                    container.append(card);
                });

                $('#noTransferMsg').hide();
                updateTransferDestinations();
            }

            // ✅ Update transfer destinations based on approved quantities
            function updateTransferDestinations() {
                let hasTransfers = false;

                destinationWarehouses.forEach(function(warehouse) {
                    const warehouseId = warehouse.id;
                    const $productList = $(`.transfer-list-${warehouseId}`);
                    const $noProducts = $(`.no-products-${warehouseId}`);
                    const $warehouseTotal = $(`.warehouse-total-${warehouseId}`);

                    let totalUnits = 0;
                    let productsHtml = '';

                    // Check each row for products going to this warehouse
                    $('.table tbody tr').each(function() {
                        const selectedWarehouse = $(this).find('.warehouse-select').val();
                        const productName = $(this).find('strong').text().trim();
                        const approvedQty = parseInt($(this).find('.approved-qty').val()) || 0;
                        const productCode = $(this).find('small.text-muted').text().trim();

                        if (selectedWarehouse == warehouseId && approvedQty > 0) {
                            hasTransfers = true;
                            totalUnits += approvedQty;

                            productsHtml += `
                        <div style="margin-bottom: 8px; padding: 8px; background: white; border-left: 3px solid #0d6efd; border-radius: 3px;">
                            <small class="d-block fw-bold">${productName}</small>
                            <small class="text-muted d-block">${productCode}</small>
                            <small class="badge bg-success">Qty: ${approvedQty}</small>
                        </div>
                    `;
                        }
                    });

                    if (productsHtml) {
                        $productList.html(productsHtml);
                        $noProducts.hide();
                        $warehouseTotal.show();
                        $(`.total-units-${warehouseId}`).text(totalUnits);
                    } else {
                        $productList.html('');
                        $noProducts.show();
                        $warehouseTotal.hide();
                    }
                });

                if (hasTransfers) {
                    $('#noTransferMsg').hide();
                } else {
                    $('#noTransferMsg').show();
                }
            }

            // Initial calculation and destination setup
            calculateGrandTotal();
            initializeDestinationWarehouses();
        });
    </script>
@endsection
