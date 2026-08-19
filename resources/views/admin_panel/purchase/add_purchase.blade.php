@extends('admin_panel.layout.app')

@section('css')
<style>
    :root {
        --coa-navy: #1e3a5f;
        --coa-navy-dark: #0f1f38;
        --coa-navy-light: #2c5282;
        --coa-gold: #c8973a;
        --coa-emerald: #059669;
        --coa-emerald-dark: #047857;
        --coa-border: #cbd5e1;
    }

    .pur-wrapper {
        padding: 10px 0 40px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* 1. Header Banner */
    .pur-header-bar {
        background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 60%, var(--coa-navy-light) 100%);
        border-radius: 9px;
        padding: 16px 22px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        box-shadow: 0 4px 15px rgba(15, 31, 56, 0.15);
        margin-bottom: 25px;
    }

    .pur-header-icon {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        color: var(--coa-gold);
        border: 1px solid rgba(200, 151, 58, 0.3);
        flex-shrink: 0;
    }

    .pur-header-title {
        font-size: 18px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        line-height: 1.2;
    }

    .pur-header-sub {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.85);
        margin-top: 3px;
    }

    /* 2. Corporate ERP Module Cards */
    .erp-module-card {
        background: #ffffff;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .erp-module-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(15, 31, 56, 0.09);
    }

    .module-card-header {
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #ffffff;
    }

    .header-navy {
        background: linear-gradient(135deg, #0f1f38 0%, #1e3a5f 100%);
    }

    .header-emerald {
        background: linear-gradient(135deg, #064e3b 0%, #059669 100%);
    }

    .module-card-title {
        font-size: 16px;
        font-weight: 800;
        margin: 0;
        display: flex;
        align-items: center;
        color: #ffffff !important;
    }

    .module-card-badge {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 4px 10px;
        border-radius: 50px;
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .module-card-body {
        padding: 22px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        justify-content: space-between;
    }

    .module-desc {
        font-size: 13px;
        color: #475569;
        line-height: 1.5;
        margin-bottom: 20px;
    }

    .module-info-panel {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px 16px;
        margin-bottom: 20px;
    }

    .module-info-panel.emerald-tint {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .info-item {
        font-size: 12.5px;
        color: #334155;
        padding: 5px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .info-item i {
        font-size: 14px;
        width: 16px;
        text-align: center;
    }

    .f-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #334155;
        letter-spacing: 0.04em;
        margin-bottom: 7px;
        display: block;
    }

    .or-divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 14px 0;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .or-divider::before,
    .or-divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #cbd5e1;
    }

    .or-divider:not(:empty)::before { margin-right: .6em; }
    .or-divider:not(:empty)::after { margin-left: .6em; }

    /* Select2 Tweaks */
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 6px !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 10px !important;
        font-size: 12.5px !important;
        background-color: #ffffff !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b !important;
        line-height: 36px !important;
        padding-left: 0 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }
</style>
@endsection

@section('content')
@can('purchase.create')
<div class="main-content">
    <div class="pur-wrapper">
        <div class="container-fluid px-3">

            {{-- 1. Corporate Header Bar --}}
            <div class="pur-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="pur-header-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h4 class="pur-header-title">Create Purchase Entry</h4>
                        <div class="pur-header-sub">
                            <span><i class="fas fa-boxes mr-1" style="color: var(--coa-gold);"></i> Purchase Management &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('Purchase.home') }}" class="btn btn-sm btn-outline-light font-weight-bold">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Purchase List
                    </a>
                </div>
            </div>

            {{-- 2. Module Choice Cards --}}
            <div class="row justify-content-center g-4 my-1">
                
                {{-- 🏢 COMPANY PURCHASE CARD --}}
                <div class="col-lg-5 col-md-6 mb-4">
                    <div class="erp-module-card">
                        <div class="module-card-header header-navy">
                            <h4 class="module-card-title">
                                <i class="fas fa-building mr-2" style="color: var(--coa-gold);"></i> Company Purchase
                            </h4>
                            <span class="module-card-badge">Gatepass Flow</span>
                        </div>

                        <div class="module-card-body">
                            <p class="module-desc">
                                Stock arriving from authorized mills / manufacturers against formal Purchase Orders. Requires physical verification via an <strong>Inward Gatepass</strong>.
                            </p>

                            <div class="module-info-panel">
                                <div class="info-item">
                                    <i class="fas fa-check text-primary"></i>
                                    <span>Verified against approved Purchase Orders</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-check text-primary"></i>
                                    <span>Factory freight, carriage & bill adjustments</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-check text-primary"></i>
                                    <span>Automatic posting to official Vendor Ledger</span>
                                </div>
                            </div>

                            <div class="mt-auto">
                                <label class="f-label">
                                    <i class="fas fa-barcode mr-1 text-primary"></i> Select Existing Gatepass to Bill:
                                </label>
                                <select id="select_grn" class="form-control select2" style="width: 100%;">
                                    <option value="">-- Choose Inward Gatepass (GRN) --</option>
                                    @foreach($inwardGatepasses as $gp)
                                        <option value="{{ $gp->id }}">
                                            GRN #{{ $gp->id }} &bull; {{ $gp->vendor->name ?? 'Vendor' }} &bull; {{ \Carbon\Carbon::parse($gp->created_at)->format('d-M-Y') }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="or-divider">Or Create New Gatepass</div>

                                <a href="{{ route('add_inwardgatepass') }}" class="btn btn-sm btn-outline-primary w-100 font-weight-bold py-2" style="border-radius: 6px;">
                                    <i class="fas fa-plus-circle mr-1"></i> Issue New Inward Gatepass
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 🛒 DIRECT LOCAL PURCHASE CARD --}}
                <div class="col-lg-5 col-md-6 mb-4">
                    <div class="erp-module-card">
                        <div class="module-card-header header-emerald">
                            <h4 class="module-card-title">
                                <i class="fas fa-store mr-2" style="color: #fde047;"></i> Local Market Purchase
                            </h4>
                            <span class="module-card-badge">Instant Entry</span>
                        </div>

                        <div class="module-card-body">
                            <p class="module-desc">
                                Direct spot purchases from local market shops / dealers. Stock is added to inventory <strong>immediately</strong> without requiring an Inward Gatepass.
                            </p>

                            <div class="module-info-panel emerald-tint">
                                <div class="info-item">
                                    <i class="fas fa-bolt text-success"></i>
                                    <span>Instant stock on-hand quantity increment</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-money-check-alt text-success"></i>
                                    <span>Spot cash, bank transfer or shop balance tracking</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-file-invoice text-success"></i>
                                    <span>Direct invoice generation with payment status</span>
                                </div>
                            </div>

                            <div class="mt-auto">
                                <label class="f-label text-success">
                                    <i class="fas fa-cart-plus mr-1"></i> Launch Direct Purchase Entry:
                                </label>
                                <a href="{{ route('purchase.addLocal') }}" class="btn btn-success w-100 font-weight-bold py-2 shadow-sm" style="border-radius: 6px; background: var(--coa-emerald); border-color: var(--coa-emerald); height: 38px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-plus-circle mr-1"></i> Create Direct Local Purchase
                                </a>

                                <div class="or-divider" style="visibility: hidden;">Or</div>

                                <a href="{{ route('report.local_purchase') }}" class="btn btn-sm btn-outline-secondary w-100 font-weight-bold py-2" style="border-radius: 6px;">
                                    <i class="fas fa-list-alt mr-1"></i> View Local Purchase Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@else
<div class="main-content">
    <div class="container py-5">
        <div class="alert alert-danger shadow-sm border-0" style="border-radius: 8px;">
            <i class="fas fa-exclamation-triangle mr-2"></i> You do not have permission to create Purchases.
        </div>
    </div>
</div>
@endcan
@endsection

@section('js')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({
                width: '100%'
            });
        }
        
        // Inward Gatepass Redirect
        $('#select_grn').on('change', function() {
            var gatepassId = $(this).val();
            if (gatepassId) {
                var url = "{{ url('inward-gatepass') }}/" + gatepassId + "/add-bill";
                window.location.href = url;
            }
        });
    });
</script>
@endsection