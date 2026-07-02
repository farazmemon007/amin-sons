<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Replacement Slip - {{ $replacement->replacement_slip_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; background: #f0f0f0; color: #000; }

        .page-toolbar {
            background: #2c3e50;
            color: #fff;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-toolbar h5 { flex: 1; font-size: 14px; margin: 0; }
        .btn-print {
            background: #27ae60; color: white; border: none;
            padding: 8px 20px; border-radius: 5px; cursor: pointer;
            font-size: 13px; font-weight: bold;
        }
        .btn-back {
            background: #7f8c8d; color: white; border: none;
            padding: 8px 16px; border-radius: 5px; cursor: pointer;
            font-size: 13px; text-decoration: none; display: inline-block;
        }

        .slip-wrapper {
            display: flex;
            justify-content: center;
            padding: 30px 20px;
            min-height: calc(100vh - 60px);
        }

        .slip-container {
            width: 80mm;
            background: #fff;
            padding: 8mm 6mm;
            border: 1px solid #999;
            font-size: 9pt;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .company-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 6px;
        }
        .company-name {
            font-size: 15pt;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .company-sub { font-size: 7pt; color: #555; margin-top: 1px; }

        .slip-title {
            text-align: center;
            font-size: 10pt;
            font-weight: 700;
            background: #1a1a1a;
            color: white;
            padding: 4px;
            margin: 6px 0;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .slip-no {
            text-align: center;
            font-size: 13pt;
            font-weight: 900;
            letter-spacing: 2px;
            margin: 4px 0 6px;
            color: #c0392b;
        }
        .status-box {
            text-align: center;
            padding: 3px 6px;
            font-weight: 700;
            font-size: 8pt;
            border: 2px solid #000;
            margin: 4px 0 6px;
            background: #fffde7;
            letter-spacing: 1px;
        }

        .info-table { width: 100%; margin: 4px 0; border-collapse: collapse; }
        .info-table td { padding: 2.5px 2px; font-size: 8pt; vertical-align: top; line-height: 1.3; }
        .info-table td:first-child { font-weight: 700; width: 40%; }

        .section-header {
            font-weight: 700;
            font-size: 7.5pt;
            text-transform: uppercase;
            border-bottom: 1px dashed #333;
            padding-bottom: 2px;
            margin: 8px 0 4px;
            background: #f5f5f5;
            padding: 3px 4px;
        }

        .claim-box {
            border: 2px solid #000;
            padding: 6px;
            margin: 6px 0;
            background: #f9f9f9;
        }
        .claim-product-name {
            font-size: 11pt;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .claim-part-name {
            font-size: 9pt;
            font-weight: 700;
            text-align: center;
            color: #c0392b;
            border: 1px dashed #c0392b;
            padding: 2px 4px;
            margin: 3px 0;
            text-transform: uppercase;
        }
        .claim-qty {
            text-align: center;
            font-size: 8.5pt;
            margin-top: 3px;
        }

        .defective-box {
            border: 1px dashed #888;
            padding: 5px;
            margin: 5px 0;
            background: #fff5f5;
        }

        .sig-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            border-top: 1px solid #000;
            width: 44%;
            text-align: center;
            padding-top: 3px;
            font-size: 7pt;
        }
        .footer-note {
            border-top: 1px dashed #aaa;
            margin-top: 12px;
            padding-top: 6px;
            text-align: center;
            font-size: 6.5pt;
            color: #555;
            line-height: 1.4;
        }

        @media print {
            @page { margin: 0; size: 80mm auto; }
            body { background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page-toolbar { display: none; }
            .slip-wrapper { padding: 0; background: #fff; }
            .slip-container { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>

<div class="page-toolbar no-print">
    <h5>🖨️ Replacement Claim Slip — {{ $replacement->replacement_slip_no }}</h5>
    <a href="{{ url()->previous() }}" class="btn-back">← Back</a>
    <button onclick="window.print()" class="btn-print">🖨️ Print Now</button>
</div>

<div class="slip-wrapper">
<div class="slip-container">

    {{-- Company Header --}}
    <div class="company-header">
        <div class="company-name">{{ $replacement->complaint->branch->name ?? 'Ameen & Sons' }}</div>
        <div class="company-sub">Complaint &amp; Claims Management System</div>
        <div class="company-sub">Branch: {{ $replacement->complaint->branch->address ?? '' }}</div>
    </div>

    {{-- Title & Slip No --}}
    <div class="slip-title">🎟 REPLACEMENT CLAIM SLIP</div>
    <div class="slip-no">{{ $replacement->replacement_slip_no }}</div>

    {{-- Status --}}
    <div class="status-box">
        STATUS: {{ strtoupper($replacement->claim_status) }}
    </div>

    {{-- Complaint & Customer Info --}}
    <div class="section-header">Complaint & Customer Details</div>
    <table class="info-table">
        <tr><td>Date:</td><td>{{ $replacement->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Complaint Ref:</td><td><strong>{{ $replacement->complaint->complaint_no }}</strong></td></tr>
        <tr><td>Customer:</td><td><strong>{{ $replacement->complaint->customer_name }}</strong></td></tr>
        <tr><td>Mobile:</td><td>{{ $replacement->complaint->customer_mobile ?? '-' }}</td></tr>
    </table>

    {{-- Complained Product Info --}}
    <div class="section-header">Complained Product</div>
    <table class="info-table">
        <tr><td>Product:</td><td><strong>{{ $replacement->complaint->product_name ?? ($replacement->issuedProduct->item_name ?? '-') }}</strong></td></tr>
        @if($replacement->complaint->is_product_part && $replacement->complaint->product_part_name)
        <tr><td>Part Name:</td><td><strong style="color:#c0392b;">{{ $replacement->complaint->product_part_name }}</strong></td></tr>
        @endif
        @if($replacement->complaint->product_model)
        <tr><td>Model:</td><td>{{ $replacement->complaint->product_model }}</td></tr>
        @endif
        @if($replacement->complaint->product_serial)
        <tr><td>Serial/IMEI:</td><td>{{ $replacement->complaint->product_serial }}</td></tr>
        @endif
        <tr><td>Issue:</td><td style="font-style:italic;">{{ Str::limit($replacement->complaint->issue_description, 60) }}</td></tr>
    </table>

    {{-- Collected Defective Item --}}
    @if($replacement->collect_damaged && $replacement->collected_damaged_product_id)
    <div class="section-header">Defective Item Collected from Customer</div>
    <div class="defective-box">
        <table class="info-table">
            <tr><td>Product:</td><td><strong>{{ $replacement->collectedDamagedProduct->item_name ?? '-' }}</strong></td></tr>
            @if($replacement->is_collected_part && $replacement->collected_part_name)
            <tr><td>Damaged Part:</td><td><strong style="text-decoration:underline; color:#c0392b;">{{ $replacement->collected_part_name }}</strong></td></tr>
            @endif
            <tr><td>Qty Collected:</td><td><strong>{{ (float)$replacement->damaged_qty }} unit(s)</strong></td></tr>
        </table>
    </div>
    @endif

    {{-- Clean Replacement to Claim --}}
    <div class="section-header" style="background:#e8f5e9; border-bottom:2px solid #27ae60;">Replacement Item to Claim at Counter</div>
    <div class="claim-box">
        <div class="claim-product-name">
            {{ $replacement->issuedProduct->item_name ?? 'N/A' }}
        </div>
        @if($replacement->is_issued_part && $replacement->issued_part_name)
        <div class="claim-part-name">
            Part: {{ $replacement->issued_part_name }}
        </div>
        @endif
        <div class="claim-qty">
            Quantity to Receive: <strong>{{ (float)$replacement->quantity }} unit(s)</strong>
        </div>
    </div>

    {{-- Signatures --}}
    <div class="sig-section">
        <div class="sig-box">Customer Signature</div>
        <div class="sig-box">Authorized Signature</div>
    </div>

    {{-- Footer --}}
    <div class="footer-note">
        <strong>INSTRUCTIONS:</strong> Present this slip at the shop counter to claim your replacement item.
        This slip will be marked as claimed once the item is issued.
        <br><br>
        Printed: {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp; Generated by: {{ $replacement->createdByUser->name ?? 'System' }}
    </div>

</div>
</div>

<script>
    // Auto-trigger print dialog when page loads (only when opened fresh)
    window.addEventListener('load', function() {
        // Small delay to let CSS render
        setTimeout(function() {
            window.print();
        }, 600);
    });
</script>

</body>
</html>
