{{-- laporan-kesehatan/laporan-kesehatan.blade.php — tata letak "Laporan kesehatan" design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Laporan Kesehatan'])
    <style>
        .lk { table-layout: fixed; }
        .lk th { padding: .75rem .625rem; position: sticky; top: 0; z-index: 1; }
        .lk td { padding: .5rem .625rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        /* Kepala tabel tegas: hijau tua + teks putih tebal */
        .lk th { background: var(--strong); color: var(--on-primary); font-weight: 600; font-size: max(14px, .875rem); border-bottom: 0; }
        .lk th .urut:hover { color: var(--on-primary); text-decoration: underline; text-underline-offset: 3px; }
        .lk th .urut:focus-visible { outline-color: var(--on-primary); }
        .lk th .urut[data-ke]::before { background: var(--on-primary); color: var(--strong); } /* nomor prioritas urut di kepala hijau */
        /* Teks panjang: maksimal 2 baris lalu "…" (teks utuh ada di popup ringkas) */
        .lk td.lk-bungkus { white-space: normal; }
        .lk-clamp { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-clamp: 2; overflow: hidden; overflow-wrap: break-word; }
        /* Kontrol terlambat / hari ini */
        .lk td.lk-kontrol { white-space: normal; }
        .lk-kontrol > span:first-child { display: block; }
        .lk-kontrol .pl-badge { margin-top: .25rem; }
        /* Tombol di baris toolbar: tinggi sama dengan kolom cari & filter (2,75rem); versi ikon berbentuk persegi */
        .lk-tombol-bar { height: 2.75rem; }
        /* Kolom cari tampil sama dengan tombol filter (.pl-saring__tombol): pil, garis inset, huruf 500 */
        .pl-search .pl-input { border: 0; box-shadow: inset 0 0 0 1px var(--field-line); font: 500 max(14px, .875rem)/1.4 var(--font-sans); transition: box-shadow .15s; }
        .pl-search .pl-input:hover { box-shadow: inset 0 0 0 1px var(--primary-400); }
        .pl-search .pl-input:focus-visible { outline: 2px solid var(--focus); outline-offset: 2px; box-shadow: inset 0 0 0 1px var(--primary-400); }
        .lk-tombol-ikon { width: 2.75rem; padding: 0; justify-content: center; }
        .lk td.lk-nama { border-right: 1px solid var(--border); } /* sekat antara kolom nama dan kolom lain */
        .lk-penanda { width: 1.125rem; height: 1.125rem; flex: none; }
        .lk-penanda-baris { display: none; margin-right: .25rem; vertical-align: -.2em; }
        .lk td.lk-nama.lk-gabung .lk-penanda { display: none; } /* sel nama tergabung: penanda per keluhan pindah ke sel keluhan */
        .lk tr:has(> td.lk-nama.lk-gabung) .lk-penanda-baris, .lk tr:has(> td.lk-nama[hidden]) .lk-penanda-baris { display: inline-block; }
        .pl-badge--ikon::before { display: none; }
        .pl-badge--ikon svg { width: .875rem; height: .875rem; flex: none; color: var(--ring); }
.lk td.lk-nama.lk-hover { background: color-mix(in srgb, var(--cloud) 45%, transparent); }
        .lk-telat-ikon { display: none; width: 1.125rem; height: 1.125rem; flex: none; color: var(--secondary-500); } /* penanda terlambat = ikon jam setelah nama; sel nama gabungan menampilkannya bila ada keluhan terlambat di kelompoknya */
        .lk td.lk-nama.lk-telat .lk-telat-ikon { display: block; }
        /* header sempit: ikon urut harus tetap muat */
        .lk th.lk-th-warna { padding-left: 0; padding-right: 0; }
        .lk th.lk-th-warna .urut { width: 100%; justify-content: center; }
        .lk td.lk-warna.lk-bawah { border-radius: 0 0 .5rem 0; }
        .lk tbody tr td.lk-warna { padding: 0; border-bottom: 0; background: var(--w) !important; } /* sel penuh warna tingkat sakit; !important mengalahkan warna baris & hover */

        /* Popup ringkas saat baris di-hover: kartu kecil di samping kursor, bisa didatangi untuk menekan tombol */
        .lk-pop { position: fixed; z-index: 40; left: 0; top: 0; width: max(24rem, 340px); max-width: calc(100vw - 1rem); box-sizing: border-box; display: flex; flex-direction: column; gap: .625rem; max-height: calc(100vh - 1rem); overflow-y: auto; overscroll-behavior: contain;
                  padding: 1rem 1.125rem 1.125rem; border-radius: var(--radius-sm); background: var(--surface-card); color: var(--text); box-shadow: 0 0 0 1px var(--border), var(--shadow-lg);
                  font: 400 max(14px, .875rem)/1.4 var(--font-sans);
                  opacity: 0; visibility: hidden; transform: translateY(.375rem) scale(.98); transform-origin: top left;
                  transition: opacity .16s ease, transform .2s cubic-bezier(.2, .7, .2, 1), visibility 0s .2s; }
        /* posisi baru beranimasi hanya saat sudah terbuka (pindah baris); saat pertama muncul langsung di tempatnya */
        .lk-pop.on { opacity: 1; visibility: visible; transform: none; transition: opacity .16s ease, transform .2s cubic-bezier(.2, .7, .2, 1), top .18s ease, left .18s ease; }
        .lk-pop__kepala { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; padding-bottom: .625rem; border-bottom: 1px solid var(--border); }
        .lk-pop__kepala b { font: 700 max(16px, 1rem)/1.3 var(--font-sans); letter-spacing: -.01em; color: var(--heading); }
        .lk-pop__kepala small { display: block; margin-top: .125rem; color: var(--text-muted); font: 500 max(12px, .75rem)/1.3 var(--font-sans); }
        .lk-pop dt { font-weight: 500; color: var(--text-muted); }
        .lk-pop dd { font-weight: 400; color: var(--text); }
        .lk-pop__identitas { display: grid; grid-template-columns: 1fr 1fr; gap: .25rem .75rem; margin: 0; padding: .5rem .625rem; border-radius: .625rem; background: var(--cloud); }
        .lk-pop__identitas > div { display: flex; align-items: baseline; gap: .375rem; min-width: 0; }
        .lk-pop__identitas dd { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .lk-pop__identitas dt { color: var(--text-muted); font-size: max(12px, .75rem); }
        .lk-pop__identitas dd { margin: 0; font-weight: 600; font-variant-numeric: tabular-nums; }
        .lk-pop__isi { display: flex; flex-direction: column; gap: .375rem; margin: 0; }
        .lk-pop__isi dt { color: var(--text-muted); font-size: max(12px, .75rem); }
        .lk-pop__isi dd { margin: .125rem 0 0; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-clamp: 2; overflow: hidden; }
        .lk-pop__isi dd .pl-badge { margin-left: .375rem; vertical-align: middle; }
        .lk-pop__daftar { display: flex; flex-direction: column; gap: .875rem; }
        .lk-pop__blok { display: flex; flex-direction: column; gap: .625rem; }
        .lk-pop__blok + .lk-pop__blok { padding-top: .875rem; border-top: 1px solid var(--border); }
        .lk-pop__blokkepala { display: flex; align-items: center; gap: .625rem; }
        .lk-pop__blokkepala .pl-badge::before { display: none; } /* badge tingkat keparahan: warna latar = tingkat, tanpa titik */
        .lk-pop__blokkepala .pl-badge { padding: .3125rem .75rem; background: var(--w, var(--cloud)); color: #fff; font: 700 max(13px, .8125rem)/1.2 var(--font-sans); letter-spacing: .01em; }
        .lk-pop__blokkepala small { color: var(--text-muted); font: 500 max(12px, .75rem)/1.3 var(--font-sans); }
        .lk-pop__aksi { display: flex; gap: .5rem; }
        .lk-pop__aksi > * { flex: 1; justify-content: center; }
        .lk-pop__aksi form { display: flex; }
        .lk-pop__aksi .pl-btn { height: 2.25rem; padding: 0 .75rem; font-size: max(13px, .8125rem); }
        .lk-pop__aksi form .pl-btn { flex: 1; justify-content: center; }
        /* Mode menyamping (hover sel nama, taruna dengan >1 keluhan): desain sama dengan popup biasa,
           blok keluhan berjajar ke samping dengan sekat tegak, tanpa tombol sembuh / edit */
        .lk-pop--samping { width: auto; max-width: min(max(54rem, 760px), calc(100vw - 1rem)); }
        .lk-pop--samping .lk-pop__daftar { flex-direction: row; gap: 1rem; overflow-x: auto; overscroll-behavior: contain; padding-bottom: .125rem; }
        .lk-pop--samping .lk-pop__blok { flex: 0 0 max(15rem, 220px); }
        .lk-pop--samping .lk-pop__blok + .lk-pop__blok { padding-top: 0; border-top: 0; padding-left: 1rem; border-left: 1px solid var(--border); }
        @media (prefers-reduced-motion: reduce) { .lk-pop { transition: opacity .1s, visibility 0s .1s; transform: none; } }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $bisaEdit = auth()->user()->can('edit-data');
        $nadaStatus =['Ringan' => 'pl-badge-primary', 'Sedang' => 'pl-badge-notice', 'Berat' => 'pl-badge-accent'];
        $nadaPemulihan = ['Dalam perawatan' => 'pl-badge-accent', 'Isolasi mandiri' => 'pl-badge-notice', 'Sembuh' => 'pl-badge-primary'];
        // penanda setelah nama: [ikon, warna] untuk taruna yang sudah sembuh / sedang isolasi mandiri
        $penandaPemulihan = [
            'Sembuh' => ['M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'var(--link)'],
            'Isolasi mandiri' => ['m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25', 'var(--tertiary-500)'],
        ];
        // warna lencana di popup: warna tingkat sakit versi lebih gelap agar teks putih terbaca (kontras ≥ 5:1)
        $warnaLencana = ['Ringan' => '#2f7366', 'Sedang' => '#8f6212', 'Berat' => '#b4492a'];
        // warna tingkat sakit = warna Ringan/Sedang/Berat di dashboard
        $warnaStatus = ['Ringan' => '#6fa89d', 'Sedang' => '#c88a1e', 'Berat' => '#d8693f'];
        // [judul, lebar kolom (px desain / 16), rata tengah]
        $kolom = [
            ['Nama', 8, false], ['NPM', 5.25, true], ['Tingkat', 5.5, true],
            ['Keluhan', 14, false], ['Terapi', 15, false], ['Evaluasi kontrol kesehatan', 19, false], ['Kontrol selanjutnya', 9, true],
            ['Pegawai pemeriksa', 9, false], ['Tingkat sakit', 1.75, true],
        ];
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <h1 class="pl-page-title">Laporan Kesehatan</h1>
                <p class="pl-page-sub">{{ now()->locale('id')->translatedFormat('d F Y') }}</p>
            </div>
        </header>

        <div class="pl-toolbar">
            @include('partials.pilih-bagian', ['aktif' => 'perawat'])
            <label class="pl-search">
                <span class="pl-sr">Cari nama atau NPM taruna</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="cari" type="search" class="pl-input" placeholder="Cari nama atau NPM">
            </label>
            {{-- Filter pilihan ganda: dalam satu filter = salah satu cocok (ATAU), antarfilter = semua cocok (DAN) --}}
            @include('partials.saring', ['kunci' => 'status', 'label' => 'Status', 'opsi' => ['ringan' => 'Ringan', 'sedang' => 'Sedang', 'berat' => 'Berat'],
                'warna' => ['ringan' => $warnaStatus['Ringan'], 'sedang' => $warnaStatus['Sedang'], 'berat' => $warnaStatus['Berat']]])
            @include('partials.saring', ['kunci' => 'pemulihan', 'label' => 'Pemulihan', 'opsi' => ['dalam perawatan' => 'Dalam perawatan', 'isolasi mandiri' => 'Isolasi mandiri', 'sembuh' => 'Sembuh']])
            @include('partials.saring', ['kunci' => 'tingkat', 'label' => 'Tingkat', 'opsi' => ['I' => 'Tingkat I', 'II' => 'Tingkat II', 'III' => 'Tingkat III', 'IV' => 'Tingkat IV']])
            @include('partials.saring', ['kunci' => 'kontrol', 'label' => 'Kontrol', 'opsi' => ['terlambat' => 'Terlambat', 'hari-ini' => 'Hari ini', 'mendatang' => 'Akan datang', 'tanpa' => 'Tanpa jadwal']])
            <button type="button" id="reset-saring" class="pl-btn pl-btn-ghost lk-tombol-bar" disabled>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Reset filter
            </button>
            <div class="pl-toolbar__end">
                <a href="{{ route('laporan-kesehatan.ekspor') }}" class="pl-btn pl-btn-secondary lk-tombol-bar lk-tombol-ikon" aria-label="Ekspor laporan kesehatan">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                </a>
                @can('tambah-data')
                    <a href="{{ route('laporan-kesehatan.create') }}" class="pl-btn pl-btn-primary lk-tombol-bar">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah keluhan
                    </a>
                @endcan
            </div>
        </div>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status" style="flex-direction: row; align-items: center; gap: .75rem">
                <svg class="w-6 h-6 flex-none" style="color: var(--link)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="pl-table-wrap max-h-[70vh]">
            <table class="pl-table lk">
                <colgroup>
                    @foreach ($kolom as [, $lebar])
                        {{-- batas bawah px: huruf tabel minimal 14px, jadi kolom tidak boleh ikut menyusut di layar kecil (badge tidak terpotong) --}}
                        <col style="width: max({{ $lebar }}rem, {{ $lebar * 15 }}px)">
                    @endforeach
                </colgroup>
                <thead>
                    <tr>
                        @foreach ($kolom as [$judul, , $tengah])
                            {{-- urutan bawaan dari server: kontrol terdekat/terlambat di atas --}}
                            <th scope="col" @class(['c' => $tengah, 'lk-th-warna' => $judul === 'Tingkat sakit']) @if ($judul === 'Kontrol selanjutnya') aria-sort="ascending" @endif @if (in_array($judul, ['Keluhan', 'Terapi', 'Evaluasi kontrol kesehatan'])) data-tanpa-urut @endif>@if ($judul === 'Tingkat sakit')<span class="sr-only">{{ $judul }}</span>@else{{ $judul }}@endif</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="isi-tabel">
                    @forelse ($laporan->groupBy('taruna_id')->flatten(1) as $r) {{-- keluhan taruna yang sama berdampingan agar kolom namanya bisa digabung --}}
                        @php
                            $evaluasi = $r->riwayatKontrol->first()->hasil_kontrol ?? '-';
                            // ponytail: taruna yang sudah sembuh tidak punya jadwal kontrol lagi
                            $kontrol = $r->status_pemulihan !== 'Sembuh' ? $r->tanggal_kontrol_selanjutnya : null;
                            $hariTelat = $kontrol?->lt(today()) ? (int) $kontrol->diffInDays(today()) : 0;
                            // Data popup ringkas (diisi skrip saat baris di-hover); aksi kosong = tombol nonaktif beserta alasannya
                            $alasan = $r->status_pemulihan === 'Sembuh' ? 'Sudah sembuh' : ($bisaEdit ? null : 'Tidak punya akses Edit');
                            $info = [
                                'nama' => $r->taruna->nama, 'npm' => $r->taruna->npm, 'tingkat' => $r->taruna->tingkat,
                                'awal' => $r->tanggal_awal->format('d/m/Y'), 'kelas' => $r->taruna->kelas, 'kamar' => $r->taruna->kamar, 'kelamin' => $r->taruna->jenis_kelamin,
                                'penanda' => $penandaPemulihan[$r->status_pemulihan] ?? null,
                                'status' => $r->status, 'warnaStatus' => $warnaLencana[$r->status] ?? '#55584f',
                                'pemulihan' => $r->status_pemulihan,
                                'keluhan' => $r->keluhan, 'terapi' => str_replace("\n", '; ', trim($r->terapi)), 'evaluasi' => $evaluasi,
                                'kontrol' => $kontrol?->format('d/m/Y') ?? '-',
                                'telat' => $hariTelat > 0 ? "Terlambat {$hariTelat} hari" : ($kontrol?->isToday() ? 'Hari ini' : null),
                                'sembuh' => $alasan ? null : route('laporan-kesehatan.toggle-sembuh', $r),
                                'edit' => $alasan ? null : route('laporan-kesehatan.show', $r).'#catat-kontrol', // popup Catat Kontrol di halaman detail
                                'alasan' => $alasan,
                            ];
                        @endphp
                        <tr data-info="{{ json_encode($info) }}" data-href="{{ route('laporan-kesehatan.show', $r) }}" data-taruna="{{ $r->taruna_id }}" data-nama="{{ strtolower($r->taruna->nama) }}" data-npm="{{ $r->taruna->npm }}"
                            data-status="{{ strtolower($r->status) }}" data-pemulihan="{{ strtolower($r->status_pemulihan) }}" data-tingkat="{{ $r->taruna->tingkat }}"
                            data-kontrol="{{ $hariTelat > 0 ? 'terlambat' : (! $kontrol ? 'tanpa' : ($kontrol->isToday() ? 'hari-ini' : 'mendatang')) }}" @class(['is-telat' => $hariTelat > 0])>
                            <td class="font-medium lk-nama">
                                <div class="flex items-center gap-1.5">
                                    <span class="truncate">{{ $r->taruna->nama }}</span>
                                    <svg class="lk-telat-ikon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Kontrol terlambat"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    @if (isset($penandaPemulihan[$r->status_pemulihan]))
                                        @php [$ikonPemulihan, $warnaPemulihan] = $penandaPemulihan[$r->status_pemulihan]; @endphp
                                        <svg class="lk-penanda" style="color: {{ $warnaPemulihan }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="{{ $r->status_pemulihan }}"><path d="{{ $ikonPemulihan }}"/></svg>
                                    @endif
                                </div>
                            </td>
                            <td class="c tabular-nums">{{ $r->taruna->npm }}</td>
                            <td class="c">{{ $r->taruna->tingkat }}</td>
                            {{-- penanda per keluhan: tampil di sini bila sel nama tergabung (penanda sel nama hanya mewakili baris pertama) --}}
                            <td class="lk-bungkus"><div class="lk-clamp">
                                @if (isset($penandaPemulihan[$r->status_pemulihan]))
                                    <svg class="lk-penanda lk-penanda-baris" style="color: {{ $penandaPemulihan[$r->status_pemulihan][1] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="{{ $r->status_pemulihan }}"><path d="{{ $penandaPemulihan[$r->status_pemulihan][0] }}"/></svg>
                                @endif
                                {{ $r->keluhan }}
                            </div></td>
                            <td class="lk-bungkus"><div class="lk-clamp">{{ str_replace("\n", '; ', trim($r->terapi)) }}</div></td>
                            <td class="lk-bungkus"><div class="lk-clamp">{{ $evaluasi }}</div></td>
                            {{-- data-urut: kunci urut tanggal (sel juga berisi badge) --}}
                            <td class="c tabular-nums lk-kontrol" data-urut="{{ $kontrol?->format('Ymd') }}">
                                <span>{{ $kontrol?->format('d/m/Y') ?? '-' }}</span>
                                @if ($hariTelat > 0)
                                    <span class="pl-badge pl-badge-accent pl-badge--ikon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>Terlambat {{ $hariTelat }} hari</span>
                                @elseif ($kontrol?->isToday())
                                    <span class="pl-badge pl-badge-notice pl-badge--ikon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>Hari ini</span>
                                @endif
                            </td>
                            <td class="lk-bungkus"><div class="lk-clamp">{{ $r->pembuat?->name ?? '-' }}</div></td>
                            <td class="lk-warna" data-urut="{{ array_search($r->status, array_keys($warnaStatus)) + 1 }}" style="--w: {{ $warnaStatus[$r->status] ?? 'var(--neutral-400)' }}"><span class="sr-only">Tingkat {{ $r->status }}</span></td>
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
    </main>

    {{-- Popup ringkas (satu elemen dipakai bersama): hover sel nama = semua keluhan taruna itu, hover baris lain = keluhan baris itu saja --}}
    <div id="lk-pop" class="lk-pop" role="dialog" aria-label="Ringkasan keluhan taruna" aria-hidden="true">
        <div class="lk-pop__kepala">
            <div>
                <span class="flex items-center gap-1.5"><b data-isi="nama"></b><svg class="lk-penanda" data-penanda fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" style="display: none"><path d=""/></svg></span>
                <small data-isi="jumlah"></small>
            </div>
        </div>
        <dl class="lk-pop__identitas">
            <div><dt>NPM</dt><dd data-isi="npm"></dd></div>
            <div><dt>Tingkat</dt><dd data-isi="tingkat"></dd></div>
            <div><dt>Kelas</dt><dd data-isi="kelas"></dd></div>
            <div><dt>Kamar</dt><dd data-isi="kamar"></dd></div>
            <div style="grid-column: 1 / -1"><dt>Jenis kelamin</dt><dd data-isi="kelamin"></dd></div>
        </dl>
        <div class="lk-pop__daftar" id="lk-pop-daftar"></div>
    </div>

    <template id="lk-pop-blok">
        <section class="lk-pop__blok">
            <div class="lk-pop__blokkepala">
                <span class="pl-badge" data-isi="status"></span>
                <small data-isi="pemulihan"></small>
            </div>
            <dl class="lk-pop__isi">
                <div><dt>Awal keluhan</dt><dd data-isi="awal"></dd></div>
                <div><dt>Keluhan</dt><dd data-isi="keluhan"></dd></div>
                <div><dt>Terapi</dt><dd data-isi="terapi"></dd></div>
                <div><dt>Evaluasi kontrol</dt><dd data-isi="evaluasi"></dd></div>
                <div><dt>Kontrol selanjutnya</dt><dd><span data-isi="kontrol"></span><span class="pl-badge" data-isi="telat"></span></dd></div>
            </dl>
            <div class="lk-pop__aksi">
                {{-- Sembuh bersifat final: lewat dialog konfirmasi, formulirnya satu untuk semua blok --}}
                <button type="button" class="pl-btn pl-btn-primary" data-aksi="sembuh">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                    Tandai sembuh
                </button>
                <a class="pl-btn pl-btn-secondary" data-aksi="edit">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                    Catat kontrol
                </a>
            </div>
        </section>
    </template>


    <form method="POST" id="lk-form-sembuh" hidden>
        @csrf
        @method('PATCH')
    </form>

    @include('partials.konfirmasi', [
        'id' => 'konfirmasi-sembuh', 'nada' => 'primary', 'ikon' => 'selesai', 'form' => 'lk-form-sembuh',
        'judul' => 'Tandai taruna sembuh?',
        'pesan' => 'Status pemulihan menjadi Sembuh dan tidak bisa dikembalikan.',
        'tombol' => 'Ya, tandai sembuh',
    ])

    <script>
        // Popup ringkas: muncul setelah kursor diam sebentar. Di sel nama = semua keluhan taruna itu (yang tampil),
        // di sel lain = keluhan baris itu saja. Mengikuti kursor saat pindah, dan tetap terbuka saat kursor masuk ke popup (tombolnya bisa ditekan)
        (() => {
            const pop = document.getElementById('lk-pop'), isi = document.getElementById('isi-tabel');
            const daftar = document.getElementById('lk-pop-daftar'), blok = document.getElementById('lk-pop-blok');
            const formSembuh = document.getElementById('lk-form-sembuh'), dialogSembuh = document.getElementById('konfirmasi-sembuh');
            const IKON_KALENDER = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>';
            const IKON_JAM = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>';
            let aktif = null, tundaBuka = null, tundaTutup = null, x = 0;

            const isiBlok = (el, d) => {
                el.querySelectorAll('[data-isi]').forEach((e) => { e.textContent = d[e.dataset.isi] ?? '-'; });
                el.querySelector('[data-isi="status"]').style.setProperty('--w', d.warnaStatus);
                // badge sama dengan di tabel: ikon (jam = terlambat, kalender = hari ini), tanpa titik
                const telat = el.querySelector('[data-isi="telat"]');
                const terlambat = Boolean(d.telat) && d.telat !== 'Hari ini';
                telat.hidden = !d.telat;
                telat.className = 'pl-badge pl-badge--ikon ' + (terlambat ? 'pl-badge-accent' : 'pl-badge-notice');
                if (d.telat) { telat.insertAdjacentHTML('afterbegin', terlambat ? IKON_JAM : IKON_KALENDER); }
                const tombolSembuh = el.querySelector('button[data-aksi="sembuh"]'), tautanEdit = el.querySelector('a[data-aksi="edit"]');
                tombolSembuh.disabled = !d.sembuh;
                tombolSembuh.addEventListener('click', () => {
                    formSembuh.action = d.sembuh;
                    document.getElementById('konfirmasi-sembuh-pesan').textContent =
                        `Status pemulihan ${d.nama} (${d.npm}) menjadi Sembuh dan tidak bisa dikembalikan.`;
                    dialogSembuh.showModal();
                });
                if (d.edit) { tautanEdit.href = d.edit; }
                // aksi tidak tersedia: tombol tetap tampil tapi nonaktif, alasannya di tooltip
                tautanEdit.toggleAttribute('aria-disabled', !d.edit);
                tautanEdit.style.pointerEvents = d.edit ? '' : 'none';
                tautanEdit.style.opacity = d.edit ? '' : '.5';
                [tombolSembuh, tautanEdit].forEach((e) => { e.title = d.alasan ?? ''; });
            };
            const isiPopup = (infos) => {
                const d = infos[0];
                pop.querySelectorAll(':scope > .lk-pop__kepala [data-isi], :scope > .lk-pop__identitas [data-isi]').forEach((e) => { e.textContent = d[e.dataset.isi] ?? '-'; });
                pop.querySelector('[data-isi="jumlah"]').textContent = infos.length > 1 ? `${infos.length} keluhan` : '';
                // penanda setelah nama (sembuh / isolasi mandiri): hanya bila semua keluhan yang ditampilkan berstatus pemulihan sama
                const penanda = pop.querySelector('[data-penanda]');
                const sama = d.penanda && infos.every((i) => i.pemulihan === d.pemulihan);
                penanda.style.display = sama ? '' : 'none';
                if (sama) {
                    penanda.querySelector('path').setAttribute('d', d.penanda[0]);
                    penanda.setAttribute('aria-label', d.pemulihan);
                    penanda.style.color = d.penanda[1];
                }
                // lebih dari satu keluhan (dari sel nama): blok sama, berjajar ke samping, tanpa tombol sembuh / edit
                pop.classList.toggle('lk-pop--samping', infos.length > 1);
                daftar.replaceChildren(...infos.map((i) => {
                    const el = blok.content.cloneNode(true).firstElementChild;
                    isiBlok(el, i);
                    if (infos.length > 1) { el.querySelector('.lk-pop__aksi').remove(); }
                    return el;
                }));
            };
            // Di kanan kursor, sejajar atas baris; pindah ke kiri / atas bila tidak muat di layar
            const posisikan = (tr) => {
                const r = tr.getBoundingClientRect(), w = pop.offsetWidth, h = pop.offsetHeight;
                const kiri = x + 20 + w > innerWidth - 8 ? x - 20 - w : x + 20;
                const atas = Math.min(Math.max(8, r.top), Math.max(8, innerHeight - h - 8));
                pop.style.left = `${Math.max(8, kiri)}px`;
                pop.style.top = `${atas}px`;
            };
            const buka = (tr, semua) => {
                aktif = { tr, semua };
                const baris = semua ? [...isi.querySelectorAll(`tr[data-taruna="${tr.dataset.taruna}"]:not([hidden])`)] : [tr];
                isiPopup(baris.map((b) => JSON.parse(b.dataset.info)));
                pop.scrollTop = 0;
                posisikan(tr);
                pop.offsetWidth; // terapkan posisi dulu, baru kelas .on (agar tidak meluncur dari pojok)
                pop.classList.add('on');
                pop.setAttribute('aria-hidden', 'false');
            };
            const tutup = () => {
                clearTimeout(tundaBuka);
                aktif = null;
                pop.classList.remove('on');
                pop.setAttribute('aria-hidden', 'true');
            };
            const tutupNanti = () => { clearTimeout(tundaBuka); clearTimeout(tundaTutup); tundaTutup = setTimeout(tutup, 220); };

            isi.addEventListener('mousemove', (e) => {
                x = e.clientX;
                const tr = e.target.closest('tr[data-info]');
                if (!tr) { tutupNanti(); return; }
                clearTimeout(tundaTutup);
                const semua = Boolean(e.target.closest('td.lk-nama'));
                if (aktif && tr === aktif.tr && semua === aktif.semua) { return; }
                clearTimeout(tundaBuka);
                // sudah terbuka: pindah baris sedikit lebih lambat, supaya kursor sempat menuju popup tanpa berganti baris
                tundaBuka = setTimeout(() => buka(tr, semua), pop.classList.contains('on') ? 260 : 450);
            });
            isi.addEventListener('mouseleave', tutupNanti);
            pop.addEventListener('mouseenter', () => { clearTimeout(tundaTutup); clearTimeout(tundaBuka); });
            pop.addEventListener('mouseleave', tutupNanti);
            addEventListener('scroll', (e) => { if (!pop.contains(e.target)) { tutup(); } }, true); // gulir halaman / tabel menutup; gulir di dalam popup tidak
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { tutup(); } });
        })();
    </script>

    <script>
        // Ujung melengkung: kolom warna = sel dari baris tampil terakhir (kanan bawah)
        function bulatkanWarna() {
            const tampil = [...document.querySelectorAll('#isi-tabel tr[data-status]:not([hidden])')];
            document.querySelectorAll('#isi-tabel td.lk-warna').forEach((td) => td.classList.remove('lk-bawah'));
            gabungNama();
            [...document.querySelectorAll('#isi-tabel tr[data-status]:not([hidden]) td.lk-warna')].pop()?.classList.add('lk-bawah');
        }
        bulatkanWarna();

        // Keluhan berurutan milik taruna yang sama: sel nama digabung (rowspan); ikon terlambat tampil di sel nama bila ada keluhan terlambat di kelompoknya; dihitung ulang tiap filter / urut
        function gabungNama() {
            document.querySelectorAll('#isi-tabel td.lk-nama').forEach((td) => { td.hidden = false; td.removeAttribute('rowspan'); td.classList.remove('lk-telat', 'lk-gabung'); });
            const tampil = [...document.querySelectorAll('#isi-tabel tr[data-taruna]:not([hidden])')];
            for (let i = 0, j; i < tampil.length; i = j) {
                for (j = i + 1; j < tampil.length && tampil[j].dataset.taruna === tampil[i].dataset.taruna; j++) {
                    tampil[j].querySelector('.lk-nama').hidden = true;
                }
                if (j - i > 1) { tampil[i].querySelector('.lk-nama').rowSpan = j - i; tampil[i].querySelector('.lk-nama').classList.add('lk-gabung'); }
                tampil[i].querySelector('.lk-nama').classList.toggle('lk-telat', tampil.slice(i, j).some((tr) => tr.classList.contains('is-telat')));
            }
        }
        gabungNama();

        // Sel nama gabungan ikut tersorot saat baris keluhan ke-2 dst. di-hover (sel itu milik baris pertama kelompok, jadi hover bawaan tidak menjangkaunya)
        (() => {
            const isi = document.getElementById('isi-tabel');
            const bersihkan = () => isi.querySelectorAll('.lk-nama.lk-hover').forEach((td) => td.classList.remove('lk-hover'));
            isi.addEventListener('mouseover', (e) => {
                bersihkan();
                let tr = e.target.closest('tr[data-taruna]');
                while (tr && tr.querySelector('.lk-nama').hidden) { tr = tr.previousElementSibling; }
                if (tr && tr !== e.target.closest('tr')) { tr.querySelector('.lk-nama').classList.add('lk-hover'); }
            });
            isi.addEventListener('mouseleave', bersihkan);
        })();

        // Cari nama / NPM + filter pilihan ganda (partials.saring): saring baris tabel di sisi browser.
        // Dalam satu filter cukup salah satu nilai cocok; antarfilter harus cocok semua. Reset = kosongkan semua + urutan bawaan.
        (() => {
            const baris = [...document.querySelectorAll('#isi-tabel tr[data-nama]')];
            const kosong = document.getElementById('tidak-ditemukan');
            const cari = document.getElementById('cari'), reset = document.getElementById('reset-saring');
            const filter = [...document.querySelectorAll('[data-saring]')];
            const tabel = document.querySelector('.lk');
            const dipilih = (s) => [...s.querySelectorAll('input:checked')].map((i) => i.value);

            // jumlah baris per opsi (dari seluruh data, bukan yang tersaring)
            filter.forEach((s) => s.querySelectorAll('[data-jumlah]').forEach((el) => {
                el.textContent = baris.filter((tr) => tr.dataset[s.dataset.saring] === el.dataset.jumlah).length;
            }));

            const saring = () => {
                const q = cari.value.trim().toLowerCase();
                const aturan = filter.map((s) => [s.dataset.saring, dipilih(s)]).filter(([, nilai]) => nilai.length);
                let ada = 0;
                baris.forEach((tr) => {
                    const cocok = (tr.dataset.nama.includes(q) || tr.dataset.npm.includes(q))
                        && aturan.every(([kunci, nilai]) => nilai.includes(tr.dataset[kunci]));
                    tr.hidden = !cocok;
                    if (cocok) ada++;
                });
                kosong.hidden = ada > 0 || baris.length === 0;
                reset.disabled = !q && !aturan.length && !tabel.classList.contains('lk-urut-ubah');
                bulatkanWarna();
            };
            cari.addEventListener('input', saring);
            filter.forEach((s) => s.addEventListener('change', saring));
            tabel.addEventListener('urut:ubah', saring); // urutan diubah pengguna → tombol reset aktif
            reset.addEventListener('click', () => {
                cari.value = '';
                filter.forEach((s) => { s.querySelectorAll('input:checked').forEach((i) => { i.checked = false; }); s.ringkas?.(); });
                tabel.dispatchEvent(new Event('urut:reset'));
                saring();
                cari.focus();
            });
        })();
    </script>

    @include('laporan-kesehatan.partials.urut-tabel')
</body>
</html>
