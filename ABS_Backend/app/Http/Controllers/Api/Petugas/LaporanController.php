<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Exports\Manager\LaporanPetugasExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Pengepul;
use App\Models\Nasabah;
use App\Models\Sampah;
use App\Models\KonfigurasiWeb;
use App\Models\TransaksiPengepul;
use App\Models\ItemSampah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LaporanController extends Controller
{
    public function exportExcel(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::now()->subDays($request->start_date)->startOfDay()
            : Carbon::now()->subMonth()->startOfDay();

        $endDate = Carbon::now()->endOfDay();

        return Excel::download(new LaporanPetugasExport($startDate, $endDate, $request->gudang_id), 'laporan-transaksi.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subMonth()->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        // Ambil Data
        $pengepulData = Pengepul::whereHas('transaksiPengepul', function ($q) use ($startDate, $endDate) {
            $q->whereBetween(
                'created_at',
                [$startDate, $endDate]
            );
        })->with([
            'transaksiPengepul' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                )
                ->with('detailTransaksi');
            }
        ])->get();
        $nasabahData = Nasabah::whereHas('penimbangan', function ($q) use ($startDate, $endDate) {
            $q->whereBetween(
                'created_at',
                [$startDate, $endDate]
            );
        })->with([
            'penimbangan' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                )
                ->with('transaksi');
            }
        ])->get();
        $pembelianSampahData = Sampah::whereHas('penimbangan', function ($q) use ($startDate, $endDate) {
            $q->whereBetween(
                'created_at',
                [$startDate, $endDate]
            );
        })->with([
            'penimbangan' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                );
            },
            'itemSampah', 'gudang'
        ])->get();
        $penjualanSampahData = Sampah::whereHas('detailTransaksi', function ($q) use ($startDate, $endDate) {
            $q->whereBetween(
                'created_at',
                [$startDate, $endDate]
            );
        })->with([
            'detailTransaksi' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                );
            },
            'itemSampah', 'gudang'
        ])->get();
        $gudangData = DB::table('gudangs')
            ->join('sampahs', 'gudangs.gudang_id', '=', 'sampahs.gudang_id')
            ->join('detail_transaksis', 'sampahs.sampah_id', '=', 'detail_transaksis.sampah_id')
            ->join('transaksi_pengepuls', 'detail_transaksis.transaksi_id', '=', 'transaksi_pengepuls.transaksi_id')
            ->whereBetween('transaksi_pengepuls.created_at', [$startDate, $endDate])
            ->select(
                'gudangs.alamat as alamat',
                DB::raw('COUNT(DISTINCT transaksi_pengepuls.transaksi_id) as jumlah_transaksi'),
                DB::raw('COUNT(DISTINCT CASE WHEN transaksi_pengepuls.status = "selesai" THEN transaksi_pengepuls.transaksi_id END) as jumlah_transaksi_selesai'),
                DB::raw('COUNT(DISTINCT CASE WHEN transaksi_pengepuls.status = "pending" THEN transaksi_pengepuls.transaksi_id END) as jumlah_transaksi_pending'),
                DB::raw('(
                    SELECT SUM(s.stok)
                    FROM sampahs s
                    WHERE s.gudang_id = gudangs.gudang_id
                ) as total_stok'),
            )
            ->groupBy('gudangs.gudang_id', 'gudangs.alamat')
            ->get();
        $transaksiPengepulData = TransaksiPengepul::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'selesai')
                ->get();
        $itemSampahData = ItemSampah::wherehas('sampah')->withSum('sampah', 'stok')->get();

        // Olah Data
        $pengepul = $pengepulData->map(function ($item) {
            $details = $item->transaksiPengepul->flatMap(function ($transaksi) {
                return $transaksi->detailTransaksi;
            });

            return [
                'nama' => $item->nama,
                'lembaga' => $item->nama_lembaga,
                'jumlah_transaksi' => $item->transaksiPengepul->count(),
                'total_harga' => $details->sum('harga'),
                'total_berat' => $details->sum('berat'),
            ];
        });
        $nasabah = $nasabahData->map(function ($item) {
            $jumlahTransaksi = $item->penimbangan
                ->pluck('transaksi')
                ->unique('transaksi_id')
                ->count();

            return [
                'nama' => $item->nama,
                'jumlah_transaksi' => $jumlahTransaksi,
                // 'total_harga' => $details->sum('harga'),
                'total_berat' => $item->penimbangan->sum('berat_timbang'),
            ];
        });
        $pembelianSampah = $pembelianSampahData->map(function ($item) {
            return [
                'nama' => $item->itemSampah->nama,
                'gudang' => $item->gudang->alamat,
                'jumlah_transaksi' => $item->penimbangan->count(),
                // 'total_harga' => $details->sum('harga'),
                'total_berat' => $item->penimbangan->sum('berat_timbang'),
            ];
        });
        $penjualanSampah = $penjualanSampahData->map(function ($item) {
            return [
                'nama' => $item->itemSampah->nama,
                'gudang' => $item->gudang->alamat,
                'jumlah_transaksi' => $item->detailTransaksi->count(),
                'total_harga' => $item->detailTransaksi->sum('harga'),
                'total_berat' => $item->detailTransaksi->sum('berat'),
            ];
        });
        $gudang = $gudangData->map(function ($item) {
            return [
                'alamat' => $item->alamat,
                'jumlah_transaksi' => $item->jumlah_transaksi,
                'jumlah_transaksi_selesai' => $item->jumlah_transaksi_selesai,
                'jumlah_transaksi_pending' => $item->jumlah_transaksi_pending,
                'total_stok' => $item->total_stok,
            ];
        });
        $dataStatistik = [
            'total_transaksi' => $transaksiPengepulData->count(),
            'total_transaksi_selesai' => $transaksiPengepulData->where('status', 'selesai')->count(),
            'total_transaksi_pending' => $transaksiPengepulData->where('status', 'pending')->count(),
            'total_stok' => Sampah::sum('stok'),
        ];
        $totalStokItem = $itemSampahData->map(function ($item) use ($dataStatistik) {
            $persentaseItem = $item->sampah_sum_stok / $dataStatistik['total_stok'];

            return [
                'nama' => $item->nama,
                'stok' => $item->sampah_sum_stok,
                'persentase' => $persentaseItem,
            ];
        });

        $config = KonfigurasiWeb::firstOrCreate([]);

        $pdf = Pdf::loadView('pdf.laporan-petugas', compact(['pengepul', 'nasabah', 'pembelianSampah', 'penjualanSampah', 'config', 'dataStatistik', 'gudang', 'totalStokItem']));

        return $pdf->stream();
    }
}
