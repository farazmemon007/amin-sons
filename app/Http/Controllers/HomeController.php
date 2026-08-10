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

        $user = Auth::user();
        $usertype = $user->usertype;
        $userId = $user->id;

        if ($usertype == 'user') {
            return view('user_panel.dashboard', compact('userId'));
        } elseif ($usertype == 'admin') {
            // Filter by branch if user is not super admin and has a branch_id
            $branchId = !$user->hasRole('super admin') ? $user->branch_id : null;

            // Stats
            $categoryCount = DB::table('categories')->count();
            $subcategoryCount = DB::table('subcategories')->count();
            $productCount = DB::table('products')->count();

            $customersQuery = DB::table('customers');
            $purchasesQuery = DB::table('purchases');
            $purchaseReturnsQuery = DB::table('purchase_returns');
            $salesQuery = DB::table('sales');
            $salesReturnsQuery = DB::table('sales_returns');

            if ($branchId) {
                $customersQuery->where('branch_id', $branchId);
                $purchasesQuery->where('branch_id', $branchId);
                $salesQuery->where('branch_id', $branchId);

                $purchaseReturnsQuery->whereExists(function ($query) use ($branchId) {
                    $query->select(DB::raw(1))
                          ->from('purchases')
                          ->whereColumn('purchases.id', 'purchase_returns.purchase_id')
                          ->where('purchases.branch_id', $branchId);
                });

                $salesReturnsQuery->whereExists(function ($query) use ($branchId) {
                    $query->select(DB::raw(1))
                          ->from('sales')
                          ->whereColumn('sales.id', 'sales_returns.sale_id')
                          ->where('sales.branch_id', $branchId);
                });
            }

            $customerscount = $customersQuery->count();
            $totalPurchases = $purchasesQuery->sum('net_amount');
            $totalPurchaseReturns = $purchaseReturnsQuery->sum('net_amount');
            $totalSales = $salesQuery->sum('total_net');
            $totalSalesReturns = $salesReturnsQuery->sum('total_net');

            // Charts data
            $dailyLabels = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i)->format('Y-m-d'));
            $dailyData = $dailyLabels->map(function ($date) use ($branchId) {
                $query = DB::table('sales')->whereDate('created_at', $date);
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
                return $query->sum('total_net');
            });

            $weeklyLabels = ['This Week', 'Last Week', '2 Weeks Ago'];
            $weeklyData = collect([0, 1, 2])->map(function ($i) use ($branchId) {
                $start = Carbon::now()->startOfWeek()->subWeeks($i);
                $end = $start->copy()->endOfWeek();
                $query = DB::table('sales')->whereBetween('created_at', [$start, $end]);
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
                return $query->sum('total_net');
            })->reverse()->values();

            $months = range(1, Carbon::now()->month);
            $monthLabels = collect($months)->map(fn($m) => Carbon::create()->month($m)->format('F'));
            $monthlyData = collect($months)->map(function ($m) use ($branchId) {
                $query = DB::table('sales')
                    ->whereMonth('created_at', $m)
                    ->whereYear('created_at', Carbon::now()->year);
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
                return $query->sum('total_net');
            });

            $salesChartStats = [
                'daily' => ['categories' => $dailyLabels, 'series' => [['name' => 'Sales', 'data' => $dailyData]]],
                'weekly' => ['categories' => $weeklyLabels, 'series' => [['name' => 'Sales', 'data' => $weeklyData]]],
                'monthly' => ['categories' => $monthLabels, 'series' => [['name' => 'Sales', 'data' => $monthlyData]]]
            ];

            // Purchase Charts
            $purchaseDailyLabels = $dailyLabels;
            $purchaseDailyData = $purchaseDailyLabels->map(function ($date) use ($branchId) {
                $query = DB::table('purchases')->whereDate('created_at', $date);
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
                return $query->sum('net_amount');
            });
            
            $purchaseWeeklyData = collect([0, 1, 2])->map(function ($i) use ($branchId) {
                $start = Carbon::now()->startOfWeek()->subWeeks($i);
                $end = $start->copy()->endOfWeek();
                $query = DB::table('purchases')->whereBetween('created_at', [$start, $end]);
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
                return $query->sum('net_amount');
            })->reverse()->values();

            $purchaseMonthlyData = collect($months)->map(function ($m) use ($branchId) {
                $query = DB::table('purchases')
                    ->whereMonth('created_at', $m)
                    ->whereYear('created_at', Carbon::now()->year);
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
                return $query->sum('net_amount');
            });

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
