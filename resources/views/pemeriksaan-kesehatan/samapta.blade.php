{{-- pemeriksaan-kesehatan/samapta.blade.php — Halaman input Samapta (2x per semester) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Samapta'])
    <style>
        .smt-table { table-layout: fixed; min-width: 72.25rem; }
        .smt-table td { padding: .5rem .4rem; height: 3.25rem; }
        .smt-table thead { position: sticky; top: 0; z-index: 1; }
        /* Kepala tabel tegas seperti Laporan Kesehatan: hijau tua + teks putih tebal */
        .smt-table thead th { padding: .75rem .5rem; text-align: center; vertical-align: middle; background: var(--strong); color: var(--on-primary); font-weight: 600; font-size: max(14px, .875rem); border-bottom: 0; }
        .smt-table thead th small { display: block; font-weight: 400; font-size: max(12px, .75rem); opacity: .75; }
        .smt-table thead th[colspan] { border-bottom: 1px solid color-mix(in srgb, var(--on-primary) 30%, transparent); }
        .smt-table th.grp { border-left: 1px solid color-mix(in srgb, var(--on-primary) 30%, transparent); border-right: 1px solid color-mix(in srgb, var(--on-primary) 30%, transparent); }
        .smt-table td.c { text-align: center; }
        .smt-table td.nm { text-align: left; font-weight: 500; border-right: 1px solid var(--border); } /* sekat antara kolom nama dan kolom lain */
        .smt-table td.nm a { color: inherit; text-decoration: none; }
        .smt-table td.nm a:hover { text-decoration: underline; text-underline-offset: 3px; }
        .smt-table tbody tr { cursor: pointer; } /* klik baris = halaman detail taruna */
        /* Isian minimalis: latar lembut tanpa garis, garis hijau saat fokus; tanda vital lebih ringkas */
        .smt-in { box-sizing: border-box; display: block; width: 100%; height: 2.25rem; border: 1px solid transparent; border-radius: .625rem; background: var(--cloud); padding: 0 .625rem; font: inherit; color: var(--text); text-align: left; cursor: text; transition: background-color .15s, border-color .15s, box-shadow .15s; }
        select.smt-in { cursor: pointer; }
        .smt-in.vital { height: 2rem; max-width: 5.5rem; margin: 0 auto; text-align: center; font-variant-numeric: tabular-nums; }
        .smt-in::placeholder { color: var(--neutral-400); }
        .smt-in:hover { border-color: var(--field-line); }
        .smt-in:focus-visible { outline: 0; background: var(--surface-card); border-color: var(--strong); box-shadow: 0 0 0 3px color-mix(in srgb, var(--strong) 18%, transparent); }
        .smt-in:disabled { background: transparent; color: var(--text-muted); cursor: not-allowed; }
        .smt-in[aria-invalid="true"] { border-color: var(--danger); background: var(--danger-wash); }
        .smt-in.suhu-tinggi { background: var(--danger-wash); color: var(--danger); font-weight: 700; }
        .smt-penanda { width: 1.125rem; height: 1.125rem; flex: none; }
        #smt-kosong { display: none; }
        /* Informasi hari, tanggal, sesi (pengganti teks penjelasan di bawah judul) */
        .pk-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr)); gap: .75rem; }
        .pk-info > div { display: flex; align-items: center; gap: .75rem; padding: .875rem 1rem; border-radius: var(--radius-card); background: var(--cloud); }
        .pk-info__ikon { display: grid; place-items: center; width: 2.5rem; height: 2.5rem; flex: none; border-radius: 9999px; background: var(--surface-card); color: var(--link); }
        .pk-info__ikon svg { width: 1.25rem; height: 1.25rem; }
        .pk-info small { display: block; font: 400 max(12px, .75rem)/1.3 var(--font-sans); color: var(--text-muted); }
        .pk-info b { display: flex; align-items: center; gap: .5rem; font: 600 1rem/1.3 var(--font-sans); color: var(--heading); }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php $suhuBatas = 37.5; $selesai = $kegiatan->selesai(); @endphp

    <main class="pl-main">

        {{-- Header --}}
        <header class="pl-page-head">
            <div>
                <a href="{{ route('pemeriksaan-kesehatan.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">{{ $kegiatan->nama }}</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if ($selesai)
                    <span class="pl-badge pl-badge-primary">Selesai</span>
                @else
                    @can('edit-data')
                        <button type="button" class="pl-btn pl-btn-secondary" data-konfirmasi="konfirmasi-selesai">
                            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            Selesaikan pemeriksaan
                        </button>
                    @endcan
                    <button type="submit" form="form-smt" class="pl-btn pl-btn-primary">
                        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Simpan perubahan
                    </button>
                @endif
            </div>
        </header>

        <section class="pk-info" aria-label="Informasi pemeriksaan">
            <div>
                <span class="pk-info__ikon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                <span><small>Hari ini</small><b>{{ today()->locale('id')->translatedFormat('l') }}</b></span>
            </div>
            <div>
                <span class="pk-info__ikon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                <span><small>Tanggal</small><b>{{ today()->locale('id')->translatedFormat('d F Y') }}</b></span>
            </div>
            <div>
                <span class="pk-info__ikon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5"/></svg></span>
                <span><small>Periode Samapta</small><b>{{ $kegiatan->labelRentang() }}</b></span>
            </div>
            <div>
                <span class="pk-info__ikon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
                <span><small>Status</small><b>{{ $selesai ? 'Selesai' : 'Berjalan' }}</b></span>
            </div>
        </section>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
        @endif
        @if ($selesai)
            <div class="pl-alert pl-alert-info" role="note">Pemeriksaan ini sudah selesai. Hasilnya hanya bisa dilihat.</div>
        @endif

        <div class="pl-toolbar">
            <label class="pl-search">
                <span class="pl-sr">Cari nama taruna</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="smt-cari" type="search" class="pl-input" placeholder="Cari nama taruna">
            </label>
            {{-- Satu tingkat per tampilan; baris tingkat lain hanya disembunyikan, jadi isian yang belum disimpan tidak hilang saat pindah --}}
            <div class="pl-segment" id="smt-tingkat" role="group" aria-label="Pilih tingkat">
                @foreach ($taruna as $tingkat => $listTaruna)
                    <button type="button" data-tingkat="{{ $tingkat }}" aria-pressed="false">Tingkat {{ $tingkat }} <span class="ml-1.5 opacity-70 tabular-nums">{{ $listTaruna->count() }}</span></button>
                @endforeach
            </div>
            <div class="pl-segment" id="smt-filter" role="group" aria-label="Saring status pemeriksaan">
                <button type="button" class="chip" data-f="all"    aria-pressed="true">Semua</button>
                <button type="button" class="chip" data-f="belum"  aria-pressed="false">Belum diperiksa</button>
                <button type="button" class="chip" data-f="sudah"  aria-pressed="false">Sudah diperiksa</button>
            </div>
        </div>

        {{-- Form tabel --}}
        <form id="form-smt" method="POST" action="{{ route('pemeriksaan-kesehatan.simpan', $kegiatan) }}" class="flex flex-col gap-3">
            @csrf

            <div class="pl-table-wrap max-h-[62vh]">
                <table class="pl-table smt-table">
                    {{-- lebar kolom di sini: table-layout fixed hanya membaca baris kepala pertama, yang berisi sel gabungan --}}
                    <colgroup>
                        <col style="width: 2.75rem">
                        <col style="width: 13rem">
                        <col style="width: 6.5rem">
                        <col style="width: 5.25rem">
                        <col style="width: 5.25rem">
                        <col style="width: 7.5rem">
                        <col style="width: 16rem">
                    </colgroup>
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th rowspan="2">Nama</th>
                            <th colspan="4" class="grp">Tanda tanda vital</th>
                            <th rowspan="2">Keluhan</th>
                            <th rowspan="2">Terapi</th>
                        </tr>
                        <tr>
                            <th class="grp">Tekanan darah<small>mmHg</small></th>
                            <th>Nadi<small>x/menit</small></th>
                            <th>Suhu<small>°C</small></th>
                            <th class="grp">Pernapasan<small>x/menit</small></th>
                        </tr>
                    </thead>
                    <tbody id="smt-isi">
                        @foreach ($taruna as $tingkat => $listTaruna)
                            @foreach ($listTaruna as $i => $t)
                                @php $h = old("data.{$t['id']}", $t['hasil']); $kunci = $selesai || ! auth()->user()->bisa(array_filter($t['hasil']) ? 'edit' : 'tambah'); @endphp
                                <tr data-nama="{{ strtolower($t['nama']) }}" data-tingkat="{{ $tingkat }}">
                                    <td class="c">{{ $i + 1 }}</td>
                                    <td class="nm">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('pemeriksaan-kesehatan.detail', [$kegiatan, $t['id']]) }}" class="truncate" data-detail>{{ $t['nama'] }}</a>
                                            {{-- centang = sudah diperiksa (diatur skrip) --}}
                                            <svg class="smt-penanda" data-status hidden style="color: var(--link)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Sudah diperiksa"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        </div>
                                    </td>
                                    <td><input type="text" class="smt-in vital" data-k="tekanan_darah" name="data[{{ $t['id'] }}][tekanan_darah]" @disabled($kunci) @error("data.{$t['id']}.tekanan_darah") aria-invalid="true" @enderror value="{{ $h['tekanan_darah'] }}" placeholder="120/80" aria-label="Tekanan darah {{ $t['nama'] }}"></td>
                                    <td><input type="text" inputmode="numeric" class="smt-in vital" data-k="nadi" name="data[{{ $t['id'] }}][nadi]" @disabled($kunci) @error("data.{$t['id']}.nadi") aria-invalid="true" @enderror value="{{ $h['nadi'] }}" placeholder="80" aria-label="Nadi {{ $t['nama'] }}"></td>
                                    <td><input type="text" inputmode="decimal" class="smt-in vital smt-suhu" data-k="suhu" name="data[{{ $t['id'] }}][suhu]" @disabled($kunci) @error("data.{$t['id']}.suhu") aria-invalid="true" @enderror value="{{ $h['suhu'] }}" placeholder="36,5" aria-label="Suhu {{ $t['nama'] }}"></td>
                                    <td><input type="text" inputmode="numeric" class="smt-in vital" data-k="pernapasan" name="data[{{ $t['id'] }}][pernapasan]" @disabled($kunci) @error("data.{$t['id']}.pernapasan") aria-invalid="true" @enderror value="{{ $h['pernapasan'] }}" placeholder="20" aria-label="Pernapasan {{ $t['nama'] }}"></td>
                                    <td><input type="text" class="smt-in" data-k="keluhan" name="data[{{ $t['id'] }}][keluhan]" @disabled($kunci) @error("data.{$t['id']}.keluhan") aria-invalid="true" @enderror value="{{ $h['keluhan'] }}" aria-label="Keluhan {{ $t['nama'] }}"></td>
                                    <td><input type="text" class="smt-in" data-k="terapi" name="data[{{ $t['id'] }}][terapi]" @disabled($kunci) @error("data.{$t['id']}.terapi") aria-invalid="true" @enderror value="{{ $h['terapi'] }}" aria-label="Terapi {{ $t['nama'] }}"></td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
                <p id="smt-kosong" class="text-center py-8 m-0 text-muted bg-white rounded-2xl mt-2">Tidak ada taruna yang cocok dengan pencarian atau filter.</p>
            </div>

            <div class="pl-form-actions !justify-start">
                <a href="{{ route('pemeriksaan-kesehatan.show', $kegiatan) }}" class="pl-btn pl-btn-secondary">
                    <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Ekspor PDF
                </a>
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
            const baris = [...document.querySelectorAll('#smt-isi tr[data-nama]')];
            const kosong = document.getElementById('smt-kosong');
            const form = document.getElementById('form-smt');
            const tombolTingkat = [...document.querySelectorAll('#smt-tingkat button')];
            // Tingkat awal: yang punya isian gagal validasi, lalu #tingkat-X (dibawa redirect setelah simpan), lalu tingkat pertama
            const tingkatSalah = document.querySelector('#smt-isi [aria-invalid="true"]')?.closest('tr').dataset.tingkat;
            const tingkatHash = decodeURIComponent(location.hash.replace('#tingkat-', ''));
            let tingkatAktif = tingkatSalah ?? (tombolTingkat.some((b) => b.dataset.tingkat === tingkatHash) ? tingkatHash : tombolTingkat[0]?.dataset.tingkat);
            let filterAktif = 'all';
            let kataKunci = '';

            function statusBaris(tr) {
                const ambil = (k) => tr.querySelector(`[data-k="${k}"]`).value.trim();
                const lengkap = ['tekanan_darah', 'nadi', 'suhu', 'pernapasan', 'keluhan'].every((k) => ambil(k) !== '');
                const suhu = parseFloat(ambil('suhu').replace(',', '.'));
                tr.querySelector('.smt-suhu').classList.toggle('suhu-tinggi', !Number.isNaN(suhu) && suhu >= suhuBatas);
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
                        (filterAktif === 'belum'  && !lengkap) ||
                        (filterAktif === 'sudah'  && lengkap);
                    const cocok = tr.dataset.tingkat === tingkatAktif && cocokNama && cocokFilter;
                    tr.hidden = !cocok;
                    if (cocok) tampil += 1;
                });
                kosong.style.display = tampil ? 'none' : 'block';
            }

            function pilihTingkat(tingkat) {
                tingkatAktif = tingkat;
                tombolTingkat.forEach((b) => b.setAttribute('aria-pressed', b.dataset.tingkat === tingkat ? 'true' : 'false'));
                // fragmen ikut ke URL redirect setelah simpan, jadi tetap di tingkat yang sama
                history.replaceState(null, '', `#tingkat-${tingkat}`);
                form.action = form.action.split('#')[0] + location.hash;
                perbarui();
            }

            document.getElementById('smt-tingkat').addEventListener('click', (e) => {
                const tombol = e.target.closest('button');
                if (tombol) pilihTingkat(tombol.dataset.tingkat);
            });

            let berubah = false; // ada isian yang belum disimpan
            document.getElementById('smt-isi').addEventListener('input', () => { berubah = true; perbarui(); });
            form.addEventListener('submit', () => { berubah = false; });

            // Klik baris (selain isian & tombol aksi) membuka halaman detail taruna
            document.getElementById('smt-isi').addEventListener('click', (e) => {
                if (e.target.closest('input, select, button') || e.ctrlKey || e.metaKey) return;
                e.preventDefault();
                if (!berubah || confirm('Ada isian yang belum disimpan. Tinggalkan halaman ini?')) {
                    location.href = (e.target.closest('a') ?? e.target.closest('tr').querySelector('[data-detail]')).href;
                }
            });

            // Navigasi Enter antar input
            document.getElementById('smt-isi').addEventListener('keydown', (e) => {
                if (e.key !== 'Enter' || !e.target.matches('.smt-in')) return;
                e.preventDefault();
                const bisaDiisi = [...document.querySelectorAll('#smt-isi tr:not([hidden]) .smt-in:not(:disabled)')];
                const idx = bisaDiisi.indexOf(e.target);
                (bisaDiisi[idx + 1] || e.target).focus();
            });

            document.getElementById('smt-cari').addEventListener('input', (e) => {
                kataKunci = e.target.value.trim().toLowerCase();
                perbarui();
            });

            document.getElementById('smt-filter').addEventListener('click', (e) => {
                const tombol = e.target.closest('.chip');
                if (!tombol) return;
                filterAktif = tombol.dataset.f;
                document.querySelectorAll('#smt-filter .chip').forEach((b) => b.setAttribute('aria-pressed', b === tombol ? 'true' : 'false'));
                perbarui();
            });

            if (tingkatAktif) { pilihTingkat(tingkatAktif); } else { perbarui(); }
        })();
    </script>
</body>
</html>
