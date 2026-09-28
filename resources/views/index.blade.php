{{-- pemeriksaan-kesehatan/index.blade.php — Landing page fitur Pemeriksaan Kesehatan --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Pemeriksaan Kesehatan'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        // Dummy data riwayat pemeriksaan
        $riwayat = [
            [
                'jenis'        => 'MPTB',
                'judul'        => 'MPTB Tahun Anggaran 2026/2027',
                'tanggal_dari' => '1 Sep 2026',
                'tanggal_sampai' => '30 Sep 2026',
                'diperbarui'   => '2 jam lalu',
                'progress'     => 40,
                'status'       => 'aktif',
            ],
            [
                'jenis'        => 'Samapta',
                'judul'        => 'Samapta Semester Ganjil 2026/2027',
                'tanggal_dari' => '1 Agt 2026',
                'tanggal_sampai' => '31 Agt 2026',
                'diperbarui'   => '1 hari lalu',
                'progress'     => 75,
                'status'       => 'aktif',
            ],
            [
                'jenis'        => 'Samapta',
                'judul'        => 'Samapta Semester Genap 2025/2026',
                'tanggal_dari' => '1 Feb 2026',
                'tanggal_sampai' => '28 Feb 2026',
                'diperbarui'   => '7 bulan lalu',
                'progress'     => 100,
                'status'       => 'selesai',
            ],
            [
                'jenis'        => 'MPTB',
                'judul'        => 'MPTB Tahun Anggaran 2025/2026',
                'tanggal_dari' => '1 Sep 2025',
                'tanggal_sampai' => '30 Sep 2025',
                'diperbarui'   => '1 tahun lalu',
                'progress'     => 100,
                'status'       => 'selesai',
            ],
            [
                'jenis'        => 'Lainnya',
                'judul'        => 'Pemeriksaan Khusus Juli 2025',
                'tanggal_dari' => '1 Jul 2025',
                'tanggal_sampai' => '31 Jul 2025',
                'diperbarui'   => '1 tahun lalu',
                'progress'     => 100,
                'status'       => 'selesai',
            ],
            [
                'jenis'        => 'Samapta',
                'judul'        => 'Samapta Semester Ganjil 2025/2026',
                'tanggal_dari' => '1 Agt 2025',
                'tanggal_sampai' => '31 Agt 2025',
                'diperbarui'   => '1 tahun lalu',
                'progress'     => 100,
                'status'       => 'selesai',
            ],
        ];

        // Warna badge per jenis
        $nadaJenis = [
            'MPTB'    => ['badge' => 'bg-primary-50 text-primary-700 ring-1 ring-primary-400',    'progress' => 'bg-primary-700'],
            'Samapta' => ['badge' => 'bg-[#e0eaf5] text-[#1a3d6e] ring-1 ring-[#a8c0e0]',        'progress' => 'bg-[#1a3d6e]'],
            'Lainnya' => ['badge' => 'bg-tertiary-50 text-tertiary-800 ring-1 ring-tertiary-500',  'progress' => 'bg-tertiary-800'],
        ];

        // Ikon SVG per jenis (heroicons outline)
        $ikonJenis = [
            'MPTB'    => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
            'Samapta' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
            'Lainnya' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z',
        ];

        // Deskripsi per jenis
        $deskJenis = [
            'MPTB'    => 'Masa Pengenalan Taruna Baru',
            'Samapta' => 'Pemeriksaan Semester Ganjil / Genap',
            'Lainnya' => 'Pemeriksaan Khusus / Fleksibel',
        ];

        // Route per jenis (akan dibuat nanti)
        $routeJenis = [
            'MPTB'    => '#', // route('pemeriksaan-kesehatan.mptb.create')
            'Samapta' => '#', // route('pemeriksaan-kesehatan.samapta.create')
            'Lainnya' => '#', // route('pemeriksaan-kesehatan.lainnya.create')
        ];
    @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-6 px-6 py-6">

        {{-- Header --}}
        <header class="flex flex-col gap-1">
            <h1 class="h1 m-0 text-primary-900">Pemeriksaan Kesehatan</h1>
            <p class="body-lg m-0 font-medium text-neutral-600">{{ now()->locale('id')->translatedFormat('d F Y') }}</p>
        </header>

        {{-- Tombol Mulai Pemeriksaan Baru --}}
        <section aria-labelledby="label-baru">
            <p id="label-baru" class="label font-semibold text-neutral-400 uppercase tracking-wide text-xs mb-3">Mulai Pemeriksaan Baru</p>
            <div class="grid grid-cols-3 gap-4">
                @foreach (['MPTB', 'Samapta', 'Lainnya'] as $jenis)
                    <a href="{{ $routeJenis[$jenis] }}"
                        class="group flex flex-col items-center gap-3 p-5 bg-white rounded-2xl border border-neutral-200 hover:border-primary-700 hover:shadow-md transition-all duration-150 no-underline">
                        <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-700 flex items-center justify-center group-hover:bg-primary-700 group-hover:text-white transition-colors duration-150">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikonJenis[$jenis] }}"/>
                            </svg>
                        </div>
                        <div class="flex flex-col items-center gap-1">
                            <span class="font-heading font-bold text-primary-900 text-sm">{{ $jenis }}</span>
                            <span class="text-xs text-neutral-400 text-center">{{ $deskJenis[$jenis] }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Riwayat Pemeriksaan --}}
        <section aria-labelledby="label-riwayat" class="flex flex-col gap-3 flex-1">
            <p id="label-riwayat" class="label font-semibold text-neutral-400 uppercase tracking-wide text-xs">Riwayat Pemeriksaan</p>
            <div class="grid grid-cols-3 gap-4">
                @foreach ($riwayat as $item)
                    @php
                        $nada   = $nadaJenis[$item['jenis']];
                        $aktif  = $item['status'] === 'aktif';
                    @endphp
                    <a href="#" class="group flex flex-col gap-3 p-4 bg-white rounded-2xl border border-neutral-200 hover:border-primary-700 hover:shadow-md transition-all duration-150 no-underline">

                        {{-- Badge + Panah --}}
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $nada['badge'] }}">
                                {{ $item['jenis'] }}
                            </span>
                            <svg class="w-4 h-4 text-neutral-200 group-hover:text-primary-700 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                            </svg>
                        </div>

                        {{-- Judul --}}
                        <p class="font-heading font-semibold text-primary-900 text-sm leading-snug m-0">
                            {{ $item['judul'] }}
                        </p>

                        {{-- Progress bar --}}
                        <div class="h-1 w-full bg-neutral-200 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $nada['progress'] }}" style="width: {{ $item['progress'] }}%"></div>
                        </div>

                        {{-- Divider --}}
                        <hr class="border-neutral-200 m-0">

                        {{-- Info: tanggal + last updated --}}
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center gap-1.5 text-xs text-neutral-400">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                                </svg>
                                <span>{{ $item['tanggal_dari'] }} – {{ $item['tanggal_sampai'] }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-neutral-400">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                <span>Diperbarui {{ $item['diperbarui'] }}</span>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="flex justify-start">
                            @if ($aktif)
                                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-primary-50 text-primary-700 ring-1 ring-primary-400">
                                    Aktif
                                </span>
                            @else
                                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-neutral-200 text-neutral-400">
                                    Selesai
                                </span>
                            @endif
                        </div>

                    </a>
                @endforeach
            </div>
        </section>

    </main>

</body>
</html>
