{{-- laporan-kesehatan/partials/urut-tabel.blade.php — urut tabel multi-kolom di sisi browser.
     Klik judul kolom: belum diurut → naik, naik → turun, turun → dilepas. Beberapa kolom bisa aktif; kolom yang dipilih
     lebih dulu didahulukan (nomor prioritas tampil di judul). th[aria-sort] dari server = urutan bawaan, diganti saat
     pengguna mulai memilih sendiri.
     Pakai di akhir <body>: @include('laporan-kesehatan.partials.urut-tabel'). Tabel harus .lk dengan <tbody id="isi-tabel">;
     kolom "Aksi" dan th ber-data-tanpa-urut tidak bisa diurut. Event di tabel: "urut:reset" (masuk) kembali ke urutan
     bawaan; "urut:ubah" (keluar) dikirim tiap urutan berubah, kelas .lk-urut-ubah = urutan bukan bawaan. --}}
<style>
    .lk .urut { all: unset; box-sizing: border-box; cursor: pointer; display: inline-flex; align-items: center; gap: .25rem; border-radius: .25rem; }
    .lk .urut:hover { color: var(--text); }
    .lk tbody tr[data-href] { cursor: pointer; }
    .lk .urut:focus-visible { outline: 2px solid var(--primary-700); outline-offset: 2px; }
    .lk .urut::after { content: '\2195'; opacity: .45; font-size: .875rem; }
    .lk th[aria-sort="ascending"] .urut::after { content: '\2191'; opacity: 1; }
    .lk th[aria-sort="descending"] .urut::after { content: '\2193'; opacity: 1; }
    /* nomor prioritas (hanya bila lebih dari satu kolom diurut) */
    .lk .urut[data-ke]::before { content: attr(data-ke); display: grid; place-items: center; order: 2; min-width: 1.125rem; height: 1.125rem; border-radius: 9999px; background: var(--strong); color: var(--on-primary); font: 700 max(11px, .6875rem)/1 var(--font-sans); }
    .lk .urut[data-ke]::after { order: 1; }
</style>
<script>
    (() => {
        const isi = document.getElementById('isi-tabel');
        const tabel = isi.closest('table');
        // Klik baris (di luar tombol / tautan / formulir) membuka detail keluhan; teks yang sedang diseleksi tidak dihitung
        isi.addEventListener('click', (e) => {
            const tr = e.target.closest('tr[data-href]');
            if (!tr || e.target.closest('a, button, form') || getSelection().toString()) return;
            location.href = tr.dataset.href;
        });
        const kosong = document.getElementById('tidak-ditemukan');
        const baris = [...isi.querySelectorAll('tr[data-nama]')];
        const ths = [...document.querySelectorAll('.lk thead th')];
        baris.forEach((tr, i) => tr.dataset.asal = i);

        const romawi = { I: 1, II: 2, III: 3, IV: 4 }, tingkat = { Ringan: 1, Sedang: 2, Berat: 3 };
        // Nilai pembanding sel: data-urut bila ada (sel berisi badge), tanggal d/m/Y → yyyymmdd, tingkat romawi & status → urutan,
        // angka → Number, sisanya teks. Kosong ("-") → null.
        const kunci = (tr, k) => {
            const sel = tr.children[k];
            if (sel.dataset.urut !== undefined) return sel.dataset.urut === '' ? null : +sel.dataset.urut;
            const t = sel.textContent.trim();
            if (t === '' || t === '-') return null;
            const tgl = t.match(/^(\d\d)\/(\d\d)\/(\d{4})$/);
            if (tgl) return +(tgl[3] + tgl[2] + tgl[1]);
            if (romawi[t]) return romawi[t];
            if (tingkat[t]) return tingkat[t];
            return /^\d+$/.test(t) ? +t : t;
        };
        const banding = (a, b) => typeof a === 'number' && typeof b === 'number' ? a - b
            : typeof a === 'number' ? -1 : typeof b === 'number' ? 1
            : a.localeCompare(b, 'id', { numeric: true, sensitivity: 'base' });

        // urutan = [{ k: indeks kolom, arah: 1 | -1 }], indeks 0 = prioritas tertinggi
        const bawaan = ths.flatMap((th, k) => th.hasAttribute('aria-sort') ? [{ k, arah: th.getAttribute('aria-sort') === 'descending' ? -1 : 1 }] : []);
        const salinBawaan = () => bawaan.map((u) => ({ ...u }));
        let urutan = salinBawaan(), pilihanSendiri = false;
        const tombol = [];

        const terapkan = () => {
            ths.forEach((th, k) => {
                const i = urutan.findIndex((u) => u.k === k);
                if (i < 0) { th.removeAttribute('aria-sort'); } else { th.setAttribute('aria-sort', urutan[i].arah === 1 ? 'ascending' : 'descending'); }
                if (!tombol[k]) { return; }
                if (urutan.length > 1 && i >= 0) { tombol[k].dataset.ke = i + 1; } else { delete tombol[k].dataset.ke; }
            });
            baris.sort((a, b) => {
                for (const { k, arah } of urutan) {
                    const x = kunci(a, k), y = kunci(b, k);
                    if (x === null && y === null) continue;
                    if (x === null || y === null) return x === null ? 1 : -1; // kosong selalu di bawah
                    const hasil = banding(x, y) * arah;
                    if (hasil) return hasil;
                }
                return a.dataset.asal - b.dataset.asal;
            });
            // baris ber-data-taruna (taruna sama) tetap bersusun: kelompok ditempatkan di posisi barisnya yang paling atas,
            // urutan di dalam kelompok mengikuti hasil urut di atas
            if (baris[0]?.dataset.taruna !== undefined) {
                const posisiKelompok = new Map(), posisi = new Map(baris.map((tr, i) => [tr, i]));
                baris.forEach((tr, i) => { if (!posisiKelompok.has(tr.dataset.taruna)) { posisiKelompok.set(tr.dataset.taruna, i); } });
                baris.sort((a, b) => posisiKelompok.get(a.dataset.taruna) - posisiKelompok.get(b.dataset.taruna) || posisi.get(a) - posisi.get(b));
            }
            baris.forEach((tr) => isi.insertBefore(tr, kosong));
            tabel.classList.toggle('lk-urut-ubah', pilihanSendiri);
            tabel.dispatchEvent(new Event('urut:ubah'));
            if (typeof bulatkanWarna === 'function') bulatkanWarna();
        };

        ths.forEach((th, k) => {
            if (th.textContent.trim() === 'Aksi' || th.hasAttribute('data-tanpa-urut')) return;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'urut';
            btn.title = 'Urutkan kolom ' + th.textContent.trim() + ' (bisa digabung dengan kolom lain)';
            btn.append(...th.childNodes); // isi th dipindah apa adanya, jadi judul yang disembunyikan (.sr-only) tetap tersembunyi
            th.replaceChildren(btn);
            tombol[k] = btn;

            btn.addEventListener('click', () => {
                if (!pilihanSendiri) { urutan = []; pilihanSendiri = true; } // pilihan pertama pengguna menggantikan urutan bawaan
                const i = urutan.findIndex((u) => u.k === k);
                if (i < 0) { urutan.push({ k, arah: 1 }); } else if (urutan[i].arah === 1) { urutan[i].arah = -1; } else { urutan.splice(i, 1); }
                if (!urutan.length) { urutan = salinBawaan(); pilihanSendiri = false; }
                terapkan();
            });
        });

        tabel.addEventListener('urut:reset', () => { urutan = salinBawaan(); pilihanSendiri = false; terapkan(); });
    })();
</script>
