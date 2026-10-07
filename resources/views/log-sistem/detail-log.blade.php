{{-- log-sistem/detail-log.blade.php — tata letak "Detail log" design system Pagi (warna palet aplikasi). Khusus admin (route can:admin). --}}
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

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ $kembali }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">Detail Log</h1>
                <p class="pl-page-sub">Informasi rinci dan penuh log aktivitas sistem</p>
            </div>
        </header>

        <section aria-label="Ringkasan log" class="pl-profile !items-start">
            <p class="pl-profile__name line-clamp-2 flex-[1_1_26rem] max-w-[40ch]" title="{{ $log->pesan }}">{{ $log->pesan }}</p>
            <dl class="pl-kvs">
                <div class="pl-kv"><dt>Tanggal</dt><dd>{{ $log->created_at->locale('id')->translatedFormat('d F Y') }}</dd></div>
                <div class="pl-kv"><dt>Waktu</dt><dd>{{ $log->created_at->format('H:i:s') }}</dd></div>
            </dl>
        </section>

        <div class="pl-row">
            <section aria-labelledby="judul-info" class="pl-panel" style="flex: 1 1 18rem">
                <h2 id="judul-info" class="pl-panel__title">Informasi Log</h2>
                <dl class="m-0 grid gap-4">
                    <div class="pl-kv">
                        <dt>Aksi</dt>
                        <dd><span class="pl-badge {{ $badge[$log->aksi] ?? 'pl-badge-primary' }}">{{ $log->aksi }}</span></dd>
                    </div>
                    <div class="pl-kv"><dt>Sumber Daya</dt><dd>{{ $log->sumber_daya }}</dd></div>
                    <div class="pl-kv">
                        <dt>Pengguna</dt>
                        <dd>{{ $role }}</dd>
                        <dd class="body-sm !font-normal text-muted">{{ $log->user?->name ?? 'Pengguna dihapus' }}</dd>
                    </div>
                </dl>
            </section>
            <section aria-labelledby="judul-pesan" class="pl-panel" style="flex: 3 1 30rem">
                <h2 id="judul-pesan" class="pl-panel__title">Pesan</h2>
                <p class="pl-panel__text max-w-[72ch] whitespace-pre-line [overflow-wrap:anywhere]">{{ $log->pesan }}</p>
                @if ($url)
                    <a href="{{ $url }}" class="pl-btn pl-btn-ghost pl-btn-sm self-start">
                        {{ $tautan }}
                        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                @endif
            </section>
        </div>
    </main>

</body>
</html>
