<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

#[Fillable(['nama', 'jabatan'])]
class Pegawai extends Model
{
    /** Jabatan yang bisa dipilih sebagai pemeriksa keluhan & kontrol poliklinik (perawat). */
    public const JABATAN_POLIKLINIK = ['Dokter', 'Perawat'];

    /** Jabatan yang bisa dipilih sebagai pemeriksa keluhan psikologi. */
    public const JABATAN_PSIKOLOGI = ['Psikolog'];

    protected $table = 'pegawai';

    /**
     * Pilihan pemeriksa untuk combobox, dikelompokkan per jabatan.
     *
     * @param  list<string>  $jabatan
     * @return Collection<int, self>
     */
    public static function pemeriksa(array $jabatan): Collection
    {
        return self::whereIn('jabatan', $jabatan)->orderBy('jabatan')->orderBy('nama')->get(['id', 'nama', 'jabatan']);
    }

    /**
     * Aturan validasi pemeriksa: wajib dan harus pegawai dengan salah satu jabatan ini.
     *
     * @param  list<string>  $jabatan
     * @return list<mixed>
     */
    public static function aturanPemeriksa(array $jabatan): array
    {
        return ['required', Rule::exists('pegawai', 'id')->whereIn('jabatan', $jabatan)];
    }
}
