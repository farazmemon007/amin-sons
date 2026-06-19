<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complaint Slip - {{ $complaint->complaint_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; background: #fff; color: #000; }

        .slip-container {
            width: 80mm;
            min-height: 120mm;
            margin: 0 auto;
            padding: 8mm 6mm;
            border: 1px solid #000;
            font-size: 9pt;
        }

        .company-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        .company-name {
            font-size: 14pt;
            font-weight: 900;
            letter-spacing: 1px;
        }
        .company-sub {
            font-size: 7pt;
            color: #444;
        }
        .slip-title {
            text-align: center;
            font-size: 11pt;
            font-weight: 700;
            background: #000;
            color: white;
            padding: 4px;
            margin: 8px 0;
            letter-spacing: 1px;
        }
        .cmp-no {
            text-align: center;
            font-size: 13pt;
            font-weight: 900;
            letter-spacing: 2px;
            margin: 6px 0;
        }
        .info-table { width: 100%; margin: 6px 0; }
        .info-table td { padding: 3px 2px; font-size: 8pt; vertical-align: top; }
        .info-table td:first-child { font-weight: 700; width: 35%; }
        .barcode-section { text-align: center; margin: 8px 0; border-top: 1px dashed #999; padding-top: 8px; }
        .barcode-section img { max-width: 100%; height: 55px; }
        .footer-note {
            border-top: 1px dashed #999;
            margin-top: 8px;
            padding-top: 6px;
            text-align: center;
            font-size: 7pt;
            color: #555;
        }
        .scenario-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 7pt;
            font-weight: 700;
            background: #f0f0f0;
            border: 1px solid #ccc;
        }
        .status-box {
            text-align: center;
            padding: 4px;
            font-weight: 700;
            font-size: 9pt;
            border: 2px solid #000;
            margin: 5px 0;
        }

        @media print {
            @page { margin: 0; size: 80mm auto; }
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="padding: 10px; text-align:center; background:#f8f9fa; border-bottom: 1px solid #ddd;">
    <button onclick="window.print()" style="background:#2c3e90;color:white;border:none;padding:8px 20px;border-radius:5px;cursor:pointer;font-size:14px;">
        🖨️ Print Slip
    </button>
    <button onclick="window.close()" style="background:#6c757d;color:white;border:none;padding:8px 20px;border-radius:5px;cursor:pointer;font-size:14px;margin-left:8px;">
        ✕ Close
    </button>
</div>

<div class="slip-container">

    {{-- Company Header --}}
    <div class="company-header">
        <div class="company-name">AMEEN & SONS</div>
        <div class="company-sub">Complaint Management System</div>
        <div class="company-sub">Branch: {{ $complaint->branch->name ?? 'Head Office' }}</div>
    </div>

    {{-- Title --}}
    <div class="slip-title">⚠ COMPLAINT SLIP</div>

    {{-- Complaint No (big) --}}
    <div class="cmp-no">{{ $complaint->complaint_no }}</div>

    {{-- Scenario --}}
    <div style="text-align:center; margin-bottom:6px;">
        <span class="scenario-badge">
            @if($complaint->scenario_type === 'walk_in') 🏪 Walk-in Shop
            @elseif($complaint->scenario_type === 'remote') 📱 Phone/WhatsApp
            @else 🏠 Home Service
            @endif
        </span>
    </div>

    {{-- Status --}}
    <div class="status-box">
        STATUS: {{ strtoupper(str_replace('_', ' ', $complaint->status)) }}
    </div>

    {{-- Info --}}
    <table class="info-table">
        <tr><td>Date:</td><td>{{ $complaint->complaint_date->format('d/m/Y') }}</td></tr>
        <tr><td>Customer:</td><td><strong>{{ $complaint->customer_name }}</strong></td></tr>
        <tr><td>Mobile:</td><td>{{ $complaint->customer_mobile ?? '-' }}</td></tr>
        <tr><td>Address:</td><td>{{ $complaint->customer_address ?? '-' }}</td></tr>
        <tr><td>Product:</td><td>{{ $complaint->product_name ?? '-' }}</td></tr>
        @if($complaint->product_serial)
        <tr><td>Serial:</td><td>{{ $complaint->product_serial }}</td></tr>
        @endif
        @if($complaint->product_model)
        <tr><td>Model:</td><td>{{ $complaint->product_model }}</td></tr>
        @endif
    </table>

    {{-- Issue --}}
    <div style="background:#f8f8f8; border: 1px solid #ddd; padding: 5px; margin:5px 0; font-size:8pt; border-radius:3px;">
        <strong>Issue:</strong> {{ $complaint->issue_description }}
    </div>

    @if($complaint->scenario_type === 'home_service' && $complaint->homeServices->count() > 0)
    @php $lastVisit = $complaint->homeServices->last(); @endphp
    <table class="info-table" style="border: 1px solid #f5b942; padding: 4px; background: #fffaf0;">
        <tr><td>Technician:</td><td>{{ $lastVisit->technician_name }}</td></tr>
        <tr><td>Visit Date:</td><td>{{ $lastVisit->visit_date->format('d/m/Y') }}</td></tr>
        <tr><td>Charges:</td><td>Rs. {{ number_format($lastVisit->visiting_charges, 0) }}</td></tr>
    </table>
    @endif

    {{-- Barcode --}}
    <div class="barcode-section">
        @if($complaint->barcode_path && file_exists(storage_path('app/public/' . $complaint->barcode_path)))
        <img src="{{ asset('storage/' . $complaint->barcode_path) }}" alt="barcode">
        @else
        <div style="font-family:monospace; font-size:10pt; letter-spacing:3px;">{{ $complaint->complaint_no }}</div>
        @endif
        <div style="font-size:8pt; font-weight:700; margin-top:3px;">{{ $complaint->complaint_no }}</div>
    </div>

    {{-- Footer --}}
    <div class="footer-note">
        <strong>Please keep this slip safe for tracking your complaint.</strong><br>
        Printed: {{ now()->format('d/m/Y H:i') }}
    </div>

</div>

<script>
    // Auto-trigger print when page loads (optional)
    // window.addEventListener('load', () => window.print());
</script>
</body>
</html>
