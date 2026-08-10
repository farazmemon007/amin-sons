<!DOCTYPE html>
<html>
<head>
    <title>Branch Ledger Report - {{ $currentBranch->name ?? 'Branch #' . $currentBranch->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 2px; color: #2C3E50; }
        .header p { margin: 0; font-size: 12px; color: #7F8C8D; }
        
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background-color: #fcfcfc; }
        .summary-table td, .summary-table th { padding: 8px; border: 1px solid #ddd; }
        .summary-table th { background-color: #f2f2f2; text-align: left; font-weight: bold; width: 25%; }
        
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        .table th { background-color: #34495E; color: white; font-weight: bold; font-size: 10px; }
        .table tr:nth-child(even) { background-color: #f9f9f9; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        
        .badge { padding: 3px 6px; font-size: 9px; font-weight: bold; border-radius: 3px; color: white; display: inline-block; }
        .badge-danger { background-color: #E74C3C; }
        .badge-success { background-color: #2ECC71; }
        
        .text-danger { color: #C0392B; font-weight: bold; }
        .text-success { color: #27AE60; font-weight: bold; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #7F8C8D; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Branch Ledger Report</h2>
        <p>{{ $currentBranch->name ?? 'Branch #' . $currentBranch->id }}</p>
        <p style="font-size: 10px; margin-top: 5px;">Generated on: {{ now()->format('M d, Y H:i A') }}</p>
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
            <th>Total Credits</th>
            <td class="text-success">{{ number_format($totalCredit, 2) }}</td>
            <th>Total Debits</th>
            <td class="text-danger">{{ number_format($totalDebit, 2) }}</td>
        </tr>
    </table>

    <h4 style="margin: 15px 0 5px 0; color: #2C3E50;">Transaction History</h4>
    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 40%;">Description</th>
                <th style="width: 15%;">Related Branch</th>
                <th style="width: 10%;">Type</th>
                <th style="width: 20%;" class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                    <td>{{ $transaction->display_description }}</td>
                    <td>{{ $transaction->relatedBranch->name ?? 'N/A' }}</td>
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
                    <td colspan="5" style="text-align: center; padding: 15px; color: #7F8C8D;">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        © {{ date('Y') }} Ameen & Sons. All rights reserved.
    </div>
</body>
</html>
