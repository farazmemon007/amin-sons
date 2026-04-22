@extends('admin_panel.layout.app')

@section('content')

<div class="container-fluid">
    <div class="card shadow-sm border-0 mt-3">

        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Generate DC</h5>

            <form id="invoiceSearchForm" class="d-flex">
                <div class="input-group">
                    <input type="text"
                           id="invoice_input"
                           class="form-control"
                           placeholder="Enter Invoice No (e.g. 14)">
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
let input = document.getElementById('invoice_input');

input.addEventListener('keyup', function() {

    let number = this.value.trim();

    if(number === '') {
        document.getElementById('salesTableBody').innerHTML = '';
        return;
    }

    number = number.padStart(4, '0');
    let invoice = 'INVSLE-' + number;

    fetch("/dc-find/" + invoice)
        .then(response => response.text())
        .then(data => {
            document.getElementById('salesTableBody').innerHTML = data;
        });
});
</script>

@endsection