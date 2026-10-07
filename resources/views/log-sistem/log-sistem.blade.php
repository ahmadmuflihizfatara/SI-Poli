{{-- log-sistem/log-sistem.blade.php — tata letak "Log sistem" design system Pagi (warna palet aplikasi). Khusus admin (route can:admin). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Log Sistem'])
    <style>
        .sw { width: .625rem; height: .625rem; border-radius: 9999px; flex-shrink: 0; }
        .lg { table-layout: fixed; }
        .lg th { position: sticky; top: 0; z-index: 1; }
        .lg td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $warna = ['Tambah' => '#146b5f', 'Edit' => '#d8693f'];
        $badge = ['Tambah' => 'pl-badge-primary', 'Edit' => 'pl-badge-accent'];
        $peran = \App\Http\Controllers\LogController::PENGGUNA;

        // Grafik garis 7 hari: x merata 10..270, y 6..54 (makin tinggi makin banyak)
        $maks = max(1, ...array_merge(...array_values($sebaran)));
        $titik = fn ($nilai) => collect($nilai)->map(fn ($n, $i) => [10 + $i * 260 / 6, round(54 - $n / $maks * 48, 1)]);

        $filter = array_filter($filter, fn ($v) => $v !== null && $v !== '');
        $kolom = [['Timestamp', 11, true], ['Aksi', 6.5, true], ['Sumber Daya', 10, true], ['Pengguna', 8, true], ['Pesan', null, false], ['Tindakan', 6, true]];
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <h1 class="pl-page-title">Log Sistem</h1>
                <p class="pl-page-sub">Seluruh log aktivitas yang tercatat di sistem informasi</p>
            </div>
        </header>

        <section aria-label="Ringkasan log" class="pl-row">
            <div class="grid gap-4" style="flex: 1 1 16rem">
                <div class="pl-stat">
                    <h2 class="pl-stat__label">Total Log</h2>
                    <p class="pl-stat__value">{{ $total }}</p>
                </div>
                <div class="pl-stat">
                    <h2 class="pl-stat__label">Log Hari Ini</h2>
                    <p class="pl-stat__value">{{ $hariIni }}</p>
                </div>
            </div>
            <div class="pl-panel" style="flex: 2 1 30rem">
                <div class="pl-section__head">
                    <div class="pl-panel__head">
                        <h2 class="pl-panel__title">Sebaran Log</h2>
                        <p class="pl-panel__sub">7 hari terakhir</p>
                    </div>
                    <ul class="pl-legend">
                        @foreach ($warna as $aksi => $w)
                            <li style="--ring: {{ $w }}">{{ $aksi }}</li>
                        @endforeach
                    </ul>
                </div>
                <svg viewBox="0 0 280 70" class="w-full h-auto max-h-[9rem]" role="img"
                    aria-label="@foreach ($sebaran as $aksi => $nilai){{ $aksi }}: {{ implode(', ', $nilai) }}. @endforeach">
                    <line x1="10" y1="54.5" x2="270" y2="54.5" stroke="var(--neutral-200)"/>
                    @foreach ($sebaran as $aksi => $nilai)
                        <polyline fill="none" stroke="{{ $warna[$aksi] }}" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"
                            points="{{ $titik($nilai)->map(fn ($p) => implode(',', $p))->implode(' ') }}"/>
                        @foreach ($titik($nilai) as $i => [$x, $y])
                            <circle cx="{{ $x }}" cy="{{ $y }}" r="2.5" fill="{{ $warna[$aksi] }}"><title>{{ $aksi }} {{ $hari[$i]->locale('id')->translatedFormat('D, d M') }}: {{ $nilai[$i] }}</title></circle>
                        @endforeach
                    @endforeach
                    @foreach ($hari as $i => $d)
                        <text x="{{ 10 + $i * 260 / 6 }}" y="67" text-anchor="middle" font-size="9" font-family="Instrument Sans, sans-serif" fill="var(--text-muted)">{{ $d->locale('id')->translatedFormat('D') }}</text>
                    @endforeach
                </svg>
            </div>
        </section>

        <form method="GET" action="{{ route('log.index') }}" class="pl-toolbar">
            <label class="pl-search">
                <span class="pl-sr">Cari nama taruna</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="search" name="q" value="{{ $filter['q'] ?? '' }}" class="pl-input" placeholder="Cari nama taruna">
            </label>

            @foreach ([
                'waktu' => ['Waktu', \App\Http\Controllers\LogController::WAKTU],
                'aksi' => ['Aksi', array_combine(\App\Http\Controllers\LogController::AKSI, \App\Http\Controllers\LogController::AKSI)],
                'sumber' => ['Sumber Daya', $sumberDaya->combine($sumberDaya)->all()],
                'pengguna' => ['Pengguna', $peran],
            ] as $nama => [$judul, $opsi])
                <select name="{{ $nama }}" aria-label="Filter {{ strtolower($judul) }}" onchange="this.form.submit()" class="pl-input pl-input--pill !w-auto min-w-[10rem]">
                        <option value="">{{ $judul }}</option>
                        @foreach ($opsi as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(($filter[$nama] ?? '') === (string) $nilai)>{{ $label }}</option>
                        @endforeach
                </select>
            @endforeach

            @if ($filter)
                <a href="{{ route('log.index') }}" class="pl-btn pl-btn-ghost pl-btn-sm">Hapus filter</a>
            @endif
            <noscript><button type="submit" class="pl-btn pl-btn-secondary pl-btn-sm">Terapkan</button></noscript>
        </form>

        <div class="pl-table-wrap">
            <table class="pl-table lg">
                <colgroup>
                    @foreach ($kolom as [, $lebar])
                        <col @if ($lebar) style="width: {{ $lebar }}rem" @endif>
                    @endforeach
                </colgroup>
                <thead>
                    <tr>
                        @foreach ($kolom as [$judul, , $tengah])
                            <th scope="col" @class(['c' => $tengah])>{{ $judul }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($log as $l)
                        @php
                            $waktu = $l->created_at->format('d-m-Y H:i:s');
                            $role = $peran[$l->user?->role] ?? '-';
                        @endphp
                        <tr>
                            <td class="c tabular-nums">{{ $waktu }}</td>
                            <td class="c"><span class="pl-badge {{ $badge[$l->aksi] ?? 'pl-badge-primary' }}">{{ $l->aksi }}</span></td>
                            <td class="c">{{ $l->sumber_daya }}</td>
                            <td class="c" title="{{ $l->user?->name }}">{{ $role }}</td>
                            <td title="{{ $l->pesan }}">{{ $l->pesan }}</td>
                            <td class="c">
                                <a href="{{ route('log.show', $l) }}" class="pl-icon-btn" aria-label="Detail log" title="Detail log">
                                    <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">{{ $filter ? 'Tidak ada log yang cocok dengan filter.' : 'Belum ada log aktivitas.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pl-table-foot">
            @include('laporan-kesehatan.partials.zoom-tabel')
            @include('partials.halaman', ['data' => $log])
        </div>
    </main>

</body>
</html>
