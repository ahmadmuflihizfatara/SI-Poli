{{-- laporan-kesehatan/keluhan-psikologi-baru.blade.php — formulir "Tambah keluhan" psikolog, susunan & gaya sama dengan keluhan-baru.blade.php (perawat):
     bagian bernomor bergaya popup Catat Kontrol, form selebar layar + panel ringkasan sticky di kanan (taruna, navigasi bagian) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Tambahkan Keluhan Baru'])
    <style>
        /* gaya disalin dari keluhan-baru.blade.php supaya kedua formulir tambah keluhan tampil sama */
        html { scroll-behavior: smooth; }
        /* Kembali: pil hijau muda + panah bergeser ke kiri saat hover (sama dengan halaman detail keluhan) */
        .pl-back { margin-left: -.75rem; padding: .375rem .875rem .375rem .625rem; border-radius: var(--radius-button); font-size: 14px; transition: background-color .15s, color .15s, box-shadow .15s; }
        .pl-back svg { transition: transform .2s ease; }
        .pl-back:hover { background: var(--cloud); color: var(--heading); box-shadow: inset 0 0 0 1px var(--field-line); }
        .pl-back:hover svg { transform: translateX(-4px); }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } .pl-back:hover svg { transform: none; } }
        /* form + ringkasan; ringkasan disembunyikan di layar sempit (form memenuhi lebar) */
        .kb-tata { display: grid; grid-template-columns: minmax(0, 1fr) 19rem; gap: 1.5rem; align-items: start; }
        @media (max-width: 960px) { .kb-tata { grid-template-columns: minmax(0, 1fr); } .kb-ringkas { display: none; } }
        /* bagian = panel hijau muda seperti popup Catat Kontrol; mengambang saat hover */
        .kb-form { --tinggi-isian: clamp(40px, 2.75rem, 46px); }
        .kb-form > .pl-panel { gap: 1.25rem; padding: 1.5rem; border-radius: var(--radius-image); scroll-margin-top: 1.5rem; transition: transform .2s ease, box-shadow .2s ease; }
        .kb-form > .pl-panel:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        @media (prefers-reduced-motion: reduce) { .kb-form > .pl-panel { transition: none; } .kb-form > .pl-panel:hover { transform: none; } }
        /* kepala bagian: nomor urut bulat + judul + penjelasan singkat */
        .kb-kepala { display: flex; align-items: flex-start; gap: 1rem; }
        .kb-no { display: grid; place-items: center; width: 2.25rem; height: 2.25rem; flex: none; border-radius: var(--radius-full); background: var(--strong); color: var(--on-primary); font: 600 max(14px, .875rem)/1 var(--font-sans); }
        .kb-form .pl-panel__title { font: 600 1.375rem/1.25 var(--font-sans); letter-spacing: -.01rem; }
        /* isian berbentuk pil seperti popup Catat Kontrol; huruf isian 16px supaya terbaca dan Safari iOS tidak memperbesar halaman */
        .kb-form .pl-field-label, .kb-form .pl-field > legend { font: 600 max(14px, .875rem)/1.4 var(--font-sans); }
        .kb-form .pl-input, .kb-form .pl-date__teks { font: 500 16px/1.4 var(--font-sans); }
        .kb-form .pl-input { min-height: var(--tinggi-isian); border-radius: var(--radius-button); }
        .kb-form input.pl-input { height: var(--tinggi-isian); padding: 0 1rem; }
        .kb-form .pl-input::placeholder { font-weight: 400; }
        .kb-form .pl-input:user-invalid { border-color: var(--danger); }
        .kb-form textarea.pl-input { min-height: 4rem; max-height: 16rem; padding: .625rem 1rem; border-radius: 1.25rem; field-sizing: content; resize: none; }
        .kb-form .pl-combo input.pl-input { padding: 0 2.5rem; }
        .kb-form .pl-combo__ikon { left: .875rem; }
        .kb-form .pl-combo__chev { right: .875rem; }
        .kb-form .pl-combo__chev, .kb-form .pl-combo__ikon { top: calc(var(--tinggi-isian) / 2); width: 1rem; height: 1rem; }
        .kb-form .pl-option { min-height: 2.5rem; padding: 0 1rem; transition: background-color .15s, box-shadow .15s; }
        .kb-form .pl-option:hover:not(:has(input:checked)):not(:has(input:disabled)) { background: var(--cloud); box-shadow: inset 0 0 0 1px var(--strong); }
        .kb-form .pl-option:has(input:checked):hover { background: var(--strong-hover); }
        .kb-form .pl-btn-ghost:hover { background: var(--cloud); box-shadow: inset 0 0 0 1px var(--strong); opacity: 1; }
        /* pilihan lanjut selebar isinya, tanggal konseling mengisi sisa baris */
        .kb-lanjut { grid-template-columns: auto minmax(0, 1fr); }
        @media (max-width: 640px) { .kb-lanjut { grid-template-columns: minmax(0, 1fr); } .kb-form > .pl-panel { padding: 1rem; } }
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
        $wajib = '<span class="pl-req" aria-hidden="true">*</span>';
        // [nomor, judul, penjelasan]
        $bagian = [
            'umum' => [1, 'Informasi Umum', 'Taruna yang melapor keluhan psikologi dan psikolog pemeriksa.'],
            'keluhan' => [2, 'Informasi Keluhan', 'Keluhan yang dinyatakan dan terapi psikologi.'],
            'lanjut' => [3, 'Tindak Lanjut', 'Kelanjutan konseling dan jadwal berikutnya.'],
        ];
        // label => selisih hari; "1 minggu" diisi otomatis saat "Masih lanjut" dipilih
        $pintasanKonseling = ['Besok' => 1, '3 hari lagi' => 3, '1 minggu' => 7, '2 minggu' => 14];
    @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <a href="{{ route('laporan-kesehatan.index') }}" class="pl-back" data-batal>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">Tambahkan Keluhan Baru</h1>
                <p class="pl-page-sub">Tambahkan informasi konseling taruna yang melaporkan keluhan baru. Kolom bertanda * wajib diisi.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="pl-alert pl-alert-accent pl-alert--ikon" role="alert">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="kb-tata">
            <form method="POST" action="{{ route('laporan-kesehatan.psikologi.store') }}" class="pl-form kb-form" id="form-keluhan">
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
                                {{-- dipilih petugas, bukan otomatis dari akun yang menambahkan (akun pencatat tetap disimpan di created_by) --}}
                                @include('partials.combobox', [
                                    'id' => 'cari-pemeriksa', 'name' => 'pemeriksa_id', 'label' => 'Psikolog pemeriksa', 'wajib' => $wajib,
                                    'placeholder' => $pegawai->isEmpty() ? 'Belum ada data psikolog' : 'Ketik nama psikolog', 'terpilih' => old('pemeriksa_id'),
                                    'opsi' => $pegawai->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama]),
                                ])
                                @break

                            @case('keluhan')
                                <div class="pl-fields">
                                    <div class="pl-field pl-field--wide">
                                        <label class="pl-field-label" for="keluhan">Keluhan{!! $wajib !!}</label>
                                        <textarea class="pl-input" id="keluhan" name="keluhan" required>{{ old('keluhan') }}</textarea>
                                    </div>
                                    <div class="pl-field pl-field--wide">
                                        <label class="pl-field-label" for="terapi">Terapi psikologi{!! $wajib !!}</label>
                                        <textarea class="pl-input" id="terapi" name="terapi" required aria-describedby="terapi-hint">{{ old('terapi') }}</textarea>
                                        <div class="pl-field-hint" id="terapi-hint">Satu terapi per baris.</div>
                                    </div>
                                </div>
                                @break

                            @case('lanjut')
                                <div class="pl-fields kb-lanjut">
                                    <fieldset class="pl-field">
                                        <legend class="mb-2">Melanjutkan konseling?{!! $wajib !!}</legend>
                                        <div class="pl-options">
                                            @foreach (['1' => 'Masih lanjut', '0' => 'Tidak'] as $nilai => $label)
                                                <label class="pl-option"><input type="radio" name="lanjut_konseling" value="{{ $nilai }}" required @checked(old('lanjut_konseling') === (string) $nilai)>{{ $label }}</label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                    {{-- tampil & wajib hanya saat konseling dilanjutkan (partial tanggal-konseling) --}}
                                    <div class="pl-field" id="kolom-tanggal">
                                        <label class="pl-field-label" for="tanggal_konseling_selanjutnya">Tanggal konseling selanjutnya{!! $wajib !!}</label>
                                        @include('partials.pilih-tanggal', [
                                            'id' => 'tanggal_konseling_selanjutnya', 'name' => 'tanggal_konseling_selanjutnya', 'nilai' => old('tanggal_konseling_selanjutnya'), 'gaya' => 'tombol',
                                            'min' => today()->toDateString(), 'wajib' => old('lanjut_konseling') === '1', 'pintasan' => $pintasanKonseling,
                                        ])
                                        <div class="pl-field-hint">Terisi otomatis 1 minggu lagi saat "Masih lanjut" dipilih; ubah bila perlu.</div>
                                    </div>
                                </div>
                                @break
                        @endswitch
                    </section>
                @endforeach

                <div class="pl-form-actions">
                    <a href="{{ route('laporan-kesehatan.index') }}" class="pl-btn pl-btn-ghost" data-batal>Batal</a>
                    <button type="submit" class="pl-btn pl-btn-primary">Tambahkan keluhan</button>
                </div>
            </form>

            <aside class="kb-ringkas" aria-label="Ringkasan isian">
                <div class="kb-ringkas__taruna">
                    <span class="kb-ringkas__ikon" aria-hidden="true" data-ringkas-inisial><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></span>
                    <div class="min-w-0"><small>Taruna</small><b data-ringkas-nama>Belum dipilih</b><small data-ringkas-sub>Pilih di bagian Informasi Umum</small></div>
                </div>
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
        'id' => 'konfirmasi-batal', 'nada' => 'secondary', 'ikon' => 'hapus', 'tautan' => route('laporan-kesehatan.index'),
        'judul' => 'Buang isian keluhan ini?', 'pesan' => 'Isian yang sudah diketik akan hilang dan tidak tersimpan.',
        'tombol' => 'Ya, buang isian', 'tombolTutup' => 'Lanjut mengisi',
    ])

    @include('laporan-kesehatan.partials.tanggal-konseling')

    <script>
        (() => {
            const form = document.getElementById('form-keluhan'), idTaruna = document.getElementById('taruna_id');
            const isian = () => new URLSearchParams(new FormData(form)).toString();
            let isianAwal = null;

            const ringkasNama = document.querySelector('[data-ringkas-nama]'), ringkasSub = document.querySelector('[data-ringkas-sub]');
            const ringkasInisial = document.querySelector('[data-ringkas-inisial]'), ikonTaruna = ringkasInisial.innerHTML;

            const perbarui = () => {
                const o = document.querySelector(`#cari-taruna-daftar [data-id="${idTaruna.value}"]`), info = JSON.parse(o?.dataset.info || '{}');
                ringkasNama.textContent = o ? o.querySelector('b').textContent : 'Belum dipilih';
                ringkasSub.textContent = o ? `NPM ${info.NPM}` : 'Pilih di bagian Informasi Umum';
                if (o) { ringkasInisial.textContent = o.firstElementChild.textContent; } else { ringkasInisial.innerHTML = ikonTaruna; }

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

            // "Masih lanjut": tanggal konseling diisi otomatis 1 minggu lagi lewat pintasan kalender, kecuali petugas sudah memilih tanggal sendiri
            const wadahTanggal = document.getElementById('tanggal_konseling_selanjutnya').closest('[data-pilih-tanggal]');
            const nilaiTanggal = wadahTanggal.querySelector('.pl-date__nilai');
            form.addEventListener('change', (e) => {
                if (e.target.name !== 'lanjut_konseling' || e.target.value !== '1' || nilaiTanggal.value) { return; }
                wadahTanggal.querySelector('[data-pintas="7"]').click();
                e.target.focus();
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
