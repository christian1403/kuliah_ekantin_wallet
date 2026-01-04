<?php

use App\Http\Controllers\Mahasiswa\MahasiswaController;
use App\Http\Controllers\Mahasiswa\WalletController;
use App\Http\Controllers\Mahasiswa\TransactionHistoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mahasiswa Routes
|--------------------------------------------------------------------------
|
| Here are the routes for mahasiswa (student) users. All routes in this
| group are protected by the 'auth' and 'mahasiswa' middleware.
|
*/

Route::prefix('mahasiswa')->name('mahasiswa.')->middleware(['auth', 'mahasiswa'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [MahasiswaController::class, 'dashboard'])->name('dashboard');
    
    // Profile Management
    Route::get('/profile', [MahasiswaController::class, 'profile'])->name('profile');
    Route::put('/profile', [MahasiswaController::class, 'updateProfile'])->name('profile.update');
    
    // QR Code
    Route::get('/qr-code', [MahasiswaController::class, 'qrCode'])->name('qr-code');
    
    // Wallet Management
    Route::prefix('/wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::post('/create', [WalletController::class, 'createWallet'])->name('create');
        Route::get('/top-up', [WalletController::class, 'topUp'])->name('top-up');
        Route::post('/top-up', [WalletController::class, 'processTopUp'])->name('process-top-up');
        Route::post('/verify-pin', [WalletController::class, 'verifyPin'])->name('verify-pin');
        Route::post('/update-pin', [WalletController::class, 'updatePin'])->name('update-pin');
        Route::get('/transfer', [WalletController::class, 'transfer'])->name('transfer');
        Route::post('/transfer', [WalletController::class, 'processTransfer'])->name('process-transfer');
        Route::get('/transactions/{id}', [WalletController::class, 'transactionDetail'])->name('transaction-detail');
    });
    
    // API Routes for Wallet
    Route::get('/api/search-mahasiswa', [WalletController::class, 'searchMahasiswa'])->name('api.search-mahasiswa');
    
    // Transaction History
    Route::prefix('/transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionHistoryController::class, 'index'])->name('index');
        Route::get('/{id}', [TransactionHistoryController::class, 'show'])->name('show');
        Route::get('/export', [TransactionHistoryController::class, 'export'])->name('export');
    });
    Route::get('/analytics', [TransactionHistoryController::class, 'analytics'])->name('analytics');
    
    // Merchants
    Route::get('/merchants', [MahasiswaController::class, 'merchants'])->name('merchants.index');
    Route::get('/merchants/{merchantId}', [MahasiswaController::class, 'merchantDetail'])->name('merchants.show');
    Route::get('/merchants/{merchantId}/products', [MahasiswaController::class, 'getMerchantProducts'])->name('merchants.products');
    Route::post('/orders', [MahasiswaController::class, 'createOrder'])->name('orders.create');
    
    // Notifications (placeholder routes)
    Route::get('/notifications', function () {
        return inertia('Mahasiswa/Notifications/Index');
    })->name('notifications.index');
    
    // Settings (placeholder routes)
    Route::get('/settings', function () {
        return inertia('Mahasiswa/Settings/Index');
    })->name('settings.index');
    
    Route::get('/settings/security', function () {
        return inertia('Mahasiswa/Settings/Security');
    })->name('settings.security');
    
    Route::get('/settings/privacy', function () {
        return inertia('Mahasiswa/Settings/Privacy');
    })->name('settings.privacy');
    
    // Help & Support (placeholder routes)
    Route::get('/help', function () {
        return inertia('Mahasiswa/Help/Index');
    })->name('help.index');
    
    Route::get('/support', function () {
        return inertia('Mahasiswa/Support/Index');
    })->name('support.index');
});