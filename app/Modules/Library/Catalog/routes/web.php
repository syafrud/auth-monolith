<?php

use Illuminate\Support\Facades\Route;
use Modules\Library\Catalog\Controllers\BookController;

Route::middleware(['web', 'auth'])->prefix('library')->name('library.')->group(function () {
    Route::get('books', [BookController::class, 'index'])->name('books.index');

    Route::middleware('role:admin')->group(function () {
        Route::get('books/create', [BookController::class, 'create'])->name('books.create');
        Route::post('books', [BookController::class, 'store'])->name('books.store');
        Route::delete('books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
        Route::patch('books/{book}/restore', [BookController::class, 'restore'])
            ->withTrashed()
            ->name('books.restore');
    });

    Route::get('books/{book}', [BookController::class, 'show'])->whereNumber('book')->name('books.show');
});
