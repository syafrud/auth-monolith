<?php

use App\Providers\AppServiceProvider;
use Modules\Auth\Providers\AuthServiceProvider;
use Modules\Library\Providers\LibraryServiceProvider;
use Modules\Products\Providers\ProductServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    ProductServiceProvider::class,
    LibraryServiceProvider::class,
];
