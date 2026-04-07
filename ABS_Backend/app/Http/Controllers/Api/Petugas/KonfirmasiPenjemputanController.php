<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Penjemputan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KonfirmasiPenjemputanController extends Controller
{
    public function penjemputan()
    {
        $penjemputan = Penjemputan::with('petugas')->latest()->get();
        return response()->json(['data' => $penjemputan], 200);
    }

    public function terima(Request $request, Penjemputan $penjemputan)
    {
        $penjemputan->status = 'proses';
        $penjemputan->petugas_id = Auth::id(); // Mengisi ID petugas yang sedang login
        $penjemputan->save();

        return response()->json(['message' => 'Penjemputan di Proses'], 200);
    }

    // public function tolak(Penjemputan $penjemputan){
    //     $penjemputan->status = 'tolak';
    //     $penjemputan->save();

    //     return response()->json(['message' => 'Penjemputan di Tolak'], 200);
    // }

    public function tolak(Request $request, Penjemputan $penjemputan)
    {
        // 1. Validasi request untuk memastikan alasan diisi
        $request->validate([
            'ket_status' => 'required|string|max:255',
        ]);

        // 2. Simpan alasan dan ubah status
        $penjemputan->status = 'tolak';
        $penjemputan->ket_status = $request->ket_status;
        $penjemputan->petugas_id = Auth::id(); // Mengisi ID petugas yang sedang login
        $penjemputan->save();


        return response()->json(['message' => 'Registrasi penjemputan$penjemputan ditolak, status diubah menjadi nonaktif, dan notifikasi email terkirim'], 200);
    }
}
