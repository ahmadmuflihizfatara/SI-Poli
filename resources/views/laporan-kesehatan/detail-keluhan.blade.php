{{-- laporan-kesehatan/detail-keluhan.blade.php — halaman "Informasi keluhan" design system Pagi (warna palet aplikasi) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Informasi Keluhan'])
    <style>
        /* Kembali: pil hijau muda + panah bergeser ke kiri saat hover, jelas bisa diklik */
        .pl-back { margin-left: -.75rem; padding: .375rem .875rem .375rem .625rem; border-radius: var(--radius-button); font-size: 14px; transition: background-color .15s, color .15s, box-shadow .15s; }
        .pl-back svg { transition: transform .2s ease; }
        .pl-back:hover { background: var(--cloud); color: var(--heading); box-shadow: inset 0 0 0 1px var(--field-line); }
        .pl-back:hover svg { transform: translateX(-4px); }
        /* tombol sembuh: hover berubah hijau pekat + terangkat */
        body .ik-sembuh { transition: background-color .2s, color .2s, box-shadow .2s, transform .2s; }
        body .ik-sembuh:hover { background: var(--strong); color: var(--on-primary); box-shadow: var(--shadow-lg); transform: translateY(-2px); opacity: 1; }
        @media (prefers-reduced-motion: reduce) { .pl-back:hover svg, body .ik-sembuh:hover { transform: none; } }
        /* Isi data 15–16px tebal 500; label tetap kecil */
        .ik-data { display: grid; grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr)); gap: 1.25rem 2rem; margin: 0; }
        .ik-data dt { font: 400 max(13px, .8125rem)/1.3 var(--font-sans); color: var(--text-muted); }
        .ik-data dd { margin: .25rem 0 0; font: 500 clamp(15px, 1rem, 16px)/1.4 var(--font-sans); color: var(--text); font-variant-numeric: tabular-nums; }
        .ik-isi { font-size: clamp(15px, 1rem, 16px); font-weight: 500; }
        /* Penanda berwarna selalu disertai ikon + teks (tidak bergantung pada warna saja). Warna tetap di tema gelap */
        .ik-tanda { display: inline-flex; align-items: center; gap: .375rem; padding: .3125rem .75rem .3125rem .625rem; border-radius: var(--radius-full); font: 600 max(14px, .875rem)/1.1 var(--font-sans); white-space: nowrap; }
        .ik-tanda svg { width: 1rem; height: 1rem; flex: none; }
        .ik-tanda--ringan { background: #146b5f; color: #fff; }
        .ik-tanda--sedang { background: #e3a531; color: #232620; }
        .ik-tanda--berat, .ik-tanda--telat { background: #b42318; color: #fff; }
        .ik-tanda--rawat { background: #b4492a; color: #fff; }
        .ik-tanda--lg { padding: .4375rem .9375rem .4375rem .75rem; font-size: max(15px, .9375rem); }
        .ik-tanda--lg svg { width: 1.125rem; height: 1.125rem; }
        .ik-kontrol { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
        /* Kartu sebaris sama tinggi; mengambang saat hover */
        .ik-row { align-items: stretch; }
        .ik-kartu { transition: transform .2s ease, box-shadow .2s ease; }
        .ik-kartu:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        @media (prefers-reduced-motion: reduce) { .ik-kartu { transition: none; } .ik-kartu:hover { transform: none; } }
        /* Kepala tabel hijau tua + teks putih tebal, sama dengan tabel Laporan Kesehatan */
        .ik-tabel th { background: var(--strong); color: var(--on-primary); font-weight: 600; font-size: max(14px, .875rem); border-bottom: 0; }
        /* Popup pencatatan kontrol: ukuran isian ikut skala layar (rem), dibatasi 40-46px supaya tidak kebesaran */
        .ik-dialog { --tinggi-isian: clamp(40px, 2.75rem, 46px); --tinggi-label: calc(1.4 * max(13px, .8125rem) + .5rem); width: min(54rem, calc(100vw - 2rem)); max-height: calc(100vh - 2rem); }
        /* bagian popup = panel hijau muda seperti formulir aplikasi, supaya isian putih terlihat jelas */
        .ik-dialog .pl-panel { gap: 1rem; padding: 1.25rem; border-radius: var(--radius-image); }
        .ik-dialog .pl-panel .ik-bagian__judul { margin: 0; padding: 0; font: 600 1.375rem/1.25 var(--font-sans); letter-spacing: -.01rem; color: var(--heading); }
        .ik-dialog .pl-option { min-height: 2.5rem; padding: 0 1rem; font: 500 max(14px, .875rem)/1.4 var(--font-sans); }
        /* hover pilihan (sebelum/sesudah makan, lainnya, perlu, tidak) dan tombol batal */
        .ik-dialog .pl-option { transition: background-color .15s, box-shadow .15s; }
        .ik-dialog .pl-option:hover:not(:has(input:checked)):not(:has(input:disabled)) { background: var(--cloud); box-shadow: inset 0 0 0 1px var(--strong); }
        .ik-dialog .pl-option:has(input:checked):hover { background: var(--strong-hover); }
        .ik-dialog .pl-btn-ghost:hover { background: var(--cloud); box-shadow: inset 0 0 0 1px var(--strong); opacity: 1; }
        /* isian berbentuk pil seperti halaman masuk, hurufnya seperti kotak cari tabel */
        .ik-dialog .pl-field-label, .ik-dialog .pl-field > legend { font: 600 max(13px, .8125rem)/1.4 var(--font-sans); }
        .ik-dialog .pl-input, .ik-dialog .pl-date__teks { font: 500 max(14px, .875rem)/1.4 var(--font-sans); }
        .ik-dialog .pl-input { min-height: var(--tinggi-isian); border-radius: var(--radius-button); }
        .ik-dialog input.pl-input { height: var(--tinggi-isian); padding: 0 1rem; }
        .ik-dialog .pl-combo input.pl-input { padding: 0 2.5rem; }
        .ik-dialog .pl-combo__ikon { left: .875rem; }
        .ik-dialog .pl-combo__chev { right: .875rem; }
        .ik-dialog .pl-combo__chev, .ik-dialog .pl-combo__ikon { top: calc(var(--tinggi-isian) / 2); width: 1rem; height: 1rem; }
        /* hasil kontrol: tinggi mengikuti isi (minimal 2 baris, maksimal ±12 baris lalu digulir) */
        .ik-dialog textarea.pl-input { min-height: 4rem; max-height: 16rem; padding: .625rem 1rem; border-radius: 1.25rem; field-sizing: content; resize: none; }
        /* skala nyeri NRS 0–10 (partial input-vital): tombol angka rapat */
        .ik-dialog .pl-nrs .pl-option { justify-content: center; padding: 0; border-radius: .75rem; font-variant-numeric: tabular-nums; }
        /* baris terapi/obat: bernomor, kolom terapi : aturan pakai = 3 : 1 */
        .ik-dialog #daftar-obat { counter-reset: obat; }
        .ik-dialog [data-baris-obat] { align-items: flex-start; }
        .ik-dialog [data-baris-obat]::before { counter-increment: obat; content: counter(obat); display: grid; place-items: center; width: 1.75rem; height: 1.75rem; flex: none;
            margin-top: calc(var(--tinggi-label) + (var(--tinggi-isian) - 1.75rem) / 2); border-radius: var(--radius-full); background: var(--strong); color: var(--on-primary); font: 600 max(12px, .75rem)/1 var(--font-sans); }
        .ik-dialog [data-kolom-obat] { grid-template-columns: minmax(0, 3fr) minmax(0, 1fr); }
        .ik-dialog [data-hapus-obat] { width: 2rem; height: 2rem; margin-top: calc(var(--tinggi-label) + (var(--tinggi-isian) - 2rem) / 2); }
        .ik-dialog [data-hapus-obat] svg { width: 1rem; height: 1rem; }
        @media (max-width: 640px) {
            .ik-dialog { padding: 1.25rem; }
            .ik-dialog .pl-panel { padding: 1rem; }
            .ik-dialog .pl-panel .ik-bagian__judul { font-size: 1.125rem; }
            .ik-dialog [data-kolom-obat] { grid-template-columns: minmax(0, 1fr); }
        }
        /* kartu ringkas di bawah judul popup: taruna yang dikontrol + tanggal kontrol hari ini */
        .ik-ringkas { display: flex; flex-wrap: wrap; gap: .75rem 2rem; margin-top: .875rem; padding: 1.125rem 1.375rem; border-radius: var(--radius-image); background: var(--strong); color: var(--on-primary); }
        .ik-ringkas > div { display: flex; align-items: center; gap: .75rem; min-width: 0; }
        .ik-ringkas__ikon { display: grid; place-items: center; width: 2.5rem; height: 2.5rem; flex: none; border-radius: var(--radius-full); background: rgb(255 255 255 / .16); font: 600 max(13px, .8125rem)/1 var(--font-sans); }
        .ik-ringkas__ikon svg { width: 1.125rem; height: 1.125rem; }
        .ik-ringkas small { display: block; font: 500 max(12px, .75rem)/1.3 var(--font-sans); opacity: .8; }
        .ik-ringkas b { display: block; font: 600 max(15px, 1rem)/1.3 var(--font-sans); }
        /* pilihan rujukan selebar isinya, tanggal kontrol mengisi sisa baris */
        .ik-lanjut { grid-template-columns: auto minmax(0, 1fr); }
        @media (max-width: 640px) { .ik-lanjut { grid-template-columns: minmax(0, 1fr); } }
        /* tempat rujukan muncul hanya bila "Perlu" dipilih */
        .ik-rujukan { display: none; }
        form:has([name="perlu_rujukan"][value="1"]:checked) .ik-rujukan { display: flex; }
        .ik-rujuk { display: flex; flex-wrap: wrap; align-items: center; gap: .375rem .5rem; }
        .ik-rujuk .pl-icon-btn { width: 2rem; height: 2rem; }
        .ik-rujuk .pl-icon-btn svg { width: 1rem; height: 1rem; }
        /* baris riwayat kontrol bisa diklik: hijau muda saat hover, tanggal bergaris bawah seperti tautan */
        .ik-baris { cursor: pointer; }
        .ik-baris td { transition: background-color .15s; }
        .ik-baris:hover td { background: var(--cloud); }
        .ik-baris__buka { padding: 0; border: 0; background: none; color: var(--link); font: inherit; font-weight: 600; font-variant-numeric: tabular-nums; text-decoration: underline; text-underline-offset: 3px; text-decoration-color: color-mix(in srgb, currentColor 40%, transparent); cursor: pointer; }
        .ik-baris:hover .ik-baris__buka { text-decoration-color: currentColor; }
        .ik-baris__buka:focus-visible { outline: 2px solid var(--focus); outline-offset: 2px; border-radius: 4px; }
        /* popup detail kontrol: kartu tanda vital berjejer, daftar obat bernomor */
        .ik-detail { width: min(46rem, calc(100vw - 2rem)); max-height: calc(100vh - 2rem); }
        .ik-detail .pl-panel { gap: .875rem; padding: 1.25rem; border-radius: var(--radius-image); }
        .ik-detail .pl-panel .ik-bagian__judul { margin: 0; font: 600 1.125rem/1.25 var(--font-sans); color: var(--heading); }
        .ik-detail__ke { margin: 0 0 .25rem; font: 600 max(12px, .75rem)/1.3 var(--font-sans); letter-spacing: .06em; text-transform: uppercase; color: var(--link); }
        .ik-detail__teks { margin: 0; font: 500 clamp(15px, 1rem, 16px)/1.5 var(--font-sans); white-space: pre-line; }
        /* kartu tanda vital: 3 kolom rata (2 di layar sempit), ikon bulat + label, nilai besar + satuan, rentang normal; putih tanpa garis seperti kartu obat */
        .ik-vital { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .625rem; margin: 0; }
        @media (max-width: 640px) { .ik-vital { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .ik-vital > div { display: flex; flex-direction: column; min-width: 0; padding: .875rem 1rem; border-radius: var(--radius-image); background: var(--surface-card); }
        .ik-vital dt { display: flex; align-items: center; gap: .5rem; min-width: 0; }
        .ik-vital__ikon { display: grid; place-items: center; width: 1.75rem; height: 1.75rem; flex: none; border-radius: var(--radius-full); background: var(--cloud); color: var(--link); }
        .ik-vital__ikon svg { width: 1rem; height: 1rem; }
        .ik-vital dd.ik-vital__normal { margin: auto 0 0; padding-top: .25rem; font: 400 max(12px, .75rem)/1.3 var(--font-sans); color: var(--text-muted); white-space: normal; }
        /* kartu mengambang saat di-hover */
        .ik-vital > div, .ik-obat li, .ik-ringkas > div { transition: transform .2s ease, box-shadow .2s ease; }
        .ik-vital > div:hover, .ik-obat li:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
        .ik-ringkas > div:hover { transform: translateY(-2px); }
        @media (prefers-reduced-motion: reduce) { .ik-vital > div, .ik-obat li, .ik-ringkas > div { transition: none; } .ik-vital > div:hover, .ik-obat li:hover, .ik-ringkas > div:hover { transform: none; } }
        .ik-vital dt { font: 500 max(13px, .8125rem)/1.3 var(--font-sans); color: var(--text-muted); }
        .ik-vital dd { margin: .5rem 0 0; font: 600 1.25rem/1.2 var(--font-sans); color: var(--heading); font-variant-numeric: tabular-nums; white-space: nowrap; }
        .ik-vital dd small { margin-left: .25rem; font: 500 max(12px, .75rem)/1 var(--font-sans); color: var(--text-muted); }
        .ik-obat { display: flex; flex-direction: column; gap: .5rem; margin: 0; padding: 0; list-style: none; counter-reset: obat; }
        .ik-obat li { display: grid; grid-template-columns: 1.75rem minmax(0, 1fr); column-gap: .75rem; align-items: center; padding: .875rem 1.125rem; border-radius: .875rem; background: var(--surface-card); }
        .ik-obat li::before { counter-increment: obat; content: counter(obat); grid-row: span 2; display: grid; place-items: center; width: 1.75rem; height: 1.75rem; border-radius: var(--radius-full); background: var(--strong); color: var(--on-primary); font: 600 max(12px, .75rem)/1 var(--font-sans); }
        .ik-obat b { font: 600 max(15px, .9375rem)/1.35 var(--font-sans); color: var(--text); overflow-wrap: anywhere; }
        .ik-obat span { font: 400 max(13px, .8125rem)/1.35 var(--font-sans); color: var(--text-muted); }
        .ik-detail .ik-data dd { display: flex; flex-wrap: wrap; align-items: center; gap: .375rem .5rem; }
        @media (max-width: 640px) { .ik-detail { padding: 1.25rem; } .ik-detail .pl-panel { padding: 1rem; } }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $taruna = $keluhan->taruna;
        $inisial = collect(explode(' ', $taruna->nama))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
        $tanggal = fn ($t) => $t->locale('id')->translatedFormat('j M Y');
        // [kelas warna, ikon] per status pemulihan: warna pekat + ikon, tanpa titik
        $tandaPemulihan = [
            'Dalam perawatan' => ['ik-tanda--rawat', 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
            'Isolasi mandiri' => ['ik-tanda--sedang', 'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
            'Sembuh' => ['ik-tanda--ringan', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ];
        [$kelasPemulihan, $ikonPemulihan] = $tandaPemulihan[$keluhan->status_pemulihan] ?? $tandaPemulihan['Dalam perawatan'];
        // [kelas warna, ikon] per tingkat keparahan
        $tandaStatus = [
            'Ringan' => ['ik-tanda--ringan', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            'Sedang' => ['ik-tanda--sedang', 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z'],
            'Berat' => ['ik-tanda--berat', 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z'],
        ];
        $sembuh = $keluhan->status_pemulihan === 'Sembuh';
        $bisaEdit = ! $sembuh && auth()->user()->can('edit-data');
        // ponytail: taruna yang sudah sembuh tidak punya jadwal kontrol lagi (sama seperti tabel laporan)
        $kontrol = $sembuh ? null : $keluhan->tanggal_kontrol_selanjutnya;
        $hariTelat = $kontrol?->lt(today()) ? (int) $kontrol->diffInDays(today()) : 0;
        $obat = $keluhan->daftarObat();
        $wajib = '<span class="pl-req" aria-hidden="true">*</span>';
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('laporan-kesehatan.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="pl-page-title">Informasi Keluhan</h1>
                    <span class="ik-tanda ik-tanda--lg {{ $kelasPemulihan }}"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $ikonPemulihan }}"/></svg>{{ $keluhan->status_pemulihan }}</span>
                </div>
                <p class="pl-page-sub">Informasi rinci keluhan kesehatan taruna</p>
            </div>
            @if ($bisaEdit)
                <div class="pl-page-actions">
                    <button type="button" class="pl-btn pl-btn-secondary ik-sembuh" data-konfirmasi="konfirmasi-sembuh">
                        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                        Nyatakan Sembuh
                    </button>
                </div>
            @endif
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info pl-alert--ikon" role="status">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="pl-row ik-row">
            <section class="pl-panel ik-kartu" aria-labelledby="judul-identitas">
                <h2 class="pl-panel__title" id="judul-identitas">Identitas</h2>
                <div class="pl-profile__who">
                    <span class="pl-avatar" aria-hidden="true">{{ $inisial }}</span>
                    <div>
                        <p class="pl-profile__name">{{ $taruna->nama }}</p>
                        <p class="pl-profile__meta">{{ $taruna->jenis_kelamin }}</p>
                    </div>
                </div>
                <dl class="ik-data">
                    <div><dt>NPM</dt><dd>{{ $taruna->npm }}</dd></div>
                    <div><dt>Tingkat</dt><dd>{{ $taruna->tingkat }}</dd></div>
                    <div><dt>Kelas</dt><dd>{{ $taruna->kelas ?: '-' }}</dd></div>
                    <div><dt>Kamar</dt><dd>{{ $taruna->kamar ?: '-' }}</dd></div>
                </dl>
            </section>

            <section class="pl-panel ik-kartu" aria-labelledby="judul-klinis">
                <h2 class="pl-panel__title" id="judul-klinis">Data Klinis</h2>
                <dl class="ik-data">
                    <div>
                        <dt>Awal keluhan</dt>
                        <dd>{{ $tanggal($keluhan->tanggal_awal) }}@if ($keluhan->waktu_laporan), {{ $keluhan->waktu_laporan->format('H.i') }}@endif</dd>
                    </div>
                    <div>
                        <dt>Tingkat keparahan</dt>
                        <dd>
                            @if (isset($tandaStatus[$keluhan->status]))
                                @php [$kelasTanda, $ikonTanda] = $tandaStatus[$keluhan->status]; @endphp
                                <span class="ik-tanda {{ $kelasTanda }}"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $ikonTanda }}"/></svg>{{ $keluhan->status }}</span>
                            @else
                                {{ $keluhan->status }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt>Kontrol selanjutnya</dt>
                        <dd class="ik-kontrol">
                            <span>{{ $kontrol ? $tanggal($kontrol) : '-' }}</span>
                            @if ($hariTelat > 0)
                                <span class="ik-tanda ik-tanda--telat"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>Terlambat {{ $hariTelat }} hari</span>
                            @elseif ($kontrol?->isToday())
                                <span class="ik-tanda ik-tanda--telat"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>Hari ini</span>
                            @endif
                        </dd>
                    </div>
                    <div><dt>Pegawai pemeriksa</dt><dd>{{ $keluhan->pemeriksa?->nama ?? $keluhan->pembuat?->name ?? '-' }}</dd></div>
                </dl>
            </section>
        </div>

        <div class="pl-row ik-row">
            <section class="pl-panel ik-kartu" aria-labelledby="judul-keluhan">
                <h2 class="pl-panel__title" id="judul-keluhan">Keluhan</h2>
                <p class="pl-panel__text ik-isi">{{ $keluhan->keluhan }}</p>
            </section>
        </div>

        <section class="pl-section" aria-labelledby="judul-terapi">
            <h2 class="pl-section__title" id="judul-terapi">Terapi dan Obat</h2>
            <div class="pl-table-wrap">
                <table class="pl-table ik-tabel">
                    <thead>
                        <tr>
                            <th scope="col">Terapi/Obat</th>
                            <th scope="col">Aturan Pakai</th>
                            <th scope="col">Waktu Minum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($obat as $o)
                            <tr>
                                <td>{{ $o['nama'] }}</td>
                                <td>{{ $o['aturan'] ?? '-' }}</td>
                                <td>{{ $o['waktu'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="c !py-8 text-muted">Belum ada terapi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="pl-section" aria-labelledby="judul-riwayat">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="pl-section__title" id="judul-riwayat">Riwayat Kontrol</h2>
                @if ($bisaEdit)
                    <button type="button" class="pl-btn pl-btn-primary" data-buka-kontrol>
                        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Catat Kontrol
                    </button>
                @endif
            </div>
            <div class="pl-table-wrap">
                <table class="pl-table ik-tabel">
                    <thead>
                        <tr>
                            <th scope="col">Tanggal Kontrol</th>
                            <th scope="col">Waktu Laporan</th>
                            <th scope="col">Hasil Kontrol</th>
                            <th scope="col">Rujukan</th>
                            <th scope="col">Pegawai Pemeriksa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($keluhan->riwayatKontrol as $k)
                            {{-- klik di mana saja pada baris membuka popup detail; tombol tanggal = jalur papan ketik --}}
                            <tr class="ik-baris" data-detail-kontrol="detail-kontrol-{{ $k->id }}">
                                <td class="tabular-nums"><button type="button" class="ik-baris__buka" aria-haspopup="dialog" aria-label="Lihat detail kontrol {{ $tanggal($k->tanggal_kontrol) }}">{{ $tanggal($k->tanggal_kontrol) }}</button></td>
                                {{-- waktu laporan = saat hasil kontrol dicatat --}}
                                <td class="tabular-nums">{{ $k->created_at?->format('H.i') ?? '-' }}</td>
                                <td>{{ $k->hasil_kontrol }}</td>
                                <td>
                                    @if ($k->perlu_rujukan)
                                        <div class="ik-rujuk">
                                            <span class="pl-badge pl-badge-solid pl-badge-accent">Dirujuk</span>
                                            <span @class(['text-muted' => ! $k->tempat_rujukan])>{{ $k->tempat_rujukan ?: 'Tempat belum diisi' }}</span>
                                            @if ($bisaEdit)
                                                <button type="button" class="pl-icon-btn" data-ubah-rujukan="{{ route('laporan-kesehatan.kontrol.rujukan', [$keluhan, $k]) }}" data-tempat="{{ $k->tempat_rujukan }}"
                                                    aria-label="{{ $k->tempat_rujukan ? 'Ubah' : 'Isi' }} tempat rujukan kontrol {{ $tanggal($k->tanggal_kontrol) }}">
                                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                {{-- data lama tanpa pemeriksa: tampilkan akun yang mencatat --}}
                                <td>{{ $k->pemeriksa?->nama ?? $k->pembuat?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="c !py-8 text-muted">Belum ada riwayat kontrol.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    {{-- Popup detail tiap kontrol: semua isian saat Catat Kontrol. Kontrol lama (sebelum kolom kondisi/terapi/jadwal ada) tampil "-" --}}
    @foreach ($keluhan->riwayatKontrol as $k)
        @php
            $angka = fn ($n, int $desimal = 0) => $n === null ? null : number_format((float) $n, $desimal, ',', '.');
            // [label, nilai, satuan, rentang normal (sama dengan teks bantuan partial input-vital), ikon]
            $kondisi = [
                ['Tekanan darah', $k->tekanan_darah, 'mmHg', 'Normal 90–120 / 60–80', 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
                ['Suhu', $angka($k->suhu, 1), '°C', 'Normal 36,1–37,2', 'M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z'],
                ['Nadi', $angka($k->nadi), 'kali/menit', 'Normal 60–100', 'm3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z'],
                ['Saturasi oksigen', $angka($k->saturasi), '%', 'Normal 95–100', 'M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5'],
                ['Pernapasan', $angka($k->pernapasan), 'kali/menit', 'Normal 12–20', 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99'],
                ['Skala nyeri', $angka($k->skala_nyeri), '/ 10', '0 tidak nyeri · 10 terhebat', 'M15.182 16.318A4.486 4.486 0 0 0 12.016 15a4.486 4.486 0 0 0-3.198 1.318M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z'],
            ];
            $obatKontrol = \App\Models\Keluhan::obatDariTerapi($k->terapi);
        @endphp
        <dialog id="detail-kontrol-{{ $k->id }}" class="pl-dialog ik-detail" aria-labelledby="judul-detail-{{ $k->id }}">
            <div class="pl-dialog__body">
                <div>
                    <p class="ik-detail__ke">Kontrol ke-{{ $loop->iteration }}</p>
                    <h2 class="pl-dialog__title" id="judul-detail-{{ $k->id }}">Detail Kontrol</h2>
                    <div class="ik-ringkas">
                        <div>
                            <span class="ik-ringkas__ikon" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                            <div><small>Tanggal kontrol</small><b>{{ $k->tanggal_kontrol->locale('id')->translatedFormat('l, j F Y') }}</b><small>Dicatat pukul {{ $k->created_at?->format('H.i') ?? '-' }}</small></div>
                        </div>
                        <div>
                            <span class="ik-ringkas__ikon" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></span>
                            <div class="min-w-0"><small>Pegawai pemeriksa</small><b>{{ $k->pemeriksa?->nama ?? $k->pembuat?->name ?? '-' }}</b><small>Taruna {{ $taruna->nama }}</small></div>
                        </div>
                    </div>
                </div>
                <section class="pl-panel" aria-labelledby="detail-kondisi-{{ $k->id }}">
                    <h3 class="ik-bagian__judul" id="detail-kondisi-{{ $k->id }}">Kondisi Kesehatan</h3>
                    <dl class="ik-vital">
                        @foreach ($kondisi as [$label, $nilai, $satuan, $normal, $ikon])
                            <div>
                                <dt><span class="ik-vital__ikon" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $ikon }}"/></svg></span>{{ $label }}</dt>
                                <dd>@if ($nilai !== null){{ $nilai }}<small>{{ $satuan }}</small>@else<span class="text-muted">-</span>@endif</dd>
                                <dd class="ik-vital__normal">{{ $normal }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
                <section class="pl-panel" aria-labelledby="detail-hasil-{{ $k->id }}">
                    <h3 class="ik-bagian__judul" id="detail-hasil-{{ $k->id }}">Hasil Kontrol</h3>
                    <p class="ik-detail__teks">{{ $k->hasil_kontrol }}</p>
                </section>
                <section class="pl-panel" aria-labelledby="detail-obat-{{ $k->id }}">
                    <h3 class="ik-bagian__judul" id="detail-obat-{{ $k->id }}">Terapi dan Obat</h3>
                    @if ($obatKontrol)
                        <ol class="ik-obat">
                            @foreach ($obatKontrol as $o)
                                <li><b>{{ $o['nama'] }}</b><span>{{ collect([$o['aturan'], $o['waktu']])->filter()->implode(' · ') ?: 'Tanpa aturan pakai' }}</span></li>
                            @endforeach
                        </ol>
                    @else
                        <p class="ik-detail__teks text-muted">Tidak tercatat pada kontrol ini.</p>
                    @endif
                </section>
                <section class="pl-panel" aria-labelledby="detail-lanjut-{{ $k->id }}">
                    <h3 class="ik-bagian__judul" id="detail-lanjut-{{ $k->id }}">Tindak Lanjut</h3>
                    <dl class="ik-data">
                        <div>
                            <dt>Rujukan</dt>
                            <dd>
                                @if ($k->perlu_rujukan)
                                    <span class="pl-badge pl-badge-accent">Dirujuk</span>
                                    <span @class(['text-muted' => ! $k->tempat_rujukan])>{{ $k->tempat_rujukan ?: 'Tempat belum diisi' }}</span>
                                @else
                                    Tidak dirujuk
                                @endif
                            </dd>
                        </div>
                        <div><dt>Kontrol selanjutnya</dt><dd>{{ $k->tanggal_kontrol_selanjutnya ? $tanggal($k->tanggal_kontrol_selanjutnya) : '-' }}</dd></div>
                    </dl>
                </section>
                <div class="pl-dialog__actions">
                    <button type="button" class="pl-btn pl-btn-primary" data-tutup>Tutup</button>
                </div>
            </div>
        </dialog>
    @endforeach

    <script>
        (() => {
            document.querySelectorAll('[data-detail-kontrol]').forEach((baris) => baris.addEventListener('click', (e) => {
                if (e.target.closest('[data-ubah-rujukan]')) { return; } // tombol ubah rujukan punya popupnya sendiri
                document.getElementById(baris.dataset.detailKontrol).showModal();
            }));
            document.querySelectorAll('.ik-detail').forEach((dialog) => {
                dialog.querySelector('[data-tutup]').addEventListener('click', () => dialog.close());
                dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
            });
        })();
    </script>

    @if ($bisaEdit)
        {{-- Popup Catat Kontrol; gagal validasi = popup terbuka lagi dengan isian lama. #catat-kontrol di URL (dari tabel laporan) langsung membukanya --}}
        <dialog id="catat-kontrol" class="pl-dialog ik-dialog" aria-labelledby="judul-kontrol" data-buka="{{ $errors->any() ? '1' : '' }}">
            <form method="POST" action="{{ route('laporan-kesehatan.kontrol.update', $keluhan) }}" class="pl-dialog__body">
                @csrf
                <div>
                    <h2 class="pl-dialog__title" id="judul-kontrol">Catat Kontrol</h2>
                    <div class="ik-ringkas">
                        <div>
                            <span class="ik-ringkas__ikon" aria-hidden="true">{{ $inisial }}</span>
                            <div class="min-w-0"><small>Taruna</small><b>{{ $taruna->nama }}</b><small>NPM {{ $taruna->npm }}</small></div>
                        </div>
                        <div>
                            <span class="ik-ringkas__ikon" aria-hidden="true"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                            <div><small>Tanggal kontrol</small><b>{{ now()->locale('id')->translatedFormat('l, j F Y') }}</b><small>Dicatat hari ini</small></div>
                        </div>
                    </div>
                </div>
                @if ($errors->any())
                    <div class="pl-alert pl-alert-accent pl-alert--ikon" role="alert">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
                <section class="pl-panel" aria-labelledby="bagian-kondisi">
                    <h3 class="ik-bagian__judul" id="bagian-kondisi">Kondisi Kesehatan</h3>
                    @include('laporan-kesehatan.partials.input-vital', ['wajib' => $wajib])
                </section>
                <section class="pl-panel" aria-labelledby="bagian-hasil">
                    <h3 class="ik-bagian__judul" id="bagian-hasil">Hasil Kontrol</h3>
                    <div class="pl-field">
                        <label class="pl-field-label" for="hasil_pemeriksaan">Hasil kontrol{!! $wajib !!}</label>
                        <textarea class="pl-input" id="hasil_pemeriksaan" name="hasil_pemeriksaan" required>{{ old('hasil_pemeriksaan') }}</textarea>
                    </div>
                    @include('partials.combobox', [
                        'id' => 'cari-pemeriksa', 'name' => 'pemeriksa_id', 'label' => 'Nama pemeriksa', 'wajib' => $wajib,
                        'placeholder' => 'Ketik nama dokter atau perawat', 'terpilih' => old('pemeriksa_id'),
                        'opsi' => $pegawai->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama, 'sub' => $p->jabatan, 'grup' => $p->jabatan]),
                    ])
                </section>
                <section class="pl-panel">
                    @include('laporan-kesehatan.partials.input-obat', ['obat' => old('obat', $obat), 'label' => 'Terapi dan Obat Terbaru', 'kelasLabel' => 'ik-bagian__judul mb-3'])
                </section>
                <section class="pl-panel" aria-labelledby="bagian-lanjut">
                    <h3 class="ik-bagian__judul" id="bagian-lanjut">Tindak Lanjut</h3>
                    <div class="pl-fields ik-lanjut">
                        <fieldset class="pl-field">
                            <legend class="mb-2">Perlu dirujuk di luar poliklinik?{!! $wajib !!}</legend>
                            <div class="pl-options">
                                <label class="pl-option"><input type="radio" name="perlu_rujukan" value="1" required @checked(old('perlu_rujukan') === '1')>Perlu</label>
                                <label class="pl-option"><input type="radio" name="perlu_rujukan" value="0" required @checked(old('perlu_rujukan') === '0')>Tidak</label>
                            </div>
                        </fieldset>
                        <div class="pl-field">
                            <label class="pl-field-label" for="tanggal_kontrol_selanjutnya">Tanggal kontrol selanjutnya{!! $wajib !!}</label>
                            @include('partials.pilih-tanggal', [
                                'id' => 'tanggal_kontrol_selanjutnya', 'name' => 'tanggal_kontrol_selanjutnya', 'nilai' => old('tanggal_kontrol_selanjutnya'), 'gaya' => 'tombol',
                                'min' => today()->toDateString(), 'wajib' => true, 'pintasan' => \App\Models\Keluhan::PINTASAN_KONTROL,
                            ])
                        </div>
                        <div class="pl-field pl-field--wide ik-rujukan">
                            <label class="pl-field-label" for="tempat_rujukan">Tempat rujukan <span class="text-muted">(opsional)</span></label>
                            <input class="pl-input" id="tempat_rujukan" name="tempat_rujukan" value="{{ old('tempat_rujukan') }}" maxlength="150" placeholder="Contoh: RSUD Kota Bogor" aria-describedby="tempat-rujukan-hint">
                            <div class="pl-field-hint" id="tempat-rujukan-hint">Belum pasti? Kosongkan dulu, isi nanti dari tabel Riwayat Kontrol.</div>
                        </div>
                    </div>
                </section>
                <div class="pl-dialog__actions">
                    <button type="button" class="pl-btn pl-btn-ghost" data-tutup>Batal</button>
                    <button type="submit" class="pl-btn pl-btn-primary">Simpan hasil kontrol</button>
                </div>
            </form>
        </dialog>

        <script>
            (() => {
                const dialog = document.getElementById('catat-kontrol');
                document.querySelector('[data-buka-kontrol]').addEventListener('click', () => {
                    dialog.querySelector('[role="alert"]')?.remove();
                    dialog.showModal();
                });
                dialog.querySelector('[data-tutup]').addEventListener('click', () => dialog.close());
                // cadangan untuk browser tanpa field-sizing: content (mis. Firefox): tinggi textarea ikut isi
                if (!CSS.supports('field-sizing', 'content')) {
                    dialog.querySelectorAll('textarea').forEach((t) => {
                        const ukur = () => { t.style.height = 'auto'; t.style.height = `${t.scrollHeight + 2}px`; };
                        t.addEventListener('input', ukur);
                        ukur();
                    });
                }
                dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
                if (dialog.dataset.buka || location.hash === '#catat-kontrol') { dialog.showModal(); }
                // #catat-kontrol cukup sekali: dihapus agar muat ulang tidak membuka popup lagi
                if (location.hash === '#catat-kontrol') { history.replaceState(null, '', location.pathname + location.search); }
            })();
        </script>

        <dialog id="ubah-rujukan" class="pl-dialog" aria-labelledby="judul-rujukan">
            <form method="POST" class="pl-dialog__body">
                @csrf
                @method('PATCH')
                <div>
                    <h2 class="pl-dialog__title" id="judul-rujukan">Tempat Rujukan</h2>
                    <p class="pl-dialog__text">Isi atau perbarui tempat rujukan {{ $taruna->nama }} setelah jelas.</p>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="ubah_tempat_rujukan">Tempat rujukan{!! $wajib !!}</label>
                    <input class="pl-input" id="ubah_tempat_rujukan" name="tempat_rujukan" maxlength="150" required placeholder="Contoh: RSUD Kota Bogor">
                </div>
                <div class="pl-dialog__actions">
                    <button type="button" class="pl-btn pl-btn-ghost" data-tutup>Batal</button>
                    <button type="submit" class="pl-btn pl-btn-primary">Simpan</button>
                </div>
            </form>
        </dialog>

        <script>
            (() => {
                const dialog = document.getElementById('ubah-rujukan'), form = dialog.querySelector('form');
                document.querySelectorAll('[data-ubah-rujukan]').forEach((b) => b.addEventListener('click', () => {
                    form.action = b.dataset.ubahRujukan;
                    form.elements.tempat_rujukan.value = b.dataset.tempat;
                    dialog.showModal();
                }));
                dialog.querySelector('[data-tutup]').addEventListener('click', () => dialog.close());
                dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
            })();
        </script>

        @include('partials.konfirmasi', [
            'id' => 'konfirmasi-sembuh', 'nada' => 'primary', 'ikon' => 'selesai',
            'aksi' => route('laporan-kesehatan.toggle-sembuh', $keluhan), 'metode' => 'PATCH',
            'judul' => "Nyatakan {$taruna->nama} sembuh?",
            'pesan' => 'Status pemulihan menjadi Sembuh dan tidak bisa dikembalikan.',
            'tombol' => 'Ya, nyatakan sembuh',
        ])
    @endif
</body>
</html>
