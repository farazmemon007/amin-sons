@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid px-0">

            {{-- ══════════════════════════════════════════════════
                 PAGE HEADER
            ══════════════════════════════════════════════════ --}}
            <div class="d-flex align-items-center justify-content-between mb-4" style="flex-wrap:wrap; gap:12px;">
                <div>
                    <h4 style="font-weight:800; margin:0; color:var(--text-primary); font-size:22px; letter-spacing:-0.3px;">
                        Dashboard
                    </h4>
                    <p style="margin:4px 0 0; font-size:12px; color:var(--text-muted);">
                        <i class="fas fa-calendar-day" style="color:var(--brand-gold); margin-right:4px;"></i>
                        {{ \Carbon\Carbon::now()->format('l, d F Y') }}
                        &nbsp;&bull;&nbsp;
                        Welcome back, <strong style="color:var(--brand-primary);">{{ Auth::user()->name }}</strong>
                    </p>
                </div>
                <div style="background: linear-gradient(135deg, #1e3a5f, #2c5282); padding: 8px 18px; border-radius: 10px; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-building" style="color:#c8973a; font-size:13px;"></i>
                    <span style="font-size:12px; font-weight:600; color:rgba(255,255,255,0.92);">
                        @if(Auth::user()->hasRole('super admin'))
                            Super Admin &mdash; All Branches
                        @else
                            {{ Auth::user()->branch->name ?? 'Branch' }}
                        @endif
                    </span>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════
                 STAT CARDS — ROW 1 (Inventory Counts)
            ══════════════════════════════════════════════════ --}}
            <div class="row g-3 mb-3">
                {{-- Categories --}}
                <div class="col-xl-3 col-md-6">
                    <div class="as-stat-card stat-blue animated fadeInUp">
                        <div class="stat-info">
                            <div class="stat-label">Categories</div>
                            <div class="stat-value">{{ $categoryCount }}</div>
                        </div>
                        <div class="stat-icon-wrap">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                </div>

                {{-- Subcategories --}}
                <div class="col-xl-3 col-md-6">
                    <div class="as-stat-card stat-teal animated fadeInUp">
                        <div class="stat-info">
                            <div class="stat-label">Subcategories</div>
                            <div class="stat-value">{{ $subcategoryCount }}</div>
                        </div>
                        <div class="stat-icon-wrap">
                            <i class="fas fa-sitemap"></i>
                        </div>
                    </div>
                </div>

                {{-- Products --}}
                <div class="col-xl-3 col-md-6">
                    <div class="as-stat-card stat-purple animated fadeInUp">
                        <div class="stat-info">
                            <div class="stat-label">Products</div>
                            <div class="stat-value">{{ $productCount }}</div>
                        </div>
                        <div class="stat-icon-wrap">
                            <i class="fas fa-box-open"></i>
                        </div>
                    </div>
                </div>

                {{-- Customers --}}
                <div class="col-xl-3 col-md-6">
                    <div class="as-stat-card stat-gold animated fadeInUp">
                        <div class="stat-info">
                            <div class="stat-label">Customers</div>
                            <div class="stat-value">{{ $customerscount }}</div>
                        </div>
                        <div class="stat-icon-wrap">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════
                 STAT CARDS — ROW 2 (Financial Summary)
            ══════════════════════════════════════════════════ --}}
            <div class="row g-3 mb-4">
                {{-- Total Purchases --}}
                <div class="col-xl-3 col-md-6">
                    <div class="as-stat-card stat-blue">
                        <div class="stat-info">
                            <div class="stat-label">Total Purchases</div>
                            <div class="stat-value currency">Rs {{ number_format($totalPurchases, 2) }}</div>
                            <div style="font-size:11px; color:var(--text-muted); margin-top:5px;">
                                <i class="fas fa-circle" style="color:var(--color-success); font-size:7px; margin-right:4px;"></i>All-time procurement
                            </div>
                        </div>
                        <div class="stat-icon-wrap">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>

                {{-- Purchase Returns --}}
                <div class="col-xl-3 col-md-6">
                    <div class="as-stat-card stat-red">
                        <div class="stat-info">
                            <div class="stat-label">Purchase Returns</div>
                            <div class="stat-value currency">Rs {{ number_format($totalPurchaseReturns, 2) }}</div>
                            <div style="font-size:11px; color:var(--text-muted); margin-top:5px;">
                                <i class="fas fa-circle" style="color:var(--color-danger); font-size:7px; margin-right:4px;"></i>Returned to vendors
                            </div>
                        </div>
                        <div class="stat-icon-wrap">
                            <i class="fas fa-undo-alt"></i>
                        </div>
                    </div>
                </div>

                {{-- Total Sales --}}
                <div class="col-xl-3 col-md-6">
                    <div class="as-stat-card stat-green">
                        <div class="stat-info">
                            <div class="stat-label">Total Sales</div>
                            <div class="stat-value currency">Rs {{ number_format($totalSales, 2) }}</div>
                            <div style="font-size:11px; color:var(--text-muted); margin-top:5px;">
                                <i class="fas fa-circle" style="color:var(--color-success); font-size:7px; margin-right:4px;"></i>Revenue generated
                            </div>
                        </div>
                        <div class="stat-icon-wrap">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>

                {{-- Sales Returns --}}
                <div class="col-xl-3 col-md-6">
                    <div class="as-stat-card stat-orange">
                        <div class="stat-info">
                            <div class="stat-label">Sales Returns</div>
                            <div class="stat-value currency">Rs {{ number_format($totalSalesReturns, 2) }}</div>
                            <div style="font-size:11px; color:var(--text-muted); margin-top:5px;">
                                <i class="fas fa-circle" style="color:var(--color-warning); font-size:7px; margin-right:4px;"></i>Returned by customers
                            </div>
                        </div>
                        <div class="stat-icon-wrap">
                            <i class="fas fa-undo"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════
                 CHARTS
            ══════════════════════════════════════════════════ --}}
            <div class="row g-3">
                {{-- Sales Chart --}}
                <div class="col-md-12">
                    <div class="card" style="border-radius:16px; overflow:hidden; border:none; box-shadow:0 4px 6px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.06);">
                        <div class="card-header" style="background:#fff; border-bottom:1px solid #e2e8f0; padding:18px 24px; display:flex; align-items:center; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:40px; height:40px; background:rgba(13,159,110,0.1); border-radius:10px; display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-chart-area" style="color:#0d9f6e; font-size:17px;"></i>
                                </div>
                                <div>
                                    <div style="font-size:15px; font-weight:700; color:#1e293b;">Sales Report</div>
                                    <div style="font-size:11px; color:#94a3b8;">Revenue trend over selected period</div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <label for="salesFilter" style="font-size:12px; font-weight:600; color:#64748b; margin:0; white-space:nowrap;">Filter By:</label>
                                <select id="salesFilter" style="border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 14px; font-size:13px; font-weight:600; color:#334155; background:#fff; cursor:pointer; outline:none;">
                                    <option value="daily" selected>Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body" style="padding:20px 24px;">
                            <div id="salesReportChart" style="height:360px;"></div>
                        </div>
                    </div>
                </div>

                {{-- Purchase Chart --}}
                <div class="col-md-12">
                    <div class="card" style="border-radius:16px; overflow:hidden; border:none; box-shadow:0 4px 6px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.06);">
                        <div class="card-header" style="background:#fff; border-bottom:1px solid #e2e8f0; padding:18px 24px; display:flex; align-items:center; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:40px; height:40px; background:rgba(30,58,95,0.08); border-radius:10px; display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-chart-line" style="color:#1e3a5f; font-size:17px;"></i>
                                </div>
                                <div>
                                    <div style="font-size:15px; font-weight:700; color:#1e293b;">Purchase Report</div>
                                    <div style="font-size:11px; color:#94a3b8;">Procurement expenditure trend</div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <label for="purchaseFilter" style="font-size:12px; font-weight:600; color:#64748b; margin:0; white-space:nowrap;">Filter By:</label>
                                <select id="purchaseFilter" style="border:1.5px solid #e2e8f0; border-radius:8px; padding:6px 14px; font-size:13px; font-weight:600; color:#334155; background:#fff; cursor:pointer; outline:none;">
                                    <option value="daily" selected>Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body" style="padding:20px 24px;">
                            <div id="purchaseReportChart" style="height:360px;"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const salesStats = @json($salesChartStats);

        const salesOptions = {
            chart: {
                type: 'area',
                height: 360,
                toolbar: { show: false },
                fontFamily: 'Inter, Segoe UI, sans-serif',
                dropShadow: { enabled: false }
            },
            stroke: { curve: 'smooth', width: 2.5 },
            colors: ['#0d9f6e'],
            series: salesStats.daily.series,
            xaxis: {
                categories: salesStats.daily.categories,
                labels: { style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'Inter, sans-serif' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'Inter, sans-serif' } }
            },
            dataLabels: { enabled: false },
            markers: {
                size: 4,
                colors: ['#fff'],
                strokeColors: '#0d9f6e',
                strokeWidth: 2,
                hover: { size: 6 }
            },
            fill: {
                type: "gradient",
                gradient: { shadeIntensity: 1, opacityFrom: 0.2, opacityTo: 0.01, stops: [0, 95, 100] }
            },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 5 },
            tooltip: {
                theme: "light",
                y: { formatter: val => "Rs " + val.toLocaleString() }
            },
            legend: { position: 'top', labels: { colors: '#64748b', fontFamily: 'Inter, sans-serif', fontWeight: 600 } }
        };

        const salesChart = new ApexCharts(document.querySelector("#salesReportChart"), salesOptions);
        salesChart.render();

        document.getElementById('salesFilter').addEventListener('change', function() {
            const selected = this.value;
            salesChart.updateOptions({
                series: salesStats[selected].series,
                xaxis: { categories: salesStats[selected].categories }
            });
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const purchaseStats = @json($purchaseChartStats);

        const purchaseOptions = {
            chart: {
                type: 'area',
                height: 360,
                toolbar: { show: false },
                fontFamily: 'Inter, Segoe UI, sans-serif',
                dropShadow: { enabled: false }
            },
            stroke: { curve: 'smooth', width: 2.5 },
            colors: ['#1e3a5f'],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light', type: "vertical",
                    shadeIntensity: 0.2,
                    gradientToColors: ['#2c5282'],
                    inverseColors: false,
                    opacityFrom: 0.2,
                    opacityTo: 0.01,
                    stops: [0, 95, 100]
                }
            },
            series: purchaseStats.daily.series,
            xaxis: {
                categories: purchaseStats.daily.categories,
                labels: { style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'Inter, sans-serif' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'Inter, sans-serif' } }
            },
            markers: {
                size: 4,
                colors: ['#fff'],
                strokeColors: '#1e3a5f',
                strokeWidth: 2,
                hover: { size: 6 }
            },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 5 },
            legend: { position: 'top', labels: { colors: '#64748b', fontFamily: 'Inter, sans-serif', fontWeight: 600 } },
            tooltip: {
                theme: 'light',
                y: { formatter: val => "Rs " + val.toLocaleString() }
            }
        };

        const purchaseChart = new ApexCharts(document.querySelector("#purchaseReportChart"), purchaseOptions);
        purchaseChart.render();

        document.getElementById('purchaseFilter').addEventListener('change', function() {
            const selected = this.value;
            purchaseChart.updateOptions({
                series: purchaseStats[selected].series,
                xaxis: { categories: purchaseStats[selected].categories }
            });
        });
    });
</script>