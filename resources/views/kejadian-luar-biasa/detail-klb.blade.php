{{-- kejadian-luar-biasa/detail-klb.blade.php — mengikuti desain "Catatan Kejadian Luar Biasa" (PNG), design system Pulih. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => $klb->nama])
    <style>
        .kt { table-layout: fixed; font-size: .8125rem; line-height: 1.25rem; }
        .kt th, .kt td { padding: .75rem .75rem; }
        .kt thead th { position: sticky; top: 0; z-index: 1; background: var(--primary-700); color: var(--on-primary); border-bottom-color: var(--primary-700); }
        .kt td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .kt .c { text-align: center; }
        .ab { width: 2.25rem; height: 2.25rem; border-radius: var(--radius-sm); display: inline-flex; align-items: center; justify-content: center; background: var(--primary-50); color: var(--primary-700); transition: background-color .15s, color .15s; }
        .ab:hover { background: var(--primary-700); color: #fff; }
        .ab:focus-visible { outline: 2px solid transparent; box-shadow: var(--focus-ring); }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $kolom = [['No', 3.5, true], ['Nama Taruna', 12, false], ['Keluhan', null, false], ['Obat', null, false], ['Tanggal Kontrol Selanjutnya', 12, true], ['Hasil Kontrol Terakhir', null, false], ['Aksi', 5, true]];
        $berlangsung = ! $klb->selesai();
    @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-4 pr-4 py-6">

        <header class="flex flex-col gap-1">
            <a href="{{ route('klb.index') }}" class="pl-btn pl-btn-ghost self-start h-9 pl-2 pr-3">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
            <div class="flex flex-col gap-1">
                <h1 class="h1 m-0 text-primary-900">{{ $klb->nama }}</h1>
                <p class="body m-0 text-muted max-w-4xl">{{ $klb->deskripsi }}</p>
            </div>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        <section aria-label="Status kejadian luar biasa" @class(['self-start min-w-[15.5rem] rounded-[var(--radius-sm)] px-5 py-4 flex flex-col gap-1', 'bg-primary-50' => ! $berlangsung, 'bg-secondary-50' => $berlangsung])>
            <h2 @class(['m-0 font-heading font-semibold text-[1.0625rem] leading-6', 'text-primary-700' => ! $berlangsung, 'text-secondary-800' => $berlangsung])>Status</h2>
            <p class="body m-0">{{ $klb->status }}{{ $klb->selesai_at ? ' sejak '.$klb->selesai_at->locale('id')->translatedFormat('j F Y') : '' }}</p>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <label class="relative block w-72">
                <span class="sr-only">Cari nama taruna</span>
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-[1.125rem] h-[1.125rem] text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="cari" type="search" class="pl-input !pl-10" placeholder="Cari nama taruna">
            </label>
            @if ($berlangsung)
                @can('tambah-data')
                    <a href="{{ route('klb.keluhan.create', $klb) }}" class="pl-btn pl-btn-primary h-10">
                        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah Keluhan
                    </a>
                @endcan
            @endif
        </div>

        <div class="pl-table-wrap max-h-[60vh]">
            <table class="pl-table kt">
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
                <tbody id="isi-tabel">
                    @forelse ($klb->keluhan as $i => $k)
                        <tr data-nama="{{ strtolower($k->nama) }}">
                            <td class="c">{{ $i + 1 }}</td>
                            <td class="font-semibold" title="{{ $k->nama }}">{{ $k->nama }}</td>
                            <td title="{{ $k->keluhan }}">{{ $k->keluhan }}</td>
                            <td title="{{ $k->terapi }}">{{ $k->terapi }}</td>
                            <td class="c tabular-nums">{{ $k->tanggal_kontrol_selanjutnya->locale('id')->translatedFormat('j F Y') }}</td>
                            <td title="{{ $k->kontrolTerakhir?->hasil_kontrol }}">{{ $k->kontrolTerakhir?->hasil_kontrol ?? '-' }}</td>
                            <td class="c">
                                <a href="{{ route('klb.keluhan.show', [$klb, $k]) }}" class="ab" aria-label="Detail keluhan {{ $k->nama }}" title="Detail keluhan">
                                    <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">Belum ada keluhan pada kejadian luar biasa ini.</td></tr>
                    @endforelse
                    <tr id="tidak-ditemukan" hidden>
                        <td colspan="{{ count($kolom) }}" class="c !py-8 text-muted">Nama taruna tidak ditemukan.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div>
            @include('laporan-kesehatan.partials.zoom-tabel')
        </div>

        @if ($berlangsung)
            @can('edit-data')
                <button type="button" data-konfirmasi="konfirmasi-selesai" class="pl-btn pl-btn-primary self-end h-10 shadow-[0_4px_4px_rgba(0,0,0,0.25)]">
                    Kejadian Luar Biasa Selesai
                </button>
            @endcan
        @endif
    </main>

    @if ($berlangsung)
        @can('edit-data')
            @include('partials.konfirmasi', [
                'id' => 'konfirmasi-selesai', 'nada' => 'primary', 'ikon' => 'selesai',
                'judul' => 'Kejadian Luar Biasa Selesai', 'pesan' => 'Apakah anda menyetujui untuk menyatakan kejadian luar biasa berikut selesai?',
                'tombol' => 'Selesai', 'aksi' => route('klb.selesai', $klb), 'metode' => 'PATCH',
            ])
        @endcan
    @endif

    <script>
        // Pencarian nama taruna langsung di tabel
        (() => {
            const baris = [...document.querySelectorAll('#isi-tabel tr[data-nama]')];
            const kosong = document.getElementById('tidak-ditemukan');
            document.getElementById('cari').addEventListener('input', (e) => {
                const q = e.target.value.trim().toLowerCase();
                let ada = 0;
                baris.forEach((tr) => { tr.hidden = !tr.dataset.nama.includes(q); if (!tr.hidden) ada++; });
                kosong.hidden = ada > 0 || baris.length === 0;
            });
        })();
    </script>
</body>
</html>
