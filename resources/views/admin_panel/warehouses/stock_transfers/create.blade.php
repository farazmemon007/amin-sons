@extends('admin_panel.layout.app')

@section('content')
<style>
    .st-card { border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .st-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 24px; border-radius: 12px 12px 0 0; }
    .st-header h3 { margin: 0; font-weight: 700; }
    .st-header p { margin: 4px 0 0 0; opacity: 0.9; font-size: 0.9rem; }
    .section-box { padding: 24px; border-bottom: 1px solid #f0f0f0; }
    .section-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #9ca3af; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
    .section-label span { display: inline-block; width: 24px; height: 24px; border-radius: 50%; background: #e5e7eb; color: #6b7280; font-weight: 700; text-align: center; line-height: 24px; font-size: 0.75rem; }
    .section-label.active span { background: #667eea; color: white; }
    .location-radio-group { display: flex; gap: 12px; margin-bottom: 16px; }
    .location-radio { padding: 10px 16px; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; transition: all 0.2s; background: white; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; }
    .location-radio:hover { border-color: #667eea; background: #f5f7ff; }
    .location-radio input { cursor: pointer; }
    .location-radio input:checked { }
    .location-radio input:checked + label { color: #667eea; font-weight: 600; }
    .form-input-group { margin-bottom: 16px; }
    .form-input-group label { font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 6px; display: block; }
    .form-input-group select, .form-input-group input { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; font-size: 0.9rem; width: 100%; }
    .form-input-group select:focus, .form-input-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table thead tr { background: #f9fafb; border-bottom: 2px solid #e5e7eb; }
    .items-table th { padding: 12px; text-align: left; font-size: 0.85rem; font-weight: 600; color: #6b7280; text-transform: uppercase; }
    .items-table td { padding: 12px; border-bottom: 1px solid #f3f4f6; }
    .items-table input, .items-table select { border: 1px solid #d1d5db; border-radius: 6px; padding: 8px; font-size: 0.9rem; width: 100%; }
    .items-table input:focus, .items-table select:focus { outline: none; border-color: #667eea; background: #f5f7ff; }
    .btn-action { padding: 6px 12px; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; background: white; color: #667eea; transition: all 0.2s; font-size: 0.9rem; }
    .btn-action:hover { background: #f5f7ff; border-color: #667eea; }
    .btn-danger { color: #dc2626; border-color: #fecaca; }
    .btn-danger:hover { background: #fee2e2; border-color: #dc2626; }
    .add-row-btn { padding: 10px 16px; background: white; border: 2px dashed #667eea; border-radius: 8px; color: #667eea; cursor: pointer; font-weight: 600; font-size: 0.9rem; width: 100%; margin-top: 12px; transition: all 0.2s; }
    .add-row-btn:hover { background: #f5f7ff; }
    .button-group { display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; }
    .btn-submit { background: #667eea; color: white; padding: 12px 32px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9rem; transition: all 0.2s; }
    .btn-submit:hover { background: #5568d3; }
    .btn-cancel { background: white; color: #6b7280; padding: 12px 32px; border: 1px solid #d1d5db; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9rem; text-decoration: none; display: inline-flex; align-items: center; }
    .btn-cancel:hover { background: #f9fafb; }
    .alert-warning { background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 12px; margin-bottom: 16px; color: #92400e; font-size: 0.9rem; display: none; }
    
    /* ✅ SELECT2 OPTGROUP STYLING */
    .select2-container .select2-results__group { 
        background: #f3f4f6; 
        color: #374151; 
        font-weight: 700; 
        padding: 8px 12px; 
        font-size: 0.85rem;
        text-transform: uppercase;
    }
    .select2-container .select2-results__option--group { padding: 0; }
    .select2-container .select2-results__option { padding: 8px 12px; }
    
    /* Primary options (in stock at current branch) - green accent */
    .primary-product { color: #059669; font-weight: 500; }
    .primary-product::before { content: "✓ "; color: #10b981; font-weight: bold; margin-right: 4px; }
    
    /* Secondary options (not in stock) - gray accent */
    .secondary-product { color: #6b7280; font-style: italic; }
    .secondary-product::before { content: "○ "; color: #d1d5db; margin-right: 4px; }
</style>

<div class="container-fluid py-4">
    <div class="col-md-12">
        <div class="st-card">
            <div class="st-header">
                <h3><i class="fas fa-truck-loading me-2"></i>Stock Transfer</h3>
                <p>Transfer inventory between warehouses and branches</p>
            </div>

            <form action="{{ route('stock_transfers.store') }}" method="POST" id="transferForm">
                @csrf

                <!-- SECTION 1: SOURCE LOCATION -->
                <div class="section-box">
                    <div class="section-label active">
                        <span>1</span>
                        Transfer From (Origin)
                    </div>

                    <div class="alert-warning" id="location_alert"></div>

                    <div class="row">
                        @if ($isSuperAdmin)
                        <div class="col-md-4">
                            <div class="form-input-group">
                                <label>Select Branch</label>
                                <select name="branch_id" id="branch_id_select" class="select2" required>
                                    <option value="">-- Select Branch --</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">🏪 {{ $branch->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif

                        <div class="col-md-4">
                            <div class="form-input-group">
                                <label>Transfer From</label>
                                <div class="location-radio-group">
                                    <div class="location-radio">
                                        <input type="radio" name="from_type" id="from_warehouse_radio" value="warehouse" checked>
                                        <label for="from_warehouse_radio">📦 Warehouse</label>
                                    </div>
                                    <div class="location-radio">
                                        <input type="radio" name="from_type" id="from_branch_radio" value="branch">
                                        <label for="from_branch_radio">🏪 Branch</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4" id="source_location_field">
                            <div class="form-input-group">
                                <label id="source_label">Source Location</label>
                                <select id="from_location_id" class="select2">
                                    <option value="">-- Select Location --</option>
                                </select>
                            </div>
                        </div>
                        <!-- Hidden field that will actually be submitted -->
                        <input type="hidden" name="from_warehouse_id" id="from_warehouse_id_field" value="{{ $currentBranchId }}">

                    </div>
                </div>

                <!-- SECTION 2: ITEMS SELECTION -->
                <div class="section-box">
                    <div class="section-label">
                        <span>2</span>
                        Select Items to Transfer
                    </div>

                    <div class="alert-warning" id="stock_alert"></div>

                    <div class="table-responsive">
                        <table class="items-table" id="product_table">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">Item Description</th>
                                    <th style="width: 20%;">Available Stock</th>
                                    <th style="width: 20%;">Transfer Qty</th>
                                    <th style="width: 20%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="product_body">
                                <tr class="product_row">
                                    <td>
                                        <select name="product_id[]" class="product-select" required>
                                            <option value="">-- Search & Select Item --</option>
                                            
                                            @if ($primaryProducts->count() > 0)
                                            <optgroup label="✓ Available in Current Branch">
                                                @foreach ($primaryProducts as $product)
                                                    <option value="{{ $product->id }}" class="primary-product">✓ {{ $product->item_name }} ({{ $product->item_code }})</option>
                                                @endforeach
                                            </optgroup>
                                            @endif
                                            
                                            @if ($secondaryProducts->count() > 0)
                                            <optgroup label="○ Not in Current Branch">
                                                @foreach ($secondaryProducts as $product)
                                                    <option value="{{ $product->id }}" class="secondary-product">{{ $product->item_name }} ({{ $product->item_code }})</option>
                                                @endforeach
                                            </optgroup>
                                            @endif
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="available-stock" readonly placeholder="0.00">
                                    </td>
                                    <td>
                                        <input type="number" name="quantity[]" class="transfer-qty" min="0.01" step="0.01" placeholder="0.00" required>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-action btn-danger remove-row">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="add-row-btn" id="add_row_btn">
                        <i class="fas fa-plus me-2"></i>Add Another Item
                    </button>
                </div>

                <!-- SECTION 3: DESTINATION -->
                <div class="section-box">
                    <div class="section-label">
                        <span>3</span>
                        Delivery Destination
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-input-group">
                                <label>Deliver To</label>
                                <div class="location-radio-group">
                                    <div class="location-radio">
                                        <input type="radio" name="to_type" id="to_warehouse_radio" value="warehouse" checked>
                                        <label for="to_warehouse_radio">📦 Warehouse</label>
                                    </div>
                                    <div class="location-radio">
                                        <input type="radio" name="to_type" id="to_branch_radio" value="branch">
                                        <label for="to_branch_radio">🏪 Branch</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6" id="dest_location_field">
                            <div class="form-input-group">
                                <label id="dest_label">Destination Location</label>
                                <select id="to_location_id" class="select2">
                                    <option value="">-- Select Destination --</option>
                                </select>
                            </div>
                        </div>
                        <!-- Hidden field for branch destination -->
                        <input type="hidden" name="to_warehouse_id" id="to_warehouse_id_field" value="{{ $currentBranchId }}">
                    </div>
                </div>

                <!-- SECTION 4: REMARKS -->
                <div class="section-box">
                    <div class="form-input-group">
                        <label>Internal Remarks</label>
                        <textarea name="remarks" rows="3" placeholder="Add any instructions or transfer reasons..." style="border: 1px solid #d1d5db; border-radius: 8px; padding: 10px; width: 100%; font-size: 0.9rem;"></textarea>
                    </div>
                </div>

                <!-- BUTTONS -->
                <div class="section-box" style="border-bottom: none;">
                    <div class="button-group">
                        <a href="{{ route('stock_transfers.index') }}" class="btn-cancel">
                            <i class="fas fa-times me-2"></i>Discard
                        </a>
                        <button type="submit" class="btn-submit" id="submitBtn">
                            <i class="fas fa-save me-2"></i>Save Transfer
                        </button>
                    </div>
                </div>

                <input type="hidden" name="to_shop" id="to_shop" value="0">
                <input type="hidden" name="from_location_type" id="from_location_type" value="warehouse">
            </form>

            <!-- Hidden template for product row cloning -->
            <template id="product_row_template">
                <tr class="product_row">
                    <td>
                        <select name="product_id[]" class="product-select" required>
                            <option value="">-- Search & Select Item --</option>
                            
                            @if ($primaryProducts->count() > 0)
                            <optgroup label="✓ Available in Current Branch">
                                @foreach ($primaryProducts as $product)
                                    <option value="{{ $product->id }}" class="primary-product">✓ {{ $product->item_name }} ({{ $product->item_code }})</option>
                                @endforeach
                            </optgroup>
                            @endif
                            
                            @if ($secondaryProducts->count() > 0)
                            <optgroup label="○ Not in Current Branch">
                                @foreach ($secondaryProducts as $product)
                                    <option value="{{ $product->id }}" class="secondary-product">{{ $product->item_name }} ({{ $product->item_code }})</option>
                                @endforeach
                            </optgroup>
                            @endif
                        </select>
                    </td>
                    <td>
                        <input type="text" class="available-stock" readonly placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" name="quantity[]" class="transfer-qty" min="0.01" step="0.01" placeholder="0.00" required>
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-action btn-danger remove-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            </template>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2 for all fields
    $('.select2').select2({ width: '100%', placeholder: 'Select...' });

    // ==== STEP 1: SOURCE LOCATION LOGIC ====
    const isSuperAdmin = {{ $isSuperAdmin ? 'true' : 'false' }};
    
    // Update hidden field when warehouse dropdown changes
    $(document).on('change', '#from_location_id', function() {
        $('#from_warehouse_id_field').val($(this).val());
    });
    
    // Update hidden field when superadmin selects branch
    if (isSuperAdmin) {
        $(document).on('change', '#branch_id_select', function() {
            if ($('#from_branch_radio').is(':checked')) {
                $('#from_warehouse_id_field').val($(this).val());
            }
        });
    }
    
    $('input[name="from_type"]').change(function() {
        const type = $(this).val();
        $('#from_location_type').val(type);
        
        if (type === 'branch') {
            // Hide field for branch selection - use branch by default
            $('#source_location_field').hide();
            $('#from_location_id').prop('required', false);
            
            // Set the hidden field value
            if (isSuperAdmin) {
                $('#from_warehouse_id_field').val($('#branch_id_select').val());
            } else {
                $('#from_warehouse_id_field').val({{ $currentBranchId ?? 'null' }});
            }
            
        } else {
            // Show field for warehouse selection
            $('#source_location_field').show();
            $('#from_location_id').prop('required', true);
            
            const locations = @json($warehouses);
            const label = 'Source Warehouse';
            const icon = '📦';
            
            $('#source_label').text(label);
            $('#from_location_id').html('<option value="">-- Select ' + label + ' --</option>');
            
            locations.forEach(loc => {
                const name = loc.warehouse_name || loc.branch_name;
                $('#from_location_id').append(`<option value="${loc.id}">${icon} ${name}</option>`);
            });
            
            // Clear the hidden field when switching to warehouse
            $('#from_warehouse_id_field').val('');
        }
        
        // Re-validate and refresh destination to prevent same location selection
        if ($('input[name="to_type"]:checked').val() === 'warehouse') {
            $('#to_warehouse_radio').trigger('change');
        }
        validateLocationsNotSame();
        
        // Clear products when source changes - use template to preserve options
        const template = document.getElementById('product_row_template');
        const newRow = template.content.cloneNode(true);
        $('#product_body').html(newRow);
        initProductSelect2();
    });

    $('#from_warehouse_radio').trigger('change');

    // ==== STEP 3: DESTINATION LOCATION LOGIC ====
    // Update hidden field when warehouse dropdown changes
    $(document).on('change', '#to_location_id', function() {
        $('#to_warehouse_id_field').val($(this).val());
        validateLocationsNotSame();
    });
    
    // Validate source and destination are not the same
    function validateLocationsNotSame() {
        const fromType = $('#from_location_type').val();
        const toType = $('input[name="to_type"]:checked').val();
        const fromLocationId = $('#from_warehouse_id_field').val();
        const toLocationId = $('input[name="to_type"]:checked').val() === 'branch' 
            ? $('#to_warehouse_id_field').val()
            : $('#to_location_id').val();
        
        // Check if same type and same location
        if (fromType === toType && fromLocationId === toLocationId && fromLocationId) {
            $('#location_alert').html('⚠️ <strong>Invalid:</strong> Source and destination cannot be the same location. Transfer must be from one location to a different location.').show();
            return false;
        } else {
            $('#location_alert').hide();
            return true;
        }
    }
    
    $('input[name="to_type"]').change(function() {
        const type = $(this).val();
        
        if (type === 'branch') {
            // Hide field for branch selection - auto-select current branch
            $('#dest_location_field').hide();
            $('#to_location_id').prop('required', false);
            
            // Set the hidden field value to current branch
            $('#to_warehouse_id_field').val({{ $currentBranchId ?? 'null' }});
            $('#to_shop').val(1); // Mark as branch transfer
            
        } else {
            // Show field for warehouse selection
            $('#dest_location_field').show();
            $('#to_location_id').prop('required', true);
            
            const locations = @json($warehouses);
            const label = 'Destination Warehouse';
            const icon = '📦';
            
            $('#dest_label').text(label);
            $('#to_location_id').html('<option value="">-- Select ' + label + ' --</option>');
            
            // Get the source warehouse ID to exclude it from destination options
            const sourceWarehouseId = $('#from_warehouse_id_field').val();
            const fromType = $('#from_location_type').val();
            
            locations.forEach(loc => {
                const name = loc.warehouse_name || loc.branch_name;
                
                // Disable/mark option if it's the same as source warehouse (when source is also warehouse)
                const isDisabled = (fromType === 'warehouse' && loc.id == sourceWarehouseId);
                const disabledAttr = isDisabled ? 'disabled' : '';
                const optionClass = isDisabled ? 'style="background-color: #fee2e2; color: #991b1b;"' : '';
                
                $('#to_location_id').append(`<option value="${loc.id}" ${disabledAttr} ${optionClass}>${icon} ${name}${isDisabled ? ' (Same as Source)' : ''}</option>`);
            });
            
            $('#to_shop').val(0); // Mark as warehouse transfer
            
            // Clear the hidden field when switching to warehouse
            $('#to_warehouse_id_field').val('');
        }
        
        validateLocationsNotSame();
    });

    $('#to_warehouse_radio').trigger('change');

    // ==== STEP 2: PRODUCT SELECTION ====
    function initProductSelect2() {
        $('.product-select').select2({
            placeholder: 'Search & select item...',
            width: '100%',
            allowClear: false,
            matcher: function(params, data) {
                // Allow filtering by item name and code
                if (!params.term) return data;
                if ((data.text || '').toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
                    return data;
                }
                return null;
            }
        });
    }

    initProductSelect2();

    // ==== PRODUCT SELECTION EVENT ====
    $(document).on('select2:select', '.product-select', function() {
        const $row = $(this).closest('tr');
        const productId = $(this).val();
        const sourceType = $('#from_location_type').val();
        const sourceId = $('#from_warehouse_id_field').val();

        if (productId && sourceId) {
            // Fetch available stock from correct endpoint
            const params = {
                product_id: productId
            };
            
            // Add correct parameter based on source type
            if (sourceType === 'branch') {
                params.branch_id = sourceId;
            } else {
                params.warehouse_id = sourceId;
            }
            
            $.ajax({
                url: '/warehouse-stock-quantity',
                type: 'GET',
                data: params,
                success: data => {
                    const qty = data.quantity || 0;
                    $row.find('.available-stock').val(parseFloat(qty).toFixed(2));
                    $row.find('.transfer-qty').attr('max', qty).val('');
                },
                error: () => {
                    $row.find('.available-stock').val('0.00');
                }
            });
        }
    });

    // ==== ADD / REMOVE ROWS ====
    $('#add_row_btn').click(function() {
        const template = document.getElementById('product_row_template');
        const newRow = template.content.cloneNode(true);
        $('#product_body').append(newRow);
        initProductSelect2();
    });

    $(document).on('click', '.remove-row', function() {
        if ($('#product_body tr').length > 1) {
            $(this).closest('tr').remove();
        }
    });

    // ==== VALIDATION ====
    $(document).on('input', '.transfer-qty', function() {
        const max = parseFloat($(this).closest('tr').find('.available-stock').val()) || 999999;
        const val = parseFloat($(this).val()) || 0;
        
        if (val > max) {
            $('#stock_alert').text('⚠️ Transfer quantity exceeds available stock!').show();
            $(this).val(max.toFixed(2));
        } else {
            $('#stock_alert').hide();
        }
    });

    // Form submission validation
    $('#transferForm').submit(function(e) {
        // ✅ CRITICAL VALIDATION: Check locations not the same
        if (!validateLocationsNotSame()) {
            e.preventDefault();
            alert('❌ Source and destination cannot be the same location!');
            return false;
        }
        
        // Check if at least one product is selected with a valid quantity
        let hasValidItems = false;
        
        $('#product_body tr').each(function() {
            const productId = $(this).find('select[name="product_id[]"]').val();
            const quantity = parseFloat($(this).find('input[name="quantity[]"]').val()) || 0;
            
            if (productId && quantity > 0) {
                hasValidItems = true;
                return false; // break loop
            }
        });
        
        const fromLocation = $('#from_warehouse_id_field').val();
        const toLocationType = $('input[name="to_type"]:checked').val();
        
        // Get destination location: either from hidden field (branch) or dropdown (warehouse)
        let toLocation = null;
        if (toLocationType === 'branch') {
            toLocation = $('#to_warehouse_id_field').val();
        } else {
            toLocation = $('#to_location_id').val();
        }
        
        if (!fromLocation) {
            e.preventDefault();
            alert('❌ Please select source location');
            return false;
        }
        if (!toLocation) {
            e.preventDefault();
            alert('❌ Please select destination location');
            return false;
        }
        if (!hasValidItems) {
            e.preventDefault();
            alert('❌ Please add at least one item with quantity to transfer');
            return false;
        }
    });
});
</script>
@endsection