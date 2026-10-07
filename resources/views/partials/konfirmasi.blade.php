{{-- partials/konfirmasi.blade.php — dialog konfirmasi, design system Pagi (warna palet aplikasi).
     Pakai: @include('partials.konfirmasi', ['id' => 'x', 'nada' => 'primary'|'secondary', 'ikon' => 'tambah'|'hapus'|'selesai',
            'judul' => '...', 'pesan' => '...', 'tombol' => '...'] + salah satu:
            'form' => id form di halaman yang dikirim, atau 'aksi' => url + 'metode' => 'DELETE' dst.)
     Tombol pembuka: <button type="button" data-konfirmasi="x">; bila tombol itu punya atribut form, isian form dicek dulu. --}}
@php
    $ikonPath = [
        'hapus' => 'M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'selesai' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    ][$ikon] ?? 'M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'; // tambah
@endphp

<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-judul" aria-describedby="{{ $id }}-pesan" @class(['pl-dialog', 'pl-dialog--danger' => $nada === 'secondary'])>
    <div class="pl-dialog__body">
        <span class="pl-dialog__icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $ikonPath }}"/></svg>
        </span>
        <div>
            <h2 id="{{ $id }}-judul" class="pl-dialog__title">{{ $judul }}</h2>
            <p id="{{ $id }}-pesan" class="pl-dialog__text">{{ $pesan }}</p>
        </div>

        @isset($aksi)
            <form id="{{ $id }}-form" method="POST" action="{{ $aksi }}" hidden>
                @csrf
                @method($metode ?? 'POST')
            </form>
        @endisset

        <div class="pl-dialog__actions">
            <button type="button" class="pl-btn pl-btn-ghost" data-tutup>Batal</button>
            <button type="submit" form="{{ $form ?? $id.'-form' }}" @class(['pl-btn', 'pl-btn-danger' => $nada === 'secondary', 'pl-btn-primary' => $nada !== 'secondary'])>{{ $tombol }}</button>
        </div>
    </div>
</dialog>

<script>
    (() => {
        const dialog = document.getElementById(@json($id));
        document.querySelectorAll('[data-konfirmasi="{{ $id }}"]').forEach((btn) => btn.addEventListener('click', () => {
            if (btn.form && !btn.form.reportValidity()) return; // tampilkan isian yang belum valid dulu
            dialog.showModal();
        }));
        dialog.querySelector('[data-tutup]').addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
    })();
</script>
