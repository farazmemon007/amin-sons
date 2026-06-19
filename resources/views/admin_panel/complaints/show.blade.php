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
</style>
@endsection

@section('content')
<div class="container-fluid mt-3">

    {{-- Flash --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
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
                            <span class="badge-remote"><i class="fab fa-whatsapp mr-1"></i>Remote</span>
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
                        <tr class="info-row"><td>Model</td><td>{{ $complaint->product_model ?? '-' }}</td></tr>
                        <tr class="info-row"><td>Serial / IMEI</td><td>{{ $complaint->product_serial ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Issue Description --}}
            <div class="detail-card card">
                <div class="card-header" style="background:#fdf0e8; color:#a04000;">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Issue Description
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space:pre-wrap;">{{ $complaint->issue_description }}</p>
                    @if($complaint->photo_path)
                    <hr>
                    <p class="font-weight-bold mb-2">Product Photo:</p>
                    <img src="{{ asset('storage/' . $complaint->photo_path) }}" style="max-width:300px; border-radius:8px; border:1px solid #ddd;">
                    @endif
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

});
</script>
@endsection
