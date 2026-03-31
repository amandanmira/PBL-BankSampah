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
        ]);

        $penarikan = Penarikan::create([
            'jumlah' => $validated['jumlah'],
            'status' => 'pending',
            'nasabah_id' => $request->nasabah_id,
        ]);

        return response()->json([
            'message' => 'Request Penarikan Berhasil Dibuat',
            'data' => $penarikan
        ], 200);
    }
}
