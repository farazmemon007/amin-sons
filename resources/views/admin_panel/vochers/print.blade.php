<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Receipt Voucher - {{ $voucher->rvid }}</title>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1e293b;
            --primary-light: #334155;
            --accent: #0f766e;
            --accent-light: #ccfbf1;
            --text-dark: #0f172a;
            --text-muted: #475569;
            --bg-light: #f8fafc;
            --border-color: #cbd5e1;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: var(--text-dark);
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Container Page ── */
        .page {
            max-width: 960px;
            margin: 30px auto;
            padding: 40px;
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        /* Watermark Background */
        #watermark {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            width: 80%;
            max-width: 600px;
            opacity: 0.03;
            pointer-events: none;
        }

        /* ── Header ── */
        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .company-details h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.02em;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .company-details p {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 2px;
        }

        .company-details .highlight {
            font-weight: 600;
            color: var(--accent);
        }

        .voucher-badge-container {
            text-align: right;
        }

        .logo-img {
            max-height: 55px;
            margin-bottom: 12px;
            object-fit: contain;
        }

        .receipt-badge {
            background-color: var(--primary);
            color: #ffffff;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 6px;
            letter-spacing: 0.05em;
            display: inline-block;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* ── Meta Section ── */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 30px;
        }

        .card {
            background: var(--bg-light);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
        }

        .card-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--accent);
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
        }

        .meta-line {
            display: flex;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .meta-line:last-child {
            margin-bottom: 0;
        }

        .meta-label {
            font-weight: 600;
            color: var(--text-muted);
            width: 100px;
            flex-shrink: 0;
        }

        .meta-value {
            color: var(--text-dark);
            font-weight: 500;
            word-break: break-word;
        }

        /* ── Receipt Details Table ── */
        .details-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .erp-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .erp-table th {
            background-color: var(--primary);
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
            padding: 12px 14px;
            text-align: left;
        }

        .erp-table td {
            padding: 12px 14px;
            font-size: 13px;
            border-bottom: 1px solid #e2e8f0;
            color: var(--text-dark);
        }

        .erp-table tbody tr:nth-child(even) {
            background-color: var(--bg-light);
        }

        .erp-table td.amount-col {
            text-align: right;
            font-family: monospace;
            font-size: 14px;
            font-weight: 600;
        }

        .erp-table th.amount-col {
            text-align: right;
        }

        .erp-table .total-row td {
            font-weight: 700;
            background-color: #f1f5f9;
            border-top: 2px solid var(--primary);
            border-bottom: 2px solid var(--primary);
            color: var(--primary);
        }

        /* ── Summary & Words ── */
        .bottom-section {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 24px;
            margin-bottom: 35px;
            align-items: start;
        }

        .words-card {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .words-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .words-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--accent);
            font-style: italic;
            line-height: 1.4;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 500;
            border-bottom: 1px dashed #e2e8f0;
        }

        .summary-table tr:last-child td {
            border-bottom: none;
        }

        .summary-table td.val {
            text-align: right;
            font-weight: 700;
            font-family: monospace;
            font-size: 14px;
        }

        .summary-table .highlight td {
            background-color: var(--accent-light);
            color: var(--accent);
            border-radius: 4px;
            font-weight: 700;
        }

        /* ── Signature Section ── */
        .signature-section {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 50px;
            padding-top: 20px;
        }

        .sig-box {
            text-align: center;
        }

        .sig-line {
            border-top: 1px dashed var(--border-color);
            margin-bottom: 8px;
            width: 85%;
            margin-left: auto;
            margin-right: auto;
        }

        .sig-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        /* ── Footer Info ── */
        .footer-info {
            border-top: 1px solid #e2e8f0;
            padding-top: 14px;
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: var(--text-muted);
        }

        /* ── Print Styling ── */
        @media print {
            body {
                background-color: #ffffff;
            }

            .page {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border: none;
                border-radius: 0;
            }

            header {
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>
    @php
        $senderName = '-';
        $senderHead = '-';
        $senderCode = '-';
        $senderType = 'Party';

        if (is_numeric($voucher->type)) {
            $senderName = $party->name ?? '-';
            $senderHead = $party->head_name ?? 'Account Head';
            $senderCode = $party->phone ?? '-';
            $senderType = 'Account Head';
        } elseif ($voucher->type === 'vendor') {
            $senderName = $party->name ?? '-';
            $senderHead = 'Vendor Accounts';
            $senderCode = $party->phone ?? '-';
            $senderType = 'Vendor';
        } elseif (in_array($voucher->type, ['customer', 'walkin'])) {
            $senderName = $party->customer_name ?? '-';
            $senderHead = 'Customer Accounts';
            $senderCode = $party->mobile ?? '-';
            $senderType = $voucher->type === 'walkin' ? 'Walk-in Customer' : 'Customer';
        }

        $receiverName = '-';
        $receiverHead = '-';
        $receiverCode = '-';

        if (count($rows) === 1) {
            $receiverName = $rows[0]['account_name'] ?? '-';
            $receiverHead = $rows[0]['account_head'] ?? '-';
            $receiverCode = $rows[0]['account_code'] ?? '-';
        } elseif (count($rows) > 1) {
            $receiverName = 'Multiple Accounts';
            $receiverHead = 'Mixed Heads';
            $receiverCode = 'Mixed Codes';
        }
    @endphp

    <div class="page">
        <!-- Watermark Background -->
        <img id="watermark" src="{{ asset('amt-watermark.png') }}" alt="AMT watermark" onerror="this.style.display='none'">

        <!-- Header -->
        <header>
            <div class="company-details">
                <h1>{{ $branch->name ?? 'AMIT SONS' }}</h1>
                <p>{{ $branch->address ?? 'Main Branch, Karachi' }}</p>
                <p>Mobile / Whatsapp: <span class="highlight">{{ $branch->number ?? 'N/A' }}</span></p>
            </div>
            <div class="voucher-badge-container">
                <img src="{{ asset('amt-logo.png') }}" alt="Logo" class="logo-img" onerror="this.style.display='none'">
                <div>
                    <span class="receipt-badge">RECEIPT VOUCHER</span>
                </div>
            </div>
        </header>

        <!-- Meta Grid -->
        <div class="meta-grid">
            <!-- Sender Details Card -->
            <div class="card">
                <div class="card-title">Sender / Received From</div>
                
                <div class="meta-line">
                    <span class="meta-label">Name:</span>
                    <span class="meta-value" style="font-weight: 700; color: var(--accent);">{{ $senderName }}</span>
                </div>
                
                @if(is_numeric($voucher->type))
                    <div class="meta-line">
                        <span class="meta-label">Account Head:</span>
                        <span class="meta-value">{{ $senderHead }}</span>
                    </div>
                    <div class="meta-line">
                        <span class="meta-label">Account Code:</span>
                        <span class="meta-value" style="font-family: monospace;">{{ $senderCode }}</span>
                    </div>
                @else
                    <div class="meta-line">
                        <span class="meta-label">Type:</span>
                        <span class="meta-value" style="text-transform: capitalize; font-weight: 600;">{{ $senderType }}</span>
                    </div>
                    <div class="meta-line">
                        <span class="meta-label">Phone:</span>
                        <span class="meta-value">{{ $senderCode }}</span>
                    </div>
                @endif
            </div>

            <!-- Receiver Details Card -->
            <div class="card">
                <div class="card-title">Receiver / Deposited To</div>
                
                <div class="meta-line">
                    <span class="meta-label">Name:</span>
                    <span class="meta-value" style="font-weight: 700; color: var(--accent);">{{ $receiverName }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Account Head:</span>
                    <span class="meta-value">{{ $receiverHead }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Account Code:</span>
                    <span class="meta-value" style="font-family: monospace;">{{ $receiverCode }}</span>
                </div>
            </div>

            <!-- Voucher Details Card -->
            <div class="card">
                <div class="card-title">Voucher Reference</div>
                <div class="meta-line">
                    <span class="meta-label">Voucher No:</span>
                    <span class="meta-value" style="font-weight: 800; color: var(--accent);">{{ $voucher->rvid }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Voucher Date:</span>
                    <span class="meta-value">{{ \Carbon\Carbon::parse($voucher->receipt_date)->format('d-M-Y') }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Payment Mode:</span>
                    <span class="meta-value" style="font-weight: 600;">Cash/Bank</span>
                </div>
            </div>
        </div>

        <!-- Receipt Details Table -->
        <div class="details-section">
            <h3 class="section-title">Itemized Receipt Breakdowns</h3>
            <table class="erp-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">S.No</th>
                        <th style="width: 25%;">Account / Code</th>
                        <th style="width: 15%;">Reference</th>
                        <th style="width: 37%;">Narration / Remarks</th>
                        <th style="width: 15%; text-align: right;">Amount (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $key => $row)
                    @php
                        $rowNarration = $row['narration'] ?? null;
                        if (empty($rowNarration) || $rowNarration === 'N/A') {
                            $rowNarration = $voucher->remarks ?? null;
                        }
                        if (empty($rowNarration)) {
                            $rowNarration = "Receipt Voucher — Received from " . $senderName;
                        }
                    @endphp
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $row['account_name'] ?? '-' }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $row['account_head'] ?? '' }} ({{ $row['account_code'] ?? '' }})</div>
                        </td>
                        <td><span style="font-family: monospace; font-size: 12px;">{{ $row['reference'] ?? '—' }}</span></td>
                        <td>{{ $rowNarration }}</td>
                        <td class="amount-col">{{ number_format($row['amount'], 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="4" style="text-align: right;">Total Amount Received:</td>
                        <td class="amount-col">PKR {{ number_format($voucher->total_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Bottom Grid (Words + Financial Summary) -->
        <div class="bottom-section">
            <!-- Words Card -->
            <div class="card words-card">
                <div class="words-title">Amount in words</div>
                <div class="words-value"><span id="amountInWords">{{ $voucher->total_amount }}</span> Only</div>
            </div>

            <!-- Financial Summary Box -->
            <div class="card" style="padding: 10px 14px;">
                @php
                    $amountPayable = $previousBalance - $voucher->total_amount;
                @endphp
                <table class="summary-table">
                    <tr>
                        <td>Previous Outstanding:</td>
                        <td class="val">PKR {{ number_format($previousBalance, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Amount Received (-):</td>
                        <td class="val" style="color: var(--accent);">PKR {{ number_format($voucher->total_amount, 2) }}</td>
                    </tr>
                    <tr class="highlight">
                        <td>Remaining Balance:</td>
                        <td class="val">PKR {{ number_format($amountPayable, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Signature Lines -->
        <div class="signature-section">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Prepared By</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Checked By</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Approved By</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Received / depositor</div>
            </div>
        </div>

        <!-- Footer Details -->
        <div class="footer-info">
            <div>
                System Logged: {{ now()->format('d-M-Y H:i:s') }}
            </div>
        </div>
    </div>

    <!-- Number to Words Converter Script -->
    <script>
        function numberToWords(num) {
            const a = [
                '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
                'Seventeen', 'Eighteen', 'Nineteen'
            ];
            const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            if ((num = num.toString()).length > 9) return 'Overflow';
            let n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
            if (!n) return;
            let str = '';
            str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + ' Crore ' : '';
            str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + ' Lakh ' : '';
            str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + ' Thousand ' : '';
            str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + ' Hundred ' : '';
            str += (n[5] != 0) ? ((str != '') ? 'and ' : '') +
                (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) + ' ' : '';
            return str.trim();
        }

        document.addEventListener("DOMContentLoaded", function() {
            let amountElement = document.getElementById("amountInWords");
            let amountVal = parseInt(amountElement.innerText);
            if (!isNaN(amountVal)) {
                amountElement.innerText = numberToWords(amountVal);
            }
        });
    </script>
</body>

</html>
