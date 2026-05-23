<?php

namespace App\Exports\Manager;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPetugasExport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;
    protected $gudangId;
    protected $sampah;

    public function __construct($startDate, $endDate, $gudangId, $sampah)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->gudangId = $gudangId;
        $this->sampah = $sampah;
    }

    public function sheets(): array
    {
        return [
            new LaporanTransaksiPengepulExport($this->startDate, $this->endDate, $this->gudangId, $this->sampah),
            new LaporanTransaksiNasabahExport($this->startDate, $this->endDate, $this->gudangId, $this->sampah),
            new LaporanDetailTransaksiExport($this->startDate, $this->endDate, $this->gudangId, $this->sampah),
            new LaporanPenimbanganExport($this->startDate, $this->endDate, $this->gudangId, $this->sampah),
            new LaporanPengepulExport($this->startDate, $this->endDate, $this->gudangId, $this->sampah),
            new LaporanNasabahExport($this->startDate, $this->endDate, $this->gudangId, $this->sampah),
            new LaporanPenjualanSampahExport($this->startDate, $this->endDate, $this->gudangId, $this->sampah),
            new LaporanPembelianSampahExport($this->startDate, $this->endDate, $this->gudangId, $this->sampah),
            new LaporanGudangExport($this->startDate, $this->endDate, $this->gudangId, $this->sampah),
            new LaporanSampahExport($this->startDate, $this->endDate, $this->gudangId, $this->sampah),
        ];
    }
}
