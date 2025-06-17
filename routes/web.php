<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\LaboratoriumController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('pengguna', PenggunaController::class);
        Route::resource('laboratorium', LaboratoriumController::class);
    });

    // Kepala Lab routes
    Route::middleware('role:kepalalab')->prefix('kepalalab')->name('kepalalab.')->group(function () {
        Route::get('/dashboard', function () {
            return view('kepalalab.dashboard');
        })->name('dashboard');
    });

    // Asisten Lab routes
    Route::middleware('role:asistenlab')->prefix('asistenlab')->name('asistenlab.')->group(function () {
        Route::get('/dashboard', function () {
            return view('asistenlab.dashboard');
        })->name('dashboard');
    });
});
