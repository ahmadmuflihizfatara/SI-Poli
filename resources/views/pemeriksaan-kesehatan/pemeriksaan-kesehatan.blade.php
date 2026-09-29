{{-- pemeriksaan-kesehatan/pemeriksaan-kesehatan.blade.php — halaman utama Pemeriksaan Kesehatan, design system Pulih --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Pemeriksaan Kesehatan'])
    <style>
        .pk-kartu { transition: border-color .15s, box-shadow .15s; }
        a.pk-kartu:hover { border-color: var(--primary-400); box-shadow: var(--shadow-lg); }
        a.pk-kartu:focus-visible { outline: 2px solid transparent; box-shadow: var(--focus-ring); }
        .pk-bar { height: .5rem; background: var(--neutral-200); border-radius: var(--radius-full); overflow: hidden; }
        .pk-bar i { display: block; height: 100%; border-radius: inherit; }
        .pk-titik { width: .5rem; height: .5rem; border-radius: var(--radius-full); flex-shrink: 0; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        // Nada warna per jenis mengikuti palet Pulih (primary / tertiary / secondary)
        $jenis = [
            'MPTB' => [
                'desk' => 'Masa Pengenalan Taruna Baru',
                'url' => route('pemeriksaan-kesehatan.mptb.index'),
                'badge' => 'pl-badge-primary', 'latar' => 'bg-primary-50 text-primary-700', 'warna' => 'var(--primary-700)',
                'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
            ],
            'Samapta' => [
                'desk' => 'Pemeriksaan Semester Ganjil / Genap',
                'url' => route('pemeriksaan-kesehatan.samapta.index'),
                'badge' => 'pl-badge-notice', 'latar' => 'bg-tertiary-50 text-tertiary-800', 'warna' => 'var(--tertiary-500)',
                'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
            ],
            'Lainnya' => [
                'desk' => 'Pemeriksaan Khusus / Fleksibel',
                'url' => null, // menyusul
                'badge' => 'pl-badge-accent', 'latar' => 'bg-secondary-50 text-secondary-800', 'warna' => 'var(--secondary-500)',
                'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z',
            ],
        ];
    @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-6 pr-4 py-6">

        <header class="flex flex-col gap-1">
            <h1 class="h1 m-0 text-primary-900">Pemeriksaan Kesehatan</h1>
            <p class="body-lg m-0 font-medium">{{ now()->locale('id')->translatedFormat('d F Y') }}</p>
        </header>

        <section aria-labelledby="judul-baru" class="flex flex-col gap-3">
            <h2 id="judul-baru" class="h3 m-0 text-primary-700">Mulai Pemeriksaan Baru</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($jenis as $nama => $j)
                    @php $tag = $j['url'] ? 'a' : 'div'; @endphp
                    <{{ $tag }} @if ($j['url']) href="{{ $j['url'] }}" @else aria-disabled="true" @endif
                        @class(['pk-kartu pl-card flex items-center gap-4 px-5 py-4 no-underline', 'opacity-60' => ! $j['url']])>
                        <div class="w-14 h-14 shrink-0 rounded-[0.625rem] flex items-center justify-center {{ $j['latar'] }}">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $j['icon'] }}"/></svg>
                        </div>
                        <div class="flex flex-col gap-0.5 min-w-0 flex-1">
                            <span class="h3 text-primary-900">{{ $nama }}</span>
                            <span class="body-sm text-muted">{{ $j['desk'] }}</span>
                        </div>
                        @if ($j['url'])
                            <svg class="w-5 h-5 shrink-0 text-primary-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                        @else
                            <span class="pl-badge {{ $j['badge'] }}">Segera hadir</span>
                        @endif
                    </{{ $tag }}>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="judul-riwayat" class="flex flex-col gap-3">
            <h2 id="judul-riwayat" class="h3 m-0 text-primary-700">Riwayat Pemeriksaan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse ($riwayat as $item)
                    @php
                        $j = $jenis[$item->jenis];
                        $total = $totalTaruna[$item->jenis];
                        $progress = min(100, (int) round($item->selesai / max(1, $total) * 100));
                    @endphp
                    <a href="{{ $item->url() }}" class="pk-kartu pl-card flex flex-col gap-3 p-5 no-underline">
                        <div class="flex items-center justify-between gap-2">
                            <span class="pl-badge {{ $j['badge'] }}">{{ $item->jenis }}</span>
                            <span class="flex items-center gap-1.5 label-sm text-muted">
                                <span class="pk-titik" style="background: {{ $progress < 100 ? 'var(--secondary-500)' : 'var(--primary-700)' }}"></span>
                                {{ $progress < 100 ? 'Aktif' : 'Selesai' }}
                            </span>
                        </div>

                        <p class="m-0 font-heading font-semibold text-primary-900">{{ $item->labelPeriode() }}</p>

                        <div class="flex items-center gap-3">
                            <div class="pk-bar flex-1" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progres pemeriksaan">
                                <i style="width: {{ $progress }}%; background: {{ $j['warna'] }}"></i>
                            </div>
                            <span class="label-sm text-primary-900 tabular-nums">{{ $progress }}%</span>
                        </div>

                        <div class="flex flex-col gap-1 body-sm text-muted border-t border-neutral-200 pt-3">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                {{ $item->selesai }} dari {{ $total }} taruna diperiksa
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Diperbarui {{ $item->diperbarui->locale('id')->diffForHumans() }}
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="pl-card md:col-span-2 xl:col-span-3 m-0 py-8 text-center body text-muted">Belum ada riwayat pemeriksaan. Mulai dari salah satu jenis di atas.</p>
                @endforelse
            </div>
        </section>

    </main>

</body>
</html>
