@extends('admin_panel.layout.app')

@section('content')
    @can('purchase.invoice')
        <div class="main-content" style="background-color: #f4f7f6; min-height: 100vh; padding: 20px;">
            <div class="container mt-2">

                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <h4 class="text-secondary fw-light">Purchase <span class="fw-bold text-dark">Voucher</span></h4>
                    <div>
                        <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-printer me-2"></i> PRINT INVOICE
                        </button>
                    </div>
                </div>

                <div class="card shadow-lg border-0" style="border-radius: 0; position: relative; overflow: hidden;">

                    <div
                        style="position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: linear-gradient(to bottom, #2c3e50, #4ca1af);">
                    </div>

                    <div class="card-body p-0">
                        <div class="p-5 pb-4">
                            <div class="row">
                                <div class="col-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-dark text-white d-flex align-items-center justify-content-center rounded text-uppercase"
                                            style="width: 50px; height: 50px; font-size: 24px; font-weight: bold;">
                                            {{ strtoupper(substr($purchase->branch->name ?? 'W', 0, 1)) }}
                                        </div>
                                        <div class="ms-3">
                                            <h4 class="mb-0 fw-bold text-uppercase letter-spacing-1">
                                                {{ $purchase->branch->name ?? 'Branch Name' }}</h4>
                                            {{-- <span class="text-muted small">Tax ID: {{ rand(10000, 99999) }}-ERP</span> --}}
                                        </div>
                                    </div>
                                    <p class="text-muted small w-75">
                                        <i class="bi bi-geo-alt-fill me-1"></i>
                                        {{ $purchase->branch->address ?? 'Address not available' }}<br>
                                        <i class="bi bi-telephone-fill me-1"></i> {{ $purchase->branch->number ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-6 text-end">
                                    <h1 class="display-6 fw-bold text-primary mb-0"
                                        style="opacity: 0.1; position: absolute; right: 50px; top: 40px;">ORIGINAL</h1>
                                    <div class="mb-3">
                                        <span class="badge bg-soft-primary text-primary px-3 py-2 uppercase">Purchase
                                            Invoice</span>
                                    </div>
                                    <div class="text-dark">
                                        <p class="mb-1"><strong>Invoice No:</strong> <span
                                                class="text-primary">{{ $purchase->invoice_no }}</span></p>
                                        <p class="mb-0"><strong>Date:</strong>
                                            {{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-0 border-top border-bottom">
                            <div class="col-md-6 border-end p-5">
                                <h6 class="text-uppercase text-muted fw-bold mb-3 small">Vendor Details</h6>
                                <h5 class="fw-bold mb-1 text-dark">{{ $purchase->vendor->name ?? 'N/A' }}</h5>
                                <p class="text-muted mb-0">{{ $purchase->vendor->address ?? 'N/A' }}</p>
                                <p class="text-muted mb-0 small mt-2"><strong>Contact:</strong>
                                    {{ $purchase->vendor->phone ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 p-5 bg-light-alt">
                                <h6 class="text-uppercase text-muted fw-bold mb-3 small">Deliver To</h6>
                                <h5 class="fw-bold mb-1 text-dark">{{ $purchase->warehouse->warehouse_name ?? 'N/A' }}</h5>
                                <p class="text-muted mb-0 small">{{ $purchase->warehouse->location ?? 'N/A' }}</p>
                                <div class="mt-3">
                                    <span class="small text-muted">Status:</span>
                                    <span class="badge rounded-pill bg-success px-3">Received</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-0">
                            <table class="table table-borderless mb-0 erp-table">
                                <thead>
                                    <tr>
                                        <th class="ps-5">#</th>
                                        <th>Item Description</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end pe-5">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchase->items as $index => $item)
                                        <tr class="border-bottom">
                                            <td class="ps-5 text-muted">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $item->product->item_name ?? 'N/A' }}</div>
                                                <small class="text-muted">Unit: {{ $item->unit }}</small>
                                            </td>
                                            <td class="text-center fw-bold">{{ $item->qty }}</td>
                                            <td class="text-end">{{ number_format($item->price, 2) }}</td>
                                            <td class="text-end text-danger">-{{ number_format($item->item_discount, 2) }}</td>
                                            <td class="text-end pe-5 fw-bold text-dark">
                                                {{ number_format($item->line_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="p-5">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="border rounded p-3 bg-light">
                                        <p class="small fw-bold mb-1 text-uppercase">Payment Remarks:</p>
                                        <p class="small text-muted mb-0">Please mention invoice number while processing
                                            payments. All disputes are subject to local jurisdiction.</p>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Sub Total</span>
                                        <span class="fw-bold">{{ number_format($purchase->subtotal, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Extra Cost (+)</span>
                                        <span class="fw-bold text-info">{{ number_format($purchase->extra_cost, 2) }}</span>
                                    </div>
                                   <div class="d-flex justify-content-between mt-3 p-3 bg-dark shadow-sm rounded" style="background-color: #212529 !important; color: #ffffff !important;">
    <span class="h5 mb-0 fw-light text-white" style="color: #ffffff !important;">Net Amount</span>
    <span class="h5 mb-0 fw-bold text-white" style="color: #ffffff !important;">{{ number_format($purchase->net_amount, 2) }}</span>
</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-light p-4 text-center border-top">
                            <p class="small text-muted mb-0">Software Generated Invoice - Powered by ERP Solutions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Professional Styling */
            .bg-soft-primary {
                background-color: #e3f2fd;
            }

            .bg-light-alt {
                background-color: #fafbfc;
            }

            .letter-spacing-1 {
                letter-spacing: 1px;
            }

            .erp-table thead th {
                background-color: #f8f9fa;
                color: #6c757d;
                font-weight: 700;
                text-transform: uppercase;
                font-size: 11px;
                padding-top: 15px;
                padding-bottom: 15px;
            }

            .erp-table tbody td {
                padding-top: 18px;
                padding-bottom: 18px;
                font-size: 14px;
            }

            @media print {
                .no-print {
                    display: none !important;
                }

                .main-content {
                    background: #fff !important;
                    padding: 0 !important;
                }

                .card {
                    box-shadow: none !important;
                    border: 1px solid #eee !important;
                }

                .bg-dark {
                    background-color: #000 !important;
                    color: #fff !important;
                    -webkit-print-color-adjust: exact;
                }

                .text-white {
                    color: #fff !important;
                }
            }
        </style>
    @else
        <div class="container py-5">
            <div class="text-center p-5 shadow-sm bg-white rounded">
                <h2 class="text-danger fw-bold">403</h2>
                <p class="lead">Unauthorized Access</p>
            </div>
        </div>
    @endcan
@endsection
