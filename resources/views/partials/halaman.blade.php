{{-- partials/halaman.blade.php — navigasi halaman di kaki tabel (TableFoot Pagi): "Menampilkan x–y dari z" + sebelumnya/berikutnya.
     Pakai: @include('partials.halaman', ['data' => $paginator]) dengan paginator hasil ->paginate(). --}}
@if ($data->hasPages())
    <nav class="flex flex-wrap items-center gap-3" aria-label="Halaman tabel">
        <span>Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }}</span>
        @foreach ([['Halaman sebelumnya', $data->previousPageUrl(), 'M15.75 19.5 8.25 12l7.5-7.5', 'prev'], ['Halaman berikutnya', $data->nextPageUrl(), 'm8.25 4.5 7.5 7.5-7.5 7.5', 'next']] as [$label, $url, $ikon, $rel])
            @if ($url)
                <a href="{{ $url }}" rel="{{ $rel }}" class="pl-icon-btn" aria-label="{{ $label }}" title="{{ $label }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $ikon }}"/></svg>
                </a>
            @else
                <span class="pl-icon-btn opacity-50 pointer-events-none" aria-hidden="true">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $ikon }}"/></svg>
                </span>
            @endif
        @endforeach
    </nav>
@endif
