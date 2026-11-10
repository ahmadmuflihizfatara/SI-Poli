<?php

namespace App\Http\Controllers;

use App\Services\Kolaborasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Titik sinkron pengisian bersama (lihat Kolaborasi); dipanggil berkala oleh partials/kolaborasi. */
class KolaborasiController extends Controller
{
    public function __invoke(Request $request): JsonResponse|Response
    {
        // sendBeacon saat halaman ditutup
        if ($request->boolean('keluar')) {
            $token = Kolaborasi::baca($request->input('token'), $request->user());
            abort_unless($token, 403);
            Kolaborasi::keluar($token, $request->user());

            return response()->noContent();
        }

        $data = $request->validate([
            'token' => 'required|string',
            'since' => 'required|integer|min:0',
            'kursor' => 'nullable|array:a,x,y',
            'kursor.a' => 'required_with:kursor|string|max:300',
            'kursor.x' => 'required_with:kursor|numeric|between:0,1',
            'kursor.y' => 'required_with:kursor|numeric|between:0,1',
            'fokus' => 'nullable|string|max:200',
            'isian' => 'array|max:2000',
            'isian.*' => 'nullable|string|max:10000',
            'hapus' => 'array|max:200',
            'hapus.*' => 'string|max:200',
        ]);

        $token = Kolaborasi::baca($data['token'], $request->user());
        abort_unless($token, 403);

        return response()->json(Kolaborasi::sinkron($token, $request->user(), [
            'since' => (int) $data['since'],
            'kursor' => isset($data['kursor']) ? ['a' => $data['kursor']['a'], 'x' => (float) $data['kursor']['x'], 'y' => (float) $data['kursor']['y']] : null,
            'fokus' => $data['fokus'] ?? null,
            'isian' => $data['isian'] ?? [],
            'hapus' => array_values($data['hapus'] ?? []),
        ]));
    }
}
