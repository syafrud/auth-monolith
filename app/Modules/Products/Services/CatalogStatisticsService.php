<?php

namespace Modules\Products\Services;

use Modules\Products\Models\Category;
use Modules\Products\Models\Product;
use Modules\Shared\Contracts\CatalogStatisticsInterface;

class CatalogStatisticsService implements CatalogStatisticsInterface
{
    public function getSummary(): array
    {
        return [
            'total_products' => Product::count(),
            'available_products' => Product::available()->count(),
            'low_stock_products' => Product::lowStock()->where('stock', '>', 0)->count(),
            'out_of_stock_products' => Product::where('stock', '<=', 0)->count(),
            'total_categories' => Category::count(),
        ];
    }

    public function getLowStockProducts(int $limit = 5): array
    {
        return Product::lowStock()
            ->orderBy('stock')
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'stock', 'low_stock_threshold'])
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'stock' => $product->stock,
                'low_stock_threshold' => $product->low_stock_threshold,
            ])
            ->all();
    }
}
