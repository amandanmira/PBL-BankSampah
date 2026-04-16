<?php

namespace App\Exports\Petugas;

use Carbon\Carbon;
use App\Models\TransaksiNasabah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanTransaksiNasabahExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        return TransaksiNasabah::where('tanggal', '>=', $oneMonthAgo)
            ->where('status', 'selesai')
            ->with([
                'penimbangan' => function ($q) use ($oneMonthAgo) {
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
            $transaksi->tipe_transaksi,
            $transaksi->tanggal,
            $nasabah->nama,
            $penimbangan,
        ];
    }

    public function headings(): array
    {
        return ['ID', 'Tipe Transaksi', 'Tanggal Transaksi', 'Nasabah', 'Detail Transaksi'];
    }

    public function title(): string
    {
        return 'Transaksi Nasabah';
    }
}
