{{-- @include('admin_panel.layout.header') --}}

{{-- @yield('content')
@include('admin_panel.layout.footer') --}}



<!DOCTYPE html>
<html class="no-js" lang="zxx">

<head>
    {{-- ✅ Premium CSS Design System --}}
    <link rel="stylesheet" href="{{ asset('assets/css/custom-premium.css') }}">
    <style>
        /* ─── Layout-level overrides (keep as minimal as possible) ─── */
        .rt_nav_wrapper { min-height: 60px; }

        /* Multi-column submenu layout */
        .col-group-wrapper.row {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            margin-right: 0 !important;
            margin-left: 0 !important;
        }
        .col-group {
            flex: 1 1 20% !important;
            margin-right: 0 !important;
            margin-left: 0 !important;
            min-width: 20%;
        }
    </style>
    <!--=========================*
                Met Data
    *===========================-->
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Zare Bootstrap 4 Admin Template">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!--=========================*
              Page Title
    *===========================-->
    <title>Home 2 | Zare Bootstrap 4 Admin Template</title>

    <!--=========================*
                Favicon
    *===========================-->

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/et-line.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/flag-icon.min.css') }}">
    <script src="{{ asset('assets/js/modernizr-2.8.3.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/metisMenu.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/slicknav.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/am-charts/css/am-charts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/charts/morris-bundle/morris.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/charts/c3charts/c3.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/data-table/css/jquery.dataTables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/data-table/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/data-table/css/responsive.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/data-table/css/responsive.jqueryui.min.css') }}">
    {{-- Online Links --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/brands.min.css" integrity="sha512-58P9Hy7II0YeXLv+iFiLCv1rtLW47xmiRpC1oFafeKNShp8V5bKV/ciVtYqbk2YfxXQMt58DjNfkXFOn62xE+g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/brands.min.css" integrity="sha512-58P9Hy7II0YeXLv+iFiLCv1rtLW47xmiRpC1oFafeKNShp8V5bKV/ciVtYqbk2YfxXQMt58DjNfkXFOn62xE+g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- ✅ Fix Scrollbar Jiggling & Layout Shift -->
    <style>
        /* ─── Core layout fixes ─── */
        html { overflow-y: scroll; }

        body.modal-open {
            overflow: hidden;
            padding-right: 0 !important;
        }
        .modal-backdrop { z-index: 1040; }
        .modal.show    { z-index: 1050; }

        /* ─── Navbar wrapping fix ─── */
        .nav.page-navigation {
            display: flex !important;
            flex-wrap: nowrap !important;
            width: 100% !important;
            justify-content: center !important;
        }
        .nav.page-navigation .nav-item {
            flex: 0 0 auto !important;
            position: relative !important;
        }
        .nav.page-navigation .nav-item:hover {
            z-index: 1000 !important;
        }

        /* ─── Submenu gap bridge fix ─── */
        .nav.page-navigation .nav-item .submenu {
            top: 100% !important;
            margin-top: -2px !important;
            padding-top: 10px !important;
            transition: none !important;
            z-index: 999 !important;
        }

        /* ─── Responsive ─── */
        @media (max-width: 1200px) {
            .nav-bottom .container { max-width: 100% !important; }
            .nav.page-navigation {
                overflow-x: auto !important;
                padding-bottom: 5px;
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
            .nav.page-navigation::-webkit-scrollbar { display: none; }
        }
    </style>

    @yield('css')
</head>

<body>
    <!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

    <!--=========================*
         Page Container
*===========================-->
    <div class="container-scroller">
        <!--=========================*
              Navigation
    *===========================-->
        <nav class="rt_nav_header horizontal-layout col-lg-12 col-12 p-0">
            {{-- ═══════════════ TOP BAR ═══════════════ --}}
            <div class="top_nav flex-grow-1">
                <div class="container d-flex flex-row h-100 align-items-center">

                    {{-- Brand Logo --}}
                    <div class="rt_nav_wrapper d-flex align-items-center">
                        <a class="nav_logo rt_logo" href="{{ url('/') }}">
                            @if(Auth::user()->hasRole('super admin'))
                                <i class="fas fa-crown"></i>
                                <span>Ameen &amp; Sons</span>
                            @else
                                <i class="fas fa-store"></i>
                                <span>{{ Auth::user()->branch->name ?? 'Branch' }}</span>
                            @endif
                        </a>
                    </div>

                    {{-- Right-side: Notification + User --}}
                    <div class="nav_wrapper_main d-flex align-items-center justify-content-end flex-grow-1" style="gap:4px;">
                        <ul class="navbar-nav navbar-nav-right mr-0" style="flex-direction:row; align-items:center; gap:4px;">

                            {{-- Notification Bell --}}
                            @include('components.notification-icon')

                            {{-- User Profile --}}
                            <li class="nav-item nav-profile dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-toggle="dropdown" id="profileDropdown" style="gap:8px; padding:6px 12px; border-radius:8px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15);">
                                    <span style="width:30px; height:30px; background:rgba(200,151,58,0.25); border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                        <i class="fas fa-user" style="font-size:12px; color:#f0c050;"></i>
                                    </span>
                                    <span class="profile_name">{{ Auth::user()->name }}</span>
                                    <i class="fas fa-chevron-down" style="font-size:10px; color:rgba(255,255,255,0.6);"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown" style="min-width:200px; margin-top:8px;">
                                    <div style="padding:12px 16px 10px; border-bottom:1px solid #f1f5f9;">
                                        <div style="font-weight:700; font-size:13px; color:#1e293b;">{{ Auth::user()->name }}</div>
                                        <div style="font-size:11px; color:#94a3b8; margin-top:2px;">{{ Auth::user()->email }}</div>
                                    </div>
                                    <div style="padding:6px;">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item" style="color:#dc3545;">
                                                <i class="fas fa-sign-out-alt" style="color:#dc3545;"></i>
                                                Sign Out
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        </ul>

                        {{-- Mobile hamburger --}}
                        <button class="navbar-toggler align-self-center" type="button" data-toggle="minimize" style="border:none; background:rgba(255,255,255,0.1); border-radius:6px; padding:6px 10px;">
                            <span class="feather ft-menu text-white"></span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="nav-bottom">
                <div class="container">
                    <ul class="nav page-navigation">
                        <!--=========================*
                              Home
                    *===========================-->
                        <li class="nav-item">
                            <a href="{{ url('/')}}" class="nav-link"><i class="menu_icon feather ft-home"></i><span class="menu-title">Dashboard</span></a>
                        </li>

                        <!--=========================*
                         📦 Products Management
                         Show if user has ANY product-related permission
                    *===========================-->
                        @if(Auth::user()->canAny(['product.view', 'category.view', 'subcategory.view', 'brand.view', 'unit.view']))
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="menu_icon fas fa-box"></i>
                                <span class="menu-title">Products</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="submenu">
                                <ul class="submenu-item">
                                    @can('product.view')
                                    <li><a href="{{route('product')}}"><i class="fas fa-box"></i> All Products</a></li>
                                    <li><a href="{{route('opening.stocks.index')}}"><i class="fas fa-hourglass-half" style="color: #ffc107;"></i> Opening Stocks</a></li>
                                    @endcan
                                    @can('category.view')
                                    <li><a href="{{route('Category.home')}}"><i class="fas fa-list"></i> Categories</a></li>
                                    @endcan
                                    @can('subcategory.view')
                                    <li><a href="{{route('subcategory.home')}}"><i class="fas fa-th-list"></i> Sub Categories</a></li>
                                    @endcan
                                    @can('brand.view')
                                    <li><a href="{{route('Brand.home')}}"><i class="fas fa-trademark"></i> Brands</a></li>
                                    @endcan
                                    @can('unit.view')
                                    <li><a href="{{route('Unit.home')}}"><i class="fas fa-balance-scale"></i> Units</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endif

                        <!--=========================*
                         🛒 Purchase & Stock
                         Show if user has ANY purchase/warehouse/inventory permission
                    *===========================-->
                        @if(Auth::user()->canAny(['purchase.view', 'purchase.order.view', 'purchase.order.create', 'inward.gatepass.view', 'vendor.view', 'vendor.ledger', 'warehouse.view', 'warehouse.manage', 'warehouse.stock.view', 'stock.transfer.view', 'warehouse.orders.view', 'stock.request.view']) || Auth::user()->hasRole('super admin'))
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="menu_icon fas fa-boxes"></i>
                                <span class="menu-title">Purchase & Stock</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="submenu">
                                <ul class="submenu-item" style="display: flex; flex-direction: row; gap: 20px; list-style: none;">

                                    {{-- Column 1: Purchase Orders --}}
                                    @if(Auth::user()->canAny(['purchase.view', 'purchase.order.view', 'purchase.order.create', 'inward.gatepass.view', 'vendor.view', 'vendor.ledger']))
                                    <li style="flex: 1;">
                                        <a href="#" style="font-weight: 600; color: #2980b9; padding: 6px 8px; cursor: default;" onclick="return false;">
                                            <i class="fas fa-shopping-cart"></i> Purchase Orders
                                        </a>
                                        <ul style="list-style: none; padding: 0;">
                                            @can('purchase.order.view')
                                            <li><a href="{{route('purchase_orders.index')}}"><i class="fas fa-clipboard-list"></i> PO List</a></li>
                                            @endcan
                                            @can('purchase.order.create')
                                            <li><a href="{{route('purchase_orders.create')}}"><i class="fas fa-plus-circle"></i> Create New PO</a></li>
                                            @endcan
                                            @can('inward.gatepass.view')
                                            <li><a href="{{route('InwardGatepass.home')}}"><i class="fas fa-door-open"></i> Inward Gatepasses</a></li>
                                            @endcan
                                            @can('purchase.view')
                                            <li><a href="{{route('Purchase.home')}}"><i class="fas fa-file-invoice"></i> Purchase Invoice</a></li>
                                            @endcan
                                            @can('vendor.view')
                                            <li><a href="{{url('vendorlist')}}"><i class="fas fa-truck"></i> Vendors</a></li>
                                            @endcan
                                            <!-- @can('vendor.ledger')
                                            <li><a href="{{ route('vendors.ledger') }}"><i class="fas fa-book"></i> Vendor Ledger</a></li>
                                            @endcan -->
                                        </ul>
                                    </li>
                                    @endif

                                    {{-- Column 2: Warehouse Management --}}
                                    @if(Auth::user()->canAny(['warehouse.view', 'warehouse.manage', 'warehouse.orders.view']) || Auth::user()->hasRole('super admin'))
                                    <li style="flex: 1; border-left: 1px solid #eee; padding-left: 15px;">
                                        <a href="#" style="font-weight: 600; color: #2980b9; padding: 6px 8px; cursor: default;" onclick="return false;">
                                            <i class="fas fa-warehouse"></i> Warehouse Management
                                        </a>
                                        <ul style="list-style: none; padding: 0;">
                                            @can('warehouse.view')
                                            <li><a href="{{url('warehouse')}}"><i class="fas fa-building"></i> Warehouses</a></li>
                                            @endcan
                                            @if(Auth::user()->can('warehouse.manage') || Auth::user()->hasRole('super admin'))
                                            <li><a href="{{ url('/admin/branch-warehouse') }}"><i class="fas fa-sitemap"></i> WH Assignments</a></li>
                                            @endif
                                            @can('warehouse.orders.view')
                                            <li><a href="{{url('warehouse_orders')}}"><i class="fas fa-file-alt"></i> Warehouse Orders</a></li>
                                            @endcan
                                        </ul>
                                    </li>
                                    @endif

                                    {{-- Column 3: Inventory Management --}}
                                    @if(Auth::user()->canAny(['warehouse.stock.view', 'stock.transfer.view', 'stock.request.view']))
                                    <li style="flex: 1; border-left: 1px solid #eee; padding-left: 15px;">
                                        <a href="#" style="font-weight: 600; color: #2980b9; padding: 6px 8px; cursor: default;" onclick="return false;">
                                            <i class="fas fa-warehouse"></i> Inventory Management
                                        </a>
                                        <ul style="list-style: none; padding: 0;">
                                            @can('warehouse.stock.view')
                                            <li><a href="{{url('warehouse_stocks')}}"><i class="fas fa-boxes"></i> Warehouse Stocks</a></li>
                                            @endcan
                                            @can('stock.transfer.view')
                                            <li><a href="{{url('stock_transfers')}}"><i class="fas fa-exchange-alt"></i> Stock Transfers</a></li>
                                            @endcan
                                            @can('stock.request.view')
                                            <li><a href="{{url('inter-branch/stock-requests')}}"><i class="fas fa-random"></i> Inter-Branch Transfers</a></li>
                                            @endcan
                                            @can('inter.branch.voucher.view')
                                            <li><a href="{{url('inter-branch/vouchers')}}"><i class="fas fa-receipt"></i> Inter-Branch Vouchers</a></li>
                                            @endcan
                                        </ul>
                                    </li>
                                    @endif

                                </ul>
                            </div>
                        </li>
                        @endif

                        <!--=========================*
                         💰 Sales & Customers
                         Show if user has ANY sales/customer permission
                    *===========================-->
                        @if(Auth::user()->canAny(['sale.view', 'sale.create', 'generate Dc.view', 'outward.gatepass.view', 'zone.view', 'sales.officer.view', 'customer.view', 'customerremainingproducts.view', 'find Dc.view']))
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="menu_icon fas fa-receipt"></i>
                                <span class="menu-title">Sales & Customers</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="submenu">
                                <ul class="submenu-item">
                                    <!-- Sales Section -->
                                    <li><a href="#" style="font-weight: 600; color: #2980b9; padding: 6px 8px; cursor: default;" onclick="return false;"><i class="fas fa-shopping-cart"></i> Sales Orders</a></li>
                                    @can('sale.view')
                                    <li><a href="{{url('sale')}}"><i class="fas fa-file-invoice"></i> Sales Invoices</a></li>
                                    @endcan
                                    @can('generate Dc.view')
                                    <li><a href="{{url('OutwardGatepass')}}"><i class="fas fa-file-pdf"></i> Create Delivery Challan</a></li>
                                    @endcan
                                    @can('outward.gatepass.view')
                                    <li><a href="{{url('OutwardGatepass/list')}}"><i class="fas fa-list"></i> Outward Gate Pass</a></li>
                                    @endcan
                                    @can('find Dc.view')
                                    <li><a href="{{ route('sale.find.view') }}"><i class="fas fa-search"></i> Find DC</a></li>
                                    @endcan
                                    @can('zone.view')
                                    <li><a href="{{url('zone')}}"><i class="fas fa-map-marker-alt"></i> Zones</a></li>
                                    @endcan
                                    @can('sales.officer.view')
                                    <li><a href="{{ route('sales.officer.index') }}"><i class="fas fa-user-tie"></i> Salesmen (Officers)</a></li>
                                    @endcan

                                    <li style="border-top: 1px solid #eee; margin: 4px 0; padding: 0;"></li>

                                    <!-- Customers Section -->
                                    <li><a href="#" style="font-weight: 600; color: #2980b9; padding: 6px 8px; cursor: default;" onclick="return false;"><i class="fas fa-users"></i> Customers</a></li>
                                    @can('customer.view')
                                    <li><a href="{{url('customers')}}"><i class="fas fa-user"></i> All Customers</a></li>
                                    @endcan
                                    @can('customerremainingproducts.view')
                                    <li><a href="{{url('customer-remaining')}}"><i class="fas fa-box-open"></i> Remaining Products</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endif

                        <!--=========================*
                         🔔 Notifications (always visible for logged-in users)
                    *===========================-->
                        <li class="nav-item">
                            <a href="{{route('notifications.index')}}" class="nav-link">
                                <i class="menu_icon fas fa-bell"></i>
                                <span class="menu-title">Notifications</span>
                            </a>
                        </li>

                        {{-- ✅ Complaints --}}
                        @if(Auth::user()->canAny(['complaint.view', 'complaint.create', 'warehouse.stock.view']))
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="menu_icon fas fa-exclamation-circle" style="color:#e67e22;"></i>
                                <span class="menu-title">Complaints</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="submenu">
                                <ul class="submenu-item">
                                    @can('complaint.view')
                                    <li><a href="{{ route('complaints.index') }}"><i class="fas fa-list"></i> Complaints List</a></li>
                                    @endcan
                                    @can('warehouse.stock.view')
                                    <li><a href="{{ route('complaints.damaged-stock.index') }}"><i class="fas fa-dumpster"></i> Damaged Stock</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endif

                        {{-- ✅ Find Document (always visible for logged-in users) --}}
                        <li class="nav-item">
                            <a href="{{ route('find.index') }}" class="nav-link">
                                <i class="menu_icon fas fa-search"></i>
                                <span class="menu-title">Find</span>
                            </a>
                        </li>

                        <!-- Vouchers Menu -->
                        @if(Auth::user()->canAny(['voucher.view', 'chart.of.accounts.view', 'narration.view', 'receipts.voucher.view', 'payment.voucher.view', 'expense.voucher.view', 'journal.voucher.view']))
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="menu_icon feather ft-clipboard"></i>
                                <span class="menu-title">Vouchers</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="submenu">
                                <ul class="submenu-item">
                                    @can('chart.of.accounts.view')
                                    <li><a href="{{ route('view_all') }}"><i class="fa-solid fa-money-bill-wave"></i> Chart Of Accounts</a></li>
                                    @endcan
                                    @can('narration.view')
                                    <li><a href="{{ route('narrations.index') }}"><i class="fa-solid fa-money-bill-wave"></i> Narrations</a></li>
                                    @endcan
                                    @can('receipts.voucher.view')
                                    <li><a href="{{ route('all-recepit-vochers') }}"><i class="fa-solid fa-wallet"></i> Receipts Voucher</a></li>
                                    @endcan
                                    @can('payment.voucher.view')
                                    <li><a href="{{ route('all-Payment-vochers') }}"><i class="fa-solid fa-wallet"></i> Payment Voucher</a></li>
                                    @endcan
                                    @can('expense.voucher.view')
                                    <li><a href="{{ route('all-expense-vochers') }}"><i class="fa-solid fa-money-bill-wave"></i> Expense Voucher</a></li>
                                    @endcan
                                    @can('journal.voucher.view')
                                    <li><a href="{{ route('journal.vouchers.index') }}"><i class="fa-solid fa-wallet"></i> Journal Voucher</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endif

                        <!-- Reports Menu -->
                        @if(Auth::user()->canAny(['report.item.stock.view', 'report.customer.ledger.view', 'report.vendor.ledger.view', 'report.purchase.view', 'report.sale.view', 'branch.ledger.view', 'report.assembly.view', 'report.inventory.onhand.view', 'report.stock.hold.view']))
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="menu_icon feather ft-clipboard"></i>
                                <span class="menu-title">Reports</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="submenu">
                                <ul class="submenu-item">
                                    @can('report.customer.ledger.view')
                                    <li><a href="{{ route('report.customer.ledger.new') }}"><i class="fa-solid fa-users"></i> Customer Ledger Report</a></li>
                                    @endcan
                                    @can('report.vendor.ledger.view')
                                    <li><a href="{{ route('vendors-ledger') }}"><i class="fa-solid fa-users"></i> Vendor Ledger Report</a></li>
                                    @endcan
                                    @can('report.item.stock.view')
                                    <li><a href="{{ route('report.item_stock') }}"><i class="fa-solid fa-users"></i> Item Stock Report</a></li>
                                    @endcan
                                    @can('report.purchase.view')
                                    <li><a href="{{ route('report.purchase') }}"><i class="fa-solid fa-users"></i> Purchase Report</a></li>
                                    <li><a href="{{ route('report.local_purchase') }}"><i class="fas fa-store-alt"></i> Local Purchase Report</a></li>
                                    <li><a href="{{ route('report.po_vs_gatepass') }}"><i class="fas fa-tasks"></i> PO vs Gatepass Report</a></li>
                                    @endcan
                                    @can('report.sale.view')
                                    <li><a href="{{ route('report.sale') }}"><i class="fa-solid fa-users"></i> Sale Report</a></li>
                                    <li><a href="{{ route('report.salesman.performance') }}"><i class="fa-solid fa-user-tie"></i> Salesman Performance</a></li>
                                    @endcan
                                    @can('report.customer.ledger.view')
                                    <li><a href="{{ route('report.customer.ledger') }}"><i class="fa-solid fa-users"></i> Customer Ledger</a></li>
                                    @endcan
                                    @can('branch.ledger.view')
                                    @if(Auth::user()->hasRole('super admin'))
                                        <li><a href="{{ route('branch_ledger_all_branches') }}"><i class="fa-solid fa-book"></i> All Branches Ledger</a></li>
                                    @else
                                        <li><a href="{{ route('branch_ledger_view_branch', Auth::user()->branch_id) }}"><i class="fa-solid fa-book"></i> Branch Ledger</a></li>
                                    @endif
                                    @endcan
                                    @can('report.assembly.view')
                                    <li><a href="{{route('assembly.report')}}"><i class="fas fa-cogs"></i> Assembly Report</a></li>
                                    @endcan
                                    @can('report.inventory.onhand.view')
                                    <li><a href="{{ route('reports.onhand') }}"><i class="fas fa-warehouse"></i> Inventory On-Hand</a></li>
                                    @endcan
                                    @can('report.stock.hold.view')
                                    <li><a href="{{ route('report.stock.hold.audit') }}"><i class="fas fa-boxes"></i> Stock Hold Audit</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endif

                        <!-- User Management Menu -->
                        @if(Auth::user()->canAny(['user.view', 'role.view', 'permission.view', 'branch.view']) || Auth::user()->hasRole('super admin'))
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="menu_icon feather ft-clipboard"></i>
                                <span class="menu-title">User Management</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="submenu">
                                <ul class="submenu-item">
                                    @can('user.view')
                                    <li><a href="{{ route('users.index') }}"><i class="fa-solid fa-users"></i> Users</a></li>
                                    @endcan
                                    @can('role.view')
                                    <li><a href="{{ route('roles.index') }}"><i class="fa-solid fa-user-lock"></i> Roles</a></li>
                                    @endcan
                                    @can('permission.view')
                                    <li><a href="{{ route('permissions.index') }}"><i class="fa-solid fa-user-lock"></i> Permissions</a></li>
                                    @endcan
                                    @can('branch.view')
                                    <li><a href="{{ route('branch.index') }}"><i class="fa-solid fa-code-branch"></i> Branches</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endif

                    </ul>
                </div>
            </div>
        </nav>

        @yield('content')

        <footer>
            <div class="footer-area">
                <p>&copy; {{ date('Y') }} Ameen &amp; Sons &mdash; All Rights Reserved. Powered by ERP System.</p>
            </div>
        </footer>
    </div>
    <!-- Jquery Js -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <!-- bootstrap 4 js -->
    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    <!-- Owl Carousel Js -->
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
    <!-- Metis Menu Js -->
    <script src="{{ asset('assets/js/metisMenu.min.js') }}"></script>
    <!-- SlimScroll Js -->
    <script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>
    <!-- Slick Nav -->
    <script src="{{ asset('assets/js/jquery.slicknav.min.js') }}"></script>

    <!-- start amchart js -->
    <script src="{{ asset('assets/vendors/am-charts/js/ammap.js') }}"></script>
    <script src="{{ asset('assets/vendors/am-charts/js/worldLow.js') }}"></script>
    <script src="{{ asset('assets/vendors/am-charts/js/continentsLow.js') }}"></script>
    <script src="{{ asset('assets/vendors/am-charts/js/light.js') }}"></script>
    <!-- maps js -->
    <script src="{{ asset('assets/js/am-maps.js') }}"></script>

    <!-- Morris Chart -->
    <script src="{{ asset('assets/vendors/charts/morris-bundle/raphael.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/charts/morris-bundle/morris.js') }}"></script>

    <!-- Chart Js -->
    <script src="{{ asset('assets/vendors/charts/charts-bundle/Chart.bundle.js') }}"></script>

    <!-- C3 Chart -->
    <script src="{{ asset('assets/vendors/charts/c3charts/c3.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/charts/c3charts/d3-5.4.0.min.js') }}"></script>

    <!-- Data Table js -->
    <script src="{{ asset('assets/vendors/data-table/js/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('assets/vendors/data-table/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/data-table/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/data-table/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/data-table/js/responsive.bootstrap.min.js') }}"></script>

    <!-- Sparkline Chart -->
    <script src="{{ asset('assets/vendors/charts/sparkline/jquery.sparkline.js') }}"></script>

    <!-- Home Script -->
    <script src="{{ asset('assets/js/home.js') }}"></script>

    <!-- Main Js -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- SweetAlert2 JS (globally available) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- html2pdf.js (required for reports) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <!-- Navbar Hover Logic - Instant Tab Switching -->
    <script>
    $(document).ready(function() {
        var $navItems = $('.nav.page-navigation .nav-item');
        
        $navItems.on('mouseenter', function() {
            // When entering a new tab, immediately hide all other submenus
            // This allows the current one to show without waiting for CSS transitions or delays
            $navItems.not(this).removeClass('show').find('.submenu').hide();
            $(this).addClass('show').find('.submenu').show();
        });

        // Optional: Hide when leaving the whole navigation area to be safe
        $('.nav.page-navigation').on('mouseleave', function() {
             // Let the default CSS handle the final close or force it here if needed
        });
    });
    </script>

    <script>
        @if(Session::has('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ Session::get('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if(Session::has('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ Session::get('error') }}',
            });
        @endif
    </script>

    @yield('js')

</body>

</html>
