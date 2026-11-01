<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Beranda (butuh PIN terbuka).
Route::get('/', [DashboardController::class, 'index'])
    ->middleware('pin.unlocked')
    ->name('dashboard.index');

// Dompet (butuh PIN terbuka).
Route::middleware('pin.unlocked')->group(function () {
    Route::get('/wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::post('/wallets', [WalletController::class, 'store'])->name('wallets.store');
    Route::put('/wallets/{wallet}', [WalletController::class, 'update'])->name('wallets.update');
    Route::delete('/wallets/{wallet}', [WalletController::class, 'destroy'])->name('wallets.destroy');
});

// Kategori (butuh PIN terbuka).
Route::middleware('pin.unlocked')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});

// Hanya aktif selama PIN belum dibuat.
Route::get('/pin/setup', [PinController::class, 'showSetupForm'])->name('pin.setup');
Route::post('/pin/setup', [PinController::class, 'storeSetup'])->name('pin.setup.store');

// Layar Masukkan PIN (wajib sudah ada PIN).
Route::get('/pin/unlock', [PinController::class, 'showUnlockForm'])
    ->middleware('pin.configured')
    ->name('pin.unlock');
Route::post('/pin/unlock', [PinController::class, 'verifyUnlock'])
    ->middleware('pin.configured')
    ->name('pin.unlock.verify');

// Tombol Kunci Aplikasi (wajib sesi sedang terbuka).
Route::post('/pin/lock', [PinController::class, 'lockSession'])
    ->middleware('pin.unlocked')
    ->name('pin.lock');
