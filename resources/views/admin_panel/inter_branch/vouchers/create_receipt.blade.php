@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">💵 Create Receipt Voucher</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Validation Errors:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('inter_branch_vouchers.store_receipt') }}" method="POST">
                @csrf

                <!-- Step 1: Select Sender Branch -->
                <div class="card mb-4 border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title mb-3">🏪 Step 1: Receipt Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Received From Branch:</strong></label>
                                <select name="from_branch_id" class="form-control @error('from_branch_id') is-invalid @enderror" required>
                                    <option value="">-- Select Branch --</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">
                                            🏪 {{ $branch->name ?? $branch->branch_name ?? 'Branch #' . $branch->id }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_branch_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Amount:</strong></label>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" required min="0.01" step="0.01" placeholder="0.00">
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Payment Method -->
                <div class="card mb-4 border-0">
                    <div class="card-body">
                        <h6 class="card-title mb-3">💰 Step 2: Receipt Method</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Method:</strong></label>
                                <select name="method" class="form-control @error('method') is-invalid @enderror" required>
                                    <option value="">-- Select Method --</option>
                                    <option value="cash">💵 Cash</option>
                                    <option value="bank">🏦 Bank Transfer</option>
                                    <option value="cheque">📋 Cheque</option>
                                </select>
                                @error('method')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Reference (Optional):</strong></label>
                                <input type="text" name="reference" class="form-control" placeholder="Cheque no, Bank reference, etc.">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Remarks -->
                <div class="card mb-4 border-0">
                    <div class="card-body">
                        <h6 class="card-title mb-3">📝 Step 3: Remarks (Optional)</h6>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Add any notes..."></textarea>
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-check-circle"></i> Record Receipt
                    </button>
                    <a href="{{ route('inter_branch_vouchers.index') }}" class="btn btn-secondary">
                        Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
