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
            ->with(['sampah.itemSampah'])
            ->latest()
            ->take(5)
            ->get();
            
        foreach ($penimbangans as $p) {
            $sampahName = $p->sampah->itemSampah->nama ?? 'Sampah';
            $activities[] = [
                'id' => 'p-' . $p->penimbangan_id,
                'type' => 'transaksi',
                'title' => 'Setor Sampah',
                'description' => $sampahName . ' (' . $p->berat_timbang . ' kg)',
                'time' => $p->created_at->diffForHumans(),
                'timestamp' => $p->created_at->timestamp,
            ];
        }

        // Penjemputan (Pickup Requests)
        $penjemputans = Penjemputan::where('nasabah_id', $nasabah->nasabah_id)
            ->latest()
            ->take(5)
            ->get();

        foreach ($penjemputans as $pj) {
            $activities[] = [
                'id' => 'pj-' . $pj->penjemputan_id,
                'type' => 'nasabah',
                'title' => 'Request Penjemputan',
                'description' => 'Status: ' . ucfirst($pj->status),
                'time' => $pj->created_at->diffForHumans(),
                'timestamp' => $pj->created_at->timestamp,
            ];
        }

        // Penarikan (Withdrawals)
        $penarikans = Penarikan::where('nasabah_id', $nasabah->nasabah_id)
            ->latest()
            ->take(5)
            ->get();

        foreach ($penarikans as $pn) {
            $activities[] = [
                'id' => 'pn-' . $pn->penarikan_id,
                'type' => 'laporan',
                'title' => 'Request Penarikan',
                'description' => 'Rp ' . number_format($pn->jumlah, 0, ',', '.'),
                'time' => $pn->created_at->diffForHumans(),
                'timestamp' => $pn->created_at->timestamp,
            ];
        }

        // Sort and slice activities
        usort($activities, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });
        $activities = array_slice($activities, 0, 10);

        return response()->json([
            'stats' => [
                'saldo_tersedia' => $saldoTersedia,
                'total_sampah' => (float) $totalSampah,
                'total_transaksi' => $totalTransaksi,
            ],
            'user' => [
                'nama' => $nasabah->nama,
                'username' => $nasabah->username,
            ],
            'activities' => $activities
        ]);
    }
}
