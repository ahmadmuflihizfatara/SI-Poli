{{-- log-sistem/detail-log.blade.php — mengikuti desain "Detail Log" (PNG), design system Pulih. Khusus admin (route can:admin). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Detail Log'])
    <style>
        .dl-ringkas { background: var(--primary-50); border-radius: var(--radius-sm); padding: 1.625rem 1.75rem;
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem 4rem; }
        .dl-judul { margin: 0; font: 600 2rem/2.5rem var(--font-heading); color: var(--primary-700); }
        .dl-h { margin: 0; font: 600 1.25rem/1.75rem var(--font-heading); color: var(--primary-700); }
        .dl-label { margin: 0; font: 500 1.0625rem/1.5rem var(--font-sans); color: var(--text); }
        .dl-nilai { margin: 0; font-size: .8125rem; line-height: 1.25rem; }
        .dl-kartu { border: 1px solid var(--neutral-400); border-radius: var(--radius-sm); background: var(--surface-card); }
        .dl-pesan { border: 1px solid var(--neutral-400); border-radius: var(--radius-sm); padding: 1.75rem; min-height: 10.25rem;
            font-size: .8125rem; line-height: 1.25rem; white-space: pre-line; overflow-wrap: anywhere; }
    </style>
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

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-6 pr-4 py-6">

        <header class="flex flex-col gap-1">
            <a href="{{ $kembali }}" class="pl-btn pl-btn-ghost self-start h-9 pl-2 pr-3 body-lg font-medium">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
            <h1 class="m-0 font-heading font-bold text-[2.25rem] leading-[2.75rem] text-primary-900">Detail Log</h1>
            <p class="body-lg m-0 font-medium">Informasi rinci dan penuh log aktivitas sistem</p>
        </header>

        <div class="flex flex-col gap-6 max-w-[73rem]">
            <section aria-label="Ringkasan log" class="dl-ringkas">
                <p class="dl-judul line-clamp-2 flex-1 min-w-[18rem]" title="{{ $log->pesan }}">{{ $log->pesan }}</p>
                <dl class="flex gap-16 m-0 text-center shrink-0">
                    <div class="flex flex-col gap-1">
                        <dt class="body font-semibold">Tanggal</dt>
                        <dd class="body m-0">{{ $log->created_at->locale('id')->translatedFormat('d F Y') }}</dd>
                    </div>
                    <div class="flex flex-col gap-1">
                        <dt class="body font-semibold">Waktu</dt>
                        <dd class="body m-0 tabular-nums">{{ $log->created_at->format('H:i:s') }}</dd>
                    </div>
                </dl>
            </section>

            <section aria-labelledby="judul-info" class="dl-kartu px-6 pt-5 pb-10 flex flex-col gap-5">
                <h2 id="judul-info" class="dl-h">Informasi Log</h2>
                <div class="grid grid-cols-1 md:grid-cols-[14.75rem_minmax(0,52rem)] gap-6">
                    <dl class="flex flex-col gap-5 m-0">
                        <div class="flex flex-col gap-1.5">
                            <dt class="dl-label">Aksi</dt>
                            <dd class="m-0"><span class="pl-badge {{ $badge[$log->aksi] ?? 'pl-badge-primary' }} min-w-[4.75rem] justify-center">{{ $log->aksi }}</span></dd>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <dt class="dl-label">Sumber Daya</dt>
                            <dd class="dl-nilai">{{ $log->sumber_daya }}</dd>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <dt class="dl-label">Pengguna</dt>
                            <dd class="dl-nilai">{{ $role }}</dd>
                            <dd class="dl-nilai text-muted">{{ $log->user?->name ?? 'Pengguna dihapus' }}</dd>
                        </div>
                    </dl>

                    <div class="flex flex-col gap-2.5">
                        <h3 class="dl-label">Pesan</h3>
                        <div class="dl-pesan">{{ $log->pesan }}</div>
                        @if ($url)
                            <a href="{{ $url }}" class="pl-btn pl-btn-secondary self-end">
                                {{ $tautan }}
                                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            </a>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </main>

</body>
</html>
