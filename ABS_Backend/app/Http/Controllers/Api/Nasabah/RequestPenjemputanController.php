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
            'estimasi_berat' => 'required|string',
            'rentang_hari' => 'nullable|string',
            'rentan_waktu' => 'nullable|string',
            'foto' => 'required|array|max:3',
            'foto.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            'nasabah_id' => 'required',
            'gudang_id' => 'required',
        ]);

        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            $files = $request->file('foto');
            foreach ($files as $file) {
                $fotoPaths[] = $file->store('foto-penjemputan', 'public');
            }
        }
        $validated['foto'] = $fotoPaths;

        $penjemputan = Penjemputan::create([
            'deskripsi' => $validated['deskripsi'],
            'alamat' => $validated['alamat'],
            'estimasi_berat' => $validated['estimasi_berat'],
            'rentang_hari' => $validated['rentang_hari'] ?? null,
            'rentan_waktu' => $validated['rentan_waktu'] ?? null,
            'foto' => $validated['foto'],
            'status' => 'pending',
            'nasabah_id' => $validated['nasabah_id'],
            'gudang_id' => $validated['gudang_id'],
        ]);

        if ($request->has('detail')) {
            foreach ($request->detail as $itemData) {
                    $penjemputan->detailPenjemputan()->create([
                        'sampah_id' => $itemData['sampah_id'],
                    ]);
                }
        }

        return response()->json([
            'message' => 'Request Penjemputan Berhasil Dibuat',
            'data' => $penjemputan
        ], 200);
    }

    public function cancel(Request $request, Penjemputan $penjemputan) {
        if ($penjemputan->status !== 'pending') {
            return response()->json([
                'message' => 'Penjemputan hanya bisa dibatalkan saat status masih pending'
            ], 422);
        }

        $request->validate([
            'ket_status' => 'nullable|string'
        ]);

        $penjemputan->update([
            'status' => 'batal',
            'ket_status' => $request->ket_status ?? 'Dibatalkan oleh nasabah'
        ]);

        return response()->json([
            'message' => 'Penjemputan berhasil dibatalkan',
            'data' => $penjemputan
        ], 200);
    }
    public function setujuJadwal(Request $request, Penjemputan $penjemputan) {
        if ($penjemputan->status !== 'menunggu_persetujuan') {
            return response()->json([
                'message' => 'Penjemputan tidak dalam status menunggu persetujuan'
            ], 422);
        }

        $penjemputan->update([
            'status' => 'proses',
            'ket_status' => null
        ]);

        return response()->json([
            'message' => 'Jadwal penjemputan berhasil disetujui',
            'data' => $penjemputan
        ], 200);
    }

    public function tolakJadwal(Request $request, Penjemputan $penjemputan) {
        if ($penjemputan->status !== 'menunggu_persetujuan') {
            return response()->json([
                'message' => 'Penjemputan tidak dalam status menunggu persetujuan'
            ], 422);
        }

        $request->validate([
            'alasan_tolak' => 'required|string'
        ]);

        $penjemputan->update([
            'status' => 'jadwal_ditolak',
            'ket_status' => $request->alasan_tolak
        ]);

        return response()->json([
            'message' => 'Jadwal penjemputan ditolak',
            'data' => $penjemputan
        ], 200);
    }
}
