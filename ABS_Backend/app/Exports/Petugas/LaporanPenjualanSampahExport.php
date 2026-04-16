<?php

namespace App\Exports\Petugas;

use App\Models\Sampah;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanPenjualanSampahExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        return Sampah::whereHas('detailTransaksi', function ($q) use ($oneMonthAgo) {
            $q->where('created_at', '>=', $oneMonthAgo);
        })->with([
            'detailTransaksi' => function ($q) use ($oneMonthAgo) {
                $q->where('created_at', '>=', $oneMonthAgo);
            },
            'itemSampah', 'gudang'
        ])->get();
    }

    public function map($sampah): array
    {
        // jumlah transaksi
        $jumlahTransaksi = $sampah->detailTransaksi->count();

        // total harga
        $totalHarga = $sampah->detailTransaksi->sum('harga');

        // total berat
        $totalBerat = $sampah->detailTransaksi->sum('berat'); // atau 'qty' kalau itu berat

        return [
            $sampah->itemSampah->nama,
            $sampah->gudang->alamat,
            $jumlahTransaksi,
            $totalHarga,
            $totalBerat,
        ];
    }

    public function headings(): array
    {
        return ['Sampah', 'Gudang', 'Jumlah Transaksi', 'Total Harga', 'Total Berat'];
    }

    public function title(): string
    {
        return 'Penjualan Sampah';
    }
}
