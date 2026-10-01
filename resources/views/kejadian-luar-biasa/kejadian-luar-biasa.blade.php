{{-- kejadian-luar-biasa/kejadian-luar-biasa.blade.php — mengikuti desain "Kejadian Luar Biasa" Normal / Tambah (PNG), design system Pulih.
     Form tambah tampil lewat ?tambah=1 (atau otomatis saat validasi gagal). --}}
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['judul' => 'Kejadian Luar Biasa'])
    <style>
        .klb-h { margin: 0; font: 600 1.375rem/1.875rem var(--font-heading); color: var(--primary-700); }
        .klb-kartu { background: var(--surface-card); border: 1px solid var(--neutral-400); border-radius: var(--radius-sm); }
        a.klb-kartu { transition: border-color .15s, box-shadow .15s; }
        a.klb-kartu:hover { border-color: var(--primary-400); box-shadow: var(--shadow-lg); }
        a.klb-kartu:focus-visible { outline: 2px solid transparent; box-shadow: var(--focus-ring); }
        .klb-status { min-width: 5.875rem; justify-content: center; border-radius: var(--radius-sm); font-weight: 500; }
    </style>
</head>
<body class="overflow-x-hidden">

    @include('partials.sidebar')

    @php $tambah = (request()->boolean('tambah') || $errors->any()) && auth()->user()->can('tambah-data'); @endphp

    <main class="ml-[6.25rem] peer-[.is-open]:ml-[16.25rem] transition-[margin] duration-200 min-h-screen flex flex-col gap-5 pr-4 py-6">

        <header class="flex flex-col gap-1">
            <h1 class="h1 m-0 text-primary-900">Kejadian Luar Biasa</h1>
            <p class="body-lg m-0 font-medium">Laporan dan data kejadian luar biasa yang terjadi</p>
        </header>

        @if (session('status'))
            <div class="pl-alert pl-alert-info" role="status">{{ session('status') }}</div>
        @endif

        @if ($tambah)
            <form method="POST" action="{{ route('klb.store') }}" class="klb-kartu w-full max-w-[18rem] p-4 flex flex-col gap-3">
                @csrf
                <h2 class="m-0 font-heading font-semibold text-[1rem] leading-6 text-primary-700">Informasi Kejadian Luar Biasa</h2>
                @if ($errors->any())
                    <div class="pl-alert pl-alert-accent !py-2" role="alert">{{ $errors->first() }}</div>
                @endif
                <div class="pl-field">
                    <label class="pl-field-label" for="nama">Nama Kejadian Luar Biasa</label>
                    <input class="pl-input" id="nama" name="nama" value="{{ old('nama') }}" maxlength="150" required autofocus>
                </div>
                <div class="pl-field">
                    <label class="pl-field-label" for="deskripsi">Deskripsi Kejadian Luar Biasa</label>
                    <textarea class="pl-input" id="deskripsi" name="deskripsi" maxlength="2000" required>{{ old('deskripsi') }}</textarea>
                </div>
                <div class="flex justify-end items-center gap-2 mt-1">
                    <a href="{{ route('klb.index') }}" class="pl-btn pl-btn-ghost h-8 px-3">Batal</a>
                    <button type="submit" class="pl-btn pl-btn-primary h-8 px-6">Tambah</button>
                </div>
            </form>
        @elseif (auth()->user()->can('tambah-data'))
            <a href="{{ route('klb.index', ['tambah' => 1]) }}" class="pl-btn pl-btn-primary self-start h-9 shadow-[0_4px_4px_rgba(0,0,0,0.25)]">
                <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambah Kejadian Luar Biasa Baru
            </a>
        @endif

        <section aria-labelledby="judul-riwayat" class="flex flex-col gap-3">
            <h2 id="judul-riwayat" class="klb-h">Riwayat Kejadian Luar Biasa</h2>
            <div class="grid grid-cols-[repeat(auto-fill,minmax(14.5rem,1fr))] gap-5">
                @forelse ($klb as $k)
                    <a href="{{ route('klb.show', $k) }}" class="klb-kartu no-underline !text-[var(--text)] px-3 py-3 flex flex-col gap-2">
                        <h3 class="m-0 font-heading font-medium text-[1.0625rem] leading-6 text-primary-700">{{ $k->nama }}</h3>
                        <div class="px-1.5 flex flex-col gap-1">
                            <p class="body-sm m-0 line-clamp-3">{{ $k->deskripsi }}</p>
                            <p class="caption m-0 text-muted">{{ $k->created_at->locale('id')->translatedFormat('j M Y') }} · {{ $k->keluhan_count }} keluhan</p>
                        </div>
                        <span @class(['pl-badge klb-status mt-auto self-end', 'pl-badge-primary' => $k->selesai(), 'bg-secondary-50 text-secondary-800' => ! $k->selesai()])>{{ $k->status }}</span>
                    </a>
                @empty
                    <p class="col-span-full klb-kartu m-0 py-8 text-center body text-muted">Belum ada kejadian luar biasa yang dilaporkan.</p>
                @endforelse
            </div>
        </section>
    </main>

</body>
</html>
