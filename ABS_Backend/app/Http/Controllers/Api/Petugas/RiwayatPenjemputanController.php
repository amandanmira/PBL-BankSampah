<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Penjemputan;
use Illuminate\Http\Request;


class RiwayatPenjemputanController extends Controller
{
    public function riwayatPenjemputan(Request $request)
    {
        $search = $request->query('search');

        $user = \Illuminate\Support\Facades\Auth::user();
        $gudangId = $user->gudang_id;

        $query = Penjemputan::with(['nasabah', 'tukang', 'detailPenjemputan.sampah.itemSampah', 'penimbangan.sampah.itemSampah', 'penimbangan.transaksi', 'gudang'])
            ->where('gudang_id', $gudangId)
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('penjemputan_id', 'like', "%$search%")
                    ->orWhereHas('nasabah', function ($nq) use ($search) {
                        $nq->where('nama', 'like', "%$search%");
                    });
            });
        }

        $riwayat = $query->paginate(10);

        return response()->json($riwayat, 200);
    }

    public function show($id)
    {
        $penjemputan = Penjemputan::with([
            'nasabah',
            'detailPenjemputan.sampah.itemSampah',
            'penimbangan.sampah.itemSampah',
            'penimbangan.transaksi.petugas',
            'tukang',
            'gudang',
            'petugas'
        ])->findOrFail($id);

        return response()->json([
            'message' => 'Detail berhasil diambil',
            'data' => $penjemputan
        ], 200);
    }
}
