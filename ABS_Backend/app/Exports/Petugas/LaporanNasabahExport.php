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
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Nasabah::whereHas('penimbangan', function ($q) {
            $q->whereBetween(
                'created_at',
                [$this->startDate, $this->endDate]
            );
        })->with([
            'penimbangan' => function ($q) {
                $q->whereBetween(
                    'created_at',
                    [$this->startDate, $this->endDate]
                )
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
