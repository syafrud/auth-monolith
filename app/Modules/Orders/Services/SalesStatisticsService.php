<?php

namespace Modules\Orders\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Orders\Models\Order;
use Modules\Shared\Contracts\SalesStatisticsInterface;

class SalesStatisticsService implements SalesStatisticsInterface
{
    public function getSummary(?int $userId = null): array
    {
        return [
            'total_orders' => $this->ordersFor($userId)->count(),
            'pending_orders' => $this->ordersFor($userId)->byStatus('pending')->count(),
            'total_revenue' => (float) $this->ordersFor($userId)->where('status', '!=', 'cancelled')->sum('total_amount'),
        ];
    }

    public function getRecentOrders(int $limit = 5, ?int $userId = null): array
    {
        return $this->ordersFor($userId)
            ->with('user:id,name')
            ->withCount('items')
            ->orderByDesc('ordered_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Order $order): array => [
                'order_number' => $order->order_number,
                'customer_name' => $order->user?->name ?? '-',
                'items_count' => $order->items_count,
                'total_amount' => (float) $order->total_amount,
                'status' => $order->status,
                'ordered_at' => $order->ordered_at,
            ])
            ->all();
    }

    /**
     * @return Builder<Order>
     */
    private function ordersFor(?int $userId): Builder
    {
        return Order::query()->when(
            $userId !== null,
            fn (Builder $query): Builder => $query->where('user_id', $userId)
        );
    }
}
