{{-- manajemen-akun/manajemen-akun.blade.php — tata letak "Manajemen akun" design system Pagi (warna palet aplikasi). Khusus admin (route can:admin). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Manajemen Akun'])
    <style>
        .ma { table-layout: fixed; }
        /* Tabel: sama dengan tabel Laporan Kesehatan (kepala hijau tua, teks putih tebal) */
        .ma th { padding: .75rem .625rem; position: sticky; top: 0; z-index: 1; background: var(--strong); color: var(--on-primary); font-weight: 600; font-size: max(14px, .875rem); border-bottom: 0; }
        .ma td { padding: .5rem .625rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pl-toolbar .pl-btn { height: 2.75rem; } /* tinggi sama dengan kolom cari, seperti toolbar Laporan Kesehatan */
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $kolom = [['No', 3.5, true], ['Nama Pengguna', null, false], ['Role', 11, true], ['Aktif Terakhir', 9, true], ['Tanggal Ditambahkan', 12.5, true], ['Tindakan', 6.5, true]];
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <h1 class="pl-page-title">Manajemen Akun</h1>
                <p class="pl-page-sub">Seluruh akun pengguna yang terdaftar di sistem informasi</p>
            </div>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        @if ($menunggu)
            <div class="pl-alert pl-alert-notice" role="status">
                {{ $menunggu }} akun meminta perubahan kata sandi dan menunggu persetujuan. Akun tersebut tampil paling atas dengan titik kuning; buka detailnya untuk menyetujui.
            </div>
        @endif

        <form method="GET" action="{{ route('akun.index') }}" class="pl-toolbar">
            <label class="pl-search">
                <span class="pl-sr">Cari nama pengguna</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="search" name="q" value="{{ $q }}" class="pl-input" placeholder="Cari nama pengguna">
            </label>
            @include('partials.saring', ['kunci' => 'role', 'label' => 'Role', 'opsi' => \App\Http\Controllers\AkunController::ROLE, 'terpilih' => $role])
            @include('partials.reset-saring', ['url' => route('akun.index'), 'aktif' => $q || $role])
            <div class="pl-toolbar__end">
                <a href="{{ route('akun.create') }}" class="pl-btn pl-btn-primary">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambah Akun
                </a>
            </div>
        </form>

        <div class="pl-table-wrap max-h-[70vh]">
            <table class="pl-table ma">
                <colgroup>
                    @foreach ($kolom as [, $lebar])
                        {{-- batas bawah px seperti Laporan Kesehatan: kolom tidak ikut menyusut di layar kecil (badge tidak terpotong) --}}
                        <col @if ($lebar) style="width: max({{ $lebar }}rem, {{ $lebar * 15 }}px)" @endif>
                    @endforeach
                </colgroup>
                <thead>
                    <tr>
                        @foreach ($kolom as [$judul, , $tengah])
                            <th scope="col" @class(['c' => $tengah])>{{ $judul }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($akun as $a)
                        <tr>
                            <td class="c tabular-nums">{{ $akun->firstItem() + $loop->index }}</td>
                            <td title="{{ $a->name }} ({{ $a->username }})">
                                <span class="font-medium">{{ $a->username }}</span>
                                <span class="text-muted"> · {{ $a->name }}</span>
                                @if ($a->is(auth()->user()))
                                    <span class="pl-badge pl-badge-primary ml-1">Anda</span>
                                @endif
                            </td>
                            <td class="c">{{ \App\Http\Controllers\AkunController::ROLE[$a->role] ?? $a->role }}</td>
                            <td class="c" title="{{ $a->terakhir_aktif_at?->format('d-m-Y H:i') }}">{{ $a->labelAktif() }}</td>
                            <td class="c">{{ $a->created_at?->locale('id')->translatedFormat('j F Y') }}</td>
                            <td class="c">
                                <span class="inline-flex items-center gap-2">
                                    <a href="{{ route('akun.show', $a) }}" class="pl-icon-btn" aria-label="Detail akun {{ $a->username }}" title="Detail akun">
                                        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                    </a>
                                    @if ($a->sandi_diminta_at)
                                        <span class="w-2.5 h-2.5 rounded-full bg-tertiary-500" title="Menunggu persetujuan ubah kata sandi"></span>
                                        <span class="sr-only">Menunggu persetujuan</span>
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">{{ $q || $role ? 'Tidak ada akun yang cocok dengan pencarian atau filter.' : 'Belum ada akun.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pl-table-foot">
            @include('laporan-kesehatan.partials.zoom-tabel')
            @include('partials.halaman', ['data' => $akun])
        </div>
    </main>

</body>
</html>
