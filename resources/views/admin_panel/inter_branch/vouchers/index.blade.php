@extends('admin_panel.layout.app')

@section('content')
<style>
    /* ═══════════════════════════════════════════════════════════
       AMEEN & SONS ERP — INTER-BRANCH VOUCHERS INDEX
       ═══════════════════════════════════════════════════════════ */
    :root {
        --theme-navy: #1e3a5f;
        --theme-navy-light: #2c5282;
        --theme-gold: #c8973a;
        --theme-border: #e2e8f0;
        --theme-bg: #f8fafc;
    }

    .ibv-wrapper {
        padding: 4px 6px 24px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    /* 1. Header Bar */
    .ibv-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        border-radius: 12px;
        padding: 14px 22px;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(30, 58, 95, 0.15);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .ibv-title {
        font-size: 17px;
        font-weight: 800;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        letter-spacing: -0.2px;
        color: #ffffff !important;
    }

    .ibv-subtitle {
        font-size: 11.5px;
        color: rgba(255, 255, 255, 0.85) !important;
        margin-top: 2px;
    }

    .ibv-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-create-payment {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 8px;
        padding: 7px 16px;
        font-size: 12.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3);
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-create-payment:hover {
        background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(220, 38, 38, 0.4);
    }

    .btn-create-receipt {
        background: linear-gradient(135deg, #0d9f6e 0%, #059669 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 8px;
        padding: 7px 16px;
        font-size: 12.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 6px rgba(13, 159, 110, 0.3);
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-create-receipt:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(13, 159, 110, 0.4);
    }

    /* 2. Main Card */
    .ibv-main-card {
        background: #ffffff;
        border: 1px solid var(--theme-border);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        padding: 18px 20px;
    }

    /* 3. Empty State */
    .ibv-empty-state {
        text-align: center;
        padding: 48px 20px;
    }

    .ibv-empty-icon {
        width: 70px;
        height: 70px;
        background: #f1f5f9;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: #94a3b8;
        margin-bottom: 14px;
        border: 1.5px dashed #cbd5e1;
    }

    .ibv-empty-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .ibv-empty-desc {
        font-size: 12.5px;
        color: #64748b;
        max-width: 440px;
        margin: 0 auto 18px;
        line-height: 1.5;
    }

    /* 4. Table Styling */
    .ibv-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0;
    }

    .ibv-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.55px;
        padding: 12px 14px;
        border-top: none;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    .ibv-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
        color: #334155;
        background: #ffffff;
    }

    .ibv-table tbody tr:hover td {
        background: #f8fafc;
    }

    /* Micro-Badges */
    .badge-voucher-id {
        font-family: monospace;
        font-size: 12px;
        font-weight: 700;
        background: #f1f5f9;
        color: #1e3a5f;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        display: inline-block;
    }

    .badge-type-payment {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-type-receipt {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-branch-chip {
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        background: rgba(30, 58, 95, 0.07);
        color: #1e3a5f;
        border: 1px solid rgba(30, 58, 95, 0.12);
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .badge-method {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 4px;
        text-transform: capitalize;
    }

    .btn-action-print {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #1e3a5f;
        border: 1px solid #cbd5e1;
        transition: all 0.2s ease;
        text-decoration: none;
        font-size: 13px;
    }

    .btn-action-print:hover {
        background: #1e3a5f;
        color: #ffffff;
        border-color: #1e3a5f;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(30, 58, 95, 0.2);
    }
</style>

<div class="main-content">
    <div class="ibv-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Top Header Bar --}}
            <div class="ibv-header">
                <div>
                    <h1 class="ibv-title">
                        <i class="fas fa-file-invoice-dollar" style="color: var(--theme-gold);"></i>
                        Inter-Branch Financial Vouchers
                    </h1>
                    <div class="ibv-subtitle">
                        Record and audit inter-branch payments, receipts, and internal financial settlements
                    </div>
                </div>
                <div class="ibv-header-actions">
                    @can('inter.branch.voucher.create')
                        <a href="{{ route('inter_branch_vouchers.create_payment') }}" class="btn-create-payment">
                            <i class="fas fa-arrow-up"></i> Create Payment Voucher
                        </a>
                        <a href="{{ route('inter_branch_vouchers.create_receipt') }}" class="btn-create-receipt">
                            <i class="fas fa-arrow-down"></i> Create Receipt Voucher
                        </a>
                    @endcan
                </div>
            </div>

            {{-- 2. Alerts if any --}}
            @if (session('success'))
                <div class="alert alert-success py-2 px-3 mb-3 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #10b981 !important; border-radius: 6px;">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger py-2 px-3 mb-3 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #ef4444 !important; border-radius: 6px;">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                </div>
            @endif

            {{-- 3. Main Card --}}
            <div class="ibv-main-card">
                @if ($vouchers->isEmpty())
                    <div class="ibv-empty-state">
                        <div class="ibv-empty-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="ibv-empty-title">No Inter-Branch Vouchers Recorded</div>
                        <div class="ibv-empty-desc">
                            There are currently no financial vouchers between branches. Use the actions above to record payment transfers or receive payments from other branches.
                        </div>
                        @can('inter.branch.voucher.create')
                            <div class="d-inline-flex gap-2" style="gap: 8px;">
                                <a href="{{ route('inter_branch_vouchers.create_payment') }}" class="btn-create-payment">
                                    <i class="fas fa-arrow-up"></i> New Payment Voucher
                                </a>
                                <a href="{{ route('inter_branch_vouchers.create_receipt') }}" class="btn-create-receipt">
                                    <i class="fas fa-arrow-down"></i> New Receipt Voucher
                                </a>
                            </div>
                        @endcan
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="ibv-table">
                            <thead>
                                <tr>
                                    <th style="width: 9%;">Voucher ID</th>
                                    <th style="width: 11%;">Date</th>
                                    <th style="width: 10%; text-align: center;">Type</th>
                                    <th style="width: 14%;">From Branch</th>
                                    <th style="width: 14%;">To Branch</th>
                                    <th style="width: 12%; text-align: right;">Amount</th>
                                    <th style="width: 8%; text-align: center;">Method</th>
                                    <th style="width: 8%;">Reference</th>
                                    <th style="width: 8%;">Created By</th>
                                    <th style="width: 6%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vouchers as $voucher)
                                    <tr>
                                        <td>
                                            <span class="badge-voucher-id">#VCH-{{ $voucher->id }}</span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: #1e293b;">
                                                {{ $voucher->created_at->format('M d, Y') }}
                                            </div>
                                            <small class="text-muted">{{ $voucher->created_at->format('h:i A') }}</small>
                                        </td>
                                        <td style="text-align: center;">
                                            @if ($voucher->type === 'payment')
                                                <span class="badge-type-payment">
                                                    <i class="fas fa-arrow-up"></i> Payment
                                                </span>
                                            @else
                                                <span class="badge-type-receipt">
                                                    <i class="fas fa-arrow-down"></i> Receipt
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge-branch-chip">
                                                <i class="fas fa-store text-primary"></i>
                                                {{ $voucher->fromBranch->name ?? $voucher->fromBranch->branch_name ?? 'Branch #' . $voucher->from_branch_id }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-branch-chip">
                                                <i class="fas fa-building text-info"></i>
                                                {{ $voucher->toBranch->name ?? $voucher->toBranch->branch_name ?? 'Branch #' . $voucher->to_branch_id }}
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <span style="font-weight: 800; font-size: 13.5px; color: {{ $voucher->type === 'payment' ? '#dc2626' : '#0d9f6e' }};">
                                                PKR {{ number_format($voucher->amount, 2) }}
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge-method">{{ $voucher->method }}</span>
                                        </td>
                                        <td>
                                            <span style="font-size: 12px; color: #64748b;">{{ $voucher->reference ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <span style="font-weight: 600; color: #1e293b; font-size: 12px;">{{ $voucher->createdBy->name ?? 'System' }}</span>
                                            @if($voucher->remarks)
                                                <br><small class="text-muted" title="{{ $voucher->remarks }}"><i class="fas fa-comment-alt mr-1"></i>{{ Str::limit($voucher->remarks, 20) }}</small>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{ route('inter_branch_vouchers.show', $voucher->id) }}" class="btn-action-print" title="Print Official Receipt" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <small class="text-muted">Showing page {{ $vouchers->currentPage() }} of {{ $vouchers->lastPage() }}</small>
                        <div>
                            {{ $vouchers->links() }}
                        </div>
                    </div>
                @endif
            </div>{{-- end ibv-main-card --}}

        </div>
    </div>
</div>
@endsection
