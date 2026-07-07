@extends('admin_panel.layout.app')
@section('content')

<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">

            <div class="page-header row">
                <div class="page-title col-lg-6">
                    <h4>Warehouse Management</h4>
                    <h6>Manage Warehouses & Assign Staff Responsibility</h6>
                </div>
                <div class="page-btn d-flex justify-content-end col-lg-6">
                    @can('warehouse.create')
                    <button class="btn btn-outline-primary mb-2" data-toggle="modal" data-target="#warehouseModal" onclick="clearWarehouse()">
                        <i class="fas fa-plus me-1"></i> Add Warehouse
                    </button>
                    @endcan
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                    <div class="alert alert-success"><strong>Success!</strong> {{ session('success') }}</div>
                    @endif

                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Created By</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Remarks</th>
                                <th>Assigned Staff</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($warehouses as $key => $w)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $w->user->name ?? '-' }}</td>
                                <td><strong>{{ $w->warehouse_name }}</strong></td>
                                <td>{{ $w->location ?? '-' }}</td>
                                <td>{{ $w->remarks ?? '-' }}</td>
                                <td>
                                    @foreach($w->assignedUsers as $u)
                                        <span class="badge {{ $u->pivot->is_incharge ? 'bg-warning text-dark' : 'bg-secondary' }} me-1">
                                            @if($u->pivot->is_incharge) <i class="fas fa-star"></i> @endif
                                            {{ $u->name }}
                                        </span>
                                    @endforeach
                                    @if($w->assignedUsers->isEmpty())
                                        <span class="text-muted small">No staff assigned</span>
                                    @endif
                                </td>
                                <td class="d-flex gap-1 flex-wrap">
                                    @can('warehouse.edit')
                                    <button class="btn btn-sm btn-primary edit-warehouse-btn"
                                        data-id="{{ $w->id }}"
                                        data-name="{{ $w->warehouse_name }}"
                                        data-location="{{ $w->location }}"
                                        data-remarks="{{ $w->remarks }}"
                                        data-branches="{{ $w->branches->pluck('id')->implode(',') }}"
                                        data-toggle="modal"
                                        data-target="#warehouseModal">
                                        <i class="fas fa-pen"></i> Edit
                                    </button>
                                    @endcan
                                    @can('warehouse.delete')
                                    <a href="{{ url('warehouse/delete/'.$w->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                    @endcan
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

{{-- Add/Edit Warehouse Modal --}}
<div class="modal fade" id="warehouseModal">
    <div class="modal-dialog">
        <form action="{{ url('warehouse/store') }}" method="POST">
            @csrf
            <input type="hidden" name="id" id="warehouse_id">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add/Edit Warehouse</h5></div>
                <div class="modal-body">
                    @if(Auth::check() && Auth::user()->hasRole('super admin'))
                        <div class="mb-2">
                            <label>Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control">
                                <option value="">-- Select Branch --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name ?? 'Branch '.$b->id }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="branch_id" id="branch_id" value="{{ Auth::user()->branch_id }}">
                    @endif
                    <div class="mb-2"><input class="form-control" name="warehouse_name" id="warehouse_name" placeholder="Name" required></div>
                    <div class="mb-2 d-none"><input class="form-control" name="creater_id" value="{{ Auth()->user()->id }}" required></div>
                    <div class="mb-2"><input class="form-control" name="location" id="location" placeholder="Location"></div>
                    <div class="mb-2"><textarea class="form-control" name="remarks" id="remarks" placeholder="Remarks"></textarea></div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
            </div>
        </form>
    </div>
</div>

{{-- ✅ ERP: Assign Staff to Warehouse Modal --}}
<div class="modal fade" id="assignStaffModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#0066cc,#004499);color:#fff;">
                <h5 class="modal-title">
                    <i class="fas fa-users-cog mr-2"></i>
                    Assign Staff — <span id="assignWarehouseName">Warehouse</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 mb-3" style="background:#e8f4ff;">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Cross-Branch Support:</strong> Super Admin can assign a single Incharge to warehouses across multiple branches.
                </div>

                <div id="assignStaffLoading" class="text-center py-4" style="display:none;">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Loading staff list...</p>
                </div>

                <div id="assignStaffContent">
                    <input type="hidden" id="assignWarehouseId">

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-star text-warning me-1"></i> Warehouse Incharge (Main Responsible)
                        </label>
                        <select id="inchargeSelect" class="form-select">
                            <option value="">-- Select Incharge (Optional) --</option>
                        </select>
                        <div class="form-text">The Incharge is the primary responsible person. Marked with ⭐ in the list.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-users me-1"></i> Assigned Staff Members
                        </label>
                        <div id="staffCheckboxContainer" class="p-3 border rounded" style="max-height:280px;overflow-y:auto;background:#f8fafc;">
                            <p class="text-muted">Loading...</p>
                        </div>
                        <div class="form-text">Check all users who should have access to this warehouse. Incharge is automatically included.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveStaffBtn">
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
