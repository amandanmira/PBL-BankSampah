<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\TransaksiPengepul;

class TransaksiPengepulExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    protected $pengepulId;

    public function __construct($pengepulId)
    {
        $this->pengepulId = $pengepulId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return TransaksiPengepul::where('pengepul_id', $this->pengepulId)->with('detailTransaksi.sampah.itemSampah')->get();
    }

    public function map($transaksi): array
    {
        // gabungkan detail transaksi jadi 1 kolom
        $detail = $transaksi->detailTransaksi->map(function ($item) {
            return $item->sampah->itemSampah->nama . ' (' . $item->berat . ' KG )';
        })->implode(', ');

        $total = $transaksi->detailTransaksi->sum(function ($item) {
            return $item->harga; // sesuaikan field
        });

        return [
            $transaksi->transaksi_id,
            $transaksi->status,
            $transaksi->deadline,
            $transaksi->created_at,
            $detail,
            $total,
        ];
    }

    public function headings(): array
    {
        return ['ID', 'Status', 'Deadline', 'Tanggal Transaksi', 'Detail Transaksi', 'Total'];
    }
}
