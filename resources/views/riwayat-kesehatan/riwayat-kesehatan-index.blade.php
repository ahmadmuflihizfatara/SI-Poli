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
<html lang="id" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Kesehatan | SI-Poliklinik</title>
    <link rel="stylesheet" href="{{ asset('css/pulih.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        primary:   { 900: '#0B3B36', 700: '#146B5F', 400: '#6FA89D', 50: '#E3F1EE' },
                        secondary: { 800: '#8A3620', 500: '#D8693F', 300: '#F0B79B', 50: '#FBEAE0' },
                        tertiary:  { 800: '#7A5308', 500: '#C88A1E', 50: '#FBEACD' },
                        neutral:   { 900: '#232620', 600: '#55584F', 400: '#9A9C92', 200: '#DEDFD7', 50: '#FEFDFC' },
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        if (localStorage.theme === 'dark' ||
           (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="font-sans bg-neutral-50 dark:bg-neutral-900 text-neutral-900 dark:text-neutral-50 transition-colors">

    <div class="flex min-h-screen">

        @include('partials.sidebar')

        {{-- ===== Sidebar ===== --}}
        <aside class="hidden w-16 bg-primary-900 flex flex-col items-center py-6 gap-6 fixed h-full z-20">
            <button class="text-primary-50 hover:text-white transition" title="Menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <nav class="flex flex-col gap-3 mt-4">
                <a href="{{ route('dashboard') }}" title="Dashboard"
                   class="w-10 h-10 rounded-xl flex items-center justify-center text-primary-50 hover:bg-primary-700 hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                    </svg>
                </a>
                <a href="{{ route('laporan-kesehatan.index') }}" title="Laporan Kesehatan"
                   class="w-10 h-10 rounded-xl flex items-center justify-center text-primary-50 hover:bg-primary-700 hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </a>
                <a href="{{ route('riwayat-kesehatan.index') }}" title="Riwayat Kesehatan"
                   class="w-10 h-10 rounded-xl bg-primary-700 flex items-center justify-center text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </a>
            </nav>

            <a href="{{ route('logout') }}" title="Keluar"
               class="mt-auto text-primary-50 hover:text-white transition"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        </aside>

        {{-- ===== Konten Utama ===== --}}
        <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] min-w-0 flex-1 p-6 md:p-8 transition-[margin] duration-200">

            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-5">
                <div>
                    <h1 class="text-2xl font-semibold text-primary-900 dark:text-primary-50">Riwayat Kesehatan</h1>
                    <p class="text-neutral-600 dark:text-neutral-400 text-sm mt-1">
                        Lihat histori keluhan, terapi, dan kontrol kesehatan tiap taruna.
                    </p>
                </div>

                <button type="button" onclick="toggleDarkMode()" title="Ganti tema"
                    class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-lg border border-neutral-200 dark:border-neutral-700
                           bg-white dark:bg-neutral-800 text-neutral-600 dark:text-tertiary-500 hover:bg-primary-50 dark:hover:bg-neutral-700 transition">
                    <svg id="iconMoon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                    </svg>
                    <svg id="iconSun" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>
            </div>

            {{-- ===== Kartu Indikator Ringan/Sedang/Berat ===== --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-primary-50 dark:bg-neutral-800 rounded-2xl p-5 border border-primary-400/30 dark:border-neutral-700">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-9 h-9 rounded-xl bg-white dark:bg-neutral-900 flex items-center justify-center text-primary-700 shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Riwayat Ringan</span>
                    </div>
                    <p class="text-3xl font-semibold text-primary-900 dark:text-primary-50">
                        <span id="countRingan">0</span>
                        <span class="text-base font-normal text-neutral-600 dark:text-neutral-400">kejadian</span>
                    </p>
                </div>

                <div class="bg-tertiary-50 dark:bg-neutral-800 rounded-2xl p-5 border border-tertiary-500/30 dark:border-neutral-700">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-9 h-9 rounded-xl bg-white dark:bg-neutral-900 flex items-center justify-center text-tertiary-800 shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 21h6M12 3v10m0 0a3 3 0 100 6 3 3 0 000-6z"/>
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Riwayat Sedang</span>
                    </div>
                    <p class="text-3xl font-semibold text-tertiary-800 dark:text-tertiary-500">
                        <span id="countSedang">0</span>
                        <span class="text-base font-normal text-neutral-600 dark:text-neutral-400">kejadian</span>
                    </p>
                </div>

                <div class="bg-secondary-50 dark:bg-neutral-800 rounded-2xl p-5 border border-secondary-500/30 dark:border-neutral-700">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-9 h-9 rounded-xl bg-white dark:bg-neutral-900 flex items-center justify-center text-secondary-800 shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Riwayat Berat</span>
                    </div>
                    <p class="text-3xl font-semibold text-secondary-800 dark:text-secondary-500">
                        <span id="countBerat">0</span>
                        <span class="text-base font-normal text-neutral-600 dark:text-neutral-400">kejadian</span>
                    </p>
                </div>
            </div>
            <p class="text-xs text-neutral-400 -mt-4 mb-6">
                Jumlah kejadian dihitung dari seluruh episode keluhan sesuai rentang tanggal &amp; filter tingkat yang dipilih.
            </p>

            {{-- ===== Toolbar: Pencarian + Kalender Rentang + Filter + Ekspor PDF ===== --}}
            <div class="flex flex-col md:flex-row md:items-center gap-3 mb-5">

                {{-- Pencarian Riwayat --}}
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="w-4 h-4 text-neutral-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                    <input type="text" id="searchInput" placeholder="Cari nama atau NPM taruna..." oninput="renderAll()"
                        class="w-full rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800
                               pl-10 pr-4 py-2.5 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400
                               focus:outline-none focus:ring-2 focus:ring-primary-400">
                </div>

                {{-- ===== Kalender Rentang Tanggal ===== --}}
                <div class="relative" id="calendarWrapper">
                    <button type="button" id="calendarBtn" onclick="toggleCalendar()"
                        class="flex items-center gap-2 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700
                               rounded-lg pl-3.5 pr-3 py-2.5 text-sm text-neutral-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-primary-400 cursor-pointer min-w-[190px] justify-between">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-700 dark:text-primary-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span id="calendarLabel">Semua waktu</span>
                        </span>
                        <svg class="w-4 h-4 text-neutral-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div id="calendarPopover"
                         class="hidden absolute left-0 mt-2 w-[310px] bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700
                                rounded-xl shadow-lg z-30 p-4">

                        {{-- Preset cepat --}}
                        <div class="flex flex-wrap gap-2 mb-3">
                            <button type="button" onclick="applyPreset('semua')" class="text-xs px-2.5 py-1.5 rounded-full border border-neutral-200 dark:border-neutral-700 hover:bg-primary-50 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200">Semua waktu</button>
                            <button type="button" onclick="applyPreset('7hari')" class="text-xs px-2.5 py-1.5 rounded-full border border-neutral-200 dark:border-neutral-700 hover:bg-primary-50 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200">7 hari terakhir</button>
                            <button type="button" onclick="applyPreset('30hari')" class="text-xs px-2.5 py-1.5 rounded-full border border-neutral-200 dark:border-neutral-700 hover:bg-primary-50 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200">30 hari terakhir</button>
                            <button type="button" onclick="applyPreset('bulanini')" class="text-xs px-2.5 py-1.5 rounded-full border border-neutral-200 dark:border-neutral-700 hover:bg-primary-50 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200">Bulan ini</button>
                        </div>

                        {{-- Navigasi bulan --}}
                        <div class="flex items-center justify-between mb-2">
                            <button type="button" onclick="changeMonth(-1)" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-300">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <span id="calendarMonthLabel" class="text-sm font-medium text-neutral-900 dark:text-neutral-100"></span>
                            <button type="button" onclick="changeMonth(1)" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-300">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>

                        {{-- Grid hari --}}
                        <div class="grid grid-cols-7 gap-y-1 text-center text-[11px] text-neutral-400 mb-1">
                            <span>M</span><span>S</span><span>S</span><span>R</span><span>K</span><span>J</span><span>S</span>
                        </div>
                        <div id="calendarGrid" class="grid grid-cols-7 gap-y-1 text-center text-xs"></div>

                        <p id="calendarHint" class="text-[11px] text-neutral-400 mt-2">Klik tanggal awal, lalu tanggal akhir rentang.</p>

                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-neutral-100 dark:border-neutral-700">
                            <button type="button" onclick="resetCalendar()" class="text-xs text-neutral-500 hover:text-secondary-800">Reset</button>
                            <button type="button" onclick="applyCalendarSelection()"
                                class="bg-primary-700 hover:bg-primary-900 text-white text-xs font-medium px-4 py-1.5 rounded-lg transition">
                                Terapkan
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Filter Tingkat --}}
                <div class="relative">
                    <select id="filterTingkat" onchange="renderAll()"
                        class="appearance-none bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700
                               rounded-lg pl-4 pr-9 py-2.5 text-sm text-neutral-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-primary-400 cursor-pointer">
                        <option value="semua">Semua tingkat</option>
                        <option value="Tingkat I">Tingkat I</option>
                        <option value="Tingkat II">Tingkat II</option>
                        <option value="Tingkat III">Tingkat III</option>
                        <option value="Tingkat IV">Tingkat IV</option>
                    </select>
                    <svg class="w-4 h-4 text-neutral-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                {{-- Filter Status --}}
                <div class="relative">
                    <select id="filterStatus" onchange="renderAll()"
                        class="appearance-none bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700
                               rounded-lg pl-4 pr-9 py-2.5 text-sm text-neutral-900 dark:text-neutral-100
                               focus:outline-none focus:ring-2 focus:ring-primary-400 cursor-pointer">
                        <option value="semua">Semua status</option>
                        <option value="Ringan">Ringan</option>
                        <option value="Sedang">Sedang</option>
                        <option value="Berat">Berat</option>
                    </select>
                    <svg class="w-4 h-4 text-neutral-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                {{-- Ekspor Riwayat (PDF) --}}
                <button type="button" onclick="exportPdf()"
                    class="flex items-center justify-center gap-2 bg-primary-700 hover:bg-primary-900 text-white text-sm font-medium
                           px-4 py-2.5 rounded-lg transition whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Ekspor PDF
                </button>
            </div>

            {{-- ===== Tabel Riwayat Taruna ===== --}}
            <div class="bg-white dark:bg-neutral-800 rounded-2xl border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-primary-50 dark:bg-neutral-900/60 text-neutral-600 dark:text-neutral-400 text-xs uppercase tracking-wide">
                                <th class="text-left px-5 py-3 font-medium">Nama Taruna</th>
                                <th class="text-left px-5 py-3 font-medium">NPM</th>
                                <th class="text-left px-5 py-3 font-medium">Kamar</th>
                                <th class="text-left px-5 py-3 font-medium">Tingkat</th>
                                <th class="text-left px-5 py-3 font-medium">Jumlah Riwayat</th>
                                <th class="text-left px-5 py-3 font-medium">Status Terakhir</th>
                                <th class="text-left px-5 py-3 font-medium">Lapor Terakhir</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody" class="divide-y divide-neutral-100 dark:divide-neutral-700">
                            {{-- Diisi oleh JavaScript (renderAll) --}}
                        </tbody>
                    </table>
                </div>
                <p id="emptyState" class="hidden text-center text-sm text-neutral-400 py-10">
                    Tidak ada data yang cocok dengan pencarian/filter.
                </p>
            </div>

            <p class="text-xs text-neutral-400 mt-3">
                Klik nama taruna pada tabel untuk melihat seluruh riwayat medisnya (bisa lebih dari satu episode keluhan).
            </p>
        </main>
    </div>

    {{-- ===== Modal Detail Riwayat Medis (menampilkan SEMUA episode) ===== --}}
    <div id="modalOverlay" class="hidden fixed inset-0 bg-neutral-900/50 backdrop-blur-sm z-40 flex items-start md:items-center justify-center p-4 overflow-y-auto"
         onclick="if(event.target === this) closeModal()">
        <div class="bg-white dark:bg-neutral-800 rounded-2xl w-full max-w-2xl my-8 shadow-2xl">

            {{-- Header Modal: Kartu Profil --}}
            <div class="flex items-start justify-between gap-4 p-6 border-b border-neutral-100 dark:border-neutral-700">
                <div class="flex items-center gap-4">
                    <div id="modalAvatar" class="w-14 h-14 rounded-full bg-primary-700 text-white flex items-center justify-center text-lg font-semibold flex-shrink-0">
                        T
                    </div>
                    <div>
                        <h3 id="modalNama" class="text-lg font-semibold text-primary-900 dark:text-primary-50">Taruna</h3>
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-0.5">
                            NPM <span id="modalNpm"></span> &middot;
                            Kamar <span id="modalKamar"></span> &middot;
                            <span id="modalTingkat"></span>
                        </p>
                        <p id="modalJumlah" class="text-xs font-medium text-primary-700 dark:text-primary-400 mt-1.5"></p>
                    </div>
                </div>
                <button onclick="closeModal()" class="text-neutral-400 hover:text-secondary-800 transition flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Daftar episode (accordion) --}}
            <div id="modalEpisodeList" class="p-6 space-y-3 max-h-[65vh] overflow-y-auto">
                {{-- diisi JS --}}
            </div>

            {{-- Footer Modal --}}
            <div class="flex items-center justify-end gap-3 p-5 border-t border-neutral-100 dark:border-neutral-700">
                <button onclick="closeModal()"
                    class="px-4 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white transition">
                    Tutup
                </button>
                <button onclick="exportPdf(true)"
                    class="flex items-center gap-2 bg-primary-700 hover:bg-primary-900 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Unduh Seluruh Riwayat (PDF)
                </button>
            </div>
        </div>
    </div>

    <script>
        // ================== DATA (dari Blade/PHP) ==================
        const riwayatData = @json($riwayatTaruna);
        const selectedTarunaId = @json($tarunaTerpilihId);

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            })[character]);
        }

        // ================== DARK MODE ==================
        function updateDarkIcons() {
            const isDark = document.documentElement.classList.contains('dark');
            document.getElementById('iconMoon').classList.toggle('hidden', isDark);
            document.getElementById('iconSun').classList.toggle('hidden', !isDark);
        }
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            updateDarkIcons();
        }
        updateDarkIcons();

        // ================== HELPER ==================
        function badgeClass(status) {
            switch (status) {
                case 'Ringan': return 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400';
                case 'Sedang': return 'bg-tertiary-50 dark:bg-tertiary-800/30 text-tertiary-800 dark:text-tertiary-500';
                case 'Berat':  return 'bg-secondary-50 dark:bg-secondary-800/30 text-secondary-800 dark:text-secondary-500';
                default: return 'bg-neutral-100 text-neutral-600';
            }
        }
        function cardBorderClass(status) {
            switch (status) {
                case 'Ringan': return 'border-primary-400';
                case 'Sedang': return 'border-tertiary-500';
                case 'Berat':  return 'border-secondary-500';
                default: return 'border-neutral-200';
            }
        }
        function initials(nama) {
            return nama.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
        }
        function formatTanggalIndo(isoDate) {
            return new Date(isoDate + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        // ================== STATE FILTER ==================
        let rangeStart = null; // Date object atau null = semua waktu
        let rangeEnd = null;

        function episodeDate(ep) { return new Date(ep.tanggal_lapor + 'T00:00:00'); }

        function episodeDalamRentang(ep) {
            if (!rangeStart || !rangeEnd) return true;
            const d = episodeDate(ep);
            return d >= rangeStart && d <= rangeEnd;
        }

        // ================== RENDER KARTU INDIKATOR ==================
        function renderCounters() {
            const tingkat = document.getElementById('filterTingkat').value;
            let ringan = 0, sedang = 0, berat = 0;

            riwayatData.forEach(t => {
                if (tingkat !== 'semua' && t.tingkat !== tingkat) return;
                t.riwayat.forEach(ep => {
                    if (!episodeDalamRentang(ep)) return;
                    if (ep.status === 'Ringan') ringan++;
                    else if (ep.status === 'Sedang') sedang++;
                    else if (ep.status === 'Berat') berat++;
                });
            });

            document.getElementById('countRingan').textContent = ringan;
            document.getElementById('countSedang').textContent = sedang;
            document.getElementById('countBerat').textContent = berat;
        }

        // ================== RENDER TABEL ==================
        function renderTable() {
            const keyword = document.getElementById('searchInput').value.toLowerCase().trim();
            const tingkat = document.getElementById('filterTingkat').value;
            const status = document.getElementById('filterStatus').value;

            const rows = [];

            riwayatData.forEach(t => {
                const cocokKeyword = t.nama.toLowerCase().includes(keyword) || t.npm.includes(keyword);
                const cocokTingkatTaruna = tingkat === 'semua' || t.tingkat === tingkat;
                if (!cocokKeyword || !cocokTingkatTaruna) return;

                // Episode yang lolos filter tanggal + status (untuk menentukan taruna ini tampil atau tidak)
                const episodeCocok = t.riwayat.filter(ep => episodeDalamRentang(ep) && (status === 'semua' || ep.status === status));
                if (episodeCocok.length === 0) return;

                // Episode terbaru (dari seluruh riwayat, bukan hanya yang cocok filter) untuk kolom status/tanggal
                const episodeTerbaru = [...t.riwayat].sort((a, b) => episodeDate(b) - episodeDate(a))[0];

                rows.push({ taruna: t, episodeTerbaru, jumlahTotal: t.riwayat.length });
            });

            const tbody = document.getElementById('tableBody');
            const emptyState = document.getElementById('emptyState');

            if (rows.length === 0) {
                tbody.innerHTML = '';
                emptyState.classList.remove('hidden');
                return;
            }
            emptyState.classList.add('hidden');

            tbody.innerHTML = rows.map(({ taruna: t, episodeTerbaru, jumlahTotal }) => `
                <tr class="hover:bg-primary-50/50 dark:hover:bg-neutral-700/40 transition">
                    <td class="px-5 py-3">
                        <button onclick="openModal(${t.id})"
                            class="font-medium text-primary-700 dark:text-primary-400 hover:text-primary-900 dark:hover:text-primary-300 hover:underline text-left">
                            ${escapeHtml(t.nama)}
                        </button>
                    </td>
                    <td class="px-5 py-3 text-neutral-600 dark:text-neutral-400">${escapeHtml(t.npm)}</td>
                    <td class="px-5 py-3 text-neutral-600 dark:text-neutral-400">${escapeHtml(t.kamar)}</td>
                    <td class="px-5 py-3 text-neutral-600 dark:text-neutral-400">${escapeHtml(t.tingkat)}</td>
                    <td class="px-5 py-3">
                        <button onclick="openModal(${t.id})"
                            class="text-xs font-medium px-2.5 py-1 rounded-full bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-primary-50 dark:hover:bg-neutral-600 transition">
                            ${jumlahTotal} riwayat
                        </button>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full ${badgeClass(episodeTerbaru.status)}">${escapeHtml(episodeTerbaru.status)}</span>
                    </td>
                    <td class="px-5 py-3 text-neutral-600 dark:text-neutral-400">${formatTanggalIndo(episodeTerbaru.tanggal_lapor)}</td>
                </tr>`).join('');
        }

        function renderAll() {
            renderCounters();
            renderTable();
        }

        // ================== MODAL DETAIL (SEMUA EPISODE) ==================
        let currentTarunaId = null;

        function openModal(id) {
            const t = riwayatData.find(x => x.id === id);
            if (!t) return;
            currentTarunaId = id;

            document.getElementById('modalAvatar').textContent = initials(t.nama);
            document.getElementById('modalNama').textContent = t.nama;
            document.getElementById('modalNpm').textContent = t.npm;
            document.getElementById('modalKamar').textContent = t.kamar;
            document.getElementById('modalTingkat').textContent = t.tingkat;
            document.getElementById('modalJumlah').textContent =
                `Tercatat ${t.riwayat.length} episode keluhan selama menjadi taruna`;

            const episodesSorted = [...t.riwayat].sort((a, b) => episodeDate(b) - episodeDate(a));

            document.getElementById('modalEpisodeList').innerHTML = episodesSorted.map((ep, idx) => `
                <details ${idx === 0 ? 'open' : ''} class="group border border-neutral-200 dark:border-neutral-700 rounded-xl overflow-hidden">
                    <summary class="flex items-center justify-between gap-3 cursor-pointer select-none px-4 py-3
                                     bg-neutral-50 dark:bg-neutral-900/50 hover:bg-primary-50 dark:hover:bg-neutral-700/60 transition">
                        <span class="flex items-center gap-2.5 min-w-0">
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full flex-shrink-0 ${badgeClass(ep.status)}">${escapeHtml(ep.status)}</span>
                            <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">${formatTanggalIndo(ep.tanggal_lapor)}</span>
                            <span class="text-xs text-neutral-500 dark:text-neutral-400 truncate hidden sm:inline">&middot; ${escapeHtml(ep.keluhan_awal)}</span>
                        </span>
                        <svg class="w-4 h-4 text-neutral-400 flex-shrink-0 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>

                    <div class="p-4 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="bg-primary-50 dark:bg-neutral-900/40 rounded-lg p-3 border border-primary-400/20 dark:border-neutral-700">
                                <h4 class="text-[11px] font-semibold uppercase tracking-wide text-primary-700 dark:text-primary-400 mb-1.5">Keluhan Awal</h4>
                                <p class="text-sm text-neutral-800 dark:text-neutral-200 leading-relaxed">${escapeHtml(ep.keluhan_awal)}</p>
                            </div>
                            <div class="bg-tertiary-50 dark:bg-neutral-900/40 rounded-lg p-3 border border-tertiary-500/20 dark:border-neutral-700">
                                <h4 class="text-[11px] font-semibold uppercase tracking-wide text-tertiary-800 dark:text-tertiary-500 mb-1.5">Terapi &amp; Obat</h4>
                                <p class="text-sm text-neutral-800 dark:text-neutral-200 leading-relaxed">${escapeHtml(ep.terapi)}</p>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-primary-900 dark:text-primary-50 mb-1.5">Riwayat Kontrol</h4>
                            <div class="border border-neutral-200 dark:border-neutral-700 rounded-lg overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-neutral-50 dark:bg-neutral-900/60 text-neutral-600 dark:text-neutral-400 text-[11px] uppercase">
                                            <th class="text-left px-3 py-2 font-medium w-24">Tanggal</th>
                                            <th class="text-left px-3 py-2 font-medium">Hasil Kontrol</th>
                                            <th class="text-left px-3 py-2 font-medium">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700 text-neutral-800 dark:text-neutral-200">
                                        ${ep.kontrol.map(k => `
                                            <tr>
                                                <td class="px-3 py-2 whitespace-nowrap">${escapeHtml(k.tanggal)}</td>
                                                <td class="px-3 py-2">${escapeHtml(k.hasil)}</td>
                                                <td class="px-3 py-2">${escapeHtml(k.keterangan)}</td>
                                            </tr>`).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="bg-secondary-50 dark:bg-neutral-900/40 border border-secondary-300/40 dark:border-neutral-700 rounded-lg p-3">
                            <h4 class="text-[11px] font-semibold uppercase tracking-wide text-secondary-800 dark:text-secondary-500 mb-1.5">Keterangan Lainnya</h4>
                            <p class="text-sm text-neutral-800 dark:text-neutral-200 leading-relaxed">${escapeHtml(ep.catatan)}</p>
                        </div>
                    </div>
                </details>
            `).join('');

            document.getElementById('modalOverlay').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            document.getElementById('modalOverlay').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            currentTarunaId = null;
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeModal();
        });

        // ================== KALENDER RENTANG TANGGAL ==================
        let calSelectStart = null;
        let calSelectEnd = null;
        let calViewDate = new Date(); // bulan yang sedang ditampilkan di kalender

        const NAMA_BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        function toggleCalendar() {
            const popover = document.getElementById('calendarPopover');
            popover.classList.toggle('hidden');
            if (!popover.classList.contains('hidden')) renderCalendarGrid();
        }

        function changeMonth(delta) {
            calViewDate.setMonth(calViewDate.getMonth() + delta);
            renderCalendarGrid();
        }

        function sameDay(a, b) {
            return a && b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
        }

        function renderCalendarGrid() {
            document.getElementById('calendarMonthLabel').textContent =
                `${NAMA_BULAN[calViewDate.getMonth()]} ${calViewDate.getFullYear()}`;

            const year = calViewDate.getFullYear();
            const month = calViewDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const startOffset = firstDay.getDay(); // 0 = Minggu
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            let cells = '';
            for (let i = 0; i < startOffset; i++) {
                cells += `<span></span>`;
            }
            for (let day = 1; day <= daysInMonth; day++) {
                const thisDate = new Date(year, month, day);
                let cls = 'w-8 h-8 flex items-center justify-center rounded-lg cursor-pointer mx-auto text-neutral-700 dark:text-neutral-200 hover:bg-primary-50 dark:hover:bg-neutral-700';

                const isStart = sameDay(thisDate, calSelectStart);
                const isEnd = sameDay(thisDate, calSelectEnd);
                const inRange = calSelectStart && calSelectEnd && thisDate > calSelectStart && thisDate < calSelectEnd;

                if (isStart || isEnd) {
                    cls = 'w-8 h-8 flex items-center justify-center rounded-lg cursor-pointer mx-auto bg-primary-700 text-white font-medium';
                } else if (inRange) {
                    cls = 'w-8 h-8 flex items-center justify-center rounded-lg cursor-pointer mx-auto bg-primary-50 dark:bg-primary-900/30 text-primary-900 dark:text-primary-200';
                }

                cells += `<span class="${cls}" onclick="pickCalendarDate(${year}, ${month}, ${day})">${day}</span>`;
            }

            document.getElementById('calendarGrid').innerHTML = cells;
        }

        function pickCalendarDate(year, month, day) {
            const clicked = new Date(year, month, day);
            if (!calSelectStart || (calSelectStart && calSelectEnd)) {
                calSelectStart = clicked;
                calSelectEnd = null;
            } else if (clicked < calSelectStart) {
                calSelectEnd = calSelectStart;
                calSelectStart = clicked;
            } else {
                calSelectEnd = clicked;
            }
            renderCalendarGrid();
        }

        function applyPreset(jenis) {
            const today = new Date(); today.setHours(0,0,0,0);
            if (jenis === 'semua') {
                rangeStart = null; rangeEnd = null;
                document.getElementById('calendarLabel').textContent = 'Semua waktu';
            } else if (jenis === '7hari') {
                rangeEnd = today;
                rangeStart = new Date(today); rangeStart.setDate(rangeStart.getDate() - 6);
                document.getElementById('calendarLabel').textContent = '7 hari terakhir';
            } else if (jenis === '30hari') {
                rangeEnd = today;
                rangeStart = new Date(today); rangeStart.setDate(rangeStart.getDate() - 29);
                document.getElementById('calendarLabel').textContent = '30 hari terakhir';
            } else if (jenis === 'bulanini') {
                rangeStart = new Date(today.getFullYear(), today.getMonth(), 1);
                rangeEnd = today;
                document.getElementById('calendarLabel').textContent = 'Bulan ini';
            }
                calSelectStart = rangeStart; calSelectEnd = rangeEnd;
                document.getElementById('calendarPopover').classList.add('hidden');
                renderAll();
            }

        function applyCalendarSelection() {
            if (calSelectStart && calSelectEnd) {
                rangeStart = calSelectStart;
                rangeEnd = calSelectEnd;
                const fmt = (d) => d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                document.getElementById('calendarLabel').textContent = `${fmt(rangeStart)} &ndash; ${fmt(rangeEnd)}`.replace('&ndash;', '–');
            } else if (calSelectStart && !calSelectEnd) {
                // hanya satu tanggal dipilih: anggap rentang 1 hari itu saja
                rangeStart = calSelectStart;
                rangeEnd = calSelectStart;
                document.getElementById('calendarLabel').textContent = calSelectStart.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            }
            document.getElementById('calendarPopover').classList.add('hidden');
            renderAll();
        }

        function resetCalendar() {
            calSelectStart = null; calSelectEnd = null;
            rangeStart = null; rangeEnd = null;
            document.getElementById('calendarLabel').textContent = 'Semua waktu';
            renderCalendarGrid();
            renderAll();
        }

        document.addEventListener('click', function (e) {
            const wrapper = document.getElementById('calendarWrapper');
            if (!wrapper.contains(e.target)) {
                document.getElementById('calendarPopover').classList.add('hidden');
            }
        });

        // ================== EKSPOR PDF ==================
        function exportPdf(satuTaruna = false) {
            // TODO: ganti dengan request ke endpoint backend, misal:
            // window.location.href = satuTaruna
            //     ? `/riwayat-kesehatan/${currentTarunaId}/export-pdf`
            //     : `/riwayat-kesehatan/export-pdf?${new URLSearchParams({...filter aktif})}`;
            const target = satuTaruna
                ? riwayatData.find(x => x.id === currentTarunaId)?.nama ?? 'taruna ini'
                : 'seluruh data pada tabel';
            alert(`Fitur ekspor PDF untuk ${target} akan terhubung ke backend. (placeholder)`);
        }

        // Render awal
        renderAll();
        if (selectedTarunaId !== null) openModal(selectedTarunaId);
    </script>
</body>
</html>
