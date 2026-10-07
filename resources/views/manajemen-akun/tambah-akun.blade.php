{{-- manajemen-akun/tambah-akun.blade.php — formulir "Tambah akun" design system Pagi (warna palet aplikasi). Khusus admin (route can:admin). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Tambahkan Akun Baru'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php $wajib = '<span class="pl-req" aria-hidden="true">*</span>'; @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('akun.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">Tambahkan Akun Baru</h1>
                <p class="pl-page-sub">Tambahkan akun pengguna baru. Kolom bertanda * wajib diisi.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
        @endif

        <form id="form-akun" method="POST" action="{{ route('akun.store') }}" class="pl-panel max-w-[60rem]" aria-labelledby="judul-akun">
            @csrf
            <h2 class="pl-panel__title" id="judul-akun">Informasi Akun</h2>
            <div class="pl-fields">
                <div class="pl-field">
                    <label class="pl-field-label" for="name">Nama Lengkap{!! $wajib !!}</label>
                    <input class="pl-input" id="name" name="name" value="{{ old('name') }}" maxlength="100" required autofocus>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="username">Nama Pengguna{!! $wajib !!}</label>
                    <input class="pl-input" id="username" name="username" value="{{ old('username') }}" maxlength="50" pattern="[A-Za-z0-9_\-]+" autocomplete="off" required aria-describedby="username-hint">
                    <div class="pl-field-hint" id="username-hint">Dipakai untuk masuk. Huruf, angka, - atau _ tanpa spasi.</div>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="role">Role{!! $wajib !!}</label>
                    <select class="pl-input" id="role" name="role" required>
                        <option value="">Pilih role</option>
                        @foreach (\App\Http\Controllers\AkunController::ROLE as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(old('role') === $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <fieldset class="pl-field" id="akses">
                    <legend class="mb-2">Akses</legend>
                    <div class="pl-options">
                        @foreach (['edit' => 'Edit', 'tambah' => 'Tambah'] as $nilai => $label)
                            <label class="pl-option"><input type="checkbox" name="akses_{{ $nilai }}" value="1" @checked(old() ? old("akses_$nilai") : true)>{{ $label }}</label>
                        @endforeach
                    </div>
                    <div class="pl-field-hint" id="akses-hint">Tanpa akses, akun hanya bisa melihat data. Admin selalu punya semua akses.</div>
                </fieldset>
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
            <div class="pl-form-actions">
                <button type="button" form="form-akun" data-konfirmasi="konfirmasi-tambah" class="pl-btn pl-btn-primary">Tambah Akun</button>
            </div>
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
