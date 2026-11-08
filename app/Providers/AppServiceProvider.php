<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
        Gate::define('admin', fn (User $user) => $user->role === 'admin');
        // Dokter mendapat bagian KLB tanpa otomatis memperoleh bagian perawat lainnya.
        Gate::define('bagian-perawat', fn (User $user) => in_array($user->role, ['admin', 'perawat'], true));
        // Pemeriksaan kesehatan (rikkes): dokter ikut mengakses, tetapi hanya dokter & admin yang boleh menambah.
        // Pengasuh ikut rikkes, tetapi hanya kegiatan yang masih aktif: melihat & mengubah hasil yang sudah ada.
        Gate::define('bagian-pemeriksaan', fn (User $user) => in_array($user->role, ['admin', 'perawat', 'dokter', 'pengasuh'], true));
        Gate::define('ubah-pemeriksaan', fn (User $user) => in_array($user->role, ['admin', 'perawat', 'dokter', 'pengasuh'], true));
        Gate::define('riwayat-pemeriksaan', fn (User $user) => $user->role !== 'pengasuh'); // melihat rikkes selesai & ekspor
        Gate::define('tambah-pemeriksaan', fn (User $user) => in_array($user->role, ['admin', 'dokter'], true));
        Gate::define('kelola-pemeriksaan', fn (User $user) => in_array($user->role, ['admin', 'dokter'], true)); // menyelesaikan, mengaktifkan kembali & menghapus
        Gate::define('bagian-klb', fn (User $user) => in_array($user->role, ['admin', 'perawat', 'dokter'], true));
        Gate::define('buat-klb', fn (User $user) => in_array($user->role, ['admin', 'dokter'], true));
        Gate::define('bagian-psikolog', fn (User $user) => in_array($user->role, ['admin', 'psikolog'], true));
        // Asisten kesehatan & pengasuh hanya melihat Dashboard dan Laporan Kesehatan (termasuk detail keluhan medis)
        Gate::define('lihat-keluhan', fn (User $user) => in_array($user->role, ['admin', 'perawat', ...User::HANYA_LIHAT], true));
        Gate::define('riwayat-kesehatan', fn (User $user) => ! $user->hanyaLihat());
        Gate::define('tambah-data', fn (User $user) => ! $user->hanyaLihat());
        Gate::define('edit-data', fn (User $user) => ! $user->hanyaLihat());
    }
}
