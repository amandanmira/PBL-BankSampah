<?php

namespace App\Exports\Petugas;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPetugasExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new LaporanTransaksiPengepulExport(),
            new LaporanDetailTransaksiExport(),
        ];
    }
}
