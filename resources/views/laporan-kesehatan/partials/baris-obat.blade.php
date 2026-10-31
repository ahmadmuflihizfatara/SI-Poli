{{-- laporan-kesehatan/partials/baris-obat.blade.php — satu baris isian obat (dipakai input-obat dan templatnya) --}}
{{-- kolom mengikuti lebar kartu: sejajar bila muat, bertumpuk di kartu sempit --}}
<div class="flex items-end gap-3 pb-3 border-b border-[var(--border)]" data-baris-obat>
    <div class="grid flex-1 min-w-0 gap-3 grid-cols-[repeat(auto-fit,minmax(12rem,1fr))]" data-kolom-obat>
        @foreach ($bagianObat as [$kunci, $labelBagian, $contoh, $wajibBagian])
            <div class="pl-field">
                <label class="pl-field-label" for="obat-{{ $i }}-{{ $kunci }}">{{ $labelBagian }}{!! $wajibBagian ? $wajib : '' !!}</label>
                {{-- terapi/obat bisa panjang: textarea satu baris yang tingginya ikut isi --}}
                @if ($kunci === 'nama')
                    <textarea class="pl-input pl-input--tumbuh" rows="1" id="obat-{{ $i }}-{{ $kunci }}" name="obat[{{ $i }}][{{ $kunci }}]" placeholder="{{ $contoh }}" @required($wajibBagian)>{{ $o[$kunci] ?? '' }}</textarea>
                @else
                    <input class="pl-input" id="obat-{{ $i }}-{{ $kunci }}" name="obat[{{ $i }}][{{ $kunci }}]" value="{{ $o[$kunci] ?? '' }}" placeholder="{{ $contoh }}" @required($wajibBagian)>
                @endif
            </div>
        @endforeach
        @php
            // nilai tersimpan selain dua pilihan baku = "Lainnya" dengan keterangannya
            $waktu = $o['waktu'] ?? null;
            $pilihanWaktu = in_array($waktu, \App\Models\Keluhan::WAKTU_OBAT, true) ? $waktu : ($waktu ? 'Lainnya' : null);
            $waktuLain = $o['waktu_lain'] ?? ($pilihanWaktu === 'Lainnya' && $waktu !== 'Lainnya' ? $waktu : '');
        @endphp
        <fieldset class="pl-field pl-obat-waktu">
            <legend class="mb-2">Waktu minum</legend>
            <div class="pl-options">
                @foreach (\App\Models\Keluhan::WAKTU_OBAT as $pilihan)
                    <label class="pl-option"><input type="radio" name="obat[{{ $i }}][waktu]" value="{{ $pilihan }}" data-waktu-obat @checked($pilihanWaktu === $pilihan)>{{ $pilihan }}</label>
                @endforeach
            </div>
            <div class="pl-obat-lain mt-2">
                <label class="pl-sr" for="obat-{{ $i }}-waktu-lain">Keterangan waktu minum lainnya</label>
                <input class="pl-input" id="obat-{{ $i }}-waktu-lain" name="obat[{{ $i }}][waktu_lain]" value="{{ $waktuLain }}" maxlength="100" placeholder="Contoh: saat demam, sebelum tidur" data-waktu-lain @required($pilihanWaktu === 'Lainnya')>
            </div>
        </fieldset>
    </div>
    <button type="button" class="pl-icon-btn flex-none" data-hapus-obat aria-label="Hapus obat ini">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.955-2.25-2.134-2.25h-3.32c-1.18 0-2.134 1.07-2.134 2.25v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
    </button>
</div>
