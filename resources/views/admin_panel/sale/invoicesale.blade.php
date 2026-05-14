{{-- @php
echo "<pre>";
print_r($sale->toArray());
echo "</pre>";
exit;
@endphp --}}


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
echo"<pre>";
    print_r($sale->toArray());
    // print_r($saleItems->toArray());

echo"</pre>";
    dd();
@endphp --}}
<div class="container-fluid mt-4">

    {{-- PRINT BUTTON --}}
    {{-- ACTION BUTTONS --}}
    <div class="text-end mb-3 no-print d-flex justify-content-end gap-2">
        <button type="button" onclick="shareWhatsApp()" class="btn btn-outline-success shadow-sm" style="border-color:#25D366; color:#25D366; background: #fff;">
            <i class="fab fa-whatsapp me-1"></i> WhatsApp
        </button>
        <button type="button" onclick="showExportOptions()" class="btn btn-outline-info shadow-sm" style="background: #fff;">
            <i class="fas fa-download me-1"></i> Export
        </button>
        <button onclick="window.print()" class="btn btn-dark px-4 shadow-sm">
            🖨️ Print Invoice
        </button>
        <button onclick="window.open('{{ url('sale/print2') }}/{{ $sale->id }}', '_blank')" class="btn btn-primary px-4 shadow-sm">
            🧾 Thermal
        </button>
    </div>

    <div class="invoice-wrapper" id="invoiceContent">

        {{-- HEADER --}}
        <div class="invoice-header">
            <div>
                <div class="company-name">{{ strtoupper($branch->name ?? 'AMEEN & SONS') }}</div>
                <div class="company-address">
                    Electronics & Home Appliences <br>
                    Lahore <br>
                    0300-0000000
                </div>
            </div>

            <div class="invoice-meta">
                <div><strong>Invoice #:</strong> {{ $sale->invoice_no }}</div>
                @if($sale->manual_invoice)
                <div><strong>Manual Invoice #:</strong> <span style="color:#e65100;font-weight:700;">{{ $sale->manual_invoice }}</span></div>
                @endif
                {{ $sale->created_at ? $sale->created_at->format('d-m-Y') : date('d-m-Y') }}  
                <div><strong>Status:</strong> Posted Sale</div>
            </div>
        </div>

        {{-- POSTED SALE INVOICE HEADING --}}
        <div class="invoice-title">📋 SALE INVOICE</div>

        <hr>

        {{-- CUSTOMER INFO --}}
        <div class="info-grid">
            <div class="info-box">
                @if ($sale->party_type === 'walking')
                    <div><strong>Customer Name:</strong> {{ $sale->sub_customer ?? 'N/A' }}</div>
                    @else
                    <div><strong>Customer Name:</strong> {{ $sale->customer->customer_name ?? 'N/A' }}</div>

                @endif
                <div><strong>Customer Type:</strong> {{ $sale->party_type ?? 'N/A' }}</div>
                <div><strong>Mobile:</strong> {{ $sale->tel ?? '-' }}</div>
            </div>

            <div class="info-box text-end">
                <div><strong>Address:</strong> {{ $sale->address ?? '-' }}</div>
                <div><strong>Remarks:</strong> {{ $sale->remarks ?? '-' }}</div>
            </div>
        </div>
        {{-- @php
        echo "<pre>";
           print_r($sale->toArray());
           dd();
        @endphp --}}

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
                @foreach($sale->saleItems as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if($item->product)
                            {{ $item->product->item_name ?? $item->product->product_name ?? 'N/A' }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($item->sales_qty ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($item->retail_price ?? $item->sales_price ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($item->discount_amount ?? 0, 2) }}</td>
                    <td class="text-end">{{ number_format($item->amount ?? 0, 2) }}</td>
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
                    // 🔹 BUSINESS LOGIC - DISPLAY TOTALS FROM ITEM AMOUNTS
                    
                    // Step 1: Calculate Gross Total (sum of all item amounts BEFORE order-level discount)
                    // Each item.amount already has line-item discount subtracted
                    $grossTotal = 0;
                    if($sale->saleItems){
                        foreach($sale->saleItems as $item){
                            $grossTotal += (float)($item->amount ?? 0);
                        }
                    }
                    // Alternative: use sale->sub_total1 if all items are properly saved
                    if($grossTotal == 0 && $sale->sub_total1){
                        $grossTotal = (float)$sale->sub_total1;
                    }
                    
                    // Step 2: Apply order-level discount
                    $orderLevelDiscount = (float)($sale->discount_amount ?? 0);
                    $afterOrderDiscount = $grossTotal - $orderLevelDiscount;
                    
                    // Step 3: Apply additional discount and extra charges
                    $additionalDiscount = (float)($sale->additional_discount ?? 0);
                    $extraCharges = (float)($sale->extra_charges ?? 0);
                    
                    // Step 4: Calculate Net Total (after ALL discounts and charges)
                    $netTotal = $afterOrderDiscount - $additionalDiscount + $extraCharges;
                    $netTotal = max(0, $netTotal); // Never negative
                    
                    // Step 5: Total Payable = Net Total (for display purposes)
                    $payableAmount = $netTotal;
                    
                    // Step 6: Get receipts from receipt vouchers for this invoice
                    $totalReceived = 0;
                    if(isset($sale) && isset($sale->invoice_no)){
                        $receipts = \App\Models\ReceiptsVoucher::where('reference_no', $sale->invoice_no)
                            ->where('type', 'SALE_RECEIPT')
                            ->get();
                        // Use total_amount (numeric) instead of amount (which can be JSON)
                        $totalReceived = (float) $receipts->sum('total_amount');
                    }
                    
                    // Step 7: Get customer ledger data (the source of truth for closing balance)
                    $ledgerData = null;
                    $displayPrevious = 0;
                    $displayClosing = 0;
                    $isCreditCustomer = ($sale->party_type ?? '') === 'credit';
                    
                    if($isCreditCustomer && isset($sale->customer)){
                        // Get the LATEST ledger entry for this customer
                        $ledgerData = \App\Models\CustomerLedger::where('customer_id', $sale->customer->id)
                            ->latest('id')
                            ->first();
                        
                        if($ledgerData){
                            $displayPrevious = floatval($ledgerData->previous_balance ?? 0);
                            $displayClosing = floatval($ledgerData->closing_balance ?? 0);  // Use ledger's closing balance
                        } else {
                            // Fallback: no ledger yet, calculate on the fly
                            $displayPrevious = floatval($sale->customer->opening_balance ?? 0);
                            $displayClosing = $payableAmount - $totalReceived + $displayPrevious;
                        }
                    } else {
                        // Cash customer: no ledger, closing = 0
                        $displayClosing = 0;
                    }
                @endphp
                
                <tr style="font-weight: bold;">
                    <td>Total</td>
                    <td class="text-end">{{ number_format($grossTotal, 2) }}</td>
                </tr>
                
                @if($orderLevelDiscount > 0)
                <tr style="font-weight: bold;">
                    <td>Order Discount</td>
                    <td class="text-end">-{{ number_format($orderLevelDiscount, 2) }}</td>
                </tr>
                @endif
                
                @if($additionalDiscount > 0)
                <tr style="font-weight: bold;">
                    <td>Additional Discount</td>
                    <td class="text-end">-{{ number_format($additionalDiscount, 2) }}</td>
                </tr>
                @endif
                
                @if($extraCharges > 0)
                <tr style="font-weight: bold;">
                    <td>Extra Charges</td>
                    <td class="text-end">+{{ number_format($extraCharges, 2) }}</td>
                </tr>
                @endif
                
                <tr style="background-color: #f5f5f5; font-weight: bold;">
                    <td>Net Total</td>
                    <td class="text-end">{{ number_format($netTotal, 2) }}</td>
                </tr>
                
                <tr class="summary-total" style="border-top: 2px solid #ddd; padding-top: 8px; margin-top: 8px; background-color: #fff3e0;">
                    <td style="color: #e65100; font-weight: bold; font-size: 16px;">💰 TOTAL PAYABLE</td>
                    <td class="text-end" style="color: #e65100; font-weight: bold; font-size: 16px;">{{ number_format($payableAmount, 2) }}</td>
                </tr>
                
                {{-- RECEIVED AMOUNT - Show for all customer types --}}
                <tr style="background-color: #e8f5e9;">
                    <td style="color: #388e3c; font-weight: bold;">Received Amount</td>
                    <td class="text-end" style="color: #388e3c; font-weight: bold;">{{ number_format($totalReceived, 2) }}</td>
                </tr>
                
                {{-- REMAINING BALANCE - Only show for credit customers or if there's actual balance pending --}}
                @php
                    $remainingBalance = max(0, $payableAmount - $totalReceived);
                    $isCredit = ($sale->party_type ?? '') === 'credit';
                    $showRemaining = $isCredit || $remainingBalance > 0;
                @endphp
                @if($showRemaining)
                <tr style="background-color: #fff3e0;">
                    <td style="color: #e65100; font-weight: bold;">Remaining Balance</td>
                    <td class="text-end" style="color: #e65100; font-weight: bold;">{{ number_format($remainingBalance, 2) }}</td>
                </tr>
                @endif
                
                {{-- CREDIT CUSTOMER SPECIFIC INFO --}}
                @if($sale->party_type == 'credit')
                <tr>
                    <td><strong>Previous Balance</strong></td>
                    <td class="text-end"><strong>{{ number_format($displayPrevious, 2) }}</strong></td>
                </tr>
                
                <tr class="summary-total" style="border-top: 2px solid #ddd; padding-top: 8px; margin-top: 8px; background-color: #f5f5f5;">
                    <td style="color: #d32f2f;"><strong>Closing Balance</strong></td>
                    <td class="text-end" style="color: #d32f2f;"><strong>{{ number_format($displayClosing, 2) }}</strong></td>
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

@section('js')
<script>
/* ---------- WhatsApp Share ---------- */
window.shareWhatsApp = function() {
    Swal.fire({
        title: 'Preparing WhatsApp Share...',
        text: 'Generating PDF document to share.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    var element = document.getElementById('invoiceContent');
    var opt = {
      margin:       [0.3, 0.3, 0.3, 0.3],
      filename:     'Sale_Invoice_{{ $sale->invoice_no }}.pdf',
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
        var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
        
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            navigator.share({
                title: 'Sale Invoice',
                text: 'Please find the attached Sale Invoice #{{ $sale->invoice_no }}.',
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
        
        var msg = "*Sale Invoice #{{ $sale->invoice_no }}*\nPlease find the attached PDF document.";
        var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
        window.open(waUrl, '_blank');
    });
}

/* ---------- Export Options & PDF ---------- */
window.showExportOptions = function() {
    Swal.fire({
        title: 'Export Sale Invoice',
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

    var element = document.getElementById('invoiceContent');
    var opt = {
      margin:       [0.3, 0.3, 0.3, 0.3],
      filename:     'Sale_Invoice_{{ $sale->invoice_no }}.pdf',
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save().then(function() {
        Swal.close();
    });
};

window.exportCSV = function () {
    var rows = [['#', 'Item Name', 'Qty', 'Rate', 'Disc Amt', 'Amount']];
    
    $('.items-table tbody tr').each(function () {
        var cells = [];
        $(this).find('td').each(function () {
            var text = $(this).text().trim().replace(/"/g, '""');
            cells.push('"' + text + '"');
        });
        if (cells.length) rows.push(cells);
    });
    
    rows.push([]);
    rows.push(['', 'Gross Total', '', '', '', '{{ number_format($grossTotal, 2) }}']);
    rows.push(['', 'Net Total', '', '', '', '{{ number_format($netTotal, 2) }}']);
    rows.push(['', 'Total Payable', '', '', '', '{{ number_format($payableAmount, 2) }}']);

    var csv  = rows.map(function(r){return r.join(',');}).join('\n');
    var blob = new Blob(["\uFEFF" + csv], {type:'text/csv;charset=utf-8;'});
    var url  = URL.createObjectURL(blob);
    var a    = document.createElement('a');
    a.href   = url;
    a.download = 'Sale_Invoice_{{ $sale->invoice_no }}.csv';
    a.click();
};
</script>
@endsection
