<?php

namespace App\Exports\Manager;

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
            new LaporanTransaksiNasabahExport($this->startDate, $this->endDate),
            new LaporanDetailTransaksiExport($this->startDate, $this->endDate),
            new LaporanPenimbanganExport($this->startDate, $this->endDate),
            new LaporanPengepulExport($this->startDate, $this->endDate),
            new LaporanNasabahExport($this->startDate, $this->endDate),
            new LaporanPenjualanSampahExport($this->startDate, $this->endDate),
            new LaporanPembelianSampahExport($this->startDate, $this->endDate),
            new LaporanGudangExport($this->startDate, $this->endDate),
            new LaporanSampahExport($this->startDate, $this->endDate),
        ];
    }
}
