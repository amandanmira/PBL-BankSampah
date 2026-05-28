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
            'transaksi',
            'penjemputan.gudang'
        ])->whereHas('penjemputan', function($q) {
            $q->whereIn('status', ['selesai', 'tolak', 'batal']);
        });

        // Filter by Gudang
        if ($gudang && $gudang !== 'Semua Gudang') {
            $query->whereHas('penjemputan.gudang', function($q) use ($gudang) {
                $q->where('alamat', $gudang)->orWhere('nama_gudang', $gudang);
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

    public function index(Request $request)
    {
        $query = $this->buildQuery($request);
        
        $perPage = $request->query('per_page', 10);
        $data = $query->latest()->paginate($perPage);

        // Transform data to flat structure expected by frontend
        $data->getCollection()->transform(function ($p) {
            // Mapping English months to Indonesian abbreviation as handled by dayjs manually or directly sending standard format
            $created_at = Carbon::parse($p->created_at);
            
            return [
                'tanggal' => $created_at->translatedFormat('d M Y'),
                'nasabah' => $p->nasabah->nama ?? 'Unknown',
                'gudang' => $p->penjemputan->gudang->alamat ?? $p->penjemputan->gudang->nama_gudang ?? 'Unknown Gudang',
                'jenis' => $p->sampah->itemSampah->nama ?? 'Unknown',
                'berat' => (float)$p->berat_timbang,
                'petugas' => $p->tukang->nama ?? 'Unknown',
                'status' => optional($p->transaksi)->status === 'selesai' ? 'Verified' : 'Pending',
                'rawDate' => $p->created_at,
            ];
        });

        return response()->json($data, 200);
    }

    public function summary(Request $request)
    {
        $query = $this->buildQuery($request);

        // Fetch all matching records to compute aggregations
        $allData = $query->get();

        $totalTransaksi = 0;
        $totalBerat = 0;
        $verifiedCount = 0;
        $pendingCount = 0;

        $perGudangMap = [];
        $jenisSampahMap = [
            'Organik' => ['berat' => 0],
            'Plastik PET' => ['berat' => 0],
            'Kertas' => ['berat' => 0],
            'Logam' => ['berat' => 0]
        ];

        foreach ($allData as $row) {
            $totalTransaksi++;
            $berat = (float)$row->berat_timbang;
            $totalBerat += $berat;
            
            $status = optional($row->transaksi)->status === 'selesai' ? 'Verified' : 'Pending';
            if ($status === 'Verified') {
                $verifiedCount++;
            } else {
                $pendingCount++;
            }

            $gudang = $row->penjemputan->gudang->alamat ?? $row->penjemputan->gudang->nama_gudang ?? 'Unknown Gudang';
            if (!isset($perGudangMap[$gudang])) {
                $perGudangMap[$gudang] = ['gudang' => $gudang, 'transaksi' => 0, 'berat' => 0, 'verified' => 0, 'pending' => 0];
            }
            $perGudangMap[$gudang]['transaksi']++;
            $perGudangMap[$gudang]['berat'] += $berat;
            if ($status === 'Verified') {
                $perGudangMap[$gudang]['verified']++;
            } else {
                $perGudangMap[$gudang]['pending']++;
            }

            $jenis = $row->sampah->itemSampah->nama ?? 'Unknown';
            if (isset($jenisSampahMap[$jenis])) {
                $jenisSampahMap[$jenis]['berat'] += $berat;
            } else {
                $jenisSampahMap[$jenis] = ['berat' => $berat];
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
            'pendingCount' => $pendingCount,
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
