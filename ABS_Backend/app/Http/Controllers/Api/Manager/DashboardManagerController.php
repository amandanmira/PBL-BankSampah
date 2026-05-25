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

    public function charts(Request $request)
    {
        $period = $request->input('period', '6 Bulan');
        $monthsToSub = 5;
        if ($period == '3 Bulan') $monthsToSub = 2;
        if ($period == '1 Bulan') $monthsToSub = 0;

        $startDate = \Carbon\Carbon::now()->subMonths($monthsToSub)->startOfMonth();
        $endDate = \Carbon\Carbon::now()->endOfMonth();

        $trendData = \Illuminate\Support\Facades\DB::table('penimbangans')
            ->join('sampahs', 'penimbangans.sampah_id', '=', 'sampahs.sampah_id')
            ->join('item_sampahs', 'sampahs.item_id', '=', 'item_sampahs.item_id')
            ->join('kategori_sampahs', 'item_sampahs.kategori_id', '=', 'kategori_sampahs.kategori_id')
            ->whereBetween('penimbangans.created_at', [$startDate, $endDate])
            ->select(
                'kategori_sampahs.nama as kategori',
                \Illuminate\Support\Facades\DB::raw('YEAR(penimbangans.created_at) as year'),
                \Illuminate\Support\Facades\DB::raw('MONTH(penimbangans.created_at) as month'),
                \Illuminate\Support\Facades\DB::raw('SUM(penimbangans.berat_timbang) as total_berat')
            )
            ->groupBy('kategori', 'year', 'month')
            ->get();

        $allCategories = \Illuminate\Support\Facades\DB::table('kategori_sampahs')->pluck('nama')->toArray();

        $growthData = \Illuminate\Support\Facades\DB::table('penimbangans')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                \Illuminate\Support\Facades\DB::raw('YEAR(created_at) as year'),
                \Illuminate\Support\Facades\DB::raw('MONTH(created_at) as month'),
                \Illuminate\Support\Facades\DB::raw('SUM(berat_timbang) as total_berat')
            )
            ->groupBy('year', 'month')
            ->get();

        $gudangStatusRaw = \Illuminate\Support\Facades\DB::table('sampahs')
            ->join('gudangs', 'sampahs.gudang_id', '=', 'gudangs.gudang_id')
            ->select(
                'gudangs.alamat as nama',
                \Illuminate\Support\Facades\DB::raw('SUM(sampahs.stok) as total_stok')
            )
            ->groupBy('gudangs.gudang_id', 'gudangs.alamat')
            ->get();

        $distribusiRaw = \Illuminate\Support\Facades\DB::table('sampahs')
            ->join('item_sampahs', 'sampahs.item_id', '=', 'item_sampahs.item_id')
            ->join('kategori_sampahs', 'item_sampahs.kategori_id', '=', 'kategori_sampahs.kategori_id')
            ->select(
                'kategori_sampahs.nama as nama',
                \Illuminate\Support\Facades\DB::raw('SUM(sampahs.stok) as total_stok')
            )
            ->groupBy('kategori_sampahs.kategori_id', 'kategori_sampahs.nama')
            ->get();

        $monthsList = [];
        for ($i = $monthsToSub; $i >= 0; $i--) {
            $dt = \Carbon\Carbon::now()->subMonths($i);
            $monthsList[] = [
                'year' => $dt->year,
                'month' => $dt->month,
                'label' => $dt->locale('id')->shortMonthName
            ];
        }
        $trendCategories = array_column($monthsList, 'label');

        $trendSeries = [];
        foreach ($allCategories as $catName) {
            $dataArr = [];
            foreach ($monthsList as $m) {
                $found = $trendData->first(function($val) use ($catName, $m) {
                    return $val->kategori == $catName && $val->year == $m['year'] && $val->month == $m['month'];
                });
                $dataArr[] = $found ? (float)$found->total_berat : 0;
            }
            $trendSeries[] = [
                'name' => $catName,
                'data' => $dataArr
            ];
        }

        $initialTotal = \Illuminate\Support\Facades\DB::table('penimbangans')
            ->where('created_at', '<', $startDate)
            ->sum('berat_timbang');

        $growthSeriesData = [];
        $currentTotal = $initialTotal;
        foreach ($monthsList as $m) {
            $found = $growthData->first(function($val) use ($m) {
                return $val->year == $m['year'] && $val->month == $m['month'];
            });
            $monthTotal = $found ? (float)$found->total_berat : 0;
            $currentTotal += $monthTotal;
            $growthSeriesData[] = $currentTotal;
        }

        return response()->json([
            'trendCategories' => $trendCategories,
            'trendSeries' => $trendSeries,
            'growthSeries' => [
                ['name' => 'Total', 'data' => $growthSeriesData]
            ],
            'gudangStatus' => $gudangStatusRaw,
            'distribusiSaatIni' => $distribusiRaw
        ]);
    }
}
