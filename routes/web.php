<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KeluhanController;
use Illuminate\Support\Facades\Route;

// Route Halaman Utama
Route::get('/', function () {
    return view('login');
});

// Route Auth & Profile
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::get('/lupa-password', function () {
    return view('lupa-password');
})->name('password.request');

Route::get('/password-update', function () {
    return redirect('/login');
})->name('password.update');

// Route Logout (Mengarahkan kembali ke Login)
Route::any('/logout', [AuthController::class, 'destroy'])->name('logout');

// Route Fitur & Navigasi Dashboard
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/laporan-kesehatan', [KeluhanController::class, 'index'])->name('laporan-kesehatan.index');
    Route::get('/laporan-kesehatan/ekspor', [KeluhanController::class, 'ekspor'])->name('laporan-kesehatan.ekspor');
    Route::get('/laporan-kesehatan/tambah', [KeluhanController::class, 'create'])->name('laporan-kesehatan.create');
    Route::post('/laporan-kesehatan', [KeluhanController::class, 'store'])->name('laporan-kesehatan.store');
    Route::get('/laporan-kesehatan/{keluhan}', [KeluhanController::class, 'show'])->name('laporan-kesehatan.show');
    Route::patch('/laporan-kesehatan/{keluhan}/toggle-sembuh', [KeluhanController::class, 'toggleSembuh'])->name('laporan-kesehatan.toggle-sembuh');
    Route::get('/laporan-kesehatan/{keluhan}/kontrol', [KeluhanController::class, 'editKontrol'])->name('laporan-kesehatan.kontrol.edit');
    Route::post('/laporan-kesehatan/{keluhan}/kontrol', [KeluhanController::class, 'updateKontrol'])->name('laporan-kesehatan.kontrol.update');
});
