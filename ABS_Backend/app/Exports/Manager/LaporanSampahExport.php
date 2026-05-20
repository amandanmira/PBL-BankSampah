<?php

namespace App\Exports\Manager;

use Carbon\Carbon;
use App\Models\ItemSampah;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanSampahExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        return ItemSampah::wherehas('sampah')->withSum('sampah', 'stok')->get();
    }

    public function map($sampah): array
    {
        return [
            $sampah->nama,
            $sampah->sampah_sum_stok,
        ];
    }

    public function headings(): array
    {
        return ['Nama', 'Stok'];
    }

    public function title(): string
    {
        return 'Sampah';
    }
}
