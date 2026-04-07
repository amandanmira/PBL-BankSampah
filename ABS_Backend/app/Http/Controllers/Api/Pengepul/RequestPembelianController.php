<?php

namespace App\Http\Controllers\Api\Pengepul;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransaksiPengepul;
use App\Models\ItemSampah;

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
            'detail' => 'array'
        ]);

        $transaksi = TransaksiPengepul::create([
            'status' => 'pending',
            'pengepul_id' => $request->pengepul_id,
        ]);

        // simpan item jika ada
        if ($request->has('detail')) {
            foreach ($request->detail as $d) {
                $transaksi->detailTransaksi()->create([
                    'sampah_id' => $d['sampah_id'],
                    'berat' => $d['berat'] ?? 0,
                    'harga' => $d['harga'] ?? 0,
                ]);
            }
        }

        return response()->json($transaksi->load('detailTransaksi'), 201);
    }
}
