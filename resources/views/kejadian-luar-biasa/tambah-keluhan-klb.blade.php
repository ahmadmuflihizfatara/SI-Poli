{{-- kejadian-luar-biasa/tambah-keluhan-klb.blade.php — mengikuti desain "Tambah Keluhan Kejadian Luar Biasa" (PNG), design system Pulih.
     Data taruna diketik langsung (tidak terhubung ke tabel taruna / laporan kesehatan utama). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Tambah Keluhan Baru'])
    <style>
        .kb-card { background: var(--surface-card); border: 1px solid var(--neutral-400); border-radius: var(--radius-sm); padding: 1.5rem 2rem 2rem; display: flex; flex-direction: column; gap: var(--space-3); }
        .kb-h { margin: 0 0 var(--space-2); font: 600 1.5rem/2rem var(--font-heading); color: var(--primary-700); }
        .kb-radio { width: 1.25rem; height: 1.25rem; accent-color: var(--primary-700); cursor: pointer; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $wajib = '<span class="pl-req" aria-hidden="true"> *</span>';
        // [name, label, atribut tambahan]
        $umum = [
            ['nama', 'Nama', 'maxlength="150" autofocus'],
            ['npm', 'NPM', 'maxlength="20" inputmode="numeric"'],
            ['kelas', 'Kelas', 'maxlength="20" placeholder="I RKS A"'],
        ];
    @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-4 pr-4 py-6">

        <header class="flex flex-col gap-1">
            <a href="{{ route('klb.show', $klb) }}" class="pl-btn pl-btn-ghost self-start h-9 pl-2 pr-3">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
            <div class="flex flex-col gap-1">
                <h1 class="h1 m-0 text-primary-900">Tambah Keluhan Baru</h1>
                <p class="body m-0 text-muted">Tambahkan keluhan taruna pada {{ $klb->nama }}. Kolom bertanda * wajib diisi.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent max-w-[54rem]" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('klb.keluhan.store', $klb) }}" class="grid grid-cols-1 lg:grid-cols-[minmax(0,26rem)_minmax(0,26rem)] gap-x-10 gap-y-5 items-start">
            @csrf

            <section class="kb-card">
                <h2 class="kb-h">Informasi Umum</h2>
                @foreach ($umum as [$name, $label, $attr])
                    <div class="pl-field">
                        <label class="pl-field-label" for="{{ $name }}">{{ $label }}{!! $wajib !!}</label>
                        <input class="pl-input" id="{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" {!! $attr !!} required>
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
                <fieldset class="pl-field border-0 p-0 m-0">
                    <legend class="pl-field-label mb-2">Jenis Kelamin{!! $wajib !!}</legend>
                    <div class="flex gap-8">
                        @foreach (['Laki-laki', 'Perempuan'] as $jk)
                            <label class="flex items-center gap-2 body cursor-pointer">
                                <input type="radio" class="kb-radio" name="jenis_kelamin" value="{{ $jk }}" @checked(old('jenis_kelamin') === $jk) required>
                                {{ $jk }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <div class="pl-field">
                    <label class="pl-field-label" for="kamar">Kamar{!! $wajib !!}</label>
                    <input class="pl-input" id="kamar" name="kamar" value="{{ old('kamar') }}" maxlength="20" required>
                </div>
            </section>

            <div class="flex flex-col gap-5">
                <section class="kb-card">
                    <h2 class="kb-h">Informasi Keluhan</h2>
                    <div class="pl-field">
                        <label class="pl-field-label" for="keluhan">Keluhan{!! $wajib !!}</label>
                        <textarea class="pl-input" id="keluhan" name="keluhan" required>{{ old('keluhan') }}</textarea>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="terapi">Terapi dan Obat{!! $wajib !!}</label>
                        <textarea class="pl-input" id="terapi" name="terapi" required aria-describedby="terapi-hint">{{ old('terapi') }}</textarea>
                        <div class="pl-field-hint" id="terapi-hint">Satu obat per baris.</div>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="hasil_pemeriksaan">Hasil Pemeriksaan</label>
                        <textarea class="pl-input" id="hasil_pemeriksaan" name="hasil_pemeriksaan">{{ old('hasil_pemeriksaan') }}</textarea>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="status">Tingkat Keparahan{!! $wajib !!}</label>
                        <select class="pl-input" id="status" name="status" required>
                            <option value="">Pilih tingkat keparahan</option>
                            @foreach (['Ringan', 'Sedang', 'Berat'] as $s)
                                <option value="{{ $s }}" @selected(old('status') === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="tanggal_kontrol_selanjutnya">Tanggal Kontrol Selanjutnya{!! $wajib !!}</label>
                        <input class="pl-input" type="date" id="tanggal_kontrol_selanjutnya" name="tanggal_kontrol_selanjutnya" value="{{ old('tanggal_kontrol_selanjutnya') }}" min="{{ today()->toDateString() }}" required>
                    </div>
                </section>
                <div class="flex justify-end items-center gap-3">
                    <a href="{{ route('klb.show', $klb) }}" class="pl-btn pl-btn-ghost h-11 px-5">Batal</a>
                    <button type="submit" class="pl-btn pl-btn-primary h-11 px-6 shadow-[0_4px_4px_rgba(0,0,0,0.25)]">Tambahkan Keluhan</button>
                </div>
            </div>
        </form>
    </main>

</body>
</html>
