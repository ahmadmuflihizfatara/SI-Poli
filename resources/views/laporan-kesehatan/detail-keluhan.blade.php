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
        /* baris terapi/obat: bernomor, kolom terapi : aturan pakai = 3 : 1, terapi tumbuh mengikuti isi */
        .ik-dialog #daftar-obat { counter-reset: obat; }
        .ik-dialog [data-baris-obat] { align-items: flex-start; }
        .ik-dialog [data-baris-obat]::before { counter-increment: obat; content: counter(obat); display: grid; place-items: center; width: 1.75rem; height: 1.75rem; flex: none;
            margin-top: calc(var(--tinggi-label) + (var(--tinggi-isian) - 1.75rem) / 2); border-radius: var(--radius-full); background: var(--strong); color: var(--on-primary); font: 600 max(12px, .75rem)/1 var(--font-sans); }
        .ik-dialog [data-kolom-obat] { grid-template-columns: minmax(0, 3fr) minmax(0, 1fr); }
        .ik-dialog [data-hapus-obat] { width: 2rem; height: 2rem; margin-top: calc(var(--tinggi-label) + (var(--tinggi-isian) - 2rem) / 2); }
        .ik-dialog [data-hapus-obat] svg { width: 1rem; height: 1rem; }
        .ik-dialog textarea.pl-input--tumbuh { min-height: var(--tinggi-isian); max-height: 10rem; padding-block: calc((var(--tinggi-isian) - 1.4em - 2px) / 2); }
        @media (max-width: 640px) {
            .ik-dialog { padding: 1.25rem; }
            .ik-dialog .pl-panel { padding: 1rem; }
            .ik-dialog .pl-panel .ik-bagian__judul { font-size: 1.125rem; }
            .ik-dialog [data-kolom-obat] { grid-template-columns: minmax(0, 1fr); }
        }
        /* kartu ringkas di bawah judul popup: taruna yang dikontrol + tanggal kontrol hari ini */
        .ik-ringkas { display: flex; flex-wrap: wrap; gap: .75rem 2rem; margin-top: .875rem; padding: .875rem 1.125rem; border-radius: var(--radius-image); background: var(--strong); color: var(--on-primary); }
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
                    <div><dt>Pegawai pemeriksa</dt><dd>{{ $keluhan->pembuat?->name ?? '-' }}</dd></div>
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
                            <tr>
                                <td class="tabular-nums">{{ $tanggal($k->tanggal_kontrol) }}</td>
                                {{-- waktu laporan = saat hasil kontrol dicatat --}}
                                <td class="tabular-nums">{{ $k->created_at?->format('H.i') ?? '-' }}</td>
                                <td>{{ $k->hasil_kontrol }}</td>
                                <td>
                                    @if ($k->perlu_rujukan)
                                        <div class="ik-rujuk">
                                            <span class="pl-badge pl-badge-accent">Dirujuk</span>
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
                            <div class="min-w-0"><small>Taruna</small><b>{{ $taruna->nama }}</b><small>NPM {{ $taruna->npm }}{{ $taruna->kelas ? ' · '.$taruna->kelas : '' }}</small></div>
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
