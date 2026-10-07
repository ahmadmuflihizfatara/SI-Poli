{{-- kejadian-luar-biasa/kejadian-luar-biasa.blade.php — tata letak "Kejadian luar biasa" design system Pagi (warna palet aplikasi).
     Form tambah tampil lewat ?tambah=1 (atau otomatis saat validasi gagal). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Kejadian Luar Biasa'])
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php $tambah = (request()->boolean('tambah') || $errors->any()) && auth()->user()->can('tambah-data'); @endphp

    <main class="pl-main">

        <header class="pl-page-head">
            <div>
                <h1 class="pl-page-title">Kejadian Luar Biasa</h1>
                <p class="pl-page-sub">Laporan dan data kejadian luar biasa yang terjadi</p>
            </div>
            @if (! $tambah && auth()->user()->can('tambah-data'))
                <div class="pl-page-actions">
                    <a href="{{ route('klb.index', ['tambah' => 1]) }}" class="pl-btn pl-btn-primary">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah Kejadian Luar Biasa Baru
                    </a>
                </div>
            @endif
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        @if ($tambah)
            <form method="POST" action="{{ route('klb.store') }}" class="pl-panel max-w-[45rem]" aria-labelledby="judul-tambah">
                @csrf
                <h2 class="pl-panel__title" id="judul-tambah">Informasi Kejadian Luar Biasa</h2>
                @if ($errors->any())
                    <div class="pl-alert pl-alert-accent" role="alert">{{ $errors->first() }}</div>
                @endif
                <div class="pl-fields">
                    <div class="pl-field pl-field--wide">
                        <label class="pl-field-label" for="nama">Nama Kejadian Luar Biasa</label>
                        <input class="pl-input" id="nama" name="nama" value="{{ old('nama') }}" maxlength="150" required autofocus>
                    </div>
                    <div class="pl-field pl-field--wide">
                        <label class="pl-field-label" for="deskripsi">Deskripsi Kejadian Luar Biasa</label>
                        <textarea class="pl-input" id="deskripsi" name="deskripsi" maxlength="2000" required>{{ old('deskripsi') }}</textarea>
                    </div>
                </div>
                <div class="pl-form-actions">
                    <a href="{{ route('klb.index') }}" class="pl-btn pl-btn-ghost">Batal</a>
                    <button type="submit" class="pl-btn pl-btn-primary">Tambah</button>
                </div>
            </form>
        @endif

        <section aria-labelledby="judul-riwayat" class="pl-section">
            <h2 id="judul-riwayat" class="pl-section__title">Riwayat Kejadian Luar Biasa</h2>
            <div class="grid grid-cols-[repeat(auto-fill,minmax(20rem,1fr))] gap-6">
                @forelse ($klb as $k)
                    <a href="{{ route('klb.show', $k) }}" class="pl-panel !gap-3">
                        <span @class(['pl-badge self-start', 'pl-badge-primary' => $k->selesai(), 'pl-badge-accent' => ! $k->selesai()])>{{ $k->status }}</span>
                        <h3 class="pl-panel__title">{{ $k->nama }}</h3>
                        <p class="pl-panel__text line-clamp-3">{{ $k->deskripsi }}</p>
                        <p class="pl-panel__sub mt-2">{{ $k->created_at->locale('id')->translatedFormat('j M Y') }} · {{ $k->keluhan_count }} keluhan</p>
                    </a>
                @empty
                    <p class="col-span-full pl-panel m-0 text-center body text-muted">Belum ada kejadian luar biasa yang dilaporkan.</p>
                @endforelse
            </div>
        </section>
    </main>
</body>
</html>
