<?php

namespace App\Exports\Petugas;

use Carbon\Carbon;
use App\Models\DetailTransaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;


class LaporanDetailTransaksiExport implements FromCollection, WithMapping, WithHeadings, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        return DetailTransaksi::where('created_at', '>=', $oneMonthAgo)
            ->with(['transaksiPengepul.pengepul', 'sampah.itemSampah'])->get();
    }

    public function map($detail): array
    {
        return [
            $detail->detail_id,
            $detail->transaksi_id,
            $detail->transaksiPengepul->pengepul->nama,
            $detail->created_at,
            $detail->sampah->itemSampah->nama,
            $detail->berat,
            $detail->harga,
        ];
    }

    public function headings(): array
    {
        return ['ID', 'Transaksi_id', 'Pengepul', 'Tanggal Transaksi', 'Sampah', 'Berat', 'Harga'];
    }

    public function title(): string
    {
        return 'Detail Transaksi Pengepul';
    }
}
