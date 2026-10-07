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
        /* Kartu Ringan/Sedang/Berat: saat dihover berwarna sesuai tingkat keparahan (palet grafik dashboard) */
        .stat-tingkat:hover { background: var(--warna); }
        .stat-tingkat:hover :is(.pl-stat__label, .pl-stat__value, .pl-stat__meta) { color: #232620; }
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
                    {{ $bagian === 'psikolog' ? 'Lihat keluhan, terapi, dan evaluasi konseling tiap taruna.' : 'Lihat histori keluhan, terapi, dan kontrol kesehatan tiap taruna.' }}
                </p>
            </div>
            @if ($bisaLihatPsikologi)
                <div class="pl-page-actions">
                    <nav aria-label="Pilih tampilan bagian" class="pl-segment">
                        @foreach (['perawat' => 'Kesehatan', 'psikolog' => 'Psikologi'] as $jenis => $label)
                            <button id="{{ $jenis === 'perawat' ? 'healthHistoryTab' : 'psychologyHistoryTab' }}"
                                type="button" onclick="setJenisRiwayat('{{ $jenis }}')"
                                aria-pressed="{{ $bagian === $jenis ? 'true' : 'false' }}">{{ $label }}</button>
                        @endforeach
                    </nav>
                </div>
            @endif
        </header>

        {{-- ===== Kartu Indikator Ringan/Sedang/Berat ===== --}}
        <section aria-label="Ringkasan riwayat" class="pl-section">
            <div id="healthSummary" @class(['hidden' => $bagian === 'psikolog', 'pl-stats'])>
                @foreach (['Ringan' => '#6fa89d', 'Sedang' => '#c88a1e', 'Berat' => '#d8693f'] as $tingkatRiwayat => $warna)
                    <div class="pl-stat pl-hover stat-tingkat" style="--warna: {{ $warna }}">
                        <h2 class="pl-stat__label">Riwayat {{ $tingkatRiwayat }}</h2>
                        <p class="pl-stat__value" id="count{{ $tingkatRiwayat }}">0</p>
                        <p class="pl-stat__meta">kejadian</p>
                    </div>
                @endforeach
            </div>
            <p id="healthSummaryNote" @class(['hidden' => $bagian === 'psikolog', 'body-sm m-0 text-muted'])>
                Jumlah kejadian dihitung dari seluruh episode keluhan sesuai rentang tanggal &amp; filter tingkat yang dipilih.
            </p>
        </section>

        {{-- ===== Toolbar: Pencarian + Kalender Rentang + Filter + Ekspor PDF ===== --}}
        <div class="pl-toolbar">

            {{-- Pencarian Riwayat --}}
            <label class="pl-search">
                <span class="pl-sr">Cari nama atau NPM taruna</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="search" id="searchInput" class="pl-input" placeholder="Cari nama atau NPM taruna..." oninput="renderAll()">
            </label>

            {{-- ===== Kalender Rentang Tanggal ===== --}}
            <div class="relative" id="calendarWrapper">
                <button type="button" id="calendarBtn" onclick="toggleCalendar()" class="pl-date cursor-pointer min-w-[13rem] border-0">
                    <span class="pl-date__icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                    <span class="text-left"><small>Rentang tanggal</small><b id="calendarLabel">Semua waktu</b></span>
                </button>

                <div id="calendarPopover" class="hidden absolute left-0 mt-2 w-[20rem] z-30 p-5 rounded-3xl bg-white shadow-[var(--shadow-lg)]">

                    {{-- Preset cepat --}}
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach (['semua' => 'Semua waktu', '7hari' => '7 hari terakhir', '30hari' => '30 hari terakhir', 'bulanini' => 'Bulan ini'] as $preset => $labelPreset)
                            <button type="button" onclick="applyPreset('{{ $preset }}')" class="pl-btn pl-btn-ghost !h-8 !px-3 !text-xs">{{ $labelPreset }}</button>
                        @endforeach
                    </div>

                    {{-- Navigasi bulan --}}
                    <div class="flex items-center justify-between mb-2">
                        <button type="button" onclick="changeMonth(-1)" class="pl-icon-btn !w-8 !h-8" aria-label="Bulan sebelumnya">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                        </button>
                        <span id="calendarMonthLabel" class="label"></span>
                        <button type="button" onclick="changeMonth(1)" class="pl-icon-btn !w-8 !h-8" aria-label="Bulan berikutnya">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        </button>
                    </div>

                    {{-- Grid hari --}}
                    <div class="grid grid-cols-7 gap-y-1 text-center caption text-muted mb-1">
                        <span>M</span><span>S</span><span>S</span><span>R</span><span>K</span><span>J</span><span>S</span>
                    </div>
                    <div id="calendarGrid" class="grid grid-cols-7 gap-y-1 text-center body-sm"></div>

                    <p id="calendarHint" class="caption text-muted mt-2 mb-0">Klik tanggal awal, lalu tanggal akhir rentang.</p>

                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-neutral-200">
                        <button type="button" onclick="resetCalendar()" class="label text-muted hover:text-secondary-800">Reset</button>
                        <button type="button" onclick="applyCalendarSelection()" class="pl-btn pl-btn-primary pl-btn-sm !h-9">Terapkan</button>
                    </div>
                </div>
            </div>

            {{-- Filter Tingkat --}}
            <label class="block">
                <span class="pl-sr">Filter tingkat</span>
                <select id="filterTingkat" onchange="renderAll()" class="pl-input pl-input--pill !w-auto min-w-[10rem]">
                    <option value="semua">Semua tingkat</option>
                    <option value="Tingkat I">Tingkat I</option>
                    <option value="Tingkat II">Tingkat II</option>
                    <option value="Tingkat III">Tingkat III</option>
                    <option value="Tingkat IV">Tingkat IV</option>
                </select>
            </label>

            {{-- Filter Status --}}
            <label class="block">
                <span class="pl-sr">Filter status</span>
                <select id="filterStatus" onchange="renderAll()" class="pl-input pl-input--pill !w-auto min-w-[10rem]">
                    <option value="semua">Semua status</option>
                    <option value="Ringan">Ringan</option>
                    <option value="Sedang">Sedang</option>
                    <option value="Berat">Berat</option>
                </select>
            </label>

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
                            <th>Nama Taruna</th>
                            <th>NPM</th>
                            <th>Kamar</th>
                            <th>Tingkat</th>
                            <th>Jumlah Riwayat</th>
                            <th id="lastStatusHeading">{{ $bagian === 'psikolog' ? 'Status Konseling' : 'Status Terakhir' }}</th>
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

            <p id="historyFootnote" class="pl-table-foot m-0">
                {{ $bagian === 'psikolog' ? 'Klik nama taruna untuk melihat seluruh riwayat psikologi dan evaluasi konselingnya.' : 'Klik nama taruna pada tabel untuk melihat seluruh riwayat medisnya (bisa lebih dari satu episode keluhan).' }}
            </p>
        </div>
    </main>

    {{-- ===== Modal Detail Riwayat Medis (menampilkan SEMUA episode) ===== --}}
    <div id="modalOverlay" class="hidden fixed inset-0 z-40 flex items-start md:items-center justify-center p-4 overflow-y-auto" style="background: var(--backdrop)"
         onclick="if(event.target === this) closeModal()">
        <div role="dialog" aria-modal="true" aria-labelledby="modalNama" class="bg-white rounded-3xl w-full max-w-2xl my-8 shadow-[var(--shadow-lg)]">

            {{-- Header Modal: Kartu Profil --}}
            <div class="flex items-start justify-between gap-4 p-8 pb-6">
                <div class="pl-profile__who">
                    <span id="modalAvatar" class="pl-avatar" aria-hidden="true">T</span>
                    <div>
                        <h3 id="modalNama" class="pl-profile__name">Taruna</h3>
                        <p class="pl-profile__meta">
                            NPM <span id="modalNpm"></span> &middot;
                            Kamar <span id="modalKamar"></span> &middot;
                            <span id="modalTingkat"></span>
                        </p>
                        <p id="modalJumlah" class="body-sm m-0 mt-1 font-medium text-primary-700"></p>
                    </div>
                </div>
                <button type="button" onclick="closeModal()" class="pl-icon-btn" aria-label="Tutup">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Daftar episode (accordion) --}}
            <div id="modalEpisodeList" class="px-8 flex flex-col gap-3 max-h-[60vh] overflow-y-auto">
                {{-- diisi JS --}}
            </div>

            {{-- Footer Modal --}}
            <div class="pl-dialog__actions p-8 pt-6">
                <button type="button" onclick="closeModal()" class="pl-btn pl-btn-ghost">Tutup</button>
                @if (in_array(true, $bisaEkspor, true))
                <button type="button" onclick="exportPdf(true)" class="pl-btn pl-btn-primary" data-ekspor>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Unduh Seluruh Riwayat (PDF)
                </button>
                @endif
            </div>
        </div>
    </div>

    <script>
        // ================== DATA (dari Blade/PHP) ==================
        const riwayatKesehatanData = @json($riwayatTaruna);
        const riwayatPsikologiData = @json($riwayatPsikologi);
        let jenisRiwayat = @json($bagian);
        let riwayatData = jenisRiwayat === 'psikolog' ? riwayatPsikologiData : riwayatKesehatanData;
        const selectedTarunaId = @json($tarunaTerpilihId);
        const bisaEkspor = @json($bisaEkspor);
        const urlEkspor = {
            kesehatan: [@json(route('riwayat-kesehatan.ekspor')), @json(route('riwayat-kesehatan.ekspor.satu', 0))],
            psikologi: [@json(route('riwayat-kesehatan.psikologi.ekspor')), @json(route('riwayat-kesehatan.psikologi.ekspor.satu', 0))],
        };

        function setJenisRiwayat(jenis) {
            jenisRiwayat = ['psikolog', 'psikologi'].includes(jenis) ? 'psikologi' : 'kesehatan';
            riwayatData = jenisRiwayat === 'psikologi' ? riwayatPsikologiData : riwayatKesehatanData;

            const isPsychology = jenisRiwayat === 'psikologi';
            document.getElementById('historyTitle').textContent = isPsychology ? 'Riwayat Psikologi' : 'Riwayat Kesehatan';
            document.getElementById('historyDescription').textContent = isPsychology
                ? 'Lihat keluhan, terapi, dan evaluasi konseling tiap taruna.'
                : 'Lihat histori keluhan, terapi, dan kontrol kesehatan tiap taruna.';
            document.getElementById('historyFootnote').textContent = isPsychology
                ? 'Klik nama taruna untuk melihat seluruh riwayat psikologi dan evaluasi konselingnya.'
                : 'Klik nama taruna pada tabel untuk melihat seluruh riwayat medisnya (bisa lebih dari satu episode keluhan).';
            document.getElementById('healthSummary').classList.toggle('hidden', isPsychology);
            document.getElementById('healthSummaryNote').classList.toggle('hidden', isPsychology);
            document.querySelectorAll('[data-ekspor]').forEach(el => { el.style.display = bisaEkspor[jenisRiwayat] ? '' : 'none'; });
            document.getElementById('lastStatusHeading').textContent = isPsychology ? 'Status Konseling' : 'Status Terakhir';
            document.getElementById('lastDateHeading').textContent = isPsychology ? 'Konseling Terakhir' : 'Lapor Terakhir';

            const statusFilter = document.getElementById('filterStatus');
            const statuses = isPsychology
                ? [...new Set(riwayatData.flatMap(taruna => taruna.riwayat.map(episode => episode.status)))]
                : ['Ringan', 'Sedang', 'Berat'];
            statusFilter.replaceChildren(new Option('Semua status', 'semua'));
            statuses.forEach(status => statusFilter.add(new Option(status, status)));
            statusFilter.value = 'semua';

            const healthTab = document.getElementById('healthHistoryTab');
            const psychologyTab = document.getElementById('psychologyHistoryTab');
            if (healthTab && psychologyTab) {
                healthTab.setAttribute('aria-pressed', String(!isPsychology));
                psychologyTab.setAttribute('aria-pressed', String(isPsychology));
            }

            renderAll();
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
                <tr>
                    <td>
                        <button type="button" onclick="openModal(${t.id})"
                            class="font-medium text-primary-700 hover:underline underline-offset-4 text-left">
                            ${escapeHtml(t.nama)}
                        </button>
                    </td>
                    <td class="text-muted">${escapeHtml(t.npm)}</td>
                    <td class="text-muted">${escapeHtml(t.kamar)}</td>
                    <td class="text-muted">${escapeHtml(t.tingkat)}</td>
                    <td>
                        <button type="button" onclick="openModal(${t.id})" class="pl-badge [--ring:var(--primary-400)] hover:underline">
                            ${jumlahTotal} riwayat
                        </button>
                    </td>
                    <td>
                        <span class="pl-badge ${badgeClass(episodeTerbaru.status)}">${escapeHtml(episodeTerbaru.status)}</span>
                    </td>
                    <td class="text-muted">${formatTanggalIndo(episodeTerbaru.tanggal_lapor)}</td>
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
            const isPsychology = jenisRiwayat === 'psikologi';
            document.getElementById('modalJumlah').textContent =
                `Tercatat ${t.riwayat.length} ${isPsychology ? 'riwayat psikologi' : 'episode keluhan kesehatan'} selama menjadi taruna`;
            const labelKeluhan = isPsychology ? 'Keluhan Psikologi' : 'Keluhan Awal';
            const labelTerapi = isPsychology ? 'Terapi Psikologi' : 'Terapi & Obat';
            const labelRiwayat = isPsychology ? 'Riwayat Konseling' : 'Riwayat Kontrol';
            const labelHasil = isPsychology ? 'Hasil Konseling' : 'Hasil Kontrol';

            const episodesSorted = [...t.riwayat].sort((a, b) => episodeDate(b) - episodeDate(a));

            document.getElementById('modalEpisodeList').innerHTML = episodesSorted.map((ep, idx) => `
                <details ${idx === 0 ? 'open' : ''} class="group pl-card overflow-hidden">
                    <summary class="flex items-center justify-between gap-3 cursor-pointer select-none px-5 py-4 [&::-webkit-details-marker]:hidden list-none">
                        <span class="flex items-center gap-2.5 min-w-0">
                            <span class="pl-badge flex-shrink-0 ${badgeClass(ep.status)}">${escapeHtml(ep.status)}</span>
                            <span class="label truncate">${formatTanggalIndo(ep.tanggal_lapor)}</span>
                            <span class="body-sm text-muted truncate hidden sm:inline">&middot; ${escapeHtml(ep.keluhan_awal)}</span>
                        </span>
                        <svg class="w-4 h-4 text-muted flex-shrink-0 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>

                    <div class="px-5 pb-5 flex flex-col gap-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="bg-white rounded-2xl p-4">
                                <h4 class="label-sm text-primary-700 m-0 mb-1.5">${labelKeluhan}</h4>
                                <p class="body-sm m-0">${escapeHtml(ep.keluhan_awal)}</p>
                            </div>
                            <div class="bg-white rounded-2xl p-4">
                                <h4 class="label-sm text-tertiary-800 m-0 mb-1.5">${labelTerapi}</h4>
                                <p class="body-sm m-0">${escapeHtml(ep.terapi)}</p>
                            </div>
                        </div>

                        <div>
                            <h4 class="label text-primary-900 m-0 mb-2">${labelRiwayat}</h4>
                            <div class="rounded-2xl overflow-hidden">
                                <table class="pl-table">
                                    <thead>
                                        <tr>
                                            <th class="w-28">Tanggal</th>
                                            <th>${labelHasil}</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${ep.kontrol.map(k => `
                                            <tr>
                                                <td class="whitespace-nowrap">${escapeHtml(k.tanggal)}</td>
                                                <td>${escapeHtml(k.hasil)}</td>
                                                <td>${escapeHtml(k.keterangan)}</td>
                                            </tr>`).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="bg-secondary-50 rounded-2xl p-4">
                            <h4 class="label-sm text-secondary-800 m-0 mb-1.5">Keterangan Lainnya</h4>
                            <p class="body-sm m-0">${escapeHtml(ep.catatan)}</p>
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
                let cls = 'w-8 h-8 flex items-center justify-center rounded-full cursor-pointer mx-auto hover:bg-primary-50';

                const isStart = sameDay(thisDate, calSelectStart);
                const isEnd = sameDay(thisDate, calSelectEnd);
                const inRange = calSelectStart && calSelectEnd && thisDate > calSelectStart && thisDate < calSelectEnd;

                if (isStart || isEnd) {
                    cls = 'w-8 h-8 flex items-center justify-center rounded-full cursor-pointer mx-auto bg-primary-700 text-white font-medium';
                } else if (inRange) {
                    cls = 'w-8 h-8 flex items-center justify-center rounded-full cursor-pointer mx-auto bg-primary-50 text-primary-900';
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
        // Riwayat yang sedang dibuka (kesehatan/psikologi). Satu taruna = taruna di modal; semua = mengikuti filter aktif
        function exportPdf(satuTaruna = false) {
            const [urlSemua, urlSatu] = urlEkspor[jenisRiwayat];
            if (satuTaruna) {
                window.location.href = urlSatu.replace('/0/', `/${currentTarunaId}/`);
                return;
            }
            const iso = d => d.toLocaleDateString('sv-SE'); // format YYYY-MM-DD zona lokal
            const filter = {
                cari: document.getElementById('searchInput').value.trim(),
                tingkat: document.getElementById('filterTingkat').value.replace('Tingkat ', ''),
                status: document.getElementById('filterStatus').value,
                dari: rangeStart ? iso(rangeStart) : '',
                sampai: rangeEnd ? iso(rangeEnd) : '',
            };
            const params = new URLSearchParams(Object.entries(filter).filter(([, v]) => v && v !== 'semua'));
            window.location.href = urlSemua + (params.size ? '?' + params : '');
        }

        // Render awal
        setJenisRiwayat(jenisRiwayat);
        if (selectedTarunaId !== null) openModal(selectedTarunaId);
    </script>
</body>
</html>
