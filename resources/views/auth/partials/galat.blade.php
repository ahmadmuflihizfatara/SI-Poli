{{-- auth/partials/galat.blade.php — semua pesan galat satu kolom, tepat di bawah kolomnya.
     Pakai: @include('auth.partials.galat', ['kolom' => 'password']). id "{kolom}-galat" dirujuk aria-describedby kolom. --}}
@if ($errors->has($kolom))
    <ul class="pl-field-error" id="{{ $kolom }}-galat">
        @foreach ($errors->get($kolom) as $pesan)
            <li>{{ $pesan }}</li>
        @endforeach
    </ul>
@endif
