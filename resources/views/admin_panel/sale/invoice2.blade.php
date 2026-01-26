


@extends('admin_panel.layout.app')

@section('content')

<style>
body{
    font-family: Arial, Helvetica, sans-serif;
    background:#f1f3f5;
}

.invoice-wrapper{
    background:#fff;
    padding:35px 45px;
    max-width:1100px;
    margin:auto;
    border-radius:6px;
    box-shadow:0 0 8px rgba(0,0,0,0.08);
}

/* Increase most invoice text for better readability (exclude product rows) */
.invoice-wrapper {
    font-size:20px;
}

/* HEADER */
.invoice-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
}

.company-name{
    font-size:26px;
    font-weight:700;
}

.company-address{
    font-size:13px;
    color:#555;
    margin-top:5px;
}

.invoice-meta{
    text-align:right;
    font-size:14px;
}

.invoice-meta div{
    margin-bottom:4px;
}

hr{
    border-top:2px solid #000;
    margin:18px 0;
}

/* INFO */
.info-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    font-size:14px;
}

.info-box strong{
    display:inline-block;
    width:140px;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:25px;
    font-size:14px;
}

table thead th{
    padding:10px;
    background:#f5f5f5;
    border-bottom:2px solid #ddd;
}

table tbody td{
    padding:10px;
    border-bottom:1px solid #eee;
}

.text-end{
    text-align:right;
}

/* SUMMARY */
.summary-box{
    width:40%;
    margin-left:auto;
    margin-top:25px;
    border:1px solid #ddd;
    padding:15px;
}

.summary-box table td{
    padding:8px 5px;
}

.summary-total{
    font-weight:700;
    border-top:2px solid #000;
}

/* SIGN */
.signatures{
    display:flex;
    justify-content:space-between;
    margin-top:60px;
}

.signature-line{
    width:220px;
    border-top:1px solid #000;
    text-align:center;
    padding-top:6px;
    font-size:14px;
}

@media print{
    .no-print{display:none!important;}
    body{background:#fff;}
}
</style>

{{-- @php
// echo"<pre>";
//     print_r($booking->toArray());
// echo"</pre>";
//     dd();
// @endphp --}}
<div class="container-fluid mt-4">

    {{-- PRINT BUTTON --}}
    <div class="text-end mb-3 no-print">
        <button onclick="window.print()" class="btn btn-dark">
            Print Invoice
        </button>
    </div>

    <div class="invoice-wrapper">

        {{-- HEADER --}}
        <div class="invoice-header">
            <div>
                <div class="company-name">Ameer & Sons</div>
                <div class="company-address">
                    Electronics & Home Appliences <br>
                    Lahore <br>
                    0300-0000000
                </div>
            </div>

            <div class="invoice-meta">
                <div><strong>Invoice #:</strong> {{ $booking->invoice_no }}</div>
                {{ $booking->created_at ? $booking->created_at->format('d-m-Y') : date('d-m-Y') }}  
                <div><strong>Status:</strong> {{ ucfirst($booking->status) }}</div>
            </div>
        </div>

        <hr>

        {{-- CUSTOMER INFO --}}
        <div class="info-grid">
            <div class="info-box">
                <div><strong>Customer Name:</strong> {{ $booking->customer->customer_name }}</div>
                <div><strong>Customer Type:</strong> {{ $booking->party_type }}</div>
                <div><strong>Mobile:</strong> {{ $booking->customer->mobile_2 ?? '-' }}</div>
            </div>

            <div class="info-box text-end">
                <div><strong>Address:</strong> {{ $booking->address }}</div>
                <div><strong>Remarks:</strong> {{ $booking->remarks ?? '-' }}</div>
            </div>
        </div>

        {{-- ITEMS TABLE --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Rate</th>
                    <th class="text-end">Disc Amt</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->item_name }}</td>
                    <td class="text-end">{{ number_format($item->sales_qty, 2) }}</td>
                    <td class="text-end">{{ number_format($item->retail_price, 2) }}</td>
                    <td class="text-end">{{ number_format(data_get($item, 'discount_amount', 0), 2) }}</td>
                    <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <style>
            /* Keep product/item rows at a compact size */
            .items-table tbody td { font-size:14px; }
            .items-table thead th { font-size:14px; }
        </style>

        {{-- SUMMARY --}}
        <div class="summary-box">
            <table width="100%">
                <tr>
                    <td>Sub Total</td>
                    <td class="text-end">{{ number_format($booking->sub_total1,2) }}</td>
                </tr>
                <tr>
                    <td>Discount</td>
                    <td class="text-end">{{ number_format($booking->discount_amount,2) }}</td>
                </tr>
                {{-- @php
                    echo "<pre>";
                    print_r($booking->toArray());
                    echo "</pre>";
                    dd();
                @endphp --}}
                @php
                    $latestLedger = null;
                    if(isset($booking->customer) && isset($booking->customer->ledgers)){
                        $ledgers = collect($booking->customer->ledgers);
                        $latestLedger = $ledgers->sortByDesc('id')->first();
                    }
                    $displayPrevious = $latestLedger->previous_balance ?? $booking->previous_balance ?? $booking->customer->opening_balance ?? 0;
                    $displayClosing = $latestLedger->closing_balance ?? $booking->total_balance ?? 0;
                @endphp
                <tr>
                    <td>Previous Balance</td>
                    <td class="text-end">{{ number_format($displayPrevious,2) }}</td>
                </tr>
                <tr class="summary-total">
                    <td>Closing Balance</td>
                    <td class="text-end">{{ number_format($displayClosing,2) }}</td>
                </tr>
            </table>
        </div>

        {{-- SIGNATURES --}}
        <div class="signatures">
            <div class="signature-line">Receiver Signature</div>
            <div class="signature-line">Authorized Signature</div>
        </div>

    </div>
</div>

@endsection
