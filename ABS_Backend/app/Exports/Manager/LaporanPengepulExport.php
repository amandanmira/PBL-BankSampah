<?php

namespace App\Exports\Manager;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

use App\Models\Pengepul;

class LaporanPengepulExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        return Pengepul::whereHas('transaksiPengepul', function ($q) {
            $q->whereBetween(
                'created_at',
                [$this->startDate, $this->endDate]
            )
            ->when($this->gudangId, function ($q) {
                $q->whereHas('detailTransaksi.sampah.gudang', function ($q) {
                    $q->where('gudang_id', $this->gudangId);
                });
            })
            ->when($this->sampah, function ($query) {
                $query->whereHas('detailTransaksi.sampah', function ($q) {
                    $q->whereIn('item_id', $this->sampah);
                });
            });
        })->with([
            'transaksiPengepul' => function ($q) {
                $q->whereBetween(
                    'created_at',
                    [$this->startDate, $this->endDate]
                )
                ->when($this->gudangId, function ($q) {
                    $q->whereHas('detailTransaksi.sampah.gudang', function ($q) {
                        $q->where('gudang_id', $this->gudangId);
                    });
                })
                ->when($this->sampah, function ($query) {
                    $query->whereHas('detailTransaksi.sampah', function ($q) {
                        $q->whereIn('item_id', $this->sampah);
                    });
                })
                ->with('detailTransaksi');
            }
        ])->get();
    }

    public function map($pengepul): array
    {
        // jumlah transaksi
        $jumlahTransaksi = $pengepul->transaksiPengepul->count();

        // ambil semua detail dari semua transaksi
        $details = $pengepul->transaksiPengepul->flatMap(function ($transaksi) {
            return $transaksi->detailTransaksi;
        });

        // total harga
        $totalHarga = $details->sum('harga');

        // total berat
        $totalBerat = $details->sum('berat'); // atau 'qty' kalau itu berat

        return [
            $pengepul->nama,
            $pengepul->nama_lembaga,
            $jumlahTransaksi,
            $totalHarga,
            $totalBerat,
        ];
    }

    public function headings(): array
    {
        return ['Pengepul', 'Lembaga', 'Jumlah Transaksi', 'Total Harga', 'Total Berat'];
    }

    public function title(): string
    {
        return 'Pengepul';
    }
}
