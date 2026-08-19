@extends('admin_panel.layout.app')

@section('css')
<style>
    :root {
        --coa-navy: #1e3a5f;
        --coa-navy-dark: #0f1f38;
        --coa-navy-light: #2c5282;
        --coa-gold: #c8973a;
        --coa-emerald: #0d9f6e;
        --coa-border: #e2e8f0;
    }

    .sr-approve-wrapper {
        padding: 6px 0 20px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* 1. Header Bar */
    .sr-header-bar {
        background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 60%, var(--coa-navy-light) 100%);
        border-radius: 10px;
        padding: 13px 18px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        box-shadow: 0 4px 14px rgba(15, 31, 56, 0.12);
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .sr-header-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        color: var(--coa-gold);
        border: 1px solid rgba(200, 151, 58, 0.3);
        flex-shrink: 0;
    }

    .sr-header-title {
        font-size: 16.5px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        line-height: 1.2;
    }

    .sr-header-sub {
        font-size: 11.5px;
        color: rgba(255, 255, 255, 0.85);
        margin-top: 2px;
    }

    /* 2. Info Cards Grid */
    .sr-info-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-bottom: 14px;
    }

    @media (max-width: 992px) {
        .sr-info-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .sr-info-grid {
            grid-template-columns: 1fr;
        }
    }

    .sr-info-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 10px 13px;
        border: 1px solid var(--coa-border);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .sr-info-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
    }

    .sr-info-val {
        font-size: 13px;
        font-weight: 700;
        color: var(--coa-navy);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 175px;
    }

    .sr-info-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* 3. Compact Approval Table */
    .table-approve-clean {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .table-approve-clean thead th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-weight: 700;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        padding: 9px 8px !important;
        vertical-align: middle !important;
        border: 1px solid #1e3a5f !important;
        white-space: nowrap;
    }

    .table-approve-clean tbody td {
        padding: 7px 6px !important;
        vertical-align: middle !important;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        font-size: 12px;
    }

    .table-approve-clean tbody tr:nth-child(even) td {
        background-color: #f8fafc;
    }

    .table-approve-clean tbody tr:hover td {
        background-color: #f1f5f9;
    }

    /* Form Fields Inside Table */
    .table-approve-clean .form-control {
        height: 34px !important;
        min-height: 34px !important;
        padding: 4px 6px !important;
        font-size: 12px !important;
        border-radius: 5px !important;
        box-sizing: border-box !important;
        width: 100% !important;
        border: 1px solid #cbd5e1;
    }

    .table-approve-clean .form-control:focus {
        border-color: #0284c7 !important;
        box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15) !important;
        outline: none !important;
    }

    .warehouse-select {
        background-color: #ffffff !important;
        font-weight: 500 !important;
    }

    .warehouse-stock {
        background-color: #f1f5f9 !important;
        font-weight: 700 !important;
        color: #334155 !important;
        cursor: not-allowed;
    }

    .approved-qty {
        font-weight: 700 !important;
        border-color: #94a3b8 !important;
    }

    .unit-price {
        background-color: #fffbeb !important;
        border-color: #fde68a !important;
        font-weight: 700 !important;
        color: #b45309 !important;
    }

    .delivery-charges {
        background-color: #fff7ed !important;
        border-color: #fed7aa !important;
        font-weight: 700 !important;
        color: #c2410c !important;
    }

    .destination-warehouse-select {
        background-color: #f0fdf4 !important;
        border-color: #bbf7d0 !important;
        font-weight: 600 !important;
        color: #15803d !important;
    }

    .prod-title {
        font-size: 12px;
        font-weight: 700;
        color: #0f1f38;
        line-height: 1.25;
        margin-bottom: 2px;
        display: block;
    }

    .prod-code {
        font-size: 10.5px;
        color: #64748b;
        font-family: monospace;
    }

    .qty-badge {
        background: #e0f2fe;
        color: #0369a1;
        font-weight: 700;
        font-size: 12px;
        padding: 4px 7px;
        border-radius: 4px;
        border: 1px solid #bae6fd;
        display: inline-block;
    }

    .amount-display {
        font-family: monospace;
        font-size: 13px;
        font-weight: 700;
        color: #047857;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <div class="sr-approve-wrapper">
        <div class="container-fluid px-2">

            <!-- 1. Corporate Header Bar -->
            <div class="sr-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="sr-header-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h4 class="sr-header-title">Approve Stock Request &mdash; #{{ $stockRequest->id }}</h4>
                        <div class="sr-header-sub">
                            <span><i class="fas fa-random mr-1" style="color: var(--coa-gold);"></i> Inter-branch stock transfer & fulfillment &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('inter_branch_stock_requests.index') }}" class="btn btn-sm btn-light font-weight-bold text-muted border">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Requests
                    </a>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <div class="font-weight-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Please correct the following errors:</div>
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li style="font-size: 12px;">{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- 2. Request Details KPI Cards -->
            <div class="sr-info-grid">
                <div class="sr-info-card">
                    <div>
                        <div class="sr-info-label">Request From (Destination)</div>
                        <div class="sr-info-val text-primary" title="{{ $stockRequest->fromBranch->name ?? 'Branch #' . $stockRequest->from_branch_id }}">
                            <i class="fas fa-building mr-1"></i>{{ $stockRequest->fromBranch->name ?? 'Branch #' . $stockRequest->from_branch_id }}
                        </div>
                    </div>
                    <div class="sr-info-icon" style="background:#e0f2fe; color:#0284c7;">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                </div>

                <div class="sr-info-card">
                    <div>
                        <div class="sr-info-label">Fulfilling Branch (Source)</div>
                        <div class="sr-info-val text-dark" title="{{ $stockRequest->toBranch->name ?? 'Branch #' . $stockRequest->to_branch_id }}">
                            <i class="fas fa-store mr-1"></i>{{ $stockRequest->toBranch->name ?? 'Branch #' . $stockRequest->to_branch_id }}
                        </div>
                    </div>
                    <div class="sr-info-icon" style="background:#fef3c7; color:#d97706;">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                </div>

                <div class="sr-info-card">
                    <div>
                        <div class="sr-info-label">Requested Date</div>
                        <div class="sr-info-val" style="font-size: 12.5px;">
                            <i class="far fa-calendar-alt text-muted mr-1"></i>{{ $stockRequest->created_at->format('d-M-Y H:i') }}
                        </div>
                    </div>
                    <div class="sr-info-icon" style="background:#f1f5f9; color:#64748b;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>

                <div class="sr-info-card">
                    <div>
                        <div class="sr-info-label">Remarks / Notes</div>
                        <div class="sr-info-val text-muted" style="font-size: 12px;" title="{{ $stockRequest->remarks ?? 'None' }}">
                            {{ $stockRequest->remarks ?? 'None' }}
                        </div>
                    </div>
                    <div class="sr-info-icon" style="background:#f1f5f9; color:#64748b;">
                        <i class="fas fa-comment-alt"></i>
                    </div>
                </div>
            </div>

            <!-- 3. Main Form & Items Table -->
            <form id="approveForm" action="{{ route('inter_branch_stock_requests.approve', $stockRequest) }}" method="POST">
                @csrf

                <div class="card shadow-sm border-0 mb-3" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                    <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between">
                        <div class="font-weight-bold" style="font-size: 13px; color: var(--coa-navy);">
                            <i class="fas fa-boxes mr-1" style="color: var(--coa-gold);"></i> Requested Stock Items ({{ count($items) }})
                        </div>
                        <small class="text-muted">Fill source warehouse, approve quantity, unit price & destination warehouse</small>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-approve-clean mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 22%;">Product Details</th>
                                        <th style="width: 6%;" class="text-center">Req Qty</th>
                                        <th style="width: 15%;">Source Warehouse</th>
                                        <th style="width: 8%;" class="text-center">Avail. Stock</th>
                                        <th style="width: 8%;" class="text-center">Approve Qty</th>
                                        <th style="width: 10%;" class="text-end">Unit Price</th>
                                        <th style="width: 10%;" class="text-end">Del. Charges</th>
                                        <th style="width: 13%;">Dest. Warehouse</th>
                                        <th style="width: 8%;" class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        <tr>
                                            <td>
                                                <span class="prod-title">{{ $item->product->item_name ?? 'Product #' . $item->product_id }}</span>
                                                <span class="prod-code">Code: {{ $item->product->item_code ?? 'N/A' }}</span>
                                                <input type="hidden" name="item_id[]" value="{{ $item->id }}">
                                                <input type="hidden" name="requested_branch" value="{{ $stockRequest->from_branch_id }}">
                                            </td>

                                            <td class="text-center">
                                                <span class="qty-badge">{{ $item->requested_qty }}</span>
                                            </td>

                                            <td>
                                                <select name="warehouse_id[]"
                                                    class="form-control warehouse-select" required
                                                    data-product-id="{{ $item->product_id }}"
                                                    data-item-index="{{ $loop->index }}">
                                                    <option value="">-- Source --</option>
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
                                                <input type="number" name="approved_qty[]"
                                                    class="form-control approved-qty text-center" required
                                                    min="0" value="" placeholder="0"
                                                    data-item-id="{{ $item->id }}">
                                            </td>

                                            <td>
                                                <input type="number" name="unit_price[]"
                                                    class="form-control unit-price text-end"
                                                    step="0.01" min="0" value="{{ number_format($item->defaultUnitPrice ?? ($item->product->wholesale_price ?: $item->product->price ?: 0), 2, '.', '') }}">
                                            </td>

                                            <td>
                                                <input type="number" name="delivery_charges[]"
                                                    class="form-control delivery-charges text-end"
                                                    min="0" step="0.01" value="" placeholder="0.00">
                                            </td>

                                            <td>
                                                <select name="destination_warehouse_id[]"
                                                    class="form-control destination-warehouse-select" required>
                                                    <option value="">-- Destination --</option>
                                                    @foreach ($destinationWarehouses as $warehouse)
                                                        <option value="{{ $warehouse->id }}">{{ $warehouse->warehouse_name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td class="text-end">
                                                <strong class="total-amount amount-display">0.00</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: #f8fafc; border-top: 2px solid #cbd5e1;">
                                        <td colspan="8" class="text-end font-weight-bold py-2" style="padding-right: 14px; color: #475569; font-size: 12px;">Items Total:</td>
                                        <td class="text-end py-2" style="background-color: #f0fdf4;">
                                            <strong id="items-total" class="text-success font-monospace" style="font-size: 13px;">0.00</strong>
                                        </td>
                                    </tr>
                                    <tr style="background-color: #f8fafc;">
                                        <td colspan="8" class="text-end font-weight-bold py-2" style="padding-right: 14px; color: #475569; font-size: 12px;">Delivery Charges:</td>
                                        <td class="text-end py-2" style="background-color: #fff7ed;">
                                            <strong id="charges-total" class="font-monospace" style="font-size: 13px; color: #ea580c;">0.00</strong>
                                        </td>
                                    </tr>
                                    <tr style="background-color: #ecfdf5; border-top: 2px solid #10b981; border-bottom: 2px solid #10b981;">
                                        <td colspan="8" class="text-end font-weight-bold py-2" style="color: #065f46; font-size: 13px; padding-right: 14px;">GRAND TOTAL:</td>
                                        <td class="text-end py-2" style="background-color: #d1fae5;">
                                            <strong id="grand-total" class="font-monospace" style="font-size: 15px; font-weight: 800; color: #065f46;">0.00</strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Action Bar inside Card Footer -->
                    <div class="card-footer bg-white border-top p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-sm btn-success font-weight-bold px-4 py-2" id="submitBtn" style="border-radius: 6px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);">
                                <i class="fas fa-check-circle mr-1"></i> Approve Request
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold px-4 py-2" data-toggle="modal" data-target="#rejectModal" data-bs-toggle="modal" data-bs-target="#rejectModal" style="border-radius: 6px;">
                                <i class="fas fa-times-circle mr-1"></i> Reject Request
                            </button>
                        </div>
                        <a href="{{ route('inter_branch_stock_requests.index') }}" class="btn btn-sm btn-light font-weight-bold text-muted border px-3 py-2" style="border-radius: 6px;">
                            Cancel & Back
                        </a>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 9px; overflow: hidden; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title font-weight-bold" id="rejectModalLabel" style="font-size: 15px;">
                    <i class="fas fa-times-circle mr-1"></i> Reject Stock Request #{{ $stockRequest->id }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('inter_branch_stock_requests.reject', $stockRequest) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold mb-1" style="font-size: 12px; color: #334155;">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required
                            placeholder="Please explain why you are rejecting this stock request..." style="border-radius: 6px; font-size: 12.5px; border-color: #cbd5e1;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-3" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger font-weight-bold px-3">Confirm Rejection</button>
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
            const product = $(this).find('.prod-title').text().trim();

            if (!warehouse) {
                isValid = false;
                errorMsg.push(`Row ${index + 1} (${product}): Please select source warehouse`);
            }

            if (!destWarehouse) {
                isValid = false;
                errorMsg.push(`Row ${index + 1} (${product}): Please select destination warehouse`);
            }

            if (!qty || parseInt(qty) <= 0) {
                isValid = false;
                errorMsg.push(`Row ${index + 1} (${product}): Please enter approval quantity (must be > 0)`);
            }
        });

        if (!isValid) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required Fields Missing',
                    html: errorMsg.join('<br>')
                });
            } else {
                alert('Please fill all required fields:\n\n' + errorMsg.join('\n'));
            }
            return false;
        }

        // Check total items quantity
        let totalApprovedQty = 0;
        $('.table tbody tr').each(function() {
            const qty = parseInt($(this).find('.approved-qty').val()) || 0;
            totalApprovedQty += qty;
        });

        if (totalApprovedQty <= 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Quantity',
                    text: 'Please enter approval quantity greater than 0 for at least one item.'
                });
            } else {
                alert('Please enter approval quantity greater than 0 for at least one item');
            }
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
