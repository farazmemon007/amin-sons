<?php

/**
 * ERP Permission Configuration
 * ─────────────────────────────
 * Single source of truth for all modules and their permissions.
 *
 * Format:
 *   'module_key' => [
 *       'label'       => 'Human-readable Module Name',
 *       'icon'        => 'fa-icon-name',
 *       'color'       => '#hex',
 *       'permissions' => [
 *           'permission.name' => 'Human Label',
 *       ],
 *       'cross_branch' => true|false,  // Can be granted cross-branch
 *   ]
 *
 * cross_branch = true means super admin can grant these to users for OTHER branches.
 * cross_branch = false means these are own-branch-only (admin-level settings).
 */

return [

    // ─── PRODUCTS ─────────────────────────────────────────────────────────
    'products' => [
        'label'        => 'Products',
        'icon'         => 'fa-cube',
        'color'        => '#6366f1',
        'cross_branch' => true,
        'permissions'  => [
            'product.view'     => 'View',
            'product.create'   => 'Create',
            'product.edit'     => 'Edit',
            'product.delete'   => 'Delete',
            'product.barcode'  => 'Print Barcode',
            'product.assembly' => 'Assembly',
        ],
    ],

    // ─── PRODUCT DISCOUNTS ────────────────────────────────────────────────
    'product_discounts' => [
        'label'        => 'Product Discounts',
        'icon'         => 'fa-tags',
        'color'        => '#8b5cf6',
        'cross_branch' => false,
        'permissions'  => [
            'product.discount.view'        => 'View',
            'product.discount.create'      => 'Create',
            'product.discount.edit'        => 'Edit',
            'product.discount.delete'      => 'Delete',
            'product.discount.barcode'     => 'Print Barcode',
        ],
    ],

    // ─── CATEGORIES / BRANDS / UNITS ─────────────────────────────────────
    'catalog' => [
        'label'        => 'Catalog (Categories, Brands, Units)',
        'icon'         => 'fa-layer-group',
        'color'        => '#0ea5e9',
        'cross_branch' => false,
        'permissions'  => [
            'category.view'      => 'Category: View',
            'category.create'    => 'Category: Create',
            'category.edit'      => 'Category: Edit',
            'category.delete'    => 'Category: Delete',
            'subcategory.view'   => 'Subcategory: View',
            'subcategory.create' => 'Subcategory: Create',
            'subcategory.edit'   => 'Subcategory: Edit',
            'subcategory.delete' => 'Subcategory: Delete',
            'brand.view'         => 'Brand: View',
            'brand.create'       => 'Brand: Create',
            'brand.edit'         => 'Brand: Edit',
            'brand.delete'       => 'Brand: Delete',
            'unit.view'          => 'Unit: View',
            'unit.create'        => 'Unit: Create',
            'unit.edit'          => 'Unit: Edit',
            'unit.delete'        => 'Unit: Delete',
        ],
    ],

    // ─── PURCHASES ────────────────────────────────────────────────────────
    'purchases' => [
        'label'        => 'Purchases',
        'icon'         => 'fa-shopping-cart',
        'color'        => '#f59e0b',
        'cross_branch' => true,
        'permissions'  => [
            'purchase.view'    => 'View',
            'purchase.create'  => 'Create',
            'purchase.edit'    => 'Edit',
            'purchase.delete'  => 'Delete',
            'purchase.invoice' => 'Print Invoice',
        ],
    ],

    // ─── PURCHASE RETURNS ─────────────────────────────────────────────────
    'purchase_returns' => [
        'label'        => 'Purchase Returns',
        'icon'         => 'fa-undo-alt',
        'color'        => '#ef4444',
        'cross_branch' => false,
        'permissions'  => [
            'purchase.return.view'   => 'View',
            'purchase.return.create' => 'Create',
            'purchase.return.edit'   => 'Edit',
            'purchase.return.delete' => 'Delete',
        ],
    ],

    // ─── PURCHASE ORDERS ──────────────────────────────────────────────────
    'purchase_orders' => [
        'label'        => 'Purchase Orders',
        'icon'         => 'fa-file-alt',
        'color'        => '#f97316',
        'cross_branch' => false,
        'permissions'  => [
            'purchase.order.view'   => 'View',
            'purchase.order.create' => 'Create',
            'purchase.order.edit'   => 'Edit',
            'purchase.order.delete' => 'Delete',
        ],
    ],

    // ─── INWARD GATEPASS ──────────────────────────────────────────────────
    'inward_gatepass' => [
        'label'        => 'Inward Gatepass',
        'icon'         => 'fa-sign-in-alt',
        'color'        => '#22c55e',
        'cross_branch' => false,
        'permissions'  => [
            'inward.gatepass.view'   => 'View',
            'inward.gatepass.create' => 'Create',
            'inward.gatepass.edit'   => 'Edit',
            'inward.gatepass.delete' => 'Delete',
        ],
    ],

    // ─── OUTWARD GATEPASS ─────────────────────────────────────────────────
    'outward_gatepass' => [
        'label'        => 'Outward Gatepass',
        'icon'         => 'fa-sign-out-alt',
        'color'        => '#14b8a6',
        'cross_branch' => false,
        'permissions'  => [
            'outward.gatepass.view'   => 'View',
            'outward.gatepass.create' => 'Create',
            'outward.gatepass.edit'   => 'Edit',
            'outward.gatepass.delete' => 'Delete',
            'outward.gatepass.print'  => 'Print',
        ],
    ],

    // ─── SALES ────────────────────────────────────────────────────────────
    'sales' => [
        'label'        => 'Sales',
        'icon'         => 'fa-cash-register',
        'color'        => '#10b981',
        'cross_branch' => true,
        'permissions'  => [
            'sale.view'             => 'View',
            'sale.create'           => 'Create',
            'sale.edit'             => 'Edit',
            'sale.delete'           => 'Delete',
            'sale.invoice'          => 'Print Invoice',
            'sale.receipt'          => 'Print Receipt',
            'sale.delivery.challan' => 'Delivery Challan',
        ],
    ],

    // ─── SALE RETURNS ─────────────────────────────────────────────────────
    'sale_returns' => [
        'label'        => 'Sale Returns',
        'icon'         => 'fa-reply',
        'color'        => '#f43f5e',
        'cross_branch' => false,
        'permissions'  => [
            'sale.return.view'   => 'View',
            'sale.return.create' => 'Create',
            'sale.return.edit'   => 'Edit',
            'sale.return.delete' => 'Delete',
        ],
    ],

    // ─── BOOKINGS ─────────────────────────────────────────────────────────
    'bookings' => [
        'label'        => 'Bookings',
        'icon'         => 'fa-calendar-check',
        'color'        => '#a855f7',
        'cross_branch' => false,
        'permissions'  => [
            'booking.view'    => 'View',
            'booking.create'  => 'Create',
            'booking.edit'    => 'Edit',
            'booking.invoice' => 'Print Invoice',
        ],
    ],

    // ─── CUSTOMERS ────────────────────────────────────────────────────────
    'customers' => [
        'label'        => 'Customers',
        'icon'         => 'fa-users',
        'color'        => '#3b82f6',
        'cross_branch' => true,
        'permissions'  => [
            'customer.view'              => 'View',
            'customer.create'            => 'Create',
            'customer.edit'              => 'Edit',
            'customer.delete'            => 'Delete',
            'customer.ledger'            => 'Ledger',
            'customer.toggle.status'     => 'Toggle Status',
            'customer.payments.view'     => 'Payments: View',
            'customer.payments.create'   => 'Payments: Create',
            'customer.payments.delete'   => 'Payments: Delete',
            'customerremainingproducts.view'     => 'Remaining Products: View',
            'customerremainingproducts.view.all' => 'Remaining Products: View All',
        ],
    ],

    // ─── VENDORS ──────────────────────────────────────────────────────────
    'vendors' => [
        'label'        => 'Vendors',
        'icon'         => 'fa-truck',
        'color'        => '#64748b',
        'cross_branch' => true,
        'permissions'  => [
            'vendor.view'              => 'View',
            'vendor.create'            => 'Create',
            'vendor.edit'              => 'Edit',
            'vendor.delete'            => 'Delete',
            'vendor.ledger'            => 'Ledger',
            'vendor.ledger.branch.view'=> 'Branch Ledger',
            'vendor.payments.view'     => 'Payments: View',
            'vendor.payments.create'   => 'Payments: Create',
            'vendor.payments.delete'   => 'Payments: Delete',
            'vendor.bilties.view'      => 'Bilties: View',
            'vendor.bilties.create'    => 'Bilties: Create',
            'vendor.bilties.delete'    => 'Bilties: Delete',
        ],
    ],

    // ─── WAREHOUSE ────────────────────────────────────────────────────────
    'warehouse' => [
        'label'        => 'Warehouse',
        'icon'         => 'fa-warehouse',
        'color'        => '#0891b2',
        'cross_branch' => false,
        'permissions'  => [
            'warehouse.view'   => 'View',
            'warehouse.create' => 'Create',
            'warehouse.edit'   => 'Edit',
            'warehouse.delete' => 'Delete',
            'warehouse.manage' => 'Manage Users',
        ],
    ],

    // ─── WAREHOUSE STOCK ──────────────────────────────────────────────────
    'warehouse_stock' => [
        'label'        => 'Warehouse Stock',
        'icon'         => 'fa-boxes',
        'color'        => '#0284c7',
        'cross_branch' => true,
        'permissions'  => [
            'warehouse.stock.view'   => 'View',
            'warehouse.stock.create' => 'Create',
            'warehouse.stock.edit'   => 'Edit',
            'warehouse.stock.delete' => 'Delete',
            'stock.adjust'           => 'Adjust Stock',
        ],
    ],

    // ─── WAREHOUSE ORDERS ─────────────────────────────────────────────────
    'warehouse_orders' => [
        'label'        => 'Warehouse Orders',
        'icon'         => 'fa-clipboard-list',
        'color'        => '#0369a1',
        'cross_branch' => false,
        'permissions'  => [
            'warehouse.orders.view' => 'View',
            'warehouse.order.view'  => 'View Order',
            'warehouse.order.edit'  => 'Edit Order',
        ],
    ],

    // ─── STOCK TRANSFER ───────────────────────────────────────────────────
    'stock_transfer' => [
        'label'        => 'Stock Transfer',
        'icon'         => 'fa-exchange-alt',
        'color'        => '#7c3aed',
        'cross_branch' => false,
        'permissions'  => [
            'stock.transfer.view'   => 'View',
            'stock.transfer.create' => 'Create',
            'stock.transfer.edit'   => 'Edit',
            'stock.transfer.delete' => 'Delete',
        ],
    ],

    // ─── STOCK REQUEST ────────────────────────────────────────────────────
    'stock_request' => [
        'label'        => 'Stock Request (Inter-Branch)',
        'icon'         => 'fa-random',
        'color'        => '#db2777',
        'cross_branch' => false,
        'permissions'  => [
            'stock.request.view'    => 'View',
            'stock.request.create'  => 'Create',
            'stock.request.approve' => 'Approve',
            'stock.request.reject'  => 'Reject',
        ],
    ],

    // ─── VOUCHERS ─────────────────────────────────────────────────────────
    'vouchers' => [
        'label'        => 'Vouchers',
        'icon'         => 'fa-file-invoice',
        'color'        => '#9333ea',
        'cross_branch' => false,
        'permissions'  => [
            'voucher.view'                => 'General: View',
            'receipts.voucher.view'       => 'Receipts: View',
            'receipts.voucher.create'     => 'Receipts: Create',
            'receipts.voucher.print'      => 'Receipts: Print',
            'receipts.voucher.delete'     => 'Receipts: Delete',
            'payment.voucher.view'        => 'Payment: View',
            'payment.voucher.create'      => 'Payment: Create',
            'payment.voucher.print'       => 'Payment: Print',
            'payment.voucher.delete'      => 'Payment: Delete',
            'expense.voucher.view'        => 'Expense: View',
            'expense.voucher.create'      => 'Expense: Create',
            'expense.voucher.print'       => 'Expense: Print',
            'expense.voucher.delete'      => 'Expense: Delete',
            'journal.voucher.view'        => 'Journal: View',
            'journal.voucher.create'      => 'Journal: Create',
            'journal.voucher.delete'      => 'Journal: Delete',
            'narration.view'              => 'Narrations: View',
            'narration.create'            => 'Narrations: Create',
            'narration.delete'            => 'Narrations: Delete',
        ],
    ],

    // ─── INTER-BRANCH VOUCHERS ────────────────────────────────────────────
    'inter_branch_vouchers' => [
        'label'        => 'Inter-Branch Vouchers',
        'icon'         => 'fa-sitemap',
        'color'        => '#6d28d9',
        'cross_branch' => false,
        'permissions'  => [
            'inter.branch.voucher.view'   => 'View',
            'inter.branch.voucher.create' => 'Create',
            'inter.branch.voucher.delete' => 'Delete',
        ],
    ],

    // ─── CHART OF ACCOUNTS ────────────────────────────────────────────────
    'chart_of_accounts' => [
        'label'        => 'Chart of Accounts',
        'icon'         => 'fa-balance-scale',
        'color'        => '#0f766e',
        'cross_branch' => false,
        'permissions'  => [
            'chart.of.accounts.view'   => 'View',
            'chart.of.accounts.create' => 'Create',
            'chart.of.accounts.edit'   => 'Edit',
            'chart.of.accounts.delete' => 'Delete',
            'branch.account.view'      => 'Branch Account: View',
        ],
    ],

    // ─── BRANCH LEDGER ────────────────────────────────────────────────────
    'branch_ledger' => [
        'label'        => 'Branch Ledger',
        'icon'         => 'fa-book',
        'color'        => '#1d4ed8',
        'cross_branch' => false,
        'permissions'  => [
            'branch.ledger.view'   => 'View',
            'branch.ledger.report' => 'Report',
        ],
    ],

    // ─── REPORTS ──────────────────────────────────────────────────────────
    'reports' => [
        'label'        => 'Reports',
        'icon'         => 'fa-chart-bar',
        'color'        => '#b45309',
        'cross_branch' => true,
        'permissions'  => [
            'report.item.stock.view'       => 'Item Stock',
            'report.purchase.view'         => 'Purchase',
            'report.sale.view'             => 'Sale',
            'report.customer.ledger.view'  => 'Customer Ledger',
            'report.vendor.ledger.view'    => 'Vendor Ledger',
            'report.inventory.onhand.view' => 'Inventory Onhand',
            'report.stock.hold.view'       => 'Stock Hold',
            'report.assembly.view'         => 'Assembly',
        ],
    ],

    // ─── COMPLAINTS ───────────────────────────────────────────────────────
    'complaints' => [
        'label'        => 'Complaints',
        'icon'         => 'fa-exclamation-circle',
        'color'        => '#dc2626',
        'cross_branch' => false,
        'permissions'  => [
            'complaint.view'         => 'View',
            'complaint.create'       => 'Create',
            'complaint.edit'         => 'Edit',
            'complaint.delete'       => 'Delete',
            'complaint.print'        => 'Print',
            'complaint.home_service' => 'Home Service',
        ],
    ],

    // ─── FIND / GENERATE DC ───────────────────────────────────────────────
    'dc_operations' => [
        'label'        => 'DC Operations (Find / Generate)',
        'icon'         => 'fa-search',
        'color'        => '#475569',
        'cross_branch' => false,
        'permissions'  => [
            'find Dc.view'     => 'Find DC',
            'generate Dc.view' => 'Generate DC',
        ],
    ],

    // ─── SALES OFFICERS ───────────────────────────────────────────────────
    'sales_officers' => [
        'label'        => 'Sales Officers',
        'icon'         => 'fa-user-tie',
        'color'        => '#0d9488',
        'cross_branch' => false,
        'permissions'  => [
            'sales.officer.view'   => 'View',
            'sales.officer.create' => 'Create',
            'sales.officer.edit'   => 'Edit',
            'sales.officer.delete' => 'Delete',
        ],
    ],

    // ─── ZONES ────────────────────────────────────────────────────────────
    'zones' => [
        'label'        => 'Zones',
        'icon'         => 'fa-map-marker-alt',
        'color'        => '#059669',
        'cross_branch' => false,
        'permissions'  => [
            'zone.view'   => 'View',
            'zone.create' => 'Create',
            'zone.edit'   => 'Edit',
            'zone.delete' => 'Delete',
        ],
    ],

    // ─── USER MANAGEMENT ──────────────────────────────────────────────────
    'user_management' => [
        'label'        => 'User Management',
        'icon'         => 'fa-user-cog',
        'color'        => '#6366f1',
        'cross_branch' => false,
        'permissions'  => [
            'user.view'   => 'View',
            'user.create' => 'Create',
            'user.edit'   => 'Edit',
            'user.delete' => 'Delete',
        ],
    ],

    // ─── ROLES & PERMISSIONS ──────────────────────────────────────────────
    'system_roles' => [
        'label'        => 'Roles & Permissions',
        'icon'         => 'fa-shield-alt',
        'color'        => '#1e293b',
        'cross_branch' => false,
        'permissions'  => [
            'role.view'              => 'Role: View',
            'role.create'            => 'Role: Create',
            'role.edit'              => 'Role: Edit',
            'role.delete'            => 'Role: Delete',
            'role.permission.update' => 'Role: Update Permissions',
            'permission.view'        => 'Permission: View',
            'permission.create'      => 'Permission: Create',
            'permission.delete'      => 'Permission: Delete',
            'management.view'        => 'Management Panel: View',
        ],
    ],

    // ─── BRANCHES ─────────────────────────────────────────────────────────
    'branches' => [
        'label'        => 'Branches',
        'icon'         => 'fa-code-branch',
        'color'        => '#334155',
        'cross_branch' => false,
        'permissions'  => [
            'branch.view'   => 'View',
            'branch.create' => 'Create',
            'branch.edit'   => 'Edit',
            'branch.delete' => 'Delete',
        ],
    ],

    // ─── DASHBOARD ────────────────────────────────────────────────────────
    'dashboard' => [
        'label'        => 'Dashboard',
        'icon'         => 'fa-tachometer-alt',
        'color'        => '#2563eb',
        'cross_branch' => false,
        'permissions'  => [
            'view dashboard' => 'View Dashboard',
        ],
    ],
];
