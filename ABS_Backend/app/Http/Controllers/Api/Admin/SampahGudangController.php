<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sampah;

class SampahGudangController extends Controller
{
    public function update(Request $request, $id)
    {
        foreach ($request->sampah as $s) {
            if (isset($s['sampah_id'])) {
                $sampah = Sampah::find($k['sampah_id']);
                if ($sampah) {
                    $sampah->update([
                        'kategori_id' => $s['kategori_id'],
                        'stok' => $s['stok'],
                    ]);
                }
            }
            else {
                Sampah::create([
                    'kategori_id' => $s['kategori_id'],
                    'stok' => $s['stok'],
                    'gudang_id' => $id,
                ]);
            }
        }


        return response()->json([
            'message' => 'Update Sampah Berhasil!'
        ]);
    }
}
