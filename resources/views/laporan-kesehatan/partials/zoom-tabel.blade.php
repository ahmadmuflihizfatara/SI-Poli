{{-- laporan-kesehatan/partials/zoom-tabel.blade.php — tombol perbesar/perkecil tabel (pl-table pertama di halaman) (CSS zoom, 70–200%), pilihan diingat di browser. Juga bisa dicubit (pinch) di trackpad / layar sentuh.
     Pakai di bawah tabel (setelah pembungkus .pl-table-wrap); perataan diatur pemanggil: @include('laporan-kesehatan.partials.zoom-tabel'). --}}
<div class="flex items-center gap-1" role="group" aria-label="Zoom tabel">
    @foreach ([['zoom-kurang', 'Perkecil tabel', 'M19.5 12h-15'], ['zoom-tambah', 'Perbesar tabel', 'M12 4.5v15m7.5-7.5h-15']] as [$id, $label, $ikon])
        @if ($id === 'zoom-tambah')
            <button type="button" id="zoom-nilai" class="pl-btn pl-btn-ghost !h-9 w-16 justify-center tabular-nums" title="Kembalikan ke 100%" aria-label="Kembalikan zoom ke 100%">100%</button>
        @endif
        <button type="button" id="{{ $id }}" class="pl-btn pl-btn-secondary !h-9 !w-9 !p-0 justify-center" title="{{ $label }}" aria-label="{{ $label }}">
            <svg class="w-[1.125rem] h-[1.125rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $ikon }}"/></svg>
        </button>
    @endforeach
</div>
<script>
    // tunggu halaman selesai dimuat supaya tabel pasti ada
    addEventListener('DOMContentLoaded', () => {
        const tabel = document.querySelector('.pl-table-wrap > table'), nilai = document.getElementById('zoom-nilai');
        const kurang = document.getElementById('zoom-kurang'), tambah = document.getElementById('zoom-tambah');
        const MIN = 70, MAX = 200, LANGKAH = 10;
        let z = 100;
        try { z = Math.min(MAX, Math.max(MIN, parseInt(localStorage.getItem('zoom-tabel')) || 100)); } catch (e) {}
        const terapkan = (baru) => {
            z = Math.min(MAX, Math.max(MIN, baru));
            tabel.style.zoom = z / 100;
            tabel.style.minWidth = Math.max(100, z) + '%'; // tabel selebar 100% pun ikut melebar (dan bisa digeser), bukan hanya hurufnya
            nilai.textContent = Math.round(z) + '%';
            kurang.disabled = z <= MIN;
            tambah.disabled = z >= MAX;
            try { localStorage.setItem('zoom-tabel', Math.round(z)); } catch (e) {}
        };
        kurang.onclick = () => terapkan(z - LANGKAH);
        tambah.onclick = () => terapkan(z + LANGKAH);
        nilai.onclick = () => terapkan(100);
        terapkan(z);

        // Pinch: trackpad (Chrome/Edge/Firefox) = wheel + ctrlKey, trackpad Safari = gesturechange, layar sentuh = dua jari.
        // Hanya di atas tabel; cubitan di luar tabel tetap zoom halaman biasa.
        const wadah = tabel.parentElement;
        wadah.style.touchAction = 'pan-x pan-y';
        wadah.addEventListener('wheel', (e) => {
            if (!e.ctrlKey) return;
            e.preventDefault();
            terapkan(z * Math.exp(-e.deltaY * 0.01));
        }, { passive: false });

        let awal = 0, jarak = 0, safari = 100;
        const dua = (t) => Math.hypot(t[0].clientX - t[1].clientX, t[0].clientY - t[1].clientY);
        wadah.addEventListener('touchstart', (e) => { if (e.touches.length === 2) { awal = z; jarak = dua(e.touches); } }, { passive: true });
        wadah.addEventListener('touchmove', (e) => {
            if (e.touches.length !== 2 || !jarak) return;
            e.preventDefault();
            terapkan(awal * dua(e.touches) / jarak);
        }, { passive: false });
        wadah.addEventListener('touchend', () => { jarak = 0; });
        wadah.addEventListener('gesturestart', (e) => { e.preventDefault(); safari = z; });
        wadah.addEventListener('gesturechange', (e) => { e.preventDefault(); terapkan(safari * e.scale); });
    });
</script>
