const rujukanVital = {{ \Illuminate\Support\Js::from(\App\Models\Pemeriksaan::RUJUKAN_VITAL) }};

function statusVital(parameter, nilai) {
    const rujukan = rujukanVital[parameter];
    if (!rujukan || String(nilai).trim() === '') return null;

    if (parameter === 'tekanan_darah') {
        const hasil = String(nilai).match(/^\s*(\d{2,3})\s*\/\s*(\d{2,3})\s*$/);
        if (!hasil) return null;

        const sistolik = Number(hasil[1]), diastolik = Number(hasil[2]);
        if (sistolik < rujukan.hipotensi_sistolik || diastolik < rujukan.hipotensi_diastolik ||
            sistolik >= rujukan.hipertensi_sistolik || diastolik >= rujukan.hipertensi_diastolik) return 'danger';
        if (sistolik < rujukan.sistolik_min || sistolik > rujukan.sistolik_max ||
            diastolik < rujukan.diastolik_min || diastolik > rujukan.diastolik_max) return 'warning';
        return null;
    }

    const angka = Number(String(nilai).replace(',', '.'));
    if (!Number.isFinite(angka)) return null;

    if (parameter === 'saturasi' && angka < rujukan.min) {
        return angka >= rujukan.rendah_min ? 'warning' : 'danger';
    }
    if (parameter === 'suhu') {
        if (angka < rujukan.hipotermia) return 'danger';
        if (angka < rujukan.min) return 'warning';
    }

    return angka < rujukan.min || angka > rujukan.max ? 'danger' : null;
}

function tandaiVital(input) {
    const status = statusVital(input.dataset.k, input.value);
    input.classList.toggle('vital-danger', status === 'danger');
    input.classList.toggle('vital-warning', status === 'warning');
}
