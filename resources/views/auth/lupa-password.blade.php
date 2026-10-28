{{-- auth/lupa-password.blade.php — layar "Lupa kata sandi" design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Ubah Kata Sandi'])
</head>
<body class="pl-auth">

    <div class="min-h-screen flex gap-4 p-4 box-border">
        <main class="flex-1 min-w-0 flex flex-col gap-8 px-6 py-6 sm:px-12 box-border">
            @include('auth.partials.merek')

            {{-- Kata sandi baru berlaku setelah disetujui admin di Manajemen Akun --}}
            {{-- novalidate: validasi diserahkan ke server (pesan Indonesia, lang/id/validation.php) --}}
            <form method="POST" action="{{ route('password.store') }}" class="pl-auth__form" novalidate>
                @csrf
                <div>
                    <a href="{{ route('login') }}" class="pl-back">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                        Kembali ke halaman masuk
                    </a>
                    <img src="{{ asset('images/logo-poltek.png') }}" alt="" class="pl-auth__logo-mobile">
                    <h1 class="pl-page-title">Lupa kata sandi</h1>
                    <p class="pl-page-sub">Buat kata sandi baru. Admin poliklinik akan menyetujui permintaan ini sebelum kata sandi berlaku.</p>
                </div>

                @include('auth.partials.status')

                {{-- Nama pengguna sudah terisi (kiriman sebelumnya gagal): langsung fokus ke kata sandi baru --}}
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
                        <label class="pl-field-label" for="password">Kata sandi baru</label>
                        @include('auth.partials.sandi', ['nama' => 'password', 'autocomplete' => 'new-password', 'fokus' => $fokusSandi, 'keterangan' => 'password-cek'])
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="password_confirmation">Ulangi kata sandi baru</label>
                        @include('auth.partials.sandi', ['nama' => 'password_confirmation', 'autocomplete' => 'new-password', 'keterangan' => 'password-cek'])
                    </div>
                    {{-- Syarat dicentang langsung saat mengetik (skrip-sandi); sama dengan aturan server --}}
                    <ul class="pl-cek" id="password-cek" data-cek-sandi="password" data-konfirmasi="password_confirmation" aria-label="Syarat kata sandi">
                        <li data-syarat="panjang">Minimal 8 karakter<span class="sr-only"></span></li>
                        <li data-syarat="huruf">Ada huruf<span class="sr-only"></span></li>
                        <li data-syarat="angka">Ada angka<span class="sr-only"></span></li>
                        <li data-syarat="cocok">Konfirmasi cocok<span class="sr-only"></span></li>
                    </ul>
                    <button type="submit" class="pl-btn pl-btn-primary pl-auth__kirim justify-center">Kirim permintaan</button>
                </div>
            </form>
        </main>

        @include('auth.partials.sorotan', [
            'judul' => 'Satu akun, satu orang.',
            'teks' => 'Setiap perubahan data kesehatan tercatat atas nama pengguna yang masuk.',
        ])
    </div>

    @include('auth.partials.skrip-sandi')
</body>
</html>
