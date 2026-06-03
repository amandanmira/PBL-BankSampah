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
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();
        $startOfWeek = $now->copy()->startOfWeek();

        // Total Petugas Aktif
        $totalPetugas = Petugas::count();
        $petugasBulanIni = Petugas::where('created_at', '>=', $startOfMonth)->count();
        $petugasIncrease = '+' . $petugasBulanIni . ' bulan ini';

        // Total Sampah kg
        $totalSampah = Penimbangan::sum('berat_timbang');
        $sampahBulanIni = Penimbangan::whereBetween('created_at', [$startOfMonth, $now])->sum('berat_timbang');
        $sampahBulanLalu = Penimbangan::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->sum('berat_timbang');
        
        $sampahGrowth = 0;
        if ($sampahBulanLalu > 0) {
            $sampahGrowth = (($sampahBulanIni - $sampahBulanLalu) / $sampahBulanLalu) * 100;
        } elseif ($sampahBulanIni > 0) {
            $sampahGrowth = 100;
        }
        $sampahSign = $sampahGrowth >= 0 ? '+' : '';
        $sampahIncrease = $sampahSign . round($sampahGrowth, 1) . '%';

        // Nasabah Terverifikasi
        $nasabahVerifikasi = Nasabah::where('status', 'aktif')->count();
        $nasabahMingguIni = Nasabah::where('status', 'aktif')->where('created_at', '>=', $startOfWeek)->count();
        $nasabahIncrease = '+' . $nasabahMingguIni . ' minggu ini';

        // Transaksi Bulan Ini (Dynamic: combined nasabah and pengepul transactions)
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $nasabahTransCount = \App\Models\TransaksiNasabah::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $pengepulTransCount = \App\Models\TransaksiPengepul::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $transaksiBulanIni = $nasabahTransCount + $pengepulTransCount;

        // Transaksi Bulan Lalu for growth calculation
        $startOfLastMonth = now()->subMonth()->startOfMonth();
        $endOfLastMonth = now()->subMonth()->endOfMonth();
        $nasabahTransCountLalu = \App\Models\TransaksiNasabah::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $pengepulTransCountLalu = \App\Models\TransaksiPengepul::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $transaksiBulanLalu = $nasabahTransCountLalu + $pengepulTransCountLalu;

        $trxGrowth = 0;
        if ($transaksiBulanLalu > 0) {
            $trxGrowth = (($transaksiBulanIni - $transaksiBulanLalu) / $transaksiBulanLalu) * 100;
        } elseif ($transaksiBulanIni > 0) {
            $trxGrowth = 100;
        }
        $trxSign = $trxGrowth >= 0 ? '+' : '';
        $trxIncrease = $trxSign . round($trxGrowth, 1) . '%';

        // Total Gudang
        $totalGudang = Gudang::count();
        $gudangIncrease = $totalGudang . ' dari ' . $totalGudang . ' online';

        // Total Gudang
        $totalGudang = Gudang::count();
        $activeGudang = Gudang::where('active', 1)->count();

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
                'petugas_increase' => $petugasIncrease,
                'total_sampah' => $totalSampah,
                'sampah_increase' => $sampahIncrease,
                'nasabah_verifikasi' => $nasabahVerifikasi,
                'nasabah_increase' => $nasabahIncrease,
                'transaksi_bulan_ini' => $transaksiBulanIni,
                'transaksi_increase' => $trxIncrease,
                'total_gudang' => $totalGudang,
                'gudang_increase' => $gudangIncrease,
                'active_gudang' => $activeGudang,
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

        $gudangStatusRaw = \Illuminate\Support\Facades\DB::table('gudangs')
            ->leftJoin('sampahs', 'gudangs.gudang_id', '=', 'sampahs.gudang_id')
            ->select(
                'gudangs.gudang_id',
                'gudangs.alamat as nama',
                'gudangs.kapasitas',
                \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(sampahs.stok), 0) as total_stok')
            )
            ->groupBy('gudangs.gudang_id', 'gudangs.alamat', 'gudangs.kapasitas')
            ->get();

        $distribusiRaw = \Illuminate\Support\Facades\DB::table('sampahs')
            ->join('item_sampahs', 'sampahs.item_id', '=', 'item_sampahs.item_id')
            ->join('kategori_sampahs', 'item_sampahs.kategori_id', '=', 'kategori_sampahs.kategori_id')
            ->select(
                'kategori_sampahs.kategori_id',
                'kategori_sampahs.nama as nama',
                \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(sampahs.stok), 0) as total_stok')
            )
            ->groupBy('kategori_sampahs.kategori_id', 'kategori_sampahs.nama')
            ->get();

        $detailSampahRaw = \Illuminate\Support\Facades\DB::table('sampahs')
            ->join('item_sampahs', 'sampahs.item_id', '=', 'item_sampahs.item_id')
            ->join('kategori_sampahs', 'item_sampahs.kategori_id', '=', 'kategori_sampahs.kategori_id')
            ->join('gudangs', 'sampahs.gudang_id', '=', 'gudangs.gudang_id')
            ->select(
                'kategori_sampahs.kategori_id',
                'item_sampahs.nama as nama_item',
                'gudangs.alamat as nama_gudang',
                'sampahs.stok',
                'item_sampahs.harga_beli',
                'item_sampahs.harga_jual'
            )
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
            'distribusiSaatIni' => $distribusiRaw,
            'detailSampah' => $detailSampahRaw
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
