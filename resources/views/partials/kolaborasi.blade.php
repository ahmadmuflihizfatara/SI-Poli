{{-- partials/kolaborasi.blade.php — pengisian bersama satu formulir oleh beberapa akun (lihat App\Services\Kolaborasi):
     kursor akun lain bergerak di layar, isian yang sedang diisi akun lain diberi garis warna + nama dan dikunci, nilai isian tersinkron.
     Pakai (setelah <form>): @include('partials.kolaborasi', [
         'form' => 'id-form', 'kanal' => 'rikkes.1.2026-11-10.Pagi', 'ubah' => bool,
         'sekali' => false,   // true = formulir data baru: hanya boleh disimpan sekali, akun lain diarahkan ke 'selesai' setelahnya
         'selesai' => url,    // tujuan akun lain setelah formulir sekali-simpan disimpan
         'draf' => id,        // id draf (Kolaborasi::draf) yang ditulis ke ?draf= supaya tautan bisa dibuka akun lain
     ])
     Kait untuk skrip halaman (dikirim ke <form>): "kolaborasi:butuh" (detail = nama isian yang belum ada, mis. baris obat baru),
     "kolaborasi:hapus" (detail = nama isian yang dihapus akun lain), "kolaborasi:disimpan" (detail.nama = akun yang menyimpan).
     Perubahan dari akun lain dikirim sebagai event input/change dengan isTrusted = false; isian ber-data-kolab-awal (mis. jam sekarang)
     disamakan dengan nilai akun pertama yang membuka formulir. --}}
@php
    $tokenKolaborasi = \App\Services\Kolaborasi::token($kanal, (bool) $ubah, (bool) ($sekali ?? false));
@endphp
<style>
    .kolab-lapis { position: fixed; inset: 0; z-index: 60; overflow: hidden; pointer-events: none; }
    .kolab-kursor { position: absolute; left: 0; top: 0; will-change: transform; transition: opacity .2s; }
    .kolab-kursor svg { display: block; width: 1.25rem; height: 1.25rem; filter: drop-shadow(0 1px 1px rgb(0 0 0 / .35)); }
    .kolab-kursor span, .kolab-tag { padding: .0625rem .5rem; border-radius: 9999px; color: #fff; font: 600 12px/1.5 var(--font-sans); white-space: nowrap; }
    .kolab-kursor span { position: absolute; left: 1rem; top: 1rem; }
    .kolab-tag { position: absolute; left: 0; top: 0; border-radius: .375rem .375rem .375rem 0; font-size: 11px; transition: opacity .2s; }
    .kolab-kunci { outline: 2px solid var(--kolab-warna) !important; outline-offset: 1px; }
    .kolab-kunci:not(input, textarea) { pointer-events: none; } /* isian teks cukup readonly; klik tetap tertangkap isiannya, bukan baris tabel */
    .kolab-hadir { position: fixed; right: 1rem; bottom: 1rem; z-index: 61; display: flex; align-items: center; gap: .625rem; max-width: calc(100vw - 2rem); padding: .375rem .875rem .375rem .375rem; border-radius: 9999px;
                   background: var(--surface-card); box-shadow: 0 0 0 1px var(--border), var(--shadow-lg); color: var(--text); font: 500 max(13px, .8125rem)/1.3 var(--font-sans); }
    .kolab-hadir[hidden] { display: none; }
    .kolab-hadir > span:first-child { display: flex; flex: none; }
    .kolab-avatar { display: grid; place-items: center; width: 1.875rem; height: 1.875rem; border-radius: 9999px; box-shadow: 0 0 0 2px var(--surface-card); color: #fff; font: 600 11px/1 var(--font-sans); }
    .kolab-avatar + .kolab-avatar { margin-left: -.5rem; }
    .kolab-pesan { position: fixed; left: 50%; top: 1rem; z-index: 62; max-width: calc(100vw - 2rem); padding: .75rem 1.25rem; border-radius: 1rem; transform: translateX(-50%);
                   background: var(--strong); color: var(--on-primary); box-shadow: var(--shadow-lg); font: 500 max(14px, .875rem)/1.4 var(--font-sans); }
    .kolab-pesan[hidden] { display: none; }
</style>
<div class="kolab-lapis" id="kolab-lapis" aria-hidden="true"></div>
<div class="kolab-hadir" id="kolab-hadir" role="status" aria-live="polite" hidden><span data-avatar></span><span data-teks></span></div>
<div class="kolab-pesan" id="kolab-pesan" role="status" aria-live="polite" hidden></div>
<script>
    (() => {
        const form = document.getElementById(@json($form));
        if (!form || !window.fetch) { return; }
        const token = @json($tokenKolaborasi), alamat = @json(route('kolaborasi')), csrf = @json(csrf_token());
        const bolehUbah = @json((bool) $ubah), sekali = @json((bool) ($sekali ?? false)), tujuanSelesai = @json($selesai ?? null), draf = @json($draf ?? null);
        const lapis = document.getElementById('kolab-lapis'), hadir = document.getElementById('kolab-hadir'), kotakPesan = document.getElementById('kolab-pesan');

        // token ikut terkirim saat formulir disimpan (Kolaborasi::catatSimpan)
        form.append(Object.assign(document.createElement('input'), { type: 'hidden', name: '_kolab', value: token }));
        if (draf) {
            const u = new URL(location.href);
            u.searchParams.set('draf', draf);
            history.replaceState(history.state, '', u);
        }

        const warna = (id) => `hsl(${(id * 137) % 360} 65% 40%)`;
        const inisial = (nama) => nama.split(/\s+/).filter(Boolean).slice(0, 2).map((k) => k[0]).join('').toUpperCase();
        const batasi = (n) => Math.min(1, Math.max(0, n || 0));
        const dilewati = (el) => !el.name || el.name.startsWith('_') || ['submit', 'button', 'reset', 'file'].includes(el.type);
        const elemen = (nama) => [...form.elements].filter((el) => el.name === nama);

        // Nilai semua isian bernama: radio = nilai yang dipilih (kosong bila belum), checkbox = nilainya bila dicentang
        const baca = () => {
            const s = {};
            for (const el of form.elements) {
                if (dilewati(el)) { continue; }
                if (el.type === 'radio') { s[el.name] ??= ''; if (el.checked) { s[el.name] = el.value; } } else if (el.type === 'checkbox') { s[el.name] = el.checked ? el.value : ''; } else { s[el.name] = el.value; }
            }
            return s;
        };

        // Nilai dari akun lain; combobox & kalender diberi tahu lewat event setel agar teks tampilannya ikut berubah
        const terapkan = (nama, nilai) => {
            let els = elemen(nama);
            if (nilai === null) {
                if (els.length) { form.dispatchEvent(new CustomEvent('kolaborasi:hapus', { detail: nama })); }
                return;
            }
            if (!els.length) { form.dispatchEvent(new CustomEvent('kolaborasi:butuh', { detail: nama })); els = elemen(nama); }
            const pilihan = (el) => el.type === 'radio' || el.type === 'checkbox';
            const berubah = els.filter((el) => (pilihan(el) ? el.checked !== (el.value === nilai) : el.value !== nilai));
            if (!berubah.length) { return; }
            berubah.forEach((el) => { if (pilihan(el)) { el.checked = el.value === nilai; } else { el.value = nilai; } });
            // radio: satu event pada pilihan yang kini tercentang, supaya skrip halaman membaca nilai yang benar
            const sasaran = els[0].type === 'radio' ? [els.find((el) => el.checked) ?? els[0]] : berubah;
            sasaran.forEach((el) => ['combo:setel', 'tanggal:setel', 'input', 'change'].forEach((jenis) => el.dispatchEvent(new Event(jenis, { bubbles: jenis === 'input' || jenis === 'change' }))));
        };

        // Isian tampak untuk satu nama: input tersembunyi combobox/kalender → wadahnya, radio → kelompok pilihannya
        const tampak = (nama) => {
            const el = elemen(nama)[0];
            return el && (el.closest('[data-combo], [data-pilih-tanggal]') ?? (el.type === 'radio' ? el.closest('fieldset, .pl-options') : null) ?? el);
        };
        const namaFokus = (el) => (el && el.name && !dilewati(el) ? el.name : el?.closest?.('[data-combo], [data-pilih-tanggal]')?.querySelector('input[name]')?.name) || null;

        // Kursor disimpan relatif terhadap isian/elemen ber-id terdekat, jadi tetap tepat walau ukuran layar & gulir tabel berbeda
        const selektor = (el) => {
            for (let x = el; x && x !== document.documentElement; x = x.parentElement) {
                if (x.name && x.form === form && x.type !== 'hidden') {
                    return `#${CSS.escape(form.id)} [name="${CSS.escape(x.name)}"]` + (x.type === 'radio' ? `[value="${CSS.escape(x.value)}"]` : '');
                }
                if (x.id && !x.id.startsWith('kolab-')) { return `#${CSS.escape(x.id)}`; }
            }
            return 'html';
        };
        const posisi = (k) => {
            if (!k) { return null; }
            const akar = document.documentElement;
            if (k.a === 'html') { return [k.x * akar.scrollWidth - scrollX, k.y * akar.scrollHeight - scrollY]; }
            let el = null;
            try { el = document.querySelector(k.a); } catch { return null; }
            const r = el?.getBoundingClientRect();
            return r && (r.width || r.height) ? [r.left + k.x * r.width, r.top + k.y * r.height] : null;
        };

        let kursorSaya = null;
        document.addEventListener('mousemove', (e) => {
            const a = selektor(e.target), akar = document.documentElement;
            if (a === 'html') { kursorSaya = { a, x: batasi(e.pageX / akar.scrollWidth), y: batasi(e.pageY / akar.scrollHeight) }; return; }
            const r = document.querySelector(a).getBoundingClientRect();
            kursorSaya = { a, x: batasi((e.clientX - r.left) / r.width), y: batasi((e.clientY - r.top) / r.height) };
        }, { passive: true });
        document.documentElement.addEventListener('mouseleave', () => { kursorSaya = null; });

        const pesan = (teks) => {
            kotakPesan.textContent = teks;
            kotakPesan.hidden = false;
            clearTimeout(pesan.jeda);
            pesan.jeda = setTimeout(() => { kotakPesan.hidden = true; }, 4000);
        };

        // ===== Tampilan akun lain: daftar hadir, kursor, isian terkunci =====
        let lain = [], kunci = {}, terkunci = [];
        const kursor = new Map();

        const gambarHadir = () => {
            hadir.hidden = !lain.length;
            hadir.querySelector('[data-avatar]').replaceChildren(...lain.map((p) => Object.assign(document.createElement('span'), {
                className: 'kolab-avatar', textContent: inisial(p.nama), title: p.nama, style: `background: ${warna(p.id)}`,
            })));
            const nama = lain.map((p) => p.nama);
            hadir.querySelector('[data-teks]').textContent = nama.length > 2 ? `${nama.slice(0, 2).join(', ')} +${nama.length - 2} lainnya juga di halaman ini` : `${nama.join(' & ')} juga di halaman ini`;
        };

        const gambarKursor = () => {
            const ada = new Set(lain.map((p) => p.id));
            kursor.forEach((c, id) => { if (!ada.has(id)) { c.el.remove(); kursor.delete(id); } });
            lain.forEach((p) => {
                if (!kursor.has(p.id)) {
                    const el = document.createElement('div');
                    el.className = 'kolab-kursor';
                    el.innerHTML = `<svg viewBox="0 0 24 24"><path d="M4 2.5 20 11l-7.2 1.8L9.5 20z" fill="${warna(p.id)}" stroke="#fff" stroke-width="1.5" stroke-linejoin="round"/></svg>`;
                    el.append(Object.assign(document.createElement('span'), { textContent: p.nama, style: `background: ${warna(p.id)}` }));
                    lapis.append(el);
                    kursor.set(p.id, { el, x: null, y: null });
                }
                kursor.get(p.id).data = p.kursor;
            });
        };

        const gambarKunci = () => {
            terkunci.forEach(({ el, tag, bacaSaja }) => { el.classList.remove('kolab-kunci'); el.style.removeProperty('--kolab-warna'); tag.remove(); bacaSaja.forEach((x) => { x.readOnly = false; }); });
            terkunci = [];
            Object.entries(kunci).forEach(([nama, id]) => {
                const el = tampak(nama), p = lain.find((x) => x.id === id);
                if (!el || !p) { return; }
                el.classList.add('kolab-kunci');
                el.style.setProperty('--kolab-warna', warna(id));
                const tag = Object.assign(document.createElement('div'), { className: 'kolab-tag', textContent: `${p.nama} sedang mengisi`, style: `background: ${warna(id)}` });
                lapis.append(tag);
                const bacaSaja = [...elemen(nama), ...el.querySelectorAll?.('input') ?? []]
                    .filter((x) => !x.readOnly && (x.tagName === 'TEXTAREA' || ['text', 'search', 'number', 'time', 'date', 'email', 'tel'].includes(x.type)));
                bacaSaja.forEach((x) => { x.readOnly = true; });
                terkunci.push({ el, tag, bacaSaja });
            });
        };

        // Gerak kursor dihaluskan tiap frame, jadi tetap mulus walau data datang tiap ±0,5 detik
        const gerak = () => {
            kursor.forEach((c) => {
                const p = posisi(c.data);
                c.el.style.opacity = p ? '1' : '0';
                if (!p) { return; }
                c.x ??= p[0];
                c.y ??= p[1];
                c.x += (p[0] - c.x) * 0.2;
                c.y += (p[1] - c.y) * 0.2;
                c.el.style.transform = `translate(${c.x}px, ${c.y}px)`;
            });
            terkunci.forEach(({ el, tag }) => {
                const r = el.getBoundingClientRect();
                tag.style.opacity = r.width ? '1' : '0';
                tag.style.transform = `translate(${r.left}px, ${r.top - 2}px) translateY(-100%)`;
            });
            requestAnimationFrame(gerak);
        };
        requestAnimationFrame(gerak);

        // ===== Sinkron =====
        let since = 0, terakhir = baca(), fokus = null, jalan = true, sibuk = false, gagal = 0, jeda = null, sudahDisimpan = false;

        const disimpan = (nama) => {
            form.dispatchEvent(new CustomEvent('kolaborasi:disimpan', { detail: { nama } }));
            if (!sekali) { pesan(`${nama} baru saja menyimpan data di halaman ini.`); return; }
            if (sudahDisimpan) { return; }
            sudahDisimpan = true;
            jalan = false;
            document.querySelectorAll(`#${CSS.escape(form.id)} [type="submit"], [form="${CSS.escape(form.id)}"][type="submit"]`).forEach((b) => { b.disabled = true; });
            pesan(`${nama} sudah menyimpan formulir ini. Anda diarahkan kembali…`);
            if (tujuanSelesai) { setTimeout(() => { location.href = tujuanSelesai; }, 2500); }
        };

        const jadwal = (ms) => { clearTimeout(jeda); if (jalan) { jeda = setTimeout(sinkron, ms); } };

        async function sinkron() {
            if (sibuk || !jalan) { return; }
            sibuk = true;
            const kini = baca(), isian = {}, hapus = [];
            if (bolehUbah) {
                for (const k in kini) { if (kini[k] !== terakhir[k]) { isian[k] = kini[k]; } }
                for (const k in terakhir) { if (!(k in kini)) { hapus.push(k); } }
            }
            try {
                const r = await fetch(alamat, {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ token, since, kursor: kursorSaya, fokus, isian, hapus }),
                });
                if ([401, 403, 419].includes(r.status)) { jalan = false; pesan('Pengisian bersama terputus. Muat ulang halaman untuk tersambung lagi.'); return; }
                if (!r.ok) { throw new Error(String(r.status)); }
                const d = await r.json();
                const tolak = new Set(d.tolak);

                // yang terkirim jadi patokan baru; yang ditolak (isian sedang dipegang akun lain) dikembalikan ke nilai sebelumnya
                for (const k in isian) { if (!tolak.has(k)) { terakhir[k] = isian[k]; } }
                hapus.forEach((k) => { if (!tolak.has(k)) { delete terakhir[k]; } });
                tolak.forEach((k) => { if (!(k in d.isian)) { d.isian[k] = terakhir[k] ?? null; } });

                // pertama membuka & sendirian: nilai awal yang bergantung waktu (data-kolab-awal) jadi patokan bersama
                if (since === 0 && !d.pengguna.length) {
                    form.querySelectorAll('[data-kolab-awal][name]').forEach((el) => { delete terakhir[el.name]; });
                }

                kunci = d.kunci;
                lain = d.pengguna;
                // ketikan sendiri yang belum terkirim tidak ditimpa (akan menang di kiriman berikutnya), kecuali isiannya dipegang akun lain
                const sekarang = baca(), diterapkan = [];
                Object.entries(d.isian).forEach(([k, v]) => {
                    if (!tolak.has(k) && !kunci[k] && sekarang[k] !== terakhir[k]) { return; }
                    terapkan(k, v);
                    diterapkan.push(k);
                });
                if (diterapkan.length) {
                    const s = baca();
                    diterapkan.forEach((k) => { if (k in s) { terakhir[k] = s[k]; } else { delete terakhir[k]; } });
                }

                since = d.seq;
                gagal = 0;
                gambarHadir();
                gambarKursor();
                gambarKunci();
                if (d.simpan) { disimpan(d.simpan); }
            } catch {
                gagal += 1;
            } finally {
                sibuk = false;
                // ponytail: polling; cepat saat ada akun lain, jarang saat sendirian atau tab tersembunyi
                jadwal(gagal ? Math.min(10000, 1000 * 2 ** gagal) : document.hidden ? 5000 : lain.length ? 500 : 2000);
            }
        }

        // ketikan & pindah fokus dikirim segera, tidak menunggu jadwal berikutnya
        const segera = () => { if (!sibuk) { jadwal(120); } };
        form.addEventListener('input', (e) => { if (e.isTrusted) { segera(); } });
        form.addEventListener('change', (e) => { if (e.isTrusted) { segera(); } });
        const aturFokus = () => {
            const el = document.activeElement, nama = el && (form.contains(el) || el.form === form) ? namaFokus(el) : null;
            if (nama !== fokus) { fokus = nama; segera(); }
        };
        document.addEventListener('focusin', aturFokus);
        document.addEventListener('focusout', () => setTimeout(aturFokus));
        document.addEventListener('visibilitychange', () => { if (!document.hidden) { segera(); } });
        addEventListener('pagehide', () => {
            const data = new FormData();
            data.append('_token', csrf);
            data.append('token', token);
            data.append('keluar', '1');
            navigator.sendBeacon?.(alamat, data);
        });

        // setelah skrip combobox & kalender (DOMContentLoaded) menyiapkan nilainya
        const mulai = () => { terakhir = baca(); sinkron(); };
        if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', () => setTimeout(mulai)); } else { setTimeout(mulai); }
    })();
</script>
