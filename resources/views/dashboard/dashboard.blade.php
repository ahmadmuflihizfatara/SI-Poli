{{-- dashboard/dashboard.blade.php — susunan dari Prototype SI Kesehatan Taruna 2 (Pulih), gaya komponen dari desain Dashboard (PNG/SVG awal) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Dashboard'])
    <style>
        .db-judul { margin: 0; font: 600 1.375rem/1.875rem var(--font-heading); color: var(--primary-700); }
        .db-h2 { margin: 0; font: 600 1.25rem/1.75rem var(--font-heading); color: var(--primary-700); }
        .db-num { font-family: var(--font-heading); font-weight: 600; color: var(--primary-900); font-variant-numeric: tabular-nums; }
        .sw { width: .75rem; height: .75rem; border-radius: 9999px; flex-shrink: 0; }
        .sw-kotak { border-radius: 3px; }

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
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $warnaSakit = ['#6fa89d', '#c88a1e', '#d8693f']; // ringan, sedang, berat
        $maksTingkat = max(array_map('array_sum', $perTingkat)) ?: 1;

        // Titik [x, y] di lingkaran untuk sudut (derajat, searah jarum jam dari arah jam 3)
        $titik = fn ($cx, $cy, $r, $deg) => [round($cx + $r * cos(deg2rad($deg)), 1), round($cy + $r * sin(deg2rad($deg)), 1)];

        // Irisan pie (r0 = 0) atau cincin (r0 > 0) dari sudut a0 ke a1. Digambar sebagai <path> berukuran pasti,
        // bukan trik stroke-dasharray + pathLength yang skalanya tidak konsisten antar-browser (irisan jadi meleset).
        $irisan = function ($cx, $cy, $r, $r0, $a0, $a1) use ($titik) {
            $besar = $a1 - $a0 > 180 ? 1 : 0;
            [$x1, $y1] = $titik($cx, $cy, $r, $a0);
            [$x2, $y2] = $titik($cx, $cy, $r, $a1);
            if (! $r0) {
                return "M{$cx} {$cy} L{$x1} {$y1} A{$r} {$r} 0 {$besar} 1 {$x2} {$y2} Z";
            }
            [$x3, $y3] = $titik($cx, $cy, $r0, $a1);
            [$x4, $y4] = $titik($cx, $cy, $r0, $a0);

            return "M{$x1} {$y1} A{$r} {$r} 0 {$besar} 1 {$x2} {$y2} L{$x3} {$y3} A{$r0} {$r0} 0 {$besar} 0 {$x4} {$y4} Z";
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

        // Setengah donat jenis kelamin: 180° (kiri) → 360° (kanan) lewat atas
        $totalJk = array_sum($jenisKelamin);
        $persenJk = $persen($jenisKelamin);
        $warnaJk = array_combine(array_keys($jenisKelamin), ['#6fa89d', '#d8693f']);
        $segJk = $segmen($jenisKelamin, 180, 180);

        // Pie perbandingan: Sakit mulai arah jam 3 searah jarum jam, lalu Sembuh (sesuai desain)
        $totalBanding = array_sum($perbandingan);
        $persenBanding = $persen($perbandingan);
        $warnaBanding = ['Sembuh' => '#6fa89d', 'Sakit' => '#d8693f'];
        $segBanding = $segmen(['Sakit' => $perbandingan['Sakit'], 'Sembuh' => $perbandingan['Sembuh']], 0, 360);

        $labelPeriode = \App\Http\Controllers\DashboardController::PERIODE[$periode];
        $fmt = fn ($d, $pola) => $d->locale('id')->translatedFormat($pola);
        $rentang = match (true) {
            $awal->isToday() => $fmt(today(), 'd F Y'),
            $awal->isSameMonth(today()) => $fmt($awal, 'j').' – '.$fmt(today(), 'd F Y'),
            $awal->isSameYear(today()) => $fmt($awal, 'd F').' – '.$fmt(today(), 'd F Y'),
            default => $fmt($awal, 'd F Y').' – '.$fmt(today(), 'd F Y'),
        };

        $kartu = [
            ['judul' => 'Sakit Ringan', 'jumlah' => $ringan, 'teks' => 'text-primary-700',   'latar' => 'bg-primary-50',   'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
            ['judul' => 'Sakit Sedang', 'jumlah' => $sedang, 'teks' => 'text-tertiary-800',  'latar' => 'bg-tertiary-50',  'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z'],
            ['judul' => 'Sakit Berat',  'jumlah' => $berat,  'teks' => 'text-secondary-800', 'latar' => 'bg-secondary-50', 'icon' => 'M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ];
    @endphp

    {{-- lg:h-screen + flex-1/min-h-0: grafik mengisi sisa tinggi sehingga halaman tidak perlu scroll.
         ml = tepi kanan sidebar (5rem, terbuka 15rem) + celah 1.25rem. --}}
    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen lg:h-screen flex flex-col gap-6 pr-10 pt-6 pb-4">

        <header class="flex flex-wrap justify-between items-start gap-6">
            <div class="flex flex-col gap-3">
                <div class="flex flex-col gap-1">
                    <h1 class="m-0 font-heading font-bold text-[2.25rem] leading-[2.75rem] text-primary-900">Selamat Datang</h1>
                    <p class="body m-0">Informasi keadaan kesehatan {{ strtolower($labelPeriode) }}</p>
                </div>
                <form method="GET" action="{{ route('dashboard') }}">
                    <label class="flex items-center gap-3" for="periode">
                        <span class="flex items-center gap-1.5 label">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                            Filter
                        </span>
                        <span class="relative">
                            <select id="periode" name="periode" onchange="this.form.submit()" class="appearance-none h-[2.4rem] w-[11.25rem] rounded-full bg-primary-700 hover:bg-primary-900 text-white label text-center pl-6 pr-10 shadow-[0_4px_4px_rgba(0,0,0,0.25)] cursor-pointer transition-colors focus-visible:outline-none focus-visible:shadow-[var(--focus-ring)]">
                                @foreach (\App\Http\Controllers\DashboardController::PERIODE as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected($periode === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m0 0 6.75-6.75M12 19.5l-6.75-6.75"/></svg>
                        </span>
                    </label>
                    <noscript><button type="submit" class="pl-btn pl-btn-ghost h-9 mt-2">Terapkan</button></noscript>
                </form>
            </div>
            <div class="pl-card flex items-center gap-4 px-6 py-3 w-80">
                <svg class="w-10 h-10 text-primary-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                <div class="flex flex-col gap-0.5">
                    <span class="body-sm font-medium text-muted">Tanggal / rentang tanggal</span>
                    <span class="h3 text-primary-900">{{ $rentang }}</span>
                </div>
            </div>
        </header>

        <section aria-label="Ringkasan tingkat sakit" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach ($kartu as $k)
                <div class="pl-card px-6 py-5 flex flex-col gap-3">
                    <h2 class="db-h2 {{ $k['teks'] }}">{{ $k['judul'] }}</h2>
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-[0.625rem] flex items-center justify-center {{ $k['latar'] }} {{ $k['teks'] }}">
                            <svg class="w-[1.875rem] h-[1.875rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $k['icon'] }}"/></svg>
                        </div>
                        <span class="db-num text-[2.75rem] leading-[3.25rem]" data-hitung="{{ $k['jumlah'] }}">{{ $k['jumlah'] }}</span>
                        <span class="text-sm text-muted self-end mb-2">taruna</span>
                    </div>
                </div>
            @endforeach
        </section>

        <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] gap-x-12 gap-y-6">

            {{-- Kolom kiri --}}
            <div class="min-h-0 flex flex-col gap-5">
                <section class="flex flex-col gap-4 px-1">
                    <div class="flex flex-wrap justify-between items-center gap-2">
                        <h2 class="db-judul">Taruna Sakit Per Tingkat</h2>
                        <div class="flex gap-4 body-sm text-muted">
                            @foreach (['Ringan', 'Sedang', 'Berat'] as $i => $nama)
                                <span class="flex items-center gap-2"><span class="sw" style="background: {{ $warnaSakit[$i] }}"></span>{{ $nama }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex flex-col gap-3" role="img"
                        aria-label="@foreach ($perTingkat as $t => $v){{ $t }}: {{ $v[0] }} ringan, {{ $v[1] }} sedang, {{ $v[2] }} berat. @endforeach">
                        @foreach ($perTingkat as $tingkat => $nilai)
                            <div class="grid grid-cols-[6.5rem_1fr] items-center">
                                <span class="body-lg">{{ $tingkat }}</span>
                                <div class="flex items-center gap-3">
                                    {{-- ponytail: lebar batang = total / total terbesar x 85%, sisa ruang untuk angka total --}}
                                    <div class="anim-batang flex h-7" style="--i: {{ $loop->index }}; width: {{ array_sum($nilai) / $maksTingkat * 85 }}%">
                                        @foreach ($nilai as $i => $v)
                                            @if ($v > 0)<span class="opacity-90" style="flex: {{ $v }}; background: {{ $warnaSakit[$i] }}"></span>@endif
                                        @endforeach
                                    </div>
                                    <span class="anim-muncul body text-muted tabular-nums" style="--d: {{ 700 + $loop->index * 120 }}ms">{{ array_sum($nilai) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <div class="grid grid-cols-2 gap-5">
                    <div class="pl-card px-6 py-5 flex flex-col gap-1">
                        <h2 class="db-h2">Sembuh</h2>
                        <p class="body-sm m-0 text-muted">Dinyatakan sembuh {{ strtolower($labelPeriode) }}</p>
                        <span class="db-num text-[2.5rem] leading-[3rem] mt-1" data-hitung="{{ $sembuh }}">{{ $sembuh }}</span>
                    </div>
                    <div class="pl-card px-6 py-5 flex flex-col gap-1">
                        <h2 class="db-h2">Isoman</h2>
                        <p class="body-sm m-0 text-muted">Sedang melaksanakan isolasi mandiri</p>
                        <span class="db-num text-[2.5rem] leading-[3rem] mt-1" data-hitung="{{ $isoman }}">{{ $isoman }}</span>
                    </div>
                </div>

                <section class="flex-1 min-h-0 flex flex-col gap-3 px-1">
                    <h2 class="db-judul">Taruna Sakit Berdasarkan Jenis Kelamin</h2>
                    <div class="flex-1 min-h-0 flex items-center justify-center gap-12">
                        <svg viewBox="0 0 280 145" class="h-full max-h-[10rem] w-auto max-w-[19rem]" role="img"
                            aria-label="@foreach ($jenisKelamin as $jk => $n){{ $jk }} {{ $n }} taruna ({{ $persenJk[$jk] }}%), @endforeach total {{ $totalJk }}">
                            <g class="anim-tumbuh" style="transform-origin: 140px 140px">
                                @forelse ($segJk as $jk => [$a0, $a1])
                                    <path d="{{ $irisan(140, 140, 132, 88, $a0, $a1) }}" fill="{{ $warnaJk[$jk] }}" stroke="#ffffff" stroke-width="2" stroke-linejoin="round"/>
                                @empty
                                    <path d="{{ $irisan(140, 140, 132, 88, 180, 360) }}" fill="var(--neutral-200)"/>
                                @endforelse
                            </g>
                            <text x="140" y="138" text-anchor="middle" font-family="Open Sans, sans-serif" font-weight="300" font-size="56" fill="#232620" data-hitung="{{ $totalJk }}">{{ $totalJk }}</text>
                        </svg>
                        <div class="anim-muncul flex flex-col gap-2.5 min-w-[11.25rem] text-sm" style="--d: 700ms">
                            @foreach ($jenisKelamin as $jk => $n)
                                <div class="flex items-center gap-2">
                                    <span class="sw" style="background: {{ $warnaJk[$jk] }}"></span>
                                    <span class="flex-1">{{ $jk }}</span>
                                    <strong class="font-semibold">{{ $n }}</strong>
                                    <span class="w-11 text-right text-muted">{{ $persenJk[$jk] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>

            {{-- Kolom kanan --}}
            <section class="pl-card min-h-0 px-6 py-5 flex flex-col gap-4">
                <h2 class="db-h2 text-center">Perbandingan Kesehatan Taruna</h2>
                <div class="flex-1 min-h-0 flex flex-col items-center justify-center gap-6">
                    <svg viewBox="0 0 320 320" class="w-full max-w-[20rem] min-h-0 flex-1 max-h-[20rem]" role="img"
                        aria-label="{{ $totalBanding ? 'Sembuh '.$persenBanding['Sembuh'].' persen, sakit '.$persenBanding['Sakit'].' persen' : 'Belum ada data taruna' }}">
                        @if (! $totalBanding)
                            <circle cx="160" cy="160" r="150" fill="none" stroke="var(--neutral-200)" stroke-width="2"/>
                            <text x="160" y="160" text-anchor="middle" dominant-baseline="middle" font-family="Open Sans, sans-serif" font-size="16" fill="var(--text-muted)">Belum ada data</text>
                        @else
                            <g class="anim-tumbuh" style="transform-origin: 160px 160px">
                                @foreach ($segBanding as $k => [$a0, $a1])
                                    @if ($a1 - $a0 >= 359.9)
                                        {{-- satu kategori 100%: path irisan 360° tidak tergambar, pakai lingkaran penuh --}}
                                        <circle cx="160" cy="160" r="150" fill="{{ $warnaBanding[$k] }}"/>
                                    @else
                                        <path d="{{ $irisan(160, 160, 150, 0, $a0, $a1) }}" fill="{{ $warnaBanding[$k] }}" stroke="#ffffff" stroke-width="2" stroke-linejoin="round"/>
                                    @endif
                                @endforeach
                            </g>
                            <g class="anim-muncul" style="--d: 600ms" font-family="Montserrat, sans-serif" font-weight="600" font-size="28" fill="#232620" text-anchor="middle" dominant-baseline="central">
                                @foreach ($segBanding as $k => [$a0, $a1])
                                    {{-- irisan di bawah 10% terlalu sempit untuk angka; persennya tetap ada di legenda --}}
                                    @continue($a1 - $a0 < 36)
                                    @php [$lx, $ly] = $a1 - $a0 >= 359.9 ? [160, 160] : $titik(160, 160, 92, ($a0 + $a1) / 2); @endphp
                                    <text x="{{ $lx }}" y="{{ $ly }}">{{ $persenBanding[$k] }}%</text>
                                @endforeach
                            </g>
                        @endif
                    </svg>
                    <div class="anim-muncul flex gap-8 text-sm" style="--d: 700ms">
                        @if (! $totalBanding)
                            <span class="text-muted">Belum ada data taruna.</span>
                        @else
                            @foreach ($warnaBanding as $k => $w)
                                <span class="flex items-center gap-2"><span class="sw sw-kotak" style="background: {{ $w }}"></span>{{ $k }} ({{ $persenBanding[$k] }}%)</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script>
        // Angka menghitung naik dari 0 (dilewati bila pengguna memilih kurangi gerakan)
        if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.querySelectorAll('[data-hitung]').forEach((el) => {
                const akhir = Number(el.dataset.hitung), mulai = performance.now(), durasi = 900;
                const langkah = (t) => {
                    const p = Math.min((t - mulai) / durasi, 1);
                    el.textContent = Math.round(akhir * (1 - Math.pow(1 - p, 3)));
                    if (p < 1) requestAnimationFrame(langkah);
                };
                requestAnimationFrame(langkah);
            });
        }
    </script>
</body>
</html>
