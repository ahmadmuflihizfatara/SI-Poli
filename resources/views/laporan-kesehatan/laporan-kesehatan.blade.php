{{-- laporan-kesehatan/laporan-kesehatan.blade.php — tata letak "Laporan kesehatan" design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Laporan Kesehatan'])
    <style>
        .lk { table-layout: fixed; }
        .lk th { padding: .75rem .625rem; position: sticky; top: 0; z-index: 1; }
        .lk td { padding: .5rem .625rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        /* Warna baris per status: kelas warna-{status} di tabel diatur tombol "Warna baris" */
        .lk.warna-ringan tr[data-status="ringan"] td { background: color-mix(in srgb, #6fa89d 24%, transparent); }
        .lk.warna-sedang tr[data-status="sedang"] td { background: color-mix(in srgb, #c88a1e 24%, transparent); }
        .lk.warna-berat tr[data-status="berat"] td { background: color-mix(in srgb, #d8693f 24%, transparent); }
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

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <h1 class="pl-page-title">Laporan Kesehatan</h1>
                <p class="pl-page-sub">{{ now()->locale('id')->translatedFormat('d F Y') }}</p>
            </div>
            <div class="pl-page-actions">
                <a href="{{ route('laporan-kesehatan.ekspor') }}" class="pl-btn pl-btn-secondary">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Ekspor laporan kesehatan
                </a>
                @can('tambah-data')
                <a href="{{ route('laporan-kesehatan.create') }}" class="pl-btn pl-btn-primary">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambahkan keluhan baru
                </a>
                @endcan
            </div>
        </header>

        <div class="pl-toolbar">
            @include('partials.pilih-bagian', ['aktif' => 'perawat'])
            <label class="pl-search">
                <span class="pl-sr">Cari nama taruna</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="cari" type="search" class="pl-input" placeholder="Cari nama taruna">
            </label>
            @foreach ([['f-status', 'Status', 'Semua status', array_keys($nadaStatus)], ['f-pemulihan', 'Status pemulihan', 'Semua pemulihan', array_keys($nadaPemulihan)]] as [$id, $nama, $semua, $opsi])
                <label class="block">
                    <span class="pl-sr">Filter {{ $nama }}</span>
                    <select id="{{ $id }}" class="pl-input pl-input--pill !w-auto min-w-[11rem]">
                        <option value="">{{ $semua }}</option>
                        @foreach ($opsi as $o)<option value="{{ strtolower($o) }}">{{ $o }}</option>@endforeach
                    </select>
                </label>
            @endforeach
        </div>

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
                            <td class="font-medium">{{ $r->taruna->nama }}</td>
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
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('laporan-kesehatan.show', $r) }}" class="pl-icon-btn" aria-label="Detail" title="Detail">
                                        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                    </a>
                                    @php $alasan = $r->status_pemulihan === 'Sembuh' ? 'Sudah sembuh' : ($bisaEdit ? null : 'Tidak punya akses Edit'); @endphp
                                    @if ($alasan)
                                        <button type="button" class="pl-icon-btn pl-icon-btn--accent" aria-label="{{ $alasan }}" title="{{ $alasan }}" disabled>
                                            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        </button>
                                    @else
                                        <form action="{{ route('laporan-kesehatan.toggle-sembuh', $r) }}" method="POST" class="inline-flex">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="pl-icon-btn pl-icon-btn--accent" aria-label="Tandai sembuh" title="Tandai sembuh">
                                                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if ($alasan)
                                        <button type="button" class="pl-icon-btn pl-icon-btn--notice" aria-label="{{ $alasan }}, tidak bisa diperbarui" title="{{ $alasan }}, tidak bisa diperbarui" disabled>
                                            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                                        </button>
                                    @else
                                        <a href="{{ route('laporan-kesehatan.kontrol.edit', $r) }}" class="pl-icon-btn pl-icon-btn--notice" aria-label="Perbarui keluhan" title="Perbarui keluhan">
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

        <div class="pl-table-foot">
            <div class="pl-segment items-center" role="group" aria-label="Warna baris berdasarkan status">
                <span class="px-3 label">Warna baris</span>
                @foreach (['Ringan' => '#6fa89d', 'Sedang' => '#c88a1e', 'Berat' => '#d8693f'] as $status => $warna)
                    <button type="button" class="gap-2" data-warna-status="{{ strtolower($status) }}" aria-pressed="false">
                        <span class="w-3 h-3 rounded-full border border-white/70" style="background: {{ $warna }}" aria-hidden="true"></span>{{ $status }}
                    </button>
                @endforeach
            </div>
            @include('laporan-kesehatan.partials.zoom-tabel')
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

        // Warna baris: tiap tombol menyalakan kelas warna-{status} di tabel; pilihan diingat di browser
        (() => {
            const tabel = document.querySelector('.lk');
            let aktif = [];
            try { aktif = JSON.parse(localStorage.getItem('warna-baris')) || []; } catch (e) {}
            document.querySelectorAll('[data-warna-status]').forEach((btn) => {
                const status = btn.dataset.warnaStatus;
                const pasang = (nyala) => {
                    btn.setAttribute('aria-pressed', nyala);
                    tabel.classList.toggle('warna-' + status, nyala);
                };
                pasang(aktif.includes(status));
                btn.addEventListener('click', () => {
                    pasang(btn.getAttribute('aria-pressed') !== 'true');
                    aktif = [...document.querySelectorAll('[data-warna-status][aria-pressed="true"]')].map((b) => b.dataset.warnaStatus);
                    try { localStorage.setItem('warna-baris', JSON.stringify(aktif)); } catch (e) {}
                });
            });
        })();
    </script>

    @include('laporan-kesehatan.partials.urut-tabel')
</body>
</html>
