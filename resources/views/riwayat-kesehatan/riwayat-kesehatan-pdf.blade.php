{{-- PDF riwayat kesehatan / psikologi ($psikologi), satu tabel per taruna --}}
@php $judul = $psikologi ? 'Riwayat Psikologi' : 'Riwayat Kesehatan'; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #232620; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        h2 { font-size: 12px; margin: 16px 0 2px; }
        p { margin: 0 0 6px; color: #666; }
        p.tanggal { margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        tr { page-break-inside: avoid; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #2f5d55; color: #fff; }
        td.c, th.c { text-align: center; }
        ul { margin: 0; padding-left: 12px; }
    </style>
</head>
<body>
    <h1>{{ $judul }}</h1>
    <p class="tanggal">Dicetak {{ now()->locale('id')->translatedFormat('d F Y') }}</p>

    @forelse ($perTaruna as $episode)
        @php $taruna = $episode->first()->taruna; @endphp
        <h2>{{ $taruna->nama }}</h2>
        <p>NPM {{ $taruna->npm }} &middot; Kelas {{ $taruna->kelas }} &middot; Tingkat {{ $taruna->tingkat }} &middot; Kamar {{ $taruna->kamar }} &middot; {{ $episode->count() }} riwayat</p>
        <table>
            <thead>
                <tr>
                    <th class="c" style="width: 3%">No</th>
                    <th class="c" style="width: 9%">Tanggal Lapor</th>
                    <th class="c" style="width: 7%">{{ $psikologi ? 'Keterangan' : 'Status' }}</th>
                    <th style="width: 18%">Keluhan</th>
                    <th style="width: 18%">Terapi</th>
                    <th style="width: 30%">{{ $psikologi ? 'Evaluasi Konseling' : 'Riwayat Kontrol' }}</th>
                    <th style="width: 15%">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($episode as $k)
                    @php
                        // [tanggal, hasil, keterangan] per kontrol / sesi konseling
                        $tindakLanjut = $psikologi
                            ? $k->riwayat->map(fn ($r) => [$r->tanggal_konseling, $r->hasil_konseling, null])
                            : $k->riwayatKontrol->map(fn ($r) => [$r->tanggal_kontrol, $r->hasil_kontrol, $r->keterangan]);
                        $catatan = $psikologi
                            ? ($k->tanggal_konseling_selanjutnya ? 'Jadwal konseling selanjutnya: '.$k->tanggal_konseling_selanjutnya->format('d/m/Y') : '-')
                            : ($k->keterangan ?: '-');
                    @endphp
                    <tr>
                        <td class="c">{{ $loop->iteration }}</td>
                        <td class="c">{{ $k->tanggal_awal->format('d/m/Y') }}</td>
                        <td class="c">{{ $psikologi ? $k->keterangan() : $k->status }}</td>
                        <td>{{ $k->keluhan }}</td>
                        <td>{{ $k->terapi }}</td>
                        <td>
                            @if ($tindakLanjut->isEmpty())
                                -
                            @else
                                <ul>
                                    @foreach ($tindakLanjut as [$tanggal, $hasil, $keterangan])
                                        <li>{{ $tanggal->format('d/m/Y') }}: {{ $hasil }}{{ $keterangan ? ' ('.$keterangan.')' : '' }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        <td>{{ $catatan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p>Tidak ada {{ strtolower($judul) }} yang cocok dengan filter.</p>
    @endforelse
</body>
</html>
