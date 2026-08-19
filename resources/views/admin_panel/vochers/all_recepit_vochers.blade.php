@extends('admin_panel.layout.app')

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        /* ═══════════════════════════════════════════════════════════
           AMEEN & SONS ERP — RECEIPTS VOUCHERS (CRV)
           ═══════════════════════════════════════════════════════════ */
        :root {
            --theme-navy: #1e3a5f;
            --theme-navy-light: #2c5282;
            --theme-gold: #c8973a;
            --theme-border: #e2e8f0;
            --theme-bg: #f8fafc;
        }

        .rv-wrapper {
            padding: 4px 6px 24px;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        /* 1. Header Bar */
        .rv-header {
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

        .rv-title {
            font-size: 17px;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.2px;
            color: #ffffff !important;
        }

        .rv-subtitle {
            font-size: 11.5px;
            color: rgba(255, 255, 255, 0.85) !important;
            margin-top: 2px;
        }

        .btn-create-rv {
            background: linear-gradient(135deg, #0d9f6e 0%, #059669 100%);
            color: #ffffff !important;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            box-shadow: 0 2px 8px rgba(13, 159, 110, 0.3);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-create-rv:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 159, 110, 0.4);
            color: #ffffff !important;
        }

        /* 2. Super Admin Filter Card */
        .branch-filter-card {
            background: #ffffff;
            border: 1px solid var(--theme-border);
            border-radius: 10px;
            padding: 12px 18px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
        }

        .branch-filter-select {
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            min-width: 240px;
            background-color: #f8fafc;
            transition: all 0.15s ease;
        }

        .branch-filter-select:focus {
            border-color: #1e3a5f;
            background-color: #ffffff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.12);
        }

        /* 3. Stat Summary Cards */
        .stat-card-mini {
            background: #ffffff;
            border: 1px solid var(--theme-border);
            border-radius: 10px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            height: 100%;
        }

        .stat-icon-mini {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .stat-label-mini {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 2px;
        }

        .stat-value-mini {
            font-size: 17px;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.2;
        }

        /* 4. Main Data Card */
        .rv-main-card {
            background: #ffffff;
            border: 1px solid var(--theme-border);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            padding: 18px 20px;
        }

        /* 5. Data Table */
        #example {
            width: 100% !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }

        #example thead th {
            background: #f8fafc !important;
            color: #64748b !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.55px !important;
            padding: 12px 14px !important;
            border-top: none !important;
            border-bottom: 2px solid #e2e8f0 !important;
            white-space: nowrap !important;
        }

        #example tbody td {
            padding: 11px 13px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9 !important;
            font-size: 13px !important;
            color: #334155 !important;
            background: #ffffff !important;
            white-space: nowrap !important;
        }

        #example tbody tr:hover td {
            background: #f8fafc !important;
        }

        /* Micro Badges */
        .badge-branch-chip {
            font-size: 11.5px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 6px;
            background: rgba(30, 58, 95, 0.08);
            color: #1e3a5f;
            border: 1px solid rgba(30, 58, 95, 0.15);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .badge-voucher-no {
            font-family: monospace;
            font-size: 12px;
            font-weight: 700;
            background: #f1f5f9;
            color: #1e3a5f;
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-type-customer {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-block;
        }

        .badge-type-vendor {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-block;
        }

        .badge-type-account {
            background: #f3e8ff;
            color: #7e22ce;
            border: 1px solid #e9d5ff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-block;
        }

        .badge-ref {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            font-size: 11.5px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 4px;
            display: inline-block;
        }

        .badge-status-voided {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        /* Action Buttons */
        .btn-action-print {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            color: #1e3a5f;
            border-radius: 6px;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .btn-action-print:hover {
            background: #1e3a5f;
            color: #ffffff !important;
            border-color: #1e3a5f;
        }

        .btn-action-void {
            background: #ffffff;
            border: 1.5px solid #fde68a;
            color: #b45309;
            border-radius: 6px;
            padding: 3px 9px;
            font-size: 11.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .btn-action-void:hover {
            background: #f59e0b;
            color: #ffffff;
            border-color: #f59e0b;
        }
    </style>
@endsection

@section('content')
@php
    $totalAmount = $receipts->where('status', '!=', 'voided')->sum('total_amount');
    $activeCount = $receipts->where('status', '!=', 'voided')->count();
    $voidedCount = $receipts->where('status', '===', 'voided')->count();
@endphp

<div class="main-content">
    <div class="rv-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Top Header Bar --}}
            <div class="rv-header">
                <div>
                    <h1 class="rv-title">
                        <i class="fas fa-file-invoice-dollar" style="color: var(--theme-gold);"></i>
                        Receipts Vouchers (CRV)
                    </h1>
                    <div class="rv-subtitle">
                        Manage cash &amp; bank receipt collections from customers, vendors, and general ledger accounts
                    </div>
                </div>
                <div>
                    @can('receipts.voucher.create')
                        <a href="{{ route('recepit-vochers') }}" class="btn-create-rv">
                            <i class="fas fa-plus"></i> Add Receipt Voucher
                        </a>
                    @endcan
                </div>
            </div>

            {{-- 2. Super Admin Multi-Branch Filter Bar --}}
            @if($isSuperAdmin)
                <div class="branch-filter-card">
                    <div class="d-flex align-items-center" style="gap: 10px;">
                        <div style="width: 32px; height: 32px; background: rgba(30, 58, 95, 0.08); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #1e3a5f;">
                            <i class="fas fa-filter"></i>
                        </div>
                        <div>
                            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">
                                Super Admin Scope Filter
                            </div>
                            <div style="font-size: 13px; font-weight: 700; color: #1e293b;">
                                Filter Vouchers by Branch
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <label for="branchFilter" class="small font-weight-bold text-muted mb-0 d-none d-sm-inline">
                            Branch:
                        </label>
                        <select id="branchFilter" class="form-select branch-filter-select">
                            <option value="all" {{ ($selectedBranch == 'all' || empty($selectedBranch)) ? 'selected' : '' }}>
                                🌐 All Branches (Consolidated View)
                            </option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ ($selectedBranch == $b->id) ? 'selected' : '' }}>
                                    🏬 {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($selectedBranch && $selectedBranch != 'all')
                            <a href="{{ route('all-recepit-vochers') }}" class="btn btn-sm btn-outline-secondary" title="Clear Filter">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 3. Mini KPI Summary Cards --}}
            <div class="row g-2 mb-3">
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="stat-card-mini">
                        <div class="stat-icon-mini" style="background: rgba(30, 58, 95, 0.1); color: #1e3a5f;">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div>
                            <div class="stat-label-mini">Total Vouchers</div>
                            <div class="stat-value-mini">{{ $receipts->count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="stat-card-mini">
                        <div class="stat-icon-mini" style="background: rgba(13, 159, 110, 0.1); color: #0d9f6e;">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <div class="stat-label-mini">Total Collected</div>
                            <div class="stat-value-mini" style="color: #0d9f6e;">PKR {{ number_format($totalAmount, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="stat-card-mini">
                        <div class="stat-icon-mini" style="background: rgba(14, 165, 233, 0.1); color: #0284c7;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="stat-label-mini">Active Receipts</div>
                            <div class="stat-value-mini text-info">{{ $activeCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="stat-card-mini">
                        <div class="stat-icon-mini" style="background: rgba(239, 68, 68, 0.1); color: #dc2626;">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div>
                            <div class="stat-label-mini">Voided Vouchers</div>
                            <div class="stat-value-mini text-danger">{{ $voidedCount }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Alerts if any --}}
            @if(session('success'))
                <div class="alert alert-success py-2 px-3 mb-3 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #10b981 !important; border-radius: 6px;">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger py-2 px-3 mb-3 small font-weight-bold border-0 shadow-sm" style="border-left: 4px solid #ef4444 !important; border-radius: 6px;">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                </div>
            @endif

            {{-- 5. Main Data Card --}}
            <div class="rv-main-card">
                <div class="table-responsive">
                    <table id="example" class="table">
                        <thead>
                            <tr>
                                <th style="width: 4%; text-align: center;">#</th>
                                @if($isSuperAdmin)
                                    <th style="width: 14%;">Branch</th>
                                @endif
                                <th style="width: 12%;">Voucher No</th>
                                <th style="width: 10%;">Receipt Date</th>
                                <th style="width: 10%;">Type</th>
                                <th style="width: 18%;">Party / Account</th>
                                <th style="width: 9%;">Ref No</th>
                                <th style="width: 12%; text-align: right;">Total Amount</th>
                                <th style="width: 11%;">Created At</th>
                                <th style="width: 10%; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receipts as $item)
                                @php
                                    $refs = json_decode($item->reference_no, true);
                                    $reference = is_array($refs) ? implode(', ', array_filter($refs)) : $item->reference_no;
                                    $isVoided = ($item->status === 'voided');
                                @endphp
                                <tr style="{{ $isVoided ? 'opacity: 0.65; background-color: #fafafa;' : '' }}">
                                    <td style="text-align: center;">
                                        <span class="badge" style="background: #f1f5f9; color: #64748b; font-family: monospace; font-size: 11px;">#{{ $item->id }}</span>
                                    </td>
                                    @if($isSuperAdmin)
                                        <td>
                                            <span class="badge-branch-chip">
                                                <i class="fas fa-store text-primary" style="font-size: 10px;"></i>
                                                {{ $item->branch->name ?? $item->branch->branch_name ?? 'Branch #' . $item->branch_id }}
                                            </span>
                                        </td>
                                    @endif
                                    <td>
                                        <span class="badge-voucher-no">
                                            <i class="fas fa-receipt text-primary" style="font-size: 10px;"></i>
                                            {{ $item->rvid ?? 'RV-' . str_pad($item->id, 4, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #1e293b;">
                                            {{ !empty($item->receipt_date) ? \Carbon\Carbon::parse($item->receipt_date)->format('M d, Y') : '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if(stripos($item->type_label, 'Customer') !== false || stripos($item->type_label, 'Walk-in') !== false)
                                            <span class="badge-type-customer"><i class="fas fa-user-tag mr-1"></i>{{ $item->type_label }}</span>
                                        @elseif(stripos($item->type_label, 'Vendor') !== false)
                                            <span class="badge-type-vendor"><i class="fas fa-truck mr-1"></i>{{ $item->type_label }}</span>
                                        @else
                                            <span class="badge-type-account"><i class="fas fa-sitemap mr-1"></i>{{ $item->type_label }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #1e293b; font-size: 13px;">
                                            {{ $item->party_name ?? '-' }}
                                        </div>
                                        @if(!empty($item->remarks))
                                            <small class="text-muted" title="{{ $item->remarks }}">
                                                <i class="fas fa-comment-dots mr-1"></i>{{ Str::limit($item->remarks, 25) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($reference))
                                            <span class="badge-ref">{{ $reference }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">
                                        <span style="font-weight: 800; font-size: 13.5px; color: {{ $isVoided ? '#94a3b8; text-decoration: line-through;' : '#0d9f6e;' }};">
                                            PKR {{ number_format((float)$item->total_amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #475569; font-size: 12px;">
                                            {{ $item->created_at ? $item->created_at->format('M d, Y') : '-' }}
                                        </div>
                                        <small class="text-muted">{{ $item->created_at ? $item->created_at->format('h:i A') : '' }}</small>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="d-inline-flex align-items-center" style="gap: 6px;">
                                            @if(!$isVoided)
                                                <a href="{{ route('receiptVoucher.print', $item->id) }}"
                                                    target="_blank"
                                                    class="btn-action-print" title="Print Receipt Voucher">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                @can('receipts.voucher.create')
                                                    <form action="{{ route('recepit-vochers.destroy', $item->id) }}" method="POST" class="d-inline void-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn-action-void btn-trigger-void" title="Void Voucher">
                                                            <i class="fas fa-ban"></i> Void
                                                        </button>
                                                    </form>
                                                @endcan
                                            @else
                                                <span class="badge-status-voided"><i class="fas fa-times-circle"></i> Voided</span>
                                                <a href="{{ route('receiptVoucher.print', $item->id) }}"
                                                    target="_blank"
                                                    class="btn-action-print" title="Print Voided Receipt">
                                                    <i class="fas fa-print text-muted"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>{{-- end rv-main-card --}}

        </div>
    </div>
</div>
@endsection

@section('js')
    <!-- DataTable JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                "pageLength": 10,
                "lengthMenu": [5, 10, 25, 50, 100],
                "order": [
                    [0, 'desc']
                ],
                "language": {
                    "search": "Search Receipts:",
                    "lengthMenu": "Show _MENU_ entries"
                }
            });

            // Super Admin Branch Filter Change Event
            $('#branchFilter').on('change', function() {
                const branchId = $(this).val();
                let url = "{{ route('all-recepit-vochers') }}";
                if (branchId && branchId !== 'all') {
                    url += "?branch_id=" + encodeURIComponent(branchId);
                }
                window.location.href = url;
            });

            // SweetAlert Confirm for Void Action
            $(document).on('click', '.btn-trigger-void', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                
                Swal.fire({
                    title: 'Void Receipt Voucher?',
                    text: 'This will reverse all financial ledger entries and mark this voucher as void. This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Void Voucher',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
