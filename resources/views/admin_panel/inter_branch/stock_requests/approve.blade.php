@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Premium Table & Field Fixes for Stock Approval */
    .table-approve-custom {
        min-width: 1320px;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    
    .table-approve-custom th {
        background: #1e293b !important;
        color: #f8fafc !important;
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 14px 12px !important;
        vertical-align: middle !important;
        white-space: nowrap;
        border-bottom: 2px solid #0f172a !important;
    }
    
    .table-approve-custom td {
        padding: 12px 10px !important;
        vertical-align: middle !important;
        border-color: #e2e8f0;
        background-color: #ffffff;
    }

    /* Fixed Select & Input Field Heights, Widths, and Padding */
    .table-approve-custom select.form-control,
    .table-approve-custom input.form-control {
        height: 40px !important;
        min-height: 40px !important;
        line-height: 1.4 !important;
        padding: 6px 10px !important;
        font-size: 0.88rem !important;
        border-radius: 6px !important;
        box-sizing: border-box !important;
        width: 100% !important;
    }

    .table-approve-custom select.form-control:focus,
    .table-approve-custom input.form-control:focus {
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2) !important;
        border-color: #2563eb !important;
        outline: none !important;
    }

    .warehouse-select {
        background-color: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        color: #0f172a !important;
        font-weight: 500 !important;
    }

    .warehouse-stock {
        background-color: #f1f5f9 !important;
        border: 1px solid #cbd5e1 !important;
        font-weight: 700 !important;
        color: #0f172a !important;
    }

    .approved-qty {
        background-color: #ffffff !important;
        border: 1.5px solid #94a3b8 !important;
        font-weight: 700 !important;
        color: #0f172a !important;
    }

    .unit-price {
        background-color: #fffbeb !important;
        border: 1.5px solid #fde68a !important;
        font-weight: 700 !important;
        color: #b45309 !important;
    }

    .delivery-charges {
        background-color: #fff7ed !important;
        border: 1.5px solid #ffedd5 !important;
        font-weight: 700 !important;
        color: #c2410c !important;
    }

    .destination-warehouse-select {
        background-color: #f0fdf4 !important;
        border: 1.5px solid #bbf7d0 !important;
        font-weight: 600 !important;
        color: #15803d !important;
    }

    /* Request Summary Card Styling */
    .request-summary-card {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #ffffff;
        border-radius: 10px;
        padding: 18px 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .request-summary-card .summary-label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        opacity: 0.9;
        margin-bottom: 3px;
        font-weight: 600;
    }

    .request-summary-card .summary-value {
        font-size: 1.1rem;
        font-weight: 700;
    }
</style>

<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-check-circle me-2"></i> Approve Stock Request - Stock Request #{{ $stockRequest->id }}</h5>
        </div>
        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    <strong>Validation Errors:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Request Summary Card -->
            <div class="request-summary-card mb-4">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="summary-label"><i class="fas fa-store me-1"></i> Request From</div>
                        <div class="summary-value">{{ $stockRequest->fromBranch->name ?? 'Branch #' . $stockRequest->from_branch_id }}</div>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="summary-label"><i class="fas fa-calendar-alt me-1"></i> Requested On</div>
                        <div class="summary-value">{{ $stockRequest->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-label"><i class="fas fa-comment-alt me-1"></i> Remarks</div>
                        <div class="summary-value">{{ $stockRequest->remarks ?? 'None' }}</div>
                    </div>
                </div>
            </div>

            <form id="approveForm" action="{{ route('inter_branch_stock_requests.approve', $stockRequest) }}" method="POST">
                @csrf

                <!-- Approval Table -->
                <div class="table-responsive mb-4 shadow-sm rounded" style="border: 1px solid #cbd5e1;">
                    <table class="table table-bordered align-middle table-approve-custom mb-0">
                        <thead>
                            <tr>
                                <th style="min-width: 230px;">Product Details</th>
                                <th style="min-width: 90px;" class="text-center">Req Qty</th>
                                <th style="min-width: 190px;">Select Source Warehouse</th>
                                <th style="min-width: 120px;" class="text-center">Available Stock</th>
                                <th style="min-width: 120px;" class="text-center">Approve Qty</th>
                                <th style="min-width: 135px;" class="text-end">Unit Price</th>
                                <th style="min-width: 145px;" class="text-end">Delivery Charges</th>
                                <th style="min-width: 200px;">Destination Warehouse</th>
                                <th style="min-width: 140px;" class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td>
                                        <strong class="d-block text-dark mb-1" style="font-size: 0.92rem; line-height: 1.3;">{{ $item->product->item_name ?? 'Product #' . $item->product_id }}</strong>
                                        <span class="badge bg-light text-secondary border">(Code: {{ $item->product->item_code ?? 'N/A' }})</span>
                                        <input type="hidden" name="item_id[]" value="{{ $item->id }}">
                                        <input type="hidden" name="requested_branch" value="{{ $stockRequest->from_branch_id }}">
                                    </td>

                                    <td class="text-center">
                                        <span class="badge bg-primary px-3 py-2 fs-6" style="font-weight: 700;">{{ $item->requested_qty }}</span>
                                    </td>

                                    <td>
                                        <select name="warehouse_id[]"
                                            class="form-control warehouse-select" required
                                            data-product-id="{{ $item->product_id }}"
                                            data-item-index="{{ $loop->index }}">
                                            <option value="">-- Select Source --</option>
                                            @foreach ($sourceWarehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input type="number"
                                            class="form-control warehouse-stock text-center" readonly
                                            value="0">
                                    </td>

                                    <td>
                                        <input type="text" name="approved_qty[]"
                                            class="form-control approved-qty text-center" required
                                            min="0" value="" placeholder="Enter Qty"
                                            data-item-id="{{ $item->id }}">
                                    </td>

                                    <td>
                                        <input type="number" name="unit_price[]"
                                            class="form-control unit-price text-end"
                                            step="0.01" min="0" value="{{ number_format($item->defaultUnitPrice ?? ($item->product->wholesale_price ?: $item->product->price ?: 0), 2, '.', '') }}">
                                    </td>

                                    <td>
                                        <input type="text" name="delivery_charges[]"
                                            class="form-control delivery-charges text-end"
                                            min="0" step="0.01" value="" placeholder="Enter Charges">
                                    </td>

                                    <td>
                                        <select name="destination_warehouse_id[]"
                                            class="form-control destination-warehouse-select" required>
                                            <option value="">-- Select Warehouse --</option>
                                            @foreach ($destinationWarehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="text-end">
                                        <strong class="total-amount text-success fs-6" style="font-weight: 700;">0.00</strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8fafc; border-top: 2px solid #cbd5e1;">
                                <td colspan="8" class="text-end fw-bold py-3" style="padding-right: 18px; color: #334155; font-size: 0.95rem;">Items Total:</td>
                                <td class="text-end py-3" style="background-color: #f0fdf4;">
                                    <strong id="items-total" class="text-success fs-6" style="font-weight: 700;">0.00</strong>
                                </td>
                            </tr>
                            <tr style="background-color: #f8fafc;">
                                <td colspan="8" class="text-end fw-bold py-3" style="padding-right: 18px; color: #334155; font-size: 0.95rem;">Delivery Charges:</td>
                                <td class="text-end py-3" style="background-color: #fff7ed;">
                                    <strong id="charges-total" class="fs-6" style="font-weight: 700; color: #ea580c;">0.00</strong>
                                </td>
                            </tr>
                            <tr style="background-color: #ecfdf5; border-top: 2px solid #10b981; border-bottom: 2px solid #10b981;">
                                <td colspan="8" class="text-end fw-bold py-3" style="color: #065f46; font-size: 1.05rem; padding-right: 18px;">GRAND TOTAL:</td>
                                <td class="text-end py-3" style="background-color: #d1fae5;">
                                    <strong id="grand-total" style="font-size: 1.3rem; font-weight: 800; color: #065f46;">0.00</strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success px-4 py-2 fw-bold" id="submitBtn">
                        <i class="fas fa-check-circle me-1"></i> Approve Request
                    </button>
                    <button type="button" class="btn btn-danger px-4 py-2 fw-bold" data-toggle="modal" data-target="#rejectModal" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times-circle me-1"></i> Reject Request
                    </button>
                    <a href="{{ route('inter_branch_stock_requests.index') }}" class="btn btn-secondary px-4 py-2 fw-bold">
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
                <h5 class="modal-title fw-bold">Reject Request</h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('inter_branch_stock_requests.reject', $stockRequest) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rejection Reason</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required
                            placeholder="Why are you rejecting this request?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold">Confirm Rejection</button>
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
                    },
                    error: function(xhr, status, error) {
                        $stockField.val('0');
                        $priceField.val('0.00');
                        $approveQtyField.val('').attr('max', '0');
                        console.error('Error loading warehouse stock:', error);
                        calculateRowTotal($row);
                        calculateGrandTotal();
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

            // Initial calculation
            calculateGrandTotal();
        });
    </script>
@endsection
