@extends('admin_panel.layout.app')

@section('content')
<style>
    :root {
        --coa-navy: #1e3a5f;
        --coa-navy-dark: #0f1f38;
        --coa-navy-light: #2c5282;
        --coa-gold: #c8973a;
        --coa-emerald: #0d9f6e;
        --coa-border: #e2e8f0;
    }

    .rpt-wrapper {
        padding: 12px 0 30px 0;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .rpt-header-bar {
        background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 60%, var(--coa-navy-light) 100%);
        border-radius: 10px;
        padding: 16px 22px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        box-shadow: 0 4px 15px rgba(15, 31, 56, 0.15);
        margin-bottom: 18px;
    }

    .rpt-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--coa-gold);
        border: 1px solid rgba(200, 151, 58, 0.3);
        flex-shrink: 0;
    }

    .rpt-header-title {
        font-size: 18px;
        font-weight: 800;
        color: #ffffff !important;
        margin: 0;
        line-height: 1.2;
    }

    .rpt-header-sub {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.85);
        margin-top: 3px;
    }

    .f-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        letter-spacing: 0.03em;
        margin-bottom: 4px;
        display: block;
    }

    #stockTable th {
        background: #0f1f38 !important;
        color: #ffffff !important;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 10px 8px;
        border: 1px solid #1e3a5f;
    }
</style>

<div class="main-content">
    <div class="rpt-wrapper">
        <div class="container-fluid px-2">

            {{-- 1. Corporate Header Bar --}}
            <div class="rpt-header-bar">
                <div class="d-flex align-items-center gap-3">
                    <div class="rpt-header-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <h4 class="rpt-header-title">Item Stock Report</h4>
                        <div class="rpt-header-sub">
                            <span><i class="fas fa-warehouse mr-1" style="color: var(--coa-gold);"></i> Track opening, purchased, sold, reserved & balance per product &mdash; Ameen & Sons Corporate ERP</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="waShareBtn" onclick="shareWhatsApp()" class="btn btn-sm btn-outline-light font-weight-bold" style="background: rgba(37, 211, 102, 0.2); border-color: #25D366; color: #25D366;">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </button>
                    <button type="button" onclick="showExportOptions()" class="btn btn-sm btn-light font-weight-bold text-dark border">
                        <i class="fas fa-download mr-1 text-primary"></i> Export
                    </button>
                </div>
            </div>

            {{-- 2. Filter Card --}}
            <div class="card shadow-sm mb-3 border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;">
                <div class="card-body p-3">
                    <form id="stockFilterForm" class="row g-2 align-items-end mb-0">
                        {{-- Branch Selector (Super Admin Only) --}}
                        @if($isSuperAdmin)
                        <div class="col-md-3">
                            <label class="f-label">Branch</label>
                            <select name="branch_id" id="branch_id" class="form-control form-control-sm" style="height: 38px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                                @foreach($userBranches as $branch)
                                    <option value="{{ $branch->id }}" @selected($branch->id == $selectedBranchId)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="f-label">Product</label>
                            <select name="product_id" id="product_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Products --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->item_code }} - {{ $prod->item_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-search mr-1"></i> Search Stock
                            </button>
                        </div>
                        @else
                        {{-- Non-Admin User: Branch Display Only --}}
                        <div class="col-md-3">
                            <label class="f-label">Branch</label>
                            <div class="form-control form-control-sm bg-light font-weight-bold" style="height: 38px; display: flex; align-items: center; border: 1.5px solid #cbd5e1;">
                                {{ $userBranches[0]?->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-5">
                            <label class="f-label">Product</label>
                            <select name="product_id" id="product_id" class="form-control form-control-sm select2">
                                <option value="all">-- All Products --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->item_code }} - {{ $prod->item_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="button" id="btnSearch" class="btn btn-sm btn-primary w-100 font-weight-bold" style="height: 38px; border-radius: 6px; background: var(--coa-navy); border-color: var(--coa-navy);">
                                <i class="fas fa-search mr-1"></i> Search Stock
                            </button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- 3. Report Table Card --}}
            <div class="card shadow-sm border-0" style="border-radius: 9px; border: 1px solid var(--coa-border) !important;" id="reportContent">
                <div class="card-body p-3">
                    <div id="loader" style="display:none;text-align:center;padding:30px;">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2 small font-weight-bold">Fetching stock metrics...</p>
                    </div>

                    <div class="table-responsive">
                        <table id="stockTable" class="table table-bordered mb-0" style="font-size: 12.5px; width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 3%;">#</th>
                                    <th style="width: 9%;">Item Code</th>
                                    <th>Item Name</th>
                                    <th class="text-end" style="width: 7%;">Opening</th>
                                    <th class="text-end" style="width: 7%;">Purchased</th>
                                    <th class="text-end" style="width: 8%;">Purch. Value</th>
                                    <th class="text-end" style="width: 7%;">Sold Qty</th>
                                    <th class="text-end" style="width: 8%;">Sold Value</th>
                                    <th class="text-end" style="width: 7%;">Reserved</th>
                                    <th class="text-end" style="width: 7%;">Balance</th>
                                    <th class="text-end" style="width: 7%;">Price</th>
                                    <th class="text-end" style="width: 9%;">Stock Value</th>
                                    <th class="text-center" style="width: 8%;">Warehouses</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                                <!-- Filled by AJAX -->
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold bg-light" style="font-family: monospace; font-size: 13px;">
                                    <th colspan="11" class="text-end font-weight-bold" style="font-family: sans-serif;">Grand Stock Value:</th>
                                    <th class="text-end text-success font-weight-bold" id="grandStockValue">0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>

            <!-- Warehouse Breakdown Modal -->
            <div class="modal fade" id="warehouseModal" tabindex="-1" role="dialog" aria-labelledby="warehouseModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow" style="border-radius: 10px; overflow: hidden;">
                        <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--coa-navy-dark) 0%, var(--coa-navy) 100%);">
                            <h6 class="modal-title font-weight-bold mb-0" id="warehouseModalLabel">
                                <i class="fas fa-warehouse mr-1" style="color: var(--coa-gold);"></i> Warehouse Stock Breakdown
                            </h6>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <h6 id="modalProductName" class="mb-3"></h6>
                            <table class="table table-bordered table-hover" id="warehouseTable">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th>Warehouse Name</th>
                                        <th>Location</th>
                                        <th class="text-end">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody id="warehouseTableBody">
                                    <!-- Filled dynamically -->
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('js')

<script>
$(document).ready(function() {
    var warehouseDataStore = {}; // ✅ Global storage for warehouse data
    
    var stockTable = $('#stockTable').DataTable({
        paging: true,
        searching: true,
        info: true,
        ordering: true,
        columnDefs: [
            { orderable: false, targets: -1 } // Disable sorting on action column
        ]
    });

    function renderRows(rows, grandTotal) {
        // Destroy and recreate DataTable for clean rendering
        if ($.fn.DataTable.isDataTable('#stockTable')) {
            stockTable.destroy();
        }
        
        // Clear the table body
        $('#reportBody').html('');
        warehouseDataStore = {}; // Reset warehouse data

        let hasNegativeStock = false;

        rows.forEach(function(r, idx) {
            // ============= STORE WAREHOUSE DATA =============
            let dataKey = 'product_' + r.id;
            warehouseDataStore[dataKey] = {
                itemCode: r.item_code,
                itemName: r.item_name,
                warehouses: r.warehouse_breakdown || []
            };

            // ============= BUILD WAREHOUSE BREAKDOWN HTML =============
            let warehouseHtml = '';
            if (r.warehouse_breakdown && r.warehouse_breakdown.length > 0) {
                warehouseHtml = `<button type="button" class="btn btn-sm btn-info warehouse-btn" data-product-id="${r.id}">
                    View <span class="badge badge-light">${r.warehouse_breakdown.length}</span>
                </button>`;
            } else {
                warehouseHtml = '<span class="text-muted">No breakdown</span>';
            }

            // ============= NEGATIVE STOCK VISUAL LOGIC =============
            let balance     = parseFloat(r.balance);
            let stockValue  = parseFloat(r.stock_value);
            let isNegative  = balance < 0;

            if (isNegative) hasNegativeStock = true;

            let balanceHtml = isNegative
                ? `<strong class="text-danger">${balance.toFixed(2)} <span class="badge badge-danger" title="Negative Stock — sold more than available">⚠ Negative</span></strong>`
                : `<strong>${balance.toFixed(2)}</strong>`;

            let stockValueHtml = isNegative
                ? `<strong class="text-danger">Rs. ${stockValue.toFixed(2)}</strong>`
                : `<strong>Rs. ${stockValue.toFixed(2)}</strong>`;

            let rowStyle = isNegative ? 'background-color: #fff5f5;' : '';

            // ============= BUILD TABLE ROW =============
            let row = `
                <tr style="${rowStyle}">
                    <td>${idx + 1}${isNegative ? ' <span class="text-danger" title="Negative Stock">⚠</span>' : ''}</td>
                    <td><strong>${r.item_code}</strong></td>
                    <td>${r.item_name}</td>
                    <td class="text-end">${parseFloat(r.initial_stock).toFixed(2)}</td>
                    <td class="text-end">${parseFloat(r.purchased).toFixed(2)}</td>
                    <td class="text-end">Rs. ${parseFloat(r.purchase_amount).toFixed(2)}</td>
                    <td class="text-end">${parseFloat(r.sold).toFixed(2)}</td>
                    <td class="text-end">Rs. ${parseFloat(r.sale_amount).toFixed(2)}</td>
                    <td class="text-end"><span class="badge bg-warning text-dark" title="Booked in sales but not yet physically delivered via gatepass">${parseFloat(r.reserved_qty).toFixed(2)} pending</span></td>
                    <td class="text-end">${balanceHtml}</td>
                    <td class="text-end">Rs. ${parseFloat(r.price).toFixed(2)}</td>
                    <td class="text-end">${stockValueHtml}</td>
                    <td class="text-center">${warehouseHtml}</td>
                </tr>
            `;
            
            $('#reportBody').append(row);
        });

        // Negative stock summary row
        if (hasNegativeStock) {
            $('#reportBody').append(`
                <tr class="bg-danger text-white">
                    <td colspan="13" class="text-center fw-bold py-2">
                        ⚠ One or more products have <strong>Negative Stock</strong> due to force sales. Please review and restock.
                    </td>
                </tr>
            `);
        }
        
        // Reinitialize DataTable with new data
        stockTable = $('#stockTable').DataTable({
            paging: true,
            searching: true,
            info: true,
            ordering: true,
            columnDefs: [
                { orderable: false, targets: -1 } // Disable sorting on action column
            ]
        });

        // ============= ATTACH CLICK HANDLERS TO WAREHOUSE BUTTONS =============
        $(document).on('click', '.warehouse-btn', function() {
            let productId = $(this).data('product-id');
            let dataKey = 'product_' + productId;
            
            if (warehouseDataStore[dataKey]) {
                let data = warehouseDataStore[dataKey];
                showWarehouseBreakdown(data.itemCode, data.itemName, data.warehouses);
            }
        });

        // Update grand total
        let grandTotalVal = parseFloat(grandTotal);
        let grandTotalEl  = $('#grandStockValue');
        grandTotalEl.text(grandTotalVal.toFixed(2));
        if (hasNegativeStock) {
            grandTotalEl.addClass('text-danger');
        } else {
            grandTotalEl.removeClass('text-danger');
        }
    }

    // ============= SHOW WAREHOUSE BREAKDOWN MODAL =============
    function showWarehouseBreakdown(itemCode, itemName, warehouses) {
        $('#modalProductName').text(`${itemCode} - ${itemName}`);
        $('#warehouseTableBody').html('');
        
        let totalQty = 0;
        
        if (!warehouses || warehouses.length === 0) {
            $('#warehouseTableBody').html('<tr><td colspan="3" class="text-center text-muted">No warehouse data available</td></tr>');
            $('#warehouseModal').modal('show');
            return;
        }
        
        warehouses.forEach(function(w) {
            let qty = parseFloat(w.qty || 0);
            totalQty += qty;
            let qtyClass = qty < 0 ? 'text-danger fw-bold' : '';
            let qtyLabel = qty < 0 ? ` <span class="badge badge-danger">⚠ Negative</span>` : '';

            let row = `
                <tr>
                    <td><strong>${w.warehouse_name || 'Unknown'}</strong></td>
                    <td>${w.location || '-'}</td>
                    <td class="text-end ${qtyClass}">${qty.toFixed(2)}${qtyLabel}</td>
                </tr>
            `;
            $('#warehouseTableBody').append(row);
        });
        
        // Add total row
        $('#warehouseTableBody').append(`
            <tr class="fw-bold bg-light">
                <td colspan="2" class="text-end">Total Quantity:</td>
                <td class="text-end"><strong>${totalQty.toFixed(2)}</strong></td>
            </tr>
        `);
        
        $('#warehouseModal').modal('show');
    }

    // ============= MANUAL CLOSE BUTTON HANDLERS =============
    $(document).on('click', '[data-dismiss="modal"]', function(){
        $('#warehouseModal').modal('hide');
    });

    $('#btnSearch').on('click', function() { fetchReport(); });
    $('#product_id').on('keypress', function(e){ if(e.key==='Enter'){ e.preventDefault(); fetchReport(); } });
    
    // ✅ ERP STANDARD: When super admin changes branch, reload products for that branch
    $('#branch_id').on('change', function() {
        var selectedBranchId = $(this).val();
        
        // Fetch products for the selected branch
        $.ajax({
            url: "{{ route('report.item_stock') }}",
            type: "GET",
            data: { branch_id: selectedBranchId },
            success: function(html) {
                // Extract product options from the new HTML
                var newProductOptions = $(html).find('#product_id').html();
                $('#product_id').html(newProductOptions);
                
                // Auto-fetch report with new branch
                fetchReport();
            },
            error: function() {
                console.error('Error loading products for branch');
                // Still fetch report with current branch
                fetchReport();
            }
        });
    });

    function fetchReport() {
        var productId = $('#product_id').val();
        var branchId = $('#branch_id').val() || '{{ $selectedBranchId }}'; // Use default branch if not in dropdown
        
        $('#loader').show();
        $.ajax({
            url: "{{ route('report.item_stock.fetch') }}",
            type: "POST",
            data: { 
                _token: "{{ csrf_token() }}", 
                product_id: productId,
                branch_id: branchId
            },
            success: function(response) {
                $('#loader').hide();
                if (response.data && response.data.length) {
                    renderRows(response.data, response.grand_total);
                } else {
                    renderRows([], 0);
                    alert('No data found for selected product in this branch.');
                }
            },
            error: function(xhr, status, err) {
                $('#loader').hide();
                alert('Error fetching report. See console.');
                console.error(xhr.responseText || err);
            }
        });
    }

    // Initial load
    fetchReport();

    /* ---------- WhatsApp Share ---------- */
    window.shareWhatsApp = function() {
        Swal.fire({
            title: 'Preparing WhatsApp Share...',
            text: 'Generating PDF document to share.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        var element = document.getElementById('reportContent');
        var opt = {
          margin:       0.2,
          filename:     'Item_Stock_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).outputPdf('blob').then(function(pdfBlob) {
            var file = new File([pdfBlob], opt.filename, { type: 'application/pdf' });
            
            if (navigator.canShare && navigator.canShare({ files: [file] })) {
                navigator.share({
                    title: 'Item Stock Report',
                    text: 'Please find the attached Item Stock Report.',
                    files: [file]
                }).then(() => {
                    Swal.close();
                }).catch((error) => {
                    console.log('Error sharing', error);
                    fallbackWaShare(pdfBlob, opt.filename);
                });
            } else {
                fallbackWaShare(pdfBlob, opt.filename);
            }
        });
    };

    function fallbackWaShare(pdfBlob, filename) {
        Swal.fire({
            icon: 'info',
            title: 'Share PDF via WhatsApp',
            text: 'The PDF will be downloaded now. WhatsApp will open allowing you to choose any chat. Please attach the downloaded PDF manually.',
            confirmButtonText: 'Download & Open WhatsApp'
        }).then(() => {
            var url = URL.createObjectURL(pdfBlob);
            var a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            
            var msg = "*Item Stock Report*\nPlease find the attached PDF document.";
            var waUrl = "https://wa.me/?text=" + encodeURIComponent(msg);
            window.open(waUrl, '_blank');
        });
    }

    /* ---------- Export Options & PDF ---------- */
    window.showExportOptions = function() {
        Swal.fire({
            title: 'Export Stock Report',
            text: 'Choose your preferred export format:',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-file-excel me-1"></i> Excel (CSV)',
            cancelButtonText: '<i class="fas fa-file-pdf me-1"></i> PDF',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                exportCSV();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                exportPDF();
            }
        });
    };

    window.exportPDF = function() {
        Swal.fire({
            title: 'Generating PDF...',
            text: 'Please wait while your PDF is being prepared.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        var element = document.getElementById('reportContent');
        var opt = {
          margin:       0.2,
          filename:     'Item_Stock_Report_' + new Date().toISOString().slice(0,10) + '.pdf',
          image:        { type: 'jpeg', quality: 0.98 },
          html2canvas:  { scale: 2, useCORS: true },
          jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
        };

        html2pdf().set(opt).from(element).save().then(function() {
            Swal.close();
        });
    };

    window.exportCSV = function () {
        var rows = [['Item Code','Item Name','Opening Stock','Purchased Qty','Purchased Amount','Sold Qty','Sold Amount','Reserved Qty','Balance','Price','Stock Value']];
        
        $('#reportBody tr').each(function () {
            var cells = [];
            $(this).find('td').each(function (idx) {
                if(idx === 0 || idx === 12) return; // Skip # and Action columns
                var text = $(this).text().replace(/Rs\.\s?/g, '').trim().replace(/"/g, '""');
                cells.push('"' + text + '"');
            });
            if (cells.length) rows.push(cells);
        });
        
        // Add grand total
        rows.push(['','','','','','','','','','Grand Total','"' + $('#grandStockValue').text() + '"']);

        var csv  = rows.map(function(r){return r.join(',');}).join('\n');
        var blob = new Blob(["\uFEFF" + csv], {type:'text/csv;charset=utf-8;'});
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href   = url;
        a.download = 'item_stock_report_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
    };
});
</script>
@endsection
