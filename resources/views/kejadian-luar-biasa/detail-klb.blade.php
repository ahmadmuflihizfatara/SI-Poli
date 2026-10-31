{{-- kejadian-luar-biasa/detail-klb.blade.php — tata letak "Catatan KLB" design system Pagi (warna palet aplikasi). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => $klb->nama])
    <style>
        .kt { table-layout: fixed; }
        .kt th { padding: .75rem .625rem; position: sticky; top: 0; z-index: 1; background: var(--strong); color: var(--on-primary); font-weight: 600; font-size: max(14px, .875rem); border-bottom: 0; }
        .kt td { padding: .5rem .625rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .kt th .urut:hover { color: var(--on-primary); text-decoration: underline; text-underline-offset: 3px; }
        .kt th .urut:focus-visible { outline-color: var(--on-primary); }
        .kt th .urut[data-ke]::before { background: var(--on-primary); color: var(--strong); }
        .pl-toolbar .pl-btn { height: 2.75rem; }

        /* Popup ringkas saat baris di-hover (desain sama dengan laporan kesehatan) */
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
        .lk-pop__isi { display: flex; flex-direction: column; gap: .375rem; margin: 0; }
        .lk-pop__isi dd { margin-top: .125rem; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-clamp: 2; overflow: hidden; }
        @media (prefers-reduced-motion: reduce) { .lk-pop { transition: opacity .1s, visibility 0s .1s; transform: none; } }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $kolom = [['Nama Taruna', 12, true], ['Keluhan', null, true], ['Obat', null, true], ['Tanggal Kontrol Selanjutnya', 12, true], ['Hasil Kontrol Terakhir', null, true]];
        $berlangsung = ! $klb->selesai();
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('klb.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <div class="flex flex-wrap items-center gap-4">
                    <h1 class="pl-page-title">{{ $klb->nama }}</h1>
                    <span aria-label="Status kejadian luar biasa" @class(['pl-badge pl-badge-lg pl-badge-solid','pl-badge-primary' => ! $berlangsung, 'pl-badge-accent' => $berlangsung])>{{ $klb->status }}{{ $klb->selesai_at ? ' sejak '.$klb->selesai_at->locale('id')->translatedFormat('j F Y') : '' }}</span>
                </div>
                <p class="pl-page-sub max-w-4xl">{{ $klb->deskripsi }}</p>
            </div>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        <div class="pl-toolbar">
            <label class="pl-search">
                <span class="pl-sr">Cari nama taruna</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="cari" type="search" class="pl-input" placeholder="Cari nama taruna">
            </label>
            @include('partials.saring', ['kunci' => 'tingkat', 'label' => 'Tingkat', 'opsi' => ['I' => 'Tingkat I', 'II' => 'Tingkat II', 'III' => 'Tingkat III', 'IV' => 'Tingkat IV']])
            @include('partials.saring', ['kunci' => 'kontrol', 'label' => 'Kontrol', 'opsi' => ['terlambat' => 'Terlambat', 'hari-ini' => 'Hari ini', 'mendatang' => 'Akan datang']])
            <button type="button" id="reset-saring" class="pl-btn pl-btn-ghost" disabled>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.992 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                Reset filter
            </button>
            @if ($berlangsung)
                <div class="pl-toolbar__end">
                    @can('edit-data')
                        <button type="button" data-konfirmasi="konfirmasi-selesai" class="pl-btn pl-btn-ghost">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            Kejadian Luar Biasa Selesai
                        </button>
                    @endcan
                    @can('tambah-data')
                        <a href="{{ route('klb.keluhan.create', $klb) }}" class="pl-btn pl-btn-primary">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Tambah Keluhan
                        </a>
                    @endcan
                </div>
            @endif
        </div>

        <div class="pl-table-wrap max-h-[60vh]">
            <table class="pl-table kt lk">
                <colgroup>
                    @foreach ($kolom as [, $lebar])
                        <col @if ($lebar) style="width: {{ $lebar }}rem" @endif>
                    @endforeach
                </colgroup>
                <thead>
                    <tr>
                        @foreach ($kolom as [$judul, , $tengah])
                            <th scope="col" class="c" @if (! in_array($judul, ['Nama Taruna', 'Tanggal Kontrol Selanjutnya'])) data-tanpa-urut @endif>{{ $judul }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="isi-tabel">
                    @forelse ($klb->keluhan as $k)
                        @php
                            $kontrol = $k->tanggal_kontrol_selanjutnya;
                            $info = [
                                'nama' => $k->nama, 'npm' => $k->npm, 'tingkat' => $k->tingkat, 'kelas' => $k->kelas, 'kamar' => $k->kamar, 'kelamin' => $k->jenis_kelamin,
                                'keluhan' => $k->keluhan, 'terapi' => $k->terapi, 'evaluasi' => $k->kontrolTerakhir?->hasil_kontrol ?? '-', 'kontrol' => $kontrol->format('d/m/Y'),
                            ];
                        @endphp
                        <tr data-nama="{{ strtolower($k->nama) }}" data-tingkat="{{ $k->tingkat }}" data-kontrol="{{ $kontrol->lt(today()) ? 'terlambat' : ($kontrol->isToday() ? 'hari-ini' : 'mendatang') }}"
                            data-info="{{ json_encode($info) }}" data-href="{{ route('klb.keluhan.show', [$klb, $k]) }}">
                            <td class="font-medium">{{ $k->nama }}</td>
                            <td class="c">{{ $k->keluhan }}</td>
                            <td class="c">{{ $k->terapi }}</td>
                            <td class="c tabular-nums" data-urut="{{ $kontrol->format('Ymd') }}">{{ $k->tanggal_kontrol_selanjutnya->locale('id')->translatedFormat('j F Y') }}</td>
                            <td class="c">{{ $k->kontrolTerakhir?->hasil_kontrol ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">Belum ada keluhan pada kejadian luar biasa ini.</td></tr>
                    @endforelse
                    <tr id="tidak-ditemukan" hidden>
                        <td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">Tidak ada keluhan yang cocok.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pl-table-foot">
            @include('laporan-kesehatan.partials.zoom-tabel')
        </div>
    </main>

    @if ($berlangsung)
        @can('edit-data')
            @include('partials.konfirmasi', [
                'id' => 'konfirmasi-selesai', 'nada' => 'primary', 'ikon' => 'selesai',
                'judul' => 'Kejadian Luar Biasa Selesai', 'pesan' => 'Apakah anda menyetujui untuk menyatakan kejadian luar biasa berikut selesai?',
                'tombol' => 'Selesai', 'aksi' => route('klb.selesai', $klb), 'metode' => 'PATCH',
            ])
        @endcan
    @endif

    <div id="lk-pop" class="lk-pop" role="tooltip" aria-hidden="true">
        <div class="lk-pop__kepala"><b data-isi="nama"></b></div>
        <dl class="lk-pop__identitas">
            <div><dt>NPM</dt><dd data-isi="npm"></dd></div>
            <div><dt>Tingkat</dt><dd data-isi="tingkat"></dd></div>
            <div><dt>Kelas</dt><dd data-isi="kelas"></dd></div>
            <div><dt>Kamar</dt><dd data-isi="kamar"></dd></div>
            <div style="grid-column: 1 / -1"><dt>Jenis kelamin</dt><dd data-isi="kelamin"></dd></div>
        </dl>
        <dl class="lk-pop__isi">
            <div><dt>Keluhan</dt><dd data-isi="keluhan"></dd></div>
            <div><dt>Obat</dt><dd data-isi="terapi"></dd></div>
            <div><dt>Hasil kontrol terakhir</dt><dd data-isi="evaluasi"></dd></div>
            <div><dt>Kontrol selanjutnya</dt><dd data-isi="kontrol"></dd></div>
        </dl>
    </div>

    <script>
        // Popup ringkas: muncul setelah kursor diam sebentar di baris, mengikuti kursor saat pindah baris
        (() => {
            const pop = document.getElementById('lk-pop'), isi = document.getElementById('isi-tabel');
            let aktif = null, tunda = null, x = 0;
            const tutup = () => { clearTimeout(tunda); aktif = null; pop.classList.remove('on'); pop.setAttribute('aria-hidden', 'true'); };
            const buka = (tr) => {
                aktif = tr;
                const d = JSON.parse(tr.dataset.info);
                pop.querySelectorAll('[data-isi]').forEach((e) => { e.textContent = d[e.dataset.isi] ?? '-'; });
                const r = tr.getBoundingClientRect(), w = pop.offsetWidth, h = pop.offsetHeight;
                pop.style.left = `${Math.max(8, x + 20 + w > innerWidth - 8 ? x - 20 - w : x + 20)}px`;
                pop.style.top = `${Math.min(Math.max(8, r.top), Math.max(8, innerHeight - h - 8))}px`;
                pop.offsetWidth;
                pop.classList.add('on');
                pop.setAttribute('aria-hidden', 'false');
            };
            isi.addEventListener('mousemove', (e) => {
                x = e.clientX;
                const tr = e.target.closest('tr[data-info]');
                if (!tr) { tutup(); return; }
                if (tr === aktif) { return; }
                clearTimeout(tunda);
                tunda = setTimeout(() => buka(tr), pop.classList.contains('on') ? 260 : 450);
            });
            isi.addEventListener('mouseleave', tutup);
            addEventListener('scroll', tutup, true);
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { tutup(); } });
        })();

        // Cari nama + filter pilihan ganda (partials.saring): dalam satu filter ATAU, antarfilter DAN
        (() => {
            const baris = [...document.querySelectorAll('#isi-tabel tr[data-nama]')];
            const kosong = document.getElementById('tidak-ditemukan');
            const cari = document.getElementById('cari'), reset = document.getElementById('reset-saring');
            const filter = [...document.querySelectorAll('[data-saring]')];
            const dipilih = (s) => [...s.querySelectorAll('input:checked')].map((i) => i.value);
            filter.forEach((s) => s.querySelectorAll('[data-jumlah]').forEach((el) => {
                el.textContent = baris.filter((tr) => tr.dataset[s.dataset.saring] === el.dataset.jumlah).length;
            }));
            const saring = () => {
                const q = cari.value.trim().toLowerCase();
                const aturan = filter.map((s) => [s.dataset.saring, dipilih(s)]).filter(([, nilai]) => nilai.length);
                let ada = 0;
                baris.forEach((tr) => {
                    tr.hidden = !(tr.dataset.nama.includes(q) && aturan.every(([kunci, nilai]) => nilai.includes(tr.dataset[kunci])));
                    if (!tr.hidden) ada++;
                });
                kosong.hidden = ada > 0 || baris.length === 0;
                reset.disabled = !q && !aturan.length;
            };
            cari.addEventListener('input', saring);
            filter.forEach((s) => s.addEventListener('change', saring));
            reset.addEventListener('click', () => {
                cari.value = '';
                filter.forEach((s) => { s.querySelectorAll('input:checked').forEach((i) => { i.checked = false; }); s.ringkas?.(); });
                saring();
                cari.focus();
            });
        })();
    </script>
    @include('laporan-kesehatan.partials.urut-tabel')
</body>
</html>
