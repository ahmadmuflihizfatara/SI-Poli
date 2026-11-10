{{-- laporan-kesehatan/keluhan-baru.blade.php — formulir "Tambah keluhan" design system Pagi (warna palet aplikasi): isian bergaya popup Catat Kontrol,
     form selebar layar + panel ringkasan sticky di kanan (taruna, tanda vital di luar normal, navigasi bagian).
     $klb = dipakai juga untuk keluhan Kejadian Luar Biasa: tanpa tingkat keparahan, simpan & kembali ke halaman KLB. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Tambahkan Keluhan Baru'])
    <style>
        html { scroll-behavior: smooth; }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }
        /* form + ringkasan; ringkasan disembunyikan di layar sempit (form memenuhi lebar) */
        .kb-tata { display: grid; grid-template-columns: minmax(0, 1fr) 19rem; gap: 1.5rem; align-items: start; }
        @media (max-width: 960px) { .kb-tata { grid-template-columns: minmax(0, 1fr); } .kb-ringkas { display: none; } }
        /* bagian = panel hijau muda seperti popup Catat Kontrol; mengambang saat hover */
        .kb-form { --tinggi-isian: clamp(40px, 2.75rem, 46px); --tinggi-label: calc(1.4 * max(14px, .875rem) + .5rem); }
        .kb-form > .pl-panel { gap: 1.25rem; padding: 1.5rem; border-radius: var(--radius-image); scroll-margin-top: 1.5rem; transition: transform .2s ease, box-shadow .2s ease; }
        .kb-form > .pl-panel:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        @media (prefers-reduced-motion: reduce) { .kb-form > .pl-panel { transition: none; } .kb-form > .pl-panel:hover { transform: none; } }
        /* kepala bagian: nomor urut bulat + judul + penjelasan singkat */
        .kb-kepala { display: flex; align-items: flex-start; gap: 1rem; }
        .kb-no { display: grid; place-items: center; width: 2.25rem; height: 2.25rem; flex: none; border-radius: var(--radius-full); background: var(--strong); color: var(--on-primary); font: 600 max(14px, .875rem)/1 var(--font-sans); }
        .kb-form .pl-panel__title { font: 600 1.375rem/1.25 var(--font-sans); letter-spacing: -.01rem; }
        /* isian berbentuk pil seperti popup Catat Kontrol; huruf isian 16px supaya terbaca dan Safari iOS tidak memperbesar halaman */
        .kb-form .pl-field-label, .kb-form .pl-field > legend { font: 600 max(14px, .875rem)/1.4 var(--font-sans); }
        .kb-form .pl-input, .kb-form .pl-date__teks, .kb-form .pl-date__jam { font: 500 16px/1.4 var(--font-sans); }
        .kb-form .pl-input { min-height: var(--tinggi-isian); border-radius: var(--radius-button); }
        .kb-form input.pl-input { height: var(--tinggi-isian); padding: 0 1rem; }
        .kb-form .pl-input::placeholder { font-weight: 400; }
        .kb-form .pl-input:user-invalid { border-color: var(--danger); }
        .kb-form textarea.pl-input { min-height: 4rem; max-height: 16rem; padding: .625rem 1rem; border-radius: 1.25rem; field-sizing: content; resize: none; }
        .kb-form .pl-combo input.pl-input { padding: 0 2.5rem; }
        .kb-form .pl-combo__ikon { left: .875rem; }
        .kb-form .pl-combo__chev { right: .875rem; }
        .kb-form .pl-combo__chev, .kb-form .pl-combo__ikon { top: calc(var(--tinggi-isian) / 2); width: 1rem; height: 1rem; }
        .kb-form .pl-date--isian { min-height: var(--tinggi-isian); border-radius: var(--radius-button); }
        .kb-form .pl-option { min-height: 2.5rem; padding: 0 1rem; transition: background-color .15s, box-shadow .15s; }
        .kb-form .pl-option:hover:not(:has(input:checked)):not(:has(input:disabled)) { background: var(--cloud); box-shadow: inset 0 0 0 1px var(--strong); }
        .kb-form .pl-option:not(.kb-parah):has(input:checked):hover { background: var(--strong-hover); }
        .kb-form .pl-btn-ghost:hover { background: var(--cloud); box-shadow: inset 0 0 0 1px var(--strong); opacity: 1; }
        /* tanggal + jam laporan sebaris, bertumpuk di layar sempit */
        .kb-waktu { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); gap: .75rem; }
        @media (max-width: 640px) { .kb-waktu { grid-template-columns: 1fr; } }
        /* skala nyeri NRS 0–10 (partial input-vital): tombol angka rapat */
        .kb-form .pl-nrs .pl-option { justify-content: center; padding: 0; border-radius: .75rem; font-variant-numeric: tabular-nums; }
        /* tingkat keparahan: ikon + warna semantik (hijau/kuning/merah) saat dipilih */
        .kb-parah svg { width: 1.125rem; height: 1.125rem; flex: none; }
        .kb-parah--ringan svg { color: #146b5f; }
        .kb-parah--sedang svg { color: #a86f0b; }
        .kb-parah--berat svg { color: #b42318; }
        html.dark .kb-parah--ringan svg { color: #85c9b8; }
        html.dark .kb-parah--sedang svg { color: #f0c46a; }
        html.dark .kb-parah--berat svg { color: #f4a196; }
        .kb-form .kb-parah--ringan:has(input:checked) { background: #146b5f; color: #fff; }
        .kb-form .kb-parah--sedang:has(input:checked) { background: #e3a531; color: #232620; }
        .kb-form .kb-parah--berat:has(input:checked) { background: #b42318; color: #fff; }
        .kb-form .kb-parah:has(input:checked) svg { color: currentColor; }
        .kb-form .kb-parah:has(input:checked):hover { filter: brightness(.93); }
        /* keparahan selebar isinya, tanggal kontrol mengisi sisa baris */
        .kb-lanjut { grid-template-columns: auto minmax(0, 1fr); }
        @media (max-width: 640px) { .kb-lanjut { grid-template-columns: minmax(0, 1fr); } }
        /* baris terapi/obat: bernomor, kolom terapi : aturan pakai = 3 : 1 (sama dengan popup Catat Kontrol) */
        .kb-form #daftar-obat { counter-reset: obat; }
        .kb-form [data-baris-obat] { align-items: flex-start; }
        .kb-form [data-baris-obat]::before { counter-increment: obat; content: counter(obat); display: grid; place-items: center; width: 1.75rem; height: 1.75rem; flex: none;
            margin-top: calc(var(--tinggi-label) + (var(--tinggi-isian) - 1.75rem) / 2); border-radius: var(--radius-full); background: var(--strong); color: var(--on-primary); font: 600 max(12px, .75rem)/1 var(--font-sans); }
        .kb-form [data-kolom-obat] { grid-template-columns: minmax(0, 3fr) minmax(0, 1fr); }
        .kb-form [data-hapus-obat] { width: 2rem; height: 2rem; margin-top: calc(var(--tinggi-label) + (var(--tinggi-isian) - 2rem) / 2); }
        .kb-form [data-hapus-obat] svg { width: 1rem; height: 1rem; }
        .kb-form .kb-grup-obat { font: 600 1.125rem/1.3 var(--font-sans); color: var(--heading); }
        @media (max-width: 640px) {
            .kb-form > .pl-panel { padding: 1rem; }
            .kb-form [data-kolom-obat] { grid-template-columns: minmax(0, 1fr); }
        }
        /* panel ringkasan: tetap terlihat saat menggulir */
        .kb-ringkas { position: sticky; top: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem; padding: 1.25rem; border-radius: var(--radius-image); background: var(--cloud); transition: transform .2s ease, box-shadow .2s ease; }
        .kb-ringkas:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        @media (prefers-reduced-motion: reduce) { .kb-ringkas { transition: none; } .kb-ringkas:hover { transform: none; } }
        .kb-ringkas__judul { margin: 0 0 .5rem; font: 600 max(13px, .8125rem)/1.3 var(--font-sans); letter-spacing: .04em; text-transform: uppercase; color: var(--text-muted); }
        .kb-ringkas__taruna { display: flex; align-items: center; gap: .75rem; padding: .875rem 1rem; border-radius: var(--radius-image); background: var(--strong); color: var(--on-primary); }
        .kb-ringkas__ikon { display: grid; place-items: center; width: 2.5rem; height: 2.5rem; flex: none; border-radius: var(--radius-full); background: rgb(255 255 255 / .16); font: 600 max(13px, .8125rem)/1 var(--font-sans); }
        .kb-ringkas__ikon svg { width: 1.125rem; height: 1.125rem; }
        .kb-ringkas__taruna small { display: block; font: 500 max(12px, .75rem)/1.3 var(--font-sans); opacity: .8; }
        .kb-ringkas__taruna b { display: block; font: 600 max(15px, 1rem)/1.3 var(--font-sans); overflow-wrap: anywhere; }
        .kb-ringkas__vital { display: flex; flex-direction: column; gap: .5rem; margin: 0; padding: 0; list-style: none; }
        .kb-ringkas__vital li { display: flex; align-items: center; justify-content: space-between; gap: .5rem; font: 500 max(14px, .875rem)/1.4 var(--font-sans); font-variant-numeric: tabular-nums; }
        .kb-ringkas__kosong { margin: 0; font: 400 max(14px, .875rem)/1.4 var(--font-sans); color: var(--text-muted); }
        .kb-nav { display: flex; flex-direction: column; gap: .25rem; margin: 0; padding: 0; list-style: none; }
        body .kb-nav a { display: flex; align-items: center; gap: .625rem; padding: .5rem .625rem; border-radius: .75rem; color: var(--text); text-decoration: none; font: 500 max(14px, .875rem)/1.3 var(--font-sans); transition: background-color .15s; }
        body .kb-nav a:hover { background: var(--surface-card); color: var(--heading); }
        .kb-nav__no { display: grid; place-items: center; width: 1.75rem; height: 1.75rem; flex: none; border-radius: var(--radius-full); background: var(--surface-card); color: var(--link); box-shadow: inset 0 0 0 1px var(--field-line); font: 600 max(12px, .75rem)/1 var(--font-sans); }
        .kb-nav__cek { width: 1.125rem; height: 1.125rem; margin-left: auto; flex: none; color: var(--link); visibility: hidden; }
        .kb-nav a.is-lengkap .kb-nav__no { background: var(--strong); color: var(--on-primary); box-shadow: none; }
        .kb-nav a.is-lengkap .kb-nav__cek { visibility: visible; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $klb ??= null;
        $kembali = $klb ? route('klb.show', $klb) : route('laporan-kesehatan.index');
        $wajib = '<span class="pl-req" aria-hidden="true">*</span>';
        $ikonTanda = 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z';
        // [tingkat, kelas warna, ikon, hari kontrol yang diisi otomatis (harus ada di Keluhan::PINTASAN_KONTROL)]
        $keparahan = [
            ['Ringan', 'kb-parah--ringan', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 7],
            ['Sedang', 'kb-parah--sedang', 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z', 3],
            ['Berat', 'kb-parah--berat', $ikonTanda, 1],
        ];
        $ringan = old('status') === 'Ringan';
        // [nomor, judul, penjelasan]
        $bagian = [
            'umum' => [1, 'Informasi Umum', 'Taruna yang melapor, waktu laporan, dan pegawai pemeriksa.'],
            'kondisi' => [2, 'Kondisi Kesehatan', 'Tanda vital saat taruna diperiksa.'],
            'keluhan' => [3, 'Informasi Keluhan', 'Keluhan, terapi, dan hasil pemeriksaan.'],
            'lanjut' => [4, 'Tindak Lanjut', $klb ? 'Jadwal kontrol berikutnya.' : 'Tingkat keparahan dan jadwal kontrol berikutnya.'],
        ];
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ $kembali }}" class="pl-back" data-batal>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">Tambahkan Keluhan Baru</h1>
                <p class="pl-page-sub">{{ $klb ? "Tambahkan keluhan taruna pada {$klb->nama}." : 'Tambahkan informasi kontrol taruna yang melaporkan keluhan baru.' }} Kolom bertanda * wajib diisi.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent pl-alert--ikon" role="alert">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="kb-tata">
            <form method="POST" action="{{ $klb ? route('klb.keluhan.store', $klb) : route('laporan-kesehatan.store') }}" class="pl-form kb-form" id="form-keluhan">
                @csrf

                @foreach ($bagian as $kunci => [$nomor, $judul, $penjelasan])
                    <section class="pl-panel" id="bagian-{{ $kunci }}" aria-labelledby="judul-{{ $kunci }}" data-bagian="{{ $kunci }}">
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
                                            <input class="pl-date__jam" type="time" id="jam_laporan" name="jam_laporan" value="{{ old('jam_laporan', now()->format('H:i')) }}" required data-kolab-awal>
                                            <span class="pl-date__pil">WIB</span>
                                        </label>
                                    </div>
                                    <input type="hidden" name="waktu_laporan" value="{{ old('waktu_laporan', now()->format('Y-m-d\TH:i')) }}">
                                    <div class="pl-field-hint" id="waktu-laporan-hint">Terisi otomatis dengan waktu sekarang; ubah bila keluhan dilaporkan lebih awal.</div>
                                </div>
                                {{-- dipilih petugas, bukan otomatis dari akun yang menambahkan (akun pencatat tetap disimpan di created_by) --}}
                                @include('partials.combobox', [
                                    'id' => 'cari-pemeriksa', 'name' => 'pemeriksa_id', 'label' => 'Pegawai pemeriksa', 'wajib' => $wajib,
                                    'placeholder' => 'Ketik nama dokter atau perawat', 'terpilih' => old('pemeriksa_id'),
                                    'opsi' => $pegawai->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama, 'sub' => $p->jabatan, 'grup' => $p->jabatan]),
                                ])
                                @break

                            @case('kondisi')
                                @include('laporan-kesehatan.partials.input-vital', ['wajib' => $wajib])
                                @break

                            @case('keluhan')
                                <div class="pl-fields">
                                    <div class="pl-field pl-field--wide">
                                        <label class="pl-field-label" for="keluhan">Keluhan{!! $wajib !!}</label>
                                        <textarea class="pl-input" id="keluhan" name="keluhan" required>{{ old('keluhan') }}</textarea>
                                    </div>
                                    @include('laporan-kesehatan.partials.input-obat', ['obat' => old('obat'), 'label' => 'Terapi dan obat', 'kelasLabel' => 'kb-grup-obat mb-3'])
                                    <div class="pl-field pl-field--wide">
                                        <label class="pl-field-label" for="hasil_pemeriksaan">Hasil pemeriksaan</label>
                                        <textarea class="pl-input" id="hasil_pemeriksaan" name="hasil_pemeriksaan">{{ old('hasil_pemeriksaan') }}</textarea>
                                    </div>
                                    <div class="pl-field pl-field--wide">
                                        <label class="pl-field-label" for="keterangan">Keterangan <span class="text-muted font-normal">(opsional)</span></label>
                                        <textarea class="pl-input" id="keterangan" name="keterangan">{{ old('keterangan') }}</textarea>
                                    </div>
                                </div>
                                @break

                            @case('lanjut')
                                <div @class(['pl-fields', 'kb-lanjut' => ! $klb])>
                                    @unless ($klb)
                                    <fieldset class="pl-field">
                                        <legend class="mb-2">Tingkat keparahan{!! $wajib !!}</legend>
                                        <div class="pl-options">
                                            @foreach ($keparahan as [$s, $kelas, $ikon, $hari])
                                                <label class="pl-option kb-parah {{ $kelas }}">
                                                    <input type="radio" name="status" value="{{ $s }}" data-hari-kontrol="{{ $hari }}" required @checked(old('status') === $s)>
                                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $ikon }}"/></svg>{{ $s }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                    @endunless
                                    <div class="pl-field">
                                        <label class="pl-field-label" for="tanggal_kontrol_selanjutnya">Tanggal kontrol selanjutnya<span data-kontrol-wajib @if ($ringan) hidden @endif>{!! $wajib !!}</span>
                                            <span class="text-muted font-normal" data-kontrol-opsional @if (! $ringan) hidden @endif>(opsional)</span></label>
                                        @include('partials.pilih-tanggal', [
                                            'id' => 'tanggal_kontrol_selanjutnya', 'name' => 'tanggal_kontrol_selanjutnya', 'nilai' => old('tanggal_kontrol_selanjutnya'), 'gaya' => 'tombol',
                                            'min' => today()->toDateString(), 'wajib' => ! $ringan, 'pintasan' => \App\Models\Keluhan::PINTASAN_KONTROL,
                                        ])
                                        <div class="pl-field-hint">{{ $klb ? 'Pilih tanggal taruna kontrol berikutnya.' : 'Terisi otomatis saat tingkat keparahan dipilih: Ringan 1 minggu, Sedang 3 hari, Berat besok. Keluhan ringan boleh tanpa jadwal kontrol.' }}</div>
                                    </div>
                                </div>
                                @break
                        @endswitch
                    </section>
                @endforeach

                <div class="pl-form-actions">
                    <a href="{{ $kembali }}" class="pl-btn pl-btn-ghost" data-batal>Batal</a>
                    <button type="submit" class="pl-btn pl-btn-primary">Tambahkan keluhan</button>
                </div>
            </form>

            <aside class="kb-ringkas" aria-label="Ringkasan isian">
                <div class="kb-ringkas__taruna">
                    <span class="kb-ringkas__ikon" aria-hidden="true" data-ringkas-inisial><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></span>
                    <div class="min-w-0"><small>Taruna</small><b data-ringkas-nama>Belum dipilih</b><small data-ringkas-sub>Pilih di bagian Informasi Umum</small></div>
                </div>
                <section aria-labelledby="ringkas-vital">
                    <h2 class="kb-ringkas__judul" id="ringkas-vital">Tanda vital di luar normal</h2>
                    <ul class="kb-ringkas__vital" data-ringkas-vital></ul>
                    <p class="kb-ringkas__kosong" data-ringkas-vital-kosong aria-live="polite">Tanda vital belum diisi.</p>
                </section>
                <nav aria-labelledby="ringkas-bagian">
                    <h2 class="kb-ringkas__judul" id="ringkas-bagian">Bagian formulir</h2>
                    <ol class="kb-nav">
                        @foreach ($bagian as $kunci => [$nomor, $judul])
                            <li>
                                <a href="#bagian-{{ $kunci }}" data-nav="{{ $kunci }}">
                                    <span class="kb-nav__no" aria-hidden="true">{{ $nomor }}</span>{{ $judul }}
                                    <svg class="kb-nav__cek" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    <span class="pl-sr" data-nav-status>, belum lengkap</span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </nav>
            </aside>
        </div>
    </main>

    @include('partials.konfirmasi', [
        'id' => 'konfirmasi-batal', 'nada' => 'secondary', 'ikon' => 'hapus', 'tautan' => $kembali,
        'judul' => 'Buang isian keluhan ini?', 'pesan' => 'Isian yang sudah diketik akan hilang dan tidak tersimpan.',
        'tombol' => 'Ya, buang isian', 'tombolTutup' => 'Lanjut mengisi',
    ])

    @include('partials.kolaborasi', [
        'form' => 'form-keluhan', 'kanal' => 'draf.'.($klb ? "klb-{$klb->id}" : 'keluhan-baru').".$draf", 'ubah' => true,
        'sekali' => true, 'selesai' => $kembali, 'draf' => $draf,
    ])

    <script>
        (() => {
            const form = document.getElementById('form-keluhan'), idTaruna = document.getElementById('taruna_id');
            const isian = () => new URLSearchParams(new FormData(form)).toString();
            let isianAwal = null;

            form.addEventListener('submit', () => {
                // [name=…]: id tombol kalender sama dengan name inputnya, jadi form.elements.tanggal_laporan mengembalikan dua elemen
                const tanggalLaporan = form.querySelector('input[name="tanggal_laporan"]').value;
                form.elements.waktu_laporan.value = `${tanggalLaporan}T${form.elements.jam_laporan.value}`;
            });

            const opsiTaruna = () => document.querySelector(`#cari-taruna-daftar [data-id="${idTaruna.value}"]`);

            const ringkasNama = document.querySelector('[data-ringkas-nama]'), ringkasSub = document.querySelector('[data-ringkas-sub]');
            const ringkasInisial = document.querySelector('[data-ringkas-inisial]'), ikonTaruna = ringkasInisial.innerHTML;
            const ringkasVital = document.querySelector('[data-ringkas-vital]'), ringkasKosong = document.querySelector('[data-ringkas-vital-kosong]');

            const perbarui = () => {
                const o = opsiTaruna(), info = JSON.parse(o?.dataset.info || '{}');
                ringkasNama.textContent = o ? o.querySelector('b').textContent : 'Belum dipilih';
                ringkasSub.textContent = o ? `NPM ${info.NPM}` : 'Pilih di bagian Informasi Umum';
                if (o) { ringkasInisial.textContent = o.firstElementChild.textContent; } else { ringkasInisial.innerHTML = ikonTaruna; }

                let adaIsian = false;
                // penanda [data-tanda] sudah ditulis skrip partial input-vital (pendengar di wadah berjalan lebih dulu)
                ringkasVital.replaceChildren(...[...form.querySelectorAll('[data-vital]')].flatMap((wadah) => {
                    const tanda = wadah.querySelector('[data-tanda]'), isianVital = [...wadah.querySelectorAll('input')];
                    adaIsian ||= isianVital.some((i) => i.value !== '');
                    if (tanda.hidden) { return []; }
                    const nilai = isianVital.map((i) => i.value.replace('.', ',') || '–').join('/');
                    const li = document.createElement('li');
                    li.append(`${wadah.dataset.nama} ${nilai} ${wadah.dataset.satuan}`, tanda.cloneNode(true));
                    li.lastChild.removeAttribute('aria-live');
                    return [li];
                }));
                ringkasKosong.hidden = ringkasVital.children.length > 0;
                ringkasKosong.textContent = adaIsian ? 'Semua tanda vital yang diisi dalam batas normal.' : 'Tanda vital belum diisi.';

                // navigasi: bagian bertanda centang bila semua isian wajibnya valid (validity, bukan checkValidity, agar tidak memicu tanda galat)
                form.querySelectorAll('[data-bagian]').forEach((bagian) => {
                    const lengkap = [...bagian.querySelectorAll('[required]')].every((el) => el.validity.valid);
                    const nav = document.querySelector(`[data-nav="${bagian.dataset.bagian}"]`);
                    nav.classList.toggle('is-lengkap', lengkap);
                    nav.querySelector('[data-nav-status]').textContent = lengkap ? ', lengkap' : ', belum lengkap';
                });
            };
            form.addEventListener('input', perbarui);
            form.addEventListener('change', perbarui);
            // change dari combobox tidak menggelembung ke form
            [idTaruna, document.getElementById('pemeriksa_id')].forEach((el) => el.addEventListener('change', perbarui));

            // Tingkat keparahan: Ringan = kontrol opsional; tanggal kontrol diisi otomatis lewat pintasan kalender,
            // kecuali petugas sudah memilih tanggal sendiri. Pilihan dari akun lain (pengisian bersama, isTrusted false) tidak mengisi otomatis:
            // tanggalnya ikut terkirim dari akun itu.
            const wadahKontrol = document.getElementById('tanggal_kontrol_selanjutnya').closest('[data-pilih-tanggal]');
            const nilaiKontrol = wadahKontrol.querySelector('.pl-date__nilai');
            let kontrolOtomatis = null;
            form.addEventListener('change', (e) => {
                if (e.target.name !== 'status') { return; }
                const ringan = e.target.value === 'Ringan';
                nilaiKontrol.required = !ringan;
                document.querySelector('[data-kontrol-wajib]').hidden = ringan;
                document.querySelector('[data-kontrol-opsional]').hidden = !ringan;
                if (e.isTrusted && (!nilaiKontrol.value || nilaiKontrol.value === kontrolOtomatis)) {
                    wadahKontrol.querySelector(`[data-pintas="${e.target.dataset.hariKontrol}"]`).click();
                    kontrolOtomatis = nilaiKontrol.value;
                    e.target.focus();
                }
                perbarui();
            });

            // Batal/Kembali: minta konfirmasi bila form sudah diisi. Setelah gagal validasi isian lama selalu dianggap sudah diisi.
            const konfirmasi = document.getElementById('konfirmasi-batal');
            document.querySelectorAll('[data-batal]').forEach((tautan) => tautan.addEventListener('click', (e) => {
                if (isian() === isianAwal) { return; }
                e.preventDefault();
                konfirmasi.showModal();
            }));

            // setelah skrip combobox & kalender (DOMContentLoaded) selesai menyiapkan isiannya
            document.addEventListener('DOMContentLoaded', () => {
                isianAwal = @json($errors->any()) ? '' : isian();
                perbarui();
            });
        })();
    </script>

</body>
</html>
