@extends('admin_panel.layout.app')

@section('content')
    @can('product.view')
        <style>
            .incomplete-row {
                background: linear-gradient(90deg, rgba(255,193,7,0.05), transparent);
                border-left: 4px solid #ffc107;
                transition: all 0.3s ease;
            }
            
            .incomplete-row:hover {
                background: linear-gradient(90deg, rgba(255,193,7,0.1), transparent);
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            
            .status-badge {
                display: inline-block;
                background: #fff3cd;
                color: #856404;
                padding: 8px 15px;
                border-radius: 20px;
                font-size: 0.9rem;
                font-weight: 600;
                border: 1px solid #ffc107;
            }
            
            .complete-btn {
                padding: 5px 15px;
                font-size: 0.9rem;
            }
        </style>

        <div class="main-content">
            <div class="main-content-inner">
                <div class="container-fluid">
                    <div class="body-wrapper">
                        <div class="bodywrapper__inner">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="page-title mb-0">⏳ Opening Stocks (Incomplete Products)</h6>
                                    <small class="text-muted">Products awaiting Phase 2 configuration (opening stock, prices, warehouses)</small>
                                </div>
                                <a href="{{ route('store') }}" class="btn btn-primary btn-sm">
                                    <i class="las la-plus-circle"></i> Add New Product
                                </a>
                            </div>

                            @if ($products->count() > 0)
                                <div class="card">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Product Name</th>
                                                        <th>Product Code</th>
                                                        <th>Category</th>
                                                        <th>Brand</th>
                                                        <th>Created</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($products as $idx => $product)
                                                        <tr class="incomplete-row">
                                                            <td>{{ $products->firstItem() + $idx }}</td>
                                                            <td>
                                                                <strong>{{ $product->item_name }}</strong><br>
                                                                <small class="text-muted">{{ $product->category_relation?->name ?? 'N/A' }}</small>
                                                            </td>
                                                            <td>
                                                                <code>{{ $product->item_code }}</code>
                                                            </td>
                                                            <td>{{ $product->category_relation?->name ?? 'N/A' }}</td>
                                                            <td>{{ $product->brand?->name ?? 'N/A' }}</td>
                                                            <td>
                                                                <small>{{ $product->created_at->format('M d, Y') }}</small>
                                                            </td>
                                                            <td>
                                                                <span class="status-badge">
                                                                    ⏳ Profile Not Complete
                                                                </span>
                                                                <br>
                                                                <small class="text-muted d-block mt-1">Opening stock not configured</small>
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('product.opening-stock.create', ['product_id' => $product->id]) }}" 
                                                                    class="btn btn-success btn-sm complete-btn">
                                                                    <i class="las la-edit"></i> Complete
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Pagination -->
                                        <div class="mt-3 d-flex justify-content-between align-items-center">
                                            <div class="text-muted">
                                                Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                                            </div>
                                            {{ $products->links() }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="card border-0">
                                    <div class="card-body text-center py-5">
                                        <div style="font-size: 3rem; margin-bottom: 15px;">✅</div>
                                        <h5 class="mb-2">All Set!</h5>
                                        <p class="text-muted mb-3">
                                            No incomplete products. All products have been fully configured.
                                        </p>
                                        <a href="{{ route('store') }}" class="btn btn-primary">
                                            <i class="las la-plus-circle"></i> Create New Product
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div><!-- bodywrapper__inner end -->
                </div><!-- body-wrapper end -->
            </div>
        </div>
    @else
        <div class="alert alert-danger">
            ❌ You do not have permission to view products.
        </div>
    @endcan
@endsection
