<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pemeriksaan Kesehatan</title>
    <style>
        body { font-family: sans-serif; font-size: 8px; color: #232620; }
        h1 { font-size: 16px; margin: 0 0 3px; }
        p { margin: 0 0 8px; color: #55584f; }
        table { width: 100%; border-collapse: collapse; }
        tr { page-break-inside: avoid; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: left; vertical-align: top; }
        th { background: #146b5f; color: #fff; }
        th.c, td.c { text-align: center; }
        td.danger { color: #8a3620; font-weight: bold; }
        td.warning { color: #7a5308; font-weight: bold; }
        td.tanda-tangan { min-width: 90px; }
        .ttd { margin: 24px 0 0 auto; width: 220px; text-align: center; }
        .ttd .ruang { height: 45px; }
    </style>
</head>
<body>
    <h1>Pemeriksaan Kesehatan — {{ $kegiatan->nama }}</h1>
    <p>
        {{ $kegiatan->jenis }} ·
        @if ($kegiatan->jenis === 'MPTB')
            {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y') }} · Sesi {{ $sesi }}
        @else
            Periode {{ $kegiatan->labelRentang() }}
        @endif
        · Dicetak {{ now()->locale('id')->translatedFormat('d F Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th style="width: 14%">Nama</th>
                <th class="c" style="width: 9%">Tekanan darah<br>mmHg</th>
                <th class="c" style="width: 6%">Nadi<br>x/menit</th>
                <th class="c" style="width: 6%">Suhu<br>°C</th>
                <th class="c" style="width: 6%">Saturasi<br>%</th>
                <th class="c" style="width: 7%">Pernapasan<br>x/menit</th>
                <th style="width: 17%">Keluhan</th>
                <th style="width: 17%">Terapi</th>
                <th style="width: 18%">Tanda tangan dokter penanggung jawab</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($baris as $barisTaruna)
                @php $hasil = $barisTaruna['hasil']; @endphp
                <tr>
                    <td>{{ $barisTaruna['taruna']->nama }}</td>
                    <td @class(['c', 'danger' => \App\Models\Pemeriksaan::statusVital('tekanan_darah', $hasil?->tekanan_darah) === 'danger', 'warning' => \App\Models\Pemeriksaan::statusVital('tekanan_darah', $hasil?->tekanan_darah) === 'warning'])>{{ $hasil?->tekanan_darah ?? '-' }}</td>
                    <td @class(['c', 'danger' => \App\Models\Pemeriksaan::statusVital('nadi', $hasil?->nadi) === 'danger'])>{{ $hasil?->nadi ?? '-' }}</td>
                    <td @class(['c', 'danger' => \App\Models\Pemeriksaan::statusVital('suhu', $hasil?->suhu) === 'danger', 'warning' => \App\Models\Pemeriksaan::statusVital('suhu', $hasil?->suhu) === 'warning'])>{{ $hasil?->suhu !== null ? str_replace('.', ',', $hasil->suhu) : '-' }}</td>
                    <td @class(['c', 'danger' => \App\Models\Pemeriksaan::statusVital('saturasi', $hasil?->saturasi) === 'danger', 'warning' => \App\Models\Pemeriksaan::statusVital('saturasi', $hasil?->saturasi) === 'warning'])>{{ $hasil?->saturasi ?? '-' }}</td>
                    <td @class(['c', 'danger' => \App\Models\Pemeriksaan::statusVital('pernapasan', $hasil?->pernapasan) === 'danger'])>{{ $hasil?->pernapasan ?? '-' }}</td>
                    <td>{{ $hasil?->keluhan ?: '-' }}</td>
                    <td>{{ $hasil?->terapi ?: '-' }}</td>
                    <td class="tanda-tangan">&nbsp;</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="ttd">
        <div>Dokter Penanggung Jawab</div>
        <div class="ruang"></div>
        <div>( ______________________________ )</div>
    </div>
</body>
</html>
