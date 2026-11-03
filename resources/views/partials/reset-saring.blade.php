{{-- partials/reset-saring.blade.php — tombol "Reset filter" untuk toolbar ber-filter server (Data Taruna, Data Pegawai, Manajemen Akun).
     Pakai: @include('partials.reset-saring', ['url' => route(...), 'aktif' => ada pencarian / filter]); nonaktif bila tidak ada yang disaring. --}}
@php $ikon = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>'; @endphp
@if ($aktif)
    <a href="{{ $url }}" class="pl-btn pl-btn-ghost">{!! $ikon !!} Reset filter</a>
@else
    <button type="button" class="pl-btn pl-btn-ghost" disabled>{!! $ikon !!} Reset filter</button>
@endif
