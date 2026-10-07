<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** Satu kegiatan MPTB/Samapta (nama + rentang tanggal); hasil per taruna ada di Pemeriksaan. */
#[Fillable(['jenis', 'nama', 'tanggal_mulai', 'tanggal_selesai', 'created_by'])]
class KegiatanPemeriksaan extends Model
{
    protected $table = 'kegiatan_pemeriksaan';

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    /** '1 – 14 Oktober 2026' */
    public function labelRentang(): string
    {
        $fmt = fn (Carbon $d, string $pola) => $d->locale('id')->translatedFormat($pola);
        $pola = match (true) {
            $this->tanggal_mulai->isSameMonth($this->tanggal_selesai) => 'j',
            $this->tanggal_mulai->isSameYear($this->tanggal_selesai) => 'j F',
            default => 'j F Y',
        };

        return $this->tanggal_mulai->isSameDay($this->tanggal_selesai)
            ? $fmt($this->tanggal_selesai, 'j F Y')
            : $fmt($this->tanggal_mulai, $pola).' – '.$fmt($this->tanggal_selesai, 'j F Y');
    }

    /**
     * Tanggal yang bisa dipilih di tabel MPTB (Y-m-d).
     *
     * @return Collection<int, string>
     */
    public function daftarTanggal(): Collection
    {
        return collect($this->tanggal_mulai->daysUntil($this->tanggal_selesai))->map->toDateString();
    }

    /** @return HasMany<Pemeriksaan, $this> */
    public function pemeriksaan(): HasMany
    {
        return $this->hasMany(Pemeriksaan::class);
    }
}
