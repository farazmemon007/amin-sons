<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $order->po_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #1e293b;
            --accent-blue: #3b82f6;
            --accent-green: #10b981;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
        }

        @page { size: A4; margin: 0; }
        
        body { 
            font-family: 'Inter', sans-serif; 
            color: var(--text-main); 
            margin: 0; 
            padding: 0; 
            background: #f1f5f9;
            -webkit-print-color-adjust: exact;
        }

        .document-wrapper {
            width: 210mm;
            min-height: 297mm;
            margin: 20mm auto;
            background: #fff;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            position: relative;
            box-sizing: border-box;
        }

        @media print {
            body { background: #fff; }
            .document-wrapper { margin: 0; box-shadow: none; width: 100%; }
            .no-print { display: none !important; }
        }

        /* Top Header Banner */
        .top-banner {
            background: var(--primary-dark);
            padding: 25pt 40pt;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
        }

        .title-area h1 {
            margin: 0;
            font-size: 26pt;
            font-weight: 800;
            letter-spacing: -1pt;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12pt;
        }

        .status-badge {
            background: #fef3c7;
            color: #92400e;
            font-size: 8pt;
            font-weight: 800;
            padding: 4pt 10pt;
            border-radius: 20pt;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
        }

        .doc-id {
            font-size: 10pt;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            margin-top: 5pt;
        }

        .company-info {
            text-align: right;
        }

        .company-name {
            font-size: 20pt;
            font-weight: 800;
            letter-spacing: -0.5pt;
        }
        .company-name span { color: var(--accent-blue); }

        .company-sub {
            font-size: 9pt;
            font-weight: 600;
            color: rgba(255,255,255,0.8);
            margin-top: 2pt;
        }

        /* Info Cards Grid */
        .info-cards {
            display: grid;
            grid-template-columns: 1fr 1.5fr 1.2fr;
            gap: 15pt;
            padding: 25pt 40pt;
        }

        .card {
            background: var(--bg-light);
            border-radius: 8pt;
            padding: 15pt;
            border-left: 4pt solid transparent;
        }

        .card-blue { border-left-color: var(--accent-blue); }
        .card-green { border-left-color: var(--accent-green); }

        .card-label {
            font-size: 7pt;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            margin-bottom: 6pt;
        }

        .card-value {
            font-size: 11pt;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 4pt;
        }

        .card-sub-label {
            font-size: 7pt;
            font-weight: 800;
            color: #ef4444;
            text-transform: uppercase;
            margin-top: 8pt;
            margin-bottom: 2pt;
        }

        .badge-small {
            display: inline-block;
            background: #fff;
            border: 1pt solid var(--border-color);
            padding: 2pt 6pt;
            border-radius: 4pt;
            font-size: 7pt;
            font-weight: 700;
            color: var(--text-muted);
            margin-top: 4pt;
        }

        /* Main Table */
        .table-container {
            padding: 0 40pt 30pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            text-align: center;
            font-size: 7.5pt;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            padding: 10pt 5pt;
            border-bottom: 1.5pt solid var(--border-color);
        }

        tbody td {
            padding: 12pt 5pt;
            font-size: 9pt;
            color: var(--text-main);
            border-bottom: 0.5pt solid var(--bg-light);
            text-align: center;
            vertical-align: middle;
        }

        .td-left { text-align: left; }
        .td-right { text-align: right; }

        .item-title { font-weight: 700; color: var(--primary-dark); font-size: 10pt; }
        .item-code { font-size: 7.5pt; color: var(--text-muted); font-family: monospace; }

        .color-badge {
            display: inline-block;
            border: 1pt solid var(--accent-blue);
            color: var(--accent-blue);
            padding: 2pt 8pt;
            border-radius: 3pt;
            font-size: 7.5pt;
            font-weight: 800;
            text-transform: uppercase;
        }

        .pending-highlight { color: #ef4444; font-weight: 800; }
        .extension-val { font-weight: 700; color: var(--text-muted); }

        /* Summary Banner */
        .summary-banner {
            background: var(--primary-dark);
            margin: 0 40pt;
            padding: 20pt 30pt;
            border-radius: 4pt;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
        }

        .summary-left h4 {
            font-size: 7.5pt;
            font-weight: 800;
            color: rgba(255,255,255,0.6);
            margin: 0 0 5pt 0;
            text-transform: uppercase;
        }

        .summary-left .words {
            font-size: 12pt;
            font-weight: 700;
            color: #fff;
        }

        .summary-right {
            text-align: right;
        }

        .summary-right h4 {
            font-size: 7.5pt;
            font-weight: 800;
            color: rgba(255,255,255,0.6);
            margin: 0 0 5pt 0;
            text-transform: uppercase;
        }

        .total-amount {
            font-size: 24pt;
            font-weight: 500;
        }

        /* Signatures */
        .signature-section {
            margin-top: 60pt;
            padding: 0 40pt;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 40pt;
            text-align: center;
        }

        .sig-box {
            border-top: 1.5pt solid var(--border-color);
            padding-top: 8pt;
        }

        .sig-label {
            font-size: 7.5pt;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .sig-name {
            font-size: 9pt;
            font-weight: 700;
            margin-top: 4pt;
            color: var(--primary-dark);
        }

        /* Bottom Log */
        .system-log {
            text-align: center;
            font-size: 7pt;
            color: var(--text-muted);
            margin-top: 50pt;
            padding-bottom: 20pt;
        }

        /* Controls */
        .action-bar {
            position: fixed;
            top: 20pt;
            right: 20pt;
            display: flex;
            flex-direction: column;
            gap: 10pt;
            z-index: 9999;
        }

        .btn {
            background: #fff;
            border: 1pt solid var(--border-color);
            padding: 10pt 15pt;
            border-radius: 8pt;
            font-weight: 700;
            font-size: 9pt;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8pt;
            box-shadow: 0 4pt 12pt rgba(0,0,0,0.1);
            transition: 0.2s;
            text-decoration: none;
            color: var(--text-main);
        }

        .btn:hover { background: var(--bg-light); transform: translateY(-2pt); }
        .btn-whatsapp { background: #25d366; color: #fff; border-color: #22c55e; }
        .btn-whatsapp:hover { background: #16a34a; color: #fff; }

    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button class="btn" onclick="window.print()">
            🖨️ Export to PDF
        </button>
        <a href="https://wa.me/?text={{ urlencode('Purchase Order ' . $order->po_number . ' from ' . ($order->branch->name ?? 'New Wijdan') . "\nTotal: Rs. " . number_format($order->total_amount, 2) . "\nView: " . request()->fullUrl()) }}" target="_blank" class="btn btn-whatsapp">
            💬 Share via WhatsApp
        </a>
    </div>

    <div class="document-wrapper">
        <div class="top-banner">
            <div class="title-area">
                <h1>PURCHASE ORDER <span class="status-badge">{{ strtoupper($order->status ?? 'PENDING APPROVAL') }}</span></h1>
                <div class="doc-id">Official Document: #{{ $order->po_number }}</div>
            </div>
            <div class="company-info">
                <div class="company-name">{{ substr($order->branch->name ?? 'NEW WIJDAN', 0, 4) }}<span>{{ substr($order->branch->name ?? 'NEW WIJDAN', 4) }}</span></div>
                <div class="company-sub">{{ $order->branch->address ?? 'Main Market, Lahore' }}</div>
            </div>
        </div>

        <div class="info-cards">
            <div class="card card-blue">
                <div class="card-label">Order Date</div>
                <div class="card-value">{{ \Carbon\Carbon::parse($order->order_date)->format('d M, Y') }}</div>
                
                <div class="card-sub-label">Target Warehouse</div>
                <div class="card-value">{{ $order->warehouse->warehouse_name ?? 'N/A' }}</div>
            </div>

            <div class="card">
                <div class="card-label">Vendor / Supplier</div>
                <div class="card-value">{{ $order->vendor->name ?? 'N/A' }}</div>
                <div class="badge-small">DWG</div>
                <div style="font-size: 8.5pt; color: var(--text-muted); margin-top: 8pt; line-height: 1.4;">
                    📍 {{ $order->vendor->address ?? 'E27/286 Fakir Ka Pir' }}<br>
                    📞 {{ $order->vendor->phone ?? '0316324656' }}
                </div>
            </div>

            <div class="card card-green">
                <div class="card-label">Deals In (Brands)</div>
                <div style="font-size: 9pt; font-weight: 700; color: var(--accent-green);">
                    ✅ {{ !empty($brandNames) ? implode(', ', $brandNames) : 'Pak Fan, Super Asia' }}
                </div>
                
                <div class="card-label" style="margin-top: 15pt;">Instructions</div>
                <div style="font-size: 9pt; color: var(--text-muted); font-style: italic;">
                    "{{ $order->note ?? 'testing' }}"
                </div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 30pt;">ID</th>
                        <th class="td-left">Product Details</th>
                        <th style="width: 80pt;">Brand</th>
                        <th style="width: 60pt;">UOM</th>
                        <th style="width: 70pt;">Unit Price</th>
                        <th style="width: 60pt;">Ordered</th>
                        <th style="width: 60pt;">Received</th>
                        <th style="width: 60pt;">Pending</th>
                        <th class="td-right" style="width: 80pt;">Extension</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedItems = [];
                        foreach($order->items as $item) {
                            $key = $item->product_id . '_' . $item->unit_price;
                            if (!isset($groupedItems[$key])) {
                                $groupedItems[$key] = [
                                    'product' => $item->product,
                                    'colors' => [],
                                    'unit' => $item->unit,
                                    'unit_price' => $item->unit_price,
                                    'qty' => 0,
                                    'received_qty' => 0,
                                    'line_total' => 0,
                                ];
                            }
                            if ($item->color) {
                                $groupedItems[$key]['colors'][] = strtoupper($item->color) . ' (' . $item->qty . ')';
                            }
                            $groupedItems[$key]['qty'] += $item->qty;
                            $groupedItems[$key]['received_qty'] += $item->received_qty;
                            $groupedItems[$key]['line_total'] += $item->line_total;
                        }
                    @endphp
                    @foreach(array_values($groupedItems) as $i => $item)
                        <tr>
                            <td style="font-weight: 700;">{{ $i + 1 }}</td>
                            <td class="td-left">
                                <div class="item-title">{{ $item['product']->item_name ?? 'N/A' }}</div>
                                <div class="item-code" style="margin-bottom: 4pt;">{{ $item['product']->item_code ?? 'N/A' }}</div>
                                @if(count($item['colors']) > 0)
                                    <div>
                                        @foreach($item['colors'] as $c)
                                            <div class="color-badge" style="margin-bottom: 2pt; margin-right: 2pt;">{{ $c }}</div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($item['product'] && $item['product']->brand)
                                    <div style="font-weight: 700; color: var(--text-main);">{{ $item['product']->brand->name }}</div>
                                @else
                                    <span style="color: var(--text-muted); font-size: 8pt;">-</span>
                                @endif
                            </td>
                            <td style="font-weight: 700; font-size: 8pt; text-transform: uppercase;">{{ $item['unit'] ?? 'PIECE' }}</td>
                            <td>{{ number_format($item['unit_price'], 2) }}</td>
                            <td style="font-weight: 800;">{{ number_format($item['qty']) }}</td>
                            <td style="color: var(--accent-green); font-weight: 800;">{{ number_format($item['received_qty'] ?? 0) }}</td>
                            <td class="pending-highlight">{{ number_format($item['qty'] - ($item['received_qty'] ?? 0)) }}</td>
                            <td class="td-right extension-val">{{ number_format($item['line_total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="summary-banner">
            <div class="summary-left">
                <h4>Total Payable (In Words)</h4>
                <div class="words">{{ $order->amountInWords() }}</div>
            </div>
            <div class="summary-right">
                <h4>Grand Total Amount</h4>
                <div class="total-amount">Rs. {{ number_format($order->total_amount, 2) }}</div>
            </div>
        </div>

        <div class="signature-section">
            <div class="sig-box">
                <div class="sig-label">Prepared By</div>
                <div class="sig-name">{{ $order->creator->name ?? 'admin' }}</div>
            </div>
            <div class="sig-box">
                <div class="sig-label">Verified By</div>
                <div class="sig-name" style="color: var(--text-muted); font-size: 8pt;">Internal Audit Dept</div>
            </div>
            <div class="sig-box">
                <div class="sig-label">Authorized Signature</div>
                <div class="sig-name" style="color: var(--text-muted); font-size: 8pt;">Operations Manager</div>
            </div>
        </div>

        <div class="system-log">
            🕒 System Log: PO Created at {{ $order->created_at->format('d M Y h:i A') }} | Last Modified: {{ $order->updated_at->format('d M Y h:i A') }}
        </div>
    </div>

</body>
</html>
