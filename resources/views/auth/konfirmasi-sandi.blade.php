{{-- auth/konfirmasi-sandi.blade.php — konfirmasi ulang kata sandi sebelum aksi admin berisiko tinggi (middleware password.confirm),
     design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Konfirmasi Kata Sandi'])
</head>
<body class="pl-auth">

    <div class="h-dvh flex gap-4 p-4 box-border">
        <main class="flex-1 min-w-0 min-h-0 overflow-y-auto flex flex-col gap-8 px-6 py-6 sm:px-12 box-border">
            @include('auth.partials.merek')

            {{-- novalidate: validasi diserahkan ke server (pesan Indonesia, lang/id/validation.php) --}}
            <form method="POST" action="{{ route('password.confirm.store') }}" class="pl-auth__form" novalidate>
                @csrf
                <div>
                    <a href="{{ session('url.intended', route('dashboard')) }}" class="pl-back">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                        Kembali
                    </a>
                    <img src="{{ asset('images/logo-poltek.png') }}" alt="" class="pl-auth__logo-mobile">
                    <h1 class="pl-page-title">Konfirmasi kata sandi</h1>
                    <p class="pl-page-sub">Tindakan ini mengubah akun pengguna. Masukkan kembali kata sandi {{ auth()->user()->username }} untuk melanjutkan.</p>
                </div>

                @include('auth.partials.status')

                <div class="pl-form">
                    <div class="pl-field">
                        <label class="pl-field-label" for="password">Kata sandi</label>
                        @include('auth.partials.sandi', ['nama' => 'password', 'autocomplete' => 'current-password', 'fokus' => true])
                    </div>
                    <button type="submit" class="pl-btn pl-btn-primary pl-auth__kirim justify-center">Konfirmasi</button>
                </div>
            </form>
        </main>

        @include('auth.partials.sorotan', [
            'judul' => 'Satu akun, satu orang.',
            'teks' => 'Setiap perubahan akun pengguna tercatat di Log Sistem atas nama admin yang masuk.',
        ])
    </div>

    @include('auth.partials.skrip-sandi')
</body>
</html>
