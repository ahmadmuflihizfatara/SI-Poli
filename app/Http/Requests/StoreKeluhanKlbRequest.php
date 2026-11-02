<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

/** Isian "Tambahkan Keluhan Baru" pada Kejadian Luar Biasa: sama dengan Laporan Kesehatan, tanpa tingkat keparahan; jadwal kontrol selalu wajib. */
class StoreKeluhanKlbRequest extends StoreKeluhanRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...Arr::except(parent::rules(), 'status'),
            'tanggal_kontrol_selanjutnya' => 'required|date|after_or_equal:today',
        ];
    }
}
