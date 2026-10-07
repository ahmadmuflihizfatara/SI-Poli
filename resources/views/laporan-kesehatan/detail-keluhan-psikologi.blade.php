{{-- laporan-kesehatan/detail-keluhan-psikologi.blade.php — prototype "Informasi Keluhan Psikolog", susunan & gaya sama dengan
     detail-keluhan.blade.php (design system Pagi). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Informasi Keluhan'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $taruna = $konseling->taruna;
        $inisial = collect(explode(' ', $taruna->nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
        $terapi = collect(preg_split('/\r?\n/', trim($konseling->terapi)))->filter();
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('laporan-kesehatan.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">Informasi Keluhan</h1>
                <p class="pl-page-sub">Informasi rinci keluhan kesehatan psikologi taruna</p>
            </div>
        </header>

        <section class="pl-profile" aria-label="Identitas taruna">
            <div class="pl-profile__who">
                <span class="pl-avatar" aria-hidden="true">{{ $inisial }}</span>
                <div>
                    <p class="pl-profile__name">{{ $taruna->nama }}</p>
                    <p class="pl-profile__meta">Tingkat {{ $taruna->tingkat }} &middot; {{ $taruna->kelas }} &middot; {{ $taruna->jenis_kelamin }}</p>
                </div>
            </div>
            <dl class="pl-kvs">
                @foreach ([
                    'Awal Keluhan' => $konseling->tanggal_awal->format('d/m/Y'),
                    'Kamar' => $taruna->kamar,
                    'Konseling Selanjutnya' => $konseling->tanggal_konseling_selanjutnya?->format('d/m/Y') ?? '-',
                    'NPM' => $taruna->npm,
                ] as $label => $nilai)
                    <div class="pl-kv"><dt>{{ $label }}</dt><dd>{{ $nilai }}</dd></div>
                @endforeach
            </dl>
        </section>

        <div class="pl-row">
            <section class="pl-panel" aria-labelledby="judul-keluhan">
                <div class="pl-panel__head">
                    <h2 class="pl-panel__title" id="judul-keluhan">Keluhan</h2>
                    <p class="pl-panel__sub">Keluhan awal yang dinyatakan</p>
                </div>
                <p class="pl-panel__text whitespace-pre-line">{{ $konseling->keluhan }}</p>
            </section>
            <section class="pl-panel" aria-labelledby="judul-terapi">
                <div class="pl-panel__head">
                    <h2 class="pl-panel__title" id="judul-terapi">Terapi Psikologi</h2>
                    <p class="pl-panel__sub">Terapi yang diterima terakhir</p>
                </div>
                <ul class="pl-list">
                    @foreach ($terapi as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </section>
        </div>

        <section class="pl-section" aria-labelledby="judul-riwayat">
            <h2 class="pl-section__title" id="judul-riwayat">Riwayat Konseling</h2>
            <div class="pl-table-wrap">
                <table class="pl-table">
                    <thead>
                        <tr>
                            <th scope="col" class="c">No</th>
                            <th scope="col">Tanggal Konseling</th>
                            <th scope="col">Hasil Konseling</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($konseling->riwayat as $i => $k)
                            <tr>
                                <td class="c">{{ $i + 1 }}</td>
                                <td class="tabular-nums">{{ $k->tanggal_konseling->format('d/m/Y') }}</td>
                                <td>{{ $k->hasil_konseling }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="c !py-8 text-muted">Belum ada riwayat konseling.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="pl-alert pl-alert-accent max-w-[35rem]" aria-labelledby="judul-lain">
            <h2 class="pl-alert__title" id="judul-lain">Keterangan Konseling</h2>
            <p class="pl-alert__text">{{ $konseling->keterangan() }}{{ $konseling->selesai_at ? ' (sejak '.$konseling->selesai_at->format('d/m/Y').')' : '' }}</p>
        </section>
    </main>
</body>
</html>
