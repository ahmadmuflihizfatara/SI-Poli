{{-- laporan-kesehatan/detail-keluhan.blade.php — halaman "Informasi keluhan" design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Informasi Keluhan'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $taruna = $keluhan->taruna;
        $inisial = collect(explode(' ', $taruna->nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
        $terapi = collect(explode("\n", trim($keluhan->terapi)))->filter();
        $nadaStatus = ['Ringan' => 'pl-badge-primary', 'Sedang' => 'pl-badge-notice', 'Berat' => 'pl-badge-accent'];
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('laporan-kesehatan.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">Informasi Keluhan</h1>
                <p class="pl-page-sub">Informasi rinci keluhan kesehatan taruna</p>
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
                <div class="pl-kv"><dt>Awal Keluhan</dt><dd>{{ $keluhan->tanggal_awal->format('d/m/Y') }}</dd></div>
                <div class="pl-kv"><dt>Kamar</dt><dd>{{ $taruna->kamar }}</dd></div>
                <div class="pl-kv"><dt>Tingkat Keparahan</dt><dd><span class="pl-badge pl-badge-lg {{ $nadaStatus[$keluhan->status] ?? 'pl-badge-primary' }}">{{ $keluhan->status }}</span></dd></div>
                <div class="pl-kv"><dt>NPM</dt><dd>{{ $taruna->npm }}</dd></div>
                <div class="pl-kv"><dt>Kontrol Selanjutnya</dt><dd>{{ $keluhan->tanggal_kontrol_selanjutnya?->format('d/m/Y') ?? '-' }}</dd></div>
            </dl>
        </section>

        <div class="pl-row">
            <section class="pl-panel" aria-labelledby="judul-keluhan">
                <div class="pl-panel__head">
                    <h2 class="pl-panel__title" id="judul-keluhan">Keluhan</h2>
                    <p class="pl-panel__sub">Keluhan awal yang dinyatakan</p>
                </div>
                <p class="pl-panel__text">{{ $keluhan->keluhan }}</p>
            </section>
            <section class="pl-panel" aria-labelledby="judul-terapi">
                <div class="pl-panel__head">
                    <h2 class="pl-panel__title" id="judul-terapi">Terapi dan Obat</h2>
                    <p class="pl-panel__sub">Terapi dan obat yang diterima saat ini</p>
                </div>
                <ul class="pl-list">
                    @foreach ($terapi as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </section>
        </div>

        <section class="pl-section" aria-labelledby="judul-riwayat">
            <h2 class="pl-section__title" id="judul-riwayat">Riwayat Kontrol</h2>
            <div class="pl-table-wrap">
                <table class="pl-table">
                    <thead>
                        <tr>
                            <th scope="col" class="c">No</th>
                            <th scope="col">Tanggal Kontrol</th>
                            <th scope="col">Hasil Kontrol</th>
                            <th scope="col">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($keluhan->riwayatKontrol as $i => $k)
                            <tr>
                                <td class="c">{{ $i + 1 }}</td>
                                <td class="tabular-nums">{{ $k->tanggal_kontrol->format('d/m/Y') }}</td>
                                <td>{{ $k->hasil_kontrol }}</td>
                                <td>
                                    {{ $k->keterangan ?: '-' }}
                                    @if ($k->perlu_rujukan)
                                        <span class="pl-badge pl-badge-accent ml-1">Dirujuk</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="c !py-8 text-muted">Belum ada riwayat kontrol.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="pl-alert pl-alert-accent max-w-[35rem]" aria-labelledby="judul-lain">
            <h2 class="pl-alert__title" id="judul-lain">Keterangan Lainnya</h2>
            <p class="pl-alert__text">{{ $keluhan->keterangan ?: 'Tidak ada.' }}</p>
        </section>
    </main>
</body>
</html>
