<?php

namespace App\Exports\Manager;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPetugasExport implements WithMultipleSheets
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

    public function sheets(): array
    {
        return [
            new LaporanTransaksiPengepulExport($this->startDate, $this->endDate, $this->gudangId),
            new LaporanTransaksiNasabahExport($this->startDate, $this->endDate, $this->gudangId),
            new LaporanDetailTransaksiExport($this->startDate, $this->endDate, $this->gudangId),
            new LaporanPenimbanganExport($this->startDate, $this->endDate, $this->gudangId),
            new LaporanPengepulExport($this->startDate, $this->endDate, $this->gudangId),
            new LaporanNasabahExport($this->startDate, $this->endDate, $this->gudangId),
            new LaporanPenjualanSampahExport($this->startDate, $this->endDate, $this->gudangId),
            new LaporanPembelianSampahExport($this->startDate, $this->endDate, $this->gudangId),
            new LaporanGudangExport($this->startDate, $this->endDate, $this->gudangId),
            new LaporanSampahExport($this->startDate, $this->endDate),
        ];
    }
}
