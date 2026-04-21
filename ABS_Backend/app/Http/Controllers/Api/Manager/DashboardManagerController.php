<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use App\Models\Nasabah;
use App\Models\Gudang;
use App\Models\Penimbangan;
use App\Models\Penjemputan;
use App\Models\Penarikan;
use Illuminate\Http\Request;

class DashboardManagerController extends Controller
{
    public function index()
    {
        // Total Petugas Aktif (seluruh petugas)
        $totalPetugas = Petugas::count();

        // Total Sampah kg (Statis sesuai request)
        $totalSampah = "X";

        // Nasabah Terverifikasi (total jumlah akun nasabah)
        $nasabahVerifikasi = Nasabah::where('status', 'aktif')->count();

        // Transaksi Bulan Ini (Statis sesuai request)
        $transaksiBulanIni = "X";

        // Recent activities - Dynamic data
        $activities = [];

        // 1. Latest Nasabah
        $nasabahs = Nasabah::where('status', 'aktif')->latest()->take(3)->get();
        foreach ($nasabahs as $n) {
            $activities[] = [
                'id' => 'nasabah-' . $n->nasabah_id,
                'type' => 'nasabah',
                'title' => 'Nasabah terverifikasi',
                'description' => $n->nama,
                'time' => $n->created_at->format('H:i'),
                'date' => $n->created_at->toDateString(),
                'timestamp' => $n->created_at->timestamp,
            ];
        }

        // 2. Latest Penimbangan (Setor Manual)
        $penimbangans = Penimbangan::with('nasabah')->latest()->take(3)->get();
        foreach ($penimbangans as $p) {
            $activities[] = [
                'id' => 'penimbangan-' . $p->penimbangan_id,
                'type' => 'transaksi',
                'title' => 'Setor manual selesai',
                'description' => $p->nasabah->nama ?? 'Nasabah Umum',
                'time' => $p->created_at->format('H:i'),
                'date' => $p->created_at->toDateString(),
                'timestamp' => $p->created_at->timestamp,
            ];
        }

        // 3. Latest Penarikan
        $penarikans = Penarikan::with('nasabah')->latest()->take(3)->get();
        foreach ($penarikans as $pn) {
            $activities[] = [
                'id' => 'penarikan-' . $pn->penarikan_id,
                'type' => 'laporan',
                'title' => 'Request penarikan saldo',
                'description' => $pn->nasabah->nama ?? 'User',
                'time' => $pn->created_at->format('H:i'),
                'date' => $pn->created_at->toDateString(),
                'timestamp' => $pn->created_at->timestamp,
            ];
        }

        // 4. Latest Gudang Updates
        $gudangs = Gudang::latest('updated_at')->take(2)->get();
        foreach ($gudangs as $g) {
            $activities[] = [
                'id' => 'gudang-' . $g->gudang_id,
                'type' => 'gudang',
                'title' => 'Gudang diperbarui',
                'description' => $g->nama,
                'time' => $g->updated_at->format('H:i'),
                'date' => $g->updated_at->toDateString(),
                'timestamp' => $g->updated_at->timestamp,
            ];
        }

        // Sort combined activities by timestamp descending
        usort($activities, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        // Take only top 10
        $activities = array_slice($activities, 0, 10);

        return response()->json([
            'stats' => [
                'total_petugas' => $totalPetugas,
                'total_sampah' => $totalSampah,
                'nasabah_verifikasi' => $nasabahVerifikasi,
                'transaksi_bulan_ini' => $transaksiBulanIni,
            ],
            'activities' => $activities
        ]);
    }
}
