@extends('admin_panel.layout.app')

@section('content')
@can('purchase.view')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Purchase</h3>
                        {{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#purchaseModal" id="reset">Create</button> --}}
                        <a class="btn btn-primary" href="{{ route('add_purchase') }}">Add purchase</a>

                    </div>

                    <div class="border mt-1 shadow rounded" style="background-color: white;">
                        <div class="col-lg-12 m-auto">
                            @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show"
                                role="alert">
                                <strong>Success!</strong> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                            @endif
                            <div class="table-responsive mt-5 mb-5">
                                <table id="purchase-table" class="table">
                                    <thead class="text-center" style="background:#add8e6">
                                        <tr>
                                            <th>ID</th>
                                            <th>Branch</th>
                                            <th>Warehouse</th>
                                            <th>Vendor</th>
                                            <th>Invoice No</th>
                                            <th>Note</th>
                                            <th>Subtotal</th>
                                            <th>Discount</th>
                                            <th>Extra Cost</th>
                                            <th>Net Amount</th>
                                            <th>Paid</th>
                                            <th>Due</th>
                                            <th>Purchase Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        @foreach ($Purchase as $purchase)
                                        <tr>
                                            <td>{{ $purchase->id }}</td>
                                            <td>{{ $purchase->branch->name ?? 'N/A' }}</td>
                                            <td>{{ $purchase->warehouse->warehouse_name ?? 'N/A' }}</td>
                                            <td>{{ $purchase->vendor->name ?? 'N/A' }}</td>
                                            <td>{{ $purchase->invoice_no }}</td>
                                            <td>{{ $purchase->note }}</td>
                                            <td>{{ $purchase->subtotal }}</td>
                                            <td>{{ $purchase->discount }}</td>
                                            <td>{{ $purchase->extra_cost }}</td>
                                            <td>{{ $purchase->net_amount }}</td>
                                            <td>{{ $purchase->paid_amount }}</td>
                                            <td>{{ $purchase->due_amount }}</td>
                                            <td>{{ $purchase->purchase_date }}</td>
                                            <td>
                                                <div class="btn-group" role="group" aria-label="Actions">
                                                    @can('purchase.edit')
                                                    <a href="{{ route('purchase.edit', $purchase->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                                    @endcan

                                                    @can('purchase.return')
                                                    <a href="{{ route('purchase.return.show', $purchase->id) }}" class="btn btn-sm btn-danger">Return</a>
                                                    @endcan

                                                    @can('purchase.invoice')
                                                    <a href="{{ route('purchase.invoice', $purchase->id) }}" class="btn btn-sm btn-info text-white">Invoice</a>
                                                    @endcan

                                                    @can('purchase.delete')
                                                     <form action="{{ route('purchase.destroy', $purchase->id) }}" method="POST" class="d-inline delete-form">
    @csrf
    @method('DELETE')
    <button type="button" class="btn btn-danger btn-sm delete-btn mb-1">Delete</button>
</form>
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

                    {{-- Modal --}}
                    <div class="modal fade" id="purchaseModal" tabindex="-1" aria-labelledby="purchaseModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="purchaseModalLabel">Add Purchase</h5>
                                </div>
                                <div class="modal-body">
                                    <form class="myform" action="{{ route('store.Purchase') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="edit_id" id="id" />
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Invoice No</label>
                                                <input type="text" name="invoice_no" class="form-control" id="invoice_no" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Supplier</label>
                                                <input type="text" name="supplier" class="form-control" id="supplier" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Purchase Date</label>
                                                <input type="date" name="purchase_date" class="form-control" id="purchase_date" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Warehouse</label>
                                                <input type="text" name="warehouse_id" class="form-control" id="warehouse_id" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Item Category</label>
                                                <input type="text" name="item_category" class="form-control" id="item_category">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Item Name</label>
                                                <input type="text" name="item_name" class="form-control" id="item_name">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" name="quantity" class="form-control" id="quantity">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <input type="submit" class="btn btn-primary save-btn" value="Save">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Scripts --}}
                    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
                    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
                    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    <script src="{{ asset('assets/js/mycode.js') }}"></script>

                    <script>
                        $(document).on('submit', '.myform', function(e) {
                            e.preventDefault();
                            var formdata = new FormData(this);
                            url = $(this).attr('action');
                            method = $(this).attr('method');
                            $(this).find(':submit').attr('disabled', true);
                            myAjax(url, formdata, method);
                        });

                        $(document).on('click', '.edit-btn', function() {
                            var tr = $(this).closest("tr");
                            $('#id').val(tr.find(".id").text());
                            $('#invoice_no').val(tr.find(".invoice_no").text());
                            $('#supplier').val(tr.find(".supplier").text());
                            $('#purchase_date').val(tr.find(".purchase_date").text());
                            $('#warehouse_id').val(tr.find(".warehouse_id").text());
                            $("#purchaseModal").modal("show");
                        });

                        $(document).ready(function() {
                            $('#purchase-table').DataTable({
                                "pageLength": 10,
                                "lengthMenu": [5, 10, 25, 50, 100],
                                "order": [
                                    [0, 'desc']
                                ],
                                "language": {
                                    "search": "Search Purchase:",
                                    "lengthMenu": "Show _MENU_ entries"
                                }
                            });
                        });
                    </script>

                    <script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".delete-btn").forEach(function (btn) {
            btn.addEventListener("click", function () {
                let form = this.closest("form");

                Swal.fire({
                    title: "Are you sure?",
                    text: "This purchase will be deleted permanently!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>


                </div>
            </div>
        </div>
    </div>
</div>
@else
    <div class="container py-4">
        <div class="alert alert-danger">You do not have permission to view Purchases.</div>
    </div>
@endcan
@endsection
