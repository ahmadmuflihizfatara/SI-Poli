<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'akses_tambah', 'akses_edit'])]
#[Hidden(['password', 'remember_token', 'sandi_baru'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Sama dengan default kolom, supaya objek yang baru dibuat langsung punya nilai akses. */
    protected $attributes = ['akses_tambah' => true, 'akses_edit' => true];

    /** @return HasMany<Keluhan, $this> */
    public function keluhan(): HasMany
    {
        return $this->hasMany(Keluhan::class, 'created_by');
    }

    /** Admin selalu punya semua akses. */
    public function bisa(string $akses): bool
    {
        return $this->role === 'admin' || (bool) $this->{"akses_$akses"};
    }

    public function labelAktif(): string
    {
        return match (true) {
            ! $this->terakhir_aktif_at => 'Belum pernah',
            $this->terakhir_aktif_at->gt(now()->subMinutes(5)) => 'Saat ini',
            default => $this->terakhir_aktif_at->locale('id')->diffForHumans(),
        };
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'akses_tambah' => 'boolean',
            'akses_edit' => 'boolean',
            'terakhir_aktif_at' => 'datetime',
            'sandi_diminta_at' => 'datetime',
        ];
    }
}
