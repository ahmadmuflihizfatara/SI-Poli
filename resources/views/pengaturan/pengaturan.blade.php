{{-- pengaturan/pengaturan.blade.php — halaman Pengaturan (dibuka dari profil di sidebar), design system Pagi.
     Tema disimpan di localStorage 'theme' dan dibaca partials/head di setiap halaman; kata sandi diubah lewat AuthController::ubahSandi. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Pengaturan'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <h1 class="pl-page-title">Pengaturan</h1>
                <p class="pl-page-sub">Atur tampilan aplikasi dan kata sandi akun Anda.</p>
            </div>
        </header>

        <section class="pl-panel max-w-[45rem]" aria-labelledby="judul-tampilan">
            <h2 class="pl-panel__title" id="judul-tampilan">Tampilan</h2>
            <fieldset class="pl-field">
                <legend class="mb-2">Mode warna</legend>
                <div class="pl-options" id="pilih-tema">
                    @foreach (['light' => 'Terang', 'dark' => 'Gelap', '' => 'Ikuti sistem'] as $nilai => $label)
                        <label class="pl-option"><input type="radio" name="tema" value="{{ $nilai }}">{{ $label }}</label>
                    @endforeach
                </div>
            </fieldset>
            <p class="pl-field-hint">Pilihan mode warna tersimpan di browser ini.</p>
        </section>

        <form method="POST" action="{{ route('pengaturan.sandi') }}" class="pl-panel max-w-[45rem]" aria-labelledby="judul-sandi">
            @csrf
            @method('PUT')
            <h2 class="pl-panel__title" id="judul-sandi">Ubah Kata Sandi</h2>
            @if (session('status'))
                <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
            @endif
            <div class="pl-fields">
                <div class="pl-field">
                    <label class="pl-field-label" for="current_password">Kata Sandi Saat Ini</label>
                    <input class="pl-input" id="current_password" name="current_password" type="password" required autocomplete="current-password" @error('current_password') aria-invalid="true" @enderror>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="password">Kata Sandi Baru</label>
                    <input class="pl-input" id="password" name="password" type="password" required minlength="8" autocomplete="new-password" aria-describedby="password-hint" @error('password') aria-invalid="true" @enderror>
                    <div class="pl-field-hint" id="password-hint">Minimal 8 karakter, huruf dan angka.</div>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="password_confirmation">Verifikasi Kata Sandi Baru</label>
                    <input class="pl-input" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                </div>
            </div>
            <div class="pl-form-actions">
                <button type="submit" class="pl-btn pl-btn-primary">Simpan Kata Sandi</button>
            </div>
        </form>
    </main>

    <script>
        (() => {
            const sistemGelap = matchMedia('(prefers-color-scheme: dark)');
            let tema = '';
            try { tema = localStorage.getItem('theme') || ''; } catch (e) {}
            const terapkan = () => document.documentElement.classList.toggle('dark', tema === 'dark' || (tema === '' && sistemGelap.matches));

            (document.querySelector(`#pilih-tema input[value="${tema}"]`) || document.querySelector('#pilih-tema input[value=""]')).checked = true;
            document.getElementById('pilih-tema').addEventListener('change', (e) => {
                tema = e.target.value;
                try { tema ? localStorage.setItem('theme', tema) : localStorage.removeItem('theme'); } catch (e) {}
                terapkan();
            });
            sistemGelap.addEventListener('change', terapkan);
        })();
    </script>
</body>
</html>
