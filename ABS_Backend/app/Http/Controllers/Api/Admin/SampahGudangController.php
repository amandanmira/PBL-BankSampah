<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sampah; 
use App\Models\Gudang;

class SampahGudangController extends Controller
{
    /**
     * Memperbarui atau menambahkan stok item sampah di dalam Gudang
     */
    public function update(Request $request, $id)
    {
        // 1. Validasi data yang dikirim dari Vue
        $request->validate([
            'sampah' => 'required|array',
            'sampah.*.item_id' => 'required|exists:item_sampahs,item_id',
            'sampah.*.stok' => 'required|numeric|min:0',
            'sampah.*.active' => 'required|boolean'
        ]);

        return DB::transaction(function () use ($request, $id) {
            // Pastikan gudangnya memang ada
            $gudang = Gudang::findOrFail($id);

            // 2. Looping data array sampah dari Vue
            foreach ($request->sampah as $item) {
                
                // Gunakan updateOrCreate agar:
                // - Jika item belum ada di gudang ini, maka dibuatkan row baru
                // - Jika item sudah ada, maka stoknya ditimpa dengan angka baru
                Sampah::updateOrCreate(
                    [
                        'gudang_id' => $gudang->gudang_id,
                        'item_id'   => $item['item_id']
                    ],
                    [
                        'stok'      => $item['stok'],
                        'active'    => $item['active']
                    ]
                );
            }

            return response()->json([
                'message' => 'Stok gudang berhasil diperbarui',
            ], 200);
        });
    }

    /**
     * Menghapus referensi stok sampah dari gudang tertentu
     */
    public function destroy($id)
    {
        $sampah = Sampah::findOrFail($id);
        $sampah->delete();

        return response()->json([
            'message' => 'Item sampah berhasil dihapus dari gudang'
        ], 200);
    }
}