<?php

use Illuminate\Support\Facades\Route;
use Modules\Library\Membership\Controllers\MemberController;

Route::middleware(['web', 'auth'])->prefix('library')->name('library.')->group(function () {
    Route::get('members', [MemberController::class, 'index'])->name('members.index');

    Route::middleware('role:admin')->group(function () {
        Route::delete('members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
        Route::patch('members/{member}/restore', [MemberController::class, 'restore'])
            ->withTrashed()
            ->name('members.restore');
    });
});
