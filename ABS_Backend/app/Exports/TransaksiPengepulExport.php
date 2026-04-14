<?php

namespace App\Exports;

use App\Models\TransaksiPengepul;
use Maatwebsite\Excel\Concerns\FromCollection;

class TransaksiPengepulExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return TransaksiPengepul::select('transaksi_id', 'status')->get();
    }
}
