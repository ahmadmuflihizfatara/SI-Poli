{{-- partials/pilih-bagian.blade.php — khusus admin: pindah tampilan Dashboard / Laporan Kesehatan antara bagian Perawat dan Psikolog.
     Pakai: @include('partials.pilih-bagian', ['aktif' => 'perawat'|'psikolog']). Pilihan diingat di session (Controller::bagian). --}}
@can('admin')
    <nav aria-label="Pilih tampilan bagian" class="inline-flex items-center gap-1 p-1 rounded-full bg-primary-50 self-start">
        @foreach (['perawat' => 'Perawat', 'psikolog' => 'Psikolog'] as $nilai => $label)
            <a href="{{ request()->fullUrlWithQuery(['bagian' => $nilai]) }}" @if ($aktif === $nilai) aria-current="page" @endif
                @class([
                    'label h-9 px-5 rounded-full inline-flex items-center no-underline transition-colors focus-visible:outline-none focus-visible:shadow-[var(--focus-ring)]',
                    'bg-primary-700 !text-white' => $aktif === $nilai,
                    '!text-primary-700 hover:bg-white' => $aktif !== $nilai,
                ])>{{ $label }}</a>
        @endforeach
    </nav>
@endcan
