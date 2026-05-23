<?php

namespace App\Exports\Manager;

use Carbon\Carbon;
use App\Models\Sampah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanPembelianSampahExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        return Sampah::whereHas('penimbangan', function ($q) {
            $q->whereBetween(
                'created_at',
                [$this->startDate, $this->endDate]
            )
            ->when($this->gudangId, function ($q) {
                $q->whereHas('sampah.gudang', function ($q) {
                    $q->where('gudang_id', $this->gudangId);
                });
            })
            ->when($this->sampah, function ($query) {
                $query->whereHas('sampah', function ($q) {
                    $q->whereIn('item_id', $this->sampah);
                });
            });
        })->with([
            'penimbangan' => function ($q) {
                $q->whereBetween(
                    'created_at',
                    [$this->startDate, $this->endDate]
                );
            },
            'itemSampah', 'gudang'
        ])->get();
    }

    public function map($sampah): array
    {
        // jumlah transaksi
        $jumlahTransaksi = $sampah->penimbangan->count();

        // total harga
        // $totalHarga = $sampah->penimbangan->sum('harga');

        // total berat
        $totalBerat = $sampah->penimbangan->sum('berat_timbang'); // atau 'qty' kalau itu berat

        return [
            $sampah->itemSampah->nama,
            $sampah->gudang->alamat,
            $jumlahTransaksi,
            $totalBerat,
        ];
    }

    public function headings(): array
    {
        return ['Sampah', 'Gudang', 'Jumlah Transaksi', 'Total Berat'];
    }

    public function title(): string
    {
        return 'Pembelian Sampah';
    }
}
