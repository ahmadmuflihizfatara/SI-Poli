<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KeluhanController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PemeriksaanController;
use Illuminate\Support\Facades\Route;

// Halaman utama: tamu ke login, pengguna yang sudah masuk diarahkan middleware guest ke dashboard.
Route::redirect('/', '/login');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::view('/lupa-password', 'auth.lupa-password')->name('password.request');
});

Route::any('/logout', [AuthController::class, 'destroy'])->name('logout');

// Fitur (admin & perawat)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::controller(KeluhanController::class)->prefix('laporan-kesehatan')->name('laporan-kesehatan.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/ekspor', 'ekspor')->name('ekspor');
        Route::get('/tambah', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{keluhan}', 'show')->name('show');
        Route::patch('/{keluhan}/toggle-sembuh', 'toggleSembuh')->name('toggle-sembuh');
        Route::get('/{keluhan}/kontrol', 'editKontrol')->name('kontrol.edit');
        Route::post('/{keluhan}/kontrol', 'updateKontrol')->name('kontrol.update');
    });

    Route::controller(PemeriksaanController::class)->prefix('pemeriksaan-kesehatan')->name('pemeriksaan-kesehatan.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/mptb', 'mptb')->name('mptb.index');
        Route::post('/mptb', 'simpanMptb')->name('mptb.simpan');
        Route::get('/samapta', 'samapta')->name('samapta.index');
        Route::post('/samapta', 'simpanSamapta')->name('samapta.simpan');
    });

    // Khusus admin
    Route::controller(LogController::class)->middleware('can:admin')->prefix('log')->name('log.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{log}', 'show')->name('show');
    });
});
