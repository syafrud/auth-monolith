<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Modules\Auth\Middleware\RoleMiddleware;
use Modules\Library\Exceptions\AuthorHasBooksException;
use Modules\Library\Exceptions\BookNotAvailableException;
use Modules\Products\Exceptions\InsufficientStockException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            InsufficientStockException $e,
            $request
        ) {
            return $e->render($request);
        });

        $exceptions->render(function (BookNotAvailableException $e, Request $request) {
            return $e->render($request);
        });

        $exceptions->render(function (AuthorHasBooksException $e, Request $request) {
            return $e->render($request);
        });
    })
    ->create();
