{{-- dashboard/dashboard.blade.php — tata letak "Dasbor" design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Dashboard'])
    <style>
        .sw { width: .75rem; height: .75rem; border-radius: 9999px; flex-shrink: 0; }
        .sw-kotak { border-radius: 3px; }

        /* StackBar Pagi: segmen membulat, angka di dalam segmen */
        .seg { min-width: 1.75rem; display: grid; place-items: center; border-radius: .5rem; font: 500 .75rem/1 var(--font-sans); color: #232620; }

        /* Grafik interaktif: elemen ber-[data-judul] menyorot sendiri & meredupkan saudaranya */
        [data-judul] { cursor: pointer; outline: none; transition: opacity .2s, transform .25s cubic-bezier(.2, .7, .2, 1), filter .2s; }
        [data-judul]:hover, [data-judul]:focus-visible { filter: brightness(1.08) saturate(1.1); }
        .grup-tip:has([data-judul]:hover, [data-judul]:focus-visible) [data-judul]:not(:hover, :focus-visible) { opacity: .45; }
        .irisan:hover, .irisan:focus-visible { transform: translate(var(--dx, 0px), var(--dy, 0px)); }
        .seg:hover, .seg:focus-visible { transform: scaleY(1.18); }

        /* Tooltip grafik */
        #tip { position: fixed; z-index: 50; left: 0; top: 0; pointer-events: none; opacity: 0; transition: opacity .12s; max-width: 16rem;
               background: var(--primary-900); color: #fff; border-radius: 1rem; padding: .625rem .875rem; box-shadow: var(--shadow-lg);
               font: 400 .8125rem/1.3 var(--font-sans); }
        #tip.on { opacity: 1; }
        #tip b { display: flex; align-items: center; gap: .5rem; font: 600 .8125rem/1.3 var(--font-sans); }
        #tip i { width: .625rem; height: .625rem; border-radius: 9999px; flex-shrink: 0; border: 1px solid rgba(255, 255, 255, .7); }

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
        // Isi menyesuaikan bagian: perawat (keluhan medis) atau psikolog (konseling), lihat DashboardController
        $psikolog = $bagian === 'psikolog';
        $kategori = $psikolog ? ['Masih konseling', 'Selesai konseling'] : ['Ringan', 'Sedang', 'Berat'];
        $warnaSakit = $psikolog ? ['#c88a1e', '#6fa89d'] : ['#6fa89d', '#c88a1e', '#d8693f'];
        $judul = $psikolog
            ? ['tingkat' => 'Taruna Keluhan Psikologi Per Tingkat', 'jk' => 'Taruna Konseling Berdasarkan Jenis Kelamin', 'banding' => 'Perbandingan Kesehatan Psikologi Taruna']
            : ['tingkat' => 'Taruna Sakit Per Tingkat', 'jk' => 'Taruna Sakit Berdasarkan Jenis Kelamin', 'banding' => 'Perbandingan Kesehatan Taruna'];
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

        // StatTile Pagi: [judul, jumlah, warna titik (null = tanpa chip), keterangan, tile tegas]
        $kartu = $psikolog ? [
            ['Taruna yang Menyatakan Keluhan', $menyatakan, 'var(--primary-700)', 'taruna', false],
            ['Taruna yang Melanjutkan Konseling', $melanjutkan, 'var(--tertiary-500)', 'taruna', false],
            ['Taruna yang Selesai Konseling', $selesai, 'var(--secondary-500)', 'taruna', false],
        ] : [
            ['Sakit Ringan', $ringan, '#6fa89d', 'taruna', false],
            ['Sakit Sedang', $sedang, '#c88a1e', 'taruna', false],
            ['Sakit Berat', $berat, '#d8693f', 'taruna', true],
            ['Sembuh', $sembuh, null, 'Dinyatakan sembuh '.strtolower($labelPeriode), false],
            ['Isoman', $isoman, null, 'Sedang melaksanakan isolasi mandiri', false],
        ];
    @endphp

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
                        <a href="{{ request()->fullUrlWithQuery(['periode' => $nilai]) }}" @if ($periode === $nilai) aria-current="true" @endif>{{ $label }}</a>
                    @endforeach
                </nav>
                <div class="pl-date">
                    <span class="pl-date__icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                    {{-- hari ini = satu tanggal; 7 hari / bulan ini = rentang --}}
                    <span><small>{{ $periode === 'hari-ini' ? 'Tanggal' : 'Rentang tanggal' }}</small><b>{{ $rentang }}</b></span>
                </div>
            </div>
        </header>

        <section aria-label="Ringkasan tingkat sakit" class="pl-stats">
            @foreach ($kartu as [$namaKartu, $jumlah, , $keterangan])
                <div class="pl-stat pl-hover">
                    <h2 class="pl-stat__label">{{ $namaKartu }}</h2>
                    <p class="pl-stat__value" data-hitung="{{ $jumlah }}">{{ $jumlah }}</p>
                    <p class="pl-stat__meta">{{ $keterangan }}</p>
                </div>
            @endforeach
        </section>

        <div class="pl-row">

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
                    @foreach ($perTingkat as $tingkat => $nilai)
                        <div class="grid grid-cols-[5.5rem_1fr] items-center gap-3 label">
                            <span>{{ $tingkat }}</span>
                            <div class="flex items-center gap-3">
                                {{-- ponytail: lebar batang = total / total terbesar x 85%, sisa ruang untuk angka total --}}
                                <div class="anim-batang flex gap-0.5 h-8" style="--i: {{ $loop->index }}; width: {{ array_sum($nilai) / $maksTingkat * 85 }}%">
                                    @php $persenTingkat = $persen($nilai); @endphp
                                    @foreach ($nilai as $i => $v)
                                        @if ($v > 0)<span class="seg" tabindex="0" data-judul="{{ $tingkat }} · {{ $kategori[$i] }}" data-isi="{{ $v }} taruna ({{ $persenTingkat[$i] }}% dari {{ $tingkat }})" data-warna="{{ $warnaSakit[$i] }}" style="flex: {{ $v }}; background: {{ $warnaSakit[$i] }}">{{ $v }}</span>@endif
                                    @endforeach
                                </div>
                                <span class="anim-muncul tabular-nums" style="--d: {{ 700 + $loop->index * 120 }}ms">{{ array_sum($nilai) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Keluhan yang dicatat hari ini (tidak ikut periode) --}}
            <section class="pl-panel pl-hover">
                <div class="pl-section__head">
                    <h2 class="pl-panel__title">Keluhan Baru Hari Ini</h2>
                    <span class="label text-muted">{{ $keluhanBaru->count() }} keluhan</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="pl-table">
                        <thead>
                            <tr>
                                <th scope="col" class="c">No</th>
                                <th scope="col">Nama</th>
                                <th scope="col" class="c">NPM</th>
                                <th scope="col" class="c">Tingkat</th>
                                <th scope="col">Keluhan</th>
                                <th scope="col" class="c">{{ $psikolog ? 'Keterangan' : 'Status' }}</th>
                                <th scope="col" class="c">Jam</th>
                                <th scope="col" class="c">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($keluhanBaru as $k)
                                <tr>
                                    <td class="c">{{ $loop->iteration }}</td>
                                    <td class="font-medium">{{ $k->taruna->nama }}</td>
                                    <td class="c tabular-nums">{{ $k->taruna->npm }}</td>
                                    <td class="c">{{ $k->taruna->tingkat }}</td>
                                    <td class="max-w-[16rem] truncate" title="{{ $k->keluhan }}">{{ $k->keluhan }}</td>
                                    <td class="c">
                                        @if ($psikolog)
                                            {{ $k->keterangan() }}
                                        @else
                                            <span class="pl-badge {{ ['Ringan' => 'pl-badge-primary', 'Sedang' => 'pl-badge-notice', 'Berat' => 'pl-badge-accent'][$k->status] ?? 'pl-badge-primary' }}">{{ $k->status }}</span>
                                        @endif
                                    </td>
                                    <td class="c tabular-nums">{{ $k->created_at->format('H:i') }}</td>
                                    <td class="c">
                                        <a href="{{ $psikolog ? route('laporan-kesehatan.psikologi.show', $k) : route('laporan-kesehatan.show', $k) }}" class="pl-icon-btn inline-flex" aria-label="Detail" title="Detail">
                                            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="c !py-8 text-muted">Belum ada keluhan baru hari ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            </div>

            <section class="pl-panel pl-hover" style="flex: 1 1 20rem">
                <h2 class="pl-panel__title">{{ $judul['banding'] }}</h2>
                <div class="flex flex-col items-center gap-5">
                    <svg viewBox="0 0 320 320" class="w-full max-w-[16rem]" role="img"
                        aria-label="{{ $totalBanding ? 'Sembuh '.$persenBanding['Sembuh'].' persen, sakit '.$persenBanding['Sakit'].' persen' : 'Belum ada data taruna' }}">
                        @if (! $totalBanding)
                            <circle cx="160" cy="160" r="150" fill="none" stroke="var(--neutral-200)" stroke-width="2"/>
                            <text x="160" y="160" text-anchor="middle" dominant-baseline="middle" font-family="Instrument Sans, sans-serif" font-size="16" fill="var(--text-muted)">Belum ada data</text>
                        @else
                            <g class="anim-tumbuh grup-tip" style="transform-origin: 160px 160px">
                                @foreach ($segBanding as $k => [$a0, $a1])
                                    @php
                                        [$dx, $dy] = $titik(0, 0, 8, ($a0 + $a1) / 2);
                                        $tipBanding = 'data-judul="'.$k.'" data-isi="'.$perbandingan[$k].' taruna ('.$persenBanding[$k].'%)" data-warna="'.$warnaBanding[$k].'"';
                                    @endphp
                                    @if ($a1 - $a0 >= 359.9)
                                        {{-- satu kategori 100%: path irisan 360° tidak tergambar, pakai lingkaran penuh --}}
                                        <circle class="irisan" tabindex="0" {!! $tipBanding !!} cx="160" cy="160" r="150" fill="{{ $warnaBanding[$k] }}"/>
                                    @else
                                        <path class="irisan" tabindex="0" {!! $tipBanding !!} style="--dx: {{ $dx }}px; --dy: {{ $dy }}px" d="{{ $irisan(160, 160, 150, 0, $a0, $a1) }}" fill="{{ $warnaBanding[$k] }}" stroke="var(--cloud)" stroke-width="3" stroke-linejoin="round"/>
                                    @endif
                                @endforeach
                            </g>
                            <g class="anim-muncul pointer-events-none" style="--d: 600ms" font-family="Instrument Sans, sans-serif" font-weight="600" font-size="28" fill="#232620" text-anchor="middle" dominant-baseline="central">
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
                            @foreach ($warnaBanding as $k => $w)
                                <span class="flex items-center gap-2"><span class="sw sw-kotak" style="background: {{ $w }}"></span>{{ $k }} ({{ $persenBanding[$k] }}%)</span>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-4 pt-6 border-t border-neutral-200">
                    <h3 class="m-0 label">{{ $judul['jk'] }}</h3>
                    <div class="flex flex-wrap items-center justify-center gap-6">
                        <svg viewBox="0 0 280 145" class="w-full max-w-[13rem] h-auto" role="img"
                            aria-label="@foreach ($jenisKelamin as $jk => $n){{ $jk }} {{ $n }} taruna ({{ $persenJk[$jk] }}%), @endforeach total {{ $totalJk }}">
                            <g class="anim-tumbuh grup-tip" style="transform-origin: 140px 140px">
                                @forelse ($segJk as $jk => [$a0, $a1])
                                    @php [$dx, $dy] = $titik(0, 0, 6, ($a0 + $a1) / 2); @endphp
                                    <path class="irisan" tabindex="0" data-judul="{{ $jk }}" data-isi="{{ $jenisKelamin[$jk] }} taruna ({{ $persenJk[$jk] }}%)" data-warna="{{ $warnaJk[$jk] }}" style="--dx: {{ $dx }}px; --dy: {{ $dy }}px" d="{{ $irisan(140, 140, 132, 88, $a0, $a1) }}" fill="{{ $warnaJk[$jk] }}" stroke="var(--cloud)" stroke-width="3" stroke-linejoin="round"/>
                                @empty
                                    <path d="{{ $irisan(140, 140, 132, 88, 180, 360) }}" fill="var(--neutral-200)"/>
                                @endforelse
                            </g>
                            <text x="140" y="138" text-anchor="middle" font-family="Instrument Sans, sans-serif" font-weight="600" font-size="52" letter-spacing="-1.5" fill="var(--text)" data-hitung="{{ $totalJk }}">{{ $totalJk }}</text>
                        </svg>
                        <div class="anim-muncul flex flex-col gap-2.5 min-w-[10rem] label" style="--d: 700ms">
                            @foreach ($jenisKelamin as $jk => $n)
                                <div class="flex items-center gap-2">
                                    <span class="sw" style="background: {{ $warnaJk[$jk] }}"></span>
                                    <span class="flex-1">{{ $jk }}</span>
                                    <strong class="font-semibold tabular-nums">{{ $n }}</strong>
                                    <span class="w-11 text-right text-muted tabular-nums">{{ $persenJk[$jk] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
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
                const b = document.createElement('b'), i = document.createElement('i');
                i.style.background = el.dataset.warna;
                b.append(i, el.dataset.judul);
                tip.replaceChildren(b, el.dataset.isi);
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
