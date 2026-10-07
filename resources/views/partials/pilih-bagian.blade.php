{{-- partials/pilih-bagian.blade.php — khusus admin: pindah tampilan Dashboard / Laporan Kesehatan antara bagian Perawat dan Psikolog.
     Pakai: @include('partials.pilih-bagian', ['aktif' => 'perawat'|'psikolog']). Pilihan diingat di session (Controller::bagian). --}}
@can('admin')
    <nav aria-label="Pilih tampilan bagian" class="pl-segment">
        @foreach (['perawat' => 'Kesehatan', 'psikolog' => 'Psikologi'] as $nilai => $label)
            <a href="{{ request()->fullUrlWithQuery(['bagian' => $nilai]) }}" @if ($aktif === $nilai) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>
@endcan
