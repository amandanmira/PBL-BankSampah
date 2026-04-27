<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Penjemputan;


class RiwayatPenjemputanController extends Controller
{
    public function riwayatPenjemputan()
    {
        // Menyaring data hanya untuk status 'selesai' dan 'tolak'
        $riwayat = Penjemputan::with('nasabah')
            ->latest()
            ->paginate(10);

        return response()->json($riwayat, 200);
    }

    public function show($id)
    {
        $penjemputan = Penjemputan::with('nasabah')->findOrFail($id);

        if ($penjemputan->status === 'selesai') {
            $penjemputan->load([
                'penimbangan.sampah.itemSampah',
                'penimbangan.transaksi' // <--- UBAH MENJADI INI
            ]);
        }

        return response()->json([
            'message' => 'Detail berhasil diambil',
            'data' => $penjemputan
        ], 200);
    }
}
