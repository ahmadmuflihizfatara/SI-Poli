{{-- partials/head.blade.php — kepala halaman bersama. Pakai: @include('partials.head', ['judul' => 'Dashboard']) --}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $judul }} | SI-Poliklinik</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
{{-- 1rem = 16px tepat di layar 1440x1024 (ukuran prototype); layar lain ikut berskala, dibatasi 12-18px --}}
<style>html { font-size: clamp(12px, min(1.1111vw, 1.5625vh), 18px); }</style>
<link rel="stylesheet" href="{{ asset('css/pulih.css') }}">
<script src="https://cdn.tailwindcss.com"></script>
<script>
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
                    sans: ['"Open Sans"', 'system-ui', 'sans-serif'],
                    heading: ['Montserrat', '"Segoe UI"', 'sans-serif'],
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
<script>
    function createThemeToggle() {
        const button = document.createElement('button');
        button.type = 'button';
        button.id = 'theme-toggle';
        button.className = 'theme-toggle';
        button.addEventListener('click', function () {
            const isDark = document.documentElement.classList.toggle('dark');

            try {
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            } catch (error) {
                // The selected theme still applies for this page when storage is unavailable.
            }

            updateThemeToggle(button, isDark);
        });

        updateThemeToggle(button, document.documentElement.classList.contains('dark'));
        document.body.appendChild(button);
    }

    function updateThemeToggle(button, isDark) {
        button.setAttribute('aria-pressed', String(isDark));
        button.setAttribute('aria-label', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
        button.title = isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap';
        button.innerHTML = isDark
            ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364-.707-.707M6.343 6.343l-.707-.707m12.728 0-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg><span>Mode terang</span>'
            : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg><span>Mode gelap</span>';
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createThemeToggle, { once: true });
    } else {
        createThemeToggle();
    }
</script>
