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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Fillable(['name', 'username', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token', 'sandi_baru'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Role yang hanya bisa melihat Dashboard dan Laporan Kesehatan, tanpa tambah/edit. */
    public const HANYA_LIHAT = ['asisten_kesehatan', 'pengasuh'];

    /** Sama dengan default kolom (role berprivilese terendah), supaya objek yang baru dibuat langsung punya role. */
    protected $attributes = ['role' => 'pengasuh'];

    /** @return HasMany<Keluhan, $this> */
    public function keluhan(): HasMany
    {
        return $this->hasMany(Keluhan::class, 'created_by');
    }

    public function hanyaLihat(): bool
    {
        return in_array($this->role, self::HANYA_LIHAT, true);
    }

    /** Akhiri seluruh sesi aktif & "ingat saya" akun ini, dipakai saat role/kata sandi diubah atau akun dinonaktifkan. */
    public function putusSesi(): void
    {
        $this->forceFill(['remember_token' => Str::random(60)])->save();

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))->table(config('session.table'))->where('user_id', $this->id)->delete();
        }
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
            'terakhir_aktif_at' => 'datetime',
            'sandi_diminta_at' => 'datetime',
            'nonaktif_at' => 'datetime',
            'wajib_ganti_sandi' => 'boolean',
        ];
    }
}
