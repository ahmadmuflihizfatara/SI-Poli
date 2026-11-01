{{-- laporan-kesehatan/partials/input-vital.blade.php — isian Kondisi Kesehatan (tanda vital + skala nyeri) untuk Tambah keluhan dan popup Catat Kontrol.
     Pakai: @include('laporan-kesehatan.partials.input-vital', ['wajib' => $wajib]). Nilai lama dari old(); aturan server di Keluhan::aturanVital().
     Tiap [data-vital] punya penanda [data-tanda] (Periksa nilai / Tinggi / Demam / Rendah) yang ditulis skrip di bawah. --}}
@php
    $batas = \App\Models\Keluhan::BATAS_VITAL;
    // Tanda vital selain tekanan darah. Rentang normal ditulis sebagai teks bantuan, bukan placeholder (placeholder tampak seperti nilai terisi).
    // [name, label, satuan, teks normal, "Rendah" bila < ini, penanda tinggi bila ≥ ini, label penanda tinggi, wajib, step]
    $vital = [
        ['suhu', 'Suhu', '°C', '36,1–37,2', 36, 37.5, 'Demam', true, '0.1'],
        ['nadi', 'Nadi', 'kali/menit', '60–100', 60, 101, 'Tinggi', true, '1'],
        ['saturasi', 'Saturasi oksigen', '%', '95–100', 95, null, null, false, '1'],
        ['pernapasan', 'Pernapasan', 'kali/menit', '12–20', 12, 21, 'Tinggi', false, '1'],
    ];
    $ikonVital = 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z';
@endphp
<div class="pl-fields">
    {{-- dua kolom angka, bukan teks bebas: "120-80" atau "12080" tidak bisa lolos --}}
    <div class="pl-field" role="group" aria-labelledby="label-tekanan" data-vital data-nama="Tekanan darah" data-satuan="mmHg" data-label-tinggi="Tinggi" data-label-rendah="Rendah">
        <span class="pl-field-label" id="label-tekanan">Tekanan darah (mmHg){!! $wajib !!}</span>
        <div class="pl-vital-td">
            <input class="pl-input" type="number" inputmode="numeric" id="sistolik" name="sistolik" value="{{ old('sistolik') }}" min="{{ $batas['sistolik'][0] }}" max="{{ $batas['sistolik'][1] }}"
                data-rendah="90" data-tinggi="140" aria-label="Sistolik" aria-describedby="tekanan-hint" required @error('sistolik') aria-invalid="true" @enderror>
            <span aria-hidden="true">/</span>
            <input class="pl-input" type="number" inputmode="numeric" id="diastolik" name="diastolik" value="{{ old('diastolik') }}" min="{{ $batas['diastolik'][0] }}" max="{{ $batas['diastolik'][1] }}"
                data-rendah="60" data-tinggi="90" aria-label="Diastolik" aria-describedby="tekanan-hint" required @error('diastolik') aria-invalid="true" @enderror>
        </div>
        <div class="pl-field-hint" id="tekanan-hint">Sistolik / diastolik. Normal: 90–120 / 60–80</div>
        <span class="pl-vital-tanda" data-tanda hidden aria-live="polite"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $ikonVital }}"/></svg><span data-tanda-teks></span></span>
    </div>
    @foreach ($vital as [$name, $label, $satuan, $normal, $rendah, $tinggi, $labelTinggi, $req, $step])
        <div class="pl-field" data-vital data-nama="{{ $label }}" data-satuan="{{ $satuan }}" data-label-tinggi="{{ $labelTinggi }}" data-label-rendah="Rendah">
            <label class="pl-field-label" for="{{ $name }}">{{ $label }} ({{ $satuan }}){!! $req ? $wajib : '' !!}</label>
            <input class="pl-input" type="number" inputmode="{{ $step === '1' ? 'numeric' : 'decimal' }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name) }}" step="{{ $step }}"
                min="{{ $batas[$name][0] }}" max="{{ $batas[$name][1] }}" data-rendah="{{ $rendah }}" @if ($tinggi) data-tinggi="{{ $tinggi }}" @endif
                aria-describedby="{{ $name }}-hint" @required($req) @error($name) aria-invalid="true" @enderror>
            <div class="pl-field-hint" id="{{ $name }}-hint">Normal: {{ $normal }}</div>
            <span class="pl-vital-tanda" data-tanda hidden aria-live="polite"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $ikonVital }}"/></svg><span data-tanda-teks></span></span>
        </div>
    @endforeach
    {{-- Numeric Rating Scale: 0 = tidak nyeri, 10 = nyeri terhebat --}}
    <fieldset class="pl-field pl-field--wide" aria-describedby="nyeri-hint">
        <legend class="mb-2">Skala nyeri (0–10)</legend>
        <div class="pl-nrs">
            @foreach (range(0, 10) as $n)
                <label class="pl-option"><input type="radio" name="skala_nyeri" value="{{ $n }}" @checked(old('skala_nyeri') === (string) $n)>{{ $n }}</label>
            @endforeach
        </div>
        <div class="pl-nrs-ujung pl-field-hint" id="nyeri-hint"><span>0 · Tidak nyeri</span><span>10 · Nyeri terhebat</span></div>
    </fieldset>
</div>

<script>
    // Penanda tanda vital: "Periksa nilai" bila di luar batas isian, tinggi bila ≥ data-tinggi, "Rendah" bila < data-rendah
    document.querySelectorAll('[data-vital]').forEach((wadah) => {
        const isian = [...wadah.querySelectorAll('input')], tanda = wadah.querySelector('[data-tanda]');
        const perbarui = () => {
            const terisi = isian.filter((i) => i.value !== '');
            const label = !terisi.length ? ''
                : terisi.some((i) => !i.validity.valid) ? 'Periksa nilai'
                : terisi.some((i) => i.dataset.tinggi && +i.value >= +i.dataset.tinggi) ? wadah.dataset.labelTinggi
                : terisi.some((i) => i.dataset.rendah && +i.value < +i.dataset.rendah) ? wadah.dataset.labelRendah : '';
            tanda.hidden = !label;
            tanda.querySelector('[data-tanda-teks]').textContent = label;
        };
        wadah.addEventListener('input', perbarui);
        perbarui();
    });
</script>
