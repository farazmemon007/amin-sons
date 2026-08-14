@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #858796;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --dark-bg: #1a1c23;
        --light-bg: #f8f9fc;
        --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }

    .main-content {
        background-color: var(--light-bg);
        min-height: 100vh;
        padding: 1.5rem;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        background: #fff;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: var(--card-shadow);
    }

    .page-title {
        font-weight: 700;
        color: #333;
        margin: 0;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .page-title i {
        color: var(--danger-color);
    }

    .card-filter {
        border: none;
        border-radius: 0.75rem;
        box-shadow: var(--card-shadow);
        margin-bottom: 1.5rem;
        background: #fff;
    }

    .filter-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--secondary-color);
        text-transform: uppercase;
        margin-bottom: 0.4rem;
    }

    /* Table Styling */
    .table-container {
        background: #fff;
        border-radius: 0.75rem;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        padding: 1rem;
    }

    .table thead th {
        background: #f1f4f9;
        color: #444;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.025em;
        padding: 1rem 0.75rem;
        border-bottom: 2px solid #dee2e6;
    }

    .table tbody td {
        padding: 0.85rem 0.75rem;
        vertical-align: middle;
        font-size: 0.85rem;
        color: #555;
    }

    .balance-val {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 700;
        text-align: right;
    }

    .badge-branch {
        background-color: rgba(78, 115, 223, 0.1);
        color: var(--primary-color);
        font-weight: 600;
        padding: 0.35em 0.65em;
        border-radius: 0.35rem;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
        .main-content { padding: 1rem; }
    }
</style>

<div class="main-content">
    <div class="page-header">
        <h3 class="page-title"><i class="fa-solid fa-file-invoice-dollar"></i> Vendor Ledger Report</h3>
        <div class="btn-action-group">
            <a href="{{ route('vendor.index') }}" class="btn btn-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa fa-arrow-left me-1"></i> Back to Vendors
            </a>
        </div>
    </div>

    {{-- ✅ Search Filters --}}
    <div class="card card-filter">
        <div class="card-body">
            <form action="{{ route('vendors.ledger') }}" method="GET" class="row g-3 align-items-end" id="ledger-filter-form">
                @if(auth()->user()->hasRole('super admin'))
                    <div class="col-md-3">
                        <label class="filter-label">Branch</label>
                        <select name="branch_id" id="branch_id" class="form-control select2">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="{{ auth()->user()->hasRole('super admin') ? 'col-md-3' : 'col-md-4' }}">
                    <label class="filter-label">Search Vendor</label>
                    <select name="vendor_id" id="vendor_id" class="form-control select2">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-6">
                    <label class="filter-label">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>

                <div class="col-md-2 col-6">
                    <label class="filter-label">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">
                        <i class="fa fa-search me-1"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table id="default-datatable" class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Branch</th>
                        <th>Vendor</th>
                        <th class="text-end">Opening Bal</th>
                        <th class="text-end">Previous Bal</th>
                        <th class="text-end">Closing Bal</th>
                        <th class="text-center">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($VendorLedgers as $key => $ledger)
                        <tr>
                            <td><span class="text-muted fw-bold">{{ $key+1 }}</span></td>
                            <td><span class="badge-branch">{{ $ledger->branch->name ?? 'Main Branch' }}</span></td>
                            <td><span class="fw-bold text-dark">{{ $ledger->vendor->name ?? 'N/A' }}</span></td>
                            <td class="balance-val text-primary">{{ number_format($ledger->opening_balance, 2) }}</td>
                            <td class="balance-val text-secondary">{{ number_format($ledger->previous_balance, 2) }}</td>
                            <td class="balance-val {{ $ledger->closing_balance > 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($ledger->closing_balance, 2) }}
                            </td>
                            <td class="text-center"><i class="fa fa-calendar-day me-1 text-muted"></i> {{ $ledger->created_at->format('d-m-Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
 <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
 <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
 <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
     $(document).ready(function() {

         // Initialize Select2 on all select elements
         $('.select2').select2({
             placeholder: "Select an option",
             allowClear: true,
             width: '100%'
         });

         // DataTable
         $('#default-datatable').DataTable({
             "pageLength": 10,
             "lengthMenu": [10, 25, 50, 100],
             "order": [[0, 'asc']],
             "language": {
                 "search": "Filter Record:",
                 "lengthMenu": "_MENU_ per page"
             }
         });

         // ✅ Branch Change → Fetch branch vendors via AJAX → Update dropdown
         $('#branch_id').on('change', function() {
             var branchId = $(this).val();
             var vendorSelect = $('#vendor_id');

             // Destroy Select2, show loading
             if (vendorSelect.hasClass('select2-hidden-accessible')) {
                 vendorSelect.select2('destroy');
             }
             vendorSelect.html('<option value="">Loading vendors...</option>');
             vendorSelect.select2({ placeholder: "Loading...", allowClear: true, width: '100%' });

             $.ajax({
                 url: "{{ route('vendors-by-branch') }}",
                 type: "GET",
                 data: { branch_id: branchId },
                 success: function(response) {
                     // Destroy Select2 before repopulating
                     if (vendorSelect.hasClass('select2-hidden-accessible')) {
                         vendorSelect.select2('destroy');
                     }

                     var html = '<option value="">All Vendors</option>';
                     if (Array.isArray(response)) {
                         response.forEach(function(vendor) {
                             var vName = vendor.name || vendor.customer_name;
                             html += '<option value="' + vendor.id + '">' + vName + '</option>';
                         });
                     }
                     vendorSelect.html(html);

                     // Reinitialize Select2
                     vendorSelect.select2({
                         placeholder: "Select an option",
                         allowClear: true,
                         width: '100%'
                     });

                     // ✅ Redirect with branch_id in URL so table also filters
                     var baseUrl = "{{ route('vendors.ledger') }}";
                     var startDate = $('#start_date').val();
                     var endDate   = $('#end_date').val();
                     var params    = new URLSearchParams();
                     if (branchId)   params.append('branch_id',  branchId);
                     if (startDate)  params.append('start_date', startDate);
                     if (endDate)    params.append('end_date',   endDate);
                     window.location.href = baseUrl + (params.toString() ? '?' + params.toString() : '');
                 },
                 error: function(xhr) {
                     console.error('Error fetching vendors by branch:', xhr);
                     if (vendorSelect.hasClass('select2-hidden-accessible')) {
                         vendorSelect.select2('destroy');
                     }
                     vendorSelect.html('<option value="">All Vendors</option>');
                     vendorSelect.select2({ placeholder: "Select an option", allowClear: true, width: '100%' });
                 }
             });
         });
     });
 </script>
@endpush
