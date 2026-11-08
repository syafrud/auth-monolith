<?php

namespace Modules\Library\Circulation\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Library\Circulation\Events\BookBorrowed;
use Modules\Library\Circulation\Listeners\LogBookBorrowed;

class CirculationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        Event::listen(BookBorrowed::class, LogBookBorrowed::class);
    }
}
