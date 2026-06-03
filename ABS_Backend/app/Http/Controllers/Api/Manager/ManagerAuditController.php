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
                    $q2->where('alamat', $gudang)->orWhere('nama_gudang', $gudang);
                })->orWhereHas('transaksi.petugas.gudang', function($q3) use ($gudang) {
                    $q3->where('alamat', $gudang)->orWhere('nama_gudang', $gudang);
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

        // 1. Get Completed Transactions
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
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
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

        // 2. Get Failed Transactions (from Penjemputan directly)
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
        if ($startDate) $failedQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate) $failedQuery->whereDate('created_at', '<=', $endDate);
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

        return $completedData->merge($failedData)->sortByDesc('rawDate')->values();
    }

    public function index(Request $request)
    {
        $allData = $this->getAllAuditData($request);
        
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
        $allData = $this->getAllAuditData($request);

        $totalTransaksi = $allData->count();
        $totalBerat = $allData->sum('berat');
        $verifiedCount = $allData->where('status', 'Selesai')->count();
        $verifiedWeight = $allData->where('status', 'Selesai')->sum('berat');
        $pendingCount = $allData->where('status', 'Tidak Terlaksana')->count();
        
        $jemputCount = $allData->where('sumber', 'Jemput')->count();
        $jemputWeight = $allData->where('sumber', 'Jemput')->sum('berat');
        $setorManualCount = $allData->where('sumber', 'Setor Manual')->count();
        $setorManualWeight = $allData->where('sumber', 'Setor Manual')->sum('berat');

        $gudangFilter = $request->query('gudang');

        $perGudangMap = [];
        $masterGudangs = \App\Models\Gudang::all();
        foreach ($masterGudangs as $g) {
            $nama = $g->alamat ?? 'Unknown Gudang';
            if ($gudangFilter && $gudangFilter !== 'Semua Gudang' && $nama !== $gudangFilter) {
                continue;
            }
            $perGudangMap[$nama] = ['gudang' => $nama, 'transaksi' => 0, 'berat' => 0, 'verified' => 0, 'pending' => 0];
        }
        $jenisSampahMap = [
            'Organik' => ['berat' => 0],
            'Plastik PET' => ['berat' => 0],
            'Kertas' => ['berat' => 0],
            'Logam' => ['berat' => 0]
        ];

        foreach ($allData as $row) {
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

            $jenis = $row['jenis'];
            if ($jenis !== 'Belum Ditimbang') {
                if (isset($jenisSampahMap[$jenis])) {
                    $jenisSampahMap[$jenis]['berat'] += $row['berat'];
                } else {
                    $jenisSampahMap[$jenis] = ['berat' => $row['berat']];
                }
            }
        }

        // Sort arrays
        usort($perGudangMap, function($a, $b) {
            return $b['berat'] <=> $a['berat'];
        });

        $jenisSampahList = [];
        foreach ($jenisSampahMap as $name => $data) {
            $berat = $data['berat'];
            $percentage = $totalBerat > 0 ? ($berat / $totalBerat) * 100 : 0;
            if ($berat > 0 || in_array($name, ['Organik', 'Plastik PET', 'Kertas', 'Logam'])) {
                $jenisSampahList[] = [
                    'name' => $name,
                    'berat' => $berat,
                    'percentage' => $percentage
                ];
            }
        }

        usort($jenisSampahList, function($a, $b) {
            return $b['berat'] <=> $a['berat'];
        });

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
            'jenisSampahList' => $jenisSampahList
        ], 200);
    }

    private function buildPenarikanQuery(Request $request)
    {
        $durasi = $request->query('durasi');
        $search = $request->query('search');

        $query = \App\Models\Penarikan::with('nasabah')->latest();

        // Filter by Durasi
        if ($durasi && $durasi !== 'Semua Waktu') {
            if ($durasi === '1 Minggu Terakhir') {
                $query->where('created_at', '>=', now()->subWeek());
            } elseif ($durasi === '1 Bulan Terakhir') {
                $query->where('created_at', '>=', now()->subMonth());
            } elseif ($durasi === '3 Bulan Terakhir') {
                $query->where('created_at', '>=', now()->subMonths(3));
            }
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
            
            $statusStr = 'Diproses';
            if ($p->status === 'selesai') $statusStr = 'Selesai';
            elseif ($p->status === 'tolak') $statusStr = 'Ditolak';

            return [
                // Prefix with WD- and pad with zeros to match design
                'id' => 'WD-2026-' . str_pad($p->penarikan_id, 4, '0', STR_PAD_LEFT),
                'tanggal' => $created_at->translatedFormat('d M Y'),
                'nasabah' => $p->nasabah->nama ?? 'Unknown',
                'nominal' => 'Rp ' . number_format($p->jumlah, 0, ',', '.'),
                'status' => $statusStr,
                'rawDate' => $p->created_at,
            ];
        });

        return response()->json($data, 200);
    }

    public function penarikanSummary(Request $request)
    {
        $query = $this->buildPenarikanQuery($request);
        
        $allData = $query->get();

        $selesai = 0;
        $diproses = 0;
        $ditolak = 0;

        foreach ($allData as $row) {
            if ($row->status === 'selesai') {
                $selesai++;
            } elseif ($row->status === 'tolak') {
                $ditolak++;
            } else {
                $diproses++;
            }
        }

        // Sum all requested nominals
        $totalNominalAll = $allData->sum('jumlah');

        return response()->json([
            'totalNominal' => 'Rp ' . number_format($totalNominalAll, 0, ',', '.'),
            'selesai' => $selesai,
            'diproses' => $diproses,
            'ditolak' => $ditolak
        ], 200);
    }
}
