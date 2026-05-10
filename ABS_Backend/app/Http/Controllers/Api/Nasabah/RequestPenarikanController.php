<?php

namespace App\Http\Controllers\Api\Nasabah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penarikan;

class RequestPenarikanController extends Controller
{
    public function getData(Request $request)
    {
        $nasabah = $request->user();
        return response()->json([
            'saldo_tersedia' => $nasabah->saldo_tersedia,
            'completion_percentage' => $nasabah->completion_percentage,
            'rekening_profil' => [
                'nama_bank' => $nasabah->nama_bank,
                'no_rekening' => $nasabah->no_rekening,
                'nama_rek' => $nasabah->nama_rek,
            ]
        ]);
    }

    public function store(Request $request) {
        $nasabah = $request->user();

        $validated = $request->validate([
            'jumlah' => 'required|integer|min:1',
            'no_rekening' => 'required|string',
            'nama_bank' => 'required|string',
            'nama_rek' => 'required|string',
        ]);

        if ($nasabah->saldo_tersedia < $validated['jumlah']) {
            return response()->json([
                'message' => 'Saldo tersedia tidak mencukupi untuk penarikan ini.'
            ], 400);
        }

        $penarikan = Penarikan::create([
            'jumlah' => $validated['jumlah'],
            'status' => 'pending',
            'nasabah_id' => $nasabah->nasabah_id,
            'no_rekening' => $validated['no_rekening'],
            'nama_bank' => $validated['nama_bank'],
            'nama_rek' => $validated['nama_rek'],
        ]);

        return response()->json([
            'message' => 'Request Penarikan Berhasil Dibuat',
            'data' => $penarikan
        ], 200);
    }
}
