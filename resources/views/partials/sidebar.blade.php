{{-- partials/sidebar.blade.php — SideNav design system Pagi.
     Pakai: @include('partials.sidebar') lalu beri konten utama class "pl-main" (harus sibling setelah sidebar).
     Tambah/ubah menu cukup di array $menu di bawah. Ikon = atribut "d" path SVG (heroicons outline).
     'gate' (opsional) = nama Gate yang wajib dimiliki pengguna agar menu tampil.
     'aktif' (opsional) = pola nama route yang ikut menandai menu aktif, mis. halaman tambah/ubah. --}}
@php
    $menu = [
        ['label' => 'Dashboard',         'route' => 'dashboard',               'icon' => 'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
        ['label' => 'Laporan Kesehatan', 'route' => 'laporan-kesehatan.index', 'aktif' => 'laporan-kesehatan.*', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['label' => 'Riwayat Kesehatan', 'route' => 'riwayat-kesehatan.index', 'aktif' => 'riwayat-kesehatan.*', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-2.636-6.364L21 8m0 0V3m0 5h-5'],
        ['label' => 'Pemeriksaan Kesehatan', 'route' => 'pemeriksaan-kesehatan.index', 'gate' => 'bagian-perawat', 'aktif' => 'pemeriksaan-kesehatan.*', 'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
        ['label' => 'Kejadian Luar Biasa',   'route' => 'klb.index', 'gate' => 'bagian-perawat', 'aktif' => 'klb.*', 'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z'],
    ];
    $pengguna = auth()->user();
    $inisialPengguna = collect(preg_split('/\s+/', trim($pengguna->name)))->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
@endphp

<aside id="sidebar" aria-label="Navigasi utama" class="pl-side is-open">

    <div class="pl-side__top">
        <a href="{{ route('dashboard') }}" class="pl-side__brand">
            <img src="{{ asset('images/logo-poltek.png') }}" alt="Logo Politeknik Siber dan Sandi Negara">
            <span><b>SI-Poliklinik</b><span>Poliklinik Poltek SSN</span></span>
        </a>
        <button id="sidebar-toggle" type="button" class="pl-side__toggle" aria-label="Buka/tutup sidebar" aria-expanded="true" title="Buka/tutup menu">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
        </button>
    </div>

    <nav class="pl-side__group">
        @foreach ($menu as $item)
            {{-- 'gate' (opsional) = menu hanya untuk bagian tertentu, mis. psikolog tidak melihat Pemeriksaan & KLB --}}
            @continue(isset($item['gate']) && $pengguna->cannot($item['gate']))
            @php $aktif = request()->routeIs($item['aktif'] ?? $item['route']); @endphp
            <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}" @class(['pl-side__link', 'is-active' => $aktif]) @if ($aktif) aria-current="page" @endif>
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $item['icon'] }}"/></svg>
                <span class="pl-side__text">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    @can('admin')
        @php
            // Titik penanda: ada permintaan ubah kata sandi yang menunggu persetujuan
            $menungguSandi = \App\Models\User::whereNotNull('sandi_diminta_at')->exists();
            $menuAdmin = [
                ['label' => 'Manajemen Akun', 'route' => 'akun.index', 'aktif' => 'akun.*', 'titik' => $menungguSandi, 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
                ['label' => 'Log Sistem', 'route' => 'log.index', 'aktif' => 'log.*', 'titik' => false, 'icon' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
            ];
        @endphp
        <nav class="pl-side__group" aria-label="Menu admin">
            <p class="pl-side__label">Admin</p>
            @foreach ($menuAdmin as $item)
                @php $aktif = request()->routeIs($item['aktif']); @endphp
                <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}{{ $item['titik'] ? ' (ada permintaan ubah kata sandi)' : '' }}"
                    @class(['pl-side__link', 'is-active' => $aktif]) @if ($aktif) aria-current="page" @endif>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $item['icon'] }}"/></svg>
                    <span class="pl-side__text">{{ $item['label'] }}</span>
                    @if ($item['titik'])
                        <span class="pl-side__dot" aria-hidden="true"></span>
                    @endif
                </a>
            @endforeach
        </nav>
    @endcan

    <div class="pl-side__foot">
        {{-- Profil membuka halaman Pengaturan (tema gelap/terang) --}}
        <a href="{{ route('pengaturan') }}" class="pl-side__user" title="Pengaturan · {{ $pengguna->name }}" @if (request()->routeIs('pengaturan')) aria-current="page" @endif>
            <span class="pl-avatar pl-avatar--sm" aria-hidden="true">{{ $inisialPengguna }}</span>
            <span class="pl-side__text min-w-0"><b>{{ $pengguna->name }}</b><span>{{ \App\Http\Controllers\AkunController::ROLE[$pengguna->role] ?? $pengguna->role }}</span></span>
        </a>
        <a href="{{ route('logout') }}" title="Keluar" class="pl-side__link">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
            <span class="pl-side__text">Keluar</span>
        </a>
    </div>
</aside>

<script>
    (() => {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebar-toggle');
        const set = (open) => {
            sidebar.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open);
        };
        // Status buka/tutup diingat per browser; bawaan terbuka kecuali layar sempit
        try {
            const simpan = localStorage.getItem('sidebar-open');
            set(simpan === null ? matchMedia('(min-width: 768px)').matches : simpan === '1');
        } catch (e) {}
        toggle.addEventListener('click', () => {
            const open = !sidebar.classList.contains('is-open');
            set(open);
            try { localStorage.setItem('sidebar-open', open ? '1' : '0'); } catch (e) {}
        });
    })();
</script>
