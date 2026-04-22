@extends('admin_panel.layout.app')

@section('content')
    <style>
        .gp-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .gp-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .gp-title h4 {
            margin: 0;
            font-weight: 700;
            color: #1a2332;
            font-size: 1.5rem;
        }

        .gp-title small {
            color: #6b7280;
            display: block;
            margin-top: 4px;
        }

        .gp-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border: 1px solid #1e40af;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.3);
        }

        .btn-outline {
            background: white;
            color: #2563eb;
            border: 1px solid #dbeafe;
        }

        .btn-outline:hover {
            background: #eff6ff;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2563eb;
            margin: 10px 0;
        }

        .stat-card .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
            margin: 0;
        }

        .stat-card .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .gp-table-wrapper {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        }

        .gp-table-head {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .gp-table-head h6 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a2332;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 700;
            padding: 14px 16px;
            text-align: left;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }

        tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.9rem;
            color: #374151;
        }

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        .gp-id {
            font-weight: 600;
            color: #2563eb;
        }

        .gp-customer {
            font-weight: 500;
            color: #1a2332;
        }

        .gp-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #dbeafe;
            color: #0c4a6e;
        }

        .status-completed {
            background: #dcfce7;
            color: #166534;
        }

        .gp-actions-cell {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            background: white;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .action-btn:hover {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .action-btn.danger:hover {
            background: #ef4444;
            border-color: #ef4444;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .pagination {
            margin-top: 20px;
            justify-content: center;
        }

        .pagination .page-link {
            border-radius: 6px;
            margin: 0 2px;
            border: 1px solid #dbeafe;
            color: #2563eb;
        }

        .pagination .page-link:hover {
            background: #eff6ff;
        }

        .pagination .page-item.active .page-link {
            background: #2563eb;
            border-color: #2563eb;
        }

        .badge-items {
            display: inline-block;
            background: #eef2f7;
            color: #1a2332;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>

    <div class="gp-container">
        <!-- Header Section -->
        <div class="gp-header">
            <div class="gp-title">
                <h4><i class="fas fa-receipt" style="margin-right: 10px; color: #2563eb;"></i>Outward Gate Passes</h4>
                <small>View and manage all created gate passes</small>
            </div>
            <div class="gp-actions">
                <a href="{{ route('OutwardGatepass.list') }}" class="btn-action btn-primary-custom">
                    <i class="fas fa-plus"></i> Create New
                </a>
                <a href="{{ route('OutwardGatepass.home') }}" class="btn-action btn-outline">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box-open" style="color: #2563eb;"></i></div>
                <div class="stat-label">Total Gate Passes</div>
                <div class="stat-value">{{ $stats['total'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-month" style="color: #10b981;"></i></div>
                <div class="stat-label">This Month</div>
                <div class="stat-value">{{ $stats['thisMonth'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-cubes" style="color: #f59e0b;"></i></div>
                <div class="stat-label">Total Items</div>
                <div class="stat-value">{{ $stats['totalItems'] ?? 0 }}</div>
            </div>
        </div>

        <!-- Gate Passes Table -->
        <div class="gp-table-wrapper">
            <div class="gp-table-head">
                <h6>Gate Pass Records</h6>
            </div>

            @if($gatepasses->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>GP ID</th>
                            <th>DC No</th>
                            <th>Customer</th>
                            <th>Warehouse</th>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Items</th>
                            <th>Created On</th>
                            <th>Issued By</th>
                            <th>Transport Receipt</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gatepasses as $gp)
                            <tr>
                                <td><span class="gp-id">#{{ $gp->id }}</span></td>
                                <td><strong>{{ $gp->dc_no ?? 'N/A' }}</strong></td>
                                <td>
                                    <div class="gp-customer">{{ $gp->display_customer_name ?? 'N/A' }}</div>
                                    @if($gp->is_walking_customer)
                                        <small class="badge bg-warning text-dark" style="color: #9ca3af;">Walking Customer</small>
                                    @endif
                                    <small style="color: #9ca3af;">{{ $gp->contact_person ?? '' }}</small>
                                </td>
                                <td>
                                    @if($gp->warehouse_id)
                                        <strong>{{ $gp->warehouse_name ?? 'N/A' }}</strong>
                                        <br><small class="badge bg-primary">Warehouse</small>
                                    @else
                                        <strong>{{ $gp->branch_name ?? 'N/A' }}</strong>
                                        <br><small class="badge bg-success">Branch</small>
                                    @endif
                                </td>
                                <td>{{ $gp->driver_name ?? '-' }}</td>
                                <td>{{ $gp->vehicle_number ?? '-' }}</td>
                                <td><span class="badge-items">{{ $gp->items_count }} items</span></td>
                                <td>
                                    <small>{{ $gp->created_at ? \Carbon\Carbon::parse($gp->created_at)->format('M d, Y') : '-' }}</small>
                                    <br>
                                    <small style="color: #9ca3af;">{{ $gp->created_at ? \Carbon\Carbon::parse($gp->created_at)->format('h:i A') : '' }}</small>
                                </td>
                                <td>{{ $gp->issued_by ?? '-' }}</td>
                                <td>
                                    @if($gp->transport_receipt_path)
                                        <button type="button" class="action-btn" title="View Receipt" onclick="viewTransportReceipt('{{ $gp->id }}', '{{ asset('storage/' . $gp->transport_receipt_path) }}')">
                                            <i class="fas fa-image" style="color: #10b981;"></i>
                                        </button>
                                    @else
                                        <span style="color: #9ca3af; font-size: 0.85rem;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="gp-actions-cell">
                                        <a href="{{ route('OutwardGatepass.show', $gp->id) }}" class="action-btn" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('OutwardGatepass.pdf', $gp->id) }}" class="action-btn" title="Download PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <button type="button" class="action-btn" title="Driver Receipt" onclick="showDeliveryReceipt({{ $gp->id }})">
                                            <i class="fas fa-receipt"></i>
                                        </button>
                                        <button type="button" class="action-btn" title="Upload Transport Receipt" onclick="showUploadReceiptModal({{ $gp->id }})">
                                            <i class="fas fa-upload"></i>
                                        </button>
                                        <a href="{{ route('OutwardGatepass.thermal', $gp->id) }}" class="action-btn" title="Thermal Print">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $gatepasses->links('pagination::bootstrap-4') }}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p style="font-size: 1.1rem; margin: 20px 0;">No gate passes found</p>
                    <p style="font-size: 0.9rem; color: #d1d5db;">
                        Create your first gate pass by clicking the "Create New" button above
                    </p>
                </div>
            @endif
        </div>
    </div>

<!-- ✅ DELIVERY RECEIPT MODAL - For Driver/Transport (Professional ERP Format - Landscape) -->
<div class="modal fade" id="deliveryReceiptModal" tabindex="-1" aria-labelledby="deliveryReceiptLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 85vw; margin: auto;">
        <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15); max-height: 90vh; display: flex; flex-direction: column;">
            <!-- Professional Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #0dcaf0 0%, #0ea5e9 100%); border: none; padding: 16px 20px; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-truck" style="font-size: 1.2rem; color: white;"></i>
                    </div>
                    <div style="text-align: left;">
                        <h5 class="modal-title fw-bold" id="deliveryReceiptLabel" style="color: white; margin: 0; font-size: 1rem;">
                            Delivery Receipt
                        </h5>
                        <small style="color: rgba(255,255,255,0.8); font-size: 0.85rem;">Driver Copy</small>
                    </div>
                </div>
                <button type="button" class="btn-close" onclick="closeDeliveryReceipt()" aria-label="Close" style="filter: invert(1); opacity: 0.8;"></button>
            </div>

            <!-- Body - Landscape Layout with Controlled Scroll -->
            <div class="modal-body" id="deliveryReceiptContent" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11px; flex: 1; overflow-y: auto; overflow-x: hidden; padding: 16px 20px; background: #fafbfc; position: relative;">
                <!-- Receipt content will load here -->
                <div class="text-center p-4" style="display: flex; align-items: center; justify-content: center; min-height: 300px;">
                    <div>
                        <div class="spinner-border spinner-border-sm text-info" role="status" style="color: #0dcaf0 !important;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p style="margin-top: 12px; color: #6b7280; font-size: 13px;">Loading delivery receipt...</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 12px 20px; background: #ffffff; border-radius: 0 0 8px 8px; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                <button type="button" class="btn btn-sm" onclick="closeDeliveryReceipt()" style="background: #f3f4f6; color: #1f2937; border: 1px solid #d1d5db; padding: 6px 14px; border-radius: 5px; font-weight: 500; cursor: pointer; font-size: 0.9rem; transition: all 0.2s;">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-sm" onclick="thermalPrintDeliveryReceipt()" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border: none; padding: 6px 14px; border-radius: 5px; font-weight: 500; cursor: pointer; font-size: 0.9rem; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-receipt"></i> Thermal Print
                </button>
                <button type="button" class="btn btn-sm" onclick="printDeliveryReceipt()" style="background: linear-gradient(135deg, #0dcaf0 0%, #0ea5e9 100%); color: white; border: none; padding: 6px 14px; border-radius: 5px; font-weight: 500; cursor: pointer; font-size: 0.9rem; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-print"></i> A4 Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ✅ UPLOAD TRANSPORT RECEIPT MODAL - For handwritten receipt image (ERP Standard) -->
<div class="modal fade" id="uploadReceiptModal" tabindex="-1" aria-labelledby="uploadReceiptLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" style="max-width: 420px;">
        <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <!-- Professional Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border: none; padding: 20px; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-upload" style="font-size: 1.3rem; color: white;"></i>
                    </div>
                    <div style="text-align: left;">
                        <h5 class="modal-title fw-bold" id="uploadReceiptLabel" style="color: white; margin: 0; font-size: 1.1rem;">
                            Upload Receipt
                        </h5>
                        <small style="color: rgba(255,255,255,0.8);">Handwritten Document Image</small>
                    </div>
                </div>
                <button type="button" class="btn-close" onclick="closeUploadReceiptModal()" aria-label="Close" style="filter: invert(1); opacity: 0.8;"></button>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding: 24px; background: #fafbfc;">
                <form id="uploadReceiptForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="receiptGpId" name="id" value="">
                    
                    <!-- File Input Section -->
                    <div class="mb-4">
                        <label for="receiptImage" class="form-label fw-600" style="color: #1f2937; margin-bottom: 10px; display: block;">
                            <i class="fas fa-image" style="color: #f59e0b; margin-right: 6px;"></i> Select Receipt Image
                        </label>
                        <div style="position: relative; border: 2px dashed #d97706; border-radius: 8px; padding: 24px; text-align: center; background: white; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#f59e0b'; this.style.background='#fffbf0';" onmouseout="this.style.borderColor='#d97706'; this.style.background='white';">
                            <input type="file" class="form-control" id="receiptImage" name="receipt_image" accept="image/*" required style="display: none;">
                            <div onclick="document.getElementById('receiptImage').click();" style="cursor: pointer;">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #f59e0b; margin-bottom: 8px; display: block;"></i>
                                <p style="margin: 8px 0; color: #1f2937; font-weight: 500;">Click to select or drag & drop</p>
                                <small style="color: #6b7280;">JPG, PNG, GIF • Max 5MB</small>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div id="receiptPreview" style="display: none; margin-bottom: 20px;">
                        <label class="form-label fw-600" style="color: #1f2937; margin-bottom: 8px; display: block;">
                            <i class="fas fa-check-circle" style="color: #10b981;"></i> Image Preview
                        </label>
                        <img id="previewImage" src="" alt="Preview" style="max-width: 100%; max-height: 220px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    </div>

                    <!-- Upload Status -->
                    <div id="uploadStatus" style="display: none; margin-bottom: 20px;">
                        <div class="alert" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; color: #1e40af; padding: 16px;" role="alert">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="spinner-border spinner-border-sm" role="status" style="color: #3b82f6;">
                                    <span class="visually-hidden">Uploading...</span>
                                </div>
                                <span id="uploadStatusText">Uploading receipt...</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 16px 24px; background: #ffffff; border-radius: 0 0 8px 8px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-sm" onclick="closeUploadReceiptModal()" style="background: #f3f4f6; color: #1f2937; border: 1px solid #d1d5db; padding: 8px 16px; border-radius: 6px; font-weight: 500; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-sm" onclick="submitUploadReceipt()" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 500; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-upload"></i> Upload Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ✅ VIEW TRANSPORT RECEIPT MODAL - ERP Standard Professional Design -->
<div class="modal fade" id="viewReceiptModal" tabindex="-1" aria-labelledby="viewReceiptLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 90vw;">
        <div class="modal-content" style="overflow: visible; border-radius: 8px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <!-- Professional Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; padding: 20px; display: flex; justify-content: space-between; align-items: center; border-radius: 8px 8px 0 0;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-image" style="font-size: 1.3rem; color: white;"></i>
                    </div>
                    <div style="text-align: left;">
                        <h5 class="modal-title fw-bold" id="viewReceiptLabel" style="color: white; margin: 0; font-size: 1.1rem;">
                            Transport Receipt Image
                        </h5>
                        <small style="color: rgba(255,255,255,0.8);">View & Manage Receipt</small>
                    </div>
                </div>
                <!-- Control Buttons -->
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="button" class="btn btn-sm" onclick="rotateReceiptImage(-90)" title="Rotate Left (CCW)" style="background: rgba(255,255,255,0.2); color: white; border: none; padding: 8px 12px; border-radius: 6px; transition: all 0.2s;">
                        <i class="fas fa-redo"></i>
                    </button>
                    <button type="button" class="btn btn-sm" onclick="rotateReceiptImage(90)" title="Rotate Right (CW)" style="background: rgba(255,255,255,0.2); color: white; border: none; padding: 8px 12px; border-radius: 6px; transition: all 0.2s;">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="button" class="btn-close" onclick="closeViewReceiptModal()" aria-label="Close" style="filter: invert(1); opacity: 0.8;"></button>
                </div>
            </div>

            <!-- Body - Image Display -->
            <div class="modal-body" style="text-align: center; padding: 30px; background: linear-gradient(135deg, #f8fafb 0%, #eef2f5 100%); overflow: auto; max-height: 85vh;">
                <div id="receiptImageContainer" style="display: inline-block; overflow: visible; border-radius: 8px; background: white; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); max-width: 95%; border: 1px solid #e5e7eb; position: relative;">
                    <img id="fullReceiptImage" src="" alt="Receipt" style="width: auto; height: auto; max-width: 100%; max-height: 85vh; display: block; transition: transform 0.3s ease; transform-origin: center; transform: rotate(0deg); border-radius: 4px;">
                    <!-- Image Info -->
                    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 0.85rem;">
                        <i class="fas fa-info-circle" style="color: #3b82f6;"></i> Use rotation buttons to adjust orientation
                    </div>
                </div>
            </div>

            <!-- Professional Footer -->
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 16px 24px; background: #ffffff; border-radius: 0 0 8px 8px; display: flex; justify-content: space-between; align-items: center;">
                <small style="color: #6b7280;">
                    <i class="fas fa-check-circle" style="color: #10b981;"></i> Receipt Ready for Download
                </small>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-sm" onclick="closeViewReceiptModal()" style="background: #f3f4f6; color: #1f2937; border: 1px solid #d1d5db; padding: 8px 16px; border-radius: 6px; font-weight: 500; transition: all 0.2s;">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <a id="downloadReceiptLink" href="#" class="btn btn-sm btn-success" download style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 500; text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fas fa-download"></i> Download Receipt
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
// ✅ ERP STANDARD: Delivery Receipt Modal Handler - Professional Setup
let currentDeliveryReceipt = null;
let deliveryReceiptModalInstance = null;

// Initialize modal on page load
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('deliveryReceiptModal');
    if (modalElement) {
        deliveryReceiptModalInstance = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
    }
});

// Function to open delivery receipt modal
function showDeliveryReceipt(gpId) {
    console.log('🔓 Opening delivery receipt for gpId:', gpId);
    
    // Show loading spinner
    const contentDiv = document.getElementById('deliveryReceiptContent');
    if (!contentDiv) {
        console.error('❌ deliveryReceiptContent element not found!');
        return;
    }
    
    contentDiv.innerHTML = `
        <div class="text-center p-3">
            <div class="spinner-border spinner-border-sm text-info" role="status">
                <span class="visually-hidden">Loading receipt...</span>
            </div>
            <p class="mt-2">Loading delivery receipt...</p>
        </div>
    `;

    // Show modal
    if (deliveryReceiptModalInstance) {
        deliveryReceiptModalInstance.show();
        console.log('✅ Modal shown');
    } else {
        console.error('❌ Modal instance not initialized');
        return;
    }

    // Fetch gate pass details with all data
    fetch(`/outward-gatepass/${gpId}/delivery-receipt`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Store all data with proper field names
        currentDeliveryReceipt = {
            gpId: data.id,
            orderId: data.order_id,
            invoiceNo: data.invoice_no || 'N/A',
            customeName: data.customer_name || 'N/A',
            contactPerson: data.contact_person || '',
            deliveryCity: data.delivery_city || '-',
            warehouse: data.warehouse_name || 'Main Warehouse',
            driverName: data.driver_name || '-',
            vehicleNumber: data.vehicle_number || '-',
            vehicleType: data.vehicle_type || '-',
            transporter: data.transporter || '',
            billtyNo: data.billty_no || '',
            billtyDate: data.billty_date || '-',
            billtyAmount: parseFloat(data.billty_amount) || 0,
            transportRent: parseFloat(data.transport_rent) || 0,
            dcNo: data.dc_no || 'N/A',
            remarks: data.remarks || '',
            packingNotes: data.packing_notes || '',
            issuedBy: data.issued_by || '-',
            items: data.items || []
        };
        
        // Render receipt
        loadDeliveryReceipt();
        console.log('✅ Receipt loaded and displayed');
    })
    .catch(error => {
        console.error('❌ Error fetching delivery receipt:', error);
        document.getElementById('deliveryReceiptContent').innerHTML = `
            <div class="alert alert-danger" role="alert">
                <strong>Error!</strong> Failed to load delivery receipt: ${error.message}
                <br><small>Check browser console for details.</small>
            </div>
        `;
    });
}

// Function to close delivery receipt modal
function closeDeliveryReceipt() {
    console.log('🔒 Closing delivery receipt modal');
    if (deliveryReceiptModalInstance) {
        deliveryReceiptModalInstance.hide();
        console.log('✅ Modal closed');
    }
}

function loadDeliveryReceipt() {
    const contentDiv = document.getElementById('deliveryReceiptContent');
    const gp = currentDeliveryReceipt;
    
    if (!gp) {
        contentDiv.innerHTML = '<div class="alert alert-danger">No data available</div>';
        return;
    }
    
    // Calculate totals
    let totalQty = 0;
    let totalAmount = 0;
    if (Array.isArray(gp.items) && gp.items.length > 0) {
        gp.items.forEach(item => {
            const qty = parseFloat(item.qty) || 0;
            const price = parseFloat(item.retail_price) || 0;
            totalQty += qty;
            totalAmount += (qty * price);
        });
    }

    // ✅ ERP STANDARD: Professional Delivery Receipt Template - Complete with All Fields
    const receipt = `
        <div style="background: white; padding: 12px 14px; border: 1px solid #ccc; border-radius: 3px; font-family: Arial, sans-serif; font-size: 9px; line-height: 1.3;">
            
            <!-- Header Section - Compact -->
            <div style="margin-bottom: 8px; padding-bottom: 6px; border-bottom: 2px solid #333;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 50%;"><div style="font-size: 13px; font-weight: 700; color: #003366;">AMEEN & SONS</div><div style="font-size: 8px; color: #666;">Warehouse & Logistics</div></td>
                        <td style="width: 30%; text-align: center;"><div style="font-size: 11px; font-weight: 700; color: #0066cc;">DELIVERY RECEIPT</div></td>
                        <td style="width: 20%; text-align: right;"><div style="font-size: 9px; font-weight: 700;">GP# ${gp.gpId}</div><div style="font-size: 8px; color: #666;">${new Date().toLocaleDateString('en-PK')}</div></td>
                    </tr>
                </table>
            </div>

            <!-- Quick Reference Row - All Key Fields -->
            <table style="width: 100%; margin-bottom: 8px; font-size: 8px; border-collapse: collapse; background: #f5f5f5;">
                <tr>
                    <td style="padding: 2px 4px; border: 1px solid #ddd;"><strong>Receipt:</strong> ${gp.gpId}</td>
                    <td style="padding: 2px 4px; border: 1px solid #ddd;"><strong>Invoice:</strong> ${gp.invoiceNo || '-'}</td>
                    <td style="padding: 2px 4px; border: 1px solid #ddd;"><strong>Billty:</strong> ${gp.billtyNo || '-'}</td>
                    <td style="padding: 2px 4px; border: 1px solid #ddd;"><strong>Billty Amt:</strong> ${gp.billtyAmount ? gp.billtyAmount.toFixed(2) : '-'}</td>
                    <td style="padding: 2px 4px; border: 1px solid #ddd;"><strong>Transport Rent:</strong> ${gp.transportRent ? gp.transportRent.toFixed(2) : '-'}</td>
                </tr>
            </table>

            <!-- From/To Section with All Details - Three Columns -->
            <table style="width: 100%; margin-bottom: 8px; font-size: 8px; border-collapse: collapse;">
                <tr>
                    <td style="width: 33%; padding: 4px; border: 1px solid #999; vertical-align: top;">
                        <div style="font-weight: 700; color: #003366; margin-bottom: 2px; border-bottom: 1px solid #0066cc; padding-bottom: 1px;">WAREHOUSE (FROM)</div>
                        <div style="margin: 1px 0;"><strong>Name:</strong> ${gp.warehouse || 'Main'}</div>
                        <div style="margin: 1px 0;"><strong>Location:</strong> Karachi, PK</div>
                    </td>
                    <td style="width: 33%; padding: 4px; border: 1px solid #999; vertical-align: top;">
                        <div style="font-weight: 700; color: #003366; margin-bottom: 2px; border-bottom: 1px solid #0066cc; padding-bottom: 1px;">CUSTOMER (TO)</div>
                        <div style="margin: 1px 0;"><strong>Name:</strong> ${gp.customeName}</div>
                        <div style="margin: 1px 0;"><strong>City:</strong> ${gp.deliveryCity || '-'}</div>
                        ${gp.contactPerson ? `<div style="margin: 1px 0;"><strong>Contact:</strong> ${gp.contactPerson}</div>` : ''}
                    </td>
                    <td style="width: 34%; padding: 4px; border: 1px solid #999; vertical-align: top;">
                        <div style="font-weight: 700; color: #003366; margin-bottom: 2px; border-bottom: 1px solid #0066cc; padding-bottom: 1px;">TRANSPORT DETAILS</div>
                        <div style="margin: 1px 0;"><strong>Driver:</strong> ${gp.driverName}</div>
                        <div style="margin: 1px 0;"><strong>Vehicle:</strong> ${gp.vehicleNumber} (${gp.vehicleType || 'N/A'})</div>
                        <div style="margin: 1px 0;"><strong>Carrier:</strong> ${gp.transporter || '-'}</div>
                    </td>
                </tr>
            </table>

            <!-- Items Table - Ultra Compact -->
            <div style="margin-bottom: 8px;">
                <div style="font-weight: 700; color: #003366; background: #f0f0f0; padding: 2px 4px; margin-bottom: 3px; font-size: 8px; border-radius: 2px;">📦 ITEMS</div>
                ${Array.isArray(gp.items) && gp.items.length > 0 ? `
                    <table style="width: 100%; border-collapse: collapse; font-size: 7px; margin-bottom: 4px;">
                        <thead>
                            <tr style="background: #e8f4f8; border: 1px solid #999;">
                                <th style="padding: 1px 2px; text-align: center; border: 1px solid #999; font-weight: 700;">#</th>
                                <th style="padding: 1px 2px; text-align: left; border: 1px solid #999; font-weight: 700;">Description</th>
                                <th style="padding: 1px 2px; text-align: center; border: 1px solid #999; font-weight: 700;">Unit</th>
                                <th style="padding: 1px 2px; text-align: right; border: 1px solid #999; font-weight: 700;">Qty</th>
                                <th style="padding: 1px 2px; text-align: right; border: 1px solid #999; font-weight: 700;">Rate</th>
                                <th style="padding: 1px 2px; text-align: right; border: 1px solid #999; font-weight: 700;">Amt</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${gp.items.map((item, idx) => `
                                <tr style="border: 1px solid #ddd;">
                                    <td style="padding: 1px 2px; text-align: center;">${idx + 1}</td>
                                    <td style="padding: 1px 2px; text-align: left;">${(item.product_name || item.text || '-').substring(0, 20)}</td>
                                    <td style="padding: 1px 2px; text-align: center;">${item.unit || 'Box'}</td>
                                    <td style="padding: 1px 2px; text-align: right;">${parseFloat(item.qty || 0).toFixed(0)}</td>
                                    <td style="padding: 1px 2px; text-align: right;">${parseFloat(item.retail_price || 0).toFixed(0)}</td>
                                    <td style="padding: 1px 2px; text-align: right;">${(parseFloat(item.qty || 0) * parseFloat(item.retail_price || 0)).toFixed(0)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                        <tfoot>
                            <tr style="background: #e8f4f8; border: 1px solid #999; font-weight: 700; font-size: 7px;">
                                <td colspan="3" style="padding: 1px 2px; text-align: right;">TOTAL:</td>
                                <td style="padding: 1px 2px; text-align: right; border: 1px solid #999;">${totalQty.toFixed(0)}</td>
                                <td style="padding: 1px 2px; text-align: right; border: 1px solid #999;"></td>
                                <td style="padding: 1px 2px; text-align: right; border: 1px solid #999;">${totalAmount.toFixed(0)}</td>
                            </tr>
                        </tfoot>
                    </table>
                ` : `<div style="font-size: 8px; color: #999; padding: 2px;">No items</div>`}
            </div>

            <!-- Dates & More Details -->
            <table style="width: 100%; margin-bottom: 6px; font-size: 8px; border-collapse: collapse; background: #f9f9f9;">
                <tr>
                    <td style="padding: 2px 4px; border: 1px solid #ddd; width: 25%;"><strong>Billty Date:</strong> ${gp.billtyDate || '-'}</td>
                    <td style="padding: 2px 4px; border: 1px solid #ddd; width: 25%;"><strong>Issued By:</strong> ${gp.issuedBy || '-'}</td>
                    <td style="padding: 2px 4px; border: 1px solid #ddd; width: 50%;"><strong>DC No:</strong> ${gp.dcNo || '-'}</td>
                </tr>
            </table>

            <!-- Packing Notes & Remarks - Two Columns -->
            <table style="width: 100%; margin-bottom: 6px; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; padding: 3px; border: 1px solid #ddd;">
                        <div style="font-weight: 700; color: #e65100; font-size: 8px; margin-bottom: 1px;">🎁 PACKING NOTES</div>
                        <div style="min-height: 18px; background: white; padding: 2px; font-size: 7px; border: 1px solid #ffb74d; border-radius: 2px; line-height: 1.1; ">
                            ${gp.packingNotes ? `${gp.packingNotes}` : `<span style="color: #999;">-</span>`}
                        </div>
                    </td>
                    <td style="width: 50%; padding: 3px 3px 3px 6px; border: 1px solid #ddd;">
                        <div style="font-weight: 700; color: #7b1fa2; font-size: 8px; margin-bottom: 1px;">📝 REMARKS</div>
                        <div style="min-height: 18px; background: white; padding: 2px; font-size: 7px; border: 1px solid #e1bee7; border-radius: 2px; line-height: 1.1;">
                            ${gp.remarks || `<span style="color: #999;">-</span>`}
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Signature - Three Compact Boxes -->
            <table style="width: 100%; font-size: 7px; border-collapse: collapse; margin-top: 4px;">
                <tr>
                    <td style="width: 33%; text-align: center; padding: 3px; border: 1px solid #ddd;">
                        <div style="min-height: 18px; border-top: 1px solid #000;"></div>
                        <div style="font-weight: 700; font-size: 7px; margin-top: 1px;">Driver</div>
                    </td>
                    <td style="width: 33%; text-align: center; padding: 3px; border: 1px solid #ddd;">
                        <div style="min-height: 18px; border-top: 1px solid #000;"></div>
                        <div style="font-weight: 700; font-size: 7px; margin-top: 1px;">Receiver</div>
                    </td>
                    <td style="width: 34%; text-align: center; padding: 3px; border: 1px solid #ddd;">
                        <div style="min-height: 18px; border-top: 1px solid #000;"></div>
                        <div style="font-weight: 700; font-size: 7px; margin-top: 1px;">Auth</div>
                    </td>
                </tr>
            </table>

            <!-- Footer -->
            <div style="border-top: 1px solid #ddd; padding-top: 3px; text-align: center; font-size: 7px; color: #666; margin-top: 4px;">
                <div>Generated: ${new Date().toLocaleString('en-PK')}</div>
            </div>

        </div>
    `;

    contentDiv.innerHTML = receipt;
}

// ✅ ERP STANDARD: Print Delivery Receipt
function printDeliveryReceipt() {
    if (!currentDeliveryReceipt) {
        alert('No delivery receipt loaded');
        return;
    }

    const gp = currentDeliveryReceipt;
    const contentDiv = document.getElementById('deliveryReceiptContent');
    
    const printWindow = window.open('', '', 'height=900,width=600');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Delivery Receipt #${gp.gpId}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                @page { size: A4 portrait; margin: 8mm; }
                body {
                    font-family: Arial, sans-serif;
                    font-size: 10px;
                    background: white;
                    color: #333;
                    line-height: 1.2;
                }
                @media print {
                    body { margin: 0; padding: 8px; background: white; }
                    .no-print { display: none; }
                    * { box-shadow: none; }
                    @page { size: A4 portrait; }
                }
                .receipt { max-width: 100%; width: 100%; margin: 0; padding: 6px; background: white; }
                .header { text-align: center; margin-bottom: 8px; border-bottom: 1px solid #333; padding-bottom: 4px; }
                .header h2 { font-size: 12px; margin: 2px 0; }
                .header p { font-size: 9px; color: #666; }
                .section { margin-bottom: 6px; }
                .section-title { font-weight: 700; border-bottom: 1px solid #999; padding-bottom: 2px; margin-bottom: 3px; background: #f0f0f0; padding: 2px 3px; font-size: 9px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
                th, td { padding: 2px 3px; text-align: left; border: 1px solid #ddd; }
                th { background: #f0f0f0; font-weight: 600; }
                .items-table th, .items-table td { font-size: 9px; padding: 2px 3px; }
                .total { text-align: right; font-weight: 600; padding-right: 3px; }
                .signature-area { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-top: 8px; }
                .sig-box { text-align: center; border-top: 1px solid #000; padding-top: 2px; font-size: 8px; }
                footer { text-align: center; margin-top: 6px; font-size: 8px; color: #999; }
            </style>
        </head>
        <body>
            <div class="receipt">
                <div class="header">
                    <h2>AMEEN & SONS</h2>
                    <p>📦 DELIVERY RECEIPT</p>
                    <p>Gate Pass #${gp.gpId}</p>
                </div>

                <div class="section">
                    <div class="section-title">CUSTOMER INFORMATION</div>
                    <p><strong>${gp.customeName}</strong></p>
                    ${gp.contactPerson ? `<p>Contact: ${gp.contactPerson}</p>` : ''}
                </div>

                <div class="section">
                    <div class="section-title">TRANSPORT DETAILS</div>
                    <table>
                        <tr>
                            <td style="font-weight: 600;">Driver:</td>
                            <td>${gp.driverName}</td>
                            <td style="font-weight: 600;">Vehicle:</td>
                            <td>${gp.vehicleNumber}</td>
                        </tr>
                        ${gp.transporter ? `<tr><td style="font-weight: 600;">Carrier:</td><td colspan="3">${gp.transporter}</td></tr>` : ''}
                        ${gp.billtyNo ? `<tr><td style="font-weight: 600;">Billty #:</td><td>${gp.billtyNo}</td>${gp.billtyDate ? `<td style="font-weight: 600;">Date:</td><td>${gp.billtyDate}</td>` : ''}</tr>` : ''}
                    </table>
                </div>

                <div class="section">
                    <div class="section-title">ITEMS DELIVERED</div>
                    ${Array.isArray(gp.items) && gp.items.length > 0 ? `
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Quantity</th>
                                    <th>Item Description</th>
                                    <th style="text-align: right;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${gp.items.map((item, idx) => `
                                    <tr>
                                        <td>${idx + 1}</td>
                                        <td style="text-align: center;">${item.qty || 0}</td>
                                        <td>${item.product_name || item.text || '-'}</td>
                                        <td style="text-align: right;">${item.retail_price ? (parseFloat(item.qty || 0) * parseFloat(item.retail_price)).toFixed(2) : '-'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    ` : `<p style="text-align: center; color: #999;">No items recorded</p>`}
                </div>

                ${gp.packingNotes ? `
                <div class="section">
                    <div class="section-title">PACKING NOTES & INSTRUCTIONS</div>
                    <p>${gp.packingNotes}</p>
                </div>
                ` : ''}

                <div class="section">
                    <div class="section-title">✍️ PACKING & HANDLING INSTRUCTIONS (Handwritten)</div>
                    <div style="border: 1px solid #ddd; background: #fffbf0; padding: 8px; min-height: 80px; margin-bottom: 6px;">
                        <p style="font-size: 8px; color: #999; margin: 0;">Write any special handling, packing, or delivery instructions here</p>
                    </div>
                </div>

                <div class="section">
                    <div class="section-title">RECEIVER ACKNOWLEDGEMENT</div>
                    <div class="signature-area">
                        <div class="sig-box">
                            <p style="margin-bottom: 15px;"></p>
                            <p><strong>Driver</strong></p>
                        </div>
                        <div class="sig-box">
                            <p style="margin-bottom: 15px;"></p>
                            <p><strong>Receiver</strong></p>
                        </div>
                        <div class="sig-box">
                            <p style="margin-bottom: 15px;"></p>
                            <p><strong>Date</strong></p>
                        </div>
                    </div>
                </div>

                <footer>
                    <p style="margin: 2px 0;">Gen: ${new Date().toLocaleString('en-PK').split(',')[0]}</p>
                </footer>
            </div>
            <script>
                setTimeout(() => window.print(), 300);
            <\/script>
        </body>
        </html>
    `);
    
    printWindow.document.close();
}

// ✅ THERMAL PRINT: 80mm Thermal Printer Format (Receipt Printer)
function thermalPrintDeliveryReceipt() {
    if (!currentDeliveryReceipt) {
        alert('No delivery receipt loaded');
        return;
    }

    const gp = currentDeliveryReceipt;
    
    const thermalWindow = window.open('', '', 'height=600,width=350');
    thermalWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Thermal Receipt #${gp.gpId}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                @page { size: 80mm auto; margin: 0; }
                body {
                    font-family: 'Courier New', monospace;
                    font-size: 11px;
                    width: 80mm;
                    background: white;
                    color: #000;
                    line-height: 1.3;
                    margin: 0;
                    padding: 0;
                }
                @media print {
                    body { 
                        margin: 0; 
                        padding: 0; 
                        width: 80mm;
                    }
                    * { box-shadow: none; }
                    @page { size: 80mm auto; margin: 0; }
                }
                .thermal-receipt {
                    width: 80mm;
                    padding: 2mm;
                    background: white;
                }
                .header {
                    text-align: center;
                    margin-bottom: 2mm;
                    padding-bottom: 1mm;
                    border-bottom: 1px dashed #000;
                    font-weight: bold;
                }
                .header h2 {
                    font-size: 12px;
                    margin: 2px 0;
                    letter-spacing: 0.5px;
                }
                .header p {
                    font-size: 9px;
                    margin: 1px 0;
                }
                .divider {
                    border-bottom: 1px dashed #000;
                    margin: 2mm 0;
                }
                .row {
                    display: flex;
                    justify-content: space-between;
                    font-size: 9px;
                    margin: 1mm 0;
                    padding: 1mm 0;
                }
                .label { font-weight: bold; width: 35%; }
                .value { text-align: right; width: 65%; word-break: break-all; }
                .single-row {
                    font-size: 9px;
                    margin: 1mm 0;
                    padding: 1mm 0;
                }
                .section-title {
                    font-weight: bold;
                    font-size: 10px;
                    margin: 2mm 0 1mm 0;
                    border-bottom: 1px dashed #000;
                    padding-bottom: 1mm;
                }
                .items-table {
                    width: 100%;
                    font-size: 8px;
                    margin: 1mm 0;
                }
                .items-table th {
                    font-weight: bold;
                    border-bottom: 1px solid #000;
                    padding: 1mm 1mm;
                    text-align: left;
                }
                .items-table td {
                    padding: 1mm 1mm;
                    border-bottom: 1px dotted #ccc;
                }
                .qty-col { text-align: center; width: 15%; }
                .rate-col { text-align: right; width: 20%; }
                .amt-col { text-align: right; width: 20%; }
                .name-col { text-align: left; flex: 1; }
                .total-row {
                    font-weight: bold;
                    border-top: 1px solid #000;
                    border-bottom: 1px solid #000;
                    padding: 2mm 1mm;
                    text-align: right;
                }
                .footer {
                    text-align: center;
                    font-size: 8px;
                    margin-top: 2mm;
                    padding-top: 1mm;
                    border-top: 1px dashed #000;
                }
                .notes {
                    font-size: 8px;
                    margin: 1mm 0;
                    padding: 1mm;
                    border: 1px dashed #ccc;
                    line-height: 1.2;
                    max-height: 15mm;
                    overflow: hidden;
                }
                .sig-area {
                    display: flex;
                    justify-content: space-between;
                    margin-top: 3mm;
                    font-size: 8px;
                    text-align: center;
                }
                .sig-box {
                    flex: 1;
                    padding-top: 5mm;
                    border-top: 1px solid #000;
                }
            </style>
        </head>
        <body>
            <div class="thermal-receipt">
                <!-- Header -->
                <div class="header">
                    <h2>AMEEN & SONS</h2>
                    <p>DELIVERY RECEIPT</p>
                    <p>GP#${gp.gpId}</p>
                </div>

                <!-- Quick Info -->
                <div class="divider"></div>
                <div class="row">
                    <span class="label">Invoice:</span>
                    <span class="value">${gp.invoiceNo || '-'}</span>
                </div>
                <div class="row">
                    <span class="label">DC No:</span>
                    <span class="value">${gp.dcNo || '-'}</span>
                </div>
                <div class="row">
                    <span class="label">Billty:</span>
                    <span class="value">${gp.billtyNo || '-'}</span>
                </div>

                <!-- Warehouse Info -->
                <div class="section-title">FROM: WAREHOUSE</div>
                <div class="single-row">${gp.warehouse || 'Main Store'}, Karachi</div>

                <!-- Customer Info -->
                <div class="section-title">TO: CUSTOMER</div>
                <div class="single-row"><strong>${gp.customeName}</strong></div>
                <div class="single-row">City: ${gp.deliveryCity || '-'}</div>
                ${gp.contactPerson ? `<div class="single-row">Contact: ${gp.contactPerson}</div>` : ''}

                <!-- Transport Info -->
                <div class="section-title">TRANSPORT</div>
                <div class="row">
                    <span class="label">Driver:</span>
                    <span class="value">${gp.driverName}</span>
                </div>
                <div class="row">
                    <span class="label">Vehicle:</span>
                    <span class="value">${gp.vehicleNumber} (${gp.vehicleType || '-'})</span>
                </div>
                <div class="row">
                    <span class="label">Carrier:</span>
                    <span class="value">${gp.transporter || '-'}</span>
                </div>
                <div class="row">
                    <span class="label">Rent:</span>
                    <span class="value">Rs. ${gp.transportRent ? gp.transportRent.toFixed(0) : '-'}</span>
                </div>

                <!-- Items -->
                <div class="section-title">ITEMS (${Array.isArray(gp.items) ? gp.items.length : 0})</div>
                ${Array.isArray(gp.items) && gp.items.length > 0 ? `
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th class="qty-col">Qty</th>
                                <th class="rate-col">Rate</th>
                                <th class="amt-col">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${gp.items.map((item) => `
                                <tr>
                                    <td colspan="3" style="padding: 1mm 1mm; font-weight: 600; border-bottom: 1px dotted #ccc;">
                                        ${(item.product_name || item.text || 'Item').substring(0, 25)}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="qty-col">${parseFloat(item.qty || 0).toFixed(0)}</td>
                                    <td class="rate-col">Rs.${parseFloat(item.retail_price || 0).toFixed(0)}</td>
                                    <td class="amt-col">Rs.${(parseFloat(item.qty || 0) * parseFloat(item.retail_price || 0)).toFixed(0)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                ` : `<div class="single-row">No items recorded</div>`}

                <!-- Packing Notes -->
                ${gp.packingNotes ? `
                    <div class="section-title">PACKING NOTES</div>
                    <div class="notes">${gp.packingNotes}</div>
                ` : ''}

                <!-- Dates -->
                <div class="divider"></div>
                <div class="row">
                    <span class="label">Billty Date:</span>
                    <span class="value">${gp.billtyDate || '-'}</span>
                </div>
                <div class="row">
                    <span class="label">Issued By:</span>
                    <span class="value">${gp.issuedBy || '-'}</span>
                </div>

                <!-- Signatures -->
                <div class="sig-area">
                    <div class="sig-box">Driver</div>
                    <div class="sig-box">Receiver</div>
                    <div class="sig-box">Authority</div>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <p>Generated: ${new Date().toLocaleString('en-PK')}</p>
                    <p>Thank you for your business!</p>
                </div>
            </div>

            <script>
                // Auto-print for thermal printer
                window.onload = function() {
                    setTimeout(() => {
                        window.print();
                    }, 500);
                };
            <\/script>
        </body>
        </html>
    `);
    
    thermalWindow.document.close();
}

// ✅ ERP STANDARD: Transport Receipt Upload Modal Functions
let uploadReceiptModalInstance = null;
let viewReceiptModalInstance = null;

// Initialize upload and view modals on page load
document.addEventListener('DOMContentLoaded', function() {
    const uploadModalElement = document.getElementById('uploadReceiptModal');
    if (uploadModalElement) {
        uploadReceiptModalInstance = new bootstrap.Modal(uploadModalElement);
    }
    
    const viewModalElement = document.getElementById('viewReceiptModal');
    if (viewModalElement) {
        viewReceiptModalInstance = new bootstrap.Modal(viewModalElement);
    }

    // File preview on selection
    const receiptImageInput = document.getElementById('receiptImage');
    if (receiptImageInput) {
        receiptImageInput.addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const previewDiv = document.getElementById('receiptPreview');
                    const previewImage = document.getElementById('previewImage');
                    previewImage.src = event.target.result;
                    previewDiv.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// Function to open upload receipt modal
function showUploadReceiptModal(gpId) {
    console.log('📤 Opening upload receipt modal for GP ID:', gpId);
    
    // Reset form
    const form = document.getElementById('uploadReceiptForm');
    if (form) {
        form.reset();
    }
    document.getElementById('receiptPreview').style.display = 'none';
    document.getElementById('uploadStatus').style.display = 'none';
    document.getElementById('receiptGpId').value = gpId;
    
    // Show modal
    if (uploadReceiptModalInstance) {
        uploadReceiptModalInstance.show();
    }
}

// Function to close upload receipt modal
function closeUploadReceiptModal() {
    console.log('📤 Closing upload receipt modal');
    if (uploadReceiptModalInstance) {
        uploadReceiptModalInstance.hide();
    }
}

// Function to submit upload receipt form
function submitUploadReceipt() {
    console.log('📤 Submitting receipt upload');
    
    const gpId = document.getElementById('receiptGpId').value;
    const form = document.getElementById('uploadReceiptForm');
    const fileInput = document.getElementById('receiptImage');
    
    if (!fileInput.files[0]) {
        alert('Please select an image file');
        return;
    }
    
    // Show loading status
    document.getElementById('uploadStatus').style.display = 'block';
    document.getElementById('uploadStatusText').textContent = 'Uploading receipt...';
    
    // Create FormData
    const formData = new FormData();
    formData.append('id', gpId);
    formData.append('receipt_image', fileInput.files[0]);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    // Submit
    fetch(`/outward-gatepass/${gpId}/transport-receipt`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Receipt uploaded successfully');
            document.getElementById('uploadStatusText').textContent = 'Receipt uploaded successfully!';
            
            // Close modal after 1 second
            setTimeout(() => {
                closeUploadReceiptModal();
                // Reload page to show new receipt
                location.reload();
            }, 1000);
        } else {
            throw new Error(data.message || 'Upload failed');
        }
    })
    .catch(error => {
        console.error('❌ Error uploading receipt:', error);
        document.getElementById('uploadStatus').style.display = 'none';
        alert('Error uploading receipt: ' + error.message);
    });
}

// Function to view transport receipt
function viewTransportReceipt(gpId, receiptUrl) {
    console.log('👁️ Viewing transport receipt for GP ID:', gpId);
    
    if (!receiptUrl) {
        alert('Receipt URL not found');
        return;
    }
    
    // Reset rotation
    currentReceiptRotation = 0;
    
    // Set image and download link
    const imageElement = document.getElementById('fullReceiptImage');
    const modalDialog = document.querySelector('#viewReceiptModal .modal-dialog');
    
    imageElement.src = receiptUrl;
    imageElement.style.transform = 'rotate(0deg)';
    document.getElementById('downloadReceiptLink').href = receiptUrl;
    
    // Reset container padding
    const containerElement = document.getElementById('receiptImageContainer');
    if (containerElement) {
        containerElement.style.padding = '20px';
    }
    
    // Wait for image to load and adjust modal size
    const img = new Image();
    img.onload = function() {
        console.log(`📐 Image dimensions: ${this.width}x${this.height}`);
        
        // Get viewport dimensions
        const maxWidth = window.innerWidth * 0.9; // 90% of viewport width
        const maxHeight = window.innerHeight * 0.9; // 90% of viewport height
        
        // Calculate aspect ratio
        const aspectRatio = this.width / this.height;
        
        let modalWidth = this.width + 80; // Add padding
        let modalHeight = this.height + 200; // Add header + footer + padding
        
        // Constrain to viewport
        if (modalWidth > maxWidth) {
            modalWidth = maxWidth;
            modalHeight = (maxWidth - 80) / aspectRatio + 200;
        }
        
        if (modalHeight > maxHeight) {
            modalHeight = maxHeight;
            modalWidth = (maxHeight - 200) * aspectRatio + 80;
        }
        
        // Apply modal dimensions
        if (modalDialog) {
            modalDialog.style.maxWidth = modalWidth + 'px';
            modalDialog.style.width = '90%';
        }
        
        console.log(`📏 Modal size: ${modalWidth}x${modalHeight}`);
    };
    img.src = receiptUrl;
    
    // Show modal
    if (viewReceiptModalInstance) {
        viewReceiptModalInstance.show();
    }
}

// Global rotation angle
let currentReceiptRotation = 0;

// Function to rotate receipt image
function rotateReceiptImage(angle) {
    currentReceiptRotation += angle;
    // Normalize to 0-360
    currentReceiptRotation = currentReceiptRotation % 360;
    
    const imageElement = document.getElementById('fullReceiptImage');
    const containerElement = document.getElementById('receiptImageContainer');
    
    imageElement.style.transform = `rotate(${currentReceiptRotation}deg)`;
    
    // Adjust container padding based on rotation to prevent clipping
    const isRotated = (currentReceiptRotation % 180) !== 0;
    if (isRotated) {
        containerElement.style.padding = '40px';
    } else {
        containerElement.style.padding = '20px';
    }
    
    console.log(`🔄 Image rotated to: ${currentReceiptRotation}°`);
}

// Function to close view receipt modal
function closeViewReceiptModal() {
    console.log('👁️ Closing view receipt modal');
    // Reset rotation
    currentReceiptRotation = 0;
    const containerElement = document.getElementById('receiptImageContainer');
    if (containerElement) {
        containerElement.style.padding = '20px';
    }
    
    if (viewReceiptModalInstance) {
        viewReceiptModalInstance.hide();
    }
}
</script>
@endsection
