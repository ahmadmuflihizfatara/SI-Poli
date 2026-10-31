{{-- laporan-kesehatan/partials/input-obat.blade.php — isian terapi terstruktur: satu baris per obat (terapi/obat, aturan pakai, waktu minum).
     Pakai: @include('laporan-kesehatan.partials.input-obat', ['obat' => old('obat', $keluhan->daftarObat()), 'label' => '...', 'wajib' => $wajib])
     'kelasLabel' opsional untuk gaya legend (bawaan: label isian biasa). --}}
@php
    $obat = array_values($obat ?: [[]]);
    // [kunci, label, placeholder, wajib]
    $bagianObat = [
        ['nama', 'Terapi/obat yang diberikan', 'Paracetamol 500 mg', true],
        ['aturan', 'Aturan pakai', '3×1 sehari', false],
    ];
@endphp

<fieldset class="pl-field pl-field--wide">
    <legend class="{{ $kelasLabel ?? 'mb-2' }}">{{ $label }}{!! $wajib !!}</legend>
    <div class="flex flex-col gap-3" id="daftar-obat">
        @foreach ($obat as $i => $o)
            @include('laporan-kesehatan.partials.baris-obat', ['i' => $i, 'o' => $o])
        @endforeach
    </div>
    <button type="button" class="pl-btn pl-btn-ghost self-start" id="tambah-obat">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Tambah terapi/obat
    </button>
</fieldset>

<template id="templat-obat">
    @include('laporan-kesehatan.partials.baris-obat', ['i' => '__i__', 'o' => []])
</template>

<script>
    (() => {
        const daftar = document.getElementById('daftar-obat'), templat = document.getElementById('templat-obat');
        let ke = {{ count($obat) }};
        document.getElementById('tambah-obat').addEventListener('click', () => {
            daftar.insertAdjacentHTML('beforeend', templat.innerHTML.replaceAll('__i__', ke++));
            daftar.lastElementChild.querySelector('textarea, input').focus();
        });
        // baris terakhir tidak dihapus, cukup dikosongkan
        daftar.addEventListener('click', (e) => {
            const tombol = e.target.closest('[data-hapus-obat]');
            if (!tombol) return;
            const baris = tombol.closest('[data-baris-obat]');
            if (daftar.children.length > 1) baris.remove();
            else baris.querySelectorAll('input, textarea').forEach((isian) => { isian.value = ''; });
        });
        // "Lainnya" mewajibkan keterangan waktu minum dan langsung memfokuskannya
        daftar.addEventListener('change', (e) => {
            if (!e.target.matches('[data-waktu-obat]')) return;
            const lain = e.target.closest('[data-baris-obat]').querySelector('[data-waktu-lain]');
            lain.required = e.target.value === 'Lainnya';
            if (lain.required) lain.focus();
        });
        // terapi/obat satu baris: Enter tidak membuat baris baru (teks tetap dibungkus otomatis)
        daftar.addEventListener('keydown', (e) => { if (e.key === 'Enter' && e.target.matches('.pl-input--tumbuh')) { e.preventDefault(); } });
        // cadangan untuk browser tanpa field-sizing: content (mis. Firefox): tinggi textarea ikut isi
        if (!CSS.supports('field-sizing', 'content')) {
            const ukur = (t) => { t.style.height = 'auto'; t.style.height = `${t.scrollHeight + 2}px`; };
            daftar.addEventListener('input', (e) => { if (e.target.matches('.pl-input--tumbuh')) { ukur(e.target); } });
            daftar.querySelectorAll('.pl-input--tumbuh').forEach(ukur);
        }
    })();
</script>
