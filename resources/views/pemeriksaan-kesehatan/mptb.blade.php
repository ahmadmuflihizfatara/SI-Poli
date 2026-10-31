{{-- pemeriksaan-kesehatan/mptb.blade.php — Halaman input MPTB (2 sesi per hari selama MPTB berjalan) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'MPTB'])
    <style>
        .mptb-table { table-layout: fixed; min-width: 91.5rem; }
        .mptb-table td { padding: .5rem .4rem; height: 3.25rem; }
        .mptb-table thead { position: sticky; top: 0; z-index: 1; }
        /* Kepala tabel tegas seperti Laporan Kesehatan: hijau tua + teks putih tebal */
        .mptb-table thead th { padding: .375rem .5rem; line-height: 1.25; text-align: center; vertical-align: middle; background: var(--strong); color: var(--on-primary); font-weight: 600; font-size: max(14px, .875rem); border-bottom: 0; }
        .mptb-table thead th small { display: block; font-weight: 400; font-size: max(12px, .75rem); opacity: .75; }
        .mptb-table thead th[colspan] { border-bottom: 1px solid color-mix(in srgb, var(--on-primary) 30%, transparent); }
        .mptb-table th.grp { border-left: 1px solid color-mix(in srgb, var(--on-primary) 30%, transparent); border-right: 1px solid color-mix(in srgb, var(--on-primary) 30%, transparent); }
        .mptb-table td.nm { text-align: left; font-weight: 500; border-right: 1px solid var(--border); padding-left: .875rem; } /* sekat antara kolom nama dan kolom lain */
        .mptb-table td.nm a { color: inherit; text-decoration: none; }
        .mptb-table td.nm a:hover { text-decoration: underline; text-underline-offset: 3px; }
        .mptb-table td.riwayat { text-align: center; color: var(--text-muted); }
        .mptb-table tbody tr { cursor: pointer; } /* klik baris = halaman detail taruna */
        .mptb-penanda { width: 1.125rem; height: 1.125rem; flex: none; }
        /* Isian minimalis: latar lembut tanpa garis, garis hijau saat fokus; tanda vital lebih ringkas */
        .mptb-in { box-sizing: border-box; display: block; width: 100%; height: 2.25rem; border: 1px solid transparent; border-radius: .625rem; background: var(--cloud); padding: 0 .625rem; font: inherit; color: var(--text); text-align: left; cursor: text; transition: background-color .15s, border-color .15s, box-shadow .15s; }
        select.mptb-in { cursor: pointer; }
        .mptb-in.vital { height: 2rem; max-width: 5.5rem; margin: 0 auto; text-align: center; font-variant-numeric: tabular-nums; }
        .mptb-in::placeholder { color: var(--neutral-400); }
        .mptb-in:hover { border-color: var(--field-line); }
        .mptb-in:focus-visible { outline: 0; background: var(--surface-card); border-color: var(--strong); box-shadow: 0 0 0 3px color-mix(in srgb, var(--strong) 18%, transparent); }
        .mptb-in:disabled { background: transparent; color: var(--text-muted); cursor: not-allowed; }
        .mptb-in[aria-invalid="true"] { border-color: var(--danger); background: var(--danger-wash); }
        .mptb-in.suhu-tinggi { background: var(--danger-wash); color: var(--danger); font-weight: 700; }
        #mptb-kosong { display: none; }
        /* Informasi hari, tanggal, sesi (pengganti teks penjelasan di bawah judul) */
        .pk-info { display: flex; flex-wrap: wrap; width: fit-content; max-width: 100%; padding: .5rem; border-radius: var(--radius-card); background: var(--cloud); }
        .pk-info > div { display: flex; align-items: center; gap: .75rem; padding: .5rem 1.25rem; }
        .pk-info > div + div { border-left: 1px solid var(--border); }
        .pk-info__ikon { display: grid; place-items: center; width: 2.5rem; height: 2.5rem; flex: none; border-radius: 9999px; background: var(--surface-card); color: var(--link); }
        .pk-info__ikon svg { width: 1.25rem; height: 1.25rem; }
        .pk-info small { display: block; font: 400 max(12px, .75rem)/1.3 var(--font-sans); color: var(--text-muted); }
        .pk-info b { display: flex; align-items: center; gap: .5rem; font: 600 1rem/1.3 var(--font-sans); color: var(--heading); }

        /* Pemilih tanggal: tombol tanggal + kalender popover, sama dengan rentang tanggal di dashboard */
        .pl-toolbar { position: relative; z-index: 5; }
        /* Semua kontrol di toolbar setinggi 2.75rem (cari, tanggal, segmen, tombol) */
        .pl-toolbar .pl-btn { height: 2.75rem; }
        .pl-toolbar .pl-segment { align-self: center; }
        .pilih-tanggal > .pl-date { border: 0; cursor: pointer; text-align: left; min-height: 0; height: 2.75rem; gap: .5rem; padding: .25rem 1rem .25rem .25rem; transition: background-color .2s, box-shadow .2s, transform .2s; }
        .pilih-tanggal .pl-date__icon { width: 2.25rem; height: 2.25rem; transition: background-color .2s, color .2s; }
        .pilih-tanggal .pl-date b { font: 500 max(14px, .875rem)/1.4 var(--font-sans); }
        .pilih-tanggal > .pl-date:hover { background: var(--surface-card); box-shadow: 0 0 0 1px var(--border), var(--shadow-lg); transform: translateY(-1px); }
        .pilih-tanggal > .pl-date[aria-expanded="true"] { background: var(--surface-card); box-shadow: 0 0 0 2px var(--strong); }
        .pilih-tanggal > .pl-date:hover .pl-date__icon, .pilih-tanggal > .pl-date[aria-expanded="true"] .pl-date__icon { background: var(--strong); color: var(--on-primary); }
        .kal-pop { position: absolute; left: 0; top: calc(100% + .5rem); z-index: 30; width: 19rem; padding: 1rem; box-sizing: border-box; border-radius: 1.5rem;
                   background: var(--surface-card); box-shadow: 0 0 0 1px var(--border), var(--shadow-lg);
                   opacity: 0; visibility: hidden; transform: translateY(-.5rem) scale(.97); transform-origin: top left;
                   transition: opacity .18s ease, transform .22s cubic-bezier(.2, .7, .2, 1), visibility 0s .22s; }
        .kal-pop.on { opacity: 1; visibility: visible; transform: none; transition-delay: 0s; }
        .kal-kepala { display: flex; align-items: center; justify-content: space-between; margin-bottom: .5rem; font: 600 1rem/1.3 var(--font-sans); color: var(--heading); }
        .kal-grid { display: grid; grid-template-columns: repeat(7, 1fr); row-gap: .25rem; }
        .kal-grid > span { display: grid; place-items: center; height: 2rem; font: 500 max(12px, .75rem)/1 var(--font-sans); color: var(--text-muted); }
        .kal-grid > button { height: 2.25rem; border: 0; padding: 0; background: transparent; color: var(--text); font: 500 max(14px, .875rem)/1 var(--font-sans); font-variant-numeric: tabular-nums; cursor: pointer; }
        .kal-grid > button > span { display: grid; place-items: center; width: 2.25rem; height: 2.25rem; margin: auto; border-radius: 9999px; transition: background-color .15s, color .15s; }
        .kal-grid > button:hover:not(:disabled) > span { background: var(--cloud); }
        .kal-grid > button.hari-ini > span { box-shadow: inset 0 0 0 1px var(--field-line); }
        .kal-grid > button.pilih > span { background: var(--strong); color: var(--on-primary); }
        .kal-grid > button:disabled { color: var(--neutral-400); cursor: default; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $hari = \Carbon\Carbon::parse($tanggal)->locale('id');
        $labelTanggal = $hari->translatedFormat('l, d F Y');
        $ikonSesi = $sesi === 'Pagi'
            ? 'M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z'
            : 'M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z';
        $suhuBatas = 37.5; // contoh saja, batas resmi ditentukan tenaga medis
        $selesai = $kegiatan->selesai();
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('pemeriksaan-kesehatan.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">{{ $kegiatan->nama }}</h1>
            </div>
        </header>

        <section class="pk-info" aria-label="Informasi pemeriksaan">
            <div>
                <span class="pk-info__ikon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                <span><small>Hari</small><b>{{ $hari->translatedFormat('l') }} @if ($hari->isToday())<span class="pl-badge pl-badge-primary">Hari ini</span>@endif</b></span>
            </div>
            <div>
                <span class="pk-info__ikon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                <span><small>Tanggal</small><b>{{ $hari->translatedFormat('d F Y') }}</b></span>
            </div>
            <div>
                <span class="pk-info__ikon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $ikonSesi }}"/></svg></span>
                <span><small>Sesi</small><b>Sesi {{ strtolower($sesi) }}</b></span>
            </div>
            <div>
                <span class="pk-info__ikon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5"/></svg></span>
                <span><small>Periode MPTB</small><b>{{ $kegiatan->labelRentang() }}</b></span>
            </div>
        </section>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status" style="flex-direction: row; align-items: center; gap: .75rem">
                <svg class="w-6 h-6 flex-none" style="color: var(--link)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="pl-alert pl-alert-accent" role="alert" style="flex-direction: row; align-items: center; gap: .75rem">
                <svg class="w-6 h-6 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.008v.008H12v-.008Z"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif
        @if ($selesai)
            <div class="pl-alert pl-alert-info" role="note" style="flex-direction: row; align-items: center; gap: .75rem">
                <svg class="w-6 h-6 flex-none" style="color: var(--link)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                <span>Pemeriksaan ini sudah selesai. Hasilnya hanya bisa dilihat.</span>
            </div>
        @endif

        {{-- Cari nama, pilih tanggal & sesi (ganti tanggal/sesi memuat ulang halaman), saring status --}}
        <form id="mptb-pilih" method="GET" action="{{ route('pemeriksaan-kesehatan.show', $kegiatan) }}" class="pl-toolbar">
            <label class="pl-search">
                <span class="pl-sr">Cari nama taruna</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="mptb-cari" type="search" class="pl-input" placeholder="Cari nama taruna" onkeydown="if (event.key === 'Enter') event.preventDefault()">
            </label>
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
            <div class="pilih-tanggal relative">
                <button type="button" class="pl-date" aria-haspopup="dialog" aria-expanded="false" aria-controls="kal-pop" aria-label="Pilih tanggal, sekarang {{ $labelTanggal }}">
                    <span class="pl-date__icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                    <span><b>{{ $labelTanggal }}</b></span>
                </button>
                <div id="kal-pop" class="kal-pop" role="dialog" aria-label="Kalender pilih tanggal"
                    data-tanggal="{{ $tanggal }}" data-hari-ini="{{ today()->toDateString() }}" data-tersedia="{{ $tanggalTersedia->toJson() }}">
                    <div class="kal-kepala">
                        <button type="button" class="pl-icon-btn" data-geser="-1" aria-label="Bulan sebelumnya"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 19.5 8.25 12l7.5-7.5"/></svg></button>
                        <span aria-live="polite" data-bulan></span>
                        <button type="button" class="pl-icon-btn" data-geser="1" aria-label="Bulan berikutnya"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg></button>
                    </div>
                    <div class="kal-grid" data-grid></div>
                </div>
            </div>
            <div class="pl-segment" role="group" aria-label="Pilih sesi">
                @foreach (['Pagi', 'Malam'] as $s)
                    <button type="submit" name="sesi" value="{{ $s }}" aria-pressed="{{ $s === $sesi ? 'true' : 'false' }}">Sesi {{ strtolower($s) }}</button>
                @endforeach
            </div>
            <div class="pl-segment" id="mptb-filter" role="group" aria-label="Saring status pemeriksaan">
                <button type="button" class="chip" data-f="all" aria-pressed="true">Semua</button>
                <button type="button" class="chip" data-f="belum" aria-pressed="false">Belum diperiksa</button>
                <button type="button" class="chip" data-f="sudah" aria-pressed="false">Sudah diperiksa</button>
            </div>
            <div class="flex flex-wrap items-center gap-3 ml-auto">
                    @if ($selesai)
                        <span class="pl-badge pl-badge-primary">Selesai</span>
                    @else
                        @can('edit-data')
                            <button type="button" class="pl-btn pl-btn-secondary" data-konfirmasi="konfirmasi-selesai">
                                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Selesaikan pemeriksaan
                            </button>
                        @endcan
                        <button type="submit" form="form-mptb" class="pl-btn pl-btn-primary">
                            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Simpan perubahan
                        </button>
                    @endif
            </div>
        </form>

        {{-- Tabel input hasil pemeriksaan --}}
        <form id="form-mptb" method="POST" action="{{ route('pemeriksaan-kesehatan.simpan', $kegiatan) }}" class="flex flex-col gap-3">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
            <input type="hidden" name="sesi" value="{{ $sesi }}">

            <div class="pl-table-wrap max-h-[62vh]">
                <table class="pl-table mptb-table">
                    {{-- lebar kolom di sini: table-layout fixed hanya membaca baris kepala pertama, yang berisi sel gabungan --}}
                    <colgroup>
                        <col style="width: 13rem">
                        <col style="width: 6.5rem">
                        <col style="width: 5.25rem">
                        <col style="width: 5.25rem">
                        <col style="width: 7.5rem">
                        <col style="width: 16rem">
                        <col style="width: 11rem">
                        <col style="width: 11rem">
                        <col style="width: 16rem">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Tekanan darah<small>mmHg</small></th>
                            <th>Nadi<small>x/menit</small></th>
                            <th>Suhu<small>°C</small></th>
                            <th>Pernapasan<small>x/menit</small></th>
                            <th>Keluhan</th>
                            <th>Keluhan sesi sebelumnya</th>
                            <th>Terapi sesi sebelumnya</th>
                            <th>Terapi</th>
                        </tr>
                    </thead>
                    <tbody id="mptb-isi">
                        @foreach ($taruna as $t)
                            @php $h = old("data.{$t['id']}", $t['hasil']); $kunci = $selesai || ! auth()->user()->bisa(array_filter($t['hasil']) ? 'edit' : 'tambah'); @endphp
                            <tr data-nama="{{ strtolower($t['nama']) }}">
                                <td class="nm">
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('pemeriksaan-kesehatan.detail', [$kegiatan, $t['id']]) }}" class="truncate" data-detail>{{ $t['nama'] }}</a>
                                        {{-- centang = sudah diperiksa (diatur skrip) --}}
                                        <svg class="mptb-penanda" data-status hidden style="color: var(--link)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Sudah diperiksa"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    </div>
                                </td>
                                <td><input type="text" class="mptb-in vital" data-k="tekanan_darah" name="data[{{ $t['id'] }}][tekanan_darah]" @disabled($kunci) @error("data.{$t['id']}.tekanan_darah") aria-invalid="true" @enderror value="{{ $h['tekanan_darah'] }}" placeholder="120/80" aria-label="Tekanan darah {{ $t['nama'] }}"></td>
                                <td><input type="text" inputmode="numeric" class="mptb-in vital" data-k="nadi" name="data[{{ $t['id'] }}][nadi]" @disabled($kunci) @error("data.{$t['id']}.nadi") aria-invalid="true" @enderror value="{{ $h['nadi'] }}" placeholder="80" aria-label="Nadi {{ $t['nama'] }}"></td>
                                <td><input type="text" inputmode="decimal" class="mptb-in vital mptb-suhu" data-k="suhu" name="data[{{ $t['id'] }}][suhu]" @disabled($kunci) @error("data.{$t['id']}.suhu") aria-invalid="true" @enderror value="{{ $h['suhu'] }}" placeholder="36,5" aria-label="Suhu {{ $t['nama'] }}"></td>
                                <td><input type="text" inputmode="numeric" class="mptb-in vital" data-k="pernapasan" name="data[{{ $t['id'] }}][pernapasan]" @disabled($kunci) @error("data.{$t['id']}.pernapasan") aria-invalid="true" @enderror value="{{ $h['pernapasan'] }}" placeholder="20" aria-label="Pernapasan {{ $t['nama'] }}"></td>
                                <td><input type="text" class="mptb-in" data-k="keluhan" name="data[{{ $t['id'] }}][keluhan]" @disabled($kunci) @error("data.{$t['id']}.keluhan") aria-invalid="true" @enderror value="{{ $h['keluhan'] }}" aria-label="Keluhan {{ $t['nama'] }}"></td>
                                <td class="riwayat">{{ $t['keluhan_sebelumnya'] }}</td>
                                <td class="riwayat">{{ $t['terapi_sebelumnya'] }}</td>
                                <td><input type="text" class="mptb-in" data-k="terapi" name="data[{{ $t['id'] }}][terapi]" @disabled($kunci) @error("data.{$t['id']}.terapi") aria-invalid="true" @enderror value="{{ $h['terapi'] }}" aria-label="Terapi {{ $t['nama'] }}"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p id="mptb-kosong" class="text-center py-8 m-0 text-muted bg-white rounded-2xl mt-2">Tidak ada taruna yang cocok dengan pencarian atau filter.</p>
            </div>
        </form>
    </main>

    @if (! $selesai && auth()->user()->can('edit-data'))
        @include('partials.konfirmasi', [
            'id' => 'konfirmasi-selesai', 'nada' => 'primary', 'ikon' => 'selesai',
            'aksi' => route('pemeriksaan-kesehatan.selesai', $kegiatan), 'metode' => 'PATCH',
            'judul' => 'Selesaikan pemeriksaan?',
            'pesan' => "Pemeriksaan {$kegiatan->nama} ditutup per hari ini dan hasilnya tidak bisa diubah lagi. Simpan dulu isian yang belum disimpan.",
            'tombol' => 'Ya, selesaikan',
        ])
    @endif

    <script>
        (() => {
            const suhuBatas = {{ $suhuBatas }};
            const baris = [...document.querySelectorAll('#mptb-isi tr')];
            const kosong = document.getElementById('mptb-kosong');
            let filterAktif = 'all';
            let kataKunci = '';
            let berubah = false; // ada isian yang belum disimpan

            function statusBaris(tr) {
                const ambil = (k) => tr.querySelector(`[data-k="${k}"]`).value.trim();
                const lengkap = ['tekanan_darah', 'nadi', 'suhu', 'pernapasan', 'keluhan'].every((k) => ambil(k) !== '');
                const suhu = parseFloat(ambil('suhu').replace(',', '.'));
                tr.querySelector('.mptb-suhu').classList.toggle('suhu-tinggi', !Number.isNaN(suhu) && suhu >= suhuBatas);
                tr.querySelector('[data-status]').hidden = !lengkap;
                return lengkap;
            }

            function perbarui() {
                let tampil = 0;
                baris.forEach((tr) => {
                    const lengkap = statusBaris(tr);
                    const cocokNama = tr.dataset.nama.includes(kataKunci);
                    const cocokFilter =
                        filterAktif === 'all' ||
                        (filterAktif === 'belum' && !lengkap) ||
                        (filterAktif === 'sudah' && lengkap);
                    const cocok = cocokNama && cocokFilter;
                    tr.hidden = !cocok;
                    if (cocok) tampil += 1;
                });
                kosong.style.display = tampil ? 'none' : 'block';
            }

            const isi = document.getElementById('mptb-isi');
            const tinggalkan = () => !berubah || confirm('Ada isian yang belum disimpan. Tinggalkan halaman ini?');
            isi.addEventListener('input', () => { berubah = true; perbarui(); });
            document.getElementById('form-mptb').addEventListener('submit', () => { berubah = false; });
            document.getElementById('mptb-pilih').addEventListener('submit', (e) => { if (!tinggalkan()) e.preventDefault(); });

            // Klik baris (selain isian) membuka halaman detail taruna
            isi.addEventListener('click', (e) => {
                if (e.target.closest('input, select') || e.ctrlKey || e.metaKey) return;
                e.preventDefault();
                if (tinggalkan()) location.href = e.target.closest('tr').querySelector('[data-detail]').href;
            });

            isi.addEventListener('keydown', (e) => {
                if (e.key !== 'Enter' || !e.target.matches('.mptb-in')) return;
                e.preventDefault();
                const bisaDiisi = [...document.querySelectorAll('#mptb-isi tr:not([hidden]) .mptb-in:not(:disabled)')];
                const idx = bisaDiisi.indexOf(e.target);
                (bisaDiisi[idx + 1] || e.target).focus();
            });

            document.getElementById('mptb-cari').addEventListener('input', (e) => {
                kataKunci = e.target.value.trim().toLowerCase();
                perbarui();
            });

            document.getElementById('mptb-filter').addEventListener('click', (e) => {
                const tombol = e.target.closest('.chip');
                if (!tombol) return;
                filterAktif = tombol.dataset.f;
                document.querySelectorAll('#mptb-filter .chip').forEach((b) => b.setAttribute('aria-pressed', b === tombol ? 'true' : 'false'));
                perbarui();
            });

            perbarui();
        })();

        // Kalender pilih tanggal: hanya tanggal selama MPTB berjalan yang bisa dipilih; klik tanggal langsung memuat sesi yang sedang aktif
        (() => {
            const pop = document.getElementById('kal-pop'), tombol = document.querySelector('.pilih-tanggal > .pl-date'), form = document.getElementById('mptb-pilih');
            const grid = pop.querySelector('[data-grid]'), judulBulan = pop.querySelector('[data-bulan]');
            const baca = (s) => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
            const iso = (d) => [d.getFullYear(), String(d.getMonth() + 1).padStart(2, '0'), String(d.getDate()).padStart(2, '0')].join('-');
            const tersedia = JSON.parse(pop.dataset.tersedia), bolehPilih = new Set(tersedia);
            const bulanPertama = baca(tersedia[0]).setDate(1), bulanTerakhir = baca(tersedia.at(-1)).setDate(1);
            const terpilih = pop.dataset.tanggal, hariIni = pop.dataset.hariIni;
            let lihat = baca(terpilih);
            lihat.setDate(1);

            const gambar = () => {
                judulBulan.textContent = lihat.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                pop.querySelector('[data-geser="-1"]').disabled = +lihat <= bulanPertama;
                pop.querySelector('[data-geser="1"]').disabled = +lihat >= bulanTerakhir;
                const isi = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'].map((h) => Object.assign(document.createElement('span'), { textContent: h }));
                for (let i = (lihat.getDay() + 6) % 7; i > 0; i--) { isi.push(document.createElement('span')); }
                const jumlahHari = new Date(lihat.getFullYear(), lihat.getMonth() + 1, 0).getDate();
                for (let t = 1; t <= jumlahHari; t++) {
                    const d = new Date(lihat.getFullYear(), lihat.getMonth(), t), b = document.createElement('button');
                    b.type = 'button';
                    b.dataset.tanggal = iso(d);
                    b.disabled = !bolehPilih.has(iso(d));
                    b.classList.toggle('hari-ini', iso(d) === hariIni);
                    b.classList.toggle('pilih', iso(d) === terpilih);
                    b.setAttribute('aria-pressed', String(iso(d) === terpilih));
                    b.setAttribute('aria-label', d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
                    b.append(Object.assign(document.createElement('span'), { textContent: t }));
                    isi.push(b);
                }
                grid.replaceChildren(...isi);
            };

            grid.addEventListener('click', (e) => {
                const b = e.target.closest('button[data-tanggal]');
                if (!b || b.dataset.tanggal === terpilih) { return; }
                form.elements.tanggal.value = b.dataset.tanggal;
                form.requestSubmit(form.querySelector('[name="sesi"][aria-pressed="true"]'));
            });
            pop.querySelectorAll('[data-geser]').forEach((b) => b.addEventListener('click', () => {
                lihat = new Date(lihat.getFullYear(), lihat.getMonth() + Number(b.dataset.geser), 1);
                gambar();
            }));

            const atur = (buka) => {
                pop.classList.toggle('on', buka);
                tombol.setAttribute('aria-expanded', String(buka));
                if (buka) { gambar(); }
            };
            tombol.addEventListener('click', () => atur(!pop.classList.contains('on')));
            document.addEventListener('click', (e) => { if (!e.composedPath().includes(tombol.parentElement)) { atur(false); } });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && pop.classList.contains('on')) { atur(false); tombol.focus(); } });
            gambar();
        })();
    </script>
</body>
</html>
