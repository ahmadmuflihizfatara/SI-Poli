{{-- laporan-kesehatan/perbarui-keluhan-psikologi.blade.php — prototype "Edit Keluhan Psikolog", susunan & gaya sama dengan
     perbarui-keluhan.blade.php (design system Pagi). Setiap pembaruan menambah satu baris riwayat konseling. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Perbarui Keluhan'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $taruna = $konseling->taruna;
        $inisial = collect(explode(' ', $taruna->nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
        $lanjut = old('lanjut_konseling', $konseling->lanjut_konseling ? '1' : '0');
        // Jadwal lama diisikan bila belum lewat (kolom tanggal minimal hari ini)
        $jadwal = $konseling->tanggal_konseling_selanjutnya?->isBefore(today()) ? null : $konseling->tanggal_konseling_selanjutnya?->toDateString();
        $wajib = '<span class="pl-req" aria-hidden="true">*</span>';
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('laporan-kesehatan.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">Perbarui Keluhan</h1>
                <p class="pl-page-sub">Perbarui informasi keadaan kesehatan psikologi taruna</p>
            </div>
            <div class="pl-page-actions">
                <div class="pl-date">
                    <span class="pl-date__icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                    <span><small>Tanggal Konseling</small><b>{{ now()->locale('id')->translatedFormat('d F Y') }}</b></span>
                </div>
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
                @foreach ([
                    'Awal Keluhan' => $konseling->tanggal_awal->format('d/m/Y'),
                    'Kamar' => $taruna->kamar,
                    'Konseling Selanjutnya' => $konseling->tanggal_konseling_selanjutnya?->format('d/m/Y') ?? '-',
                    'NPM' => $taruna->npm,
                ] as $label => $nilai)
                    <div class="pl-kv"><dt>{{ $label }}</dt><dd>{{ $nilai }}</dd></div>
                @endforeach
            </dl>
        </section>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('laporan-kesehatan.psikologi.update', $konseling) }}" class="pl-panel" aria-labelledby="judul-form">
            @csrf
            @method('PUT')
            <h2 class="pl-panel__title" id="judul-form">Perbarui Hasil Konseling</h2>

            <div class="pl-fields !grid-cols-[repeat(auto-fit,minmax(17.5rem,1fr))]">
                <div class="pl-field">
                    <label class="pl-field-label" for="hasil_konseling">Hasil Konseling{!! $wajib !!}</label>
                    <textarea class="pl-input" id="hasil_konseling" name="hasil_konseling" required>{{ old('hasil_konseling') }}</textarea>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="terapi">Terapi Psikologi{!! $wajib !!}</label>
                    <textarea class="pl-input" id="terapi" name="terapi" required>{{ old('terapi', $konseling->terapi) }}</textarea>
                </div>
            </div>

            <div class="pl-fields !grid-cols-[repeat(auto-fit,minmax(17.5rem,1fr))]">
                <fieldset class="pl-field">
                    <legend class="mb-2">Apakah masih melanjutkan konseling?{!! $wajib !!}</legend>
                    <div class="pl-options">
                        @foreach (['1' => 'Perlu', '0' => 'Tidak'] as $nilai => $label)
                            <label class="pl-option"><input type="radio" name="lanjut_konseling" value="{{ $nilai }}" required @checked((string) $lanjut === (string) $nilai)>{{ $label }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <div class="pl-field" id="kolom-tanggal">
                    <label class="pl-field-label" for="tanggal_konseling_selanjutnya">Tanggal Konseling Selanjutnya{!! $wajib !!}</label>
                    <input class="pl-input" type="date" id="tanggal_konseling_selanjutnya" name="tanggal_konseling_selanjutnya" value="{{ old('tanggal_konseling_selanjutnya', $jadwal) }}" min="{{ today()->toDateString() }}">
                </div>
            </div>

            <div class="pl-form-actions">
                <a href="{{ route('laporan-kesehatan.index') }}" class="pl-btn pl-btn-ghost">Batal</a>
                <button type="submit" class="pl-btn pl-btn-primary">Perbarui</button>
            </div>
        </form>
    </main>

    @include('laporan-kesehatan.partials.tanggal-konseling')
</body>
</html>
