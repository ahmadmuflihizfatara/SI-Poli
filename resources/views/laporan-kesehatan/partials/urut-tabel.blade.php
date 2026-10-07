{{-- laporan-kesehatan/partials/urut-tabel.blade.php — urut tabel per kolom di sisi browser (klik judul kolom: naik ↔ turun).
     Pakai di akhir <body>: @include('laporan-kesehatan.partials.urut-tabel'). Tabel harus .lk dengan <tbody id="isi-tabel">; kolom "Aksi" tidak bisa diurut. --}}
<style>
    .lk .urut { all: unset; box-sizing: border-box; cursor: pointer; display: inline-flex; align-items: center; gap: .25rem; border-radius: .25rem; }
    .lk .urut:hover { color: var(--text); }
    .lk .urut:focus-visible { outline: 2px solid var(--primary-700); outline-offset: 2px; }
    .lk .urut::after { content: '\2195'; opacity: .45; font-size: .875rem; }
    .lk th[aria-sort="ascending"] .urut::after { content: '\2191'; opacity: 1; }
    .lk th[aria-sort="descending"] .urut::after { content: '\2193'; opacity: 1; }
</style>
<script>
    (() => {
        const isi = document.getElementById('isi-tabel');
        const kosong = document.getElementById('tidak-ditemukan');
        const baris = [...isi.querySelectorAll('tr[data-nama]')];
        const ths = [...document.querySelectorAll('.lk thead th')];
        baris.forEach((tr, i) => tr.dataset.asal = i);

        const romawi = { I: 1, II: 2, III: 3, IV: 4 }, tingkat = { Ringan: 1, Sedang: 2, Berat: 3 };
        // Nilai pembanding sel: tanggal d/m/Y → yyyymmdd, tingkat romawi & status → urutan, angka → Number, sisanya teks. Kosong ("-") → null.
        const kunci = (tr, k) => {
            if (k === 0) return +tr.dataset.asal;
            const t = tr.children[k].textContent.trim();
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

        ths.forEach((th, k) => {
            if (th.textContent.trim() === 'Aksi') return;
            const tombol = document.createElement('button');
            tombol.type = 'button';
            tombol.className = 'urut';
            tombol.textContent = th.textContent.trim();
            tombol.title = 'Urutkan kolom ' + tombol.textContent;
            th.replaceChildren(tombol);

            tombol.addEventListener('click', () => {
                const naik = th.getAttribute('aria-sort') !== 'ascending';
                ths.forEach((x) => x.removeAttribute('aria-sort'));
                th.setAttribute('aria-sort', naik ? 'ascending' : 'descending');

                const arah = naik ? 1 : -1;
                baris.sort((a, b) => {
                    const x = kunci(a, k), y = kunci(b, k);
                    if (x === null || y === null) return x === y ? a.dataset.asal - b.dataset.asal : x === null ? 1 : -1; // kosong selalu di bawah
                    return banding(x, y) * arah || a.dataset.asal - b.dataset.asal;
                });
                let no = 0;
                baris.forEach((tr) => {
                    isi.insertBefore(tr, kosong);
                    if (!tr.hidden) tr.firstElementChild.textContent = ++no; // nomor = urutan baris yang tampil
                });
            });
        });
    })();
</script>
