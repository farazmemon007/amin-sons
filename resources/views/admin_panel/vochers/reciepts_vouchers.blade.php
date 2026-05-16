@extends('admin_panel.layout.app')
@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-blue: #2563eb;
            --success-green: #10b981;
            --bg-light: #f8fafc;
            --border-color: #e2e8f0;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        .main-content {
            background-color: var(--bg-light);
            min-height: 100vh;
            padding: 1.5rem;
        }

        .voucher-header {
            background: white;
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            border-left: 5px solid var(--primary-blue);
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            background: white;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 0.6rem 0.75rem;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .table-custom {
            margin-top: 1rem;
        }

        .table-custom thead th {
            background: #f1f5f9;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            padding: 0.75rem;
            border-bottom: 2px solid var(--border-color);
        }

        .table-custom tbody td {
            padding: 0.5rem;
            vertical-align: middle;
        }

        .total-section {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-outline-secondary {
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
        }

        .party-info-badge {
            background: #eff6ff;
            color: #1e40af;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            margin-top: 0.5rem;
            border: 1px solid #dbeafe;
        }

        .balance-positive { color: #059669; }
        .balance-negative { color: #dc2626; }

        .narration-group { position: relative; }
        .add-row-btn {
            background: #f1f5f9;
            color: var(--primary-blue);
            border: 1px dashed var(--primary-blue);
            width: 100%;
            padding: 0.5rem;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 0.5rem;
            transition: all 0.2s;
        }
        .add-row-btn:hover {
            background: #eff6ff;
            border-style: solid;
        }
    </style>

    <div class="main-content">
        <div class="container-fluid">
            <div class="voucher-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold m-0" style="color: var(--text-dark);">Receipt Voucher</h3>
                    <p class="text-muted m-0" style="font-size: 0.85rem;">Record payments received from customers or vendors</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary px-3 py-2" style="font-size: 0.9rem;">{{ $nextRvid }}</span>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('recepit.vochers.store') }}" method="POST" id="receiptForm">
                        @csrf
                        <input type="hidden" name="rvid" value="{{ $nextRvid }}">

                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Receipt Date</label>
                                <input type="date" name="receipt_date" class="form-control" value="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Entry Date</label>
                                <input type="date" name="entry_date" class="form-control" value="{{ now()->toDateString() }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Voucher Remarks</label>
                                <input type="text" name="remarks" class="form-control" placeholder="Overall voucher description...">
                            </div>
                        </div>

                        <div class="row g-4 mb-4" style="background: #f8fafc; padding: 1.5rem; border-radius: 12px; border: 1px solid #f1f5f9;">
                            <div class="col-md-3">
                                <label class="form-label">Party Type</label>
                                <select name="vendor_type" class="form-select" id="vendor_type" required>
                                    <option value="">Select Type</option>
                                    @foreach ($AccountHeads as $head)
                                        <option value="{{ $head->id }}">{{ $head->name }}</option>
                                    @endforeach
                                    <option value="customer">Customer</option>
                                    <option value="walkin">Walkin Customer</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Select Party / Account</label>
                                <select name="vendor_id" class="form-select" id="vendor_id" required>
                                    <option value="">First Select Type</option>
                                </select>
                                <div id="party_balance_info" class="party-info-badge d-none">
                                    Current Balance: <span id="current_bal_display" class="fw-bold">0.00</span>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Code / Tel</label>
                                <input type="text" name="tel" id="tel" class="form-control bg-white" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Previous Balance</label>
                                <input type="number" name="bal" id="bal" class="form-control fw-bold bg-white" readonly>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-custom border" id="voucherTable">
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">Narration</th>
                                        <th style="width: 15%;">Reference#</th>
                                        <th style="width: 20%;">Bank/Cash Head</th>
                                        <th style="width: 20%;">Specific Account</th>
                                        <th style="width: 15%;" class="text-end">Amount</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="voucher-row">
                                        <td>
                                            <div class="narration-group">
                                                <select name="narration_id[]" class="form-select narrationSelect">
                                                    <option value="">+ Add New Narration</option>
                                                    @foreach ($narrations as $id => $name)
                                                        <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="narration_text[]" class="form-control narrationInput mt-2" 
                                                       placeholder="Type narration here..." style="display:none;">
                                            </div>
                                        </td>
                                        <td><input name="reference_no[]" type="text" class="form-control" placeholder="Ref/Cheque#"></td>
                                        <td>
                                            <select name="row_account_head[]" class="form-select rowAccountHead">
                                                <option value="">Select Head</option>
                                                @foreach ($AccountHeads as $head)
                                                    <option value="{{ $head->id }}">{{ $head->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="row_account_id[]" class="form-select rowAccountSub" required>
                                                <option value="">Select Account</option>
                                            </select>
                                        </td>
                                        <td><input name="amount[]" type="number" step="0.01" class="form-control text-end amount fw-bold" placeholder="0.00" required></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm removeRow"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="add-row-btn" id="addRowBtn">
                                <i class="bi bi-plus-lg me-1"></i> Add Another Payment Row
                            </button>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-7">
                                <div class="p-3 border rounded-3 bg-light">
                                    <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-1"></i> Quick Tips</h6>
                                    <ul class="m-0 ps-3 text-muted" style="font-size: 0.8rem;">
                                        <li>You can receive payment into multiple accounts (e.g. part Cash, part Bank).</li>
                                        <li>Recording a receipt will automatically reduce the Customer's outstanding balance.</li>
                                        <li>If you type a new narration, it will be saved for future use.</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="total-section border">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted fw-600">Total Received:</span>
                                        <h4 class="m-0 fw-bold text-primary">Rs. <span id="totalAmountDisplay">0.00</span></h4>
                                        <input type="hidden" name="total_amount" id="totalAmount">
                                    </div>
                                    <div class="d-grid gap-2 mt-3">
                                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                            <i class="bi bi-check2-all me-1"></i> Confirm & Save Voucher
                                        </button>
                                        <a href="{{ route('all-recepit-vochers') }}" class="btn btn-outline-secondary">
                                            <i class="bi bi-list-ul me-1"></i> View All Receipts
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Narration Toggle
            $(document).on('change', '.narrationSelect', function() {
                let $input = $(this).siblings('.narrationInput');
                if ($(this).val() === '') {
                    $input.show().attr('required', true).focus();
                } else {
                    $input.hide().removeAttr('required').val('');
                }
            });

            // Type change -> fetch parties
            $('#vendor_type').on('change', function() {
                let type = $(this).val();
                let $partySelect = $('#vendor_id');
                
                $('#tel').val('');
                $('#bal').val(0);
                $('#party_balance_info').addClass('d-none');
                
                $partySelect.empty().append('<option value="" disabled selected>Loading...</option>');

                if (type === 'vendor' || type === 'customer' || type === 'walkin') {
                    $.get('{{ route('party.list') }}?type=' + type, function(data) {
                        $partySelect.empty().append('<option value="" disabled selected>Select Party</option>');
                        data.forEach(function(item) {
                            $partySelect.append(`<option value="${item.id}" data-bal="${item.closing_balance || 0}" data-mobile="${item.mobile || ''}">${item.text}</option>`);
                        });
                    });
                } else if (type) {
                    $.get('{{ url('get-accounts-by-head') }}/' + type, function(data) {
                        $partySelect.empty().append('<option value="" disabled selected>Select Account</option>');
                        data.forEach(function(acc) {
                            $partySelect.append(`<option value="${acc.id}" data-code="${acc.account_code}" data-bal="${acc.opening_balance}">${acc.title} (${acc.account_code})</option>`);
                        });
                    });
                }
            });

            // Party select -> show balance info
            $('#vendor_id').on('change', function() {
                let $selected = $(this).find(':selected');
                let bal = parseFloat($selected.data('bal')) || 0;
                let mobile = $selected.data('mobile') || $selected.data('code') || '';
                
                $('#bal').val(bal);
                $('#tel').val(mobile);
                
                $('#current_bal_display').text(bal.toLocaleString(undefined, {minimumFractionDigits: 2}));
                $('#current_bal_display').removeClass('balance-positive balance-negative').addClass(bal >= 0 ? 'balance-positive' : 'balance-negative');
                $('#party_balance_info').removeClass('d-none');
            });

            // Account Head change -> fetch sub accounts
            $(document).on('change', '.rowAccountHead', function() {
                let headId = $(this).val();
                let $subSelect = $(this).closest('tr').find('.rowAccountSub');
                
                $subSelect.html('<option value="">Loading...</option>');
                
                if (headId) {
                    $.get('{{ url('get-accounts-by-head') }}/' + headId, function(res) {
                        let html = '<option value="">Select Account</option>';
                        res.forEach(acc => {
                            html += `<option value="${acc.id}">${acc.title}</option>`;
                        });
                        $subSelect.html(html);
                    });
                } else {
                    $subSelect.html('<option value="">Select Account</option>');
                }
            });

            // Calculation
            $(document).on('input', '.amount', function() {
                calculateTotals();
            });

            function calculateTotals() {
                let total = 0;
                $('.amount').each(function() {
                    total += parseFloat($(this).val()) || 0;
                });
                $('#totalAmount').val(total.toFixed(2));
                $('#totalAmountDisplay').text(total.toLocaleString(undefined, {minimumFractionDigits: 2}));
            }

            // Add/Remove Rows
            $('#addRowBtn').on('click', function() {
                let $template = $('.voucher-row').first().clone();
                $template.find('input').val('');
                $template.find('.narrationInput').hide();
                $template.find('.rowAccountSub').html('<option value="">Select Account</option>');
                $('#voucherTable tbody').append($template);
            });

            $(document).on('click', '.removeRow', function() {
                if ($('.voucher-row').length > 1) {
                    $(this).closest('tr').remove();
                    calculateTotals();
                } else {
                    alert("At least one payment row is required.");
                }
            });
            
            // Form validation
            $('#receiptForm').on('submit', function() {
                let total = parseFloat($('#totalAmount').val()) || 0;
                if (total <= 0) {
                    alert("Total amount must be greater than zero.");
                    return false;
                }
                return true;
            });
        });
    </script>
@endsection
