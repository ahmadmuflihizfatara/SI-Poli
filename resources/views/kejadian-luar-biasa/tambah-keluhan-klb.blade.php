{{-- kejadian-luar-biasa/tambah-keluhan-klb.blade.php — formulir "Tambah keluhan KLB" design system Pagi (warna palet aplikasi).
     Taruna dipilih dari database seperti Laporan Kesehatan; datanya disalin ke keluhan KLB (terpisah dari laporan kesehatan utama). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Tambah Keluhan Baru'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $wajib = '<span class="pl-req" aria-hidden="true">*</span>';
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('klb.show', $klb) }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">Tambah Keluhan Baru</h1>
                <p class="pl-page-sub">Tambahkan keluhan taruna pada {{ $klb->nama }}. Kolom bertanda * wajib diisi.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent max-w-[54rem]" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('klb.keluhan.store', $klb) }}" class="pl-form max-w-[60rem]">
            @csrf

            <div class="pl-row">
                <section class="pl-panel" aria-labelledby="judul-umum">
                    <h2 class="pl-panel__title" id="judul-umum">Informasi Umum</h2>
                    @include('partials.pilih-taruna', ['taruna' => $taruna, 'wajib' => $wajib])
                </section>

                <section class="pl-panel" aria-labelledby="judul-keluhan">
                    <h2 class="pl-panel__title" id="judul-keluhan">Informasi Keluhan</h2>
                    <div class="pl-fields">
                        <div class="pl-field pl-field--wide">
                            <label class="pl-field-label" for="keluhan">Keluhan{!! $wajib !!}</label>
                            <textarea class="pl-input" id="keluhan" name="keluhan" required>{{ old('keluhan') }}</textarea>
                        </div>
                        <div class="pl-field pl-field--wide">
                            <label class="pl-field-label" for="terapi">Terapi dan Obat{!! $wajib !!}</label>
                            <textarea class="pl-input" id="terapi" name="terapi" required aria-describedby="terapi-hint">{{ old('terapi') }}</textarea>
                            <div class="pl-field-hint" id="terapi-hint">Satu obat per baris.</div>
                        </div>
                        <div class="pl-field pl-field--wide">
                            <label class="pl-field-label" for="hasil_pemeriksaan">Hasil Pemeriksaan</label>
                            <textarea class="pl-input" id="hasil_pemeriksaan" name="hasil_pemeriksaan">{{ old('hasil_pemeriksaan') }}</textarea>
                        </div>
                        <fieldset class="pl-field pl-field--wide">
                            <legend class="mb-2">Tingkat Keparahan{!! $wajib !!}</legend>
                            <div class="pl-options">
                                @foreach (['Ringan', 'Sedang', 'Berat'] as $s)
                                    <label class="pl-option"><input type="radio" name="status" value="{{ $s }}" required @checked(old('status') === $s)>{{ $s }}</label>
                                @endforeach
                            </div>
                        </fieldset>
                        <div class="pl-field pl-field--wide">
                            <label class="pl-field-label" for="tanggal_kontrol_selanjutnya">Tanggal Kontrol Selanjutnya{!! $wajib !!}</label>
                            <input class="pl-input" type="date" id="tanggal_kontrol_selanjutnya" name="tanggal_kontrol_selanjutnya" value="{{ old('tanggal_kontrol_selanjutnya') }}" min="{{ today()->toDateString() }}" required>
                        </div>
                    </div>
                </section>
            </div>

            <div class="pl-form-actions">
                <a href="{{ route('klb.show', $klb) }}" class="pl-btn pl-btn-ghost">Batal</a>
                <button type="submit" class="pl-btn pl-btn-primary">Tambahkan Keluhan</button>
            </div>
        </form>
    </main>
</body>
</html>
