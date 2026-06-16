<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Penarikan;
use App\Models\Penjemputan;
use App\Models\Penimbangan;
use App\Models\TransaksiNasabah;
use App\Models\TransaksiPengepul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardPetugasController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');
        $user = Auth::user();
        $today = Carbon::today();

        // 1. Pickup Requests Today
        $pickupRequests = Penjemputan::whereDate('created_at', $today)->count();

        // 2. Withdrawal Requests Today
        $withdrawalRequests = Penarikan::whereDate('created_at', $today)->count();

        // 3. Pengepul Orders Today
        $pengepulRequests = TransaksiPengepul::whereDate('created_at', $today)->count();

        // 4. Total Transactions Finished Today (Sum of various finished activities)
        $finishedPickup = TransaksiNasabah::whereDate('created_at', $today)
            ->where('tipe_transaksi', 'dijemput')
            ->where('status', 'selesai')
            ->count();
        $finishedManual = TransaksiNasabah::whereDate('created_at', $today)
            ->where('tipe_transaksi', 'antar_sendiri')
            ->where('status', 'selesai')
            ->count();
        $finishedWithdrawal = Penarikan::whereDate('updated_at', $today)
            ->where('status', 'selesai')
            ->count();
        $finishedPengepul = TransaksiPengepul::whereDate('updated_at', $today)
            ->where('status', 'selesai')
            ->count();

        $totalFinished = $finishedPickup + $finishedManual + $finishedWithdrawal + $finishedPengepul;

        // 5. Total Waste Today (kg)
        $todayWaste = Penimbangan::whereDate('created_at', $today)
            ->whereHas('transaksi', function ($query) {
                $query->where('status', 'selesai');
            })
            ->sum('berat_timbang');

        // Attention Items (Pending/Proses items)
        $attentionItems = [];

        // Pending/Proses Pickups
        $latestPickup = Penjemputan::with('nasabah')
            ->whereIn('status', ['dijemput', 'perlu_input'])
            ->latest()
            ->take(3)
            ->get();

        foreach ($latestPickup as $p) {
            $statusLabel = $p->status === 'perlu_input' ? 'Perlu Input' : 'Menunggu';
            $attentionItems[] = [
                'id' => 'REQ-' . str_pad($p->penjemputan_id, 3, '0', STR_PAD_LEFT),
                'type' => 'pickup',
                'name' => $p->nasabah->nama ?? 'Nasabah',
                'address' => $p->alamat,
                'time' => $p->created_at->diffForHumans(),
                'status' => $statusLabel,
                'action' => 'Lihat Detail',
                'color' => 'bg-[#4A7043]'
            ];
        }

        // Attention Items: Withdrawal Requests (Waiting)
        $latestWithdrawal = Penarikan::with('nasabah')
            ->where('status', 'pending')
            ->latest()
            ->take(2)
            ->get();

        foreach ($latestWithdrawal as $w) {
            $attentionItems[] = [
                'id' => 'WD-' . str_pad($w->penarikan_id, 3, '0', STR_PAD_LEFT),
                'type' => 'withdrawal',
                'name' => $w->nasabah->nama ?? 'Nasabah',
                'address' => 'Rp ' . number_format($w->jumlah, 0, ',', '.'),
                'time' => $w->created_at->diffForHumans(),
                'status' => 'Menunggu',
                'action' => 'Setujui',
                'color' => 'bg-[#5FA09B]'
            ];
        }

        // Recent Activities
        $activities = [];

        // Latest Penimbangan
        $latestPenimbangan = Penimbangan::with(['nasabah', 'sampah.itemSampah'])
            ->latest()
            ->take(5)
            ->get();

        foreach ($latestPenimbangan as $p) {
            $activities[] = [
                'id' => 'p-' . $p->penimbangan_id,
                'icon' => 'material-symbols:delete-outline',
                'iconBg' => 'bg-green-50 text-green-600',
                'title' => 'Setoran ' . ($p->sampah->itemSampah->nama ?? 'Sampah'),
                'user' => $p->nasabah->nama ?? 'Nasabah',
                'ref' => 'TR-' . $p->transaksi_id,
                'time' => $p->created_at->format('H:i'),
                'timestamp' => $p->created_at->timestamp
            ];
        }

        // Latest Withdrawals
        $latestWithdrawals = Penarikan::with('nasabah')
            ->where('status', 'selesai')
            ->latest()
            ->take(3)
            ->get();

        foreach ($latestWithdrawals as $w) {
            $activities[] = [
                'id' => 'w-' . $w->penarikan_id,
                'icon' => 'material-symbols:account-balance-wallet-outline',
                'iconBg' => 'bg-[#FFF2EB] text-[#E67E22]',
                'title' => 'Penarikan Dana Selesai',
                'user' => $w->nasabah->nama ?? 'Nasabah',
                'ref' => 'WD-' . $w->penarikan_id,
                'time' => $w->created_at->format('H:i'),
                'timestamp' => $w->created_at->timestamp
            ];
        }

        // Sort activities by timestamp
        usort($activities, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });
        $activities = array_slice($activities, 0, 8);

        // Report Summary Today
        $pickupCount = $finishedPickup;
        $pickupValue = TransaksiNasabah::whereDate('created_at', $today)
            ->where('tipe_transaksi', 'jemput')
            ->where('status', 'selesai')
            ->sum('total_harga');

        $manualCount = $finishedManual;
        $manualValue = TransaksiNasabah::whereDate('created_at', $today)
            ->where('tipe_transaksi', 'antar_sendiri')
            ->where('status', 'selesai')
            ->sum('total_harga');

        $withdrawalCount = $finishedWithdrawal;
        $withdrawalValue = Penarikan::whereDate('updated_at', $today)
            ->where('status', 'selesai')
            ->sum('jumlah');

        return response()->json([
            'stats' => [
                'pickup_requests' => $pickupRequests,
                'withdrawal_requests' => $withdrawalRequests,
                'pengepul_requests' => $pengepulRequests,
                'total_finished' => $totalFinished,
                'today_waste' => (float)$todayWaste,
            ],
            'attention_items' => $attentionItems,
            'activities' => $activities,
            'report_summary' => [
                'pickup' => [
                    'count' => $pickupCount,
                    'value' => number_format($pickupValue, 0, ',', '.')
                ],
                'manual_deposit' => [
                    'count' => $manualCount,
                    'value' => number_format($manualValue, 0, ',', '.')
                ],
                'withdrawal' => [
                    'count' => $withdrawalCount,
                    'value' => number_format($withdrawalValue, 0, ',', '.')
                ]
            ]
        ]);
    }
}
