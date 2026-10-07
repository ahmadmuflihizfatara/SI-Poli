{{-- manajemen-akun/detail-akun.blade.php — "Informasi akun" Normal / Ubah / Persetujuan, design system Pagi (warna palet aplikasi).
     Mode ubah lewat ?ubah=1 (atau otomatis saat validasi gagal). Khusus admin (route can:admin). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Informasi Akun'])
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

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('akun.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">Informasi Akun</h1>
                <p class="pl-page-sub">Informasi akun pengguna</p>
            </div>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
        @endif

        @if ($akun->sandi_diminta_at)
            <section aria-labelledby="judul-sandi" class="pl-alert pl-alert-notice">
                <h2 id="judul-sandi" class="pl-alert__title">Persetujuan Perubahan</h2>
                <p class="pl-alert__text">{{ $akun->username }} hendak melakukan perubahan kata sandi
                    <span class="text-muted">({{ $akun->sandi_diminta_at->locale('id')->diffForHumans() }})</span></p>
                <div class="pl-alert__actions">
                    <form method="POST" action="{{ route('akun.sandi.setujui', $akun) }}">
                        @csrf
                        <button type="submit" class="pl-btn pl-btn-notice">Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('akun.sandi.tolak', $akun) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="pl-btn pl-btn-ghost">Tolak</button>
                    </form>
                </div>
            </section>
        @endif

        <section class="pl-profile" aria-label="Ringkasan akun">
            <div class="pl-profile__who">
                <span class="pl-avatar" aria-hidden="true">{{ $inisial }}</span>
                <div class="min-w-0">
                    <p class="pl-profile__name truncate">{{ $akun->username }}</p>
                    <p class="pl-profile__meta truncate">{{ $akun->name }}</p>
                </div>
            </div>
            <dl class="pl-kvs">
                <div class="pl-kv"><dt>Aktif terakhir</dt><dd>{{ $akun->labelAktif() }}</dd></div>
                <div class="pl-kv"><dt>Ditambahkan</dt><dd>{{ $akun->created_at?->locale('id')->translatedFormat('j F Y') }}</dd></div>
            </dl>
        </section>

        <div class="pl-row">
            @if ($ubah)
                <form method="POST" action="{{ route('akun.update', $akun) }}" class="pl-panel" style="flex: 2 1 30rem" aria-labelledby="judul-info">
                    @csrf
                    @method('PUT')
                    <h2 class="pl-panel__title" id="judul-info">Informasi Akun</h2>
                    <div class="pl-fields">
                        <fieldset class="pl-field" id="akses">
                            <legend class="mb-2">Akses</legend>
                            <div class="pl-options">
                                @foreach ($akses as $nilai => [$label])
                                    <label class="pl-option"><input type="checkbox" name="akses_{{ $nilai }}" value="1" @checked(old() ? old("akses_$nilai") : $akun->{"akses_$nilai"})>{{ $label }}</label>
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
                    <div class="pl-form-actions">
                        <a href="{{ route('akun.show', $akun) }}" class="pl-btn pl-btn-ghost">Batal</a>
                        <button type="submit" class="pl-btn pl-btn-primary">Ubah Informasi</button>
                    </div>
                </form>
            @else
                <section aria-labelledby="judul-info" class="pl-panel" style="flex: 2 1 30rem">
                    <div class="pl-section__head">
                        <h2 id="judul-info" class="pl-panel__title">Informasi Akun</h2>
                        <a href="{{ route('akun.show', [$akun, 'ubah' => 1]) }}" class="pl-btn pl-btn-ghost pl-btn-sm">
                            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                            Ubah Informasi
                        </a>
                    </div>
                    <dl class="m-0 grid grid-cols-[repeat(auto-fit,minmax(11.25rem,1fr))] gap-x-6 gap-y-4">
                        <div class="pl-kv">
                            <dt>Akses</dt>
                            <dd class="flex flex-wrap gap-2">
                                @foreach ($akses as $nilai => [$label, $badge])
                                    @if ($akun->bisa($nilai))<span class="pl-badge {{ $badge }}">{{ $label }}</span>@endif
                                @endforeach
                                @if (! $akun->bisa('edit') && ! $akun->bisa('tambah'))<span class="text-muted">Hanya lihat</span>@endif
                            </dd>
                        </div>
                        <div class="pl-kv"><dt>Role</dt><dd>{{ $role[$akun->role] ?? $akun->role }}</dd></div>
                    </dl>
                </section>
            @endif

            @unless ($diriSendiri)
                <section aria-labelledby="judul-hapus" class="pl-alert pl-alert-accent" style="flex: 1 1 20rem">
                    <h2 id="judul-hapus" class="pl-alert__title">Hapus Akun</h2>
                    <p class="pl-alert__text">Hapus akun ini secara permanen dari sistem</p>
                    <div class="pl-alert__actions">
                        <button type="button" data-konfirmasi="konfirmasi-hapus" class="pl-btn pl-btn-danger">Hapus Akun</button>
                    </div>
                </section>
            @endunless
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
