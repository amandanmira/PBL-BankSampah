<?php

namespace App\Http\Controllers\Api\Pengepul;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransaksiPengepul;
use App\Models\ItemSampah;
use App\Models\Sampah;

class RequestPembelianController extends Controller
{
    public function indexSampah() {
        return response()->json(
            ItemSampah::with('sampah.gudang')->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'pengepul_id' => 'required',
            'detail' => 'required|array'
        ]);

        // simpan item jika ada
        foreach ($request->detail as $d) {
            $sampah = Sampah::lockForUpdate()->findOrFail($d['sampah_id']);

            if ($sampah->stok >= $d['berat']){
                $sampah->update([
                    'stok' => $sampah->stok - $d['berat'],
                ]);
            } else {
                throw new \Exception('Stok tidak cukup');
            }
        }

        $transaksi = TransaksiPengepul::create([
            'status' => 'pending',
            'pengepul_id' => $request->pengepul_id,
        ]);

        foreach ($request->detail as $d) {
            $transaksi->detailTransaksi()->create([
                'sampah_id' => $d['sampah_id'],
                'berat' => $d['berat'] ?? 0,
                'harga' => $d['harga'] ?? 0,
            ]);
        }

        return response()->json($transaksi->load('detailTransaksi'), 201);
    }
}
