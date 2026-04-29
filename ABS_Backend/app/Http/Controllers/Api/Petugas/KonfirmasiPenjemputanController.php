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
        $penjemputan = Penjemputan::with(['petugas', 'nasabah'])->whereIn('status',['pending', 'proses'])->latest()->paginate(10);
        return response()->json($penjemputan, 200);
    }

    //riwayat buat nasabah
    public function penjemputanNasabah(Request $request)
    {
        // $user di sini sudah berisi row dari tabel 'nasabah'
        $user = $request->user();

        // Validasi opsional untuk memastikan yang login benar-benar punya nasabah_id
        if (!$user || !$user->nasabah_id) {
            return response()->json([
                'message' => 'Akses ditolak. Pastikan Anda login dengan akun nasabah.'
            ], 403);
        }

        // Langsung panggil $user->nasabah_id tanpa relasi tambahan
        $penjemputan = Penjemputan::with('nasabah')
            ->where('nasabah_id', $user->nasabah_id)
            ->latest()
            ->paginate(10);

        return response()->json($penjemputan, 200);
    }

    public function terima(Request $request, Penjemputan $penjemputan)
    {
        // Validasi agar tukang_id wajib diisi
        $request->validate([
            'tukang_id' => 'required|exists:tukangs,tukang_id',
            'jadwal' => 'nullable|date_format:Y-m-d H:i:s'
        ]);

        $penjemputan->status = 'proses';
        $penjemputan->petugas_id = Auth::id();
        $penjemputan->tukang_id = $request->tukang_id; // Menyimpan ID Tukang
        if ($request->has('jadwal')) {
            $penjemputan->jadwal = $request->jadwal;
        }
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

    public function show(Penjemputan $penjemputan)
    {
        // Load relasi yang mungkin Anda perlukan di frontend
        $penjemputan->load('nasabah', 'petugas', 'tukang', 'gudang');

        return response()->json(['data' => $penjemputan], 200);
    }

    // Tambahkan use App\Models\Tukang; di bagian atas controller jika belum ada

    public function getTukang()
    {
        // Mengambil daftar tukang yang statusnya aktif (berdasarkan gambar tabel Anda)
        $tukang = \App\Models\Tukang::where('active', 1)->get();
        return response()->json(['data' => $tukang], 200);
    }
}
