{{-- kejadian-luar-biasa/kejadian-luar-biasa.blade.php — tata letak "Kejadian luar biasa" design system Pagi (warna palet aplikasi).
     Tambah KLB lewat popup (terbuka lagi otomatis saat validasi gagal). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Kejadian Luar Biasa'])
    <style>
        .klb-dialog { width: min(32rem, calc(100vw - 2rem)); }
        .klb-jumlah { background: var(--text); color: var(--surface-page); } /* kontras dengan latar kartu & badge status, terang maupun gelap */

        /* Popup ringkas saat kartu di-hover (desain sama dengan tabel detail KLB) */
        .lk-pop { position: fixed; z-index: 40; left: 0; top: 0; width: max(24rem, 340px); max-width: calc(100vw - 1rem); box-sizing: border-box; display: flex; flex-direction: column; gap: .625rem; max-height: calc(100vh - 1rem); overflow-y: auto;
                  padding: 1rem 1.125rem 1.125rem; border-radius: var(--radius-sm); background: var(--surface-card); color: var(--text); box-shadow: 0 0 0 1px var(--border), var(--shadow-lg);
                  font: 400 max(14px, .875rem)/1.4 var(--font-sans); pointer-events: none;
                  opacity: 0; visibility: hidden; transform: translateY(.375rem) scale(.98); transform-origin: top left;
                  transition: opacity .16s ease, transform .2s cubic-bezier(.2, .7, .2, 1), visibility 0s .2s; }
        .lk-pop.on { opacity: 1; visibility: visible; transform: none; transition: opacity .16s ease, transform .2s cubic-bezier(.2, .7, .2, 1), top .18s ease, left .18s ease; }
        .lk-pop__kepala { padding-bottom: .625rem; border-bottom: 1px solid var(--border); }
        .lk-pop__kepala b { font: 700 max(16px, 1rem)/1.3 var(--font-sans); letter-spacing: -.01em; color: var(--heading); }
        .lk-pop__identitas { display: grid; grid-template-columns: 1fr 1fr; gap: .25rem .75rem; margin: 0; padding: .5rem .625rem; border-radius: .625rem; background: var(--cloud); }
        .lk-pop__identitas > div { display: flex; align-items: baseline; gap: .375rem; min-width: 0; }
        .lk-pop dt { color: var(--text-muted); font-size: max(12px, .75rem); }
        .lk-pop dd { margin: 0; }
        .lk-pop__identitas dd { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600; }
        .lk-pop__isi { margin: 0; }
        .lk-pop__isi dd { margin-top: .125rem; white-space: pre-line; }
        @media (prefers-reduced-motion: reduce) { .lk-pop { transition: opacity .1s, visibility 0s .1s; transform: none; } }
        .pl-panel:is(a, button):hover, .pl-panel:is(a, button):focus-visible { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php $bisaTambah = auth()->user()->can('buat-klb') && auth()->user()->can('tambah-data'); @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <h1 class="pl-page-title">Kejadian Luar Biasa</h1>
                <p class="pl-page-sub">Laporan dan data kejadian luar biasa yang terjadi</p>
            </div>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        @if ($bisaTambah)
            <div>
                <button type="button" class="pl-btn pl-btn-primary" data-buka-tambah>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambah Kejadian Luar Biasa Baru
                </button>
            </div>
        @endif

        <section aria-labelledby="judul-riwayat" class="pl-section">
            <h2 id="judul-riwayat" class="pl-section__title">Riwayat Kejadian Luar Biasa</h2>
            <div id="daftar-klb" class="grid grid-cols-[repeat(auto-fill,minmax(20rem,1fr))] gap-6">
                @forelse ($klb as $k)
                    @php
                        $info = [
                            'nama' => $k->nama, 'deskripsi' => $k->deskripsi, 'keluhan' => $k->keluhan_count.' keluhan',
                            'mulai' => $k->created_at->locale('id')->translatedFormat('j M Y'),
                            'selesai' => $k->selesai_at?->locale('id')->translatedFormat('j M Y') ?? '-',
                            'durasi' => ((int) $k->created_at->copy()->startOfDay()->diffInDays(($k->selesai_at ?? now())->copy()->startOfDay()) + 1).' hari',
                        ];
                    @endphp
                    <a href="{{ route('klb.show', $k) }}" class="pl-panel !gap-3" data-info="{{ json_encode($info) }}">
                        <span @class(['pl-badge pl-badge-solid self-start', 'pl-badge-primary' => $k->selesai(), 'pl-badge-accent' => ! $k->selesai()])>{{ $k->status }}</span>
                        <h3 class="pl-panel__title">{{ $k->nama }}</h3>
                        <p class="pl-panel__text line-clamp-3">{{ $k->deskripsi }}</p>
                        <p class="pl-panel__sub mt-auto pt-2">{{ $k->created_at->locale('id')->translatedFormat('j M Y') }}</p>
                        <span class="pl-badge pl-badge-solid klb-jumlah self-start">{{ $k->keluhan_count }} keluhan</span>
                    </a>
                @empty
                    <p class="col-span-full pl-panel m-0 text-center body text-muted">Belum ada kejadian luar biasa yang dilaporkan.</p>
                @endforelse
            </div>
        </section>
    </main>

    <div id="lk-pop" class="lk-pop" role="tooltip" aria-hidden="true">
        <div class="lk-pop__kepala"><b data-isi="nama"></b></div>
        <dl class="lk-pop__identitas">
            <div><dt>Mulai</dt><dd data-isi="mulai"></dd></div>
            <div><dt>Selesai</dt><dd data-isi="selesai"></dd></div>
            <div><dt>Durasi</dt><dd data-isi="durasi"></dd></div>
            <div><dt>Jumlah</dt><dd data-isi="keluhan"></dd></div>
        </dl>
        <dl class="lk-pop__isi">
            <div><dt>Deskripsi</dt><dd data-isi="deskripsi"></dd></div>
        </dl>
    </div>

    <script>
        // Popup ringkas: muncul setelah kursor diam sebentar di kartu, mengikuti kursor saat pindah kartu
        (() => {
            const pop = document.getElementById('lk-pop'), daftar = document.getElementById('daftar-klb');
            let aktif = null, tunda = null, x = 0;
            const tutup = () => { clearTimeout(tunda); aktif = null; pop.classList.remove('on'); pop.setAttribute('aria-hidden', 'true'); };
            const buka = (kartu) => {
                aktif = kartu;
                const d = JSON.parse(kartu.dataset.info);
                pop.querySelectorAll('[data-isi]').forEach((e) => { e.textContent = d[e.dataset.isi] ?? '-'; });
                const r = kartu.getBoundingClientRect(), w = pop.offsetWidth, h = pop.offsetHeight;
                pop.style.left = `${Math.max(8, x + 20 + w > innerWidth - 8 ? x - 20 - w : x + 20)}px`;
                pop.style.top = `${Math.min(Math.max(8, r.top), Math.max(8, innerHeight - h - 8))}px`;
                pop.offsetWidth;
                pop.classList.add('on');
                pop.setAttribute('aria-hidden', 'false');
            };
            daftar.addEventListener('mousemove', (e) => {
                x = e.clientX;
                const kartu = e.target.closest('a[data-info]');
                if (!kartu) { tutup(); return; }
                if (kartu === aktif) { return; }
                clearTimeout(tunda);
                tunda = setTimeout(() => buka(kartu), pop.classList.contains('on') ? 260 : 450);
            });
            daftar.addEventListener('mouseleave', tutup);
            addEventListener('scroll', tutup, true);
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { tutup(); } });
        })();
    </script>

    @if ($bisaTambah)
        <dialog id="dialog-tambah" class="pl-dialog klb-dialog" aria-labelledby="judul-tambah" data-buka="{{ $errors->any() ? '1' : '' }}">
            <form method="POST" action="{{ route('klb.store') }}" class="pl-dialog__body">
                @csrf
                <h2 class="pl-dialog__title" id="judul-tambah">Tambah Kejadian Luar Biasa</h2>
                @if ($errors->any())
                    <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
                @endif
                <div class="pl-fields">
                    <div class="pl-field pl-field--wide">
                        <label class="pl-field-label" for="nama">Nama Kejadian Luar Biasa</label>
                        <input class="pl-input" id="nama" name="nama" value="{{ old('nama') }}" maxlength="150" required>
                    </div>
                    <div class="pl-field pl-field--wide">
                        <label class="pl-field-label" for="deskripsi">Deskripsi Kejadian Luar Biasa</label>
                        <textarea class="pl-input" id="deskripsi" name="deskripsi" maxlength="2000" required>{{ old('deskripsi') }}</textarea>
                    </div>
                </div>
                <div class="pl-dialog__actions">
                    <button type="button" class="pl-btn pl-btn-ghost" data-tutup>Batal</button>
                    <button type="submit" class="pl-btn pl-btn-primary">Tambah</button>
                </div>
            </form>
        </dialog>

        <script>
            (() => {
                const dialog = document.getElementById('dialog-tambah');
                document.querySelector('[data-buka-tambah]').addEventListener('click', () => {
                    dialog.querySelector('[role="alert"]')?.remove();
                    dialog.showModal();
                });
                dialog.querySelector('[data-tutup]').addEventListener('click', () => dialog.close());
                dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
                if (dialog.dataset.buka) { dialog.showModal(); }
            })();
        </script>
    @endif
</body>
</html>
