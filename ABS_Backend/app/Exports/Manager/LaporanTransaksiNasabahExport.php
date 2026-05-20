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

    public function __construct($startDate, $endDate, $gudangId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->gudangId = $gudangId;
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
            ->when($this->gudangId, function ($query) {         // ← tambah ini
                $query->whereHas('penimbangan.sampah.gudang', function ($q) {
                    $q->where('gudang_id', $this->gudangId);
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
