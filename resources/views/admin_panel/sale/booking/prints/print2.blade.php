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
    }

    .info-label {
        font-weight: bold;
        width: 40%;
    }

    .info-value {
        width: 60%;
        text-align: right;
        word-break: break-word;
    }

    /* Items Table */
    .items-section {
        border-bottom: 1px dashed #000;
        padding: 5px 0;
        margin: 5px 0;
    }

    .items-header {
        display: grid;
        grid-template-columns: 30% 15% 20% 20%;
        gap: 2px;
        border-bottom: 1px solid #000;
        font-weight: bold;
        font-size: 9px;
        padding: 2px 0;
        margin-bottom: 3px;
    }

    .item-row {
        display: grid;
        grid-template-columns: 30% 15% 20% 20%;
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
        font-size: 11px;
        border-top: 1px solid #000;
        padding-top: 3px;
    }

    .footer {
        text-align: center;
        font-size: 9px;
        margin-top: 8px;
        color: #666;
        border-top: 1px solid #000;
        padding-top: 5px;
    }

    .no-print {
        text-align: center;
        margin-bottom: 10px;
    }

    @media print {
        .no-print {
            display: none;
        }

        body {
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .thermal-receipt {
            width: 80mm;
            max-width: 80mm;
            margin: 0;
            padding: 5mm;
            border: none;
        }
    }

    .text-center {
        text-align: center;
    }

    .text-left {
        text-align: left;
    }

    .text-right {
        text-align: right;
    }
</style>

<div class="container-fluid">
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-dark btn-sm">
            🖨️ Print Receipt
        </button>
    </div>

    <div class="thermal-receipt">
        <!-- HEADER -->
        <div class="header">
            <div class="header-title">Sale Order</div>
            <div class="header-subtitle">{{ now()->format('d-m-Y H:i') }}</div>
        </div>

        <!-- COMPANY -->
        <div class="company-name">Ameer & Sons</div>
        <div class="company-details">
            Electronics & Glass Dealer<br>
            Main Road, City<br>
            Ph: 0300-0000000
        </div>

        <!-- CUSTOMER INFO -->
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Customer:</span>
                <span class="info-value">
                    {{ $booking->customer->customer_name ?? $booking->party_name ?? 'Walk-in' }}
                </span>
            </div>
            @if($booking->customer)
                <div class="info-row">
                    <span class="info-label">ID:</span>
                    <span class="info-value">{{ $booking->customer->customer_id ?? '-' }}</span>
                </div>
            @endif
            <div class="info-row">
                <span class="info-label">Invoice:</span>
                <span class="info-value">{{ $booking->invoice_no ?? $booking->id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value">{{ $booking->created_at->format('d-m-Y') }}</span>
            </div>
        </div>

        <!-- ITEMS -->
        @php
            $totalQty = 0;
            $totalAmount = 0;
        @endphp

        <div class="items-section">
            <div class="items-header">
                <div class="text-left">Item</div>
                <div class="text-right">Qty</div>
                <div class="text-right">Rate</div>
                <div class="text-right">Amount</div>
            </div>

            @forelse($booking->items ?? [] as $item)
                @php
                    $itemName = $item->product->item_name ?? 'N/A';
                    $qty = (float)($item->sales_qty ?? 0);
                    $rate = (float)($item->retail_price ?? 0);
                    $amount = (float)($item->amount ?? $qty * $rate);
                    $totalQty += $qty;
                    $totalAmount += $amount;
                @endphp
                <div class="item-row">
                    <div class="item-name">{{ substr($itemName, 0, 15) }}</div>
                    <div class="item-qty">{{ $qty == (int)$qty ? (int)$qty : number_format($qty, 2) }}</div>
                    <div class="item-rate">{{ number_format($rate, 2) }}</div>
                    <div class="item-amount">{{ number_format($amount, 2) }}</div>
                </div>
            @empty
                <div class="item-row">
                    <div class="text-center" style="grid-column: 1/-1;">No items</div>
                </div>
            @endforelse
        </div>

        <!-- TOTALS -->
        <div class="totals-section">
            <div class="total-row">
                <span class="total-label">Total Qty:</span>
                <span class="total-value">{{ $totalQty == (int)$totalQty ? (int)$totalQty : number_format($totalQty, 2) }}</span>
            </div>

            @php
                $discount = (float)($booking->discount_amount ?? 0);
                $additionalDiscount = (float)($booking->additional_discount ?? 0);
                $previousBal = (float)($booking->previous_balance ?? 0);
                $receipts = (float)($booking->receipts_total ?? 0);
                $extraCharges = (float)($booking->extra_charges ?? 0);
                $netAmount = $totalAmount - $discount - $additionalDiscount + $previousBal - $receipts + $extraCharges;
            @endphp

            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span class="total-value">{{ number_format($totalAmount, 2) }}</span>
            </div>

            @if($discount > 0)
                <div class="total-row">
                    <span class="total-label">Discount:</span>
                    <span class="total-value">-{{ number_format($discount, 2) }}</span>
                </div>
            @endif

            @if($additionalDiscount > 0)
                <div class="total-row">
                    <span class="total-label">Addl. Disc:</span>
                    <span class="total-value">-{{ number_format($additionalDiscount, 2) }}</span>
                </div>
            @endif

            @if($previousBal > 0)
                <div class="total-row">
                    <span class="total-label">Previous:</span>
                    <span class="total-value">{{ number_format($previousBal, 2) }}</span>
                </div>
            @endif

            @if($receipts > 0)
                <div class="total-row">
                    <span class="total-label">Receipts:</span>
                    <span class="total-value">-{{ number_format($receipts, 2) }}</span>
                </div>
            @endif

            @if($extraCharges > 0)
                <div class="total-row">
                    <span class="total-label">Extra Charges:</span>
                    <span class="total-value">+{{ number_format($extraCharges, 2) }}</span>
                </div>
            @endif

            <div class="total-row grand-total">
                <span class="total-label">TOTAL:</span>
                <span class="total-value">{{ number_format(abs($netAmount), 2) }}</span>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <div style="margin-bottom: 3px;">Thank You!</div>
            <div style="font-size: 8px; color: #999;">{{ now()->format('d-m-Y H:i:s') }}</div>
        </div>
    </div>
</div>

@endsection
