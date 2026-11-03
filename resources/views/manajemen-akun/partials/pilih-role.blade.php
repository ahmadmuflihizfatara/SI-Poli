{{-- manajemen-akun/partials/pilih-role.blade.php — dropdown Role (desain dropdown Tambah Keluhan) di Tambah Akun & ubah Informasi Akun.
     Pakai: @include('manajemen-akun.partials.pilih-role', ['terpilih' => role terpilih]) --}}
<div class="pl-field">
    @include('partials.combobox', [
        'id' => 'pilih-role', 'name' => 'role', 'label' => 'Role', 'wajib' => '<span class="pl-req" aria-hidden="true">*</span>',
        'placeholder' => 'Pilih role', 'terpilih' => $terpilih,
        'opsi' => collect(\App\Http\Controllers\AkunController::ROLE)->map(fn ($label, $nilai) => ['id' => $nilai, 'nama' => $label])->values(),
    ])
    <div class="pl-field-hint">Asisten Kesehatan dan Pengasuh hanya dapat melihat Dashboard dan Laporan Kesehatan, tanpa menambah atau mengubah data.</div>
</div>
