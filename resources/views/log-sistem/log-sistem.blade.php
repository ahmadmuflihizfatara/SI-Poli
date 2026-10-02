{{-- log-sistem/log-sistem.blade.php — mengikuti desain "Log Sistem" (PNG), disesuaikan ke design system Pulih. Khusus admin (route can:admin). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Log Sistem'])
    <style>
        .lg-h2 { margin: 0; font: 600 1rem/1.5rem var(--font-heading); color: var(--primary-700); }
        .lg-num { font-family: var(--font-heading); font-weight: 600; color: var(--primary-900); font-variant-numeric: tabular-nums; }
        .sw { width: .625rem; height: .625rem; border-radius: 9999px; flex-shrink: 0; }
        .lg { table-layout: fixed; font-size: .875rem; line-height: 1.25rem; }
        .lg th, .lg td { padding: .625rem .75rem; }
        .lg thead th { position: sticky; top: 0; z-index: 1; background: var(--primary-700); color: var(--on-primary); border-bottom-color: var(--primary-700); }
        .lg td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .lg .c { text-align: center; }
        .pill { appearance: none; height: 2.25rem; border: 0; border-radius: 9999px; background: var(--primary-700); color: #fff; padding: 0 2.25rem 0 1.25rem; cursor: pointer; box-shadow: 0 4px 4px rgba(0,0,0,.25); transition: background-color .15s; }
        .pill:hover { background: var(--primary-900); }
        .pill:focus-visible { outline: 2px solid transparent; box-shadow: var(--focus-ring); }
        .pill option { background: #fff; color: var(--text); }
        .ab { width: 2.25rem; height: 2.25rem; border: 0; border-radius: var(--radius-sm); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; background: var(--primary-50); color: var(--primary-700); transition: background-color .15s, color .15s; }
        .ab:hover { background: var(--primary-700); color: #fff; }
        .ab:focus-visible { outline: 2px solid transparent; box-shadow: var(--focus-ring); }
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

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-4 pr-4 py-6">

        <header class="flex flex-col gap-1">
            <h1 class="h1 m-0 text-primary-900">Log Sistem</h1>
            <p class="body-lg m-0 font-medium">Seluruh log aktivitas yang tercatat di sistem informasi</p>
        </header>

        <section aria-label="Ringkasan log" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[12rem_12rem_minmax(0,28rem)] gap-4">
            <div class="pl-card px-5 py-3 flex flex-col gap-1">
                <h2 class="lg-h2">Total Log</h2>
                <span class="lg-num text-[2.25rem] leading-[2.75rem]">{{ $total }}</span>
            </div>
            <div class="pl-card px-5 py-3 flex flex-col gap-1">
                <h2 class="lg-h2">Log Hari Ini</h2>
                <span class="lg-num text-[2.25rem] leading-[2.75rem]">{{ $hariIni }}</span>
            </div>
            <div class="pl-card px-5 py-3 flex gap-4 sm:col-span-2 lg:col-span-1">
                <div class="flex flex-col gap-1 shrink-0">
                    <h2 class="lg-h2">Sebaran Log</h2>
                    @foreach ($warna as $aksi => $w)
                        <span class="flex items-center gap-2 body-sm"><span class="sw" style="background: {{ $w }}"></span>{{ $aksi }}</span>
                    @endforeach
                    <span class="body-sm text-muted">7 hari terakhir</span>
                </div>
                <svg viewBox="0 0 280 70" class="flex-1 min-w-0 h-auto max-h-[5.5rem]" role="img"
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
                        <text x="{{ 10 + $i * 260 / 6 }}" y="67" text-anchor="middle" font-size="9" font-family="Open Sans, sans-serif" fill="var(--text-muted)">{{ $d->locale('id')->translatedFormat('D') }}</text>
                    @endforeach
                </svg>
            </div>
        </section>

        <form method="GET" action="{{ route('log.index') }}" class="flex flex-wrap items-center gap-3">
            <label class="relative block w-72">
                <span class="sr-only">Cari nama taruna</span>
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-[1.125rem] h-[1.125rem] text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="search" name="q" value="{{ $filter['q'] ?? '' }}" class="pl-input !pl-10" placeholder="Cari nama taruna">
            </label>

            @foreach ([
                'waktu' => ['Waktu', \App\Http\Controllers\LogController::WAKTU],
                'aksi' => ['Aksi', array_combine(\App\Http\Controllers\LogController::AKSI, \App\Http\Controllers\LogController::AKSI)],
                'sumber' => ['Sumber Daya', $sumberDaya->combine($sumberDaya)->all()],
                'pengguna' => ['Pengguna', $peran],
            ] as $nama => [$judul, $opsi])
                <span class="relative">
                    <select name="{{ $nama }}" aria-label="Filter {{ strtolower($judul) }}" onchange="this.form.submit()" class="pill label">
                        <option value="">{{ $judul }}</option>
                        @foreach ($opsi as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(($filter[$nama] ?? '') === (string) $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <svg class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m0 0 6.75-6.75M12 19.5l-6.75-6.75"/></svg>
                </span>
            @endforeach

            @if ($filter)
                <a href="{{ route('log.index') }}" class="pl-btn pl-btn-ghost h-9">Hapus filter</a>
            @endif
            <noscript><button type="submit" class="pl-btn pl-btn-secondary h-9">Terapkan</button></noscript>
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
                                <a href="{{ route('log.show', $l) }}" class="ab" aria-label="Detail log" title="Detail log">
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

        <div>
            @include('laporan-kesehatan.partials.zoom-tabel')
        </div>

        @if ($log->hasPages())
            <div>{{ $log->links() }}</div>
        @endif
    </main>

</body>
</html>
