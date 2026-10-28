{{-- auth/partials/skrip-sandi.blade.php — perilaku kolom kata sandi: tombol mata & daftar syarat yang diperbarui saat mengetik.
     Daftar syarat: <ul data-cek-sandi="password" data-konfirmasi="password_confirmation"> berisi <li data-syarat="panjang|huruf|angka|cocok">. --}}
<script>
    (() => {
        // Tombol mata: ganti type password <-> text; label & aria-pressed ikut berubah
        document.querySelectorAll('[data-lihat-sandi]').forEach((tombol) => {
            const kolom = document.getElementById(tombol.dataset.lihatSandi);
            tombol.addEventListener('click', () => {
                const tampil = kolom.type === 'password';
                kolom.type = tampil ? 'text' : 'password';
                tombol.setAttribute('aria-pressed', String(tampil));
                tombol.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                kolom.focus();
            });
        });

        // Syarat kata sandi sama dengan aturan server (Password::min(8)->letters()->numbers() + confirmed)
        document.querySelectorAll('[data-cek-sandi]').forEach((daftar) => {
            const sandi = document.getElementById(daftar.dataset.cekSandi), ulang = document.getElementById(daftar.dataset.konfirmasi);
            const syarat = {
                panjang: (s) => s.length >= 8,
                huruf: (s) => /\p{L}/u.test(s),
                angka: (s) => /\d/.test(s),
                cocok: (s) => s !== '' && ulang && s === ulang.value,
            };
            const periksa = () => daftar.querySelectorAll('[data-syarat]').forEach((li) => {
                const ok = syarat[li.dataset.syarat](sandi.value);
                li.classList.toggle('is-ok', ok);
                li.querySelector('.sr-only').textContent = ok ? ' (terpenuhi)' : ' (belum terpenuhi)';
            });
            [sandi, ulang].forEach((k) => k?.addEventListener('input', periksa));
            periksa();
        });
    })();
</script>
