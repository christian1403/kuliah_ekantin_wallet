<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here are the routes for admin users. All routes in this group are
| protected by the 'auth' and 'admin' middleware.
|
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [AdminController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [AdminController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
    
    // Merchant Management
    Route::get('/merchants', [UserManagementController::class, 'merchants'])->name('merchants.index');
    Route::get('/merchants/create', [UserManagementController::class, 'createMerchant'])->name('merchants.create');
    Route::post('/merchants', [UserManagementController::class, 'storeMerchant'])->name('merchants.store');
    Route::get('/merchants/{merchant}/edit', [UserManagementController::class, 'editMerchant'])->name('merchants.edit');
    Route::put('/merchants/{merchant}', [UserManagementController::class, 'updateMerchant'])->name('merchants.update');
    Route::delete('/merchants/{merchant}', [UserManagementController::class, 'destroyMerchant'])->name('merchants.destroy');
    
    // Kasir Management
    Route::get('/kasir', [UserManagementController::class, 'kasir'])->name('kasir.index');
    Route::get('/kasir/create', [UserManagementController::class, 'createKasir'])->name('kasir.create');
    Route::post('/kasir', [UserManagementController::class, 'storeKasir'])->name('kasir.store');
    
    // Mahasiswa Management
    Route::get('/mahasiswa', [UserManagementController::class, 'mahasiswa'])->name('mahasiswa.index');
    Route::get('/mahasiswa/create', [UserManagementController::class, 'createMahasiswa'])->name('mahasiswa.create');
    Route::post('/mahasiswa', [UserManagementController::class, 'storeMahasiswa'])->name('mahasiswa.store');
    
    // Transaction Management
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{transaction}/confirm', [TransactionController::class, 'confirm'])->name('transactions.confirm');
    Route::post('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
    
    // Reports & Analytics (placeholder routes)
    Route::get('/reports', function () {
        return inertia('Admin/Reports/Index');
    })->name('reports.index');
    
    Route::get('/analytics', function () {
        return inertia('Admin/Analytics/Index');
    })->name('analytics.index');
    
    // Settings (placeholder routes)
    Route::get('/settings', function () {
        return inertia('Admin/Settings/Index');
    })->name('settings.index');
    
    Route::get('/logs', function () {
        return inertia('Admin/Logs/Index');
    })->name('logs.index');
});