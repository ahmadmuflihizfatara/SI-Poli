{{-- laporan-kesehatan/detail-keluhan-psikologi.blade.php — halaman "Informasi Keluhan" psikolog design system Pagi,
     susunan & gaya sama dengan detail-keluhan.blade.php (perawat): kartu Identitas + Data Klinis, Keluhan, tabel Terapi, Riwayat Konseling --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Informasi Keluhan'])
    <style>
        /* gaya disalin dari detail-keluhan.blade.php supaya kedua halaman informasi keluhan tampil sama */
        /* Isi data 15–16px tebal 500; label tetap kecil */
        .ik-data { display: grid; grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr)); gap: 1.25rem 2rem; margin: 0; }
        .ik-data dt { font: 400 max(13px, .8125rem)/1.3 var(--font-sans); color: var(--text-muted); }
        .ik-data dd { margin: .25rem 0 0; font: 500 clamp(15px, 1rem, 16px)/1.4 var(--font-sans); color: var(--text); font-variant-numeric: tabular-nums; }
        .ik-isi { font-size: clamp(15px, 1rem, 16px); font-weight: 500; white-space: pre-line; }
        /* Penanda berwarna selalu disertai ikon + teks (tidak bergantung pada warna saja). Warna tetap di tema gelap */
        .ik-tanda { display: inline-flex; align-items: center; gap: .375rem; padding: .3125rem .75rem .3125rem .625rem; border-radius: var(--radius-full); font: 600 max(14px, .875rem)/1.1 var(--font-sans); white-space: nowrap; }
        .ik-tanda svg { width: 1rem; height: 1rem; flex: none; }
        .ik-tanda--selesai { background: #146b5f; color: #fff; }
        .ik-tanda--telat { background: #b42318; color: #fff; }
        .ik-tanda--lanjut { background: #b4492a; color: #fff; }
        .ik-tanda--lg { padding: .4375rem .9375rem .4375rem .75rem; font-size: max(15px, .9375rem); }
        .ik-tanda--lg svg { width: 1.125rem; height: 1.125rem; }
        .ik-kontrol { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
        /* Kartu sebaris sama tinggi; mengambang saat hover */
        .ik-row { align-items: stretch; }
        .ik-kartu { transition: transform .2s ease, box-shadow .2s ease; }
        .ik-kartu:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        @media (prefers-reduced-motion: reduce) { .ik-kartu { transition: none; } .ik-kartu:hover { transform: none; } }
        /* Kepala tabel hijau tua + teks putih tebal, sama dengan tabel Laporan Kesehatan */
        .ik-tabel th { background: var(--strong); color: var(--on-primary); font-weight: 600; font-size: max(14px, .875rem); border-bottom: 0; }
        .ik-tabel td { white-space: pre-line; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $taruna = $konseling->taruna;
        $inisial = collect(explode(' ', $taruna->nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
        $tanggal = fn ($t) => $t->locale('id')->translatedFormat('j M Y');
        $terapi = collect(preg_split('/\r?\n/', trim($konseling->terapi)))->filter();
        $lanjut = $konseling->lanjut_konseling;
        // [kelas warna, ikon] status konseling: warna pekat + ikon, tanpa titik (sama dengan status pemulihan perawat)
        [$kelasStatus, $ikonStatus] = $lanjut
            ? ['ik-tanda--lanjut', 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155']
            : ['ik-tanda--selesai', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'];
        // ponytail: yang tidak melanjutkan konseling tidak punya jadwal lagi (sama seperti pasien sembuh di halaman perawat)
        $jadwal = $lanjut ? $konseling->tanggal_konseling_selanjutnya : null;
        $hariTelat = $jadwal?->lt(today()) ? (int) $jadwal->diffInDays(today()) : 0;
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('laporan-kesehatan.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="pl-page-title">Informasi Keluhan</h1>
                    <span class="ik-tanda ik-tanda--lg {{ $kelasStatus }}"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $ikonStatus }}"/></svg>{{ $lanjut ? 'Konseling berlanjut' : 'Selesai konseling' }}</span>
                </div>
                <p class="pl-page-sub">Informasi rinci keluhan kesehatan psikologi taruna</p>
            </div>
            @can('edit-data')
                <div class="pl-page-actions">
                    <a href="{{ route('laporan-kesehatan.psikologi.edit', $konseling) }}" class="pl-btn pl-btn-primary">
                        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                        Perbarui Keluhan
                    </a>
                </div>
            @endcan
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info pl-alert--ikon" role="status">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="pl-row ik-row">
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

            <section class="pl-panel ik-kartu" aria-labelledby="judul-klinis">
                <h2 class="pl-panel__title" id="judul-klinis">Data Klinis</h2>
                <dl class="ik-data">
                    <div><dt>Awal keluhan</dt><dd>{{ $tanggal($konseling->tanggal_awal) }}</dd></div>
                    <div>
                        <dt>Keterangan konseling</dt>
                        <dd>{{ $konseling->keterangan() }}@if ($konseling->selesai_at), sejak {{ $tanggal($konseling->selesai_at) }}@endif</dd>
                    </div>
                    <div>
                        <dt>Konseling selanjutnya</dt>
                        <dd class="ik-kontrol">
                            <span>{{ $jadwal ? $tanggal($jadwal) : '-' }}</span>
                            @if ($hariTelat > 0)
                                <span class="ik-tanda ik-tanda--telat"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>Terlambat {{ $hariTelat }} hari</span>
                            @elseif ($jadwal?->isToday())
                                <span class="ik-tanda ik-tanda--telat"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>Hari ini</span>
                            @endif
                        </dd>
                    </div>
                    <div><dt>Psikolog pemeriksa</dt><dd>{{ $konseling->pemeriksa?->nama ?? $konseling->pembuat?->name ?? '-' }}</dd></div>
                </dl>
            </section>
        </div>

        <div class="pl-row ik-row">
            <section class="pl-panel ik-kartu" aria-labelledby="judul-keluhan">
                <h2 class="pl-panel__title" id="judul-keluhan">Keluhan</h2>
                <p class="pl-panel__text ik-isi">{{ $konseling->keluhan }}</p>
            </section>
        </div>

        <section class="pl-section" aria-labelledby="judul-terapi">
            <h2 class="pl-section__title" id="judul-terapi">Terapi Psikologi</h2>
            <div class="pl-table-wrap">
                <table class="pl-table ik-tabel">
                    <thead>
                        <tr><th scope="col">Terapi yang diterima terakhir</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($terapi as $item)
                            <tr><td>{{ $item }}</td></tr>
                        @empty
                            <tr><td class="c !py-8 text-muted">Belum ada terapi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="pl-section" aria-labelledby="judul-riwayat">
            <h2 class="pl-section__title" id="judul-riwayat">Riwayat Konseling</h2>
            <div class="pl-table-wrap">
                <table class="pl-table ik-tabel">
                    <thead>
                        <tr>
                            <th scope="col">Tanggal Konseling</th>
                            <th scope="col">Hasil Konseling</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($konseling->riwayat as $k)
                            <tr>
                                <td class="tabular-nums">{{ $tanggal($k->tanggal_konseling) }}</td>
                                <td>{{ $k->hasil_konseling }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="c !py-8 text-muted">Belum ada riwayat konseling.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
