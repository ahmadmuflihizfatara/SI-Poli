{{-- manajemen-akun/detail-akun.blade.php — mengikuti desain "Detail Akun" Normal / Edit / Alert (PNG), design system Pulih.
     Mode ubah lewat ?ubah=1 (atau otomatis saat validasi gagal). Khusus admin (route can:admin). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Informasi Akun'])
    <style>
        .da-avatar { width: 4.875rem; height: 4.875rem; border-radius: 9999px; background: var(--primary-700); color: #fff;
            display: flex; align-items: center; justify-content: center; font: 500 2rem/1 var(--font-sans); flex-shrink: 0; }
        .da-ringkas { background: var(--primary-50); border-radius: var(--radius-sm); padding: .875rem 1.125rem; display: flex; align-items: center; gap: 1rem; }
        .da-kartu { background: var(--surface-card); border: 1px solid var(--neutral-400); border-radius: var(--radius-sm); padding: 1.25rem 1.5rem; }
        .da-h { margin: 0; font: 600 1.125rem/1.5rem var(--font-heading); }
        .da-nilai { border: 1px solid var(--neutral-400); border-radius: var(--radius-sm); padding: .625rem .75rem; min-height: 2.75rem; display: flex; align-items: center; }
        .da-cek { width: 1.25rem; height: 1.25rem; accent-color: var(--primary-700); cursor: pointer; }
        .da-btn-hapus { background: var(--secondary-800); color: #fff; box-shadow: 0 4px 4px rgba(0,0,0,.25); }
        .da-btn-hapus:hover { background: #6e2a18; color: #fff; }
        .da-btn-setuju { background: var(--tertiary-800); color: #fff; box-shadow: 0 4px 4px rgba(0,0,0,.25); }
        .da-btn-setuju:hover { background: #5e4006; color: #fff; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $ubah = request()->boolean('ubah') || $errors->any();
        $diriSendiri = $akun->is(auth()->user());
        $inisial = collect(preg_split('/\s+/', trim($akun->name)))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
        $role = \App\Http\Controllers\AkunController::ROLE;
        $akses = ['edit' => ['Edit', 'pl-badge-accent'], 'tambah' => ['Tambah', 'pl-badge-primary']];
    @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-4 pr-4 py-6">

        <header class="flex flex-col gap-1">
            <a href="{{ route('akun.index') }}" class="pl-btn pl-btn-ghost self-start h-9 pl-2 pr-3">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
            <h1 class="m-0 font-heading font-bold text-[2.25rem] leading-[2.75rem] text-primary-900">Informasi Akun</h1>
            <p class="body-lg m-0 font-medium">Informasi akun pengguna</p>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info max-w-[57.5rem]" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="pl-alert pl-alert-accent max-w-[57.5rem]" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="flex flex-col gap-5 max-w-[57.5rem]">
            <section aria-label="Ringkasan akun" class="da-ringkas max-w-[46.75rem]">
                <div class="da-avatar" aria-hidden="true">{{ $inisial }}</div>
                <div class="flex flex-col gap-1 min-w-0">
                    <p class="m-0 font-heading font-semibold text-[1.375rem] leading-7 text-primary-700 truncate">{{ $akun->username }}</p>
                    <p class="body m-0 truncate">{{ $akun->name }}</p>
                </div>
                <dl class="ml-auto flex gap-8 m-0 body-sm text-right shrink-0">
                    <div><dt class="text-muted">Aktif terakhir</dt><dd class="m-0 font-semibold">{{ $akun->labelAktif() }}</dd></div>
                    <div><dt class="text-muted">Ditambahkan</dt><dd class="m-0 font-semibold">{{ $akun->created_at?->locale('id')->translatedFormat('j F Y') }}</dd></div>
                </dl>
            </section>

            @if ($ubah)
                <form method="POST" action="{{ route('akun.update', $akun) }}" class="da-kartu flex flex-col gap-4">
                    @csrf
                    @method('PUT')
                    <h2 class="da-h text-primary-700">Informasi Akun</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5 max-w-[44rem]">
                        <fieldset class="border-0 p-0 m-0 flex flex-col gap-3" id="akses">
                            <legend class="pl-field-label mb-3">Akses</legend>
                            <div class="flex gap-6">
                                @foreach ($akses as $nilai => [$label])
                                    <label class="flex items-center gap-2 body cursor-pointer">
                                        <input type="checkbox" class="da-cek" name="akses_{{ $nilai }}" value="1" @checked(old() ? old("akses_$nilai") : $akun->{"akses_$nilai"})>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                        <div class="pl-field">
                            <label class="pl-field-label" for="role">Role</label>
                            <select class="pl-input" id="role" name="role" required @disabled($diriSendiri)>
                                @foreach ($role as $nilai => $label)
                                    <option value="{{ $nilai }}" @selected(old('role', $akun->role) === $nilai)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @if ($diriSendiri)
                                {{-- select disabled tidak ikut terkirim --}}
                                <input type="hidden" name="role" value="{{ $akun->role }}">
                                <div class="pl-field-hint">Role akun sendiri tidak bisa diubah.</div>
                            @endif
                        </div>
                        <div class="pl-field">
                            <label class="pl-field-label" for="password">Kata Sandi Baru</label>
                            <input class="pl-input" id="password" name="password" type="password" minlength="8" autocomplete="new-password" aria-describedby="password-hint">
                            <div class="pl-field-hint" id="password-hint">Kosongkan bila tidak diubah. Minimal 8 karakter, huruf dan angka.</div>
                        </div>
                        <div class="pl-field">
                            <label class="pl-field-label" for="password_confirmation">Verifikasi Kata Sandi</label>
                            <input class="pl-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="flex justify-end items-center gap-3">
                        <a href="{{ route('akun.show', $akun) }}" class="pl-btn pl-btn-ghost h-9">Batal</a>
                        <button type="submit" class="pl-btn pl-btn-primary h-9">Ubah Informasi</button>
                    </div>
                </form>
            @else
                <section aria-labelledby="judul-info" class="da-kartu flex flex-col gap-4">
                    <h2 id="judul-info" class="da-h text-primary-700">Informasi Akun</h2>
                    <div class="grid grid-cols-1 md:grid-cols-[16rem_minmax(0,21rem)] gap-x-8 gap-y-5">
                        <div class="flex flex-col gap-3">
                            <span class="pl-field-label">Akses</span>
                            <span class="flex gap-4">
                                @foreach ($akses as $nilai => [$label, $badge])
                                    @if ($akun->bisa($nilai))<span class="pl-badge {{ $badge }} min-w-[4.75rem] justify-center">{{ $label }}</span>@endif
                                @endforeach
                                @if (! $akun->bisa('edit') && ! $akun->bisa('tambah'))<span class="body text-muted">Hanya lihat</span>@endif
                            </span>
                        </div>
                        <div class="flex flex-col gap-2">
                            <span class="pl-field-label">Role</span>
                            <div class="da-nilai body">{{ $role[$akun->role] ?? $akun->role }}</div>
                        </div>
                    </div>
                    <a href="{{ route('akun.show', [$akun, 'ubah' => 1]) }}" class="pl-btn pl-btn-primary self-end h-9">Ubah Informasi</a>
                </section>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[16.375rem_16.375rem] gap-x-9 gap-y-4">
                @unless ($diriSendiri)
                    <section aria-labelledby="judul-hapus" class="rounded-[var(--radius-sm)] bg-secondary-50 p-5 flex flex-col gap-3">
                        <h2 id="judul-hapus" class="da-h text-secondary-800">Hapus Akun</h2>
                        <p class="body m-0">Hapus akun ini secara permanen dari sistem</p>
                        <button type="button" data-konfirmasi="konfirmasi-hapus" class="pl-btn da-btn-hapus self-end h-8 w-[7.625rem] justify-center">Hapus Akun</button>
                    </section>
                @endunless

                @if ($akun->sandi_diminta_at)
                    <section aria-labelledby="judul-sandi" class="rounded-[var(--radius-sm)] bg-tertiary-50 p-5 flex flex-col gap-3">
                        <h2 id="judul-sandi" class="da-h text-tertiary-800">Persetujuan Perubahan</h2>
                        <p class="body m-0">{{ $akun->username }} hendak melakukan perubahan kata sandi
                            <span class="text-muted">({{ $akun->sandi_diminta_at->locale('id')->diffForHumans() }})</span></p>
                        <div class="flex justify-end items-center gap-2">
                            <form method="POST" action="{{ route('akun.sandi.tolak', $akun) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="pl-btn pl-btn-ghost h-8 !text-tertiary-800">Tolak</button>
                            </form>
                            <form method="POST" action="{{ route('akun.sandi.setujui', $akun) }}">
                                @csrf
                                <button type="submit" class="pl-btn da-btn-setuju h-8 w-[7.625rem] justify-center">Setujui</button>
                            </form>
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </main>

    @unless ($diriSendiri)
        @include('partials.konfirmasi', [
            'id' => 'konfirmasi-hapus', 'nada' => 'secondary', 'ikon' => 'hapus',
            'judul' => 'Hapus Akun', 'pesan' => "Apakah anda menyetujui untuk menghapus akun {$akun->username}?",
            'tombol' => 'Hapus', 'aksi' => route('akun.destroy', $akun), 'metode' => 'DELETE',
        ])
    @endunless

    @if ($ubah)
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
    @endif
</body>
</html>
