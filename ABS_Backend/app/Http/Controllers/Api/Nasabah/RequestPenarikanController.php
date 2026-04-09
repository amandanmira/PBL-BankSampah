<?php

namespace App\Http\Controllers\Api\Nasabah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penarikan;

class RequestPenarikanController extends Controller
{
    public function store(Request $request) {
        $validated = $request->validate([
            'jumlah' => 'required|integer',
            'nasabah_id' => 'required',
            'no_rekening' => 'required|string',
            'nama_bank' => 'required|string',
            'nama_rek' => 'required|string',
        ]);

        $penarikan = Penarikan::create([
            'jumlah' => $validated['jumlah'],
            'status' => 'pending',
            'nasabah_id' => $request->nasabah_id,
            'no_rekening' => $request->no_rekening,
            'nama_bank' => $request->nama_bank,
            'nama_rek' => $request->nama_rek,
        ]);

        return response()->json([
            'message' => 'Request Penarikan Berhasil Dibuat',
            'data' => $penarikan
        ], 200);
    }
}
