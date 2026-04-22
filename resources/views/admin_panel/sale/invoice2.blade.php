


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

.invoice-title{
    text-align:center;
    font-size:24px;
    font-weight:700;
    color:#333;
    margin:15px 0;
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
            🖨️ Print Invoice
        </button>
        <button onclick="window.open('{{ url('booking/print2') }}/{{ $booking->id }}', '_blank')" class="btn btn-primary ms-2">
            🧾 Thermal Print
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

        {{-- BOOKING INVOICE HEADING --}}
        <div class="invoice-title">📋 Sale Order</div>

        <hr>

        {{-- CUSTOMER INFO --}}
        <div class="info-grid">
            <div class="info-box">
                @if($booking->party_type == 'credit'||$booking->party_type == 'cash')
                <div><strong>Customer Name:</strong> {{ $booking->customer->customer_name }}</div>
                    @else
                    <div><strong>Customer Name:</strong> {{ $booking->customer_name }}</div>
                @endif
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
                @php
                    // Support both relationship-based and join-based item loading
                    $itemsToDisplay = isset($items) ? $items : $booking->items;
                @endphp
                @foreach($itemsToDisplay as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if($item->item_name ?? null)
                            {{ $item->item_name }}
                        @elseif($item->product ?? null)
                            {{ $item->product->item_name }}
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($item->sales_qty ?? $item->qty ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($item->retail_price ?? $item->price ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($item->discount_amount ?? $item->discount ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($item->amount ?? $item->total ?? 0, 2) }}</td>
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
                @php
                    // 🔹 BUSINESS LOGIC - DISPLAY TOTALS FROM BOOKING & CUSTOMER LEDGER
                    
                    // Step 1: Get base amounts from booking
                    $subTotal = (float)($item->amount ?? 0);              // Base amount
                    $saleLineTotal = ($booking->sub_total2 ?? 0);         // Subtotal
                    
                     
                    $orderLevelDiscount = ($booking->additional_discount ?? 0); // Add: discount
                    //  echo "Debug: orderLevelDiscount = " . $orderLevelDiscount;
                    $extraCharges = ($booking->extra_charges ?? 0);       // Extra charges
                    $salePayableAmount = $subTotal - $orderLevelDiscount + $extraCharges;
                    
                    // Step 2: Calculate Net Total
                    $netTotal = $saleLineTotal - $orderLevelDiscount;
                    $payableAmount = $salePayableAmount;
                    
                    // Step 3: Get receipts from receipt vouchers
                    $totalReceived = 0;
                    if(isset($booking) && isset($booking->invoice_no)){
                        $receipts = \App\Models\ReceiptsVoucher::where('reference_no', $booking->invoice_no)
                            ->where('type', 'SALE_RECEIPT')
                            ->get();
                        $totalReceived = $receipts->sum('amount');
                    }
                    
                    // Step 4: Get customer ledger data (the source of truth for closing balance)
                    $ledgerData = null;
                    $displayPrevious = 0;
                    $closingBalance = 0;
                    $isCreditCustomer = ($booking->party_type ?? '') === 'credit';
                    
                    if($isCreditCustomer && isset($booking->customer)){
                        // Get the LATEST ledger entry for this customer
                        $ledgerData = \App\Models\CustomerLedger::where('customer_id', $booking->customer->id)
                            ->latest('id')
                            ->first();
                        
                        if($ledgerData){
                            $displayPrevious = floatval($ledgerData->previous_balance ?? 0);
                            $closingBalance = floatval($ledgerData->closing_balance ?? 0);  // Use ledger's closing balance
                        } else {
                            // Fallback: no ledger yet, calculate on the fly
                            $displayPrevious = floatval($booking->customer->opening_balance ?? 0);
                            $closingBalance = $payableAmount - $totalReceived + $displayPrevious;
                        }
                    } else {
                        // Cash customer: calculate on the fly
                        $closingBalance = $payableAmount - $totalReceived + $displayPrevious;
                    }
                @endphp
                
                <tr style="font-weight: bold;">
                    <td>Total</td>
                    <td class="text-end">{{ number_format($subTotal, 2) }}</td>
                </tr>
                
                <tr style="background-color: #f5f5f5; font-weight: bold;">
                    <td>Add Discount</td>
                    <td class="text-end">{{ number_format($booking->additional_discount, 2) }}</td>
                </tr>
                <tr style="background-color: #f5f5f5; font-weight: bold;">
                    <td>Extra Charges</td>
                    <td class="text-end">{{ number_format($booking->extra_charges, 2) }}</td>
                </tr>
                <tr style="background-color: #f5f5f5; font-weight: bold;">
                    <td>Net Total</td>
                    <td class="text-end">{{ number_format($netTotal, 2) }}</td>
                </tr>
                @php
// echo "<pre>";
// print_r($booking->toArray());
// echo "</pre>";
// exit;
@endphp
                <tr class="summary-total" style="border-top: 2px solid #ddd; padding-top: 8px; margin-top: 8px; background-color: #fff3e0;">
                    <td style="color: #e65100; font-weight: bold; font-size: 16px;">💰 TOTAL PAYABLE</td>
                    <td class="text-end" style="color: #e65100; font-weight: bold; font-size: 16px;">{{ number_format($payableAmount, 2) }}</td>
                </tr>
                @if($booking->party_type === 'credit')
                @if($totalReceived > 0)
                <tr style="background-color: #e8f5e9; margin-top: 8px;">
                    <td style="color: #388e3c; font-weight: bold;">Less: Received Amount</td>
                    <td class="text-end" style="color: #388e3c; font-weight: bold;">-{{ number_format($totalReceived, 2) }}</td>
                </tr>
                @endif
                
                <tr>
                    <td><strong>Previous Balance</strong></td>
                    <td class="text-end"><strong>{{ number_format($displayPrevious, 2) }}</strong></td>
                </tr>
                
                <tr class="summary-total" style="border-top: 2px solid #ddd; padding-top: 8px; margin-top: 8px; background-color: #f5f5f5;">
                    <td style="color: #d32f2f;"><strong>Closing Balance</strong></td>
                    <td class="text-end" style="color: #d32f2f;"><strong>{{ number_format($closingBalance, 2) }}</strong></td>
                </tr>
                @endif
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
