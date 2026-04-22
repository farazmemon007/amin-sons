@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">➕ New Stock Request</h5>
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

            <form action="{{ route('inter_branch_stock_requests.store') }}" method="POST">
                @csrf

                <!-- Step 0: Super Admin - Select Source Branch -->
                @if(auth()->user()->hasRole('super admin'))
                <div class="card mb-4 border-0 bg-warning bg-opacity-10">
                    <div class="card-body">
                        <h6 class="card-title mb-3">⚙️ Step 0: Select Source Branch (Super Admin)</h6>
                        <div class="row">
                            <div class="col-md-8">
                                <label class="form-label"><strong>Request From Branch:</strong></label>
                                <select name="from_branch_id" id="from_branch_id" class="form-control @error('from_branch_id') is-invalid @enderror" required>
                                    <option value="">-- Select Source Branch --</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">
                                            🏪 {{ $branch->name ?? 'Branch #' . $branch->id }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_branch_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">As super admin, select which branch is creating this request</small>
                    </div>
                </div>
                @endif

                <!-- Step 1: Select Receiving Branch -->
                <div class="card mb-4 border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title mb-3">🏪 Step 1: Select Receiving Branch</h6>
                        <div class="row">
                            <div class="col-md-8">
                                <label class="form-label"><strong>Send Request To:</strong></label>
                                <select name="to_branch_id" id="to_branch_id" class="form-control @error('to_branch_id') is-invalid @enderror" required>
                                    <option value="">-- Select Branch --</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">
                                            🏪 {{ $branch->name ?? 'Branch #' . $branch->id }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_branch_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Select Products -->
                <div class="card mb-4 border-0">
                    <div class="card-body">
                        <h6 class="card-title mb-3">📋 Step 2: Add Products</h6>
                        <p class="text-muted small">Products will be filtered based on the selected branch's inventory</p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="product_table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50%">Product Name</th>
                                        <th style="width: 30%">Requested Qty</th>
                                        <th style="width: 20%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="product_body">
                                    <tr class="product_row">
                                        <td>
                                            <select name="product_id[]" class="form-control product-select" required style="width:100%">
                                                <option value="">Select Branch First</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="quantity[]" class="form-control quantity" required min="1" value="1">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-row">Remove</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" id="add_row_btn">
                            <i class="fas fa-plus"></i> Add Product Row
                        </button>
                    </div>
                </div>

                <!-- Step 3: Remarks -->
                <div class="card mb-4 border-0">
                    <div class="card-body">
                        <h6 class="card-title mb-3">📝 Step 3: Add Remarks (Optional)</h6>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Add any notes or special instructions..."></textarea>
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Submit Request
                    </button>
                    <a href="{{ route('inter_branch_stock_requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // ✅ ERP PROPER: Load products based on selected branch
    $('#to_branch_id').on('change', function() {
        const branchId = $(this).val();
        
        if (!branchId) {
            // Clear all product dropdowns if no branch selected
            $('.product-select').html('<option value="">Select Branch First</option>').prop('disabled', true);
            return;
        }

        // Fetch products for the selected branch
        $.ajax({
            url: `/api/branch-products/${branchId}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // Generate product options
                let productHtml = '<option value="">Select Product</option>';
                
                if (response.products && response.products.length > 0) {
                    response.products.forEach(function(product) {
                        productHtml += `<option value="${product.id}">${product.item_name}</option>`;
                    });
                } else {
                    productHtml = '<option value="">No products available in this branch</option>';
                }

                // Update all existing product dropdowns
                $('.product-select').html(productHtml).prop('disabled', false);
            },
            error: function(error) {
                console.error('Error loading products:', error);
                $('.product-select').html('<option value="">Error loading products</option>').prop('disabled', true);
            }
        });
    });

    // Add new row
    $('#add_row_btn').on('click', function() {
        addNewRow();
    });

    // Remove row
    $(document).on('click', '.remove-row', function() {
        const $row = $(this).closest('tr');
        if ($('#product_body tr').length === 1) {
            $row.find('select').val('');
            $row.find('input').val('1');
            return;
        }
        $row.remove();
    });

    // Add new product row with dynamic products
    function addNewRow() {
        const branchId = $('#to_branch_id').val();
        
        if (!branchId) {
            alert('Please select a branch first!');
            return;
        }

        // Get current product options from existing selects
        let productHtml = '<option value="">Select Product</option>';
        $('.product-select:first option').each(function() {
            if ($(this).val()) {
                productHtml += `<option value="${$(this).val()}">${$(this).text()}</option>`;
            }
        });

        var row = `
            <tr class="product_row">
                <td>
                    <select name="product_id[]" class="form-control product-select" required style="width:100%">
                        ${productHtml}
                    </select>
                </td>
                <td>
                    <input type="number" name="quantity[]" class="form-control quantity" required min="1" value="1">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-row">Remove</button>
                </td>
            </tr>
        `;
        $('#product_body').append(row);
    }

    // Trigger initial load if branch is already selected (e.g., validation error scenario)
    if ($('#to_branch_id').val()) {
        $('#to_branch_id').trigger('change');
    }
});
</script>
@endsection
