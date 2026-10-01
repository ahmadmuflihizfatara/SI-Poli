<?php

use App\Http\Controllers\AkunController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KeluhanController;
use App\Http\Controllers\KlbController;
use App\Http\Controllers\KonselingController;
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
    Route::post('/lupa-password', [AuthController::class, 'mintaSandi'])->middleware('throttle:5,1')->name('password.store');
});

Route::any('/logout', [AuthController::class, 'destroy'])->name('logout');

// Fitur; akses Tambah/Edit per akun diatur di Manajemen Akun.
// Bagian perawat (keluhan medis, pemeriksaan, KLB) dan psikolog (konseling) terpisah; admin bisa keduanya.
Route::middleware('auth')->group(function () {
    // Dashboard & daftar Laporan Kesehatan dipakai bersama: isinya mengikuti bagian (lihat Controller::bagian)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/laporan-kesehatan', [KeluhanController::class, 'index'])->name('laporan-kesehatan.index');
    Route::get('/laporan-kesehatan/ekspor', [KeluhanController::class, 'ekspor'])->name('laporan-kesehatan.ekspor');

    Route::middleware('can:bagian-psikolog')->controller(KonselingController::class)
        ->prefix('laporan-kesehatan/psikologi')->name('laporan-kesehatan.psikologi.')->group(function () {
            Route::get('/{konseling}', 'show')->whereNumber('konseling')->name('show');
            Route::middleware('can:tambah-data')->group(function () {
                Route::get('/tambah', 'create')->name('create');
                Route::post('/', 'store')->name('store');
            });
            Route::middleware('can:edit-data')->group(function () {
                Route::get('/{konseling}/perbarui', 'edit')->name('edit');
                Route::put('/{konseling}', 'update')->name('update');
            });
        });

    Route::middleware('can:bagian-perawat')->group(function () {
        Route::controller(KeluhanController::class)->prefix('laporan-kesehatan')->name('laporan-kesehatan.')->group(function () {
            Route::middleware('can:tambah-data')->group(function () {
                Route::get('/tambah', 'create')->name('create');
                Route::post('/', 'store')->name('store');
            });
            Route::get('/{keluhan}', 'show')->whereNumber('keluhan')->name('show');
            Route::middleware('can:edit-data')->group(function () {
                Route::patch('/{keluhan}/toggle-sembuh', 'toggleSembuh')->name('toggle-sembuh');
                Route::get('/{keluhan}/kontrol', 'editKontrol')->name('kontrol.edit');
                Route::post('/{keluhan}/kontrol', 'updateKontrol')->name('kontrol.update');
            });
        });

        Route::controller(PemeriksaanController::class)->prefix('pemeriksaan-kesehatan')->name('pemeriksaan-kesehatan.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/mptb', 'mptb')->name('mptb.index');
            Route::post('/mptb', 'simpanMptb')->name('mptb.simpan');
            Route::get('/samapta', 'samapta')->name('samapta.index');
            Route::post('/samapta', 'simpanSamapta')->name('samapta.simpan');
        });

        // Kejadian Luar Biasa: terpisah dari laporan kesehatan utama
        Route::controller(KlbController::class)->prefix('kejadian-luar-biasa')->name('klb.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{klb}', 'show')->name('show');
            Route::get('/{klb}/keluhan/{keluhan}', 'showKeluhan')->whereNumber('keluhan')->name('keluhan.show');
            Route::middleware('can:tambah-data')->group(function () {
                Route::post('/', 'store')->name('store');
                Route::get('/{klb}/keluhan/tambah', 'createKeluhan')->name('keluhan.create');
                Route::post('/{klb}/keluhan', 'storeKeluhan')->name('keluhan.store');
            });
            Route::middleware('can:edit-data')->group(function () {
                Route::patch('/{klb}/selesai', 'selesai')->name('selesai');
                Route::post('/{klb}/keluhan/{keluhan}/kontrol', 'storeKontrol')->name('keluhan.kontrol');
            });
        });
    });

    // Khusus admin
    Route::middleware('can:admin')->group(function () {
        Route::controller(LogController::class)->prefix('log')->name('log.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{log}', 'show')->name('show');
        });

        Route::controller(AkunController::class)->prefix('manajemen-akun')->name('akun.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/tambah', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{akun}', 'show')->name('show');
            Route::put('/{akun}', 'update')->name('update');
            Route::delete('/{akun}', 'destroy')->name('destroy');
            Route::post('/{akun}/kata-sandi', 'setujuiSandi')->name('sandi.setujui');
            Route::delete('/{akun}/kata-sandi', 'tolakSandi')->name('sandi.tolak');
        });
    });
});
