@extends('admin_panel.layout.app')

@section('css')
<style>
    .cmp-status-pending     { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
    .cmp-status-in_progress { background: #cce5ff; color: #004085; border: 1px solid #0080ff; }
    .cmp-status-resolved    { background: #d4edda; color: #155724; border: 1px solid #28a745; }
    .cmp-status-closed      { background: #e2e3e5; color: #383d41; border: 1px solid #6c757d; }
    .cmp-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .scenario-walkin  { background: #e8f4fd; color: #1e6fa5; border: 1px solid #90c8f0; }
    .scenario-remote  { background: #e8f8e8; color: #1d6b2c; border: 1px solid #7dc97d; }
    .scenario-home    { background: #fdf0e8; color: #a04000; border: 1px solid #e0a050; }
    .filter-card { background: #f8f9fa; border-left: 4px solid #2c3e90; border-radius: 6px; padding: 15px 20px; margin-bottom: 20px; }
    .complaint-row:hover { background: #f5f8ff !important; }
    .stats-card { border-radius: 10px; padding: 15px 20px; text-align: center; color: white; margin-bottom: 15px; }
    .stats-pending { background: linear-gradient(135deg, #f39c12, #e67e22); }
    .stats-progress { background: linear-gradient(135deg, #3498db, #2980b9); }
    .stats-resolved { background: linear-gradient(135deg, #27ae60, #1e8449); }
    .stats-closed   { background: linear-gradient(135deg, #95a5a6, #7f8c8d); }
    .action-btn { padding: 3px 8px; font-size: 11px; border-radius: 4px; margin: 1px; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 text-dark font-weight-bold">
                <i class="fas fa-exclamation-circle text-warning mr-2"></i>Complaint Management System
            </h4>
            <small class="text-muted">Track and manage all customer complaints</small>
        </div>
        @can('complaint.create')
        <a href="{{ route('complaints.create') }}" class="btn btn-primary btn-sm px-4">
            <i class="fas fa-plus mr-1"></i> New Complaint
        </a>
        @endcan
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-3">
        @php
            $counts = \App\Models\Complaint::selectRaw('status, count(*) as total')
                ->when(!auth()->user()->hasRole('super admin'), fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                ->groupBy('status')->pluck('total', 'status');
        @endphp
        <div class="col-md-3 col-6">
            <div class="stats-card stats-pending">
                <div style="font-size:24px; font-weight:700;">{{ $counts['pending'] ?? 0 }}</div>
                <div style="font-size:12px;">Pending</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stats-card stats-progress">
                <div style="font-size:24px; font-weight:700;">{{ $counts['in_progress'] ?? 0 }}</div>
                <div style="font-size:12px;">In Progress</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stats-card stats-resolved">
                <div style="font-size:24px; font-weight:700;">{{ $counts['resolved'] ?? 0 }}</div>
                <div style="font-size:12px;">Resolved</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stats-card stats-closed">
                <div style="font-size:24px; font-weight:700;">{{ $counts['closed'] ?? 0 }}</div>
                <div style="font-size:12px;">Closed</div>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    {{-- Filters --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('complaints.index') }}" id="filterForm">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <label class="small font-weight-bold mb-1">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Complaint No / Name / Mobile" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold mb-1">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                        <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>In Progress</option>
                        <option value="resolved" {{ request('status')=='resolved'?'selected':'' }}>Resolved</option>
                        <option value="closed" {{ request('status')=='closed'?'selected':'' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold mb-1">Scenario</label>
                    <select name="scenario_type" class="form-control form-control-sm">
                        <option value="">All Scenarios</option>
                        <option value="walk_in" {{ request('scenario_type')=='walk_in'?'selected':'' }}>Walk-in (Shop)</option>
                        <option value="remote" {{ request('scenario_type')=='remote'?'selected':'' }}>Company Complaint</option>
                        <option value="home_service" {{ request('scenario_type')=='home_service'?'selected':'' }}>Home Service</option>
                    </select>
                </div>
                @if(auth()->user()->hasRole('super admin'))
                <div class="col-md-2">
                    <label class="small font-weight-bold mb-1">Branch</label>
                    <select name="branch_id" class="form-control form-control-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id')==$branch->id?'selected':'' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <label class="small font-weight-bold mb-1">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="small font-weight-bold mb-1">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search mr-1"></i>Search</button>
                    <a href="{{ route('complaints.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times mr-1"></i>Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0" id="complaintsTable">
                    <thead style="background: linear-gradient(135deg, #2c3e90, #1e6fa5); color: white;">
                        <tr>
                            <th class="py-2 px-3">#</th>
                            <th class="py-2 px-3">Complaint No</th>
                            <th class="py-2 px-3">Date</th>
                            <th class="py-2 px-3">Scenario</th>
                            <th class="py-2 px-3">Customer</th>
                            <th class="py-2 px-3">Mobile</th>
                            <th class="py-2 px-3">Product</th>
                            @if(auth()->user()->hasRole('super admin'))
                            <th class="py-2 px-3">Branch</th>
                            @endif
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($complaints as $i => $c)
                        <tr class="complaint-row">
                            <td class="px-3 py-2 text-muted small">{{ $complaints->firstItem() + $i }}</td>
                            <td class="px-3 py-2">
                                <a href="{{ route('complaints.show', $c->id) }}" class="font-weight-bold text-dark text-decoration-none">
                                    <i class="fas fa-hashtag text-muted" style="font-size:10px;"></i> {{ $c->complaint_no }}
                                </a>
                            </td>
                            <td class="px-3 py-2 text-muted small">{{ $c->complaint_date->format('d M Y') }}</td>
                            <td class="px-3 py-2">
                                @if($c->scenario_type === 'walk_in')
                                    <span class="cmp-badge scenario-walkin"><i class="fas fa-store"></i> Walk-in</span>
                                @elseif($c->scenario_type === 'remote')
                                    <span class="cmp-badge scenario-remote"><i class="fas fa-building"></i> Company</span>
                                @else
                                    <span class="cmp-badge scenario-home"><i class="fas fa-home"></i> Home</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 font-weight-bold">{{ $c->customer_name }}</td>
                            <td class="px-3 py-2 text-muted small">{{ $c->customer_mobile }}</td>
                            <td class="px-3 py-2 text-muted small">{{ $c->product_name ?? ($c->product->item_name ?? '-') }}</td>
                            @if(auth()->user()->hasRole('super admin'))
                            <td class="px-3 py-2 text-muted small">{{ $c->branch->name ?? '-' }}</td>
                            @endif
                            <td class="px-3 py-2">
                                <span class="cmp-badge cmp-status-{{ $c->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $c->status)) }}
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <a href="{{ route('complaints.show', $c->id) }}" class="btn btn-info action-btn" title="View"><i class="fas fa-eye"></i></a>
                                @can('complaint.edit')
                                <a href="{{ route('complaints.edit', $c->id) }}" class="btn btn-warning action-btn" title="Edit"><i class="fas fa-edit"></i></a>
                                @endcan
                                @can('complaint.print')
                                <a href="{{ route('complaints.print-slip', $c->id) }}" target="_blank" class="btn btn-secondary action-btn" title="Print Slip"><i class="fas fa-print"></i></a>
                                <a href="{{ route('complaints.print-tag', $c->id) }}" target="_blank" class="btn btn-dark action-btn" title="Print Tag"><i class="fas fa-tag"></i></a>
                                @endcan
                                @if($c->replacements->isNotEmpty())
                                <a href="{{ route('complaints.replacements.print-slip', $c->replacements->first()->id) }}" target="_blank" class="btn btn-danger action-btn" title="Reclaim Slip / Print Bill" style="background-color: #d9534f; border-color: #d43f3a; font-weight: bold;">
                                    <i class="fas fa-file-invoice"></i> Slip
                                </a>
                                @endif
                                <a href="#" class="btn btn-success action-btn whatsapp-btn" data-id="{{ $c->id }}" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                @can('complaint.delete')
                                <form action="{{ route('complaints.destroy', $c->id) }}" method="POST" class="d-inline delete-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger action-btn" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox" style="font-size:40px; opacity:0.3;"></i>
                                <div class="mt-2">No complaints found.</div>
                                @can('complaint.create')
                                <a href="{{ route('complaints.create') }}" class="btn btn-primary btn-sm mt-2">Register First Complaint</a>
                                @endcan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($complaints->hasPages())
        <div class="card-footer">
            {{ $complaints->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Delete confirm
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Delete Complaint?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            confirmButtonText: 'Yes, Delete!'
        }).then(result => { if (result.isConfirmed) this.submit(); });
    });

    // WhatsApp share
    $('.whatsapp-btn').on('click', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        $.get(`/complaints/${id}/whatsapp`, function(res) {
            window.open(res.link, '_blank');
        });
    });
});
</script>
@endsection
