<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Controllers\ProductController;

// Wajib menggunakan middleware 'web' agar session & auth terbaca
Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('products', ProductController::class);

    // Route uji custom exception InsufficientStockException (modul 4.10 poin 3)
    Route::get('products/{product}/reduce-stock/{quantity}', [ProductController::class, 'reduceStock'])
        ->where('quantity', '[1-9][0-9]{0,8}')
        ->name('products.reduce-stock');
});
