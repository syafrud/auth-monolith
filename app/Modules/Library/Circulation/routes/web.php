<?php

use Illuminate\Support\Facades\Route;
use Modules\Library\Circulation\Controllers\LoanController;

Route::middleware(['web', 'auth'])->prefix('library')->name('library.')->group(function () {
    Route::get('loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('loans', [LoanController::class, 'store'])->name('loans.store');
    Route::patch('loans/{loan}/return', [LoanController::class, 'returnBooks'])->name('loans.return');

    Route::middleware('role:admin')->group(function () {
        Route::delete('loans/{loan}', [LoanController::class, 'destroy'])->name('loans.destroy');
        Route::patch('loans/{loan}/restore', [LoanController::class, 'restore'])
            ->withTrashed()
            ->name('loans.restore');
    });
});
