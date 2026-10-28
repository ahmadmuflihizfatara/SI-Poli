{{-- auth/partials/status.blade.php — notifikasi layar masuk: sukses (session status, ✓) dan ringkasan galat (⚠).
     Pesan galat per kolom tetap tampil di bawah kolomnya (auth.partials.galat). --}}
@if (session('status'))
    <p class="pl-auth-alert pl-auth-alert--sukses" role="status">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <span>{{ session('status') }}</span>
    </p>
@endif
@if ($errors->any())
    <p class="pl-auth-alert pl-auth-alert--galat" role="alert">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
        <span>{{ $errors->count() === 1 ? '1 isian perlu diperbaiki.' : $errors->count().' isian perlu diperbaiki.' }} Lihat keterangan di bawah kolomnya.</span>
    </p>
@endif
