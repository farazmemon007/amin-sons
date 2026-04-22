@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <div class="page-header row mb-3">
                <div class="page-title col-lg-6">
                    <h4>Sale Report</h4>
                    <h6>View Sales by date range with details</h6>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <form id="SaleFilterForm" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-primary w-100">Search</button>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" id="btnExportCsv" class="btn btn-danger">Export CSV</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="loader" style="display:none;text-align:center;margin-bottom:10px;">
                        <div class="spinner-border" role="status"></div>
                    </div>

                    <div class="table-responsive">
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-sm" id="saleReport">
                                <thead class="bg-gray">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        @php
                                            $showBranch = false;
                                            if (Auth::check()) {
                                                $user = Auth::user();
                                                if ($user->hasRole('super admin')) {
                                                    $showBranch = true;
                                                } elseif ($user->can('report.sale.branch.view')) {
                                                    $showBranch = true;
                                                } else {
                                                    // Check per-branch grant permissions like report.sale.branch.view.{id}
                                                    if (isset($branches) && $branches->count() > 0) {
                                                        foreach ($branches as $b) {
                                                            if ($user->can('report.sale.branch.view.' . $b->id)) {
                                                                $showBranch = true;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        @endphp
                                        @if($showBranch)
                                            <th>Branch</th>
                                        @endif
                                        <th>Invoice</th>
                                        <th>Customer</th>
                                        <th>Products</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Amount</th>
                                        <th>Total Net</th>
                                        <th>Returns</th>
                                    </tr>
                                </thead>
                                <tbody id="saleBody"></tbody>
                                <tfoot>
                                    <tr class="fw-bold bg-light">
                                        <td colspan="5" class="text-end">Grand Total:</td>
                                        <td id="grandQty">0.00</td>
                                        <td>-</td>
                                        <td id="grandDiscount">0.00</td>
                                        <td id="grandAmount">0.00</td>
                                        <td id="grandNet">0.00</td>
                                        <td id="grandReturn">0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css" />
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).on('click', '#btnSearch', function() {
        let start = $('#start_date').val();
        let end = $('#end_date').val();

        $("#loader").show();
        $.ajax({
            url: "{{ route('report.sale.fetch') }}",
            type: "GET",
            data: {
                start_date: start,
                end_date: end
            },
            success: function(res) {
                $("#loader").hide();
                let html = "";
                let grandQty = 0,
                    grandAmount = 0,
                    grandNet = 0,
                    grandReturn = 0,
                    grandDiscount = 0;

                res.forEach((sale, i) => {
                    let rowQty = 0;
                    let rowAmount = 0;
                    let rowDiscount = 0;
                    let productsHtml = "";

                    // ============= SALE ITEMS BREAKDOWN =============
                    if (sale.items && sale.items.length > 0) {
                        sale.items.forEach((item, idx) => {
                            productsHtml += `<strong>${item.product_name}</strong> (Code: ${item.product_code})<br>`;
                            productsHtml += `&nbsp;&nbsp;Qty: ${item.qty.toFixed(2)} | Price: ${item.price.toFixed(2)} | Amount: ${item.amount.toFixed(2)}<br>`;
                            if (item.discount_amount > 0) {
                                productsHtml += `&nbsp;&nbsp;Discount: ${item.discount_amount.toFixed(2)}<br>`;
                            }
                        });

                        rowQty = sale.total_qty;
                        rowAmount = sale.total_items_amount;
                        rowDiscount = sale.discount_amount;
                    }

                    // ============= SALES RETURNS BREAKDOWN =============
                    let returnHtml = "";
                    if (sale.returns && sale.returns.length > 0) {
                        sale.returns.forEach(r => {
                            returnHtml += `${r.product} (Qty: ${r.qty})<br>Amount: ${parseFloat(r.total_net).toFixed(2)}<br>`;
                        });
                        grandReturn += sale.total_returns_amount;
                    } else {
                        returnHtml = "-";
                    }

                    // ============= ADD ROW TO TABLE =============
                    html += `<tr>
                        <td>${i+1}</td>
                        <td>${new Date(sale.created_at).toLocaleDateString('en-GB')}</td>
                        <td><strong>${sale.branch_name}</strong></td>
                        <td><strong>${sale.invoice_no}</strong></td>
                        <td>
                            <strong>${sale.customer_name}</strong><br>
                            <small>${sale.address ?? '-'}</small><br>
                            <small>${sale.tel ?? '-'}</small>
                        </td>
                        <td>${productsHtml}</td>
                        <td class="text-end">${rowQty.toFixed(2)}</td>
                        <td class="text-end">-</td>
                        <td class="text-end">${rowDiscount.toFixed(2)}</td>
                        <td class="text-end">${rowAmount.toFixed(2)}</td>
                        <td class="text-end"><strong>${sale.total_net.toFixed(2)}</strong></td>
                        <td>
                            <small>${returnHtml}</small>
                        </td>
                    </tr>`;

                    // ============= ACCUMULATE TOTALS =============
                    grandQty += rowQty;
                    grandAmount += rowAmount;
                    grandDiscount += rowDiscount;
                    grandNet += parseFloat(sale.total_net);
                });

                // ============= POPULATE TABLE & TOTALS =============
                $('#saleBody').html(html);
                $('#grandQty').text(grandQty.toFixed(2));
                $('#grandDiscount').text(grandDiscount.toFixed(2));
                $('#grandAmount').text(grandAmount.toFixed(2));
                $('#grandNet').text(grandNet.toFixed(2));
                $('#grandReturn').text(grandReturn.toFixed(2));
            },
            error: function(xhr, status, error) {
                $("#loader").hide();
                alert('Error loading report: ' + error);
            }
        });
    });


    // Ensure DOM is loaded
    $(document).ready(function() {
        // ✅ ERP STANDARD: Auto-fetch report on page load with default date range
        $('#btnSearch').trigger('click');
        
        // CSV export
        $(document).on('click', '#btnExportCsv', function() {
            let csv = [];
            
            // Add headers
            let headers = [];
            $("#saleReport thead th").each(function() {
                headers.push('"' + $(this).text().trim() + '"');
            });
            csv.push(headers.join(","));

            // Add rows
            $("#saleReport tbody tr").each(function() {
                let row = [];
                $(this).find('td').each(function() {
                    let cellHtml = $(this).html();
                    let cellText = cellHtml
                        .replace(/<br\s*\/?>/gi, " | ")
                        .replace(/<strong\s*\/?>/gi, "")
                        .replace(/<\/strong>/gi, "")
                        .replace(/<small\s*\/?>/gi, "")
                        .replace(/<\/small>/gi, "")
                        .replace(/&nbsp;/gi, " ")
                        .replace(/<[^>]*>/g, "")
                        .trim();
                    row.push('"' + cellText.replace(/"/g, '""') + '"');
                });
                csv.push(row.join(","));
            });

            // Add grand totals
            let totalRow = [];
            $("#saleReport tfoot tr td").each(function() {
                let cellText = $(this).text().trim();
                totalRow.push('"' + cellText.replace(/"/g, '""') + '"');
            });
            csv.push(totalRow.join(","));

            let csvString = csv.join("\n");
            let blob = new Blob([csvString], {
                type: 'text/csv;charset=utf-8;'
            });

            let link = document.createElement("a");
            if (link.download !== undefined) {
                let url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "sale_report_" + new Date().toISOString().split('T')[0] + ".csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
    });
</script>