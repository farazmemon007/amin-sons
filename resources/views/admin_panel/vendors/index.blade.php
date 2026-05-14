@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #858796;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --light-bg: #f8f9fc;
        --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .main-content {
        background-color: var(--light-bg);
        min-height: 100vh;
        padding: 1.5rem;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        background: #fff;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: var(--card-shadow);
    }

    .page-title h4 {
        font-weight: 700;
        color: #333;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .page-title h4 i {
        color: var(--primary-color);
    }

    /* Card Styling */
    .premium-card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: var(--card-shadow);
        background: #fff;
        margin-bottom: 1.5rem;
    }

    .card-header-premium {
        background: #f1f4f9;
        border-bottom: 1px solid #e3e6f0;
        padding: 1rem 1.5rem;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }

    .card-header-premium h6 {
        margin: 0;
        font-weight: 700;
        color: var(--primary-color);
    }

    /* Table Styling */
    .table-container {
        padding: 1.5rem;
    }

    .table thead th {
        background: #f1f4f9;
        color: #444;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.025em;
        padding: 1.1rem 0.75rem;
        border: none;
    }

    .table tbody td {
        padding: 1.1rem 0.75rem;
        vertical-align: middle;
        font-size: 0.95rem;
        color: #444;
        border-bottom: 1px solid #f1f4f9;
    }

    .table tbody tr:hover {
        background-color: #f8f9fc;
        transform: scale(1.002);
        transition: all 0.2s;
    }

    /* Button Styling */
    .btn-premium {
        border-radius: 0.5rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        transition: all 0.2s;
    }

    .btn-add {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: #fff;
        border: none;
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
        color: #fff;
    }

    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.4rem;
        margin: 0 2px;
    }

    /* Badge Styling */
    .badge-branch {
        background: rgba(78, 115, 223, 0.1);
        color: var(--primary-color);
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-weight: 700;
        font-size: 0.85rem;
    }

    /* Filter Section */
    .filter-section {
        background: #fff;
        padding: 1.25rem;
        border-radius: 0.75rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 1.5rem;
    }

    .filter-label {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--secondary-color);
        text-transform: uppercase;
        margin-bottom: 0.4rem;
    }

    .form-label-custom {
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--secondary-color);
        margin-bottom: 0.6rem;
    }

    /* Modal Styling */
    .modal-content {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 1rem 3rem rgba(0,0,0,0.2);
    }

    .modal-header {
        background: #f1f4f9;
        border-bottom: 1px solid #e3e6f0;
        border-top-left-radius: 1rem;
        border-top-right-radius: 1rem;
    }

    .fi-custom {
        border-radius: 0.5rem;
        border: 1.5px solid #e3e6f0;
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
    }

    .fi-custom:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1);
    }
</style>

<div class="main-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h4><i class="fas fa-users"></i> Vendor Management</h4>
            </div>
            <div class="page-btn">
                <button class="btn btn-premium btn-add" data-toggle="modal" data-target="#vendorModal" onclick="clearVendor()">
                    <i class="fas fa-plus-circle mr-2"></i> Add New Vendor
                </button>
                <a href="{{ url('vendors-ledger') }}" class="btn btn-premium btn-outline-danger ml-2">
                    <i class="fas fa-book mr-2"></i> Ledger
                </a>
                <a href="{{ route('vendor.payments') }}" class="btn btn-premium btn-outline-primary ml-2">
                    <i class="fas fa-money-bill-wave mr-2"></i> Payments
                </a>
                <a href="{{ url('vendor/bilties') }}" class="btn btn-premium btn-outline-info ml-2">
                    <i class="fas fa-truck mr-2"></i> Bilty
                </a>
            </div>
        </div>

        <!-- Filter Section for Super Admin -->
        @if($isSuperAdmin)
        <div class="filter-section animated fadeIn">
            <form action="{{ url('vendorlist') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label-custom">Filter by Branch</label>
                    <select name="branch_id" class="form-select select2">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 btn-premium">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ url('vendorlist') }}" class="btn btn-outline-secondary w-100 btn-premium">
                        <i class="fas fa-sync-alt mr-2"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        @endif

        <!-- Vendor Table Card -->
        <div class="premium-card animated fadeInUp">
            <div class="card-header-premium">
                <h6><i class="fas fa-list me-2"></i> Registered Vendors</h6>
            </div>
            <div class="table-container">
                @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-check-circle mr-2"></i> Success!</strong> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif

                <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                 <th class="text-center" style="width: 50px;">#</th>
                                 <th>Name</th>
                                 <th>Companies / Brands</th>
                                 <th>Branch</th>
                                 <th>Phone</th>
                                 <th class="text-end">Outstanding Balance</th>
                                 <th class="text-center" style="width: 120px;">Action</th>
                             </tr>
                        </thead>
                        <tbody>
                            @foreach($vendors as $key => $v)
                            <tr>
                                <td class="text-center fw-bold text-secondary">{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm mr-3 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            <i class="fas fa-user text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 1.1rem;">{{ $v->name }}</div>
                                            <div class="small text-muted">ID: #V-{{ str_pad($v->id, 4, '0', STR_PAD_LEFT) }}</div>
                                        </div>
                                    </div>
                                </td>
                                 <td>
                                     @if($v->company_names)
                                         <div class="mb-1">
                                             @foreach($v->company_names as $company)
                                                 <span class="badge bg-soft-info text-info border mr-1" style="font-size: 10px;">{{ $company }}</span>
                                             @endforeach
                                         </div>
                                     @endif
                                     @if($v->brand_ids)
                                         @php
                                             $vendorBrands = $allBrands->whereIn('id', $v->brand_ids)->pluck('name');
                                         @endphp
                                         <div>
                                             @foreach($vendorBrands as $brandName)
                                                 <span class="badge bg-soft-success text-success border mr-1" style="font-size: 10px;">{{ $brandName }}</span>
                                             @endforeach
                                         </div>
                                     @endif
                                 </td>
                                 <td><span class="badge-branch">{{ $v->branch->name ?? 'Global' }}</span></td>
                                 <td><i class="fas fa-phone-alt me-2 text-muted small"></i>{{ $v->phone }}</td>
                                 <td class="text-end fw-bold {{ $v->current_balance > 0 ? 'text-danger' : ($v->current_balance < 0 ? 'text-success' : 'text-primary') }}" style="font-size: 1.05rem;">{{ number_format($v->current_balance, 2) }}</td>
                                <td class="text-center">
                                     <button class="btn btn-sm btn-primary btn-action btn-edit-vendor" 
                                         data-id="{{ $v->id }}" 
                                         data-name="{{ $v->name }}"
                                         data-phone="{{ $v->phone }}"
                                         data-address="{{ $v->address }}"
                                         data-balance="{{ $v->current_balance }}"
                                         data-branch-id="{{ $v->branch_id }}"
                                         data-companies="{{ json_encode($v->company_names ?? []) }}"
                                         data-brands="{{ json_encode($v->brand_ids ?? []) }}"
                                         title="Edit Vendor">
                                         <i class="fas fa-edit"></i>
                                     </button>
                                    <a href="{{ url('vendor/delete/'.$v->id) }}" 
                                       class="btn btn-sm btn-danger btn-action" 
                                       onclick="return confirm('Are you sure you want to delete this vendor?')"
                                       title="Delete Vendor">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal for Add/Edit Vendor -->
<div class="modal fade" id="vendorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ url('vendor/store') }}" method="POST" id="vendorForm">
            @csrf
            <input type="hidden" id="vendor_id" name="id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="fas fa-user-plus mr-2"></i> Vendor Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label-custom">Vendor Name <span class="text-danger">*</span></label>
                        <input class="form-control fi-custom" name="name" id="vname" placeholder="Full Name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label-custom">Opening Balance</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">Rs.</span>
                                <input class="form-control fi-custom border-start-0" name="opening_balance" id="opening_balance" placeholder="0.00" required>
                            </div>
                        </div>
                         <div class="col-md-6 mb-3">
                             <label class="form-label-custom">Phone Number</label>
                             <input class="form-control fi-custom" name="phone" id="vphone" placeholder="Contact No">
                         </div>
                     </div>

                     <div class="mb-3">
                        <label class="form-label-custom">Companies (DWP, Xenium, etc.)</label>
                        <input type="text" name="company_names_raw" id="vcompanies_raw" class="form-control fi-custom" placeholder="Enter companies separated by commas (e.g. DWP, Xenium)">
                        <small class="text-muted">Separate multiple companies with commas</small>
                     </div>

                     <div class="mb-3">
                        <label class="form-label-custom">Brands Sold (Gree, Haier, etc.)</label>
                        <select name="brand_ids[]" id="vbrands" class="form-select select2" multiple="multiple">
                            @foreach($allBrands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                     </div>

                    @if($isSuperAdmin)
                    <div class="mb-3">
                        <label class="form-label-custom">Assign to Branch <span class="text-danger">*</span></label>
                        <select name="branch_id" id="vbranch_id" class="form-select select2" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="mb-0">
                        <label class="form-label-custom">Office Address</label>
                        <textarea class="form-control fi-custom" name="address" id="vaddress" rows="3" placeholder="Street, City, Country"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-premium" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-premium">
                        <i class="fas fa-save mr-2"></i> Save Vendor
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js')
<script>
$(document).ready(function () {
    // Initialize DataTable with premium options
    if ($('.datanew').length > 0) {
        $('.datanew').DataTable({
            "bFilter": true,
            "sDom": 'fBtlpi',
            "ordering": true,
            "language": {
                search: ' ',
                sLengthMenu: '_MENU_',
                searchPlaceholder: "Search Vendors...",
                info: "_START_ - _END_ of _TOTAL_ items",
                paginate: {
                    next: 'Next <i class="fa fa-angle-right"></i>',
                    previous: '<i class="fa fa-angle-left"></i> Previous'
                }
            },
        });
    }

    // Initialize Select2 for Branch filter (Main Page)
    $('.select2').not('#vbranch_id').select2();

    // Initialize Select2 for Branch (In Modal)
    $('#vbranch_id').select2({
        dropdownParent: $('#vendorModal')
    });

    // Initialize Select2 Multiple for Brands
    $('#vbrands').select2({
        dropdownParent: $('#vendorModal'),
        placeholder: "Select Brands"
    });

    // Clear modal fields function
    window.clearVendor = function () {
        $('#vendor_id').val('');
        $('#vname').val('');
        $('#opening_balance').val('').prop('readonly', false);
        $('#vphone').val('');
        if($('#vbranch_id').length) {
            $('#vbranch_id').val('').trigger('change');
        }
        $('#vaddress').val('');
        $('#vcompanies_raw').val('');
        $('#vbrands').val(null).trigger('change');
        $('.modal-title').html('<i class="fas fa-user-plus mr-2"></i> Add New Vendor');
    };

    // Edit Vendor functionality
    $(document).on('click', '.btn-edit-vendor', function () {
        var id       = $(this).data('id');
        var name     = $(this).data('name');
        var phone    = $(this).data('phone');
        var address  = $(this).data('address');
        var balance  = $(this).data('balance');
        var branchId = $(this).data('branch-id');
        
        // Populate modal
        $('#vendor_id').val(id);
        $('#vname').val(name);
        $('#vphone').val(phone);
        $('#opening_balance').val(balance);
        
        if($('#vbranch_id').length) {
            $('#vbranch_id').val(branchId).trigger('change');
        }
        
        $('#vaddress').val(address);
        
        // Handle Companies (Join array to string)
        var companies = $(this).data('companies');
        if (typeof companies === 'string') companies = JSON.parse(companies);
        if(companies && companies.length > 0) {
            $('#vcompanies_raw').val(companies.join(', '));
        } else {
            $('#vcompanies_raw').val('');
        }

        // Handle Brands Select
        var brands = $(this).data('brands');
        if (typeof brands === 'string') brands = JSON.parse(brands);
        $('#vbrands').val(brands).trigger('change');
        
        $('.modal-title').html('<i class="fas fa-edit mr-2"></i> Edit Vendor');

        // Show modal
        $('#vendorModal').modal('show');
    });
});
</script>
@endsection
