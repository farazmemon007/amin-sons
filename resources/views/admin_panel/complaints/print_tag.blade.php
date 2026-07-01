<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Tag - {{ $complaint->complaint_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #fff; }

        .tag-container {
            width: 60mm;
            min-height: 40mm;
            margin: 0 auto;
            padding: 4mm;
            border: 2px solid #000;
            border-radius: 4px;
            font-size: 8pt;
        }

        .tag-header {
            text-align: center;
            font-weight: 900;
            font-size: 10pt;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }
        .tag-title {
            background: #000;
            color: white;
            text-align: center;
            font-size: 8pt;
            font-weight: 700;
            padding: 2px;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .tag-no {
            text-align: center;
            font-size: 10pt;
            font-weight: 900;
            letter-spacing: 1px;
            margin: 3px 0;
        }
        .tag-info td { font-size: 7pt; padding: 1px 0; }
        .tag-info td:first-child { font-weight: 700; padding-right: 5px; }
        .barcode-section { text-align: center; margin-top: 5px; border-top: 1px dashed #999; padding-top: 4px; }
        .barcode-section img { max-width:100%; height: 40px; }
        .warning-text {
            text-align: center;
            font-size: 6pt;
            color: #888;
            margin-top: 4px;
        }

        @media print {
            @page { margin: 0; size: 62mm 50mm; }
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="padding: 10px; text-align:center; background:#f8f9fa; border-bottom: 1px solid #ddd;">
    <button onclick="window.print()" style="background:#333;color:white;border:none;padding:8px 20px;border-radius:5px;cursor:pointer;">
        🏷️ Print Product Tag
    </button>
    <button onclick="window.close()" style="background:#6c757d;color:white;border:none;padding:8px 20px;border-radius:5px;cursor:pointer;margin-left:8px;">
        ✕ Close
    </button>
</div>

<div style="padding: 10mm; text-align: center;">
    <div class="tag-container">

        <div class="tag-header">AMEEN &amp; SONS</div>
        <div class="tag-title">⚠ DEFECTIVE / COMPLAINT ITEM</div>
        <div class="tag-no">{{ $complaint->complaint_no }}</div>

        <table class="tag-info" width="100%">
            <tr>
                <td>Date:</td>
                <td>{{ $complaint->complaint_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>Customer:</td>
                <td>{{ Str::limit($complaint->customer_name, 20) }}</td>
            </tr>
            @if($complaint->product_name)
            <tr>
                <td>Product:</td>
                <td>{{ Str::limit($complaint->product_name, 20) }}
                    @if($complaint->is_product_part && $complaint->product_part_name)
                        <br><span style="font-size:6pt; color:#666;">(Part: {{ Str::limit($complaint->product_part_name, 15) }})</span>
                    @endif
                </td>
            </tr>
            @endif
            @if($complaint->product_serial)
            <tr>
                <td>Serial:</td>
                <td>{{ $complaint->product_serial }}</td>
            </tr>
            @endif
        </table>

        <div class="barcode-section">
            @if($complaint->barcode_path && file_exists(storage_path('app/public/' . $complaint->barcode_path)))
            <img src="{{ asset('storage/' . $complaint->barcode_path) }}" alt="barcode">
            @else
            <div style="font-family:monospace; font-size:8pt; letter-spacing:2px;">{{ $complaint->complaint_no }}</div>
            @endif
        </div>

        <div class="warning-text">⚠ DO NOT SELL — UNDER COMPLAINT</div>

    </div>
</div>

</body>
</html>
