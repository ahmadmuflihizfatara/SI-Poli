{{-- pemeriksaan-kesehatan/pemeriksaan-kesehatan.blade.php — halaman utama Pemeriksaan Kesehatan, design system Pagi --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Pemeriksaan Kesehatan'])
    <style>
        .pk-dialog { width: min(32rem, calc(100vw - 2rem)); }
        /* Kartu mengambang saat hover/fokus */
        .pl-panel:is(a, button) { transition: box-shadow .2s, transform .2s; }
        .pl-panel:is(a, button):hover, .pl-panel:is(a, button):focus-visible { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        /* Badge jenis: warna penuh yang kontras antar jenis */
        .pk-jenis { font-weight: 600; }
        .pk-jenis::before { display: none; }
        .pk-jenis--MPTB { background: var(--primary-700); color: #fff; padding-left: .75rem; }
        .pk-jenis--Samapta { background: var(--tertiary-500); color: #fff; padding-left: .75rem; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        // Kartu MPTB/Samapta membuka popup tambah pemeriksaan (butuh akses Tambah)
        $bisaTambah = auth()->user()->can('tambah-data');
        // Nada warna per jenis mengikuti palet Pulih (primary / tertiary / secondary)
        $jenis = [
            'MPTB' => [
                'desk' => 'Masa Pengenalan Taruna Baru',
                'bisa' => $bisaTambah,
                'badge' => 'pl-badge-primary', 'latar' => 'var(--primary-700)',
                'icon' => 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
            ],
            'Samapta' => [
                'desk' => 'Pemeriksaan Semester Ganjil / Genap',
                'bisa' => $bisaTambah,
                'badge' => 'pl-badge-notice', 'latar' => 'var(--tertiary-500)',
                'icon' => 'M6.5 6.5v11M17.5 6.5v11M3 9v6M21 9v6M6.5 12h11',
            ],
            'Lainnya' => [
                'desk' => 'Pemeriksaan Khusus / Fleksibel',
                'bisa' => false, // menyusul
                'segera' => true,
                'badge' => 'pl-badge-accent', 'latar' => 'var(--secondary-500)',
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
            <div class="pl-alert pl-alert-info" role="status" style="flex-direction: row; align-items: center; gap: .75rem">
                <svg class="w-6 h-6 flex-none" style="color: var(--link)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <section aria-labelledby="judul-baru" class="pl-section">
            <h2 id="judul-baru" class="pl-section__title">Mulai Pemeriksaan Baru</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($jenis as $nama => $j)
                    @php $tag = $j['bisa'] ? 'button' : 'div'; @endphp
                    <{{ $tag }} @if ($j['bisa']) type="button" data-tambah="{{ $nama }}" @else aria-disabled="true" @endif
                        @class(['pl-panel !gap-4 text-left', 'cursor-pointer' => $j['bisa'], 'opacity-60' => ! $j['bisa']])>
                        <div class="flex items-start justify-between gap-3">
                            <span class="shrink-0" style="color: {{ $j['latar'] }}">
                                <svg class="w-14 h-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $j['icon'] }}"/></svg>
                            </span>
                            @unless ($j['bisa'])
                                <span class="pl-badge {{ $j['badge'] }}">{{ isset($j['segera']) ? 'Segera hadir' : 'Tidak punya akses Tambah' }}</span>
                            @endunless
                        </div>
                        <span class="pl-panel__title">{{ $nama }}</span>
                        <span class="pl-panel__text">{{ $j['desk'] }}</span>
                        @if ($j['bisa'])
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
                        $selesaiKegiatan = $item->selesai();
                    @endphp
                    <a href="{{ route('pemeriksaan-kesehatan.show', $item) }}" class="pl-panel !gap-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="pl-badge pk-jenis pk-jenis--{{ $item->jenis }}">{{ $item->jenis }}</span>
                            <span class="flex items-center gap-1.5 label-sm text-muted">
                                <svg class="w-4 h-4 shrink-0" style="color: {{ $selesaiKegiatan ? 'var(--primary-700)' : 'var(--secondary-500)' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $selesaiKegiatan ? 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' : 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' }}"/></svg>
                                {{ $selesaiKegiatan ? 'Selesai' : 'Aktif' }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-1">
                            <p class="pl-panel__title !text-lg m-0">{{ $item->nama }}</p>
                            <p class="body-sm text-muted m-0">{{ $item->labelRentang() }}</p>
                            @if ($item->keterangan)
                                <p class="body-sm m-0">{{ $item->keterangan }}</p>
                            @endif
                        </div>

                        <div class="flex flex-col gap-1 body-sm text-muted border-t border-neutral-200 pt-4">
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

    @if ($bisaTambah)
        {{-- Popup tambah pemeriksaan; jenis diisi oleh kartu yang ditekan. Gagal validasi = popup terbuka lagi dengan isian lama --}}
        <dialog id="dialog-tambah" class="pl-dialog pk-dialog" aria-labelledby="judul-tambah" data-buka="{{ old('jenis') }}">
            <form method="POST" action="{{ route('pemeriksaan-kesehatan.store') }}" class="pl-dialog__body">
                @csrf
                <input type="hidden" name="jenis" value="{{ old('jenis') }}">
                <h2 class="pl-dialog__title" id="judul-tambah">Tambah Pemeriksaan Kesehatan <span data-jenis>{{ old('jenis') }}</span></h2>
                <p class="pl-dialog__text !mt-0">Pemeriksaan dimulai hari ini dan berjalan sampai dinyatakan selesai dengan tombol <b>Selesaikan pemeriksaan</b>.</p>
                @if ($errors->any())
                    <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
                @endif
                <div class="pl-fields">
                    <div class="pl-field pl-field--wide">
                        <label class="pl-field-label" for="nama">Nama Pemeriksaan Kesehatan</label>
                        <input class="pl-input" id="nama" name="nama" value="{{ old('nama') }}" maxlength="150" required>
                    </div>
                    <div class="pl-field pl-field--wide">
                        <label class="pl-field-label" for="keterangan">Keterangan <span class="text-muted">(opsional)</span></label>
                        <input class="pl-input" id="keterangan" name="keterangan" value="{{ old('keterangan') }}" maxlength="255">
                    </div>
                </div>
                <div class="pl-dialog__actions">
                    <button type="button" class="pl-btn pl-btn-ghost" data-tutup>Batal</button>
                    <button type="submit" class="pl-btn pl-btn-primary">Tambah dan mulai pemeriksaan</button>
                </div>
            </form>
        </dialog>

        <script>
            (() => {
                const dialog = document.getElementById('dialog-tambah'), form = dialog.querySelector('form');
                const contoh = { MPTB: 'Contoh: MPTB Angkatan 2026', Samapta: 'Contoh: Samapta I Semester Ganjil 2026/2027' };
                const buka = (jenis) => {
                    form.jenis.value = jenis;
                    dialog.querySelector('[data-jenis]').textContent = jenis;
                    form.nama.placeholder = contoh[jenis];
                    dialog.showModal();
                };
                document.querySelectorAll('[data-tambah]').forEach((btn) => btn.addEventListener('click', () => {
                    if (form.jenis.value !== btn.dataset.tambah) { form.reset(); } // ganti jenis: isian sebelumnya tidak terbawa
                    dialog.querySelector('[role="alert"]')?.remove();
                    buka(btn.dataset.tambah);
                }));
                dialog.querySelector('[data-tutup]').addEventListener('click', () => dialog.close());
                dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
                if (dialog.dataset.buka) { buka(dialog.dataset.buka); }
            })();
        </script>
    @endif

</body>
</html>
