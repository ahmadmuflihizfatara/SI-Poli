{{-- log-sistem/detail-log.blade.php — rincian satu log aktivitas, design system Pulih. Khusus admin (route can:admin). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Detail Log'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $role = \App\Http\Controllers\LogController::PENGGUNA[$log->user?->role] ?? '-';
        $badge = ['Tambah' => 'pl-badge-primary', 'Edit' => 'pl-badge-accent'];
        [$url, $tautan] = match (true) {
            (bool) $log->keluhan_id => [route('laporan-kesehatan.show', $log->keluhan_id), 'Lihat laporan kesehatan'],
            (bool) $log->pemeriksaan => [$log->pemeriksaan->url(), 'Lihat pemeriksaan'],
            default => [null, null],
        };
        // Kembali ke daftar log dengan filter/halaman yang sama bila datang dari sana
        $kembali = str_starts_with(url()->previous(), route('log.index')) && url()->previous() !== url()->current()
            ? url()->previous() : route('log.index');
    @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-4 pr-4 py-6">

        <header class="flex flex-col gap-1">
            <a href="{{ $kembali }}" class="pl-btn pl-btn-ghost self-start h-9 pl-2 pr-3">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
            <h1 class="h1 m-0 text-primary-900">Detail Log</h1>
            <p class="body m-0 text-muted">Rincian aktivitas yang tercatat di sistem informasi</p>
        </header>

        <section aria-label="Rincian log" class="pl-card p-6 flex flex-col gap-6 max-w-3xl">
            <dl class="grid grid-cols-[10rem_1fr] gap-x-6 gap-y-4 m-0 body">
                <dt class="label text-muted">Timestamp</dt>
                <dd class="m-0 tabular-nums">{{ $log->created_at->locale('id')->translatedFormat('l, d F Y · H:i:s') }}</dd>

                <dt class="label text-muted">Aksi</dt>
                <dd class="m-0"><span class="pl-badge {{ $badge[$log->aksi] ?? 'pl-badge-primary' }}">{{ $log->aksi }}</span></dd>

                <dt class="label text-muted">Sumber Daya</dt>
                <dd class="m-0">{{ $log->sumber_daya }}</dd>

                <dt class="label text-muted">Pengguna</dt>
                <dd class="m-0">{{ $log->user ? $log->user->name.' ('.$role.')' : 'Pengguna dihapus' }}</dd>

                <dt class="label text-muted">Pesan</dt>
                <dd class="m-0 break-words">{{ $log->pesan }}</dd>
            </dl>

            @if ($url)
                <a href="{{ $url }}" class="pl-btn pl-btn-secondary self-end">
                    {{ $tautan }}
                    <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
            @endif
        </section>
    </main>

</body>
</html>
