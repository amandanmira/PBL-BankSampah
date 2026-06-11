<?php

namespace App\Http\Controllers\Api\Manager;

use App\Http\Controllers\Controller;
use App\Models\Penimbangan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ManagerAuditController extends Controller
{
    private function buildQuery(Request $request)
    {
        $gudang = $request->query('gudang');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $jenisSampah = $request->query('jenisSampah'); 
        $search = $request->query('search');

        $query = Penimbangan::with([
            'nasabah',
            'sampah.itemSampah',
            'tukang',
            'transaksi.petugas.gudang',
            'penjemputan.gudang'
        ])->where(function ($q) {
            $q->whereHas('penjemputan', function($q2) {
                $q2->whereIn('status', ['selesai', 'tolak', 'batal']);
            })->orWhereHas('transaksi', function($q3) {
                $q3->where('tipe_transaksi', 'antar_sendiri')
                   ->whereIn('status', ['selesai']);
            });
        });

        // Filter by Gudang
        if ($gudang && $gudang !== 'Semua Gudang') {
            $query->where(function ($q) use ($gudang) {
                $q->whereHas('penjemputan.gudang', function($q2) use ($gudang) {
                    $q2->where('alamat', $gudang);
                })->orWhereHas('transaksi.petugas.gudang', function($q3) use ($gudang) {
                    $q3->where('alamat', $gudang);
                });
            });
        }

        // Filter by Date Range
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Filter by Jenis Sampah
        if ($jenisSampah) {
            $jenisArray = is_array($jenisSampah) ? $jenisSampah : explode(',', $jenisSampah);
            if (count($jenisArray) > 0) {
                $query->whereHas('sampah.itemSampah', function($q) use ($jenisArray) {
                    $q->whereIn('nama', $jenisArray);
                });
            }
        }

        // Filter by Search (Nasabah Name or Penjemputan ID)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('nasabah', function($nq) use ($search) {
                    $nq->where('nama', 'like', "%$search%");
                })->orWhereHas('penjemputan', function($pq) use ($search) {
                    $pq->where('penjemputan_id', 'like', "%$search%");
                });
            });
        }

        return $query;
    }

    private function getAllAuditData(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $jenisSampah = $request->query('jenisSampah'); 
        $search = $request->query('search');
        $gudang = $request->query('gudang');
        $role = $request->query('role');

        $parsedStartDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $parsedEndDate = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        $completedData = collect();
        $failedData = collect();
        $pengepulData = collect();

        // 1. Get Completed Transactions (Nasabah)
        if (!$role || $role === 'Semua Role' || $role === 'Nasabah') {
            $query = Penimbangan::with([
                'nasabah',
                'sampah.itemSampah',
                'tukang',
                'transaksi.petugas.gudang',
                'penjemputan.gudang'
            ])->where(function ($q) {
                $q->whereHas('penjemputan', function($q2) {
                    $q2->whereIn('status', ['selesai']);
                })->orWhereHas('transaksi', function($q3) {
                    $q3->where('tipe_transaksi', 'antar_sendiri')
                       ->whereIn('status', ['selesai']);
                });
            });

            if ($gudang && $gudang !== 'Semua Gudang') {
                $query->where(function ($q) use ($gudang) {
                    // Untuk transaksi Jemput, cek alamat dari Gudang Penjemputan
                    $q->where(function ($qJemput) use ($gudang) {
                        $qJemput->whereHas('penjemputan.gudang', function($q2) use ($gudang) {
                            $q2->where('alamat', $gudang);
                        });
                    })
                    // Untuk transaksi Setor Manual, cek alamat dari Gudang Petugas
                    ->orWhere(function ($qSetor) use ($gudang) {
                        $qSetor->whereHas('transaksi', function($q3) use ($gudang) {
                            $q3->where('tipe_transaksi', 'antar_sendiri')
                               ->whereHas('petugas.gudang', function($q4) use ($gudang) {
                                   $q4->where('alamat', $gudang);
                               });
                        });
                    });
                });
            }
            $query->where('created_at', '>=', $parsedStartDate);
            $query->where('created_at', '<=', $parsedEndDate);
            if ($jenisSampah) {
                $jenisArray = is_array($jenisSampah) ? $jenisSampah : explode(',', $jenisSampah);
                if (count($jenisArray) > 0) {
                    $query->whereHas('sampah.itemSampah', function($q) use ($jenisArray) {
                        $q->whereIn('nama', $jenisArray);
                    });
                }
            }
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('nasabah', function($nq) use ($search) {
                        $nq->where('nama', 'like', "%$search%");
                    })->orWhereHas('penjemputan', function($pq) use ($search) {
                        $pq->where('penjemputan_id', 'like', "%$search%");
                    });
                });
            }

            $completedData = $query->get()->map(function ($p) {
                $created_at = \Carbon\Carbon::parse($p->created_at);
                $isJemput = !($p->transaksi && $p->transaksi->tipe_transaksi === 'antar_sendiri');
                
                $gudangName = $isJemput 
                    ? (optional(optional($p->penjemputan)->gudang)->alamat ?? 'Unknown Gudang')
                    : (optional(optional(optional($p->transaksi)->petugas)->gudang)->alamat ?? 'Unknown Gudang');

                return [
                    'tanggal' => $created_at->translatedFormat('d M Y'),
                    'nasabah' => $p->nasabah->nama ?? 'Unknown',
                    'role' => 'Nasabah',
                    'gudang' => $gudangName,
                    'jenis' => $p->sampah->itemSampah->nama ?? 'Unknown',
                    'berat' => (float)$p->berat_timbang,
                    'sumber' => $isJemput ? 'Jemput' : 'Setor Manual',
                    'status' => 'Selesai',
                    'petugas' => optional(optional($p->transaksi)->petugas)->nama ?? '-',
                    'tukang' => $p->tukang->nama ?? '-',
                    'rawDate' => $p->created_at,
                ];
            });
        }

        // 2. Get Failed Transactions (from Penjemputan directly)
        if (!$role || $role === 'Semua Role' || $role === 'Nasabah') {
            $failedQuery = \App\Models\Penjemputan::with([
                'nasabah',
                'gudang',
                'petugas',
                'tukang'
            ])->whereIn('status', ['tolak', 'batal']);
            
            if ($gudang && $gudang !== 'Semua Gudang') {
                $failedQuery->whereHas('gudang', function($q) use ($gudang) {
                    $q->where('alamat', $gudang);
                });
            }
            $failedQuery->where('created_at', '>=', $parsedStartDate);
            $failedQuery->where('created_at', '<=', $parsedEndDate);

            if ($search) {
                $failedQuery->where(function($q) use ($search) {
                    $q->whereHas('nasabah', function($nq) use ($search) {
                        $nq->where('nama', 'like', "%$search%");
                    })->orWhere('penjemputan_id', 'like', "%$search%");
                });
            }

            if ($jenisSampah && count(is_array($jenisSampah) ? $jenisSampah : explode(',', $jenisSampah)) > 0) {
                $failedData = collect([]);
            } else {
                $failedData = $failedQuery->get()->map(function ($p) {
                    $created_at = \Carbon\Carbon::parse($p->created_at);
                    $gudangName = $p->gudang->alamat ?? 'Unknown Gudang';

                    return [
                        'tanggal' => $created_at->translatedFormat('d M Y'),
                        'nasabah' => $p->nasabah->nama ?? 'Unknown',
                        'role' => 'Nasabah',
                        'gudang' => $gudangName,
                        'jenis' => 'Belum Ditimbang',
                        'berat' => 0.0,
                        'sumber' => 'Jemput',
                        'status' => 'Tidak Terlaksana',
                        'petugas' => $p->petugas->nama ?? '-',
                        'tukang' => $p->tukang->nama ?? '-',
                        'rawDate' => $p->created_at,
                    ];
                });
            }
        }

        // 3. Get Pengepul Transactions
        if (!$role || $role === 'Semua Role' || $role === 'Pengepul') {
            $pengepulQuery = \App\Models\TransaksiPengepul::with([
                'pengepul',
                'detailTransaksi.sampah.itemSampah',
                'detailTransaksi.sampah.gudang'
            ])->whereIn('status', ['selesai', 'tolak', 'batal']);

            if ($gudang && $gudang !== 'Semua Gudang') {
                $pengepulQuery->whereHas('detailTransaksi.sampah.gudang', function($q) use ($gudang) {
                    $q->where('alamat', $gudang);
                });
            }
            $pengepulQuery->where('created_at', '>=', $parsedStartDate);
            $pengepulQuery->where('created_at', '<=', $parsedEndDate);

            if ($jenisSampah) {
                $jenisArray = is_array($jenisSampah) ? $jenisSampah : explode(',', $jenisSampah);
                if (count($jenisArray) > 0) {
                    $pengepulQuery->whereHas('detailTransaksi.sampah.itemSampah', function($q) use ($jenisArray) {
                        $q->whereIn('nama', $jenisArray);
                    });
                }
            }

            if ($search) {
                $pengepulQuery->where(function($q) use ($search) {
                    $q->whereHas('pengepul', function($pq) use ($search) {
                        $pq->where('nama', 'like', "%$search%");
                    })->orWhere('transaksi_id', 'like', "%$search%");
                });
            }

            foreach ($pengepulQuery->get() as $t) {
                foreach ($t->detailTransaksi as $d) {
                    $gudangName = optional(optional($d->sampah)->gudang)->alamat ?? 'Unknown Gudang';
                    if ($gudang && $gudang !== 'Semua Gudang' && $gudang !== $gudangName) {
                        continue;
                    }

                    $jenisNama = optional(optional($d->sampah)->itemSampah)->nama ?? 'Unknown';
                    if ($jenisSampah) {
                        $jenisArray = is_array($jenisSampah) ? $jenisSampah : explode(',', $jenisSampah);
                        if (!in_array($jenisNama, $jenisArray)) {
                            continue;
                        }
                    }

                    $statusStr = $t->status === 'selesai' ? 'Selesai' : 'Tidak Terlaksana';

                    $pengepulData->push([
                        'tanggal' => Carbon::parse($t->created_at)->translatedFormat('d M Y'),
                        'nasabah' => $t->pengepul->nama ?? 'Unknown',
                        'role' => 'Pengepul',
                        'gudang' => $gudangName,
                        'jenis' => $jenisNama,
                        'berat' => (float)$d->berat,
                        'sumber' => 'Pengepul',
                        'status' => $statusStr,
                        'petugas' => '-',
                        'tukang' => '-',
                        'rawDate' => $t->created_at,
                    ]);
                }
            }
        }

        return $completedData->merge($failedData)->merge($pengepulData)->sortByDesc('rawDate')->values();
    }

    public function index(Request $request)
    {
        $allData = $this->getAllAuditData($request);
        
        // Exclude "Tidak Terlaksana" rows from the detailed audit table list
        $allData = $allData->filter(function($row) {
            return $row['status'] !== 'Tidak Terlaksana';
        })->values();
        
        $perPage = (int)$request->query('per_page', 10);
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $allData->forPage($page, $perPage)->values(),
            $allData->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return response()->json($paginated, 200);
    }

    public function summary(Request $request)
    {
        // Force summary to only filter by date range and gudang to match PDF print report
        $request->merge([
            'role' => null,
            'search' => null,
            'jenisSampah' => null
        ]);

        $allData = $this->getAllAuditData($request);

        // Only count Nasabah transactions in top cards and per-gudang statistics
        $nasabahData = $allData->where('role', 'Nasabah');

        $totalTransaksi = $nasabahData->count();
        $totalBerat = $nasabahData->sum('berat');
        $verifiedCount = $nasabahData->where('status', 'Selesai')->count();
        $verifiedWeight = $nasabahData->where('status', 'Selesai')->sum('berat');
        $pendingCount = $nasabahData->where('status', 'Tidak Terlaksana')->count();
        
        $jemputCount = $nasabahData->where('sumber', 'Jemput')->count();
        $jemputWeight = $nasabahData->where('sumber', 'Jemput')->sum('berat');
        $setorManualCount = $nasabahData->where('sumber', 'Setor Manual')->count();
        $setorManualWeight = $nasabahData->where('sumber', 'Setor Manual')->sum('berat');

        $gudangFilter = $request->query('gudang');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $gudangModel = ($gudangFilter && $gudangFilter !== 'Semua Gudang') ? \App\Models\Gudang::where('alamat', $gudangFilter)->first() : null;
        $gudangId = $gudangModel ? $gudangModel->gudang_id : null;

        $parsedStartDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $parsedEndDate = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        $perGudangMap = [];
        $masterGudangs = \App\Models\Gudang::all();
        foreach ($masterGudangs as $g) {
            $nama = $g->alamat ?? 'Unknown Gudang';
            if ($gudangFilter && $gudangFilter !== 'Semua Gudang' && $nama !== $gudangFilter) {
                continue;
            }
            $perGudangMap[$nama] = ['gudang' => $nama, 'transaksi' => 0, 'berat' => 0, 'verified' => 0, 'pending' => 0];
        }

        foreach ($nasabahData as $row) {
            $gudang = $row['gudang'];
            if (!isset($perGudangMap[$gudang])) {
                $perGudangMap[$gudang] = ['gudang' => $gudang, 'transaksi' => 0, 'berat' => 0, 'verified' => 0, 'pending' => 0];
            }
            $perGudangMap[$gudang]['transaksi']++;
            $perGudangMap[$gudang]['berat'] += $row['berat'];
            if ($row['status'] === 'Selesai') {
                $perGudangMap[$gudang]['verified']++;
            } else {
                $perGudangMap[$gudang]['pending']++;
            }
        }

        // Sort arrays
        usort($perGudangMap, function($a, $b) {
            return $b['berat'] <=> $a['berat'];
        });

        // Distribusi Jenis Sampah (based on current stock)
        $stockQuery = \App\Models\Sampah::with('itemSampah')->where('stok', '>', 0);
        if ($gudangId) {
            $stockQuery->where('gudang_id', $gudangId);
        }
        $stocks = $stockQuery->get();

        $groupedStocks = $stocks->groupBy(function ($s) {
            return optional($s->itemSampah)->nama ?? 'Lainnya';
        });

        $totalStok = $stocks->sum('stok');

        $jenisSampahList = [];
        foreach ($groupedStocks as $name => $items) {
            $berat = $items->sum('stok');
            $percentage = $totalStok > 0 ? ($berat / $totalStok) * 100 : 0.0;
            $jenisSampahList[] = [
                'name' => $name,
                'berat' => (float)$berat,
                'percentage' => (float)$percentage
            ];
        }

        usort($jenisSampahList, function($a, $b) {
            return $b['berat'] <=> $a['berat'];
        });

        // Penjualan Ke Pengepul
        $transaksiPengepulQuery = \App\Models\TransaksiPengepul::with(['pengepul', 'detailTransaksi.sampah.itemSampah', 'detailTransaksi.sampah.gudang'])
            ->where('status', 'selesai')
            ->whereBetween('created_at', [$parsedStartDate, $parsedEndDate]);

        if ($gudangId) {
            $transaksiPengepulQuery->whereHas('detailTransaksi.sampah.gudang', function ($q) use ($gudangId) {
                $q->where('gudang_id', $gudangId);
            });
        }
        $transaksiPengepuls = $transaksiPengepulQuery->get();

        $penjualanPengepulList = $transaksiPengepuls->map(function ($t) use ($gudangId) {
            $details = $t->detailTransaksi;
            if ($gudangId) {
                $details = $details->filter(function($d) use ($gudangId) {
                    return optional($d->sampah)->gudang_id == $gudangId;
                });
            }

            $totalBerat = $details->sum('berat');
            $diterima = $details->sum(function($d) {
                return $d->harga * $d->berat;
            });
            $keuntungan = $details->sum(function($d) {
                $hargaBeli = optional(optional($d->sampah)->itemSampah)->harga_beli ?? 0.0;
                return ($d->harga - $hargaBeli) * $d->berat;
            });

            return [
                'pengepul' => $t->pengepul->nama ?? 'Unknown',
                'total_berat' => $totalBerat,
                'diterima' => $diterima,
                'keuntungan' => $keuntungan
            ];
        });

        $totalPengepulBerat = $penjualanPengepulList->sum('total_berat');
        $totalPengepulDiterima = $penjualanPengepulList->sum('diterima');
        $totalPengepulKeuntungan = $penjualanPengepulList->sum('keuntungan');
        $jumlahPengepul = $penjualanPengepulList->pluck('pengepul')->unique()->count();

        // Total Dibayar Ke Nasabah
        $completedQuery = \App\Models\Penimbangan::where(function ($q) {
            $q->whereHas('penjemputan', function($q2) {
                $q2->whereIn('status', ['selesai']);
            })->orWhereHas('transaksi', function($q3) {
                $q3->where('tipe_transaksi', 'antar_sendiri')
                   ->whereIn('status', ['selesai']);
            });
        });

        if ($gudangFilter && $gudangFilter !== 'Semua Gudang') {
            $completedQuery->where(function ($q) use ($gudangFilter) {
                $q->where(function ($qJemput) use ($gudangFilter) {
                    $qJemput->whereHas('penjemputan.gudang', function($q2) use ($gudangFilter) {
                        $q2->where('alamat', $gudangFilter);
                    });
                })
                ->orWhere(function ($qSetor) use ($gudangFilter) {
                    $qSetor->whereHas('transaksi', function($q3) use ($gudangFilter) {
                        $q3->where('tipe_transaksi', 'antar_sendiri')
                           ->whereHas('petugas.gudang', function($q4) use ($gudangFilter) {
                               $q4->where('alamat', $gudangFilter);
                           });
                    });
                });
            });
        }
        
        $completedQuery->whereBetween('created_at', [$parsedStartDate, $parsedEndDate]);
        $completedData = $completedQuery->get();

        $transaksiNasabahIds = $completedData->pluck('transaksi_id')->filter()->unique()->toArray();
        $totalDibayarNasabah = \App\Models\TransaksiNasabah::whereIn('transaksi_id', $transaksiNasabahIds)
            ->where('status', 'selesai')
            ->sum('total_harga');

        $marginNasabah = $totalPengepulDiterima > 0 ? ($totalPengepulKeuntungan / $totalPengepulDiterima) * 100 : 0.0;

        return response()->json([
            'totalTransaksi' => $totalTransaksi,
            'totalBerat' => $totalBerat,
            'verifiedCount' => $verifiedCount,
            'verifiedWeight' => $verifiedWeight,
            'pendingCount' => $pendingCount,
            'jemputCount' => $jemputCount,
            'jemputWeight' => $jemputWeight,
            'setorManualCount' => $setorManualCount,
            'setorManualWeight' => $setorManualWeight,
            'perGudangList' => array_values($perGudangMap),
            'jenisSampahList' => $jenisSampahList,
            
            // Penjualan Pengepul & Nasabah totals
            'totalPengepulBerat' => $totalPengepulBerat,
            'totalPengepulDiterima' => $totalPengepulDiterima,
            'totalPengepulKeuntungan' => $totalPengepulKeuntungan,
            'totalDibayarNasabah' => $totalDibayarNasabah,
            'marginNasabah' => $marginNasabah,
            'jumlahPengepul' => $jumlahPengepul
        ], 200);
    }

    private function buildPenarikanQuery(Request $request)
    {
        $durasi = $request->query('durasi');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');
        $gudangId = $request->query('gudang_id');

        $query = \App\Models\Penarikan::with('nasabah')->latest();

        // Filter by Gudang
        if ($gudangId) {
            $query->whereHas('petugas', function($q) use ($gudangId) {
                $q->where('gudang_id', $gudangId);
            });
        }

        $parsedStartDate = null;
        $parsedEndDate = Carbon::now()->endOfDay();

        if ($durasi && $durasi !== 'Semua Waktu') {
            if ($durasi === '1 Minggu Terakhir') {
                $parsedStartDate = Carbon::now()->subWeek()->startOfDay();
            } elseif ($durasi === '1 Bulan Terakhir') {
                $parsedStartDate = Carbon::now()->subMonth()->startOfDay();
            } elseif ($durasi === '3 Bulan Terakhir') {
                $parsedStartDate = Carbon::now()->subMonths(3)->startOfDay();
            }
        } elseif ($startDate || $endDate) {
            if ($startDate) {
                $parsedStartDate = Carbon::parse($startDate)->startOfDay();
            }
            if ($endDate) {
                $parsedEndDate = Carbon::parse($endDate)->endOfDay();
            }
        } else {
            // Default: 1 Bulan Terakhir
            $parsedStartDate = Carbon::now()->subMonth()->startOfDay();
        }

        if ($parsedStartDate) {
            $query->where('created_at', '>=', $parsedStartDate);
        }
        if ($parsedEndDate) {
            $query->where('created_at', '<=', $parsedEndDate);
        }

        // Filter by Search (Nasabah Name or Penarikan ID)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('penarikan_id', 'like', "%$search%")
                  ->orWhereHas('nasabah', function($nq) use ($search) {
                      $nq->where('nama', 'like', "%$search%");
                  });
            });
        }

        return $query;
    }

    public function penarikanData(Request $request)
    {
        $query = $this->buildPenarikanQuery($request);
        
        $perPage = $request->query('per_page', 10);
        $data = $query->paginate($perPage);

        // Transform data
        $data->getCollection()->transform(function ($p) {
            $created_at = Carbon::parse($p->created_at);
            
            $statusStr = 'Menunggu';
            if ($p->status === 'selesai') $statusStr = 'Selesai';
            elseif ($p->status === 'tolak') $statusStr = 'Ditolak';

            return [
                // Prefix with WD- and pad with zeros to match design
                'id' => 'WD-2026-' . str_pad($p->penarikan_id, 4, '0', STR_PAD_LEFT),
                'tanggal' => $created_at->translatedFormat('d M Y'),
                'nasabah' => $p->nasabah->nama ?? 'Unknown',
                'nominal' => 'Rp ' . number_format($p->jumlah, 0, ',', '.'),
                'status' => $statusStr,
                'no_rekening' => $p->no_rekening,
                'nama_bank' => $p->nama_bank,
                'nama_rek' => $p->nama_rek,
                'petugas' => $statusStr === 'Selesai' ? (optional($p->petugas)->nama ?? '-') : '-',
                'gudang' => $statusStr === 'Selesai' ? (optional(optional($p->petugas)->gudang)->alamat ?? '-') : '-',
                'rawDate' => $p->created_at,
            ];
        });

        return response()->json($data, 200);
    }

    public function penarikanSummary(Request $request)
    {
        $query = $this->buildPenarikanQuery($request);
        
        $allData = $query->get();

        $selesaiCount = 0;
        $diprosesCount = 0;
        $ditolakCount = 0;
        
        $totalNominalSelesai = 0;
        $totalNominalDitolak = 0;

        foreach ($allData as $row) {
            if ($row->status === 'selesai') {
                $selesaiCount++;
                $totalNominalSelesai += $row->jumlah;
            } elseif ($row->status === 'tolak') {
                $ditolakCount++;
                $totalNominalDitolak += $row->jumlah;
            } else {
                $diprosesCount++;
            }
        }

        $totalNominalAll = $allData->sum('jumlah');

        // Bank distribution (group finished transactions by bank)
        $bankGroups = $allData->where('status', 'selesai')->groupBy('nama_bank');
        $bankDistribution = [];
        foreach ($bankGroups as $bankName => $items) {
            $bankNameStr = $bankName ?: 'Lainnya';
            $bankDistribution[] = [
                'nama_bank' => $bankNameStr,
                'alias' => strtolower(str_replace(' ', '', $bankNameStr)),
                'jumlah_transaksi' => $items->count(),
                'total_nominal' => $items->sum('jumlah'),
                'total_nominal_formatted' => 'Rp ' . number_format($items->sum('jumlah'), 0, ',', '.')
            ];
        }

        // Sort bank distribution by total_nominal desc
        usort($bankDistribution, function($a, $b) {
            return $b['total_nominal'] <=> $a['total_nominal'];
        });

        // Rasio percentages
        $totalFinal = $selesaiCount + $ditolakCount;
        $selesaiPercent = $totalFinal > 0 ? round(($selesaiCount / $totalFinal) * 100) : 0;
        $ditolakPercent = $totalFinal > 0 ? 100 - $selesaiPercent : 0;

        return response()->json([
            'totalNominal' => 'Rp ' . number_format($totalNominalAll, 0, ',', '.'),
            'selesai' => $selesaiCount,
            'diproses' => $diprosesCount,
            'ditolak' => $ditolakCount,
            
            // Detailed stats
            'totalTransaksiFinal' => $totalFinal,
            'totalNominalSelesai' => $totalNominalSelesai,
            'totalNominalSelesaiFormatted' => 'Rp ' . number_format($totalNominalSelesai, 0, ',', '.'),
            'totalDitolak' => $ditolakCount,
            'totalDitolakNominal' => $totalNominalDitolak,
            'totalDitolakNominalFormatted' => 'Rp ' . number_format($totalNominalDitolak, 0, ',', '.'),
            'bankDistribution' => $bankDistribution,
            'selesaiPercent' => $selesaiPercent,
            'ditolakPercent' => $ditolakPercent
        ], 200);
    }
}
