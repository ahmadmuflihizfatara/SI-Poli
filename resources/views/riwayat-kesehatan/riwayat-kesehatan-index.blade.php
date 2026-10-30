{{-- resources/views/riwayat-kesehatan/index.blade.php --}}
@php
    // ======================================================================
    // DATA DUMMY — ganti dengan data dari controller, contoh:
    // return view('riwayat-kesehatan.index', ['riwayatTaruna' => $riwayatTaruna]);
    //
    // PENTING: setiap taruna punya field 'riwayat' berupa ARRAY — satu taruna
    // bisa memiliki LEBIH DARI SATU episode sakit selama menjadi taruna.
    // Setiap episode punya keluhan, terapi, riwayat kontrol, dan catatannya
    // sendiri-sendiri (lihat Ahmad Fauzan & Bagas Pratama & Putri Ayu di bawah).
    // ======================================================================
    $riwayatTaruna = $riwayatTaruna ?? [
        [
            'id' => 1, 'nama' => 'Ahmad Fauzan', 'npm' => '123220011', 'kamar' => 'A-12', 'tingkat' => 'Tingkat II',
            'riwayat' => [
                [
                    'tanggal_lapor' => '2026-08-05', 'status' => 'Ringan',
                    'keluhan_awal' => 'Nyeri perut ringan dan kembung setelah makan di kantin.',
                    'terapi' => 'Antasida 1 tablet, istirahat 30 menit di poliklinik.',
                    'kontrol' => [
                        ['tanggal' => '5 Agu 2026', 'hasil' => 'Nyeri berkurang setelah obat', 'keterangan' => 'Diperbolehkan kembali ke asrama'],
                    ],
                    'catatan' => 'Tidak ada pantangan khusus.',
                ],
                [
                    'tanggal_lapor' => '2026-09-20', 'status' => 'Sedang',
                    'keluhan_awal' => 'Demam tinggi (38.5°C) disertai batuk kering dan nyeri tenggorokan sejak 3 hari terakhir. Nafsu makan menurun.',
                    'terapi' => 'Paracetamol 3x500mg, Vitamin C 1x1000mg, istirahat cukup, dan banyak minum air putih.',
                    'kontrol' => [
                        ['tanggal' => '20 Sep 2026', 'hasil' => 'Suhu 38.5°C, kondisi lemas', 'keterangan' => 'Diberikan obat penurun panas, disarankan istirahat'],
                        ['tanggal' => '22 Sep 2026', 'hasil' => 'Suhu 37.2°C, kondisi membaik', 'keterangan' => 'Lanjutkan terapi, kontrol kembali 2 hari lagi'],
                        ['tanggal' => '24 Sep 2026', 'hasil' => 'Suhu normal 36.5°C, stabil', 'keterangan' => 'Dinyatakan sembuh, boleh beraktivitas normal'],
                    ],
                    'catatan' => 'Disarankan isolasi mandiri hingga 23 September 2026. Jadwal kontrol ulang: 26 September 2026 di Poliklinik.',
                ],
            ],
        ],
        [
            'id' => 2, 'nama' => 'Siti Nurhaliza', 'npm' => '123220045', 'kamar' => 'B-04', 'tingkat' => 'Tingkat I',
            'riwayat' => [
                [
                    'tanggal_lapor' => '2026-09-25', 'status' => 'Ringan',
                    'keluhan_awal' => 'Pusing dan sedikit mual setelah kegiatan fisik pagi hari. Tidak ada demam.',
                    'terapi' => 'Antasida 1 tablet, istirahat 1 jam di ruang poliklinik, observasi tekanan darah.',
                    'kontrol' => [
                        ['tanggal' => '25 Sep 2026', 'hasil' => 'Tekanan darah normal, kondisi stabil', 'keterangan' => 'Diperbolehkan kembali ke asrama'],
                    ],
                    'catatan' => 'Tidak ada pantangan khusus. Disarankan sarapan sebelum kegiatan fisik pagi.',
                ],
            ],
        ],
        [
            'id' => 3, 'nama' => 'Bagas Pratama', 'npm' => '123220078', 'kamar' => 'C-09', 'tingkat' => 'Tingkat III',
            'riwayat' => [
                [
                    'tanggal_lapor' => '2026-07-10', 'status' => 'Sedang',
                    'keluhan_awal' => 'Demam dan nyeri otot menyeluruh setelah kegiatan lapangan selama 2 hari.',
                    'terapi' => 'Paracetamol 3x500mg, istirahat total 2 hari, observasi suhu tubuh.',
                    'kontrol' => [
                        ['tanggal' => '10 Jul 2026', 'hasil' => 'Suhu 38.0°C, nyeri otot skala 5/10', 'keterangan' => 'Diberikan obat penurun panas'],
                        ['tanggal' => '12 Jul 2026', 'hasil' => 'Suhu normal, nyeri otot mereda', 'keterangan' => 'Dinyatakan pulih, boleh kembali latihan ringan'],
                    ],
                    'catatan' => 'Disarankan pemanasan lebih lama sebelum kegiatan fisik berat.',
                ],
                [
                    'tanggal_lapor' => '2026-09-18', 'status' => 'Berat',
                    'keluhan_awal' => 'Cedera lutut kanan akibat terjatuh saat latihan fisik, bengkak dan nyeri saat ditekuk.',
                    'terapi' => 'Kompres es 3x sehari, Ibuprofen 2x400mg, imobilisasi dengan decker, rujuk ke dokter spesialis ortopedi.',
                    'kontrol' => [
                        ['tanggal' => '18 Sep 2026', 'hasil' => 'Bengkak signifikan, nyeri skala 7/10', 'keterangan' => 'Rontgen disarankan, rujuk RS rekanan'],
                        ['tanggal' => '21 Sep 2026', 'hasil' => 'Hasil rontgen: tidak ada fraktur, bengkak berkurang', 'keterangan' => 'Lanjutkan fisioterapi ringan'],
                        ['tanggal' => '26 Sep 2026', 'hasil' => 'Bengkak minimal, mobilitas membaik', 'keterangan' => 'Kontrol lanjutan 1 minggu ke depan'],
                    ],
                    'catatan' => 'Dibebastugaskan dari kegiatan fisik hingga pemulihan total. Jadwal kontrol ulang: 3 Oktober 2026 bersama dokter ortopedi.',
                ],
            ],
        ],
        [
            'id' => 4, 'nama' => 'Dewi Anggraini', 'npm' => '123220102', 'kamar' => 'A-07', 'tingkat' => 'Tingkat I',
            'riwayat' => [
                [
                    'tanggal_lapor' => '2026-09-24', 'status' => 'Sedang',
                    'keluhan_awal' => 'Diare disertai nyeri perut sejak semalam, frekuensi BAB lebih dari 5 kali.',
                    'terapi' => 'Oralit, Loperamide 1x2mg, probiotik, diet rendah serat sementara.',
                    'kontrol' => [
                        ['tanggal' => '24 Sep 2026', 'hasil' => 'Frekuensi BAB menurun, tanda dehidrasi ringan', 'keterangan' => 'Lanjutkan oralit, pantau asupan cairan'],
                        ['tanggal' => '26 Sep 2026', 'hasil' => 'BAB normal, kondisi stabil', 'keterangan' => 'Diperbolehkan makan normal bertahap'],
                    ],
                    'catatan' => 'Disarankan menjaga kebersihan makanan. Isolasi mandiri tidak diperlukan, namun hindari kantin selama 2 hari.',
                ],
            ],
        ],
        [
            'id' => 5, 'nama' => 'Rizky Maulana', 'npm' => '123220134', 'kamar' => 'D-01', 'tingkat' => 'Tingkat IV',
            'riwayat' => [
                [
                    'tanggal_lapor' => '2026-09-26', 'status' => 'Ringan',
                    'keluhan_awal' => 'Gatal-gatal ringan pada lengan, diduga reaksi alergi terhadap deterjen baru.',
                    'terapi' => 'Salep antihistamin topikal, CTM 1x4mg bila gatal memberat.',
                    'kontrol' => [
                        ['tanggal' => '26 Sep 2026', 'hasil' => 'Ruam mereda, tidak ada perluasan', 'keterangan' => 'Observasi mandiri, kontrol bila memburuk'],
                    ],
                    'catatan' => 'Disarankan mengganti deterjen dan mencuci pakaian baru sebelum dipakai.',
                ],
            ],
        ],
        [
            'id' => 6, 'nama' => 'Putri Ayu Lestari', 'npm' => '123220156', 'kamar' => 'B-11', 'tingkat' => 'Tingkat II',
            'riwayat' => [
                [
                    'tanggal_lapor' => '2026-06-02', 'status' => 'Ringan',
                    'keluhan_awal' => 'Sakit kepala ringan dan kurang tidur menjelang ujian tengah semester.',
                    'terapi' => 'Paracetamol 1x500mg bila perlu, disarankan tidur cukup.',
                    'kontrol' => [
                        ['tanggal' => '2 Jun 2026', 'hasil' => 'Kondisi membaik setelah istirahat', 'keterangan' => 'Tidak perlu kontrol lanjutan'],
                    ],
                    'catatan' => 'Disarankan atur pola tidur menjelang ujian.',
                ],
                [
                    'tanggal_lapor' => '2026-07-15', 'status' => 'Ringan',
                    'keluhan_awal' => 'Batuk pilek ringan, tidak ada demam.',
                    'terapi' => 'Obat batuk pilek OTC, istirahat cukup.',
                    'kontrol' => [
                        ['tanggal' => '15 Jul 2026', 'hasil' => 'Gejala mereda dalam 2 hari', 'keterangan' => 'Dinyatakan pulih'],
                    ],
                    'catatan' => 'Tidak ada tindak lanjut khusus.',
                ],
                [
                    'tanggal_lapor' => '2026-09-10', 'status' => 'Sedang',
                    'keluhan_awal' => 'Demam dan radang tenggorokan, sulit menelan sejak 2 hari.',
                    'terapi' => 'Paracetamol 3x500mg, obat kumur antiseptik, istirahat bicara.',
                    'kontrol' => [
                        ['tanggal' => '10 Sep 2026', 'hasil' => 'Suhu 37.9°C, tenggorokan merah', 'keterangan' => 'Diberikan obat, pantau 2 hari'],
                        ['tanggal' => '12 Sep 2026', 'hasil' => 'Suhu normal, menelan membaik', 'keterangan' => 'Dinyatakan pulih'],
                    ],
                    'catatan' => 'Disarankan banyak minum air hangat selama masa pemulihan.',
                ],
            ],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Riwayat Kesehatan'])
    <style>
        /* Rentang waktu: sama dengan dashboard (segmen periode + tombol tanggal + kalender popover) */
        .pl-page-head { position: relative; z-index: 20; }
        .pilih-rentang > .pl-date { border: 0; cursor: pointer; text-align: left; transition: background-color .2s, box-shadow .2s, transform .2s; min-height: 0; height: 2.75rem; gap: .5rem; padding: .25rem 1rem .25rem .25rem; }
        .pilih-rentang .pl-date__icon { width: 2.25rem; height: 2.25rem; transition: background-color .2s, color .2s; }
        .pilih-rentang .pl-date b { font: 500 max(14px, .875rem)/1.4 var(--font-sans); }
        .pilih-rentang > .pl-date:hover { background: var(--surface-card); box-shadow: 0 0 0 1px var(--border), var(--shadow-lg); transform: translateY(-1px); }
        .pilih-rentang > .pl-date[aria-expanded="true"] { background: var(--surface-card); box-shadow: 0 0 0 2px var(--strong); }
        .pilih-rentang > .pl-date:hover .pl-date__icon, .pilih-rentang > .pl-date[aria-expanded="true"] .pl-date__icon { background: var(--strong); color: var(--on-primary); }
        .kal-pop { position: absolute; right: 0; top: calc(100% + .5rem); z-index: 30; width: 19rem; padding: 1rem; box-sizing: border-box; border-radius: 1.5rem;
                   background: var(--surface-card); box-shadow: 0 0 0 1px var(--border), var(--shadow-lg);
                   opacity: 0; visibility: hidden; transform: translateY(-.5rem) scale(.97); transform-origin: top right;
                   transition: opacity .18s ease, transform .22s cubic-bezier(.2, .7, .2, 1), visibility 0s .22s; }
        .kal-pop.on { opacity: 1; visibility: visible; transform: none; transition-delay: 0s; }
        .kal-kepala { display: flex; align-items: center; justify-content: space-between; margin-bottom: .5rem; font: 600 1rem/1.3 var(--font-sans); color: var(--heading); }
        .kal-grid { display: grid; grid-template-columns: repeat(7, 1fr); row-gap: .25rem; }
        .kal-grid > span { display: grid; place-items: center; height: 2rem; font: 500 max(12px, .75rem)/1 var(--font-sans); color: var(--text-muted); }
        .kal-grid > button { height: 2.25rem; border: 0; padding: 0; background: transparent; color: var(--text); font: 500 max(14px, .875rem)/1 var(--font-sans); font-variant-numeric: tabular-nums; cursor: pointer; transition: background-color .15s, color .15s; }
        .kal-grid > button > span { display: grid; place-items: center; width: 2.25rem; height: 2.25rem; margin: auto; border-radius: 9999px; transition: inherit; }
        .kal-grid > button:hover:not(:disabled) > span { background: var(--cloud); }
        .kal-grid > button.hari-ini > span { box-shadow: inset 0 0 0 1px var(--field-line); }
        .kal-grid > button.antara { background: var(--cloud); }
        .kal-grid > button.awal { background: linear-gradient(90deg, transparent 50%, var(--cloud) 50%); }
        .kal-grid > button.akhir { background: linear-gradient(90deg, var(--cloud) 50%, transparent 50%); }
        .kal-grid > button.awal.akhir { background: transparent; }
        .kal-grid > button:is(.awal, .akhir) > span { background: var(--strong); color: var(--on-primary); }
        .kal-grid > button:disabled { color: var(--neutral-400); cursor: default; }
        .kal-kaki { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-top: .75rem; padding-top: .75rem; border-top: 1px solid var(--border); }
        .kal-kaki small { font: 500 max(12px, .75rem)/1.3 var(--font-sans); color: var(--text-muted); }
        /* Tabel: sama dengan tabel Laporan Kesehatan (kepala hijau tua, teks putih tebal) */
        .lk th { padding: .75rem .625rem; position: sticky; top: 0; z-index: 1; background: var(--strong); color: var(--on-primary); font-weight: 600; font-size: max(14px, .875rem); border-bottom: 0; }
        .lk td { padding: .5rem .625rem; }
        /* Urut kolom (nama, NPM, tingkat): sama dengan tabel Laporan Kesehatan */
        .lk .urut { all: unset; box-sizing: border-box; cursor: pointer; display: inline-flex; align-items: center; gap: .25rem; border-radius: .25rem; }
        .lk .urut:hover { color: var(--on-primary); text-decoration: underline; text-underline-offset: 3px; }
        .lk .urut:focus-visible { outline: 2px solid var(--on-primary); outline-offset: 2px; }
        .lk .urut::after { content: '\2195'; opacity: .45; font-size: .875rem; }
        .lk th[aria-sort="ascending"] .urut::after { content: '\2191'; opacity: 1; }
        .lk th[aria-sort="descending"] .urut::after { content: '\2193'; opacity: 1; }
        .lk tbody tr[data-id] { cursor: pointer; }
        /* Grafik tren (gaya grafik dashboard): garis halus + area gradasi, garis bantu putus-putus, sorot kolom saat hover */
        .tren-wadah { position: relative; }
        .tren-svg { width: 100%; height: auto; display: block; overflow: visible; }
        .tren-svg text { font: 500 12px var(--font-sans); fill: var(--text-muted); font-variant-numeric: tabular-nums; }
        .tren-svg .garis-bantu { stroke: var(--border); stroke-width: 1; stroke-dasharray: 3 4; }
        .tren-svg .garis-dasar { stroke: var(--field-line, var(--border)); stroke-width: 1; }
        .tren-svg .garis { fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }
        .tren-svg .seri { transition: opacity .2s; }
        .tren-svg .titik { stroke: var(--surface-card); stroke-width: 2; transition: r .15s ease; }
        .tren-svg .titik.aktif { r: 6; }
        .tren-svg .penunjuk { stroke: var(--text-muted); stroke-width: 1; stroke-dasharray: 2 3; opacity: 0; transition: opacity .15s; }
        .tren-wadah.hover .penunjuk { opacity: .6; }
        .tren-svg .area-hover { fill: transparent; cursor: crosshair; }
        /* hover legenda / seri: seri lain diredupkan */
        .tren-wadah[data-sorot] .seri:not(.sorot) { opacity: .2; }
        .pl-legend li[data-seri] { cursor: pointer; transition: opacity .2s; }
        .pl-legend:has(li[data-seri]:hover) li[data-seri]:not(:hover) { opacity: .45; }
        /* Tooltip grafik: kartu kecil seperti dashboard — tanggal redup di atas, nilai tiap seri di bawah */
        #tip { position: fixed; z-index: 50; left: 0; top: 0; pointer-events: none; opacity: 0; transition: opacity .15s ease, transform .12s ease-out; white-space: nowrap;
               display: flex; flex-direction: column; gap: .25rem; padding: .5rem .75rem; border-radius: .75rem;
               background: var(--surface-card); color: var(--text); box-shadow: 0 0 0 1px var(--border), var(--shadow-lg); }
        #tip.on { opacity: 1; }
        #tip small { font: 500 max(12px, .75rem)/1.3 var(--font-sans); color: var(--text-muted); }
        #tip span { display: flex; align-items: center; gap: .5rem; font: 500 max(13px, .8125rem)/1.3 var(--font-sans); }
        #tip span::before { content: ""; width: .625rem; height: .625rem; border-radius: 3px; background: var(--ring); }
        #tip b { margin-left: auto; padding-left: .75rem; font-weight: 600; font-variant-numeric: tabular-nums; }

        /* Animasi saat filter berubah: grafik digambar ulang, kartu & baris tabel muncul halus; dimatikan bila pengguna memilih kurangi gerakan */
        @media (prefers-reduced-motion: no-preference) {
            .tren-svg .garis { stroke-dasharray: 1; stroke-dashoffset: 1; animation: rk-gambar .9s cubic-bezier(.4, 0, .2, 1) forwards; }
            .tren-svg .area { animation: rk-pudar .9s ease-out both; }
            .tren-svg .titik { animation: rk-pudar .3s ease-out both; animation-delay: calc(.35s + var(--i, 0) * 25ms); }
            .rk-muncul { animation: rk-muncul .4s cubic-bezier(.2, .7, .2, 1) both; animation-delay: calc(var(--i, 0) * 35ms); }
            .pl-stat.rk-ubah .pl-stat__value { animation: rk-denyut .45s cubic-bezier(.2, .7, .2, 1); }
            @keyframes rk-gambar { to { stroke-dashoffset: 0; } }
            @keyframes rk-pudar { from { opacity: 0; } }
            @keyframes rk-muncul { from { opacity: 0; transform: translateY(.375rem); } }
            @keyframes rk-denyut { 40% { transform: scale(1.08); } }
        }
        .pl-stat__value { display: inline-block; transform-origin: left center; }

        /* Popup ringkas saat baris di-hover: sama dengan popup Laporan Kesehatan (kartu kecil di samping kursor) */
        .lk-pop { position: fixed; z-index: 40; left: 0; top: 0; width: max(24rem, 340px); max-width: calc(100vw - 1rem); box-sizing: border-box; display: flex; flex-direction: column; gap: .625rem; max-height: calc(100vh - 1rem); overflow-y: auto; overscroll-behavior: contain;
                  padding: 1rem 1.125rem 1.125rem; border-radius: var(--radius-sm); background: var(--surface-card); color: var(--text); box-shadow: 0 0 0 1px var(--border), var(--shadow-lg);
                  font: 400 max(14px, .875rem)/1.4 var(--font-sans);
                  opacity: 0; visibility: hidden; transform: translateY(.375rem) scale(.98); transform-origin: top left;
                  transition: opacity .16s ease, transform .2s cubic-bezier(.2, .7, .2, 1), visibility 0s .2s; }
        .lk-pop.on { opacity: 1; visibility: visible; transform: none; transition: opacity .16s ease, transform .2s cubic-bezier(.2, .7, .2, 1), top .18s ease, left .18s ease; }
        .lk-pop__kepala { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; padding-bottom: .625rem; border-bottom: 1px solid var(--border); }
        .lk-pop__kepala b { font: 700 max(16px, 1rem)/1.3 var(--font-sans); letter-spacing: -.01em; color: var(--heading); }
        .lk-pop__kepala small { display: block; margin-top: .125rem; color: var(--text-muted); font: 500 max(12px, .75rem)/1.3 var(--font-sans); }
        .lk-pop dt { font-weight: 500; color: var(--text-muted); }
        .lk-pop dd { font-weight: 400; color: var(--text); }
        .lk-pop__identitas { display: grid; grid-template-columns: 1fr 1fr; gap: .25rem .75rem; margin: 0; padding: .5rem .625rem; border-radius: .625rem; background: var(--cloud); }
        .lk-pop__identitas > div { display: flex; align-items: baseline; gap: .375rem; min-width: 0; }
        .lk-pop__identitas dt { color: var(--text-muted); font-size: max(12px, .75rem); }
        .lk-pop__identitas dd { margin: 0; font-weight: 600; font-variant-numeric: tabular-nums; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .lk-pop__isi { display: flex; flex-direction: column; gap: .375rem; margin: 0; }
        .lk-pop__isi dt { color: var(--text-muted); font-size: max(12px, .75rem); }
        .lk-pop__isi dd { margin: .125rem 0 0; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-clamp: 2; overflow: hidden; }
        .lk-pop__daftar { display: flex; flex-direction: column; gap: .875rem; }
        .lk-pop__blok { display: flex; flex-direction: column; gap: .625rem; }
        .lk-pop__blokkepala { display: flex; align-items: center; gap: .625rem; }
        .lk-pop__blokkepala .pl-badge::before { display: none; }
        .lk-pop__blokkepala .pl-badge { padding: .3125rem .75rem; background: var(--w, var(--cloud)); color: #fff; font: 700 max(13px, .8125rem)/1.2 var(--font-sans); letter-spacing: .01em; }
        .lk-pop__blokkepala small { color: var(--text-muted); font: 500 max(12px, .75rem)/1.3 var(--font-sans); }
        .lk-pop__aksi { display: flex; }
        .lk-pop__aksi .pl-btn { flex: 1; justify-content: center; height: 2.25rem; padding: 0 .75rem; font-size: max(13px, .8125rem); }
        /* lebih dari satu riwayat: blok berjajar ke samping dengan sekat tegak */
        .lk-pop--samping { width: auto; max-width: min(max(54rem, 760px), calc(100vw - 1rem)); }
        .lk-pop--samping .lk-pop__daftar { flex-direction: row; gap: 1rem; overflow-x: auto; overscroll-behavior: contain; padding-bottom: .125rem; }
        .lk-pop--samping .lk-pop__blok { flex: 0 0 max(15rem, 220px); }
        .lk-pop--samping .lk-pop__blok + .lk-pop__blok { padding-left: 1rem; border-left: 1px solid var(--border); }
        @media (prefers-reduced-motion: reduce) { .lk-pop { transition: opacity .1s, visibility 0s .1s; transform: none; } }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    {{-- ===== Konten Utama (design system Pagi, warna palet aplikasi) ===== --}}
    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <h1 id="historyTitle" class="pl-page-title">{{ $bagian === 'psikolog' ? 'Riwayat Psikologi' : 'Riwayat Kesehatan' }}</h1>
                <p id="historyDescription" class="pl-page-sub">
                    {{ $bagian === 'psikolog' ? 'Lihat keluhan, terapi, dan evaluasi konseling tiap taruna.' : 'Lihat riwayat keluhan, terapi, dan kontrol kesehatan tiap taruna.' }}
                </p>
            </div>
            <div class="pl-page-actions">
                @if ($bisaLihatPsikologi)
                    <nav aria-label="Pilih tampilan bagian" class="pl-segment">
                        @foreach (['perawat' => 'Kesehatan', 'psikolog' => 'Psikologi'] as $jenis => $label)
                            <button id="{{ $jenis === 'perawat' ? 'healthHistoryTab' : 'psychologyHistoryTab' }}"
                                type="button" onclick="setJenisRiwayat('{{ $jenis }}')"
                                aria-pressed="{{ $bagian === $jenis ? 'true' : 'false' }}">{{ $label }}</button>
                        @endforeach
                    </nav>
                @endif
                {{-- Rentang waktu: tombol terpisah per periode + pilih rentang sendiri (sama dengan dashboard) --}}
                <nav class="pl-segment" aria-label="Pilih periode" id="periodeSegmen">
                    @foreach (['semua' => 'Semua', 'hariini' => 'Hari ini', 'mingguini' => 'Minggu ini', 'bulanini' => 'Bulan ini'] as $preset => $labelPreset)
                        <button type="button" data-periode="{{ $preset }}" onclick="applyPreset('{{ $preset }}')" aria-pressed="{{ $preset === 'hariini' ? 'true' : 'false' }}">{{ $labelPreset }}</button>
                    @endforeach
                </nav>
                <div class="pilih-rentang relative" id="calendarWrapper">
                    <button type="button" id="calendarBtn" class="pl-date" aria-haspopup="dialog" aria-expanded="false" aria-controls="kal-pop">
                        <span class="pl-date__icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                        <span><small class="sr-only">Rentang tanggal</small><b id="calendarLabel"></b></span>
                    </button>
                    <div id="kal-pop" class="kal-pop" role="dialog" aria-label="Kalender rentang tanggal">
                        <div class="kal-kepala">
                            <button type="button" class="pl-icon-btn" data-geser="-1" aria-label="Bulan sebelumnya"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 19.5 8.25 12l7.5-7.5"/></svg></button>
                            <span aria-live="polite" data-bulan></span>
                            <button type="button" class="pl-icon-btn" data-geser="1" aria-label="Bulan berikutnya"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg></button>
                        </div>
                        <div class="kal-grid" data-grid></div>
                        <div class="kal-kaki">
                            <small data-keterangan></small>
                            <button type="button" class="pl-btn pl-btn-primary pl-btn-sm" data-terapkan>Terapkan</button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- ===== Kartu Indikator Ringan/Sedang/Berat ===== --}}
        <section aria-label="Ringkasan riwayat" class="pl-section">
            <div id="healthSummary" @class(['hidden' => $bagian === 'psikolog', 'pl-stats'])>
                {{-- sama dengan kartu dashboard: ikon tingkat di kanan atas, warna ikon = warna tingkat sakit --}}
                @foreach ([
                    'Ringan' => ['#6fa89d', 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
                    'Sedang' => ['#c88a1e', 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z'],
                    'Berat' => ['#d8693f', 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z'],
                ] as $tingkatRiwayat => [$warna, $ikonTingkat])
                    <div class="pl-stat pl-hover" style="--sev: {{ $warna }}">
                        <div class="pl-stat__head">
                            <h2 class="pl-stat__label">Riwayat {{ $tingkatRiwayat }}</h2>
                            <span class="pl-stat__icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $ikonTingkat }}"/></svg></span>
                        </div>
                        <p class="pl-stat__value" id="count{{ $tingkatRiwayat }}">0</p>
                        <p class="pl-stat__meta">kejadian</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ===== Grafik tren (hanya riwayat kesehatan); mengikuti rentang tanggal & filter, minimal 7 hari ===== --}}
        <div id="healthCharts" @class(['hidden' => $bagian === 'psikolog', 'pl-row']) style="align-items: stretch">
            @foreach (['Keparahan' => ['trenKeparahan', 'Tren Sakit per Keparahan'], 'Tingkat' => ['trenTingkat', 'Tren Sakit per Tingkat']] as [$idGrafik, $judulGrafik])
                <section class="pl-panel pl-hover" style="flex: 1 1 24rem">
                    <div class="pl-section__head">
                        <h2 class="pl-panel__title">{{ $judulGrafik }}</h2>
                        <ul class="pl-legend" id="{{ $idGrafik }}Legenda"></ul>
                    </div>
                    <div id="{{ $idGrafik }}" class="tren-wadah" role="img" aria-label="{{ $judulGrafik }}"></div>
                </section>
            @endforeach
        </div>

        {{-- ===== Toolbar: Pencarian + Kalender Rentang + Filter + Ekspor PDF ===== --}}
        <div class="pl-toolbar">

            {{-- Pencarian Riwayat --}}
            <label class="pl-search">
                <span class="pl-sr">Cari nama atau NPM taruna</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="search" id="searchInput" class="pl-input" placeholder="Cari nama atau NPM taruna..." oninput="renderAll()">
            </label>

            {{-- Filter Tingkat --}}
            @include('partials.saring', ['kunci' => 'tingkat', 'label' => 'Tingkat', 'opsi' => ['Tingkat I' => 'Tingkat I', 'Tingkat II' => 'Tingkat II', 'Tingkat III' => 'Tingkat III', 'Tingkat IV' => 'Tingkat IV']])

            {{-- Filter Status: pilihan beda untuk kesehatan & psikologi; yang tidak aktif disembunyikan --}}
            <div id="saringStatusKesehatan" @class(['hidden' => $bagian === 'psikolog'])>
                @include('partials.saring', ['kunci' => 'statusKesehatan', 'label' => 'Status', 'opsi' => ['Ringan' => 'Ringan', 'Sedang' => 'Sedang', 'Berat' => 'Berat'],
                    'warna' => ['Ringan' => '#6fa89d', 'Sedang' => '#c88a1e', 'Berat' => '#d8693f']])
            </div>
            <div id="saringStatusPsikologi" @class(['hidden' => $bagian !== 'psikolog'])>
                @include('partials.saring', ['kunci' => 'statusPsikologi', 'label' => 'Status', 'opsi' => ['Melanjutkan konseling' => 'Melanjutkan konseling', 'Tidak melanjutkan konseling' => 'Tidak melanjutkan konseling']])
            </div>

            {{-- Ekspor Riwayat (PDF): tampil sesuai akses bagian untuk jenis riwayat yang sedang dibuka --}}
            @if (in_array(true, $bisaEkspor, true))
            <div class="pl-toolbar__end" data-ekspor>
                <button type="button" onclick="exportPdf()" class="pl-btn pl-btn-secondary">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Ekspor PDF
                </button>
            </div>
            @endif
        </div>

        {{-- ===== Tabel Riwayat Taruna ===== --}}
        <div class="pl-section">
            <div class="pl-table-wrap max-h-[70vh]">
                <table class="pl-table lk">
                    <thead>
                        <tr>
                            @foreach (['nama' => 'Nama Taruna', 'npm' => 'NPM', 'tingkat' => 'Tingkat'] as $kunciUrut => $judulUrut)
                                <th scope="col" data-urut-kolom="{{ $kunciUrut }}" aria-sort="none">
                                    <button type="button" class="urut" onclick="urutkan('{{ $kunciUrut }}')" title="Urutkan kolom {{ $judulUrut }}">{{ $judulUrut }}</button>
                                </th>
                            @endforeach
                            <th>Jumlah Riwayat</th>
                            {{-- Status hanya untuk riwayat psikologi (status konseling) --}}
                            <th id="lastStatusHeading" @class(['hidden' => $bagian !== 'psikolog'])>Status Konseling</th>
                            <th id="lastDateHeading">{{ $bagian === 'psikolog' ? 'Konseling Terakhir' : 'Lapor Terakhir' }}</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        {{-- Diisi oleh JavaScript (renderAll) --}}
                    </tbody>
                </table>
                <p id="emptyState" class="hidden text-center body-sm text-muted py-10 m-0">
                    Tidak ada data yang cocok dengan pencarian/filter.
                </p>
            </div>

            <p id="historyFootnote" @class(['hidden' => $bagian !== 'psikolog', 'pl-table-foot m-0'])>Klik nama taruna untuk melihat seluruh riwayat psikologi dan evaluasi konselingnya.</p>
        </div>
    </main>

    {{-- Popup ringkas saat baris di-hover (desain sama dengan popup Laporan Kesehatan); klik baris = halaman riwayat lengkap --}}
    <div id="lk-pop" class="lk-pop" role="dialog" aria-label="Ringkasan riwayat taruna" aria-hidden="true">
        <div class="lk-pop__kepala">
            <div>
                <b data-isi="nama"></b>
            </div>
        </div>
        <dl class="lk-pop__identitas">
            <div><dt>NPM</dt><dd data-isi="npm"></dd></div>
            <div><dt>Tingkat</dt><dd data-isi="tingkat"></dd></div>
            <div><dt>Kelas</dt><dd data-isi="kelas"></dd></div>
            <div><dt>Kamar</dt><dd data-isi="kamar"></dd></div>
            <div style="grid-column: 1 / -1"><dt>Jenis kelamin</dt><dd data-isi="jenis_kelamin"></dd></div>
            <div style="grid-column: 1 / -1"><dt>Jumlah riwayat</dt><dd data-isi="jumlah"></dd></div>
        </dl>
        <div class="lk-pop__aksi"><a class="pl-btn pl-btn-secondary" data-isi-href>Lihat seluruh riwayat</a></div>
    </div>

    <div id="tip" role="tooltip"></div>

    <script>
        // ================== DATA (dari Blade/PHP) ==================
        const riwayatKesehatanData = @json($riwayatTaruna);
        const riwayatPsikologiData = @json($riwayatPsikologi);
        let jenisRiwayat = @json($bagian);
        let riwayatData = jenisRiwayat === 'psikolog' ? riwayatPsikologiData : riwayatKesehatanData;
        const bisaEkspor = @json($bisaEkspor);
        const urlEkspor = { kesehatan: @json(route('riwayat-kesehatan.ekspor')), psikologi: @json(route('riwayat-kesehatan.psikologi.ekspor')) };
        const urlDetail = @json(route('riwayat-kesehatan.show', 0));
        // Halaman riwayat lengkap satu taruna; riwayat psikologi lewat ?jenis=psikologi
        const detailUrl = id => urlDetail.replace(/0$/, id) + (jenisRiwayat === 'psikologi' ? '?jenis=psikologi' : '');
        const giliranAnimasi = el => { el.classList.remove('rk-muncul'); void el.offsetWidth; el.classList.add('rk-muncul'); };

        function setJenisRiwayat(jenis) {
            jenisRiwayat = ['psikolog', 'psikologi'].includes(jenis) ? 'psikologi' : 'kesehatan';
            riwayatData = jenisRiwayat === 'psikologi' ? riwayatPsikologiData : riwayatKesehatanData;

            const isPsychology = jenisRiwayat === 'psikologi';
            document.getElementById('historyTitle').textContent = isPsychology ? 'Riwayat Psikologi' : 'Riwayat Kesehatan';
            document.getElementById('historyDescription').textContent = isPsychology
                ? 'Lihat keluhan, terapi, dan evaluasi konseling tiap taruna.'
                : 'Lihat riwayat keluhan, terapi, dan kontrol kesehatan tiap taruna.';
            document.getElementById('historyFootnote').classList.toggle('hidden', !isPsychology);
            document.getElementById('healthSummary').classList.toggle('hidden', isPsychology);
            document.querySelectorAll('[data-ekspor]').forEach(el => { el.style.display = bisaEkspor[jenisRiwayat] ? '' : 'none'; });
            document.getElementById('healthCharts').classList.toggle('hidden', isPsychology);
            ['healthSummary', 'healthCharts'].forEach(id => giliranAnimasi(document.getElementById(id)));
            document.getElementById('lastStatusHeading').classList.toggle('hidden', !isPsychology);
            document.getElementById('lastDateHeading').textContent = isPsychology ? 'Konseling Terakhir' : 'Lapor Terakhir';

            document.getElementById('saringStatusKesehatan').classList.toggle('hidden', isPsychology);
            document.getElementById('saringStatusPsikologi').classList.toggle('hidden', !isPsychology);
            document.querySelectorAll('[data-saring^="status"] input:checked').forEach(i => { i.checked = false; });
            document.querySelectorAll('[data-saring^="status"]').forEach(s => s.ringkas?.());

            const healthTab = document.getElementById('healthHistoryTab');
            const psychologyTab = document.getElementById('psychologyHistoryTab');
            if (healthTab && psychologyTab) {
                healthTab.setAttribute('aria-pressed', String(!isPsychology));
                psychologyTab.setAttribute('aria-pressed', String(isPsychology));
            }

            renderAll();
        }

        // Nilai tercentang filter (partials.saring); kosong = semua
        function dipilih(kunci) {
            return [...document.querySelectorAll(`[data-saring="${kunci}"] input:checked`)].map(i => i.value);
        }
        function dipilihStatus() {
            return dipilih(jenisRiwayat === 'psikologi' ? 'statusPsikologi' : 'statusKesehatan');
        }

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            })[character]);
        }

        // ================== HELPER ==================
        function badgeClass(status) {
            return { Ringan: 'pl-badge-primary', Sedang: 'pl-badge-notice', Berat: 'pl-badge-accent' }[status] || 'pl-badge-primary';
        }
        function formatTanggalIndo(isoDate) {
            return new Date(isoDate + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        // ================== STATE FILTER ==================
        // Bawaan: hari ini
        let rangeStart = new Date(); rangeStart.setHours(0, 0, 0, 0);
        let rangeEnd = new Date(rangeStart);

        function episodeDate(ep) { return new Date(ep.tanggal_lapor + 'T00:00:00'); }

        function episodeDalamRentang(ep) {
            if (!rangeStart || !rangeEnd) return true;
            const d = episodeDate(ep);
            return d >= rangeStart && d <= rangeEnd;
        }

        // ================== RENDER KARTU INDIKATOR ==================
        function renderCounters() {
            const tingkat = dipilih('tingkat');
            let ringan = 0, sedang = 0, berat = 0;

            riwayatData.forEach(t => {
                if (tingkat.length && !tingkat.includes(t.tingkat)) return;
                t.riwayat.forEach(ep => {
                    if (!episodeDalamRentang(ep)) return;
                    if (ep.status === 'Ringan') ringan++;
                    else if (ep.status === 'Sedang') sedang++;
                    else if (ep.status === 'Berat') berat++;
                });
            });

            Object.entries({ Ringan: ringan, Sedang: sedang, Berat: berat }).forEach(([k, v]) => hitungKe(document.getElementById('count' + k), v));
        }

        // Angka kartu berpindah halus dari nilai lama ke nilai baru (± 0,5 detik), kartu berdenyut sekali bila nilainya berubah
        function hitungKe(el, tujuan) {
            const awal = Number(el.textContent) || 0;
            if (awal === tujuan) return;
            const kartu = el.closest('.pl-stat');
            kartu.classList.remove('rk-ubah'); void kartu.offsetWidth; kartu.classList.add('rk-ubah');
            cancelAnimationFrame(el._anim); clearTimeout(el._akhir);
            if (matchMedia('(prefers-reduced-motion: reduce)').matches) { el.textContent = tujuan; return; }
            // jaminan nilai akhir: requestAnimationFrame tidak berjalan saat tab/jendela tersembunyi
            el._akhir = setTimeout(() => { cancelAnimationFrame(el._anim); el.textContent = tujuan; }, 550);
            const mulai = performance.now();
            const langkah = (t) => {
                const p = Math.min(1, (t - mulai) / 500), mulus = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(awal + (tujuan - awal) * mulus);
                if (p < 1) el._anim = requestAnimationFrame(langkah);
            };
            el._anim = requestAnimationFrame(langkah);
        }

        // ================== RENDER TABEL ==================
        function renderTable() {
            const keyword = document.getElementById('searchInput').value.toLowerCase().trim();
            const tingkat = dipilih('tingkat');
            const status = dipilihStatus();

            const rows = [];

            riwayatData.forEach(t => {
                const cocokKeyword = t.nama.toLowerCase().includes(keyword) || t.npm.includes(keyword);
                const cocokTingkatTaruna = !tingkat.length || tingkat.includes(t.tingkat);
                if (!cocokKeyword || !cocokTingkatTaruna) return;

                // Filter tanggal hanya untuk kartu; tabel hanya mengikuti pencarian, tingkat, dan status
                const episodeCocok = t.riwayat.filter(ep => !status.length || status.includes(ep.status));
                if (episodeCocok.length === 0) return;

                // Episode terbaru (dari seluruh riwayat, bukan hanya yang cocok filter) untuk kolom status/tanggal
                const episodeTerbaru = [...t.riwayat].sort((a, b) => episodeDate(b) - episodeDate(a))[0];

                rows.push({ taruna: t, episodeTerbaru, jumlahTotal: t.riwayat.length });
            });

            if (urutKolom) {
                const nilai = (t) => urutKolom === 'tingkat' ? ROMAWI[t.tingkat.replace('Tingkat ', '')] ?? 0 : t[urutKolom];
                rows.sort((a, b) => {
                    const x = nilai(a.taruna), y = nilai(b.taruna);
                    const hasil = typeof x === 'number' ? x - y : x.localeCompare(y, 'id', { numeric: true, sensitivity: 'base' });
                    return hasil * urutArah;
                });
            }

            const tbody = document.getElementById('tableBody');
            const emptyState = document.getElementById('emptyState');

            if (rows.length === 0) {
                tbody.innerHTML = '';
                emptyState.classList.remove('hidden');
                return;
            }
            emptyState.classList.add('hidden');

            tbody.innerHTML = rows.map(({ taruna: t, episodeTerbaru, jumlahTotal }, i) => `
                <tr data-id="${t.id}" class="rk-muncul" style="--i: ${Math.min(i, 12)}">
                    <td>
                        <a href="${detailUrl(t.id)}" class="font-medium hover:underline underline-offset-4" style="color: inherit">${escapeHtml(t.nama)}</a>
                    </td>
                    <td>${escapeHtml(t.npm)}</td>
                    <td>${escapeHtml(t.tingkat)}</td>
                    <td>${jumlahTotal} riwayat</td>
                    ${jenisRiwayat === 'psikologi' ? `<td>
                        <span class="pl-badge ${badgeClass(episodeTerbaru.status)}">${escapeHtml(episodeTerbaru.status)}</span>
                    </td>` : ''}
                    <td>${formatTanggalIndo(episodeTerbaru.tanggal_lapor)}</td>
                </tr>`).join('');
        }

        // ================== URUT KOLOM (nama, NPM, tingkat): klik = naik → turun → bawaan ==================
        const ROMAWI = { I: 1, II: 2, III: 3, IV: 4 };
        let urutKolom = null, urutArah = 1;

        function urutkan(kolom) {
            if (urutKolom !== kolom) { urutKolom = kolom; urutArah = 1; }
            else if (urutArah === 1) { urutArah = -1; }
            else { urutKolom = null; urutArah = 1; }
            document.querySelectorAll('[data-urut-kolom]').forEach(th => {
                th.setAttribute('aria-sort', th.dataset.urutKolom === urutKolom ? (urutArah === 1 ? 'ascending' : 'descending') : 'none');
            });
            renderTable();
        }

        // ================== GRAFIK TREN ==================
        const WARNA_KEPARAHAN = { Ringan: '#6fa89d', Sedang: '#c88a1e', Berat: '#d8693f' };
        const WARNA_TINGKAT = { 'Tingkat I': '#4f7fb0', 'Tingkat II': '#8a63b8', 'Tingkat III': '#5a9a52', 'Tingkat IV': '#b0587a' };

        const isoLokal = d => d.toLocaleDateString('sv-SE');

        // Titik sumbu-x: harian bila jendela ≤ 62 hari, selain itu bulanan. Jendela minimal 7 hari berakhir di akhir rentang.
        function bucketTren() {
            const hariIni = new Date(); hariIni.setHours(0, 0, 0, 0);
            let akhir = rangeEnd ?? hariIni, awal = rangeStart;
            if (!awal) {
                const semuaTanggal = riwayatKesehatanData.flatMap(t => t.riwayat.map(episodeDate));
                awal = semuaTanggal.length ? new Date(Math.min(...semuaTanggal)) : new Date(akhir);
            }
            if ((akhir - awal) / 864e5 < 6) { awal = new Date(akhir); awal.setDate(awal.getDate() - 6); }

            const bulanan = (akhir - awal) / 864e5 > 62;
            const bucket = [];
            if (bulanan) {
                for (let d = new Date(awal.getFullYear(), awal.getMonth(), 1); d <= akhir; d.setMonth(d.getMonth() + 1)) {
                    bucket.push({ kunci: isoLokal(d).slice(0, 7), label: d.toLocaleDateString('id-ID', { month: 'short', year: '2-digit' }) });
                }
            } else {
                for (let d = new Date(awal); d <= akhir; d.setDate(d.getDate() + 1)) {
                    bucket.push({ kunci: isoLokal(d), label: d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) });
                }
            }
            return { bucket, panjangKunci: bulanan ? 7 : 10 };
        }

        // Kurva halus melewati semua titik (Catmull-Rom → Bézier), titik kendali dibatasi agar kurva tidak turun di bawah garis nol
        function jalurHalus(titik, yNol) {
            let d = `M${titik[0][0]},${titik[0][1]}`;
            for (let i = 0; i < titik.length - 1; i++) {
                const [p0, p1, p2, p3] = [titik[i - 1] ?? titik[i], titik[i], titik[i + 1], titik[i + 2] ?? titik[i + 1]];
                const c1y = Math.min(yNol, p1[1] + (p2[1] - p0[1]) / 6), c2y = Math.min(yNol, p2[1] - (p3[1] - p1[1]) / 6);
                d += ` C${p1[0] + (p2[0] - p0[0]) / 6},${c1y} ${p2[0] - (p3[0] - p1[0]) / 6},${c2y} ${p2[0]},${p2[1]}`;
            }
            return d;
        }

        function gambarGrafik(idEl, bucket, seri) {
            const W = 600, H = 250, kiri = 34, kanan = 12, atas = 12, bawah = 30;
            const maks = Math.max(1, ...seri.flatMap(s => s.nilai));
            const langkah = Math.max(1, Math.ceil(maks / 4)), puncak = langkah * Math.ceil(maks / langkah);
            const lebarKolom = (W - kiri - kanan) / Math.max(1, bucket.length - 1);
            const x = i => kiri + (bucket.length === 1 ? (W - kiri - kanan) / 2 : i * lebarKolom);
            const y = v => atas + (H - atas - bawah) * (1 - v / puncak);
            const lompat = Math.ceil(bucket.length / 8);

            let svg = `<svg class="tren-svg" viewBox="0 0 ${W} ${H}" aria-hidden="true"><defs>`;
            seri.forEach((s, n) => {
                svg += `<linearGradient id="${idEl}-g${n}" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="${s.warna}" stop-opacity=".22"/><stop offset="1" stop-color="${s.warna}" stop-opacity="0"/></linearGradient>`;
            });
            svg += '</defs>';
            for (let v = 0; v <= puncak; v += langkah) {
                svg += `<line class="${v === 0 ? 'garis-dasar' : 'garis-bantu'}" x1="${kiri}" x2="${W - kanan}" y1="${y(v)}" y2="${y(v)}"/><text x="${kiri - 8}" y="${y(v) + 4}" text-anchor="end">${v}</text>`;
            }
            bucket.forEach((b, i) => {
                if (i % lompat === 0) svg += `<text x="${x(i)}" y="${H - 8}" text-anchor="middle">${b.label}</text>`;
            });
            svg += `<line class="penunjuk" x1="0" x2="0" y1="${atas}" y2="${y(0)}"/>`;
            seri.forEach((s, n) => {
                const titik = s.nilai.map((v, i) => [x(i), y(v)]);
                const jalur = jalurHalus(titik, y(0));
                svg += `<g class="seri" data-seri="${n}">`
                    + `<path class="area" fill="url(#${idEl}-g${n})" d="${jalur} L${x(bucket.length - 1)},${y(0)} L${x(0)},${y(0)} Z"/>`
                    + `<path class="garis" pathLength="1" stroke="${s.warna}" d="${jalur}"/>`
                    + titik.map(([cx, cy], i) => `<circle class="titik" style="--i: ${Math.min(i, 30)}" cx="${cx}" cy="${cy}" r="${bucket.length > 31 ? 0 : 4}" fill="${s.warna}" data-i="${i}"/>`).join('')
                    + '</g>';
            });
            // area hover per kolom tanggal: tooltip berisi nilai semua seri di tanggal itu
            bucket.forEach((b, i) => {
                svg += `<rect class="area-hover" data-i="${i}" x="${bucket.length === 1 ? 0 : x(i) - lebarKolom / 2}" y="${atas}" width="${bucket.length === 1 ? W : lebarKolom}" height="${y(0) - atas}"/>`;
            });

            const wadah = document.getElementById(idEl);
            wadah.innerHTML = svg + '</svg>';
            wadah._grafik = { bucket, seri, x };
            document.getElementById(idEl + 'Legenda').innerHTML = seri.map((s, n) => `<li data-seri="${n}" style="--ring: ${s.warna}">${escapeHtml(s.nama)}</li>`).join('');
        }

        // Interaksi grafik (dipasang sekali per wadah): hover kolom = garis penunjuk + titik membesar + tooltip; hover legenda = sorot satu seri
        (() => {
            const tip = document.getElementById('tip');
            const sembunyi = (wadah) => {
                tip.classList.remove('on');
                wadah.classList.remove('hover');
                wadah.querySelectorAll('.titik.aktif').forEach(c => c.classList.remove('aktif'));
            };
            ['trenKeparahan', 'trenTingkat'].forEach(id => {
                const wadah = document.getElementById(id), legenda = document.getElementById(id + 'Legenda');
                wadah.addEventListener('mousemove', (e) => {
                    const kolom = e.target.closest('.area-hover');
                    if (!kolom || !wadah._grafik) { sembunyi(wadah); return; }
                    const i = Number(kolom.dataset.i), { bucket, seri, x } = wadah._grafik;
                    wadah.classList.add('hover');
                    wadah.querySelector('.penunjuk').setAttribute('transform', `translate(${x(i)} 0)`);
                    wadah.querySelectorAll('.titik').forEach(c => c.classList.toggle('aktif', c.dataset.i === String(i)));
                    tip.innerHTML = `<small>${escapeHtml(bucket[i].label)}</small>` + seri.map(s =>
                        `<span style="--ring: ${s.warna}">${escapeHtml(s.nama)}<b>${s.nilai[i]}</b></span>`).join('');
                    tip.classList.add('on');
                    const w = tip.offsetWidth, h = tip.offsetHeight;
                    tip.style.transform = `translate(${Math.min(e.clientX + 14, innerWidth - w - 8)}px, ${e.clientY - h - 14 < 8 ? e.clientY + 18 : e.clientY - h - 14}px)`;
                });
                wadah.addEventListener('mouseleave', () => sembunyi(wadah));
                legenda.addEventListener('mouseover', (e) => {
                    const li = e.target.closest('li[data-seri]');
                    if (!li) return;
                    wadah.dataset.sorot = li.dataset.seri;
                    wadah.querySelectorAll('.seri').forEach(g => g.classList.toggle('sorot', g.dataset.seri === li.dataset.seri));
                });
                legenda.addEventListener('mouseleave', () => { delete wadah.dataset.sorot; });
            });
            addEventListener('scroll', () => tip.classList.remove('on'), true);
        })();

        function renderTren() {
            if (jenisRiwayat === 'psikologi') return;
            const { bucket, panjangKunci } = bucketTren();
            const indeks = new Map(bucket.map((b, i) => [b.kunci, i]));
            const tingkatDipilih = dipilih('tingkat'), statusDipilih = dipilihStatus();
            const hitung = (cocok) => {
                const nilai = Object.fromEntries(Object.keys(WARNA_KEPARAHAN).map(k => [k, bucket.map(() => 0)]));
                const perTingkat = Object.fromEntries(Object.keys(WARNA_TINGKAT).map(k => [k, bucket.map(() => 0)]));
                riwayatKesehatanData.forEach(t => t.riwayat.forEach(ep => {
                    const i = indeks.get(ep.tanggal_lapor.slice(0, panjangKunci));
                    if (i === undefined || !cocok(t, ep)) return;
                    nilai[ep.status] && nilai[ep.status][i]++;
                    perTingkat[t.tingkat] && perTingkat[t.tingkat][i]++;
                }));
                return { nilai, perTingkat };
            };
            // keparahan mengikuti filter tingkat; tingkat mengikuti filter status
            const a = hitung((t) => !tingkatDipilih.length || tingkatDipilih.includes(t.tingkat));
            const b = hitung((t, ep) => !statusDipilih.length || statusDipilih.includes(ep.status));
            gambarGrafik('trenKeparahan', bucket, Object.entries(WARNA_KEPARAHAN)
                .filter(([k]) => !statusDipilih.length || statusDipilih.includes(k))
                .map(([nama, warna]) => ({ nama, warna, nilai: a.nilai[nama] })));
            gambarGrafik('trenTingkat', bucket, Object.entries(WARNA_TINGKAT)
                .filter(([k]) => !tingkatDipilih.length || tingkatDipilih.includes(k))
                .map(([nama, warna]) => ({ nama, warna, nilai: b.perTingkat[nama] })));
        }

        function renderAll() {
            renderCounters();
            renderTren();
            renderTable();
        }

        // ================== POPUP RINGKAS (HOVER BARIS) ==================
        // Sama dengan popup Laporan Kesehatan: muncul setelah kursor diam sebentar, ikut pindah baris,
        // tetap terbuka saat kursor masuk ke popup. Isi: identitas taruna + jumlah riwayat.
        (() => {
            const pop = document.getElementById('lk-pop'), isi = document.getElementById('tableBody');
            let aktif = null, tundaBuka = null, tundaTutup = null, x = 0;

            const isiPopup = (t) => {
                const psikologi = jenisRiwayat === 'psikologi';
                pop.querySelectorAll(':scope > .lk-pop__kepala [data-isi], :scope > .lk-pop__identitas [data-isi]').forEach(e => { e.textContent = t[e.dataset.isi] ?? '-'; });
                pop.querySelector('[data-isi="jumlah"]').textContent = `${t.riwayat.length} ${psikologi ? 'riwayat psikologi' : 'riwayat kesehatan'}`;
                pop.querySelector('[data-isi-href]').href = detailUrl(t.id);
            };
            // Di kanan kursor, sejajar atas baris; pindah ke kiri / atas bila tidak muat di layar
            const posisikan = (tr) => {
                const r = tr.getBoundingClientRect(), w = pop.offsetWidth, h = pop.offsetHeight;
                const kiri = x + 20 + w > innerWidth - 8 ? x - 20 - w : x + 20;
                pop.style.left = `${Math.max(8, kiri)}px`;
                pop.style.top = `${Math.min(Math.max(8, r.top), Math.max(8, innerHeight - h - 8))}px`;
            };
            const buka = (tr) => {
                const t = riwayatData.find(d => String(d.id) === tr.dataset.id);
                if (!t) return;
                aktif = tr;
                isiPopup(t);
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
                const tr = e.target.closest('tr[data-id]');
                if (!tr) { tutupNanti(); return; }
                clearTimeout(tundaTutup);
                if (tr === aktif) return;
                clearTimeout(tundaBuka);
                tundaBuka = setTimeout(() => buka(tr), pop.classList.contains('on') ? 260 : 450);
            });
            isi.addEventListener('mouseleave', tutupNanti);
            // klik di mana pun pada baris = buka halaman riwayat lengkap
            isi.addEventListener('click', (e) => {
                const tr = e.target.closest('tr[data-id]');
                if (tr && !e.target.closest('a')) window.location.href = detailUrl(tr.dataset.id);
            });
            pop.addEventListener('mouseenter', () => { clearTimeout(tundaTutup); clearTimeout(tundaBuka); });
            pop.addEventListener('mouseleave', tutupNanti);
            addEventListener('scroll', (e) => { if (!pop.contains(e.target)) tutup(); }, true);
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') tutup(); });
        })();

        // ================== RENTANG WAKTU (segmen periode + kalender, sama dengan dashboard) ==================
        const labelTanggal = document.getElementById('calendarLabel');
        const hariIniTanggal = () => { const d = new Date(); d.setHours(0, 0, 0, 0); return d; };
        const fmtTanggal = (d, opsi) => d.toLocaleDateString('id-ID', opsi);

        function labelRentang() {
            const penuh = { day: 'numeric', month: 'long', year: 'numeric' };
            if (!rangeStart) return 'Semua waktu';
            if (+rangeStart === +rangeEnd) return fmtTanggal(rangeEnd, penuh);
            if (rangeStart.getFullYear() !== rangeEnd.getFullYear()) return `${fmtTanggal(rangeStart, penuh)} – ${fmtTanggal(rangeEnd, penuh)}`;
            if (rangeStart.getMonth() !== rangeEnd.getMonth()) return `${fmtTanggal(rangeStart, { day: 'numeric', month: 'long' })} – ${fmtTanggal(rangeEnd, penuh)}`;
            return `${rangeStart.getDate()} – ${fmtTanggal(rangeEnd, penuh)}`;
        }

        function tandaiPeriode(jenis) {
            document.querySelectorAll('#periodeSegmen button').forEach(b => b.setAttribute('aria-pressed', String(b.dataset.periode === jenis)));
            labelTanggal.textContent = labelRentang();
        }

        function applyPreset(jenis) {
            const hariIni = hariIniTanggal();
            if (jenis === 'semua') { rangeStart = null; rangeEnd = null; }
            else if (jenis === 'hariini') { rangeStart = hariIni; rangeEnd = hariIni; }
            else if (jenis === 'mingguini') { rangeStart = new Date(hariIni); rangeStart.setDate(hariIni.getDate() - (hariIni.getDay() + 6) % 7); rangeEnd = hariIni; } // minggu mulai Senin
            else if (jenis === 'bulanini') { rangeStart = new Date(hariIni.getFullYear(), hariIni.getMonth(), 1); rangeEnd = hariIni; }
            tandaiPeriode(jenis);
            renderAll();
        }

        // Kalender: klik pertama = awal, klik kedua = akhir (klik sebelum awal = awal baru); tanggal setelah hari ini dinonaktifkan
        (() => {
            const pop = document.getElementById('kal-pop'), tombol = document.getElementById('calendarBtn');
            const grid = pop.querySelector('[data-grid]'), judulBulan = pop.querySelector('[data-bulan]'), ket = pop.querySelector('[data-keterangan]');
            const kirim = pop.querySelector('[data-terapkan]');
            const hariIni = hariIniTanggal();
            const singkat = d => fmtTanggal(d, { day: 'numeric', month: 'short' });
            let awal = rangeStart ?? hariIni, akhir = rangeEnd ?? hariIni, lihat = new Date(akhir.getFullYear(), akhir.getMonth(), 1);

            const gambar = () => {
                judulBulan.textContent = fmtTanggal(lihat, { month: 'long', year: 'numeric' });
                pop.querySelector('[data-geser="1"]').disabled = lihat >= new Date(hariIni.getFullYear(), hariIni.getMonth(), 1);
                const isi = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'].map(h => Object.assign(document.createElement('span'), { textContent: h }));
                for (let i = (lihat.getDay() + 6) % 7; i > 0; i--) isi.push(document.createElement('span'));
                const jumlahHari = new Date(lihat.getFullYear(), lihat.getMonth() + 1, 0).getDate();
                for (let t = 1; t <= jumlahHari; t++) {
                    const d = new Date(lihat.getFullYear(), lihat.getMonth(), t), b = document.createElement('button');
                    b.type = 'button';
                    b.dataset.tanggal = d.getTime();
                    b.disabled = d > hariIni;
                    b.classList.toggle('hari-ini', +d === +hariIni);
                    b.classList.toggle('awal', +d === +awal);
                    b.classList.toggle('akhir', akhir && +d === +akhir);
                    b.classList.toggle('antara', akhir && d > awal && d < akhir);
                    b.setAttribute('aria-pressed', String(+d === +awal || (akhir && +d === +akhir)));
                    b.setAttribute('aria-label', fmtTanggal(d, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
                    b.append(Object.assign(document.createElement('span'), { textContent: t }));
                    isi.push(b);
                }
                grid.replaceChildren(...isi);
                ket.textContent = akhir ? (+awal === +akhir ? singkat(awal) : `${singkat(awal)} – ${singkat(akhir)}`) : 'Pilih tanggal akhir';
                kirim.disabled = !akhir;
            };

            const atur = (buka) => {
                pop.classList.toggle('on', buka);
                tombol.setAttribute('aria-expanded', String(buka));
                if (buka) {
                    awal = rangeStart ?? hariIni; akhir = rangeEnd ?? hariIni; lihat = new Date(akhir.getFullYear(), akhir.getMonth(), 1);
                    gambar();
                }
            };

            grid.addEventListener('click', (e) => {
                const b = e.target.closest('button[data-tanggal]');
                if (!b) return;
                const d = new Date(Number(b.dataset.tanggal));
                if (akhir || d < awal) { awal = d; akhir = null; } else { akhir = d; }
                gambar();
            });
            pop.querySelectorAll('[data-geser]').forEach(b => b.addEventListener('click', () => {
                lihat = new Date(lihat.getFullYear(), lihat.getMonth() + Number(b.dataset.geser), 1);
                gambar();
            }));
            kirim.addEventListener('click', () => {
                rangeStart = awal; rangeEnd = akhir;
                tandaiPeriode(null); // rentang sendiri: tidak ada tombol periode yang aktif
                atur(false);
                renderAll();
            });
            tombol.addEventListener('click', () => atur(!pop.classList.contains('on')));
            // composedPath: tombol tanggal yang diklik sudah diganti gambar(), jadi target tidak lagi di dalam dokumen
            document.addEventListener('click', (e) => { if (!e.composedPath().includes(tombol.parentElement)) atur(false); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && pop.classList.contains('on')) { atur(false); tombol.focus(); } });
        })();
        tandaiPeriode('hariini');

        // ================== EKSPOR PDF ==================
        // Riwayat yang sedang dibuka (kesehatan/psikologi), mengikuti filter aktif
        function exportPdf() {
            const params = new URLSearchParams();
            const cari = document.getElementById('searchInput').value.trim();
            if (cari) params.set('cari', cari);
            dipilih('tingkat').forEach(t => params.append('tingkat[]', t.replace('Tingkat ', '')));
            dipilihStatus().forEach(s => params.append('status[]', s));
            window.location.href = urlEkspor[jenisRiwayat] + (params.size ? '?' + params : '');
        }

        document.querySelectorAll('[data-saring]').forEach(s => s.addEventListener('change', renderAll));

        // Render awal
        setJenisRiwayat(jenisRiwayat);
    </script>
</body>
</html>
