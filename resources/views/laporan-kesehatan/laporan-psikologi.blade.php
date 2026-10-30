{{-- laporan-kesehatan/laporan-psikologi.blade.php — Laporan Kesehatan bagian psikolog; desain tabel & halaman disamakan dengan laporan-kesehatan.blade.php --}}
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
        .lk-tombol-ikon { width: 2.75rem; padding: 0; justify-content: center; }
        .lk td.lk-nama { border-right: 1px solid var(--border); } /* sekat antara kolom nama dan kolom lain */
        .lk-penanda { width: 1.125rem; height: 1.125rem; flex: none; }
        .lk-penanda-baris { display: none; margin-right: .25rem; vertical-align: -.2em; }
        .lk td.lk-nama.lk-gabung .lk-penanda { display: none; } /* sel nama tergabung: penanda per keluhan pindah ke sel keluhan */
        .lk tr:has(> td.lk-nama.lk-gabung) .lk-penanda-baris, .lk tr:has(> td.lk-nama[hidden]) .lk-penanda-baris { display: inline-block; }
        .pl-badge--ikon::before { display: none; }
        .pl-badge--ikon svg { width: .875rem; height: .875rem; flex: none; color: var(--ring); }
.lk td.lk-nama.lk-hover { background: color-mix(in srgb, var(--cloud) 45%, transparent); }
        .lk td.lk-nama.lk-telat { box-shadow: inset 3px 0 0 var(--strong); } /* penanda terlambat = garis hijau aplikasi setinggi seluruh sel nama (juga sel gabungan, walau berisi keluhan yang tidak terlambat) */
        .lk td.lk-nama.lk-telat.lk-telat-akhir { border-bottom-left-radius: .5rem; } /* ujung kiri bawah penanda melingkar */
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
        // penanda setelah nama: [ikon, warna] untuk taruna yang sudah selesai konseling
        $penandaSelesai = ['M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'var(--link)'];
        // warna blok kolom warna = warna Masih / Selesai konseling di dashboard; lencana popup = versi lebih gelap agar teks putih terbaca (kontras ≥ 5:1)
        $warnaStatus = ['Melanjutkan konseling' => '#c88a1e', 'Tidak melanjutkan konseling' => '#6fa89d'];
        $warnaLencana = ['Melanjutkan konseling' => '#8f6212', 'Tidak melanjutkan konseling' => '#2f7366'];
        // [judul, lebar kolom (px desain / 16), rata tengah]
        $kolom = [
            ['Nama', 8, false], ['NPM', 5.25, true], ['Tingkat', 5.5, true],
            ['Keluhan', 14, false], ['Terapi', 15, false], ['Evaluasi konseling', 19, false], ['Konseling selanjutnya', 9, true],
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
            @include('partials.pilih-bagian', ['aktif' => 'psikolog'])
            <label class="pl-search">
                <span class="pl-sr">Cari nama atau NPM taruna</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="cari" type="search" class="pl-input" placeholder="Cari nama atau NPM">
            </label>
            {{-- Filter pilihan ganda: dalam satu filter = salah satu cocok (ATAU), antarfilter = semua cocok (DAN) --}}
            @include('partials.saring', ['kunci' => 'status', 'label' => 'Status', 'opsi' => ['melanjutkan' => 'Melanjutkan konseling', 'selesai' => 'Tidak melanjutkan konseling'],
                'warna' => ['melanjutkan' => $warnaStatus['Melanjutkan konseling'], 'selesai' => $warnaStatus['Tidak melanjutkan konseling']]])
            @include('partials.saring', ['kunci' => 'tingkat', 'label' => 'Tingkat', 'opsi' => ['I' => 'Tingkat I', 'II' => 'Tingkat II', 'III' => 'Tingkat III', 'IV' => 'Tingkat IV']])
            @include('partials.saring', ['kunci' => 'kontrol', 'label' => 'Konseling', 'opsi' => ['terlambat' => 'Terlambat', 'hari-ini' => 'Hari ini', 'mendatang' => 'Akan datang', 'tanpa' => 'Tanpa jadwal']])
            <button type="button" id="reset-saring" class="pl-btn pl-btn-ghost lk-tombol-bar" disabled>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Reset filter
            </button>
            <div class="pl-toolbar__end">
                <a href="{{ route('laporan-kesehatan.ekspor') }}" class="pl-btn pl-btn-secondary lk-tombol-bar lk-tombol-ikon" aria-label="Ekspor laporan kesehatan">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                </a>
                @can('tambah-data')
                    <a href="{{ route('laporan-kesehatan.psikologi.create') }}" class="pl-btn pl-btn-primary lk-tombol-bar lk-tombol-ikon" aria-label="Tambahkan keluhan baru">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
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
                            {{-- urutan bawaan dari server: yang masih melanjutkan konseling di atas --}}
                            <th scope="col" @class(['c' => $tengah]) @if (in_array($judul, ['Keluhan', 'Terapi', 'Evaluasi konseling'])) data-tanpa-urut @endif>{{ $judul }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="isi-tabel">
                    @forelse ($laporan->groupBy('taruna_id')->flatten(1) as $r) {{-- keluhan taruna yang sama berdampingan agar kolom namanya bisa digabung --}}
                        @php
                            $evaluasi = $r->konselingTerakhir->hasil_konseling ?? '-';
                            $label = $r->keterangan();
                            // ponytail: yang sudah tidak melanjutkan konseling tidak punya jadwal konseling lagi
                            $jadwal = $r->lanjut_konseling ? $r->tanggal_konseling_selanjutnya : null;
                            $hariTelat = $jadwal?->lt(today()) ? (int) $jadwal->diffInDays(today()) : 0;
                            $info = [
                                'nama' => $r->taruna->nama, 'npm' => $r->taruna->npm, 'tingkat' => $r->taruna->tingkat,
                                'awal' => $r->tanggal_awal->format('d/m/Y'), 'kelas' => $r->taruna->kelas, 'kamar' => $r->taruna->kamar, 'kelamin' => $r->taruna->jenis_kelamin,
                                'penanda' => $r->lanjut_konseling ? null : $penandaSelesai,
                                'status' => $label, 'warnaStatus' => $warnaLencana[$label], 'pemulihan' => $label,
                                'keluhan' => $r->keluhan, 'terapi' => $r->terapi, 'evaluasi' => $evaluasi,
                                'kontrol' => $jadwal?->format('d/m/Y') ?? '-',
                                'telat' => $hariTelat > 0 ? "Terlambat {$hariTelat} hari" : ($jadwal?->isToday() ? 'Hari ini' : null),
                                'edit' => $bisaEdit ? route('laporan-kesehatan.psikologi.edit', $r) : null,
                                'alasan' => $bisaEdit ? null : 'Tidak punya akses Edit',
                            ];
                        @endphp
                        <tr data-info="{{ json_encode($info) }}" data-href="{{ route('laporan-kesehatan.psikologi.show', $r) }}" data-taruna="{{ $r->taruna_id }}" data-nama="{{ strtolower($r->taruna->nama) }}" data-npm="{{ $r->taruna->npm }}"
                            data-status="{{ $r->lanjut_konseling ? 'melanjutkan' : 'selesai' }}" data-tingkat="{{ $r->taruna->tingkat }}"
                            data-kontrol="{{ $hariTelat > 0 ? 'terlambat' : (! $jadwal ? 'tanpa' : ($jadwal->isToday() ? 'hari-ini' : 'mendatang')) }}" @class(['is-telat' => $hariTelat > 0])>
                            <td class="font-medium lk-nama">
                                <div class="flex items-center gap-1.5">
                                    <span class="truncate">{{ $r->taruna->nama }}</span>
                                    @unless ($r->lanjut_konseling)
                                        <svg class="lk-penanda" style="color: {{ $penandaSelesai[1] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="{{ $label }}"><path d="{{ $penandaSelesai[0] }}"/></svg>
                                    @endunless
                                </div>
                            </td>
                            <td class="c tabular-nums">{{ $r->taruna->npm }}</td>
                            <td class="c">{{ $r->taruna->tingkat }}</td>
                            {{-- penanda per keluhan: tampil di sini bila sel nama tergabung (penanda sel nama hanya mewakili baris pertama) --}}
                            <td class="lk-bungkus"><div class="lk-clamp">
                                @unless ($r->lanjut_konseling)
                                    <svg class="lk-penanda lk-penanda-baris" style="color: {{ $penandaSelesai[1] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="{{ $label }}"><path d="{{ $penandaSelesai[0] }}"/></svg>
                                @endunless
                                {{ $r->keluhan }}
                            </div></td>
                            <td class="lk-bungkus"><div class="lk-clamp">{{ $r->terapi }}</div></td>
                            <td class="lk-bungkus"><div class="lk-clamp">{{ $evaluasi }}</div></td>
                            {{-- data-urut: kunci urut tanggal (sel juga berisi badge) --}}
                            <td class="c tabular-nums lk-kontrol" data-urut="{{ $jadwal?->format('Ymd') }}">
                                <span>{{ $jadwal?->format('d/m/Y') ?? '-' }}</span>
                                @if ($hariTelat > 0)
                                    <span class="pl-badge pl-badge-accent pl-badge--ikon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>Terlambat {{ $hariTelat }} hari</span>
                                @elseif ($jadwal?->isToday())
                                    <span class="pl-badge pl-badge-notice">Hari ini</span>
                                @endif
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
                            </div>
            <dl class="lk-pop__isi">
                <div><dt>Awal keluhan</dt><dd data-isi="awal"></dd></div>
                <div><dt>Keluhan</dt><dd data-isi="keluhan"></dd></div>
                <div><dt>Terapi</dt><dd data-isi="terapi"></dd></div>
                <div><dt>Evaluasi konseling</dt><dd data-isi="evaluasi"></dd></div>
                <div><dt>Konseling selanjutnya</dt><dd><span data-isi="kontrol"></span><span class="pl-badge" data-isi="telat"></span></dd></div>
            </dl>
            <div class="lk-pop__aksi">
                <a class="pl-btn pl-btn-secondary" data-aksi="edit">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                    Edit keluhan
                </a>
            </div>
        </section>
    </template>


    <script>
        // Popup ringkas: muncul setelah kursor diam sebentar. Di sel nama = semua keluhan taruna itu (yang tampil),
        // di sel lain = keluhan baris itu saja. Mengikuti kursor saat pindah, dan tetap terbuka saat kursor masuk ke popup (tombolnya bisa ditekan)
        (() => {
            const pop = document.getElementById('lk-pop'), isi = document.getElementById('isi-tabel');
            const daftar = document.getElementById('lk-pop-daftar'), blok = document.getElementById('lk-pop-blok');

            const IKON_JAM = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>';
            let aktif = null, tundaBuka = null, tundaTutup = null, x = 0;

            const isiBlok = (el, d) => {
                el.querySelectorAll('[data-isi]').forEach((e) => { e.textContent = d[e.dataset.isi] ?? '-'; });
                el.querySelector('[data-isi="status"]').style.setProperty('--w', d.warnaStatus);
                // badge terlambat sama dengan di tabel: ikon jam, tanpa titik; "Hari ini" tetap badge biasa
                const telat = el.querySelector('[data-isi="telat"]');
                const terlambat = Boolean(d.telat) && d.telat !== 'Hari ini';
                telat.hidden = !d.telat;
                telat.className = 'pl-badge ' + (terlambat ? 'pl-badge-accent pl-badge--ikon' : 'pl-badge-notice');
                if (terlambat) { telat.insertAdjacentHTML('afterbegin', IKON_JAM); }
                const tautanEdit = el.querySelector('a[data-aksi="edit"]');
                if (d.edit) { tautanEdit.href = d.edit; }
                // aksi tidak tersedia: tombol tetap tampil tapi nonaktif, alasannya di tooltip
                tautanEdit.toggleAttribute('aria-disabled', !d.edit);
                tautanEdit.style.pointerEvents = d.edit ? '' : 'none';
                tautanEdit.style.opacity = d.edit ? '' : '.5';
                tautanEdit.title = d.alasan ?? '';
            };
            const isiPopup = (infos) => {
                const d = infos[0];
                pop.querySelectorAll(':scope > .lk-pop__kepala [data-isi], :scope > .lk-pop__identitas [data-isi]').forEach((e) => { e.textContent = d[e.dataset.isi] ?? '-'; });
                pop.querySelector('[data-isi="jumlah"]').textContent = infos.length > 1 ? `${infos.length} keluhan` : '';
                // penanda setelah nama (selesai konseling): hanya bila semua keluhan yang ditampilkan berstatus sama
                const penanda = pop.querySelector('[data-penanda]');
                const sama = d.penanda && infos.every((i) => i.pemulihan === d.pemulihan);
                penanda.style.display = sama ? '' : 'none';
                if (sama) {
                    penanda.querySelector('path').setAttribute('d', d.penanda[0]);
                    penanda.setAttribute('aria-label', d.pemulihan);
                    penanda.style.color = d.penanda[1];
                }
                // lebih dari satu keluhan (dari sel nama): blok sama, berjajar ke samping, tanpa tombol edit
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
        // Ujung melengkung: kolom warna = sel dari baris tampil terakhir (kanan bawah); penanda terlambat = hanya bila baris terlambat itu baris tampil terakhir (kiri bawah)
        function bulatkanWarna() {
            const tampil = [...document.querySelectorAll('#isi-tabel tr[data-status]:not([hidden])')];
            document.querySelectorAll('#isi-tabel td.lk-warna').forEach((td) => td.classList.remove('lk-bawah'));
            gabungNama();
            // ujung kiri bawah penanda: sel nama (yang tampil) milik baris tampil terakhir; baris yang namanya tergabung memakai sel gabungan di atasnya
            document.querySelectorAll('#isi-tabel td.lk-nama').forEach((td) => td.classList.remove('lk-telat-akhir'));
            const akhir = tampil.at(-1), sel = akhir && (akhir.querySelector('.lk-nama:not([hidden])') ?? tampil.slice(0, -1).reverse().map((tr) => tr.querySelector('.lk-nama:not([hidden])')).find(Boolean));
            sel?.classList.add('lk-telat-akhir');
            [...document.querySelectorAll('#isi-tabel tr[data-status]:not([hidden]) td.lk-warna')].pop()?.classList.add('lk-bawah');
        }
        bulatkanWarna();

        // Keluhan berurutan milik taruna yang sama: sel nama digabung (rowspan); penanda terlambat (lk-telat) dipasang di sel nama bila ada keluhan terlambat di kelompoknya, setinggi seluruh sel; dihitung ulang tiap filter / urut
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
