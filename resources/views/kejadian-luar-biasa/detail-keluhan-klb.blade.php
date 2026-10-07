{{-- kejadian-luar-biasa/detail-keluhan-klb.blade.php — "Informasi keluhan KLB" design system Pagi (warna palet aplikasi).
     Form "Tambah Hasil Kontrol" ditambahkan supaya kolom Hasil Kontrol Terakhir & riwayat bisa diisi. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Informasi Keluhan'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $inisial = collect(preg_split('/\s+/', trim($keluhan->nama)))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
        $obat = collect(preg_split('/\r?\n/', trim($keluhan->terapi)))->filter();
        $nadaStatus = ['Ringan' => 'pl-badge-primary', 'Sedang' => 'pl-badge-notice', 'Berat' => 'pl-badge-accent'];
        $bolehKontrol = ! $klb->selesai() && auth()->user()->can('edit-data');
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('klb.show', $klb) }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">Informasi Keluhan</h1>
                <p class="pl-page-sub">Informasi rinci keluhan taruna pada {{ $klb->nama }}</p>
            </div>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="pl-profile" aria-label="Data taruna">
            <div class="pl-profile__who">
                <span class="pl-avatar" aria-hidden="true">{{ $inisial }}</span>
                <div>
                    <p class="pl-profile__name">{{ $keluhan->nama }}</p>
                    <p class="pl-profile__meta">Tingkat {{ $keluhan->tingkat }} &middot; {{ $keluhan->kelas }}</p>
                </div>
            </div>
            <dl class="pl-kvs">
                @foreach (['NPM' => $keluhan->npm, 'Kamar' => $keluhan->kamar, 'Jenis Kelamin' => $keluhan->jenis_kelamin,
                    'Tanggal Kontrol Selanjutnya' => $keluhan->tanggal_kontrol_selanjutnya->locale('id')->translatedFormat('j F Y')] as $label => $nilai)
                    <div class="pl-kv"><dt>{{ $label }}</dt><dd>{{ $nilai }}</dd></div>
                @endforeach
            </dl>
        </section>

        <div class="pl-row">
            <section class="pl-panel" aria-labelledby="judul-keluhan">
                <h2 class="pl-panel__title" id="judul-keluhan">Keluhan</h2>
                <p class="pl-panel__text whitespace-pre-line">{{ $keluhan->keluhan }}</p>
            </section>
            <section class="pl-panel" aria-labelledby="judul-obat">
                <h2 class="pl-panel__title" id="judul-obat">Obat</h2>
                <ul class="pl-list">
                    @foreach ($obat as $o)
                        <li>{{ $o }}</li>
                    @endforeach
                </ul>
            </section>
            <section class="pl-panel bg-secondary-50" aria-labelledby="judul-hasil">
                <div class="pl-panel__head items-start">
                    <h2 class="pl-panel__title" style="color: var(--secondary-800)" id="judul-hasil">Hasil Pemeriksaan</h2>
                    <span class="pl-badge {{ $nadaStatus[$keluhan->status] }}">Keparahan {{ strtolower($keluhan->status) }}</span>
                </div>
                <p class="pl-panel__text whitespace-pre-line">{{ $keluhan->hasil_pemeriksaan ?: 'Belum ada hasil pemeriksaan.' }}</p>
            </section>
        </div>

        <section aria-labelledby="judul-riwayat" class="pl-section">
            <h2 id="judul-riwayat" class="pl-section__title">Riwayat Evaluasi Kontrol</h2>
            <div class="pl-table-wrap">
                <table class="pl-table">
                    <colgroup><col style="width: 5rem"><col style="width: 15rem"><col></colgroup>
                    <thead>
                        <tr><th scope="col" class="c">No</th><th scope="col">Tanggal Kontrol</th><th scope="col">Hasil Kontrol</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($keluhan->kontrol as $i => $k)
                            <tr>
                                <td class="c">{{ $i + 1 }}</td>
                                <td class="tabular-nums">{{ $k->tanggal_kontrol->locale('id')->translatedFormat('j F Y') }}</td>
                                <td>{{ $k->hasil_kontrol }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="c !py-8 text-muted">Belum ada riwayat kontrol.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($bolehKontrol)
                <form method="POST" action="{{ route('klb.keluhan.kontrol', [$klb, $keluhan]) }}" class="pl-panel !flex-row flex-wrap items-end !gap-4">
                    @csrf
                    <div class="pl-field flex-1 min-w-[18rem]">
                        <label class="pl-field-label" for="hasil_kontrol">Tambah hasil kontrol hari ini<span class="pl-req" aria-hidden="true">*</span></label>
                        <input class="pl-input" id="hasil_kontrol" name="hasil_kontrol" value="{{ old('hasil_kontrol') }}" required placeholder="Kondisi taruna saat kontrol">
                    </div>
                    <div class="pl-field w-56">
                        <label class="pl-field-label" for="tanggal_kontrol_selanjutnya">Kontrol selanjutnya</label>
                        <input class="pl-input" type="date" id="tanggal_kontrol_selanjutnya" name="tanggal_kontrol_selanjutnya" value="{{ old('tanggal_kontrol_selanjutnya') }}" min="{{ today()->toDateString() }}">
                    </div>
                    <button type="submit" class="pl-btn pl-btn-primary">Simpan hasil kontrol</button>
                </form>
            @endif
        </section>
    </main>
</body>
</html>
