<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Tampilan Dashboard & Laporan Kesehatan: 'perawat' (keluhan medis) atau 'psikolog' (konseling).
     * Perawat/psikolog selalu melihat bagiannya; admin memilih lewat ?bagian=..., diingat di session.
     */
    protected function bagian(Request $request): string
    {
        $role = $request->user()->role;
        if ($role !== 'admin') {
            return $role;
        }
        if (in_array($request->query('bagian'), ['perawat', 'psikolog'], true)) {
            $request->session()->put('bagian', $request->query('bagian'));
        }

        return $request->session()->get('bagian', 'perawat');
    }
}
