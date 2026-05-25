<?php

namespace App\Exports\Manager;

use Carbon\Carbon;
use App\Models\ItemSampah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanSampahExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        $this->sampah = collect($sampah)->pluck('sampah_id')->toArray();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ItemSampah::whereHas('sampah', function ($q) {
            $q->when($this->sampah, function ($q) {
                $q->whereIn('item_id', $this->sampah);
            })
            ->when($this->gudangId, function ($q) {
                $q->where('gudang_id', $this->gudangId);
            });
        })
        ->withSum(['sampah' => function ($q) {
            $q->when($this->sampah, function ($q) {
                $q->whereIn('item_id', $this->sampah);
            })
            ->when($this->gudangId, function ($q) {
                $q->where('gudang_id', $this->gudangId);
            });
        }], 'stok')
        ->get();
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
