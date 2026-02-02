{{-- @include('admin_panel.layout.header') --}}

{{-- @yield('content')
@include('admin_panel.layout.footer') --}}



<!DOCTYPE html>
<html class="no-js" lang="zxx">

<head>
    <style>
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
    </style>
    <!--=========================*
                Met Data
    *===========================-->
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Zare Bootstrap 4 Admin Template">

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('assets/vendors/data-table/css/responsive.jqueryui.min.css') }}">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Online Links --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/brands.min.css" integrity="sha512-58P9Hy7II0YeXLv+iFiLCv1rtLW47xmiRpC1oFafeKNShp8V5bKV/ciVtYqbk2YfxXQMt58DjNfkXFOn62xE+g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/brands.min.css" integrity="sha512-58P9Hy7II0YeXLv+iFiLCv1rtLW47xmiRpC1oFafeKNShp8V5bKV/ciVtYqbk2YfxXQMt58DjNfkXFOn62xE+g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
                        <a class="nav_logo rt_logo text-success" href="index.html">Ameen And Son's</a>
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
                              Management Menu
                    *===========================-->
                        @can('product.view')
                        <li class="nav-item mega-menu">
                            <a href="#" class="nav-link">
                                <i class="menu_icon fas fa-cogs"></i>
                                <span class="menu-title">Management</span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="submenu">
                                <div class="col-group-wrapper row">

                                    <!-- Products & Categories -->
                                    <div class="col-group col-md-3">
                                        <p class="category-heading">Products & Categories</p>
                                        <ul class="submenu-item">
                                            @can('product.view')
                                            <li><a href="{{route('product')}}"><i class="fas fa-box"></i> Products</a></li>
                                            @endcan
                                            @can('product.discount.view')
                                            <li><a href="{{route('discount.index')}}"><i class="fas fa-tags"></i> Discount Products</a></li>
                                            @endcan
                                            @can('category.view')
                                            <li><a href="{{route('Category.home')}}"><i class="fas fa-list"></i> Category</a></li>
                                            @endcan
                                            @can('subcategory.view')
                                            <li><a href="{{route('subcategory.home')}}"><i class="fas fa-th-list"></i> Sub Category</a></li>
                                            @endcan
                                            @can('brand.view')
                                            <li><a href="{{route('Brand.home')}}"><i class="fas fa-trademark"></i> Brands</a></li>
                                            @endcan
                                            @can('unit.view')
                                            <li><a href="{{route('Unit.home')}}"><i class="fas fa-balance-scale"></i> Units</a></li>
                                            @endcan
                                            @can('stock.adjust')
                                            <li><a href="{{ route('stock.adjust.form') }}"><i class="fas fa-sliders-h"></i> Parts Adjust</a></li>
                                            @endcan
                                        </ul>
                                    </div>

                                    <!-- Purchase & Inventory -->
                                    <div class="col-group col-md-3">
                                        <p class="category-heading">Purchase & Inventory</p>
                                        <ul class="submenu-item">
                                            @can('inward.gatepass.view')
                                            <li><a href="{{route('InwardGatepass.home')}}"><i class="fas fa-shopping-cart"></i> Inward Gatepass </a></li>
                                            <li><a href="{{route('add_inwardgatepass')}}"><i class="fas fa-shopping-cart"></i> Add Inward Gatepass </a></li>
                                            @endcan
                                            @can('purchase.view')
                                            <li><a href="{{route('Purchase.home')}}"><i class="fas fa-shopping-cart"></i> Purchase</a></li>
                                            @endcan
                                            @can('vendor.view')
                                            <li><a href="{{url('vendor')}}"><i class="fas fa-truck"></i> Vendor</a></li>
                                            @endcan
                                        </ul>
                                    </div>

                                    <!-- Accounts -->
                                    <div class="col-group col-md-3">
                                        <p class="category-heading">Accounts</p>
                                        <ul class="submenu-item">
                                            @can('warehouse.view')
                                            <li><a href="{{url('warehouse')}}"><i class="fas fa-warehouse"></i> Warehouse</a></li>
                                            @endcan
                                            @can('warehouse.stock.view')
                                            <li><a href="{{url('warehouse_stocks')}}"><i class="fas fa-boxes"></i> Warehouse Stock</a></li>
                                            @endcan
                                            @can('stock.transfer.view')
                                            <li><a href="{{url('stock_transfers')}}"><i class="fas fa-exchange-alt"></i> Stock Transfer</a></li>
                                            @endcan
                                        </ul>
                                    </div>
                                    <!-- Customers & Sales -->
                                    <div class="col-group col-md-3">
                                        <p class="category-heading">Sales & Customers</p>
                                        <ul class="submenu-item">
                                            @can('sale.view')
                                            <li><a href="{{url('sale')}}"><i class="fas fa-receipt"></i> Sales</a></li>
                                            @endcan
                                            @can('customer.view')
                                            <li><a href="{{url('customers')}}"><i class="fas fa-user"></i> Customer</a></li>
                                            @endcan
                                            @can('sales.officer.view')
                                            <li><a href="{{url('sales-officers')}}"><i class="fas fa-user-tie"></i> Sales Officer</a></li>
                                            @endcan
                                            @can('zone.view')
                                            <li><a href="{{url('zone')}}"><i class="fas fa-map-marker-alt"></i> Zone</a></li>
                                            @endcan
                                        </ul>
                                    </div>

                                    <!-- Notifications & Alerts -->
                                    <div class="col-group col-md-3">
                                        <p class="category-heading">Notifications & Alerts</p>
                                        <ul class="submenu-item">
                                            <li><a href="{{route('notifications.index')}}"><i class="fas fa-bell"></i> All Notifications</a></li>
                                        </ul>
                                    </div>

                                </div>
                            </div>
                        </li>
                        @endcan

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
                                    @can('report.item.stock.view')
                                    <li><a href="{{ route('report.item_stock') }}"><i class="fa-solid fa-users"></i> Item Stock Report</a></li>
                                    @endcan
                                    @can('report.purchase.view')
                                    <li><a href="{{ route('report.purchase') }}"><i class="fa-solid fa-users"></i> Purchase Report</a></li>
                                    @endcan
                                    @can('report.sale.view')
                                    <li><a href="{{ route('report.sale') }}"><i class="fa-solid fa-users"></i> Sale Report</a></li>
                                    @endcan
                                    @can('report.customer.ledger.view')
                                    <li><a href="{{ route('report.customer.ledger') }}"><i class="fa-solid fa-users"></i> Customer Ledger</a></li>
                                    @endcan
                                    @can('report.assembly.view')
                                    <li><a href="{{route('assembly.report')}}"><i class="fas fa-cogs"></i> Assembly Report</a></li>
                                    @endcan
                                    @can('report.inventory.onhand.view')
                                    <li><a href="{{ route('reports.onhand') }}"><i class="fas fa-warehouse"></i> Inventory On-Hand</a></li>
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

    @yield('js')

</body>

</html>
