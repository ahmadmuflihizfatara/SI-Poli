{{-- laporan-kesehatan/keluhan-psikologi-baru.blade.php — prototype "Keluhan Psikolog Baru", susunan & gaya sama dengan
     keluhan-baru.blade.php (design system Pagi). Taruna dipilih dari tabel taruna seperti laporan kesehatan perawat. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Tambahkan Keluhan Baru'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php $wajib = '<span class="pl-req" aria-hidden="true">*</span>'; @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('laporan-kesehatan.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">Tambahkan Keluhan Baru</h1>
                <p class="pl-page-sub">Tambahkan informasi konseling taruna yang melaporkan keluhan baru. Kolom bertanda * wajib diisi.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent max-w-3xl" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('laporan-kesehatan.psikologi.store') }}" class="pl-form max-w-5xl">
            @csrf

            <div class="pl-row">
                <section class="pl-panel" aria-labelledby="judul-umum">
                    <h2 class="pl-panel__title" id="judul-umum">Informasi Umum</h2>
                    <div class="pl-field">
                        <label class="pl-field-label" for="taruna_id">Taruna{!! $wajib !!}</label>
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
                    <dl id="info-taruna" class="m-0 grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 body" aria-live="polite"></dl>
                </section>

                <section class="pl-panel" aria-labelledby="judul-keluhan">
                    <h2 class="pl-panel__title" id="judul-keluhan">Informasi Keluhan</h2>
                    <div class="pl-fields">
                        <div class="pl-field pl-field--wide">
                            <label class="pl-field-label" for="keluhan">Keluhan{!! $wajib !!}</label>
                            <textarea class="pl-input" id="keluhan" name="keluhan" required>{{ old('keluhan') }}</textarea>
                        </div>
                        <div class="pl-field pl-field--wide">
                            <label class="pl-field-label" for="terapi">Terapi psikologi{!! $wajib !!}</label>
                            <textarea class="pl-input" id="terapi" name="terapi" required>{{ old('terapi') }}</textarea>
                        </div>
                        <fieldset class="pl-field pl-field--wide">
                            <legend class="mb-2">Melanjutkan konseling?{!! $wajib !!}</legend>
                            <div class="pl-options">
                                @foreach (['1' => 'Masih lanjut', '0' => 'Tidak'] as $nilai => $label)
                                    <label class="pl-option"><input type="radio" name="lanjut_konseling" value="{{ $nilai }}" required @checked(old('lanjut_konseling') === (string) $nilai)>{{ $label }}</label>
                                @endforeach
                            </div>
                        </fieldset>
                        <div class="pl-field pl-field--wide" id="kolom-tanggal">
                            <label class="pl-field-label" for="tanggal_konseling_selanjutnya">Tanggal konseling selanjutnya{!! $wajib !!}</label>
                            <input class="pl-input" type="date" id="tanggal_konseling_selanjutnya" name="tanggal_konseling_selanjutnya" value="{{ old('tanggal_konseling_selanjutnya') }}" min="{{ today()->toDateString() }}">
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

    @include('laporan-kesehatan.partials.tanggal-konseling')

    <script>
        const pilihTaruna = document.getElementById('taruna_id');
        const infoTaruna = document.getElementById('info-taruna');
        const tampilkanInfo = () => {
            const info = JSON.parse(pilihTaruna.selectedOptions[0].dataset.info || '{}');
            infoTaruna.replaceChildren(...Object.entries(info).flatMap(([k, v]) => {
                const dt = document.createElement('dt'), dd = document.createElement('dd');
                dt.className = 'text-muted body-sm'; dt.textContent = k;
                dd.className = 'm-0 font-medium'; dd.textContent = v;
                return [dt, dd];
            }));
        };
        pilihTaruna.addEventListener('change', tampilkanInfo);
        tampilkanInfo();
    </script>
</body>
</html>
