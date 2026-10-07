{{-- partials/pilih-taruna.blade.php — isi panel "Informasi Umum": pilih taruna dari database, data umumnya tampil otomatis.
     Pakai: @include('partials.pilih-taruna', ['taruna' => $taruna, 'wajib' => $wajib]) --}}
<div class="pl-field">
    <label class="pl-field-label" for="taruna_id">Taruna{!! $wajib !!}</label>
    {{-- ponytail: data taruna hanya dari tabel taruna; form tidak bisa membuat taruna baru --}}
    <select class="pl-input" id="taruna_id" name="taruna_id" required>
        <option value="">{{ $taruna->isEmpty() ? 'Belum ada data taruna' : 'Pilih taruna' }}</option>
        @foreach ($taruna as $t)
            <option value="{{ $t->id }}" @selected(old('taruna_id') == $t->id)
                data-info="{{ json_encode(['NPM' => $t->npm, 'Kelas' => $t->kelas, 'Tingkat' => 'Tingkat '.$t->tingkat, 'Jenis kelamin' => $t->jenis_kelamin, 'Kamar' => $t->kamar]) }}">
                {{ $t->nama }} — {{ $t->npm }}
            </option>
        @endforeach
    </select>
</div>
<dl id="info-taruna" class="m-0 grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 body" aria-live="polite"></dl>
<script>
    (() => {
        const pilihTaruna = document.getElementById('taruna_id');
        const infoTaruna = document.getElementById('info-taruna');
        const tampilkanInfo = () => {
            const info = JSON.parse(pilihTaruna.selectedOptions[0].dataset.info || '{}');
            infoTaruna.replaceChildren(...Object.entries(info).flatMap(([k, v]) => {
                const dt = document.createElement('dt'), dd = document.createElement('dd');
                dt.className = 'text-muted body-sm'; dt.textContent = k;
                dd.className = 'm-0 font-medium'; dd.textContent = v;
                return [dt, dd];
            }));
        };
        pilihTaruna.addEventListener('change', tampilkanInfo);
        tampilkanInfo();
    })();
</script>
