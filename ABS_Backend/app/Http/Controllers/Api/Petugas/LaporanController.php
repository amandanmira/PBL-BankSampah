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

class LaporanController extends Controller
{
    public function exportExcel()
    {
        return Excel::download(new LaporanPetugasExport(), 'laporan-transaksi.xlsx');
    }

    public function exportPdf()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        // Ambil Data
        $pengepulData = Pengepul::whereHas('transaksiPengepul', function ($q) use ($oneMonthAgo) {
            $q->where('created_at', '>=', $oneMonthAgo);
        })->with([
            'transaksiPengepul' => function ($q) use ($oneMonthAgo) {
                $q->where('created_at', '>=', $oneMonthAgo)
                ->with('detailTransaksi');
            }
        ])->get();
        $nasabahData = Nasabah::whereHas('penimbangan', function ($q) use ($oneMonthAgo) {
            $q->where('created_at', '>=', $oneMonthAgo);
        })->with([
            'penimbangan' => function ($q) use ($oneMonthAgo) {
                $q->where('created_at', '>=', $oneMonthAgo)
                ->with('transaksi');
            }
        ])->get();
        $pembelianSampahData = Sampah::whereHas('penimbangan', function ($q) use ($oneMonthAgo) {
            $q->where('created_at', '>=', $oneMonthAgo);
        })->with([
            'penimbangan' => function ($q) use ($oneMonthAgo) {
                $q->where('created_at', '>=', $oneMonthAgo);
            },
            'itemSampah', 'gudang'
        ])->get();
        $penjualanSampahData = Sampah::whereHas('detailTransaksi', function ($q) use ($oneMonthAgo) {
            $q->where('created_at', '>=', $oneMonthAgo);
        })->with([
            'detailTransaksi' => function ($q) use ($oneMonthAgo) {
                $q->where('created_at', '>=', $oneMonthAgo);
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
