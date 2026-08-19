@extends('admin_panel.layout.app')
@section('content')

<style>
    :root {
        --coa-navy: #1e3a5f;
        --coa-navy-dark: #0f1f38;
        --coa-navy-light: #2c5282;
        --coa-gold: #c8973a;
        --coa-emerald: #0d9f6e;
        --coa-border: #e2e8f0;
    }

    .wh-mgmt-wrapper {
        padding: 12px 0 30px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .wh-mgmt-header {
        background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 60%, var(--coa-navy-light) 100%);
        border-radius: 10px;
        padding: 16px 22px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        box-shadow: 0 4px 15px rgba(15, 31, 56, 0.15);
        margin-bottom: 18px;
    }

    .wh-mgmt-icon {
        width: 44px;
        height: 44px;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--coa-gold);
        border: 1px solid rgba(200, 151, 58, 0.3);
        flex-shrink: 0;
    }

    .wh-mgmt-title {
        font-size: 18px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        line-height: 1.2;
    }

    .wh-mgmt-sub {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.82);
        margin-top: 3px;
    }

    .btn-coa-add {
        background: linear-gradient(135deg, var(--coa-emerald) 0%, #059669 100%);
        color: #ffffff !important;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(13, 159, 110, 0.3);
        transition: all 0.15s;
    }

    .btn-coa-add:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
    }
</style>

<div class="main-content">
    <div class="wh-mgmt-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Corporate Header Bar --}}
            <div class="wh-mgmt-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="wh-mgmt-icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div>
                        <h4 class="wh-mgmt-title">Warehouse Management</h4>
                        <div class="wh-mgmt-sub">
                            <span><i class="fas fa-cubes" style="color: var(--coa-gold);"></i> Manage Physical Storage Locations & Assign Staff Incharge</span>
                        </div>
                    </div>
                </div>
                <div>
                    @can('warehouse.create')
                    <button class="btn-coa-add" data-toggle="modal" data-target="#warehouseModal" onclick="clearWarehouse()">
                        <i class="fas fa-plus"></i> + Add Warehouse
                    </button>
                    @endcan
                </div>
            </div>

            {{-- 2. Alert Messages --}}
            @if (session()->has('success'))
                <div class="alert alert-success py-2 px-3 mb-3 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #10b981 !important; border-radius: 6px;">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif

            {{-- 3. Data Table Card --}}
            <div class="card shadow-sm border-0" style="border-radius: 9px; overflow: hidden; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table datanew mb-0" style="font-size: 12.5px;">
                            <thead style="background: #f8fafc;">
                                <tr>
                                    <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">#</th>
                                    <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">Warehouse Name</th>
                                    <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">Location</th>
                                    <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">Remarks</th>
                                    <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1;">Assigned Staff</th>
                                    <th style="padding: 11px 14px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #475569; border-bottom: 1.5px solid #cbd5e1; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($warehouses as $key => $w)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 12px 14px; vertical-align: middle; font-weight: 700; color: #64748b;">{{ $key+1 }}</td>
                                    <td style="padding: 12px 14px; vertical-align: middle;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width: 28px; height: 28px; border-radius: 6px; background: #eff6ff; display: flex; align-items: center; justify-content: center; color: var(--coa-navy); font-size: 11px; font-weight: 800;">
                                                <i class="fas fa-warehouse"></i>
                                            </div>
                                            <strong style="color: #0f172a; font-size: 13px;">{{ $w->warehouse_name }}</strong>
                                        </div>
                                    </td>
                                    <td style="padding: 12px 14px; vertical-align: middle; color: #475569;">
                                        <i class="fas fa-map-marker-alt text-danger mr-1"></i> {{ $w->location ?? '-' }}
                                    </td>
                                    <td style="padding: 12px 14px; vertical-align: middle; color: #64748b; font-size: 12px;">{{ $w->remarks ?? '-' }}</td>
                                    <td style="padding: 12px 14px; vertical-align: middle;">
                                        @foreach($w->assignedUsers as $u)
                                            <span class="badge {{ $u->pivot->is_incharge ? 'badge-warning text-dark' : 'badge-light text-dark border' }} me-1" style="font-size: 10.5px; padding: 3px 7px;">
                                                @if($u->pivot->is_incharge) <i class="fas fa-star text-warning mr-1"></i> @endif
                                                {{ $u->name }}
                                            </span>
                                        @endforeach
                                        @if($w->assignedUsers->isEmpty())
                                            <span class="text-muted small">No staff assigned</span>
                                        @endif
                                    </td>
                                    <td style="padding: 12px 14px; vertical-align: middle; text-align: center;">
                                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                                            <button class="btn btn-sm btn-info assign-staff-btn py-1 px-2 font-weight-bold"
                                                data-warehouse-id="{{ $w->id }}"
                                                data-warehouse-name="{{ $w->warehouse_name }}"
                                                style="border-radius: 4px; font-size: 11px;">
                                                <i class="fas fa-users-cog"></i> Staff
                                            </button>
                                            @can('warehouse.edit')
                                            <button class="btn btn-sm btn-primary edit-warehouse-btn py-1 px-2 font-weight-bold"
                                                data-id="{{ $w->id }}"
                                                data-name="{{ $w->warehouse_name }}"
                                                data-location="{{ $w->location }}"
                                                data-remarks="{{ $w->remarks }}"
                                                data-branches="{{ $w->branches->pluck('id')->implode(',') }}"
                                                data-toggle="modal"
                                                data-target="#warehouseModal"
                                                style="background: var(--coa-navy); border-color: var(--coa-navy); border-radius: 4px; font-size: 11px;">
                                                <i class="fas fa-pen"></i> Edit
                                            </button>
                                            @endcan
                                            @can('warehouse.delete')
                                            <a href="{{ url('warehouse/delete/'.$w->id) }}" class="btn btn-sm btn-danger py-1 px-2 font-weight-bold" onclick="return confirm('Delete this warehouse?')" style="border-radius: 4px; font-size: 11px;">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            @endcan
                                        </div>
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
</div>

{{-- Add/Edit Warehouse Modal --}}
<div class="modal fade" id="warehouseModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ url('warehouse/store') }}" method="POST">
            @csrf
            <input type="hidden" name="id" id="warehouse_id">
            <div class="modal-content" style="border-radius: 10px; overflow: hidden; border: none;">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 100%);">
                    <h6 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-warehouse mr-1"></i> Add / Edit Warehouse
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-white">
                    @if(Auth::check() && Auth::user()->hasRole('super admin'))
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark small mb-1">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" id="branch_id" class="form-control" style="border-radius: 6px;">
                                <option value="">-- Select Branch --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name ?? 'Branch '.$b->id }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="branch_id" id="branch_id" value="{{ Auth::user()->branch_id }}">
                    @endif
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">Warehouse Name <span class="text-danger">*</span></label>
                        <input class="form-control" name="warehouse_name" id="warehouse_name" placeholder="e.g., Central Warehouse A" required style="border-radius: 6px;">
                    </div>
                    <div class="d-none"><input class="form-control" name="creater_id" value="{{ Auth()->user()->id }}" required></div>
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">Location / Address</label>
                        <input class="form-control" name="location" id="location" placeholder="e.g., Plot #12, Industrial Area" style="border-radius: 6px;">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark small mb-1">Remarks</label>
                        <textarea class="form-control" name="remarks" id="remarks" rows="2" placeholder="Additional notes..." style="border-radius: 6px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-3" data-dismiss="modal" style="border-radius: 5px;">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3" style="background: var(--coa-navy); border-color: var(--coa-navy); border-radius: 5px;">
                        <i class="fas fa-save mr-1"></i> Save Warehouse
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ERP: Assign Staff to Warehouse Modal --}}
<div class="modal fade" id="assignStaffModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 10px; overflow: hidden; border: none;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 100%);">
                <h6 class="modal-title font-weight-bold mb-0">
                    <i class="fas fa-users-cog mr-2"></i> Assign Staff — <span id="assignWarehouseName">Warehouse</span>
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="alert alert-info border-0 mb-3 small" style="background: #eff6ff; border-left: 4px solid #3b82f6 !important; border-radius: 6px;">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Cross-Branch Support:</strong> Super Admin can assign a single Incharge to warehouses across multiple branches.
                </div>

                <div id="assignStaffLoading" class="text-center py-4" style="display:none;">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted small font-weight-bold">Loading staff list...</p>
                </div>

                <div id="assignStaffContent">
                    <input type="hidden" id="assignWarehouseId">

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">
                            <i class="fas fa-star text-warning me-1"></i> Warehouse Incharge (Main Responsible)
                        </label>
                        <select id="inchargeSelect" class="form-control" style="border-radius: 6px;">
                            <option value="">-- Select Incharge (Optional) --</option>
                        </select>
                        <small class="text-muted mt-1 d-block">The Incharge is the primary responsible person. Marked with ⭐ in the list.</small>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark small mb-1">
                            <i class="fas fa-users me-1"></i> Assigned Staff Members
                        </label>
                        <div id="staffCheckboxContainer" class="p-3 border rounded" style="max-height: 250px; overflow-y: auto; background: #f8fafc; border-radius: 6px;">
                            <p class="text-muted small">Loading...</p>
                        </div>
                        <small class="text-muted mt-1 d-block">Check all users who should have access to this warehouse. Incharge is automatically included.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light p-3 border-top">
                <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-3" data-dismiss="modal" style="border-radius: 5px;">Cancel</button>
                <button type="button" class="btn btn-sm btn-success font-weight-bold px-3" id="saveStaffBtn" style="border-radius: 5px;">
                    <i class="fas fa-save me-1"></i> Save Assignment
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
    <script>
    function clearWarehouse(){ $('#warehouse_id').val(''); $('#warehouse_name').val(''); $('#location').val(''); $('#remarks').val(''); }

    // Handle Edit button click
    $(document).on('click', '.edit-warehouse-btn', function () {
        $('#warehouse_id').val($(this).data('id'));
        $('#warehouse_name').val($(this).data('name'));
        $('#location').val($(this).data('location'));
        $('#remarks').val($(this).data('remarks'));
        var branches = $(this).data('branches') || '';
        if ($('#branch_id').length) {
            if (branches) {
                var first = String(branches).split(',')[0];
                $('#branch_id').val(first);
            } else {
                $('#branch_id').val('');
            }
        }
    });

    // ✅ ERP: Assign Staff Modal
    $(document).on('click', '.assign-staff-btn', function () {
        var warehouseId   = $(this).data('warehouse-id');
        var warehouseName = $(this).data('warehouse-name');

        $('#assignWarehouseId').val(warehouseId);
        $('#assignWarehouseName').text(warehouseName);
        $('#assignStaffLoading').show();
        $('#assignStaffContent').hide();

        $('#assignStaffModal').modal('show');

        // Fetch current assigned users via AJAX
        $.get('/warehouse/' + warehouseId + '/users', function (data) {
            $('#assignStaffLoading').hide();
            $('#assignStaffContent').show();

            // Build Incharge dropdown
            var $incharge = $('#inchargeSelect');
            $incharge.empty().append('<option value="">-- Select Incharge (Optional) --</option>');
            data.users.forEach(function (u) {
                var selected = (data.incharge_id == u.id) ? 'selected' : '';
                $incharge.append(
                    '<option value="' + u.id + '" ' + selected + '>' +
                    u.name + ' (Branch: ' + (u.branch_id || 'N/A') + ')' +
                    '</option>'
                );
            });

            // Build Staff checkboxes
            var $container = $('#staffCheckboxContainer');
            $container.empty();
            if (data.users.length === 0) {
                $container.append('<p class="text-muted">No users available.</p>');
                return;
            }
            data.users.forEach(function (u) {
                var checked  = data.assigned_ids.includes(u.id) ? 'checked' : '';
                var isIncharge = (data.incharge_id == u.id);
                $container.append(
                    '<div class="form-check mb-2 p-2 rounded ' + (checked ? 'bg-light border' : '') + '">' +
                        '<input class="form-check-input staff-checkbox" type="checkbox" ' +
                            'value="' + u.id + '" id="staff_' + u.id + '" ' + checked + '>' +
                        '<label class="form-check-label d-flex align-items-center gap-2" for="staff_' + u.id + '">' +
                            (isIncharge ? '<i class="fas fa-star text-warning"></i>' : '') +
                            '<strong>' + u.name + '</strong>' +
                            '<small class="text-muted">(' + u.email + ' | Branch: ' + (u.branch_id || 'N/A') + ')</small>' +
                        '</label>' +
                    '</div>'
                );
            });

            // Auto-check incharge in staff list when incharge changes
            $('#inchargeSelect').off('change').on('change', function () {
                var inchargeId = $(this).val();
                if (inchargeId) {
                    $('#staff_' + inchargeId).prop('checked', true);
                }
            });
        }).fail(function () {
            $('#assignStaffLoading').hide();
            $('#assignStaffContent').show();
            $('#staffCheckboxContainer').html('<p class="text-danger">Failed to load users. Please try again.</p>');
        });
    });

    // Save Assignment
    $('#saveStaffBtn').on('click', function () {
        var warehouseId = $('#assignWarehouseId').val();
        var inchargeId  = $('#inchargeSelect').val();
        var userIds     = [];
        $('.staff-checkbox:checked').each(function () {
            userIds.push($(this).val());
        });

        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

        $.ajax({
            url: '{{ route("warehouse.assign.users") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: {
                warehouse_id: warehouseId,
                incharge_id:  inchargeId || null,
                user_ids:     userIds,
            },
            success: function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Saved!', text: res.success, timer: 2000, showConfirmButton: false })
                        .then(() => location.reload());
                }
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to save assignment.' });
            },
            complete: function () {
                $('#saveStaffBtn').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Assignment');
            }
        });
    });

    $('.datanew').DataTable();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
