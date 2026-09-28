{{-- partials/sidebar.blade.php
     Pakai: @include('partials.sidebar') lalu beri konten utama class
     "ml-[6.25rem] peer-[.is-open]:ml-[16.25rem]" (harus sibling setelah sidebar).
     Tambah/ubah menu cukup di array $menu di bawah. Ikon = atribut "d" path SVG (heroicons outline).
     'aktif' (opsional) = pola nama route yang ikut menandai menu aktif, mis. halaman tambah/ubah. --}}
@php
    $menu = [
        ['label' => 'Dashboard',         'route' => 'dashboard',               'icon' => 'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
        ['label' => 'Laporan Kesehatan', 'route' => 'laporan-kesehatan.index', 'aktif' => 'laporan-kesehatan.*', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
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
