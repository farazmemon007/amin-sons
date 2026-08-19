@extends('admin_panel.layout.app')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        /* ═══════════════════════════════════════════════════════════
           AMEEN & SONS ERP — BRANCH MANAGEMENT
           ═══════════════════════════════════════════════════════════ */
        :root {
            --theme-navy: #1e3a5f;
            --theme-navy-light: #2c5282;
            --theme-gold: #c8973a;
            --theme-border: #e2e8f0;
            --theme-bg: #f8fafc;
        }

        .branch-wrapper {
            padding: 4px 6px 24px;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        /* 1. Header Bar */
        .branch-header {
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

        .branch-title {
            font-size: 17px;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.2px;
            color: #ffffff !important;
        }

        .branch-subtitle {
            font-size: 11.5px;
            color: rgba(255, 255, 255, 0.85) !important;
            margin-top: 2px;
        }

        .btn-create-branch {
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
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-create-branch:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 159, 110, 0.4);
        }

        /* 2. Main Card */
        .branch-main-card {
            background: #ffffff;
            border: 1px solid var(--theme-border);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            padding: 20px;
        }

        /* 3. Table Styling */
        #default-datatable {
            width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }

        #default-datatable thead th {
            background: #f8fafc !important;
            color: #64748b !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.55px !important;
            padding: 12px 14px !important;
            border-top: none !important;
            border-bottom: 2px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        #default-datatable tbody td {
            padding: 12px 14px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9 !important;
            font-size: 13px !important;
            color: #334155 !important;
            background: #ffffff !important;
        }

        #default-datatable tbody tr:hover td {
            background: #f8fafc !important;
        }

        /* Micro-Badges */
        .badge-id {
            font-family: monospace;
            font-size: 11px;
            font-weight: 700;
            background: #f1f5f9;
            color: #1e3a5f;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }

        .badge-status-active {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-status-inactive {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Action Buttons */
        .btn-action-edit {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            color: #1e3a5f;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-action-edit:hover {
            background: #1e3a5f;
            color: #ffffff;
            border-color: #1e3a5f;
        }

        .btn-action-delete {
            background: #ffffff;
            border: 1.5px solid #fecaca;
            color: #dc2626;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .btn-action-delete:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
        }

        /* Modal Styling */
        #exampleModal .modal-content {
            border-radius: 12px;
            overflow: hidden;
            border: none;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.2);
        }

        #exampleModal .modal-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header-icon {
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--theme-gold);
            font-size: 15px;
        }

        .form-label-custom {
            font-size: 11.5px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-control-custom {
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 13.5px;
            color: #1e293b;
            transition: all 0.15s ease;
        }

        .form-control-custom:focus {
            border-color: #1e3a5f;
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.15);
            outline: none;
        }
    </style>
@endsection

@section('content')
<div class="main-content">
    <div class="branch-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Top Header Bar --}}
            <div class="branch-header">
                <div>
                    <h1 class="branch-title">
                        <i class="fas fa-code-branch" style="color: var(--theme-gold);"></i>
                        Branch Management
                    </h1>
                    <div class="branch-subtitle">
                        Configure organizational branches, physical locations, contact numbers, and operational statuses
                    </div>
                </div>
                <div>
                    @if(auth()->user() && auth()->user()->hasRole('super admin'))
                        <button type="button" class="btn-create-branch" id="reset-form">
                            <i class="fas fa-plus"></i> Add New Branch
                        </button>
                    @endif
                </div>
            </div>

            {{-- 2. Alerts --}}
            @if (session('success'))
                <div class="alert alert-success py-2 px-3 mb-3 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #10b981 !important; border-radius: 6px;">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger py-2 px-3 mb-3 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #ef4444 !important; border-radius: 6px;">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                </div>
            @endif

            {{-- 3. Main Data Card --}}
            <div class="branch-main-card">
                <div class="table-responsive">
                    <table id="default-datatable" class="table">
                        <thead>
                            <tr>
                                <th style="width: 6%; text-align: center;">#</th>
                                <th style="width: 22%;">Branch Name</th>
                                <th style="width: 26%;">Address / Location</th>
                                <th style="width: 14%;">Contact Phone</th>
                                <th style="width: 12%; text-align: center;">Status</th>
                                <th style="width: 20%; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($branches as $branch)
                                <tr data-id="{{ $branch->id }}" data-status="{{ $branch->status ?? 'active' }}">
                                    <td class="text-center">
                                        <span class="badge-id id">#{{ $branch->id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center" style="gap: 8px;">
                                            <div style="width: 28px; height: 28px; background: rgba(30, 58, 95, 0.08); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #1e3a5f; font-size: 12px;">
                                                <i class="fas fa-store"></i>
                                            </div>
                                            <span class="font-weight-bold text-dark name" style="font-size: 13.5px;">{{ $branch->name }}</span>
                                        </div>
                                    </td>
                                    <td class="address" style="color: #475569; font-size: 12.5px;">
                                        <i class="fas fa-map-marker-alt text-danger mr-1" style="font-size: 11px;"></i>
                                        {{ $branch->address ?? '-' }}
                                    </td>
                                    <td class="number">
                                        <span style="font-family: monospace; font-size: 12px; font-weight: 600; color: #1e293b;">
                                            <i class="fas fa-phone-alt text-primary mr-1" style="font-size: 10px;"></i>
                                            {{ $branch->number ?? '-' }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="d-none status-val">{{ $branch->status ?? 'active' }}</span>
                                        @if(($branch->status ?? 'active') === 'active')
                                            <span class="badge-status-active">
                                                <i class="fas fa-check-circle"></i> Active
                                            </span>
                                        @else
                                            <span class="badge-status-inactive">
                                                <i class="fas fa-ban"></i> Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="d-inline-flex align-items-center" style="gap: 6px;">
                                            @if(auth()->user() && auth()->user()->hasRole('super admin'))
                                                <button type="button" class="btn-action-edit edit-btn" title="Edit Branch Profile">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <a href="{{ route('branch.delete', $branch->id) }}"
                                                    class="btn-action-delete delete-btn"
                                                    data-url="{{ route('branch.delete', $branch->id) }}"
                                                    data-msg="Are you sure you want to delete this branch?"
                                                    data-method="DELETE" onclick="confirmedBox(this, event)" title="Delete Branch">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </a>
                                            @else
                                                <span class="text-muted small"><i class="fas fa-lock mr-1"></i> Read Only</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>{{-- end branch-main-card --}}

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     BRANCH ADD / EDIT MODAL (Ameen & Sons Theme)
══════════════════════════════════════════════════ --}}
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <div class="modal-header-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0 text-white" id="exampleModalLabel" style="font-size: 15px; color: #ffffff !important;">
                            Add New Branch
                        </h5>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none; font-size: 20px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form class="myform" action="{{ route('branch.store') }}" method="POST">
                @csrf
                <input type="hidden" id="branch_id" />
                
                <div class="modal-body p-4" style="background: #f8fafc;">
                    <div class="mb-3">
                        <label for="name" class="form-label-custom">
                            <i class="fas fa-building text-primary"></i> Branch Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control form-control-custom" id="name" placeholder="e.g., Karachi Main Branch" required />
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label-custom">
                            <i class="fas fa-map-marker-alt text-danger"></i> Address / Physical Location <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="address" class="form-control form-control-custom" id="address" placeholder="e.g., Techno City, Saddar, Karachi" required />
                    </div>
                    
                    <div class="mb-3">
                        <label for="number" class="form-label-custom">
                            <i class="fas fa-phone text-success"></i> Contact Phone Number <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="number" class="form-control form-control-custom" id="number" placeholder="e.g., 03001234567" required />
                    </div>

                    <div class="mb-2">
                        <label for="status" class="form-label-custom">
                            <i class="fas fa-toggle-on text-info"></i> Operational Status <span class="text-danger">*</span>
                        </label>
                        <select name="status" id="status" class="form-control form-control-custom" required>
                            <option value="active">🟢 Active (Operational &amp; Visible)</option>
                            <option value="inactive">🔴 Inactive (Disabled)</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer bg-white py-2 px-3 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 font-weight-bold" data-dismiss="modal" style="border-radius: 6px;">
                        <i class="fas fa-times mr-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-sm px-4 font-weight-bold save-btn" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: #ffffff; border-radius: 6px; box-shadow: 0 2px 6px rgba(30,58,95,0.25);">
                        <i class="fas fa-save mr-1"></i> Save Branch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
    <!-- DataTable JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="{{ asset('assets/js/mycode.js') }}"></script>
    <script>
        $(document).on('submit', '.myform', function(e) {
            e.preventDefault();
            var formdata = new FormData(this);
            var url = $(this).attr('action');
            var method = $(this).attr('method');
            $(this).find(':submit').attr('disabled', true);
            myAjax(url, formdata, method);
        });

        // Click Edit Button
        $(document).on('click', '.edit-btn', function() {
            var tr = $(this).closest("tr");
            var id = tr.find(".id").text().replace('#', '').trim();
            var name = tr.find(".name").text().trim();
            var address = tr.find(".address").text().trim();
            var number = tr.find(".number").text().trim();
            var status = tr.data('status') || tr.find(".status-val").text().trim() || 'active';

            $('#branch_id').val(id);
            $('#name').val(name);
            $('#address').val(address);
            $('#number').val(number);
            $('#status').val(status);

            $('#exampleModalLabel').html('<i class="fas fa-edit mr-1"></i> Edit Branch Profile');
            $('.myform').attr('action', '{{ url("branch") }}/' + id);
            
            // Remove previous _method if any, then append PUT
            $('.myform input[name="_method"]').remove();
            $('.myform').append('<input type="hidden" name="_method" value="PUT">');

            $("#exampleModal").modal("show");
        });

        // Click Create Button
        $(document).on('click', '#reset-form', function() {
            $('#branch_id').val('');
            $('#name').val('');
            $('#address').val('');
            $('#number').val('');
            $('#status').val('active');

            $('#exampleModalLabel').html('<i class="fas fa-plus-circle mr-1"></i> Add New Branch');
            $('.myform').attr('action', '{{ route("branch.store") }}');
            $('.myform input[name="_method"]').remove();

            $("#exampleModal").modal("show");
        });

        function confirmedBox(element, event) {
            event.preventDefault();
            const message = element.getAttribute('data-msg') || 'Are you sure?';
            const url = element.getAttribute('href');

            Swal.fire({
                title: 'Confirm Deletion',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    </script>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif
    <script>
        $(document).ready(function() {
            $('#default-datatable').DataTable({
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50, 100],
                "order": [
                    [0, 'asc']
                ],
                "language": {
                    "search": "Search Branch:",
                    "lengthMenu": "Show _MENU_ entries"
                }
            });
        });
    </script>
@endsection
