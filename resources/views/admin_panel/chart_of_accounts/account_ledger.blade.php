@extends('admin_panel.layout.app')

@section('content')
<style>
:root {
    --primary: #1e3a5f;
    --primary-dark: #0f1f38;
    --primary-light: #2c5282;
    --gold: #c8973a;
    --success: #0d9f6e;
    --danger:  #dc2626;
    --warning: #f59e0b;
    --info:    #0284c7;
    --dark:    #0f172a;
    --muted:   #64748b;
    --border:  #e2e8f0;
    --light:   #f8fafc;
}

/* ── Page Header ─────────────────────────────── */
.ledger-header {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 60%, var(--primary-light) 100%);
    color: #ffffff !important;
    padding: 24px 0;
    margin-bottom: 24px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(15, 31, 56, 0.15);
}
.ledger-title {
    font-size: 1.8rem;
    font-weight: 800;
    margin: 0;
    color: #ffffff !important;
    letter-spacing: -0.01em;
}
.back-link {
    color: rgba(255,255,255,.9) !important;
    text-decoration: none !important;
    font-size: .88rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.1);
    padding: 5px 12px;
    border-radius: 5px;
    border: 1px solid rgba(255, 255, 255, 0.18);
    transition: all 0.2s;
}
.back-link:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff !important;
}
.account-meta { display:flex; align-items:center; gap:10px; margin-top:12px; flex-wrap:wrap; }
.account-badge {
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 6px;
    padding: 5px 12px;
    font-size: .82rem;
    font-weight: 600;
    color: #ffffff !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* ── Summary Cards ───────────────────────────── */
.summary-row { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:14px; margin-bottom:22px; }
.s-card {
    background:white; border-radius:8px;
    padding:16px 18px;
    box-shadow:0 2px 8px rgba(0,0,0,.03);
    border:1px solid var(--border);
    border-left:4px solid transparent;
}
.s-card.ob  { border-left-color: #64748b; }
.s-card.dr  { border-left-color: var(--success); }
.s-card.cr  { border-left-color: var(--danger); }
.s-card.bal { border-left-color: var(--primary); background: #f0fdf4; }
.s-card-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; color:var(--muted); font-weight:700; margin-bottom:4px; }
.s-card-value { font-size:1.35rem; font-weight:800; color:var(--dark); font-family:monospace; }

/* ── Filter Bar ──────────────────────────────── */
.filter-bar {
    background:white; border-radius:8px;
    padding:14px 18px; margin-bottom:18px;
    box-shadow:0 2px 6px rgba(0,0,0,.03);
    border:1px solid var(--border);
    display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;
}
.filter-bar label { font-size:.78rem; font-weight:700; color:var(--muted); display:block; margin-bottom:4px; }
.filter-bar input[type=date] {
    border:1.5px solid var(--border); border-radius:6px;
    padding:6px 10px; font-size:.85rem; cursor:pointer;
}
.btn-filter {
    background: var(--primary); color:#ffffff !important;
    border:none; border-radius:6px;
    padding:7px 16px; font-size:.85rem; font-weight:700;
    cursor:pointer; display:inline-flex; align-items:center; gap:6px;
    transition: background .2s;
}
.btn-filter:hover { background:var(--primary-dark); }
.btn-clear {
    background:#f1f5f9; color:var(--dark);
    border:1px solid var(--border); border-radius:6px;
    padding:7px 14px; font-size:.85rem; font-weight:600;
    cursor:pointer; text-decoration:none;
    display:inline-flex; align-items:center; gap:6px;
}

/* ── Ledger Table ────────────────────────────── */
.ledger-card {
    background:white; border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,.03);
    border:1px solid var(--border);
    overflow:hidden;
}
.ledger-card-header {
    background:#f8fafc;
    padding:14px 20px; border-bottom:1px solid var(--border);
    display:flex; align-items:center; gap:10px;
}
.ledger-table { width:100%; border-collapse:collapse; margin:0; }
.ledger-table thead th {
    background:#f1f5f9; color:#475569;
    padding:11px 14px; font-size:.75rem;
    text-transform:uppercase; letter-spacing:.05em;
    font-weight:700;
    border-bottom:1.5px solid #cbd5e1;
}
.ledger-table tbody tr { border-bottom:1px solid #f1f5f9; transition:background .12s; }
.ledger-table tbody tr:hover { background:#f8fafc; }
.ledger-table td { padding:10px 14px; font-size:.88rem; vertical-align:middle; color:#1e293b; }

.entry-no {
    font-family:monospace; font-weight:700; font-size:.82rem;
    padding:2px 7px; border-radius:4px;
}
.entry-no.br { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
.entry-no.cr { background:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; }
.entry-no.jv { background:#fef3c7; color:#b45309; border:1px solid #fde68a; }
.entry-no.ob { background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; }

.voucher-tag {
    font-size:.78rem; color:var(--primary); font-weight:600;
    background:#f1f5f9; padding:2px 6px;
    border-radius:4px; font-family:monospace;
    border:1px solid #e2e8f0;
}

.amount-debit  { color:var(--success); font-weight:700; font-family:monospace; }
.amount-credit { color:var(--danger);  font-weight:700; font-family:monospace; }
.amount-zero   { color:#cbd5e1; }

.running-balance { font-weight:800; font-size:.92rem; font-family:monospace; }
.running-balance.positive { color:#047857; }
.running-balance.negative { color:#b91c1c; }

/* Totals row */
.totals-row td {
    background:var(--primary); color:#ffffff !important;
    font-weight:700; padding:12px 14px;
    font-size:.9rem;
}
.empty-state { text-align:center; padding:50px 20px; color:var(--muted); }
.empty-state i { font-size:2.2rem; opacity:.3; margin-bottom:10px; display:block; }
</style>

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="ledger-header">
        <div class="container">
            <a href="{{ route('branch.accounts', $account->branch_id ?? 1) }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Accounts
            </a>

            <div style="margin-top:14px; display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
                <div>
                    <h1 class="ledger-title" style="color: #ffffff !important;">
                        {{ $account->title }}
                    </h1>
                    <div class="account-meta">
                        <span class="account-badge">
                            <i class="fas fa-barcode" style="color: var(--gold);"></i>
                            {{ $account->account_code ?? 'N/A' }}
                        </span>
                        <span class="account-badge">
                            <i class="fas fa-layer-group" style="color: var(--gold);"></i>
                            {{ $account->head->name ?? 'General' }}
                        </span>
                        <span class="account-badge">
                            <i class="fas fa-store" style="color: var(--gold);"></i>
                            {{ $account->branch->name ?? 'Head Office' }}
                        </span>
                        <span class="account-badge" style="{{ strtolower($account->type) == 'debit' ? 'background:rgba(13,159,110,.25); border-color:#059669;' : 'background:rgba(220,38,38,.25); border-color:#b91c1c;' }}">
                            <i class="fas {{ strtolower($account->type) == 'debit' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                            {{ $account->type }} Account
                        </span>
                    </div>
                </div>

                <div>
                    <button onclick="window.print()" class="btn-filter" style="background:rgba(255,255,255,.15); border:1.5px solid rgba(255,255,255,.3);">
                        <i class="fas fa-print"></i> Print Ledger
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">

        {{-- Summary Cards --}}
        <div class="summary-row">
            <div class="s-card ob">
                <div class="s-card-label">Opening Balance</div>
                <div class="s-card-value">PKR {{ number_format($openingBalance, 2) }}</div>
            </div>
            <div class="s-card dr">
                <div class="s-card-label">Total Debit (In)</div>
                <div class="s-card-value" style="color:var(--success);">PKR {{ number_format($totalDebit, 2) }}</div>
            </div>
            <div class="s-card cr">
                <div class="s-card-label">Total Credit (Out)</div>
                <div class="s-card-value" style="color:var(--danger);">PKR {{ number_format($totalCredit, 2) }}</div>
            </div>
            <div class="s-card bal">
                <div class="s-card-label" style="color:#047857;">Closing Balance</div>
                <div class="s-card-value" style="color:{{ $closingBalance >= 0 ? '#047857' : 'var(--danger)' }};">
                    PKR {{ number_format(abs($closingBalance), 2) }}
                    @if($closingBalance < 0) <small style="font-size:.7rem;">(Cr)</small> @endif
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('account.ledger', $account->id) }}" class="filter-bar">
            <div>
                <label>Date From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}">
            </div>
            <div>
                <label>Date To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}">
            </div>
            <button type="submit" class="btn-filter">
                <i class="fas fa-filter"></i> Filter
            </button>
            @if($dateFrom || $dateTo)
                <a href="{{ route('account.ledger', $account->id) }}" class="btn-clear">
                    <i class="fas fa-times"></i> Clear
                </a>
            @endif
            <div style="margin-left:auto; font-size:.85rem; color:var(--muted); align-self:center;">
                <i class="fas fa-info-circle"></i>
                {{ $entries->count() }} entries found
            </div>
        </form>

        {{-- Ledger Table --}}
        <div class="ledger-card">
            <div class="ledger-card-header">
                <i class="fas fa-book-open" style="color:var(--primary); font-size:1.3rem;"></i>
                <h2 style="font-size:1.1rem; font-weight:700; color:var(--dark); margin:0; flex:1;">
                    Account Ledger — {{ $account->title }}
                </h2>
                <span style="font-size:.82rem; color:var(--muted);">
                    @if($dateFrom || $dateTo)
                        {{ $dateFrom ?? '—' }} to {{ $dateTo ?? 'Today' }}
                    @else
                        All Transactions
                    @endif
                </span>
            </div>

            @if($entries->count() > 0)
            <div class="table-responsive">
                <table class="ledger-table">
                    <thead>
                        <tr>
                            <th style="width:5%;">#</th>
                            <th style="width:9%;">Entry No</th>
                            <th style="width:10%;">Date</th>
                            <th style="width:12%;">Voucher No</th>
                            <th style="width:30%;">Description</th>
                            <th style="width:11%; text-align:right;">Debit (In)</th>
                            <th style="width:11%; text-align:right;">Credit (Out)</th>
                            <th style="width:12%; text-align:right;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $i => $entry)
                        @php
                            $entryPrefix = strtolower(explode('-', $entry->entry_no ?? 'jv')[0]);
                        @endphp
                        <tr>
                            <td style="color:var(--muted);">{{ $i + 1 }}</td>
                            <td>
                                <span class="entry-no {{ $entryPrefix }}">
                                    {{ $entry->entry_no ?? '—' }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($entry->transaction_date)->format('d M Y') }}</td>
                            <td>
                                @if($entry->voucher_no)
                                    <span class="voucher-tag">{{ $entry->voucher_no }}</span>
                                @else
                                    <span style="color:var(--muted);">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $desc = $entry->description ?? '—';
                                    // ✅ Clean up legacy "N/A" entries — replace with account title
                                    if (str_contains($desc, ': N/A')) {
                                        $desc = str_replace(': N/A', ': ' . ($account->title ?? 'Account'), $desc);
                                    }
                                    // ✅ Also clean up "Party Side:" prefix for cleaner display
                                    $desc = str_replace('Receipt Voucher Party Side: ', 'RV — ', $desc);
                                @endphp
                                {{ $desc }}
                            </td>
                            <td class="text-end">
                                @if($entry->debit > 0)
                                    <span class="amount-debit">{{ number_format($entry->debit, 2) }}</span>
                                @else
                                    <span class="amount-zero">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($entry->credit > 0)
                                    <span class="amount-credit">{{ number_format($entry->credit, 2) }}</span>
                                @else
                                    <span class="amount-zero">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="running-balance {{ $entry->running_balance >= 0 ? 'positive' : 'negative' }}">
                                    {{ number_format(abs($entry->running_balance), 2) }}
                                    @if($entry->running_balance < 0)
                                        <small style="font-size:.7rem; opacity:.7;">Cr</small>
                                    @endif
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="totals-row">
                            <td colspan="5" style="text-align:right;">TOTALS</td>
                            <td style="text-align:right; color:#4ade80;">{{ number_format($totalDebit, 2) }}</td>
                            <td style="text-align:right; color:#f87171;">{{ number_format($totalCredit, 2) }}</td>
                            <td style="text-align:right; color:#38bdf8;">{{ number_format(abs($closingBalance), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <p style="font-size:1rem; font-weight:600; margin:0 0 6px;">No Transactions Yet</p>
                <p style="font-size:.85rem; margin:0;">
                    Ledger entries will appear here automatically when vouchers are created for this account.
                </p>
            </div>
            @endif
        </div>

    </div>
</div>

<style>
@media print {
    .ledger-header { background: #1e293b !important; -webkit-print-color-adjust: exact; }
    .filter-bar, .btn-filter, .back-link { display: none !important; }
    .ledger-card { box-shadow: none; }
}
</style>

@endsection
