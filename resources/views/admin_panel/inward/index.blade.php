@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Ultra-Premium SaaS/ERP Styling */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    .erp-container { 
        background-color: #f4f7f6; 
        min-height: 100vh; 
        padding-bottom: 60px; 
        font-family: 'Inter', sans-serif;
    }
    
    .page-header-wrapper {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 16px;
        padding: 24px 32px;
        margin-bottom: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .page-title { 
        color: #1e293b; 
        font-weight: 700; 
        letter-spacing: -0.025em; 
        font-size: 1.5rem;
    }
    
    .card-premium { 
        border: 1px solid rgba(226, 232, 240, 0.8); 
        border-radius: 16px; 
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.03); 
        background: #ffffff;
        overflow: hidden;
    }
    
    .btn-premium { 
        background: linear-gradient(to right, #0f172a, #334155); 
        color: #ffffff !important; 
        border: none; 
        border-radius: 10px; 
        padding: 10px 24px; 
        font-weight: 600; 
        font-size: 0.9rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.2);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-premium:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.25); 
        background: linear-gradient(to right, #000000, #1e293b); 
    }
    
    /* Table Styling */
    .erp-table { width: 100%; margin-top: 0 !important; }
    .erp-table thead th { 
        background-color: #f8fafc; 
        color: #475569; 
        font-weight: 600; 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 0.05em;
        padding: 18px 20px; 
        border-bottom: 2px solid #e2e8f0; 
        border-top: none; 
    }
    .erp-table tbody td { 
        padding: 18px 20px; 
        vertical-align: middle; 
        border-bottom: 1px solid #f1f5f9; 
        font-size: 0.875rem; 
        color: #334155; 
        font-weight: 500;
    }
    .erp-table tbody tr { transition: background-color 0.2s ease; }
    .erp-table tbody tr:hover { background-color: #fcfcfd; }
    
    /* Action Buttons (Pill shaped) */
    .action-btn { 
        border-radius: 20px; 
        padding: 6px 14px; 
        font-size: 0.8rem; 
        font-weight: 600; 
        transition: all 0.2s; 
        margin: 2px; 
        display: inline-flex; 
        align-items: center;
        gap: 4px;
        text-decoration: none;
    }
    .btn-view { background-color: #f0f9ff; color: #0284c7; border: 1px solid transparent; }
    .btn-view:hover { background-color: #e0f2fe; color: #0369a1; border-color: #bae6fd; transform: scale(1.05); }
    
    .btn-edit { background-color: #fefce8; color: #ca8a04; border: 1px solid transparent; }
    .btn-edit:hover { background-color: #fef9c3; color: #a16207; border-color: #fde047; transform: scale(1.05); }
    
    .btn-bill { background-color: #f0fdf4; color: #16a34a; border: 1px solid transparent; }
    .btn-bill:hover { background-color: #dcfce7; color: #15803d; border-color: #bbf7d0; transform: scale(1.05); }
    
    .btn-delete { background-color: #fef2f2; color: #dc2626; border: 1px solid transparent; }
    .btn-delete:hover { background-color: #fee2e2; color: #b91c1c; border-color: #fecaca; transform: scale(1.05); }
    
    /* Badges */
    .status-badge { 
        padding: 6px 12px; 
        border-radius: 20px; 
        font-weight: 600; 
        font-size: 0.75rem; 
        letter-spacing: 0.025em;
        display: inline-flex; 
        align-items: center;
        text-transform: uppercase; 
    }
    .badge-pending { background-color: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .badge-completed { background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .badge-linked { background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
    .badge-cancelled { background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    
    /* DataTable customization */
    .dataTables_wrapper { padding: 0 10px; }
    .dataTables_wrapper .dataTables_filter { margin-bottom: 20px; }
    .dataTables_wrapper .dataTables_filter input { 
        border-radius: 20px; 
        border: 1px solid #cbd5e1; 
        padding: 8px 16px; 
        outline: none; 
        font-size: 0.875rem;
        background-color: #f8fafc;
        transition: all 0.3s;
    }
    .dataTables_wrapper .dataTables_filter input:focus { 
        border-color: #3b82f6; 
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); 
    }
    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 4px 8px;
        font-size: 0.875rem;
    }
    .dataTables_wrapper .dataTables_paginate { margin-top: 20px; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { 
        background: #0f172a !important; 
        color: white !important; 
        border: none !important; 
        border-radius: 8px !important; 
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button { 
        border-radius: 8px !important; 
        border: 1px solid transparent !important;
        font-weight: 500;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f1f5f9 !important;
        color: #0f172a !important;
        border: 1px solid #e2e8f0 !important;
    }
    /* Button CSS for dropdown */
    .action-dropdown {
        border-radius: 12px;
        padding: 6px;
        min-width: 180px;
        z-index: 10000 !important;
        border: none;
    }

    .action-dropdown .dropdown-item {
        padding: 9px 14px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.25s ease;
        font-size: 0.85rem;
    }

    .action-dropdown .dropdown-item:hover {
        background: linear-gradient(90deg, #f8f9fa, #eef1f5);
        transform: translateX(4px);
    }
    
    .dropdown-appended {
        display: none;
        position: absolute;
    }
</style>

<div class="main-content erp-container">
    <div class="main-content-inner pt-4">
        <div class="container-fluid px-lg-5">
            
            <!-- Modern Header -->
            <div class="page-header-wrapper d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="page-title mb-1">Inward Gatepass</h2>
                    <p class="text-muted small mb-0" style="font-size: 0.85rem;">Manage and track all your incoming inventory deliveries.</p>
                </div>
                <a class="btn-premium" href="{{ route('add_inwardgatepass') }}">
                    <i class="bi bi-plus-lg fs-6"></i> <span>Create Gatepass</span>
                </a>
            </div>

            {{-- ─── Pending POs Section (only for non-super-admin users) ─── --}}
            @if(!$isSuperAdmin && $pendingPOs->isNotEmpty())
            <div class="card card-premium mb-4" style="border-left: 4px solid #f59e0b;">
                <div class="card-body p-0">
                    <div class="d-flex align-items-center justify-content-between px-4 pt-3 pb-2" style="background:#fffbeb; border-radius:16px 16px 0 0;">
                        <div>
                            <h5 class="mb-0 fw-bold" style="color:#b45309;">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Pending Purchase Orders — Awaiting Inward
                            </h5>
                            <p class="text-muted small mb-0 mt-1">These POs are assigned to your warehouse. Create an Inward Gatepass when stock arrives.</p>
                        </div>
                        <span class="badge rounded-pill" style="background:#f59e0b; color:white; font-size:0.85rem; padding:8px 16px;">
                            {{ $pendingPOs->count() }} PO(s) Pending
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table erp-table w-100 mb-0">
                            <thead>
                                <tr>
                                    <th>PO Number</th>
                                    <th>Vendor</th>
                                    <th>Warehouse</th>
                                    <th>Expected Date</th>
                                    <th class="text-center">Total Qty</th>
                                    <th class="text-center">Received</th>
                                    <th class="text-center">Pending</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingPOs as $po)
                                <tr>
                                    <td>
                                        <span class="fw-bold" style="color:#0f172a;">{{ $po->po_number }}</span>
                                    </td>
                                    <td>
                                        <i class="bi bi-person-circle text-muted me-1"></i>
                                        {{ $po->vendor->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <i class="bi bi-building text-muted me-1"></i>
                                        {{ $po->warehouse->warehouse_name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <span class="{{ \Carbon\Carbon::parse($po->expected_date)->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            {{ \Carbon\Carbon::parse($po->expected_date)->format('d M, Y') }}
                                            @if(\Carbon\Carbon::parse($po->expected_date)->isPast())
                                                <span class="badge bg-danger ms-1" style="font-size:0.65rem;">Overdue</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-center fw-semibold">{{ number_format($po->total_ordered, 2) }}</td>
                                    <td class="text-center">
                                        <span class="text-success fw-semibold">{{ number_format($po->total_received, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold" style="color:#d97706;">{{ number_format($po->total_pending, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($po->status === 'partially_received')
                                            <span class="status-badge" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;">
                                                <i class="bi bi-arrow-repeat me-1"></i> Partial
                                            </span>
                                        @else
                                            <span class="status-badge badge-pending">
                                                <i class="bi bi-clock-history me-1"></i> Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('inward-gatepass.from-po', $po->id) }}"
                                           class="action-btn btn-bill"
                                           title="Create Inward Gatepass from this PO">
                                            <i class="bi bi-box-arrow-in-down"></i> Receive Stock
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Inward Gatepass Table Card -->
            <div class="card card-premium">
                <div class="card-body p-0 pt-4 pb-4">
                    <div class="table-responsive">
                        <table id="gatepass-table" class="table erp-table w-100">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th>Branch</th>
                                    <th>Warehouse</th>
                                    <th>Vendor</th>
                                    <th>Date</th>
                                    <th>Note</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($gatepasses as $gp)
                                    <tr>
                                        <td class="text-center fw-bold" style="color: #64748b;">#{{ str_pad($gp->id, 4, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 32px; height: 32px; font-size: 12px;">
                                                    {{ strtoupper(substr($gp->branch->name ?? 'B', 0, 1)) }}
                                                </div>
                                                <span class="fw-semibold text-dark">{{ $gp->branch->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold">{{ $gp->warehouse->warehouse_name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-person-circle text-muted fs-5"></i>
                                                <span>{{ $gp->vendor->name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center text-muted">
                                                <i class="bi bi-calendar-event me-2"></i>
                                                {{ \Carbon\Carbon::parse($gp->gatepass_date)->format('d M, Y') }}
                                            </div>
                                        </td>
                                        <td><span class="text-truncate d-inline-block text-muted" style="max-width: 150px;" title="{{ $gp->note }}">{{ $gp->note ?? '-' }}</span></td>
                                        <td class="text-center">
                                            @if ($gp->display_status == 'pending')
                                                <span class="status-badge badge-pending"><i class="bi bi-clock-history me-1"></i> Pending</span>
                                            @elseif($gp->display_status == 'completed')
                                                <span class="status-badge badge-completed"><i class="bi bi-check2-circle me-1 fs-6"></i> Completed</span>
                                            @elseif($gp->display_status == 'linked')
                                                <span class="status-badge badge-linked"><i class="bi bi-link-45deg me-1 fs-6"></i> Linked</span>
                                            @elseif($gp->display_status == 'cancelled')
                                                <span class="status-badge badge-cancelled"><i class="bi bi-x-octagon me-1"></i> Cancelled</span>
                                            @else
                                                <span class="status-badge bg-secondary text-white">{{ $gp->display_status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <!-- CONSOLIDATED ACTIONS -->
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-dark dropdown-toggle dropdown-toggle-split" data-boundary="window" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i> More
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end shadow-lg action-dropdown">
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('InwardGatepass.show', $gp->id) }}">
                                                            <i class="bi bi-eye text-primary"></i> View
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('InwardGatepass.edit', $gp->id) }}">
                                                            <i class="bi bi-pencil text-warning"></i> Edit
                                                        </a>
                                                    </li>
                                                    @if(!$gp->purchase_id)
                                                    <li>
                                                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('add_bill', $gp->id) }}">
                                                            <i class="bi bi-receipt text-success"></i> Create Bill
                                                        </a>
                                                    </li>
                                                    @endif
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('InwardGatepass.destroy', $gp->id) }}" method="POST" class="m-0">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 text-danger delete-btn">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

                    {{-- DataTable --}}
                    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
                    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
                    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

                    <script>
                        $(document).ready(function() {
                            $('#gatepass-table').DataTable({
                                "pageLength": 10,
                                "lengthMenu": [5, 10, 25, 50, 100],
                                "order": [
                                    [0, 'desc']
                                ],
                                "language": {
                                    "search": "Search Gatepass:",
                                    "lengthMenu": "Show _MENU_ entries"
                                }
                            });

                            // --- CONSOLIDATED ACTIONS DROPDOWN LOGIC ---
                            var closeTimer;

                            function openDropdown($el) {
                                var $dropdown = $el.data('dropdown-menu');
                                if (!$dropdown || $dropdown.length === 0) {
                                    $dropdown = $el.closest('.btn-group').find('.dropdown-menu');
                                    $el.data('dropdown-menu', $dropdown);
                                }
                                if (!$dropdown || $dropdown.length === 0) return;

                                $('.dropdown-appended').not($dropdown).hide();

                                if (!$dropdown.hasClass('dropdown-appended')) {
                                    $('body').append($dropdown);
                                    $dropdown.addClass('dropdown-appended');
                                }
                                
                                $dropdown.show();
                                var offset = $el.offset();
                                var leftPos = offset.left - ($dropdown.outerWidth() - $el.outerWidth());
                                if (leftPos < 0) leftPos = 10;

                                $dropdown.css({
                                    'position': 'absolute',
                                    'top': offset.top + $el.outerHeight(),
                                    'left': leftPos,
                                    'z-index': 10500
                                });
                            }

                            // Open on Hover
                            $(document).on('mouseenter', '.dropdown-toggle', function() {
                                clearTimeout(closeTimer);
                                openDropdown($(this));
                            });

                            // Open on Click
                            $(document).on('click', '.dropdown-toggle', function (e) {
                                e.stopPropagation();
                                openDropdown($(this));
                            });

                            // Close when clicking outside
                            $(document).on('click', function (e) {
                                if (!$(e.target).closest('.dropdown-toggle').length && !$(e.target).closest('.dropdown-menu').length) {
                                    $('.dropdown-appended').hide();
                                }
                            });

                            // Small delay to move from button to menu
                            $(document).on('mouseleave', '.dropdown-toggle', function() {
                                var $el = $(this);
                                var $dropdown = $el.data('dropdown-menu');
                                if ($dropdown && $dropdown.is(':visible')) {
                                    closeTimer = setTimeout(function() {
                                        $dropdown.hide();
                                    }, 150); 
                                }
                            });

                            // Keep open if moving into menu
                            $(document).on('mouseenter', '.dropdown-appended', function() {
                                clearTimeout(closeTimer);
                            });

                            // Close when leaving menu
                            $(document).on('mouseleave', '.dropdown-appended', function() {
                                $(this).hide();
                            });
                        });
                    </script>

        </div>
    </div>
</div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Delete confirm
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');

        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to delete this gatepass!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Success alert after delete
    @if (session('success'))
        Swal.fire({
            title: 'Deleted!',
            text: "{{ session('success') }}",
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    @endif
</script>
