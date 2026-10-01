{{-- laporan-kesehatan/perbarui-keluhan-psikologi.blade.php — prototype "Edit Keluhan Psikolog", susunan & gaya sama dengan
     perbarui-keluhan.blade.php (design system Pulih). Setiap pembaruan menambah satu baris riwayat konseling. --}}
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
        $taruna = $konseling->taruna;
        $inisial = collect(explode(' ', $taruna->nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
        $lanjut = old('lanjut_konseling', $konseling->lanjut_konseling ? '1' : '0');
        // Jadwal lama diisikan bila belum lewat (kolom tanggal minimal hari ini)
        $jadwal = $konseling->tanggal_konseling_selanjutnya?->isBefore(today()) ? null : $konseling->tanggal_konseling_selanjutnya?->toDateString();
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
                    <p class="body m-0 text-muted">Perbarui informasi keadaan kesehatan psikologi taruna</p>
                </div>
            </div>
            <div class="pl-card flex items-center gap-3 px-5 py-3">
                <div class="w-11 h-11 rounded-lg border border-primary-400 flex items-center justify-center text-primary-700 shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                </div>
                <div class="flex flex-col gap-0.5">
                    <span class="body-sm font-medium text-muted">Tanggal Konseling</span>
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
                @foreach ([
                    'Awal Keluhan' => $konseling->tanggal_awal->format('d/m/Y'),
                    'Kamar' => $taruna->kamar,
                    'Konseling Selanjutnya' => $konseling->tanggal_konseling_selanjutnya?->format('d/m/Y') ?? '-',
                    'NPM' => $taruna->npm,
                ] as $label => $nilai)
                    <div class="flex flex-col gap-1">
                        <p class="dk-stat-label">{{ $label }}</p>
                        <p class="dk-stat-value">{{ $nilai }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('laporan-kesehatan.psikologi.update', $konseling) }}" class="pl-card p-6 flex flex-col gap-5">
            @csrf
            @method('PUT')
            <h2 class="dk-h">Perbarui Hasil Konseling</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="pl-field">
                    <label class="pl-field-label" for="hasil_konseling">Hasil Konseling<span class="pl-req" aria-hidden="true"> *</span></label>
                    <textarea class="pl-input" id="hasil_konseling" name="hasil_konseling" required>{{ old('hasil_konseling') }}</textarea>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="terapi">Terapi Psikologi<span class="pl-req" aria-hidden="true"> *</span></label>
                    <textarea class="pl-input" id="terapi" name="terapi" required>{{ old('terapi', $konseling->terapi) }}</textarea>
                </div>
            </div>

            <div class="flex flex-wrap justify-between items-end gap-4">
                <fieldset class="m-0 p-0 border-0">
                    <legend class="pl-field-label p-0 mb-2">Apakah masih melanjutkan konseling?<span class="pl-req" aria-hidden="true"> *</span></legend>
                    <div class="flex gap-8 h-10 items-center">
                        @foreach (['1' => 'Perlu', '0' => 'Tidak'] as $nilai => $label)
                            <label class="flex items-center gap-2 body cursor-pointer">
                                <input type="radio" name="lanjut_konseling" value="{{ $nilai }}" required class="w-[1.125rem] h-[1.125rem] m-0 accent-primary-700" @checked((string) $lanjut === (string) $nilai)>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <div class="pl-field" id="kolom-tanggal">
                    <label class="pl-field-label" for="tanggal_konseling_selanjutnya">Tanggal Konseling Selanjutnya<span class="pl-req" aria-hidden="true"> *</span></label>
                    <input class="pl-input" type="date" id="tanggal_konseling_selanjutnya" name="tanggal_konseling_selanjutnya" value="{{ old('tanggal_konseling_selanjutnya', $jadwal) }}" min="{{ today()->toDateString() }}">
                </div>
                <div class="flex justify-end gap-3 ml-auto">
                    <a href="{{ route('laporan-kesehatan.index') }}" class="pl-btn pl-btn-ghost h-11 px-5">Batal</a>
                    <button type="submit" class="pl-btn pl-btn-primary h-11 px-6">Perbarui</button>
                </div>
            </div>
        </form>
    </main>

    @include('laporan-kesehatan.partials.tanggal-konseling')
</body>
</html>
