<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Inter-Branch Voucher #VCH-{{ $voucher->id }} - Ameen & Sons</title>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        :root {
            --primary: #1e3a5f;
            --primary-dark: #0f1f38;
            --primary-light: #2c5282;
            --accent: #0d9f6e;
            --accent-light: #ecfdf5;
            --gold: #c8973a;
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

        /* ── Action Toolbar (Hidden during Print) ── */
        .print-toolbar {
            max-width: 960px;
            margin: 20px auto 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(30, 58, 95, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0f1f38 0%, #1e3a5f 100%);
            color: #ffffff;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        /* ── Container Page ── */
        .page {
            max-width: 960px;
            margin: 15px auto 40px auto;
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
            width: 70%;
            max-width: 500px;
            opacity: 0.03;
            pointer-events: none;
        }

        /* ── Header ── */
        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .company-details h1 {
            font-size: 26px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.02em;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .company-tagline {
            font-size: 11px;
            font-weight: 700;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 6px;
        }

        .company-details p {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-bottom: 2px;
        }

        .voucher-badge-container {
            text-align: right;
        }

        .voucher-badge {
            background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
            color: #ffffff;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 6px;
            letter-spacing: 0.05em;
            display: inline-block;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
        }

        .voucher-id-sub {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* ── Meta Grid ── */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 25px;
        }

        .card {
            background: var(--bg-light);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
        }

        .card-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--primary);
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .meta-line {
            display: flex;
            margin-bottom: 6px;
            font-size: 12.5px;
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
            font-weight: 600;
            word-break: break-word;
        }

        /* ── ERP Details Table ── */
        .details-section {
            margin-bottom: 25px;
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
            padding: 11px 14px;
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

        .erp-table td.amount-col, .erp-table th.amount-col {
            text-align: right;
        }

        .erp-table .total-row td {
            font-weight: 800;
            background-color: #f1f5f9;
            border-top: 2px solid var(--primary);
            border-bottom: 2px solid var(--primary);
            color: var(--primary);
            font-size: 14px;
        }

        /* ── Summary & Words ── */
        .bottom-section {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 20px;
            margin-bottom: 35px;
            align-items: stretch;
        }

        .words-card {
            background: var(--bg-light);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
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
            font-size: 13.5px;
            font-weight: 700;
            color: var(--primary);
            font-style: italic;
            line-height: 1.4;
        }

        .summary-box {
            background: var(--accent-light);
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 14px 16px;
            text-align: right;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .summary-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #065f46;
            margin-bottom: 4px;
        }

        .summary-amount {
            font-size: 22px;
            font-weight: 800;
            color: var(--accent);
            font-family: monospace;
        }

        /* ── Signatures ── */
        .signature-section {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 50px;
            padding-top: 10px;
        }

        .sig-block {
            text-align: center;
        }

        .sig-line {
            border-top: 1.5px dashed #94a3b8;
            margin-bottom: 6px;
            padding-top: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
        }

        .sig-sub {
            font-size: 10px;
            color: #94a3b8;
        }

        /* ── Print Media Query ── */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .print-toolbar {
                display: none !important;
            }

            .page {
                margin: 0 auto !important;
                padding: 20px !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>

<body>

    <!-- Print Action Bar -->
    <div class="print-toolbar">
        <div>
            <a href="{{ route('inter_branch_vouchers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Vouchers List
            </a>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Voucher
            </button>
        </div>
    </div>

    <!-- Printable Page -->
    <div class="page">
        <!-- Watermark -->
        <svg id="watermark" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" font-size="28" font-weight="900" fill="currentColor">AMEEN & SONS</text>
        </svg>

        <!-- Header -->
        <header>
            <div class="company-details">
                <h1>Ameen & Sons</h1>
                <div class="company-tagline">Corporate ERP • Inter-Branch Financial Management</div>
                <p><i class="fas fa-map-marker-alt" style="color: var(--gold); width: 14px;"></i> Central Corporate Headquarters • Multan, Pakistan</p>
                <p><i class="fas fa-phone" style="color: var(--gold); width: 14px;"></i> +92 61 111-222-333 • <i class="fas fa-envelope" style="color: var(--gold); width: 14px;"></i> accounts@ameensons.com</p>
            </div>
            <div class="voucher-badge-container">
                <div class="voucher-badge">
                    @if ($voucher->type === 'payment')
                        <i class="fas fa-arrow-up mr-1"></i> Inter-Branch Payment
                    @else
                        <i class="fas fa-arrow-down mr-1"></i> Inter-Branch Receipt
                    @endif
                </div>
                <div class="voucher-id-sub">
                    Voucher ID: <strong style="color: var(--primary);">#VCH-{{ $voucher->id }}</strong>
                </div>
            </div>
        </header>

        <!-- Meta Section (3 Cards) -->
        <div class="meta-grid">
            <!-- 1. Sending Branch Card -->
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-paper-plane text-primary"></i> 1. Sending Entity (Pay From)
                </div>
                <div class="meta-line">
                    <span class="meta-label">Branch:</span>
                    <span class="meta-value">🏪 {{ $voucher->fromBranch->name ?? $voucher->fromBranch->branch_name ?? 'Branch #' . $voucher->from_branch_id }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Account:</span>
                    <span class="meta-value">{{ $voucher->fromAccount->title ?? 'Main Account' }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Head / Code:</span>
                    <span class="meta-value">{{ $voucher->fromAccount->head->name ?? 'General' }} <small class="text-muted">({{ $voucher->fromAccount->account_code ?? '-' }})</small></span>
                </div>
            </div>

            <!-- 2. Receiving Branch Card -->
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-hand-holding-usd text-success"></i> 2. Receiving Entity (Pay To)
                </div>
                <div class="meta-line">
                    <span class="meta-label">Branch:</span>
                    <span class="meta-value">🏬 {{ $voucher->toBranch->name ?? $voucher->toBranch->branch_name ?? 'Branch #' . $voucher->to_branch_id }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Account:</span>
                    <span class="meta-value">{{ $voucher->toAccount->title ?? 'Main Account' }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Head / Code:</span>
                    <span class="meta-value">{{ $voucher->toAccount->head->name ?? 'General' }} <small class="text-muted">({{ $voucher->toAccount->account_code ?? '-' }})</small></span>
                </div>
            </div>

            <!-- 3. Voucher Info Card -->
            <div class="card">
                <div class="card-title">
                    <i class="fas fa-info-circle text-info"></i> 3. Voucher Details
                </div>
                <div class="meta-line">
                    <span class="meta-label">Issue Date:</span>
                    <span class="meta-value">{{ $voucher->created_at ? $voucher->created_at->format('d M, Y') : now()->format('d M, Y') }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Method:</span>
                    <span class="meta-value" style="text-transform: uppercase;">{{ $voucher->method ?? 'Cash / Transfer' }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Created By:</span>
                    <span class="meta-value">{{ $voucher->createdBy->name ?? 'Administrator' }}</span>
                </div>
            </div>
        </div>

        <!-- Table Details -->
        <div class="details-section">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 45%;">Transaction Particulars / Narration</th>
                        <th style="width: 18%;">Payment Method</th>
                        <th style="width: 14%;">Reference #</th>
                        <th style="width: 18%; text-align: right;">Amount (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            <strong style="color: var(--primary);">Inter-Branch Fund Transfer</strong>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                From: <strong>{{ $voucher->fromBranch->name ?? 'Branch #' . $voucher->from_branch_id }}</strong> ({{ $voucher->fromAccount->title ?? 'Account' }})
                                &rarr;
                                To: <strong>{{ $voucher->toBranch->name ?? 'Branch #' . $voucher->to_branch_id }}</strong> ({{ $voucher->toAccount->title ?? 'Account' }})
                            </div>
                            @if ($voucher->remarks)
                                <div style="font-size: 12px; color: #1e3a5f; margin-top: 4px; font-style: italic;">
                                    <i class="fas fa-comment-dots me-1"></i> Remarks: {{ $voucher->remarks }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span style="font-weight: 600; text-transform: uppercase;">{{ $voucher->method ?? 'Cash' }}</span>
                        </td>
                        <td>
                            <span style="font-family: monospace; font-weight: 600;">{{ $voucher->reference ?? '-' }}</span>
                        </td>
                        <td class="amount-col">
                            <strong>{{ number_format($voucher->amount, 2) }}</strong>
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="4" style="text-align: right; text-transform: uppercase;">
                            Total Transfer Amount:
                        </td>
                        <td class="amount-col" style="color: var(--primary);">
                            PKR {{ number_format($voucher->amount, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary & Amount in Words -->
        <div class="bottom-section">
            <div class="words-card">
                <div class="words-title">Amount in Words:</div>
                <div class="words-value" id="amountInWords">
                    Calculating amount in words...
                </div>
            </div>
            <div class="summary-box">
                <div class="summary-label">Net Payable / Received</div>
                <div class="summary-amount">
                    PKR {{ number_format($voucher->amount, 2) }}
                </div>
            </div>
        </div>

        <!-- Signatures Section -->
        <div class="signature-section">
            <div class="sig-block">
                <div class="sig-line">Prepared By</div>
                <div class="sig-sub">{{ $voucher->createdBy->name ?? 'Operator' }}</div>
            </div>
            <div class="sig-block">
                <div class="sig-line">Sending Branch Mgr</div>
                <div class="sig-sub">Stamp & Signature</div>
            </div>
            <div class="sig-block">
                <div class="sig-line">Receiving Branch Mgr</div>
                <div class="sig-sub">Stamp & Signature</div>
            </div>
            <div class="sig-block">
                <div class="sig-line">Audited & Approved</div>
                <div class="sig-sub">Finance Directorate</div>
            </div>
        </div>
    </div>

    <!-- Amount in Words Script -->
    <script>
        function numberToWords(num) {
            num = Math.floor(Math.abs(num));
            if (num === 0) return 'Zero Rupees Only';

            const a = ['', 'One ', 'Two ', 'Three ', 'Four ', 'Five ', 'Six ', 'Seven ', 'Eight ', 'Nine ', 'Ten ', 'Eleven ', 'Twelve ', 'Thirteen ', 'Fourteen ', 'Fifteen ', 'Sixteen ', 'Seventeen ', 'Eighteen ', 'Nineteen '];
            const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            function inWords(n) {
                if (n === 0) return '';
                let str = '';
                if (n >= 10000000) {
                    str += inWords(Math.floor(n / 10000000)) + 'Crore ';
                    n %= 10000000;
                }
                if (n >= 100000) {
                    str += inWords(Math.floor(n / 100000)) + 'Lakh ';
                    n %= 100000;
                }
                if (n >= 1000) {
                    str += inWords(Math.floor(n / 1000)) + 'Thousand ';
                    n %= 1000;
                }
                if (n >= 100) {
                    str += inWords(Math.floor(n / 100)) + 'Hundred ';
                    n %= 100;
                }
                if (n > 0) {
                    if (n < 20) {
                        str += a[n];
                    } else {
                        str += b[Math.floor(n / 10)] + (n % 10 !== 0 ? ' ' + a[n % 10] : ' ');
                    }
                }
                return str;
            }

            return inWords(num).trim() + ' Rupees Only';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const amountVal = {{ (float)$voucher->amount }};
            const el = document.getElementById('amountInWords');
            if (el) {
                el.innerText = numberToWords(amountVal);
            }
        });
    </script>
</body>

</html>