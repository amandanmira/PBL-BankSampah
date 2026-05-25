<?php

namespace App\Exports\Manager;

use Carbon\Carbon;
use App\Models\DetailTransaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;


class LaporanDetailTransaksiExport implements FromCollection, WithMapping, WithHeadings, WithTitle
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
        return DetailTransaksi::whereBetween(
                'created_at',
                [$this->startDate, $this->endDate]
            )
            ->whereHas('transaksiPengepul', function ($q) {
                $q->where('status', 'selesai');
            })
            ->when($this->gudangId, function ($query) {
                $query->whereHas('sampah.gudang', function ($q) {
                    $q->where('gudang_id', $this->gudangId);
                });
            })
            ->when($this->sampah, function ($query) {
                $query->whereHas('sampah', function ($q) {
                    $q->whereIn('item_id', $this->sampah);
                });
            })
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
