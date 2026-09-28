{{-- lupa-password.blade.php — mengikuti artboard "Ubah Kata Sandi" (Prototype SI Kesehatan Taruna 2, design system Pulih) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Ubah Kata Sandi'])
</head>
<body class="h-screen flex overflow-hidden">

    <main class="relative flex-1 flex items-center justify-center p-6">
        <img src="{{ asset('images/logo-poltek.png') }}" alt="Logo Politeknik Siber dan Sandi Negara"
            class="absolute left-6 top-6 w-14 h-14 object-contain">

        {{-- Form Diarahkan ke /login --}}
        <form method="GET" action="/login" class="w-[25rem] max-w-full flex flex-col gap-6">
            <div class="flex flex-col gap-2 text-center">
                <h1 class="display m-0 text-primary-900">Ubah Kata Sandi</h1>
                <p class="body m-0 text-muted">Ganti kata sandi lama Anda dengan kata sandi baru.</p>
            </div>

            @if (session('status'))
                <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
            @endif

            <div class="flex flex-col gap-4">
                <div class="pl-field">
                    <label class="pl-field-label" for="password">Kata sandi baru<span class="pl-req" aria-hidden="true"> *</span></label>
                    <input class="pl-input" id="password" name="password" type="password" autocomplete="new-password" minlength="8" required autofocus aria-describedby="password-hint">
                    <div class="pl-field-hint" id="password-hint">Gunakan minimal 8 karakter dengan kombinasi huruf dan angka.</div>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="password_confirmation">Verifikasi kata sandi<span class="pl-req" aria-hidden="true"> *</span></label>
                    <input class="pl-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <button type="submit" class="pl-btn pl-btn-primary h-11 justify-center text-[0.9375rem]">Simpan kata sandi</button>
                <a href="{{ route('login') }}" class="label self-center font-semibold no-underline">Kembali ke halaman masuk</a>
            </div>
        </form>
    </main>

    <aside class="hidden lg:block w-[31.5rem] shrink-0 my-6 mr-6 rounded-2xl overflow-hidden bg-primary-700">
        <img src="{{ asset('images/login-ilustrasi.png') }}" alt="" class="block w-full h-full object-cover object-top">
    </aside>

</body>
</html>
