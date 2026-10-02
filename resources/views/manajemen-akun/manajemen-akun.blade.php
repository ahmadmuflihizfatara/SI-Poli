{{-- manajemen-akun/manajemen-akun.blade.php — mengikuti desain "Manajemen Akun" (PNG), design system Pulih. Khusus admin (route can:admin). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Manajemen Akun'])
    <style>
        .ma { table-layout: fixed; font-size: .875rem; line-height: 1.25rem; }
        .ma th, .ma td { padding: .625rem .75rem; }
        .ma thead th { position: sticky; top: 0; z-index: 1; background: var(--primary-700); color: var(--on-primary); border-bottom-color: var(--primary-700); }
        .ma td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ma .c { text-align: center; }
        .ab { width: 2.25rem; height: 2.25rem; border: 0; border-radius: var(--radius-sm); display: inline-flex; align-items: center; justify-content: center; background: var(--primary-50); color: var(--primary-700); transition: background-color .15s, color .15s; }
        .ab:hover { background: var(--primary-700); color: #fff; }
        .ab:focus-visible { outline: 2px solid transparent; box-shadow: var(--focus-ring); }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $kolom = [['No', 3.5, true], ['Nama Pengguna', null, false], ['Akses', 11, true], ['Role', 7, true], ['Aktif Terakhir', 9, true], ['Tanggal Ditambahkan', 12.5, true], ['Tindakan', 6.5, true]];
    @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-4 pr-4 py-6">

        <header class="flex flex-col gap-1">
            <h1 class="m-0 font-heading font-bold text-[2.25rem] leading-[2.75rem] text-primary-900">Manajemen Akun</h1>
            <p class="body-lg m-0 font-medium">Seluruh akun pengguna yang terdaftar di sistem informasi</p>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif
        @if ($menunggu)
            <div class="pl-alert bg-tertiary-50 text-tertiary-800" role="status">
                {{ $menunggu }} akun meminta perubahan kata sandi dan menunggu persetujuan. Akun tersebut tampil paling atas dengan titik kuning; buka detailnya untuk menyetujui.
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('akun.index') }}" class="flex items-center gap-3">
                <label class="relative block w-72">
                    <span class="sr-only">Cari nama pengguna</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-[1.125rem] h-[1.125rem] text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <input type="search" name="q" value="{{ $q }}" class="pl-input !pl-10" placeholder="Cari nama pengguna">
                </label>
                @if ($q)
                    <a href="{{ route('akun.index') }}" class="pl-btn pl-btn-ghost h-9">Hapus pencarian</a>
                @endif
            </form>
            <a href="{{ route('akun.create') }}" class="pl-btn pl-btn-primary h-11">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambah Akun
            </a>
        </div>

        <div class="pl-table-wrap">
            <table class="pl-table ma">
                <colgroup>
                    @foreach ($kolom as [, $lebar])
                        <col @if ($lebar) style="width: {{ $lebar }}rem" @endif>
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
                                <span class="font-semibold">{{ $a->username }}</span>
                                <span class="text-muted"> · {{ $a->name }}</span>
                                @if ($a->is(auth()->user()))
                                    <span class="pl-badge pl-badge-primary ml-1">Anda</span>
                                @endif
                            </td>
                            <td class="c">
                                <span class="inline-flex gap-2">
                                    {{-- warna sama dengan aksi di Log Sistem: Tambah hijau, Edit oranye --}}
                                    @if ($a->bisa('edit'))<span class="pl-badge pl-badge-accent">Edit</span>@endif
                                    @if ($a->bisa('tambah'))<span class="pl-badge pl-badge-primary">Tambah</span>@endif
                                    @if (! $a->bisa('edit') && ! $a->bisa('tambah'))<span class="text-muted">Hanya lihat</span>@endif
                                </span>
                            </td>
                            <td class="c">{{ \App\Http\Controllers\AkunController::ROLE[$a->role] ?? $a->role }}</td>
                            <td class="c" title="{{ $a->terakhir_aktif_at?->format('d-m-Y H:i') }}">{{ $a->labelAktif() }}</td>
                            <td class="c">{{ $a->created_at?->locale('id')->translatedFormat('j F Y') }}</td>
                            <td class="c">
                                <span class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('akun.show', $a) }}" class="ab" aria-label="Detail akun {{ $a->username }}" title="Detail akun">
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
                        <tr><td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">{{ $q ? 'Tidak ada akun yang cocok dengan pencarian.' : 'Belum ada akun.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            @include('laporan-kesehatan.partials.zoom-tabel')
        </div>

        @if ($akun->hasPages())
            <div>{{ $akun->links() }}</div>
        @endif
    </main>

</body>
</html>
