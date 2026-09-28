{{-- laporan-kesehatan.blade.php — mengikuti artboard "Laporan Kesehatan" (Prototype SI Kesehatan Taruna 2, design system Pulih) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Laporan Kesehatan'])
    <style>
        .lk { table-layout: fixed; font-size: .875rem; line-height: 1.25rem; }
        .lk th, .lk td { padding: .625rem .5rem; }
        .lk thead th { position: sticky; top: 0; z-index: 1; background: var(--primary-700); color: var(--on-primary); border-bottom-color: var(--primary-700); }
        .lk td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .lk .c { text-align: center; }
        .ab { width: 2.25rem; height: 2.25rem; border: 0; border-radius: var(--radius-sm); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: background-color .15s, color .15s; }
        .ab:focus-visible { outline: 2px solid transparent; box-shadow: var(--focus-ring); }
        .ab-detail { background: var(--primary-50); color: var(--primary-700); } .ab-detail:hover { background: var(--primary-700); color: #fff; }
        .ab-ubah { background: var(--secondary-50); color: var(--secondary-800); } .ab-ubah:hover { background: var(--secondary-800); color: #fff; }
        .ab-eval { background: var(--tertiary-50); color: var(--tertiary-800); } .ab-eval:hover { background: var(--tertiary-800); color: #fff; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        // Data contoh sampai tabel laporan tersedia di database; controller cukup mengirim $laporan
        $laporan = $laporan ?? collect(range(1, 14))->map(fn ($i) => [
            'nama' => 'Rahadian Ronggo', 'npm' => '123456', 'kelas' => 'II RKS A', 'tingkat' => 'II', 'kamar' => 'C201',
            'keluhan' => 'Demam', 'terapi' => 'Paracetamol', 'awal' => '24/09/2026',
            'evaluasi' => 'Sudah membaik', 'keterangan' => 'Tidak ada',
            'status' => ['Ringan', 'Ringan', 'Sedang', 'Ringan', 'Berat'][($i - 1) % 5],
        ]);
        $nadaStatus = ['Ringan' => 'pl-badge-primary', 'Sedang' => 'pl-badge-notice', 'Berat' => 'pl-badge-accent'];
        // [judul, lebar kolom (px desain / 16), rata tengah]
        $kolom = [
            ['No', 3, true], ['Nama', 9.375, false], ['NPM', 5, true], ['Kelas', 5.5, true], ['Tingkat', 4, true], ['Kamar', 4, true],
            ['Keluhan', 6, false], ['Terapi', 6.875, false], ['Awal keluhan', 6.75, true], ['Evaluasi kontrol kesehatan', 9.375, false],
            ['Keterangan', 6.5, false], ['Status', 6.25, true], ['Aksi', 8.5, true],
        ];
    @endphp

    {{-- lg:h-screen + flex-1/min-h-0: tabel mengisi sisa tinggi dan scroll di dalam kotaknya sendiri, halaman tidak scroll --}}
    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen lg:h-screen flex flex-col gap-4 pr-4 py-6">

        <header class="flex flex-wrap justify-between items-end gap-4">
            <div class="flex flex-col gap-3">
                <div class="flex flex-col gap-1">
                    <h1 class="h1 m-0 text-primary-900">Laporan Kesehatan</h1>
                    <p class="body-lg m-0 font-medium">{{ now()->locale('id')->translatedFormat('d F Y') }}</p>
                </div>
                <label class="relative block w-80">
                    <span class="sr-only">Cari nama taruna</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-[1.125rem] h-[1.125rem] text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input id="cari" type="search" class="pl-input !pl-10" placeholder="Cari nama taruna">
                </label>
            </div>
            <a href="{{ route('laporan-kesehatan.create') }}" class="pl-btn pl-btn-primary h-11">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambahkan keluhan baru
            </a>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        <div class="pl-table-wrap flex-1 min-h-0">
            <table class="pl-table lk">
                <colgroup>
                    @foreach ($kolom as [, $lebar])
                        <col style="width: {{ $lebar }}rem">
                    @endforeach
                </colgroup>
                <thead>
                    <tr>
                        @foreach ($kolom as [$judul, , $tengah])
                            <th scope="col" @class(['c' => $tengah])>{{ $judul }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="isi-tabel">
                    @forelse ($laporan as $i => $r)
                        <tr data-nama="{{ strtolower($r['nama']) }}">
                            <td class="c">{{ $i + 1 }}</td>
                            <td class="font-semibold">{{ $r['nama'] }}</td>
                            <td class="c tabular-nums">{{ $r['npm'] }}</td>
                            <td class="c">{{ $r['kelas'] }}</td>
                            <td class="c">{{ $r['tingkat'] }}</td>
                            <td class="c">{{ $r['kamar'] }}</td>
                            <td>{{ $r['keluhan'] }}</td>
                            <td>{{ $r['terapi'] }}</td>
                            <td class="c tabular-nums">{{ $r['awal'] }}</td>
                            <td title="{{ $r['evaluasi'] }}">{{ $r['evaluasi'] }}</td>
                            <td>{{ $r['keterangan'] }}</td>
                            <td class="c"><span class="pl-badge {{ $nadaStatus[$r['status']] ?? 'pl-badge-primary' }}">{{ $r['status'] }}</span></td>
                            <td>
                                <div class="flex justify-center gap-1.5">
                                    <button type="button" class="ab ab-detail" aria-label="Detail" title="Detail">
                                        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                    </button>
                                    <button type="button" class="ab ab-ubah" aria-label="Ubah" title="Ubah">
                                        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                                    </button>
                                    <button type="button" class="ab ab-eval" aria-label="Evaluasi kontrol" title="Evaluasi kontrol">
                                        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12h4l3-8 4 16 3-8h4"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">Belum ada laporan kesehatan.</td></tr>
                    @endforelse
                    <tr id="tidak-ditemukan" hidden>
                        <td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">Nama taruna tidak ditemukan.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            <button type="button" class="pl-btn pl-btn-secondary h-11">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Ekspor laporan kesehatan
            </button>
        </div>
    </main>

    <script>
        // Cari nama taruna: saring baris tabel di sisi browser
        (() => {
            const baris = document.querySelectorAll('#isi-tabel tr[data-nama]');
            const kosong = document.getElementById('tidak-ditemukan');
            document.getElementById('cari').addEventListener('input', (e) => {
                const q = e.target.value.trim().toLowerCase();
                let ada = 0;
                baris.forEach((tr) => {
                    const cocok = tr.dataset.nama.includes(q);
                    tr.hidden = !cocok;
                    ada += cocok;
                });
                kosong.hidden = ada > 0 || baris.length === 0;
            });
        })();
    </script>
</body>
</html>
