<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\KonfigurasiWeb;

class WebController extends Controller
{
    public function show()
    {
        $config = KonfigurasiWeb::firstOrCreate([]);

        return response()->json($config, 200);
    }

    public function update(Request $request)
    {
        $config = KonfigurasiWeb::firstOrCreate([]);

        $validated = $request->validate([
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'quote' => 'nullable|string',
            'instagram' => 'nullable|string',
            'facebook' => 'nullable|string',
            'linkedin' => 'nullable|string',
            'youtube' => 'nullable|string',
            'no_telp' => 'nullable|string|max:16',
            'email' => 'nullable|email|max:50',
            'lama_deadline' => 'nullable|integer',
            'alamat' => 'nullable|string',
            'tentang' => 'nullable|string',
        ]);

        // handle upload logo
        if ($request->hasFile('logo')) {
            // hapus logo lama jika ada
            if ($config->logo && Storage::disk('public')->exists($config->logo)) {
                Storage::disk('public')->delete($config->logo);
            }

            // simpan logo baru
            $path = $request->file('logo')->store('logo', 'public');

            $validated['logo'] = $path;
        }

        $config->update($validated);

        return response()->json([
            'message' => 'Konfigurasi berhasil diperbarui',
            'tes' => $request->all(),
            'data' => $config
        ], 200);
    }
}
