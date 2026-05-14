@extends('admin_panel.layout.app')


<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }

    body { background-color: #f8fafc; }
    .main-content { background-color: #f8fafc; min-height: 100vh; padding: 2rem 1.5rem; }
    
    .section-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.75rem; display: block; }
    
    /* Select2 Adjustments for Premium UI */
    .select2-container .select2-selection--single { height: 45px; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; background-color: #f8fafc; display: flex; align-items: center; padding: 0 1rem; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 43px; right: 10px; }
    
    .select-grn-container { background: white; padding: 2.5rem; border-radius: 1.5rem; box-shadow: var(--card-shadow); margin-bottom: 2rem; border-left: 5px solid #4f46e5; max-width: 650px; margin: 60px auto; }
</style>

@section('content')
@can('purchase.create')
<div class="main-content">
    <div class="container-fluid">
        <!-- TOP GRN SELECTION -->
        <div class="row justify-content-center g-4 mt-5">
            <!-- 🏢 COMPANY PURCHASE CARD -->
            <div class="col-md-5">
                <div class="select-grn-container h-100 m-0" style="max-width: 100%; border-left-color: #4f46e5;">
                    <h4 class="mb-4 fw-bold text-center" style="color: #1e293b;">
                        <i class="fas fa-building me-2 text-primary"></i> Company Purchase
                    </h4>
                    <p class="text-muted text-center mb-4">Stock arrives from the company via an Inward Gatepass first.</p>
                    
                    <div class="mb-4">
                        <label class="section-label mb-2 text-center d-block">Select Gatepass to Bill</label>
                        <select id="select_grn" class="form-select select2" style="width: 100%;">
                            <option value="">-- Search Inward Gatepass --</option>
                            @foreach($inwardGatepasses as $gp)
                                <option value="{{ $gp->id }}">GRN #{{ $gp->id }} | {{ $gp->vendor->name ?? 'Unknown' }} | {{ \Carbon\Carbon::parse($gp->created_at)->format('d M Y') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="text-center mt-auto pt-4 border-top">
                        <a href="{{ route('add_inwardgatepass') }}" class="btn btn-outline-primary fw-bold w-100">
                            <i class="fa fa-plus-circle me-1"></i> New Inward Gatepass
                        </a>
                    </div>
                </div>
            </div>

            <!-- 🛒 LOCAL PURCHASE CARD -->
            <div class="col-md-5">
                <div class="select-grn-container h-100 m-0" style="max-width: 100%; border-left-color: #10b981;">
                    <h4 class="mb-4 fw-bold text-center" style="color: #1e293b;">
                        <i class="fas fa-shopping-basket me-2 text-success"></i> Local Purchase
                    </h4>
                    <p class="text-muted text-center mb-4">Buy directly from local market. Stock is added to inventory immediately.</p>
                    
                    <div class="d-grid gap-3 mt-auto pt-4 border-top" style="margin-top: 85px !important;">
                        <a href="{{ route('purchase.addLocal') }}" class="btn btn-success fw-bold text-white shadow-sm py-3" style="border-radius: 12px; font-size: 1.1rem;">
                            <i class="fa fa-cart-plus me-2"></i> CREATE DIRECT PURCHASE
                        </a>
                        <small class="text-center text-muted">No Inward Gatepass required for this flow.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('Purchase.home') }}" class="btn btn-link text-secondary fw-bold text-decoration-none">
                <i class="fa fa-arrow-left me-1"></i> Back to Purchase List
            </a>
        </div>
    </div>
</div>

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });
        
        // ✅ INWARD GATEPASS REDIRECT LOGIC
        $('#select_grn').on('change', function() {
            var gatepassId = $(this).val();
            if(gatepassId) {
                var url = "{{ url('inward-gatepass') }}/" + gatepassId + "/add-bill";
                window.location.href = url;
            }
        });
    });
</script>
@endsection
@else
    <div class="container py-4">
        <div class="alert alert-danger">You do not have permission to create Purchases.</div>
    </div>
@endcan
@endsection