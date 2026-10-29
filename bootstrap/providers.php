<?php

use App\Providers\AppServiceProvider;
use Modules\Auth\Providers\AuthServiceProvider;
use Modules\Products\Providers\ProductServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    ProductServiceProvider::class,
];
