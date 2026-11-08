<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Controllers\AdminDashboardController;

// Route untuk admin (memerlukan login + role admin)
Route::middleware(['web', 'auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
});

// Route untuk user biasa (memerlukan login)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/user/dashboard', DashboardController::class)
        ->name('user.dashboard');
});
