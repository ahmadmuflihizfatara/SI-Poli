{{-- laporan-kesehatan/perbarui-keluhan.blade.php — halaman "Perbarui Keluhan" (input hasil kontrol kesehatan), design system Pulih --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Perbarui Keluhan'])
    <style>
        .dk-avatar { width: 3.5rem; height: 3.5rem; border-radius: 9999px; background: var(--primary-900); color: #fff;
            display: flex; align-items: center; justify-content: center; font: 600 1.125rem/1 var(--font-heading); flex-shrink: 0; }
        .dk-summary { background: var(--primary-50); border-radius: var(--radius-md); padding: var(--space-6);
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: var(--space-6); }
        .dk-stat-label { margin: 0; font-size: .75rem; color: var(--text-muted); }
        .dk-stat-value { margin: 0; font: 600 1rem/1.5rem var(--font-heading); color: var(--primary-900); }
        .dk-h { margin: 0; font: 600 1.25rem/1.75rem var(--font-heading); color: var(--primary-700); }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $taruna = $keluhan->taruna;
        $inisial = collect(explode(' ', $taruna->nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-4 pr-4 py-6">

        <header class="flex flex-wrap justify-between items-start gap-4">
            <div class="flex flex-col gap-1">
                <a href="{{ route('laporan-kesehatan.index') }}" class="pl-btn pl-btn-ghost self-start h-9 pl-2 pr-3">
                    <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <div class="flex flex-col gap-1">
                    <h1 class="h1 m-0 text-primary-900">Perbarui Keluhan</h1>
                    <p class="body m-0 text-muted">Perbarui informasi keadaan kesehatan taruna</p>
                </div>
            </div>
            <div class="pl-card flex items-center gap-3 px-5 py-3">
                <div class="w-11 h-11 rounded-lg border border-primary-400 flex items-center justify-center text-primary-700 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                </div>
                <div class="flex flex-col gap-0.5">
                    <span class="body-sm font-medium text-muted">Tanggal Kontrol</span>
                    <span class="h3 text-primary-900">{{ now()->locale('id')->translatedFormat('d F Y') }}</span>
                </div>
            </div>
        </header>

        <div class="dk-summary">
            <div class="flex items-center gap-4">
                <div class="dk-avatar">{{ $inisial }}</div>
                <div class="flex flex-col gap-1">
                    <p class="h2 m-0 text-primary-900">{{ $taruna->nama }}</p>
                    <p class="body-sm m-0 text-muted">Tingkat {{ $taruna->tingkat }} &middot; {{ $taruna->kelas }} &middot; {{ $taruna->jenis_kelamin }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-8">
                <div class="flex flex-col gap-1">
                    <p class="dk-stat-label">Awal Keluhan</p>
                    <p class="dk-stat-value">{{ $keluhan->tanggal_awal->format('d/m/Y') }}</p>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="dk-stat-label">Kamar</p>
                    <p class="dk-stat-value">{{ $taruna->kamar }}</p>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="dk-stat-label">Tingkat Keparahan</p>
                    <p class="dk-stat-value">{{ $keluhan->status }}</p>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="dk-stat-label">NPM</p>
                    <p class="dk-stat-value">{{ $taruna->npm }}</p>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('laporan-kesehatan.kontrol.update', $keluhan) }}" class="pl-card p-6 flex flex-col gap-5">
            @csrf
            <h2 class="dk-h">Perbarui Hasil Kontrol</h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="pl-field">
                    <label class="pl-field-label" for="hasil_pemeriksaan">Hasil Pemeriksaan<span class="pl-req" aria-hidden="true"> *</span></label>
                    <textarea class="pl-input" id="hasil_pemeriksaan" name="hasil_pemeriksaan" required>{{ old('hasil_pemeriksaan') }}</textarea>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="terapi">Terapi dan Obat Terbaru<span class="pl-req" aria-hidden="true"> *</span></label>
                    <textarea class="pl-input" id="terapi" name="terapi" required>{{ old('terapi', $keluhan->terapi) }}</textarea>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="keterangan">Keterangan Lainnya</label>
                    <textarea class="pl-input" id="keterangan" name="keterangan">{{ old('keterangan') }}</textarea>
                </div>
            </div>

            <div class="flex flex-wrap justify-between items-center gap-4">
                <fieldset class="m-0 p-0 border-0">
                    <legend class="pl-field-label p-0 mb-2">Perlu dirujuk di luar poliklinik?<span class="pl-req" aria-hidden="true"> *</span></legend>
                    <div class="flex gap-8 h-10 items-center">
                        <label class="flex items-center gap-2 body cursor-pointer">
                            <input type="radio" name="perlu_rujukan" value="1" required class="w-[1.125rem] h-[1.125rem] m-0 accent-primary-700" @checked(old('perlu_rujukan') === '1')>
                            Perlu
                        </label>
                        <label class="flex items-center gap-2 body cursor-pointer">
                            <input type="radio" name="perlu_rujukan" value="0" required class="w-[1.125rem] h-[1.125rem] m-0 accent-primary-700" @checked(old('perlu_rujukan') === '0')>
                            Tidak
                        </label>
                    </div>
                </fieldset>
                <div class="pl-field">
                    <label class="pl-field-label" for="tanggal_kontrol_selanjutnya">Tanggal Kontrol Selanjutnya<span class="pl-req" aria-hidden="true"> *</span></label>
                    <input class="pl-input" type="date" id="tanggal_kontrol_selanjutnya" name="tanggal_kontrol_selanjutnya" value="{{ old('tanggal_kontrol_selanjutnya') }}" min="{{ today()->toDateString() }}" required>
                </div>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('laporan-kesehatan.index') }}" class="pl-btn pl-btn-ghost h-11 px-5">Batal</a>
                    <button type="submit" class="pl-btn pl-btn-primary h-11 px-6">Perbarui</button>
                </div>
            </div>
        </form>
    </main>
</body>
</html>
