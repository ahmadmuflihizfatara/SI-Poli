<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * Pengisian formulir bersama: akun-akun di halaman yang sama saling melihat kursor, isian yang sedang difokuskan, dan nilai isian.
 * Satu kanal = satu formulir (mis. satu sesi MPTB, satu draf keluhan baru); statusnya di cache dan hanya diubah di bawah lock per kanal.
 * Konsistensi: server satu-satunya sumber nilai (berurutan lewat seq), nilai terakhir menang, dan isian yang sedang difokuskan
 * satu akun terkunci untuk akun lain (akun yang lebih dulu fokus menang).
 * ponytail: polling HTTP ±0,5 detik lewat cache; ganti Laravel Reverb (WebSocket) bila pengguna serentak banyak atau kursor perlu lebih mulus.
 */
class Kolaborasi
{
    /** Akun dianggap meninggalkan halaman setelah sekian detik tanpa sinkron. */
    public const AKTIF_DETIK = 15;

    /** Status kanal hilang sendiri setelah sejam tanpa aktivitas. */
    private const SIMPAN_DETIK = 3600;

    /** Token halaman: kanal + akun + hak ubah, dienkripsi supaya klien tidak bisa memilih kanal atau haknya sendiri. */
    public static function token(string $kanal, bool $ubah, bool $sekali = false): string
    {
        return Crypt::encryptString(json_encode(['k' => $kanal, 'u' => auth()->id(), 'ubah' => $ubah, 'sekali' => $sekali]));
    }

    /** @return array{k: string, u: int, ubah: bool, sekali: bool}|null token milik akun ini, atau null bila palsu/milik akun lain */
    public static function baca(?string $token, User $user): ?array
    {
        try {
            $isi = json_decode(Crypt::decryptString((string) $token), true);
        } catch (DecryptException) {
            return null;
        }

        return is_array($isi) && ($isi['u'] ?? null) === $user->id ? $isi : null;
    }

    /** Id draf formulir data baru dari ?draf=..., atau id baru bila kosong/tidak valid. */
    public static function draf(Request $request): string
    {
        $draf = (string) $request->query('draf');

        return preg_match('/^[A-Za-z0-9]{12}$/', $draf) ? $draf : Str::random(12);
    }

    /** @return array<string, list<string>> id draf jenis ini yang sedang diisi akun lain => nama akun-akun itu */
    public static function drafAktif(string $jenis, User $user): array
    {
        $batas = microtime(true) - self::AKTIF_DETIK;
        $aktif = [];
        foreach (array_keys(Cache::get("kolaborasi:draf:$jenis") ?? []) as $id) {
            $s = Cache::get(self::kunciKanal("draf.$jenis.$id"));
            $nama = collect($s['pengguna'] ?? [])->filter(fn (array $p, int $u) => $u !== $user->id && $p['t'] >= $batas)->pluck('nama')->values()->all();
            if ($nama && ! $s['simpan']) {
                $aktif[$id] = $nama;
            }
        }

        return $aktif;
    }

    /**
     * Terima isian/kursor/fokus akun ini, kembalikan perubahan akun lain sejak seq $masuk['since'].
     *
     * @param  array{k: string, u: int, ubah: bool, sekali: bool}  $token
     * @param  array{since: int, kursor: ?array{a: string, x: float, y: float}, fokus: ?string, isian: array<string, ?string>, hapus: list<string>}  $masuk
     * @return array{seq: int, isian: array<string, ?string>, tolak: list<string>, kunci: array<string, int>, pengguna: list<array{id: int, nama: string, kursor: ?array, fokus: ?string}>, simpan: ?string}
     */
    public static function sinkron(array $token, User $user, array $masuk): array
    {
        $since = $masuk['since'];
        $hasil = self::ubahKanal($token['k'], function (array &$s) use ($token, $user, $masuk, $since): array {
            $kini = microtime(true);
            $s['pengguna'] = array_filter($s['pengguna'], fn (array $p) => $p['t'] >= $kini - self::AKTIF_DETIK);

            // Halaman baru dibuka dan tak ada akun lain: draf lama dibuang, isian kembali mengikuti data tersimpan.
            if ($since === 0 && array_diff_key($s['pengguna'], [$user->id => true]) === []) {
                $s['isian'] = [];
                $s['simpan'] = null;
            }

            // Akun yang hanya melihat tidak ikut mengunci isian.
            $fokus = $token['ubah'] ? $masuk['fokus'] : null;
            $lama = $s['pengguna'][$user->id] ?? null;
            $s['pengguna'][$user->id] = [
                'nama' => $user->name,
                't' => $kini,
                'kursor' => $masuk['kursor'],
                'fokus' => $fokus,
                'fokus_t' => $fokus !== null && $fokus === ($lama['fokus'] ?? null) ? $lama['fokus_t'] : $kini,
            ];

            $kunci = self::pemilikFokus($s['pengguna']);
            $tolak = [];
            if ($token['ubah']) {
                $perubahan = $masuk['isian'] + array_fill_keys($masuk['hapus'], null);
                foreach ($perubahan as $nama => $nilai) {
                    if (($kunci[$nama] ?? $user->id) !== $user->id) {
                        $tolak[] = (string) $nama;

                        continue;
                    }
                    $s['isian'][$nama] = ['v' => $nilai === null && ! in_array($nama, $masuk['hapus'], true) ? '' : $nilai, 'seq' => ++$s['seq'], 'oleh' => $user->id];
                }
            }

            // Gema isian sendiri tidak dikirim balik (bisa menimpa ketikan yang lebih baru), kecuali saat halaman baru dibuka.
            $isian = [];
            foreach ($s['isian'] as $nama => $i) {
                if (($i['seq'] > $since && ($i['oleh'] !== $user->id || $since === 0)) || in_array((string) $nama, $tolak, true)) {
                    $isian[$nama] = $i['v'];
                }
            }

            $pengguna = [];
            foreach ($s['pengguna'] as $id => $p) {
                if ($id !== $user->id) {
                    $pengguna[] = ['id' => $id, 'nama' => $p['nama'], 'kursor' => $p['kursor'], 'fokus' => $p['fokus']];
                }
            }

            $simpan = $s['simpan'];
            $disimpanLain = $simpan && $simpan['oleh'] !== $user->id && ($token['sekali'] || ($since > 0 && $simpan['seq'] > $since));

            return [
                'seq' => $s['seq'],
                'isian' => $isian,
                'tolak' => $tolak,
                'kunci' => array_filter($kunci, fn (int $id) => $id !== $user->id),
                'pengguna' => $pengguna,
                'simpan' => $disimpanLain ? $simpan['nama'] : null,
            ];
        });

        if ($since === 0 && str_starts_with($token['k'], 'draf.')) {
            [, $jenis, $id] = explode('.', $token['k'], 3);
            self::daftarkanDraf($jenis, $id);
        }

        return $hasil;
    }

    /** Akun menutup halaman: kursor & kunci fokusnya langsung hilang di layar akun lain (tanpa menunggu AKTIF_DETIK). */
    public static function keluar(array $token, User $user): void
    {
        self::ubahKanal($token['k'], function (array &$s) use ($user): void {
            unset($s['pengguna'][$user->id]);
        });
    }

    /**
     * Catat formulir disimpan supaya akun lain diberi tahu. Formulir sekali-simpan (draf data baru) menolak simpan kedua
     * agar data tidak tercatat dobel: mengembalikan nama akun yang sudah menyimpannya.
     */
    public static function catatSimpan(?string $token, User $user): ?string
    {
        $t = self::baca($token, $user);
        if (! $t) {
            return null;
        }

        return self::ubahKanal($t['k'], function (array &$s) use ($t, $user): ?string {
            if ($t['sekali'] && $s['simpan']) {
                return $s['simpan']['nama'];
            }
            $s['simpan'] = ['oleh' => $user->id, 'nama' => $user->name, 'seq' => ++$s['seq']];

            return null;
        });
    }

    /** @return array<string, int> nama isian => id akun yang paling dulu memfokuskannya */
    private static function pemilikFokus(array $pengguna): array
    {
        uasort($pengguna, fn (array $a, array $b) => $a['fokus_t'] <=> $b['fokus_t']);
        $kunci = [];
        foreach ($pengguna as $id => $p) {
            if ($p['fokus'] !== null) {
                $kunci[$p['fokus']] ??= $id;
            }
        }

        return $kunci;
    }

    /** Daftar draf per jenis untuk drafAktif(); draf yang statusnya sudah kedaluwarsa dibuang. */
    private static function daftarkanDraf(string $jenis, string $id): void
    {
        Cache::lock("kolaborasi:draf:$jenis:kunci", 5)->block(3, function () use ($jenis, $id): void {
            $daftar = array_filter([$id => true] + (Cache::get("kolaborasi:draf:$jenis") ?? []),
                fn ($_, int|string $d) => (string) $d === $id || Cache::has(self::kunciKanal("draf.$jenis.$d")), ARRAY_FILTER_USE_BOTH);
            Cache::put("kolaborasi:draf:$jenis", $daftar, self::SIMPAN_DETIK);
        });
    }

    private static function kunciKanal(string $kanal): string
    {
        return 'kolaborasi:'.sha1($kanal);
    }

    /** Baca-ubah-tulis status kanal di bawah lock supaya permintaan serentak tidak saling menimpa. */
    private static function ubahKanal(string $kanal, callable $ubah): mixed
    {
        $kunci = self::kunciKanal($kanal);

        return Cache::lock("$kunci:kunci", 5)->block(3, function () use ($kunci, $ubah): mixed {
            // seq mulai 1: since = 0 khusus untuk halaman yang baru dibuka
            $s = Cache::get($kunci) ?? ['seq' => 1, 'isian' => [], 'pengguna' => [], 'simpan' => null];
            $hasil = $ubah($s);
            Cache::put($kunci, $s, self::SIMPAN_DETIK);

            return $hasil;
        });
    }
}
