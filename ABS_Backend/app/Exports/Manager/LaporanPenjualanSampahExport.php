<?php

namespace App\Exports\Manager;

use Carbon\Carbon;
use App\Models\Sampah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanPenjualanSampahExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        return Sampah::whereHas('detailTransaksi', function ($q) {
            $q->whereBetween(
                'created_at',
                [$this->startDate, $this->endDate]
            )
            ->when($this->gudangId, function ($query) {
                $query->whereHas('sampah.gudang', function ($q) {
                    $q->where('gudang_id', $this->gudangId);
                });
            })
            ->when($this->sampah, function ($query) {
                $query->whereHas('sampah', function ($q) {
                    $q->whereIn('item_id', $this->sampah);
                });
            });
        })->with([
            'detailTransaksi' => function ($q) {
                $q->whereBetween(
                    'created_at',
                    [$this->startDate, $this->endDate]
                )
                ->when($this->gudangId, function ($query) {
                    $query->whereHas('sampah.gudang', function ($q) {
                        $q->where('gudang_id', $this->gudangId);
                    });
                })
                ->when($this->sampah, function ($query) {
                    $query->whereHas('sampah', function ($q) {
                        $q->whereIn('item_id', $this->sampah);
                    });
                });
            },
            'itemSampah', 'gudang'
        ])->get();
    }

    public function map($sampah): array
    {
        // jumlah transaksi
        $jumlahTransaksi = $sampah->detailTransaksi->count();

        // total harga
        $totalHarga = $sampah->detailTransaksi->sum('harga');

        // total berat
        $totalBerat = $sampah->detailTransaksi->sum('berat'); // atau 'qty' kalau itu berat

        return [
            $sampah->itemSampah->nama,
            $sampah->gudang->alamat,
            $jumlahTransaksi,
            $totalHarga,
            $totalBerat,
        ];
    }

    public function headings(): array
    {
        return ['Sampah', 'Gudang', 'Jumlah Transaksi', 'Total Harga', 'Total Berat'];
    }

    public function title(): string
    {
        return 'Penjualan Sampah';
    }
}
