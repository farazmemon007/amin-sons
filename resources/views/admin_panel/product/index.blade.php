@extends('admin_panel.layout.app')

@section('content')
@can('product.view')

<style>
    /* ─── Google Inter Font ─── */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    /* ─── Page & Table Base ─── */
    .product-page-wrap {
        font-family: 'Inter', 'Segoe UI', sans-serif;
        padding: 0;
    }

    /* ─── Card ─── */
    .product-card {
        background: #ffffff;
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.07), 0 1px 3px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .product-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 24px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
    }

    .product-card-header .header-left h5 {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .product-card-header .header-left h5 .icon-box {
        width: 32px; height: 32px;
        background: rgba(30,58,95,0.08);
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #1e3a5f;
        font-size: 14px;
    }

    .product-card-header .header-left small {
        font-size: 12px;
        color: #94a3b8;
        margin-top: 2px;
        display: block;
    }

    /* ─── Add Product Button ─── */
    .btn-add-product {
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 20px;
        border-radius: 8px;
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        color: #fff;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        box-shadow: 0 2px 8px rgba(30,58,95,0.3);
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .btn-add-product:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(30,58,95,0.4);
        color: #fff;
        text-decoration: none;
    }

    /* ─── DataTable Wrapper Padding ─── */
    .product-card .dataTables_wrapper {
        padding: 16px 24px 20px;
        font-family: 'Inter', sans-serif;
    }
    div.dataTables_wrapper div.dataTables_length select {
        width: 70px !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 6px !important;
        padding: 4px 8px !important;
        font-size: 13px !important;
        font-family: 'Inter', sans-serif !important;
    }
    div.dataTables_wrapper div.dataTables_filter input {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 6px !important;
        padding: 6px 12px !important;
        font-size: 13px !important;
        font-family: 'Inter', sans-serif !important;
    }
    div.dataTables_wrapper div.dataTables_filter input:focus {
        border-color: #1e3a5f !important;
        box-shadow: 0 0 0 3px rgba(30,58,95,0.08) !important;
        outline: none !important;
    }
    div.dataTables_wrapper div.dataTables_info {
        font-size: 12px !important;
        color: #94a3b8 !important;
        padding-top: 10px !important;
    }
    div.dataTables_wrapper div.dataTables_paginate .paginate_button.current {
        background: #1e3a5f !important;
        border-color: #1e3a5f !important;
        color: #fff !important;
        border-radius: 6px !important;
    }
    div.dataTables_wrapper div.dataTables_paginate .paginate_button:hover {
        background: #f1f5f9 !important;
        border-color: #e2e8f0 !important;
        color: #1e3a5f !important;
        border-radius: 6px !important;
    }

    /* ─── Table Core ─── */
    #productTable {
        font-family: 'Inter', sans-serif !important;
        font-size: 13px !important;
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    #productTable thead th {
        background: #f8fafc !important;
        color: #64748b !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.55px !important;
        padding: 13px 14px !important;
        border-top: none !important;
        border-bottom: 2px solid #e2e8f0 !important;
        white-space: nowrap !important;
        vertical-align: middle !important;
    }

    #productTable tbody td {
        padding: 11px 14px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #334155 !important;
        font-size: 13px !important;
        background: #ffffff !important;
    }

    #productTable tbody tr {
        transition: background 0.15s ease;
    }
    #productTable tbody tr:hover td {
        background: #f8faff !important;
    }
    #productTable tbody tr.secondary-row td {
        background: #fffbeb !important;
    }
    #productTable tbody tr.secondary-row:hover td {
        background: #fef9e7 !important;
    }
    #productTable tbody tr:last-child td {
        border-bottom: none !important;
    }

    /* ─── Product Image Cell ─── */
    .prod-img-thumb {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        object-fit: cover;
        border: 1.5px solid #e2e8f0;
    }
    .prod-no-img {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        background: #f1f5f9;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #cbd5e1;
        font-size: 16px;
        border: 1.5px solid #e2e8f0;
    }

    /* ─── Item Code ─── */
    .item-code-pill {
        font-family: 'JetBrains Mono', 'Courier New', monospace;
        font-size: 11.5px;
        font-weight: 700;
        color: #1e3a5f;
        background: rgba(30,58,95,0.07);
        padding: 4px 10px;
        border-radius: 6px;
        letter-spacing: 0.4px;
        display: inline-block;
        white-space: nowrap;
    }

    /* ─── Badges ─── */
    .badge-branch {
        font-family: 'Inter', sans-serif;
        font-size: 11.5px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        background: rgba(30,58,95,0.08);
        color: #1e3a5f;
        border: 1px solid rgba(30,58,95,0.15);
        letter-spacing: 0.2px;
        display: inline-block;
        white-space: nowrap;
    }
    .badge-in-stock {
        font-family: 'Inter', sans-serif;
        font-size: 11px; font-weight: 700;
        padding: 4px 10px; border-radius: 20px;
        background: rgba(13,159,110,0.1);
        color: #0d9f6e;
        border: 1px solid rgba(13,159,110,0.2);
    }
    .badge-no-stock {
        font-family: 'Inter', sans-serif;
        font-size: 11px; font-weight: 700;
        padding: 4px 10px; border-radius: 20px;
        background: rgba(245,158,11,0.1);
        color: #b45309;
        border: 1px solid rgba(245,158,11,0.2);
    }
    .badge-primary-role {
        font-family: 'Inter', sans-serif;
        font-size: 11px; font-weight: 700;
        padding: 4px 10px; border-radius: 20px;
        background: rgba(13,159,110,0.1);
        color: #0d9f6e;
        border: 1px solid rgba(13,159,110,0.2);
    }
    .badge-secondary-role {
        font-family: 'Inter', sans-serif;
        font-size: 11px; font-weight: 700;
        padding: 4px 10px; border-radius: 20px;
        background: rgba(245,158,11,0.1);
        color: #b45309;
        border: 1px solid rgba(245,158,11,0.2);
    }
    .badge-qty {
        font-family: 'Inter', sans-serif;
        font-size: 11.5px; font-weight: 700;
        padding: 3px 9px; border-radius: 6px;
        background: rgba(30,58,95,0.08);
        color: #1e3a5f;
        border: 1px solid rgba(30,58,95,0.12);
        min-width: 32px;
        display: inline-block;
        text-align: center;
    }
    .badge-no-qty {
        font-family: 'Inter', sans-serif;
        font-size: 11px; font-weight: 700;
        padding: 3px 9px; border-radius: 6px;
        background: rgba(220,53,69,0.08);
        color: #dc3545;
        border: 1px solid rgba(220,53,69,0.15);
    }

    /* ─── Stock By Branch Column ─── */
    .branch-stock-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 5px;
        font-size: 12.5px;
    }
    .branch-stock-row:last-child { margin-bottom: 0; }
    .branch-stock-name {
        font-weight: 600;
        color: #475569;
        font-size: 12px;
        min-width: 80px;
    }

    /* ─── Category Cell ─── */
    .cat-main { font-weight: 600; color: #1e293b; font-size: 13px; }
    .cat-sub   { font-size: 11.5px; color: #94a3b8; margin-top: 2px; }

    /* ─── Item Name Cell ─── */
    .item-name-text {
        font-weight: 600;
        color: #1e293b;
        font-size: 13px;
        max-width: 200px;
    }

    /* ─── Price Cell ─── */
    .price-cell {
        font-weight: 700;
        color: #1e293b;
        font-size: 13px;
        white-space: nowrap;
    }
    .price-cell .currency-label {
        font-size: 10px;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-right: 2px;
    }

    /* ─── Action Buttons ─── */
    .btn-view-product {
        font-family: 'Inter', sans-serif;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 7px;
        background: rgba(245,158,11,0.12);
        color: #92640a;
        border: 1px solid rgba(245,158,11,0.3);
        display: inline-flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .btn-view-product:hover {
        background: rgba(245,158,11,0.2);
        border-color: rgba(245,158,11,0.5);
        transform: translateY(-1px);
    }

    .btn-more-actions {
        font-family: 'Inter', sans-serif;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 7px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .btn-more-actions:hover {
        background: #1e3a5f;
        color: #ffffff;
        border-color: #1e3a5f;
        transform: translateY(-1px);
    }

    /* ─── Dropdown Menu ─── */
    .custom-dropdown {
        border-radius: 10px;
        padding: 6px;
        min-width: 210px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12), 0 4px 10px rgba(0,0,0,0.06);
    }
    .custom-dropdown .dropdown-item {
        border-radius: 6px;
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 500;
        color: #334155;
        font-family: 'Inter', sans-serif;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.15s;
    }
    .custom-dropdown .dropdown-item:hover {
        background: #f1f5f9;
        color: #1e3a5f;
        padding-left: 18px;
    }
    .custom-dropdown .dropdown-divider {
        border-color: #f1f5f9;
        margin: 4px 0;
    }

    /* ─── PVM Compact Premium Modal Styling (Ameen & Sons Theme) ─── */
    #productViewModal .modal-dialog {
        max-width: 820px !important;
        margin: 1.75rem auto;
    }
    #productViewModal .modal-content {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #cbd5e1;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.18);
    }
    #productViewModal .modal-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        padding: 10px 18px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .pvm-header-icon {
        width: 32px;
        height: 32px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: #c8973a;
        flex-shrink: 0;
    }
    .pvm-section-box {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        padding: 9px 12px;
        margin-bottom: 8px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }
    .pvm-section-title {
        font-size: 10.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #1e3a5f;
        margin-bottom: 7px;
        display: flex;
        align-items: center;
        gap: 5px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 4px;
    }
    .pvm-label {
        font-size: 9.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #64748b;
        margin-bottom: 1px;
        font-family: 'Inter', sans-serif;
    }
    .pvm-value {
        font-size: 12px;
        font-weight: 600;
        color: #1e293b;
        font-family: 'Inter', sans-serif;
        line-height: 1.2;
    }
    .pvm-img-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        margin-bottom: 8px;
    }
    .pvm-stat-card {
        border-radius: 8px;
        padding: 8px 11px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        margin-bottom: 8px;
    }
    #productViewModal .standard-field,
    #productViewModal .customize-field { display: none; }
    #productViewModal .standard-field.d-show,
    #productViewModal .customize-field.d-show { display: block; }
    .color-badge {
        display: inline-block;
        padding: 2px 8px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-size: 10.5px;
        font-weight: 600;
        margin: 1px;
        color: #334155;
        font-family: 'Inter', sans-serif;
    }

    /* ─── Dropdown Appended (JS Clipping Fix) ─── */
    .dropdown-appended {
        display: none;
        position: absolute;
    }

    /* ─── Multi-Select Branch Filter Styling ─── */
    .select2-container--default .select2-selection--multiple {
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 6px !important;
        min-height: 34px !important;
        padding: 2px 5px !important;
        background-color: #ffffff !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%) !important;
        border: none !important;
        color: #ffffff !important;
        border-radius: 4px !important;
        padding: 2px 8px !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        margin-top: 2px !important;
        margin-right: 4px !important;
        display: inline-flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #ffffff !important;
        margin-right: 5px !important;
        font-weight: bold !important;
        border: none !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fca5a5 !important;
        background: transparent !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #1e3a5f !important;
        box-shadow: 0 0 0 2px rgba(30, 58, 95, 0.1) !important;
    }
</style>

@php 
    $isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : false;
    $selectedBranchIds = isset($selectedBranchIds) ? $selectedBranchIds : [];
@endphp

{{-- ==================== PRODUCT TABLE ==================== --}}
<div class="product-page-wrap">
<div class="product-card">
    <div class="product-card-header">
        <div class="header-left">
            <h5>
                <span class="icon-box"><i class="fas fa-box"></i></span>
                Product List
            </h5>
            <small>Manage all products &amp; inventory</small>
        </div>
        <div class="d-flex align-items-center" style="gap: 10px; flex-wrap: wrap;">
            @if($isSuperAdmin)
            <div class="d-flex align-items-center" style="gap: 8px; background: #f8fafc; padding: 4px 10px; border-radius: 8px; border: 1.5px solid #e2e8f0;">
                <label for="branchFilter" style="margin: 0; font-size: 11.5px; font-weight: 700; color: #475569; white-space: nowrap; text-transform: uppercase; letter-spacing: 0.4px;">
                    <i class="fas fa-code-branch text-primary mr-1"></i> Branches:
                </label>
                <div style="min-width: 240px; max-width: 380px;">
                    <select id="branchFilter" class="form-control" multiple="multiple" style="width: 100%;">
                        @foreach($branchesList as $b)
                            <option value="{{ $b->id }}" {{ (!empty($selectedBranchIds) && in_array($b->id, $selectedBranchIds)) ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if(!empty($selectedBranchIds))
                    <button type="button" id="clearBranchFilter" class="btn btn-sm btn-outline-danger" title="Show All Branches" style="padding: 3px 8px; font-size: 11px; font-weight: 700; border-radius: 6px; white-space: nowrap;">
                        <i class="fas fa-undo mr-1"></i> All
                    </button>
                @endif
            </div>
            @endif

            <a href="{{ url('create_prodcut') }}" class="btn-add-product">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session()->has('success'))
    <div style="padding: 0 24px; margin-top:16px;">
        <div class="alert alert-success alert-dismissible fade show" style="border:none; border-radius:10px; background:rgba(13,159,110,0.08); color:#0d9f6e; border-left:4px solid #0d9f6e; font-size:13px; font-family:'Inter',sans-serif;">
            <i class="fas fa-check-circle" style="margin-right:6px;"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="color:#0d9f6e;">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
    @endif

    <div class="table-responsive" style="overflow-x: auto;">
        <table id="productTable" class="table" style="width:100%">
            <thead>
                <tr>
                    <th style="width:28px; text-align:center; padding: 13px 6px;"><input type="checkbox" id="selectAll" style="cursor:pointer;"></th>
                    <th style="width:30px; text-align:center; padding: 13px 6px;">#</th>
                    <th style="white-space: nowrap; min-width: 105px;">Item Code</th>
                    @if($isSuperAdmin)
                        <th style="white-space: nowrap; min-width: 150px;">In-Stock Branches</th>
                        <th style="white-space: nowrap; min-width: 105px;">Stock Status</th>
                    @else
                        <th style="white-space: nowrap;">Stock</th>
                        <th style="white-space: nowrap; min-width: 95px;">Status</th>
                    @endif
                    <th style="width:64px; text-align:center;">Image</th>
                    <th style="white-space: nowrap;">Category / Sub</th>
                    <th style="min-width: 170px;">Item Name</th>
                    <th>Model</th>
                    <th style="white-space: nowrap;">Price</th>
                    <th style="width:80px; text-align:center;">Alert Qty</th>
                    <th>Brand</th>
                    <th style="width:130px; text-align:center;">Action</th>
                </tr>
            </thead>
                <tbody>
                    @foreach($products as $key => $product)
                    <tr class="{{ (!$isSuperAdmin && $product->is_secondary) ? 'secondary-row' : '' }}">
                        <td style="text-align:center; padding: 10px 6px;"><input type="checkbox" class="selectProduct" value="{{ $product->id }}" style="cursor:pointer;"></td>
                        <td style="text-align:center; padding: 10px 6px; color:#94a3b8; font-weight:700; font-size:12px;">{{ $key + 1 }}</td>
                        <td style="white-space: nowrap;">
                            <span class="item-code-pill">
                                @if($isSuperAdmin)
                                    {{ $product->item_code }}
                                @else
                                    {{ $product->branch_item_code ?? $product->item_code }}
                                @endif
                            </span>
                        </td>

                        {{-- 1. In-Stock Branches (Placed BEFORE Stock Status) --}}
                        @if($isSuperAdmin)
                            <td>
                                @if($product->all_warehouse_stocks && count($product->all_warehouse_stocks) > 0)
                                    @foreach($product->all_warehouse_stocks as $stock)
                                        <div class="branch-stock-row">
                                            <span class="branch-stock-name">{{ $stock['branch_name'] }}:</span>
                                            <span class="badge-qty">{{ $stock['quantity'] }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="badge-no-qty">No Stock</span>
                                @endif
                            </td>
                        @else
                            <td>
                                @if($product->branch_stock_qty > 0)
                                    <span class="badge-qty">{{ $product->branch_stock_qty }}</span>
                                @else
                                    <span class="badge-no-qty">Out</span>
                                @endif
                            </td>
                        @endif

                        {{-- 2. Stock Status --}}
                        @if($isSuperAdmin)
                            <td style="white-space: nowrap;">
                                @if($product->all_warehouse_stocks && count($product->all_warehouse_stocks) > 0)
                                    <span class="badge-in-stock"><i class="fas fa-check-circle" style="font-size:10px; margin-right:3px;"></i>In Stock</span>
                                @else
                                    <span class="badge-no-stock"><i class="fas fa-circle" style="font-size:8px; margin-right:3px;"></i>No Stock</span>
                                @endif
                            </td>
                        @else
                            <td>
                                @if($product->is_primary)
                                    <span class="badge-primary-role"><i class="fas fa-check-circle" style="font-size:10px; margin-right:3px;"></i>Primary</span>
                                @else
                                    <span class="badge-secondary-role"><i class="fas fa-circle" style="font-size:8px; margin-right:3px;"></i>Secondary</span>
                                @endif
                            </td>
                        @endif

                        <td>
                            @if($product->image)
                                <img src="{{ asset('uploads/products/' . $product->image) }}" class="prod-img-thumb">
                            @else
                                <span class="prod-no-img"><i class="fas fa-image"></i></span>
                            @endif
                        </td>
                        <td>
                            <div class="cat-main">{{ $product->category_relation->name ?? '-' }}</div>
                            <div class="cat-sub">{{ $product->sub_category_relation->name ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="item-name-text">{{ $product->item_name }}</div>
                            @if($product->branch)
                                <div style="font-size: 11px; color: #64748b; opacity: 0.68; margin-top: 3px; font-weight: 500;">
                                    <i class="fas fa-store-alt mr-1" style="font-size: 9px;"></i>Origin: {{ $product->branch->name }}
                                </div>
                            @endif
                        </td>
                        <td style="color:#64748b; font-size:12.5px;">{{ $product->model ?? '-' }}</td>
                        <td class="price-cell"><span class="currency-label">PKR</span>{{ number_format($product->price) }}</td>
                        <td style="color:#64748b; font-weight:600; font-size:13px; text-align:center;">{{ $product->alert_quantity }}</td>
                        <td style="font-size:12.5px; font-weight:500; color:#475569;">{{ $product->brand->name ?? '-' }}</td>
                        <td style="text-align:center; white-space:nowrap;">
                            <div style="display:inline-flex; align-items:center; gap:6px;">
                                <button type="button" class="btn-view-product viewProductBtn" data-id="{{ $product->id }}">
                                    <i class="fas fa-eye" style="font-size:11px;"></i> View
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn-more-actions dropdown-toggle" data-toggle="dropdown" data-boundary="window" aria-expanded="false">
                                        More <i class="fas fa-chevron-down" style="font-size:9px;"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end custom-dropdown">
                                        @if(auth()->user()->can('product.edit') || auth()->user()->can('edit product') || auth()->user()->hasAnyRole(['super admin', 'admin']))
                                            <li><a class="dropdown-item" href="{{ route('products.edit', $product->id) }}"><i class="fas fa-edit" style="color:#1e3a5f; width:16px;"></i> Edit Profile</a></li>
                                            <li><a class="dropdown-item" href="{{ route('opening.stocks.edit', $product->id) }}"><i class="fas fa-dollar-sign" style="color:#0d9f6e; width:16px;"></i> Edit Stock &amp; Pricing</a></li>
                                        @endif
                                        <li><a class="dropdown-item" href="{{ route('generate-barcode-image', $product->id) }}"><i class="fas fa-barcode" style="color:#64748b; width:16px;"></i> Generate Barcode</a></li>
                                        @if($product->is_assembled)
                                            <li><div class="dropdown-divider"></div></li>
                                            <li><a class="dropdown-item" href="{{ route('assembly.report.show', $product->id) }}"><i class="fas fa-cogs" style="color:#7c3aed; width:16px;"></i> Assembly Report</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
</div>
</div>

{{-- ==================== PRODUCT VIEW MODAL (Compact Ameen & Sons Theme) ==================== --}}
<div class="modal fade" id="productViewModal" tabindex="-1" role="dialog" aria-labelledby="pvmLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 10px; overflow: hidden;">

            {{-- HEADER --}}
            <div class="modal-header text-white border-0 px-3 py-2" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <div class="pvm-header-icon">
                        <i class="fas fa-boxes-packing" style="color: #c8973a; font-size: 14px;"></i>
                    </div>
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0 text-white" id="pvmLabel" style="font-size: 14px; letter-spacing: -0.2px;">Product Specifications</h6>
                        <div class="d-flex align-items-center" style="margin-top: 1px;">
                            <span class="badge" style="background: rgba(255,255,255,0.18); color: #ffffff; font-weight: 600; font-size: 10.5px; border-radius: 4px; padding: 1px 6px;" id="pvm_header_sub">Loading...</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none; font-size: 20px; padding: 0.6rem 1rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- BODY --}}
            <div class="modal-body p-0" style="background: #f8fafc;">
                <div class="row no-gutters">

                    {{-- LEFT COLUMN: Image + Live Stock + Pricing (Compact) --}}
                    <div class="col-md-4 bg-white border-right p-2.5 p-md-3 d-flex flex-column" style="gap: 8px;">

                        {{-- Product Image --}}
                        <div class="pvm-img-card">
                            <img id="pvm_image" src="" alt="Product" class="img-fluid" style="max-height: 100%; object-fit: contain; display: none;">
                            <div id="pvm_no_image" class="text-center text-muted p-2">
                                <i class="fas fa-image mb-1" style="font-size: 28px; color: #cbd5e1;"></i>
                                <div style="font-size: 10.5px; font-weight: 600; color: #94a3b8;">No Image</div>
                            </div>
                        </div>

                        {{-- Stock Summary Box --}}
                        <div class="pvm-stat-card" style="background: #f0fdf4; border-color: #86efac;">
                            <div class="pvm-label text-success">
                                <i class="fas fa-cubes text-success mr-1"></i> Current Stock
                            </div>
                            <div class="d-flex align-items-baseline" style="gap: 5px;">
                                <span class="font-weight-bold text-success" style="font-size: 20px; line-height: 1.1;" id="pvm_stock">0</span>
                                <span class="text-muted font-weight-bold" style="font-size: 11px;" id="pvm_unit_label">pcs</span>
                            </div>
                            <div id="pvm_low_stock_alert" class="mt-1 p-1 px-2 rounded font-weight-bold text-danger" style="background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.25); display: none; font-size: 10px;">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Low Stock! Min: <span id="pvm_alert_qty">0</span>
                            </div>
                        </div>

                        {{-- Pricing Card --}}
                        <div class="pvm-stat-card bg-white">
                            <div class="pvm-label mb-1 text-primary">
                                <i class="fas fa-tags text-primary mr-1"></i> Pricing
                            </div>
                            <div class="d-flex justify-content-between align-items-center pb-1 mb-1 border-bottom" style="font-size: 11.5px;">
                                <small class="text-muted font-weight-600">Wholesale:</small>
                                <span class="font-weight-bold text-secondary" id="pvm_wholesale">PKR 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center" style="font-size: 11.5px;">
                                <small class="text-muted font-weight-600">Retail:</small>
                                <span class="font-weight-bolder text-success" style="font-size: 13px;" id="pvm_retail">PKR 0</span>
                            </div>
                        </div>

                    </div>

                    {{-- RIGHT COLUMN: Product Specifications & Attributes (Compact) --}}
                    <div class="col-md-8 p-2.5 p-md-3">

                        <h5 class="font-weight-bold text-dark pb-1 mb-2 border-bottom" id="pvm_item_name" style="font-size: 14.5px; letter-spacing: -0.2px;">-</h5>

                        {{-- 1. Classification & Hierarchy --}}
                        <div class="pvm-section-box">
                            <div class="pvm-section-title">
                                <i class="fas fa-sitemap text-primary"></i> Classification
                            </div>
                            <div class="row" style="row-gap: 8px;">
                                <div class="col-6 col-sm-4">
                                    <div class="pvm-label">Item Code</div>
                                    <div class="pvm-value"><span class="item-code-pill" id="pvm_item_code" style="font-size: 11px; padding: 2px 7px;">-</span></div>
                                </div>
                                @if(auth()->user() && auth()->user()->hasRole('super admin'))
                                <div class="col-6 col-sm-4">
                                    <div class="pvm-label">Origin Branch</div>
                                    <div class="pvm-value"><span class="badge-branch" id="pvm_branch" style="font-size: 10.5px; padding: 2px 8px;">-</span></div>
                                </div>
                                @endif
                                <div class="col-6 col-sm-4">
                                    <div class="pvm-label">Category</div>
                                    <div class="pvm-value font-weight-bold" id="pvm_category">-</div>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <div class="pvm-label">Sub Category</div>
                                    <div class="pvm-value" id="pvm_subcategory">-</div>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <div class="pvm-label">Brand</div>
                                    <div class="pvm-value font-weight-bold text-primary" id="pvm_brand">-</div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Identification & Technical Codes --}}
                        <div class="pvm-section-box">
                            <div class="pvm-section-title">
                                <i class="fas fa-barcode text-info"></i> Identification &amp; Specs
                            </div>
                            <div class="row" style="row-gap: 8px;">
                                <div class="col-6 col-sm-4">
                                    <div class="pvm-label">Barcode / SKU</div>
                                    <div class="pvm-value font-monospace" style="font-family: monospace; font-size: 11.5px; font-weight: 700; color: #1e3a5f;" id="pvm_barcode">-</div>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <div class="pvm-label">Model / Series</div>
                                    <div class="pvm-value" id="pvm_model">-</div>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <div class="pvm-label">HS Code</div>
                                    <div class="pvm-value" id="pvm_hs_code">-</div>
                                </div>
                                <div class="col-12 mt-1">
                                    <div class="pvm-label mb-1">Colors</div>
                                    <div id="pvm_color">-</div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Packaging & Unit Configuration --}}
                        <div class="pvm-section-box mb-0">
                            <div class="pvm-section-title">
                                <i class="fas fa-box-open text-warning"></i> Packaging &amp; Units
                            </div>
                            <div class="row" style="row-gap: 8px;">
                                <div class="col-6 col-sm-4">
                                    <div class="pvm-label">Pack Type</div>
                                    <div class="pvm-value"><span class="badge badge-info px-2 py-0.5" style="font-size: 10px; font-weight: 700;" id="pvm_pack_type">-</span></div>
                                </div>
                                <div class="col-6 col-sm-4 standard-field">
                                    <div class="pvm-label">Base Unit</div>
                                    <div class="pvm-value" id="pvm_unit">-</div>
                                </div>
                                <div class="col-6 col-sm-4 customize-field">
                                    <div class="pvm-label">Pack Qty</div>
                                    <div class="pvm-value" id="pvm_pack_qty">-</div>
                                </div>
                                <div class="col-6 col-sm-4 customize-field">
                                    <div class="pvm-label">Pcs / Pack</div>
                                    <div class="pvm-value" id="pvm_piece_per_pack">-</div>
                                </div>
                                <div class="col-6 col-sm-4 customize-field">
                                    <div class="pvm-label">Loose Pcs</div>
                                    <div class="pvm-value" id="pvm_loose_piece">-</div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- end col-md-8 --}}
                </div>{{-- end row --}}
            </div>{{-- end modal-body --}}

            {{-- FOOTER --}}
            <div class="modal-footer bg-white py-1.5 px-3 border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-dismiss="modal" style="font-size: 11.5px; font-weight: 600; border-radius: 6px;">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                @if(auth()->user()->can('product.edit') || auth()->user()->can('edit product') || auth()->user()->hasAnyRole(['super admin', 'admin']))
                    <a href="#" id="pvm_edit_btn" class="btn btn-sm px-3" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); color: #ffffff; font-size: 11.5px; font-weight: 700; border-radius: 6px; box-shadow: 0 2px 6px rgba(30,58,95,0.2);">
                        <i class="fas fa-edit mr-1"></i> Edit Stock &amp; Pricing
                    </a>
                @endif
            </div>

        </div>{{-- end modal-content --}}
    </div>{{-- end modal-dialog --}}
</div>{{-- end modal --}}

<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

@else
    <div class="container py-4">
        <div class="alert alert-danger">You do not have permission to view Products.</div>
    </div>
@endcan
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {

    // ===== DataTable =====
    if ($.fn.DataTable) {
        $('#productTable').DataTable({
            responsive: false, // Disabled to prevent the green '+' icons and weird column collapsing
            scrollX: true,     // Enable native DataTables horizontal scrolling
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            language: { search: "_INPUT_", searchPlaceholder: "Search products..." }
        });
    }

    // ===== Select All =====
    $('#selectAll').on('click', function () {
        $('.selectProduct').prop('checked', this.checked);
    });

    // ===== Multi-Select Branch Filter for Super Admin =====
    if ($('#branchFilter').length) {
        $('#branchFilter').select2({
            placeholder: "🌐 All Branches (Click to select)",
            allowClear: false,
            width: '100%',
            closeOnSelect: true
        });

        $('#branchFilter').on('change', function () {
            var selected = $(this).val(); // array of IDs e.g. ["1", "2"]
            var url = new URL(window.location.href);
            
            // Remove previous branch query parameters
            url.searchParams.delete('branch_ids[]');
            url.searchParams.delete('branch_ids');
            url.searchParams.delete('branch_id');

            if (selected && selected.length > 0) {
                selected.forEach(function (id) {
                    url.searchParams.append('branch_ids[]', id);
                });
            }

            window.location.href = url.toString();
        });

        $('#clearBranchFilter').on('click', function () {
            var url = new URL(window.location.href);
            url.searchParams.delete('branch_ids[]');
            url.searchParams.delete('branch_ids');
            url.searchParams.delete('branch_id');
            window.location.href = url.toString();
        });
    }

    // --- DROPDOWN CLIPPING FIX ---
    var closeTimer;

    function openDropdown($el) {
        var $dropdown = $el.data('dropdown-menu');
        if (!$dropdown || $dropdown.length === 0) {
            $dropdown = $el.closest('.btn-group').find('.dropdown-menu');
            $el.data('dropdown-menu', $dropdown);
        }
        if (!$dropdown || $dropdown.length === 0) return;

        $('.dropdown-appended').not($dropdown).hide();

        if (!$dropdown.hasClass('dropdown-appended')) {
            $('body').append($dropdown);
            $dropdown.addClass('dropdown-appended');
        }
        
        $dropdown.show();
        var offset = $el.offset();
        var leftPos = offset.left - ($dropdown.outerWidth() - $el.outerWidth());
        if (leftPos < 0) leftPos = 10;

        $dropdown.css({
            'position': 'absolute',
            'top': offset.top + $el.outerHeight(),
            'left': leftPos,
            'z-index': 10500
        });
    }

    // Open on Hover
    $(document).on('mouseenter', '.dropdown-toggle', function() {
        clearTimeout(closeTimer);
        openDropdown($(this));
    });

    // Open on Click
    $(document).on('click', '.dropdown-toggle', function (e) {
        e.stopPropagation();
        openDropdown($(this));
    });

    // Close when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.dropdown-toggle').length && !$(e.target).closest('.dropdown-menu').length) {
            $('.dropdown-appended').hide();
        }
    });

    // Small delay to move from button to menu
    $(document).on('mouseleave', '.dropdown-toggle', function() {
        var $el = $(this);
        var $dropdown = $el.data('dropdown-menu');
        if ($dropdown && $dropdown.is(':visible')) {
            closeTimer = setTimeout(function() {
                $dropdown.hide();
            }, 150); 
        }
    });

    // Keep open if moving into menu
    $(document).on('mouseenter', '.dropdown-appended', function() {
        clearTimeout(closeTimer);
    });

    // Close when leaving menu
    $(document).on('mouseleave', '.dropdown-appended', function() {
        $(this).hide();
    });

});

// ===== Product View Modal (Bootstrap 4 compatible) =====
$(document).on('click', '.viewProductBtn', function () {
    var productId = $(this).data('id');

    // Reset modal state
    $('#pvm_header_sub').text('Loading...');
    $('#pvm_item_name').text('...');
    $('#pvm_image').hide();
    $('#pvm_no_image').show();
    $('#pvm_color').html('-');
    $('#pvm_low_stock_alert').hide();
    $('#productViewModal .standard-field').removeClass('d-show');
    $('#productViewModal .customize-field').removeClass('d-show');

    // ✅ Bootstrap 4: Use data-dismiss="modal" and jQuery .modal()
    $('#productViewModal').modal('show');

    $.ajax({
        url: '{{ url("productview") }}/' + productId,
        type: 'GET',
        success: function (p) {

            // Header
            $('#pvm_header_sub').text((p.item_code || '-') + ' | ' + (p.brand ? p.brand.name : 'No Brand'));
            $('#pvm_item_name').text(p.item_name || '-');

            // Stock
            var stock    = parseFloat(p.stock ? p.stock.qty : 0);
            var alertQty = parseFloat(p.alert_quantity || 0);
            $('#pvm_stock').text(stock);
            $('#pvm_alert_qty').text(alertQty);
            $('#pvm_unit_label').text(p.unit ? p.unit.name : 'pcs');
            if (alertQty > 0 && stock <= alertQty) { $('#pvm_low_stock_alert').show(); }

            // Pricing
            $('#pvm_wholesale').text('PKR ' + parseFloat(p.wholesale_price || 0).toLocaleString());
            $('#pvm_retail').text('PKR ' + parseFloat(p.price || 0).toLocaleString());

            // Image
            if (p.image) {
                $('#pvm_image').attr('src', '{{ asset("uploads/products") }}/' + p.image).show();
                $('#pvm_no_image').hide();
            }

            // Classification
            $('#pvm_item_code').text(p.item_code || '-');
            $('#pvm_branch').text(p.branch ? p.branch.name : '-');
            $('#pvm_category').text(p.category_relation ? p.category_relation.name : '-');
            $('#pvm_subcategory').text(p.sub_category_relation ? p.sub_category_relation.name : '-');
            $('#pvm_brand').text(p.brand ? p.brand.name : '-');

            // Identification
            $('#pvm_barcode').text(p.barcode_path || '-');
            $('#pvm_model').text(p.model || '-');
            $('#pvm_hs_code').text(p.hs_code || '-');

            // Colors (safe parse)
            if (p.color) {
                try {
                    var cols = (typeof p.color === 'string') ? JSON.parse(p.color) : p.color;
                    var html = Array.isArray(cols)
                        ? cols.map(function(c){ return '<span class="color-badge">'+c+'</span>'; }).join('')
                        : '<span class="color-badge">'+p.color+'</span>';
                    $('#pvm_color').html(html);
                } catch(e) { $('#pvm_color').text(p.color); }
            } else {
                $('#pvm_color').text('-');
            }

            // Packaging
            $('#pvm_pack_type').text(p.pack_type || '-');
            $('#pvm_pack_qty').text(p.pack_qty || '-');
            $('#pvm_piece_per_pack').text(p.piece_per_pack || '-');
            $('#pvm_loose_piece').text(p.loose_piece || '-');

            if (p.pack_type === 'Standard') {
                $('#productViewModal .standard-field').addClass('d-show');
                $('#pvm_unit').text('Piece');
            } else if (p.pack_type === 'Customize') {
                $('#productViewModal .customize-field').addClass('d-show');
                $('#pvm_unit').text(p.unit ? p.unit.name : '-');
            } else {
                $('#pvm_unit').text(p.unit ? p.unit.name : '-');
            }

            // Edit link — opens the Opening Stock Edit page for this product
            $('#pvm_edit_btn').attr('href', '{{ url("opening-stocks") }}/' + p.id + '/edit');
        },
        error: function (xhr) {
            $('#productViewModal').modal('hide');
            Swal.fire('Error', 'Could not load product. (Status: ' + xhr.status + ')', 'error');
        }
    });
});
</script>
@endsection
