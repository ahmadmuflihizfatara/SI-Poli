{{-- laporan-kesehatan/keluhan-baru.blade.php — formulir "Tambah keluhan" design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Tambahkan Keluhan Baru'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $wajib = '<span class="pl-req" aria-hidden="true">*</span>';
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

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('laporan-kesehatan.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">Tambahkan Keluhan Baru</h1>
                <p class="pl-page-sub">Tambahkan informasi kontrol taruna yang melaporkan keluhan baru. Kolom bertanda * wajib diisi.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent max-w-3xl" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('laporan-kesehatan.store') }}" class="pl-form">
            @csrf

            <div class="pl-row">
                <section class="pl-panel" aria-labelledby="judul-umum">
                    <h2 class="pl-panel__title" id="judul-umum">Informasi Umum</h2>
                    @include('partials.pilih-taruna', ['taruna' => $taruna, 'wajib' => $wajib])
                </section>

                <section class="pl-panel" aria-labelledby="judul-kondisi">
                    <h2 class="pl-panel__title" id="judul-kondisi">Kondisi Kesehatan</h2>
                    <div class="pl-fields">
                        @foreach ($kondisi as [$name, $label, $attr, $req])
                            <div class="pl-field">
                                <label class="pl-field-label" for="{{ $name }}">{{ $label }}{!! $req ? $wajib : '' !!}</label>
                                <input class="pl-input" id="{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" {!! $attr !!} @required($req)>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="pl-panel" aria-labelledby="judul-keluhan">
                    <h2 class="pl-panel__title" id="judul-keluhan">Informasi Keluhan</h2>
                    <div class="pl-fields">
                        <div class="pl-field pl-field--wide">
                            <label class="pl-field-label" for="keluhan">Keluhan{!! $wajib !!}</label>
                            <textarea class="pl-input" id="keluhan" name="keluhan" required>{{ old('keluhan') }}</textarea>
                        </div>
                        <div class="pl-field pl-field--wide">
                            <label class="pl-field-label" for="terapi">Terapi dan obat{!! $wajib !!}</label>
                            <textarea class="pl-input" id="terapi" name="terapi" required>{{ old('terapi') }}</textarea>
                        </div>
                        <div class="pl-field pl-field--wide">
                            <label class="pl-field-label" for="hasil_pemeriksaan">Hasil pemeriksaan</label>
                            <textarea class="pl-input" id="hasil_pemeriksaan" name="hasil_pemeriksaan">{{ old('hasil_pemeriksaan') }}</textarea>
                        </div>
                        <fieldset class="pl-field pl-field--wide">
                            <legend class="mb-2">Tingkat keparahan{!! $wajib !!}</legend>
                            <div class="pl-options">
                                @foreach (['Ringan', 'Sedang', 'Berat'] as $s)
                                    <label class="pl-option"><input type="radio" name="status" value="{{ $s }}" required @checked(old('status') === $s)>{{ $s }}</label>
                                @endforeach
                            </div>
                        </fieldset>
                        <div class="pl-field pl-field--wide">
                            <label class="pl-field-label" for="tanggal_kontrol_selanjutnya">Tanggal kontrol selanjutnya{!! $wajib !!}</label>
                            <input class="pl-input" type="date" id="tanggal_kontrol_selanjutnya" name="tanggal_kontrol_selanjutnya" value="{{ old('tanggal_kontrol_selanjutnya') }}" min="{{ today()->toDateString() }}" required>
                        </div>
                        <div class="pl-field pl-field--wide">
                            <label class="pl-field-label" for="keterangan">Keterangan lainnya</label>
                            <textarea class="pl-input !min-h-[5.5rem]" id="keterangan" name="keterangan">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>
                </section>
            </div>

            <div class="pl-form-actions">
                <a href="{{ route('laporan-kesehatan.index') }}" class="pl-btn pl-btn-ghost">Batal</a>
                <button type="submit" class="pl-btn pl-btn-primary">Tambahkan keluhan</button>
            </div>
        </form>
    </main>

</body>
</html>
