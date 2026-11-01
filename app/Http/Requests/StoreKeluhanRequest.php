<?php

namespace App\Http\Requests;

use App\Models\Keluhan;
use App\Models\Pegawai;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Isian "Tambahkan Keluhan Baru". Tekanan darah datang sebagai sistolik + diastolik, digabung jadi "120/80" di controller. */
class StoreKeluhanRequest extends FormRequest
{
    /** Akses tambah sudah dijaga middleware can:tambah-data di rute. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'taruna_id' => 'required|exists:taruna,id',
            'pemeriksa_id' => Pegawai::aturanPemeriksa(Pegawai::JABATAN_POLIKLINIK),
            ...Keluhan::aturanVital(),
            'waktu_laporan' => 'required|date|before_or_equal:now',
            'keluhan' => 'required|string',
            ...Keluhan::ATURAN_OBAT,
            'hasil_pemeriksaan' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:Ringan,Sedang,Berat',
            // keluhan ringan boleh tanpa jadwal kontrol
            'tanggal_kontrol_selanjutnya' => 'nullable|required_unless:status,Ringan|date|after_or_equal:today',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return Keluhan::PESAN_VITAL + [
            'taruna_id.required' => 'Pilih taruna dari daftar hasil pencarian.',
            'taruna_id.exists' => 'Pilih taruna dari daftar hasil pencarian.',
            'pemeriksa_id.required' => 'Pilih pegawai pemeriksa dari daftar.',
            'pemeriksa_id.exists' => 'Pilih pegawai pemeriksa dari daftar.',
            'waktu_laporan.before_or_equal' => 'Waktu laporan tidak boleh melewati waktu sekarang.',
            'tanggal_kontrol_selanjutnya.required_unless' => 'Tanggal kontrol selanjutnya wajib diisi untuk keluhan Sedang atau Berat.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return Keluhan::LABEL_OBAT + Keluhan::LABEL_VITAL + ['taruna_id' => 'taruna', 'pemeriksa_id' => 'nama pemeriksa', 'waktu_laporan' => 'waktu laporan'];
    }
}
