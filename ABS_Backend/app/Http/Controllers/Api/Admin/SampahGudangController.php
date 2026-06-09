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
                $sampah = Sampah::find($s['sampah_id']);
                if ($sampah) {
                    $sampah->update([
                        'item_id' => $s['item_id'],
                        'stok' => $s['stok'],
                        'active' => $s['active'],
                    ]);
                }
            }
            else {
                $existing = Sampah::where('gudang_id', $id)->where('item_id', $s['item_id'])->first();
                if ($existing) {
                    $existing->update([
                        'stok' => $s['stok'],
                        'active' => $s['active'],
                    ]);
                } else {
                    Sampah::create([
                        'item_id' => $s['item_id'],
                        'stok' => $s['stok'],
                        'active' => $s['active'],
                        'gudang_id' => $id,
                    ]);
                }
            }
        }


        return response()->json([
            'message' => 'Update Sampah Berhasil!'
        ]);
    }

    public function destroy($id)
    {
        $sampah = Sampah::findOrFail($id);
        $sampah->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
