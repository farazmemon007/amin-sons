@extends('admin_panel.layout.app')

@section('content')

<style>
body{
    font-family: Arial, Helvetica, sans-serif;
    background:#f1f3f5;
    font-size:13px;
}

.dc-wrapper{
    background:#fff;
    width:800px;
    margin:auto;
    padding:30px 35px;
    border:1px solid #ccc;
}

/* HEADER */
.dc-header{
    text-align:center;
    margin-bottom:10px;
}

.dc-header h2{
    margin:0;
    font-size:22px;
    letter-spacing:1px;
}

.dc-header small{
    font-size:12px;
    color:#555;
}

/* COMPANY INFO */
.company-info{
    font-size:12px;
    margin-bottom:10px;
}

.company-info strong{
    font-size:14px;
}

/* TOP INFO */
.dc-info{
    display:flex;
    justify-content:space-between;
    border-top:2px solid #000;
    border-bottom:2px solid #000;
    padding:8px 0;
    margin-bottom:10px;
}

.dc-info div{
    width:48%;
}

.dc-info table td{
    padding:2px 0;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

table th, table td{
    border:1px solid #000;
    padding:6px;
    text-align:center;
}

table thead th{
    background:#f3f3f3;
    font-weight:bold;
}

.text-left{text-align:left}
.text-right{text-align:right}

/* TOTALS */
.totals{
    width:40%;
    margin-left:auto;
    margin-top:10px;
}

.totals td{
    border:none;
    padding:4px 0;
}

/* FOOTER */
.dc-footer{
    display:flex;
    justify-content:space-between;
    margin-top:50px;
}

.sign{
    width:200px;
    border-top:1px solid #000;
    text-align:center;
    padding-top:5px;
    font-size:12px;
}

.no-print{
    margin-bottom:10px;
    text-align:right;
}

@media print{
    .no-print{display:none}
    body{background:#fff}
}
</style>

<div class="container-fluid mt-3">

    {{-- PRINT BUTTON --}}
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-dark btn-sm">
            Print Delivery Challan
        </button>
    </div>

    <div class="dc-wrapper">

        {{-- HEADER --}}
        <div class="dc-header">
            <h2>DELIVERY CHALLAN</h2>
            <small>Delivery Challan (DC)</small>
        </div>

        {{-- COMPANY --}}
        <div class="company-info">
            <strong>Ameer & Sons</strong><br>
            Electronics & Glass Dealer<br>
            Main Road, City Name<br>
            Phone: 0300-0000000
        </div>

        {{-- INFO --}}
        <div class="dc-info">
            <div>
                <table>
                    <tr>
                        <td><strong>Customer Name</strong></td>
                        <td>: {{ $booking->customer->customer_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Address</strong></td>
                        <td>: {{ $booking->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Contact</strong></td>
                        <td>: {{ $booking->customer->mobile_2 ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <div>
                <table>
                    <tr>
                        <td><strong>DC No</strong></td>
                        <td>: {{ $booking->invoice_no ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Date</strong></td>
                        <td>: {{ $booking->created_at?->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>: {{ ucfirst($booking->status ?? 'pending') }}</td>
                    </tr>
                </table>
            </div>
        </div>
@php
    echo "<pre>";
    print_r($booking->ToArray());
    dd();
@endphp
        {{-- ITEMS --}}
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th class="text-left">Description</th>
                    <th>Warehouse</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            
            <tbody>
                @foreach($booking->items as $i => $item)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td class="text-left">
                        {{ $item->product->item_name ?? '-' }}
                        @if(!empty($item->product->item_code))<br><small>Code: {{ $item->product->item_code }}</small>@endif
                        @if(!empty($item->product->model))<br><small>Model: {{ $item->product->model }}</small>@endif
                    </td>
                    <td>
                        @php
                            $wname = $item->warehouse->name ?? null;
                            if(!$wname && !empty($item->warehouse_id)) {
                                $w = \App\Models\Warehouse::find($item->warehouse_id);
                                $wname = $w->name ?? null;
                            }
                        @endphp
                        {{ $wname ?? '-' }}
                    </td>
                    <td>{{ $item->sales_qty }}</td>
                    <td>{{ number_format($item->retail_price,2) }}</td>
                    <td>{{ number_format($item->amount,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TOTAL --}}
        <table class="totals">
            <tr>
                <td class="text-right"><strong>Total Qty:</strong></td>
                <td class="text-right">{{ $booking->quantity }}</td>
            </tr>
            <tr>
                <td class="text-right"><strong>Sub Total:</strong></td>
                <td class="text-right">{{ number_format($booking->sub_total1,2) }}</td>
            </tr>
            <tr>
                <td class="text-right"><strong>Discount:</strong></td>
                <td class="text-right">{{ number_format($booking->discount_amount ?? 0,2) }}</td>
            </tr>
            <tr>
                <td class="text-right"><strong>Previous Balance:</strong></td>
                <td class="text-right">{{ number_format($booking->previous_balance ?? ($booking->customer->opening_balance ?? 0),2) }}</td>
            </tr>
            <tr>
                <td class="text-right"><strong>Total Balance:</strong></td>
                <td class="text-right">{{ number_format($booking->total_balance ?? 0,2) }}</td>
            </tr>
        </table>

        {{-- FOOTER --}}
        <div class="dc-footer">
            <div class="sign">Receiver Signature</div>
            <div class="sign">Authorized Signature</div>
        </div>

    </div>
</div>

@endsection
