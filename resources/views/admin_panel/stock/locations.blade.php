@extends('admin_panel.layout.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Stock Locations by Product</h3>
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row gy-3">
                @if(isset($showBranchSelect) && $showBranchSelect)
                    <div class="col-md-4">
                        <label class="form-label">Branch</label>
                        <select id="branchSelect" class="form-select">
                            <option value="" selected disabled>Select branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    {{-- Hidden branch selector so JS can still read the branch value --}}
                    <select id="branchSelect" class="d-none">
                        <option value="{{ $selectedBranchId }}" selected></option>
                    </select>
                @endif

                <div class="col-md-5">
                    <label class="form-label">Product</label>
                    <select id="productSelect" class="form-select" disabled>
                        <option value="" selected disabled>Select branch first</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button id="btnRefresh" class="btn btn-primary w-100" disabled>Refresh</button>
                </div>
            </div>

            <div class="mt-4">
                <div class="table-responsive">
                    <table class="table table-bordered" id="locationsTable">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th class="text-end">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="2" class="text-center text-muted">Select a branch and product to view stock locations</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const branchSelect = $('#branchSelect');
    const productSelect = $('#productSelect');
    const locationsTableBody = $('#locationsTable tbody');
    const refreshBtn = $('#btnRefresh');

    const preselectedBranchId = @json($selectedBranchId ?? null);
    const showBranchSelect = @json($showBranchSelect ?? false);

    $(function() {
        if (preselectedBranchId) {
            branchSelect.val(preselectedBranchId);
            loadProductsForBranch(preselectedBranchId);
        }
    });

    function loadProductsForBranch(branchId) {
        productSelect.prop('disabled', true);
        productSelect.html('<option selected disabled>Loading products…</option>');

        $.get('{{ route('productget') }}')
            .done(function(products) {
                const filtered = products.filter(p => p.branch_id == branchId);
                if (!filtered.length) {
                    productSelect.html('<option selected disabled>No products found for this branch</option>');
                    refreshBtn.prop('disabled', true);
                    return;
                }
                const options = ['<option value="" selected disabled>Select product</option>'];
                filtered.forEach(p => {
                    options.push(`<option value="${p.id}">${p.item_name} (${p.item_code})</option>`);
                });
                productSelect.html(options.join(''));
                productSelect.prop('disabled', false);
                refreshBtn.prop('disabled', true);
            })
            .fail(function() {
                productSelect.html('<option selected disabled>Error loading products</option>');
                refreshBtn.prop('disabled', true);
            });
    }

    function loadStockLocations(branchId, productId) {
        locationsTableBody.html('<tr><td colspan="2" class="text-center">Loading...</td></tr>');

        $.get('{{ route('stock.locations.data') }}', { branch_id: branchId, product_id: productId })
            .done(function(res) {
                if (!res.success) {
                    locationsTableBody.html(`<tr><td colspan="2" class="text-danger">${res.message || 'Unable to load data'}</td></tr>`);
                    return;
                }

                const rows = res.locations.map(loc => {
                    return `<tr><td>${loc.location}</td><td class="text-end">${parseFloat(loc.quantity).toFixed(2)}</td></tr>`;
                });

                if (!rows.length) {
                    locationsTableBody.html('<tr><td colspan="2" class="text-center text-muted">No stock records found</td></tr>');
                } else {
                    locationsTableBody.html(rows.join(''));
                }
            })
            .fail(function() {
                locationsTableBody.html('<tr><td colspan="2" class="text-danger">Failed to load stock locations</td></tr>');
            });
    }

    branchSelect.on('change', function() {
        const branchId = $(this).val();
        if (!branchId) return;
        loadProductsForBranch(branchId);
    });

    productSelect.on('change', function() {
        const branchId = branchSelect.val();
        const productId = $(this).val();
        refreshBtn.prop('disabled', !branchId || !productId);
    });

    refreshBtn.on('click', function() {
        const branchId = branchSelect.val();
        const productId = productSelect.val();
        if (!branchId || !productId) return;
        loadStockLocations(branchId, productId);
    });
</script>
@endsection
