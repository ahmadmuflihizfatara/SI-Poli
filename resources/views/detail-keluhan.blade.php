{{-- detail-keluhan.blade.php — halaman "Informasi Keluhan", design system Pulih --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Informasi Keluhan'])
    <style>
        .dk-avatar { width: 3.5rem; height: 3.5rem; border-radius: 9999px; background: var(--primary-900); color: #fff;
            display: flex; align-items: center; justify-content: center; font: 600 1.125rem/1 var(--font-heading); flex-shrink: 0; }
        .dk-summary { background: var(--primary-50); border-radius: var(--radius-md); padding: var(--space-6);
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: var(--space-6); }
        .dk-stat-label { margin: 0; font-size: .75rem; color: var(--text-muted); }
        .dk-stat-value { margin: 0; font: 600 1rem/1.5rem var(--font-heading); color: var(--primary-900); }
        .dk-h { margin: 0; font: 600 1.25rem/1.75rem var(--font-heading); color: var(--primary-700); }
        .dk-sub { margin: 0; font-size: .8125rem; color: var(--text-muted); }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $inisial = collect(explode(' ', $r['nama']))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-4 pr-4 py-6">

        <header class="flex flex-col gap-1">
            <a href="{{ route('laporan-kesehatan.index') }}" class="pl-btn pl-btn-ghost self-start h-9 pl-2 pr-3">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
            <div class="flex flex-col gap-1">
                <h1 class="h1 m-0 text-primary-900">Informasi Keluhan</h1>
                <p class="body m-0 text-muted">Informasi rinci keluhan kesehatan taruna</p>
            </div>
        </header>

        <div class="dk-summary">
            <div class="flex items-center gap-4">
                <div class="dk-avatar">{{ $inisial }}</div>
                <div class="flex flex-col gap-1">
                    <p class="h2 m-0 text-primary-900">{{ $r['nama'] }}</p>
                    <p class="body-sm m-0 text-muted">Tingkat {{ $r['tingkat'] }} &middot; {{ $r['kelas'] }} &middot; {{ $r['jenis_kelamin'] }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-8">
                <div class="flex flex-col gap-1">
                    <p class="dk-stat-label">Awal Keluhan</p>
                    <p class="dk-stat-value">{{ $r['awal'] }}</p>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="dk-stat-label">Kamar</p>
                    <p class="dk-stat-value">{{ $r['kamar'] }}</p>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="dk-stat-label">Tingkat Keparahan</p>
                    <p class="dk-stat-value">{{ $r['status'] }}</p>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="dk-stat-label">NPM</p>
                    <p class="dk-stat-value">{{ $r['npm'] }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <section class="pl-card p-6 flex flex-col gap-2">
                <h2 class="dk-h">Keluhan</h2>
                <p class="dk-sub">Keluhan awal yang dinyatakan</p>
                <p class="body m-0">{{ $r['keluhan'] }}</p>
            </section>
            <section class="pl-card p-6 flex flex-col gap-2">
                <h2 class="dk-h">Terapi dan Obat</h2>
                <p class="dk-sub">Terapi dan obat yang diterima saat ini</p>
                <ul class="body m-0 pl-5 list-disc">
                    @foreach ($r['terapi'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </section>
        </div>

        <h2 class="h3 m-0 text-primary-900">Riwayat Kontrol</h2>
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
                    @forelse ($r['riwayat'] as $i => $k)
                        <tr>
                            <td class="c">{{ $i + 1 }}</td>
                            <td class="tabular-nums">{{ $k['tanggal'] }}</td>
                            <td>{{ $k['hasil'] }}</td>
                            <td>{{ $k['keterangan'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="c !py-8 text-muted">Belum ada riwayat kontrol.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pl-alert pl-alert-accent">
            <div class="flex flex-col gap-1">
                <p class="label m-0">Keterangan Lainnya</p>
                <p class="body-sm m-0">{{ $r['keterangan_lainnya'] }}</p>
            </div>
        </div>
    </main>
</body>
</html>
