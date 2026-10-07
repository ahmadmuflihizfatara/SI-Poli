{{-- partials/head.blade.php — kepala halaman bersama. Pakai: @include('partials.head', ['judul' => 'Dashboard']) --}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $judul }} | SI-Poliklinik</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
{{-- 1rem = 16px tepat di layar 1440x1024 (ukuran prototype); layar lain ikut berskala, dibatasi 12-18px --}}
<style>html { font-size: clamp(12px, min(1.1111vw, 1.5625vh), 18px); }</style>
<link rel="stylesheet" href="{{ asset('css/pulih.css') }}">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    // Tema dari halaman Pengaturan: 'light' / 'dark' / kosong = ikuti sistem
    (function () {
        try {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (savedTheme === 'dark' || (savedTheme !== 'light' && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        } catch (error) {
            // Theme preference is optional when browser storage is unavailable.
        }
    })();

    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    sans: ['"Instrument Sans"', 'system-ui', 'sans-serif'],
                    heading: ['"Instrument Sans"', 'system-ui', 'sans-serif'],
                },
                colors: {
                    primary: { 900: '#0B3B36', 700: '#146B5F', 400: '#6FA89D', 50: '#E3F1EE' },
                    secondary: { 800: '#8A3620', 300: '#F0B79B', 500: '#D8693F', 50: '#FBEAE0' },
                    tertiary: { 800: '#7A5308', 500: '#C88A1E', 50: '#FBEACD' },
                    neutral: { 900: '#232620', 600: '#55584F', 400: '#9A9C92', 200: '#DEDFD7', 50: '#FEFDFC' },
                    muted: '#55584F',
                },
            }
        }
    }
</script>
