<?php

namespace App\Exports\Petugas;

use Carbon\Carbon;
use App\Models\Penimbangan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanPenimbanganExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        return Penimbangan::where('created_at', '>=', $oneMonthAgo)
            ->whereHas('transaksi', function ($q) {
                $q->where('status', 'selesai');
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
