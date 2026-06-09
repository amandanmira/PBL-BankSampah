<?php

namespace App\Http\Controllers\Api\Nasabah;

use App\Http\Controllers\Controller;
use App\Models\Penimbangan;
use App\Models\Penjemputan;
use App\Models\Penarikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Pastikan Carbon dipanggil

class DashboardNasabahController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $nasabah = \App\Models\Nasabah::find($user->nasabah_id);
        
        if (!$nasabah) {
            return response()->json(['message' => 'Nasabah not found'], 404);
        }

        $saldoTersedia = (float) $nasabah->saldo_tersedia;

        $totalSampah = Penimbangan::where('nasabah_id', $nasabah->nasabah_id)
            ->whereHas('transaksi', function($q) {
                $q->where('status', 'selesai');
            })
            ->sum('berat_timbang') ?? 0;

        $totalTransaksi = \App\Models\TransaksiNasabah::where('status', 'selesai')
            ->whereHas('penimbangan', function($query) use ($nasabah) {
                $query->where('nasabah_id', $nasabah->nasabah_id);
            })->count();

        // ---------------------------------------------------------
        // LOGIKA AKTIVITAS TERBARU DENGAN BATAS 1 JAM UNTUK STATUS FINAL
        // ---------------------------------------------------------
        $activities = [];
        $now = now();
        $completedStatuses = ['selesai', 'tolak', 'ditolak', 'batal'];

        // 1. Penimbangan (Setor Sampah)
        $penimbangans = Penimbangan::where('nasabah_id', $nasabah->nasabah_id)
            ->with(['sampah.itemSampah', 'penjemputan', 'transaksi'])
            ->latest()
            ->take(20) // Ambil lebih banyak untuk difilter
            ->get();
            
        foreach ($penimbangans as $p) {
            $status = strtolower($p->transaksi->status ?? 'menunggu');
            $date = Carbon::parse($p->updated_at ?? $p->created_at ?? $now);
            $isCompleted = in_array($status, $completedStatuses);
            
            // Tampilkan jika belum selesai, ATAU jika selesai umurnya <= 60 menit (1 jam)
            if (!$isCompleted || ($isCompleted && $date->diffInMinutes($now) <= 60)) {
                $typeText = $p->penjemputan_id ? 'Jemput sampah' : 'Setor manual';
                // Tambahkan status di deskripsi agar nasabah tahu
                $descriptionText = $typeText . ' - ' . floatval($p->berat_timbang) . 'kg (' . ucfirst(str_replace('_', ' ', $status)) . ')';
                $price = $p->berat_timbang * ($p->sampah->itemSampah->harga_beli ?? 0);
                
                $activities[] = [
                    'id' => 'p-' . $p->penimbangan_id,
                    'type' => 'setor_sampah',
                    'title' => 'Setor Sampah',
                    'description' => $descriptionText,
                    'time' => $date->diffForHumans(), // Gunakan format waktu relatif seperti "10 minutes ago"
                    'amount' => '+Rp ' . number_format($price, 0, ',', '.'),
                    'timestamp' => $date->timestamp,
                ];
            }
        }

        // 2. Penarikan Saldo
        $penarikans = Penarikan::where('nasabah_id', $nasabah->nasabah_id)
            ->latest()
            ->take(20)
            ->get();

        foreach ($penarikans as $pn) {
            $status = strtolower($pn->status ?? 'menunggu');
            $date = Carbon::parse($pn->updated_at ?? $pn->created_at ?? $now);
            $isCompleted = in_array($status, $completedStatuses);

            if (!$isCompleted || ($isCompleted && $date->diffInMinutes($now) <= 60)) {
                $bankName = $pn->nama_bank ?? 'BCA';
                $activities[] = [
                    'id' => 'pn-' . $pn->penarikan_id,
                    'type' => 'penarikan',
                    'title' => 'Penarikan',
                    'description' => 'Transfer Bank ' . $bankName . ' (' . ucfirst(str_replace('_', ' ', $status)) . ')',
                    'time' => $date->diffForHumans(),
                    'amount' => '-Rp ' . number_format($pn->jumlah, 0, ',', '.'),
                    'timestamp' => $date->timestamp,
                ];
            }
        }

        // Urutkan aktivitas agar yang terbaru selalu di atas
        usort($activities, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });
        
        // Hanya kirim 10 data teratas ke frontend
        $activities = array_slice($activities, 0, 10);

        // ---------------------------------------------------------
        // (Sisa Kode Sama Persis dengan Milik Anda)
        // ---------------------------------------------------------
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

        // Penjemputan aktif: menunggu_persetujuan, proses, dijemput
        $penjemputanPending = Penjemputan::where('nasabah_id', $nasabah->nasabah_id)
            ->whereIn('status', ['menunggu_persetujuan', 'proses', 'dijemput', 'perlu_input'])
            ->with(['tukang'])
            ->latest()
            ->get()
            ->map(function($pj) {
                $foto = $pj->foto;
                if (is_string($foto)) {
                    $decoded = json_decode($foto, true);
                    $foto = is_array($decoded) ? $decoded : [$foto];
                }
                if (!is_array($foto)) {
                    $foto = [];
                }
                return [
                    'penjemputan_id' => $pj->penjemputan_id,
                    'deskripsi'      => $pj->deskripsi,
                    'alamat'         => $pj->alamat,
                    'foto'           => $foto,
                    'status'         => $pj->status,
                    'ket_status'     => $pj->ket_status,
                    'jadwal'         => $pj->jadwal,
                    'created_at'     => $pj->created_at->toIso8601String(),
                    'tukang'         => $pj->tukang ? [
                        'nama'     => $pj->tukang->nama,
                        'no_telp'  => $pj->tukang->no_telp,
                        'foto'     => $pj->tukang->foto,
                    ] : null,
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
            'activities' => $activities,
            'penjemputan_pending' => $penjemputanPending,
        ]);
    }

    public function transaksiBulanIni(Request $request)
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

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

        $merged = $nasabahTrans->concat($pengepulTrans)->sortByDesc('created_timestamp')->values();

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