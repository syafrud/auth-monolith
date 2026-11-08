<?php

return [
    App\Providers\AppServiceProvider::class,
    Modules\Auth\Providers\AuthServiceProvider::class,
    Modules\Products\Providers\ProductServiceProvider::class,
    Modules\Orders\Providers\OrderServiceProvider::class,
    Modules\Library\Catalog\Providers\CatalogServiceProvider::class,
    Modules\Library\Membership\Providers\MembershipServiceProvider::class,
    Modules\Library\Circulation\Providers\CirculationServiceProvider::class,
];
