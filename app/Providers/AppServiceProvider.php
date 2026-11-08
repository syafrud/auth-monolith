<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Orders\Services\SalesStatisticsService;
use Modules\Products\Services\CatalogStatisticsService;
use Modules\Products\Services\ProductService;
use Modules\Shared\Contracts\CatalogStatisticsInterface;
use Modules\Shared\Contracts\ProductServiceInterface;
use Modules\Shared\Contracts\SalesStatisticsInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProductServiceInterface::class,
            ProductService::class
        );
        $this->app->bind(
            CatalogStatisticsInterface::class,
            CatalogStatisticsService::class
        );
        $this->app->bind(
            SalesStatisticsInterface::class,
            SalesStatisticsService::class
        );
    }

    public function boot(): void
    {
        //
    }
}
