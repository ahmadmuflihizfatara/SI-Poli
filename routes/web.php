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

// ponytail: dummy detail record, field ekstra (riwayat kontrol, keterangan lainnya)
// gak ada di dummy index; ganti ke Keluhan::with('taruna','riwayatKontrol')->findOrFail($id)
// begitu tabel keluhan/taruna/riwayat_kontrol beneran ada
Route::get('/laporan-kesehatan/{id}', function (string $id) {
    abort_unless(ctype_digit($id) && $id >= 0 && $id <= 13, 404);

    $status = ['Ringan', 'Ringan', 'Sedang', 'Ringan', 'Berat'][$id % 5];

    $r = [
        'nama' => 'Rahadian Ronggo', 'npm' => '123456', 'kelas' => 'II RKS A', 'tingkat' => 'II',
        'jenis_kelamin' => 'Laki-laki', 'kamar' => 'C201',
        'keluhan' => 'Demam tinggi disertai pusing sejak dua hari terakhir.',
        'terapi' => ['Paracetamol 500mg, 3x sehari', 'Istirahat cukup', 'Perbanyak minum air putih'],
        'awal' => '24/09/2026', 'status' => $status,
        'keterangan_lainnya' => 'Taruna disarankan isolasi mandiri sampai 26/09/2026 dan kontrol ulang pada 27/09/2026.',
        'riwayat' => [
            ['tanggal' => '24/09/2026', 'hasil' => 'Demam 38.2°C', 'keterangan' => 'Diberikan paracetamol'],
            ['tanggal' => '25/09/2026', 'hasil' => 'Demam turun 37.1°C', 'keterangan' => 'Kondisi membaik'],
        ],
    ];

    return view('detail-keluhan', ['id' => $id, 'r' => $r]);
})->name('laporan-kesehatan.show');

Route::get('/password-update', function () {
    return redirect('/login');
})->name('password.update');