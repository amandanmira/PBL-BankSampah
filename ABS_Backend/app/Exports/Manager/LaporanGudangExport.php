<?php

namespace App\Exports\Manager;

use Carbon\Carbon;
use App\Models\Sampah;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanGudangExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        return DB::table('gudangs')
            ->join('sampahs', 'gudangs.gudang_id', '=', 'sampahs.gudang_id')
            ->join('detail_transaksis', 'sampahs.sampah_id', '=', 'detail_transaksis.sampah_id')
            ->join('transaksi_pengepuls', 'detail_transaksis.transaksi_id', '=', 'transaksi_pengepuls.transaksi_id')
            ->whereBetween('transaksi_pengepuls.created_at', [$this->startDate, $this->endDate])
            ->when($this->gudangId, function ($query) {
                $query->where('gudangs.gudang_id', $this->gudangId);
            })
            ->when($this->sampah, function ($query) {
                $query->whereIn('sampahs.item_id', $this->sampah);
            })
            ->select(
                'gudangs.alamat as alamat',
                DB::raw('COUNT(DISTINCT transaksi_pengepuls.transaksi_id) as jumlah_transaksi'),
                DB::raw('COUNT(DISTINCT CASE WHEN transaksi_pengepuls.status = "selesai" THEN transaksi_pengepuls.transaksi_id END) as jumlah_transaksi_selesai'),
                DB::raw('COUNT(DISTINCT CASE WHEN transaksi_pengepuls.status = "pending" THEN transaksi_pengepuls.transaksi_id END) as jumlah_transaksi_pending'),
                DB::raw('(
                    SELECT SUM(s.stok)
                    FROM sampahs s
                    WHERE s.gudang_id = gudangs.gudang_id
                    ' . ($this->sampah ? 'AND s.item_id IN (' . implode(',', $this->sampah) . ')' : '') . '
                ) as total_stok'),
            )
            ->groupBy('gudangs.gudang_id', 'gudangs.alamat')
            ->get();
    }

    public function map($gudang): array
    {
        return [
            $gudang->alamat,
            $gudang->jumlah_transaksi,
            $gudang->total_stok,
            $gudang->jumlah_transaksi_selesai,
            $gudang->jumlah_transaksi_pending,
        ];
    }

    public function headings(): array
    {
        return ['Alamat', 'Jumlah Transaksi', 'Total Stok', 'Jumlah Terverifikasi', 'Jumlah Pending'];
    }

    public function title(): string
    {
        return 'Gudang';
    }
}
