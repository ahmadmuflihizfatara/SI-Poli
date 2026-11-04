<?php

namespace App\Http\Controllers;

use App\Models\KegiatanPemeriksaan;
use App\Models\LogAktivitas;
use App\Models\Pemeriksaan;
use App\Models\Taruna;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pemeriksaan kesehatan massal: perawat membuat kegiatan MPTB/Samapta (mulai hari itu, selesai lewat tombol),
 * lalu mengisi hasil per taruna. MPTB 2 sesi per hari selama berjalan, Samapta satu kali per kegiatan.
 */
class PemeriksaanController extends Controller
{
    public function index(): View
    {
        $selesai = collect(Pemeriksaan::WAJIB)->map(fn ($k) => "$k IS NOT NULL")->implode(' AND ');

        return view('pemeriksaan-kesehatan.pemeriksaan-kesehatan', [
            'riwayat' => KegiatanPemeriksaan::withCount(['pemeriksaan as diperiksa' => fn ($q) => $q->whereRaw($selesai)])
                ->withMax('pemeriksaan as diperbarui', 'updated_at')
                ->withCasts(['diperbarui' => 'datetime'])
                ->latest('tanggal_mulai')->latest('id')
                ->get(),
            'totalTaruna' => ['MPTB' => $this->taruna('MPTB')->count(), 'Samapta' => Taruna::count()],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jenis' => 'required|in:MPTB,Samapta',
            'nama' => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:255',
        ], [], ['nama' => 'nama pemeriksaan kesehatan']);

        $kegiatan = KegiatanPemeriksaan::create($data + ['tanggal_mulai' => today(), 'created_by' => $request->user()->id]);
        LogAktivitas::catat('Tambah', "Pemeriksaan {$kegiatan->jenis}", "Pemeriksaan {$kegiatan->nama} ({$kegiatan->labelRentang()}) ditambahkan.");

        return redirect()->route('pemeriksaan-kesehatan.show', $kegiatan)->with('status', "Pemeriksaan {$kegiatan->nama} berhasil ditambahkan.");
    }

    /** Selesai bersifat final: setelah ini hasil pemeriksaan tidak bisa diubah lagi. */
    public function selesaikan(KegiatanPemeriksaan $kegiatan): RedirectResponse
    {
        if (! $kegiatan->selesai()) {
            $kegiatan->update(['tanggal_selesai' => $kegiatan->tanggalAkhir()]);
            LogAktivitas::catat('Edit', "Pemeriksaan {$kegiatan->jenis}", "Pemeriksaan {$kegiatan->nama} dinyatakan selesai ({$kegiatan->labelRentang()}).");
        }

        return redirect()->route('pemeriksaan-kesehatan.show', $kegiatan)->with('status', "Pemeriksaan {$kegiatan->nama} telah selesai.");
    }

    /** Semua hasil pemeriksaan satu taruna dalam satu kegiatan (MPTB: per sesi, terbaru di atas). */
    public function detail(KegiatanPemeriksaan $kegiatan, Taruna $taruna): View
    {
        abort_if($kegiatan->jenis === 'MPTB' && $taruna->tingkat !== 'I', 404);

        return view('pemeriksaan-kesehatan.detail-taruna', [
            'kegiatan' => $kegiatan,
            'taruna' => $taruna,
            'hasil' => $kegiatan->pemeriksaan()->where('taruna_id', $taruna->id)->orderByDesc('periode')->orderBy('sesi')->get(), // 'Malam' < 'Pagi': malam di atas pagi pada hari yang sama
        ]);
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
        $tanggal = $q['tanggal'] ?? min(max(today(), $kegiatan->tanggal_mulai), $kegiatan->tanggalAkhir())->toDateString();
        $sesi = $q['sesi'] ?? 'Pagi';

        return view('pemeriksaan-kesehatan.mptb', [
            'kegiatan' => $kegiatan,
            'tanggalTersedia' => $tanggalTersedia,
            'tanggal' => $tanggal,
            'sesi' => $sesi,
            'taruna' => $this->baris($this->taruna('MPTB'), $kegiatan, $tanggal, $sesi),
        ]);
    }

    public function eksporPdf(Request $request, KegiatanPemeriksaan $kegiatan): Response
    {
        $data = $this->dataEkspor($request, $kegiatan);
        $namaFile = 'pemeriksaan-'.strtolower($kegiatan->jenis).'-'.($data['tanggal'] ?: today()->toDateString()).'.pdf';

        return Pdf::loadView('pemeriksaan-kesehatan.ekspor-pdf', $data + ['kegiatan' => $kegiatan])
            ->setPaper('a4', 'landscape')
            ->download($namaFile);
    }

    public function eksporExcel(Request $request, KegiatanPemeriksaan $kegiatan): StreamedResponse
    {
        $data = $this->dataEkspor($request, $kegiatan);
        $namaFile = 'pemeriksaan-'.strtolower($kegiatan->jenis).'-'.($data['tanggal'] ?: today()->toDateString()).'.csv';

        return response()->streamDownload(function () use ($data, $kegiatan): void {
            $output = fopen('php://output', 'wb');

            if ($output === false) {
                throw new RuntimeException('Tidak dapat membuka aliran untuk ekspor pemeriksaan.');
            }

            if (fwrite($output, "\xEF\xBB\xBF") === false) {
                throw new RuntimeException('Gagal menulis penanda UTF-8 untuk ekspor pemeriksaan.');
            }

            $metadata = [
                ['Pemeriksaan Kesehatan', $kegiatan->nama],
                ['Jenis', $kegiatan->jenis],
                ['Periode', $kegiatan->jenis === 'MPTB' ? $data['tanggal'].' - Sesi '.$data['sesi'] : $kegiatan->labelRentang()],
                [],
                ['Nama', 'Tekanan darah (mmHg)', 'Nadi (x/menit)', 'Suhu (°C)', 'Saturasi (%)', 'Pernapasan (x/menit)', 'Keluhan', 'Terapi', 'Tanda tangan dokter penanggung jawab'],
            ];

            foreach ($metadata as $baris) {
                if (fputcsv($output, array_map(fn ($nilai) => $this->nilaiCsv($nilai), $baris), ';', '"', '') === false) {
                    throw new RuntimeException('Gagal menulis data ekspor pemeriksaan.');
                }
            }

            foreach ($data['baris'] as $baris) {
                $hasil = $baris['hasil'];
                $kolom = [
                    $baris['taruna']->nama,
                    $hasil?->tekanan_darah,
                    $hasil?->nadi,
                    $hasil?->suhu,
                    $hasil?->saturasi,
                    $hasil?->pernapasan,
                    $hasil?->keluhan,
                    $hasil?->terapi,
                    '',
                ];

                if (fputcsv($output, array_map(fn ($nilai) => $this->nilaiCsv($nilai), $kolom), ';', '"', '') === false) {
                    throw new RuntimeException('Gagal menulis data ekspor pemeriksaan.');
                }
            }

            fclose($output);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function simpan(Request $request, KegiatanPemeriksaan $kegiatan): RedirectResponse
    {
        if ($kegiatan->selesai()) {
            return back()->withErrors(['kegiatan' => 'Pemeriksaan ini sudah selesai, hasilnya tidak bisa diubah lagi.']);
        }

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
        return Taruna::query()
            ->when($jenis === 'MPTB', fn ($q) => $q->where('tingkat', 'I'))
            ->orderBy('tingkat')->orderBy('nama')
            ->get();
    }

    /** @return Collection<int, Pemeriksaan> hasil satu sesi, per taruna_id */
    private function hasilSesi(KegiatanPemeriksaan $kegiatan, string $periode, string $sesi): Collection
    {
        return $kegiatan->pemeriksaan()->where(compact('periode', 'sesi'))->get()->keyBy('taruna_id');
    }

    /** Bentuk baris yang dipakai tabel input MPTB/Samapta. */
    private function baris(Collection $taruna, KegiatanPemeriksaan $kegiatan, string $periode, string $sesi): Collection
    {
        $hasil = $this->hasilSesi($kegiatan, $periode, $sesi);
        // MPTB: keluhan & terapi dari sesi sebelumnya (malam <- pagi hari itu, pagi <- malam kemarin)
        $sebelumnya = match ($sesi) {
            'Malam' => $this->hasilSesi($kegiatan, $periode, 'Pagi'),
            'Pagi' => $this->hasilSesi($kegiatan, Carbon::parse($periode)->subDay()->toDateString(), 'Malam'),
            default => collect(),
        };

        return $taruna->map(fn (Taruna $t) => [
            'id' => $t->id,
            'nama' => $t->nama,
            'tingkat' => $t->tingkat,
            // identitas untuk popup ringkas saat baris di-hover
            'info' => $t->only('npm', 'kelas', 'kamar', 'jenis_kelamin'),
            'keluhan_sebelumnya' => $sebelumnya->get($t->id)?->keluhan ?: '-',
            'terapi_sebelumnya' => $sebelumnya->get($t->id)?->terapi ?: '-',
            'hasil' => collect(Pemeriksaan::HASIL)->mapWithKeys(fn ($k) => [$k => (string) $hasil->get($t->id)?->$k])->all(),
        ]);
    }

    /**
     * @return array{
     *     tanggal: string,
     *     sesi: string,
     *     baris: Collection<int, array{taruna: Taruna, hasil: ?Pemeriksaan}>
     * }
     */
    private function dataEkspor(Request $request, KegiatanPemeriksaan $kegiatan): array
    {
        $tanggal = '';
        $sesi = '';
        $query = $kegiatan->pemeriksaan();

        if ($kegiatan->jenis === 'MPTB') {
            $pilihan = $request->validate([
                'tanggal' => ['sometimes', 'required', Rule::in($kegiatan->daftarTanggal()), 'before_or_equal:today'],
                'sesi' => ['sometimes', 'required', 'in:Pagi,Malam'],
            ]);
            $tanggal = $pilihan['tanggal'] ?? min(max(today(), $kegiatan->tanggal_mulai), $kegiatan->tanggalAkhir())->toDateString();
            $sesi = $pilihan['sesi'] ?? 'Pagi';
            $query->where('periode', $tanggal)->where('sesi', $sesi);
        }

        $hasil = $query->get()->keyBy('taruna_id');
        $baris = $this->taruna($kegiatan->jenis)
            ->map(fn (Taruna $taruna): array => ['taruna' => $taruna, 'hasil' => $hasil->get($taruna->id)]);

        return compact('tanggal', 'sesi', 'baris');
    }

    private function nilaiCsv(mixed $nilai): string
    {
        $nilai = (string) ($nilai ?? '');

        return preg_match('/^[\t\r ]*[=+\-@]/', $nilai) ? "'".$nilai : $nilai;
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
            'data.*.saturasi' => 'nullable|integer|between:50,100',
            'data.*.pernapasan' => 'nullable|integer|between:0,100',
            'data.*.keluhan' => 'nullable|string|max:255',
            'data.*.terapi' => 'nullable|string|max:255',
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
                if ($user->cannot($baru ? 'tambah-data' : 'edit-data')) {
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
