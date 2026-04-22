@extends('admin_panel.layout.app')

@section('content')
    <style>
        /* ====== Look & Feel ====== */
        .gp-shell {
            max-width: 1200px;
            margin-inline: auto
        }

        .gp-card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 4px 16px rgba(16, 24, 40, .06)
        }

        .gp-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-bottom: 1px solid #eef2f7
        }

        .gp-head h6 {
            margin: 0;
            font-weight: 700
        }

        .gp-body {
            padding: 12px
        }

        .gp-row {
            display: grid;
            gap: 10px
        }

        .gp-2 {
            grid-template-columns: repeat(2, 1fr)
        }

        label {
            font-size: .82rem;
            color: #6b7280;
            margin-bottom: 4px
        }

        .form-control,
        .form-select {
            height: 34px;
            padding: .28rem .55rem;
            font-size: .9rem;
            border-radius: 9px
        }

        .table-sm th,
        .table-sm td {
            padding: .45rem .55rem;
            vertical-align: middle
        }

        .table thead th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 700
        }

        .compact {
            border: 1px solid #edf0f5;
            border-radius: 10px;
            overflow: hidden
        }

        .searchWrap {
            position: relative
        }

        .searchResults {
            position: absolute;
            inset: calc(100% + 2px) 0 auto 0;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 12px 22px rgba(16, 24, 40, .12);
            max-height: 220px;
            overflow: auto;
            z-index: 9999;
            display: none
        }

        .searchResults .result {
            padding: .5rem .65rem;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            cursor: pointer
        }

        .gp-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 10px 14px;
            border-top: 1px solid #eef2f7;
            background: #fcfcfd;
            border-bottom-left-radius: 14px;
            border-bottom-right-radius: 14px
        }

        .btn-slim {
            --bs-btn-padding-y: .35rem;
            --bs-btn-padding-x: .7rem;
            --bs-btn-font-size: .86rem;
            border-radius: 10px
        }
    </style>

    <div class="container-fluid mb-3" style="padding:10px;">
        <div class="gp-header row align-items-center mb-2">
            <div class="col-md-3">
                <div class="gp-title">
                    <h5 class="mb-0 fw-semibold">Add Outward Gatepass</h5>
                    <small class="text-muted">Create & manage outward gate passes</small>
                </div>
            </div>
            <div class="col-7">
                <div class="gp-actions-center text-center">
                    <a href="#" class="gp-action-btn"><i class="fa fa-box"></i><span>Item</span></a>
                    <a href="#" class="gp-action-btn danger" onclick="return confirm('Delete this gatepass?')"><i
                            class="fa fa-trash"></i><span>Delete</span></a>
                </div>
            </div>
            <div class="col-md-2 text-end">
                <a href="{{ route('OutwardGatepass.list') }}" class="btn btn-info btn-sm" title="View all gate passes">
                    <i class="fa fa-receipt"></i> Gate Pass
                </a>
                <a href="{{ route('OutwardGatepass.home') }}" class="btn btn-outline-secondary btn-sm"><i
                        class="fa fa-arrow-left"></i> Back</a>
            </div>
        </div>
    </div>

    <div class="gp-card">
        <div class="gp-head">
            <h6>Outward Gate Pass</h6>
            <div class="d-flex gap-2"><button form="gatepassForm" class="btn btn-primary btn-slim">Save</button></div>
        </div>

        <div class="gp-body">
            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-2">
                    <h6 class="mb-2">❌ Validation Errors:</h6>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success py-2 px-3 mb-2">
                    <strong>✅ Success:</strong> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger py-2 px-3 mb-2">
                    <strong>❌ Error:</strong> {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('store.OutwardGatepass') }}" method="POST" id="gatepassForm">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id ?? '' }}">
                <input type="hidden" name="items_text" id="items_text">

                <div class="row mb-2">
                    <div class="col-md-6">
                        <div class="border rounded p-2 h-100">
                            <h6 class="mb-2 text-muted">Gatepass Info</h6>
                            <div class="gp-row gp-2 mb-2">
                                <div>
                                    <label>Date</label>
                                    <input type="date" name="gatepass_date" class="form-control"
                                        value="{{ old('gatepass_date', date('Y-m-d')) }}">
                                </div>
                                <div>
                                    <label>DC No</label>
                                    <input type="text" name="dc_no" class="form-control"
                                        value="{{ $order->dc_no ?? old('dc_no') }}" readonly>
                                </div>
                                <div>
                                    <label>Invoice No</label>
                                    <input type="text" name="invoice_no" class="form-control"
                                        value="{{ old('invoice_no', $prefillData['invoice_no'] ?? '') }}" readonly>
                                </div>
                                <div>
                                    <label>Warehouse</label>
                                    <input type="text" class="form-control"
                                        value="{{ $prefillData['warehouse_name'] ?? 'N/A' }}"
                                        readonly>
                                    @if($prefillData['delivery_location_type'] === 'Branch')
                                        <small class="badge bg-success">Branch Delivery</small>
                                    @else
                                        <small class="badge bg-primary">Warehouse Delivery</small>
                                    @endif
                                    <input type="hidden" name="warehouse_id" value="{{ $order->warehouse_id ?? '' }}">
                                </div>
                                <div>
                                    <label>Customer Name</label>
                                    <input type="text" name="customer_name" class="form-control"
                                        value="{{ old('customer_name', $prefillData['customer_name'] ?? '') }}" readonly>
                                    @if($prefillData['is_walking_customer'] ?? false)
                                        <small class="badge bg-warning text-dark">Walking Customer</small>
                                    @endif
                                </div>
                                <div>
                                    <label>Issued By</label>
                                    <input type="text" name="issued_by" class="form-control"
                                        value="{{ old('issued_by', auth()->user()->name ?? '') }}"
                                        readonly>
                                </div>
                                <div>
                                    <label>Delivery City</label>
                                    <input type="text" name="delivery_city" class="form-control"
                                        >
                                </div>
                                <div>
                                    <label>Remarks</label>
                                    <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}">
                                </div>
                                <div>
                                    <label>Billty No</label>
                                    <input type="text" name="billty_no" class="form-control"
                                        value="{{ old('billty_no') }}">
                                </div>
                                <div>
                                    <label>Billty Date</label>
                                    <input type="date" name="billty_date" class="form-control"
                                        value="{{ old('billty_date') }}">
                                </div>
                                <div>
                                    <label>Transport</label>
                                    <input type="text" name="transporter" class="form-control"
                                        value="{{ old('transporter') }}">
                                </div>
                                <div>
                                    <label>Billty Amount</label>
                                    <input type="number" step="0.01" name="billty_amount" class="form-control"
                                        value="{{ old('billty_amount') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-2 h-100">
                            <h6 class="mb-2 text-muted">Transport / Driver</h6>
                            <div class="gp-row gp-2 mb-2">
                                <div>
                                    <label>Vehicle Type</label>
                                    <input type="text" name="vehicle_type" class="form-control"
                                        value="{{ old('vehicle_type') }}">
                                </div>
                                <div>
                                    <label>Driver Name</label>
                                    <input type="text" name="driver_name" class="form-control"
                                        value="{{ old('driver_name') }}">
                                </div>
                                <div>
                                    <label>Vehicle Number</label>
                                    <input type="text" name="vehicle_number" class="form-control"
                                        value="{{ old('vehicle_number') }}">
                                </div>
                                <div>
                                    <label>Transport Rent</label>
                                    <input type="number" step="0.01" name="transport_rent" class="form-control"
                                        value="{{ old('transport_rent', $prefillData['transport_rent'] ?? '') }}" placeholder="Freight charges">
                                </div>
                                <div style="grid-column:span 2">
                                    <label>Packing Type</label>
                                    <textarea name="note" class="form-control" style="height:180px; resize: vertical;">
                                     {{ old('note') }}
                                     </textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive compact mb-2">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr class="text-center">
                                <th style="min-width:200px;">Product</th>
                                <th style="min-width:100px;">Item Code</th>
                                <th style="min-width:100px;">Brand</th>
                                <th style="min-width:80px;">Unit</th>
                                <th style="min-width:80px;">Available</th>
                                <th style="min-width:80px;">Deliver Qty</th>
                                <th style="min-width:80px;">Remaining</th>
                                <th style="width:60px">Action</th>
                            </tr>
                        </thead>
                        <tbody id="gatepassItems">
                            @php
                                // ✅ Separate variables for header info vs items table
                                // Header info should come from controller $prefillData if available
                                if (empty($prefillData) || !isset($prefillData['invoice_no'])) {
                                    $prefillData = [
                                        'invoice_no' => null,
                                        'customer_name' => null,
                                        'delivery_city' => null,
                                    ];
                                }
                                
                                // Items prefill - use different variable to avoid conflict
                                $itemsPrefill = [];
                                
                                // Priority 1: Use passed $prefill variable (from createFromRemaining)
                                if (!empty($prefill) && is_array($prefill)) {
                                    $itemsPrefill = $prefill;
                                }
                                // Priority 2: Use order items
                                elseif (!empty($order->items) && is_array($order->items)) {
                                    $itemsPrefill = $order->items;
                                }
                                // Priority 3: Use sale items
                                elseif (!empty($sale) && $sale->saleItems->count() > 0) {
                                    // prefer sale items filtered by warehouse if order has warehouse_id
                                    $itemsPrefill = $sale->saleItems
                                        ->filter(function ($si) use ($order) {
                                            return empty($order->warehouse_id) ||
                                                $si->warehouse_id == $order->warehouse_id;
                                        })
                                        ->map(function ($si) {
                                            return [
                                                'product_id' => $si->product_id,
                                                'product_name' => $si->product->item_name ?? null,
                                                'item_code' => $si->product->item_code ?? null,
                                                'qty' => $si->sales_qty ?? ($si->qty ?? 0),
                                                'retail_price' => $si->retail_price ?? null,
                                                'amount' => $si->amount ?? null,
                                            ];
                                        })
                                        ->values()
                                        ->toArray();
                                }
                            @endphp

                            @forelse($itemsPrefill as $p)
                                <tr>
                                    <td class="searchWrap">
                                        <input type="hidden" name="product_id[]" class="product_id"
                                            value="{{ $p['product_id'] ?? '' }}">
                                        <input type="text" class="form-control productSearch"
                                            placeholder="Search product by name/code" autocomplete="off"
                                            value="{{ $p['product_name'] ?? '' }}">
                                        <div class="searchResults"></div>
                                    </td>
                                    <td><input type="text" name="item_code[]" class="form-control" readonly
                                            value="{{ $p['item_code'] ?? '' }}"></td>
                                    <td><input type="text" name="brand[]" class="form-control" readonly
                                            value="{{ $p['brand'] ?? ($productsMap[$p['product_id']]['brand'] ?? '') }}">
                                    </td>
                                    <td><input type="text" name="unit[]" class="form-control" readonly
                                            value="{{ $p['unit'] ?? ($productsMap[$p['product_id']]['unit'] ?? '') }}">
                                    </td>
                                    <td><input type="number" name="available_qty[]" class="form-control available_qty text-end" readonly
                                            value="{{ $p['available_qty'] ?? $p['qty'] ?? 1 }}"></td>
                                    <td><input type="number" name="qty[]" class="form-control quantity text-end"
                                            min="1" value="{{ $p['qty'] ?? 1 }}" 
                                            data-product-id="{{ $p['product_id'] ?? '' }}" 
                                            data-total-sale-qty="{{ $p['total_sale_qty'] ?? $p['available_qty'] ?? 0 }}"
                                            data-previously-delivered="{{ $p['previously_delivered'] ?? 0 }}"></td>
                                    <td><input type="number" name="remaining_qty[]" class="form-control remaining_qty text-end text-danger" readonly
                                            title="Total: {{ $p['total_sale_qty'] ?? 0 }} | Previously Delivered: {{ $p['previously_delivered'] ?? 0 }} | Current: {{ $p['qty'] ?? 0 }} | Remaining: {{ $p['remaining_qty'] ?? 0 }}"
                                            value="{{ $p['remaining_qty'] ?? 0 }}"></td>
                                    <td class="text-center"><button type="button"
                                            class="btn btn-outline-danger btn-slim remove-row">X</button></td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="searchWrap">
                                        <input type="hidden" name="product_id[]" class="product_id">
                                        <input type="text" class="form-control productSearch"
                                            placeholder="Search product by name/code" autocomplete="off">
                                        <div class="searchResults"></div>
                                    </td>
                                    <td><input type="text" name="item_code[]" class="form-control" readonly></td>
                                    <td><input type="text" name="brand[]" class="form-control" readonly></td>
                                    <td><input type="text" name="unit[]" class="form-control" readonly></td>
                                    <td><input type="number" name="available_qty[]" class="form-control available_qty text-end" readonly value="0"></td>
                                    <td><input type="number" name="qty[]" class="form-control quantity text-end"
                                            min="1" value="1"></td>
                                    <td><input type="number" name="remaining_qty[]" class="form-control remaining_qty text-end text-danger" readonly value="0"></td>
                                    <td class="text-center"><button type="button"
                                            class="btn btn-outline-danger btn-slim remove-row">X</button></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="gp-foot">
                    <button type="button" id="addRowBtn" class="btn btn-outline-primary btn-slim">Add Row</button>
                    <button type="submit" class="btn btn-primary btn-slim">Submit Gatepass</button>
                </div>
            </form>

            <!-- Modal for Add/Edit Vendor -->
            <div class="modal fade" id="vendorModal">
                <div class="modal-dialog">
                    <form action="{{ url('vendor/store') }}" method="POST">@csrf
                        <input type="hidden" id="vendor_id" name="id">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add/Edit Vendor</h5>
                            </div>
                            <div class="modal-body">
                                <div class="mb-2"><input class="form-control" name="name" id="vname"
                                        placeholder="Name" required></div>
                                <div class="mb-2"><input class="form-control" name="opening_balance"
                                        id="opening_balance" placeholder="Opening Balance" required></div>
                                <div class="mb-2"><input class="form-control" name="phone" id="vphone"
                                        placeholder="Phone"></div>
                                <div class="mb-2">
                                    <textarea class="form-control" name="address" id="vaddress" placeholder="Address"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

@endsection

{{-- libs --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select One',
            allowClear: true
        });

        function escapeHtml(t) {
            return String(t || '').replace(/[&<>\"'`=\/]/g, s => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '\"': '&quot;',
                "'": '&#39;',
                '/': '&#47;',
                '`': '&#96;',
                '=': '&#61;'
            } [s]));
        }

        function appendBlankRow() {
            $('#gatepassItems').append(
                `<tr>
                    <td class="searchWrap">
                        <input type="hidden" name="product_id[]" class="product_id">
                        <input type="text" class="form-control productSearch" placeholder="Search product by name/code" autocomplete="off">
                        <div class="searchResults"></div>
                    </td>
                    <td><input type="text" name="item_code[]" class="form-control" readonly></td>
                    <td><input type="text" name="brand[]" class="form-control" readonly></td>
                    <td><input type="text" name="unit[]" class="form-control" readonly></td>
                    <td><input type="number" name="available_qty[]" class="form-control available_qty text-end" readonly value="0"></td>
                    <td><input type="number" name="qty[]" class="form-control quantity text-end" min="1" value="1"></td>
                    <td><input type="number" name="remaining_qty[]" class="form-control remaining_qty text-end text-danger" readonly value="0"></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-danger btn-slim remove-row">X</button></td>
                </tr>`
            );
        }

        // Calculate remaining qty and validate
        $(document).on('change keyup', '.quantity', function() {
            const $row = $(this).closest('tr');
            const available = parseFloat($row.find('.available_qty').val()) || 0;
            const delivered = parseFloat($(this).val()) || 0;
            const totalSaleQty = parseFloat($(this).data('total-sale-qty')) || available;
            const previouslyDelivered = parseFloat($(this).data('previously-delivered')) || 0;
            
            // Remaining = Total - Previously Delivered - Current Delivery
            const remaining = Math.max(0, totalSaleQty - previouslyDelivered - delivered);
            
            $row.find('.remaining_qty').val(remaining.toFixed(4));
            
            // Warn if delivered > available
            if (delivered > available && available > 0) {
                $(this).addClass('border-danger');
                $row.find('.quantity').css('background-color', '#ffe5e5');
            } else {
                $(this).removeClass('border-danger');
                $row.find('.quantity').css('background-color', '');
            }
        });

        // Fetch available stock when product is selected
        function fetchAvailableStock(productId, $row) {
            if (!productId) return;
            
            const warehouseId = $('input[name="warehouse_id"]').val();
            if (!warehouseId) return;
            
            $.get("{{ route('get-warehouse-stock') }}", {
                product_id: productId,
                warehouse_id: warehouseId
            }, function(data) {
                const availableQty = data && data.quantity ? parseFloat(data.quantity) : 0;
                $row.find('.available_qty').val(availableQty.toFixed(4));
                
                // Reset delivered qty to available if previously was more
                const deliveredQty = parseFloat($row.find('.quantity').val()) || 0;
                if (deliveredQty === 1 || deliveredQty > availableQty) {
                    $row.find('.quantity').val(availableQty.toFixed(4)).trigger('change');
                } else {
                    $row.find('.quantity').trigger('change');
                }
            }).fail(function() {
                $row.find('.available_qty').val('0');
                $row.find('.quantity').trigger('change');
            });
        }

        $('#addRowBtn').on('click', appendBlankRow);

        $(document).on('keyup', '.productSearch', function() {
            const $inp = $(this),
                q = $inp.val().trim(),
                $wrap = $inp.closest('.searchWrap'),
                $box = $wrap.find('.searchResults');
            if (!q) {
                $box.hide().empty();
                return;
            }
            $.get("{{ route('search-products') }}", {
                q
            }, function(data) {
                let html = '';
                (data || []).forEach(p => {
                    const brand = p.brand && p.brand.name ? p.brand.name : '';
                    const unitName = (p.unit && p.unit.name) ? p.unit.name : (p
                        .unit_name || p.unit || p.unit_id || '');
                    html +=
                        `\n          <div class="result" data-id="${p.id||''}" data-name="${escapeHtml(p.item_name||'')}" data-code="${escapeHtml(p.item_code||'')}" data-brand="${escapeHtml(brand)}" data-unit="${escapeHtml(unitName)}">\n            <span>${escapeHtml(p.item_name||'')} <small>(${escapeHtml(p.item_code||'')})</small></span>\n            <small>${escapeHtml(brand)} ${unitName?(' | '+escapeHtml(unitName)) : ''}</small>\n          </div>`;
                });
                $box.html(html).show();
            });
        });

        $(document).on('click', '.searchResults .result', function() {
            const $r = $(this),
                $tr = $r.closest('tr');
            const productId = $r.data('id');
            
            $tr.find('.product_id').val(productId);
            $tr.find('.productSearch').val($r.data('name'));
            $tr.find('input[name="item_code[]"]').val($r.data('code'));
            $tr.find('input[name="brand[]"]').val($r.data('brand'));
            $tr.find('input[name="unit[]"]').val($r.data('unit'));
            $r.parent().hide().empty();
            
            // Fetch available stock for this product
            fetchAvailableStock(productId, $tr);
            
            if ($('#gatepassItems tr:last .product_id').val()) {
                appendBlankRow();
                $('#gatepassItems tr:last .productSearch').focus();
            }
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.searchWrap').length) {
                $('.searchResults').hide().empty();
            }
        });
        $(document).on('click', '.remove-row', function() {
            if ($('#gatepassItems tr').length > 1) $(this).closest('tr').remove();
        });

        $('#gatepassForm').on('submit', function(e) {
            e.preventDefault(); // Prevent default submission first
            
            console.log('📝 Form submission started...');
            
            // Remove rows with no product or code
            $('#gatepassItems tr').each(function() {
                if (!$(this).find('.product_id').val() && !$(this).find('.productSearch').val())
                    $(this).remove();
            });
            
            console.log('📊 Remaining rows after cleanup:', $('#gatepassItems tr').length);
            
            // Count valid products with quantities
            let validCount = 0;
            const productsData = [];
            
            $('#gatepassItems tr').each(function() {
                const productId = $(this).find('.product_id').val();
                const productName = $(this).find('.productSearch').val();
                const qty = parseFloat($(this).find('input[name="qty[]"]').val()) || 0;
                
                if ((productId || productName) && qty > 0) {
                    validCount++;
                    productsData.push({
                        product_id: productId,
                        product_name: productName,
                        qty: qty
                    });
                }
            });
            
            console.log('✅ Valid products count:', validCount);
            console.log('📦 Products data:', productsData);
            
            // Build items text for display
            const lines = [];
            $('#gatepassItems tr').each(function() {
                const name = $(this).find('.productSearch').val() || '';
                const code = $(this).find('input[name="item_code[]"]').val() || '';
                const qty = $(this).find('input[name="qty[]"]').val() || '';
                if (name || code) {
                    lines.push((name || code) + (qty ? ' | Qty: ' + qty : ''));
                }
            });
            $('#items_text').val(lines.join('\n'));
            
            console.log('📄 Items text:', $('#items_text').val());
            
            // Validate: must have at least one valid product
            if (validCount === 0) {
                console.error('❌ No valid products found!');
                Swal.fire('Error', '❌ Please add at least one product with quantity > 0 to create a gate pass', 'error');
                return false;
            }
            
            // All validation passed - submit form
            console.log('✅ All validations passed. Submitting form...');
            
            // Show loading state
            Swal.fire({
                title: 'Submitting...',
                html: 'Creating gatepass with ' + validCount + ' product(s)...',
                didOpen: () => Swal.showLoading(),
                allowOutsideClick: false,
                allowEscapeKey: false
            });
            
            this.submit();
        });

        $('#gatepassForm').on('keypress', function(e) {
            if (e.key === 'Enter' && e.target.type !== 'textarea') {
                e.preventDefault();
            }
        });
    });
</script>
