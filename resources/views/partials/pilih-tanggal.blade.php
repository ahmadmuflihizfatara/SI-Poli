{{-- partials/pilih-tanggal.blade.php — pemilih satu tanggal bergaya kalender rentang dashboard: chip tanggal + kalender popover.
     Pakai: @include('partials.pilih-tanggal', ['id' => 'tanggal_x', 'name' => 'tanggal_x', 'nilai' => old('tanggal_x'),
                'min' => today()->toDateString(), 'max' => null, 'wajib' => true, 'pintasan' => ['Besok' => 1, '1 minggu' => 7]])
     'gaya' => 'tombol' = tampilan tombol rentang tanggal dashboard (pil tanpa garis, selebar isi); bawaan 'isian' = kotak seperti .pl-input.
     <label for="{{ $id }}"> menunjuk tombol chip. Teks chip (tanggal, jarak dari hari ini) ditulis skrip. --}}
@php
    $tanggalTerpilih = filled($nilai ?? null) ? \Illuminate\Support\Carbon::parse($nilai) : null;
@endphp
<div class="relative" data-pilih-tanggal data-min="{{ $min ?? '' }}" data-max="{{ $max ?? '' }}" data-hari-ini="{{ today()->toDateString() }}">
    <button type="button" class="pl-date {{ ($gaya ?? 'isian') === 'tombol' ? 'pl-date--tombol' : 'pl-date--isian' }}" id="{{ $id }}" aria-haspopup="dialog" aria-expanded="false" aria-controls="{{ $id }}-kalender"
        @error($name) aria-invalid="true" @enderror>
        <span class="pl-date__icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
        <span class="pl-date__teks" data-tanggal-teks @if (! $tanggalTerpilih) data-kosong @endif>{{ $tanggalTerpilih?->locale('id')->translatedFormat('l, j F Y') ?? 'Pilih tanggal' }}</span>
        <span class="pl-date__pil" data-jarak></span>
        @if (($gaya ?? 'isian') !== 'tombol')
            <svg class="pl-date__chev" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
        @endif
    </button>
    <input class="pl-date__nilai" name="{{ $name }}" value="{{ $tanggalTerpilih?->toDateString() }}" tabindex="-1" aria-hidden="true" @required($wajib ?? false)>
    <div id="{{ $id }}-kalender" class="kal-pop kal-pop--kiri" role="dialog" aria-label="Kalender">
        <div class="kal-kepala">
            <button type="button" class="pl-icon-btn" data-geser="-1" aria-label="Bulan sebelumnya"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 19.5 8.25 12l7.5-7.5"/></svg></button>
            <span aria-live="polite" data-bulan></span>
            <button type="button" class="pl-icon-btn" data-geser="1" aria-label="Bulan berikutnya"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg></button>
        </div>
        <div class="kal-grid" data-grid></div>
        @if (! empty($pintasan))
            <div class="kal-kaki kal-pintas">
                @foreach ($pintasan as $labelPintasan => $selisihHari)
                    <button type="button" data-pintas="{{ $selisihHari }}">{{ $labelPintasan }}</button>
                @endforeach
            </div>
        @endif
    </div>
</div>

@once
    <script>
        // Kalender satu tanggal: klik tanggal/pintasan = pilih lalu tutup; tanggal di luar min/max dinonaktifkan
        document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('[data-pilih-tanggal]').forEach((wadah) => {
            const tombol = wadah.querySelector('button.pl-date'), nilai = wadah.querySelector('.pl-date__nilai'), pop = wadah.querySelector('.kal-pop');
            const grid = pop.querySelector('[data-grid]'), judulBulan = pop.querySelector('[data-bulan]');
            const baca = (s) => { const [y, m, d] = s.split('-').map(Number); return new Date(y, m - 1, d); };
            const iso = (d) => [d.getFullYear(), String(d.getMonth() + 1).padStart(2, '0'), String(d.getDate()).padStart(2, '0')].join('-');
            const bulanDari = (d) => new Date(d.getFullYear(), d.getMonth(), 1);
            const hariIni = baca(wadah.dataset.hariIni);
            const min = wadah.dataset.min ? baca(wadah.dataset.min) : null, max = wadah.dataset.max ? baca(wadah.dataset.max) : null;
            const boleh = (d) => (!min || d >= min) && (!max || d <= max);
            let pilih = nilai.value ? baca(nilai.value) : null, lihat = bulanDari(pilih ?? hariIni);

            const jarak = (d) => {
                const n = Math.round((d - hariIni) / 864e5);
                return ({ 0: 'Hari ini', 1: 'Besok', [-1]: 'Kemarin' })[n] ?? (n > 0 ? `${n} hari lagi` : `${-n} hari lalu`);
            };
            const tulis = () => {
                const teks = tombol.querySelector('[data-tanggal-teks]');
                teks.textContent = pilih ? pilih.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : 'Pilih tanggal';
                teks.toggleAttribute('data-kosong', !pilih);
                tombol.querySelector('[data-jarak]').textContent = pilih ? jarak(pilih) : '';
                nilai.value = pilih ? iso(pilih) : '';
            };
            const gambar = () => {
                judulBulan.textContent = lihat.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                pop.querySelector('[data-geser="-1"]').disabled = !!min && lihat <= bulanDari(min);
                pop.querySelector('[data-geser="1"]').disabled = !!max && lihat >= bulanDari(max);
                const isi = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'].map((h) => Object.assign(document.createElement('span'), { textContent: h }));
                for (let i = (lihat.getDay() + 6) % 7; i > 0; i--) { isi.push(document.createElement('span')); }
                const jumlahHari = new Date(lihat.getFullYear(), lihat.getMonth() + 1, 0).getDate();
                for (let t = 1; t <= jumlahHari; t++) {
                    const d = new Date(lihat.getFullYear(), lihat.getMonth(), t), b = document.createElement('button'), dipilih = !!pilih && +d === +pilih;
                    b.type = 'button';
                    b.dataset.tanggal = iso(d);
                    b.disabled = !boleh(d);
                    b.classList.toggle('hari-ini', +d === +hariIni);
                    b.classList.toggle('awal', dipilih);
                    b.classList.toggle('akhir', dipilih);
                    b.setAttribute('aria-pressed', String(dipilih));
                    b.setAttribute('aria-label', d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
                    b.append(Object.assign(document.createElement('span'), { textContent: t }));
                    isi.push(b);
                }
                grid.replaceChildren(...isi);
                pop.querySelectorAll('[data-pintas]').forEach((b) => { b.disabled = !boleh(new Date(hariIni.getFullYear(), hariIni.getMonth(), hariIni.getDate() + Number(b.dataset.pintas))); });
            };
            const atur = (buka) => {
                if (buka) {
                    gambar();
                    // buka ke atas bila ruang di bawah chip (layar atau popup) tidak cukup
                    const kotak = tombol.getBoundingClientRect(), batas = tombol.closest('dialog')?.getBoundingClientRect().bottom ?? innerHeight;
                    pop.classList.toggle('kal-pop--atas', batas - kotak.bottom < pop.offsetHeight + 16 && kotak.top > batas - kotak.bottom);
                }
                pop.classList.toggle('on', buka);
                tombol.setAttribute('aria-expanded', String(buka));
            };
            const tetapkan = (d) => {
                pilih = d;
                lihat = bulanDari(d);
                tombol.removeAttribute('aria-invalid');
                tulis();
                nilai.dispatchEvent(new Event('change', { bubbles: true }));
                atur(false);
                tombol.focus();
            };

            grid.addEventListener('click', (e) => { const b = e.target.closest('button[data-tanggal]'); if (b) { tetapkan(baca(b.dataset.tanggal)); } });
            pop.querySelectorAll('[data-pintas]').forEach((b) => b.addEventListener('click', () => tetapkan(new Date(hariIni.getFullYear(), hariIni.getMonth(), hariIni.getDate() + Number(b.dataset.pintas)))));
            pop.querySelectorAll('[data-geser]').forEach((b) => b.addEventListener('click', () => {
                lihat = new Date(lihat.getFullYear(), lihat.getMonth() + Number(b.dataset.geser), 1);
                gambar();
            }));
            tombol.addEventListener('click', () => atur(!pop.classList.contains('on')));
            nilai.addEventListener('invalid', () => tombol.setAttribute('aria-invalid', 'true'));
            // composedPath: tombol tanggal yang diklik sudah diganti gambar(), jadi target tidak lagi di dalam dokumen
            document.addEventListener('click', (e) => { if (!e.composedPath().includes(wadah)) { atur(false); } });
            // Escape menutup kalender saja, bukan popup <dialog> tempatnya berada
            wadah.addEventListener('keydown', (e) => { if (e.key === 'Escape' && pop.classList.contains('on')) { e.preventDefault(); e.stopPropagation(); atur(false); tombol.focus(); } });
            tulis();
        }));
    </script>
@endonce
