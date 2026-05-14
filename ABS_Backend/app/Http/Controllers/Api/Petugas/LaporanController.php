<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Exports\Petugas\LaporanPetugasExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Pengepul;
use App\Models\Nasabah;
use App\Models\Sampah;
use Illuminate\Support\Facades\Log;

class LaporanController extends Controller
{
    public function exportExcel(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subMonth()->startOfDay();

        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        return Excel::download(new LaporanPetugasExport($startDate, $endDate), 'laporan-transaksi.xlsx');
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

        $pdf = Pdf::loadView('pdf.laporan-petugas', compact(['pengepul', 'nasabah', 'pembelianSampah', 'penjualanSampah']));

        return $pdf->stream();
    }
}
