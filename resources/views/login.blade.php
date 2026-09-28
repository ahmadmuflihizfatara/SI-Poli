{{-- login.blade.php — mengikuti artboard "Login" (Prototype SI Kesehatan Taruna 2, design system Pulih) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Masuk'])
</head>
<body class="h-screen flex overflow-hidden">

    <main class="relative flex-1 flex items-center justify-center p-6">
        <img src="{{ asset('images/logo-poltek.png') }}" alt="Logo Politeknik Siber dan Sandi Negara"
            class="absolute left-6 top-6 w-14 h-14 object-contain">

        {{-- Form Diubah Mengarah Langsung ke Route Dashboard untuk Testing --}}
        <form method="GET" action="/dashboard" class="w-[25rem] max-w-full flex flex-col gap-6">
            <div class="flex flex-col gap-2 text-center">
                <h1 class="display m-0 text-primary-900">Selamat Datang</h1>
                <p class="body m-0 text-muted">Masuk untuk melanjutkan aktivitas Anda.</p>
            </div>

            @if ($errors->any())
                <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
            @endif

            <div class="flex flex-col gap-4">
                <div class="pl-field">
                    <label class="pl-field-label" for="username">Nama pengguna<span class="pl-req" aria-hidden="true"> *</span></label>
                    <input class="pl-input" id="username" name="username" autocomplete="username" required autofocus>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="password">Kata sandi<span class="pl-req" aria-hidden="true"> *</span></label>
                    <input class="pl-input" id="password" name="password" type="password" autocomplete="current-password" required>
                </div>
                <div class="flex justify-end">
                    <a href="{{ route('password.request') }}" class="label font-semibold no-underline">Lupa kata sandi?</a>
                </div>
            </div>

            <button type="submit" class="pl-btn pl-btn-primary h-11 justify-center text-[0.9375rem]">Masuk</button>
        </form>
    </main>

    <aside class="hidden lg:block w-[31.5rem] shrink-0 my-6 mr-6 rounded-2xl overflow-hidden bg-primary-700">
        <img src="{{ asset('images/login-ilustrasi.png') }}" alt="" class="block w-full h-full object-cover object-top">
    </aside>

</body>
</html>
