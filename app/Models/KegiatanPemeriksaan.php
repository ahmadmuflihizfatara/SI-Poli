<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** Satu kegiatan MPTB/Samapta (nama, mulai saat dibuat, selesai lewat tombol); hasil per taruna ada di Pemeriksaan. */
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

    /** Selesai = sudah dinyatakan selesai lewat tombol; sebelumnya tanggal_selesai kosong. */
    public function selesai(): bool
    {
        return $this->tanggal_selesai !== null;
    }

    /** Akhir rentang yang sudah berjalan: tanggal selesai, atau hari ini bila masih berjalan. */
    public function tanggalAkhir(): Carbon
    {
        return $this->tanggal_selesai ?? max(today(), $this->tanggal_mulai);
    }

    /** '1 – 14 Oktober 2026', atau '1 Oktober 2026 – sekarang' selama masih berjalan */
    public function labelRentang(): string
    {
        $fmt = fn (Carbon $d, string $pola) => $d->locale('id')->translatedFormat($pola);
        if (! $this->selesai()) {
            return $fmt($this->tanggal_mulai, 'j F Y').' – sekarang';
        }
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
        return collect($this->tanggal_mulai->daysUntil($this->tanggalAkhir()))->map->toDateString();
    }

    /** @return HasMany<Pemeriksaan, $this> */
    public function pemeriksaan(): HasMany
    {
        return $this->hasMany(Pemeriksaan::class);
    }
}
