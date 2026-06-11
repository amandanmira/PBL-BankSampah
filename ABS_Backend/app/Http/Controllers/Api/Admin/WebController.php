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
            'hero_quote_top' => 'nullable|string',
            'hero_quote_bottom' => 'nullable|string',
            'instagram' => 'nullable|string',
            'facebook' => 'nullable|string',
            'linkedin' => 'nullable|string',
            'youtube' => 'nullable|string',
            'no_telp' => 'nullable|string|max:16',
            'batas_waktu_edit' => 'nullable|integer|min:1',
            'email' => 'nullable|email|max:50',
            'lama_deadline' => 'nullable|integer',
            'alamat' => 'nullable|string',
            'tentang' => 'nullable|string',
        ]);

        // handle upload logo
        if ($request->hasFile('logo')) {
            // hapus logo lama jika ada (lokal)
            if ($config->logo && !\Illuminate\Support\Str::startsWith($config->logo, 'http') && Storage::disk('public')->exists($config->logo)) {
                Storage::disk('public')->delete($config->logo);
            }

            // simpan logo baru ke Cloudinary melalui Storage driver
            $path = $request->file('logo')->store('logo', 'cloudinary');
            $uploadedFileUrl = Storage::disk('cloudinary')->url($path);

            $validated['logo'] = $uploadedFileUrl;
        }

        $config->update($validated);

        return response()->json([
            'message' => 'Konfigurasi berhasil diperbarui',
            'data' => $config
        ], 200);
    }
}
