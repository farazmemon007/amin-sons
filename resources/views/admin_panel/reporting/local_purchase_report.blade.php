@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid px-4">

            {{-- PAGE HEADER --}}
            <div class="row mb-3 align-items-center">
                <div class="col">
                    <h4 class="mb-0 fw-bold" style="color:#1a1a2e;">
                        <i class="fas fa-store-alt me-2" style="color:#4f46e5;"></i>
                        Local Purchase & Market Report
                    </h4>
                    <small class="text-muted">Summary of all direct market purchases with payment status</small>
                </div>
                <div class="col-auto" id="printBtnWrap" style="display:none;">
                    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary me-2">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                    <button onclick="exportExcel()" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </button>
                </div>
            </div>

            {{-- FILTER CARD --}}
            <div class="card shadow-sm mb-3 border-0" style="border-radius:12px;">
                <div class="card-body py-4">
                    <form id="filterForm" class="row g-3 align-items-end">
                        @php $user = Auth::user(); @endphp

                        @if($user && $user->hasRole('super admin'))
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-secondary small mb-1">Select Branch</label>
                                <select id="branch_id" class="form-select fi-premium shadow-none">
                                    <option value="">-- All Branches --</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name ?? $b->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" id="branch_id" value="{{ $user->branch_id }}">
                        @endif

                        <div class="col-md-3">
                            <label class="form-label fw-bold text-secondary small mb-1">Shop / Market Name (Search)</label>
                            <div class="input-group input-group-premium">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" id="shop_name" class="form-control shadow-none" placeholder="Enter shop name...">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold text-secondary small mb-1">From Date</label>
                            <input type="date" id="start_date" class="form-control fi-premium shadow-none" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-secondary small mb-1">To Date</label>
                            <input type="date" id="end_date" class="form-control fi-premium shadow-none" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-primary btn-sm w-100 fw-bold" style="background:#4f46e5; border:none; border-radius:8px; padding: 10px 0; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- LOADER --}}
            <div id="loader" class="text-center py-5" style="display:none;">
                <div class="spinner-grow text-primary" role="status"></div>
                <p class="text-muted mt-3 fw-medium">Analyzing market transactions...</p>
            </div>

            {{-- REPORT OUTPUT --}}
            <div id="reportBox" style="display:none;">
                
                {{-- Summary Stats --}}
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm" style="border-radius:15px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                            <div class="card-body p-4 text-white">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-medium opacity-75">Total Net Purchases</span>
                                    <i class="fas fa-shopping-cart opacity-50"></i>
                                </div>
                                <h3 class="fw-bold mb-0" id="stat_net">Rs. 0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm" style="border-radius:15px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <div class="card-body p-4 text-white">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-medium opacity-75">Total Amount Paid</span>
                                    <i class="fas fa-money-bill-wave opacity-50"></i>
                                </div>
                                <h3 class="fw-bold mb-0" id="stat_paid">Rs. 0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm" style="border-radius:15px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                            <div class="card-body p-4 text-white">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-medium opacity-75">Outstanding Balance</span>
                                    <i class="fas fa-exclamation-triangle opacity-50"></i>
                                </div>
                                <h3 class="fw-bold mb-0" id="stat_due">Rs. 0</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius:15px;">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="reportTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 text-muted small fw-bold text-uppercase py-3" style="width: 100px;">Date</th>
                                    <th class="border-0 text-muted small fw-bold text-uppercase py-3">Invoice #</th>
                                    <th class="border-0 text-muted small fw-bold text-uppercase py-3">Shop / Market</th>
                                    <th class="border-0 text-muted small fw-bold text-uppercase py-3 text-end">Net Amount</th>
                                    <th class="border-0 text-muted small fw-bold text-uppercase py-3 text-end">Paid</th>
                                    <th class="border-0 text-muted small fw-bold text-uppercase py-3 text-end">Balance</th>
                                    <th class="border-0 text-muted small fw-bold text-uppercase py-3 text-center">Status</th>
                                    <th class="pe-4 border-0 text-muted small fw-bold text-uppercase py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="reportContent" style="font-size: 14px; border-top: 0;">
                                <!-- AJAX content -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

{{-- PAYMENT MODAL --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:15px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="pay_title">Make Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="alert alert-info py-2 border-0 small mb-3" style="background:#eef2ff; color:#4338ca;">
                    <i class="fas fa-info-circle me-1"></i> 
                    Outstanding Balance: <strong>Rs. <span id="pay_due_display">0</span></strong>
                </div>
                
                <input type="hidden" id="pay_purchase_id">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Select Payment Account</label>
                    <select id="pay_account_id" class="form-select bg-light border-0 shadow-none">
                        <option value="">-- Choose Account --</option>
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->title }} (Bal: Rs. {{ number_format($acc->opening_balance, 2) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Payment Amount</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0">Rs.</span>
                            <input type="number" id="pay_amount" class="form-control bg-light border-0 shadow-none" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Payment Date</label>
                        <input type="date" id="pay_date" class="form-control bg-light border-0 shadow-none" value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-bold text-muted">Note (Optional)</label>
                    <textarea id="pay_note" class="form-control bg-light border-0 shadow-none" rows="2" placeholder="e.g. Paid via Cheque #123"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light fw-semibold" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnConfirmPayment" class="btn btn-primary fw-bold px-4" style="background:#4f46e5; border:none; border-radius:8px;">
                    Confirm Payment
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
    .status-badge {
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-paid { background: #dcfce7; color: #15803d; }
    .badge-partial { background: #fef9c3; color: #a16207; }
    .badge-due { background: #fee2e2; color: #b91c1c; }
    
    .table tbody tr:hover { background-color: #f8fafc; }
    .invoice-link { color: #4f46e5; text-decoration: none; font-weight: 600; }
    .invoice-link:hover { text-decoration: underline; }

    /* Premium Input Styling */
    .fi-premium {
        border-radius: 8px !important;
        border: 1.5px solid #e3e6f0 !important;
        background-color: #ffffff !important;
        padding: 0.6rem 0.75rem !important;
        font-size: 0.9rem !important;
        transition: all 0.2s ease-in-out;
    }
    .fi-premium:focus {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.1) !important;
        background-color: #fff !important;
    }
    .input-group-premium {
        border-radius: 8px;
        overflow: hidden;
        border: 1.5px solid #e3e6f0;
        transition: all 0.2s ease-in-out;
    }
    .input-group-premium:focus-within {
        border-color: #4f46e5;
        box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.1);
    }
    .input-group-premium .input-group-text {
        background-color: #f8fafc;
        border: none;
        color: #94a3b8;
    }
    .input-group-premium .form-control {
        border: none !important;
        padding: 0.6rem 0.75rem;
    }
    
    @media print {
        .card-body.py-4, button, .main-content-inner { padding: 0 !important; }
        #filterForm, #printBtnWrap, .btn-submit, .btn-sm { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; }
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    
    function fmt(v) {
        return parseFloat(v || 0).toLocaleString('en-PK', { minimumFractionDigits: 2 });
    }

    $('#btnSearch').click(function() {
        const data = {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            branch_id: $('#branch_id').val(),
            shop_name: $('#shop_name').val()
        };

        if (!data.start_date || !data.end_date) {
            Swal.fire('Error', 'Please select date range', 'error');
            return;
        }

        $('#loader').show();
        $('#reportBox').hide();
        $('#printBtnWrap').hide();

        $.get("{{ route('report.local_purchase.fetch') }}", data)
        .done(function(res) {
            $('#loader').hide();
            renderReport(res);
        })
        .fail(function() {
            $('#loader').hide();
            Swal.fire('Error', 'Failed to fetch report data', 'error');
        });
    });

    function renderReport(res) {
        const tbody = $('#reportContent');
        tbody.empty();

        if (res.data.length === 0) {
            tbody.append('<tr><td colspan="8" class="text-center py-5 text-muted">No local purchases found for the selected criteria.</td></tr>');
        } else {
            res.data.forEach(p => {
                let statusClass = 'badge-due';
                if (p.status === 'Paid') statusClass = 'badge-paid';
                else if (p.status === 'Partial') statusClass = 'badge-partial';

                tbody.append(`
                    <tr>
                        <td class="ps-4 fw-medium text-secondary">${p.date}</td>
                        <td><span class="fw-bold text-dark">${p.invoice_no}</span></td>
                        <td>
                            <div class="fw-bold">${p.shop_name}</div>
                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>${p.branch}</small>
                        </td>
                        <td class="text-end fw-bold">Rs. ${fmt(p.net_amount)}</td>
                        <td class="text-end text-success fw-medium">Rs. ${fmt(p.paid_amount)}</td>
                        <td class="text-end text-danger fw-medium">Rs. ${fmt(p.due_amount)}</td>
                        <td class="text-center">
                            <span class="status-badge ${statusClass}">${p.status}</span>
                        </td>
                        <td class="text-center pe-4">
                            <div class="btn-group">
                                <a href="/purchase/${p.id}/invoice" target="_blank" class="btn btn-sm btn-light border" title="View Invoice">
                                    <i class="fas fa-file-invoice text-primary"></i>
                                </a>
                                ${p.due_amount > 0 ? `
                                    <button type="button" class="btn btn-sm btn-success ms-1 btn-pay" 
                                        data-id="${p.id}" 
                                        data-invoice="${p.invoice_no}" 
                                        data-shop="${p.shop_name}" 
                                        data-due="${p.due_amount}"
                                        title="Make Payment">
                                        <i class="fas fa-money-bill-wave"></i> Pay
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `);
            });
        }

        // Summary Stats
        $('#stat_net').text('Rs. ' + fmt(res.summary.total_net));
        $('#stat_paid').text('Rs. ' + fmt(res.summary.total_paid));
        $('#stat_due').text('Rs. ' + fmt(res.summary.total_due));

        $('#reportBox').fadeIn();
        $('#printBtnWrap').show();
    }

    // --- PAYMENT MODAL LOGIC ---
    $(document).on('click', '.btn-pay', function() {
        const d = $(this).data();
        $('#pay_purchase_id').val(d.id);
        $('#pay_title').text(`Pay to: ${d.shop} (${d.invoice})`);
        $('#pay_amount').val(d.due).attr('max', d.due);
        $('#pay_due_display').text(fmt(d.due));
        
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
    });

    $('#btnConfirmPayment').click(function() {
        const btn = $(this);
        const data = {
            _token: "{{ csrf_token() }}",
            purchase_id: $('#pay_purchase_id').val(),
            account_id: $('#pay_account_id').val(),
            amount: $('#pay_amount').val(),
            date: $('#pay_date').val(),
            note: $('#pay_note').val()
        };

        if (!data.account_id || !data.amount || data.amount <= 0) {
            Swal.fire('Error', 'Please select account and valid amount', 'error');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

        $.post("{{ route('report.local_purchase.pay') }}", data)
        .done(function(res) {
            btn.prop('disabled', false).text('Confirm Payment');
            
            // Hide modal using jQuery for better compatibility
            $('#paymentModal').modal('hide');
            
            Swal.fire({
                icon: 'success',
                title: 'Paid!',
                text: res.success,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload(); // Explicitly reload page as requested
            });
        })
        .fail(function(xhr) {
            btn.prop('disabled', false).text('Confirm Payment');
            let msg = xhr.responseJSON?.error || 'Payment failed';
            Swal.fire('Error', msg, 'error');
        });
    });

    // Export Excel (CSV Simple)
    window.exportExcel = function() {
        let csv = "Date,Invoice #,Shop Name,Branch,Net Amount,Paid,Balance,Status\n";
        $('#reportContent tr').each(function() {
            let row = [];
            $(this).find('td').each(function(i) {
                if(i < 7) { // Skip Action column
                    let text = $(this).text().trim().replace(/Rs\./g, '').replace(/,/g, '');
                    row.push('"' + text + '"');
                }
            });
            if(row.length > 0) csv += row.join(",") + "\n";
        });
        
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.setAttribute('hidden', '');
        a.setAttribute('href', url);
        a.setAttribute('download', 'local_purchase_report_' + new Date().toISOString().slice(0,10) + '.csv');
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    };

    // Auto-search on start
    $('#btnSearch').trigger('click');
});
</script>
@endsection
