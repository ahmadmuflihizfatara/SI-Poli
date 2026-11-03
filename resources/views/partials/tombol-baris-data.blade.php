{{-- partials/tombol-baris-data.blade.php — tombol Ubah & Hapus di kolom Tindakan (Data Taruna, Data Pegawai).
     Pakai: @include('partials.tombol-baris-data', ['nama' => ..., 'ubah' => [data baris + 'url' PUT], 'hapus' => url DELETE]);
     perilakunya dari partials.form-data-dialog. --}}
<span class="inline-flex items-center gap-2">
    <button type="button" class="pl-icon-btn" aria-label="Ubah data {{ $nama }}" title="Ubah data" data-ubah="{{ json_encode($ubah) }}">
        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
    </button>
    <button type="button" class="pl-icon-btn pl-icon-btn--accent" aria-label="Hapus data {{ $nama }}" title="Hapus data"
        data-konfirmasi="konfirmasi-hapus" data-hapus="{{ json_encode(['url' => $hapus, 'nama' => $nama]) }}">
        <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.955-2.25-2.134-2.25h-3.32c-1.18 0-2.134 1.07-2.134 2.25v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
    </button>
</span>
