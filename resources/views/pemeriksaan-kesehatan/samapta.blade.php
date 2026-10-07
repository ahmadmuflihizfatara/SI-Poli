{{-- pemeriksaan-kesehatan/samapta.blade.php — Halaman input Samapta (2x per semester) --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Samapta'])
    <style>
        .smt-table { table-layout: fixed; font-size: .8125rem; line-height: 1.2rem; min-width: 96rem; }
        .smt-table th, .smt-table td { padding: .5rem .4rem; }
        .smt-table td { height: 3.25rem; }
        .smt-table thead { position: sticky; top: 0; z-index: 1; }
        .smt-table thead th { text-align: center; vertical-align: middle; }
        .smt-table thead tr.sub th { padding-top: .375rem; }
        .smt-table th.grp { border-left: 1px solid var(--border); border-right: 1px solid var(--border); }
        .smt-table td.c { text-align: center; }
        .smt-table td.nm { text-align: left; font-weight: 500; }
        .smt-table td.riwayat { text-align: left; color: var(--text-muted); }
        .smt-table tr.tingkat-header td { height: auto; background: var(--cloud); color: var(--heading); font-weight: 600; font-size: .8125rem; padding: .625rem 1rem; border-bottom: 0; }
        .smt-in { box-sizing: border-box; width: 100%; height: 2.5rem; border: 1px solid var(--field-line); border-radius: .75rem; background: var(--surface-card); padding: 0 .625rem; font: inherit; color: var(--text); text-align: center; }
        .smt-in:hover { border-color: var(--primary-400); }
        .smt-in.text-left { text-align: left; }
        .smt-in:disabled { background: var(--neutral-50); color: var(--text-muted); cursor: not-allowed; }
        .smt-in[aria-invalid="true"] { border-color: var(--danger); background: var(--danger-wash); }
        .smt-in:focus-visible { outline: 2px solid var(--focus); outline-offset: 1px; }
        .smt-in.suhu-tinggi { background: var(--danger-wash); border-color: var(--danger); color: var(--danger); font-weight: 700; }
        .prog-bar { flex: 1; min-width: 11rem; height: .5rem; background: var(--surface-card); border-radius: var(--radius-full); overflow: hidden; }
        .prog-bar i { display: block; height: 100%; border-radius: inherit; background: var(--primary-700); }
        #smt-kosong { display: none; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php $suhuBatas = 37.5; @endphp

    <main class="pl-main">

        {{-- Header --}}
        <header class="pl-page-head">
            <div>
                <a href="{{ route('pemeriksaan-kesehatan.index') }}" class="pl-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Kembali
                </a>
                <h1 class="pl-page-title">{{ $kegiatan->nama }}</h1>
                <p class="pl-page-sub">Samapta &middot; {{ $kegiatan->labelRentang() }}</p>
            </div>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="pl-toolbar">
            <label class="pl-search ml-auto">
                <span class="pl-sr">Cari nama taruna</span>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input id="smt-cari" type="search" class="pl-input" placeholder="Cari nama taruna">
            </label>
        </div>

        {{-- Progress --}}
        <div class="pl-card flex flex-wrap items-center gap-4 px-6 py-4">
            <div>
                <span class="font-semibold text-primary-900 text-2xl tabular-nums" id="smt-jumlah">0</span>
                <span class="text-muted"> dari {{ $totalTaruna }} taruna sudah diperiksa</span>
            </div>
            <div class="prog-bar"><i id="smt-progress" style="width:0%"></i></div>
            <div class="pl-segment" id="smt-filter">
                <button type="button" class="chip" data-f="all"    aria-pressed="true">Semua</button>
                <button type="button" class="chip" data-f="belum"  aria-pressed="false">Belum diperiksa</button>
                <button type="button" class="chip" data-f="sudah"  aria-pressed="false">Sudah diperiksa</button>
                <button type="button" class="chip" data-f="tindak" aria-pressed="false">Perlu tindak lanjut</button>
            </div>
        </div>

        {{-- Form tabel --}}
        <form id="form-smt" method="POST" action="{{ route('pemeriksaan-kesehatan.simpan', $kegiatan) }}" class="flex flex-col gap-3">
            @csrf

            <div class="pl-table-wrap max-h-[62vh]">
                <table class="pl-table smt-table">
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
                    <tbody id="smt-isi">
                        @php $noGlobal = 1; @endphp
                        @foreach ($taruna as $tingkat => $listTaruna)
                            {{-- Group header per tingkat --}}
                            <tr class="tingkat-header">
                                <td colspan="15">
                                    Tingkat {{ $tingkat }} &nbsp;·&nbsp; {{ $listTaruna->count() }} taruna
                                </td>
                            </tr>
                            @foreach ($listTaruna as $t)
                                @php $h = old("data.{$t['id']}", $t['hasil']); $kunci = ! auth()->user()->bisa(array_filter($t['hasil']) ? 'edit' : 'tambah'); @endphp
                                <tr data-nama="{{ strtolower($t['nama']) }}" data-tingkat="{{ $tingkat }}">
                                    <td class="c">{{ $noGlobal++ }}</td>
                                    <td class="nm">{{ $t['nama'] }}</td>
                                    <td class="c">{{ $t['jenis_kelamin'] }}</td>
                                    <td><input type="text" class="smt-in" data-k="tekanan_darah" name="data[{{ $t['id'] }}][tekanan_darah]" @disabled($kunci) @error("data.{$t['id']}.tekanan_darah") aria-invalid="true" @enderror value="{{ $h['tekanan_darah'] }}" placeholder="120/80" aria-label="Tekanan darah {{ $t['nama'] }}"></td>
                                    <td><input type="text" inputmode="numeric" class="smt-in smt-vital" data-k="nadi" name="data[{{ $t['id'] }}][nadi]" @disabled($kunci) @error("data.{$t['id']}.nadi") aria-invalid="true" @enderror value="{{ $h['nadi'] }}" aria-label="Nadi {{ $t['nama'] }}"></td>
                                    <td><input type="text" inputmode="decimal" class="smt-in smt-vital smt-suhu" data-k="suhu" name="data[{{ $t['id'] }}][suhu]" @disabled($kunci) @error("data.{$t['id']}.suhu") aria-invalid="true" @enderror value="{{ $h['suhu'] }}" aria-label="Suhu {{ $t['nama'] }}"></td>
                                    <td><input type="text" inputmode="numeric" class="smt-in smt-vital" data-k="pernapasan" name="data[{{ $t['id'] }}][pernapasan]" @disabled($kunci) @error("data.{$t['id']}.pernapasan") aria-invalid="true" @enderror value="{{ $h['pernapasan'] }}" aria-label="Pernapasan {{ $t['nama'] }}"></td>
                                    <td><input type="text" list="smt-daftar-keluhan" class="smt-in text-left smt-vital" data-k="keluhan" name="data[{{ $t['id'] }}][keluhan]" @disabled($kunci) @error("data.{$t['id']}.keluhan") aria-invalid="true" @enderror value="{{ $h['keluhan'] }}" aria-label="Keluhan {{ $t['nama'] }}"></td>
                                    <td class="riwayat">{{ $t['keluhan_sebelumnya'] }}</td>
                                    <td class="riwayat">{{ $t['terapi_sebelumnya'] }}</td>
                                    <td><input type="text" list="smt-daftar-terapi" class="smt-in text-left" data-k="terapi" name="data[{{ $t['id'] }}][terapi]" @disabled($kunci) @error("data.{$t['id']}.terapi") aria-invalid="true" @enderror value="{{ $h['terapi'] }}" aria-label="Terapi {{ $t['nama'] }}"></td>
                                    <td>
                                        <select class="smt-in text-left" data-k="keterangan" name="data[{{ $t['id'] }}][keterangan]" @disabled($kunci) @error("data.{$t['id']}.keterangan") aria-invalid="true" @enderror aria-label="Keterangan {{ $t['nama'] }}">
                                            <option value="" @selected($h['keterangan'] === '')>Pilih</option>
                                            <option value="Sudah membaik"    @selected($h['keterangan'] === 'Sudah membaik')>Sudah membaik</option>
                                            <option value="Dalam perawatan" @selected($h['keterangan'] === 'Dalam perawatan')>Dalam perawatan</option>
                                        </select>
                                    </td>
                                    <td class="c"><span class="pl-badge smt-status" data-status>Belum diperiksa</span></td>
                                    <td>
                                        <div class="flex justify-center gap-2">
                                            <button type="button" class="pl-icon-btn" aria-label="Detail {{ $t['nama'] }}" title="Detail">
                                                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                            </button>
                                            <button type="button" class="pl-icon-btn pl-icon-btn--notice" aria-label="Tren semester sebelumnya {{ $t['nama'] }}" title="Tren semester sebelumnya">
                                                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12h4.5l2.25-6 4.5 12 2.25-6H21"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
                <p id="smt-kosong" class="text-center py-8 m-0 text-muted bg-white rounded-2xl mt-2">Tidak ada taruna yang cocok dengan pencarian atau filter.</p>
            </div>

            <datalist id="smt-daftar-keluhan">
                <option>Demam</option><option>Pusing</option><option>Batuk</option><option>Pilek</option>
                <option>Nyeri perut</option><option>Lemas</option><option>Sakit tenggorokan</option><option>Tidak ada</option>
            </datalist>
            <datalist id="smt-daftar-terapi">
                <option>Paracetamol</option><option>Ibuprofen</option><option>OBH Combi</option><option>Cetirizine</option>
                <option>Antasida</option><option>Vitamin B kompleks</option><option>Obat kumur antiseptik</option><option>Tidak ada</option>
            </datalist>

            <div class="pl-form-actions !justify-between">
                <a href="{{ route('pemeriksaan-kesehatan.show', $kegiatan) }}"
                    class="pl-btn pl-btn-secondary">
                    <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Ekspor PDF
                </a>
                <button type="submit" class="pl-btn pl-btn-primary">
                    <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Simpan hasil pemeriksaan
                </button>
            </div>
        </form>

    </main>

    <script>
        (() => {
            const suhuBatas = {{ $suhuBatas }};
            const baris = [...document.querySelectorAll('#smt-isi tr[data-nama]')];
            const kosong = document.getElementById('smt-kosong');
            const jumlahEl = document.getElementById('smt-jumlah');
            const progressEl = document.getElementById('smt-progress');
            let filterAktif = 'all';
            let kataKunci = '';

            function statusBaris(tr) {
                const ambil = (k) => tr.querySelector(`[data-k="${k}"]`).value.trim();
                const lengkap = ['nadi', 'suhu', 'pernapasan', 'keluhan'].every((k) => ambil(k) !== '') && ambil('tekanan_darah') !== '';
                const suhu = parseFloat(ambil('suhu').replace(',', '.'));
                const suhuTinggi = !Number.isNaN(suhu) && suhu >= suhuBatas;
                const perluTindakLanjut = lengkap && (ambil('keterangan') === 'Dalam perawatan' || suhuTinggi);
                tr.querySelector('.smt-suhu').classList.toggle('suhu-tinggi', suhuTinggi);

                const badge = tr.querySelector('[data-status]');
                if (!lengkap) {
                    badge.className = 'pl-badge smt-status';
                    badge.textContent = 'Belum diperiksa';
                    return { lengkap: false, perluTindakLanjut: false };
                }
                if (perluTindakLanjut) {
                    badge.className = 'pl-badge smt-status pl-badge-accent';
                    badge.textContent = 'Perlu tindak lanjut';
                } else {
                    badge.className = 'pl-badge smt-status pl-badge-primary';
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
                        (filterAktif === 'belum'  && !lengkap) ||
                        (filterAktif === 'sudah'  && lengkap) ||
                        (filterAktif === 'tindak' && lengkap && perluTindakLanjut);
                    const cocok = cocokNama && cocokFilter;
                    tr.hidden = !cocok;
                    if (cocok) tampil += 1;
                });

                // Sembunyikan group header tingkat jika semua barisnya tersembunyi
                document.querySelectorAll('#smt-isi tr.tingkat-header').forEach((header) => {
                    let adaYangTampil = false;
                    let next = header.nextElementSibling;
                    while (next && !next.classList.contains('tingkat-header')) {
                        if (!next.hidden) { adaYangTampil = true; break; }
                        next = next.nextElementSibling;
                    }
                    header.hidden = !adaYangTampil;
                });

                jumlahEl.textContent = selesai;
                progressEl.style.width = baris.length ? `${(selesai / baris.length) * 100}%` : '0%';
                kosong.style.display = tampil ? 'none' : 'block';
            }

            document.getElementById('smt-isi').addEventListener('input', perbarui);

            // Navigasi Enter antar input
            document.getElementById('smt-isi').addEventListener('keydown', (e) => {
                if (e.key !== 'Enter' || !e.target.matches('.smt-in')) return;
                e.preventDefault();
                const bisaDiisi = [...document.querySelectorAll('#smt-isi tr:not([hidden]) .smt-in')];
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

            perbarui();
        })();
    </script>
</body>
</html>
