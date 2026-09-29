{{-- partials/sidebar.blade.php
     Pakai: @include('partials.sidebar') lalu beri konten utama class
     "ml-[6.25rem] peer-[.is-open]:ml-[16.25rem]" (harus sibling setelah sidebar).
     Tambah/ubah menu cukup di array $menu di bawah. Ikon = atribut "d" path SVG (heroicons outline).
     'aktif' (opsional) = pola nama route yang ikut menandai menu aktif, mis. halaman tambah/ubah. --}}
@php
    $menu = [
        ['label' => 'Dashboard',         'route' => 'dashboard',               'icon' => 'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
        ['label' => 'Laporan Kesehatan', 'route' => 'laporan-kesehatan.index', 'aktif' => 'laporan-kesehatan.*', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['label' => 'Pemeriksaan Kesehatan', 'route' => 'pemeriksaan-kesehatan.index', 'aktif' => 'pemeriksaan-kesehatan.*', 'icon' => 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
    ];
@endphp

<aside id="sidebar" aria-label="Navigasi utama"
    class="group peer fixed z-20 top-2.5 bottom-2.5 left-4 w-16 [&.is-open]:w-56 flex flex-col gap-2 px-2.5 py-4 bg-primary-700 text-white rounded-[1.25rem] shadow-[0_4px_4px_rgba(0,0,0,0.25)] overflow-hidden transition-[width] duration-200">

    <button id="sidebar-toggle" type="button" aria-label="Buka/tutup sidebar" aria-expanded="false"
        class="label flex items-center gap-3 h-11 px-2.5 rounded-full hover:bg-primary-900 focus-visible:outline-none focus-visible:shadow-[0_0_0_2px_#146b5f,0_0_0_4px_#fff] transition">
        <svg class="w-5 h-5 shrink-0 transition-transform group-[.is-open]:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
        </svg>
        <span class="hidden group-[.is-open]:inline whitespace-nowrap font-semibold">Tutup menu</span>
    </button>

    <nav class="flex flex-col gap-1 mt-4">
        @foreach ($menu as $item)
            @php $aktif = request()->routeIs($item['aktif'] ?? $item['route']); @endphp
            <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}"
                class="label flex items-center gap-3 h-11 px-2.5 rounded-full no-underline transition focus-visible:outline-none focus-visible:shadow-[0_0_0_2px_#146b5f,0_0_0_4px_#fff] {{ $aktif ? 'bg-primary-400 !text-white' : '!text-white hover:bg-primary-900' }}" @if ($aktif) aria-current="page" @endif>
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                </svg>
                <span class="hidden group-[.is-open]:inline whitespace-nowrap">{{ $item['label'] }}</span>
            </a>
        @endforeach

        @can('admin')
            @php
                // Titik penanda: ada permintaan ubah kata sandi yang menunggu persetujuan
                $menungguSandi = \App\Models\User::whereNotNull('sandi_diminta_at')->exists();
                $menuAdmin = [
                    ['label' => 'Log Sistem', 'route' => 'log.index', 'aktif' => 'log.*', 'titik' => false, 'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25'],
                    ['label' => 'Manajemen Akun', 'route' => 'akun.index', 'aktif' => 'akun.*', 'titik' => $menungguSandi, 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
                ];
            @endphp
            <hr class="w-full my-2 border-0 border-t border-white/40">
            <span class="label-sm text-center group-[.is-open]:text-left group-[.is-open]:px-2.5">Admin</span>
            @foreach ($menuAdmin as $item)
                @php $aktif = request()->routeIs($item['aktif']); @endphp
                <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}{{ $item['titik'] ? ' (ada permintaan ubah kata sandi)' : '' }}"
                    class="label relative flex items-center gap-3 h-11 px-2.5 rounded-full no-underline transition focus-visible:outline-none focus-visible:shadow-[0_0_0_2px_#146b5f,0_0_0_4px_#fff] {{ $aktif ? 'bg-primary-400 !text-white' : '!text-white hover:bg-primary-900' }}" @if ($aktif) aria-current="page" @endif>
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                    </svg>
                    <span class="hidden group-[.is-open]:inline whitespace-nowrap">{{ $item['label'] }}</span>
                    @if ($item['titik'])
                        <span class="absolute left-7 top-2 w-2.5 h-2.5 rounded-full bg-tertiary-500 ring-2 ring-primary-700" aria-hidden="true"></span>
                    @endif
                </a>
            @endforeach
        @endcan
    </nav>

    <a href="{{ route('logout') }}" title="Keluar"
        class="label mt-auto flex items-center gap-3 h-11 px-2.5 rounded-full no-underline !text-white hover:bg-primary-900 focus-visible:outline-none focus-visible:shadow-[0_0_0_2px_#146b5f,0_0_0_4px_#fff] transition">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        <span class="hidden group-[.is-open]:inline whitespace-nowrap">Keluar</span>
    </a>
</aside>

<script>
    (() => {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.getElementById('sidebar-toggle');
        const set = (open) => {
            sidebar.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open);
        };
        // Status buka/tutup diingat per browser
        try { set(localStorage.getItem('sidebar-open') === '1'); } catch (e) {}
        toggle.addEventListener('click', () => {
            const open = !sidebar.classList.contains('is-open');
            set(open);
            try { localStorage.setItem('sidebar-open', open ? '1' : '0'); } catch (e) {}
        });
    })();
</script>
