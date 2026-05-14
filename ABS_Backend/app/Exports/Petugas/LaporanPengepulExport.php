<?php

namespace App\Exports\Petugas;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

use App\Models\Pengepul;

class LaporanPengepulExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        return Pengepul::whereHas('transaksiPengepul', function ($q) {
            $q->whereBetween(
                'created_at',
                [$this->startDate, $this->endDate]
            );
        })->with([
            'transaksiPengepul' => function ($q) {
                $q->whereBetween(
                    'created_at',
                    [$this->startDate, $this->endDate]
                )
                ->with('detailTransaksi');
            }
        ])->get();
    }

    public function map($pengepul): array
    {
        // jumlah transaksi
        $jumlahTransaksi = $pengepul->transaksiPengepul->count();

        // ambil semua detail dari semua transaksi
        $details = $pengepul->transaksiPengepul->flatMap(function ($transaksi) {
            return $transaksi->detailTransaksi;
        });

        // total harga
        $totalHarga = $details->sum('harga');

        // total berat
        $totalBerat = $details->sum('berat'); // atau 'qty' kalau itu berat

        return [
            $pengepul->nama,
            $pengepul->nama_lembaga,
            $jumlahTransaksi,
            $totalHarga,
            $totalBerat,
        ];
    }

    public function headings(): array
    {
        return ['Pengepul', 'Lembaga', 'Jumlah Transaksi', 'Total Harga', 'Total Berat'];
    }

    public function title(): string
    {
        return 'Pengepul';
    }
}
