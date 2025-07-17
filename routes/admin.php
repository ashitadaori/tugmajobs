<?php

use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JobApplicationController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\KycController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile Management
    Route::prefix('profile')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/password', [ProfileController::class, 'password'])->name('profile.password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    });
    
    // User Management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/export', [UserController::class, 'export'])->name('users.export');
        Route::post('/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
        Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
    
    // Admin Management
    Route::prefix('admins')->group(function () {
        Route::get('/', [AdminManagementController::class, 'index'])->name('admins.index');
        Route::get('/create', [AdminManagementController::class, 'create'])->name('admins.create');
        Route::post('/', [AdminManagementController::class, 'store'])->name('admins.store');
        Route::get('/{admin}/edit', [AdminManagementController::class, 'edit'])->name('admins.edit');
        Route::put('/{admin}', [AdminManagementController::class, 'update'])->name('admins.update');
        Route::delete('/{admin}', [AdminManagementController::class, 'destroy'])->name('admins.destroy');
    });
    
    // KYC Management
    Route::prefix('kyc')->group(function () {
        Route::get('/', [KycController::class, 'index'])->name('kyc.index');
        Route::put('/{document}/verify', [KycController::class, 'verify'])->name('kyc.verify');
        Route::put('/{document}/reject', [KycController::class, 'reject'])->name('kyc.reject');
    });
    
    // Job Management Routes
    Route::prefix('jobs')->name('jobs.')->group(function () {
        Route::get('/', [JobController::class, 'index'])->name('index');
        Route::get('/pending', [JobController::class, 'pending'])->name('pending');
        Route::get('/create', [JobController::class, 'create'])->name('create');
        Route::post('/', [JobController::class, 'store'])->name('store');
        Route::get('/{job}', [JobController::class, 'show'])->name('show');
        Route::get('/{job}/edit', [JobController::class, 'edit'])->name('edit');
        Route::put('/{job}', [JobController::class, 'update'])->name('update');
        Route::delete('/{job}', [JobController::class, 'destroy'])->name('destroy');
        Route::patch('/{job}/approve', [JobController::class, 'approve'])->name('approve');
        Route::patch('/{job}/reject', [JobController::class, 'reject'])->name('reject');
    });
    
    // Categories Management
    Route::resource('categories', CategoryController::class);
    
    // Analytics
    Route::prefix('analytics')->group(function () {
        Route::get('/', [DashboardController::class, 'analytics'])->name('analytics.dashboard');
    });
    
    // Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('/', [AdminSettingsController::class, 'update'])->name('settings.update');
    });
});
