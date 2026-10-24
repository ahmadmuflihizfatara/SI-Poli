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
        Gate::define('bagian-klb', fn (User $user) => in_array($user->role, ['admin', 'perawat', 'dokter'], true));
        Gate::define('buat-klb', fn (User $user) => $user->role === 'dokter');
        Gate::define('bagian-psikolog', fn (User $user) => in_array($user->role, ['admin', 'psikolog'], true));
        // Akses per akun, diatur di Manajemen Akun
        Gate::define('tambah-data', fn (User $user) => $user->bisa('tambah'));
        Gate::define('edit-data', fn (User $user) => $user->bisa('edit'));
    }
}
