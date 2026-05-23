<?php

namespace App\Exports\Manager;

use Carbon\Carbon;
use App\Models\TransaksiNasabah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanTransaksiNasabahExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        return TransaksiNasabah::whereBetween(
                'created_at',
                [$this->startDate, $this->endDate]
            )
            ->when($this->gudangId, function ($query) {
                $query->whereHas('penimbangan.sampah.gudang', function ($q) {
                    $q->where('gudang_id', $this->gudangId);
                });
            })
            ->when($this->sampah, function ($query) {
                $query->whereHas('penimbangan.sampah', function ($q) {
                    $q->whereIn('item_id', $this->sampah);
                });
            })
            ->with([
                'penimbangan' => function ($q) {
                    $q->with(['sampah.itemSampah', 'nasabah']);
                }
            ])->get();
    }

    public function map($transaksi): array
    {
        // gabungkan detail transaksi jadi 1 kolom
        $penimbangan = $transaksi->penimbangan->map(function ($item) {
            return $item->sampah->itemSampah->nama . ' (' . $item->berat_timbang . ' KG )';
        })->implode(', ');

        $nasabah = $transaksi->penimbangan
            ->first()->nasabah;

        //$total = $transaksi->detailTransaksi->sum('harga');

        return [
            $transaksi->transaksi_id,
            $transaksi->status,
            $transaksi->tipe_transaksi,
            $transaksi->tanggal,
            $nasabah->nama,
            $penimbangan,
        ];
    }

    public function headings(): array
    {
        return ['ID', 'Status', 'Tipe Transaksi', 'Tanggal Transaksi', 'Nasabah', 'Detail Transaksi'];
    }

    public function title(): string
    {
        return 'Transaksi Nasabah';
    }
}
