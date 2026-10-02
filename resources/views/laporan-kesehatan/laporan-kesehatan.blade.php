{{-- laporan-kesehatan/laporan-kesehatan.blade.php — mengikuti artboard "Laporan Kesehatan" (Prototype SI Kesehatan Taruna 2, design system Pulih) --}}
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
        .ab-sembuh { background: var(--secondary-50); color: var(--secondary-800); } .ab-sembuh:hover { background: var(--secondary-800); color: #fff; }
        .ab-kontrol { background: var(--tertiary-50); color: var(--tertiary-800); } .ab-kontrol:hover { background: var(--tertiary-800); color: #fff; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $bisaEdit = auth()->user()->can('edit-data');
        $nadaStatus = ['Ringan' => 'pl-badge-primary', 'Sedang' => 'pl-badge-notice', 'Berat' => 'pl-badge-accent'];
        $nadaPemulihan = ['Dalam perawatan' => 'pl-badge-accent', 'Isolasi mandiri' => 'pl-badge-notice', 'Sembuh' => 'pl-badge-primary'];
        // [judul, lebar kolom (px desain / 16), rata tengah]
        $kolom = [
            ['No', 3, true], ['Nama', 9.375, false], ['NPM', 5, true], ['Kelas', 5.5, true], ['Tingkat', 4, true], ['Kamar', 4, true],
            ['Keluhan', 6, false], ['Terapi', 6.875, false], ['Awal keluhan', 6.75, true], ['Evaluasi kontrol kesehatan', 9.375, false], ['Kontrol selanjutnya', 7, true],
            ['Keterangan', 6.5, false], ['Status', 6.25, true], ['Status Pemulihan', 7, true], ['Aksi', 8.5, true],
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
                @include('partials.pilih-bagian', ['aktif' => 'perawat'])
                <div class="flex flex-wrap items-center gap-3">
                    <label class="relative block w-80">
                        <span class="sr-only">Cari nama taruna</span>
                        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-[1.125rem] h-[1.125rem] text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <input id="cari" type="search" class="pl-input !pl-10" placeholder="Cari nama taruna">
                    </label>
                    @foreach ([['f-status', 'Status', 'Semua status', array_keys($nadaStatus)], ['f-pemulihan', 'Status pemulihan', 'Semua pemulihan', array_keys($nadaPemulihan)]] as [$id, $nama, $semua, $opsi])
                        <label class="block">
                            <span class="sr-only">Filter {{ $nama }}</span>
                            <select id="{{ $id }}" class="pl-input !w-auto min-w-[9.5rem]">
                                <option value="">{{ $semua }}</option>
                                @foreach ($opsi as $o)<option value="{{ strtolower($o) }}">{{ $o }}</option>@endforeach
                            </select>
                        </label>
                    @endforeach
                </div>
            </div>
            @can('tambah-data')
            <a href="{{ route('laporan-kesehatan.create') }}" class="pl-btn pl-btn-primary h-11">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambahkan keluhan baru
            </a>
            @endcan
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

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
                        @php $evaluasi = $r->riwayatKontrol->first()->hasil_kontrol ?? '-'; @endphp
                        <tr data-nama="{{ strtolower($r->taruna->nama) }}" data-status="{{ strtolower($r->status) }}" data-pemulihan="{{ strtolower($r->status_pemulihan) }}">
                            <td class="c">{{ $i + 1 }}</td>
                            <td class="font-semibold">{{ $r->taruna->nama }}</td>
                            <td class="c tabular-nums">{{ $r->taruna->npm }}</td>
                            <td class="c">{{ $r->taruna->kelas }}</td>
                            <td class="c">{{ $r->taruna->tingkat }}</td>
                            <td class="c">{{ $r->taruna->kamar }}</td>
                            <td>{{ $r->keluhan }}</td>
                            <td>{{ $r->terapi }}</td>
                            <td class="c tabular-nums">{{ $r->tanggal_awal->format('d/m/Y') }}</td>
                            <td title="{{ $evaluasi }}">{{ $evaluasi }}</td>
                            {{-- ponytail: taruna yang sudah sembuh tidak punya jadwal kontrol lagi --}}
                            <td class="c tabular-nums">{{ $r->status_pemulihan !== 'Sembuh' ? ($r->tanggal_kontrol_selanjutnya?->format('d/m/Y') ?? '-') : '-' }}</td>
                            <td>{{ $r->keterangan ?: '-' }}</td>
                            <td class="c"><span class="pl-badge {{ $nadaStatus[$r->status] ?? 'pl-badge-primary' }}">{{ $r->status }}</span></td>
                            <td class="c"><span class="pl-badge {{ $nadaPemulihan[$r->status_pemulihan] ?? 'pl-badge-primary' }}">{{ $r->status_pemulihan }}</span></td>
                            <td>
                                <div class="flex justify-center gap-1.5">
                                    <a href="{{ route('laporan-kesehatan.show', $r) }}" class="ab ab-detail" aria-label="Detail" title="Detail">
                                        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                    </a>
                                    @php $alasan = $r->status_pemulihan === 'Sembuh' ? 'Sudah sembuh' : ($bisaEdit ? null : 'Tidak punya akses Edit'); @endphp
                                    @if ($alasan)
                                        <button type="button" class="ab ab-sembuh" style="opacity:.5;cursor:default" aria-label="{{ $alasan }}" title="{{ $alasan }}" disabled>
                                            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        </button>
                                    @else
                                        <form action="{{ route('laporan-kesehatan.toggle-sembuh', $r) }}" method="POST" class="inline-flex">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="ab ab-sembuh" aria-label="Tandai sembuh" title="Tandai sembuh">
                                                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if ($alasan)
                                        <button type="button" class="ab ab-kontrol" style="opacity:.5;cursor:default" aria-label="{{ $alasan }}, tidak bisa diperbarui" title="{{ $alasan }}, tidak bisa diperbarui" disabled>
                                            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                                        </button>
                                    @else
                                        <a href="{{ route('laporan-kesehatan.kontrol.edit', $r) }}" class="ab ab-kontrol" aria-label="Perbarui keluhan" title="Perbarui keluhan">
                                            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">Belum ada laporan kesehatan.</td></tr>
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
        // Cari nama taruna + filter status / status pemulihan: saring baris tabel di sisi browser, nomor ikut baris yang tampil
        (() => {
            const baris = document.querySelectorAll('#isi-tabel tr[data-nama]');
            const kosong = document.getElementById('tidak-ditemukan');
            const cari = document.getElementById('cari'), fStatus = document.getElementById('f-status'), fPemulihan = document.getElementById('f-pemulihan');
            const saring = () => {
                const q = cari.value.trim().toLowerCase();
                let ada = 0;
                baris.forEach((tr) => {
                    const cocok = tr.dataset.nama.includes(q)
                        && (!fStatus.value || tr.dataset.status === fStatus.value)
                        && (!fPemulihan.value || tr.dataset.pemulihan === fPemulihan.value);
                    tr.hidden = !cocok;
                    // nomor urut = baris yang tampil
                    if (cocok) tr.firstElementChild.textContent = ++ada;
                });
                kosong.hidden = ada > 0 || baris.length === 0;
            };
            [cari, fStatus, fPemulihan].forEach((el) => el.addEventListener('input', saring));
        })();
    </script>

    @include('laporan-kesehatan.partials.urut-tabel')
</body>
</html>
