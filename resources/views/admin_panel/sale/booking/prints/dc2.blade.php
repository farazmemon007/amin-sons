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
    font-size:14px;
    margin-bottom:10px;
}

.company-info strong{
    font-size:25px;
    font-weight:800;
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
                        <td><strong>Customer</strong></td>
                        <td> {{ data_get($customer, 'customer_name', data_get($booking, 'customer.customer_name', '-')) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Customer ID</strong></td>
                        <td> {{ data_get($customer, 'customer_id', data_get($booking, 'customer.customer_id', '-')) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Type</strong></td>
                        <td> {{ data_get($customer, 'customer_type', data_get($booking, 'party_type', '-')) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Address</strong></td>
                        <td> {{ data_get($booking, 'address', data_get($customer, 'address', '-')) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Contact</strong></td>
                        <td> {{ data_get($customer, 'mobile', data_get($customer, 'mobile_2', data_get($booking, 'tel', '-'))) }}</td>
                    </tr>
                </table>
            </div>

            <div>
                <table>
                    <tr>
                        <td><strong>DC No</strong></td>
                        <td>: {{ data_get($booking, 'invoice_no', '-') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Invoice</strong></td>
                        <td>: {{ data_get($booking, 'manual_invoice') ?: data_get($booking, 'invoice_no', '-') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Date</strong></td>
                        <td>: {{ optional(data_get($booking, 'created_at'))->format('d-m-Y') ?? date('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>: {{ ucfirst(data_get($booking, 'status', 'pending')) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Prepared By</strong></td>
                        <td>: {{ data_get($booking, 'prepared_by') ?: (auth()->check() ? auth()->user()->name : '-') }}</td>
                    </tr>
                </table>
            </div>
        </div>

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
                @php $rows = $items ?? $booking->items; @endphp
                @foreach($rows as $i => $item)
                @php
                    // Support both array rows (from controller) and Eloquent models
                    $iname = data_get($item, 'item_name') ?: data_get($item, 'product.item_name');
                    $icode = data_get($item, 'item_code') ?: data_get($item, 'product.item_code');
                    $imodel = data_get($item, 'model') ?: data_get($item, 'product.model');
                    $wname = data_get($item, 'warehouse_name') ?: data_get($item, 'warehouse.name');
                    if(!$wname && data_get($item, 'warehouse_id')) {
                        $w = \App\Models\Warehouse::find(data_get($item, 'warehouse_id'));
                        $wname = $w->name ?? null;
                    }
                    $iqty = data_get($item, 'sales_qty') ?: data_get($item, 'sales_qty');
                    $irate = data_get($item, 'retail_price') ?: data_get($item, 'retail_price');
                    $iamt = data_get($item, 'amount') ?: data_get($item, 'amount');
                @endphp
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td class="text-left">
                        {{ $iname ?? '-' }}
                        @if($icode)<br><small>Code: {{ $icode }}</small>@endif
                        @if($imodel)<br><small>Model: {{ $imodel }}</small>@endif
                    </td>
                    <td>{{ $wname ?? '-' }}</td>
                    <td>{{ $iqty }}</td>
                    <td>{{ number_format($irate,2) }}</td>
                    <td>{{ number_format($iamt,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TOTAL --}}
        @php
            // Determine latest ledger entry from provided $customer or booking->customer
            $latestLedger = null;
            if(!empty($customer) && data_get($customer, 'ledgers')){
                $ledgers = collect(data_get($customer, 'ledgers'));
                $latestLedger = $ledgers->sortByDesc('id')->first();
            } elseif(isset($booking->customer) && isset($booking->customer->ledgers)){
                $ledgers = collect($booking->customer->ledgers);
                $latestLedger = $ledgers->sortByDesc('id')->first();
            }

            $displayPrevious = $latestLedger->previous_balance ?? $booking->previous_balance ?? ($booking->customer->opening_balance ?? ($customer['opening_balance'] ?? 0));
            $displayClosing = $latestLedger->closing_balance ?? $booking->total_balance ?? ($booking->customer->opening_balance ?? ($customer['opening_balance'] ?? 0));

            // Compute total qty and subtotal from provided items or booking items
            $rowsForTotals = $items ?? $booking->items ?? [];
            $totalQty = collect($rowsForTotals)->sum(function($r){ return (float) data_get($r, 'sales_qty', 0); });
            $subTotal = collect($rowsForTotals)->sum(function($r){
                // prefer explicit 'amount', fall back to 'per_total' or compute from rate*qty
                $amt = data_get($r, 'amount', null);
                if ($amt !== null) return (float) $amt;
                $amt = data_get($r, 'per_total', null);
                if ($amt !== null) return (float) $amt;
                $rate = data_get($r, 'retail_price', data_get($r, 'price', 0));
                $qty = data_get($r, 'sales_qty', data_get($r, 'qty', 0));
                return (float) $rate * (float) $qty;
            });
        @endphp

        <table class="totals">
            <tr>
                <td class="text-right"><strong>Total Qty:</strong></td>
                <td class="text-right">{{ $totalQty }}</td>
            </tr>
            <tr>
                <td class="text-right"><strong>Sub Total:</strong></td>
                <td class="text-right">{{ number_format($subTotal,2) }}</td>
            </tr>
            <tr>
                <td class="text-right"><strong>Discount:</strong></td>
                <td class="text-right">{{ number_format($booking->discount_amount ?? 0,2) }}</td>
            </tr>
            <tr>
                <td class="text-right"><strong>Previous Balance</strong></td>
                <td class="text-right">{{ number_format($displayPrevious,2) }}</td>
            </tr>
            <tr>
                <td class="text-right"><strong>Closing Balance</strong></td>
                <td class="text-right">{{ number_format($displayClosing,2) }}</td>
            </tr>
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
