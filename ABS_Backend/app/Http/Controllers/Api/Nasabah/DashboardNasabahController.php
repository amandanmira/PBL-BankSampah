<?php

namespace App\Http\Controllers\Api\Nasabah;

use App\Http\Controllers\Controller;
use App\Models\Penimbangan;
use App\Models\Penjemputan;
use App\Models\Penarikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardNasabahController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Fetch fresh nasabah data from DB to ensure latest saldo
        $nasabah = \App\Models\Nasabah::find($user->nasabah_id);
        
        if (!$nasabah) {
            return response()->json(['message' => 'Nasabah not found'], 404);
        }

        // 1. Saldo Tersedia (using model attribute)
        $saldoTersedia = (float) $nasabah->saldo_tersedia;

        // 2. Total Sampah (Sum of berat_timbang from all completed transactions)
        $totalSampah = Penimbangan::where('nasabah_id', $nasabah->nasabah_id)
            ->whereHas('transaksi', function($q) {
                $q->where('status', 'selesai');
            })
            ->sum('berat_timbang') ?? 0;

        // 3. Total Transaksi (Count of unique transaction sessions with status selesai)
        $totalTransaksi = \App\Models\TransaksiNasabah::where('status', 'selesai')
            ->whereHas('penimbangan', function($query) use ($nasabah) {
                $query->where('nasabah_id', $nasabah->nasabah_id);
            })->count();

        // Activities
        $activities = [];

        // Penimbangan (Deposits)
        $penimbangans = Penimbangan::where('nasabah_id', $nasabah->nasabah_id)
            ->with(['sampah.itemSampah', 'penjemputan'])
            ->latest()
            ->take(5)
            ->get();
            
        foreach ($penimbangans as $p) {
            $typeText = $p->penjemputan_id ? 'Jemput sampah' : 'Setor manual';
            $descriptionText = $typeText . ' - ' . floatval($p->berat_timbang) . 'kg';
            $price = $p->berat_timbang * ($p->sampah->itemSampah->harga_beli ?? 0);
            
            $activities[] = [
                'id' => 'p-' . $p->penimbangan_id,
                'type' => 'setor_sampah',
                'title' => 'Setor Sampah',
                'description' => $descriptionText,
                'time' => $p->created_at->format('Y-m-d'),
                'amount' => '+Rp ' . number_format($price, 0, ',', '.'),
                'timestamp' => $p->created_at->timestamp,
            ];
        }

        // Penarikan (Withdrawals)
        $penarikans = Penarikan::where('nasabah_id', $nasabah->nasabah_id)
            ->latest()
            ->take(5)
            ->get();

        foreach ($penarikans as $pn) {
            $bankName = $pn->nama_bank ?? 'BCA';
            $activities[] = [
                'id' => 'pn-' . $pn->penarikan_id,
                'type' => 'penarikan',
                'title' => 'Penarikan',
                'description' => 'Transfer Bank ' . $bankName,
                'time' => $pn->created_at->format('Y-m-d'),
                'amount' => '-Rp ' . number_format($pn->jumlah, 0, ',', '.'),
                'timestamp' => $pn->created_at->timestamp,
            ];
        }

        // Sort and slice activities
        usort($activities, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });
        $activities = array_slice($activities, 0, 10);

        // Chart Data (Last 6 Months)
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthLabel = $month->translatedFormat('M');
            
            $monthlyVolume = Penimbangan::where('nasabah_id', $nasabah->nasabah_id)
                ->whereHas('transaksi', function($q) {
                    $q->where('status', 'selesai');
                })
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('berat_timbang');

            // Income is sum of (berat * harga_beli)
            $monthlyIncome = Penimbangan::where('nasabah_id', $nasabah->nasabah_id)
                ->whereHas('transaksi', function($q) {
                    $q->where('status', 'selesai');
                })
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->with(['sampah.itemSampah'])
                ->get()
                ->sum(function($p) {
                    return $p->berat_timbang * ($p->sampah->itemSampah->harga_beli ?? 0);
                });

            $chartData[] = [
                'month' => $monthLabel,
                'volume' => (float)$monthlyVolume,
                'income' => (float)$monthlyIncome,
            ];
        }

        // Top Nasabah (Current Month)
        $topNasabah = \App\Models\Nasabah::withCount(['penimbangan as total_transaksi' => function($q) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year)
                  ->whereHas('transaksi', function($t) {
                      $t->where('status', 'selesai');
                  });
            }])
            ->withSum(['penimbangan as total_sampah' => function($q) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year)
                  ->whereHas('transaksi', function($t) {
                      $t->where('status', 'selesai');
                  });
            }], 'berat_timbang')
            ->orderByDesc('total_sampah')
            ->take(5)
            ->get()
            ->map(function($n, $index) {
                return [
                    'rank' => $index + 1,
                    'nama' => $n->nama,
                    'total_transaksi' => $n->total_transaksi ?? 0,
                    'total_sampah' => (float)($n->total_sampah ?? 0),
                ];
            });

        return response()->json([
            'stats' => [
                'saldo_tersedia' => $saldoTersedia,
                'total_sampah' => (float) $totalSampah,
                'total_transaksi' => $totalTransaksi,
            ],
            'user' => [
                'nama' => $nasabah->nama,
                'username' => $nasabah->username,
                'saldo' => $nasabah->saldo,
                'saldo_tersedia' => $nasabah->saldo_tersedia,
                'alamat' => $nasabah->alamat,
            ],
            'chart_data' => $chartData,
            'top_nasabah' => $topNasabah,
            'activities' => $activities
        ]);
    }

    public function transaksiBulanIni(Request $request)
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // 1. Fetch Transaksi Nasabah this month
        $nasabahTrans = \App\Models\TransaksiNasabah::with(['penimbangan.nasabah'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get()
            ->map(function ($t) {
                $nasabahName = $t->penimbangan->first()?->nasabah?->nama ?? 'Nasabah';
                return [
                    'transaksi_id' => $t->transaksi_id,
                    'kode' => 'TXN-' . str_pad($t->transaksi_id, 5, '0', STR_PAD_LEFT),
                    'tipe' => 'Nasabah',
                    'pelaku' => $nasabahName,
                    'tanggal' => $t->created_at->format('d M Y H:i'),
                    'status' => $t->status,
                    'total' => 'Rp ' . number_format($t->total_harga, 0, ',', '.'),
                    'keterangan' => $t->tipe_transaksi === 'jemput' ? 'Penjemputan Sampah' : 'Setor Manual',
                    'created_timestamp' => $t->created_at->timestamp
                ];
            });

        // 2. Fetch Transaksi Pengepul this month
        $pengepulTrans = \App\Models\TransaksiPengepul::with(['pengepul', 'detailTransaksi'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get()
            ->map(function ($t) {
                $pengepulName = $t->pengepul?->nama ?? 'Pengepul';
                $totalHarga = $t->detailTransaksi->sum(function ($d) {
                    return $d->harga * $d->berat;
                });
                return [
                    'transaksi_id' => $t->transaksi_id,
                    'kode' => 'TXP-' . str_pad($t->transaksi_id, 5, '0', STR_PAD_LEFT),
                    'tipe' => 'Pengepul',
                    'pelaku' => $pengepulName,
                    'tanggal' => $t->created_at->format('d M Y H:i'),
                    'status' => $t->status,
                    'total' => 'Rp ' . number_format($totalHarga, 0, ',', '.'),
                    'keterangan' => 'Pembelian Sampah',
                    'created_timestamp' => $t->created_at->timestamp
                ];
            });

        // Merge and sort
        $merged = $nasabahTrans->concat($pengepulTrans)->sortByDesc('created_timestamp')->values();

        // Paginate manually
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 5);
        $offset = ($page - 1) * $perPage;

        $sliced = $merged->slice($offset, $perPage)->values();
        $total = $merged->count();

        return response()->json([
            'data' => $sliced,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => (int) ceil($total / $perPage)
        ]);
    }
}
