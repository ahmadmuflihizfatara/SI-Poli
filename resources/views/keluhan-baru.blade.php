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
        $umum = [
            ['nama', 'Nama', '', true],
            ['npm', 'NPM', 'inputmode="numeric"', true],
            ['kelas', 'Kelas', 'placeholder="II RKS A"', true],
        ];
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
                @foreach ($umum as [$name, $label, $attr, $req])
                    <div class="pl-field">
                        <label class="pl-field-label" for="{{ $name }}">{{ $label }}{!! $req ? $wajib : '' !!}</label>
                        <input class="pl-input" id="{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" {!! $attr !!} @required($req)>
                    </div>
                @endforeach
                <div class="pl-field">
                    <label class="pl-field-label" for="tingkat">Tingkat{!! $wajib !!}</label>
                    <select class="pl-input" id="tingkat" name="tingkat" required>
                        <option value="">Pilih tingkat</option>
                        @foreach (['I', 'II', 'III', 'IV'] as $t)
                            <option value="{{ $t }}" @selected(old('tingkat') === $t)>Tingkat {{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <fieldset class="m-0 p-0 border-0">
                    <legend class="pl-field-label p-0 mb-2">Jenis kelamin{!! $wajib !!}</legend>
                    <div class="flex gap-8 h-10 items-center">
                        @foreach (['Laki-laki', 'Perempuan'] as $jk)
                            <label class="flex items-center gap-2 body cursor-pointer">
                                <input type="radio" name="jenis_kelamin" value="{{ $jk }}" required @checked(old('jenis_kelamin') === $jk)
                                    class="w-[1.125rem] h-[1.125rem] m-0 accent-primary-700">
                                {{ $jk }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <div class="pl-field">
                    <label class="pl-field-label" for="kamar">Kamar{!! $wajib !!}</label>
                    <input class="pl-input" id="kamar" name="kamar" value="{{ old('kamar') }}" placeholder="C201" required>
                </div>
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
</body>
</html>
