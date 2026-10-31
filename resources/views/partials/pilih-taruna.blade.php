{{-- partials/pilih-taruna.blade.php — isi panel "Informasi Umum": cari taruna (nama/NPM) lalu pilih dari dropdown, data umumnya tampil otomatis.
     Pakai: @include('partials.pilih-taruna', ['taruna' => $taruna, 'wajib' => $wajib]) --}}
{{-- ponytail: data taruna hanya dari tabel taruna; form tidak bisa membuat taruna baru --}}
@include('partials.combobox', [
    'id' => 'cari-taruna', 'name' => 'taruna_id', 'label' => 'Taruna', 'wajib' => $wajib,
    'placeholder' => 'Cari nama atau NPM taruna', 'terpilih' => old('taruna_id'),
    'opsi' => $taruna->map(fn ($t) => [
        'id' => $t->id, 'nama' => $t->nama, 'sub' => $t->npm,
        'info' => ['NPM' => $t->npm, 'Kelas' => $t->kelas, 'Tingkat' => 'Tingkat '.$t->tingkat, 'Jenis kelamin' => $t->jenis_kelamin, 'Kamar' => $t->kamar],
    ]),
])
<dl id="info-taruna" class="m-0 grid grid-cols-[auto_1fr] gap-x-6 gap-y-3 body" aria-live="polite"></dl>
<script>
    (() => {
        const idTaruna = document.getElementById('taruna_id'), infoTaruna = document.getElementById('info-taruna');
        const tampilkanInfo = () => {
            const info = JSON.parse(document.querySelector(`#cari-taruna-daftar [data-id="${idTaruna.value}"]`)?.dataset.info || '{}');
            infoTaruna.replaceChildren(...Object.entries(info).flatMap(([k, v]) => {
                const dt = document.createElement('dt'), dd = document.createElement('dd');
                dt.className = 'text-muted body-sm'; dt.textContent = k;
                dd.className = 'm-0 font-medium'; dd.textContent = v;
                return [dt, dd];
            }));
        };
        idTaruna.addEventListener('change', tampilkanInfo);
        tampilkanInfo();
    })();
</script>
