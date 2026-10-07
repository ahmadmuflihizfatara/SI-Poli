{{-- pemeriksaan-kesehatan/pemeriksaan-kesehatan.blade.php — halaman utama Pemeriksaan Kesehatan, design system Pagi --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Pemeriksaan Kesehatan'])
    <style>
        .pk-bar { height: .5rem; background: var(--surface-card); border-radius: var(--radius-full); overflow: hidden; }
        .pk-bar i { display: block; height: 100%; border-radius: inherit; }
        .pk-titik { width: .5rem; height: .5rem; border-radius: var(--radius-full); flex-shrink: 0; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        // Kartu MPTB/Samapta membuka formulir tambah pemeriksaan (butuh akses Tambah)
        $bisaTambah = auth()->user()->can('tambah-data');
        $urlTambah = fn ($jenis) => $bisaTambah ? route('pemeriksaan-kesehatan.index', ['tambah' => $jenis]).'#form-tambah' : null;
        // Nada warna per jenis mengikuti palet Pulih (primary / tertiary / secondary)
        $jenis = [
            'MPTB' => [
                'desk' => 'Masa Pengenalan Taruna Baru',
                'url' => $urlTambah('MPTB'),
                'badge' => 'pl-badge-primary', 'latar' => 'text-primary-700', 'warna' => 'var(--primary-700)',
                'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
            ],
            'Samapta' => [
                'desk' => 'Pemeriksaan Semester Ganjil / Genap',
                'url' => $urlTambah('Samapta'),
                'badge' => 'pl-badge-notice', 'latar' => 'text-tertiary-800', 'warna' => 'var(--tertiary-500)',
                'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
            ],
            'Lainnya' => [
                'desk' => 'Pemeriksaan Khusus / Fleksibel',
                'url' => null, // menyusul
                'segera' => true,
                'badge' => 'pl-badge-accent', 'latar' => 'text-secondary-800', 'warna' => 'var(--secondary-500)',
                'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z',
            ],
        ];
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <h1 class="pl-page-title">Pemeriksaan Kesehatan</h1>
                <p class="pl-page-sub">{{ now()->locale('id')->translatedFormat('d F Y') }}</p>
            </div>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        @if ($tambah)
            <form id="form-tambah" method="POST" action="{{ route('pemeriksaan-kesehatan.store') }}" class="pl-panel max-w-[45rem]" aria-labelledby="judul-tambah">
                @csrf
                <input type="hidden" name="jenis" value="{{ $tambah }}">
                <h2 class="pl-panel__title" id="judul-tambah">Tambah Pemeriksaan Kesehatan {{ $tambah }}</h2>
                @if ($errors->any())
                    <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
                @endif
                <div class="pl-fields">
                    <div class="pl-field pl-field--wide">
                        <label class="pl-field-label" for="nama">Nama Pemeriksaan Kesehatan</label>
                        <input class="pl-input" id="nama" name="nama" value="{{ old('nama') }}" maxlength="150" placeholder="{{ $tambah === 'MPTB' ? 'Contoh: MPTB Angkatan 2026' : 'Contoh: Samapta I Semester Ganjil 2026/2027' }}" required autofocus>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="tanggal_mulai">Tanggal mulai</label>
                        <input class="pl-input" type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', today()->toDateString()) }}" required
                            onchange="document.getElementById('tanggal_selesai').min = this.value">
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="tanggal_selesai">Tanggal selesai</label>
                        <input class="pl-input" type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" min="{{ old('tanggal_mulai', today()->toDateString()) }}" required>
                    </div>
                </div>
                <div class="pl-form-actions">
                    <a href="{{ route('pemeriksaan-kesehatan.index') }}" class="pl-btn pl-btn-ghost">Batal</a>
                    <button type="submit" class="pl-btn pl-btn-primary">Tambah dan mulai pemeriksaan</button>
                </div>
            </form>
        @endif

        <section aria-labelledby="judul-baru" class="pl-section">
            <h2 id="judul-baru" class="pl-section__title">Mulai Pemeriksaan Baru</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($jenis as $nama => $j)
                    @php $tag = $j['url'] ? 'a' : 'div'; @endphp
                    <{{ $tag }} @if ($j['url']) href="{{ $j['url'] }}" @else aria-disabled="true" @endif
                        @class(['pl-panel !gap-4', 'opacity-60' => ! $j['url']])>
                        <div class="flex items-start justify-between gap-3">
                            <span class="w-12 h-12 shrink-0 rounded-full flex items-center justify-center bg-white {{ $j['latar'] }}">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $j['icon'] }}"/></svg>
                            </span>
                            @unless ($j['url'])
                                <span class="pl-badge {{ $j['badge'] }}">{{ isset($j['segera']) ? 'Segera hadir' : 'Tidak punya akses Tambah' }}</span>
                            @endunless
                        </div>
                        <span class="pl-panel__title">{{ $nama }}</span>
                        <span class="pl-panel__text">{{ $j['desk'] }}</span>
                        @if ($j['url'])
                            <svg class="w-5 h-5 mt-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        @endif
                    </{{ $tag }}>
                @endforeach
            </div>
        </section>

        <section aria-labelledby="judul-riwayat" class="pl-section">
            <h2 id="judul-riwayat" class="pl-section__title">Riwayat Pemeriksaan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse ($riwayat as $item)
                    @php
                        $j = $jenis[$item->jenis];
                        // MPTB: 2 sesi tiap hari dalam rentang; Samapta: sekali per taruna
                        $total = $totalTaruna[$item->jenis] * ($item->jenis === 'MPTB' ? 2 * $item->daftarTanggal()->count() : 1);
                        $progress = min(100, (int) round($item->selesai / max(1, $total) * 100));
                        $selesaiKegiatan = $progress >= 100 || $item->tanggal_selesai->lt(today());
                    @endphp
                    <a href="{{ route('pemeriksaan-kesehatan.show', $item) }}" class="pl-panel !gap-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="pl-badge {{ $j['badge'] }}">{{ $item->jenis }}</span>
                            <span class="flex items-center gap-1.5 label-sm text-muted">
                                <span class="pk-titik" style="background: {{ $selesaiKegiatan ? 'var(--primary-700)' : 'var(--secondary-500)' }}"></span>
                                {{ $selesaiKegiatan ? 'Selesai' : 'Aktif' }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-1">
                            <p class="pl-panel__title !text-lg m-0">{{ $item->nama }}</p>
                            <p class="body-sm text-muted m-0">{{ $item->labelRentang() }}</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="pk-bar flex-1" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progres pemeriksaan">
                                <i style="width: {{ $progress }}%; background: {{ $j['warna'] }}"></i>
                            </div>
                            <span class="label-sm text-primary-900 tabular-nums">{{ $progress }}%</span>
                        </div>

                        <div class="flex flex-col gap-1 body-sm text-muted border-t border-neutral-200 pt-4">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                {{ $item->selesai }} dari {{ $total }} {{ $item->jenis === 'MPTB' ? 'sesi pemeriksaan taruna' : 'taruna diperiksa' }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Diperbarui {{ ($item->diperbarui ?? $item->created_at)->locale('id')->diffForHumans() }}
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="pl-panel md:col-span-2 xl:col-span-3 m-0 text-center body text-muted">Belum ada riwayat pemeriksaan. Mulai dari salah satu jenis di atas.</p>
                @endforelse
            </div>
        </section>

    </main>

</body>
</html>
