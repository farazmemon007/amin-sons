@extends('admin_panel.layout.app')
@section('content')

    <style>
        :root {
            --user-primary: #6366f1;
            --user-success: #22c55e;
            --user-warning: #f59e0b;
            --user-danger: #ef4444;
            --user-info: #0ea5e9;
            --user-bg: #f8fafc;
            --user-card: #ffffff;
            --user-border: #e2e8f0;
            --user-text: #1e293b;
            --user-muted: #64748b;
        }

        .page-header {
            margin-bottom: 28px;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--user-text);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title i {
            color: var(--user-primary);
        }

        .page-subtitle {
            color: var(--user-muted);
            font-size: 0.9rem;
            margin-top: 4px;
        }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--user-card);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid var(--user-border);
            transition: all 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 12px;
        }

        .stat-card.primary .stat-icon {
            background: #eef2ff;
            color: var(--user-primary);
        }

        .stat-card.success .stat-icon {
            background: #dcfce7;
            color: var(--user-success);
        }

        .stat-card.warning .stat-icon {
            background: #fef3c7;
            color: var(--user-warning);
        }

        .stat-card.info .stat-icon {
            background: #e0f2fe;
            color: var(--user-info);
        }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--user-text);
        }

        .stat-card .stat-label {
            font-size: 0.8rem;
            color: var(--user-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Users Card Container */
        .users-card {
            background: var(--user-card);
            border-radius: 16px;
            border: 1px solid var(--user-border);
            overflow: hidden;
        }

        .users-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--user-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            max-width: 350px;
            flex: 1;
        }

        .search-box input {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 2px solid var(--user-border);
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.2s;
            background: #f8fafc;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--user-primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            background: white;
        }

        .search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--user-muted);
        }

        .btn-create {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.35);
            color: white;
        }

        /* User Cards Grid */
        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 20px;
            padding: 24px;
        }

        .user-card {
            background: var(--user-card);
            border: 1px solid var(--user-border);
            border-radius: 14px;
            padding: 20px;
            transition: all 0.2s;
        }

        .user-card:hover {
            border-color: var(--user-primary);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.12);
            transform: translateY(-2px);
        }

        .user-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .user-avatar {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 700;
            color: white;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        .user-info {
            flex: 1;
            margin-left: 14px;
        }

        .user-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--user-text);
            margin: 0;
        }

        .user-email {
            font-size: 0.85rem;
            color: var(--user-muted);
            margin-top: 2px;
        }

        .user-meta {
            font-size: 0.75rem;
            color: var(--user-muted);
            margin-top: 4px;
        }

        .user-actions {
            display: flex;
            gap: 6px;
        }

        .user-actions .btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: all 0.2s;
        }

        .user-actions .btn-roles {
            background: #e0f2fe;
            color: var(--user-info);
            border: none;
        }

        .user-actions .btn-roles:hover {
            background: var(--user-info);
            color: white;
        }

        .user-actions .btn-edit {
            background: #fef3c7;
            color: var(--user-warning);
            border: none;
        }

        .user-actions .btn-edit:hover {
            background: var(--user-warning);
            color: white;
        }

        .user-actions .btn-delete {
            background: #fee2e2;
            color: var(--user-danger);
            border: none;
        }

        .user-actions .btn-delete:hover {
            background: var(--user-danger);
            color: white;
        }

        /* Role Tags */
        .roles-section {
            margin-top: 14px;
        }

        .roles-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--user-muted);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .role-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .role-tag {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 6px;
            font-weight: 500;
        }

        .role-tag.no-role {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        /* Modal Styling */
        .modal-content {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .modal-header.gradient {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            padding: 24px 28px;
            border: none;
        }

        .modal-header.gradient .modal-title {
            font-weight: 700;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-header.gradient .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        .modal-body {
            padding: 28px;
            background: #ffffff;
        }

        .form-group-modern {
            margin-bottom: 20px;
        }

        .form-group-modern .form-label {
            font-weight: 600;
            color: var(--user-text);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }

        .form-group-modern .form-label i {
            color: var(--user-primary);
            font-size: 0.9rem;
        }

        .form-group-modern .form-control {
            border: 2px solid var(--user-border);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: #f8fafc;
        }

        .form-group-modern .form-control:focus {
            border-color: var(--user-primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            background: #ffffff;
        }

        .modal-footer-modern {
            padding: 20px 28px;
            background: #f8fafc;
            border-top: 1px solid var(--user-border);
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-cancel {
            background: #f1f5f9;
            color: var(--user-text);
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
        }

        .btn-save {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.35);
            color: white;
        }

        /* Roles Checkbox Grid */
        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            max-height: 200px;
            overflow-y: auto;
            padding: 16px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid var(--user-border);
        }

        .role-checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid var(--user-border);
            transition: all 0.15s;
            cursor: pointer;
        }

        .role-checkbox-item:hover {
            border-color: var(--user-primary);
        }

        .role-checkbox-item.checked {
            background: #eef2ff;
            border-color: var(--user-primary);
        }

        .role-checkbox-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--user-primary);
        }

        .role-checkbox-item label {
            font-size: 0.85rem;
            color: var(--user-text);
            cursor: pointer;
            margin: 0;
        }

        .user-actions .btn-warehouse {
            background: #ecfdf5;
            color: #059669;
            border: none;
        }

        .user-actions .btn-warehouse:hover {
            background: #059669;
            color: white;
        }

        .user-actions .btn-crossbranch {
            background: #eff6ff;
            color: #2563eb;
            border: none;
        }

        .user-actions .btn-crossbranch:hover {
            background: #2563eb;
            color: white;
        }

        /* Warehouse Assignment Modal */
        .wh-branch-block {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .wh-branch-header {
            background: linear-gradient(135deg, #0066cc, #0044aa);
            color: white;
            padding: 10px 16px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .wh-branch-body {
            padding: 12px;
            background: #f8fafc;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 8px;
        }

        .wh-item {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.15s;
        }

        .wh-item:hover {
            border-color: #0066cc;
        }

        .wh-item.selected {
            border-color: #0066cc;
            background: #eff6ff;
        }

        .wh-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #0066cc;
            cursor: pointer;
        }

        .wh-item label {
            flex: 1;
            font-size: 0.875rem;
            color: #1e293b;
            cursor: pointer;
            margin: 0;
            font-weight: 500;
        }

        .wh-star-btn {
            background: none;
            border: none;
            padding: 0;
            font-size: 1rem;
            cursor: pointer;
            color: #cbd5e1;
            transition: color 0.15s;
            line-height: 1;
        }

        .wh-star-btn.active {
            color: #f59e0b;
        }

        .wh-star-btn:hover {
            color: #f59e0b;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--user-muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 16px;
            color: #cbd5e1;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .users-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .stats-row {
                grid-template-columns: 1fr;
            }

            .users-header {
                flex-direction: column;
            }

            .search-box {
                max-width: 100%;
            }
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <!-- Page Header -->
                <div class="page-header d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="page-title"><i class="fa fa-users"></i> User Management</h1>
                        <p class="page-subtitle">Manage system users and their roles</p>
                    </div>
                    {{-- @can('Users.create') --}}
                        <button type="button" class="btn btn-create" id="addUserBtn">
                            <i class="fa fa-user-plus"></i> Add User
                        </button>
                    {{-- @endcan --}}
                </div>

                <!-- Stats Row -->
                <div class="stats-row">
                    <div class="stat-card primary">
                        <div class="stat-icon"><i class="fa fa-users"></i></div>
                        <div class="stat-value">{{ $users->count() }}</div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-icon"><i class="fa fa-user-shield"></i></div>
                        <div class="stat-value">{{ $allRoles->count() }}</div>
                        <div class="stat-label">Available Roles</div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-icon"><i class="fa fa-user-check"></i></div>
                        <div class="stat-value">{{ $users->filter(fn($u) => $u->getRoleNames()->count() > 0)->count() }}
                        </div>
                        <div class="stat-label">Users with Roles</div>
                    </div>
                    <div class="stat-card info">
                        <div class="stat-icon"><i class="fa fa-user-times"></i></div>
                        <div class="stat-value">{{ $users->filter(fn($u) => $u->getRoleNames()->count() === 0)->count() }}
                        </div>
                        <div class="stat-label">No Role Assigned</div>
                    </div>
                </div>

                <!-- Users Card -->
                <div class="users-card">
                    <div class="users-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="search-box">
                                <i class="fa fa-search"></i>
                                <input type="search" id="userSearch" placeholder="Search users...">
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-outline-secondary btn-sm" id="exportUsersBtn"><i
                                        class="fa fa-download"></i></button>
                                <button class="btn btn-outline-secondary btn-sm" id="refreshBtn"><i
                                        class="fa fa-sync"></i></button>
                            </div>
                        </div>
                        <span class="text-muted small" id="usersCount">{{ $users->count() }} users</span>
                    </div>

                    <div class="users-grid" id="usersGrid">
                        @forelse($users as $user)
                            <div class="user-card" data-user-id="{{ $user->id }}"
                                data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}"
                                data-roles="{{ json_encode($user->getRoleNames()) }}"
                                data-branch="{{ $user->branch_id ?? 0 }}"
                                data-branch-name="{{ $user->branch?->name ?? 'No Branch' }}">
                                <div class="user-card-header">
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                                        <div class="user-info">
                                            <h4 class="user-name">{{ $user->name }}</h4>
                                            <div class="user-email">{{ $user->email }}</div>
                                            <div class="user-email" style="font-size:0.78rem;color:#64748b;"><i class="fa fa-code-branch me-1"></i>{{ $user->branch?->name ?? 'No Branch' }}</div>
                                            <div class="user-meta">ID: {{ $user->id }} • Joined
                                                {{ $user->created_at?->diffForHumans() ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="user-actions">
                                        @can('users.edit')
                                            <button class="btn btn-roles edit-role-btn" title="Edit Roles">
                                                <i class="fa fa-key"></i>
                                            </button>
                                            <button class="btn btn-edit edit-user-btn" title="Edit User">
                                                <i class="fa fa-pen"></i>
                                            </button>
                                        @endcan
                                        @can('warehouse.manage')
                                            <button class="btn btn-warehouse assign-warehouses-btn"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                title="Assign Warehouses">
                                                <i class="fa fa-warehouse"></i>
                                            </button>
                                        @endcan
                                        @if(Auth::user()->hasRole('super admin'))
                                            <button class="btn btn-crossbranch crossbranch-btn"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                title="Cross-Branch Permissions">
                                                <i class="fa fa-globe"></i>
                                            </button>
                                        @endif
                                        @can('users.delete')
                                            <button class="btn btn-delete delete-user-btn" data-id="{{ $user->id }}"
                                                title="Delete User">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="roles-section">
                                    <div class="roles-label">Assigned Roles</div>
                                    <div class="role-tags">
                                        @forelse($user->getRoleNames() as $role)
                                            <span class="role-tag">{{ $role }}</span>
                                        @empty
                                            <span class="role-tag no-role">No Role Assigned</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state" style="grid-column: 1/-1;">
                                <i class="fa fa-users"></i>
                                <p>No users found. Add your first user!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header gradient">
                    <h5 class="modal-title" id="userModalTitle">
                        <i class="fa fa-user-plus"></i>
                        <span>Add New User</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="userForm" class="myform" action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="edit_id" id="userEditId">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label">
                                        <i class="fa fa-user"></i> Full Name
                                    </label>
                                    <input type="text" name="name" id="userName" class="form-control"
                                        placeholder="Enter full name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-modern">
                                    <label class="form-label">
                                        <i class="fa fa-envelope"></i> Email Address
                                    </label>
                                    <input type="email" name="email" id="userEmail" class="form-control"
                                        placeholder="Enter email address" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group-modern">
                                    <label class="form-label">
                                        <i class="fa fa-lock"></i> Password
                                    </label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="userPassword" class="form-control"
                                            placeholder="Enter password (leave blank to keep existing)">
                                        <button class="btn btn-outline-secondary toggle-password" type="button"
                                            data-target="userPassword">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @php
                                // Ensure branches are available to the view; controller may pass $branches
                                if (!isset($branches)) {
                                    $branches = \App\Models\Branch::all();
                                }
                            @endphp

                            @if(auth()->user()->hasRole('super admin'))
                                <div class="col-md-6" >
                                    <div class="form-group-modern">
                                        <label class="form-label">
                                            <i class="fa fa-sitemap"></i> Branch
                                        </label>
                                        <select name="branch_id" id="branchSelect" class="form-control" style="height:50px">
                                            <option value="">Select Branch</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->branch_name ?? $branch->name ?? 'Branch '.$branch->id }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="branch_id" id="branchHidden" value="{{ auth()->user()->branch_id ?? 0 }}">
                            @endif
                            <div class="col-md-12">
                                <div class="form-group-modern">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">
                                            <i class="fa fa-user-shield"></i> Assign Roles
                                        </label>
                                        <label class="select-all-toggle">
                                            <input type="checkbox" id="selectAllRoles">
                                            <span class="toggle-label">Select All</span>
                                        </label>
                                    </div>
                                    <div class="roles-grid" id="rolesContainer"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer-modern">
                        <button type="button" class="btn btn-cancel" data-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-save">
                            <i class="fa fa-check"></i>
                            <span id="userSaveText">Save User</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Roles Modal -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header gradient">
                    <div class="d-flex flex-column">
                        <h5 class="modal-title">
                            <i class="fa fa-key"></i>
                            <span>Update User Roles</span>
                        </h5>
                        <small class="text-white-50 mt-1" id="editRoleUserLabel">User: John Doe</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editRoleForm" class="edit-role-form" action="{{ route('users.update.roles') }}"
                    method="POST">
                    @csrf
                    <input type="hidden" name="edit_id" id="editRoleUserId">

                    <div class="modal-body">
                        <div class="form-group-modern">
                            <label class="form-label">
                                <i class="fa fa-user-shield"></i> Available Roles
                            </label>
                            <div class="roles-grid" id="editRolesContainer"></div>
                        </div>
                    </div>

                    <div class="modal-footer-modern">
                        <button type="button" class="btn btn-cancel" data-dismiss="modal">
                            <i class="fa fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-save">
                            <i class="fa fa-check"></i>
                            <span>Save Roles</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ✅ ERP: Assign Warehouses to User Modal -->
    <div class="modal fade" id="assignWarehousesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px;overflow:hidden;">
                <div class="modal-header" style="background:linear-gradient(135deg,#0066cc,#004499);color:#fff;padding:20px 28px;">
                    <h5 class="modal-title">
                        <i class="fa fa-warehouse me-2"></i>
                        Assign Warehouses — <span id="awUserName" class="fw-bold"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">

                    <div class="alert border-0 mb-3" style="background:#eff6ff;color:#1e40af;">
                        <i class="fa fa-info-circle me-1"></i>
                        <strong>Multi-Branch Assignment:</strong> Check any warehouse across branches.
                        Click <i class="fa fa-star" style="color:#f59e0b"></i> to mark as <strong>Incharge</strong> of that warehouse.
                    </div>

                    <div id="awLoading" class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2 text-muted small">Loading branches & warehouses...</p>
                    </div>

                    <div id="awContent" style="display:none;">
                        <input type="hidden" id="awUserId">
                        <div id="awBranchList" style="max-height:420px;overflow-y:auto;"></div>
                    </div>

                </div>
                <div class="modal-footer" style="background:#f8fafc;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="awSaveBtn">
                        <i class="fa fa-save me-1"></i> Save Assignments
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- Cross-Branch Permissions Modal (Super Admin only)             --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="crossbranchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border:none;border-radius:20px;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div class="modal-header" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);color:white;padding:22px 28px;border:none;">
                    <div>
                        <h5 class="modal-title mb-0" style="font-weight:700;font-size:1.2rem;">
                            <i class="fa fa-globe me-2"></i>Cross-Branch Permissions
                        </h5>
                        <small class="opacity-75" id="cbUserLabel">Loading...</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4" style="background:#f8fafc;max-height:70vh;overflow-y:auto;">

                    <div class="alert border-0 mb-3" style="background:#dbeafe;color:#1e40af;border-radius:10px;">
                        <i class="fa fa-info-circle me-2"></i>
                        <strong>Super Admin:</strong> Grant this user view/access permissions for other branches.
                        Cross-branch permissions are only for <em>read/view/report</em> access — create/edit/delete always remain on the user's home branch.
                    </div>

                    <div id="cbLoading" class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2 text-muted small">Loading branch permissions...</p>
                    </div>

                    <div id="cbContent" style="display:none;">
                        <input type="hidden" id="cbUserId">
                        <div id="cbBranchAccordion" class="accordion"></div>
                    </div>

                </div>

                <div class="modal-footer" style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:18px 28px;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="cbSaveBtn" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);border:none;padding:10px 24px;border-radius:10px;font-weight:600;">
                        <i class="fa fa-check me-1"></i>Save Cross-Branch Permissions
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('assets/js/mycode.js') }}"></script>

    <script>
        const allRoles = @json($allRoles);
        const currentUserIsSuperAdmin = @json(Auth::check() && Auth::user()->hasRole('super admin'));
        const crossBranchModulesConfig = @json(collect(config('permissions'))->filter(fn($m) => !empty($m['cross_branch']))->all());

        function buildRolesCheckboxes(container, assignedRoles) {
            var $container = $(container);
            $container.empty();

            (allRoles || []).forEach(function(r) {
                // Prevent non-super-admins from seeing/assigning the 'super admin' role
                if (!currentUserIsSuperAdmin && r.name === 'super admin') return;

                var checked = (assignedRoles || []).includes(r.name);
                var id = 'role_' + r.name.replace(/[^a-z0-9]/gi, '_');
                var $item = $(`
                    <label class="role-checkbox-item ${checked ? 'checked' : ''}">
                        <input type="checkbox" name="roles[]" value="${r.name}" ${checked ? 'checked' : ''}>
                        <span>${r.name}</span>
                    </label>
                `);
                $container.append($item);
            });

            $container.find('input[type="checkbox"]').on('change', function() {
                $(this).closest('.role-checkbox-item').toggleClass('checked', $(this).is(':checked'));
            });
        }

        $(document).ready(function() {
            // Add User Button
            $('#addUserBtn').click(function() {
                $('#userEditId').val('');
                $('#userName').val('');
                $('#userEmail').val('');
                $('#userPassword').val('');
                $('#userModalTitle').html('<i class="fa fa-user-plus"></i><span>Add New User</span>');
                $('#userSaveText').text('Create User');
                buildRolesCheckboxes('#rolesContainer', []);
                $('#selectAllRoles').prop('checked', false);
                $('#userModal').modal('show');
            });

            // Edit User Button
            $(document).on('click', '.edit-user-btn', function() {
                var card = $(this).closest('.user-card');
                var id = card.data('user-id');
                var name = card.find('.user-name').text();
                var email = card.find('.user-email').text();
                var roles = card.data('roles') || [];
                var branch = card.data('branch') || 0;

                $('#userEditId').val(id);
                $('#userName').val(name);
                $('#userEmail').val(email);
                $('#userPassword').val('');
                $('#userModalTitle').html('<i class="fa fa-pen"></i><span>Edit User</span>');
                $('#userSaveText').text('Save Changes');
                buildRolesCheckboxes('#rolesContainer', roles);
                $('#selectAllRoles').prop('checked', false);
                // populate branch select or hidden input
                if ($('#branchSelect').length) {
                    $('#branchSelect').val(branch);
                }
                if ($('#branchHidden').length) {
                    $('#branchHidden').val(branch || $('#branchHidden').val());
                }
                $('#userModal').modal('show');
            });

            // Edit Roles Button
            $(document).on('click', '.edit-role-btn', function() {
                var card = $(this).closest('.user-card');
                var id = card.data('user-id');
                var name = card.find('.user-name').text();
                var roles = card.data('roles') || [];

                $('#editRoleUserId').val(id);
                $('#editRoleUserLabel').text('User: ' + name);
                buildRolesCheckboxes('#editRolesContainer', roles);
                $('#editRoleModal').modal('show');
            });

            // Delete User
            $(document).on('click', '.delete-user-btn', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Delete User?',
                    text: 'This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ url('users/delete') }}/' + id;
                    }
                });
            });

            // Select All Roles
            $('#selectAllRoles').change(function() {
                var checked = $(this).is(':checked');
                $('#rolesContainer input[type="checkbox"]').prop('checked', checked).each(function() {
                    $(this).closest('.role-checkbox-item').toggleClass('checked', checked);
                });
            });

            // Search Users
            $('#userSearch').on('input', function() {
                var q = $(this).val().toLowerCase();
                $('.user-card').each(function() {
                    var name = $(this).data('name') || '';
                    var email = $(this).data('email') || '';
                    var roles = JSON.stringify($(this).data('roles') || []).toLowerCase();
                    $(this).toggle(name.indexOf(q) !== -1 || email.indexOf(q) !== -1 || roles
                        .indexOf(q) !== -1);
                });
                var visible = $('.user-card:visible').length;
                $('#usersCount').text(visible + ' users');
            });

            // Refresh
            $('#refreshBtn').click(function() {
                location.reload();
            });

            // Forms submission
            $('#userForm').submit(function(e) {
                e.preventDefault();
                var formdata = new FormData(this);
                var url = $(this).attr('action');
                $(this).find(':submit').prop('disabled', true);
                myAjax(url, formdata, 'POST');
            });

            $('#editRoleForm').submit(function(e) {
                e.preventDefault();
                var formdata = new FormData(this);
                var url = $(this).attr('action');
                $(this).find(':submit').prop('disabled', true);
                myAjax(url, formdata, 'POST');
            });

            // Toggle Password Visibility
            $(document).on('click', '.toggle-password', function() {
                var targetId = $(this).data('target');
                var input = $('#' + targetId);
                var icon = $(this).find('i');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            // ── Assign Warehouses Button ─────────────────────────────────
            function getAwModal() {
                return $('#assignWarehousesModal');
            }

            $(document).on('click', '.assign-warehouses-btn', function () {
                var userId   = $(this).data('user-id');
                var userName = $(this).data('user-name');

                $('#awUserId').val(userId);
                $('#awUserName').text(userName);
                $('#awLoading').show();
                $('#awContent').hide();
                $('#awBranchList').empty();
                // Reset save button
                $('#awSaveBtn').prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Assignments');

                getAwModal().modal('show');

                // AJAX: load branches + warehouses + current assignments
                $.get('/users/' + userId + '/warehouse-assignments', function (data) {
                    $('#awLoading').hide();
                    $('#awContent').show();

                    if (!data.branches || data.branches.length === 0) {
                        $('#awBranchList').html('<p class="text-muted">No branches or warehouses found.</p>');
                        return;
                    }

                    // Use Sets for O(1) lookup — entries are "branchId_warehouseId" strings
                    var assignedSet = new Set(data.assigned_entries);
                    var inchargeSet = new Set(data.incharge_entries);

                    data.branches.forEach(function (branch) {
                        if (!branch.warehouses || branch.warehouses.length === 0) return;

                        var warehouseItems = '';
                        branch.warehouses.forEach(function (wh) {
                            // Use "branchId_warehouseId" pair for exact pre-selection
                            var entry = branch.id + '_' + wh.id;
                            var isAssigned = assignedSet.has(entry);
                            var isIncharge = inchargeSet.has(entry);
                            // Unique element ID = branch_id + warehouse_id (avoids duplicate HTML ids)
                            var elemId = 'b' + branch.id + '_wh' + wh.id;
                            warehouseItems += `
                                <div class="wh-item ${isAssigned ? 'selected' : ''}" id="wh-item-${elemId}">
                                    <input type="checkbox" class="wh-checkbox"
                                        id="${elemId}"
                                        value="${branch.id}_${wh.id}"
                                        data-branch-id="${branch.id}"
                                        ${isAssigned ? 'checked' : ''}>
                                    <label for="${elemId}">${wh.name}</label>
                                    <button type="button"
                                        class="wh-star-btn ${isIncharge ? 'active' : ''}"
                                        data-entry="${branch.id}_${wh.id}"
                                        title="Mark as Incharge of this warehouse">
                                        <i class="fa fa-star"></i>
                                    </button>
                                </div>`;
                        });

                        var block = `
                            <div class="wh-branch-block">
                                <div class="wh-branch-header">
                                    <i class="fa fa-building"></i>
                                    ${branch.name}
                                    <span class="ms-auto badge bg-white text-primary">${branch.warehouses.length} warehouses</span>
                                </div>
                                <div class="wh-branch-body">${warehouseItems}</div>
                            </div>`;
                        $('#awBranchList').append(block);
                    });

                }).fail(function () {
                    $('#awLoading').hide();
                    $('#awContent').show();
                    $('#awBranchList').html('<p class="text-danger">Failed to load. Please try again.</p>');
                });
            });

            // ── Checkbox & Star handlers (attached ONCE outside AJAX) ─────
            // Using delegated events on #awBranchList to avoid duplicate bindings

            // Checkbox toggle: highlight selected state, unstar if unchecked
            $('#assignWarehousesModal').on('change', '.wh-checkbox', function () {
                var $item = $(this).closest('.wh-item');
                $item.toggleClass('selected', $(this).is(':checked'));
                if (!$(this).is(':checked')) {
                    $item.find('.wh-star-btn').removeClass('active');
                }
            });

            // Star (incharge) toggle — auto-checks the warehouse too
            $('#assignWarehousesModal').on('click', '.wh-star-btn', function () {
                var $btn  = $(this);
                var $item = $btn.closest('.wh-item');
                var $chk  = $item.find('.wh-checkbox');

                $btn.toggleClass('active');
                if ($btn.hasClass('active')) {
                    $chk.prop('checked', true);
                    $item.addClass('selected');
                }
            });

            // ── Save Warehouse Assignments ───────────────────────────────
            $('#awSaveBtn').on('click', function () {
                var userId = $('#awUserId').val();

                // Collect "branchId_warehouseId" entries (deduplicated with Set)
                var entrySet         = new Set();
                var inchargeEntrySet = new Set();

                // Collect checked entries (scoped inside the modal)
                $('#assignWarehousesModal .wh-checkbox:checked').each(function () {
                    entrySet.add($(this).val());  // value is already "branchId_warehouseId"
                });
                $('#assignWarehousesModal .wh-star-btn.active').each(function () {
                    inchargeEntrySet.add($(this).data('entry'));
                });

                var entries         = Array.from(entrySet);
                var inchargeEntries = Array.from(inchargeEntrySet);

                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving...');

                $.ajax({
                    url: '{{ route("users.assign.warehouses") }}',
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: {
                        user_id:          userId,
                        entries:          entries,
                        incharge_entries: inchargeEntries,
                    },
                    success: function (res) {
                        if (res.success) {
                            getAwModal().modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Saved!',
                                text: res.success,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        }
                    },
                    error: function (xhr) {
                        var msg = 'Could not save assignments.';
                        if (xhr.responseJSON) {
                            msg = xhr.responseJSON.message || JSON.stringify(xhr.responseJSON.errors) || msg;
                        }
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    },
                    complete: function () {
                        $('#awSaveBtn').prop('disabled', false)
                            .html('<i class="fa fa-save me-1"></i> Save Assignments');
                    }
                });
            });

            // ── Cross-Branch Permissions ──────────────────────────────────────
            $(document).on('click', '.crossbranch-btn', function() {
                var userId   = $(this).data('user-id');
                var userName = $(this).data('user-name');

                $('#cbUserId').val(userId);
                $('#cbUserLabel').text('User: ' + userName);
                $('#cbBranchAccordion').empty();
                $('#cbLoading').show();
                $('#cbContent').hide();
                $('#crossbranchModal').modal('show');

                $.getJSON('{{ url("/users") }}/' + userId + '/crossbranch-permissions', function(data) {
                    $('#cbLoading').hide();
                    $('#cbContent').show();

                    var acc = $('#cbBranchAccordion');
                    acc.empty();

                    if (!data.branches || data.branches.length === 0) {
                        acc.html('<div class="text-center text-muted py-4"><i class="fa fa-info-circle fa-2x mb-2 d-block"></i>No other branches available.</div>');
                        return;
                    }

                    data.branches.forEach(function(branch, bi) {
                        var collapseId = 'cbCollapse_' + bi;
                        var headingId  = 'cbHeading_' + bi;

                        // Count total and granted perms for this branch
                        var totalPerms  = 0;
                        var grantedCount = 0;
                        branch.modules.forEach(function(mod) {
                            mod.perms.forEach(function(p) {
                                totalPerms++;
                                if (p.checked) grantedCount++;
                            });
                        });

                        var allGranted = grantedCount === totalPerms && totalPerms > 0;
                        var hasSome    = grantedCount > 0 && !allGranted;

                        var branchHtml = `
                            <div class="accordion-item mb-2" style="border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                                <h2 class="accordion-header" id="${headingId}">
                                    <button class="accordion-button ${bi === 0 ? '' : 'collapsed'}" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#${collapseId}"
                                        style="background:linear-gradient(135deg,#1e40af,#1d4ed8);color:white;font-weight:700;border-radius:0;">
                                        <span class="me-auto"><i class="fa fa-code-branch me-2"></i>${branch.name}</span>
                                        <span class="badge ms-3" style="background:rgba(255,255,255,0.25);">
                                            ${grantedCount}/${totalPerms} active
                                        </span>
                                    </button>
                                </h2>
                                <div id="${collapseId}" class="accordion-collapse collapse ${bi === 0 ? 'show' : ''}"
                                    aria-labelledby="${headingId}" data-bs-parent="">
                                    <div class="accordion-body p-0">

                                        <div class="p-3 border-bottom" style="background:#f1f5f9;">
                                            <label style="cursor:pointer;font-weight:600;font-size:0.85rem;">
                                                <input type="checkbox" class="cb-branch-all me-2"
                                                    data-branch="${branch.id}"
                                                    ${allGranted ? 'checked' : ''}
                                                    ${hasSome ? 'data-indeterminate="1"' : ''}>
                                                Grant All Permissions for ${branch.name}
                                            </label>
                                        </div>
                        `;

                        // Render each module
                        branch.modules.forEach(function(mod, mi) {
                            var modGranted = mod.perms.filter(p => p.checked).length;
                            var modTotal   = mod.perms.length;
                            var modAll     = modGranted === modTotal && modTotal > 0;
                            var modSome    = modGranted > 0 && !modAll;
                            var modColor   = '#6366f1';

                            branchHtml += `
                                <div class="p-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong style="font-size:0.9rem;">
                                            <i class="fa ${mod.icon} me-1" style="color:${modColor};"></i>
                                            ${mod.label}
                                            <span class="badge bg-secondary ms-1">${modGranted}/${modTotal}</span>
                                        </strong>
                                        <label style="cursor:pointer;font-size:0.82rem;font-weight:600;">
                                            <input type="checkbox" class="cb-module-all me-1"
                                                data-branch="${branch.id}" data-module="${mod.key}"
                                                ${modAll ? 'checked' : ''}
                                                ${modSome ? 'data-indeterminate="1"' : ''}>
                                            All
                                        </label>
                                    </div>
                                    <div class="row g-1">
                            `;

                            mod.perms.forEach(function(p) {
                                var cbId   = 'cb_b' + branch.id + '_' + p.key.replace(/\./g, '_');
                                var permVal= 'branch:' + branch.id + ':' + p.key;
                                branchHtml += `
                                    <div class="col-6 col-md-4 col-lg-3">
                                        <label class="d-flex align-items-center gap-1 p-1 rounded" style="cursor:pointer;font-size:0.82rem;${p.checked ? 'background:#dbeafe;' : ''}">
                                            <input type="checkbox" class="cb-crossbranch-perm"
                                                id="${cbId}"
                                                name="granted[]"
                                                value="${permVal}"
                                                data-branch="${branch.id}"
                                                data-module="${mod.key}"
                                                ${p.checked ? 'checked' : ''}>
                                            ${p.label}
                                        </label>
                                    </div>
                                `;
                            });

                            branchHtml += `</div></div>`; // row + module block
                        });

                        branchHtml += `</div></div></div></div>`; // accordion body/collapse/item
                        acc.append(branchHtml);
                    });

                    // Set indeterminate state
                    acc.find('[data-indeterminate="1"]').each(function() {
                        this.indeterminate = true;
                    });

                    // Branch "All" toggle
                    acc.on('change', '.cb-branch-all', function() {
                        var branchId = $(this).data('branch');
                        var checked  = $(this).is(':checked');
                        acc.find(`.cb-crossbranch-perm[data-branch="${branchId}"]`).prop('checked', checked);
                        acc.find(`.cb-module-all[data-branch="${branchId}"]`).prop('checked', checked).prop('indeterminate', false);
                        updateBranchAllState(branchId);
                    });

                    // Module "All" toggle
                    acc.on('change', '.cb-module-all', function() {
                        var branchId = $(this).data('branch');
                        var modKey   = $(this).data('module');
                        var checked  = $(this).is(':checked');
                        acc.find(`.cb-crossbranch-perm[data-branch="${branchId}"][data-module="${modKey}"]`).prop('checked', checked);
                        updateBranchAllState(branchId);
                    });

                    // Individual permission change
                    acc.on('change', '.cb-crossbranch-perm', function() {
                        var branchId = $(this).data('branch');
                        var modKey   = $(this).data('module');
                        // update module all
                        var modPerms  = acc.find(`.cb-crossbranch-perm[data-branch="${branchId}"][data-module="${modKey}"]`);
                        var modChecked = modPerms.filter(':checked').length;
                        var $modAll   = acc.find(`.cb-module-all[data-branch="${branchId}"][data-module="${modKey}"]`);
                        $modAll.prop('checked', modChecked === modPerms.length).prop('indeterminate', modChecked > 0 && modChecked < modPerms.length);
                        updateBranchAllState(branchId);
                    });

                    function updateBranchAllState(branchId) {
                        var allPerms    = acc.find(`.cb-crossbranch-perm[data-branch="${branchId}"]`);
                        var chkCount    = allPerms.filter(':checked').length;
                        var $branchAll  = acc.find(`.cb-branch-all[data-branch="${branchId}"]`);
                        $branchAll.prop('checked', chkCount === allPerms.length && allPerms.length > 0)
                                  .prop('indeterminate', chkCount > 0 && chkCount < allPerms.length);
                    }

                }).fail(function(xhr) {
                    $('#cbLoading').html('<p class="text-danger">Error loading permissions: ' + (xhr.responseJSON?.message || 'Unknown error') + '</p>');
                });
            });

            // Save cross-branch permissions
            $('#cbSaveBtn').click(function() {
                var userId  = $('#cbUserId').val();
                var granted = [];
                $('#cbBranchAccordion .cb-crossbranch-perm:checked').each(function() {
                    granted.push($(this).val());
                });

                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i>Saving...');

                $.ajax({
                    url: '{{ route("users.crossbranch.save") }}',
                    type: 'POST',
                    data: {
                        _token:  '{{ csrf_token() }}',
                        user_id: userId,
                        granted: granted,
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#crossbranchModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Saved!',
                                text: res.success,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message || 'Could not save permissions.';
                        Swal.fire({ icon: 'error', title: 'Error', text: msg });
                    },
                    complete: function() {
                        $('#cbSaveBtn').prop('disabled', false)
                            .html('<i class="fa fa-check me-1"></i>Save Cross-Branch Permissions');
                    }
                });
            });
            // ──────────────────────────────────────────────────────────────────

        });
    </script>
@endsection
