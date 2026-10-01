{{-- laporan-kesehatan/partials/tanggal-konseling.blade.php — kolom #kolom-tanggal hanya tampil & wajib saat konseling dilanjutkan
     (radio name="lanjut_konseling" value="1"). Server tetap memvalidasi lewat KonselingController::aturanLanjut. --}}
<script>
    (() => {
        const kolom = document.getElementById('kolom-tanggal');
        const tanggal = kolom.querySelector('input');
        const atur = () => {
            const lanjut = document.querySelector('input[name="lanjut_konseling"]:checked')?.value === '1';
            kolom.style.display = lanjut ? '' : 'none'; // bukan [hidden]: .pl-field memakai display:flex
            tanggal.required = lanjut;
        };
        document.querySelectorAll('input[name="lanjut_konseling"]').forEach((r) => r.addEventListener('change', atur));
        atur();
    })();
</script>
