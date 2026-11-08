<?php

namespace Modules\Library\Membership\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Library\Membership\Services\MembershipService;
use Modules\Shared\Contracts\MembershipServiceInterface;

class MembershipServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MembershipServiceInterface::class, MembershipService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}
