@extends('admin_panel.layout.app')

@section('css')
<style>
    .scenario-tab { cursor: pointer; border: 2px solid #dee2e6; border-radius: 10px; padding: 15px 20px; text-align: center; transition: all 0.3s; margin-bottom: 10px; }
    .scenario-tab:hover, .scenario-tab.active { border-color: #2c3e90; background: #eef2ff; color: #2c3e90; }
    .scenario-tab.active { box-shadow: 0 4px 12px rgba(44,62,144,0.15); }
    .scenario-tab i { font-size: 24px; display: block; margin-bottom: 5px; }
    .scenario-tab.tab-walkin.active  { border-color: #1e6fa5; background: #e8f4fd; color: #1e6fa5; }
    .scenario-tab.tab-remote.active  { border-color: #1d6b2c; background: #e8f8e8; color: #1d6b2c; }
    .scenario-tab.tab-home.active    { border-color: #a04000; background: #fdf0e8; color: #a04000; }
    .section-title { font-size: 13px; font-weight: 700; color: #2c3e90; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #eef2ff; padding-bottom: 6px; margin-bottom: 15px; }
    .form-label { font-weight: 600; font-size: 13px; color: #444; margin-bottom: 3px; }
    .required-star { color: red; }
    #homeServiceSection { display: none; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 font-weight-bold text-dark">
                <i class="fas fa-plus-circle text-primary mr-2"></i>Register New Complaint
            </h4>
            <small class="text-muted">Select scenario and fill complaint details</small>
        </div>
        <a href="{{ route('complaints.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>Back to List
        </a>
    </div>

    <form action="{{ route('complaints.store') }}" method="POST" enctype="multipart/form-data" id="complaintForm">
        @csrf

        {{-- Hidden scenario type --}}
        <input type="hidden" name="scenario_type" id="scenario_type" value="walk_in">

        <div class="row">
            {{-- LEFT: Scenario Selector --}}
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-dark text-white py-2">
                        <i class="fas fa-list mr-1"></i> Select Scenario
                    </div>
                    <div class="card-body">

                        <div class="scenario-tab tab-walkin active" data-scenario="walk_in" id="tab_walk_in">
                            <i class="fas fa-store text-primary"></i>
                            <div class="font-weight-bold">Walk-in (Shop)</div>
                            <small class="text-muted">Customer brings defective product to shop</small>
                        </div>

                        <div class="scenario-tab tab-remote" data-scenario="remote" id="tab_remote">
                            <i class="fas fa-building text-success"></i>
                            <div class="font-weight-bold">Company Complaint</div>
                            <small class="text-muted">Complaint received from head office or company channels</small>
                        </div>

                        <div class="scenario-tab tab-home" data-scenario="home_service" id="tab_home_service">
                            <i class="fas fa-home" style="color:#a04000;"></i>
                            <div class="font-weight-bold">Home Service</div>
                            <small class="text-muted">Technician visit at customer's place</small>
                        </div>

                    </div>

                    {{-- Scenario Info Box --}}
                    <div class="card-footer bg-light">
                        <div id="scenarioInfo" class="small text-muted">
                            <i class="fas fa-info-circle text-info mr-1"></i>
                            <span id="scenarioInfoText">Customer arrives at shop with defective product. Barcode sticker will be generated for product and receipt.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Form --}}
            <div class="col-md-9">
                <div class="card shadow-sm">
                    <div class="card-body">

                        {{-- Validation Errors --}}
                        @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-1">
                                @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                        @endif

                        {{-- Branch + Date --}}
                        <div class="section-title"><i class="fas fa-building mr-1"></i>General Information</div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Branch <span class="required-star">*</span></label>
                                <select name="branch_id" class="form-control form-control-sm" required id="branch_select">
                                    @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ ($user->branch_id == $branch->id || old('branch_id') == $branch->id) ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Complaint Date <span class="required-star">*</span></label>
                                <input type="date" name="complaint_date" class="form-control form-control-sm" value="{{ old('complaint_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Photo (Optional)</label>
                                <input type="file" name="photo_path" class="form-control form-control-sm" accept="image/*">
                                <small class="text-muted">Max 4MB. For remote complaints, photo of defective product.</small>
                            </div>
                        </div>

                        {{-- Customer Info --}}
                        <div class="section-title"><i class="fas fa-user mr-1"></i>Customer Information</div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Existing Customer (Optional)</label>
                                <select name="customer_id" id="customer_select" class="form-control form-control-sm select2">
                                    <option value="">-- Search existing customer --</option>
                                    @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}"
                                        data-name="{{ $cust->customer_name }}"
                                        data-mobile="{{ $cust->mobile }}"
                                        data-address="{{ $cust->address }}"
                                        {{ old('customer_id') == $cust->id ? 'selected' : '' }}>
                                        {{ $cust->customer_name }} ({{ $cust->mobile }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Customer Name <span class="required-star">*</span></label>
                                <input type="text" name="customer_name" class="form-control form-control-sm" placeholder="Full name" value="{{ old('customer_name') }}" required id="cust_name">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Mobile Number</label>
                                <input type="text" name="customer_mobile" class="form-control form-control-sm" placeholder="03xx-xxxxxxx" value="{{ old('customer_mobile') }}" id="cust_mobile">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="customer_address" class="form-control form-control-sm" rows="2" placeholder="Customer's full address" id="cust_address">{{ old('customer_address') }}</textarea>
                            </div>
                        </div>

                        {{-- Product Info --}}
                        <div class="section-title"><i class="fas fa-box mr-1"></i>Product Information</div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Select from Catalog (Optional)</label>
                                <select name="product_id" id="product_select" class="form-control form-control-sm select2">
                                    <option value="">-- Search product --</option>
                                    @foreach($products as $prod)
                                    <option value="{{ $prod->id }}"
                                        data-name="{{ $prod->item_name }}"
                                        {{ old('product_id') == $prod->id ? 'selected' : '' }}>
                                        {{ $prod->item_name }} ({{ $prod->item_code }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="product_name" class="form-control form-control-sm" placeholder="Product name" value="{{ old('product_name') }}" id="prod_name">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Serial / IMEI</label>
                                <input type="text" name="product_serial" class="form-control form-control-sm" placeholder="Serial no." value="{{ old('product_serial') }}">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Model</label>
                                <input type="text" name="product_model" class="form-control form-control-sm" placeholder="Model no." value="{{ old('product_model') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label d-block">Complaint Scope</label>
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="radio" name="is_product_part" id="scope_complete" value="0" {{ old('is_product_part', '0') == '0' ? 'checked' : '' }}>
                                    <label class="form-check-label font-weight-normal" for="scope_complete">Complete Product</label>
                                </div>
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="radio" name="is_product_part" id="scope_part" value="1" {{ old('is_product_part') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label font-weight-normal" for="scope_part">Product Part</label>
                                </div>
                            </div>
                            <div class="col-md-8 mb-3" id="partNameGroup" style="display: {{ old('is_product_part') == '1' ? 'block' : 'none' }};">
                                <label class="form-label">Product Part Name <span class="required-star">*</span></label>
                                <input type="text" name="product_part_name" id="product_part_name" class="form-control form-control-sm" placeholder="e.g. motor, circuit, blades..." value="{{ old('product_part_name') }}" {{ old('is_product_part') == '1' ? 'required' : '' }}>
                            </div>
                        </div>

                        {{-- Issue Description --}}
                        <div class="section-title"><i class="fas fa-exclamation-triangle mr-1"></i>Issue Description</div>
                        <div class="mb-3">
                            <textarea name="issue_description" class="form-control" rows="4" placeholder="Describe the issue in detail..." required>{{ old('issue_description') }}</textarea>
                        </div>

                        {{-- Home Service Section (conditional) --}}
                        <div id="homeServiceSection">
                            <div class="section-title" style="color:#a04000; border-color:#fdf0e8;">
                                <i class="fas fa-home mr-1"></i>Home Service Visit Details
                            </div>
                            <div class="alert alert-warning py-2 mb-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                Fill technician and visit details for the home service visit.
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Technician Name <span class="required-star">*</span></label>
                                    <input type="text" name="technician_name" class="form-control form-control-sm" placeholder="Technician name" value="{{ old('technician_name') }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Visit Date <span class="required-star">*</span></label>
                                    <input type="date" name="visit_date" class="form-control form-control-sm" value="{{ old('visit_date', date('Y-m-d')) }}">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Visit Time</label>
                                    <input type="time" name="visit_time" class="form-control form-control-sm" value="{{ old('visit_time') }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Visiting Charges (Rs.)</label>
                                    <input type="number" name="visiting_charges" class="form-control form-control-sm" placeholder="0" value="{{ old('visiting_charges', 0) }}" min="0" step="0.01">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Visit Notes</label>
                                    <textarea name="visit_notes" class="form-control form-control-sm" rows="2" placeholder="Any notes about the visit...">{{ old('visit_notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-3 border-top pt-3">
                            <a href="{{ route('complaints.index') }}" class="btn btn-secondary mr-2">
                                <i class="fas fa-times mr-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-5" id="submitBtn">
                                <i class="fas fa-save mr-1"></i>Register Complaint &amp; Generate Barcode
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {

    // Initialize Select2
    $('.select2').select2({ placeholder: '-- Search --', allowClear: true });

    // Scenario tab switch
    const scenarioInfo = {
        walk_in:      'Customer arrives at shop with defective product. Barcode stickers will be generated for product and receipt.',
        remote:       'Complaint received via company or official channels. Fill in details. Share complaint slip.',
        home_service: 'Technician will visit customer\'s home. Fill technician name, visit date, and visiting charges.'
    };

    $('.scenario-tab').on('click', function() {
        const scenario = $(this).data('scenario');

        // Update active tab
        $('.scenario-tab').removeClass('active');
        $(this).addClass('active');

        // Update hidden input
        $('#scenario_type').val(scenario);

        // Show/hide home service section
        if (scenario === 'home_service') {
            $('#homeServiceSection').slideDown(300);
        } else {
            $('#homeServiceSection').slideUp(300);
        }

        // Update info text
        $('#scenarioInfoText').text(scenarioInfo[scenario]);

        // Update submit button
        if (scenario === 'home_service') {
            $('#submitBtn').html('<i class="fas fa-save mr-1"></i>Register & Schedule Visit');
        } else if (scenario === 'remote') {
            $('#submitBtn').html('<i class="fas fa-save mr-1"></i>Register Company Complaint');
        } else {
            $('#submitBtn').html('<i class="fas fa-save mr-1"></i>Register Complaint & Generate Barcode');
        }
    });

    // Auto-fill customer details when existing customer selected
    $('#customer_select').on('change', function() {
        const selected = $(this).find(':selected');
        if (selected.val()) {
            $('#cust_name').val(selected.data('name'));
            $('#cust_mobile').val(selected.data('mobile'));
            $('#cust_address').val(selected.data('address'));
        }
    });

    // Auto-fill product name when catalog product selected
    $('#product_select').on('change', function() {
        const selected = $(this).find(':selected');
        if (selected.val()) {
            $('#prod_name').val(selected.data('name'));
        }
    });

    // Toggle Product Part Name field based on radio selection
    $('input[name="is_product_part"]').on('change', function() {
        if ($(this).val() === '1') {
            $('#partNameGroup').slideDown(200);
            $('#product_part_name').prop('required', true);
        } else {
            $('#partNameGroup').slideUp(200);
            $('#product_part_name').prop('required', false).val('');
        }
    });

    // Form submit with confirm
    $('#complaintForm').on('submit', function() {
        $('#submitBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i>Registering...').prop('disabled', true);
    });

});
</script>
@endsection
