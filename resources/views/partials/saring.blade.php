{{-- partials/saring.blade.php — filter pilihan ganda (pengganti <select>), design system Pagi.
     Pakai: @include('partials.saring', ['kunci' => 'status', 'label' => 'Status', 'opsi' => ['ringan' => 'Ringan', ...], 'warna' => ['ringan' => '#6fa89d']])
     'warna' (opsional) = titik warna per opsi. Nilai tercentang dibaca skrip halaman dari input[name="saring-{kunci}"];
     buka/tutup menu & ringkasan tombol diurus skrip di partial ini (sekali per halaman). --}}
<div class="pl-saring" data-saring="{{ $kunci }}">
    <button type="button" class="pl-saring__tombol" aria-haspopup="true" aria-expanded="false" aria-controls="saring-{{ $kunci }}">
        {{ $label }} <b data-ringkas>Semua</b>
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
    </button>
    <fieldset id="saring-{{ $kunci }}" class="pl-saring__menu">
        <legend class="sr-only">Filter {{ $label }}</legend>
        @foreach ($opsi as $nilai => $teks)
            <label class="pl-saring__opsi">
                <input type="checkbox" name="saring-{{ $kunci }}" value="{{ $nilai }}">
                <span class="pl-saring__cek" aria-hidden="true"></span>
                @isset($warna[$nilai])<span class="pl-saring__titik" style="--titik: {{ $warna[$nilai] }}" aria-hidden="true"></span>@endisset
                {{ $teks }}
                <small data-jumlah="{{ $nilai }}"></small>
            </label>
        @endforeach
    </fieldset>
</div>

@once
    <script>
        // Menu saring: klik tombol = buka/tutup (satu menu terbuka sekaligus), klik di luar / Esc = tutup;
        // ringkasan tombol = "Semua", nama opsi bila satu, atau "N dipilih"
        document.addEventListener('DOMContentLoaded', () => {
            const semua = [...document.querySelectorAll('[data-saring]')];
            const atur = (s, buka) => {
                s.querySelector('.pl-saring__menu').classList.toggle('on', buka);
                s.querySelector('.pl-saring__tombol').setAttribute('aria-expanded', String(buka));
            };
            const ringkas = (s) => {
                const dipilih = [...s.querySelectorAll('input:checked')];
                s.classList.toggle('is-aktif', dipilih.length > 0);
                s.querySelector('[data-ringkas]').textContent = dipilih.length === 0 ? 'Semua'
                    : dipilih.length === 1 ? dipilih[0].closest('label').textContent.replace(/\d+\s*$/, '').trim() : `${dipilih.length} dipilih`;
            };
            semua.forEach((s) => {
                s.querySelector('.pl-saring__tombol').addEventListener('click', () => {
                    const buka = s.querySelector('.pl-saring__tombol').getAttribute('aria-expanded') !== 'true';
                    semua.forEach((x) => atur(x, x === s && buka));
                });
                s.addEventListener('change', () => ringkas(s));
                s.ringkas = () => ringkas(s);
            });
            document.addEventListener('click', (e) => { if (!e.target.closest('[data-saring]')) { semua.forEach((s) => atur(s, false)); } });
            document.addEventListener('keydown', (e) => {
                const terbuka = semua.find((s) => s.querySelector('.pl-saring__menu.on'));
                if (e.key === 'Escape' && terbuka) { atur(terbuka, false); terbuka.querySelector('.pl-saring__tombol').focus(); }
            });
        });
    </script>
@endonce
