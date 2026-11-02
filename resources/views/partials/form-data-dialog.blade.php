{{-- partials/form-data-dialog.blade.php — perilaku popup tambah/ubah satu baris data (Data Taruna, Data Pegawai).
     Pakai: @include('partials.form-data-dialog', ['dialog' => id <dialog>, 'judul' => 'Data Taruna']).
     [data-tambah] = popup kosong ke form[data-tambah-url]; [data-ubah='{json}'] = popup berisi data baris, json memuat id & url (PUT).
     Isian bernama "_..." (token, _method, _ubah) tidak ikut dikosongkan/diisi. --}}
<script>
    (() => {
        const dialog = document.getElementById(@json($dialog)), form = dialog.querySelector('form');
        const buka = (data) => {
            dialog.querySelector('[role="alert"]')?.remove();
            form.action = data ? data.url : form.dataset.tambahUrl;
            form.elements._method.disabled = !data;
            form.elements._ubah.value = data ? data.id : '';
            dialog.querySelector('.pl-dialog__title').textContent = (data ? 'Ubah ' : 'Tambah ') + @json($judul);
            [...form.elements].filter((el) => el.name && !el.name.startsWith('_')).forEach((el) => { el.value = data?.[el.name] ?? ''; });
            dialog.showModal();
        };
        document.querySelector('[data-tambah]').addEventListener('click', () => buka(null));
        document.querySelectorAll('[data-ubah]').forEach((b) => b.addEventListener('click', () => buka(JSON.parse(b.dataset.ubah))));
        dialog.querySelector('[data-tutup]').addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); });
        // gagal validasi: popup terbuka lagi dengan isian lama
        if (dialog.dataset.buka) { dialog.showModal(); }
    })();
</script>
