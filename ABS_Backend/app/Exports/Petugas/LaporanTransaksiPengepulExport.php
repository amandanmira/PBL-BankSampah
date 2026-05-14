<?php

namespace App\Exports\Petugas;

use Carbon\Carbon;
use App\Models\TransaksiPengepul;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanTransaksiPengepulExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        return TransaksiPengepul::whereBetween(
                'created_at',
                [$this->startDate, $this->endDate]
            )
            ->where('status', 'selesai')
            ->with(['detailTransaksi.sampah.itemSampah', 'pengepul'])->get();
    }

    public function map($transaksi): array
    {
        // gabungkan detail transaksi jadi 1 kolom
        $detail = $transaksi->detailTransaksi->map(function ($item) {
            return $item->sampah->itemSampah->nama . ' (' . $item->berat . ' KG )';
        })->implode(', ');

        $total = $transaksi->detailTransaksi->sum('harga');

        return [
            $transaksi->transaksi_id,
            $transaksi->deadline,
            $transaksi->created_at,
            $transaksi->pengepul->nama,
            $detail,
            $total,
        ];
    }

    public function headings(): array
    {
        return ['ID', 'Deadline', 'Tanggal Transaksi', 'Pengepul', 'Detail Transaksi', 'Total'];
    }

    public function title(): string
    {
        return 'Transaksi Pengepul';
    }
}
