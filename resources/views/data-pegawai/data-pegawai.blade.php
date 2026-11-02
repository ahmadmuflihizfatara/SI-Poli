{{-- data-pegawai/data-pegawai.blade.php — "Data Pegawai" design system Pagi (warna palet aplikasi). Khusus admin (route can:admin).
     Dokter, perawat, dan psikolog di sini yang dipilih sebagai pemeriksa keluhan, kontrol, dan konseling. Tambah & ubah lewat satu popup. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Data Pegawai'])
    <style>
        /* Kepala tabel hijau tua + teks putih tebal, sama dengan tabel Laporan Kesehatan */
        .dm th { position: sticky; top: 0; z-index: 1; background: var(--strong); color: var(--on-primary); font-weight: 600; font-size: max(14px, .875rem); border-bottom: 0; }
        .dm tbody tr td { transition: background-color .15s; }
        .dm tbody tr:hover td { background: var(--cloud); }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $wajib = '<span class="pl-req" aria-hidden="true">*</span>';
        $kolom = [['No', true], ['Nama Pegawai', false], ['Jabatan', false], ['Tindakan', true]];
        $nadaJabatan = ['Dokter' => 'pl-badge-accent', 'Perawat' => 'pl-badge-primary', 'Psikolog' => 'pl-badge-notice'];
        // gagal validasi: popup dibuka lagi untuk pegawai yang sama (ubah) atau sebagai tambah
        $ubahId = old('_ubah');
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <h1 class="pl-page-title">Data Pegawai</h1>
                <p class="pl-page-sub">Dokter, perawat, dan psikolog yang dapat dipilih sebagai pemeriksa</p>
            </div>
            <div class="pl-page-actions">
                <button type="button" class="pl-btn pl-btn-primary" data-tambah>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambah Pegawai
                </button>
            </div>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        <form method="GET" action="{{ route('pegawai.index') }}" class="pl-toolbar">
            <label class="pl-search">
                <span class="pl-sr">Cari nama pegawai</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="search" name="q" value="{{ $q }}" class="pl-input" placeholder="Cari nama pegawai...">
            </label>
            @if ($q)
                <a href="{{ route('pegawai.index') }}" class="pl-btn pl-btn-ghost pl-btn-sm">Hapus pencarian</a>
            @endif
        </form>

        <div class="pl-table-wrap max-w-[60rem]">
            <table class="pl-table dm">
                <thead>
                    <tr>
                        @foreach ($kolom as [$judul, $tengah])
                            <th scope="col" @class(['c' => $tengah])>{{ $judul }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pegawai as $p)
                        <tr>
                            <td class="c tabular-nums">{{ $pegawai->firstItem() + $loop->index }}</td>
                            <td class="font-medium">{{ $p->nama }}</td>
                            <td><span class="pl-badge {{ $nadaJabatan[$p->jabatan] ?? 'pl-badge-primary' }}">{{ $p->jabatan }}</span></td>
                            <td class="c">
                                <button type="button" class="pl-icon-btn" aria-label="Ubah data {{ $p->nama }}" title="Ubah data"
                                    data-ubah="{{ json_encode($p->only('id', 'nama', 'jabatan') + ['url' => route('pegawai.update', $p)]) }}">
                                    <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">{{ $q ? 'Tidak ada pegawai yang cocok dengan pencarian.' : 'Belum ada data pegawai.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pl-table-foot">
            @include('partials.halaman', ['data' => $pegawai])
        </div>
    </main>

    <dialog id="form-pegawai" class="pl-dialog" aria-labelledby="judul-form" data-buka="{{ $errors->any() ? '1' : '' }}">
        <form method="POST" action="{{ $ubahId ? route('pegawai.update', $ubahId) : route('pegawai.store') }}" class="pl-dialog__body"
            data-tambah-url="{{ route('pegawai.store') }}">
            @csrf
            <input type="hidden" name="_method" value="PUT" @disabled(! $ubahId)>
            <input type="hidden" name="_ubah" value="{{ $ubahId }}">
            <div>
                <h2 class="pl-dialog__title" id="judul-form">{{ $ubahId ? 'Ubah Data Pegawai' : 'Tambah Data Pegawai' }}</h2>
                <p class="pl-dialog__text">Kolom bertanda * wajib diisi.</p>
            </div>
            @if ($errors->any())
                <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
            @endif
            <div class="pl-field">
                <label class="pl-field-label" for="nama">Nama Pegawai{!! $wajib !!}</label>
                <input class="pl-input" id="nama" name="nama" value="{{ old('nama') }}" maxlength="255" placeholder="Contoh: dr. Andi Wijaya" required>
            </div>
            <div class="pl-field">
                <label class="pl-field-label" for="jabatan">Jabatan{!! $wajib !!}</label>
                <select class="pl-input" id="jabatan" name="jabatan" required>
                    <option value="">Pilih jabatan</option>
                    @foreach (\App\Models\Pegawai::JABATAN as $jabatan)
                        <option value="{{ $jabatan }}" @selected(old('jabatan') === $jabatan)>{{ $jabatan }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pl-dialog__actions">
                <button type="button" class="pl-btn pl-btn-ghost" data-tutup>Batal</button>
                <button type="submit" class="pl-btn pl-btn-primary">Simpan</button>
            </div>
        </form>
    </dialog>

    @include('partials.form-data-dialog', ['dialog' => 'form-pegawai', 'judul' => 'Data Pegawai'])
</body>
</html>
