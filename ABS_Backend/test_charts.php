<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$monthsToSub = 5;
$startDate = Carbon::now()->subMonths($monthsToSub)->startOfMonth();
$endDate = Carbon::now()->endOfMonth();

$trendData = DB::table('penimbangans')
    ->join('sampahs', 'penimbangans.sampah_id', '=', 'sampahs.sampah_id')
    ->join('item_sampahs', 'sampahs.item_id', '=', 'item_sampahs.item_id')
    ->join('kategori_sampahs', 'item_sampahs.kategori_id', '=', 'kategori_sampahs.kategori_id')
    ->whereBetween('penimbangans.created_at', [$startDate, $endDate])
    ->select(
        'kategori_sampahs.nama as kategori',
        DB::raw('YEAR(penimbangans.created_at) as year'),
        DB::raw('MONTH(penimbangans.created_at) as month'),
        DB::raw('SUM(penimbangans.berat_timbang) as total_berat')
    )
    ->groupBy('kategori', 'year', 'month')
    ->get();

$allCategories = DB::table('kategori_sampahs')->pluck('nama')->toArray();

$growthData = DB::table('penimbangans')
    ->whereBetween('created_at', [$startDate, $endDate])
    ->select(
        DB::raw('YEAR(created_at) as year'),
        DB::raw('MONTH(created_at) as month'),
        DB::raw('SUM(berat_timbang) as total_berat')
    )
    ->groupBy('year', 'month')
    ->get();

$gudangStatusRaw = DB::table('sampahs')
    ->join('gudangs', 'sampahs.gudang_id', '=', 'gudangs.gudang_id')
    ->select(
        'gudangs.alamat as nama',
        DB::raw('SUM(sampahs.stok) as total_stok')
    )
    ->groupBy('gudangs.gudang_id', 'gudangs.alamat')
    ->get();

$distribusiRaw = DB::table('sampahs')
    ->join('item_sampahs', 'sampahs.item_id', '=', 'item_sampahs.item_id')
    ->join('kategori_sampahs', 'item_sampahs.kategori_id', '=', 'kategori_sampahs.kategori_id')
    ->select(
        'kategori_sampahs.nama as nama',
        DB::raw('SUM(sampahs.stok) as total_stok')
    )
    ->groupBy('kategori_sampahs.kategori_id', 'kategori_sampahs.nama')
    ->get();

$monthsList = [];
for ($i = $monthsToSub; $i >= 0; $i--) {
    $dt = Carbon::now()->subMonths($i);
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

$initialTotal = DB::table('penimbangans')
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

echo json_encode([
    'trendCategories' => $trendCategories,
    'trendSeries' => $trendSeries,
    'growthSeries' => [
        ['name' => 'Total', 'data' => $growthSeriesData]
    ],
    'gudangStatus' => $gudangStatusRaw,
    'distribusiSaatIni' => $distribusiRaw
], JSON_PRETTY_PRINT);
