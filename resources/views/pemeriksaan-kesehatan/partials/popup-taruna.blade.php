{{-- pemeriksaan-kesehatan/partials/popup-taruna.blade.php — popup ringkas saat baris taruna di-hover lama (MPTB & Samapta).
     Desain sama dengan popup Laporan/Riwayat Kesehatan. Pakai: @include(..., ['isi' => id <tbody>]).
     Tiap <tr> butuh data-info (json: nama, tingkat, npm, kelas, kamar, jenis_kelamin), tautan [data-detail], dan penanda [data-status].
     Kursor di atas isian tidak membuka popup supaya tidak menutupi pengisian. Halaman boleh memasang window.isianBerubah() untuk konfirmasi isian belum disimpan. --}}
<style>
    .pk-pop { position: fixed; z-index: 40; left: 0; top: 0; width: max(22rem, 320px); max-width: calc(100vw - 1rem); box-sizing: border-box; display: flex; flex-direction: column; gap: .625rem;
              padding: 1rem 1.125rem 1.125rem; border-radius: var(--radius-sm); background: var(--surface-card); color: var(--text); box-shadow: 0 0 0 1px var(--border), var(--shadow-lg);
              font: 400 max(14px, .875rem)/1.4 var(--font-sans);
              opacity: 0; visibility: hidden; transform: translateY(.375rem) scale(.98); transform-origin: top left;
              transition: opacity .16s ease, transform .2s cubic-bezier(.2, .7, .2, 1), visibility 0s .2s; }
    .pk-pop.on { opacity: 1; visibility: visible; transform: none; transition: opacity .16s ease, transform .2s cubic-bezier(.2, .7, .2, 1), top .18s ease, left .18s ease; }
    .pk-pop__kepala { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; padding-bottom: .625rem; border-bottom: 1px solid var(--border); }
    .pk-pop__kepala b { font: 700 max(16px, 1rem)/1.3 var(--font-sans); letter-spacing: -.01em; color: var(--heading); }
    .pk-pop__kepala small { display: block; margin-top: .125rem; color: var(--text-muted); font: 500 max(12px, .75rem)/1.3 var(--font-sans); }
    .pk-pop__kepala .pl-badge { flex: none; }
    .pk-pop__identitas { display: grid; grid-template-columns: 1fr 1fr; gap: .25rem .75rem; margin: 0; padding: .5rem .625rem; border-radius: .625rem; background: var(--cloud); }
    .pk-pop__identitas > div { display: flex; align-items: baseline; gap: .375rem; min-width: 0; }
    .pk-pop__identitas dt { color: var(--text-muted); font-size: max(12px, .75rem); font-weight: 500; }
    .pk-pop__identitas dd { margin: 0; font-weight: 600; font-variant-numeric: tabular-nums; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pk-pop__aksi { display: flex; }
    .pk-pop__aksi .pl-btn { flex: 1; justify-content: center; height: 2.25rem; padding: 0 .75rem; font-size: max(13px, .8125rem); }
    @media (prefers-reduced-motion: reduce) { .pk-pop { transition: opacity .1s, visibility 0s .1s; transform: none; } }
</style>

<div id="pk-pop" class="pk-pop" role="dialog" aria-label="Ringkasan taruna" aria-hidden="true">
    <div class="pk-pop__kepala">
        <div><b data-isi="nama"></b><small data-isi="sub"></small></div>
        <span class="pl-badge" data-isi="status"></span>
    </div>
    <dl class="pk-pop__identitas">
        <div><dt>NPM</dt><dd data-isi="npm"></dd></div>
        <div><dt>Tingkat</dt><dd data-isi="tingkat"></dd></div>
        <div><dt>Kelas</dt><dd data-isi="kelas"></dd></div>
        <div><dt>Kamar</dt><dd data-isi="kamar"></dd></div>
        <div style="grid-column: 1 / -1"><dt>Jenis kelamin</dt><dd data-isi="jenis_kelamin"></dd></div>
    </dl>
    <div class="pk-pop__aksi"><a class="pl-btn pl-btn-secondary" data-isi-href>Lihat Informasi Lebih Lanjut</a></div>
</div>

<script>
    // Sama dengan popup Riwayat Kesehatan: muncul setelah kursor diam sebentar, ikut pindah baris, tetap terbuka saat kursor masuk ke popup.
    (() => {
        const pop = document.getElementById('pk-pop'), isi = document.getElementById(@json($isi));
        const tautan = pop.querySelector('[data-isi-href]');
        let aktif = null, tundaBuka = null, tundaTutup = null, x = 0;

        const buka = (tr) => {
            const t = JSON.parse(tr.dataset.info), sudah = !tr.querySelector('[data-status]').hidden;
            aktif = tr;
            pop.querySelectorAll('.pk-pop__identitas [data-isi], [data-isi="nama"]').forEach((e) => { e.textContent = t[e.dataset.isi] || '-'; });
            pop.querySelector('[data-isi="sub"]').textContent = `Tingkat ${t.tingkat} · ${t.kelas || '-'}`;
            const status = pop.querySelector('[data-isi="status"]');
            status.textContent = sudah ? 'Sudah diperiksa' : 'Belum diperiksa';
            status.className = `pl-badge ${sudah ? 'pl-badge-primary' : 'pl-badge-notice'}`;
            tautan.href = tr.querySelector('[data-detail]').href;
            // di kanan kursor, sejajar atas baris; pindah ke kiri / atas bila tidak muat di layar
            const r = tr.getBoundingClientRect(), w = pop.offsetWidth, h = pop.offsetHeight;
            pop.style.left = `${Math.max(8, x + 20 + w > innerWidth - 8 ? x - 20 - w : x + 20)}px`;
            pop.style.top = `${Math.min(Math.max(8, r.top), Math.max(8, innerHeight - h - 8))}px`;
            pop.offsetWidth; // terapkan posisi dulu, baru kelas .on (agar tidak meluncur dari pojok)
            pop.classList.add('on');
            pop.setAttribute('aria-hidden', 'false');
        };
        const tutup = () => {
            clearTimeout(tundaBuka);
            aktif = null;
            pop.classList.remove('on');
            pop.setAttribute('aria-hidden', 'true');
        };
        const tutupNanti = () => { clearTimeout(tundaBuka); clearTimeout(tundaTutup); tundaTutup = setTimeout(tutup, 220); };

        isi.addEventListener('mousemove', (e) => {
            x = e.clientX;
            const tr = e.target.closest('tr[data-info]');
            if (!tr || e.target.closest('input, textarea, select')) { tutupNanti(); return; }
            clearTimeout(tundaTutup);
            if (tr === aktif) return;
            clearTimeout(tundaBuka);
            tundaBuka = setTimeout(() => buka(tr), pop.classList.contains('on') ? 260 : 600);
        });
        isi.addEventListener('mouseleave', tutupNanti);
        isi.addEventListener('input', tutup);
        pop.addEventListener('mouseenter', () => { clearTimeout(tundaTutup); clearTimeout(tundaBuka); });
        pop.addEventListener('mouseleave', tutupNanti);
        tautan.addEventListener('click', (e) => {
            if (window.isianBerubah?.() && !confirm('Ada isian yang belum disimpan. Tinggalkan halaman ini?')) { e.preventDefault(); }
        });
        addEventListener('scroll', (e) => { if (!pop.contains(e.target)) tutup(); }, true);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') tutup(); });
    })();
</script>
