@extends('admin_panel.layout.app')

@section('content')

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Courier New', monospace;
        background: #f1f3f5;
        font-size: 12px;
        color: #000;
    }

    .thermal-receipt {
        width: 80mm;
        max-width: 80mm;
        margin: 20px auto;
        background: #fff;
        padding: 5mm;
        border: 1px solid #ddd;
    }

    .header {
        text-align: center;
        border-bottom: 2px solid #000;
        padding-bottom: 5px;
        margin-bottom: 8px;
    }

    .header-title {
        font-weight: bold;
        font-size: 14px;
        letter-spacing: 1px;
    }

    .header-subtitle {
        font-size: 10px;
        color: #333;
    }

    .company-name {
        font-weight: bold;
        font-size: 12px;
        margin: 3px 0;
    }

    .company-details {
        font-size: 9px;
        line-height: 1.3;
        color: #555;
    }

    .info-section {
        border-bottom: 1px dashed #000;
        padding: 5px 0;
        margin: 5px 0;
        font-size: 10px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2px;
        word-break: break-word;
    }

    .info-label {
        font-weight: bold;
        width: 45%;
    }

    .info-value {
        width: 55%;
        text-align: right;
    }

    /* Items Table */
    .items-section {
        border-bottom: 1px dashed #000;
        padding: 5px 0;
        margin: 5px 0;
    }

    .items-header {
        display: grid;
        grid-template-columns: 35% 12% 18% 18%;
        gap: 2px;
        border-bottom: 1px solid #000;
        font-weight: bold;
        font-size: 8px;
        padding: 2px 0;
        margin-bottom: 3px;
    }

    .item-row {
        display: grid;
        grid-template-columns: 35% 12% 18% 18%;
        gap: 2px;
        font-size: 9px;
        padding: 2px 0;
        border-bottom: 1px dotted #ccc;
    }

    .item-name {
        word-break: break-word;
        text-align: left;
    }

    .item-qty,
    .item-rate,
    .item-amount {
        text-align: right;
    }

    /* Totals Section */
    .totals-section {
        border-bottom: 2px solid #000;
        padding: 5px 0;
        margin: 5px 0;
        font-size: 10px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        padding: 3px 0;
    }

    .total-label {
        font-weight: bold;
    }

    .total-value {
        text-align: right;
        min-width: 50px;
    }

    .grand-total {
        font-weight: bold;
        font-size: 12px;
        border-top: 1px solid #000;
        padding-top: 3px;
        color: #d32f2f;
    }

    .footer {
        text-align: center;
        font-size: 9px;
        margin-top: 10px;
        color: #666;
        border-top: 1px dashed #000;
        padding-top: 5px;
    }

    .thank-you {
        text-align: center;
        font-weight: bold;
        margin: 5px 0;
    }

    @media print {
        body {
            background: #fff;
        }
        .no-print {
            display: none !important;
        }
        .thermal-receipt {
            box-shadow: none;
            border: none;
            margin: 0;
            padding: 0;
            width: 80mm;
        }
    }
</style>

<div class="container-fluid mt-4">
    <div class="text-end mb-3 no-print">
        <button onclick="window.print()" class="btn btn-dark">
            🖨️ Print Thermal
        </button>
        <button onclick="window.close()" class="btn btn-secondary ms-2">
            ❌ Close
        </button>
    </div>

    <div class="thermal-receipt">
        {{-- HEADER --}}
        <div class="header">
            <div class="header-title">SALE INVOICE</div>
            <div class="company-name">{{ strtoupper($branch->name ?? 'AMEEN & SONS') }}</div>
            <div class="company-details">
                Electronics & Home Appliances<br>
                Lahore<br>
                0300-0000000
            </div>
        </div>

        {{-- INVOICE INFO --}}
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Invoice #:</span>
                <span class="info-value">{{ $sale->invoice_no }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value">{{ $sale->created_at ? $sale->created_at->format('d-m-Y H:i') : date('d-m-Y H:i') }}</span>
            </div>
        </div>

        {{-- CUSTOMER INFO --}}
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Customer:</span>
                <span class="info-value">{{ optional($sale->customer)->customer_name ?? $sale->sub_customer ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Type:</span>
                <span class="info-value">{{ strtoupper($sale->party_type ?? 'CASH') }}</span>
            </div>
            @php $mobile = optional($sale->customer)->mobile_2 ?? $sale->tel ?? '-'; @endphp
            <div class="info-row">
                <span class="info-label">Mobile:</span>
                <span class="info-value">{{ $mobile }}</span>
            </div>
        </div>

        {{-- ITEMS --}}
        <div class="items-section">
            <div class="items-header">
                <div class="item-name">ITEM</div>
                <div class="item-qty">QTY</div>
                <div class="item-rate">RATE</div>
                <div class="item-amount">AMOUNT</div>
            </div>

            @foreach($sale->saleItems as $item)
            <div class="item-row">
                <div class="item-name">
                    {{ $item->product ? ($item->product->item_name ?? $item->product->product_name ?? 'N/A') : 'N/A' }}
                </div>
                <div class="item-qty">{{ number_format($item->sales_qty ?? 0, 2) }}</div>
                <div class="item-rate">{{ number_format($item->retail_price ?? $item->sales_price ?? 0, 2) }}</div>
                <div class="item-amount">{{ number_format($item->amount ?? 0, 2) }}</div>
            </div>
            @endforeach
        </div>

        {{-- TOTALS --}}
        <div class="totals-section">
            @php
                $subTotal = (float)($sale->sub_total1 ?? $sale->sub_total2 ?? 0);
                $discountAmount = (float)($sale->discount_amount ?? 0);
                $netTotal = $subTotal - $discountAmount;
                $payableAmount = $netTotal;

                // Get receipts from receipt vouchers for this invoice
                $totalReceived = 0;
                if(isset($sale) && isset($sale->invoice_no)){
                    $receipts = \App\Models\ReceiptsVoucher::where('reference_no', $sale->invoice_no)
                        ->where('type', 'SALE_RECEIPT')
                        ->get();
                    $totalReceived = $receipts->sum('amount');
                }
                
                // Get customer ledger data
                $displayPrevious = 0;
                $displayClosing = 0;
                $isCreditCustomer = ($sale->party_type ?? '') === 'credit';
                
                if($isCreditCustomer && isset($sale->customer)){
                    $ledgerData = \App\Models\CustomerLedger::where('customer_id', $sale->customer->id)
                        ->latest('id')
                        ->first();
                    
                    if($ledgerData){
                        $displayPrevious = floatval($ledgerData->previous_balance ?? 0);
                        $displayClosing = floatval($ledgerData->closing_balance ?? 0);
                    } else {
                        $displayPrevious = floatval($sale->customer->opening_balance ?? 0);
                        $displayClosing = $payableAmount - $totalReceived + $displayPrevious;
                    }
                } else {
                    $displayClosing = $payableAmount - $totalReceived + $displayPrevious;
                }
            @endphp

            <div class="total-row">
                <span class="total-label">Sub Total:</span>
                <span class="total-value">{{ number_format($subTotal, 2) }}</span>
            </div>

            @if($discountAmount > 0)
            <div class="total-row">
                <span class="total-label">Discount:</span>
                <span class="total-value">-{{ number_format($discountAmount, 2) }}</span>
            </div>
            @endif

            @if(($sale->additional_discount ?? 0) > 0)
            <div class="total-row">
                <span class="total-label">Add Discount:</span>
                <span class="total-value">{{ number_format($sale->additional_discount ?? 0, 2) }}</span>
            </div>
            @endif

            @if(($sale->extra_charges ?? 0) > 0)
            <div class="total-row">
                <span class="total-label">Extra Charges:</span>
                <span class="total-value">{{ number_format($sale->extra_charges ?? 0, 2) }}</span>
            </div>
            @endif

            <div class="total-row grand-total">
                <span class="total-label">💰 TOTAL PAYABLE:</span>
                <span class="total-value">{{ number_format($payableAmount, 2) }}</span>
            </div>

            @if($sale->party_type == 'credit')
                @if($totalReceived > 0)
                <div class="total-row">
                    <span class="total-label">Received:</span>
                    <span class="total-value">-{{ number_format($totalReceived, 2) }}</span>
                </div>
                @endif

                <div class="total-row">
                    <span class="total-label">Prev Balance:</span>
                    <span class="total-value">{{ number_format($displayPrevious, 2) }}</span>
                </div>

                <div class="total-row grand-total">
                    <span class="total-label">CLOSING BAL:</span>
                    <span class="total-value">{{ number_format($displayClosing, 2) }}</span>
                </div>
            @endif
        </div>

        {{-- FOOTER --}}
        <div class="footer">
            <div class="thank-you">Thank You!</div>
            <div>Please visit again</div>
            <div style="margin-top: 5px; font-size: 8px;">
                Printed: {{ now()->format('d-m-Y H:i:s') }}
            </div>
        </div>
    </div>
</div>

@endsection
