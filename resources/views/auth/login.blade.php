{{-- auth/login.blade.php — layar "Masuk" design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Masuk'])
</head>
<body class="pl-auth">

    <div class="h-dvh flex gap-4 p-4 box-border">
        <main class="flex-1 min-w-0 min-h-0 overflow-y-auto flex flex-col gap-8 px-6 py-6 sm:px-12 box-border">
            @include('auth.partials.merek')

            {{-- novalidate: validasi diserahkan ke server (pesan Indonesia, lang/id/validation.php) --}}
            <form method="POST" action="{{ route('login.store') }}" class="pl-auth__form" novalidate>
                @csrf
                <div>
                    <img src="{{ asset('images/logo-poltek.png') }}" alt="" class="pl-auth__logo-mobile">
                    <h1 class="pl-page-title">Masuk</h1>
                    <p class="pl-page-sub">Untuk perawat, psikolog, dan admin poliklinik.</p>
                </div>

                @include('auth.partials.status')

                {{-- Nama pengguna sudah terisi (gagal masuk sebelumnya): langsung fokus ke kata sandi --}}
                @php $fokusSandi = filled(old('username')) && ! $errors->has('username'); @endphp
                <div class="pl-form">
                    <div class="pl-field">
                        <label class="pl-field-label" for="username">Nama pengguna</label>
                        <input class="pl-input" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required
                            @unless ($fokusSandi) autofocus @endunless
                            @error('username') aria-invalid="true" aria-describedby="username-galat" @enderror>
                        @include('auth.partials.galat', ['kolom' => 'username'])
                    </div>
                    <div class="pl-field">
                        <div class="flex justify-between items-baseline gap-3">
                            <label class="pl-field-label" for="password">Kata sandi</label>
                            <a href="{{ route('password.request') }}" class="pl-auth__tautan">Lupa kata sandi?</a>
                        </div>
                        @include('auth.partials.sandi', ['nama' => 'password', 'autocomplete' => 'current-password', 'fokus' => $fokusSandi])
                    </div>
                    <button type="submit" class="pl-btn pl-btn-primary pl-auth__kirim justify-center">Masuk</button>
                </div>
                <p class="pl-auth__catatan">Belum punya akun? Akun dibuat oleh admin poliklinik.</p>
            </form>
        </main>

        @include('auth.partials.sorotan', [
            'judul' => 'Sistem Informasi Poliklinik',
            'teks' => 'Keluhan, kontrol, pemeriksaan berkala, dan konseling dalam satu catatan.',
        ])
    </div>

    @include('auth.partials.skrip-sandi')
</body>
</html>
