{{-- dashboard/dashboard.blade.php — tata letak "Dasbor" design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Dashboard'])
    <script>document.documentElement.classList.add('memuat')</script>
    <style>
        .sw { width: .75rem; height: .75rem; border-radius: 9999px; flex-shrink: 0; }
        .sw-kotak { border-radius: 3px; }

        /* StackBar Pagi: hanya ujung batang membulat, angka di dalam segmen */
        .seg { min-width: 1.75rem; display: grid; place-items: center; border-radius: 0; font: 600 max(14px, .875rem)/1 var(--font-sans); color: #141612; } /* #141612 ≥ 5,2:1 di semua warna batang (WCAG 1.4.3) */
        .seg:first-child { border-radius: .5rem 0 0 .5rem; }
        .seg:last-child { border-radius: 0 .5rem .5rem 0; }
        .seg:only-child { border-radius: .5rem; }

        /* Grafik interaktif: elemen ber-[data-judul] menyorot sendiri & meredupkan saudaranya */
        [data-judul] { cursor: pointer; outline: none; transition: opacity .2s, transform .25s cubic-bezier(.2, .7, .2, 1), filter .2s; }
        [data-judul]:hover, [data-judul]:focus-visible { filter: brightness(1.08) saturate(1.1); }
        .grup-tip:has([data-judul]:hover, [data-judul]:focus-visible) [data-judul]:not(:hover, :focus-visible) { opacity: .45; }
        .irisan:hover, .irisan:focus-visible { transform: translate(var(--dx, 0px), var(--dy, 0px)); }
        .seg:hover, .seg:focus-visible { transform: scaleY(1.18); }
        .pl-legend li::before { width: .875rem; height: .875rem; } /* cukup besar agar pola garis Berat terlihat */

        /* Tooltip grafik: kartu kecil tanpa titik warna — label kecil redup di atas, angka tebal di bawah */
        #tip { position: fixed; z-index: 50; left: 0; top: 0; pointer-events: none; opacity: 0; transition: opacity .15s ease; white-space: nowrap;
               display: flex; flex-direction: column; gap: .125rem; padding: .5rem .75rem; border-radius: .75rem;
               background: var(--surface-card); color: var(--text); box-shadow: 0 0 0 1px var(--border), var(--shadow-lg); }
        #tip.on { opacity: 1; }
        #tip small { font: 500 max(12px, .75rem)/1.3 var(--font-sans); color: var(--text-muted); }
        #tip b { font: 600 max(14px, .875rem)/1.3 var(--font-sans); font-variant-numeric: tabular-nums; }

        /* Pemilih rentang tanggal: tombol tanggal + kalender popover (kepala halaman diangkat agar popover di atas kartu) */
        .pl-page-head { position: relative; z-index: 20; }
        /* tinggi & huruf sama dengan .pl-segment di sebelahnya: padding .25rem + tombol 2.25rem, teks satu baris */
        .pilih-rentang > .pl-date { min-height: 0; height: 2.75rem; gap: .5rem; padding: .25rem 1rem .25rem .25rem; }
        .pilih-rentang .pl-date__icon { width: 2.25rem; height: 2.25rem; }
        .pilih-rentang .pl-date b { font: 500 max(14px, .875rem)/1.4 var(--font-sans); }

        /* Daftar keluhan baru: "Semua" aktif sejak awal, tinggi dibatasi ±5 keluhan lalu digulir; filter lima teratas memangkas tanpa gulir */
        .daftar-keluhan[data-batas="5"] > li:nth-child(n + 6) { display: none; }
        /* --tinggi-lima = tinggi 5 kartu pertama (diukur skrip); "Semua" minimal 1,5× itu agar tidak lebih pendek dari mode lima kartu */
        .daftar-keluhan[data-batas="semua"] { max-height: max(30rem, calc(var(--tinggi-lima, 0px) * 1.5)); overflow-y: auto; padding: .25rem; margin: -.25rem; }
        .daftar-keluhan a { display: flex; align-items: center; gap: .875rem; padding: .75rem; border-radius: 1rem; background: var(--surface-card); color: var(--text); text-decoration: none; transition: box-shadow .2s, transform .2s; }
        .daftar-keluhan a:hover { box-shadow: 0 0 0 1px var(--border), var(--shadow-lg); transform: translateY(-1px); }
        @media (prefers-reduced-motion: no-preference) { .daftar-keluhan > li { animation: muncul .35s ease-out both; } }

        /* Skeleton loading: tampil sampai halaman selesai dimuat (kelas .memuat dipasang skrip di <head>, jadi tanpa JS isi tetap tampil) */
        .pl-main.pl-skel { display: none; }
        .memuat .pl-main.pl-skel { display: flex; }
        .memuat .pl-main:not(.pl-skel) { display: none; }
        .sk { display: block; border-radius: .5rem; background: linear-gradient(90deg, var(--neutral-200) 25%, var(--neutral-100, #f1f2ee) 50%, var(--neutral-200) 75%); background-size: 200% 100%; }
        .sk-bulat { border-radius: 9999px; }
        @media (prefers-reduced-motion: no-preference) { .sk { animation: sk-kilau 1.4s linear infinite; } }
        @keyframes sk-kilau { to { background-position: -200% 0; } }

        /* Animasi grafik; dimatikan bila pengguna memilih kurangi gerakan */
        @media (prefers-reduced-motion: no-preference) {
            .anim-batang { transform-origin: left; animation: tumbuh .9s cubic-bezier(.2, .7, .2, 1) both; animation-delay: calc(var(--i) * 120ms); }
            /* transform-origin diisi inline = pusat grafik (satuan viewBox) */
            .anim-tumbuh { transform-box: view-box; animation: tumbuh-bulat .7s cubic-bezier(.2, .7, .2, 1) both; }
            .anim-muncul { animation: muncul .5s ease-out both; animation-delay: var(--d, 0ms); }
            @keyframes tumbuh { from { transform: scaleX(0); } }
            @keyframes tumbuh-bulat { from { transform: scale(.4) rotate(-45deg); opacity: 0; } }
            @keyframes muncul { from { opacity: 0; transform: translateY(.25rem); } }
        }
    </style>
</head>
<body class="overflow-x-hidden" aria-busy="true">

    @include('partials.sidebar')

    @php
        // Isi menyesuaikan bagian: perawat (keluhan medis) atau psikolog (konseling), lihat DashboardController
        $psikolog = $bagian === 'psikolog';
        $kategori = $psikolog ? ['Masih konseling', 'Selesai konseling'] : ['Ringan', 'Sedang', 'Berat'];
        // Satu warna = satu makna di seluruh halaman: teal/emas/oranye hanya untuk Ringan/Sedang/Berat (juga ikon kartu).
        $warnaSakit = $psikolog ? ['#c88a1e', '#6fa89d'] : ['#6fa89d', '#c88a1e', '#d8693f'];
        $judul = $psikolog
            ? ['tingkat' => 'Taruna Keluhan Psikologi Per Tingkat', 'jk' => 'Taruna Konseling Berdasarkan Jenis Kelamin', 'banding' => 'Perbandingan Kesehatan Psikologi Taruna']
            : ['tingkat' => 'Taruna Sakit Per Tingkat', 'jk' => 'Taruna Sakit Berdasarkan Jenis Kelamin', 'banding' => 'Perbandingan Kesehatan Taruna'];
        // Sumbu batang tetap (angka bulat ≥ 5), bukan nilai terbesar: nilai 1 tidak lagi tampil sebagai batang penuh
        $sumbu = \App\Http\Controllers\DashboardController::batasSumbu(max(array_map('array_sum', $perTingkat)));
        $tikSumbu = range(0, $sumbu, $sumbu / 5);

        // Titik [x, y] di lingkaran untuk sudut (derajat, searah jarum jam dari arah jam 3)
        $titik = fn ($cx, $cy, $r, $deg) => [round($cx + $r * cos(deg2rad($deg)), 1), round($cy + $r * sin(deg2rad($deg)), 1)];

        // Irisan pie dari sudut a0 ke a1. Digambar sebagai <path> berukuran pasti,
        // bukan trik stroke-dasharray + pathLength yang skalanya tidak konsisten antar-browser (irisan jadi meleset).
        $irisan = function ($cx, $cy, $r, $a0, $a1) use ($titik) {
            $besar = $a1 - $a0 > 180 ? 1 : 0;
            [$x1, $y1] = $titik($cx, $cy, $r, $a0);
            [$x2, $y2] = $titik($cx, $cy, $r, $a1);

            return "M{$cx} {$cy} L{$x1} {$y1} A{$r} {$r} 0 {$besar} 1 {$x2} {$y2} Z";
        };
        // Bagi rentang sudut sesuai proporsi nilai; nilai 0 tidak digambar. Hasil: [kunci => [a0, a1]]
        $segmen = function (array $nilai, $mulai, $rentang) {
            $total = array_sum($nilai) ?: 1;
            $a = $mulai;
            $hasil = [];
            foreach ($nilai as $k => $n) {
                $b = $a + $rentang * $n / $total;
                if ($n > 0) {
                    $hasil[$k] = [$a, $b];
                }
                $a = $b;
            }

            return $hasil;
        };
        $persen = [\App\Http\Controllers\DashboardController::class, 'persen'];

        // Jenis kelamin: batang horizontal (perbandingan dua kategori), skala = total
        $totalJk = array_sum($jenisKelamin);
        $persenJk = $persen($jenisKelamin);
        $warnaJk = array_combine(array_keys($jenisKelamin), ['#6fa89d', '#d8693f']);

        // Pie perbandingan: Sakit mulai arah jam 3 searah jarum jam, lalu Sembuh (sesuai desain)
        $totalBanding = array_sum($perbandingan);
        $persenBanding = $persen($perbandingan);
        $warnaBanding = ['Sembuh' => '#6fa89d', 'Sakit' => '#d8693f'];
        $namaBanding = $psikolog ? ['Sembuh' => 'Tidak konseling', 'Sakit' => 'Masih konseling'] : ['Sembuh' => 'Sembuh', 'Sakit' => 'Sakit'];
        $segBanding = $segmen(['Sakit' => $perbandingan['Sakit'], 'Sembuh' => $perbandingan['Sembuh']], 0, 360);

        $labelPeriode = \App\Http\Controllers\DashboardController::PERIODE[$periode] ?? 'Rentang terpilih';
        $fmt = fn ($d, $pola) => $d->locale('id')->translatedFormat($pola);
        $rentang = match (true) {
            $awal->isSameDay($akhir) => $fmt($akhir, 'd F Y'),
            $awal->isSameMonth($akhir) => $fmt($awal, 'j').' – '.$fmt($akhir, 'd F Y'),
            $awal->isSameYear($akhir) => $fmt($awal, 'd F').' – '.$fmt($akhir, 'd F Y'),
            default => $fmt($awal, 'd F Y').' – '.$fmt($akhir, 'd F Y'),
        };

        // Ikon heroicons (outline) untuk kartu
        $ikon = [
            'ringan' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
            'sedang' => 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z',
            'berat' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
            'sembuh' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            'rumah' => 'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
            'pesan' => 'M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z',
            'ulang' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99',
        ];
        $pembanding = \App\Http\Controllers\DashboardController::PEMBANDING[$periode];

        // StatTile Pagi: [judul, jumlah, warna ikon (tingkat keparahan), ikon, keterangan, tile tegas,
        //                 tren (null = tanpa tren), teks pembanding, naik = buruk]
        $kartu = $psikolog ? [
            ['Taruna yang Menyatakan Keluhan', $menyatakan, '#f0b79b', 'pesan', 'taruna', false, $tren['menyatakan'], $pembanding, true],
            ['Taruna yang Melanjutkan Konseling', $melanjutkan, '#c88a1e', 'ulang', 'taruna', false, null, null, true],
            ['Taruna yang Selesai Konseling', $selesai, '#6fa89d', 'sembuh', 'taruna', false, $tren['selesai'], $pembanding, false],
        ] : [
            ['Sakit Ringan', $ringan, '#6fa89d', 'ringan', 'taruna', false, $tren['ringan'], 'dari kemarin', true],
            ['Sakit Sedang', $sedang, '#c88a1e', 'sedang', 'taruna', false, $tren['sedang'], 'dari kemarin', true],
            ['Sakit Berat', $berat, '#d8693f', 'berat', 'taruna, perlu prioritas', false, $tren['berat'], 'dari kemarin', true],
            ['Sembuh', $sembuh, '#9bd6c7', 'sembuh', 'Dinyatakan sembuh '.strtolower($labelPeriode), false, $tren['sembuh'], $pembanding, false],
            ['Isoman', $isoman, '#f0b79b', 'rumah', 'Sedang melaksanakan isolasi mandiri', false, null, null, true],
        ];
    @endphp

    <main class="pl-main pl-skel" aria-hidden="true">
        <div class="pl-page-head">
            <div class="flex flex-col gap-3"><span class="sk" style="width: 14rem; height: 2.5rem"></span><span class="sk" style="width: 20rem; max-width: 100%; height: 1rem"></span></div>
            <span class="sk" style="width: 18rem; max-width: 100%; height: 2.75rem"></span>
        </div>
        <div class="pl-stats">
            @foreach ($kartu as $_)
                <div class="pl-stat">
                    <div class="pl-stat__head"><span class="sk" style="width: 60%; height: 1rem"></span><span class="sk sk-bulat" style="width: 2.5rem; height: 2.5rem"></span></div>
                    <span class="sk" style="width: 40%; height: 3.5rem"></span>
                    <span class="sk" style="width: 70%; height: .875rem"></span>
                </div>
            @endforeach
        </div>
        <div class="pl-row" style="align-items: stretch">
            <div class="flex flex-col gap-6" style="flex: 2 1 30rem">
                <div class="pl-panel flex flex-col gap-4">
                    <span class="sk" style="width: 40%; height: 1.5rem"></span>
                    @foreach ([100, 80, 55] as $lebar)
                        <span class="sk" style="width: {{ $lebar }}%; height: 2rem"></span>
                    @endforeach
                </div>
                <div class="pl-panel flex flex-col gap-3">
                    <span class="sk" style="width: 35%; height: 1.5rem"></span>
                    @foreach (range(1, 5) as $_)
                        <span class="sk" style="height: 2.25rem"></span>
                    @endforeach
                </div>
            </div>
            <div class="flex flex-col gap-6" style="flex: 1 1 20rem">
                <div class="pl-panel flex flex-col items-center gap-5">
                    <span class="sk self-start" style="width: 60%; height: 1.5rem"></span>
                    <span class="sk sk-bulat" style="width: 13rem; max-width: 100%; aspect-ratio: 1"></span>
                    <span class="sk" style="width: 70%; height: 1rem"></span>
                </div>
                <div class="pl-panel flex flex-col gap-4">
                    <span class="sk" style="width: 60%; height: 1.5rem"></span>
                    <span class="sk" style="height: 1.5rem"></span>
                    <span class="sk" style="height: 1.5rem"></span>
                </div>
            </div>
        </div>
    </main>

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <h1 class="pl-page-title">Selamat Datang</h1>
                <p class="pl-page-sub">Informasi keadaan kesehatan{{ $psikolog ? ' psikologi' : '' }} {{ strtolower($labelPeriode) }}</p>
            </div>
            <div class="pl-page-actions">
                @include('partials.pilih-bagian', ['aktif' => $bagian])
                <nav class="pl-segment" aria-label="Pilih periode">
                    @foreach (\App\Http\Controllers\DashboardController::PERIODE as $nilai => $label)
                        <a href="{{ request()->fullUrlWithQuery(['periode' => $nilai, 'dari' => null, 'sampai' => null]) }}" @if ($periode === $nilai) aria-current="true" @endif>{{ $label }}</a>
                    @endforeach
                </nav>
                {{-- klik tanggal: kalender popover untuk memilih rentang sendiri (dirender skrip di bawah) --}}
                <div class="pilih-rentang relative">
                    <button type="button" class="pl-date" aria-haspopup="dialog" aria-expanded="false" aria-controls="kal-pop" aria-label="Pilih rentang tanggal, sekarang {{ $rentang }}">
                        <span class="pl-date__icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                        {{-- satu hari = satu tanggal; selain itu = rentang --}}
                        <span><small class="sr-only">{{ $awal->isSameDay($akhir) ? 'Tanggal' : 'Rentang tanggal' }}</small><b>{{ $rentang }}</b></span>
                    </button>
                    <form id="kal-pop" class="kal-pop" role="dialog" aria-label="Kalender rentang tanggal" method="GET" action="{{ route('dashboard') }}"
                        data-dari="{{ $awal->toDateString() }}" data-sampai="{{ $akhir->toDateString() }}" data-hari-ini="{{ today()->toDateString() }}">
                        <input type="hidden" name="dari" value="{{ $awal->toDateString() }}">
                        <input type="hidden" name="sampai" value="{{ $akhir->toDateString() }}">
                        <div class="kal-kepala">
                            <button type="button" class="pl-icon-btn" data-geser="-1" aria-label="Bulan sebelumnya"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 19.5 8.25 12l7.5-7.5"/></svg></button>
                            <span aria-live="polite" data-bulan></span>
                            <button type="button" class="pl-icon-btn" data-geser="1" aria-label="Bulan berikutnya"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg></button>
                        </div>
                        <div class="kal-grid" data-grid></div>
                        <div class="kal-kaki">
                            <small data-keterangan></small>
                            <button type="submit" class="pl-btn pl-btn-primary pl-btn-sm">Terapkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </header>

        <section aria-label="Ringkasan tingkat sakit" class="pl-stats">
            @foreach ($kartu as [$namaKartu, $jumlah, $warna, $namaIkon, $keterangan, $tegas, $selisih, $teksPembanding, $naikBuruk])
                <div class="pl-stat pl-hover" style="--sev: {{ $warna }}">
                    <div class="pl-stat__head">
                        <h2 class="pl-stat__label">{{ $namaKartu }}</h2>
                        <span class="pl-stat__icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $ikon[$namaIkon] }}"/></svg></span>
                    </div>
                    <p class="pl-stat__value" data-hitung="{{ $jumlah }}">{{ $jumlah }}</p>
                    <div class="flex flex-col gap-1">
                        @isset($selisih)
                            <p class="pl-stat__trend {{ $selisih ? (($selisih > 0) === $naikBuruk ? 'pl-stat__trend--buruk' : 'pl-stat__trend--baik') : '' }}">
                                @if ($selisih)
                                    <span aria-hidden="true">{{ $selisih > 0 ? '↑' : '↓' }}</span><span class="sr-only">{{ $selisih > 0 ? 'Naik' : 'Turun' }}</span>
                                    {{ abs($selisih) }} {{ $teksPembanding }}
                                @else
                                    Tidak berubah {{ $teksPembanding }}
                                @endif
                            </p>
                        @endisset
                        <p class="pl-stat__meta">{{ $keterangan }}</p>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- kolom kiri & kanan sama tinggi: panel terakhir tiap kolom (flex-1) mengisi sisa ruang --}}
        <div class="pl-row" style="align-items: stretch">

            <div class="flex flex-col gap-6" style="flex: 2 1 30rem">
            <section class="pl-panel pl-hover">
                <div class="pl-section__head">
                    <h2 class="pl-panel__title">{{ $judul['tingkat'] }}</h2>
                    <ul class="pl-legend">
                        @foreach ($kategori as $i => $nama)
                            <li style="--ring: {{ $warnaSakit[$i] }}">{{ $nama }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="grup-tip flex flex-col gap-3" role="img"
                    aria-label="@foreach ($perTingkat as $t => $v){{ $t }}: @foreach ($v as $i => $n){{ $n }} {{ strtolower($kategori[$i]) }}{{ $loop->last ? '.' : ',' }} @endforeach @endforeach">
                    {{-- Batang bertumpuk (Ringan|Sedang|Berat dalam satu batang) di atas sumbu tetap 0 – $sumbu, garis bantu tiap tik --}}
                    @foreach ($perTingkat as $tingkat => $nilai)
                        <div class="grid grid-cols-[5.5rem_1fr_2rem] items-center gap-3 label">
                            <span>{{ $tingkat }}</span>
                            <div class="relative h-8" style="background: repeating-linear-gradient(90deg, var(--border) 0 1px, transparent 1px {{ 100 / 5 }}%)">
                                <div class="anim-batang flex h-full" style="--i: {{ $loop->index }}; width: {{ array_sum($nilai) / $sumbu * 100 }}%">
                                    @php $persenTingkat = $persen($nilai); @endphp
                                    @foreach ($nilai as $i => $v)
                                        @if ($v > 0)<span class="seg" tabindex="0" data-judul="{{ $tingkat }} · {{ $kategori[$i] }}" data-isi="{{ $v }} taruna ({{ $persenTingkat[$i] }}% dari {{ $tingkat }})" data-warna="{{ $warnaSakit[$i] }}" style="flex: {{ $v }}; background: {{ $warnaSakit[$i] }}">{{ $v }}</span>@endif
                                    @endforeach
                                </div>
                            </div>
                            <span class="anim-muncul text-right tabular-nums" style="--d: {{ 700 + $loop->index * 120 }}ms">{{ array_sum($nilai) }}</span>
                        </div>
                    @endforeach
                    <div class="grid grid-cols-[5.5rem_1fr_2rem] gap-3 text-muted tabular-nums" style="font-size: max(12px, .75rem)" aria-hidden="true">
                        <span></span>
                        <div class="relative h-4">
                            @foreach ($tikSumbu as $tik)
                                <span class="absolute -translate-x-1/2" style="left: {{ $tik / $sumbu * 100 }}%">{{ $tik }}</span>
                            @endforeach
                        </div>
                    </div>
                    <p class="m-0 text-muted" style="font-size: max(12px, .75rem)">Jumlah taruna · warna dalam satu batang = {{ $psikolog ? 'status konseling' : 'tingkat keparahan' }}</p>
                </div>
            </section>

            {{-- Keluhan yang dicatat hari ini (tidak ikut periode) --}}
            <section class="pl-panel pl-hover flex-1">
                <div class="pl-section__head">
                    <div class="flex flex-col gap-1">
                        <h2 class="pl-panel__title">Keluhan Baru Hari Ini</h2>
                        <span class="label text-muted">{{ $keluhanBaru->count() }} keluhan · terbaru di atas</span>
                    </div>
                    @if ($keluhanBaru->count() > 5)
                        <div class="pl-segment" role="group" aria-label="Jumlah keluhan yang ditampilkan" data-filter-keluhan>
                            <button type="button" data-batas="5" aria-pressed="false">5 terbaru</button>
                            <button type="button" data-batas="semua" aria-pressed="true">Semua ({{ $keluhanBaru->count() }})</button>
                        </div>
                    @endif
                </div>
                @if ($keluhanBaru->isEmpty())
                    <p class="m-0 py-8 text-center text-muted label">Belum ada keluhan baru hari ini.</p>
                @else
                    <ul class="daftar-keluhan m-0 flex list-none flex-col gap-2 p-0" data-batas="semua" tabindex="0" aria-label="Daftar keluhan baru">
                        @foreach ($keluhanBaru as $k)
                            <li style="animation-delay: {{ min($loop->index, 5) * 50 }}ms">
                                <a href="{{ $psikolog ? route('laporan-kesehatan.psikologi.show', $k) : route('laporan-kesehatan.show', $k) }}">
                                    <span class="pl-avatar pl-avatar--sm" aria-hidden="true">{{ collect(explode(' ', $k->taruna->nama))->take(2)->map(fn ($kata) => mb_substr($kata, 0, 1))->implode('') }}</span>
                                    <span class="flex min-w-0 flex-1 flex-col gap-0.5">
                                        <b class="truncate font-semibold">{{ $k->taruna->nama }}</b>
                                        <span class="truncate text-muted" style="font-size: max(14px, .875rem)">{{ $k->keluhan }}</span>
                                        <span class="text-muted tabular-nums" style="font-size: max(12px, .75rem)">NPM {{ $k->taruna->npm }} · Tingkat {{ $k->taruna->tingkat }}</span>
                                    </span>
                                    <span class="flex flex-none flex-col items-end gap-1.5">
                                        @if ($psikolog)
                                            <span class="pl-badge">{{ $k->keterangan() }}</span>
                                        @else
                                            <span class="pl-badge {{ ['Ringan' => 'pl-badge-primary', 'Sedang' => 'pl-badge-notice', 'Berat' => 'pl-badge-accent'][$k->status] ?? 'pl-badge-primary' }}">{{ $k->status }}</span>
                                        @endif
                                        <span class="text-muted tabular-nums" style="font-size: max(12px, .75rem)">{{ $k->created_at->format('H:i') }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
            </div>

            <div class="flex flex-col gap-6" style="flex: 1 1 20rem">
            <section class="pl-panel pl-hover">
                <h2 class="pl-panel__title">{{ $judul['jk'] }}</h2>
                {{-- Dua kategori dibandingkan dengan batang sejajar (bukan gauge); panjang = bagian dari total --}}
                <div class="grup-tip flex flex-1 flex-col justify-center gap-4 label" role="img"
                    aria-label="@foreach ($jenisKelamin as $jk => $n){{ $jk }} {{ $n }} taruna ({{ $persenJk[$jk] }}%), @endforeach total {{ $totalJk }}">
                    @foreach ($jenisKelamin as $jk => $n)
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center gap-2">
                                {{-- simbol ♂ / ♀ dengan warna batangnya --}}
                                <svg class="h-[1.125rem] w-[1.125rem] flex-none" viewBox="0 0 24 24" fill="none" stroke="{{ $warnaJk[$jk] }}" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    @if ($loop->first)
                                        <circle cx="10" cy="14" r="5.5"/><path d="M14 10 20 4m-5 0h5v5"/>
                                    @else
                                        <circle cx="12" cy="9" r="5.5"/><path d="M12 14.5V21m-3.5-3h7"/>
                                    @endif
                                </svg>
                                <span class="flex-1">{{ $jk }}</span>
                                <strong class="font-semibold tabular-nums">{{ $n }}</strong>
                                <span class="w-11 text-right text-muted tabular-nums">{{ $persenJk[$jk] }}%</span>
                            </div>
                            <div class="h-3 rounded-full" style="background: var(--surface-card)">
                                <div class="anim-batang h-full rounded-full" tabindex="0" data-judul="{{ $jk }}" data-isi="{{ $n }} taruna ({{ $persenJk[$jk] }}%)" data-warna="{{ $warnaJk[$jk] }}"
                                    style="--i: {{ $loop->index }}; width: {{ $persenJk[$jk] }}%; background: {{ $warnaJk[$jk] }}"></div>
                            </div>
                        </div>
                    @endforeach
                    <p class="m-0 text-muted">Total <strong class="font-semibold tabular-nums" style="color: var(--text)" data-hitung="{{ $totalJk }}">{{ $totalJk }}</strong> taruna</p>
                </div>
            </section>

            <section class="pl-panel pl-hover flex flex-1 flex-col">
                <h2 class="pl-panel__title">{{ $judul['banding'] }}</h2>
                <div class="flex flex-1 flex-col items-center justify-center gap-5">
                    <svg viewBox="0 0 320 320" class="w-full max-w-[22rem]" role="img"
                        aria-label="{{ $totalBanding ? $namaBanding['Sembuh'].' '.$persenBanding['Sembuh'].' persen, '.strtolower($namaBanding['Sakit']).' '.$persenBanding['Sakit'].' persen' : 'Belum ada data taruna' }}">
                        @if (! $totalBanding)
                            <circle cx="160" cy="160" r="150" fill="none" stroke="var(--neutral-200)" stroke-width="2"/>
                            <text x="160" y="160" text-anchor="middle" dominant-baseline="middle" font-family="Instrument Sans, sans-serif" font-size="16" fill="var(--text-muted)">Belum ada data</text>
                        @else
                            <g class="anim-tumbuh grup-tip" style="transform-origin: 160px 160px">
                                @foreach ($segBanding as $k => [$a0, $a1])
                                    @php
                                        [$dx, $dy] = $titik(0, 0, 8, ($a0 + $a1) / 2);
                                        $tipBanding = 'data-judul="'.$namaBanding[$k].'" data-isi="'.$perbandingan[$k].' taruna ('.$persenBanding[$k].'%)" data-warna="'.$warnaBanding[$k].'"';
                                    @endphp
                                    @if ($a1 - $a0 >= 359.9)
                                        {{-- satu kategori 100%: path irisan 360° tidak tergambar, pakai lingkaran penuh --}}
                                        <circle class="irisan" tabindex="0" {!! $tipBanding !!} cx="160" cy="160" r="150" fill="{{ $warnaBanding[$k] }}"/>
                                    @else
                                        <path class="irisan" tabindex="0" {!! $tipBanding !!} style="--dx: {{ $dx }}px; --dy: {{ $dy }}px" d="{{ $irisan(160, 160, 150, $a0, $a1) }}" fill="{{ $warnaBanding[$k] }}" stroke="var(--cloud)" stroke-width="3" stroke-linejoin="round"/>
                                    @endif
                                @endforeach
                            </g>
                            <g class="anim-muncul pointer-events-none" style="--d: 600ms" font-family="Instrument Sans, sans-serif" font-weight="600" font-size="28" fill="#141612" text-anchor="middle" dominant-baseline="central">
                                @foreach ($segBanding as $k => [$a0, $a1])
                                    {{-- irisan di bawah 10% terlalu sempit untuk angka; persennya tetap ada di legenda --}}
                                    @continue($a1 - $a0 < 36)
                                    @php [$lx, $ly] = $a1 - $a0 >= 359.9 ? [160, 160] : $titik(160, 160, 92, ($a0 + $a1) / 2); @endphp
                                    <text x="{{ $lx }}" y="{{ $ly }}">{{ $persenBanding[$k] }}%</text>
                                @endforeach
                            </g>
                        @endif
                    </svg>
                    <div class="anim-muncul flex flex-wrap justify-center gap-x-6 gap-y-2 label" style="--d: 700ms">
                        @if (! $totalBanding)
                            <span class="text-muted">Belum ada data taruna.</span>
                        @else
                            {{-- legenda selalu memuat persen & jumlah: irisan < 10% tidak diberi label di dalam pie --}}
                            @foreach ($warnaBanding as $k => $w)
                                <span class="flex items-center gap-2"><span class="sw sw-kotak" style="background: {{ $w }}"></span>{{ $namaBanding[$k] }} ({{ $persenBanding[$k] }}%) · {{ $perbandingan[$k] }} taruna</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </section>
            </div>
        </div>
    </main>

    <div id="tip" role="tooltip"></div>

    <script>
        // Tooltip grafik: elemen ber-[data-judul] (data-judul, data-isi, data-warna); ikut kursor, muncul juga saat fokus keyboard
        (() => {
            const tip = document.getElementById('tip');
            const taruh = (x, y) => {
                const w = tip.offsetWidth, h = tip.offsetHeight;
                tip.style.transform = `translate(${Math.min(x + 14, innerWidth - w - 8)}px, ${y - h - 14 < 8 ? y + 18 : y - h - 14}px)`;
            };
            const tampil = (el, x, y) => {
                const judul = document.createElement('small'), isi = document.createElement('b');
                judul.textContent = el.dataset.judul;
                isi.textContent = el.dataset.isi;
                tip.replaceChildren(judul, isi);
                tip.classList.add('on');
                taruh(x, y);
            };
            document.addEventListener('mousemove', (e) => {
                const el = e.target.closest('[data-judul]');
                el ? tampil(el, e.clientX, e.clientY) : tip.classList.remove('on');
            });
            document.addEventListener('focusin', (e) => {
                const el = e.target.closest('[data-judul]');
                if (el) { const r = el.getBoundingClientRect(); tampil(el, r.left + r.width / 2, r.top); }
            });
            document.addEventListener('focusout', () => tip.classList.remove('on'));
            document.addEventListener('scroll', () => tip.classList.remove('on'), true);
        })();

        // Kalender rentang: klik pertama = awal, klik kedua = akhir (klik sebelum awal = awal baru); tanggal setelah hari ini dinonaktifkan
        (() => {
            const pop = document.getElementById('kal-pop'), tombol = document.querySelector('.pilih-rentang > .pl-date');
            const grid = pop.querySelector('[data-grid]'), judulBulan = pop.querySelector('[data-bulan]'), ket = pop.querySelector('[data-keterangan]');
            const kirim = pop.querySelector('[type="submit"]');
            const baca = (s) => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
            const iso = (d) => [d.getFullYear(), String(d.getMonth() + 1).padStart(2, '0'), String(d.getDate()).padStart(2, '0')].join('-');
            const singkat = (d) => d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            const hariIni = baca(pop.dataset.hariIni);
            let awal = baca(pop.dataset.dari), akhir = baca(pop.dataset.sampai), lihat = new Date(akhir.getFullYear(), akhir.getMonth(), 1);

            const gambar = () => {
                judulBulan.textContent = lihat.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                pop.querySelector('[data-geser="1"]').disabled = lihat >= new Date(hariIni.getFullYear(), hariIni.getMonth(), 1);
                const isi = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'].map((h) => Object.assign(document.createElement('span'), { textContent: h }));
                for (let i = (lihat.getDay() + 6) % 7; i > 0; i--) { isi.push(document.createElement('span')); }
                const jumlahHari = new Date(lihat.getFullYear(), lihat.getMonth() + 1, 0).getDate();
                for (let t = 1; t <= jumlahHari; t++) {
                    const d = new Date(lihat.getFullYear(), lihat.getMonth(), t), b = document.createElement('button');
                    b.type = 'button';
                    b.dataset.tanggal = iso(d);
                    b.disabled = d > hariIni;
                    b.classList.toggle('hari-ini', +d === +hariIni);
                    b.classList.toggle('awal', +d === +awal);
                    b.classList.toggle('akhir', akhir && +d === +akhir);
                    b.classList.toggle('antara', akhir && d > awal && d < akhir);
                    b.setAttribute('aria-pressed', String(+d === +awal || (akhir && +d === +akhir)));
                    b.setAttribute('aria-label', d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
                    b.append(Object.assign(document.createElement('span'), { textContent: t }));
                    isi.push(b);
                }
                grid.replaceChildren(...isi);
                ket.textContent = akhir ? (+awal === +akhir ? singkat(awal) : `${singkat(awal)} – ${singkat(akhir)}`) : 'Pilih tanggal akhir';
                kirim.disabled = !akhir;
                pop.elements.dari.value = iso(awal);
                pop.elements.sampai.value = akhir ? iso(akhir) : '';
            };

            grid.addEventListener('click', (e) => {
                const b = e.target.closest('button[data-tanggal]');
                if (!b) { return; }
                const d = baca(b.dataset.tanggal);
                if (akhir || d < awal) { awal = d; akhir = null; } else { akhir = d; }
                gambar();
            });
            pop.querySelectorAll('[data-geser]').forEach((b) => b.addEventListener('click', () => {
                lihat = new Date(lihat.getFullYear(), lihat.getMonth() + Number(b.dataset.geser), 1);
                gambar();
            }));

            const atur = (buka) => {
                pop.classList.toggle('on', buka);
                tombol.setAttribute('aria-expanded', String(buka));
                if (buka) { gambar(); }
            };
            tombol.addEventListener('click', () => atur(!pop.classList.contains('on')));
            // composedPath: tombol tanggal yang diklik sudah diganti gambar(), jadi target tidak lagi di dalam dokumen
            document.addEventListener('click', (e) => { if (!e.composedPath().includes(tombol.parentElement)) { atur(false); } });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && pop.classList.contains('on')) { atur(false); tombol.focus(); } });
            gambar();
        })();

        // Filter daftar keluhan baru: lima teratas atau semuanya. Tinggi daftar beranimasi halus (FLIP):
        // buka = tinggi bertambah lalu kartu tambahan muncul berurutan; tutup = kartu tambahan memudar dulu lalu tinggi menyusut.
        document.querySelector('[data-filter-keluhan]')?.addEventListener('click', async (e) => {
            const b = e.target.closest('button[data-batas]'), daftar = document.querySelector('.daftar-keluhan');
            if (!b || b.getAttribute('aria-pressed') === 'true') { return; }
            b.parentElement.querySelectorAll('button').forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
            const lebih = [...daftar.children].slice(5), halus = !matchMedia('(prefers-reduced-motion: reduce)').matches;
            const gerak = { duration: 320, easing: 'cubic-bezier(.2, .7, .2, 1)' };

            if (halus && b.dataset.batas === '5') {
                await Promise.all(lebih.map((li) => li.animate([{ opacity: 1 }, { opacity: 0, transform: 'translateY(.25rem)' }], { duration: 160, fill: 'forwards' }).finished));
                // pengguna sudah klik "Semua" lagi selama memudar: batalkan penutupan
                if (b.getAttribute('aria-pressed') !== 'true') { lebih.forEach((li) => li.getAnimations().forEach((a) => a.cancel())); return; }
            }
            const dari = daftar.offsetHeight;
            daftar.dataset.batas = b.dataset.batas;
            lebih.forEach((li) => li.getAnimations().forEach((a) => a.cancel()));
            if (!halus) { return; }
            const ke = daftar.offsetHeight;
            daftar.style.overflow = 'hidden';
            daftar.animate([{ height: `${dari}px` }, { height: `${ke}px` }], gerak).finished.then(() => { daftar.style.overflow = ''; });
            if (b.dataset.batas === 'semua') {
                lebih.forEach((li, i) => li.animate([{ opacity: 0, transform: 'translateY(.5rem)' }, { opacity: 1, transform: 'none' }], { ...gerak, delay: 80 + Math.min(i, 8) * 40, fill: 'backwards' }));
            }
        });

        // Angka menghitung naik dari 0 (dilewati bila pengguna memilih kurangi gerakan)
        const hitung = () => {
            if (matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
            document.querySelectorAll('[data-hitung]').forEach((el) => {
                const akhir = Number(el.dataset.hitung), mulai = performance.now(), durasi = 900;
                const langkah = (t) => {
                    const p = Math.min((t - mulai) / durasi, 1);
                    el.textContent = Math.round(akhir * (1 - Math.pow(1 - p, 3)));
                    if (p < 1) requestAnimationFrame(langkah);
                };
                requestAnimationFrame(langkah);
            });
        };

        // Buka isi halaman setelah semua aset dimuat (cadangan 4 detik); animasi grafik & hitung angka baru mulai saat ini
        let terbuka = false;
        // Ukur tinggi 5 kartu pertama daftar keluhan (dipakai batas tinggi mode "Semua"); diulang saat ukuran layar berubah
        const ukurDaftar = () => {
            const daftar = document.querySelector('.daftar-keluhan');
            if (!daftar || daftar.children.length <= 5) { return; }
            const tinggi = daftar.children[4].getBoundingClientRect().bottom - daftar.children[0].getBoundingClientRect().top;
            daftar.style.setProperty('--tinggi-lima', `${tinggi}px`);
        };
        addEventListener('resize', ukurDaftar);

        const buka = () => {
            if (terbuka) { return; }
            terbuka = true;
            document.documentElement.classList.remove('memuat');
            document.body.removeAttribute('aria-busy');
            hitung();
            ukurDaftar();
        };
        document.readyState === 'complete' ? buka() : addEventListener('load', buka);
        setTimeout(buka, 4000);
    </script>
</body>
</html>
