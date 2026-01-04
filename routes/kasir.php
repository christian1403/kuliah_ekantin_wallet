<?php

use App\Http\Controllers\Kasir\KasirController;
use App\Http\Controllers\Kasir\TransactionController;
use App\Http\Controllers\Kasir\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Kasir Routes
|--------------------------------------------------------------------------
|
| Here are the routes for kasir users. All routes in this group are
| protected by the 'auth' and 'kasir' middleware.
|
*/

Route::prefix('kasir')->name('kasir.')->middleware(['auth', 'kasir'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [KasirController::class, 'dashboard'])->name('dashboard');
    
    // Profile Management
    Route::get('/profile', [KasirController::class, 'profile'])->name('profile');
    Route::put('/profile', [KasirController::class, 'updateProfile'])->name('profile.update');
    
    // Transaction Management
    Route::get('/transactions', [KasirController::class, 'transactions'])->name('transactions.index');
    Route::post('/transactions/{id}/confirm', [KasirController::class, 'confirmTransaction'])->name('transactions.confirm');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transactions/{id}/detail', [KasirController::class, 'transactionDetail'])->name('transactions.detail');
    
    // API Routes for Transactions
    Route::post('/api/search-mahasiswa', [TransactionController::class, 'searchMahasiswa'])->name('api.search-mahasiswa');
    Route::post('/api/process-payment', [TransactionController::class, 'processPayment'])->name('api.process-payment');
    
    // Product Management
    Route::resource('products', ProductController::class);
    Route::post('/products/{produk}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::get('/api/products/search', [ProductController::class, 'search'])->name('api.products.search');
    
    // Reports (placeholder routes)
    Route::get('/reports', function () {
        return inertia('Kasir/Reports/Index');
    })->name('reports.index');
    
    Route::get('/reports/daily', function () {
        return inertia('Kasir/Reports/Daily');
    })->name('reports.daily');
    
    Route::get('/reports/monthly', function () {
        return inertia('Kasir/Reports/Monthly');
    })->name('reports.monthly');
    
    // POS Interface (placeholder routes)
    Route::get('/pos', function () {
        return inertia('Kasir/POS/Index');
    })->name('pos.index');
    
    // Inventory Management (placeholder routes)
    Route::get('/inventory', function () {
        return inertia('Kasir/Inventory/Index');
    })->name('inventory.index');
});