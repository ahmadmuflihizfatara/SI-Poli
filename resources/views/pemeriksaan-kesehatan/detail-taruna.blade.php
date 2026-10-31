{{-- pemeriksaan-kesehatan/detail-taruna.blade.php — semua hasil pemeriksaan satu taruna dalam satu kegiatan MPTB/Samapta --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Pemeriksaan '.$taruna->nama])
    <style>
        .dt-table { table-layout: fixed; min-width: 60rem; }
        .dt-table th { padding: .375rem .625rem; line-height: 1.25; position: sticky; top: 0; z-index: 1; text-align: center; background: var(--strong); color: var(--on-primary); font-weight: 600; font-size: max(14px, .875rem); border-bottom: 0; }
        .dt-table th small { display: block; font-weight: 400; font-size: max(12px, .75rem); opacity: .75; }
        .dt-table td { padding: .5rem .625rem; }
        .dt-table td.teks { text-align: center; white-space: normal; overflow-wrap: break-word; }
        .dt-table td.suhu-tinggi { color: var(--danger); font-weight: 700; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $inisial = collect(explode(' ', $taruna->nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
        $mptb = $kegiatan->jenis === 'MPTB';
        $suhuBatas = 37.5; // sama dengan tabel input
        $diperiksa = $hasil->filter(fn ($p) => collect(\App\Models\Pemeriksaan::WAJIB)->every(fn ($k) => $p->$k !== null));
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('pemeriksaan-kesehatan.show', $kegiatan) }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">Hasil Pemeriksaan Taruna</h1>
                <p class="pl-page-sub">{{ $kegiatan->nama }} &middot; {{ $kegiatan->jenis }} &middot; {{ $kegiatan->labelRentang() }}</p>
            </div>
        </header>

        <section class="pl-profile" aria-label="Identitas taruna">
            <div class="pl-profile__who">
                <span class="pl-avatar" aria-hidden="true">{{ $inisial }}</span>
                <div>
                    <p class="pl-profile__name">{{ $taruna->nama }}</p>
                    <p class="pl-profile__meta">Tingkat {{ $taruna->tingkat }} &middot; {{ $taruna->kelas }} &middot; {{ $taruna->jenis_kelamin }}</p>
                </div>
            </div>
            <dl class="pl-kvs">
                <div class="pl-kv"><dt>NPM</dt><dd>{{ $taruna->npm }}</dd></div>
                <div class="pl-kv"><dt>Kamar</dt><dd>{{ $taruna->kamar }}</dd></div>
                @if ($mptb)
                    <div class="pl-kv"><dt>Sesi diperiksa</dt><dd>{{ $diperiksa->count() }} dari {{ $kegiatan->daftarTanggal()->count() * 2 }}</dd></div>
                @else
                    <div class="pl-kv"><dt>Status</dt><dd>{{ $diperiksa->isNotEmpty() ? 'Sudah diperiksa' : 'Belum diperiksa' }}</dd></div>
                @endif
            </dl>
        </section>

        <div class="pl-table-wrap">
            <table class="pl-table dt-table">
                <thead>
                    <tr>
                        @if ($mptb)
                            <th style="width:12rem">Tanggal</th>
                            <th style="width:6rem">Sesi</th>
                        @endif
                        <th style="width:7rem">Tekanan darah<small>mmHg</small></th>
                        <th style="width:5.5rem">Nadi<small>x/menit</small></th>
                        <th style="width:5.5rem">Suhu<small>°C</small></th>
                        <th style="width:7rem">Pernapasan<small>x/menit</small></th>
                        <th style="width:14rem">Keluhan</th>
                        <th style="width:14rem">Terapi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($hasil as $p)
                        <tr>
                            @if ($mptb)
                                <td>{{ \Carbon\Carbon::parse($p->periode)->locale('id')->translatedFormat('l, d M Y') }}</td>
                                <td class="c">{{ $p->sesi }}</td>
                            @endif
                            <td class="c tabular-nums">{{ $p->tekanan_darah ?? '-' }}</td>
                            <td class="c tabular-nums">{{ $p->nadi ?? '-' }}</td>
                            <td @class(['c tabular-nums', 'suhu-tinggi' => $p->suhu >= $suhuBatas])>{{ $p->suhu !== null ? str_replace('.', ',', $p->suhu) : '-' }}</td>
                            <td class="c tabular-nums">{{ $p->pernapasan ?? '-' }}</td>
                            <td class="teks">{{ $p->keluhan ?: '-' }}</td>
                            <td class="teks">{{ $p->terapi ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $mptb ? 8 : 6 }}" class="c !py-8 text-muted">Belum ada hasil pemeriksaan untuk taruna ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>
