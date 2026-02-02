@extends('admin_panel.layout.app')
@section('content')
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .main-content {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        .main-content-inner {
            width: 100%;
            padding: 0;
            margin: 0;
        }

        .container {
            padding: 0 1rem;
            width: 100%;
            max-width: 100%;
        }

        h3 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: #333;
        }

        .form-control, .form-select {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }

        .btn {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }

        .btn-primary {
            background-color: #007bff;
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 0.4rem;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: -0.5rem;
        }

        .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6 {
            padding: 0.5rem;
            flex: 0 0 auto;
        }

        .col-md-2 {
            width: 16.666%;
        }

        .col-md-3 {
            width: 25%;
        }

        .col-md-4 {
            width: 25%;
        }

        .col-md-5 {
            width: 33.333%;
        }

        .col-md-6 {
            width: 33.333%;
        }

        .col-md-12 {
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        .button-group {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: nowrap;
            align-items: center;
        }

        .button-group .btn {
            flex: 0 1 auto;
            min-width: 140px;
            white-space: nowrap;
            padding: 0.6rem 1.2rem;
        }

        .alert {
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        /* Mobile Responsive */
        @media (max-width: 992px) {
            .col-md-2 {
                width: 33.333% !important;
            }
            
            .col-md-3, .col-md-4, .col-md-5, .col-md-6 {
                width: 50% !important;
            }

            .form-control, .form-select {
                font-size: 0.85rem;
            }

            h3 {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 0.5rem;
            }

            .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6 {
                width: 100% !important;
            }

            h3 {
                font-size: 1rem;
            }

            .form-control, .form-select {
                font-size: 0.8rem;
            }

            .button-group {
                flex-direction: row;
                gap: 0.5rem;
                flex-wrap: wrap;
            }

            .button-group .btn {
                flex: 1;
                min-width: 120px;
            }

            .btn {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }

            .mb-3 {
                margin-bottom: 0.75rem !important;
            }

            .mt-3 {
                margin-top: 0.75rem !important;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 0 0.25rem;
            }

            h3 {
                font-size: 0.9rem;
                margin-bottom: 1rem;
            }

            .form-control, .form-select {
                font-size: 0.75rem;
            }

            .btn {
                font-size: 0.75rem;
                padding: 0.35rem 0.6rem;
            }

            .form-label {
                font-size: 0.85rem;
            }

            .mb-3 {
                margin-bottom: 0.5rem !important;
            }

            .button-group {
                flex-direction: column;
                gap: 0.4rem;
            }

            .button-group .btn {
                width: 100%;
                min-width: auto;
                padding: 0.5rem 0.75rem;
            }
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <h3>Edit Customer</h3>

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

                <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="form-label">Customer ID:</label>
                            <input type="text" class="form-control" name="customer_id" readonly value="{{ $customer->customer_id }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Customer Name:</label>
                            <input type="text" class="form-control" name="customer_name" value="{{ $customer->customer_name }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">کسٹمر کا نام:</label>
                            <input type="text" class="form-control text-end" name="customer_name_ur" dir="rtl" value="{{ $customer->customer_name_ur ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Customer Type:</label>
                            <select class="form-control" name="customer_type" required>
                                <option value="Main Customer" {{ $customer->customer_type === 'Main Customer' ? 'selected' : '' }}>Main</option>
                                <option value="Walking Customer" {{ $customer->customer_type === 'Walking Customer' ? 'selected' : '' }}>Walking</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">NTN / CNIC:</label>
                            <input type="text" class="form-control" name="cnic" value="{{ $customer->cnic ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mobile:</label>
                            <input type="text" class="form-control" name="mobile" value="{{ $customer->mobile ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Zone:</label>
                            <input type="text" class="form-control" name="address" value="{{ $customer->address ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Filer Type:</label>
                            <select class="form-control" name="filer_type" required>
                                <option value="filer" {{ $customer->filer_type === 'filer' ? 'selected' : '' }}>Filer</option>
                                <option value="non filer" {{ $customer->filer_type === 'non filer' ? 'selected' : '' }}>Non Filer</option>
                                <option value="exempt" {{ $customer->filer_type === 'exempt' ? 'selected' : '' }}>Exempt</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Opening Balance:</label>
                            <input type="number" class="form-control" name="opening_balance" step="0.01" value="{{ $customer->opening_balance ?? 0 }}" required>
                            <small class="form-text text-muted">Initial balance</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><strong>Credit Limit:</strong></label>
                            <input type="number" class="form-control" name="credit_limit" step="0.01" min="0" value="{{ $customer->credit_limit ?? 0 }}" required>
                            <small class="form-text text-muted">Max credit</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Closing Balance:</label>
                            <input type="number" class="form-control" name="closing_balance" step="0.01" value="{{ $customer->closing_balance ?? 0 }}" readonly>
                            <small class="form-text text-muted">Auto-calculated</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Address:</label>
                            <textarea rows="3" class="form-control" name="address_details">{{ $customer->address_details ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="button-group">
                            <button class="btn btn-primary" type="submit">Update Customer</button>
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
