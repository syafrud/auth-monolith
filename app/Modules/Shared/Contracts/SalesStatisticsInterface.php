<?php

namespace Modules\Shared\Contracts;

use Carbon\CarbonInterface;

interface SalesStatisticsInterface
{
    /**
     * Ringkasan pesanan. Pendapatan tidak menghitung pesanan berstatus "cancelled".
     *
     * @return array{total_orders: int, pending_orders: int, total_revenue: float}
     */
    public function getSummary(?int $userId = null): array;

    /**
     * @return list<array{order_number: string, customer_name: string, items_count: int, total_amount: float, status: string, ordered_at: CarbonInterface}>
     */
    public function getRecentOrders(int $limit = 5, ?int $userId = null): array;
}
