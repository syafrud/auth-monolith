<?php

use Illuminate\Support\Facades\Route;
use Modules\Library\Controllers\AuthorController;
use Modules\Library\Controllers\BookController;

// Wajib menggunakan middleware 'web' agar session & auth terbaca
Route::middleware(['web', 'auth'])->group(function () {
    Route::resource('books', BookController::class)->except('show');
    Route::post('books/{book}/borrow', [BookController::class, 'borrow'])->name('books.borrow');

    Route::resource('authors', AuthorController::class)->except('show');
});
