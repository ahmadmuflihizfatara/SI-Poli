{{-- laporan-kesehatan/laporan-psikologi.blade.php — Laporan Kesehatan bagian psikolog (prototype "Laporan Kesehatan Psikolog"),
     susunan & gaya sama dengan laporan-kesehatan.blade.php, design system Pulih. --}}
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
        .ab-perbarui { background: var(--secondary-50); color: var(--secondary-800); } .ab-perbarui:hover { background: var(--secondary-800); color: #fff; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $bisaEdit = auth()->user()->can('edit-data');
        // [judul, lebar kolom (rem), rata tengah]
        $kolom = [
            ['No', 3, true], ['Nama', 9.375, false], ['NPM', 5, true], ['Kelas', 5.5, true], ['Tingkat', 4, true], ['Kamar', 4, true],
            ['Keluhan', 8, false], ['Terapi', 8, false], ['Awal keluhan', 6.75, true], ['Evaluasi konseling', 11, false],
            ['Keterangan', 10, true], ['Aksi', 6, true],
        ];
    @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-4 pr-4 py-6">

        <header class="flex flex-wrap justify-between items-end gap-4">
            <div class="flex flex-col gap-3">
                <div class="flex flex-col gap-1">
                    <h1 class="h1 m-0 text-primary-900">Laporan Kesehatan</h1>
                    <div class="pl-card self-start inline-flex items-center gap-2.5 px-4 py-2 mt-1">
                        <svg class="w-6 h-6 text-primary-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                        <span class="body-lg font-semibold text-primary-900">{{ now()->locale('id')->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
                @include('partials.pilih-bagian', ['aktif' => 'psikolog'])
                <div class="flex flex-wrap items-center gap-3">
                    <label class="relative block w-80">
                        <span class="sr-only">Cari nama taruna</span>
                        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-[1.125rem] h-[1.125rem] text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <input id="cari" type="search" class="pl-input !pl-10" placeholder="Cari nama taruna">
                    </label>
                    <label class="block">
                        <span class="sr-only">Filter keterangan</span>
                        <select id="f-keterangan" class="pl-input !w-auto min-w-[9.5rem]">
                            <option value="">Semua keterangan</option>
                            <option value="1">Melanjutkan konseling</option>
                            <option value="0">Tidak melanjutkan konseling</option>
                        </select>
                    </label>
                </div>
            </div>
            @can('tambah-data')
            <a href="{{ route('laporan-kesehatan.psikologi.create') }}" class="pl-btn pl-btn-primary h-11">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambahkan keluhan baru
            </a>
            @endcan
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        @include('laporan-kesehatan.partials.zoom-tabel')

        <div class="pl-table-wrap max-h-[70vh]">
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
                        @php $evaluasi = $r->konselingTerakhir->hasil_konseling ?? '-'; @endphp
                        <tr data-nama="{{ strtolower($r->taruna->nama) }}" data-lanjut="{{ (int) $r->lanjut_konseling }}">
                            <td class="c">{{ $i + 1 }}</td>
                            <td class="font-semibold">{{ $r->taruna->nama }}</td>
                            <td class="c tabular-nums">{{ $r->taruna->npm }}</td>
                            <td class="c">{{ $r->taruna->kelas }}</td>
                            <td class="c">{{ $r->taruna->tingkat }}</td>
                            <td class="c">{{ $r->taruna->kamar }}</td>
                            <td title="{{ $r->keluhan }}">{{ $r->keluhan }}</td>
                            <td title="{{ $r->terapi }}">{{ $r->terapi }}</td>
                            <td class="c tabular-nums">{{ $r->tanggal_awal->format('d/m/Y') }}</td>
                            <td title="{{ $evaluasi }}">{{ $evaluasi }}</td>
                            <td class="c"><span class="pl-badge {{ $r->lanjut_konseling ? 'pl-badge-notice' : 'pl-badge-primary' }}">{{ $r->keterangan() }}</span></td>
                            <td>
                                <div class="flex justify-center gap-1.5">
                                    <a href="{{ route('laporan-kesehatan.psikologi.show', $r) }}" class="ab ab-detail" aria-label="Detail" title="Detail">
                                        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                    </a>
                                    @if ($bisaEdit)
                                        <a href="{{ route('laporan-kesehatan.psikologi.edit', $r) }}" class="ab ab-perbarui" aria-label="Perbarui keluhan" title="Perbarui keluhan">
                                            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                                        </a>
                                    @else
                                        <button type="button" class="ab ab-perbarui" style="opacity:.5;cursor:default" aria-label="Tidak punya akses Edit" title="Tidak punya akses Edit" disabled>
                                            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">Belum ada laporan keluhan psikologi.</td></tr>
                    @endforelse
                    <tr id="tidak-ditemukan" hidden>
                        <td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">Tidak ada laporan yang cocok.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('laporan-kesehatan.ekspor') }}" class="pl-btn pl-btn-secondary h-11">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Ekspor laporan kesehatan
            </a>
        </div>
    </main>

    <script>
        // Cari nama taruna + filter keterangan: saring baris tabel di sisi browser, nomor ikut baris yang tampil
        (() => {
            const baris = document.querySelectorAll('#isi-tabel tr[data-nama]');
            const kosong = document.getElementById('tidak-ditemukan');
            const cari = document.getElementById('cari'), fKeterangan = document.getElementById('f-keterangan');
            const saring = () => {
                const q = cari.value.trim().toLowerCase();
                let ada = 0;
                baris.forEach((tr) => {
                    const cocok = tr.dataset.nama.includes(q) && (!fKeterangan.value || tr.dataset.lanjut === fKeterangan.value);
                    tr.hidden = !cocok;
                    // nomor urut = baris yang tampil
                    if (cocok) tr.firstElementChild.textContent = ++ada;
                });
                kosong.hidden = ada > 0 || baris.length === 0;
            };
            [cari, fKeterangan].forEach((el) => el.addEventListener('input', saring));
        })();
    </script>

    @include('laporan-kesehatan.partials.urut-tabel')
</body>
</html>
