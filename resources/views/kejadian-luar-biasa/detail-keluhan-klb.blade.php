{{-- kejadian-luar-biasa/detail-keluhan-klb.blade.php — mengikuti desain "Informasi Keluhan Kejadian Luar Biasa" (PNG), design system Pulih.
     Form "Tambah Hasil Kontrol" tidak ada di PNG; ditambahkan supaya kolom Hasil Kontrol Terakhir & riwayat bisa diisi. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Informasi Keluhan'])
    <style>
        .dk-avatar { width: 4.875rem; height: 4.875rem; border-radius: 9999px; background: var(--primary-700); color: #fff;
            display: flex; align-items: center; justify-content: center; font: 500 2rem/1 var(--font-sans); flex-shrink: 0; }
        .dk-ringkas { background: var(--primary-50); border-radius: var(--radius-sm); padding: .875rem 1.125rem; display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; }
        @media (min-width: 1024px) { .dk-ringkas { flex-wrap: nowrap; } }
        .dk-stat { display: flex; flex-direction: column; gap: .75rem; padding: 0 1rem; text-align: center; white-space: nowrap; }
        .dk-stat + .dk-stat { border-left: 1px solid var(--neutral-400); }
        .dk-kartu { border: 1px solid var(--neutral-400); border-radius: var(--radius-sm); background: var(--surface-card); padding: 1rem 1rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
        .dk-h { margin: 0; font: 600 1.375rem/1.875rem var(--font-heading); }
        .kr thead th { background: var(--primary-700); color: var(--on-primary); border-bottom-color: var(--primary-700); text-transform: none; font-size: .8125rem; }
        .kr td { font-size: .8125rem; }
        .kr .c { text-align: center; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $inisial = collect(preg_split('/\s+/', trim($keluhan->nama)))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
        $obat = collect(preg_split('/\r?\n/', trim($keluhan->terapi)))->filter();
        $nadaStatus = ['Ringan' => 'pl-badge-primary', 'Sedang' => 'pl-badge-notice', 'Berat' => 'pl-badge-accent'];
        $bolehKontrol = ! $klb->selesai() && auth()->user()->can('edit-data');
    @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-4 pr-4 py-6">

        <header class="flex flex-col gap-1">
            <a href="{{ route('klb.show', $klb) }}" class="pl-btn pl-btn-ghost self-start h-9 pl-2 pr-3">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
            <div class="flex flex-col gap-1">
                <h1 class="h1 m-0 text-primary-900">Informasi Keluhan</h1>
                <p class="body m-0 text-muted">Informasi rinci keluhan taruna pada {{ $klb->nama }}</p>
            </div>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info max-w-[56.5rem]" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="pl-alert pl-alert-accent max-w-[56.5rem]" role="alert">{{ $errors->first() }}</div>
        @endif

        <section aria-label="Data taruna" class="dk-ringkas max-w-[56.5rem]">
            <div class="flex items-center gap-4 mr-auto min-w-0">
                <div class="dk-avatar" aria-hidden="true">{{ $inisial }}</div>
                <div class="flex flex-col gap-0.5">
                    <p class="m-0 font-heading font-semibold text-[1.375rem] leading-7 text-primary-700">{{ $keluhan->nama }}</p>
                    <p class="body m-0">Tingkat {{ $keluhan->tingkat }}</p>
                    <p class="body m-0">{{ $keluhan->kelas }}</p>
                </div>
            </div>
            <dl class="flex m-0 body">
                @foreach (['NPM' => $keluhan->npm, 'Kamar' => $keluhan->kamar, 'Jenis Kelamin' => $keluhan->jenis_kelamin,
                    'Tanggal Kontrol Selanjutnya' => $keluhan->tanggal_kontrol_selanjutnya->locale('id')->translatedFormat('j F Y')] as $label => $nilai)
                    <div class="dk-stat">
                        <dt class="font-semibold">{{ $label }}</dt>
                        <dd class="m-0">{{ $nilai }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-11 gap-y-4 max-w-[56.5rem]">
            <section class="dk-kartu">
                <h2 class="dk-h text-primary-700">Keluhan</h2>
                <p class="body m-0 px-5 whitespace-pre-line">{{ $keluhan->keluhan }}</p>
            </section>
            <section class="dk-kartu">
                <h2 class="dk-h text-primary-700">Obat</h2>
                <ul class="body m-0 pl-10 pr-2 list-disc">
                    @foreach ($obat as $o)
                        <li>{{ $o }}</li>
                    @endforeach
                </ul>
            </section>
            <section class="rounded-[var(--radius-sm)] bg-secondary-50 p-4 pb-6 flex flex-col gap-4">
                <div class="flex flex-col items-start gap-2">
                    <h2 class="dk-h text-secondary-800">Hasil Pemeriksaan</h2>
                    <span class="pl-badge {{ $nadaStatus[$keluhan->status] }}">Keparahan {{ strtolower($keluhan->status) }}</span>
                </div>
                <p class="body m-0 px-5 whitespace-pre-line">{{ $keluhan->hasil_pemeriksaan ?: 'Belum ada hasil pemeriksaan.' }}</p>
            </section>
        </div>

        <section aria-labelledby="judul-riwayat" class="flex flex-col gap-4 mt-2">
            <h2 id="judul-riwayat" class="m-0 font-heading font-bold text-[1.5rem] leading-8 text-primary-700">Riwayat Evaluasi Kontrol</h2>
            <div class="pl-table-wrap">
                <table class="pl-table kr">
                    <colgroup><col style="width: 5rem"><col style="width: 15rem"><col></colgroup>
                    <thead>
                        <tr><th scope="col" class="c">No</th><th scope="col" class="c">Tanggal Kontrol</th><th scope="col" class="c">Hasil Kontrol</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($keluhan->kontrol as $i => $k)
                            <tr>
                                <td class="c">{{ $i + 1 }}</td>
                                <td class="c tabular-nums">{{ $k->tanggal_kontrol->locale('id')->translatedFormat('j F Y') }}</td>
                                <td class="c">{{ $k->hasil_kontrol }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="c !py-8 text-muted">Belum ada riwayat kontrol.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($bolehKontrol)
                <form method="POST" action="{{ route('klb.keluhan.kontrol', [$klb, $keluhan]) }}" class="pl-card p-5 flex flex-wrap items-end gap-4">
                    @csrf
                    <div class="pl-field flex-1 min-w-[18rem]">
                        <label class="pl-field-label" for="hasil_kontrol">Tambah hasil kontrol hari ini<span class="pl-req" aria-hidden="true"> *</span></label>
                        <input class="pl-input" id="hasil_kontrol" name="hasil_kontrol" value="{{ old('hasil_kontrol') }}" required placeholder="Kondisi taruna saat kontrol">
                    </div>
                    <div class="pl-field w-56">
                        <label class="pl-field-label" for="tanggal_kontrol_selanjutnya">Kontrol selanjutnya</label>
                        <input class="pl-input" type="date" id="tanggal_kontrol_selanjutnya" name="tanggal_kontrol_selanjutnya" value="{{ old('tanggal_kontrol_selanjutnya') }}" min="{{ today()->toDateString() }}">
                    </div>
                    <button type="submit" class="pl-btn pl-btn-primary h-10">Simpan hasil kontrol</button>
                </form>
            @endif
        </section>
    </main>

</body>
</html>
