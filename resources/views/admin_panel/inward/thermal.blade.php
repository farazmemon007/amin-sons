<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal Receipt - Inward Gatepass</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            width: 80mm;
            margin: 0 auto;
            background: white;
            color: #000;
        }

        .receipt {
            width: 80mm;
            padding: 5mm;
            page-break-after: always;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5mm;
            margin-bottom: 5mm;
        }

        .company-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 2mm;
        }

        .title {
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            margin: 3mm 0 2mm 0;
        }

        .ref-line {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 2mm;
        }

        .label {
            font-weight: bold;
            width: 35%;
        }

        .value {
            width: 65%;
            text-align: right;
        }

        .section {
            margin-bottom: 5mm;
        }

        .section-title {
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 1px dashed #000;
            margin-bottom: 3mm;
            padding-bottom: 2mm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        table th {
            text-align: left;
            font-weight: bold;
            border-bottom: 1px dashed #000;
            padding: 2mm 0;
        }

        table td {
            padding: 2mm 0;
        }

        .item-name {
            font-weight: bold;
            width: 50%;
        }

        .item-qty {
            text-align: right;
            width: 25%;
        }

        .item-price {
            text-align: right;
            width: 25%;
        }

        .total-line {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            padding: 3mm 0;
            margin-top: 3mm;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 5mm;
            border-top: 1px dashed #000;
            padding-top: 3mm;
        }

        .note {
            font-size: 9px;
            word-wrap: break-word;
            margin: 3mm 0;
            line-height: 1.3;
        }

        .signature-area {
            margin-top: 8mm;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }

        .sig-box {
            text-align: center;
            width: 30%;
        }

        .sig-line {
            border-top: 1px solid #000;
            margin-top: 8mm;
            height: 15mm;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .receipt {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- HEADER -->
        <div class="header">
            <div class="company-name">{{ Auth::user()->branch->name ?? Auth::user()->branch_id ?? 'Head Office' }}</div>
            <div class="title">Inward Gatepass (GRN)</div>
            <div style="font-size: 9px; color: #000;">Goods Receipt Note</div>
        </div>

        <!-- REFERENCE LINES -->
        <div class="section">
            <div class="ref-line">
                <span class="label">GP #:</span>
                <span class="value">#GP-{{ str_pad($gatepass->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="ref-line">
                <span class="label">Date:</span>
                <span class="value">{{ \Carbon\Carbon::parse($gatepass->gatepass_date)->format('d-M-Y') }}</span>
            </div>
            <div class="ref-line">
                <span class="label">Vendor:</span>
                <span class="value" style="text-align: right;">{{ $gatepass->vendor->name ?? 'N/A' }}</span>
            </div>
            <div class="ref-line">
                <span class="label">Warehouse:</span>
                <span class="value">{{ $gatepass->warehouse->warehouse_name ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- ITEMS TABLE -->
        <div class="section">
            <div class="section-title">Items Received</div>
            <table>
                <thead>
                    <tr>
                        <th class="item-name">Item</th>
                        <th class="item-qty" style="width: 50%; text-align: right;">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmount = 0; @endphp
                    @foreach($gatepass->items as $item)
                        @php
                            $itemTotal = $item->qty * ($item->rate ?? 0);
                            $totalAmount += $itemTotal;
                        @endphp
                        <tr>
                            <td class="item-name">{{ $item->product->item_name ?? 'N/A' }}</td>
                            <td class="item-qty" style="width: 50%; text-align: right;">{{ number_format($item->qty, 0) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" style="font-size: 9px; color: #000;">
                                Code: {{ $item->product->item_code ?? 'N/A' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- TOTALS -->
        <div class="section">
            <div class="total-line">
                <span>TOTAL RECEIVED QTY:</span>
                <span>{{ number_format($gatepass->items->sum('qty'), 0) }}</span>
            </div>
        </div>

        <!-- STATUS & NOTE -->
        <div class="section">
            <div class="ref-line">
                <span class="label">Status:</span>
                <span class="value" style="font-weight: bold;">{{ strtoupper($gatepass->display_status ?? 'PENDING') }}</span>
            </div>
            @if($gatepass->note)
                <div class="note">
                    <strong>Note:</strong> {{ $gatepass->note }}
                </div>
            @endif
        </div>

        <!-- SIGNATURE AREA -->
        <div class="signature-area">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div style="font-size: 8px;">Receiver</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div style="font-size: 8px;">Verified By</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div style="font-size: 8px;">Authorized</div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <div style="margin: 3mm 0;">{{ now()->format('d-M-Y H:i A') }}</div>
            <div style="font-size: 8px; color: #000;">Thank you for your business</div>
        </div>
    </div>

    <script>
        // Auto-print on page load
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
