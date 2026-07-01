@extends('admin_panel.layout.app')

@section('css')
<style>
    .form-label { font-weight: 600; font-size: 13px; color: #444; margin-bottom: 3px; }
    .section-title { font-size: 13px; font-weight: 700; color: #2c3e90; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #eef2ff; padding-bottom: 6px; margin-bottom: 15px; }
    .required-star { color: red; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 font-weight-bold"><i class="fas fa-edit text-warning mr-2"></i>Edit Complaint</h4>
            <small class="text-muted">{{ $complaint->complaint_no }}</small>
        </div>
        <a href="{{ route('complaints.show', $complaint->id) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>Back to Detail
        </a>
    </div>

    <form action="{{ route('complaints.update', $complaint->id) }}" method="POST">
        @csrf @method('PUT')

        <div class="card shadow-sm">
            <div class="card-body">

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
                @endif

                {{-- Customer --}}
                <div class="section-title"><i class="fas fa-user mr-1"></i>Customer Information</div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Customer Name <span class="required-star">*</span></label>
                        <input type="text" name="customer_name" class="form-control form-control-sm" value="{{ old('customer_name', $complaint->customer_name) }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mobile</label>
                        <input type="text" name="customer_mobile" class="form-control form-control-sm" value="{{ old('customer_mobile', $complaint->customer_mobile) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="customer_address" class="form-control form-control-sm" rows="2">{{ old('customer_address', $complaint->customer_address) }}</textarea>
                    </div>
                </div>

                {{-- Product --}}
                <div class="section-title"><i class="fas fa-box mr-1"></i>Product Information</div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="product_name" class="form-control form-control-sm" value="{{ old('product_name', $complaint->product_name) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Serial / IMEI</label>
                        <input type="text" name="product_serial" class="form-control form-control-sm" value="{{ old('product_serial', $complaint->product_serial) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Model</label>
                        <input type="text" name="product_model" class="form-control form-control-sm" value="{{ old('product_model', $complaint->product_model) }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label d-block">Complaint Scope</label>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input" type="radio" name="is_product_part" id="scope_complete" value="0" {{ old('is_product_part', $complaint->is_product_part ? '1' : '0') == '0' ? 'checked' : '' }}>
                            <label class="form-check-label font-weight-normal" for="scope_complete">Complete Product</label>
                        </div>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input" type="radio" name="is_product_part" id="scope_part" value="1" {{ old('is_product_part', $complaint->is_product_part ? '1' : '0') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label font-weight-normal" for="scope_part">Product Part</label>
                        </div>
                    </div>
                    <div class="col-md-8 mb-3" id="partNameGroup" style="display: {{ old('is_product_part', $complaint->is_product_part ? '1' : '0') == '1' ? 'block' : 'none' }};">
                        <label class="form-label">Product Part Name <span class="required-star">*</span></label>
                        <input type="text" name="product_part_name" id="product_part_name" class="form-control form-control-sm" placeholder="e.g. motor, circuit, blades..." value="{{ old('product_part_name', $complaint->product_part_name) }}" {{ old('is_product_part', $complaint->is_product_part ? '1' : '0') == '1' ? 'required' : '' }}>
                    </div>
                </div>

                {{-- Issue --}}
                <div class="section-title"><i class="fas fa-exclamation-triangle mr-1"></i>Issue & Status</div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Issue Description <span class="required-star">*</span></label>
                        <textarea name="issue_description" class="form-control" rows="3" required>{{ old('issue_description', $complaint->issue_description) }}</textarea>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status <span class="required-star">*</span></label>
                        <select name="status" class="form-control form-control-sm" id="statusSelect">
                            <option value="pending" {{ old('status', $complaint->status)=='pending'?'selected':'' }}>Pending</option>
                            <option value="in_progress" {{ old('status', $complaint->status)=='in_progress'?'selected':'' }}>In Progress</option>
                            <option value="resolved" {{ old('status', $complaint->status)=='resolved'?'selected':'' }}>Resolved</option>
                            <option value="closed" {{ old('status', $complaint->status)=='closed'?'selected':'' }}>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3" id="resTypeBlock">
                        <label class="form-label">Resolution Type</label>
                        <select name="resolution_type" class="form-control form-control-sm">
                            <option value="none" {{ old('resolution_type', $complaint->resolution_type)=='none'?'selected':'' }}>Not Specified</option>
                            <option value="exchanged" {{ old('resolution_type', $complaint->resolution_type)=='exchanged'?'selected':'' }}>Product Exchanged</option>
                            <option value="repaired" {{ old('resolution_type', $complaint->resolution_type)=='repaired'?'selected':'' }}>Product Repaired</option>
                            <option value="refunded" {{ old('resolution_type', $complaint->resolution_type)=='refunded'?'selected':'' }}>Refunded</option>
                            <option value="pending_stock" {{ old('resolution_type', $complaint->resolution_type)=='pending_stock'?'selected':'' }}>Pending Stock</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Resolved Date</label>
                        <input type="date" name="resolved_date" class="form-control form-control-sm" value="{{ old('resolved_date', $complaint->resolved_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status Note (for log)</label>
                        <input type="text" name="status_note" class="form-control form-control-sm" placeholder="Reason for status change...">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Resolution Notes</label>
                        <textarea name="resolution_notes" class="form-control form-control-sm" rows="2">{{ old('resolution_notes', $complaint->resolution_notes) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end border-top pt-3">
                    <a href="{{ route('complaints.show', $complaint->id) }}" class="btn btn-secondary mr-2">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-warning px-5">
                        <i class="fas fa-save mr-1"></i>Save Changes
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $('#statusSelect').on('change', function() {
        if (['resolved', 'closed'].includes($(this).val())) {
            $('#resTypeBlock').show();
        } else {
            $('#resTypeBlock').hide();
        }
    }).trigger('change');

    $('input[name="is_product_part"]').on('change', function() {
        if ($(this).val() === '1') {
            $('#partNameGroup').slideDown(200);
            $('#product_part_name').prop('required', true);
        } else {
            $('#partNameGroup').slideUp(200);
            $('#product_part_name').prop('required', false).val('');
        }
    });
});
</script>
@endsection
