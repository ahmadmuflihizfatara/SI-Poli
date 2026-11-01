{{-- partials/head.blade.php — kepala halaman bersama. Pakai: @include('partials.head', ['judul' => 'Dashboard']) --}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $judul }} | SI-Poliklinik</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
{{-- 1rem = 16px tepat di layar 1440x1024 (ukuran prototype); layar lain ikut berskala, dibatasi 12-18px --}}
<style>html { font-size: clamp(12px, min(1.1111vw, 1.5625vh), 18px); }</style>
<link rel="stylesheet" href="{{ asset('css/pulih.css') }}?v={{ filemtime(public_path('css/pulih.css')) }}">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    // Tema dari halaman Pengaturan: 'light' / 'dark' / kosong = ikuti sistem
    (function () {
        try {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (savedTheme === 'dark' || (savedTheme !== 'light' && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        } catch (error) {
            // Theme preference is optional when browser storage is unavailable.
        }
    })();

    // Popup galat isian buatan sendiri, pengganti gelembung bawaan browser ("Please fill out this field").
    // Hanya isian pertama yang ditunjuk (seperti bawaan); popup hilang saat petugas mengetik, mengklik, menekan Esc, atau popup induk ditutup.
    // Browser tanpa Popover API tetap memakai gelembung bawaan.
    (function () {
        if (!HTMLElement.prototype.hasOwnProperty('popover')) { return; }
        let pop = null, sasaran = null, sudahTampil = false;
        const angka = (n) => String(n).replace('.', ',');
        const pesan = (el) => {
            const v = el.validity, t = el.type;
            if (v.customError) { return el.validationMessage; }
            if (v.valueMissing) {
                if (t === 'radio') { return 'Pilih salah satu opsi.'; }
                if (t === 'checkbox') { return 'Centang kotak ini untuk melanjutkan.'; }
                if (el.tagName === 'SELECT') { return 'Pilih salah satu dari daftar.'; }
                if (el.closest('[data-pilih-tanggal]')) { return 'Pilih tanggal terlebih dahulu.'; }
                if (t === 'time') { return 'Isi jam terlebih dahulu.'; }
                if (t === 'file') { return 'Pilih berkas terlebih dahulu.'; }
                return 'Kolom ini wajib diisi.';
            }
            if (v.badInput) { return t === 'number' ? 'Masukkan angka yang valid.' : 'Format isian belum sesuai.'; }
            if (v.typeMismatch) { return t === 'email' ? 'Masukkan alamat email yang valid, mis. nama@contoh.com.' : t === 'url' ? 'Masukkan alamat tautan yang valid, diawali https://.' : 'Format isian belum sesuai.'; }
            if (v.rangeUnderflow) { return t === 'number' ? `Nilai paling kecil ${angka(el.min)}.` : `Paling awal ${el.min}.`; }
            if (v.rangeOverflow) { return t === 'number' ? `Nilai paling besar ${angka(el.max)}.` : `Paling akhir ${el.max}.`; }
            if (v.stepMismatch) { return Number(el.step) === 1 ? 'Masukkan bilangan bulat tanpa koma.' : `Gunakan kelipatan ${angka(el.step)}.`; }
            if (v.tooShort) { return `Minimal ${el.minLength} karakter (sekarang ${el.value.length}).`; }
            if (v.tooLong) { return `Maksimal ${el.maxLength} karakter.`; }
            if (v.patternMismatch) { return el.title || 'Format isian belum sesuai.'; }
            return el.validationMessage;
        };
        // yang ditunjuk: tombol kalender untuk nilai tanggal tersembunyi, kelompok pilihan untuk radio, selain itu isiannya sendiri
        const jangkar = (el) => el.closest('[data-pilih-tanggal]')?.querySelector('button.pl-date')
            ?? (el.type === 'radio' ? el.closest('.pl-options, .pl-nrs, fieldset') : null) ?? el;
        const namaIsian = (el, a) => {
            const grup = el.closest('[aria-labelledby]');
            const sumber = el.type === 'radio' ? el.closest('fieldset')?.querySelector('legend')
                : a.labels?.[0] ?? el.labels?.[0] ?? (grup && document.getElementById(grup.getAttribute('aria-labelledby')));
            const nama = (sumber?.textContent ?? '').replace(/\*|\(opsional\)/g, '').replace(/\s+/g, ' ').trim();
            const bagian = el.getAttribute('aria-label');
            return [nama, bagian && bagian !== nama ? bagian : ''].filter(Boolean).join(' · ') || 'Periksa isian';
        };
        const buat = () => {
            const p = document.createElement('div');
            p.className = 'pl-galat';
            p.popover = 'manual';
            p.setAttribute('role', 'alert');
            p.innerHTML = '<span class="pl-galat__ikon" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" stroke-linecap="round"><path d="M12 7v6.5M12 17.25h.01"/></svg></span>'
                + '<span class="pl-galat__isi"><b class="pl-galat__judul"></b><span class="pl-galat__pesan"></span><small class="pl-galat__sisa"></small></span>';
            return p;
        };
        const letakkan = () => {
            if (!sasaran) { return; }
            const r = sasaran.a.getBoundingClientRect(), p = pop.getBoundingClientRect(), jarak = 12;
            // daftar combobox yang terbuka saat difokus ada di bawah isian: popup pindah ke atas bila muat
            const muatAtas = r.top - jarak - p.height >= 0;
            const bawah = sasaran.a.getAttribute('aria-expanded') === 'true' ? !muatAtas : r.bottom + jarak + p.height <= innerHeight || !muatAtas;
            const kiri = Math.min(Math.max(8, r.left), innerWidth - p.width - 8);
            pop.dataset.sisi = bawah ? 'bawah' : 'atas';
            pop.style.left = `${kiri}px`;
            pop.style.top = `${bawah ? r.bottom + jarak : r.top - jarak - p.height}px`;
            pop.style.setProperty('--panah', `${Math.min(Math.max(18, r.left + Math.min(r.width / 2, 28) - kiri), p.width - 18)}px`);
        };
        const tutup = () => {
            if (!sasaran) { return; }
            sasaran.a.classList.remove('pl-galat-sorot');
            if (pop.matches(':popover-open')) { pop.hidePopover(); }
            sasaran = null;
        };
        document.addEventListener('invalid', (e) => {
            e.preventDefault();
            if (sudahTampil) { return; }
            sudahTampil = true;
            setTimeout(() => { sudahTampil = false; }); // event invalid lain dari kiriman yang sama diabaikan
            tutup();
            const el = e.target, a = jangkar(el);
            pop ??= buat();
            (el.closest('dialog[open]') ?? document.body).append(pop); // di dalam dialog modal agar tidak ikut inert
            pop.querySelector('.pl-galat__judul').textContent = namaIsian(el, a);
            pop.querySelector('.pl-galat__pesan').textContent = pesan(el);
            const salah = new Set([...(el.form?.elements ?? [])].filter((x) => x.willValidate && !x.validity.valid).map((x) => (x.type === 'radio' ? x.name : x)));
            const sisa = pop.querySelector('.pl-galat__sisa');
            sisa.textContent = salah.size > 1 ? `+${salah.size - 1} isian lain juga perlu diperbaiki` : '';
            sisa.hidden = salah.size <= 1;
            sasaran = { a };
            a.classList.remove('pl-galat-sorot');
            void a.offsetWidth; // mulai ulang animasi goyang
            a.classList.add('pl-galat-sorot');
            a.scrollIntoView({ block: 'center', behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
            (el.tabIndex < 0 ? a : el).focus({ preventScroll: true });
            pop.showPopover();
            letakkan();
        }, true);
        ['input', 'change', 'pointerdown'].forEach((jenis) => document.addEventListener(jenis, tutup, true));
        document.addEventListener('close', tutup, true);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { tutup(); } }, true);
        document.addEventListener('scroll', () => requestAnimationFrame(letakkan), true);
        addEventListener('resize', letakkan);
    })();

    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    sans: ['"Instrument Sans"', 'system-ui', 'sans-serif'],
                    heading: ['"Instrument Sans"', 'system-ui', 'sans-serif'],
                },
                colors: {
                    primary: { 900: '#0B3B36', 700: '#146B5F', 400: '#6FA89D', 50: '#E3F1EE' },
                    secondary: { 800: '#8A3620', 300: '#F0B79B', 500: '#D8693F', 50: '#FBEAE0' },
                    tertiary: { 800: '#7A5308', 500: '#C88A1E', 50: '#FBEACD' },
                    neutral: { 900: '#232620', 600: '#55584F', 400: '#9A9C92', 200: '#DEDFD7', 50: '#FEFDFC' },
                    muted: '#55584F',
                },
            }
        }
    }
</script>
