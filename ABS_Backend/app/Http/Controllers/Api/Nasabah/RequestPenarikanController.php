<?php

namespace App\Http\Controllers\Api\Nasabah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penarikan;

class RequestPenarikanController extends Controller
{
    public function index(Request $request)
    {
        $nasabah = $request->user();
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Penarikan::where('nasabah_id', $nasabah->nasabah_id)->latest();

        if ($status && $status !== 'semua') {
            if ($status === 'menunggu') {
                $query->where('status', 'pending');
            } elseif ($status === 'ditolak') {
                $query->where('status', 'tolak');
            } elseif ($status === 'dibatalkan') {
                $query->where('status', 'batal');
            } else {
                $query->where('status', $status);
            }
        }

        if ($search) {
            $query->where('penarikan_id', 'like', "%$search%");
        }

        $penarikan = $query->paginate(10);

        // Count for each status
        $counts = [
            'menunggu' => Penarikan::where('nasabah_id', $nasabah->nasabah_id)->where('status', 'pending')->count(),
            'selesai' => Penarikan::where('nasabah_id', $nasabah->nasabah_id)->where('status', 'selesai')->count(),
            'ditolak' => Penarikan::where('nasabah_id', $nasabah->nasabah_id)->where('status', 'tolak')->count(),
            'dibatalkan' => Penarikan::where('nasabah_id', $nasabah->nasabah_id)->where('status', 'batal')->count(),
        ];

        return response()->json([
            'penarikan' => $penarikan,
            'counts' => $counts
        ], 200);
    }

    public function cancel(Request $request, $id)
    {
        $nasabah = $request->user();
        $penarikan = Penarikan::where('nasabah_id', $nasabah->nasabah_id)
            ->where('penarikan_id', $id)
            ->firstOrFail();

        if ($penarikan->status !== 'pending') {
            return response()->json([
                'message' => 'Hanya request dengan status menunggu yang dapat dibatalkan.'
            ], 400);
        }

        $penarikan->status = 'batal';
        $penarikan->save();

        return response()->json([
            'message' => 'Request Penarikan Berhasil Dibatalkan',
            'data' => $penarikan
        ], 200);
    }

    public function getData(Request $request)
    {
        $nasabah = $request->user();
        return response()->json([
            'saldo_tersedia' => $nasabah->saldo_tersedia,
            'completion_percentage' => $nasabah->completion_percentage,
            'rekening_profil' => [
                'nama_bank' => $nasabah->nama_bank,
                'no_rekening' => $nasabah->no_rekening,
                'nama_rek' => $nasabah->nama_rek,
            ]
        ]);
    }

    public function store(Request $request) {
        $nasabah = $request->user();

        $validated = $request->validate([
            'jumlah' => 'required|integer|min:1',
            'no_rekening' => 'required|string',
            'nama_bank' => 'required|string|in:BRI,BCA,DANA,Bank Jago,Bank Mandiri,BNI,OVO,GoPay,LinkAja,ShopeePay,CIMB Niaga,Bank Permata,BSI',
            'nama_rek' => 'required|string',
        ]);

        if ($nasabah->saldo_tersedia < $validated['jumlah']) {
            return response()->json([
                'message' => 'Saldo tersedia tidak mencukupi untuk penarikan ini.'
            ], 400);
        }

        $penarikan = Penarikan::create([
            'jumlah' => $validated['jumlah'],
            'status' => 'pending',
            'nasabah_id' => $nasabah->nasabah_id,
            'no_rekening' => $validated['no_rekening'],
            'nama_bank' => $validated['nama_bank'],
            'nama_rek' => $validated['nama_rek'],
            'saldo_sebelum' => $nasabah->saldo,
        ]);

        return response()->json([
            'message' => 'Request Penarikan Berhasil Dibuat',
            'data' => $penarikan
        ], 200);
    }
}
