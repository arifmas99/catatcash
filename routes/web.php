<?php

use App\Http\Controllers\PinController;
use Illuminate\Support\Facades\Route;

// Pintu masuk aplikasi. Sementara menampilkan layar "terbuka" sampai
// halaman Beranda dibuat pada tugas berikutnya.
Route::view('/', 'pages.unlocked')
    ->middleware('pin.unlocked')
    ->name('home');

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
