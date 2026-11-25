<?php

namespace App\Providers;

use App\Models\User;
use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /** Panjang masukan maksimum yang diproses bcrypt; sama dengan BCRYPT_LIMIT di .env. */
    public const BATAS_BYTE_BCRYPT = 72;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Satu aturan kata sandi untuk semua formulir. bcrypt hanya membaca 72 byte pertama, jadi sandi yang lebih
        // panjang ditolak dengan pesan alih-alih dipotong diam-diam (audit WSTG-CRYP-04 F-05).
        Password::defaults(fn () => Password::min(8)->letters()->numbers()->rules([
            function (string $attribute, mixed $value, Closure $fail): void {
                if (strlen((string) $value) > self::BATAS_BYTE_BCRYPT) {
                    $fail(':Attribute maksimal '.self::BATAS_BYTE_BCRYPT.' karakter (huruf beraksen atau emoji dihitung lebih dari satu).');
                }
            },
        ]));

        // Batas percobaan masuk: per nama pengguna + IP (tebak kata sandi) dan per IP (enumerasi banyak nama pengguna).
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by(Str::lower((string) $request->input('username')).'|'.$request->ip()),
            Limit::perMinute(20)->by($request->ip()),
        ]);

        Gate::define('admin', fn (User $user) => $user->role === 'admin');
        // Dokter mendapat bagian KLB tanpa otomatis memperoleh bagian perawat lainnya.
        Gate::define('bagian-perawat', fn (User $user) => in_array($user->role, ['admin', 'perawat'], true));
        // Pemeriksaan kesehatan (rikkes): dokter ikut mengakses, tetapi hanya dokter & admin yang boleh menambah.
        // Pengasuh ikut rikkes, tetapi hanya melihat kegiatan yang masih aktif (tanpa menambah/mengubah hasil).
        Gate::define('bagian-pemeriksaan', fn (User $user) => in_array($user->role, ['admin', 'perawat', 'dokter', 'pengasuh'], true));
        Gate::define('ubah-pemeriksaan', fn (User $user) => in_array($user->role, ['admin', 'perawat', 'dokter'], true));
        Gate::define('riwayat-pemeriksaan', fn (User $user) => $user->role !== 'pengasuh'); // melihat rikkes selesai & ekspor
        Gate::define('tambah-pemeriksaan', fn (User $user) => in_array($user->role, ['admin', 'dokter'], true));
        Gate::define('kelola-pemeriksaan', fn (User $user) => in_array($user->role, ['admin', 'dokter'], true)); // menyelesaikan, mengaktifkan kembali & menghapus
        Gate::define('bagian-klb', fn (User $user) => in_array($user->role, ['admin', 'perawat', 'dokter'], true));
        Gate::define('buat-klb', fn (User $user) => in_array($user->role, ['admin', 'dokter'], true));
        Gate::define('bagian-psikolog', fn (User $user) => in_array($user->role, ['admin', 'psikolog'], true));
        // Satu-satunya definisi akses data keluhan medis (daftar, ekspor, detail, riwayat medis); psikolog tidak termasuk.
        // Dokter boleh melihat; asisten kesehatan & pengasuh hanya melihat Dashboard dan Laporan Kesehatan.
        Gate::define('lihat-keluhan', fn (User $user) => in_array($user->role, ['admin', 'perawat', 'dokter', ...User::HANYA_LIHAT], true));
        Gate::define('riwayat-kesehatan', fn (User $user) => ! $user->hanyaLihat());
        Gate::define('tambah-data', fn (User $user) => ! $user->hanyaLihat());
        Gate::define('edit-data', fn (User $user) => ! $user->hanyaLihat());
    }
}
