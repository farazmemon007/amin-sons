@extends('admin_panel.layout.app')

@section('content')

<style>
    body{
        font-family: 'Poppins', 'Segoe UI', sans-serif;
        background:#eef2f7;
        font-size:14px;
        color:#2c3e50;
    }

    .dc-wrapper{
        background:#ffffff;
        width:100%;
        max-width:950px;
        margin:30px auto;
        padding:40px;
        border-radius:12px;
        box-shadow:0 5px 20px rgba(0,0,0,0.08);
        page-break-after: always;
    }

    /* HEADER */
    .top-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        border-bottom:3px solid #3f51b5;
        padding-bottom:15px;
        margin-bottom:25px;
    }

    .company-details h3{
        margin:0;
        font-size:26px;
        font-weight:700;
        color:#3f51b5;
    }

    .company-details p{
        margin:3px 0;
        font-size:13px;
        color:#555;
    }

    .dc-title{
        text-align:right;
    }

    .dc-title h2{
        margin:0;
        font-size:24px;
        font-weight:700;
        color:#333;
        letter-spacing:1px;
    }

    .dc-title span{
        font-size:14px;
        font-weight:600;
        color:#3f51b5;
    }

    /* INFO SECTION */
    .info-section{
        margin-bottom:25px;
        padding:15px;
        background:#f7f9fc;
        border-radius:8px;
    }

    .info-section table{
        width:100%;
    }

    .info-section td{
        padding:8px 10px;
        font-size:14px;
    }

    .label{
        font-weight:600;
        color:#3f51b5;
        width:150px;
    }

    /* TABLE */
    table.items-table{
        width:100%;
        border-collapse:collapse;
        margin-top:20px;
    }

    table.items-table th{
        background:#3f51b5;
        color:#fff;
        padding:12px;
        font-size:13px;
        text-transform:uppercase;
        letter-spacing:0.5px;
    }

    table.items-table td{
        padding:12px;
        border-bottom:1px solid #e0e6ed;
        font-size:14px;
    }

    table.items-table tbody tr:nth-child(even){
        background:#f9fbff;
    }

    .text-left{text-align:left;}
    .text-center{text-align:center;}
    .text-right{text-align:right;}

    /* TOTAL ROW */
    .total-row{
        background:#e8edff;
        font-weight:700;
        font-size:15px;
    }

    /* FOOTER */
    .footer-section{
        margin-top:60px;
        display:flex;
        justify-content:space-between;
    }

    .signature-box{
        width:30%;
        text-align:center;
    }

    .signature-line{
        border-top:2px solid #3f51b5;
        margin-top:50px;
        padding-top:8px;
        font-weight:600;
        color:#3f51b5;
    }

    .note-section{
        margin-top:25px;
        font-size:13px;
        background:#f4f6ff;
        padding:10px;
        border-left:4px solid #3f51b5;
        border-radius:5px;
    }

    .no-print{
        text-align:right;
        margin-bottom:15px;
    }

    @media print{
        .no-print{display:none;}
        body{background:#fff;}
        .dc-wrapper{box-shadow:none;}
    }
</style>

<div class="container-fluid">

    {{-- ACTION BUTTONS --}}
    <div class="no-print d-flex justify-content-end gap-2 mb-4">
        <button type="button" onclick="shareWhatsApp()" class="btn btn-outline-success shadow-sm" style="border-color:#25D366; color:#25D366; background: #fff;">
            <i class="fab fa-whatsapp me-1"></i> WhatsApp
        </button>
        <button type="button" onclick="showExportOptions()" class="btn btn-outline-info shadow-sm" style="background: #fff;">
            <i class="fas fa-download me-1"></i> Export
        </button>
        <button onclick="window.print()" class="btn btn-primary shadow-sm ms-2">
            <i class="fas fa-print me-1"></i> Print All
        </button>
        <a href="{{ route('sale.dc.thermal', $sale->id) }}" target="_blank" class="btn btn-secondary shadow-sm">
            <i class="fas fa-barcode me-1"></i> Thermal
        </a>

        @if(!empty($dcData) && count($dcData) > 0)
            @php
                $firstDC = $dcData[0];
                $warehouseOrderId = $firstDC['warehouse_order_id'] ?? null;
            @endphp
            @if($warehouseOrderId)
                <a href="{{ route('outward_gatepass.create', $warehouseOrderId) }}" 
                   class="btn btn-success shadow-sm" 
                   title="Create gate pass for this delivery">
                    <i class="fas fa-pager me-1"></i> Create Gate Pass
                </a>
            @endif
        @endif
    </div>

    <div id="dcContent">
        @foreach($dcData as $dc)

        <div class="dc-wrapper" id="dc-{{ $loop->index }}">

            <div class="no-print" style="text-align:right;margin-bottom:8px;">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="printSingleDC('dc-{{ $loop->index }}')">
                    Print This DC
                </button>
            </div>

            <!-- HEADER -->
            <div class="top-header">
                <div class="company-details">
                    <h3>{{ strtoupper($branch->name ?? 'AMEEN & SONS') }}</h3>
                    <p>Electronics & Glass Dealer</p>
                    <p>Main Road, City Name</p>
                    <p>Phone: 0300-0000000</p>
                </div>

                <div class="dc-title">
                    <h2>Delivery Challan</h2>
                    <span>DC No: {{ $dc['dc_no'] }}</span>
                </div>
            </div>

            <!-- INFO SECTION -->
            <div class="info-section">
                <table>
                    <tr>
                        <td class="label">Customer Name:</td>
                        @if ($sale->party_type == 'credit'|| $sale->party_type == 'cash')
                        <td>{{ $sale->customer->customer_name ?? '-' }}</td>
                            @else
                            <td>{{ $sale->sub_customer ?? '-' }}</td>
                        @endif

                        <td class="label">Invoice No:</td>
                        <td>{{ $sale->invoice_no }}</td>
                    </tr>
                    <tr>
                         <td class="label">Contact:</td>
                         @if ($sale->party_type == 'credit'|| $sale->party_type == 'cash')
                           <td>{{ $sale->customer->mobile ?? '-' }}</td>
                            @else
                            <td>{{ $sale->tel ?? '-' }}</td>
                        @endif

                        <td class="label">Date:</td>
                        <td>{{ $sale->created_at->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Warehouse:</td>
                        @php
                            $warehouseName = '-';
                            if (isset($dc['location_name'])) {
                                $warehouseName = $dc['location_name'];
                            } elseif (isset($dc['warehouse'])) {
                                if (is_array($dc['warehouse'])) {
                                    $warehouseName = $dc['warehouse']['warehouse_name'] ?? '-';
                                } else {
                                    $warehouseName = $dc['warehouse']->warehouse_name ?? '-';
                                }
                            }
                        @endphp
                        <td>{{ $warehouseName }}</td>

                        <td class="label">Address:</td>
                        @php
                            $address = '-';
                            if (isset($dc['delivery_location_type'])) {
                                if ($dc['delivery_location_type'] === 'branch' && isset($dc['branch'])) {
                                    if (is_array($dc['branch'])) {
                                        $address = $dc['branch']['address'] ?? '-';
                                    } else {
                                        $address = $dc['branch']->address ?? '-';
                                    }
                                } elseif ($dc['delivery_location_type'] === 'warehouse' && isset($dc['warehouse'])) {
                                    if (is_array($dc['warehouse'])) {
                                        $address = $dc['warehouse']['address'] ?? '-';
                                    } else {
                                        $address = $dc['warehouse']->address ?? '-';
                                    }
                                }
                            }
                            if ($address === '-') {
                                if ($sale->party_type == 'credit' || $sale->party_type == 'cash') {
                                    $address = $sale->customer->address ?? '-';
                                } else {
                                    $address = $sale->address ?? '-';
                                }
                            }
                        @endphp
                        <td>{{ $address }}</td>
                    </tr>
                </table>
            </div>

            <!-- ITEMS TABLE -->
            @php $totalQty = 0; @endphp

            <table class="items-table">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th class="text-left">Product</th>
                        <th width="15%">Color</th>
                        <th width="10%">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dc['items'] as $index => $item)
                        @php
                            if (is_array($item) || $item instanceof \Illuminate\Support\Fluent) {
                                $qty = (float) ($item['qty'] ?? $item['sales_qty'] ?? 0);
                                $productName = $item['product_name'] ?? ($item['product']['item_name'] ?? '-');
                                $productCode = $item['item_code'] ?? ($item['product']['item_code'] ?? '');
                                $colorRaw = $item['product']['color'] ?? '-';
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
                            } else {
                                $qty = (float) ($item->sales_qty ?? $item->qty ?? 0);
                                $productName = $item->product->item_name ?? ($item->product_name ?? '-');
                                $productCode = $item->product->item_code ?? ($item->item_code ?? '');
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
                            }
                            $totalQty += $qty;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index+1 }}</td>
                            <td class="text-left">
                                <strong>{{ $productName }}</strong><br>
                                <small>Code: {{ $productCode }}</small>
                            </td>
                            <td class="text-center">{{ $color }}</td>
                            <td class="text-center">{{ $qty }}</td>
                        </tr>
                    @endforeach

                    <tr class="total-row">
                        <td colspan="3" class="text-right">Total Quantity</td>
                        <td class="text-center">{{ $totalQty }}</td>
                    </tr>

                    @php
                        $warehouseOrderId = $dc['warehouse_order_id'] ?? null;
                        $deliveredQty = 0;
                        $remainingQty = 0;
                        if ($warehouseOrderId) {
                            $warehouseOrder = DB::table('warehouse_orders')->where('id', $warehouseOrderId)->first();
                            if ($warehouseOrder) {
                                $deliveredQty = (float)($warehouseOrder->delivered_qty ?? 0);
                                $remainingQty = (float)($warehouseOrder->remaining_qty ?? 0);
                            }
                        }
                    @endphp

                    @if($deliveredQty > 0 || $remainingQty > 0)
                    <tr style="background-color: #f0f5ff; border-top: 2px solid #3f51b5;">
                        <td colspan="3" class="text-right"><strong>Delivered Qty</strong></td>
                        <td class="text-center"><strong style="color: #4caf50;">{{ $deliveredQty }}</strong></td>
                    </tr>
                    <tr style="background-color: #f0f5ff;">
                        <td colspan="3" class="text-right"><strong>Remaining Qty</strong></td>
                        <td class="text-center"><strong style="color: #ff9800;">{{ $remainingQty }}</strong></td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <!-- NOTE -->
            <div class="note-section">
                Goods received in good condition. Kindly verify the quantity at the time of delivery.
            </div>

            <!-- SIGNATURES -->
            <div class="footer-section">
                <div class="signature-box">
                    <div class="signature-line">Receiver Signature</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">Authorized Signature</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">Warehouse In-Charge</div>
                </div>
            </div>

        </div>

        @endforeach
    </div> {{-- end #dcContent --}}

</div>

@endsection

@section('js')
<script>
    function printSingleDC(id) {
        try {
            const wrapper = document.getElementById(id);
            if (!wrapper) return alert('DC not found');
            const styles = Array.from(document.querySelectorAll('style, link[rel="stylesheet"]'))
                .map(n => n.outerHTML).join('\n');
            const html = '<!doctype html><html><head><meta charset="utf-8">' + styles + '</head><body>' + wrapper.outerHTML + '</body></html>';
            const win = window.open('', '_blank', 'toolbar=0,scrollbars=1,resizable=1,width=900,height=700');
            win.document.write(html);
            win.document.close();
            win.focus();
            setTimeout(() => { win.print(); }, 300);
        } catch (err) {
            console.error(err);
            alert('Print failed: ' + err.message);
        }
    }

/* ---------- WhatsApp Share ---------- */
window.shareWhatsApp = function() {
    Swal.fire({
        title: 'Preparing WhatsApp Share...',
        text: 'Generating PDF document to share.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    var element = document.getElementById('dcContent');
    var opt = {
      margin:       [0.3, 0.3, 0.3, 0.3],
      filename:     'Delivery_Challan_{{ $sale->invoice_no }}.pdf',
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
        var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
        
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({
                title: 'Delivery Challan',
                text: 'Please find the attached Delivery Challan for Invoice #{{ $sale->invoice_no }}.',
                files: [file]
            }).then(() => {
                Swal.close();
            }).catch((error) => {
                console.log('Error sharing', error);
                fallbackWaShare(pdfBlob, opt.filename);
            });
        } else {
            fallbackWaShare(pdfBlob, opt.filename);
        }
    });
};

function fallbackWaShare(pdfBlob, filename) {
    Swal.fire({
        icon: 'info',
        title: 'Share PDF via WhatsApp',
        text: 'The PDF will be downloaded now. WhatsApp will open allowing you to choose any chat. Please attach the downloaded PDF manually.',
        confirmButtonText: 'Download & Open WhatsApp'
    }).then(() => {
        var url = URL.createObjectURL(pdfBlob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        
        var msg = "*Delivery Challan #{{ $sale->invoice_no }}*\nPlease find the attached PDF document.";
        var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
        window.open(waUrl, '_blank');
    });
}

/* ---------- Export Options & PDF ---------- */
window.showExportOptions = function() {
    Swal.fire({
        title: 'Export Delivery Challan',
        text: 'Choose your preferred export format:',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545',
        confirmButtonText: '<i class="fas fa-file-excel me-1"></i> Excel (CSV)',
        cancelButtonText: '<i class="fas fa-file-pdf me-1"></i> PDF',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            exportCSV();
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            exportPDF();
        }
    });
};

window.exportPDF = function() {
    Swal.fire({
        title: 'Generating PDF...',
        text: 'Please wait while your PDF is being prepared.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    var element = document.getElementById('dcContent');
    var opt = {
      margin:       [0.3, 0.3, 0.3, 0.3],
      filename:     'Delivery_Challan_{{ $sale->invoice_no }}.pdf',
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save().then(function() {
        Swal.close();
    });
};

window.exportCSV = function () {
    var rows = [['#', 'Product', 'Color', 'Qty']];
    $('.dc-wrapper').each(function() {
        var dcNo = $(this).find('.dc-title span').text().trim();
        rows.push(['DC: ' + dcNo]);
        $(this).find('.items-table tbody tr').each(function () {
            if ($(this).hasClass('total-row')) return;
            var cells = [];
            $(this).find('td').each(function () {
                var text = $(this).text().trim().replace(/"/g, '""');
                cells.push('"' + text + '"');
            });
            if (cells.length) rows.push(cells);
        });
        rows.push([]);
    });
    var csv  = rows.map(function(r){return r.join(',');}).join('\n');
    var blob = new Blob(["\uFEFF" + csv], {type:'text/csv;charset=utf-8;'});
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href   = url;
    a.download = 'Delivery_Challan_{{ $sale->invoice_no }}.csv';
    a.click();
};
</script>
@endsection