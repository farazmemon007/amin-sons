<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gatepass Thermal {{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size:12px; }
        .wrap{ width:280px; margin:0 auto; }
        .center{text-align:center}
        .small{font-size:11px;color:#333}
        table{width:100%;border-collapse:collapse;margin-top:6px}
        td,th{padding:4px;border-bottom:1px dashed #ccc}
        .no-border td{border-bottom:0}
        .text-right{text-align:right}
        .bold{font-weight:700}
    </style>
</head>
<body onload="setTimeout(()=>window.print(),300)">
    <div class="wrap">
        <div class="center">
            <h3 style="margin:4px 0">GATE PASS</h3>
            <div class="small bold">{{ $gp->gatepass_number ?? ('GP-' . str_pad($gp->id, 4, '0', STR_PAD_LEFT)) }}</div>
            <div class="small">{{ optional($gp->created_at)->format('Y-m-d H:i') ?? '' }}</div>
        </div>

        <table class="no-border">
            <tr><td>DC No</td><td class="text-right">{{ $gp->dc_no ?? ($order->dc_no ?? '-') }}</td></tr>
            <tr><td>Warehouse</td><td class="text-right">{{ optional(\App\Models\Warehouse::find($gp->warehouse_id))->warehouse_name ?? ($gp->warehouse_id ?? '-') }}</td></tr>
            <tr><td>Driver</td><td class="text-right">{{ $gp->driver_name ?? '-' }}</td></tr>
            <tr><td>Vehicle</td><td class="text-right">{{ $gp->vehicle_number ?? '-' }}</td></tr>
        </table>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Gatepass Thermal #{{ $gp->id }}</title>
    <style>
        /* Thermal/80mm friendly styling - compact and business-like */
        body{font-family: 'Helvetica Neue', Arial, sans-serif; font-size:12px; color:#111;}
        .wrap{width:320px; margin:0 auto; padding:8px}
        .brand{display:flex; align-items:center; gap:8px}
        .brand h2{font-size:16px; margin:0}
        .muted{color:#666; font-size:11px}
        table{width:100%; border-collapse:collapse; margin-top:8px}
        th,td{padding:6px 4px;}
        .hr{border-top:1px dashed #999; margin:8px 0}
        .text-right{text-align:right}
        .text-center{text-align:center}
        .small{font-size:11px}
        .signature{margin-top:18px; display:flex; justify-content:space-between}
        .sig-block{width:45%; text-align:center}
    </style>
</head>
<body onload="setTimeout(()=>window.print(),300)">
    <div class="wrap">
        <div class="brand">
            <div>
                <!-- Replace with logo if available -->
                <img src="{{ asset('assets/images/WIJDAN-removebg-preview.png') }}" alt="Logo" style="height:36px; object-fit:contain">
            </div>
            <div>
                <h2>Ameen &amp; Sons</h2>
                <div class="muted small">Warehouse Gate Pass</div>
            </div>
        </div>

        <div class="hr"></div>

        <div style="display:flex; justify-content:space-between; gap:8px; align-items:flex-start;">
            <div style="width:55%">
                <div><strong>GP #:</strong> {{ $gp->id }}</div>
                <div class="muted small">Date: {{ optional($gp->created_at)->format('Y-m-d H:i') ?? '' }}</div>
                <div class="muted small">Order: {{ $gp->order_id }}</div>
                <div class="muted small"><strong>Invoice:</strong> {{ $gp->invoice_no ?? '-' }}</div>
                <div class="muted small"><strong>Customer:</strong> {{ $gp->customer_name ?? '-' }}</div>
            </div>
            <div style="width:45%; text-align:right">
                <div><strong>DC No:</strong> {{ $gp->dc_no ?? ($order->dc_no ?? '-') }}</div>
                <div class="muted small">Warehouse: {{ optional(\App\Models\Warehouse::find($gp->warehouse_id))->warehouse_name ?? ($gp->warehouse_id ?? '-') }}</div>
                <div class="muted small">City: {{ $gp->delivery_city ?? '-' }}</div>
                <div class="muted small">Vehicle: {{ $gp->vehicle_type ?? '-' }}</div>
            </div>
        </div>

        <div class="hr"></div>

        <table>
            <thead>
                <tr>
                    <th style="width:28px">#</th>
                    <th>Item</th>
                    <th style="width:60px" class="text-center">Unit</th>
                    <th style="width:60px" class="text-right">Qty</th>
                </tr>
            </thead>
            <tbody>
                @php $items = $gp->items ?? []; @endphp
                @forelse($items as $k => $it)
                    @php $row = is_array($it) ? $it : (is_object($it) ? (array)$it : ['text'=>$it]); @endphp
                    <tr>
                        <td class="text-center">{{ $k+1 }}</td>
                        <td>{{ 
                            Str::limit($row['product_name'] ?? $row['text'] ?? '-', 40) }}</td>
                        <td class="text-center small">{{ !empty($row['unit']) ? $row['unit'] : (isset($row['unit']) ? 'N/A' : '-') }}</td>
                        <td class="text-right">{{ $row['qty'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center small">No items</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="hr"></div>

        <table style="width:100%">
            <tr>
                <td style="vertical-align:top">
                    <div class="small"><strong>Driver:</strong> {{ $gp->driver_name ?? '-' }}</div>
                    <div class="small"><strong>Vehicle:</strong> {{ $gp->vehicle_number ?? '-' }}</div>
                    <div class="small"><strong>Transporter:</strong> {{ $gp->transporter ?? '-' }}</div>
                    <div class="small"><strong>Transport Rent:</strong> {{ $gp->transport_rent ? number_format($gp->transport_rent, 0) : '-' }}</div>
                </td>
                <td style="text-align:right; vertical-align:top">
                    <div class="small"><strong>Billty No:</strong> {{ $gp->billty_no ?? '-' }}</div>
                    <div class="small"><strong>Billty Date:</strong> {{ $gp->billty_date ?? '-' }}</div>
                    <div class="small"><strong>Billty Amt:</strong> {{ $gp->billty_amount ? number_format($gp->billty_amount,2) : '-' }}</div>
                </td>
            </tr>
        </table>

        <div style="margin-top:8px;" class="small">
            <strong>Packing / Handling Notes:</strong>
            <div style="margin-top:6px">{{ $gp->packing_notes ?? '-' }}</div>
        </div>

        <div style="margin-top:8px;" class="small">
            <strong>Remarks:</strong>
            <div>{{ $gp->remarks ?? '-' }}</div>
        </div>

        <div class="signature">
            <div class="sig-block">Prepared By<br><br>__________________</div>
            <div class="sig-block">Received By<br><br>__________________</div>
        </div>

        <div style="margin-top:8px; font-size:11px; text-align:center" class="muted">Issued By: {{ $gp->issued_by ?? '-' }}</div>
    </div>
</body>
</html>