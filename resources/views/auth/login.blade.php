{{-- auth/login.blade.php — layar "Masuk" design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Masuk'])
</head>
<body>

    <div class="h-dvh flex gap-4 p-4 box-border">
        <main class="flex-[1_1_30rem] min-w-0 min-h-0 overflow-y-auto flex flex-col gap-8 px-6 py-6 sm:px-12 box-border">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-poltek.png') }}" alt="Logo Politeknik Siber dan Sandi Negara" class="w-12 h-12 object-contain">
                <div>
                    <div class="font-semibold text-lg leading-tight tracking-tight">SI-Poliklinik</div>
                    <div class="body-sm text-muted">Poliklinik Poltek SSN</div>
                </div>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="my-auto w-full max-w-[25rem] self-center flex flex-col gap-8">
                @csrf
                <div>
                    <h1 class="pl-page-title">Masuk</h1>
                    <p class="pl-page-sub">Untuk perawat, psikolog, dan admin poliklinik.</p>
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
                        <div class="flex justify-between items-baseline gap-3">
                            <label class="pl-field-label" for="password">Kata sandi</label>
                            <a href="{{ route('password.request') }}" class="label font-semibold underline underline-offset-4">Lupa kata sandi?</a>
                        </div>
                        <input class="pl-input" id="password" name="password" type="password" autocomplete="current-password" required>
                    </div>
                    <button type="submit" class="pl-btn pl-btn-primary justify-center mt-2">Masuk</button>
                </div>
                <p class="body-sm text-muted m-0">Belum punya akun? Akun dibuat oleh admin poliklinik.</p>
            </form>
        </main>

        @include('auth.partials.sorotan', [
            'judul' => 'Kesehatan taruna, tercatat setiap hari.',
            'teks' => 'Keluhan, kontrol, pemeriksaan berkala, dan konseling dalam satu catatan.',
            'chip' => [['pl-badge-primary', 'Laporan harian'], ['pl-badge-notice', 'MPTB dan samapta'], ['pl-badge-accent', 'Kejadian luar biasa'], ['pl-badge-primary', 'Konseling psikologi']],
        ])
    </div>

</body>
</html>
