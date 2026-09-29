{{-- manajemen-akun/tambah-akun.blade.php — mengikuti desain "Tambah Akun" (PNG), design system Pulih. Khusus admin (route can:admin). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Tambahkan Akun Baru'])
    <style>
        .ak-card { background: var(--surface-card); border: 1px solid var(--border); border-radius: var(--radius-md); padding: var(--space-6); }
        .ak-h { margin: 0; font: 600 1.5rem/2rem var(--font-heading); color: var(--primary-700); }
        .ak-cek { width: 1.25rem; height: 1.25rem; accent-color: var(--primary-700); cursor: pointer; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php $wajib = '<span class="pl-req" aria-hidden="true"> *</span>'; @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-4 pr-4 py-6">

        <header class="flex flex-col gap-1">
            <a href="{{ route('akun.index') }}" class="pl-btn pl-btn-ghost self-start h-9 pl-2 pr-3">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
            <h1 class="m-0 font-heading font-bold text-[2.25rem] leading-[2.75rem] text-primary-900">Tambahkan Akun Baru</h1>
            <p class="body-lg m-0 font-medium">Tambahkan akun pengguna baru. Kolom bertanda * wajib diisi.</p>
        </header>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
        @endif

        <form id="form-akun" method="POST" action="{{ route('akun.store') }}" class="ak-card flex flex-col gap-5">
            @csrf
            <h2 class="ak-h">Informasi Akun</h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-12 gap-y-4">
                <div class="flex flex-col gap-4">
                    <div class="pl-field">
                        <label class="pl-field-label" for="name">Nama Lengkap{!! $wajib !!}</label>
                        <input class="pl-input" id="name" name="name" value="{{ old('name') }}" maxlength="100" required autofocus>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="username">Nama Pengguna{!! $wajib !!}</label>
                        <input class="pl-input" id="username" name="username" value="{{ old('username') }}" maxlength="50" pattern="[A-Za-z0-9_\-]+" autocomplete="off" required aria-describedby="username-hint">
                        <div class="pl-field-hint" id="username-hint">Dipakai untuk masuk. Huruf, angka, - atau _ tanpa spasi.</div>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <div class="pl-field">
                        <label class="pl-field-label" for="role">Role{!! $wajib !!}</label>
                        <select class="pl-input" id="role" name="role" required>
                            <option value="">Pilih role</option>
                            @foreach (\App\Http\Controllers\AkunController::ROLE as $nilai => $label)
                                <option value="{{ $nilai }}" @selected(old('role') === $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <fieldset class="pl-field border-0 p-0 m-0" id="akses">
                        <legend class="pl-field-label mb-2">Akses</legend>
                        <div class="flex gap-6">
                            @foreach (['edit' => 'Edit', 'tambah' => 'Tambah'] as $nilai => $label)
                                <label class="flex items-center gap-2 body cursor-pointer">
                                    <input type="checkbox" class="ak-cek" name="akses_{{ $nilai }}" value="1" @checked(old() ? old("akses_$nilai") : true)>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <div class="pl-field-hint" id="akses-hint">Tanpa akses, akun hanya bisa melihat data. Admin selalu punya semua akses.</div>
                    </fieldset>
                </div>

                <div class="flex flex-col gap-4">
                    <div class="pl-field">
                        <label class="pl-field-label" for="password">Kata Sandi{!! $wajib !!}</label>
                        <input class="pl-input" id="password" name="password" type="password" minlength="8" autocomplete="new-password" required aria-describedby="password-hint">
                        <div class="pl-field-hint" id="password-hint">Minimal 8 karakter dengan kombinasi huruf dan angka.</div>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="password_confirmation">Verifikasi Kata Sandi{!! $wajib !!}</label>
                        <input class="pl-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                    </div>
                </div>
            </div>

            <button type="button" form="form-akun" data-konfirmasi="konfirmasi-tambah" class="pl-btn pl-btn-primary self-end h-11">Tambah Akun</button>
        </form>
    </main>

    @include('partials.konfirmasi', [
        'id' => 'konfirmasi-tambah', 'nada' => 'primary', 'ikon' => 'tambah',
        'judul' => 'Tambah Akun', 'pesan' => 'Apakah anda menyetujui untuk menambahkan akun berikut?',
        'tombol' => 'Tambah', 'form' => 'form-akun',
    ])

    <script>
        // Admin selalu punya semua akses: centang terkunci saat role admin dipilih
        (() => {
            const role = document.getElementById('role');
            const cek = document.querySelectorAll('#akses input');
            const atur = () => cek.forEach((c) => { c.disabled = role.value === 'admin'; if (c.disabled) c.checked = true; });
            role.addEventListener('change', atur);
            atur();
        })();
    </script>
</body>
</html>
