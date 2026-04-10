<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Tukang;

class TukangController extends Controller
{
    public function update(Request $request, $gudang_id)
    {
        foreach ($request->tukang as $index => $t) {
            if (isset($t['tukang_id'])) {
                $tukang = Tukang::find($t['tukang_id']);
                $data = [
                    'nama' => $t['nama'],
                    'no_telp' => $t['no_telp'],
                    'active' => $t['active'],
                ];

                if ($request->hasFile("tukang.$index.foto")) {

                    // hapus file lama kalau ada
                    if ($tukang && $tukang->foto && Storage::disk('public')->exists($tukang->foto)) {
                        Storage::disk('public')->delete($tukang->foto);
                    }

                    // simpan file baru
                    $data['foto'] = $request->file("tukang.$index.foto")
                                    ->store('foto-tukang', 'public');
                }
                if ($tukang) {

                    $tukang->update($data);
                }
            }
            else {
                $foto = null;

                if ($request->hasFile("tukang.$index.foto")) {
                    // simpan file baru
                    $foto = $request->file("tukang.$index.foto")
                                    ->store('foto-tukang', 'public');
                }

                Tukang::create([
                    'nama' => $t['nama'],
                    'no_telp' => $t['no_telp'],
                    'foto' => $foto,
                    'gudang_id' => $gudang_id,
                ]);
            }
        }


        return response()->json([
            'message' => 'Update Sampah Berhasil!'
        ]);
    }
}
