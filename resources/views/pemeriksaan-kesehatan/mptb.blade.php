{{-- pemeriksaan-kesehatan/mptb.blade.php — Halaman input MPTB (2 sesi per hari selama periode MPTB) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'MPTB'])
    <style>
        .mptb-wrap { overflow-x: auto; }
        .mptb-table { table-layout: fixed; font-size: .8125rem; line-height: 1.15rem; min-width: 96rem; }
        .mptb-table th, .mptb-table td { padding: .5rem .4rem; }
        .mptb-table thead th { position: sticky; top: 0; z-index: 1; background: var(--primary-700); color: var(--on-primary); border-bottom-color: var(--primary-700); }
        .mptb-table thead tr.sub th { background: var(--primary-900); font-size: .75rem; }
        .mptb-table th.grp { border-left: 1px solid rgba(255,255,255,.25); border-right: 1px solid rgba(255,255,255,.25); }
        .mptb-table td.c { text-align: center; }
        .mptb-table td.nm { text-align: left; font-weight: 600; }
        .mptb-table td.riwayat { text-align: left; color: var(--text-muted); }
        .mptb-in { width: 100%; height: 2.25rem; border: 1px solid var(--neutral-200); border-radius: var(--radius-sm); background: var(--surface-card); padding: 0 .5rem; font: inherit; color: var(--text); text-align: center; }
        .mptb-in:hover { border-color: var(--primary-400); }
        .mptb-in.text-left { text-align: left; }
        .mptb-in[aria-invalid="true"] { border-color: var(--secondary-800); background: var(--secondary-50); }
        .mptb-in:focus-visible { outline: 2px solid transparent; box-shadow: var(--focus-ring); }
        .mptb-in.suhu-tinggi { background: var(--secondary-50); border-color: var(--secondary-800); color: var(--secondary-800); font-weight: 700; }
        .seg { display: inline-flex; border: 1px solid var(--neutral-200); border-radius: var(--radius-sm); overflow: hidden; }
        .seg button { border: 0; background: var(--surface-card); height: 2.75rem; padding: 0 1.125rem; font: 600 .875rem/1 var(--font-sans); color: var(--text-muted); cursor: pointer; }
        .seg button[aria-pressed="true"] { background: var(--primary-700); color: var(--on-primary); }
        .seg button:focus-visible, .chip:focus-visible, .ab:focus-visible { outline: 2px solid transparent; box-shadow: var(--focus-ring); }
        .prog-card { background: var(--surface-card); border: 1px solid var(--border); border-radius: var(--radius-md); padding: .875rem 1rem; display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; }
        .prog-bar { flex: 1; min-width: 11rem; height: .5rem; background: var(--neutral-200); border-radius: var(--radius-full); overflow: hidden; }
        .prog-bar i { display: block; height: 100%; background: var(--primary-700); }
        .chip { height: 2.125rem; padding: 0 .875rem; border-radius: var(--radius-full); border: 1px solid var(--neutral-200); background: none; color: var(--text-muted); font: 600 .8125rem/1 var(--font-sans); cursor: pointer; }
        .chip[aria-pressed="true"] { background: var(--primary-50); border-color: var(--primary-400); color: var(--primary-700); }
        .ab { width: 2.25rem; height: 2.25rem; border: 0; border-radius: var(--radius-sm); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: background-color .15s, color .15s; }
        .ab-detail { background: var(--primary-50); color: var(--primary-700); } .ab-detail:hover { background: var(--primary-700); color: #fff; }
        .ab-tren { background: var(--tertiary-50); color: var(--tertiary-800); } .ab-tren:hover { background: var(--tertiary-800); color: #fff; }
        #mptb-kosong { display: none; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php
        $labelTanggal = \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, d F Y');
        $suhuBatas = 37.5; // contoh saja, batas resmi ditentukan tenaga medis
    @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-4 pr-4 py-6">

        <header class="flex flex-col gap-1">
            <a href="{{ route('pemeriksaan-kesehatan.index') }}" class="pl-btn pl-btn-ghost self-start h-9 pl-2 pr-3">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
            <h1 class="h1 m-0 text-primary-900">{{ $judulPeriode }}</h1>
            <p class="body-lg m-0 font-medium">Periode aktif &middot; {{ $labelTanggal }} &middot; Sesi {{ strtolower($sesi) }}</p>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
        @endif

        {{-- Pilih tanggal & sesi (ganti kombinasi ini memuat ulang halaman) --}}
        <form method="GET" action="{{ route('pemeriksaan-kesehatan.mptb.index') }}" class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <label class="sr-only" for="tanggal">Tanggal</label>
                <select id="tanggal" name="tanggal" class="pl-input h-11" onchange="this.form.submit()">
                    @foreach ($tanggalTersedia as $t)
                        <option value="{{ $t }}" @selected($t === $tanggal)>{{ \Carbon\Carbon::parse($t)->locale('id')->translatedFormat('l, d F Y') }}</option>
                    @endforeach
                </select>
                <div class="seg" role="group" aria-label="Pilih sesi">
                    @foreach (['Pagi', 'Malam'] as $s)
                        <button type="submit" name="sesi" value="{{ $s }}" aria-pressed="{{ $s === $sesi ? 'true' : 'false' }}">Sesi {{ strtolower($s) }}</button>
                    @endforeach
                </div>
            </div>
            <label class="relative block w-72 max-w-full">
                <span class="sr-only">Cari nama taruna</span>
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-[1.125rem] h-[1.125rem] text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="mptb-cari" type="search" class="pl-input !pl-10" placeholder="Cari nama taruna">
            </label>
        </form>

        {{-- Progress sesi --}}
        <div class="prog-card">
            <div>
                <span class="font-heading font-bold text-primary-900 text-xl" id="mptb-jumlah">0</span>
                <span class="text-muted"> dari {{ $taruna->count() }} taruna sudah diperiksa</span>
            </div>
            <div class="prog-bar"><i id="mptb-progress" style="width:0%"></i></div>
            <div class="flex flex-wrap gap-1.5" id="mptb-filter">
                <button type="button" class="chip" data-f="all" aria-pressed="true">Semua</button>
                <button type="button" class="chip" data-f="belum" aria-pressed="false">Belum diperiksa</button>
                <button type="button" class="chip" data-f="sudah" aria-pressed="false">Sudah diperiksa</button>
                <button type="button" class="chip" data-f="tindak" aria-pressed="false">Perlu tindak lanjut</button>
            </div>
        </div>

        {{-- Tabel input hasil pemeriksaan --}}
        <form id="form-mptb" method="POST" action="{{ route('pemeriksaan-kesehatan.mptb.simpan') }}" class="flex flex-col gap-3">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
            <input type="hidden" name="sesi" value="{{ $sesi }}">

            <div class="pl-table-wrap mptb-wrap max-h-[62vh]">
                <table class="pl-table mptb-table">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width:2.75rem">No</th>
                            <th rowspan="2" style="width:11rem">Nama</th>
                            <th rowspan="2" style="width:6rem">Jenis kelamin</th>
                            <th colspan="5" class="grp">Tanda tanda vital</th>
                            <th rowspan="2" style="width:9rem">Keluhan sebelumnya</th>
                            <th rowspan="2" style="width:9rem">Terapi sebelumnya</th>
                            <th rowspan="2" style="width:9rem">Terapi</th>
                            <th rowspan="2" style="width:9rem">Keterangan</th>
                            <th rowspan="2" style="width:8rem">Status</th>
                            <th rowspan="2" style="width:6rem">Aksi</th>
                        </tr>
                        <tr class="sub">
                            <th class="grp" style="width:6rem">Tekanan darah</th>
                            <th style="width:4.5rem">Nadi</th>
                            <th style="width:4.5rem">Suhu</th>
                            <th style="width:6.25rem">Pernapasan</th>
                            <th class="grp" style="width:9rem">Keluhan</th>
                        </tr>
                    </thead>
                    <tbody id="mptb-isi">
                        @foreach ($taruna as $i => $t)
                            @php $h = old("data.{$t['id']}", $t['hasil']); @endphp
                            <tr data-nama="{{ strtolower($t['nama']) }}">
                                <td class="c">{{ $i + 1 }}</td>
                                <td class="nm">{{ $t['nama'] }}</td>
                                <td class="c">{{ $t['jenis_kelamin'] }}</td>
                                <td><input type="text" class="mptb-in" data-k="tekanan_darah" name="data[{{ $t['id'] }}][tekanan_darah]" @error("data.{$t['id']}.tekanan_darah") aria-invalid="true" @enderror value="{{ $h['tekanan_darah'] }}" placeholder="120/80" aria-label="Tekanan darah {{ $t['nama'] }}"></td>
                                <td><input type="text" inputmode="numeric" class="mptb-in mptb-vital" data-k="nadi" name="data[{{ $t['id'] }}][nadi]" @error("data.{$t['id']}.nadi") aria-invalid="true" @enderror value="{{ $h['nadi'] }}" aria-label="Nadi {{ $t['nama'] }}"></td>
                                <td><input type="text" inputmode="decimal" class="mptb-in mptb-vital mptb-suhu" data-k="suhu" name="data[{{ $t['id'] }}][suhu]" @error("data.{$t['id']}.suhu") aria-invalid="true" @enderror value="{{ $h['suhu'] }}" aria-label="Suhu {{ $t['nama'] }}"></td>
                                <td><input type="text" inputmode="numeric" class="mptb-in mptb-vital" data-k="pernapasan" name="data[{{ $t['id'] }}][pernapasan]" @error("data.{$t['id']}.pernapasan") aria-invalid="true" @enderror value="{{ $h['pernapasan'] }}" aria-label="Pernapasan {{ $t['nama'] }}"></td>
                                <td><input type="text" list="mptb-daftar-keluhan" class="mptb-in text-left mptb-vital" data-k="keluhan" name="data[{{ $t['id'] }}][keluhan]" @error("data.{$t['id']}.keluhan") aria-invalid="true" @enderror value="{{ $h['keluhan'] }}" aria-label="Keluhan {{ $t['nama'] }}"></td>
                                <td class="riwayat">{{ $t['keluhan_sebelumnya'] }}</td>
                                <td class="riwayat">{{ $t['terapi_sebelumnya'] }}</td>
                                <td><input type="text" list="mptb-daftar-terapi" class="mptb-in text-left" data-k="terapi" name="data[{{ $t['id'] }}][terapi]" @error("data.{$t['id']}.terapi") aria-invalid="true" @enderror value="{{ $h['terapi'] }}" aria-label="Terapi {{ $t['nama'] }}"></td>
                                <td>
                                    <select class="mptb-in text-left" data-k="keterangan" name="data[{{ $t['id'] }}][keterangan]" @error("data.{$t['id']}.keterangan") aria-invalid="true" @enderror aria-label="Keterangan {{ $t['nama'] }}">
                                        <option value="" @selected($h['keterangan'] === '')>Pilih</option>
                                        <option value="Sudah membaik" @selected($h['keterangan'] === 'Sudah membaik')>Sudah membaik</option>
                                        <option value="Dalam perawatan" @selected($h['keterangan'] === 'Dalam perawatan')>Dalam perawatan</option>
                                    </select>
                                </td>
                                <td class="c"><span class="pl-badge mptb-status" data-status>Belum diperiksa</span></td>
                                <td>
                                    <div class="flex justify-center gap-1.5">
                                        <button type="button" class="ab ab-detail" aria-label="Detail {{ $t['nama'] }}" title="Detail">
                                            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                        </button>
                                        <button type="button" class="ab ab-tren" aria-label="Tren sesi sebelumnya {{ $t['nama'] }}" title="Tren sesi sebelumnya">
                                            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12h4.5l2.25-6 4.5 12 2.25-6H21"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p id="mptb-kosong" class="text-center py-8 text-muted">Tidak ada taruna yang cocok dengan pencarian atau filter.</p>
            </div>

            <datalist id="mptb-daftar-keluhan">
                <option>Demam</option><option>Pusing</option><option>Batuk</option><option>Pilek</option>
                <option>Nyeri perut</option><option>Lemas</option><option>Sakit tenggorokan</option><option>Tidak ada</option>
            </datalist>
            <datalist id="mptb-daftar-terapi">
                <option>Paracetamol</option><option>Ibuprofen</option><option>OBH Combi</option><option>Cetirizine</option>
                <option>Antasida</option><option>Vitamin B kompleks</option><option>Obat kumur antiseptik</option><option>Tidak ada</option>
            </datalist>

            <div class="flex justify-end">
                <button type="submit" class="pl-btn pl-btn-primary h-11">
                    <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Simpan hasil pemeriksaan sesi {{ strtolower($sesi) }}
                </button>
            </div>
        </form>
    </main>

    <script>
        (() => {
            const suhuBatas = {{ $suhuBatas }};
            const baris = [...document.querySelectorAll('#mptb-isi tr')];
            const kosong = document.getElementById('mptb-kosong');
            const jumlahEl = document.getElementById('mptb-jumlah');
            const progressEl = document.getElementById('mptb-progress');
            let filterAktif = 'all';
            let kataKunci = '';

            function statusBaris(tr) {
                const ambil = (k) => tr.querySelector(`[data-k="${k}"]`).value.trim();
                const lengkap = ['nadi', 'suhu', 'pernapasan', 'keluhan'].every((k) => ambil(k) !== '') && ambil('tekanan_darah') !== '';
                const suhu = parseFloat(ambil('suhu').replace(',', '.'));
                const suhuTinggi = !Number.isNaN(suhu) && suhu >= suhuBatas;
                const perluTindakLanjut = lengkap && (ambil('keterangan') === 'Dalam perawatan' || suhuTinggi);
                tr.querySelector('.mptb-suhu').classList.toggle('suhu-tinggi', suhuTinggi);

                const badge = tr.querySelector('[data-status]');
                if (!lengkap) {
                    badge.className = 'pl-badge mptb-status';
                    badge.textContent = 'Belum diperiksa';
                    return { lengkap: false, perluTindakLanjut: false };
                }
                if (perluTindakLanjut) {
                    badge.className = 'pl-badge mptb-status pl-badge-accent';
                    badge.textContent = 'Perlu tindak lanjut';
                } else {
                    badge.className = 'pl-badge mptb-status pl-badge-primary';
                    badge.textContent = 'Sudah diperiksa';
                }
                return { lengkap: true, perluTindakLanjut };
            }

            function perbarui() {
                let selesai = 0;
                let tampil = 0;
                baris.forEach((tr) => {
                    const { lengkap, perluTindakLanjut } = statusBaris(tr);
                    if (lengkap) selesai += 1;

                    const cocokNama = tr.dataset.nama.includes(kataKunci);
                    const cocokFilter =
                        filterAktif === 'all' ||
                        (filterAktif === 'belum' && !lengkap) ||
                        (filterAktif === 'sudah' && lengkap) ||
                        (filterAktif === 'tindak' && lengkap && perluTindakLanjut);
                    const cocok = cocokNama && cocokFilter;
                    tr.hidden = !cocok;
                    if (cocok) tampil += 1;
                });
                jumlahEl.textContent = selesai;
                progressEl.style.width = baris.length ? `${(selesai / baris.length) * 100}%` : '0%';
                kosong.style.display = tampil ? 'none' : 'block';
            }

            document.getElementById('mptb-isi').addEventListener('input', perbarui);

            document.getElementById('mptb-isi').addEventListener('keydown', (e) => {
                if (e.key !== 'Enter' || !e.target.matches('.mptb-in')) return;
                e.preventDefault();
                const bisaDiisi = [...document.querySelectorAll('#mptb-isi tr:not([hidden]) .mptb-in')];
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
    </script>
</body>
</html>
