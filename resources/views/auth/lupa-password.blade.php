{{-- auth/lupa-password.blade.php — layar "Lupa kata sandi" design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Ubah Kata Sandi'])
</head>
<body>

    <div class="min-h-screen flex flex-wrap gap-4 p-4 box-border">
        <main class="flex-[1_1_30rem] min-w-0 flex flex-col gap-12 px-6 py-8 sm:px-12 box-border">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-poltek.png') }}" alt="Logo Politeknik Siber dan Sandi Negara" class="w-12 h-12 object-contain">
                <div>
                    <div class="font-semibold text-lg leading-tight tracking-tight">SI-Poliklinik</div>
                    <div class="body-sm text-muted">Poliklinik Poltek SSN</div>
                </div>
            </div>

            {{-- Kata sandi baru berlaku setelah disetujui admin di Manajemen Akun --}}
            <form method="POST" action="{{ route('password.store') }}" class="my-auto w-full max-w-[25rem] self-center flex flex-col gap-8">
                @csrf
                <div>
                    <a href="{{ route('login') }}" class="pl-back">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                        Halaman masuk
                    </a>
                    <h1 class="pl-page-title">Lupa kata sandi</h1>
                    <p class="pl-page-sub">Buat kata sandi baru. Admin poliklinik akan menyetujui permintaan ini sebelum kata sandi berlaku.</p>
                </div>

                @if (session('status'))
                    <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
                @endif

                <div class="pl-form !gap-5">
                    <div class="pl-field">
                        <label class="pl-field-label" for="username">Nama pengguna</label>
                        <input class="pl-input" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="password">Kata sandi baru</label>
                        <input class="pl-input" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required aria-describedby="password-hint">
                        <div class="pl-field-hint" id="password-hint">Gunakan minimal 8 karakter dengan kombinasi huruf dan angka.</div>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="password_confirmation">Ulangi kata sandi baru</label>
                        <input class="pl-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="pl-btn pl-btn-primary justify-center mt-2">Kirim permintaan</button>
                </div>
            </form>
        </main>

        @include('auth.partials.sorotan', [
            'judul' => 'Satu akun, satu orang.',
            'teks' => 'Setiap perubahan data kesehatan tercatat atas nama pengguna yang masuk.',
        ])
    </div>

</body>
</html>
