{{-- riwayat-kesehatan/riwayat-kesehatan-detail.blade.php — seluruh riwayat satu taruna, design system Pagi (warna palet aplikasi) --}}
{{-- Kesehatan: identitas + tabel riwayat (klik baris = halaman Informasi Riwayat Kesehatan). Psikologi: seluruh episode dalam satu halaman. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => $psikologi ? 'Riwayat Psikologi Taruna' : 'Riwayat Kesehatan Taruna'])
    <style>
        @media (prefers-reduced-motion: no-preference) {
            .rk-muncul { animation: rk-muncul .45s cubic-bezier(.2, .7, .2, 1) both; animation-delay: calc(var(--i, 0) * 70ms); }
            @keyframes rk-muncul { from { opacity: 0; transform: translateY(.5rem); } }
        }
        /* Gaya di bawah sama dengan halaman Informasi Keluhan (laporan-kesehatan/detail-keluhan) */
        .ik-data { display: grid; grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr)); gap: 1.25rem 2rem; margin: 0; }
        .ik-data dt { font: 400 max(13px, .8125rem)/1.3 var(--font-sans); color: var(--text-muted); }
        .ik-data dd { margin: .25rem 0 0; font: 500 clamp(15px, 1rem, 16px)/1.4 var(--font-sans); color: var(--text); font-variant-numeric: tabular-nums; }
        .ik-tanda { display: inline-flex; align-items: center; gap: .375rem; padding: .3125rem .75rem .3125rem .625rem; border-radius: var(--radius-full); font: 600 max(14px, .875rem)/1.1 var(--font-sans); white-space: nowrap; }
        .ik-tanda svg { width: 1rem; height: 1rem; flex: none; }
        .ik-tanda--ringan { background: #146b5f; color: #fff; }
        .ik-tanda--sedang { background: #e3a531; color: #232620; }
        .ik-tanda--berat { background: #b42318; color: #fff; }
        .ik-tanda--rawat { background: #b4492a; color: #fff; }
        .ik-row { align-items: stretch; }
        .ik-kartu { transition: transform .2s ease, box-shadow .2s ease; }
        .ik-kartu:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .ik-tabel th { background: var(--strong); color: var(--on-primary); font-weight: 600; font-size: max(14px, .875rem); border-bottom: 0; }
        .ik-baris { cursor: pointer; }
        .ik-baris td { transition: background-color .15s; }
        .ik-baris:hover td { background: var(--cloud); }
        .ik-baris__buka { color: var(--link); font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; text-decoration: underline; text-underline-offset: 3px; text-decoration-color: color-mix(in srgb, currentColor 40%, transparent); }
        .ik-baris:hover .ik-baris__buka { text-decoration-color: currentColor; }
        .ik-ringkas-teks { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-clamp: 2; overflow: hidden; }
        @media (prefers-reduced-motion: reduce) { .ik-kartu { transition: none; } .ik-kartu:hover { transform: none; } }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $inisial = collect(explode(' ', $taruna->nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
        $nadaStatus = ['Ringan' => 'pl-badge-primary', 'Sedang' => 'pl-badge-notice', 'Berat' => 'pl-badge-accent', 'Melanjutkan konseling' => 'pl-badge-notice', 'Tidak melanjutkan konseling' => 'pl-badge-primary'];
        $label = $psikologi
            ? ['keluhan' => 'Keluhan Psikologi', 'terapi' => 'Terapi Psikologi', 'riwayat' => 'Riwayat Konseling', 'hasil' => 'Hasil Konseling', 'status' => 'Status Konseling']
            : ['keluhan' => 'Keluhan Awal', 'terapi' => 'Terapi dan Obat', 'riwayat' => 'Riwayat Kontrol', 'hasil' => 'Hasil Kontrol', 'status' => 'Tingkat Keparahan'];
        $ekspor = $psikologi ? route('riwayat-kesehatan.psikologi.ekspor.satu', $taruna) : route('riwayat-kesehatan.ekspor.satu', $taruna);
        $tanggal = fn (string $iso) => \Illuminate\Support\Carbon::parse($iso)->locale('id')->translatedFormat('j M Y');
        // [kelas warna, ikon]: sama dengan penanda di halaman Informasi Keluhan
        $tanda = [
            'Ringan' => ['ik-tanda--ringan', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            'Sedang' => ['ik-tanda--sedang', 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z'],
            'Berat' => ['ik-tanda--berat', 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z'],
            'Dalam perawatan' => ['ik-tanda--rawat', 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
            'Isolasi mandiri' => ['ik-tanda--sedang', 'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
            'Sembuh' => ['ik-tanda--ringan', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ];
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('riwayat-kesehatan.index', $psikologi ? ['bagian' => 'psikolog'] : []) }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">{{ $psikologi ? 'Riwayat Psikologi Taruna' : 'Riwayat Kesehatan Taruna' }}</h1>
                <p class="pl-page-sub">Tercatat {{ count($riwayat) }} {{ $psikologi ? 'riwayat psikologi' : 'riwayat kesehatan' }} selama menjadi taruna</p>
            </div>
            @if ($bisaEkspor)
                <div class="pl-page-actions">
                    <a href="{{ $ekspor }}" class="pl-btn pl-btn-primary">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Unduh Seluruh Riwayat (PDF)
                    </a>
                </div>
            @endif
        </header>

        @if ($psikologi)
            <section class="pl-profile rk-muncul" aria-label="Identitas taruna">
                <div class="pl-profile__who">
                    <span class="pl-avatar" aria-hidden="true">{{ $inisial }}</span>
                    <div>
                        <p class="pl-profile__name">{{ $taruna->nama }}</p>
                        <p class="pl-profile__meta">Tingkat {{ $taruna->tingkat }} &middot; {{ $taruna->kelas }} &middot; {{ $taruna->jenis_kelamin }}</p>
                    </div>
                </div>
                <dl class="pl-kvs">
                    <div class="pl-kv"><dt>NPM</dt><dd>{{ $taruna->npm }}</dd></div>
                    <div class="pl-kv"><dt>Kamar</dt><dd>{{ $taruna->kamar }}</dd></div>
                    <div class="pl-kv"><dt>Jumlah Riwayat</dt><dd>{{ count($riwayat) }}</dd></div>
                    <div class="pl-kv"><dt>Lapor Terakhir</dt><dd>{{ \Illuminate\Support\Carbon::parse($riwayat[0]['tanggal_lapor'])->format('d/m/Y') }}</dd></div>
                </dl>
            </section>

            {{-- Satu bagian per episode, terbaru di atas --}}
            @foreach ($riwayat as $i => $ep)
                <section class="pl-section rk-muncul" style="--i: {{ $i + 1 }}" aria-labelledby="episode-{{ $i }}">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="pl-section__title" id="episode-{{ $i }}">{{ \Illuminate\Support\Carbon::parse($ep['tanggal_lapor'])->locale('id')->translatedFormat('d F Y') }}</h2>
                        <span class="pl-badge pl-badge-lg {{ $nadaStatus[$ep['status']] ?? 'pl-badge-primary' }}" title="{{ $label['status'] }}">{{ $ep['status'] }}</span>
                    </div>

                    <div class="pl-row">
                        <section class="pl-panel" aria-label="{{ $label['keluhan'] }}">
                            <div class="pl-panel__head"><h3 class="pl-panel__title">{{ $label['keluhan'] }}</h3></div>
                            <p class="pl-panel__text">{{ $ep['keluhan_awal'] }}</p>
                        </section>
                        <section class="pl-panel" aria-label="{{ $label['terapi'] }}">
                            <div class="pl-panel__head"><h3 class="pl-panel__title">{{ $label['terapi'] }}</h3></div>
                            <ul class="pl-list">
                                @foreach (array_filter(explode("\n", trim($ep['terapi']))) as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </section>
                    </div>

                    <h3 class="label text-primary-900 m-0">{{ $label['riwayat'] }}</h3>
                    <div class="pl-table-wrap">
                        <table class="pl-table">
                            <thead>
                                <tr>
                                    <th scope="col" class="c">No</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">{{ $label['hasil'] }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ep['kontrol'] as $n => $k)
                                    <tr>
                                        <td class="c">{{ $n + 1 }}</td>
                                        <td class="tabular-nums whitespace-nowrap">{{ $k['tanggal'] }}</td>
                                        <td>{{ $k['hasil'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="c !py-8 text-muted">Belum ada {{ strtolower($label['riwayat']) }}.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pl-alert pl-alert-accent max-w-[35rem]">
                        <h3 class="pl-alert__title">Keterangan Lainnya</h3>
                        <p class="pl-alert__text">{{ $ep['catatan'] }}</p>
                    </div>
                </section>
            @endforeach
        @else
            <div class="pl-row ik-row rk-muncul">
                <section class="pl-panel ik-kartu" aria-labelledby="judul-identitas">
                    <h2 class="pl-panel__title" id="judul-identitas">Identitas</h2>
                    <div class="pl-profile__who">
                        <span class="pl-avatar" aria-hidden="true">{{ $inisial }}</span>
                        <div>
                            <p class="pl-profile__name">{{ $taruna->nama }}</p>
                            <p class="pl-profile__meta">{{ $taruna->jenis_kelamin }}</p>
                        </div>
                    </div>
                    <dl class="ik-data">
                        <div><dt>NPM</dt><dd>{{ $taruna->npm }}</dd></div>
                        <div><dt>Tingkat</dt><dd>{{ $taruna->tingkat }}</dd></div>
                        <div><dt>Kelas</dt><dd>{{ $taruna->kelas ?: '-' }}</dd></div>
                        <div><dt>Kamar</dt><dd>{{ $taruna->kamar ?: '-' }}</dd></div>
                    </dl>
                </section>

                <section class="pl-panel ik-kartu" aria-labelledby="judul-ringkasan">
                    <h2 class="pl-panel__title" id="judul-ringkasan">Ringkasan</h2>
                    <dl class="ik-data">
                        <div><dt>Jumlah riwayat</dt><dd>{{ count($riwayat) }}</dd></div>
                        <div><dt>Lapor terakhir</dt><dd>{{ $tanggal($riwayat[0]['tanggal_lapor']) }}</dd></div>
                        @foreach (['Ringan', 'Sedang', 'Berat'] as $tingkat)
                            <div><dt>Riwayat {{ strtolower($tingkat) }}</dt><dd>{{ collect($riwayat)->where('status', $tingkat)->count() }}</dd></div>
                        @endforeach
                        <div><dt>Belum sembuh</dt><dd>{{ collect($riwayat)->where('status_pemulihan', '!=', 'Sembuh')->count() }}</dd></div>
                    </dl>
                </section>
            </div>

            <section class="pl-section rk-muncul" style="--i: 1" aria-labelledby="judul-daftar">
                <h2 class="pl-section__title" id="judul-daftar">Daftar Riwayat Kesehatan</h2>
                <div class="pl-table-wrap">
                    <table class="pl-table ik-tabel">
                        <thead>
                            <tr>
                                <th scope="col">Tanggal Lapor</th>
                                <th scope="col">Keluhan</th>
                                <th scope="col">Tingkat Keparahan</th>
                                <th scope="col">Status Pemulihan</th>
                                <th scope="col">Kontrol</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($riwayat as $ep)
                                @php $url = route('riwayat-kesehatan.keluhan.show', [$taruna, $ep['id']]); @endphp
                                {{-- klik di mana saja pada baris membuka halaman riwayat; tautan tanggal = jalur papan ketik --}}
                                <tr class="ik-baris" data-href="{{ $url }}">
                                    <td><a href="{{ $url }}" class="ik-baris__buka">{{ $tanggal($ep['tanggal_lapor']) }}</a></td>
                                    <td><span class="ik-ringkas-teks">{{ $ep['keluhan_awal'] }}</span></td>
                                    @foreach ([$ep['status'], $ep['status_pemulihan']] as $nilai)
                                        <td>
                                            @isset($tanda[$nilai])
                                                <span class="ik-tanda {{ $tanda[$nilai][0] }}"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $tanda[$nilai][1] }}"/></svg>{{ $nilai }}</span>
                                            @else
                                                {{ $nilai ?? '-' }}
                                            @endisset
                                        </td>
                                    @endforeach
                                    <td class="tabular-nums whitespace-nowrap">{{ count($ep['kontrol']) }} kali</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="pl-table-foot m-0">Klik baris untuk melihat informasi lengkap riwayat kesehatan tersebut.</p>
            </section>

            <script>
                document.querySelectorAll('[data-href]').forEach((baris) => baris.addEventListener('click', (e) => {
                    if (!e.target.closest('a')) { window.location.href = baris.dataset.href; }
                }));
            </script>
        @endif
    </main>
</body>
</html>
