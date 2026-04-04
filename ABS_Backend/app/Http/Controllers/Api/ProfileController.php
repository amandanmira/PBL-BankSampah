<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Pengepul;
use App\Models\Nasabah;

class ProfileController extends Controller
{
    public function showPengepul(string $id)
    {
        $pengepul = Pengepul::findOrFail($id);

        return response()->json($pengepul);
    }

    public function updatePengepul(Request $request, string $id)
    {
        $pengepul = Pengepul::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:50',
            'username' => [
                'required',
                'string',
                'max:20',
                Rule::unique('pengepuls')->ignore($pengepul->pengepul_id, 'pengepul_id')
            ],
            'no_telp' => 'required|string|max:16',
            'nama_lembaga' => 'required|string|max:50',
            'alamat' => 'required|string',
        ]);

        $pengepul->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => $pengepul
        ]);
    }

    public function showNasabah(string $id)
    {
        $nasabah = Nasabah::findOrFail($id);

        return response()->json($nasabah);
    }

    public function updateNasabah(Request $request, string $id)
    {
        $nasabah = Nasabah::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:50',
            'username' => [
                'required',
                'string',
                'max:20',
                Rule::unique('nasabahs')->ignore($nasabah->nasabah_id, 'nasabah_id')
            ],
            'no_telp' => 'nullable|string|max:16',
            'alamat' => 'nullable|string',
            'gmap' => 'nullable|string',
            'nama_bank' => 'nullable|string|max:50',
            'no_rekening' => 'nullable|string|max:20',
        ]);

        $nasabah->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => $nasabah
        ]);
    }
}
