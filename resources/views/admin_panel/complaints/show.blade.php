@extends('admin_panel.layout.app')

@section('css')
<style>
    .detail-card { border-radius: 10px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 20px; }
    .detail-card .card-header { border-radius: 10px 10px 0 0; padding: 10px 18px; font-weight: 700; font-size: 14px; }
    .info-row td:first-child { font-weight: 600; color: #555; width: 35%; padding: 8px 12px; }
    .info-row td:last-child  { color: #222; padding: 8px 12px; }
    .info-row { border-bottom: 1px solid #f0f0f0; }
    .status-timeline { position: relative; padding-left: 25px; }
    .status-timeline::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: #e0e0e0; }
    .timeline-item { position: relative; padding: 8px 0 8px 20px; }
    .timeline-item::before { content: ''; position: absolute; left: -19px; top: 14px; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 0 2px #2c3e90; background: #2c3e90; }
    .badge-walkin  { background: #e8f4fd; color: #1e6fa5; border: 1px solid #90c8f0; padding: 4px 10px; border-radius: 15px; font-size: 12px; }
    .badge-remote  { background: #e8f8e8; color: #1d6b2c; border: 1px solid #7dc97d; padding: 4px 10px; border-radius: 15px; font-size: 12px; }
    .badge-home    { background: #fdf0e8; color: #a04000; border: 1px solid #e0a050; padding: 4px 10px; border-radius: 15px; font-size: 12px; }
    .status-pill   { padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; }
    .s-pending     { background: #fff3cd; color: #856404; }
    .s-in_progress { background: #cce5ff; color: #004085; }
    .s-resolved    { background: #d4edda; color: #155724; }
    .s-closed      { background: #e2e3e5; color: #383d41; }
    .barcode-box   { background: white; padding: 15px; border: 2px dashed #ccc; border-radius: 8px; text-align: center; }
    .action-top-btn { margin: 2px; }
    .visit-card { border-left: 4px solid #e67e22; border-radius: 6px; padding: 12px 15px; background: #fffaf5; margin-bottom: 10px; }
    .nav-tabs .nav-link { color: rgba(255,255,255,0.75) !important; border: none !important; transition: all 0.3s; }
    .nav-tabs .nav-link.active { background: white !important; color: #2c3e90 !important; border-bottom: 3px solid #2c3e90 !important; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3">

    {{-- Flash --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm">
        <i class="fas fa-exclamation-triangle mr-2"></i>{!! session('error') !!}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 font-weight-bold">
                <i class="fas fa-file-alt text-primary mr-2"></i>
                {{ $complaint->complaint_no }}
            </h4>
            <small class="text-muted">Complaint Detail View</small>
        </div>
        <div>
            <a href="{{ route('complaints.index') }}" class="btn btn-secondary btn-sm action-top-btn">
                <i class="fas fa-arrow-left mr-1"></i>Back
            </a>
            @can('complaint.edit')
            <a href="{{ route('complaints.edit', $complaint->id) }}" class="btn btn-warning btn-sm action-top-btn">
                <i class="fas fa-edit mr-1"></i>Edit
            </a>
            @endcan
            @can('complaint.print')
            <a href="{{ route('complaints.print-slip', $complaint->id) }}" target="_blank" class="btn btn-secondary btn-sm action-top-btn">
                <i class="fas fa-receipt mr-1"></i>Print Slip
            </a>
            <a href="{{ route('complaints.print-tag', $complaint->id) }}" target="_blank" class="btn btn-dark btn-sm action-top-btn">
                <i class="fas fa-tag mr-1"></i>Print Product Tag
            </a>
            @endcan
            <button class="btn btn-success btn-sm action-top-btn" id="whatsappBtn" data-id="{{ $complaint->id }}">
                <i class="fab fa-whatsapp mr-1"></i>WhatsApp
            </button>
        </div>
    </div>

    <div class="row">

        {{-- LEFT COLUMN --}}
        <div class="col-md-8">

            {{-- Complaint Info Card --}}
            <div class="detail-card card">
                <div class="card-header d-flex justify-content-between align-items-center"
                     style="background: linear-gradient(135deg, #2c3e90, #1e6fa5); color: white;">
                    <span><i class="fas fa-info-circle mr-2"></i>Complaint Information</span>
                    <div>
                        @if($complaint->scenario_type === 'walk_in')
                            <span class="badge-walkin"><i class="fas fa-store mr-1"></i>Walk-in</span>
                        @elseif($complaint->scenario_type === 'remote')
                            <span class="badge-remote"><i class="fas fa-building mr-1"></i>Company Complaint</span>
                        @else
                            <span class="badge-home"><i class="fas fa-home mr-1"></i>Home Service</span>
                        @endif
                        &nbsp;
                        <span class="status-pill s-{{ $complaint->status }}">
                            {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr class="info-row"><td>Complaint No</td><td><strong>{{ $complaint->complaint_no }}</strong></td></tr>
                        <tr class="info-row"><td>Date</td><td>{{ $complaint->complaint_date->format('d M Y') }}</td></tr>
                        <tr class="info-row"><td>Branch</td><td>{{ $complaint->branch->name ?? '-' }}</td></tr>
                        <tr class="info-row"><td>Registered By</td><td>{{ $complaint->createdByUser->name ?? '-' }}</td></tr>
                        @if($complaint->resolved_date)
                        <tr class="info-row"><td>Resolved Date</td><td>{{ \Carbon\Carbon::parse($complaint->resolved_date)->format('d M Y') }}</td></tr>
                        <tr class="info-row"><td>Resolved By</td><td>{{ $complaint->resolvedByUser->name ?? '-' }}</td></tr>
                        @endif
                        @if($complaint->resolution_type && $complaint->resolution_type !== 'none')
                        <tr class="info-row"><td>Resolution</td><td>
                            <span class="badge badge-success">{{ ucfirst($complaint->resolution_type) }}</span>
                        </td></tr>
                        @endif
                        @if($complaint->resolution_notes)
                        <tr class="info-row"><td>Resolution Notes</td><td>{{ $complaint->resolution_notes }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Customer Info --}}
            <div class="detail-card card">
                <div class="card-header" style="background:#e8f4fd; color:#1e6fa5;">
                    <i class="fas fa-user mr-2"></i>Customer Information
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr class="info-row"><td>Name</td><td><strong>{{ $complaint->customer_name }}</strong></td></tr>
                        <tr class="info-row"><td>Mobile</td><td>{{ $complaint->customer_mobile ?? '-' }}</td></tr>
                        <tr class="info-row"><td>Address</td><td>{{ $complaint->customer_address ?? '-' }}</td></tr>
                        @if($complaint->customer)
                        <tr class="info-row"><td>Registered Customer</td>
                            <td><a href="{{ route('customers.index') }}">{{ $complaint->customer->customer_name }}</a></td></tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Product Info --}}
            <div class="detail-card card">
                <div class="card-header" style="background:#e8f8e8; color:#1d6b2c;">
                    <i class="fas fa-box mr-2"></i>Product Information
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr class="info-row"><td>Product</td><td><strong>{{ $complaint->product_name ?? ($complaint->product->item_name ?? '-') }}</strong></td></tr>
                        <tr class="info-row"><td>Complaint Scope</td><td>
                            @if($complaint->is_product_part)
                                <span class="badge badge-warning">Product Part</span>
                            @else
                                <span class="badge badge-success">Complete Product</span>
                            @endif
                        </td></tr>
                        @if($complaint->is_product_part && $complaint->product_part_name)
                        <tr class="info-row"><td>Product Part Name</td><td><strong>{{ $complaint->product_part_name }}</strong></td></tr>
                        @endif
                        <tr class="info-row"><td>Model</td><td>{{ $complaint->product_model ?? '-' }}</td></tr>
                        <tr class="info-row"><td>Serial / IMEI</td><td>{{ $complaint->product_serial ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>

            @php
                $showChangeTab = ($complaint->resolution_type === 'exchanged' || $complaint->replacements->count() > 0);
                $activeTab = $showChangeTab ? 'change' : 'repair';
            @endphp

            {{-- Tabs: Product Repair vs Product Change --}}
            <div class="detail-card card shadow-sm">
                <div class="card-header p-0" style="background: linear-gradient(135deg, #2c3e90, #1e6fa5);">
                    <ul class="nav nav-tabs border-0" id="complaintActionTabs" role="tablist">
                        <li class="nav-item m-0">
                            <a class="nav-link {{ $activeTab === 'repair' ? 'active' : '' }} text-white py-3 px-4 border-0 font-weight-bold" 
                               id="repair-tab" data-toggle="tab" href="#repair-section" role="tab" 
                               style="border-radius: 10px 0 0 0; background: rgba(255,255,255,0.1);">
                                <i class="fas fa-tools mr-1"></i> Product Repair
                            </a>
                        </li>
                        @if($showChangeTab)
                        <li class="nav-item m-0">
                            <a class="nav-link {{ $activeTab === 'change' ? 'active' : '' }} text-white py-3 px-4 border-0 font-weight-bold" 
                               id="change-tab" data-toggle="tab" href="#change-section" role="tab" 
                               style="border-radius: 0; background: rgba(255,255,255,0.1);">
                                <i class="fas fa-exchange-alt mr-1"></i> Product Change
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="complaintActionTabsContent">
                        
                        {{-- Tab 1: Product Repair --}}
                        <div class="tab-pane fade {{ $activeTab === 'repair' ? 'show active' : '' }}" id="repair-section" role="tabpanel" aria-labelledby="repair-tab">
                            <div class="mb-3">
                                <h6 class="font-weight-bold text-muted small text-uppercase">Customer Reported Issue</h6>
                                <div class="p-3 bg-light rounded border" style="white-space:pre-wrap; font-size:14px; color:#333;"><strong>{{ $complaint->issue_description }}</strong></div>
                            </div>
                            
                            @if($complaint->photo_path)
                            <div class="mb-3">
                                <h6 class="font-weight-bold text-muted small text-uppercase">Product Photo</h6>
                                <img src="{{ asset('storage/' . $complaint->photo_path) }}" style="max-width:250px; border-radius:8px; border:1px solid #ddd;">
                            </div>
                            @endif

                            <hr>

                            @if(in_array($complaint->status, ['pending', 'in_progress']))
                                <h6 class="font-weight-bold text-success mb-3"><i class="fas fa-tools mr-1"></i> Log Repair & Resolve Complaint</h6>
                                @can('complaint.edit')
                                <form action="{{ route('complaints.resolve-repair', $complaint->id) }}" method="POST" id="resolveRepairForm" class="bg-light p-3 border rounded shadow-sm" novalidate>
                                    @csrf

                                    {{-- Validation Errors --}}
                                    @if($errors->any())
                                    <div class="alert alert-danger py-2 mb-3">
                                        <strong><i class="fas fa-exclamation-circle mr-1"></i>Please fix the following errors:</strong>
                                        <ul class="mb-0 mt-1 pl-3">
                                            @foreach($errors->all() as $error)
                                                <li class="small">{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold d-block small text-muted text-uppercase mb-2">Repair Action / Status <span class="text-danger">*</span></label>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input class="custom-control-input repair-action-radio" type="radio" name="repair_action" id="action_repaired" value="repaired" checked required>
                                            <label class="custom-control-label font-weight-bold text-success" for="action_repaired" style="cursor: pointer;">
                                                <i class="fas fa-check-circle mr-1"></i> Repaired Successfully
                                            </label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline ml-3">
                                            <input class="custom-control-input repair-action-radio" type="radio" name="repair_action" id="action_unrepairable" value="unrepairable" required>
                                            <label class="custom-control-label font-weight-bold text-danger" for="action_unrepairable" style="cursor: pointer;">
                                                <i class="fas fa-times-circle mr-1"></i> Unrepairable - Exchange Product/Part
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold small text-muted text-uppercase mb-1">Technical Diagnostic Details / Notes <span class="text-danger">*</span></label>
                                        <textarea name="resolution_notes" id="resolution_notes" class="form-control form-control-sm" rows="3" placeholder="Describe technical diagnostic details, diagnostic report, or work done..."></textarea>
                                    </div>

                                    <div class="row mb-1" id="repair_price_wrapper">
                                        <div class="col-md-6 form-group mb-3">
                                            <label class="font-weight-bold small text-muted text-uppercase mb-1">Repair Price / Charges (Rs.)</label>
                                            <input type="number" name="repair_price" class="form-control form-control-sm" value="0" min="0" step="0.01" placeholder="0.00">
                                            <small class="text-muted">Leave empty or 0 if free under warranty.</small>
                                        </div>
                                    </div>

                                    {{-- Exchange Fields Wrapper (only shown when action_unrepairable is checked) --}}
                                    <div id="exchange_fields_wrapper" class="d-none mt-3 p-3 border rounded bg-white shadow-sm">
                                        <h6 class="font-weight-bold text-danger mb-3 border-bottom pb-2">
                                            <i class="fas fa-exchange-alt mr-1"></i> Product Exchange &amp; Inventory Details
                                        </h6>
                                        
                                        <div class="row">
                                            {{-- Product to issue --}}
                                            <div class="col-md-8 mb-3">
                                                <label class="font-weight-bold small text-muted text-uppercase mb-1">Product to Issue <span class="text-danger">*</span></label>
                                                <select name="issued_product_id" id="exchange_product_select" class="form-control form-control-sm select2-standard" style="width:100%;">
                                                    <option value="">-- Search product catalog --</option>
                                                    @foreach($productsList as $p)
                                                    <option value="{{ $p->id }}" {{ $complaint->product_id == $p->id ? 'selected' : '' }}>
                                                        {{ $p->item_name }} ({{ $p->item_code }})
                                                    </option>
                                                    @endforeach
                                                </select>
                                                
                                                {{-- Is Part Checkbox --}}
                                                <div class="custom-control custom-checkbox mt-2">
                                                    <input type="checkbox" name="is_issued_part" id="is_issued_part_check" value="1" class="custom-control-input">
                                                    <label class="custom-control-label small font-weight-bold text-muted" for="is_issued_part_check" style="cursor: pointer;">
                                                        Exchange a specific Part instead of the whole Product?
                                                    </label>
                                                </div>
                                                
                                                {{-- Issued Part Name Text Input --}}
                                                <div id="issued_part_name_wrapper" class="d-none mt-2">
                                                    <label class="font-weight-bold small text-danger text-uppercase mb-1">Issued Part Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="issued_part_name" id="issued_part_name" class="form-control form-control-sm" placeholder="e.g. motor, circuit, blades...">
                                                </div>
                                            </div>

                                            {{-- Quantity to Issue --}}
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-bold small text-muted text-uppercase mb-1">Quantity to Issue <span class="text-danger">*</span></label>
                                                <input type="number" name="quantity" id="exchange_quantity" class="form-control form-control-sm" value="1" min="1" step="1">
                                            </div>
                                        </div>

                                        <hr>

                                        {{-- Collect Damaged Checkbox --}}
                                        <div class="mb-3">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="collect_damaged" id="exchange_collect_damaged_check" value="1" class="custom-control-input">
                                                <label class="custom-control-label font-weight-bold text-danger" for="exchange_collect_damaged_check" style="cursor: pointer;">
                                                    Collect Defective / Damaged Part from Customer?
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Damaged Collection Section --}}
                                        <div class="border rounded p-3 bg-light" id="exchange_damaged_collection_section">
                                            <div class="row mb-0">
                                                <div class="col-md-8 mb-2">
                                                    <label class="font-weight-bold small text-muted text-uppercase mb-1">Collected Damaged Product <span class="text-danger">*</span></label>
                                                    <select name="collected_damaged_product_id" id="exchange_damaged_product_select" class="form-control form-control-sm select2-standard" style="width:100%;">
                                                        <option value="">-- Search defective catalog --</option>
                                                        @foreach($productsList as $p)
                                                        <option value="{{ $p->id }}" {{ $complaint->product_id == $p->id ? 'selected' : '' }}>
                                                            {{ $p->item_name }} ({{ $p->item_code }})
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    
                                                    {{-- Collect Part Checkbox --}}
                                                    <div class="custom-control custom-checkbox mt-2">
                                                        <input type="checkbox" name="is_collected_part" id="is_collected_part_check" value="1" class="custom-control-input">
                                                        <label class="custom-control-label small font-weight-bold text-muted" for="is_collected_part_check" style="cursor: pointer;">
                                                            Collected a specific Part instead of the whole Product?
                                                        </label>
                                                    </div>
                                                    
                                                    {{-- Collected Part Name Text Input --}}
                                                    <div id="collected_part_name_wrapper" class="d-none mt-2">
                                                        <label class="font-weight-bold small text-danger text-uppercase mb-1">Collected Part Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="collected_part_name" id="collected_part_name" class="form-control form-control-sm" placeholder="e.g. motor, circuit, blades...">
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label class="font-weight-bold small text-muted text-uppercase mb-1">Damaged Qty Collected <span class="text-danger">*</span></label>
                                                    <input type="number" name="damaged_qty" id="exchange_damaged_qty_input" class="form-control form-control-sm" value="1" min="1" step="1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-success btn-sm px-4">
                                            <i class="fas fa-check-circle mr-1"></i>Submit Details &amp; Resolve Complaint
                                        </button>
                                    </div>
                                </form>
                                @else
                                <div class="alert alert-info py-2 small">
                                    <i class="fas fa-info-circle mr-1"></i> You do not have permission to resolve complaints.
                                </div>
                                @endcan
                            @else
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="font-weight-bold text-primary mb-0"><i class="fas fa-file-invoice mr-1"></i> Repair Action & Resolution</h6>
                                </div>

                                <table class="table table-sm table-bordered" style="font-size:13px;">
                                    <tr>
                                        <td class="font-weight-bold bg-light" style="width: 30%;">Resolution Action</td>
                                        <td>
                                            @if($complaint->resolution_type && $complaint->resolution_type !== 'none')
                                                <span class="badge badge-success">{{ ucfirst($complaint->resolution_type) }}</span>
                                            @else
                                                <span class="badge badge-warning">Under Review</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold bg-light">Technical Repair Notes</td>
                                        <td>{{ $complaint->resolution_notes ?? 'No repair/resolution notes logged yet.' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold bg-light">Repair Charges / Price</td>
                                        <td class="font-weight-bold text-success">
                                            @if((float)($complaint->repair_price) > 0)
                                                Rs. {{ number_format($complaint->repair_price, 2) }}
                                            @else
                                                <span class="text-muted">Free / Under Warranty (Rs. 0.00)</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($complaint->resolved_date)
                                    <tr>
                                        <td class="font-weight-bold bg-light">Resolved Date</td>
                                        <td>{{ \Carbon\Carbon::parse($complaint->resolved_date)->format('d M Y') }}</td>
                                    </tr>
                                    @endif
                                    @if($complaint->resolvedByUser)
                                    <tr>
                                        <td class="font-weight-bold bg-light">Resolved By</td>
                                        <td>{{ $complaint->resolvedByUser->name }}</td>
                                    </tr>
                                    @endif
                                </table>
                            @endif
                        </div>

                        @if($showChangeTab)
                        {{-- Tab 2: Product Change --}}
                        <div class="tab-pane fade {{ $activeTab === 'change' ? 'show active' : '' }}" id="change-section" role="tabpanel" aria-labelledby="change-tab">
                            
                            @php
                                $pendingSlip = $complaint->replacements->where('claim_status', 'pending')->first();
                            @endphp

                            @if($pendingSlip)
                                <div class="card bg-light border-warning mb-3 shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                                            <div>
                                                <h6 class="text-warning font-weight-bold mb-1">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i> Pending Replacement Slip: <strong class="text-danger">{{ $pendingSlip->replacement_slip_no }}</strong>
                                                </h6>
                                                <p class="mb-0 text-muted small">
                                                    Defective item collected: <strong>{{ $pendingSlip->collectedDamagedProduct->item_name ?? 'Defective Product' }}</strong>
                                                    @if($pendingSlip->is_collected_part && $pendingSlip->collected_part_name)
                                                        (Part: <strong class="text-danger">{{ $pendingSlip->collected_part_name }}</strong>)
                                                    @endif. 
                                                    The customer holds this slip code and is eligible to claim: <strong>{{ $pendingSlip->issuedProduct->item_name ?? 'Product' }}</strong>
                                                    @if($pendingSlip->is_issued_part && $pendingSlip->issued_part_name)
                                                        (Part: <strong class="text-success">{{ $pendingSlip->issued_part_name }}</strong>)
                                                    @endif at the shop counter.
                                                </p>
                                            </div>
                                            @can('complaint.edit')
                                            <div class="mt-2 mt-md-0 d-flex flex-wrap gap-2">
                                                <a href="{{ route('complaints.replacements.print-slip', $pendingSlip->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm font-weight-bold mr-2">
                                                    <i class="fas fa-print mr-1"></i>Print Slip
                                                </a>
                                                <button class="btn btn-warning btn-sm font-weight-bold text-dark" data-toggle="modal" data-target="#claimReplacementModal">
                                                    <i class="fas fa-store mr-1"></i>Release Clean Item
                                                </button>
                                            </div>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="font-weight-bold text-primary mb-0"><i class="fas fa-exchange-alt mr-1"></i> Replacement Parts & Defective Items Log</h6>
                                @can('complaint.edit')
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#replacementModal">
                                    <i class="fas fa-plus mr-1"></i>Issue Replacement
                                </button>
                                @endcan
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0" style="font-size:12px;">
                                    <thead>
                                        <tr class="bg-light text-dark">
                                            <th class="py-2 px-3">Issued Item</th>
                                            <th class="py-2 px-3">Qty</th>
                                            <th class="py-2 px-3">Source Location</th>
                                            <th class="py-2 px-3">Collected Defective Part</th>
                                            <th class="py-2 px-3">Defective Qty</th>
                                            <th class="py-2 px-3">Defective Status</th>
                                            <th class="py-2 px-3">Slip Status</th>
                                            <th class="py-2 px-3">Issued By</th>
                                            <th class="py-2 px-3">Date</th>
                                            <th class="py-2 px-3">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($complaint->replacements as $rep)
                                        <tr class="info-row">
                                            <td class="px-3 py-2">
                                                <strong>{{ $rep->issuedProduct->item_name ?? '-' }}</strong>
                                                @if($rep->is_issued_part && $rep->issued_part_name)
                                                    <span class="badge badge-warning d-block mt-1 font-weight-bold text-dark">Part: {{ $rep->issued_part_name }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">{{ (float)$rep->quantity }}</td>
                                            <td class="px-3 py-2">
                                                @if($rep->source_location_type === 'shop')
                                                    <span class="badge badge-info">Shop Direct</span>
                                                @elseif($rep->source_location_type === 'warehouse')
                                                    <span class="badge badge-secondary">Warehouse: {{ $rep->sourceWarehouse->warehouse_name ?? 'N/A' }}</span>
                                                @else
                                                    <span class="badge badge-light text-muted">Pending Release</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                @if($rep->collected_damaged_product_id)
                                                    <span class="text-danger"><i class="fas fa-heart-broken mr-1"></i>{{ $rep->collectedDamagedProduct->item_name ?? '-' }}</span>
                                                    @if($rep->is_collected_part && $rep->collected_part_name)
                                                        <span class="badge badge-danger d-block mt-1 font-weight-bold">Part: {{ $rep->collected_part_name }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">{{ $rep->collected_damaged_product_id ? (float)$rep->damaged_qty : '-' }}</td>
                                            <td class="px-3 py-2">
                                                @if($rep->collected_damaged_product_id)
                                                    @if($rep->damaged_status === 'retained_at_shop')
                                                        <span class="badge badge-warning">Retained at Shop</span>
                                                    @elseif($rep->damaged_status === 'transferred_to_warehouse')
                                                        <span class="badge badge-success">Transferred to Warehouse</span>
                                                    @else
                                                        <span class="badge badge-light">-</span>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                @if($rep->claim_status === 'pending')
                                                    <span class="badge badge-warning">Pending Claim</span>
                                                @elseif($rep->claim_status === 'claimed')
                                                    <span class="badge badge-success">Claimed ✓</span>
                                                    @if($rep->claimed_at)
                                                        <small class="d-block text-muted" style="font-size:10px;">{{ \Carbon\Carbon::parse($rep->claimed_at)->format('d M Y') }}</small>
                                                    @endif
                                                @else
                                                    <span class="badge badge-secondary">Direct</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">{{ $rep->createdByUser->name ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $rep->created_at->format('d M Y, H:i') }}</td>
                                            <td class="px-3 py-2">
                                                <a href="{{ route('complaints.replacements.print-slip', $rep->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm py-0 px-2" title="Print Slip">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-3">No replacement parts issued for this complaint yet.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Home Service Visits --}}
            @if($complaint->scenario_type === 'home_service')
            <div class="detail-card card">
                <div class="card-header d-flex justify-content-between align-items-center" style="background:#fffaf5; border-left: 4px solid #e67e22;">
                    <span class="font-weight-bold" style="color:#a04000;"><i class="fas fa-home mr-2"></i>Home Service Visits</span>
                    <button class="btn btn-warning btn-sm" data-toggle="collapse" data-target="#addVisitForm">
                        <i class="fas fa-plus mr-1"></i>Add Visit
                    </button>
                </div>
                <div class="card-body">
                    {{-- Add Visit Form (collapsible) --}}
                    <div class="collapse mb-3" id="addVisitForm">
                        <div class="border rounded p-3 bg-light">
                            <form action="{{ route('complaints.home-service.store', $complaint->id) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label class="small font-weight-bold">Technician Name *</label>
                                        <input type="text" name="technician_name" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Visit Date *</label>
                                        <input type="date" name="visit_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Time</label>
                                        <input type="time" name="visit_time" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Charges (Rs.)</label>
                                        <input type="number" name="visiting_charges" class="form-control form-control-sm" value="0" min="0" step="0.01">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="small font-weight-bold">Visit Notes</label>
                                        <textarea name="visit_notes" class="form-control form-control-sm" rows="2"></textarea>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small font-weight-bold">Status</label>
                                        <select name="visit_status" class="form-control form-control-sm">
                                            <option value="scheduled">Scheduled</option>
                                            <option value="visited">Visited</option>
                                            <option value="resolved">Resolved</option>
                                            <option value="follow_up">Follow Up</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2 d-flex align-items-end">
                                        <div class="form-check">
                                            <input type="checkbox" name="charges_paid" id="charges_paid" class="form-check-input">
                                            <label class="form-check-label small font-weight-bold" for="charges_paid">Charges Paid</label>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-warning btn-sm mt-2">
                                    <i class="fas fa-save mr-1"></i>Save Visit
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Visits List --}}
                    @forelse($complaint->homeServices as $visit)
                    <div class="visit-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="fas fa-user-cog mr-1 text-warning"></i>{{ $visit->technician_name }}</strong>
                                &nbsp;&mdash;&nbsp;
                                <span class="text-muted small">{{ $visit->visit_date->format('d M Y') }}
                                    {{ $visit->visit_time ? 'at ' . $visit->visit_time : '' }}</span>
                            </div>
                            <div>
                                {!! $visit->visit_status_badge !!}
                                @if($visit->charges_paid)
                                    <span class="badge badge-success ml-1">Paid</span>
                                @else
                                    <span class="badge badge-danger ml-1">Unpaid</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-1">
                            <span class="text-muted small">Charges: <strong>Rs. {{ number_format($visit->visiting_charges, 0) }}</strong></span>
                            @if($visit->visit_notes)
                            &nbsp;|&nbsp;<span class="text-muted small">{{ $visit->visit_notes }}</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small mb-0"><i class="fas fa-info-circle mr-1"></i>No visits recorded yet.</p>
                    @endforelse
                </div>
            </div>
            @endif

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-md-4">

            {{-- Barcode --}}
            <div class="detail-card card">
                <div class="card-header text-center font-weight-bold" style="background:#f8f9fa;">
                    <i class="fas fa-barcode mr-1"></i>Complaint Barcode
                </div>
                <div class="card-body">
                    <div class="barcode-box">
                        @if($complaint->barcode_path && file_exists(storage_path('app/public/' . $complaint->barcode_path)))
                        <img src="{{ asset('storage/' . $complaint->barcode_path) }}" style="max-width:100%; height:70px;">
                        <div class="font-weight-bold mt-1" style="font-family: monospace; font-size:13px; letter-spacing:1px;">{{ $complaint->complaint_no }}</div>
                        @else
                        <div class="text-muted py-3">
                            <i class="fas fa-barcode" style="font-size:40px; opacity:0.3;"></i>
                            <div class="mt-2 small">Barcode not generated</div>
                        </div>
                        @endif
                    </div>
                    @can('complaint.print')
                    <div class="mt-3 d-flex gap-2">
                        <a href="{{ route('complaints.print-slip', $complaint->id) }}" target="_blank" class="btn btn-secondary btn-sm btn-block mb-1">
                            <i class="fas fa-receipt mr-1"></i>Print Customer Slip
                        </a>
                        <a href="{{ route('complaints.print-tag', $complaint->id) }}" target="_blank" class="btn btn-dark btn-sm btn-block">
                            <i class="fas fa-tag mr-1"></i>Print Product Tag
                        </a>
                    </div>
                    @endcan
                </div>
            </div>

            {{-- Status Change --}}
            @can('complaint.edit')
            <div class="detail-card card">
                <div class="card-header font-weight-bold" style="background:#e8f8e8; color:#1d6b2c;">
                    <i class="fas fa-sync-alt mr-1"></i>Update Status
                </div>
                <div class="card-body">
                    <form id="statusChangeForm">
                        <div class="mb-2">
                            <label class="small font-weight-bold">New Status</label>
                            <select name="status" class="form-control form-control-sm" id="newStatusSelect">
                                <option value="pending" {{ $complaint->status=='pending'?'selected':'' }}>Pending</option>
                                <option value="in_progress" {{ $complaint->status=='in_progress'?'selected':'' }}>In Progress</option>
                                <option value="resolved" {{ $complaint->status=='resolved'?'selected':'' }}>Resolved</option>
                                <option value="closed" {{ $complaint->status=='closed'?'selected':'' }}>Closed</option>
                            </select>
                        </div>
                        <div class="mb-2" id="resolutionBlock" style="display:none;">
                            <label class="small font-weight-bold">Resolution Type</label>
                            <select name="resolution_type" class="form-control form-control-sm">
                                <option value="none">Not Specified</option>
                                <option value="exchanged">Product Exchanged</option>
                                <option value="repaired">Product Repaired</option>
                                <option value="refunded">Refunded</option>
                                <option value="pending_stock">Pending Stock</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="small font-weight-bold">Notes</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Optional note..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm btn-block">
                            <i class="fas fa-check mr-1"></i>Update Status
                        </button>
                    </form>
                </div>
            </div>
            @endcan

            {{-- Status Timeline --}}
            <div class="detail-card card">
                <div class="card-header font-weight-bold" style="background:#f8f9fa;">
                    <i class="fas fa-history mr-1"></i>Status History
                </div>
                <div class="card-body py-2">
                    @forelse($complaint->statusLogs as $log)
                    <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                        <div>
                            @if($log->old_status)
                            <span class="badge badge-secondary badge-sm">{{ ucfirst(str_replace('_', ' ', $log->old_status)) }}</span>
                            <i class="fas fa-arrow-right text-muted mx-1" style="font-size:10px;"></i>
                            @endif
                            <span class="badge badge-primary badge-sm">{{ ucfirst(str_replace('_', ' ', $log->new_status)) }}</span>
                            <div class="text-muted" style="font-size:11px; margin-top:3px;">
                                {{ $log->notes }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-muted" style="font-size:10px;">{{ $log->changedByUser->name ?? '-' }}</div>
                            <div class="text-muted" style="font-size:10px;">{{ $log->created_at->format('d M, H:i') }}</div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small mb-0">No status changes yet.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@section('js')
<script>
$(document).ready(function() {

    // Show resolution block when status is resolved/closed
    $('#newStatusSelect').on('change', function() {
        if (['resolved', 'closed'].includes($(this).val())) {
            $('#resolutionBlock').slideDown(200);
        } else {
            $('#resolutionBlock').slideUp(200);
        }
    }).trigger('change');

    // AJAX status change
    $('#statusChangeForm').on('submit', function(e) {
        e.preventDefault();
        const data = $(this).serialize();
        $.ajax({
            url: '/complaints/{{ $complaint->id }}/status',
            method: 'POST',
            data: data + '&_token={{ csrf_token() }}',
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Status Updated!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            },
            error: function() {
                Swal.fire('Error', 'Could not update status.', 'error');
            }
        });
    });

    // WhatsApp share
    $('#whatsappBtn').on('click', function() {
        $.get('/complaints/{{ $complaint->id }}/whatsapp', function(res) {
            window.open(res.link, '_blank');
        });
    });

    // Select2 inside modal fix
    $('#replacementModal').on('shown.bs.modal', function () {
        $('.select2-modal').select2({
            dropdownParent: $('#replacementModal'),
            placeholder: '-- Search --'
        });
        
        // Trigger initial stock location load if product is selected
        if ($('#issue_product_select').val()) {
            $('#issue_product_select').trigger('change');
        }
    });

    // Handle product selection to load source locations and quantities
    $('#issue_product_select').on('change', function() {
        const productId = $(this).val();
        const branchId = "{{ $complaint->branch_id }}";
        const $select = $('#source_location_select');
        const $qtyLabel = $('#source_stock_qty_label');

        if (!productId) {
            $select.html('<option value="">-- Choose product first --</option>').prop('disabled', true);
            $qtyLabel.text('');
            return;
        }

        $select.html('<option value="">⏳ Loading available stocks...</option>').prop('disabled', true);
        $qtyLabel.text('');

        $.ajax({
            url: "{{ route('complaints.replacements.locations-stock') }}",
            type: "GET",
            data: { product_id: productId, branch_id: branchId },
            success: function(data) {
                if (data && data.length > 0) {
                    let html = '<option value="">-- Select Source Location --</option>';
                    data.forEach(function(loc) {
                        const val = loc.type + '_' + (loc.id || 0);
                        html += `<option value="${val}" data-qty="${loc.quantity}">${loc.label} (Qty: ${loc.quantity})</option>`;
                    });
                    $select.html(html).prop('disabled', false);
                } else {
                    $select.html('<option value="">❌ No stock locations found in this branch</option>').prop('disabled', true);
                }
            },
            error: function() {
                $select.html('<option value="">❌ Error loading stock locations</option>').prop('disabled', true);
            }
        });
    });

    // On source location change, update helper info and limit input qty
    $('#source_location_select').on('change', function() {
        const selected = $(this).find(':selected');
        const availableQty = parseFloat(selected.data('qty') || 0);
        const $qtyLabel = $('#source_stock_qty_label');

        if ($(this).val()) {
            $qtyLabel.html(`Available Quantity: <strong class="text-success">${availableQty}</strong>`);
            $('#issue_quantity').attr('max', availableQty);
        } else {
            $qtyLabel.text('');
            $('#issue_quantity').removeAttr('max');
        }
    });

    // Toggle damaged collection view and inputs
    $('#collect_damaged_check').on('change', function() {
        if ($(this).is(':checked')) {
            $('#damaged_collection_section').slideDown(200);
            $('#damaged_product_select').prop('required', true);
            $('#damaged_qty_input').prop('required', true);
        } else {
            $('#damaged_collection_section').slideUp(200);
            $('#damaged_product_select').prop('required', false).val('').trigger('change');
            $('#damaged_qty_input').prop('required', false);
        }
    }).trigger('change');

    // Confirm submit to make sure they want to adjust stock
    $('#replacementForm').on('submit', function(e) {
        const selectedLoc = $('#source_location_select');
        const selectVal = selectedLoc.val();
        if (!selectVal) {
            e.preventDefault();
            Swal.fire('Error', 'Please select a source location.', 'error');
            return false;
        }

        const selectedOption = selectedLoc.find(':selected');
        const available = parseFloat(selectedOption.data('qty') || 0);
        const requestQty = parseFloat($('#issue_quantity').val() || 0);

        if (requestQty > available) {
            e.preventDefault();
            Swal.fire('Insufficient Stock', `Requested quantity (${requestQty}) exceeds available stock (${available}).`, 'error');
            return false;
        }

        $('#submitReplacementBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i>Processing...').prop('disabled', true);
    });

    // Toggle unrepairable alert, fields wrapper, and repair price in resolve form
    $('.repair-action-radio').on('change', function() {
        if ($('#action_unrepairable').is(':checked')) {
            $('#repair_price_wrapper').slideUp(200);
            $('#exchange_fields_wrapper').removeClass('d-none').slideDown(200);
        } else {
            $('#repair_price_wrapper').slideDown(200);
            $('#exchange_fields_wrapper').slideUp(200);
        }
    });
    // Trigger initial change
    $('.repair-action-radio:checked').trigger('change');

    // Toggle defective collection section in inline form
    $('#exchange_collect_damaged_check').on('change', function() {
        if ($(this).is(':checked')) {
            $('#exchange_damaged_collection_section').slideDown(200);
        } else {
            $('#exchange_damaged_collection_section').slideUp(200);
            if ($('#exchange_damaged_product_select').data('select2')) {
                $('#exchange_damaged_product_select').val('').trigger('change');
            }
        }
    });
    // Trigger initial change
    $('#exchange_collect_damaged_check').trigger('change');

    // Toggle part name input for issued product in inline exchange form
    $('#is_issued_part_check').on('change', function() {
        if ($(this).is(':checked')) {
            $('#issued_part_name_wrapper').removeClass('d-none').slideDown(200);
            $('#issued_part_name').prop('required', true);
        } else {
            $('#issued_part_name_wrapper').slideUp(200);
            $('#issued_part_name').prop('required', false).val('');
        }
    });
    // Trigger initial change
    $('#is_issued_part_check').trigger('change');

    // Toggle part name input for collected product in inline exchange form
    $('#is_collected_part_check').on('change', function() {
        if ($(this).is(':checked')) {
            $('#collected_part_name_wrapper').removeClass('d-none').slideDown(200);
            $('#collected_part_name').prop('required', true);
        } else {
            $('#collected_part_name_wrapper').slideUp(200);
            $('#collected_part_name').prop('required', false).val('');
        }
    });
    // Trigger initial change
    $('#is_collected_part_check').trigger('change');

    // Trigger loading locations for claim modal when it is shown
    $('#claimReplacementModal').on('shown.bs.modal', function () {
        const productId = $('#claim_product_select').val();
        const branchId = "{{ $complaint->branch_id }}";
        const $select = $('#claim_source_location_select');
        const $qtyLabel = $('#claim_stock_qty_label');

        $select.html('<option value="">⏳ Loading available stocks...</option>').prop('disabled', true);
        $qtyLabel.text('');

        $.ajax({
            url: "{{ route('complaints.replacements.locations-stock') }}",
            type: "GET",
            data: { product_id: productId, branch_id: branchId },
            success: function(data) {
                if (data && data.length > 0) {
                    let html = '<option value="">-- Select Source Location --</option>';
                    data.forEach(function(loc) {
                        const val = loc.type + '_' + (loc.id || 0);
                        html += `<option value="${val}" data-qty="${loc.quantity}">${loc.label} (Qty: ${loc.quantity})</option>`;
                    });
                    $select.html(html).prop('disabled', false);
                } else {
                    $select.html('<option value="">❌ No stock locations found in this branch</option>').prop('disabled', true);
                }
            },
            error: function() {
                $select.html('<option value="">❌ Error loading stock locations</option>').prop('disabled', true);
            }
        });
    });

    $('#claim_source_location_select').on('change', function() {
        const selected = $(this).find(':selected');
        const availableQty = parseFloat(selected.data('qty') || 0);
        const $qtyLabel = $('#claim_stock_qty_label');

        if ($(this).val()) {
            $qtyLabel.html(`Available Quantity: <strong class="text-success">${availableQty}</strong>`);
            $('#claim_quantity').attr('max', availableQty);
        } else {
            $qtyLabel.text('');
            $('#claim_quantity').removeAttr('max');
        }
    });

    $('#claimReplacementForm').on('submit', function(e) {
        const selectedLoc = $('#claim_source_location_select');
        const selectVal = selectedLoc.val();
        if (!selectVal) {
            e.preventDefault();
            Swal.fire('Error', 'Please select a source location.', 'error');
            return false;
        }

        const selectedOption = selectedLoc.find(':selected');
        const available = parseFloat(selectedOption.data('qty') || 0);
        const requestQty = parseFloat($('#claim_quantity').val() || 0);

        if (requestQty > available) {
            e.preventDefault();
            Swal.fire('Insufficient Stock', `Requested quantity (${requestQty}) exceeds available stock (${available}).`, 'error');
            return false;
        }

        $('#submitClaimBtn').html('<i class="fas fa-spinner fa-spin mr-1"></i>Releasing...').prop('disabled', true);
    });

    // ─── Resolve Repair Form: JS Validation Before Submit ────────────────
    $('#resolveRepairForm').on('submit', function(e) {
        const notes = $.trim($('#resolution_notes').val());
        const action = $('input[name="repair_action"]:checked').val();

        // Validate notes
        if (!notes) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Notes Required',
                text: 'Please fill in the Technical Diagnostic Details / Notes before submitting.',
                confirmButtonColor: '#f39c12'
            });
            $('#resolution_notes').addClass('is-invalid').focus();
            return false;
        }

        // If unrepairable — validate issued product
        if (action === 'unrepairable') {
            const productId = $('#exchange_product_select').val();
            if (!productId) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Product Required',
                    text: 'Please select the Product to Issue for the customer exchange.',
                    confirmButtonColor: '#f39c12'
                });
                return false;
            }
        }

        // All good — disable button to prevent double submit
        $(this).find('button[type="submit"]')
               .html('<i class="fas fa-spinner fa-spin mr-1"></i>Submitting...')
               .prop('disabled', true);
    });

    // Remove invalid highlight on typing
    $('#resolution_notes').on('input', function() {
        $(this).removeClass('is-invalid');
    });

});
</script>

{{-- Issue Replacement Modal --}}
@can('complaint.edit')
<div class="modal fade" id="replacementModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('complaints.replacements.store') }}" method="POST" id="replacementForm">
                @csrf
                <input type="hidden" name="complaint_id" value="{{ $complaint->id }}">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold text-white"><i class="fas fa-plus-circle mr-1"></i>Issue Replacement Part / Product</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <div class="alert alert-info py-2">
                        <i class="fas fa-info-circle mr-1"></i> Issue clean stock to the customer for this complaint and optionally track the returned defective part.
                    </div>

                    <div class="row">
                        {{-- Product to issue --}}
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">Product / Part to Issue <span class="text-danger">*</span></label>
                            <select name="issued_product_id" id="issue_product_select" class="form-control form-control-sm select2-modal" required style="width:100%;">
                                <option value="">-- Search product catalog --</option>
                                @foreach($productsList as $p)
                                <option value="{{ $p->id }}" {{ $complaint->product_id == $p->id ? 'selected' : '' }}>
                                    {{ $p->item_name }} ({{ $p->item_code }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Source Location --}}
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">Source Stock Location <span class="text-danger">*</span></label>
                            <select name="source_location" id="source_location_select" class="form-control form-control-sm" required disabled>
                                <option value="">-- Choose product first --</option>
                            </select>
                            <small class="text-muted d-block mt-1" id="source_stock_qty_label"></small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small">Quantity to Issue <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="issue_quantity" class="form-control form-control-sm" value="1" min="1" step="1" required>
                        </div>
                    </div>

                    <hr>

                    {{-- Collect Damaged Checkbox --}}
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="collect_damaged" id="collect_damaged_check" value="1" class="form-check-input" checked>
                            <label class="form-check-label font-weight-bold text-danger" for="collect_damaged_check">
                                Collect Defective / Damaged Part from Customer?
                            </label>
                        </div>
                    </div>

                    {{-- Damaged Collection Section --}}
                    <div class="border rounded p-3 bg-light" id="damaged_collection_section">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="font-weight-bold small">Collected Damaged Part <span class="text-danger">*</span></label>
                                <select name="collected_damaged_product_id" id="damaged_product_select" class="form-control form-control-sm select2-modal" style="width:100%;">
                                    <option value="">-- Search defective catalog --</option>
                                    @foreach($productsList as $p)
                                    <option value="{{ $p->id }}" {{ $complaint->product_id == $p->id ? 'selected' : '' }}>
                                        {{ $p->item_name }} ({{ $p->item_code }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="font-weight-bold small">Damaged Qty Collected <span class="text-danger">*</span></label>
                                <input type="number" name="damaged_qty" id="damaged_qty_input" class="form-control form-control-sm" value="1" min="1" step="1">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" id="submitReplacementBtn">
                        <i class="fas fa-check mr-1"></i>Issue &amp; Log Replacement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- Claim Replacement Modal --}}
@can('complaint.edit')
@if(isset($pendingSlip) && $pendingSlip)
<div class="modal fade" id="claimReplacementModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('complaints.replacements.claim', $pendingSlip->id) }}" method="POST" id="claimReplacementForm">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-store mr-1"></i>Release Replacement from Stock</h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small">
                        <i class="fas fa-info-circle mr-1"></i> Select branch shop or warehouse stock location to deduct the clean product and issue it to the customer.
                    </div>

                    <div class="mb-3">
                        <label class="small font-weight-bold d-block">Replacement Slip No</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $pendingSlip->replacement_slip_no }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="small font-weight-bold d-block">Defective Item Collected</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $pendingSlip->collectedDamagedProduct->item_name ?? 'N/A' }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="small font-weight-bold">Product to Issue <span class="text-danger">*</span></label>
                        <select name="issued_product_id" id="claim_product_select" class="form-control form-control-sm" required readonly>
                            <option value="{{ $pendingSlip->collected_damaged_product_id }}" selected>
                                {{ $pendingSlip->collectedDamagedProduct->item_name ?? 'N/A' }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="small font-weight-bold">Source Stock Location <span class="text-danger">*</span></label>
                        <select name="source_location" id="claim_source_location_select" class="form-control form-control-sm" required>
                            <option value="">-- Choose Stock Location --</option>
                        </select>
                        <small class="text-muted d-block mt-1" id="claim_stock_qty_label"></small>
                    </div>

                    <div class="mb-3">
                        <label class="small font-weight-bold">Quantity to Issue *</label>
                        <input type="number" name="quantity" id="claim_quantity" class="form-control form-control-sm" value="1" min="1" step="1" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm px-4 text-dark font-weight-bold" id="submitClaimBtn">
                        <i class="fas fa-check mr-1"></i>Release &amp; Close Slip
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endcan
@endsection
