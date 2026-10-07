{{-- kejadian-luar-biasa/detail-klb.blade.php — tata letak "Catatan KLB" design system Pagi (warna palet aplikasi). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => $klb->nama])
    <style>
        .kt { table-layout: fixed; }
        .kt th { position: sticky; top: 0; z-index: 1; }
        .kt td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $kolom = [['No', 3.5, true], ['Nama Taruna', 12, false], ['Keluhan', null, false], ['Obat', null, false], ['Tanggal Kontrol Selanjutnya', 12, true], ['Hasil Kontrol Terakhir', null, false], ['Aksi', 5, true]];
        $berlangsung = ! $klb->selesai();
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('klb.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <div class="flex flex-wrap items-center gap-4">
                    <h1 class="pl-page-title">{{ $klb->nama }}</h1>
                    <span aria-label="Status kejadian luar biasa" @class(['pl-badge pl-badge-lg', 'pl-badge-primary' => ! $berlangsung, 'pl-badge-accent' => $berlangsung])>{{ $klb->status }}{{ $klb->selesai_at ? ' sejak '.$klb->selesai_at->locale('id')->translatedFormat('j F Y') : '' }}</span>
                </div>
                <p class="pl-page-sub max-w-4xl">{{ $klb->deskripsi }}</p>
            </div>
            @if ($berlangsung)
                <div class="pl-page-actions">
                    @can('edit-data')
                        <button type="button" data-konfirmasi="konfirmasi-selesai" class="pl-btn pl-btn-ghost">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            Kejadian Luar Biasa Selesai
                        </button>
                    @endcan
                    @can('tambah-data')
                        <a href="{{ route('klb.keluhan.create', $klb) }}" class="pl-btn pl-btn-primary">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Tambah Keluhan
                        </a>
                    @endcan
                </div>
            @endif
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        <div class="pl-toolbar">
            <label class="pl-search">
                <span class="pl-sr">Cari nama taruna</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="cari" type="search" class="pl-input" placeholder="Cari nama taruna">
            </label>
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
                            <td class="font-medium" title="{{ $k->nama }}">{{ $k->nama }}</td>
                            <td title="{{ $k->keluhan }}">{{ $k->keluhan }}</td>
                            <td title="{{ $k->terapi }}">{{ $k->terapi }}</td>
                            <td class="c tabular-nums">{{ $k->tanggal_kontrol_selanjutnya->locale('id')->translatedFormat('j F Y') }}</td>
                            <td title="{{ $k->kontrolTerakhir?->hasil_kontrol }}">{{ $k->kontrolTerakhir?->hasil_kontrol ?? '-' }}</td>
                            <td class="c">
                                <a href="{{ route('klb.keluhan.show', [$klb, $k]) }}" class="pl-icon-btn" aria-label="Detail keluhan {{ $k->nama }}" title="Detail keluhan">
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

        <div class="pl-table-foot">
            @include('laporan-kesehatan.partials.zoom-tabel')
        </div>
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
