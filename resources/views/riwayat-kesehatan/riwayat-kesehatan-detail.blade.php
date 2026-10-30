{{-- riwayat-kesehatan/riwayat-kesehatan-detail.blade.php — seluruh riwayat satu taruna, design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => $psikologi ? 'Riwayat Psikologi Taruna' : 'Riwayat Kesehatan Taruna'])
    <style>
        @media (prefers-reduced-motion: no-preference) {
            .rk-muncul { animation: rk-muncul .45s cubic-bezier(.2, .7, .2, 1) both; animation-delay: calc(var(--i, 0) * 70ms); }
            @keyframes rk-muncul { from { opacity: 0; transform: translateY(.5rem); } }
        }
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
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('riwayat-kesehatan.index', $psikologi ? ['bagian' => 'psikolog'] : []) }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">{{ $psikologi ? 'Riwayat Psikologi Taruna' : 'Riwayat Kesehatan Taruna' }}</h1>
                <p class="pl-page-sub">Tercatat {{ count($riwayat) }} {{ $psikologi ? 'riwayat psikologi' : 'episode keluhan kesehatan' }} selama menjadi taruna</p>
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
                                @unless ($psikologi)<th scope="col">Keterangan</th>@endunless
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ep['kontrol'] as $n => $k)
                                <tr>
                                    <td class="c">{{ $n + 1 }}</td>
                                    <td class="tabular-nums whitespace-nowrap">{{ $k['tanggal'] }}</td>
                                    <td>{{ $k['hasil'] }}</td>
                                    @unless ($psikologi)<td>{{ $k['keterangan'] }}</td>@endunless
                                </tr>
                            @empty
                                <tr><td colspan="{{ $psikologi ? 3 : 4 }}" class="c !py-8 text-muted">Belum ada {{ strtolower($label['riwayat']) }}.</td></tr>
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
    </main>
</body>
</html>
