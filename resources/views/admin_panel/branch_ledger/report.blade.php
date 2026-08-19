<!DOCTYPE html>
<html>
<head>
    <title>Branch Ledger Report - {{ $currentBranch->name ?? $currentBranch->branch_name ?? 'Branch #' . $currentBranch->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #1e3a5f; padding-bottom: 10px; }
        .header h2 { margin: 0; padding: 2px; color: #0f1f38; text-transform: uppercase; letter-spacing: 0.5px; }
        .header p { margin: 2px 0; font-size: 12px; color: #475569; }
        .header .meta { font-size: 10px; color: #64748b; margin-top: 4px; }
        
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; background-color: #fcfcfc; }
        .summary-table td, .summary-table th { padding: 7px 10px; border: 1px solid #cbd5e1; font-size: 11px; }
        .summary-table th { background-color: #f1f5f9; text-align: left; font-weight: bold; width: 25%; color: #334155; }
        
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; font-size: 10.5px; }
        .table th { background-color: #0f1f38; color: white; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .table tr:nth-child(even) { background-color: #f8fafc; }
        .table tfoot tr { background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #0f1f38; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        
        .badge { padding: 2px 5px; font-size: 8.5px; font-weight: bold; border-radius: 3px; color: white; display: inline-block; }
        .badge-danger { background-color: #dc2626; }
        .badge-success { background-color: #16a34a; }
        
        .text-danger { color: #dc2626; font-weight: bold; }
        .text-success { color: #16a34a; font-weight: bold; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #64748b; border-top: 1px solid #cbd5e1; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Branch Ledger Statement</h2>
        <p><strong>{{ $currentBranch->name ?? $currentBranch->branch_name ?? 'Branch #' . $currentBranch->id }}</strong></p>
        <div class="meta">
            Generated on: {{ now()->format('d-M-Y H:i A') }}
            @if(request('from_date') || request('to_date'))
                &nbsp;|&nbsp; Period: {{ request('from_date', 'Start') }} to {{ request('to_date', 'Current') }}
            @endif
            @if(request('type'))
                &nbsp;|&nbsp; Type: {{ ucfirst(request('type')) }}
            @endif
        </div>
    </div>

    <table class="summary-table">
        <tr>
            <th>Current Balance</th>
            <td class="{{ $balance < 0 ? 'text-danger' : ($balance > 0 ? 'text-success' : '') }}">
                {{ $balance < 0 ? '-' : ($balance > 0 ? '+' : '') }}{{ number_format(abs($balance), 2) }}
                <span style="font-size: 9px; font-weight: normal; color: #555;">
                    ({{ $balance < 0 ? "We owe" : ($balance > 0 ? "We're owed" : "Balanced") }})
                </span>
            </td>
            <th>Total Transactions</th>
            <td>{{ count($transactions) }} entries</td>
        </tr>
        <tr>
            <th>Total Credits (Receivable)</th>
            <td class="text-success">+{{ number_format($totalCredit, 2) }}</td>
            <th>Total Debits (Payable)</th>
            <td class="text-danger">-{{ number_format($totalDebit, 2) }}</td>
        </tr>
    </table>

    <h4 style="margin: 12px 0 6px 0; color: #0f1f38;">Transaction History</h4>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 14%;">Date</th>
                <th style="width: 32%;">Description</th>
                <th style="width: 16%;">Related Branch</th>
                <th style="width: 13%;">Reference</th>
                <th style="width: 9%;" class="text-center">Type</th>
                <th style="width: 16%;" class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td style="white-space: nowrap;">{{ $transaction->created_at->format('d-M-Y H:i') }}</td>
                    <td>{{ $transaction->display_description }}</td>
                    <td>{{ $transaction->relatedBranch->name ?? '—' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $transaction->reference_type)) }} #{{ $transaction->reference_id }}</td>
                    <td class="text-center">
                        <span class="badge {{ $transaction->type === 'debit' ? 'badge-danger' : 'badge-success' }}">
                            {{ ucfirst($transaction->type) }}
                        </span>
                    </td>
                    <td class="text-end {{ $transaction->type === 'debit' ? 'text-danger' : 'text-success' }}">
                        {{ $transaction->type === 'debit' ? '-' : '+' }}{{ number_format($transaction->display_amount, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 15px; color: #7F8C8D;">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end">Summary Net Balance:</td>
                <td class="text-center">
                    <span style="color:#16a34a;">+{{ number_format($totalCredit, 2) }}</span> / 
                    <span style="color:#dc2626;">-{{ number_format($totalDebit, 2) }}</span>
                </td>
                <td class="text-end {{ $balance < 0 ? 'text-danger' : ($balance > 0 ? 'text-success' : '') }}">
                    {{ $balance > 0 ? '+' : '' }}{{ number_format($balance, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        © {{ date('Y') }} Ameen & Sons Corporate ERP &mdash; Branch Financial Statement
    </div>
</body>
</html>
