<?php

namespace App\Exports\Petugas;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPetugasExport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function sheets(): array
    {
        return [
            new LaporanTransaksiPengepulExport($this->startDate, $this->endDate),
            new LaporanTransaksiNasabahExport(),
            new LaporanDetailTransaksiExport(),
            new LaporanPenimbanganExport(),
            new LaporanPengepulExport(),
            new LaporanNasabahExport(),
            new LaporanPenjualanSampahExport(),
            new LaporanPembelianSampahExport(),
        ];
    }
}
