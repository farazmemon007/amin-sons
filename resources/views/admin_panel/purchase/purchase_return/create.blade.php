@extends('admin_panel.layout.app')

@section('content')
@can('purchase.return.create')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        --accent-color: #f59e0b;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }

    .main-content { background-color: #f8fafc; min-height: 100vh; padding: 1.5rem 0.75rem; }
    .premium-card { border: none; border-radius: 1.5rem; box-shadow: var(--card-shadow); background: white; overflow: hidden; margin-bottom: 2rem; width: 100%; }
    .card-header-gradient { background: var(--primary-gradient); padding: 1.5rem 2rem; border: none; }
    .card-title-premium { color: white; font-weight: 800; font-size: 1.5rem; margin: 0; display: flex; align-items: center; gap: 0.75rem; }
    
    .section-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.5rem; display: block; }
    .fi { width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.95rem; transition: all 0.2s; background-color: #f8fafc; }
    .fi:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); background-color: white; }
    .fi[readonly] { background-color: #f1f5f9; color: #475569; cursor: not-allowed; }

    .table-premium { border-collapse: separate; border-spacing: 0 0.5rem; width: 100% !important; }
    .table-premium thead th { background: #f1f5f9; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.7rem; padding: 0.75rem 0.5rem; border: none; }
    .table-premium tbody tr { transition: transform 0.2s, box-shadow 0.2s; }
    .table-premium tbody td { padding: 0.75rem 0.5rem; vertical-align: middle; background: white; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }
    .table-premium tbody td:first-child { border-left: 1px solid #f1f5f9; border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem; }
    .table-premium tbody td:last-child { border-right: 1px solid #f1f5f9; border-top-right-radius: 0.75rem; border-bottom-right-radius: 0.75rem; }

    .summary-card { background: #f8fafc; border-radius: 1rem; padding: 1.5rem; border: 1.5px dashed #cbd5e1; }
    .summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
    .summary-label { font-weight: 600; color: #64748b; }
    .summary-value { font-weight: 800; color: #1e293b; font-size: 1.1rem; }
    .summary-total { margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #e2e8f0; }
    .summary-total .summary-value { font-size: 1.5rem; color: #4f46e5; }

    .btn-premium { padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 700; transition: all 0.2s; border: none; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
    .btn-submit { background: var(--primary-gradient); color: white; box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.39); width: 100%; margin-top: 1.5rem; }
    .btn-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(79, 70, 229, 0.23); }
    .btn-submit:disabled { background: #cbd5e1; color: #94a3b8; cursor: not-allowed; box-shadow: none; }
    .fi-table { padding: 0.5rem 0.6rem !important; font-size: 0.85rem !important; }
    
    .validation-badge { font-size: 0.7rem; font-weight: 700; border-radius: 0.25rem; padding: 0.2rem 0.5rem; margin-top: 0.25rem; display: none; }
    .validation-badge.error { background: #fee2e2; color: #ef4444; }
</style>

<div class="main-content">
    <div class="container-fluid px-2">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Create Purchase Return</h4>
                <small class="text-muted">Return products back to vendors and decrease payables</small>
            </div>
            <a href="{{ route('Purchase.home') }}" class="btn btn-outline-secondary px-4 fw-bold">
                <i class="fa fa-arrow-left me-2"></i> Back to Purchases
            </a>
        </div>

        <form action="{{ route('purchase.return.store') }}" method="POST" id="returnForm">
            @csrf
            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
            <input type="hidden" name="vendor_id" value="{{ $purchase->vendor_id }}">
            <input type="hidden" name="warehouse_id" value="{{ $purchase->warehouse_id }}">

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li><strong><i class="fa fa-exclamation-circle me-2"></i></strong>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="premium-card">
                <div class="card-header-gradient d-flex justify-content-between align-items-center">
                    <h2 class="card-title-premium">
                        <i class="bi bi-arrow-counterclockwise"></i> Purchase Return Details
                    </h2>
                    <div class="text-white opacity-75 fw-bold">Ref Purchase Invoice: {{ $purchase->invoice_no }}</div>
                </div>

                <div class="card-body p-3 p-lg-4">
                    <!-- Header Purchase Info (Read-only) -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="section-label">Vendor / Supplier</label>
                            <input type="text" class="fi" value="{{ $purchase->vendor->name ?? $purchase->vendor_name ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="section-label">Warehouse</label>
                            <input type="text" class="fi" value="{{ $purchase->warehouse->warehouse_name ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="section-label">Branch</label>
                            <input type="text" class="fi" value="{{ $purchase->branch->name ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="section-label">Return Date</label>
                            <input type="date" name="return_date" class="fi" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Transport Details -->
                    <div class="card bg-light border-0 rounded-4 p-3 mb-4">
                        <h6 class="fw-bold mb-3 text-secondary"><i class="fa fa-truck me-2 text-primary"></i>Transport Information</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="section-label">Transport Name</label>
                                <input type="text" name="transport" class="fi" placeholder="Courier/Transport Name" value="{{ $purchase->job_description ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="section-label">Vehicle No</label>
                                <input type="text" name="vehicle_no" class="fi" placeholder="e.g. KAR-1234">
                            </div>
                            <div class="col-md-3">
                                <label class="section-label">Driver Name</label>
                                <input type="text" name="driver_name" class="fi" placeholder="Driver Name">
                            </div>
                            <div class="col-md-3">
                                <label class="section-label">Delivery Person</label>
                                <input type="text" name="delivery_person" class="fi" placeholder="Delivery Person">
                            </div>
                        </div>
                    </div>

                    <!-- Reason and Notes -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="section-label">Reason for Return</label>
                            <input type="text" name="return_reason" class="fi" placeholder="e.g. Defective, Wrong item, etc.">
                        </div>
                        <div class="col-md-6">
                            <label class="section-label">Additional Remarks</label>
                            <input type="text" name="remarks" class="fi" placeholder="Write internal remarks here...">
                        </div>
                    </div>

                    <!-- Items Table -->
                    <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-list-task me-2 text-primary"></i>Purchase Items for Return</h5>
                    <div class="table-responsive mb-4" style="overflow: visible !important;">
                        <table class="table table-premium">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Product Details</th>
                                    <th style="width: 10%; text-align: center;">UOM</th>
                                    <th style="width: 10%; text-align: center;">Purchased</th>
                                    <th style="width: 10%; text-align: center;">Returned</th>
                                    <th style="width: 10%; text-align: center;">Remaining</th>
                                    <th style="width: 10%; text-align: right;">Unit Price</th>
                                    <th style="width: 10%; text-align: right;">Item Disc</th>
                                    <th style="width: 10%; text-align: center;">Return Qty</th>
                                    <th style="width: 15%; text-align: right;">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Group items by Product and Unit
                                    $groupedItems = $purchase->items->groupBy(function($item) {
                                        return $item->product_id . '_' . ($item->unit ?? 'Piece');
                                    });
                                @endphp

                                @foreach($groupedItems as $groupKey => $items)
                                @php
                                    $first = $items->first();
                                    $product = $first->product;
                                    $purchasedQty = $items->sum('qty');
                                    $returnedQty = $alreadyReturned[$first->product_id] ?? 0;
                                    $remainingQty = max(0, $purchasedQty - $returnedQty);
                                    
                                    $isFullyReturned = $remainingQty <= 0;
                                @endphp
                                <tr class="item-row {{ $isFullyReturned ? 'table-light text-muted opacity-75' : '' }}" data-product-id="{{ $first->product_id }}">
                                    <td>
                                        <div class="fw-bold text-dark mb-1">{{ $product->item_name }}</div>
                                        <div class="d-flex align-items-center flex-wrap gap-1">
                                            <span class="badge bg-light text-muted border px-2 py-0.5" style="font-size: 0.65rem;">
                                                Code: {{ $product->item_code }}
                                            </span>
                                            @if($product->brand)
                                                <span class="badge text-primary px-2 py-0.5" style="background: #eef2ff; font-size: 0.65rem; border: 1px solid #c7d2fe;">
                                                    {{ $product->brand->name }}
                                                </span>
                                            @endif
                                            @if($isFullyReturned)
                                                <span class="badge bg-danger text-white px-2 py-0.5" style="font-size: 0.65rem;">
                                                    Fully Returned
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Hidden Inputs -->
                                        <input type="hidden" name="product_id[]" value="{{ $first->product_id }}">
                                        <input type="hidden" name="unit[]" value="{{ $first->unit ?? 'Piece' }}">
                                    </td>
                                    
                                    <td class="text-center">
                                        <span class="text-muted small">{{ $first->unit ?? 'Piece' }}</span>
                                    </td>
                                    
                                    <td class="text-center fw-bold">{{ $purchasedQty }}</td>
                                    <td class="text-center text-danger">{{ $returnedQty }}</td>
                                    <td class="text-center text-success fw-bold remaining-qty-val">{{ $remainingQty }}</td>
                                    
                                    <td class="text-end">
                                        <input type="number" step="0.01" name="price[]" class="fi fi-table text-end item-price" value="{{ $first->price }}" {{ $isFullyReturned ? 'readonly' : '' }}>
                                    </td>
                                    
                                    <td class="text-end">
                                        <input type="number" step="0.01" name="item_disc[]" class="fi fi-table text-end item-discount" value="0" {{ $isFullyReturned ? 'readonly' : '' }}>
                                    </td>

                                    <td class="text-center">
                                        <input type="number" step="1" name="qty[]" class="fi fi-table text-center return-qty" value="0" min="0" max="{{ $remainingQty }}" {{ $isFullyReturned ? 'readonly' : '' }}>
                                        <span class="validation-badge error">Exceeds limit</span>
                                    </td>
                                    
                                    <td class="text-end fw-bold text-dark line-total-display">
                                        0.00
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary & Net Aggregations -->
                    <div class="row">
                        <div class="col-lg-6 col-md-12">
                            <!-- Optional Instructions or Notes -->
                            <div class="alert bg-soft-warning border-0 rounded-4 p-3 mt-2">
                                <h6 class="fw-bold text-warning mb-2"><i class="fa fa-info-circle me-2"></i>Return Policy & Limits</h6>
                                <p class="small text-muted mb-0">
                                    Returns can only be created for the remaining unreturned items. The net refund amount will be credited to the Vendor's account ledger immediately.
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-12">
                            <div class="summary-card">
                                <div class="summary-row">
                                    <span class="summary-label">Return Subtotal</span>
                                    <span class="summary-value" id="subtotalDisplay">Rs. 0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Overall Discount</span>
                                    <span class="summary-value text-danger" style="width: 40%;">
                                        <input type="number" step="0.01" name="discount" id="overallDiscount" class="fi fi-table text-end" value="0" style="padding: 0.35rem 0.5rem !important;">
                                    </span>
                                </div>
                                <div class="summary-row summary-total">
                                    <span class="summary-label">Net Credit Amount</span>
                                    <span class="summary-value" id="netAmountDisplay">Rs. 0.00</span>
                                </div>
                            </div>

                            <button type="submit" class="btn-premium btn-submit" id="submitBtn" disabled>
                                <i class="fa fa-check-circle me-1"></i> Submit Purchase Return
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        
        // Block Enter key from submitting form
        $('#returnForm').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        function num(n) {
            return isNaN(parseFloat(n)) ? 0 : parseFloat(n);
        }

        function calculateTotals() {
            let subtotal = 0;
            let hasValidReturn = false;
            let hasValidationError = false;

            $('.item-row').each(function() {
                const $row = $(this);
                const price = num($row.find('.item-price').val());
                const discount = num($row.find('.item-discount').val());
                const returnQty = num($row.find('.return-qty').val());
                const remainingQty = num($row.find('.remaining-qty-val').text());

                // Validation check
                if (returnQty > remainingQty) {
                    $row.find('.return-qty').addClass('is-invalid');
                    $row.find('.validation-badge').show();
                    hasValidationError = true;
                } else {
                    $row.find('.return-qty').removeClass('is-invalid');
                    $row.find('.validation-badge').hide();
                }

                if (returnQty > 0) {
                    hasValidReturn = true;
                }

                const lineTotal = Math.max(0, (price * returnQty) - discount);
                $row.find('.line-total-display').text(lineTotal.toFixed(2));

                subtotal += lineTotal;
            });

            $('#subtotalDisplay').text('Rs. ' + subtotal.toFixed(2));

            const overallDiscount = num($('#overallDiscount').val());
            const netAmount = Math.max(0, subtotal - overallDiscount);

            $('#netAmountDisplay').text('Rs. ' + netAmount.toFixed(2));

            // Enable or disable submit button
            if (hasValidReturn && !hasValidationError) {
                $('#submitBtn').prop('disabled', false);
            } else {
                $('#submitBtn').prop('disabled', true);
            }
        }

        // Event listeners
        $(document).on('input', '.return-qty, .item-price, .item-discount, #overallDiscount', function() {
            calculateTotals();
        });

        // Submit confirmation
        $('#returnForm').on('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will deduct stock and record a credit note on the vendor ledger!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Submit Return!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        // Trigger initial calculation
        calculateTotals();
    });
</script>
@else
    <div class="container py-5 text-center">
        <div class="alert alert-danger shadow">Access Denied: You do not have 'purchase.return.create' permission.</div>
    </div>
@endcan
@endsection
