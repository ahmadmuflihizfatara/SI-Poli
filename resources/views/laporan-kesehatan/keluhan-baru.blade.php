{{-- laporan-kesehatan/keluhan-baru.blade.php — formulir "Tambah keluhan" design system Pagi (warna palet aplikasi): satu kolom, per bagian --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Tambahkan Keluhan Baru'])
    <style>
        .kb-form { max-width: 56rem; }
        /* kepala bagian: nomor urut bulat + judul + penjelasan singkat */
        .kb-kepala { display: flex; align-items: flex-start; gap: 1rem; }
        .kb-no { display: grid; place-items: center; width: 2.5rem; height: 2.5rem; flex: none; border-radius: var(--radius-full); background: var(--strong); color: var(--on-primary); font: 600 1rem/1 var(--font-sans); }
        /* tanggal + jam laporan sebaris, bertumpuk di layar sempit */
        .kb-waktu { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); gap: .75rem; }
        @media (max-width: 640px) { .kb-waktu { grid-template-columns: 1fr; } }
    </style>
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
        // [nomor, id judul, judul, penjelasan]
        $bagian = [
            'umum' => [1, 'Informasi Umum', 'Taruna yang melapor dan waktu laporannya.'],
            'kondisi' => [2, 'Kondisi Kesehatan', 'Tanda vital saat taruna diperiksa.'],
            'keluhan' => [3, 'Informasi Keluhan', 'Keluhan, terapi, dan hasil pemeriksaan.'],
            'lanjut' => [4, 'Tindak Lanjut', 'Tingkat keparahan dan jadwal kontrol berikutnya.'],
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
            <div class="pl-alert pl-alert-accent pl-alert--ikon kb-form" role="alert">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('laporan-kesehatan.store') }}" class="pl-form kb-form" id="form-keluhan">
            @csrf

            @foreach ($bagian as $kunci => [$nomor, $judul, $penjelasan])
                <section class="pl-panel" aria-labelledby="judul-{{ $kunci }}">
                    <div class="kb-kepala">
                        <span class="kb-no" aria-hidden="true">{{ $nomor }}</span>
                        <div class="pl-panel__head">
                            <h2 class="pl-panel__title" id="judul-{{ $kunci }}">{{ $judul }}</h2>
                            <p class="pl-panel__sub">{{ $penjelasan }}</p>
                        </div>
                    </div>

                    @switch($kunci)
                        @case('umum')
                            @include('partials.pilih-taruna', ['taruna' => $taruna, 'wajib' => $wajib])
                            {{-- tanggal & jam terpisah di layar, dikirim sebagai satu waktu_laporan (digabung saat submit) --}}
                            <div class="pl-field">
                                <span class="pl-field-label" id="label-waktu-laporan">Waktu laporan{!! $wajib !!}</span>
                                <div class="kb-waktu" role="group" aria-labelledby="label-waktu-laporan" aria-describedby="waktu-laporan-hint">
                                    @include('partials.pilih-tanggal', [
                                        'id' => 'tanggal_laporan', 'name' => 'tanggal_laporan', 'nilai' => old('tanggal_laporan', today()->toDateString()),
                                        'max' => today()->toDateString(), 'wajib' => true, 'pintasan' => ['Hari ini' => 0, 'Kemarin' => -1, '2 hari lalu' => -2],
                                    ])
                                    <label class="pl-date pl-date--isian" for="jam_laporan">
                                        <span class="pl-date__icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
                                        <input class="pl-date__jam" type="time" id="jam_laporan" name="jam_laporan" value="{{ old('jam_laporan', now()->format('H:i')) }}" required>
                                        <span class="pl-date__pil">WIB</span>
                                    </label>
                                </div>
                                <input type="hidden" name="waktu_laporan" value="{{ old('waktu_laporan', now()->format('Y-m-d\TH:i')) }}">
                                <div class="pl-field-hint" id="waktu-laporan-hint">Terisi otomatis dengan waktu sekarang; ubah bila keluhan dilaporkan lebih awal.</div>
                            </div>
                            @break

                        @case('kondisi')
                            <div class="pl-fields">
                                @foreach ($kondisi as [$name, $label, $attr, $req])
                                    <div class="pl-field">
                                        <label class="pl-field-label" for="{{ $name }}">{{ $label }}{!! $req ? $wajib : '' !!}</label>
                                        <input class="pl-input" id="{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" {!! $attr !!} @required($req)>
                                    </div>
                                @endforeach
                            </div>
                            @break

                        @case('keluhan')
                            <div class="pl-fields">
                                <div class="pl-field pl-field--wide">
                                    <label class="pl-field-label" for="keluhan">Keluhan{!! $wajib !!}</label>
                                    <textarea class="pl-input" id="keluhan" name="keluhan" required>{{ old('keluhan') }}</textarea>
                                </div>
                                @include('laporan-kesehatan.partials.input-obat', ['obat' => old('obat'), 'label' => 'Terapi dan obat'])
                                <div class="pl-field pl-field--wide">
                                    <label class="pl-field-label" for="hasil_pemeriksaan">Hasil pemeriksaan</label>
                                    <textarea class="pl-input" id="hasil_pemeriksaan" name="hasil_pemeriksaan">{{ old('hasil_pemeriksaan') }}</textarea>
                                </div>
                            </div>
                            @break

                        @case('lanjut')
                            <div class="pl-fields">
                                <fieldset class="pl-field">
                                    <legend class="mb-2">Tingkat keparahan{!! $wajib !!}</legend>
                                    <div class="pl-options">
                                        @foreach (['Ringan', 'Sedang', 'Berat'] as $s)
                                            <label class="pl-option"><input type="radio" name="status" value="{{ $s }}" required @checked(old('status') === $s)>{{ $s }}</label>
                                        @endforeach
                                    </div>
                                </fieldset>
                                <div class="pl-field">
                                    <label class="pl-field-label" for="tanggal_kontrol_selanjutnya">Tanggal kontrol selanjutnya{!! $wajib !!}</label>
                                    @include('partials.pilih-tanggal', [
                                        'id' => 'tanggal_kontrol_selanjutnya', 'name' => 'tanggal_kontrol_selanjutnya', 'nilai' => old('tanggal_kontrol_selanjutnya'),
                                        'min' => today()->toDateString(), 'wajib' => true, 'pintasan' => \App\Models\Keluhan::PINTASAN_KONTROL,
                                    ])
                                </div>
                            </div>
                            @break
                    @endswitch
                </section>
            @endforeach

            <div class="pl-form-actions">
                <a href="{{ route('laporan-kesehatan.index') }}" class="pl-btn pl-btn-ghost">Batal</a>
                <button type="submit" class="pl-btn pl-btn-primary">Tambahkan keluhan</button>
            </div>
        </form>
    </main>

    <script>
        document.getElementById('form-keluhan').addEventListener('submit', (e) => {
            const f = e.target;
            f.elements.waktu_laporan.value = `${f.elements.tanggal_laporan.value}T${f.elements.jam_laporan.value}`;
        });
    </script>

</body>
</html>
