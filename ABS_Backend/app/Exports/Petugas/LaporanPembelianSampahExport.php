<?php

namespace App\Exports\Petugas;

use Carbon\Carbon;
use App\Models\Sampah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanPembelianSampahExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        return Sampah::whereHas('penimbangan', function ($q) use ($oneMonthAgo) {
            $q->where('created_at', '>=', $oneMonthAgo);
        })->with([
            'penimbangan' => function ($q) use ($oneMonthAgo) {
                $q->where('created_at', '>=', $oneMonthAgo);
            },
            'itemSampah', 'gudang'
        ])->get();
    }

    public function map($sampah): array
    {
        // jumlah transaksi
        $jumlahTransaksi = $sampah->penimbangan->count();

        // total harga
        // $totalHarga = $sampah->penimbangan->sum('harga');

        // total berat
        $totalBerat = $sampah->penimbangan->sum('berat_timbang'); // atau 'qty' kalau itu berat

        return [
            $sampah->itemSampah->nama,
            $sampah->gudang->alamat,
            $jumlahTransaksi,
            $totalBerat,
        ];
    }

    public function headings(): array
    {
        return ['Sampah', 'Gudang', 'Jumlah Transaksi', 'Total Berat'];
    }

    public function title(): string
    {
        return 'Pembelian Sampah';
    }
}
