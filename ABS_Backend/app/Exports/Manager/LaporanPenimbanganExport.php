<?php

namespace App\Exports\Manager;

use Carbon\Carbon;
use App\Models\Penimbangan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanPenimbanganExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        return Penimbangan::whereBetween(
                'created_at',
                [$this->startDate, $this->endDate]
            )
            ->whereHas('transaksi', function ($q) {
                $q->where('status', 'selesai');
            })
            ->when($this->gudangId, function ($q) {
                $q->whereHas('sampah.gudang', function ($q) {
                    $q->where('gudang_id', $this->gudangId);
                });
            })
            ->with(['nasabah', 'sampah.itemSampah'])->get();
    }

    public function map($penimbangan): array
    {
        return [
            $penimbangan->penimbangan_id,
            $penimbangan->transaksi_id,
            $penimbangan->nasabah->nama,
            $penimbangan->created_at,
            $penimbangan->sampah->itemSampah->nama,
            $penimbangan->berat_timbang,
        ];
    }

    public function headings(): array
    {
        return ['ID', 'Transaksi_id', 'Nasabah', 'Tanggal Transaksi', 'Sampah', 'Berat'];
    }

    public function title(): string
    {
        return 'Penimbangan';
    }
}
