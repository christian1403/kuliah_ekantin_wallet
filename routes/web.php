<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return redirect()->intended('/login');
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();
        
        // Redirect to role-specific dashboard
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        
        if ($user->hasRole('kasir')) {
            return redirect()->route('kasir.dashboard');
        }
        
        if ($user->hasRole('mahasiswa')) {
            return redirect()->route('mahasiswa.dashboard');
        }
        
        // Default dashboard for users without specific roles
        return Inertia::render('dashboard');
    })->name('dashboard');
});

// Include role-specific routes
require __DIR__.'/admin.php';
require __DIR__.'/kasir.php';
require __DIR__.'/mahasiswa.php';
require __DIR__.'/settings.php';
