@extends('admin_panel.layout.app')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

    .jv-page { background: #f0f4f8; min-height: 100vh; padding: 1.5rem; }

    /* ── Header ── */
    .jv-header {
        background: linear-gradient(135deg, #3730a3 0%, #6d28d9 60%, #8b5cf6 100%);
        border-radius: 16px;
        padding: 1.4rem 2rem;
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 8px 24px rgba(109,40,217,0.30);
        position: relative;
        overflow: hidden;
    }
    .jv-header::before {
        content: ''; position: absolute;
        top: -50px; right: -50px;
        width: 220px; height: 220px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .jv-header h3 { color: #fff; font-weight: 800; font-size: 1.35rem; margin: 0; }
    .jv-header p  { color: rgba(255,255,255,0.75); margin: 0; font-size: 0.82rem; margin-top: 3px; }
    .btn-new-jv {
        background: rgba(255,255,255,0.2);
        color: #fff; border: 1.5px solid rgba(255,255,255,0.4);
        border-radius: 10px; padding: 0.6rem 1.4rem;
        font-weight: 700; font-size: 0.875rem;
        text-decoration: none;
        display: flex; align-items: center; gap: 0.5rem;
        transition: all 0.2s; backdrop-filter: blur(4px);
        white-space: nowrap;
    }
    .btn-new-jv:hover { background: rgba(255,255,255,0.32); color: #fff; transform: translateY(-1px); }

    /* ── Stats Cards ── */
    .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 4px 14px rgba(0,0,0,0.07);
        display: flex; align-items: center; gap: 1rem;
    }
    .stat-icon {
        width: 50px; height: 50px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .stat-icon.purple { background: #ede9fe; color: #7c3aed; }
    .stat-icon.green  { background: #d1fae5; color: #059669; }
    .stat-icon.amber  { background: #fef3c7; color: #d97706; }
    .stat-icon.blue   { background: #dbeafe; color: #2563eb; }
    .stat-label { font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
    .stat-value { font-size: 1.35rem; font-weight: 800; color: #1e293b; }

    /* ── Search / Filter ── */
    .filter-bar {
        background: #fff;
        border-radius: 12px;
        padding: 0.9rem 1.25rem;
        margin-bottom: 1.25rem;
        display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .filter-bar input, .filter-bar select {
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        padding: 0.5rem 0.85rem; font-size: 0.85rem;
        outline: none; transition: border-color 0.2s;
    }
    .filter-bar input:focus, .filter-bar select:focus { border-color: #7c3aed; }

    /* ── Table ── */
    .jv-table-wrap {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.07);
        overflow: hidden;
    }
    .jv-table { width: 100%; border-collapse: collapse; }
    .jv-table thead th {
        background: linear-gradient(135deg, #3730a3, #6d28d9);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 0.85rem 1rem;
        text-align: left;
        white-space: nowrap;
    }
    .jv-table tbody td {
        padding: 0.85rem 1rem;
        font-size: 0.85rem;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
        vertical-align: middle;
    }
    .jv-table tbody tr:last-child td { border-bottom: none; }
    .jv-table tbody tr:hover { background: #faf5ff; }

    /* ── Badges ── */
    .badge-jvid {
        background: #ede9fe; color: #5b21b6;
        border-radius: 7px; padding: 3px 10px;
        font-size: 0.78rem; font-weight: 700;
        font-family: monospace; letter-spacing: 0.03em;
    }
    .badge-credit {
        background: #d1fae5; color: #065f46;
        border-radius: 7px; padding: 3px 9px;
        font-size: 0.75rem; font-weight: 600;
    }
    .badge-debit {
        background: #fef3c7; color: #92400e;
        border-radius: 7px; padding: 3px 9px;
        font-size: 0.75rem; font-weight: 600;
    }
    .badge-posted {
        background: #d1fae5; color: #065f46;
        border-radius: 6px; padding: 2px 9px;
        font-size: 0.72rem; font-weight: 700;
    }
    .amount-cell {
        font-family: monospace; font-size: 0.92rem;
        font-weight: 700; color: #4f46e5;
    }

    /* ── Action Buttons ── */
    .action-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px;
        border-radius: 7px;
        border: none; cursor: pointer;
        text-decoration: none;
        transition: all 0.15s;
        font-size: 0.82rem;
    }
    .action-btn.print { background: #ede9fe; color: #7c3aed; }
    .action-btn.print:hover { background: #7c3aed; color: #fff; }
    .action-btn.delete { background: #fee2e2; color: #dc2626; }
    .action-btn.delete:hover { background: #dc2626; color: #fff; }

    /* ── Empty State ── */
    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        color: #64748b;
    }
    .empty-state i { font-size: 4rem; color: #c4b5fd; margin-bottom: 1rem; display: block; }
    .empty-state h4 { font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; }

    /* ── Alert ── */
    .jv-alert { border-radius: 10px; padding: 0.85rem 1.2rem; font-size: 0.875rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
    .jv-alert.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .jv-alert.error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

    @media (max-width: 992px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 576px) {
        .jv-header { flex-direction: column; gap: 0.75rem; text-align: center; }
        .stats-row { grid-template-columns: 1fr; }
    }
</style>

<div class="jv-page">
    <div class="container-fluid" style="max-width: 1300px;">

        {{-- ── Header ── --}}
        <div class="jv-header">
            <div>
                <h3><i class="bi bi-journal-bookmark-fill me-2"></i>Journal Vouchers</h3>
                <p>All double-entry transfer records — Customer & Vendor ledger adjustments</p>
            </div>
            @can('journal.voucher.create')
            <a href="{{ route('journal.vouchers.create') }}" class="btn-new-jv">
                <i class="bi bi-plus-lg"></i> New Journal Voucher
            </a>
            @endcan
        </div>

        @if(session('success'))
            <div class="jv-alert success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="jv-alert error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
        @endif

        {{-- ── Stats ── --}}
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="bi bi-journal-text"></i></div>
                <div>
                    <div class="stat-label">Total JVs</div>
                    <div class="stat-value">{{ $vouchers->count() }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="stat-label">Total Amount</div>
                    <div class="stat-value">{{ number_format($vouchers->sum('amount'), 0) }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-calendar-check"></i></div>
                <div>
                    <div class="stat-label">This Month</div>
                    <div class="stat-value">{{ $vouchers->filter(fn($v) => \Carbon\Carbon::parse($v->voucher_date)->isCurrentMonth())->count() }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="stat-label">Posted</div>
                    <div class="stat-value">{{ $vouchers->where('status', 'posted')->count() }}</div>
                </div>
            </div>
        </div>

        {{-- ── Filter Bar ── --}}
        <div class="filter-bar">
            <i class="bi bi-funnel" style="color:#7c3aed;"></i>
            <input type="text" id="searchInput" placeholder="Search by JVID, party name..." style="flex: 1; min-width: 200px;">
            <input type="date" id="dateFilter">
            <select id="typeFilter">
                <option value="">All Parties</option>
                <option value="customer">Customer</option>
                <option value="vendor">Vendor</option>
            </select>
            <button onclick="clearFilters()" style="background:#f1f5f9; border:1.5px solid #e2e8f0; border-radius:8px; padding:0.5rem 1rem; font-size:0.83rem; color:#64748b; cursor:pointer;">
                <i class="bi bi-x-circle me-1"></i>Clear
            </button>
        </div>

        {{-- ── Table ── --}}
        <div class="jv-table-wrap">
            @if($vouchers->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-journal-x"></i>
                    <h4>No Journal Vouchers Yet</h4>
                    <p style="font-size:0.88rem;">Create your first Journal Voucher to transfer customer dues to a vendor.</p>
                    @can('journal.voucher.create')
                    <a href="{{ route('journal.vouchers.create') }}" style="
                        background: linear-gradient(135deg, #6d28d9, #4f46e5);
                        color: #fff; text-decoration: none;
                        border-radius: 10px; padding: 0.7rem 1.75rem;
                        font-weight: 700; font-size: 0.875rem;
                        display: inline-flex; align-items: center; gap: 0.4rem;
                        margin-top: 1rem;
                    ">
                        <i class="bi bi-plus-lg"></i> Create Journal Voucher
                    </a>
                    @endcan
                </div>
            @else
            <table class="jv-table" id="jvTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>JV ID</th>
                        <th>Date</th>
                        <th>Credit Side (From)</th>
                        <th>Debit Side (To)</th>
                        <th>Narration</th>
                        <th style="text-align:right">Amount (PKR)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="jvTableBody">
                    @foreach($vouchers as $i => $jv)
                    <tr class="jv-row"
                        data-jvid="{{ $jv->jvid }}"
                        data-credit="{{ strtolower($jv->credit_party_name) }}"
                        data-debit="{{ strtolower($jv->debit_party_name) }}"
                        data-credit-type="{{ $jv->credit_party_type }}"
                        data-debit-type="{{ $jv->debit_party_type }}"
                        data-date="{{ $jv->voucher_date }}">
                        <td style="color:#94a3b8; font-size:0.78rem;">{{ $i + 1 }}</td>
                        <td><span class="badge-jvid">{{ $jv->jvid }}</span></td>
                        <td style="color:#475569; font-size:0.82rem;">
                            {{ \Carbon\Carbon::parse($jv->voucher_date)->format('d M Y') }}
                            <div style="font-size:0.7rem; color:#94a3b8;">{{ \Carbon\Carbon::parse($jv->created_at)->format('h:i A') }}</div>
                        </td>
                        <td>
                            <span class="badge-credit"><i class="bi bi-arrow-up-circle me-1"></i>{{ ucfirst($jv->credit_party_type) }}</span>
                            <div style="font-weight:700; margin-top:3px; font-size:0.88rem;">{{ $jv->credit_party_name }}</div>
                        </td>
                        <td>
                            <span class="badge-debit"><i class="bi bi-arrow-down-circle me-1"></i>{{ ucfirst($jv->debit_party_type) }}</span>
                            <div style="font-weight:700; margin-top:3px; font-size:0.88rem;">{{ $jv->debit_party_name }}</div>
                        </td>
                        <td style="color:#64748b; font-size:0.82rem; max-width:180px;">
                            {{ Str::limit($jv->remarks ?? '—', 50) }}
                        </td>
                        <td class="amount-cell" style="text-align:right;">
                            {{ number_format($jv->amount, 2) }}
                        </td>
                        <td><span class="badge-posted"><i class="bi bi-check-circle me-1"></i>{{ ucfirst($jv->status) }}</span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('journal.vouchers.print', $jv->id) }}" target="_blank"
                                   class="action-btn print" title="Print JV">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @can('journal.voucher.delete')
                                <form action="{{ route('journal.vouchers.destroy', $jv->id) }}" method="POST"
                                      onsubmit="return confirm('Delete Journal Voucher {{ $jv->jvid }}? This will NOT reverse ledger entries.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn delete" title="Delete">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {

    function applyFilters() {
        var search   = $('#searchInput').val().toLowerCase();
        var dateVal  = $('#dateFilter').val();
        var typeVal  = $('#typeFilter').val().toLowerCase();

        $('.jv-row').each(function () {
            var jvid   = $(this).data('jvid').toLowerCase();
            var credit = $(this).data('credit').toLowerCase();
            var debit  = $(this).data('debit').toLowerCase();
            var ctype  = $(this).data('credit-type').toLowerCase();
            var dtype  = $(this).data('debit-type').toLowerCase();
            var date   = $(this).data('date') || '';

            var matchSearch = !search || jvid.includes(search) || credit.includes(search) || debit.includes(search);
            var matchDate   = !dateVal || date === dateVal;
            var matchType   = !typeVal || ctype.includes(typeVal) || dtype.includes(typeVal);

            $(this).toggle(matchSearch && matchDate && matchType);
        });
    }

    $('#searchInput, #dateFilter, #typeFilter').on('input change', applyFilters);

    window.clearFilters = function () {
        $('#searchInput').val('');
        $('#dateFilter').val('');
        $('#typeFilter').val('');
        applyFilters();
    };
});
</script>
@endsection
