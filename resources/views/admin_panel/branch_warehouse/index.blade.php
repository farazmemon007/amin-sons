@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Professional ERP Variables */
    :root {
        --primary-hex: #4361ee;
        --border-color: #e2e8f0;
        --text-main: #1e293b;
        --bg-light: #f8fafc;
    }

    .erp-container {
        padding: 2rem;
        background: #f1f5f9;
        min-height: 100vh;
    }

    /* Main Table Wrapper */
    .table-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    /* Header Styling */
    .table-header-custom {
        padding: 1.5rem;
        background: white;
        border-bottom: 2px solid var(--bg-light);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Data Table Styling */
    .custom-table { width: 100%; margin-bottom: 0; }
    .custom-table thead th {
        background: var(--bg-light);
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        padding: 1rem 1.5rem;
        border: none;
    }

    .custom-table tbody tr {
        border-bottom: 1px solid var(--bg-light);
        transition: background 0.2s;
    }
    .custom-table tbody tr:hover { background: #fdfdff; }

    .custom-table td {
        padding: 1.2rem 1.5rem;
        vertical-align: middle;
        color: var(--text-main);
    }

    /* Modern Tags for Warehouses */
    .wh-tag {
        display: inline-flex;
        align-items: center;
        background: #f1f5f9;
        color: #475569;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        margin: 2px;
        border: 1px solid #e2e8f0;
    }
    .wh-tag i { color: var(--primary-hex); margin-right: 5px; }

    /* Action Button */
    .btn-edit-modern {
        background: white;
        color: var(--primary-hex);
        border: 1.5px solid var(--primary-hex);
        font-weight: 700;
        font-size: 13px;
        padding: 6px 16px;
        border-radius: 8px;
        transition: 0.3s;
    }
    .btn-edit-modern:hover {
        background: var(--primary-hex);
        color: white;
    }

    /* MODAL FIXES: Layout overlap ko hatane ke liye */
    .modal-content-custom {
        border-radius: 16px;
        border: none;
    }
    .modal-header-custom {
        padding: 1.5rem;
        border-bottom: 1px solid var(--bg-light);
    }
    .warehouse-selection-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        max-height: 400px;
        overflow-y: auto;
        padding: 10px;
    }
    .selection-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 12px 15px;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: all 0.2s;
        position: relative;
    }
    .selection-item:hover { 
        border-color: var(--primary-hex); 
        background: #eff6ff; 
    }
    
    /* Checkbox overlap fix */
    .selection-item .form-check-input {
        margin: 0 12px 0 0; /* Bootstrap margins reset */
        flex-shrink: 0;
        width: 1.1rem;
        height: 1.1rem;
        cursor: pointer;
        position: static; /* Overlap khatam karne ke liye */
    }
    .selection-item label {
        cursor: pointer;
        margin-bottom: 0;
        flex-grow: 1;
        line-height: 1.3;
    }
    /* Item highlight when checked */
    .selection-item:has(.warehouse-checkbox:checked) {
        border-color: var(--primary-hex);
        background: #f0f3ff;
    }
</style>

<div class="erp-container">
    <div class="table-card">
        <div class="table-header-custom">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Branch ↔ Warehouse Mapping</h4>
                <p class="text-muted small mb-0"><i class="las la-info-circle"></i> Define which warehouses supply stock to specific branches.</p>
            </div>
            {{-- <div class="d-flex gap-2">
                <button class="btn btn-light border btn-sm"><i class="las la-file-export"></i> Export</button>
            </div> --}}
        </div>

        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th width="30%">Branch Name</th>
                        <th width="55%">Assigned Warehouses</th>
                        <th width="15%" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branches as $branch)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:35px; height:35px; font-size:12px; font-weight:bold;">
                                    {{ substr($branch->name ?? 'B', 0, 1) }}
                                </div>
                                <div>
                                    <span class="fw-bold d-block">{{ $branch->name ?? 'Branch '.$branch->id }}</span>
                                    <span class="text-muted" style="font-size:11px;">#BR-{{ $branch->id }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap">
                                @forelse($branch->warehouses as $w)
                                    <div class="wh-tag">
                                        <i class="las la-warehouse"></i> {{ $w->warehouse_name }}
                                    </div>
                                @empty
                                    <span class="text-muted small italic">Not mapped yet</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="text-end">
                            <button class="btn-edit-modern" 
                                data-toggle="modal" 
                                data-target="#mapModal" 
                                data-branch-name="{{ $branch->name }}"
                                data-branch-id="{{ $branch->id }}" 
                                data-warehouses="{{ $branch->warehouses->pluck('id')->implode(',') }}">
                                <i class="las la-cog"></i> Assign warehouse
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="mapModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-content-custom">
            <form id="mapForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header modal-header-custom">
                    <h5 class="fw-bold mb-0">Mapping for <span id="modalBranchName" class="text-primary"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="fw-bold text-muted small text-uppercase mb-0">Select Available Warehouses</label>
                        <span class="badge bg-light text-dark border">Double Column View</span>
                    </div>
                    
                    <div class="warehouse-selection-grid">
                        @foreach($warehouses as $w)
                        <div class="selection-item">
                            <input class="form-check-input warehouse-checkbox" 
                                   type="checkbox" 
                                   name="warehouse_ids[]" 
                                   value="{{ $w->id }}" 
                                   id="wh_cb_{{ $w->id }}">
                            <label class="form-check-label" for="wh_cb_{{ $w->id }}">
                                <span class="fw-bold d-block text-dark" style="font-size: 14px;">{{ $w->warehouse_name }}</span>
                                <span class="text-muted d-block" style="font-size: 11px;">
                                    <i class="las la-map-marker"></i> {{ $w->location ?? 'Main Storage' }}
                                </span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">Update Configuration</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        let currentBranchId = null;
        let originalCheckedState = {};

        $('#mapModal').on('show.bs.modal', function (e) {
            var btn = $(e.relatedTarget);
            currentBranchId = btn.data('branch-id');
            var branchName = btn.data('branch-name');
            var raw = btn.data('warehouses') || '';
            
            // Data cleaning for comma separated values
            var assigned = String(raw).split(',')
                            .map(v => v.trim())
                            .filter(v => v !== '' && v !== 'undefined');

            $('#modalBranchName').text(branchName);
            $('#mapForm').attr('action', '/admin/branch-warehouse/' + currentBranchId);

            // Reset and check assigned boxes
            $('.warehouse-checkbox').prop('checked', false);
            originalCheckedState = {}; // Reset original state
            assigned.forEach(function (val) {
                $('.warehouse-checkbox[value="' + val + '"]').prop('checked', true);
                originalCheckedState[val] = true;
            });
        });

        // ✅ Handle warehouse checkbox change with product check
        $(document).on('change', '.warehouse-checkbox', function() {
            var warehouseId = $(this).val();
            var isChecked = $(this).is(':checked');
            var wasChecked = originalCheckedState[warehouseId] === true;

            // If unchecking a previously assigned warehouse
            if (wasChecked && !isChecked) {
                // Check if warehouse has products
                $.ajax({
                    url: '/admin/branch-warehouse/check-products/' + currentBranchId + '/' + warehouseId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.has_products && response.products.length > 0) {
                            // Show sweet alert with product list
                            let productHTML = '<div style="text-align: left; margin: 15px 0;">';
                            productHTML += '<strong style="display: block; margin-bottom: 10px; color: #d32f2f;">⚠️ Products Available in This Warehouse:</strong>';
                            productHTML += '<table style="width: 100%; border-collapse: collapse; font-size: 13px;">';
                            productHTML += '<tr style="background: #f5f5f5; border-bottom: 1px solid #ddd;"><th style="padding: 8px; border: 1px solid #ddd;">Item Code</th><th style="padding: 8px; border: 1px solid #ddd;">Item Name</th><th style="padding: 8px; border: 1px solid #ddd;">Qty</th></tr>';
                            
                            response.products.forEach(function(product) {
                                productHTML += '<tr style="border-bottom: 1px solid #eee;"><td style="padding: 8px; border: 1px solid #ddd;">' + product.product_code + '</td>';
                                productHTML += '<td style="padding: 8px; border: 1px solid #ddd;">' + product.product_name + '</td>';
                                productHTML += '<td style="padding: 8px; border: 1px solid #ddd; text-align: center;"><span class="badge bg-info">' + parseFloat(product.quantity).toFixed(2) + '</span></td></tr>';
                            });
                            
                            productHTML += '</table>';
                            productHTML += '<p style="margin-top: 10px; font-size: 12px; color: #666;"><strong>Total Quantity:</strong> ' + parseFloat(response.total_qty).toFixed(2) + ' pcs</p>';
                            productHTML += '</div>';

                            Swal.fire({
                                title: '⚠️ Warehouse Has Products',
                                html: '<p style="margin-bottom: 15px;"><strong>' + response.warehouse_name + '</strong> in <strong>' + response.branch_name + '</strong></p>' + productHTML,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d32f2f',
                                cancelButtonColor: '#757575',
                                confirmButtonText: 'Yes, Remove Warehouse',
                                cancelButtonText: 'Cancel',
                                width: '600px',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // User confirmed removal, keep it unchecked
                                    $(this).prop('checked', false); // Ensure it stays unchecked
                                    
                                    Swal.fire({
                                        title: 'Warehouse Removed',
                                        text: 'Warehouse will be removed from this branch on update.',
                                        icon: 'info',
                                        confirmButtonColor: '#4361ee',
                                    });
                                } else {
                                    // User cancelled, recheck the warehouse
                                    $(this).prop('checked', true);
                                }
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Could not check warehouse products. Please try again.'
                        });
                        $(this).prop('checked', true); // Revert on error
                    }
                });
            }
        });
    });
</script>
@endsection