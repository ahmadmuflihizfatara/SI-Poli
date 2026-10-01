{{-- partials/konfirmasi.blade.php — dialog konfirmasi (desain "Pop Up Setuju Tambahkan/Hapus Akun"), design system Pulih.
     Pakai: @include('partials.konfirmasi', ['id' => 'x', 'nada' => 'primary'|'secondary', 'ikon' => 'tambah'|'hapus'|'selesai',
            'judul' => '...', 'pesan' => '...', 'tombol' => '...'] + salah satu:
            'form' => id form di halaman yang dikirim, atau 'aksi' => url + 'metode' => 'DELETE' dst.)
     Tombol pembuka: <button type="button" data-konfirmasi="x">; bila tombol itu punya atribut form, isian form dicek dulu. --}}
@php
    $warna = $nada === 'secondary'
        ? ['latar' => 'var(--secondary-50)', 'utama' => 'var(--secondary-800)', 'judul' => 'var(--secondary-800)']
        : ['latar' => 'var(--primary-50)', 'utama' => 'var(--primary-700)', 'judul' => 'var(--primary-900)'];
    $ikonPath = [
        'hapus' => 'M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'selesai' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    ][$ikon] ?? 'M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'; // tambah
@endphp

<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-judul" class="kf pl-card p-0 w-[min(26.5rem,calc(100vw-2rem))] shadow-[var(--shadow-lg)]"
    style="--kf-latar: {{ $warna['latar'] }}; --kf-utama: {{ $warna['utama'] }}">
    <div class="flex flex-col items-center gap-2 px-6 pt-12 pb-8 text-center">
        <div class="w-[6.875rem] h-[6.875rem] rounded-full flex items-center justify-center mb-8" style="background: var(--kf-latar); color: var(--kf-utama)">
            <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $ikonPath }}"/></svg>
        </div>
        <h2 id="{{ $id }}-judul" class="m-0 font-heading font-bold text-[2rem] leading-[2.5rem]" style="color: {{ $warna['judul'] }}">{{ $judul }}</h2>
        <p class="body m-0">{{ $pesan }}</p>

        @isset($aksi)
            <form id="{{ $id }}-form" method="POST" action="{{ $aksi }}">
                @csrf
                @method($metode ?? 'POST')
            </form>
        @endisset

        <div class="w-full flex justify-between gap-4 mt-14 px-5">
            <button type="button" class="kf-batal pl-btn h-8 w-[8.5rem] justify-center font-semibold" data-tutup>Batal</button>
            <button type="submit" form="{{ $form ?? $id.'-form' }}" class="kf-aksi pl-btn h-8 w-[8.5rem] justify-center font-semibold">{{ $tombol }}</button>
        </div>
    </div>
</dialog>

@once
    <style>
        .kf::backdrop { background: rgb(11 59 54 / .35); }
        .kf-batal { background: var(--surface-card); border-color: var(--kf-utama); color: var(--text); }
        .kf-batal:hover { background: var(--kf-latar); }
        .kf-aksi { background: var(--kf-utama); color: #fff; box-shadow: 0 4px 4px rgba(0,0,0,.25); }
        .kf-aksi:hover { filter: brightness(.9); color: #fff; }
    </style>
@endonce

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
