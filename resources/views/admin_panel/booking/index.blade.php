@php
    //     echo "<pre>";
    //     print_r($bookings);
    //     echo "<pre>";
    // dd();
@endphp

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

        .container-fluid {
            padding-left: 0;
            padding-right: 0;
            width: 100%;
            max-width: 100%;
        }

        .card {
            margin-left: 0;
            margin-right: 0;
            width: 100%;
        }

        .card-body {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            width: 100%;
            margin-bottom: 0;
        }

        .table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8f9fa;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
            white-space: nowrap;
            padding: 0.5rem;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .btn {
            font-size: 0.85rem;
            padding: 0.35rem 0.5rem;
        }

        .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.4rem;
        }

        .card-header {
            padding: 1rem;
        }

        .card-header h5 {
            font-size: 1.1rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .card {
                margin-top: 0.5rem;
            }

            .card-header {
                flex-direction: column;
                gap: 0.5rem;
            }

            .card-header h5 {
                font-size: 1rem;
            }

            .table {
                font-size: 0.75rem;
            }

            .table thead th {
                font-size: 0.7rem;
                padding: 0.3rem;
            }

            .table td {
                font-size: 0.7rem;
                padding: 0.3rem;
            }

            .btn {
                font-size: 0.7rem;
                padding: 0.2rem 0.3rem;
            }

            .btn-sm {
                font-size: 0.65rem;
                padding: 0.15rem 0.25rem;
            }
        }

        @media (max-width: 576px) {
            .table {
                font-size: 0.65rem;
            }

            .table thead th {
                font-size: 0.6rem;
                padding: 0.25rem;
            }

            .table td {
                font-size: 0.6rem;
                padding: 0.25rem;
            }

            .btn {
                font-size: 0.65rem;
                padding: 0.15rem 0.25rem;
            }
        }
    </style>

    <div class="container-fluid">
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0">BOOKINGS</h5>
                <span class="fw-bold text-dark">
                    <a href="{{ route('bookings.create') }}" class="btn btn-primary">Add Booking</a>
                </span>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Invoice No</th>
                                <th>Party Type</th>
                                <th>Quantity</th>
                                <th>Sub Total</th>
                                <th>Discount %</th>
                                <th>Discount Amount</th>
                                <th>Total Balance</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                <tr>
                                    <td>{{ $booking->id }}</td>
                                    <!-- Customer Name from relation -->
                                    <td>{{ $booking->customer->customer_name ?? 'N/A' }}</td>
                                    <td>{{ $booking->invoice_no }}</td>
                                    <td>{{ $booking->party_type }}</td>
                                    <td>{{ $booking->quantity ?? 0 }}</td>
                                    <td>{{ number_format($booking->sub_total1, 2) }}</td>
                                    <td>{{ number_format($booking->items->sum('discount_percent'), 2) }}</td>
                                    <td>{{ number_format($booking->items->sum('discount_amount'), 2) }}</td>
                                    <td>{{ number_format($booking->total_balance, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($booking->created_at)->format('d-m-Y') }}</td>
                                    <td>
                                        <span
                                            class="badge 
            {{ ($booking->status ?? 'pending') == 'pending'
                ? 'bg-warning'
                : (($booking->status ?? '') == 'approved'
                    ? 'bg-success'
                    : 'bg-primary') }}">
                                            {{ ucfirst($booking->status ?? 'pending') }}
                                        </span>
                                    </td>


                                    <td>
                                        <a href="{{ route('sale.invoice', $booking->id) }}" target="_blank"
                                            class="btn btn-sm btn-outline-secondary">Receipt</a>
                                        <a href="{{ route('sales.from.booking', $booking->id) }}"
                                            class="btn btn-sm btn-success">Confirm</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection
