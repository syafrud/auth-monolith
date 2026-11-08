<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Shared\Contracts\CatalogStatisticsInterface;
use Modules\Shared\Contracts\SalesStatisticsInterface;

class DashboardController extends Controller
{
    /**
     * Dashboard untuk semua user yang login: ringkasan pesanan milik user sendiri.
     */
    public function __invoke(
        Request $request,
        CatalogStatisticsInterface $catalogStatistics,
        SalesStatisticsInterface $salesStatistics
    ): View {
        $userId = $request->user()->id;

        return view('dashboard', [
            'catalogSummary' => $catalogStatistics->getSummary(),
            'salesSummary' => $salesStatistics->getSummary($userId),
            'recentOrders' => $salesStatistics->getRecentOrders(5, $userId),
        ]);
    }
}
