{{-- laporan-kesehatan/keluhan-psikologi-baru.blade.php — prototype "Keluhan Psikolog Baru", susunan & gaya sama dengan
     keluhan-baru.blade.php (design system Pulih). Taruna dipilih dari tabel taruna seperti laporan kesehatan perawat. --}}
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

    @php $wajib = '<span class="pl-req" aria-hidden="true"> *</span>'; @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-3 pr-4 pt-3 pb-2">

        <header class="flex flex-col gap-1">
            <a href="{{ route('laporan-kesehatan.index') }}" class="pl-btn pl-btn-ghost self-start h-9 pl-2 pr-3">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
            <div class="flex flex-col gap-1">
                <h1 class="h1 m-0 text-primary-900">Tambahkan Keluhan Baru</h1>
                <p class="body m-0 text-muted">Tambahkan informasi konseling taruna yang melaporkan keluhan baru. Kolom bertanda * wajib diisi.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent max-w-3xl" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('laporan-kesehatan.psikologi.store') }}" class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start max-w-5xl">
            @csrf

            <section class="kb-card">
                <h2 class="kb-h">Informasi Umum</h2>
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
                <dl id="info-taruna" class="m-0 grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 body" aria-live="polite"></dl>
            </section>

            <div class="flex flex-col gap-4">
                <section class="kb-card">
                    <h2 class="kb-h">Informasi Keluhan</h2>
                    <div class="pl-field">
                        <label class="pl-field-label" for="keluhan">Keluhan{!! $wajib !!}</label>
                        <textarea class="pl-input" id="keluhan" name="keluhan" required>{{ old('keluhan') }}</textarea>
                    </div>
                    <div class="pl-field">
                        <label class="pl-field-label" for="terapi">Terapi psikologi{!! $wajib !!}</label>
                        <textarea class="pl-input" id="terapi" name="terapi" required>{{ old('terapi') }}</textarea>
                    </div>
                    <fieldset class="m-0 p-0 border-0">
                        <legend class="pl-field-label p-0 mb-2">Melanjutkan konseling?{!! $wajib !!}</legend>
                        <div class="flex gap-8 h-10 items-center">
                            @foreach (['1' => 'Masih lanjut', '0' => 'Tidak'] as $nilai => $label)
                                <label class="flex items-center gap-2 body cursor-pointer">
                                    <input type="radio" name="lanjut_konseling" value="{{ $nilai }}" required class="w-[1.125rem] h-[1.125rem] m-0 accent-primary-700" @checked(old('lanjut_konseling') === (string) $nilai)>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <div class="pl-field" id="kolom-tanggal">
                        <label class="pl-field-label" for="tanggal_konseling_selanjutnya">Tanggal konseling selanjutnya{!! $wajib !!}</label>
                        <input class="pl-input" type="date" id="tanggal_konseling_selanjutnya" name="tanggal_konseling_selanjutnya" value="{{ old('tanggal_konseling_selanjutnya') }}" min="{{ today()->toDateString() }}">
                    </div>
                </section>
                <div class="flex justify-end gap-3">
                    <a href="{{ route('laporan-kesehatan.index') }}" class="pl-btn pl-btn-ghost h-11 px-5">Batal</a>
                    <button type="submit" class="pl-btn pl-btn-primary h-11 px-6">Tambahkan keluhan</button>
                </div>
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
                dt.className = 'text-muted'; dt.textContent = k;
                dd.className = 'm-0 font-medium'; dd.textContent = v;
                return [dt, dd];
            }));
        };
        pilihTaruna.addEventListener('change', tampilkanInfo);
        tampilkanInfo();
    </script>
</body>
</html>
