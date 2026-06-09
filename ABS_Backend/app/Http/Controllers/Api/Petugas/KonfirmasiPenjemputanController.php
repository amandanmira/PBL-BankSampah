<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Penjemputan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\StatusPenjemputanMail;

class KonfirmasiPenjemputanController extends Controller
{
    public function penjemputan()
{
    $gudangId = Auth::user()->gudang_id;
    $penjemputan = Penjemputan::with([
        'petugas', 
        'nasabah', 
        'tukang',
        'detailPenjemputan.sampah.itemSampah'
    ])
    ->where('gudang_id', $gudangId)
    // Tambahkan status baru ke dalam array ini
    ->whereIn('status', [
        'pending', 
        'menunggu_persetujuan', 
        'jadwal_ditolak', 
        'proses', 
        'dijemput', 
        'perlu_input'
    ])
    ->latest()
    ->paginate(10);

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
        $penjemputan = Penjemputan::with([
            'nasabah', 
            'detailPenjemputan.sampah.itemSampah', 
            'gudang', 
            'tukang',
            'penimbangan.transaksi.petugas',
            'penimbangan.sampah.itemSampah'
        ])
            ->where('nasabah_id', $user->nasabah_id)
            ->latest()
            ->paginate(10);

        return response()->json($penjemputan, 200);
    }

    public function terima(Request $request, Penjemputan $penjemputan)
    {
        $gudangId = Auth::user()->gudang_id;
        if ($penjemputan->gudang_id !== $gudangId) {
            return response()->json(['message' => 'Akses ditolak. Penjemputan ini ditujukan ke gudang lain.'], 403);
        }

        // Validasi agar tukang_id wajib diisi dan milik gudang yang sama
        $request->validate([
            'tukang_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('tukangs', 'tukang_id')->where(function ($query) use ($gudangId) {
                    $query->where('gudang_id', $gudangId)->where('active', 1);
                }),
            ],
            'jadwal' => 'nullable|date_format:Y-m-d H:i:s'
        ]);

        $penjemputan->status = 'menunggu_persetujuan';
        $penjemputan->petugas_id = Auth::id();
        $penjemputan->tukang_id = $request->tukang_id; // Menyimpan ID Tukang
        if ($request->has('jadwal')) {
            $penjemputan->jadwal = $request->jadwal;
        }
        $penjemputan->save();

        if ($penjemputan->nasabah && $penjemputan->nasabah->email) {
            Mail::to($penjemputan->nasabah->email)->send(new StatusPenjemputanMail($penjemputan, 'menunggu_persetujuan'));
        }

        return response()->json(['message' => 'Menunggu persetujuan nasabah'], 200);
    }

    // public function tolak(Penjemputan $penjemputan){
    //     $penjemputan->status = 'tolak';
    //     $penjemputan->save();

    //     return response()->json(['message' => 'Penjemputan di Tolak'], 200);
    // }

    public function tolak(Request $request, Penjemputan $penjemputan)
    {
        $gudangId = Auth::user()->gudang_id;
        if ($penjemputan->gudang_id !== $gudangId) {
            return response()->json(['message' => 'Akses ditolak. Penjemputan ini ditujukan ke gudang lain.'], 403);
        }

        // 1. Validasi request untuk memastikan alasan diisi
        $request->validate([
            'ket_status' => 'required|string|max:255',
        ]);

        // 2. Simpan alasan dan ubah status
        $penjemputan->status = 'tolak';
        $penjemputan->ket_status = $request->ket_status;
        $penjemputan->petugas_id = Auth::id(); // Mengisi ID petugas yang sedang login
        $penjemputan->save();

        if ($penjemputan->nasabah && $penjemputan->nasabah->email) {
            Mail::to($penjemputan->nasabah->email)->send(new StatusPenjemputanMail($penjemputan, 'tolak'));
        }

        return response()->json(['message' => 'Registrasi penjemputan ditolak'], 200);
    }

    public function dijemput(Penjemputan $penjemputan)
    {
        $gudangId = Auth::user()->gudang_id;
        if ($penjemputan->gudang_id !== $gudangId) {
            return response()->json(['message' => 'Akses ditolak. Penjemputan ini ditujukan ke gudang lain.'], 403);
        }

        $penjemputan->status = 'dijemput';
        $penjemputan->save();

        if ($penjemputan->nasabah && $penjemputan->nasabah->email) {
            Mail::to($penjemputan->nasabah->email)->send(new StatusPenjemputanMail($penjemputan, 'dijemput'));
        }

        return response()->json(['message' => 'Status diubah menjadi Dijemput'], 200);
    }

    public function sampaiLokasi(Penjemputan $penjemputan)
    {
        $gudangId = Auth::user()->gudang_id;
        if ($penjemputan->gudang_id !== $gudangId) {
            return response()->json(['message' => 'Akses ditolak. Penjemputan ini ditujukan ke gudang lain.'], 403);
        }

        $penjemputan->status = 'perlu_input';
        $penjemputan->save();

        if ($penjemputan->nasabah && $penjemputan->nasabah->email) {
            Mail::to($penjemputan->nasabah->email)->send(new StatusPenjemputanMail($penjemputan, 'perlu_input'));
        }

        return response()->json(['message' => 'Status diubah menjadi Perlu Input Data'], 200);
    }

    public function show(Penjemputan $penjemputan)
    {
        $gudangId = Auth::user()->gudang_id;
        if ($penjemputan->gudang_id !== $gudangId) {
            return response()->json(['message' => 'Akses ditolak. Penjemputan ini ditujukan ke gudang lain.'], 403);
        }

        // Load relasi yang mungkin Anda perlukan di frontend
        $penjemputan->load('nasabah', 'petugas', 'tukang', 'gudang');

        return response()->json(['data' => $penjemputan], 200);
    }

    // Tambahkan use App\Models\Tukang; di bagian atas controller jika belum ada

    public function getTukang()
    {
        $gudangId = Auth::user()->gudang_id;
        // Mengambil daftar tukang yang statusnya aktif dan berada di gudang yang sama dengan petugas
        $tukang = \App\Models\Tukang::where('active', 1)
                    ->where('gudang_id', $gudangId)
                    ->get();
        return response()->json(['data' => $tukang], 200);
    }

    public function setorManualNasabah(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->nasabah_id) {
            return response()->json([
                'message' => 'Akses ditolak. Pastikan Anda login dengan akun nasabah.'
            ], 403);
        }

        $transactions = \App\Models\TransaksiNasabah::where('tipe_transaksi', 'antar_sendiri')
            ->whereHas('penimbangan', function ($query) use ($user) {
                $query->where('nasabah_id', $user->nasabah_id);
            })
            ->with([
                'penimbangan.sampah.itemSampah',
                'penimbangan.tukang.gudang',
                'penimbangan.nasabah',
                'petugas'
            ])
            ->latest('tanggal')
            ->paginate(10);

        return response()->json($transactions, 200);
    }
    public function riwayatSetorManual(Request $request)
    {
        $search = $request->query('search');

        $query = \App\Models\TransaksiNasabah::where('tipe_transaksi', 'antar_sendiri')
            ->with([
                'penimbangan.sampah.itemSampah',
                'penimbangan.nasabah',
                'penimbangan.tukang.gudang',
                'petugas'
            ])
            ->latest('tanggal');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaksi_id', 'like', "%$search%")
                  ->orWhereHas('penimbangan.nasabah', function ($nq) use ($search) {
                      $nq->where('nama', 'like', "%$search%");
                  });
            });
        }

        $transactions = $query->paginate(10);

        return response()->json($transactions, 200);
    }

    public function showRiwayatSetorManual($id)
    {
        $transaksi = \App\Models\TransaksiNasabah::with([
            'penimbangan.sampah.itemSampah',
            'penimbangan.nasabah',
            'penimbangan.tukang.gudang',
            'petugas'
        ])->find($id);

        if (!$transaksi) {
            return response()->json(['message' => 'Data transaksi tidak ditemukan'], 404);
        }

        return response()->json(['data' => $transaksi], 200);
    }
}
