<?php

namespace App\Http\Controllers;

use App\Models\KegiatanPemeriksaan;
use App\Models\LogAktivitas;
use App\Models\Pemeriksaan;
use App\Models\Taruna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Pemeriksaan kesehatan massal: perawat membuat kegiatan MPTB/Samapta (nama + rentang tanggal),
 * lalu mengisi hasil per taruna. MPTB 2 sesi per hari di dalam rentang, Samapta satu kali per kegiatan.
 */
class PemeriksaanController extends Controller
{
    public function index(Request $request): View
    {
        $selesai = collect(Pemeriksaan::WAJIB)->map(fn ($k) => "$k IS NOT NULL")->implode(' AND ');
        $tambah = $request->query('tambah');

        return view('pemeriksaan-kesehatan.pemeriksaan-kesehatan', [
            'riwayat' => KegiatanPemeriksaan::withCount(['pemeriksaan as selesai' => fn ($q) => $q->whereRaw($selesai)])
                ->withMax('pemeriksaan as diperbarui', 'updated_at')
                ->withCasts(['diperbarui' => 'datetime'])
                ->latest('tanggal_mulai')->latest('id')
                ->get(),
            'totalTaruna' => ['MPTB' => $this->taruna('MPTB')->count(), 'Samapta' => Taruna::count()],
            // ?tambah=MPTB|Samapta membuka formulir tambah pemeriksaan (perlu akses Tambah)
            'tambah' => in_array($tambah, ['MPTB', 'Samapta'], true) && $request->user()->can('tambah-data') ? $tambah : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jenis' => 'required|in:MPTB,Samapta',
            'nama' => 'required|string|max:150',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ], [], ['nama' => 'nama pemeriksaan kesehatan', 'tanggal_mulai' => 'tanggal mulai', 'tanggal_selesai' => 'tanggal selesai']);

        $kegiatan = KegiatanPemeriksaan::create($data + ['created_by' => $request->user()->id]);
        LogAktivitas::catat('Tambah', "Pemeriksaan {$kegiatan->jenis}", "Pemeriksaan {$kegiatan->nama} ({$kegiatan->labelRentang()}) ditambahkan.");

        return redirect()->route('pemeriksaan-kesehatan.show', $kegiatan)->with('status', "Pemeriksaan {$kegiatan->nama} berhasil ditambahkan.");
    }

    public function show(Request $request, KegiatanPemeriksaan $kegiatan): View
    {
        if ($kegiatan->jenis === 'Samapta') {
            $taruna = $this->baris($this->taruna('Samapta'), $kegiatan, '', '');

            return view('pemeriksaan-kesehatan.samapta', [
                'kegiatan' => $kegiatan,
                'taruna' => $taruna->groupBy('tingkat'),
                'totalTaruna' => $taruna->count(),
            ]);
        }

        // Parameter tidak valid jatuh ke default (hari ini, dibatasi ke rentang kegiatan), bukan error.
        $tanggalTersedia = $kegiatan->daftarTanggal();
        $q = Validator::make($request->query(), ['tanggal' => [Rule::in($tanggalTersedia)], 'sesi' => 'in:Pagi,Malam'])->valid();
        $tanggal = $q['tanggal'] ?? min(max(today(), $kegiatan->tanggal_mulai), $kegiatan->tanggal_selesai)->toDateString();
        $sesi = $q['sesi'] ?? 'Pagi';

        return view('pemeriksaan-kesehatan.mptb', [
            'kegiatan' => $kegiatan,
            'tanggalTersedia' => $tanggalTersedia,
            'tanggal' => $tanggal,
            'sesi' => $sesi,
            'taruna' => $this->baris($this->taruna('MPTB'), $kegiatan, $tanggal, $sesi),
        ]);
    }

    public function simpan(Request $request, KegiatanPemeriksaan $kegiatan): RedirectResponse
    {
        if ($kegiatan->jenis === 'Samapta') {
            $hasil = $this->simpanHasil($request, $kegiatan, '', '');

            return redirect()->route('pemeriksaan-kesehatan.show', $kegiatan)
                ->with('status', "Data pemeriksaan {$kegiatan->nama} berhasil disimpan ($hasil).");
        }

        $v = $request->validate([
            'tanggal' => ['required', Rule::in($kegiatan->daftarTanggal()), 'before_or_equal:today'],
            'sesi' => 'required|in:Pagi,Malam',
        ]);
        $hasil = $this->simpanHasil($request, $kegiatan, $v['tanggal'], $v['sesi']);

        return redirect()->route('pemeriksaan-kesehatan.show', ['kegiatan' => $kegiatan] + $v)
            ->with('status', 'Data pemeriksaan sesi '.strtolower($v['sesi'])." berhasil disimpan ($hasil).");
    }

    /** MPTB = masa pengenalan taruna baru, jadi hanya tingkat I. */
    private function taruna(string $jenis): Collection
    {
        return Taruna::with('keluhanTerakhir')
            ->when($jenis === 'MPTB', fn ($q) => $q->where('tingkat', 'I'))
            ->orderBy('tingkat')->orderBy('nama')
            ->get();
    }

    /** Bentuk baris yang dipakai tabel input MPTB/Samapta. */
    private function baris(Collection $taruna, KegiatanPemeriksaan $kegiatan, string $periode, string $sesi): Collection
    {
        $hasil = $kegiatan->pemeriksaan()->where(compact('periode', 'sesi'))->get()->keyBy('taruna_id');

        return $taruna->map(fn (Taruna $t) => [
            'id' => $t->id,
            'nama' => $t->nama,
            'tingkat' => $t->tingkat,
            'jenis_kelamin' => $t->jenis_kelamin,
            'keluhan_sebelumnya' => $t->keluhanTerakhir->keluhan ?? '-',
            'terapi_sebelumnya' => $t->keluhanTerakhir->terapi ?? '-',
            'hasil' => collect(Pemeriksaan::HASIL)->mapWithKeys(fn ($k) => [$k => (string) $hasil->get($t->id)?->$k])->all(),
        ]);
    }

    /**
     * Simpan baris yang berubah saja; tiap taruna yang berubah dicatat satu log.
     * Baris baru butuh akses Tambah, mengubah baris lama butuh akses Edit (Manajemen Akun). Mengembalikan ringkasan untuk pesan status.
     */
    private function simpanHasil(Request $request, KegiatanPemeriksaan $kegiatan, string $periode, string $sesi): string
    {
        $jenis = $kegiatan->jenis;
        $taruna = $this->taruna($jenis)->keyBy('id');

        // "37,5" dari keyboard Indonesia diterima sebagai 37.5
        $request->merge(['data' => array_map(
            fn ($r) => is_array($r) && is_string($r['suhu'] ?? null) ? ['suhu' => str_replace(',', '.', $r['suhu'])] + $r : $r,
            (array) $request->input('data', []),
        )]);

        $data = $request->validate([
            'data' => 'array',
            'data.*.tekanan_darah' => 'nullable|string|max:20',
            'data.*.nadi' => 'nullable|integer|between:0,300',
            'data.*.suhu' => 'nullable|numeric|between:30,45',
            'data.*.pernapasan' => 'nullable|integer|between:0,100',
            'data.*.keluhan' => 'nullable|string|max:255',
            'data.*.terapi' => 'nullable|string|max:255',
            'data.*.keterangan' => 'nullable|in:Sudah membaik,Dalam perawatan',
        ], [], $taruna->flatMap(fn ($t) => collect(Pemeriksaan::HASIL)
            ->mapWithKeys(fn ($k) => ["data.{$t->id}.$k" => str_replace('_', ' ', $k).' '.$t->nama]))->all()
        )['data'] ?? [];

        $jumlah = $dilewati = 0;
        $user = $request->user();
        DB::transaction(function () use ($data, $taruna, $kegiatan, $jenis, $periode, $sesi, $user, &$jumlah, &$dilewati) {
            // Id taruna di luar daftar jenis ini (mis. tingkat II di MPTB) diabaikan.
            foreach (array_intersect_key($data, $taruna->all()) as $id => $baris) {
                $isi = array_intersect_key((array) $baris, array_flip(Pemeriksaan::HASIL)) + array_fill_keys(Pemeriksaan::HASIL, null);
                $p = Pemeriksaan::firstOrNew(['taruna_id' => $id, 'kegiatan_pemeriksaan_id' => $kegiatan->id, 'periode' => $periode, 'sesi' => $sesi])
                    ->fill($isi + ['jenis' => $jenis])->setRelation('kegiatan', $kegiatan);
                $baru = ! $p->exists;

                // Baris kosong yang belum pernah disimpan tidak perlu dibuat.
                if ($baru ? ! array_filter($isi, fn ($v) => $v !== null) : ! $p->isDirty(Pemeriksaan::HASIL)) {
                    continue;
                }
                if (! $user->bisa($baru ? 'tambah' : 'edit')) {
                    $dilewati++;

                    continue;
                }

                $label = fn ($k) => str_replace('_', ' ', $k);
                $rincian = $baru
                    ? 'ditambahkan: '.collect($isi)->map(fn ($v, $k) => $label($k).' '.($v ?? '-'))->implode(', ')
                    : 'diubah: '.collect($p->getDirty())->map(fn ($v, $k) => $label($k).' dari '.($p->getOriginal($k) ?? '-').' menjadi '.($v ?? '-'))->implode(', ');
                $p->save();

                LogAktivitas::catat($baru ? 'Tambah' : 'Edit', "Pemeriksaan $jenis",
                    "Hasil pemeriksaan $jenis taruna {$taruna[$id]->nama} ({$p->labelPeriode()}) $rincian.", pemeriksaanId: $p->id);
                $jumlah++;
            }
        });

        return "$jumlah taruna diperbarui".($dilewati ? ", $dilewati dilewati karena akun tidak punya akses" : '');
    }
}
