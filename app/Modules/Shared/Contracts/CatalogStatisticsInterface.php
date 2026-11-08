<?php

namespace Modules\Shared\Contracts;

interface CatalogStatisticsInterface
{
    /**
     * @return array{total_products: int, available_products: int, low_stock_products: int, out_of_stock_products: int, total_categories: int}
     */
    public function getSummary(): array;

    /**
     * @return list<array{id: int, name: string, stock: int, low_stock_threshold: int}>
     */
    public function getLowStockProducts(int $limit = 5): array;
}
