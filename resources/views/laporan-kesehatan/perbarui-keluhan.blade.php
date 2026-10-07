{{-- laporan-kesehatan/perbarui-keluhan.blade.php — halaman "Perbarui hasil kontrol" design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Perbarui Keluhan'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $taruna = $keluhan->taruna;
        $inisial = collect(explode(' ', $taruna->nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
        $nadaStatus = ['Ringan' => 'pl-badge-primary', 'Sedang' => 'pl-badge-notice', 'Berat' => 'pl-badge-accent'];
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
                <p class="pl-page-sub">Perbarui informasi keadaan kesehatan taruna</p>
            </div>
            <div class="pl-page-actions">
                <div class="pl-date">
                    <span class="pl-date__icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                    <span><small>Tanggal Kontrol</small><b>{{ now()->locale('id')->translatedFormat('d F Y') }}</b></span>
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
                <div class="pl-kv"><dt>Awal Keluhan</dt><dd>{{ $keluhan->tanggal_awal->format('d/m/Y') }}</dd></div>
                <div class="pl-kv"><dt>Kamar</dt><dd>{{ $taruna->kamar }}</dd></div>
                <div class="pl-kv"><dt>Tingkat Keparahan</dt><dd><span class="pl-badge pl-badge-lg {{ $nadaStatus[$keluhan->status] ?? 'pl-badge-primary' }}">{{ $keluhan->status }}</span></dd></div>
                <div class="pl-kv"><dt>NPM</dt><dd>{{ $taruna->npm }}</dd></div>
            </dl>
        </section>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('laporan-kesehatan.kontrol.update', $keluhan) }}" class="pl-panel" aria-labelledby="judul-form">
            @csrf
            <h2 class="pl-panel__title" id="judul-form">Perbarui Hasil Kontrol</h2>

            <div class="pl-fields !grid-cols-[repeat(auto-fit,minmax(17.5rem,1fr))]">
                <div class="pl-field">
                    <label class="pl-field-label" for="hasil_pemeriksaan">Hasil Pemeriksaan{!! $wajib !!}</label>
                    <textarea class="pl-input" id="hasil_pemeriksaan" name="hasil_pemeriksaan" required>{{ old('hasil_pemeriksaan') }}</textarea>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="terapi">Terapi dan Obat Terbaru{!! $wajib !!}</label>
                    <textarea class="pl-input" id="terapi" name="terapi" required>{{ old('terapi', $keluhan->terapi) }}</textarea>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="keterangan">Keterangan Lainnya</label>
                    <textarea class="pl-input" id="keterangan" name="keterangan">{{ old('keterangan') }}</textarea>
                </div>
            </div>

            <div class="pl-fields !grid-cols-[repeat(auto-fit,minmax(17.5rem,1fr))]">
                <fieldset class="pl-field">
                    <legend class="mb-2">Perlu dirujuk di luar poliklinik?{!! $wajib !!}</legend>
                    <div class="pl-options">
                        <label class="pl-option"><input type="radio" name="perlu_rujukan" value="1" required @checked(old('perlu_rujukan') === '1')>Perlu</label>
                        <label class="pl-option"><input type="radio" name="perlu_rujukan" value="0" required @checked(old('perlu_rujukan') === '0')>Tidak</label>
                    </div>
                </fieldset>
                <div class="pl-field">
                    <label class="pl-field-label" for="tanggal_kontrol_selanjutnya">Tanggal Kontrol Selanjutnya{!! $wajib !!}</label>
                    <input class="pl-input" type="date" id="tanggal_kontrol_selanjutnya" name="tanggal_kontrol_selanjutnya" value="{{ old('tanggal_kontrol_selanjutnya') }}" min="{{ today()->toDateString() }}" required>
                </div>
            </div>

            <div class="pl-form-actions">
                <a href="{{ route('laporan-kesehatan.index') }}" class="pl-btn pl-btn-ghost">Batal</a>
                <button type="submit" class="pl-btn pl-btn-primary">Perbarui</button>
            </div>
        </form>
    </main>
</body>
</html>
