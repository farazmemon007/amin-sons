@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Image jaisa exact ledger look dene ke liye CSS */
    .ledger-container {
        background-color: #fff;
        padding: 20px;
    }
    .ledger-table { 
        border: 1px solid #444 !important;
        width: 100%;
        border-collapse: collapse;
    }
    .ledger-table thead th {
        background-color: #f8f9fa;
        border: 1px solid #444 !important;
        text-align: center;
        font-weight: bold;
        font-size: 13px;
        padding: 8px !important;
        color: #000;
    }
    .ledger-table tbody td {
        border: 1px solid #444 !important;
        font-size: 12px;
        padding: 6px 10px !important;
        vertical-align: middle;
        color: #000;
    }
    .ledger-table tfoot td {
        border: 1px solid #444 !important;
        font-weight: bold;
        padding: 8px !important;
        background-color: #f1f1f1;
    }
    .text-end { text-align: right; }
    .text-center { text-align: center; }
    .fw-bold { font-weight: bold; }
    .bg-dark-total {
        background-color: #e9ecef !important;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            
            <div class="page-header mb-4">
                <h4>Item Stock Ledger Report</h4>
                <p class="text-muted">Visual representation of the stock movement</p>
            </div>

            <div class="card shadow-sm">
                <div class="card-body ledger-container">
                    <div class="table-responsive">
                        <table class="table ledger-table">
                            <thead>
                                <tr>
                                    <th style="width: 100px;">Date</th>
                                    <th style="width: 110px;">V NO</th>
                                    <th style="width: 70px;">Bill</th>
                                    <th>Description</th>
                                    <th style="width: 60px;">Qty</th>
                                    <th style="width: 90px;">Rate</th>
                                    <th style="width: 110px;">Debit</th>
                                    <th style="width: 110px;">Credit</th>
                                    <th style="width: 130px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Sample Row 1 --}}
                                <tr>
                                    <td class="text-center">07-07-25</td>
                                    <td>JVR-12483</td>
                                    <td></td>
                                    <td>CASH RECEIVED</td>
                                    <td class="text-center"></td>
                                    <td class="text-end"></td>
                                    <td class="text-end"></td>
                                    <td class="text-end">25,000</td>
                                    <td class="text-end fw-bold">821,525 Dr</td>
                                </tr>
                                {{-- Sample Row 2 --}}
                                <tr>
                                    <td class="text-center">13-07-25</td>
                                    <td>JVR-12500</td>
                                    <td></td>
                                    <td>SALE INCENTIVE C/O MALIK HASSAN SAB<br><small>1018*1000</small></td>
                                    <td class="text-center"></td>
                                    <td class="text-end"></td>
                                    <td class="text-end"></td>
                                    <td class="text-end">1,018,000</td>
                                    <td class="text-end fw-bold">196,475 Cr</td>
                                </tr>
                                {{-- Sample Row 3 (Product Entry) --}}
                                <tr>
                                    <td class="text-center">18-09-25</td>
                                    <td>SIN-35509</td>
                                    <td>2411</td>
                                    <td>Smart Electric 10 Ltr ()</td>
                                    <td class="text-center">10</td>
                                    <td class="text-end">12,500</td>
                                    <td class="text-end">125,000</td>
                                    <td class="text-end"></td>
                                    <td class="text-end fw-bold">111,475 Cr</td>
                                </tr>
                                {{-- Sample Row 4 --}}
                                <tr>
                                    <td class="text-center">21-09-25</td>
                                    <td>SIN-35541</td>
                                    <td>2434</td>
                                    <td>Electric Geyser 60 Litrs ()</td>
                                    <td class="text-center">10</td>
                                    <td class="text-end">16,000</td>
                                    <td class="text-end">160,000</td>
                                    <td class="text-end"></td>
                                    <td class="text-end fw-bold">388,525 Dr</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                {{-- Summary Row --}}
                                <tr>
                                    <td colspan="6" class="text-end">Sum :</td>
                                    <td class="text-end">45,287,800</td>
                                    <td class="text-end">41,939,275</td>
                                    <td class="text-end">3,348,525 Dr</td>
                                </tr>
                                {{-- Grand Total Row --}}
                                <tr class="bg-dark-total">
                                    <td colspan="8" class="text-end fw-bold">Total</td>
                                    <td class="text-end fw-bold" style="font-size: 14px;">3,348,525 Dr</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection