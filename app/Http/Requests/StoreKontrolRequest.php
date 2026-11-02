<?php

namespace App\Http\Requests;

use App\Models\Keluhan;
use App\Models\Pegawai;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/** Isian popup "Catat Kontrol" (Laporan Kesehatan & Kejadian Luar Biasa). */
class StoreKontrolRequest extends FormRequest
{
    /** Akses edit sudah dijaga middleware can:edit-data di rute. */
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
            'hasil_pemeriksaan' => 'required|string',
            'pemeriksa_id' => Pegawai::aturanPemeriksa(Pegawai::JABATAN_POLIKLINIK),
            ...Keluhan::aturanVital(),
            ...Keluhan::ATURAN_OBAT,
            'perlu_rujukan' => 'required|in:1,0',
            'tempat_rujukan' => 'nullable|string|max:150',
            'tanggal_kontrol_selanjutnya' => 'required|date|after_or_equal:today',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return Keluhan::PESAN_VITAL;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return Keluhan::LABEL_OBAT + Keluhan::LABEL_VITAL + ['hasil_pemeriksaan' => 'hasil kontrol', 'pemeriksa_id' => 'nama pemeriksa'];
    }

    /**
     * Kolom baris kontrol baru (riwayat_kontrol / kontrol_klb) dari isian tervalidasi, dicatat hari ini.
     *
     * @return array<string, mixed>
     */
    public function kontrol(): array
    {
        $data = $this->validated();

        return [
            'created_by' => $this->user()->id,
            'pemeriksa_id' => $data['pemeriksa_id'],
            'tanggal_kontrol' => now()->toDateString(),
            'hasil_kontrol' => $data['hasil_pemeriksaan'],
            ...Keluhan::kondisiDariIsian($data),
            'terapi' => Keluhan::terapiDariObat($data['obat']),
            'perlu_rujukan' => (bool) $data['perlu_rujukan'],
            // tempat rujukan hanya berarti bila memang dirujuk
            'tempat_rujukan' => $data['perlu_rujukan'] ? ($data['tempat_rujukan'] ?? null) : null,
            'tanggal_kontrol_selanjutnya' => $data['tanggal_kontrol_selanjutnya'],
        ];
    }
}
