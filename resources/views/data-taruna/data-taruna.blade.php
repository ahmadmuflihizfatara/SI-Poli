{{-- data-taruna/data-taruna.blade.php — "Data Taruna" design system Pagi (warna palet aplikasi). Khusus admin (route can:admin).
     Taruna di sini yang dipilih saat mencatat Laporan Kesehatan. Tambah & ubah lewat satu popup; hapus lewat konfirmasi. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Data Taruna'])
    <style>
        /* Tabel: sama dengan tabel Laporan Kesehatan (kepala hijau tua, teks putih tebal) */
        .dm th { padding: .75rem .625rem; position: sticky; top: 0; z-index: 1; background: var(--strong); color: var(--on-primary); font-weight: 600; font-size: max(14px, .875rem); border-bottom: 0; }
        .dm td { padding: .5rem .625rem; }
        .dm-dialog { width: min(40rem, calc(100vw - 2rem)); max-height: calc(100vh - 2rem); }
        .pl-toolbar .pl-btn { height: 2.75rem; } /* tinggi sama dengan kolom cari, seperti toolbar Laporan Kesehatan */
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $wajib = '<span class="pl-req" aria-hidden="true">*</span>';
        $kolom = [['No', true], ['Nama Taruna', false], ['NPM', false], ['Tingkat', true], ['Kelas', false], ['Jenis Kelamin', false], ['Kamar', true], ['Tindakan', true]];
        // gagal validasi: popup dibuka lagi untuk taruna yang sama (ubah) atau sebagai tambah
        $ubahId = old('_ubah');
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <h1 class="pl-page-title">Data Taruna</h1>
                <p class="pl-page-sub">Daftar taruna yang dapat dipilih saat mencatat laporan kesehatan</p>
            </div>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        <form method="GET" action="{{ route('taruna.index') }}" class="pl-toolbar">
            <label class="pl-search">
                <span class="pl-sr">Cari nama atau NPM taruna</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="search" name="q" value="{{ $q }}" class="pl-input" placeholder="Cari nama atau NPM taruna...">
            </label>
            @include('partials.saring', ['kunci' => 'tingkat', 'label' => 'Tingkat', 'opsi' => ['I' => 'Tingkat I', 'II' => 'Tingkat II', 'III' => 'Tingkat III', 'IV' => 'Tingkat IV'], 'terpilih' => $filter['tingkat']])
            @include('partials.saring', ['kunci' => 'jenis_kelamin', 'label' => 'Jenis kelamin', 'opsi' => ['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan'], 'terpilih' => $filter['jenis_kelamin']])
            @include('partials.reset-saring', ['url' => route('taruna.index'), 'aktif' => $q || array_filter($filter)])
            <div class="pl-toolbar__end">
                <button type="button" class="pl-btn pl-btn-primary" data-tambah>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambah Taruna
                </button>
            </div>
        </form>

        <div class="pl-table-wrap max-h-[70vh]">
            <table class="pl-table dm">
                <thead>
                    <tr>
                        @foreach ($kolom as [$judul, $tengah])
                            <th scope="col" @class(['c' => $tengah])>{{ $judul }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($taruna as $t)
                        <tr>
                            <td class="c tabular-nums">{{ $taruna->firstItem() + $loop->index }}</td>
                            <td class="font-medium">{{ $t->nama }}</td>
                            <td class="tabular-nums">{{ $t->npm ?? '-' }}</td>
                            <td class="c">{{ $t->tingkat ?? '-' }}</td>
                            <td>{{ $t->kelas ?? '-' }}</td>
                            <td>{{ $t->jenis_kelamin ?? '-' }}</td>
                            <td class="c">{{ $t->kamar ?? '-' }}</td>
                            <td class="c">
                                @include('partials.tombol-baris-data', [
                                    'nama' => $t->nama,
                                    'ubah' => $t->only('id', 'nama', 'npm', 'tingkat', 'kelas', 'jenis_kelamin', 'kamar') + ['url' => route('taruna.update', $t)],
                                    'hapus' => route('taruna.destroy', $t),
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">{{ $q || array_filter($filter) ? 'Tidak ada taruna yang cocok dengan pencarian atau filter.' : 'Belum ada data taruna.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pl-table-foot">
            @include('partials.halaman', ['data' => $taruna])
        </div>
    </main>

    <dialog id="form-taruna" class="pl-dialog dm-dialog pl-isian-pil" aria-labelledby="judul-form" data-buka="{{ $errors->any() ? '1' : '' }}">
        <form method="POST" action="{{ $ubahId ? route('taruna.update', $ubahId) : route('taruna.store') }}" class="pl-dialog__body"
            data-tambah-url="{{ route('taruna.store') }}">
            @csrf
            <input type="hidden" name="_method" value="PUT" @disabled(! $ubahId)>
            <input type="hidden" name="_ubah" value="{{ $ubahId }}">
            <div>
                <h2 class="pl-dialog__title" id="judul-form">{{ $ubahId ? 'Ubah Data Taruna' : 'Tambah Data Taruna' }}</h2>
                <p class="pl-dialog__text">Kolom bertanda * wajib diisi.</p>
            </div>
            @if ($errors->any())
                <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
            @endif
            <div class="pl-fields">
                <div class="pl-field pl-field--wide">
                    <label class="pl-field-label" for="nama">Nama Taruna{!! $wajib !!}</label>
                    <input class="pl-input" id="nama" name="nama" value="{{ old('nama') }}" maxlength="150" required>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="npm">NPM</label>
                    <input class="pl-input" id="npm" name="npm" value="{{ old('npm') }}" maxlength="20" inputmode="numeric" autocomplete="off">
                </div>
                @include('partials.combobox', [
                    'id' => 'pilih-tingkat', 'name' => 'tingkat', 'label' => 'Tingkat', 'wajib' => null, 'placeholder' => 'Pilih tingkat', 'terpilih' => old('tingkat'),
                    'opsi' => array_map(fn ($t) => ['id' => $t, 'nama' => "Tingkat $t"], ['I', 'II', 'III', 'IV']),
                ])
                <div class="pl-field">
                    <label class="pl-field-label" for="kelas">Kelas</label>
                    <input class="pl-input" id="kelas" name="kelas" value="{{ old('kelas') }}" maxlength="20" placeholder="Contoh: I RKS A">
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="kamar">Kamar</label>
                    <input class="pl-input" id="kamar" name="kamar" value="{{ old('kamar') }}" maxlength="20" placeholder="Contoh: A101">
                </div>
                @include('partials.combobox', [
                    'id' => 'pilih-jenis-kelamin', 'name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'wajib' => null, 'placeholder' => 'Pilih jenis kelamin', 'terpilih' => old('jenis_kelamin'),
                    'opsi' => [['id' => 'Laki-laki', 'nama' => 'Laki-laki'], ['id' => 'Perempuan', 'nama' => 'Perempuan']],
                ])
            </div>
            <div class="pl-dialog__actions">
                <button type="button" class="pl-btn pl-btn-ghost" data-tutup>Batal</button>
                <button type="submit" class="pl-btn pl-btn-primary">Simpan</button>
            </div>
        </form>
    </dialog>

    @include('partials.form-data-dialog', ['dialog' => 'form-taruna', 'judul' => 'Data Taruna'])
</body>
</html>
