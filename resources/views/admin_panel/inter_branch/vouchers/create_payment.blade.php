@extends('admin_panel.layout.app')

@section('content')
<style>
    .voucher-form-container {
        max-width: 950px;
        margin: 0 auto;
    }

    .voucher-card {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        background: #ffffff;
        overflow: hidden;
    }

    .voucher-card-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
        color: #ffffff !important;
        padding: 16px 24px;
        border-bottom: 1px solid #0f172a;
    }

    .voucher-card-header h6,
    .voucher-card-header h6 i {
        color: #ffffff !important;
        font-weight: 700 !important;
    }

    .voucher-header-badge {
        background-color: rgba(255, 255, 255, 0.15) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        font-weight: 600 !important;
    }

    .form-section-title {
        color: #1e293b;
        font-weight: 700;
        font-size: 0.88rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        margin-bottom: 14px;
        padding-bottom: 8px;
        border-bottom: 2px solid #f1f5f9;
    }

    .form-section-title i {
        color: #10b981;
        margin-right: 8px;
    }

    .form-label-compact {
        font-weight: 600;
        font-size: 0.8rem;
        color: #475569;
        margin-bottom: 5px;
        display: block;
    }

    .form-control-compact {
        height: 38px !important;
        min-height: 38px !important;
        border-radius: 6px !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 0.875rem !important;
        padding: 6px 12px !important;
        background-color: #ffffff !important;
        color: #0f172a !important;
        box-sizing: border-box !important;
    }

    .form-control-compact:focus {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15) !important;
        outline: none !important;
    }

    /* Select2 Custom Styling */
    .select2-container--bootstrap-5 .select2-selection,
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        padding: 4px 8px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        color: #0f172a !important;
        font-size: 0.875rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    .select2-dropdown {
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #10b981 !important;
    }

    .balance-badge {
        font-size: 0.8rem;
        padding: 4px 10px;
        border-radius: 4px;
        font-weight: 600;
        display: inline-block;
        margin-top: 4px;
    }
</style>

<div class="container-fluid">
    <div class="voucher-form-container py-3">
        <div class="voucher-card">
            <div class="voucher-card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fs-6"><i class="fas fa-file-invoice-dollar me-2"></i> Create Payment Voucher</h6>
                <span class="badge voucher-header-badge px-3 py-1">Inter-Branch</span>
            </div>
            <div class="card-body p-4">
                @if (session('error'))
                    <div class="alert alert-danger mb-4 rounded-2 p-3">
                        <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-4 rounded-2 p-3">
                        <strong class="d-block mb-1">Validation Errors:</strong>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('inter_branch_vouchers.store_payment') }}" method="POST">
                    @csrf

                    <!-- Section 1: Sending Branch & Account (Pay From) -->
                    <div class="mb-4">
                        <div class="form-section-title">
                            <i class="fas fa-paper-plane"></i> 1. Sending Branch & Account (Pay From)
                        </div>
                        <div class="row g-3">
                            <!-- Sending Branch -->
                            <div class="col-md-4">
                                <label class="form-label-compact">Sending Branch <span class="text-danger">*</span></label>
                                @if ($isSuperAdmin)
                                    <select name="from_branch_id" id="from_branch_id" class="form-control select2 @error('from_branch_id') is-invalid @enderror" required style="width: 100%;">
                                        <option value="">-- Select Branch --</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">
                                                🏪 {{ $branch->name ?? $branch->branch_name ?? 'Branch #' . $branch->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    @php
                                        $userBranch = $branches->firstWhere('id', $fromBranchId);
                                    @endphp
                                    <input type="text" class="form-control form-control-compact bg-light fw-bold" readonly value="🏪 {{ $userBranch->name ?? $userBranch->branch_name ?? 'Branch #' . $fromBranchId }}">
                                    <input type="hidden" name="from_branch_id" id="from_branch_id" value="{{ $fromBranchId }}">
                                @endif
                                @error('from_branch_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sending Account Head -->
                            <div class="col-md-4">
                                <label class="form-label-compact">Account Head</label>
                                <select name="from_head_id" id="from_head_id" class="form-control select2" style="width: 100%;">
                                    <option value="">-- Select Head --</option>
                                </select>
                            </div>

                            <!-- Sending Account -->
                            <div class="col-md-4">
                                <label class="form-label-compact">Sending Account <span class="text-danger">*</span></label>
                                <select name="from_account_id" id="from_account_id" class="form-control select2 @error('from_account_id') is-invalid @enderror" required style="width: 100%;">
                                    <option value="">-- Select Account --</option>
                                </select>
                                <div id="from_account_balance_container" class="mt-1" style="display: none;">
                                    <span class="badge bg-light text-primary border balance-badge" id="from_account_balance_badge">Current Balance: Rs. 0.00</span>
                                </div>
                                @error('from_account_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Receiving Branch & Account (Pay To) -->
                    <div class="mb-4">
                        <div class="form-section-title">
                            <i class="fas fa-hand-holding-usd"></i> 2. Receiving Branch & Account (Pay To)
                        </div>
                        <div class="row g-3">
                            <!-- Receiving Branch -->
                            <div class="col-md-4">
                                <label class="form-label-compact">Pay To Branch <span class="text-danger">*</span></label>
                                <select name="to_branch_id" id="to_branch_id" class="form-control select2 @error('to_branch_id') is-invalid @enderror" required style="width: 100%;">
                                    <option value="">-- Select Branch --</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">
                                            🏪 {{ $branch->name ?? $branch->branch_name ?? 'Branch #' . $branch->id }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_branch_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Receiving Account Head -->
                            <div class="col-md-4">
                                <label class="form-label-compact">Account Head</label>
                                <select name="to_head_id" id="to_head_id" class="form-control select2" style="width: 100%;">
                                    <option value="">-- Select Head --</option>
                                </select>
                            </div>

                            <!-- Receiving Account -->
                            <div class="col-md-4">
                                <label class="form-label-compact">Receiving Account <span class="text-danger">*</span></label>
                                <select name="to_account_id" id="to_account_id" class="form-control select2 @error('to_account_id') is-invalid @enderror" required style="width: 100%;">
                                    <option value="">-- Select Account --</option>
                                </select>
                                <div id="to_account_balance_container" class="mt-1" style="display: none;">
                                    <span class="badge bg-light text-success border balance-badge" id="to_account_balance_badge">Current Balance: Rs. 0.00</span>
                                </div>
                                @error('to_account_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Amount & Remarks -->
                    <div class="mb-4">
                        <div class="form-section-title">
                            <i class="fas fa-calculator"></i> 3. Payment Amount & Notes
                        </div>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label-compact">Transfer Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted fw-bold" style="border-radius: 6px 0 0 6px;">Rs.</span>
                                    <input type="number" name="amount" class="form-control form-control-compact @error('amount') is-invalid @enderror" required min="0.01" step="0.01" placeholder="0.00" style="border-radius: 0 6px 6px 0 !important; font-weight: 700;">
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-7">
                                <label class="form-label-compact">Remarks & Notes (Optional)</label>
                                <input type="text" name="remarks" class="form-control form-control-compact" placeholder="Add transaction notes or descriptions...">
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 pt-2 border-top">
                        <button type="submit" class="btn btn-success px-4 fw-bold" style="border-radius: 6px;">
                            <i class="fas fa-check-circle me-1"></i> Record Payment Voucher
                        </button>
                        <a href="{{ route('inter_branch_vouchers.index') }}" class="btn btn-secondary px-4 fw-bold" style="border-radius: 6px;">
                            Back
                        </a>
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
        // Base API URLs (dynamic and portable across localhost subfolder and live hosting)
        const API_BRANCH_HEADS = "{{ url('api/branch-heads') }}";
        const API_BRANCH_HEAD_ACCOUNTS = "{{ url('api/branch-head-accounts') }}";
        const API_ACCOUNT_BALANCE = "{{ url('api/account-balance') }}";

        // Initialize Select2
        $('.select2').select2({
            placeholder: "-- Select Option --",
            allowClear: true,
            width: '100%'
        });

        // ==========================================
        // SENDING SIDE CASCADING LOGIC
        // ==========================================
        function loadFromHeads(branchId) {
            const $headSelect = $('#from_head_id');
            const $accountSelect = $('#from_account_id');
            $('#from_account_balance_container').hide();

            if (!branchId) {
                $headSelect.html('<option value="">-- Select Head --</option>').trigger('change');
                $accountSelect.html('<option value="">-- Select Account --</option>').trigger('change');
                return;
            }

            $.ajax({
                url: `${API_BRANCH_HEADS}/${branchId}`,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    let options = '<option value="">-- Select Head --</option>';
                    if (res.heads && res.heads.length > 0) {
                        res.heads.forEach(h => {
                            options += `<option value="${h.id}">${h.name}</option>`;
                        });
                    } else {
                        options = '<option value="">No account heads setup for this branch</option>';
                    }
                    $headSelect.html(options).trigger('change');
                }
            });
        }

        function loadFromAccounts(branchId, headId) {
            const $accountSelect = $('#from_account_id');
            $('#from_account_balance_container').hide();

            if (!headId) {
                $accountSelect.html('<option value="">-- Select Account --</option>').trigger('change');
                return;
            }

            const url = branchId ? `${API_BRANCH_HEAD_ACCOUNTS}/${branchId}/${headId}` : `${API_BRANCH_HEAD_ACCOUNTS}/0/${headId}`;

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    let options = '<option value="">-- Select Account --</option>';
                    if (res.accounts && res.accounts.length > 0) {
                        res.accounts.forEach(a => {
                            options += `<option value="${a.id}">${a.title} (${a.account_code})</option>`;
                        });
                    } else {
                        options = '<option value="">No accounts under this head</option>';
                    }
                    $accountSelect.html(options).trigger('change');
                }
            });
        }

        // When From Branch Changes
        $('#from_branch_id').on('change', function() {
            loadFromHeads($(this).val());
        });

        // When From Head Changes -> Fetch Accounts under selected Head!
        $('#from_head_id').on('change', function() {
            const branchId = $('#from_branch_id').val();
            const headId = $(this).val();
            loadFromAccounts(branchId, headId);
        });

        // When From Account Changes -> Display Balance Badge
        $('#from_account_id').on('change', function() {
            const accountId = $(this).val();
            if (!accountId) {
                $('#from_account_balance_container').hide();
                return;
            }
            $.ajax({
                url: `${API_ACCOUNT_BALANCE}/${accountId}`,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        $('#from_account_balance_badge').text(`Current Balance: ${res.formatted_balance}`);
                        $('#from_account_balance_container').show();
                    }
                }
            });
        });

        // ==========================================
        // RECEIVING SIDE CASCADING LOGIC
        // ==========================================
        function loadToHeads(branchId) {
            const $headSelect = $('#to_head_id');
            const $accountSelect = $('#to_account_id');
            $('#to_account_balance_container').hide();

            if (!branchId) {
                $headSelect.html('<option value="">-- Select Head --</option>').trigger('change');
                $accountSelect.html('<option value="">-- Select Account --</option>').trigger('change');
                return;
            }

            $.ajax({
                url: `${API_BRANCH_HEADS}/${branchId}`,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    let options = '<option value="">-- Select Head --</option>';
                    if (res.heads && res.heads.length > 0) {
                        res.heads.forEach(h => {
                            options += `<option value="${h.id}">${h.name}</option>`;
                        });
                    } else {
                        options = '<option value="">No account heads setup for this branch</option>';
                    }
                    $headSelect.html(options).trigger('change');
                }
            });
        }

        function loadToAccounts(branchId, headId) {
            const $accountSelect = $('#to_account_id');
            $('#to_account_balance_container').hide();

            if (!headId) {
                $accountSelect.html('<option value="">-- Select Account --</option>').trigger('change');
                return;
            }

            const url = branchId ? `${API_BRANCH_HEAD_ACCOUNTS}/${branchId}/${headId}` : `${API_BRANCH_HEAD_ACCOUNTS}/0/${headId}`;

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    let options = '<option value="">-- Select Account --</option>';
                    if (res.accounts && res.accounts.length > 0) {
                        res.accounts.forEach(a => {
                            options += `<option value="${a.id}">${a.title} (${a.account_code})</option>`;
                        });
                    } else {
                        options = '<option value="">No accounts under this head</option>';
                    }
                    $accountSelect.html(options).trigger('change');
                }
            });
        }

        // When To Branch Changes
        $('#to_branch_id').on('change', function() {
            loadToHeads($(this).val());
        });

        // When To Head Changes -> Fetch Accounts under selected Head!
        $('#to_head_id').on('change', function() {
            const branchId = $('#to_branch_id').val();
            const headId = $(this).val();
            loadToAccounts(branchId, headId);
        });

        // When To Account Changes -> Display Balance Badge
        $('#to_account_id').on('change', function() {
            const accountId = $(this).val();
            if (!accountId) {
                $('#to_account_balance_container').hide();
                return;
            }
            $.ajax({
                url: `${API_ACCOUNT_BALANCE}/${accountId}`,
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        $('#to_account_balance_badge').text(`Current Balance: ${res.formatted_balance}`);
                        $('#to_account_balance_container').show();
                    }
                }
            });
        });

        // Initial Trigger for From Branch if pre-selected
        const initialFromBranch = $('#from_branch_id').val();
        if (initialFromBranch) {
            loadFromHeads(initialFromBranch);
        }
    });
</script>
@endsection
