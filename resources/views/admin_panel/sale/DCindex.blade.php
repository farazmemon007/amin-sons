@extends('admin_panel.layout.app')

@section('content')

<div class="container-fluid">
    <div class="card shadow-sm border-0 mt-3">

        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Generate DC</h5>

            @php
                // Ensure $branches is available when view is rendered from different controllers
                $branches = $branches ?? \App\Models\Branch::all();
            @endphp

            <form id="invoiceSearchForm" class="d-flex">
                @if(Auth::check() && Auth::user()->hasRole('super admin'))
                    <select id="branch_select" class="form-select me-2">
                        <option value="">-- Select Branch --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="hidden" id="branch_select" value="{{ Auth::user()->branch_id ?? 0 }}">
                @endif

                <div class="input-group">
                    <input type="text"
                           id="invoice_input"
                           class="form-control"
                           placeholder="Enter invoice numeric part (e.g. 14)">
                </div>

                <button type="submit" class="btn btn-primary ms-2">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">

                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            @if(Auth::check() && Auth::user()->hasRole('super admin'))
                                <th>Branch</th>
                            @endif
                            <th>Invoice No</th>
                            <th>Customer Name</th>
                            <th>Mobile</th>
                            <th>product Name</th>
                            <th>product Model</th>
                            <th>Sale Qty</th>
                            {{-- <th>Discount Amount</th> --}}
                            {{-- <th>Total Balance</th> --}}
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="salesTableBody">
                        @include('admin_panel.sale.partials.sales_rows', ['sales' => $sales])
                    </tbody>

                </table>

            </div>
        </div>
    </div>
</div>

{{-- AJAX SCRIPT --}}
<script>
const input = document.getElementById('invoice_input');
const branchSelect = document.getElementById('branch_select');

function fetchSales(invoice, branchId) {
    let url = new URL("{{ route('sale.search') }}", window.location.origin);
    if (invoice) url.searchParams.set('invoice', invoice);
    if (branchId) url.searchParams.set('branch_id', branchId);

    fetch(url.toString())
        .then(response => response.text())
        .then(data => {
            document.getElementById('salesTableBody').innerHTML = data;
        });
}

// initial load: limit to selected branch if non-super, otherwise full list
document.addEventListener('DOMContentLoaded', function() {
    const br = branchSelect ? branchSelect.value : '';
    fetchSales(null, br);
});

// live keyup: build INV- prefix and branch param
input.addEventListener('keyup', function() {
    let raw = this.value.trim();
    const br = branchSelect ? branchSelect.value : '';

    if (raw === '') {
        fetchSales(null, br);
        return;
    }

    // pad to 4 digits and prefix INV-
    let num = raw.padStart(4, '0');
    let invoice = 'INV-' + num;
    fetchSales(invoice, br);
});

// on submit also trigger search (prevents form submit)
document.getElementById('invoiceSearchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const raw = input.value.trim();
    const br = branchSelect ? branchSelect.value : '';
    let invoice = raw ? ('INV-' + raw.padStart(4, '0')) : null;
    fetchSales(invoice, br);
});
</script>

@endsection