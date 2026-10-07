{{-- pengaturan/pengaturan.blade.php — halaman Pengaturan (dibuka dari profil di sidebar), design system Pagi.
     Tema disimpan di localStorage 'theme' dan dibaca partials/head di setiap halaman. --}}
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
                <p class="pl-page-sub">Atur tampilan aplikasi. Pilihan tersimpan di browser ini.</p>
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
        </section>
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
