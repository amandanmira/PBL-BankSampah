<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Penarikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KonfirmasiPenarikanController extends Controller
{
    public function penarikan(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $petugas = Auth::user();
        $gudangId = $petugas->gudang_id;

        $query = Penarikan::with(['nasabah', 'petugas.gudang'])->latest();

        if ($status && $status !== 'semua') {
            if ($status === 'menunggu') {
                $query->whereIn('status', ['pending', 'proses']);
            } elseif ($status === 'ditolak') {
                $query->whereIn('status', ['tolak', 'batal']);
            } elseif ($status === 'selesai') {
                $query->where('status', 'selesai')
                      ->whereHas('petugas', function ($pq) use ($gudangId) {
                          $pq->where('gudang_id', $gudangId);
                      });
            } else {
                $query->where('status', $status);
            }
        } else {
            // Tampilkan yang belum diproses (atau status lainnya) dan yang selesai dari gudang yang sama
            $query->where(function ($q) use ($gudangId) {
                $q->whereIn('status', ['pending', 'proses', 'tolak', 'batal'])
                  ->orWhere(function ($sub) use ($gudangId) {
                      $sub->where('status', 'selesai')
                          ->whereHas('petugas', function ($pq) use ($gudangId) {
                              $pq->where('gudang_id', $gudangId);
                          });
                  });
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('penarikan_id', 'like', "%$search%")
                  ->orWhereHas('nasabah', function ($nq) use ($search) {
                      $nq->where('nama', 'like', "%$search%");
                  });
            });
        }

        $penarikan = $query->paginate(10);

        // Hitung total penarikan selesai hari ini untuk limit harian petugas (5jt)
        $todayTotal = Penarikan::where('status', 'selesai')
            ->whereDate('updated_at', now()->toDateString())
            ->sum('jumlah');

        return response()->json([
            'penarikan' => $penarikan,
            'today_total' => $todayTotal,
            'daily_limit' => 5000000
        ], 200);
    }

    public function terima(Request $request, $id)
    {
        // 1. Validasi file foto bukti transfer (opsional)
        $request->validate([
            'bukti_tf' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // Tidak wajib upload
        ]);

        DB::beginTransaction();

        try {
            // Cari data penarikan beserta data nasabahnya
            $penarikan = \App\Models\Penarikan::with('nasabah')->findOrFail($id);

            // Keamanan: Cek apakah saldo nasabah cukup
            if ($penarikan->nasabah->saldo < $penarikan->jumlah) {
                return response()->json([
                    'message' => 'Gagal memproses. Saldo nasabah (' . $penarikan->nasabah->saldo . ') lebih kecil dari jumlah penarikan.'
                ], 400);
            }

            // 2. Upload Bukti Transfer
            $fotoPath = null;
            if ($request->hasFile('bukti_tf')) {
                // Disimpan di folder storage/app/public/bukti_transfer
                $fotoPath = $request->file('bukti_tf')->store('bukti_transfer', 'public');
            }

            // 3. Kurangi Saldo Nasabah
            $nasabah = $penarikan->nasabah;
            $nasabah->saldo -= $penarikan->jumlah;
            $nasabah->save();

            // 4. Update Data Penarikan
            $penarikan->bukti_tf = $fotoPath;
            $penarikan->status   = 'selesai'; // Langsung diubah menjadi selesai
            $penarikan->petugas_id = Auth::id(); // Mengisi ID petugas yang sedang login
            $penarikan->saldo_sesudah = $nasabah->saldo;
            $penarikan->save();

            DB::commit();

            return response()->json([
                'message' => 'Penarikan berhasil disetujui, bukti transfer tersimpan, dan saldo nasabah telah dikurangi.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memproses penarikan.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function tolak(Request $request, Penarikan $penarikan)
    {
        // 1. Validasi request untuk memastikan alasan diisi
        $request->validate([
            'ket_status' => 'required|string|max:255',
        ]);

        // 2. Simpan alasan dan ubah status
        $penarikan->status = 'tolak';
        $penarikan->ket_status = $request->ket_status;
        $penarikan->petugas_id = Auth::id(); // Mengisi ID petugas yang sedang login
        $penarikan->save();


        return response()->json(['message' => 'Registrasi penarikan$penarikan ditolak, status diubah menjadi nonaktif, dan notifikasi email terkirim'], 200);
    }

    public function riwayatPenarikan()
    {
        $petugas = Auth::user();
        $gudangId = $petugas->gudang_id;

        // Menyaring data riwayat penarikan: hanya menampilkan yang selesai atau ditolak oleh petugas gudang ini
        $riwayat = Penarikan::with(['nasabah', 'petugas.gudang'])
            ->whereIn('status', ['selesai', 'tolak'])
            ->whereHas('petugas', function ($pq) use ($gudangId) {
                $pq->where('gudang_id', $gudangId);
            })
            ->latest()
            ->paginate(10);

        return response()->json(['penarikan' => $riwayat], 200);
    }

    public function show($id)
    {
        $penarikan = Penarikan::with(['nasabah', 'petugas.gudang'])->findOrFail($id);

        return response()->json([
            'message' => 'Detail penarikan berhasil diambil',
            'data' => $penarikan
        ], 200);
    }
}
