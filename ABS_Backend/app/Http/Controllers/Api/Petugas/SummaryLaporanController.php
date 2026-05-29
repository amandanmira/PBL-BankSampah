<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\KategoriSampah;
use App\Models\Sampah;
use App\Models\TransaksiPengepul;
use App\Models\TransaksiNasabah;
use App\Models\Penarikan;

class SummaryLaporanController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subMonth()->startOfDay();
        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();
        $gudangId = $request->gudang_id;
        $sampah = collect($request->sampah)->pluck('sampah_id')->toArray();

        // Ambil Data
        $transaksiPengepulData = TransaksiPengepul::whereBetween('updated_at', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->when($gudangId, function ($query) use ($gudangId) {
                $query->whereHas('detailTransaksi.sampah.gudang', function ($q) use ($gudangId) {
                    $q->where('gudang_id', $gudangId);
                });
            })
            ->when($sampah, function ($query) use ($sampah) {
                $query->whereHas('detailTransaksi.sampah', function ($q) use ($sampah) {
                    $q->whereIn('item_id', $sampah);
                });
            })
            ->with(['detailTransaksi.sampah.itemSampah', 'pengepul'])
            ->get();
        $transaksiNasabahData = TransaksiNasabah::whereBetween('updated_at', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->when($gudangId, function ($query) use ($gudangId) {
                $query->whereHas('penimbangan.sampah.gudang', function ($q) use ($gudangId) {
                    $q->where('gudang_id', $gudangId);
                });
            })
            ->when($sampah, function ($query) use ($sampah) {
                $query->whereHas('penimbangan.sampah', function ($q) use ($sampah) {
                    $q->whereIn('item_id', $sampah);
                });
            })
            ->with(['penimbangan.sampah.itemSampah', 'penimbangan.nasabah', 'penimbangan.penjemputan'])
            ->get();

        $penarikanData = Penarikan::whereBetween('updated_at', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->with('nasabah')
            ->get();

        $details = $transaksiPengepulData->flatMap(function ($transaksi) {
            return $transaksi->detailTransaksi;
        });
        $penimbangan_jemput = $transaksiNasabahData->where('tipe_transaksi', 'dijemput')->flatMap(function ($transaksi) {
            return $transaksi->penimbangan;
        });
        $penimbangan_setor = $transaksiNasabahData->where('tipe_transaksi', 'antar_sendiri')->flatMap(function ($transaksi) {
            return $transaksi->penimbangan;
        });

        $dataHarga = [
            'penjemputan_harga' => $transaksiNasabahData->where('tipe_transaksi', 'dijemput')->sum('total_harga'),
            'setor_harga' => $transaksiNasabahData->where('tipe_transaksi', 'antar_sendiri')->sum('total_harga'),
            'penarikan_harga' => Penarikan::sum('jumlah'),
            'pengepul_harga' => $details->sum('harga'),
        ];

        $dataStatistik = [
            'total_transaksi' => $transaksiPengepulData->count() + $transaksiNasabahData->count(),
            'total_stok' => Sampah::when($gudangId, function ($q) use ($gudangId) {
                $q->where('gudang_id', $gudangId);
                })
                ->sum('stok'),
            'total_kategori' => KategoriSampah::count(),
            'total_nilai' => $dataHarga['penjemputan_harga'] + $dataHarga['setor_harga'] + $dataHarga['penarikan_harga'] + $dataHarga['pengepul_harga'],

            'penjemputan_count' => $transaksiNasabahData->where('tipe_transaksi', 'dijemput')->count(),
            'setor_count' => $transaksiNasabahData->where('tipe_transaksi', 'antar_sendiri')->count(),
            'penarikan_count' => Penarikan::count(),
            'pengepul_count' => $transaksiPengepulData->count(),

            'penjemputan_harga' => $dataHarga['penjemputan_harga'],
            'setor_harga' => $dataHarga['setor_harga'],
            'penarikan_harga' => $dataHarga['penarikan_harga'],
            'pengepul_harga' => $dataHarga['pengepul_harga'],

            'penjemputan_berat' => $penimbangan_jemput->sum('berat_timbang'),
            'setor_berat' => $penimbangan_setor->sum('berat_timbang'),
            'pengepul_berat' => $details->sum('berat'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $dataStatistik,
            'details' => [
                'penjemputan' => $transaksiNasabahData->where('tipe_transaksi', 'dijemput')->values(),
                'setor_manual' => $transaksiNasabahData->where('tipe_transaksi', 'antar_sendiri')->values(),
                'penarikan' => $penarikanData,
                'pesanan_pengepul' => $transaksiPengepulData->values(),
            ]
        ]);
    }
}
