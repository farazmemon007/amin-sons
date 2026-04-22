<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thermal DC Print</title>
    <style>
        /* Reset all styles and isolate from external CSS */
        html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav, output, ruby, section, summary, time, mark, audio, video {
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            font-size: 100% !important;
            font: inherit !important;
            vertical-align: baseline !important;
            background: transparent !important;
            box-sizing: border-box !important;
        }
        
        /* Thermal-friendly styles (58mm paper approx) */
        @page { size: 58mm auto; margin: 2mm; }
        
        body { 
            font-family: Arial, Helvetica, sans-serif !important; 
            font-size: 11px !important; 
            color: #000 !important; 
            margin: 0 !important; 
            padding: 4px !important; 
            background: #fff !important;
            width: 100% !important;
            max-width: none !important;
            float: none !important;
            clear: both !important;
        }
        
        .therm-wrap { 
            width: 100% !important; 
            max-width: 280px !important; 
            margin: 0 auto !important; 
            float: none !important;
        }
        
        .center { text-align: center !important; }
        .company { font-weight: 700 !important; font-size: 12px !important; letter-spacing: 0.5px !important; }
        .muted { font-size: 9px !important; color: #333 !important; }
        .small { font-size: 10px !important; }
        .tiny { font-size: 9px !important; }
        
        table { 
            width: 100% !important; 
            border-collapse: collapse !important; 
            margin-top: 4px !important;
            border: none !important;
            background: transparent !important;
        }
        
        td, th { 
            padding: 2px 0 !important; 
            vertical-align: top !important; 
            font-size: 10px !important; 
            border: none !important;
            background: transparent !important;
            text-align: left !important;
        }
        
        .sep { border-top: 1px dashed #000 !important; margin: 4px 0 !important; }
        .qty { text-align: right !important; width: 40px !important; }
        .col-code { color: #333 !important; font-size: 9px !important; }
        .note { font-size: 9px !important; margin-top: 4px !important; }
        .sig { margin-top: 8px !important; font-size: 10px !important; }
        .muted-block { 
            background: #f5f5f5 !important; 
            padding: 4px !important; 
            border-radius: 2px !important; 
            margin-top: 4px !important;
            border: none !important;
        }
        .right { text-align: right !important; }
        .fw-bold { font-weight: bold !important; }

        /* Links and buttons */
        a { text-decoration: none !important; color: inherit !important; }
        .btn { 
            display: inline-block !important; 
            padding: 4px 8px !important; 
            background: #6c757d !important; 
            color: #fff !important; 
            text-decoration: none !important;
            border: none !important;
            font-size: 12px !important;
        }

        /* Print styles */
        @media print { 
            .no-print { display: none !important; } 
            body { padding: 0 !important; }
            @page { margin: 0 !important; }
            html, body { margin: 0 !important; padding: 0 !important; }
        }
    </style>
</head>
<body onload="setTimeout(function(){ window.print(); }, 300)">
    <div class="no-print" style="text-align:right;margin:8px 0;">
        @foreach($dcData as $dc)
            <button type="button" class="btn btn-sm btn-secondary" style="margin-left:6px;" onclick="printDCInline('{{ $dc['warehouse_id'] }}')">Print {{ $dc['dc_no'] }}</button>
        @endforeach
    </div>

    <div class="therm-wrap">
        @foreach($dcData as $dc)
            <div id="dc-block-{{ $dc['warehouse_id'] }}">
                <div class="center">
                    <div class="company">{{ strtoupper($branch->name ?? 'AMEEN & SONS') }}</div>
                    <div class="muted">Electronics &amp; Glass Dealer</div>
                    <div class="muted tiny">Main Road, City Name | Phone: 0300-0000000</div>
                </div>

                

                <div class="muted-block">
                    <table>
                        <tr>
                            <td class="small">DC No</td>
                            <td class="right small">{{ $dc['dc_no'] }}</td>
                        </tr>
                        <tr>
                            <td class="small">Invoice No</td>
                            <td class="right small">{{ $sale->invoice_no }}</td>
                        </tr>
                        <tr>
                            <td class="small">Date</td>
                            <td class="right small">{{ optional($sale->created_at)->format('d-m-Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="small">Customer</td>
                                @if ($sale->party_type == 'credit'|| $sale->party_type == 'cash')
                                <td class="right small">{{ $sale->customer->customer_name ?? '-' }}</td>
                                    @else
                                    <td class="right small">{{ $sale->sub_customer ?? '-' }}</td>
                           @endif
                        </tr>
                        <tr>
                            <td class="small">Contact</td>
                            <td class="right small">{{ $sale->customer->mobile ?? $sale->tel ?? '-' }}</td>
                        </tr>
                      
                        <tr>
                            <td class="small">Address</td>
                                @if ($sale->party_type == 'credit'|| $sale->party_type == 'cash')
                            <td class="right small">{{ $sale->customer->address ?? '-' }}</td>
                            @else
                            <td class="right small">{{ $sale->address ?? '-' }}</td>
                            @endif
                        </tr>
                    </table>
                </div>

                <div class="sep"></div>

                <table>
                    <thead>
                        <tr>
                                <th class="small">#</th>
                                <th class="small">Product</th>
                                <th class="small">Color</th>
                                <th class="small">Warehouse</th>
                                <th class="small qty">Qty</th>
                            </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; $totalQty = 0; @endphp
                        @foreach($dc['items'] as $item)
                            @php
                                $q = (float) ($item->sales_qty ?? 0); $totalQty += $q;
                                $colorRaw = $item->product->color ?? '-';
                                $color = '-';
                                if (is_array($colorRaw)) {
                                    $color = count($colorRaw) > 0 ? $colorRaw[0] : '-';
                                } elseif (is_string($colorRaw) && strpos($colorRaw, '[') === 0) {
                                    $decoded = json_decode($colorRaw, true);
                                    if (is_array($decoded) && count($decoded) > 0) {
                                        $color = $decoded[0];
                                    } else {
                                        $color = $colorRaw;
                                    }
                                } else {
                                    $color = $colorRaw;
                                }
                            @endphp
                            <tr>
                                <td class="small">{{ $i++ }}</td>
                                <td class="small">
                                    <div>{{ $item->product->item_name ?? '-' }}</div>
                                    <div class="col-code">{{ $item->product->item_code ?? '' }}</div>
                                </td>
                                <td class="small">{{ $color }}</td>
                                <td class="small">{{ $item->warehouse->warehouse_name ?? ($item->warehouse_id ?? '-') }}</td>
                                <td class="small qty">{{ (int)$q == $q ? (int)$q : rtrim(rtrim(number_format($q,2,'.',''),'0'),'.') }}</td>
                            </tr>
                        @endforeach

                        <tr>
                            <td colspan="4" class="small right"><strong>Total Quantity</strong></td>
                            <td class="small qty"><strong>{{ $totalQty }}</strong></td>
                        </tr>

                        <!-- ✅ ERP Standard: Show Delivery Tracking -->
                        @php
                            // Fetch warehouse_orders to get delivered/remaining tracking
                            $warehouseOrderId = $warehouseOrder->id ?? null;
                            $deliveredQty = (float)($warehouseOrder->delivered_qty ?? 0);
                            $remainingQty = (float)($warehouseOrder->remaining_qty ?? 0);
                        @endphp

                        @if($deliveredQty > 0 || $remainingQty > 0)
                        <tr style="background:#f0f5ff;">
                            <td colspan="4" class="small right"><strong>Delivered</strong></td>
                            <td class="small qty"><strong>{{ $deliveredQty }}</strong></td>
                        </tr>
                        <tr style="background:#f0f5ff;">
                            <td colspan="4" class="small right"><strong>Remaining</strong></td>
                            <td class="small qty"><strong>{{ $remainingQty }}</strong></td>
                        </tr>
                        @endif
                    </tbody>
                </table>

                <div class="note">Goods received in good condition. Kindly verify the quantity at the time of delivery.</div>

                <div class="sig">
                    <div>Receiver Signature: ____________________</div>
                    <div style="height:8px"></div>
                    <div>Prepared By: {{ auth()->user()->name ?? '-' }}</div>
                </div>

                <div style="height:12px"></div>
            </div>
            <div style="page-break-after:always"></div>
        @endforeach
    </div>
    <script>
        // Print a single DC by fetching the server-rendered thermal HTML for that warehouse
        async function printDCInline(warehouseId) {
            try {
                const url = '{{ route('sale.dc.thermal', $sale->id) }}' + '?warehouse=' + encodeURIComponent(warehouseId);

                const iframe = document.createElement('iframe');
                iframe.style.position = 'fixed';
                iframe.style.right = '0';
                iframe.style.bottom = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                iframe.style.overflow = 'hidden';
                document.body.appendChild(iframe);

                const res = await fetch(url, { credentials: 'same-origin' });
                if (!res.ok) throw new Error('Failed to load DC preview');
                const html = await res.text();

                const doc = iframe.contentWindow.document;
                doc.open();
                doc.write(html);
                doc.close();

                // Give browser time to render then print
                setTimeout(function() {
                    try { iframe.contentWindow.focus(); } catch(e) {}
                    try { iframe.contentWindow.print(); } catch(e) { alert('Print failed: ' + e.message); }
                    setTimeout(function() { try { document.body.removeChild(iframe); } catch(e) {} }, 1000);
                }, 500);

            } catch (err) {
                console.error(err);
                alert('Print failed: ' + err.message);
            }
        }
    </script>
</body>
</html>
