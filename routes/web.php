<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route Halaman Utama
Route::get('/', function () {
    return view('login');
});

// Route Auth & Profile
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/lupa-password', function () {
    return view('lupa-password');
})->name('password.request');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Route Logout (Mengarahkan kembali ke Login)
Route::any('/logout', function () {
    return redirect('/login');
})->name('logout');

// Route Fitur & Navigasi Dashboard
Route::get('/laporan-kesehatan', function () {
    return view('laporan-kesehatan');
})->name('laporan-kesehatan.index');

Route::get('/laporan-kesehatan/tambah', function () {
    return view('keluhan-baru');
})->name('laporan-kesehatan.create');

// ponytail: validasi saja, belum disimpan karena tabel laporan belum ada; simpan ke model di sini nanti
Route::post('/laporan-kesehatan', function (Request $request) {
    $request->validate([
        'nama' => 'required|string|max:100',
        'npm' => 'required|string|max:20',
        'kelas' => 'required|string|max:20',
        'tingkat' => 'required|in:I,II,III,IV',
        'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
        'kamar' => 'required|string|max:20',
        'tekanan_darah' => 'required|string|max:20',
        'suhu' => 'required|numeric|between:30,45',
        'nadi' => 'required|integer|min:0',
        'saturasi' => 'nullable|integer|between:0,100',
        'pernapasan' => 'nullable|integer|min:0',
        'skala_nyeri' => 'nullable|integer|between:0,10',
        'ruang_kelas' => 'nullable|string|max:50',
        'ruang_kamar' => 'nullable|string|max:50',
        'keluhan' => 'required|string',
        'terapi' => 'required|string',
        'hasil_pemeriksaan' => 'nullable|string',
        'status' => 'required|in:Ringan,Sedang,Berat',
        'keterangan' => 'nullable|string',
    ]);

    return redirect()->route('laporan-kesehatan.index')->with('status', 'Keluhan baru berhasil ditambahkan.');
})->name('laporan-kesehatan.store');

Route::get('/password-update', function () {
    return redirect('/login');
})->name('password.update');