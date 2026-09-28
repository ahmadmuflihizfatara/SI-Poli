{{-- keluhan-baru.blade.php — mengikuti artboard "Tambahkan Keluhan Baru" (Prototype SI Kesehatan Taruna 2, design system Pulih) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Tambahkan Keluhan Baru'])
    <style>
        .kb-card { background: var(--surface-card); border: 1px solid var(--border); border-radius: var(--radius-md); padding: var(--space-6); box-sizing: border-box; display: flex; flex-direction: column; gap: var(--space-3); }
        .kb-h { margin: 0 0 var(--space-1); font: 600 1.25rem/1.75rem var(--font-heading); color: var(--primary-700); }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $wajib = '<span class="pl-req" aria-hidden="true"> *</span>';
        // [name, label, atribut input tambahan, wajib]
        $kondisi = [
            ['tekanan_darah', 'Tekanan darah (mmHg)', 'placeholder="120/80"', true],
            ['suhu', 'Suhu (°C)', 'type="number" step="0.1" min="30" max="45" placeholder="36,5"', true],
            ['nadi', 'Nadi (kali/menit)', 'type="number" min="0" placeholder="80"', true],
            ['saturasi', 'Saturasi oksigen (%)', 'type="number" min="0" max="100" placeholder="98"', false],
            ['pernapasan', 'Pernapasan (kali/menit)', 'type="number" min="0" placeholder="18"', false],
            ['skala_nyeri', 'Skala nyeri (0–10)', 'type="number" min="0" max="10" placeholder="0"', false],
            ['ruang_kelas', 'Penempatan ruang kelas', '', false],
            ['ruang_kamar', 'Penempatan ruang kamar', '', false],
        ];
    @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-3 pr-4 pt-3 pb-2">

        <header class="flex flex-col gap-1">
            <a href="{{ route('laporan-kesehatan.index') }}" class="pl-btn pl-btn-ghost self-start h-9 pl-2 pr-3">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
            <div class="flex flex-col gap-1">
                <h1 class="h1 m-0 text-primary-900">Tambahkan Keluhan Baru</h1>
                <p class="body m-0 text-muted">Tambahkan informasi kontrol taruna yang melaporkan keluhan baru. Kolom bertanda * wajib diisi.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent max-w-3xl" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('laporan-kesehatan.store') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            @csrf

            <section class="kb-card">
                <h2 class="kb-h">Informasi Umum</h2>
                <div class="pl-field">
                    <label class="pl-field-label" for="taruna_id">Taruna{!! $wajib !!}</label>
                    {{-- ponytail: data taruna hanya dari tabel taruna; form tidak bisa membuat taruna baru --}}
                    <select class="pl-input" id="taruna_id" name="taruna_id" required>
                        <option value="">{{ $taruna->isEmpty() ? 'Belum ada data taruna' : 'Pilih taruna' }}</option>
                        @foreach ($taruna as $t)
                            <option value="{{ $t->id }}" @selected(old('taruna_id') == $t->id)
                                data-info="{{ json_encode(['NPM' => $t->npm, 'Kelas' => $t->kelas, 'Tingkat' => 'Tingkat '.$t->tingkat, 'Jenis kelamin' => $t->jenis_kelamin, 'Kamar' => $t->kamar]) }}">
                                {{ $t->nama }} — {{ $t->npm }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <dl id="info-taruna" class="m-0 grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 body" aria-live="polite"></dl>
            </section>

            <section class="kb-card">
                <h2 class="kb-h">Kondisi Kesehatan</h2>
                @foreach ($kondisi as [$name, $label, $attr, $req])
                    <div class="pl-field">
                        <label class="pl-field-label" for="{{ $name }}">{{ $label }}{!! $req ? $wajib : '' !!}</label>
                        <input class="pl-input" id="{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" {!! $attr !!} @required($req)>
                    </div>
                @endforeach
            </section>

            <div class="flex flex-col gap-4">
                <section class="kb-card">
                    <h2 class="kb-h">Informasi Keluhan</h2>
                    <div class="pl-field">
                        <label class="pl-field-label" for="keluhan">Keluhan{!! $wajib !!}</label>
                        <textarea class="pl-input" id="keluhan" name="keluhan" required>{{ old('keluhan') }}</textarea>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="terapi">Terapi dan obat{!! $wajib !!}</label>
                        <textarea class="pl-input" id="terapi" name="terapi" required>{{ old('terapi') }}</textarea>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="hasil_pemeriksaan">Hasil pemeriksaan</label>
                        <textarea class="pl-input" id="hasil_pemeriksaan" name="hasil_pemeriksaan">{{ old('hasil_pemeriksaan') }}</textarea>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="status">Tingkat keparahan{!! $wajib !!}</label>
                        <select class="pl-input" id="status" name="status" required>
                            <option value="">Pilih tingkat keparahan</option>
                            @foreach (['Ringan', 'Sedang', 'Berat'] as $s)
                                <option value="{{ $s }}" @selected(old('status') === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="tanggal_kontrol_selanjutnya">Tanggal kontrol selanjutnya{!! $wajib !!}</label>
                        <input class="pl-input" type="date" id="tanggal_kontrol_selanjutnya" name="tanggal_kontrol_selanjutnya" value="{{ old('tanggal_kontrol_selanjutnya') }}" min="{{ today()->toDateString() }}" required>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="keterangan">Keterangan lainnya</label>
                        <textarea class="pl-input !h-[4.5rem]" id="keterangan" name="keterangan">{{ old('keterangan') }}</textarea>
                    </div>
                </section>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('laporan-kesehatan.index') }}" class="pl-btn pl-btn-ghost h-11 px-5">Batal</a>
                    <button type="submit" class="pl-btn pl-btn-primary h-11 px-6">Tambahkan keluhan</button>
                </div>
            </div>
        </form>
    </main>

    <script>
        const pilihTaruna = document.getElementById('taruna_id');
        const infoTaruna = document.getElementById('info-taruna');
        const tampilkanInfo = () => {
            const info = JSON.parse(pilihTaruna.selectedOptions[0].dataset.info || '{}');
            infoTaruna.replaceChildren(...Object.entries(info).flatMap(([k, v]) => {
                const dt = document.createElement('dt'), dd = document.createElement('dd');
                dt.className = 'text-muted'; dt.textContent = k;
                dd.className = 'm-0 font-medium'; dd.textContent = v;
                return [dt, dd];
            }));
        };
        pilihTaruna.addEventListener('change', tampilkanInfo);
        tampilkanInfo();
    </script>
</body>
</html>
