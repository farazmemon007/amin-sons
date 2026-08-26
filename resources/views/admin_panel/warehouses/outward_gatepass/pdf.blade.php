<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Outward Gate Pass {{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
        .wrap{ max-width:900px; margin:0 auto; }
        .header{ text-align:left; margin-bottom:12px }
        .muted{ color:#6b7280 }
        .small{ font-size:12px }
        .card{ border:0; }
        .details td, .details th{ padding:6px; border:1px solid #ccc }
        table.items{ width:100%; border-collapse:collapse; margin-top:8px }
        table.items th, table.items td{ padding:6px; border:1px solid #ccc }
        .text-end{ text-align:right }
        .text-center{ text-align:center }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            <h3 style="margin:0">Outward Gate Pass <small class="muted">{{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</small></h3>
            <div class="small muted">
                Created: {{ optional($gp->created_at)->format('Y-m-d H:i') ?? '-' }} | 
                Invoice: {{ $gp->invoice_no ?? '-' }}
            </div>
        </div>

        <table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
            <tr>
                <td style="vertical-align:top; width:50%; padding:6px;">
                    <strong>Gatepass Details</strong>
                    <table class="details" style="width:100%; margin-top:6px; border-collapse:collapse;">
                        <tr><th style="width:35%">Order ID</th><td>{{ $gp->order_id }}</td></tr>
                        <tr><th>DC No</th><td>{{ $gp->dc_no ?? ($order->dc_no ?? '-') }}</td></tr>
                        <tr><th>Warehouse</th><td>{{ optional(\App\Models\Warehouse::find($gp->warehouse_id))->warehouse_name ?? ($gp->warehouse_id ?? '-') }}</td></tr>
                        <tr><th>Prepared By</th><td>{{ $gp->prepared_by ?? '-' }}</td></tr>
                    </table>
                </td>
                <td style="vertical-align:top; width:50%; padding:6px;">
                    <strong>Transport</strong>
                    <table class="details" style="width:100%; margin-top:6px; border-collapse:collapse;">
                        <tr><th style="width:35%">Driver</th><td>{{ $gp->driver_name ?? '-' }}</td></tr>
                        <tr><th>Vehicle</th><td>{{ $gp->vehicle_number ?? '-' }}</td></tr>
                        <tr><th>Transporter</th><td>{{ $gp->transporter ?? '-' }}</td></tr>
                        <tr><th>Billty No</th><td>{{ $gp->billty_no ?? '-' }}</td></tr>
                        <tr><th>Billty Date</th><td>{{ $gp->billty_date ?? '-' }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        @php
            $items = $gp->items ?? [];
            $totalQty = 0; $totalAmount = 0;
        @endphp

        <table class="items">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Product</th>
                    <th style="width:120px">Code</th>
                    <th style="width:80px" class="text-center">Unit</th>
                    <th style="width:80px" class="text-end">Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $k => $it)
                    @php
                        $row = is_array($it) ? $it : (is_object($it) ? (array)$it : ['text' => $it]);
                        $qty = isset($row['qty']) ? (float)$row['qty'] : 0;
                        $totalQty += $qty;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $k+1 }}</td>
                        <td>{{ $row['product_name'] ?? $row['text'] ?? '-' }}</td>
                        <td>{{ $row['item_code'] ?? '-' }}</td>
                        <td class="text-center">{{ !empty($row['unit']) ? $row['unit'] : (isset($row['unit']) ? 'N/A' : '-') }}</td>
                        <td class="text-end">{{ $qty ? rtrim(rtrim(number_format($qty,2), '0'), '.') : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No items recorded for this gate pass.</td></tr>
                @endforelse
            </tbody>
            @if(count($items))
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Totals</th>
                        <th class="text-end">{{ rtrim(rtrim(number_format($totalQty,2), '0'), '.') }}</th>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div style="margin-top:12px; display:flex; justify-content:space-between">
            <div style="width:60%">
                <strong>Packing / Handling Notes:</strong>
                <div style="margin-top:6px; color:#444"></div>
                <div style="margin-top:8px"><strong>Remarks:</strong>
                    <div style="color:#444">{{ $gp->remarks ?? '-' }}</div>
                </div>
            </div>
            <div style="text-align:right; width:35%">
                <div><strong>Issued By</strong></div>
                <div style="margin-top:6px">{{ $gp->issued_by ?? '-' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
                    <div style="margin-top:6px; color:#444">{{ $gp->remarks ?? '-' }}</div>
