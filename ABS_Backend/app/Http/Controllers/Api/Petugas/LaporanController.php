<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Exports\Petugas\LaporanPetugasExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function exportExcel()
    {
        return Excel::download(new LaporanPetugasExport(), 'laporan-transaksi.xlsx');
    }
}
