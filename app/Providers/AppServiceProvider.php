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
        // Akses per akun, diatur di Manajemen Akun
        Gate::define('tambah-data', fn (User $user) => $user->bisa('tambah'));
        Gate::define('edit-data', fn (User $user) => $user->bisa('edit'));
    }
}
