<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kesehatan</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #232620; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        p.tanggal { margin: 0 0 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #2f5d55; color: #fff; }
        td.c, th.c { text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan Kesehatan</h1>
    <p class="tanggal">{{ now()->locale('id')->translatedFormat('d F Y') }}</p>

    <table>
        <thead>
            <tr>
                <th class="c">No</th>
                <th>Nama</th>
                <th class="c">NPM</th>
                <th class="c">Kelas</th>
                <th class="c">Tingkat</th>
                <th class="c">Kamar</th>
                <th>Keluhan</th>
                <th>Terapi</th>
                <th class="c">Awal Keluhan</th>
                <th class="c">Kontrol Selanjutnya</th>
                <th>Keterangan</th>
                <th class="c">Status</th>
                <th class="c">Status Pemulihan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($laporan as $i => $r)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ $r->taruna->nama }}</td>
                    <td class="c">{{ $r->taruna->npm }}</td>
                    <td class="c">{{ $r->taruna->kelas }}</td>
                    <td class="c">{{ $r->taruna->tingkat }}</td>
                    <td class="c">{{ $r->taruna->kamar }}</td>
                    <td>{{ $r->keluhan }}</td>
                    <td>{{ $r->terapi }}</td>
                    <td class="c">{{ $r->tanggal_awal->format('d/m/Y') }}</td>
                    <td class="c">{{ $r->status_pemulihan !== 'Sembuh' ? ($r->tanggal_kontrol_selanjutnya?->format('d/m/Y') ?? '-') : '-' }}</td>
                    <td>{{ $r->keterangan ?: '-' }}</td>
                    <td class="c">{{ $r->status }}</td>
                    <td class="c">{{ $r->status_pemulihan }}</td>
                </tr>
            @empty
                <tr><td colspan="13" class="c">Belum ada laporan kesehatan.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
