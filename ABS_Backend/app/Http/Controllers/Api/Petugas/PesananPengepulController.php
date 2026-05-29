<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\TransaksiPengepul;
use App\Models\DetailTransaksi;
use App\Models\Sampah;
use App\Models\KonfigurasiWeb;

class PesananPengepulController extends Controller
{
    /**
     * Display a listing of collector orders filtered by tab and search keywords.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $gudangId = $user->gudang_id;

        if (!$gudangId) {
            return response()->json([
                'message' => 'Akun Petugas tidak terasosiasi dengan Gudang manapun.'
            ], 400);
        }

        $activeTab = $request->input('status', 'perlu_validasi'); // perlu_validasi, siap_diambil, selesai, ditolak
        $search = $request->input('search', '');

        // Base query for transactions
        $baseQuery = TransaksiPengepul::with([
            'pengepul',
            'detailTransaksi.sampah.itemSampah',
            'detailTransaksi.sampah.gudang'
        ])->whereHas('detailTransaksi.sampah', function ($q) use ($gudangId) {
            $q->where('gudang_id', $gudangId);
        });

        // Apply search if provided
        if (!empty($search)) {
            $baseQuery->where(function ($q) use ($search) {
                // If searching for PSN-001 pattern
                if (preg_match('/PSN-(\d+)/i', $search, $matches)) {
                    $id = (int)$matches[1];
                    $q->where('transaksi_id', $id);
                } else {
                    $q->where('transaksi_id', 'like', "%{$search}%")
                      ->orWhereHas('pengepul', function ($sub) use ($search) {
                          $sub->where('nama', 'like', "%{$search}%");
                      })
                      ->orWhereHas('detailTransaksi.sampah.itemSampah', function ($sub) use ($search) {
                          $sub->where('nama', 'like', "%{$search}%");
                      });
                }
            });
        }

        // 1. Count for Perlu Validasi
        // (status = pending and this warehouse has pending items) OR (status = proses and bukti_transfer is uploaded)
        $perluValidasiCount = TransaksiPengepul::where(function ($q) use ($gudangId) {
            $q->where(function ($sub) use ($gudangId) {
                $sub->where('status', 'pending')
                    ->whereHas('detailTransaksi', function ($dt) use ($gudangId) {
                        $dt->where('status', 'pending')
                           ->whereHas('sampah', function ($s) use ($gudangId) {
                               $s->where('gudang_id', $gudangId);
                           });
                    });
            })->orWhere(function ($sub) {
                $sub->where('status', 'proses')
                    ->whereNotNull('bukti_transfer')
                    ->where('bukti_transfer', '!=', '');
            });
        })->count();

        // 2. Count for Siap Diambil
        $siapDiambilCount = TransaksiPengepul::where('status', 'siap_diambil')
            ->whereHas('detailTransaksi.sampah', function ($q) use ($gudangId) {
                $q->where('gudang_id', $gudangId);
            })->count();

        // 3. Count for Selesai
        $selesaiCount = TransaksiPengepul::where('status', 'selesai')
            ->whereHas('detailTransaksi.sampah', function ($q) use ($gudangId) {
                $q->where('gudang_id', $gudangId);
            })->count();

        // 4. Count for Ditolak / Batal
        $ditolakCount = TransaksiPengepul::whereIn('status', ['tolak', 'batal'])
            ->whereHas('detailTransaksi.sampah', function ($q) use ($gudangId) {
                $q->where('gudang_id', $gudangId);
            })->count();

        // Apply Tab Filter to base query
        if ($activeTab === 'perlu_validasi') {
            $baseQuery->where(function ($q) use ($gudangId) {
                $q->where(function ($sub) use ($gudangId) {
                    $sub->where('status', 'pending')
                        ->whereHas('detailTransaksi', function ($dt) use ($gudangId) {
                            $dt->where('status', 'pending')
                               ->whereHas('sampah', function ($s) use ($gudangId) {
                                   $s->where('gudang_id', $gudangId);
                               });
                        });
                })->orWhere(function ($sub) {
                    $sub->where('status', 'proses')
                        ->whereNotNull('bukti_transfer')
                        ->where('bukti_transfer', '!=', '');
                });
            });
        } else {
            if ($activeTab === 'siap_diambil') {
                $baseQuery->where('status', 'siap_diambil');
            } elseif ($activeTab === 'selesai') {
                $baseQuery->where('status', 'selesai');
            } elseif ($activeTab === 'ditolak') {
                $baseQuery->whereIn('status', ['tolak', 'batal']);
            }
        }

        // Retrieve paginated records
        $orders = $baseQuery->latest()->paginate(10);

        return response()->json([
            'orders' => $orders,
            'counts' => [
                'perlu_validasi' => $perluValidasiCount,
                'siap_diambil' => $siapDiambilCount,
                'selesai' => $selesaiCount,
                'ditolak' => $ditolakCount
            ]
        ]);
    }

    /**
     * Approve stock for the current Petugas's warehouse on a transaction.
     */
    public function approveStock(Request $request, $id)
    {
        $user = Auth::user();
        $gudangId = $user->gudang_id;

        if (!$gudangId) {
            return response()->json(['message' => 'Akun Petugas tidak terasosiasi dengan Gudang.'], 400);
        }

        $transaksi = TransaksiPengepul::with('detailTransaksi.sampah')->findOrFail($id);

        if ($transaksi->status !== 'pending') {
            return response()->json(['message' => 'Pesanan sudah tidak berada dalam tahap pending.'], 400);
        }

        // Set status = 'setuju' for details belonging to this officer's warehouse
        DetailTransaksi::where('transaksi_id', $id)
            ->whereHas('sampah', function ($q) use ($gudangId) {
                $q->where('gudang_id', $gudangId);
            })
            ->update(['status' => 'setuju']);

        // Check if ALL details in the order are now approved ('setuju')
        $allDetailsCount = DetailTransaksi::where('transaksi_id', $id)->count();
        $approvedDetailsCount = DetailTransaksi::where('transaksi_id', $id)
            ->where('status', 'setuju')
            ->count();

        if ($allDetailsCount === $approvedDetailsCount) {
            // Update overall status to 'proses' and establish deadline
            $config = KonfigurasiWeb::first();
            $deadline = now()->addHours($config->lama_deadline ?? 24);

            $transaksi->update([
                'status' => 'proses',
                'deadline' => $deadline,
                'ket_status' => 'Stok disetujui oleh seluruh gudang. Menunggu pembayaran.'
            ]);
        } else {
            $transaksi->update([
                'ket_status' => 'Disetujui sebagian oleh Gudang ' . $gudangId . '.'
            ]);
        }

        return response()->json([
            'message' => 'Stok berhasil disetujui untuk Gudang Anda.',
            'data' => $transaksi->load('detailTransaksi')
        ]);
    }

    /**
     * Reject the order entirely, return stocks, and state a reason.
     */
    public function rejectOrder(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:500'
        ]);

        $transaksi = TransaksiPengepul::with('detailTransaksi')->findOrFail($id);

        // Cancel and restore stocks for all items in the transaction
        foreach ($transaksi->detailTransaksi as $d) {
            $sampah = Sampah::find($d->sampah_id);
            if ($sampah) {
                $sampah->update([
                    'stok' => $sampah->stok + $d->berat,
                ]);
            }
        }

        // Mark all items as rejected
        DetailTransaksi::where('transaksi_id', $id)->update(['status' => 'tolak']);

        $transaksi->update([
            'status' => 'tolak',
            'ket_status' => $request->alasan_penolakan
        ]);

        return response()->json([
            'message' => 'Pesanan berhasil ditolak.',
            'data' => $transaksi
        ]);
    }

    /**
     * Verify payment receipt uploaded by Pengepul.
     */
    public function verifyPayment(Request $request, $id)
    {
        $transaksi = TransaksiPengepul::findOrFail($id);

        if ($transaksi->status !== 'proses' || empty($transaksi->bukti_transfer)) {
            return response()->json([
                'message' => 'Pesanan belum dibayar atau tidak valid untuk diverifikasi.'
            ], 400);
        }

        $transaksi->update([
            'status' => 'siap_diambil',
            'ket_status' => 'Pembayaran tervalidasi. Silakan ambil barang di gudang.'
        ]);

        return response()->json([
            'message' => 'Pembayaran berhasil diverifikasi. Pesanan siap diambil.',
            'data' => $transaksi
        ]);
    }

    /**
     * Mark order as completed once Pengepul collects the goods.
     */
    public function completeOrder(Request $request, $id)
    {
        $transaksi = TransaksiPengepul::findOrFail($id);

        if ($transaksi->status !== 'siap_diambil') {
            return response()->json([
                'message' => 'Pesanan belum lunas atau belum siap diambil.'
            ], 400);
        }

        $transaksi->update([
            'status' => 'selesai',
            'ket_status' => 'Pesanan telah diambil oleh pengepul. Transaksi selesai.'
        ]);

        return response()->json([
            'message' => 'Pesanan berhasil diselesaikan.',
            'data' => $transaksi
        ]);
    }
}
