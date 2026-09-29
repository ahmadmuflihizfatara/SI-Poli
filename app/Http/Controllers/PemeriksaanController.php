<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Pemeriksaan;
use App\Models\Taruna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/** Pemeriksaan kesehatan massal: MPTB (2 sesi per hari) dan Samapta (per semester). */
class PemeriksaanController extends Controller
{
    public function index(): View
    {
        $selesai = collect(Pemeriksaan::WAJIB)->map(fn ($k) => "$k IS NOT NULL")->implode(' AND ');

        return view('pemeriksaan-kesehatan.pemeriksaan-kesehatan', [
            'riwayat' => Pemeriksaan::selectRaw("jenis, periode, sesi, MAX(updated_at) as diperbarui, SUM(CASE WHEN $selesai THEN 1 ELSE 0 END) as selesai")
                ->groupBy('jenis', 'periode', 'sesi')
                ->withCasts(['diperbarui' => 'datetime'])
                ->orderByDesc('diperbarui')
                ->get(),
            'totalTaruna' => ['MPTB' => $this->taruna('MPTB')->count(), 'Samapta' => Taruna::count()],
        ]);
    }

    public function mptb(Request $request): View
    {
        // Parameter tidak valid jatuh ke default, bukan error.
        $q = Validator::make($request->query(), ['tanggal' => 'date_format:Y-m-d|before_or_equal:today', 'sesi' => 'in:Pagi,Malam'])->valid();
        $tanggal = $q['tanggal'] ?? today()->toDateString();
        $sesi = $q['sesi'] ?? 'Pagi';
        $tahun = Carbon::parse($tanggal)->year;

        return view('pemeriksaan-kesehatan.mptb', [
            'judulPeriode' => "MPTB Tahun Anggaran $tahun/".($tahun + 1),
            'tanggalTersedia' => Pemeriksaan::where('jenis', 'MPTB')->distinct()->pluck('periode')
                ->push(today()->toDateString(), $tanggal)->unique()->sortDesc()->values()->all(),
            'tanggal' => $tanggal,
            'sesi' => $sesi,
            'taruna' => $this->baris($this->taruna('MPTB'), 'MPTB', $tanggal, $sesi),
        ]);
    }

    public function simpanMptb(Request $request): RedirectResponse
    {
        $v = $request->validate(['tanggal' => 'required|date_format:Y-m-d|before_or_equal:today', 'sesi' => 'required|in:Pagi,Malam']);
        $jumlah = $this->simpan($request, 'MPTB', $v['tanggal'], $v['sesi']);

        return redirect()->route('pemeriksaan-kesehatan.mptb.index', $v)
            ->with('status', 'Data pemeriksaan sesi '.strtolower($v['sesi'])." berhasil disimpan ($jumlah taruna diperbarui).");
    }

    public function samapta(Request $request): View
    {
        $semester = Validator::make($request->query(), ['semester' => 'regex:/^\d{4}-(ganjil|genap)$/'])->valid()['semester']
            ?? Pemeriksaan::semesterBerjalan();

        // Semester berjalan + 2 sebelumnya, ditambah semester yang dibuka dari riwayat bila lebih lama.
        $pilihan = [Pemeriksaan::semesterBerjalan()];
        while (count($pilihan) < 3) {
            [$y, $s] = explode('-', end($pilihan));
            $pilihan[] = $s === 'genap' ? "$y-ganjil" : ($y - 1).'-genap';
        }
        $pilihan = array_unique([...$pilihan, $semester]);

        $taruna = $this->baris($this->taruna('Samapta'), 'Samapta', $semester, '');

        return view('pemeriksaan-kesehatan.samapta', [
            'judulPeriode' => 'Samapta Semester '.Pemeriksaan::labelSemester($semester),
            'semesterTersedia' => array_map(fn ($s) => ['label' => Pemeriksaan::labelSemester($s), 'value' => $s], $pilihan),
            'semester' => $semester,
            'taruna' => $taruna->groupBy('tingkat'),
            'totalTaruna' => $taruna->count(),
        ]);
    }

    public function simpanSamapta(Request $request): RedirectResponse
    {
        $v = $request->validate(['semester' => ['required', 'regex:/^\d{4}-(ganjil|genap)$/']]);
        $jumlah = $this->simpan($request, 'Samapta', $v['semester'], '');

        return redirect()->route('pemeriksaan-kesehatan.samapta.index', $v)
            ->with('status', 'Data pemeriksaan Samapta semester '.Pemeriksaan::labelSemester($v['semester'])." berhasil disimpan ($jumlah taruna diperbarui).");
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
    private function baris(Collection $taruna, string $jenis, string $periode, string $sesi): Collection
    {
        $hasil = Pemeriksaan::where(compact('jenis', 'periode', 'sesi'))->get()->keyBy('taruna_id');

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

    /** Simpan baris yang berubah saja; tiap taruna yang berubah dicatat satu log. Mengembalikan jumlah taruna yang diperbarui. */
    private function simpan(Request $request, string $jenis, string $periode, string $sesi): int
    {
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

        $jumlah = 0;
        DB::transaction(function () use ($data, $taruna, $jenis, $periode, $sesi, &$jumlah) {
            // Id taruna di luar daftar jenis ini (mis. tingkat II di MPTB) diabaikan.
            foreach (array_intersect_key($data, $taruna->all()) as $id => $baris) {
                $isi = array_intersect_key((array) $baris, array_flip(Pemeriksaan::HASIL)) + array_fill_keys(Pemeriksaan::HASIL, null);
                $p = Pemeriksaan::firstOrNew(['taruna_id' => $id, 'jenis' => $jenis, 'periode' => $periode, 'sesi' => $sesi])->fill($isi);
                $baru = ! $p->exists;

                // Baris kosong yang belum pernah disimpan tidak perlu dibuat.
                if ($baru ? ! array_filter($isi, fn ($v) => $v !== null) : ! $p->isDirty(Pemeriksaan::HASIL)) {
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

        return $jumlah;
    }
}
