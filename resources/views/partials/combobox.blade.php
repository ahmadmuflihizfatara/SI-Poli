{{-- partials/combobox.blade.php — kotak cari + dropdown pilihan (taruna, pegawai pemeriksa). Teks yang cocok disorot, pilihan bisa dikelompokkan.
     Pakai: @include('partials.combobox', [
         'id' => 'cari-x', 'name' => 'x_id', 'label' => 'Label', 'wajib' => $wajib, 'placeholder' => 'Cari ...', 'terpilih' => old('x_id'),
         'opsi' => [['id' => 1, 'nama' => 'Nama', 'sub' => 'keterangan kecil', 'grup' => 'Opsional', 'info' => ['Kunci' => 'nilai'] /* opsional */], ...],
     ])
     Nilai terpilih ada di <input type="hidden" id="{{ $name }}">; input itu memicu event "change" setiap kali pilihan berubah. --}}
@php
    $opsi = collect($opsi)->values();
    $opsiTerpilih = $opsi->first(fn ($o) => (string) $o['id'] === (string) ($terpilih ?? ''));
    $labelOpsi = fn ($o) => isset($o['sub']) ? "{$o['nama']} — {$o['sub']}" : $o['nama'];
    $grupSebelumnya = null;
@endphp
<div class="pl-field">
    <label class="pl-field-label" for="{{ $id }}">{{ $label }}{!! $wajib ?? '' !!}</label>
    {{-- ponytail: semua opsi dirender lalu disaring di browser; cukup untuk ratusan baris --}}
    <div class="pl-combo" data-combo>
        <svg class="pl-combo__ikon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
        <input class="pl-input" id="{{ $id }}" type="text" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="{{ $id }}-daftar" autocomplete="off" spellcheck="false"
            placeholder="{{ $opsi->isEmpty() ? 'Belum ada data' : $placeholder }}" value="{{ $opsiTerpilih ? $labelOpsi($opsiTerpilih) : '' }}"
            @required(! empty($wajib)) @disabled($opsi->isEmpty()) @error($name) aria-invalid="true" @enderror>
        <svg class="pl-combo__chev" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
        <input type="hidden" id="{{ $name }}" name="{{ $name }}" value="{{ $opsiTerpilih['id'] ?? '' }}">
        <ul class="pl-combo__list" id="{{ $id }}-daftar" role="listbox" aria-label="{{ $label }}" hidden>
            @foreach ($opsi as $o)
                @if (($o['grup'] ?? null) !== $grupSebelumnya)
                    @php $grupSebelumnya = $o['grup']; @endphp
                    <li class="pl-combo__grup" role="presentation" data-grup="{{ $o['grup'] }}">{{ $o['grup'] }}</li>
                @endif
                <li role="option" id="{{ $id }}-{{ $o['id'] }}" aria-selected="{{ $opsiTerpilih && $opsiTerpilih['id'] === $o['id'] ? 'true' : 'false' }}"
                    data-id="{{ $o['id'] }}" data-label="{{ $labelOpsi($o) }}" data-grup="{{ $o['grup'] ?? '' }}" data-cari="{{ mb_strtolower($o['nama'].' '.($o['sub'] ?? '')) }}"
                    @isset($o['info']) data-info="{{ json_encode($o['info']) }}" @endisset>
                    <span aria-hidden="true">{{ collect(explode(' ', preg_replace('/^(dr|drg)\.\s*/i', '', $o['nama'])))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}</span>
                    <span><b>{{ $o['nama'] }}</b>@isset($o['sub'])<small>{{ $o['sub'] }}</small>@endisset</span>
                </li>
            @endforeach
            <li class="pl-combo__kosong" role="presentation" data-kosong hidden>Tidak ada yang cocok dengan pencarian.</li>
        </ul>
    </div>
</div>

@once
    <script>
        // Combobox: ketik untuk menyaring (nama/keterangan), panah atas/bawah + Enter untuk memilih, Escape menutup
        document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('[data-combo]').forEach((combo) => {
            const cari = combo.querySelector('[role="combobox"]'), nilai = combo.querySelector('input[type="hidden"]');
            const daftar = combo.querySelector('[role="listbox"]'), kosong = daftar.querySelector('[data-kosong]');
            const opsi = [...daftar.querySelectorAll('[role="option"]')], grup = [...daftar.querySelectorAll('.pl-combo__grup')];
            let aktif = -1;

            const terlihat = () => opsi.filter((o) => !o.hidden);
            const tandai = (i) => {
                const v = terlihat();
                aktif = Math.max(-1, Math.min(i, v.length - 1));
                v.forEach((o, k) => o.classList.toggle('aktif', k === aktif));
                if (v[aktif]) { cari.setAttribute('aria-activedescendant', v[aktif].id); v[aktif].scrollIntoView({ block: 'nearest' }); } else { cari.removeAttribute('aria-activedescendant'); }
            };
            // sorot bagian teks yang cocok dengan kata pencarian
            const sorot = (el, q) => {
                const teks = el.dataset.asli ??= el.textContent, i = q ? teks.toLowerCase().indexOf(q) : -1;
                if (i < 0) { el.textContent = teks; return; }
                el.replaceChildren(teks.slice(0, i), Object.assign(document.createElement('mark'), { textContent: teks.slice(i, i + q.length) }), teks.slice(i + q.length));
            };
            const saring = (kata) => {
                const q = kata.trim().toLowerCase();
                opsi.forEach((o) => {
                    o.hidden = q !== '' && !o.dataset.cari.includes(q);
                    o.querySelectorAll('b, small').forEach((el) => sorot(el, q));
                });
                grup.forEach((g) => { g.hidden = !opsi.some((o) => !o.hidden && o.dataset.grup === g.dataset.grup); });
                kosong.hidden = terlihat().length > 0;
                tandai(0);
            };
            const buka = (b) => { daftar.hidden = !b; cari.setAttribute('aria-expanded', String(b)); if (!b) { tandai(-1); } };
            // teks yang tidak cocok dengan pilihan mana pun = belum memilih
            const periksa = () => cari.setCustomValidity(cari.value && !nilai.value ? 'Pilih salah satu dari daftar.' : '');
            const pilih = (o) => {
                nilai.value = o.dataset.id;
                cari.value = o.dataset.label;
                opsi.forEach((x) => x.setAttribute('aria-selected', String(x === o)));
                cari.removeAttribute('aria-invalid');
                periksa();
                buka(false);
                nilai.dispatchEvent(new Event('change'));
            };

            cari.addEventListener('focus', () => { saring(''); buka(true); cari.select(); });
            cari.addEventListener('blur', () => buka(false));
            cari.addEventListener('input', () => {
                if (nilai.value) { nilai.value = ''; nilai.dispatchEvent(new Event('change')); }
                periksa();
                saring(cari.value);
                buka(true);
            });
            cari.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (daftar.hidden) { saring(''); buka(true); } else { tandai(aktif + (e.key === 'ArrowDown' ? 1 : -1)); }
                } else if (e.key === 'Enter' && !daftar.hidden && terlihat()[aktif]) {
                    e.preventDefault();
                    pilih(terlihat()[aktif]);
                } else if (e.key === 'Escape' && !daftar.hidden) {
                    // tutup daftar saja, bukan popup <dialog> tempatnya berada
                    e.preventDefault();
                    e.stopPropagation();
                    buka(false);
                }
            });
            // mousedown dicegah supaya input tidak blur (dan daftar tertutup) sebelum klik terbaca
            daftar.addEventListener('mousedown', (e) => e.preventDefault());
            daftar.addEventListener('click', (e) => { const o = e.target.closest('[role="option"]'); if (o) { pilih(o); } });
        }));
    </script>
@endonce
