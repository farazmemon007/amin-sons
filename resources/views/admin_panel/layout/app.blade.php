{{-- @include('admin_panel.layout.header') --}}

{{-- @yield('content')
@include('admin_panel.layout.footer') --}}



<!DOCTYPE html>
<html class="no-js" lang="zxx">

<head>
    <style>
        /* Logo Styling - Beautiful Brand Header */
        .nav_logo.rt_logo {
            font-size: 1.5rem !important;
            font-family: 'Segoe UI', 'Trebuchet MS', sans-serif !important;
            font-weight: 700 !important;
            letter-spacing: 0.8px !important;
            color: #1e3a5f !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            padding: 8px 16px !important;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: inline-block;
            background: linear-gradient(135deg, rgba(30, 58, 95, 0.05), rgba(30, 58, 95, 0));
            border-left: 4px solid #1e3a5f;
            text-transform: uppercase !important;
            letter-spacing: 1.2px !important;
        }

        .nav_logo.rt_logo:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 58, 95, 0.25);
            text-shadow: 0 3px 6px rgba(0, 0, 0, 0.12);
        }

        .nav_logo.rt_logo i {
            font-size: 1.8rem !important;
            color: #1e3a5f !important;
            margin-right: 8px !important;
            vertical-align: middle;
            animation: fadeInScale 0.5s ease;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .rt_nav_wrapper {
            min-height: 60px;
        }

        /* ERP Mega Menu & Normal Submenu Compact Styling */
        .nav-item .submenu,
        .mega-menu .submenu {
            background: #fff;
            padding: 12px;
            /* compact padding */
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .mega-menu .category-heading {
            font-size: 13px;
            font-weight: 600;
            color: #34495e;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #eaeaea;
        }

        .nav-item .submenu-item li,
        .mega-menu .submenu-item li {
            margin-bottom: 4px;
            /* less spacing */
        }

        .nav-item .submenu-item li a,
        .mega-menu .submenu-item li a {
            display: flex;
            align-items: center;
            font-size: 15px;
            /* smaller font */
            color: #555;
            padding: 4px 8px;
            /* compact padding */
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .nav-item .submenu-item li a i,
        .mega-menu .submenu-item li a i {
            font-size: 14px;
            margin-right: 6px;
            color: #2980b9;
            min-width: 18px;
            text-align: center;
        }

        .nav-item .submenu-item li a:hover,
        .mega-menu .submenu-item li a:hover {
            background: #f1f7fd;
            color: #2980b9;
            font-weight: 500;
        }

        /* Make 5 columns distribute evenly across full width */
        .col-group-wrapper.row {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            margin-right: 0 !important;
            margin-left: 0 !important;
        }

        .col-group {
            flex: 1 1 20% !important;  /* Each column takes 20% (5 columns = 100%) */
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
        html {
            scrollbar-gutter: stable; /* Prevent layout shift when scrollbar appears/disappears */
            overflow-y: scroll; /* Always show scrollbar space */
        }
        
        body {
            overflow-y: auto;
        }
        
        /* Prevent body scroll when modal is open */
        body.modal-open {
            overflow: hidden;
            padding-right: 0 !important; /* Bootstrap adds padding, remove it to avoid double-shift */
        }
        
        /* Modal backdrop fix */
        .modal-backdrop {
            z-index: 1040;
        }
        
        .modal.show {
            z-index: 1050;
        }

        /* ✅ Fix Navbar Wrapping Issue - Ensure all items stay in one row */
        .nav.page-navigation {
            display: flex !important;
            flex-wrap: nowrap !important;
            width: 100% !important;
            justify-content: center !important; /* Center items for better balance */
        }

        .nav.page-navigation .nav-item {
            flex: 0 0 auto !important;
        }

        .nav.page-navigation .nav-item .nav-link {
            padding: 25px 8px !important; /* Even more compact padding (8px) */
            white-space: nowrap !important;
            display: flex !important;
            align-items: center !important;
        }

        .nav.page-navigation .nav-item .menu-title {
            font-size: 13px !important;
            margin-left: 6px !important; /* Reduced margin */
        }

        /* Responsive adjustments for medium screens */
        @media (max-width: 1400px) {
            .nav.page-navigation .nav-item .nav-link {
                padding: 20px 8px !important;
            }
            .nav.page-navigation .nav-item .menu-title {
                font-size: 13px !important;
            }
        }

        @media (max-width: 1200px) {
            .nav-bottom .container {
                max-width: 100% !important;
            }
            .nav.page-navigation {
                overflow-x: auto !important; /* Allow scroll if still too wide */
                padding-bottom: 5px;
            }
            /* Hide scrollbar for clean look */
            .nav.page-navigation::-webkit-scrollbar {
                display: none;
            }
            .nav.page-navigation {
                -ms-overflow-style: none;  /* IE and Edge */
                scrollbar-width: none;  /* Firefox */
            }
        }

        /* ✅ Fix Submenu Closing Issue - Bridge the gap between tab and dropdown */
        .nav.page-navigation .nav-item {
            position: relative !important;
        }

        .nav.page-navigation .nav-item .submenu {
            top: 100% !important;
            margin-top: -20px !important; /* Overlap the parent nav-item to eliminate gap */
            padding-top: 20px !important; /* Cushion inside the menu */
            transition: none !important; /* Remove transition for instant tab switching */
            z-index: 999 !important;
        }

        /* Ensure the current hovered item is always on top */
        .nav.page-navigation .nav-item:hover {
            z-index: 1000 !important;
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
            <div class="top_nav flex-grow-1">
                <div class="container d-flex flex-row h-100 align-items-center">
                    <!--=========================*
                              Logo
                *===========================-->
                    <div class="text-center rt_nav_wrapper d-flex align-items-center">
                        {{-- <a class="nav_logo rt_logo" href="index.html"><img  src="{{asset('assets/images/WIJDAN-removebg-preview.png')}}" alt="logo" /></a> --}}
                        <a class="nav_logo rt_logo text-success" href="index.html" style="font-size: 1.2rem; font-weight: 600; letter-spacing: 0.5px;">
                            @if(Auth::user()->hasRole('super admin'))
                                <i class="fas fa-crown" style="color: #1e3a5f; margin-right: 5px;"></i>Ameen & Sons
                            @else
                                <i class="fas fa-store" style="color: #1e3a5f; margin-right: 5px;"></i>{{ Auth::user()->branch->name ?? 'Branch' }}
                            @endif
                        </a>
                        {{-- <a class="nav_logo nav_logo_mob" href="index.html"><img src="{{asset('assets/images/WIJDAN-removebg-preview.png')}}" alt="logo"/></a> --}}
                    </div>
                    <!--=========================*
                           End Logo
               *===========================-->
                    <div class="nav_wrapper_main d-flex align-items-center justify-content-between flex-grow-1">
                        <ul class="navbar-nav navbar-nav-right mr-0 ml-auto">
                            <!-- Notification Icon -->
                            @include('components.notification-icon')

                            <li class="nav-item nav-profile dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="profileDropdown">
                                    <span class="profile_name">{{ Auth::user()->name }} <i class="feather ft-chevron-down"></i></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right navbar-dropdown pt-2" aria-labelledby="profileDropdown">
                                    <span role="separator" class="divider"></span>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="ti-power-off text-dark mr-3"></i> Logout
                                        </button>
                                    </form>
                                    {{-- </a> --}}
                                </div>
                            </li>
                            <!--==================================*
                                 End Profile Menu
                        *====================================-->
                        </ul>
                        <!--=========================*
                               Mobile Menu
                   *===========================-->
                        <button class="navbar-toggler align-self-center" type="button" data-toggle="minimize">
                            <span class="feather ft-menu text-white"></span>
                        </button>
                        <!--=========================*
                           End Mobile Menu
                   *===========================-->
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
                            <a href="{{ url("/")}}" class="nav-link"><i class="menu_icon feather ft-home"></i><span class="menu-title">Dashboard</span></a>

                        </li>
                        <!--=========================*
                         📦 Products Management
                    *===========================-->
                        @can('product.view')
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
                                    {{-- ✅ Phase 2: Opening Stocks Link --}}
                                    <li><a href="{{route('opening.stocks.index')}}"><i class="fas fa-hourglass-half" style="color: #ffc107;"></i> ⏳ Opening Stocks <span class="badge badge-warning badge-pill" id="incomplete-count" style="display: none;"></span></a></li>
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
                        @endcan

                        <!--=========================*
                         � Purchase & Inventory
                    *===========================-->
                      @can('purchase.view')
<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="menu_icon fas fa-boxes"></i>
        <span class="menu-title">Purchase & Stock</span>
        <i class="menu-arrow"></i>
    </a>
    <div class="submenu">
        <ul class="submenu-item" style="display: flex; flex-direction: row; gap: 20px; list-style: none;">
            
            <li style="flex: 1;">
                <a href="#" style="font-weight: 600; color: #2980b9; padding: 6px 8px; cursor: default;" onclick="return false;">
                    <i class="fas fa-shopping-cart"></i> Purchase Orders
                </a>
                <ul style="list-style: none; padding: 0;">
                    @can('purchase.view') {{-- Using purchase.view for PO access --}}
                    <li><a href="{{route('purchase_orders.index')}}"><i class="fas fa-clipboard-list"></i> PO List</a></li>
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
                    @can('vendor.ledger')
                    <li><a href="{{ route('vendors.ledger') }}"><i class="fas fa-book"></i> Vendor Ledger</a></li>
                    @endcan
                </ul>
            </li>

            <li style="flex: 1; border-left: 1px solid #eee; padding-left: 15px;">
                <a href="#" style="font-weight: 600; color: #2980b9; padding: 6px 8px; cursor: default;" onclick="return false;">
                    <i class="fas fa-warehouse"></i> Warehouse Managment
                </a>
                <ul style="list-style: none; padding: 0;">
                    @can('warehouse.view')
                    <li><a href="{{url('warehouse')}}"><i class="fas fa-building"></i> Warehouses</a></li>
                    @endcan
                    @if(Auth::user()->can('warehouse.manage') || Auth::user()->hasRole('super admin'))
                    <li><a href="{{ url('/admin/branch-warehouse') }}"><i class="fas fa-sitemap"></i> WH Assignments</a></li>
                    @endif
                    {{-- @can('warehouse.stock.view')
                    <li><a href="{{url('warehouse_stocks')}}"><i class="fas fa-boxes"></i> Warehouse Stocks</a></li>
                    @endcan --}}
                    <!-- @can('stock.transfer.view')
                    <li><a href="{{url('stock_transfers')}}"><i class="fas fa-exchange-alt"></i> Stock Transfers</a></li>
                    @endcan -->
                    @can('warehouse.orders.view')
                    <li><a href="{{url('warehouse_orders')}}"><i class="fas fa-file-alt"></i> Warehouse Orders</a></li>
                    @endcan
                    {{-- <li><a href="{{url('inter-branch/stock-requests')}}"><i class="fas fa-random"></i> Inter-Branch Transfers</a></li> --}}
                </ul>
            </li>
            <li style="flex: 1; border-left: 1px solid #eee; padding-left: 15px;">
                <a href="#" style="font-weight: 600; color: #2980b9; padding: 6px 8px; cursor: default;" onclick="return false;">
                    <i class="fas fa-warehouse"></i> Inventory Management
                </a>
                <ul style="list-style: none; padding: 0;">
                    {{-- @can('warehouse.view')
                    <li><a href="{{url('warehouse')}}"><i class="fas fa-building"></i> Warehouses</a></li>
                    @endcan --}}
                    {{-- @if(Auth::user()->can('warehouse.manage') || Auth::user()->hasRole('super admin'))
                    <li><a href="{{ url('/admin/branch-warehouse') }}"><i class="fas fa-sitemap"></i> WH Assignments</a></li>
                    @endif --}}
                    @can('warehouse.stock.view')
                    <li><a href="{{url('warehouse_stocks')}}"><i class="fas fa-boxes"></i> Warehouse Stocks</a></li>
                    @endcan
                    @can('stock.transfer.view')
                    <li><a href="{{url('stock_transfers')}}"><i class="fas fa-exchange-alt"></i> Stock Transfers</a></li>
                    @endcan
                    {{-- @can('warehouse.orders.view')
                    <li><a href="{{url('warehouse_orders')}}"><i class="fas fa-file-alt"></i> Warehouse Orders</a></li>
                    @endcan --}}
                    <li><a href="{{url('inter-branch/stock-requests')}}"><i class="fas fa-random"></i> Inter-Branch Transfers</a></li>
                </ul>
            </li>

        </ul>
    </div>
</li>
@endcan

                        <!--=========================*
                         💰 Sales & Customers
                    *===========================-->
                        @can('sale.view')
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
                                    <li><a href="{{url("OutwardGatepass/list")}}"><i class="fas fa-list"></i>Outward Gate Pass</a></li>
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
                        @endcan

                        <!--=========================*
                         🔔 Notifications
                    *===========================-->
                        <li class="nav-item">
                            <a href="{{route('notifications.index')}}" class="nav-link">
                                <i class="menu_icon fas fa-bell"></i>
                                <span class="menu-title">Notifications</span>
                            </a>
                        </li>

                        {{-- ✅ Find Document --}}
                        <li class="nav-item">
                            <a href="{{ route('find.index') }}" class="nav-link">
                                <i class="menu_icon fas fa-search"></i>
                                <span class="menu-title">Find</span>
                            </a>
                        </li>

                        <!-- Vouchers Menu -->
                        @can('voucher.view')
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="menu_icon feather ft-clipboard"></i>
                                <span class="menu-title">Vouchers</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="submenu">
                                <ul class="submenu-item">
                                    @can('chart.of.accounts.view')
                                    <li><a href="{{ route('view_all') }}"><i class="fa-solid fa-money-bill-wave"></i> Char Of Accounts</a></li>
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
                                    <li><a href="{{ route('vouchers.index', 'journal voucher') }}"><i class="fa-solid fa-wallet"></i> Journal Voucher</a></li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endcan

                        <!-- Reports Menu -->
                        @can('report.item.stock.view')
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="menu_icon feather ft-clipboard"></i>
                                <span class="menu-title">Reports</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="submenu">
                                <ul class="submenu-item">
                                    @can('report.customer.ledger.view')
                                    <li><a href="{{ route('report.customer.ledger.new') }}"><i class="fa-solid fa-users"></i> Customer ledger Report</a></li>
                                    @endcan
                                    @can('report.vendor.ledger.view')
                                    <li><a href="{{ route('vendors-ledger') }}"><i class="fa-solid fa-users"></i> Vendor ledger Report</a></li>
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
                        @endcan

                        <!-- User Management Menu -->
                        @can('user.view')
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
                        @endcan


    </ul>
    </div>
    </div>
    </nav>

        @yield('content')

        <footer>
            <div class="footer-area">
                <p>&copy; Copyright 2025. All right reserved. Ameen & Sons .</p>
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

    @yield('js')

</body>

</html>
