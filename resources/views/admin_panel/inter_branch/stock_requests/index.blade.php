@extends('admin_panel.layout.app')

@section('content')
<style>
    /* ═══════════════════════════════════════════════════════════
       AMEEN & SONS ERP — INTER-BRANCH STOCK REQUESTS INDEX
       ═══════════════════════════════════════════════════════════ */
    :root {
        --theme-navy: #1e3a5f;
        --theme-navy-light: #2c5282;
        --theme-gold: #c8973a;
        --theme-border: #e2e8f0;
        --theme-bg: #f8fafc;
    }

    .sr-index-wrapper {
        padding: 4px 6px 24px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    /* 1. Header Bar */
    .sr-index-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        border-radius: 12px;
        padding: 14px 22px;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(30, 58, 95, 0.15);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .sr-index-title {
        font-size: 17px;
        font-weight: 800;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        letter-spacing: -0.2px;
        color: #ffffff !important;
    }

    .sr-index-subtitle {
        font-size: 11.5px;
        color: rgba(255, 255, 255, 0.85) !important;
        margin-top: 2px;
    }

    .btn-create-req {
        background: linear-gradient(135deg, #0d9f6e 0%, #059669 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 8px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        box-shadow: 0 2px 8px rgba(13, 159, 110, 0.3);
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-create-req:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(13, 159, 110, 0.4);
    }

    /* 2. Main Card */
    .sr-main-card {
        background: #ffffff;
        border: 1px solid var(--theme-border);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        padding: 18px 20px;
    }

    /* 3. Modern Segmented Tabs */
    .sr-nav-tabs {
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 18px;
        gap: 8px;
        display: flex;
        flex-wrap: wrap;
    }

    .sr-nav-tabs .nav-item {
        margin-bottom: -2px;
    }

    .sr-nav-tabs .nav-link {
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
        padding: 9px 18px;
        border: 2px solid transparent;
        border-radius: 8px 8px 0 0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.15s ease;
    }

    .sr-nav-tabs .nav-link:hover {
        color: #1e3a5f;
        background: #f1f5f9;
    }

    .sr-nav-tabs .nav-link.active {
        color: #1e3a5f !important;
        background: #ffffff !important;
        border-color: #e2e8f0 #e2e8f0 #ffffff !important;
        border-top: 3px solid #1e3a5f !important;
    }

    .sr-tab-badge {
        font-size: 11px;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 12px;
    }

    .sr-badge-incoming {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .sr-badge-outgoing {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }

    /* 4. Table Styling */
    .sr-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0;
    }

    .sr-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.55px;
        padding: 12px 14px;
        border-top: none;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    .sr-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
        color: #334155;
        background: #ffffff;
    }

    .sr-table tbody tr:hover td {
        background: #f8fafc;
    }

    /* Branch badge */
    .badge-branch-sm {
        font-size: 11.5px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        background: rgba(30, 58, 95, 0.08);
        color: #1e3a5f;
        border: 1px solid rgba(30, 58, 95, 0.15);
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    /* Status Badges */
    .badge-status-pending {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-status-approved {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-status-rejected {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .item-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #1e293b;
        margin: 2px 0;
    }

    .item-chip-qty {
        background: #1e3a5f;
        color: #ffffff;
        border-radius: 4px;
        padding: 1px 6px;
        font-size: 10.5px;
        font-weight: 700;
    }

    /* Buttons */
    .btn-approve-sm {
        background: linear-gradient(135deg, #0d9f6e 0%, #059669 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        box-shadow: 0 1px 4px rgba(13, 159, 110, 0.25);
        transition: all 0.15s ease;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-approve-sm:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
    }

    .btn-view-sm {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        color: #1e3a5f;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
    }

    .btn-view-sm:hover {
        background: #1e3a5f;
        color: #ffffff;
        border-color: #1e3a5f;
    }

    /* Modal Styling */
    #viewRequestModal .modal-content {
        border-radius: 12px;
        overflow: hidden;
        border: none;
        box-shadow: 0 20px 45px rgba(0,0,0,0.2);
    }
    #viewRequestModal .modal-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        padding: 12px 20px;
    }
</style>

<div class="main-content">
    <div class="sr-index-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Top Header Bar --}}
            <div class="sr-index-header">
                <div>
                    <h1 class="sr-index-title">
                        <i class="fas fa-boxes-packing" style="color: var(--theme-gold);"></i>
                        Inter-Branch Stock Requests
                    </h1>
                    <div class="sr-index-subtitle">
                        Review incoming requisitions &amp; track outgoing transfer requests across branches
                    </div>
                </div>
                <div>
                    <a href="{{ route('inter_branch_stock_requests.create') }}" class="btn-create-req">
                        <i class="fas fa-plus"></i> New Stock Request
                    </a>
                </div>
            </div>

            {{-- 2. Alerts if any --}}
            @if(session('success'))
                <div class="alert alert-success py-2 px-3 mb-3 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #10b981 !important; border-radius: 6px;">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger py-2 px-3 mb-3 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #ef4444 !important; border-radius: 6px;">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                </div>
            @endif

            {{-- 3. Main Card with Tabs & Tables --}}
            <div class="sr-main-card">

                {{-- Segmented Tabs Navigation --}}
                <ul class="nav sr-nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" data-bs-toggle="tab" href="#incoming" role="tab">
                            <i class="fas fa-inbox text-danger"></i> Incoming Requests (To Approve)
                            <span class="sr-tab-badge sr-badge-incoming">{{ $incomingRequests->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" data-bs-toggle="tab" href="#outgoing" role="tab">
                            <i class="fas fa-paper-plane text-info"></i> Outgoing Requests (Sent)
                            <span class="sr-tab-badge sr-badge-outgoing">{{ $outgoingRequests->count() }}</span>
                        </a>
                    </li>
                </ul>

                {{-- Tab Content --}}
                <div class="tab-content">

                    {{-- ──────────────────────────────────────────
                         TAB 1: INCOMING REQUESTS (TO APPROVE)
                    ────────────────────────────────────────── --}}
                    <div id="incoming" class="tab-pane fade show active">
                        @if ($incomingRequests->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox mb-2" style="font-size: 38px; color: #cbd5e1;"></i>
                                <div class="font-weight-bold" style="font-size: 14px;">No Incoming Stock Requests</div>
                                <small>Your branch has no pending requisitions to approve at this moment.</small>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="sr-table" id="incomingTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 18%;">From Branch (Origin)</th>
                                            <th style="width: 44%;">Requested Products &amp; Qty</th>
                                            <th style="width: 12%; text-align: center;">Status</th>
                                            <th style="width: 14%;">Requested At</th>
                                            <th style="width: 12%; text-align: center;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($incomingRequests as $request)
                                            <tr>
                                                <td>
                                                    <span class="badge-branch-sm">
                                                        <i class="fas fa-store text-primary"></i>
                                                        {{ $request->fromBranch->name ?? $request->fromBranch->branch_name ?? 'Branch #' . $request->from_branch_id }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column" style="gap: 4px;">
                                                        @foreach ($request->items as $item)
                                                            <div class="item-chip">
                                                                <span>{{ $item->product->item_name ?? 'Product #' . $item->product_id }}</span>
                                                                <span class="item-chip-qty">{{ $item->requested_qty }} Units</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td style="text-align: center;">
                                                    @if($request->status === 'pending')
                                                        <span class="badge-status-pending">
                                                            <i class="fas fa-clock"></i> Pending
                                                        </span>
                                                    @elseif($request->status === 'approved')
                                                        <span class="badge-status-approved">
                                                            <i class="fas fa-check-circle"></i> Approved
                                                        </span>
                                                    @else
                                                        <span class="badge-status-rejected">
                                                            <i class="fas fa-times-circle"></i> Rejected
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div style="font-weight: 600; color: #1e293b;">
                                                        {{ $request->created_at->format('M d, Y') }}
                                                    </div>
                                                    <small class="text-muted">{{ $request->created_at->format('h:i A') }}</small>
                                                </td>
                                                <td style="text-align: center;">
                                                    <div class="d-inline-flex align-items-center" style="gap: 6px;">
                                                        @if ($request->status === 'pending')
                                                            <a href="{{ route('inter_branch_stock_requests.show', $request) }}" class="btn-approve-sm">
                                                                <i class="fas fa-check"></i> Review
                                                            </a>
                                                        @endif
                                                        <button type="button" class="btn-view-sm view-request-btn" data-id="{{ $request->id }}" title="View Request Details">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- ──────────────────────────────────────────
                         TAB 2: OUTGOING REQUESTS (SENT)
                    ────────────────────────────────────────── --}}
                    <div id="outgoing" class="tab-pane fade">
                        @if ($outgoingRequests->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-paper-plane mb-2" style="font-size: 38px; color: #cbd5e1;"></i>
                                <div class="font-weight-bold" style="font-size: 14px;">No Outgoing Requests Found</div>
                                <small>You have not sent any stock requests to other branches yet.</small>
                                <div class="mt-3">
                                    <a href="{{ route('inter_branch_stock_requests.create') }}" class="btn btn-sm btn-outline-primary font-weight-bold">
                                        <i class="fas fa-plus mr-1"></i> Create First Stock Request
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="sr-table" id="outgoingTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 18%;">To Branch (Supplier)</th>
                                            <th style="width: 38%;">Requested Products</th>
                                            <th style="width: 12%; text-align: center;">Status</th>
                                            <th style="width: 14%;">Approved By</th>
                                            <th style="width: 10%;">Date Sent</th>
                                            <th style="width: 8%; text-align: center;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($outgoingRequests as $request)
                                            <tr>
                                                <td>
                                                    <span class="badge-branch-sm">
                                                        <i class="fas fa-truck-moving text-info"></i>
                                                        {{ $request->toBranch->name ?? $request->toBranch->branch_name ?? 'Branch #' . $request->to_branch_id }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column" style="gap: 4px;">
                                                        @foreach ($request->items as $item)
                                                            <div class="item-chip">
                                                                <span>{{ $item->product->item_name ?? 'Product #' . $item->product_id }}</span>
                                                                <span class="item-chip-qty">{{ $item->approved_qty ?? $item->requested_qty }} Units</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td style="text-align: center;">
                                                    @if ($request->status === 'pending')
                                                        <span class="badge-status-pending">
                                                            <i class="fas fa-hourglass-half"></i> Waiting
                                                        </span>
                                                    @elseif ($request->status === 'approved')
                                                        <span class="badge-status-approved">
                                                            <i class="fas fa-check-circle"></i> Approved
                                                        </span>
                                                    @else
                                                        <span class="badge-status-rejected">
                                                            <i class="fas fa-times-circle"></i> Rejected
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($request->approvedBy)
                                                        <span class="font-weight-600 text-dark"><i class="fas fa-user-check text-success mr-1"></i>{{ $request->approvedBy->name }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div style="font-weight: 600; color: #1e293b;">
                                                        {{ $request->created_at->format('M d, Y') }}
                                                    </div>
                                                    <small class="text-muted">{{ $request->created_at->format('h:i A') }}</small>
                                                </td>
                                                <td style="text-align: center;">
                                                    <button type="button" class="btn-view-sm view-request-btn" data-id="{{ $request->id }}" title="View Request Details">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>{{-- end tab-content --}}
            </div>{{-- end sr-main-card --}}

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     VIEW DETAILS MODAL (Ameen & Sons Theme)
══════════════════════════════════════════════════ --}}
<div class="modal fade" id="viewRequestModal" tabindex="-1" role="dialog" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header text-white">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.15); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--theme-gold);">
                        <i class="fas fa-clipboard-list" style="font-size: 15px;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0 text-white" id="viewRequestModalLabel" style="font-size: 15px; color: #ffffff !important;">
                            Stock Request Details
                        </h5>
                    </div>
                </div>
                <button type="button" class="close text-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none; font-size: 20px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3" id="requestDetailsBody" style="background: #f8fafc;">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white py-2 px-3 border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3 font-weight-bold" data-bs-dismiss="modal" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {

    // View Request Details Modal AJAX
    $('.view-request-btn').on('click', function() {
        const requestId = $(this).data('id');
        const $body = $('#requestDetailsBody');
        
        // Show loading spinner
        $body.html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        `);
        
        // Open modal
        $('#viewRequestModal').modal('show');
        
        // Dynamic subfolder-safe API URL
        const apiUrl = "{{ url('inter-branch/stock-requests') }}/" + requestId + "/details";
        
        $.ajax({
            url: apiUrl,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const req = response.request;
                    
                    let itemsHtml = '';
                    let tableHeadersHtml = '';
                    
                    if (req.status === 'approved') {
                        tableHeadersHtml = `
                            <tr>
                                <th style="width: 5%; text-align: center;">#</th>
                                <th style="width: 35%;">Product Details</th>
                                <th style="width: 10%; text-align: center;">Req Qty</th>
                                <th style="width: 10%; text-align: center;">App Qty</th>
                                <th style="width: 15%;">Src Warehouse</th>
                                <th style="width: 15%;">Dest Warehouse</th>
                                <th style="width: 10%; text-align: right;">Total</th>
                            </tr>
                        `;
                        
                        req.items.forEach(function(item, index) {
                            itemsHtml += `
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">${index + 1}</td>
                                    <td>
                                        <strong style="color: #1e293b;">${item.product_name}</strong><br>
                                        <small class="text-muted">Code: ${item.product_code || '-'}</small>
                                    </td>
                                    <td style="text-align: center; font-weight: 600;">${item.requested_qty}</td>
                                    <td style="text-align: center; font-weight: 700; color: #0d9f6e;">${item.approved_qty}</td>
                                    <td><small class="badge badge-light border">${item.from_warehouse || '-'}</small></td>
                                    <td><small class="badge badge-light border">${item.to_warehouse || '-'}</small></td>
                                    <td style="text-align: right; font-weight: 700; color: #1e3a5f;">${item.total_price || '-'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tableHeadersHtml = `
                            <tr>
                                <th style="width: 8%; text-align: center;">#</th>
                                <th style="width: 67%;">Product Description</th>
                                <th style="width: 25%; text-align: center;">Requested Qty</th>
                            </tr>
                        `;
                        
                        req.items.forEach(function(item, index) {
                            itemsHtml += `
                                <tr>
                                    <td style="text-align: center; font-weight: 700; color: #94a3b8;">${index + 1}</td>
                                    <td>
                                        <strong style="color: #1e293b;">${item.product_name}</strong><br>
                                        <small class="text-muted">Code: ${item.product_code || '-'}</small>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge" style="background: #1e3a5f; color: #fff; font-size: 12px; padding: 3px 9px;">${item.requested_qty} Units</span>
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    let statusBadge = '<span class="badge-status-pending"><i class="fas fa-clock mr-1"></i> PENDING</span>';
                    if (req.status === 'approved') statusBadge = '<span class="badge-status-approved"><i class="fas fa-check-circle mr-1"></i> APPROVED</span>';
                    if (req.status === 'rejected') statusBadge = '<span class="badge-status-rejected"><i class="fas fa-times-circle mr-1"></i> REJECTED</span>';

                    let processedHtml = '';
                    if (req.status !== 'pending') {
                        processedHtml = `
                            <tr>
                                <td style="color: #64748b; font-weight: 600; font-size: 11.5px;">Processed By:</td>
                                <td style="font-weight: 700; color: #1e293b;">${req.approved_by || '-'}</td>
                            </tr>
                            <tr>
                                <td style="color: #64748b; font-weight: 600; font-size: 11.5px;">Processed Date:</td>
                                <td style="font-weight: 600; color: #1e293b;">${req.approved_at || '-'}</td>
                            </tr>
                        `;
                    }

                    let detailsHtml = `
                        <div class="row mb-3">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <div class="bg-white p-3 rounded border" style="height: 100%;">
                                    <table class="table table-sm table-borderless mb-0" style="font-size: 12.5px;">
                                        <tr>
                                            <td style="width: 40%; color: #64748b; font-weight: 600; font-size: 11.5px;">Request ID:</td>
                                            <td style="font-weight: 700; color: #1e3a5f;">#REQ-${req.id}</td>
                                        </tr>
                                        <tr>
                                            <td style="color: #64748b; font-weight: 600; font-size: 11.5px;">Origin (From):</td>
                                            <td style="font-weight: 700; color: #1e293b;">${req.from_branch}</td>
                                        </tr>
                                        <tr>
                                            <td style="color: #64748b; font-weight: 600; font-size: 11.5px;">Supplier (To):</td>
                                            <td style="font-weight: 700; color: #1e293b;">${req.to_branch}</td>
                                        </tr>
                                        <tr>
                                            <td style="color: #64748b; font-weight: 600; font-size: 11.5px;">Current Status:</td>
                                            <td>${statusBadge}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-3 rounded border" style="height: 100%;">
                                    <table class="table table-sm table-borderless mb-0" style="font-size: 12.5px;">
                                        <tr>
                                            <td style="width: 40%; color: #64748b; font-weight: 600; font-size: 11.5px;">Requested Date:</td>
                                            <td style="font-weight: 600; color: #1e293b;">${req.created_at}</td>
                                        </tr>
                                        <tr>
                                            <td style="color: #64748b; font-weight: 600; font-size: 11.5px;">Created By:</td>
                                            <td style="font-weight: 700; color: #1e293b;">${req.created_by || '-'}</td>
                                        </tr>
                                        ${processedHtml}
                                    </table>
                                </div>
                            </div>
                        </div>

                        ${req.remarks ? `
                            <div class="bg-white p-3 rounded border mb-3">
                                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 4px;">
                                    <i class="fas fa-comment-dots text-secondary mr-1"></i> Requisition Remarks / Dispatch Notes
                                </div>
                                <div style="font-size: 13px; color: #334155;">${req.remarks}</div>
                            </div>
                        ` : ''}

                        <div class="bg-white rounded border overflow-hidden">
                            <div style="background: #f8fafc; padding: 8px 14px; border-bottom: 1.5px solid #e2e8f0; font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #1e3a5f; letter-spacing: 0.5px;">
                                <i class="fas fa-boxes text-primary mr-1"></i> Requested Products List
                            </div>
                            <div class="table-responsive mb-0">
                                <table class="table table-sm table-bordered table-striped mb-0" style="font-size: 12.5px;">
                                    <thead style="background: #f1f5f9; color: #475569; font-size: 11px; text-transform: uppercase; font-weight: 700;">
                                        ${tableHeadersHtml}
                                    </thead>
                                    <tbody>
                                        ${itemsHtml}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                    
                    $body.html(detailsHtml);
                } else {
                    $body.html('<div class="alert alert-danger py-2 small font-weight-bold">Error loading request details.</div>');
                }
            },
            error: function(error) {
                console.error(error);
                $body.html('<div class="alert alert-danger py-2 small font-weight-bold">Failed to fetch request details.</div>');
            }
        });
    });
});
</script>
@endsection
