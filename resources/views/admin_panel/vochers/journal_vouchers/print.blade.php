<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Journal Voucher - {{ $jv->jvid }}</title>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:       #3730a3;
            --primary-light: #4f46e5;
            --accent:        #7c3aed;
            --accent-light:  #ede9fe;
            --debit-bg:      #fffbeb;
            --debit-border:  #fde68a;
            --debit-color:   #92400e;
            --credit-bg:     #ecfdf5;
            --credit-border: #6ee7b7;
            --credit-color:  #065f46;
            --text-dark:     #0f172a;
            --text-muted:    #475569;
            --bg-light:      #f8fafc;
            --border-color:  #e2e8f0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: var(--text-dark);
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Container ── */
        .page {
            max-width: 960px;
            margin: 30px auto;
            padding: 40px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-radius: 14px;
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        /* Top color bar */
        .page::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 5px;
            background: linear-gradient(90deg, #3730a3, #7c3aed, #8b5cf6);
        }

        /* Watermark */
        #watermark {
            position: absolute;
            left: 50%; top: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            width: 80%; max-width: 600px;
            opacity: 0.025;
            pointer-events: none;
        }

        /* ── Header ── */
        header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
            padding-top: 12px;
        }

        .company-details h1 {
            font-size: 26px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.02em;
            margin-bottom: 4px;
        }
        .company-details p {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 2px;
        }
        .company-details .highlight { font-weight: 600; color: var(--accent); }

        .voucher-badge-container { text-align: right; }
        .logo-img { max-height: 55px; margin-bottom: 12px; object-fit: contain; }

        .jv-badge {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #ffffff;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 800;
            border-radius: 8px;
            letter-spacing: 0.08em;
            display: inline-block;
            box-shadow: 0 4px 12px rgba(109,40,217,0.3);
        }

        /* ── Double Entry Diagram ── */
        .jv-flow {
            display: flex;
            align-items: stretch;
            gap: 0;
            margin-bottom: 28px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .jv-flow-panel {
            flex: 1;
            padding: 18px 20px;
        }

        .jv-flow-panel.credit {
            background: var(--credit-bg);
            border-right: 1px solid var(--credit-border);
        }

        .jv-flow-panel.debit {
            background: var(--debit-bg);
            border-left: 1px solid var(--debit-border);
        }

        .jv-flow-center {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 14px 20px;
            background: var(--accent-light);
            border-left: 1px solid #c4b5fd;
            border-right: 1px solid #c4b5fd;
            min-width: 140px;
        }

        .flow-panel-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .flow-panel-title.credit { color: var(--credit-color); }
        .flow-panel-title.debit  { color: var(--debit-color); }

        .flow-party-name {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .flow-party-name.credit { color: var(--credit-color); }
        .flow-party-name.debit  { color: var(--debit-color); }

        .flow-party-type {
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
            padding: 2px 8px;
            border-radius: 5px;
        }
        .flow-party-type.credit { background: #a7f3d0; color: var(--credit-color); }
        .flow-party-type.debit  { background: #fde68a; color: var(--debit-color); }

        .flow-meta { font-size: 12px; color: var(--text-muted); margin-top: 8px; }

        .flow-amount-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--accent);
            margin-bottom: 4px;
        }
        .flow-amount-value {
            font-size: 22px;
            font-weight: 800;
            color: var(--primary);
            font-family: monospace;
        }
        .flow-arrows {
            font-size: 22px;
            color: var(--accent);
            margin: 6px 0;
            letter-spacing: 2px;
        }

        /* ── Meta Grid ── */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .card {
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 16px;
        }

        .card-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--accent);
            margin-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 6px;
        }

        .meta-line {
            display: flex;
            margin-bottom: 8px;
            font-size: 12.5px;
        }
        .meta-line:last-child { margin-bottom: 0; }
        .meta-label {
            font-weight: 600;
            color: var(--text-muted);
            width: 110px;
            flex-shrink: 0;
        }
        .meta-value {
            color: var(--text-dark);
            font-weight: 500;
            word-break: break-word;
        }

        /* ── Ledger Effect Table ── */
        .section-title {
            font-size: 13px;
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
            background: var(--border-color);
        }

        .erp-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            margin-bottom: 28px;
        }
        .erp-table th {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.06em;
            padding: 10px 14px;
            text-align: left;
        }
        .erp-table td {
            padding: 12px 14px;
            font-size: 13px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-dark);
        }
        .erp-table tbody tr:last-child td { border-bottom: none; }
        .erp-table .debit-row td  { background: var(--debit-bg); }
        .erp-table .credit-row td { background: var(--credit-bg); }

        .entry-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .entry-badge.debit  { background: var(--debit-border); color: var(--debit-color); }
        .entry-badge.credit { background: var(--credit-border); color: var(--credit-color); }

        .amount-col { text-align: right; font-family: monospace; font-weight: 700; font-size: 14px; }

        /* ── Amount in Words + Summary ── */
        .bottom-section {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 24px;
            margin-bottom: 35px;
            align-items: start;
        }
        .words-card { height: 100%; display: flex; flex-direction: column; justify-content: center; }
        .words-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); margin-bottom: 6px; }
        .words-value { font-size: 14px; font-weight: 600; color: var(--accent); font-style: italic; line-height: 1.4; }

        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 8px 12px; font-size: 13px; font-weight: 500; border-bottom: 1px dashed var(--border-color); }
        .summary-table tr:last-child td { border-bottom: none; }
        .summary-table td.val { text-align: right; font-weight: 800; font-family: monospace; font-size: 14px; }
        .summary-table .highlight td { background: var(--accent-light); color: var(--accent); border-radius: 4px; font-weight: 800; }

        /* ── Signature Section ── */
        .signature-section {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 50px;
            padding-top: 20px;
        }
        .sig-box { text-align: center; }
        .sig-line { border-top: 1px dashed var(--border-color); margin-bottom: 8px; width: 85%; margin-left: auto; margin-right: auto; }
        .sig-label { font-size: 10px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; }

        /* ── Footer ── */
        .footer-info {
            border-top: 1px solid var(--border-color);
            padding-top: 14px;
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            font-size: 10.5px;
            color: var(--text-muted);
        }

        /* ── Print Button ── */
        .print-btn {
            position: fixed;
            bottom: 24px; right: 24px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff; border: none;
            padding: 14px 28px;
            border-radius: 50px;
            font-size: 14px; font-weight: 700;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(109,40,217,0.4);
            display: flex; align-items: center; gap: 8px;
            transition: all 0.2s;
            z-index: 9999;
        }
        .print-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(109,40,217,0.5); }

        /* ── Print Media ── */
        @media print {
            body { background-color: #ffffff; }
            .page { width: 100%; margin: 0; padding: 30px; box-shadow: none; border: none; border-radius: 0; }
            .print-btn { display: none !important; }
        }
    </style>
</head>

<body>
    <div class="page">
        <!-- Watermark -->
        <img id="watermark" src="{{ asset('amt-watermark.png') }}" alt="" onerror="this.style.display='none'">

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
                    <span class="jv-badge">📊 JOURNAL VOUCHER</span>
                </div>
            </div>
        </header>

        <!-- Double Entry Flow Diagram -->
        <div class="jv-flow">
            <div class="jv-flow-panel credit">
                <div class="flow-panel-title credit">✅ Credit Side — Received From</div>
                <div class="flow-party-name credit">{{ $creditPartyName }}</div>
                <span class="flow-party-type credit">{{ ucfirst($jv->credit_party_type) }}</span>
                @if($creditPartyPhone && $creditPartyPhone !== '—')
                    <div class="flow-meta">📞 {{ $creditPartyPhone }}</div>
                @endif
                <div class="flow-meta" style="margin-top: 6px;">Outstanding reduced by JV</div>
            </div>

            <div class="jv-flow-center">
                <div class="flow-amount-label">Transfer Amount</div>
                <div class="flow-amount-value">{{ number_format($jv->amount, 0) }}</div>
                <div class="flow-arrows">→</div>
                <div style="font-size: 10px; color: #7c3aed; font-weight: 600;">PKR</div>
            </div>

            <div class="jv-flow-panel debit">
                <div class="flow-panel-title debit">📊 Debit Side — Paid To</div>
                <div class="flow-party-name debit">{{ $debitPartyName }}</div>
                <span class="flow-party-type debit">{{ ucfirst($jv->debit_party_type) }}</span>
                @if($debitPartyPhone && $debitPartyPhone !== '—')
                    <div class="flow-meta">📞 {{ $debitPartyPhone }}</div>
                @endif
                <div class="flow-meta" style="margin-top: 6px;">Payable reduced by JV</div>
            </div>
        </div>

        <!-- Meta Grid -->
        <div class="meta-grid">
            <!-- Credit Party Card -->
            <div class="card">
                <div class="card-title">Credit Party (From)</div>
                <div class="meta-line">
                    <span class="meta-label">Name:</span>
                    <span class="meta-value" style="font-weight: 800; color: var(--credit-color);">{{ $creditPartyName }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Type:</span>
                    <span class="meta-value" style="text-transform: capitalize;">{{ $jv->credit_party_type }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Phone:</span>
                    <span class="meta-value">{{ $creditPartyPhone }}</span>
                </div>
            </div>

            <!-- Debit Party Card -->
            <div class="card">
                <div class="card-title">Debit Party (To)</div>
                <div class="meta-line">
                    <span class="meta-label">Name:</span>
                    <span class="meta-value" style="font-weight: 800; color: var(--debit-color);">{{ $debitPartyName }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Type:</span>
                    <span class="meta-value" style="text-transform: capitalize;">{{ $jv->debit_party_type }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Phone:</span>
                    <span class="meta-value">{{ $debitPartyPhone }}</span>
                </div>
            </div>

            <!-- Voucher Reference Card -->
            <div class="card">
                <div class="card-title">Voucher Reference</div>
                <div class="meta-line">
                    <span class="meta-label">Voucher No:</span>
                    <span class="meta-value" style="font-weight: 800; color: var(--accent); font-family: monospace;">{{ $jv->jvid }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Date:</span>
                    <span class="meta-value">{{ \Carbon\Carbon::parse($jv->voucher_date)->format('d-M-Y') }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Entry Date:</span>
                    <span class="meta-value">{{ \Carbon\Carbon::parse($jv->entry_date)->format('d-M-Y') }}</span>
                </div>
                <div class="meta-line">
                    <span class="meta-label">Status:</span>
                    <span class="meta-value" style="font-weight: 700; color: #059669;">✅ {{ ucfirst($jv->status) }}</span>
                </div>
            </div>
        </div>

        <!-- Ledger Effect Table -->
        <div>
            <h3 class="section-title">Ledger Entries — Double Entry Effect</h3>
            <table class="erp-table">
                <thead>
                    <tr>
                        <th style="width: 6%;">S.No</th>
                        <th style="width: 12%;">Entry Type</th>
                        <th style="width: 22%;">Party</th>
                        <th style="width: 15%;">Party Type</th>
                        <th style="width: 30%;">Narration</th>
                        <th style="width: 15%; text-align: right;">Amount (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Credit Row -->
                    <tr class="credit-row">
                        <td>1</td>
                        <td><span class="entry-badge credit">✅ Credit</span></td>
                        <td style="font-weight: 700; color: var(--credit-color);">{{ $creditPartyName }}</td>
                        <td style="text-transform: capitalize; color: #475569;">{{ $jv->credit_party_type }}</td>
                        <td style="color: #475569;">{{ $jv->remarks ?? "Journal Voucher — Receivable reduced" }}</td>
                        <td class="amount-col" style="color: var(--credit-color);">{{ number_format($jv->amount, 2) }}</td>
                    </tr>
                    <!-- Debit Row -->
                    <tr class="debit-row">
                        <td>2</td>
                        <td><span class="entry-badge debit">📊 Debit</span></td>
                        <td style="font-weight: 700; color: var(--debit-color);">{{ $debitPartyName }}</td>
                        <td style="text-transform: capitalize; color: #475569;">{{ $jv->debit_party_type }}</td>
                        <td style="color: #475569;">{{ $jv->remarks ?? "Journal Voucher — Payable reduced" }}</td>
                        <td class="amount-col" style="color: var(--debit-color);">{{ number_format($jv->amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Bottom Section -->
        <div class="bottom-section">
            <!-- Amount in Words -->
            <div class="card words-card">
                <div class="words-title">Amount in Words</div>
                <div class="words-value">
                    <span id="amountInWords">{{ $jv->amount }}</span> Only
                </div>
                @if($jv->remarks)
                <div style="margin-top: 12px; font-size: 12px; color: #64748b;">
                    <strong>Narration:</strong> {{ $jv->remarks }}
                </div>
                @endif
            </div>

            <!-- Financial Summary -->
            <div class="card" style="padding: 10px 14px;">
                <table class="summary-table">
                    <tr>
                        <td>Journal Voucher Amount:</td>
                        <td class="val" style="color: var(--accent);">PKR {{ number_format($jv->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Credit Entry ({{ ucfirst($jv->credit_party_type) }}):</td>
                        <td class="val" style="color: var(--credit-color);">- {{ number_format($jv->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Debit Entry ({{ ucfirst($jv->debit_party_type) }}):</td>
                        <td class="val" style="color: var(--debit-color);">- {{ number_format($jv->amount, 2) }}</td>
                    </tr>
                    <tr class="highlight">
                        <td>Both Ledgers Updated:</td>
                        <td class="val">✅ Posted</td>
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
                <div class="sig-label">Received / Party</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-info">
            <div>Voucher: {{ $jv->jvid }} | System Logged: {{ now()->format('d-M-Y H:i:s') }}</div>
            <div style="font-style: italic; color: #94a3b8;">This is a computer-generated document.</div>
        </div>
    </div>

    <!-- Print Button (hidden in print) -->
    <button class="print-btn" onclick="window.print()">
        🖨️ Print Journal Voucher
    </button>

    <!-- Number to Words Script -->
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
            if (!n) return '';
            let str = '';
            str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + ' Crore ' : '';
            str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + ' Lakh ' : '';
            str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + ' Thousand ' : '';
            str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + ' Hundred ' : '';
            str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) + ' ' : '';
            return str.trim();
        }

        document.addEventListener("DOMContentLoaded", function () {
            let el  = document.getElementById("amountInWords");
            let val = parseInt(el.innerText);
            if (!isNaN(val)) el.innerText = numberToWords(val);
        });
    </script>
</body>

</html>
