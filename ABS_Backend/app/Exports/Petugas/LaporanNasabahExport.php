<?php

namespace App\Exports\Petugas;

use Carbon\Carbon;
use App\Models\Nasabah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanNasabahExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $oneMonthAgo = Carbon::now()->subMonth();

        return Nasabah::whereHas('penimbangan', function ($q) use ($oneMonthAgo) {
            $q->where('created_at', '>=', $oneMonthAgo);
        })->with([
            'penimbangan' => function ($q) use ($oneMonthAgo) {
                $q->where('created_at', '>=', $oneMonthAgo)
                ->with('transaksi');
            }
        ])->get();
    }

    public function map($nasabah): array
    {
        // jumlah transaksi
        $jumlahTransaksi = $nasabah->penimbangan
            ->pluck('transaksi')
            ->unique('transaksi_id')
            ->count();

        // total harga
        //$totalHarga = $penimbangan->sum('harga');

        // total berat
        $totalBerat = $nasabah->penimbangan->sum('berat_timbang'); // atau 'qty' kalau itu berat

        return [
            $nasabah->nama,
            $jumlahTransaksi,
            $totalBerat,
        ];
    }

    public function headings(): array
    {
        return ['Nasabah', 'Jumlah Transaksi', 'Total Berat'];
    }

    public function title(): string
    {
        return 'Nasabah';
    }
}
