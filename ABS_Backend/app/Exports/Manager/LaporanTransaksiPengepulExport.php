<?php

namespace App\Exports\Manager;

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
    protected $gudangId;
    protected $sampah;

    public function __construct($startDate, $endDate, $gudangId, $sampah)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->gudangId = $gudangId;
        $this->sampah = collect($sampah)->pluck('sampah_id')->toArray();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return TransaksiPengepul::whereBetween(
                'created_at',
                [$this->startDate, $this->endDate]
            )
            ->when($this->gudangId, function ($query) {
                $query->whereHas('detailTransaksi.sampah.gudang', function ($q) {
                    $q->where('gudang_id', $this->gudangId);
                });
            })
            ->when($this->sampah, function ($query) {
                $query->whereHas('detailTransaksi.sampah', function ($q) {
                    $q->whereIn('item_id', $this->sampah);
                });
            })
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
            $transaksi->status,
            $transaksi->deadline,
            $transaksi->created_at,
            $transaksi->pengepul->nama,
            $detail,
            $total,
        ];
    }

    public function headings(): array
    {
        return ['ID', 'Status', 'Deadline', 'Tanggal Transaksi', 'Pengepul', 'Detail Transaksi', 'Total'];
    }

    public function title(): string
    {
        return 'Transaksi Pengepul';
    }
}
