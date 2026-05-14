@extends('admin_panel.layout.app')

@section('content')
<style>
:root {
    --primary: #6366f1;
    --success: #22c55e;
    --danger:  #ef4444;
    --warning: #f59e0b;
    --info:    #0ea5e9;
    --dark:    #1e293b;
    --muted:   #64748b;
    --border:  #e2e8f0;
    --light:   #f8fafc;
}

/* ── Page Header ─────────────────────────────── */
.ledger-header {
    background: linear-gradient(135deg, #1e293b, #334155);
    color: white;
    padding: 32px 0;
    margin-bottom: 30px;
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0,0,0,.2);
}
.back-link { color:rgba(255,255,255,.85); text-decoration:none; font-size:.9rem; display:inline-flex; align-items:center; gap:6px; }
.back-link:hover { color:white; }
.account-meta { display:flex; align-items:center; gap:16px; margin-top:14px; }
.account-badge {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 8px; padding:6px 14px;
    font-size:.85rem; font-weight:600;
    display:inline-flex; align-items:center; gap:6px;
}

/* ── Summary Cards ───────────────────────────── */
.summary-row { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:18px; margin-bottom:28px; }
.s-card {
    background:white; border-radius:12px;
    padding:20px 22px;
    box-shadow:0 2px 12px rgba(0,0,0,.07);
    border-left:4px solid transparent;
}
.s-card.ob  { border-color:#6366f1; }
.s-card.dr  { border-color:#22c55e; }
.s-card.cr  { border-color:#ef4444; }
.s-card.bal { border-color:#0ea5e9; }
.s-card-label { font-size:.75rem; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); font-weight:700; margin-bottom:6px; }
.s-card-value { font-size:1.4rem; font-weight:800; color:var(--dark); }

/* ── Filter Bar ──────────────────────────────── */
.filter-bar {
    background:white; border-radius:12px;
    padding:16px 20px; margin-bottom:20px;
    box-shadow:0 2px 8px rgba(0,0,0,.06);
    display:flex; gap:14px; align-items:flex-end; flex-wrap:wrap;
}
.filter-bar label { font-size:.8rem; font-weight:700; color:var(--muted); display:block; margin-bottom:4px; }
.filter-bar input[type=date] {
    border:1.5px solid var(--border); border-radius:8px;
    padding:8px 12px; font-size:.9rem; cursor:pointer;
}
.btn-filter {
    background: var(--primary); color:white;
    border:none; border-radius:8px;
    padding:9px 20px; font-size:.9rem; font-weight:600;
    cursor:pointer; display:inline-flex; align-items:center; gap:6px;
    transition: background .2s, transform .15s;
}
.btn-filter:hover { background:#4f46e5; transform:translateY(-1px); }
.btn-clear {
    background:#f1f5f9; color:var(--dark);
    border:1.5px solid var(--border); border-radius:8px;
    padding:8px 16px; font-size:.9rem; font-weight:600;
    cursor:pointer; text-decoration:none;
    display:inline-flex; align-items:center; gap:6px;
}

/* ── Ledger Table ────────────────────────────── */
.ledger-card {
    background:white; border-radius:14px;
    box-shadow:0 2px 15px rgba(0,0,0,.08);
    overflow:hidden;
}
.ledger-card-header {
    background:linear-gradient(135deg,#f8fafc,#f1f5f9);
    padding:18px 24px; border-bottom:2px solid var(--border);
    display:flex; align-items:center; gap:12px;
}
.ledger-table { width:100%; border-collapse:collapse; }
.ledger-table thead th {
    background:#1e293b; color:white;
    padding:13px 16px; font-size:.78rem;
    text-transform:uppercase; letter-spacing:.06em;
    font-weight:700;
}
.ledger-table thead th:first-child { border-radius:0; }
.ledger-table tbody tr { border-bottom:1px solid var(--border); transition:background .12s; }
.ledger-table tbody tr:last-child { border-bottom:none; }
.ledger-table tbody tr:hover { background:#f8fafc; }
.ledger-table td { padding:12px 16px; font-size:.9rem; vertical-align:middle; }

.entry-no {
    font-family:'Courier New',monospace; font-weight:700; font-size:.85rem;
    padding:3px 8px; border-radius:5px;
}
.entry-no.br { background:rgba(99,102,241,.1); color:#6366f1; }
.entry-no.cr { background:rgba(239,68,68,.1); color:#ef4444; }
.entry-no.jv { background:rgba(245,158,11,.1); color:#b45309; }
.entry-no.ob { background:rgba(14,165,233,.1); color:#0369a1; }

.voucher-tag {
    font-size:.78rem; color:var(--muted);
    background:var(--light); padding:2px 7px;
    border-radius:4px; font-family:'Courier New',monospace;
}

.amount-debit  { color:var(--success); font-weight:700; }
.amount-credit { color:var(--danger);  font-weight:700; }
.amount-zero   { color:#cbd5e1; }

.running-balance { font-weight:800; font-size:.95rem; }
.running-balance.positive { color:#0ea5e9; }
.running-balance.negative { color:#ef4444; }

/* Totals row */
.totals-row td {
    background:#1e293b; color:white;
    font-weight:700; padding:14px 16px;
    font-size:.92rem;
}
.empty-state { text-align:center; padding:60px 20px; color:var(--muted); }
.empty-state i { font-size:2.5rem; opacity:.3; margin-bottom:12px; display:block; }
</style>

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="ledger-header">
        <div class="container">
            <a href="{{ url()->previous() }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Accounts
            </a>

            <div style="margin-top:16px; display:flex; justify-content:space-between; align-items:flex-start; gap:20px; flex-wrap:wrap;">
                <div>
                    <h1 style="font-size:1.8rem; font-weight:800; margin:0;">
                        {{ $account->title }}
                    </h1>
                    <div class="account-meta">
                        <span class="account-badge">
                            <i class="fas fa-code"></i>
                            {{ $account->account_code ?? 'N/A' }}
                        </span>
                        <span class="account-badge">
                            <i class="fas fa-layer-group"></i>
                            {{ $account->head->name ?? '—' }}
                        </span>
                        <span class="account-badge">
                            <i class="fas fa-building"></i>
                            {{ $account->branch->name ?? '—' }}
                        </span>
                        <span class="account-badge" style="{{ strtolower($account->type) == 'debit' ? 'background:rgba(34,197,94,.2);' : 'background:rgba(239,68,68,.2);' }}">
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
                <div class="s-card-value" style="color:#22c55e;">PKR {{ number_format($totalDebit, 2) }}</div>
            </div>
            <div class="s-card cr">
                <div class="s-card-label">Total Credit (Out)</div>
                <div class="s-card-value" style="color:#ef4444;">PKR {{ number_format($totalCredit, 2) }}</div>
            </div>
            <div class="s-card bal">
                <div class="s-card-label">Closing Balance</div>
                <div class="s-card-value" style="color:{{ $closingBalance >= 0 ? '#0ea5e9' : '#ef4444' }};">
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
                            <td>{{ $entry->description ?? '—' }}</td>
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
