<?php

namespace App\Http\Controllers\Api\Nasabah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penjemputan;

class RequestPenjemputanController extends Controller
{
    public function store(Request $request) {
        $validated = $request->validate([
            'deskripsi' => 'required|string',
            'alamat' => 'required|string',
            'foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'nasabah_id' => 'required',
        ]);

        // handle upload logo
        if ($request->hasFile('foto')) {
            // simpan logo baru
            $path = $request->file('foto')->store('foto-penjemputan', 'public');

            $validated['foto'] = $path;
        }

        $penjemputan = Penjemputan::create([
            'deskripsi' => $validated['deskripsi'],
            'alamat' => $validated['alamat'],
            'foto' => $validated['foto'],
            'status' => 'pending',
            'nasabah_id' => $request->nasabah_id,
        ]);

        return response()->json([
            'message' => 'Request Penjemputan Berhasil Dibuat',
            'data' => $penjemputan
        ], 200);
    }
}
