<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Exports\Manager\LaporanPetugasExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Pengepul;
use App\Models\Nasabah;
use App\Models\Sampah;
use App\Models\KonfigurasiWeb;
use App\Models\TransaksiPengepul;
use App\Models\ItemSampah;
use App\Models\Gudang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LaporanController extends Controller
{
    public function indexSampah()
    {
        return response()->json(
            ItemSampah::all()
        );
    }

    public function indexGudang()
    {
        return response()->json(
            Gudang::all()
        );
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subMonth()->startOfDay();
        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        return Excel::download(new LaporanPetugasExport($startDate, $endDate, $request->gudang_id, $request->sampah), 'laporan-transaksi.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->subMonth()->startOfDay();
        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();
        $gudangId = $request->gudang_id;
        $sampah = collect($request->sampah)->pluck('sampah_id')->toArray();

        // Ambil Data
        $pengepulData = Pengepul::whereHas('transaksiPengepul', function ($q) use ($startDate, $endDate, $gudangId, $sampah) {
            $q->whereBetween(
                'created_at',
                [$startDate, $endDate]
            )
            ->where('status', 'selesai')
            ->when($gudangId, function ($q) use ($gudangId) {
                $q->whereHas('detailTransaksi.sampah.gudang', function ($q) use ($gudangId) {
                    $q->where('gudang_id', $gudangId);
                });
            })
            ->when($sampah, function ($query) use ($sampah) {
                $query->whereHas('detailTransaksi.sampah', function ($q) use ($sampah) {
                    $q->whereIn('item_id', $sampah);
                });
            });
        })->with([
            'transaksiPengepul' => function ($q) use ($startDate, $endDate, $gudangId, $sampah) {
                $q->whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                )
                ->when($gudangId, function ($q) use ($gudangId) {
                    $q->whereHas('detailTransaksi.sampah.gudang', function ($q) use ($gudangId) {
                        $q->where('gudang_id', $gudangId);
                    });
                })
                ->when($sampah, function ($query) use ($sampah) {
                    $query->whereHas('detailTransaksi.sampah', function ($q) use ($sampah) {
                        $q->whereIn('item_id', $sampah);
                    });
                })
                ->with('detailTransaksi');
            }
        ])->get();
        $nasabahData = Nasabah::whereHas('penimbangan', function ($q) use ($startDate, $endDate, $gudangId, $sampah) {
            $q->whereBetween(
                'created_at',
                [$startDate, $endDate]
            )
            ->when($gudangId, function ($q) use ($gudangId) {
                $q->whereHas('sampah.gudang', function ($q) use ($gudangId) {
                    $q->where('gudang_id', $gudangId);
                });
            })
            ->when($sampah, function ($query) use ($sampah) {
                $query->whereHas('sampah', function ($q) use ($sampah) {
                    $q->whereIn('item_id', $sampah);
                });
            });
        })->with([
            'penimbangan' => function ($q) use ($startDate, $endDate, $gudangId, $sampah) {
                $q->whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                )
                ->when($gudangId, function ($q) use ($gudangId) {
                    $q->whereHas('sampah.gudang', function ($q) use ($gudangId) {
                        $q->where('gudang_id', $gudangId);
                    });
                })
                ->when($sampah, function ($query) use ($sampah) {
                    $query->whereHas('sampah', function ($q) use ($sampah) {
                        $q->whereIn('item_id', $sampah);
                    });
                })
                ->with([
                    'transaksi' => function ($q) {
                        $q->where('status', 'selesai');
                    }
                ]);
            }
        ])->get();
        $pembelianSampahData = Sampah::whereHas('penimbangan', function ($q) use ($startDate, $endDate, $gudangId, $sampah) {
            $q->whereBetween(
                'created_at',
                [$startDate, $endDate]
            )
            ->when($gudangId, function ($q) use ($gudangId) {
                $q->whereHas('sampah.gudang', function ($q) use ($gudangId) {
                    $q->where('gudang_id', $gudangId);
                });
            })
            ->when($sampah, function ($query) use ($sampah) {
                $query->whereHas('sampah', function ($q) use ($sampah) {
                    $q->whereIn('item_id', $sampah);
                });
            });
        })->with([
            'penimbangan' => function ($q) use ($startDate, $endDate, $gudangId, $sampah) {
                $q->whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                )
                ->when($gudangId, function ($q) use ($gudangId) {
                    $q->whereHas('sampah.gudang', function ($q) use ($gudangId) {
                        $q->where('gudang_id', $gudangId);
                    });
                })
                ->when($sampah, function ($query) use ($sampah) {
                    $query->whereHas('sampah', function ($q) use ($sampah) {
                        $q->whereIn('item_id', $sampah);
                    });
                });
            },
            'itemSampah', 'gudang'
        ])->get();
        $penjualanSampahData = Sampah::whereHas('detailTransaksi', function ($q) use ($startDate, $endDate, $gudangId, $sampah) {
            $q->whereBetween(
                'created_at',
                [$startDate, $endDate]
            )
            ->when($gudangId, function ($q) use ($gudangId) {
                $q->whereHas('sampah.gudang', function ($q) use ($gudangId) {
                    $q->where('gudang_id', $gudangId);
                });
            })
            ->when($sampah, function ($query) use ($sampah) {
                $query->whereHas('sampah', function ($q) use ($sampah) {
                    $q->whereIn('item_id', $sampah);
                });
            });
        })->with([
            'detailTransaksi' => function ($q) use ($startDate, $endDate, $gudangId, $sampah) {
                $q->whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                )
                ->when($gudangId, function ($q) use ($gudangId) {
                    $q->whereHas('sampah.gudang', function ($q) use ($gudangId) {
                        $q->where('gudang_id', $gudangId);
                    });
                })
                ->when($sampah, function ($query) use ($sampah) {
                    $query->whereHas('sampah', function ($q) use ($sampah) {
                        $q->whereIn('item_id', $sampah);
                    });
                });
            },
            'itemSampah', 'gudang'
        ])->get();
        $gudangData = DB::table('gudangs')
            ->join('sampahs', 'gudangs.gudang_id', '=', 'sampahs.gudang_id')
            ->join('detail_transaksis', 'sampahs.sampah_id', '=', 'detail_transaksis.sampah_id')
            ->join('transaksi_pengepuls', 'detail_transaksis.transaksi_id', '=', 'transaksi_pengepuls.transaksi_id')
            ->whereBetween('transaksi_pengepuls.created_at', [$startDate, $endDate])
            ->when($gudangId, function ($query) use ($gudangId) {
                $query->where('gudangs.gudang_id', $gudangId);
            })
            ->when($sampah, function ($query) use ($sampah) {
                $query->whereIn('sampahs.item_id', $sampah);
            })
            ->select(
                'gudangs.alamat as alamat',
                DB::raw('COUNT(DISTINCT transaksi_pengepuls.transaksi_id) as jumlah_transaksi'),
                DB::raw('COUNT(DISTINCT CASE WHEN transaksi_pengepuls.status = "selesai" THEN transaksi_pengepuls.transaksi_id END) as jumlah_transaksi_selesai'),
                DB::raw('COUNT(DISTINCT CASE WHEN transaksi_pengepuls.status = "pending" THEN transaksi_pengepuls.transaksi_id END) as jumlah_transaksi_pending'),
                DB::raw('(
                    SELECT SUM(s.stok)
                    FROM sampahs s
                    WHERE s.gudang_id = gudangs.gudang_id
                    ' . ($sampah ? 'AND s.item_id IN (' . implode(',', $sampah) . ')' : '') . '
                ) as total_stok'),
            )
            ->groupBy('gudangs.gudang_id', 'gudangs.alamat')
            ->get();
        $transaksiPengepulData = TransaksiPengepul::whereBetween('created_at', [$startDate, $endDate])
                ->when($gudangId, function ($query) use ($gudangId) {
                    $query->whereHas('detailTransaksi.sampah.gudang', function ($q) use ($gudangId) {
                        $q->where('gudang_id', $gudangId);
                    });
                })
                ->when($sampah, function ($query) use ($sampah) {
                    $query->whereHas('detailTransaksi.sampah', function ($q) use ($sampah) {
                        $q->whereIn('item_id', $sampah);
                    });
                })
                ->get();
        $itemSampahData = ItemSampah::whereHas('sampah', function ($q) use ($gudangId, $sampah) {
            $q->when($gudangId, function ($q) use ($gudangId) {
                $q->where('gudang_id', $gudangId);
            })
            ->when($sampah, function ($query) use ($sampah) {
                $query->whereIn('item_id', $sampah);
            });
        })
        ->withSum(['sampah' => function ($q) use ($gudangId, $sampah) {
            $q->when($gudangId, function ($q) use ($gudangId) {
                $q->where('gudang_id', $gudangId);
            })
            ->when($sampah, function ($query) use ($sampah) {
                $query->whereIn('item_id', $sampah);
            });;
        }], 'stok')
        ->get();

        // Olah Data
        $pengepul = $pengepulData->map(function ($item) {
            $details = $item->transaksiPengepul->flatMap(function ($transaksi) {
                return $transaksi->detailTransaksi;
            });

            return [
                'nama' => $item->nama,
                'lembaga' => $item->nama_lembaga,
                'jumlah_transaksi' => $item->transaksiPengepul->count(),
                'total_harga' => $details->sum('harga'),
                'total_berat' => $details->sum('berat'),
            ];
        });
        $nasabah = $nasabahData->map(function ($item) {
            $jumlahTransaksi = $item->penimbangan
                ->pluck('transaksi')
                ->unique('transaksi_id')
                ->count();

            return [
                'nama' => $item->nama,
                'jumlah_transaksi' => $jumlahTransaksi,
                // 'total_harga' => $details->sum('harga'),
                'total_berat' => $item->penimbangan->sum('berat_timbang'),
            ];
        });
        $pembelianSampah = $pembelianSampahData->map(function ($item) {
            return [
                'nama' => $item->itemSampah->nama,
                'gudang' => $item->gudang->alamat,
                'jumlah_transaksi' => $item->penimbangan->count(),
                // 'total_harga' => $details->sum('harga'),
                'total_berat' => $item->penimbangan->sum('berat_timbang'),
            ];
        });
        $penjualanSampah = $penjualanSampahData->map(function ($item) {
            return [
                'nama' => $item->itemSampah->nama,
                'gudang' => $item->gudang->alamat,
                'jumlah_transaksi' => $item->detailTransaksi->count(),
                'total_harga' => $item->detailTransaksi->sum('harga'),
                'total_berat' => $item->detailTransaksi->sum('berat'),
            ];
        });
        $gudang = $gudangData->map(function ($item) {
            return [
                'alamat' => $item->alamat,
                'jumlah_transaksi' => $item->jumlah_transaksi,
                'jumlah_transaksi_selesai' => $item->jumlah_transaksi_selesai,
                'jumlah_transaksi_pending' => $item->jumlah_transaksi_pending,
                'total_stok' => $item->total_stok,
            ];
        });
        $transaksiNasabahData = \App\Models\TransaksiNasabah::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->when($gudangId, function ($query) use ($gudangId) {
                $query->whereHas('penimbangan.sampah.gudang', function ($q) use ($gudangId) {
                    $q->where('gudang_id', $gudangId);
                });
            })
            ->when($sampah, function ($query) use ($sampah) {
                $query->whereHas('penimbangan.sampah', function ($q) use ($sampah) {
                    $q->whereIn('item_id', $sampah);
                });
            })
            ->with('penimbangan')
            ->get();

        $nasabahJemputCount = $transaksiNasabahData->where('tipe_transaksi', 'dijemput')->count();
        $nasabahSetorCount = $transaksiNasabahData->where('tipe_transaksi', 'antar_sendiri')->count();

        $nasabahJemputWeight = $transaksiNasabahData->where('tipe_transaksi', 'dijemput')->flatMap(function ($t) {
            return $t->penimbangan;
        })->sum('berat_timbang');

        $nasabahSetorWeight = $transaksiNasabahData->where('tipe_transaksi', 'antar_sendiri')->flatMap(function ($t) {
            return $t->penimbangan;
        })->sum('berat_timbang');

        $dataStatistik = [
            'total_transaksi' => $transaksiPengepulData->count(),
            'total_transaksi_selesai' => $transaksiPengepulData->where('status', 'selesai')->count(),
            'total_transaksi_pending' => $transaksiPengepulData->where('status', 'pending')->count(),
            'total_stok' => Sampah::when($gudangId, function ($q) use ($gudangId) {
                $q->where('gudang_id', $gudangId);
            })
            ->sum('stok'),
            'nasabah_jemput_count' => $nasabahJemputCount,
            'nasabah_setor_count' => $nasabahSetorCount,
            'nasabah_jemput_weight' => $nasabahJemputWeight,
            'nasabah_setor_weight' => $nasabahSetorWeight,
        ];
        $totalStokItem = $itemSampahData->map(function ($item) use ($dataStatistik) {
            $total = $dataStatistik['total_stok'];
            $persentaseItem = $total > 0 ? ($item->sampah_sum_stok / $total) : 0;

            return [
                'nama' => $item->nama,
                'stok' => $item->sampah_sum_stok,
                'persentase' => $persentaseItem,
            ];
        });

        $config = KonfigurasiWeb::firstOrCreate([]);

        $pdf = Pdf::loadView('pdf.laporan-petugas', compact(['pengepul', 'nasabah', 'pembelianSampah', 'penjualanSampah', 'config', 'dataStatistik', 'gudang', 'totalStokItem']));

        $pdfBase64 = base64_encode($pdf->output());

        return response()->json([
            'status' => 'success',
            'data' => [
                'pdf_base64' => $pdfBase64
            ]
        ]);
    }

    public function exportPenarikanPdf(Request $request)
    {
        $startDate = null;
        $endDate = Carbon::now()->endOfDay();

        $durasi = $request->query('durasi');
        if ($durasi && $durasi !== 'Semua Waktu') {
            if ($durasi === '1 Minggu Terakhir') {
                $startDate = Carbon::now()->subWeek()->startOfDay();
            } elseif ($durasi === '1 Bulan Terakhir') {
                $startDate = Carbon::now()->subMonth()->startOfDay();
            } elseif ($durasi === '3 Bulan Terakhir') {
                $startDate = Carbon::now()->subMonths(3)->startOfDay();
            }
        } elseif ($request->start_date || $request->end_date) {
            if ($request->start_date) {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
            }
            if ($request->end_date) {
                $endDate = Carbon::parse($request->end_date)->endOfDay();
            }
        } else {
            // Default: 1 Bulan Terakhir
            $startDate = Carbon::now()->subMonth()->startOfDay();
        }

        // Helper function to apply date range
        $applyDateRange = function($q) use ($startDate, $endDate) {
            if ($startDate) {
                $q->where('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $q->where('created_at', '<=', $endDate);
            }
        };

        // 1. Grand totals (kolom)
        $summaryQuery = DB::table('penarikans');
        $applyDateRange($summaryQuery);
        $summary = $summaryQuery->select(
            DB::raw('COUNT(penarikan_id) as total_penarikan'),
            DB::raw('COALESCE(SUM(jumlah), 0) as total_nilai'),
            DB::raw('COUNT(CASE WHEN status = "selesai" THEN 1 END) as status_selesai'),
            DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as status_pending')
        )->first();

        // 2. Table: Gudang details (alamat gudang join dari petugas)
        $gudangReport = DB::table('gudangs')
            ->leftJoin('petugas', 'gudangs.gudang_id', '=', 'petugas.gudang_id')
            ->leftJoin('penarikans', function($join) use ($startDate, $endDate) {
                $join->on('petugas.petugas_id', '=', 'penarikans.petugas_id');
                if ($startDate) {
                    $join->where('penarikans.created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $join->where('penarikans.created_at', '<=', $endDate);
                }
            })
            ->select(
                'gudangs.alamat as alamat',
                DB::raw('COUNT(penarikans.penarikan_id) as total_penarikan'),
                DB::raw('COALESCE(SUM(penarikans.jumlah), 0) as total_nilai'),
                DB::raw('COUNT(CASE WHEN penarikans.status = "selesai" THEN 1 END) as status_selesai'),
                DB::raw('COUNT(CASE WHEN penarikans.status = "pending" THEN 1 END) as status_pending')
            )
            ->groupBy('gudangs.gudang_id', 'gudangs.alamat')
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();

        // Unassigned (Pending) Penarikans (no petugas_id yet)
        $unassignedQuery = DB::table('penarikans')->whereNull('petugas_id');
        $applyDateRange($unassignedQuery);
        $unassigned = $unassignedQuery->select(
            DB::raw('"Belum Diproses (Tanpa Gudang)" as alamat'),
            DB::raw('COUNT(penarikan_id) as total_penarikan'),
            DB::raw('COALESCE(SUM(jumlah), 0) as total_nilai'),
            DB::raw('COUNT(CASE WHEN status = "selesai" THEN 1 END) as status_selesai'),
            DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as status_pending')
        )->first();

        if ($unassigned && $unassigned->total_penarikan > 0) {
            $gudangReport[] = (array) $unassigned;
        }

        // 3. Top 5 Nasabah by Jumlah Penarikan (frequency)
        $topNasabahByCountQuery = Nasabah::select('nasabahs.nasabah_id', 'nasabahs.nama')
            ->join('penarikans', 'nasabahs.nasabah_id', '=', 'penarikans.nasabah_id')
            ->where('penarikans.status', 'selesai');
        
        if ($startDate) {
            $topNasabahByCountQuery->where('penarikans.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $topNasabahByCountQuery->where('penarikans.created_at', '<=', $endDate);
        }
        
        $topNasabahByCount = $topNasabahByCountQuery
            ->selectRaw('count(penarikans.penarikan_id) as total_penarikan, sum(penarikans.jumlah) as total_nilai')
            ->groupBy('nasabahs.nasabah_id', 'nasabahs.nama')
            ->orderByDesc('total_penarikan')
            ->limit(5)
            ->get();

        // 4. Top 5 Nasabah by Total Nilai Penarikan (amount)
        $topNasabahByAmountQuery = Nasabah::select('nasabahs.nasabah_id', 'nasabahs.nama')
            ->join('penarikans', 'nasabahs.nasabah_id', '=', 'penarikans.nasabah_id')
            ->where('penarikans.status', 'selesai');
        
        if ($startDate) {
            $topNasabahByAmountQuery->where('penarikans.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $topNasabahByAmountQuery->where('penarikans.created_at', '<=', $endDate);
        }

        $topNasabahByAmount = $topNasabahByAmountQuery
            ->selectRaw('count(penarikans.penarikan_id) as total_penarikan, sum(penarikans.jumlah) as total_nilai')
            ->groupBy('nasabahs.nasabah_id', 'nasabahs.nama')
            ->orderByDesc('total_nilai')
            ->limit(5)
            ->get();

        $config = KonfigurasiWeb::firstOrCreate([]);

        $pdf = Pdf::loadView('pdf.laporan-penarikan', compact([
            'summary',
            'gudangReport',
            'topNasabahByCount',
            'topNasabahByAmount',
            'config',
            'startDate',
            'endDate'
        ]));

        $pdfBase64 = base64_encode($pdf->output());

        return response()->json([
            'status' => 'success',
            'data' => [
                'pdf_base64' => $pdfBase64
            ]
        ]);
    }
}

