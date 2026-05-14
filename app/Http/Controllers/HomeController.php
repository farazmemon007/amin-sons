<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
     public function testingform(Request $request)
    {
        return view('admin_panel.sale.testing_form') ;
    }
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usertype = Auth::user()->usertype;
        $userId = Auth::id();

        if ($usertype == 'user') {
            return view('user_panel.dashboard', compact('userId'));
        } elseif ($usertype == 'admin') {
            // Stats
            $categoryCount = DB::table('categories')->count();
            $subcategoryCount = DB::table('subcategories')->count();
            $productCount = DB::table('products')->count();
            $customerscount = DB::table('customers')->count();

            $totalPurchases = DB::table('purchases')->sum('net_amount');
            $totalPurchaseReturns = DB::table('purchase_returns')->sum('net_amount');
            $totalSales = DB::table('sales')->sum('total_net');
            $totalSalesReturns = DB::table('sales_returns')->sum('total_net');

            // Charts data
            $dailyLabels = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i)->format('Y-m-d'));
            $dailyData = $dailyLabels->map(fn($date) => DB::table('sales')->whereDate('created_at', $date)->sum('total_net'));

            $weeklyLabels = ['This Week', 'Last Week', '2 Weeks Ago'];
            $weeklyData = collect([0, 1, 2])->map(function ($i) {
                $start = Carbon::now()->startOfWeek()->subWeeks($i);
                $end = $start->copy()->endOfWeek();
                return DB::table('sales')->whereBetween('created_at', [$start, $end])->sum('total_net');
            })->reverse()->values();

            $months = range(1, Carbon::now()->month);
            $monthLabels = collect($months)->map(fn($m) => Carbon::create()->month($m)->format('F'));
            $monthlyData = collect($months)->map(fn($m) => DB::table('sales')->whereMonth('created_at', $m)->whereYear('created_at', Carbon::now()->year)->sum('total_net'));

            $salesChartStats = [
                'daily' => ['categories' => $dailyLabels, 'series' => [['name' => 'Sales', 'data' => $dailyData]]],
                'weekly' => ['categories' => $weeklyLabels, 'series' => [['name' => 'Sales', 'data' => $weeklyData]]],
                'monthly' => ['categories' => $monthLabels, 'series' => [['name' => 'Sales', 'data' => $monthlyData]]]
            ];

            // Purchase Charts
            $purchaseDailyLabels = $dailyLabels;
            $purchaseDailyData = $purchaseDailyLabels->map(fn($date) => DB::table('purchases')->whereDate('created_at', $date)->sum('net_amount'));
            
            $purchaseWeeklyData = collect([0, 1, 2])->map(function ($i) {
                $start = Carbon::now()->startOfWeek()->subWeeks($i);
                $end = $start->copy()->endOfWeek();
                return DB::table('purchases')->whereBetween('created_at', [$start, $end])->sum('net_amount');
            })->reverse()->values();

            $purchaseMonthlyData = collect($months)->map(fn($m) => DB::table('purchases')->whereMonth('created_at', $m)->whereYear('created_at', Carbon::now()->year)->sum('net_amount'));

            $purchaseChartStats = [
                'daily' => ['categories' => $purchaseDailyLabels, 'series' => [['name' => 'Purchases', 'data' => $purchaseDailyData]]],
                'weekly' => ['categories' => $weeklyLabels, 'series' => [['name' => 'Purchases', 'data' => $purchaseWeeklyData]]],
                'monthly' => ['categories' => $monthLabels, 'series' => [['name' => 'Purchases', 'data' => $purchaseMonthlyData]]]
            ];

            return view('admin_panel.dashboard', compact(
                'categoryCount', 'subcategoryCount', 'productCount', 'customerscount',
                'totalPurchases', 'totalPurchaseReturns', 'totalSales', 'totalSalesReturns',
                'salesChartStats', 'purchaseChartStats'
            ));
        } else {
            return redirect()->back()->with('error', 'Unauthorized access');
        }
    }
}
