{{-- auth/partials/sorotan.blade.php — panel kanan layar masuk / lupa kata sandi (hero Pagi: judul, deskripsi, chip opsional) --}}
<aside class="pl-hero hidden lg:flex flex-[1_1_30rem] min-w-0 min-h-0">
    <h2 class="pl-hero-title">{{ $judul }}</h2>
    <p class="pl-hero-lede">{{ $teks }}</p>
    @if (! empty($chip))
        <ul class="pl-hero-chips">
            @foreach ($chip as [$kelas, $label])
                <li class="pl-badge pl-badge-lg {{ $kelas }}">{{ $label }}</li>
            @endforeach
        </ul>
    @endif
</aside>
