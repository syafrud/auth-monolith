<?php

namespace Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;
use Modules\Shared\Contracts\CatalogStatisticsInterface;
use Modules\Shared\Contracts\SalesStatisticsInterface;

class AdminDashboardController extends Controller
{
    /**
     * Data Catalog & Sales diambil lewat contract, bukan model context lain.
     */
    public function index(
        CatalogStatisticsInterface $catalogStatistics,
        SalesStatisticsInterface $salesStatistics
    ): View {
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'totalAdmins' => User::where('role', 'admin')->count(),
            'totalRegularUsers' => User::where('role', 'user')->count(),
            'catalogSummary' => $catalogStatistics->getSummary(),
            'lowStockProducts' => $catalogStatistics->getLowStockProducts(),
            'salesSummary' => $salesStatistics->getSummary(),
            'recentOrders' => $salesStatistics->getRecentOrders(),
        ]);
    }
}
