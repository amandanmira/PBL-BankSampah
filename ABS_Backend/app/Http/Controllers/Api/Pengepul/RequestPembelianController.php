<?php

namespace App\Http\Controllers\Api\Pengepul;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\Pengepul\TransaksiPengepulExport;
use App\Models\TransaksiPengepul;
use App\Models\ItemSampah;
use App\Models\KategoriSampah;
use App\Models\Sampah;

class RequestPembelianController extends Controller
{
    public function indexSampah() {
        return response()->json([
            'sampah' => Sampah::with(['itemSampah.kategoriSampah', 'gudang'])
                ->where('stok', '>', 0)
                ->where('active', 1)
                ->get(),
            'gudang' => \App\Models\Gudang::where('active', 1)->get()
        ]);
    }

    public function index(Request $request, $pengepul_id) {
        TransaksiPengepul::cancelExpiredTransactions($pengepul_id);

        $activeTab = $request->input('status', 'menunggu');
        $search = $request->input('search', '');

        $baseQuery = TransaksiPengepul::with('detailTransaksi.sampah.itemSampah')
            ->where('pengepul_id', $pengepul_id);

        if (!empty($search)) {
            $baseQuery->where(function ($q) use ($search) {
                if (preg_match('/#?(\d+)/i', $search, $matches)) {
                    $q->where('transaksi_id', (int)$matches[1]);
                } else {
                    $q->where('transaksi_id', 'like', "%{$search}%")
                      ->orWhereHas('detailTransaksi.sampah.itemSampah', function ($sub) use ($search) {
                          $sub->where('nama', 'like', "%{$search}%");
                      });
                }
            });
        }

        // Count queries
        $menungguCount = (clone $baseQuery)->where(function ($q) {
            $q->where('status', 'pending')
              ->orWhere(function ($sub) {
                  $sub->where('status', 'proses')
                      ->where(function ($sub2) {
                          $sub2->whereNull('bukti_transfer')
                               ->orWhere('bukti_transfer', '');
                      });
              });
        })->count();

        $diprosesCount = (clone $baseQuery)->where(function ($q) {
            $q->where(function ($sub) {
                  $sub->where('status', 'proses')
                      ->whereNotNull('bukti_transfer')
                      ->where('bukti_transfer', '!=', '');
              })
              ->orWhere('status', 'siap_diambil');
        })->count();

        $selesaiCount = (clone $baseQuery)->where('status', 'selesai')->count();
        $ditolakCount = (clone $baseQuery)->whereIn('status', ['tolak', 'batal'])->count();

        // Apply Tab Filter
        if ($activeTab === 'menunggu') {
            $baseQuery->where(function ($q) {
                $q->where('status', 'pending')
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'proses')
                          ->where(function ($sub2) {
                              $sub2->whereNull('bukti_transfer')
                                   ->orWhere('bukti_transfer', '');
                          });
                  });
            });
        } elseif ($activeTab === 'diproses') {
            $baseQuery->where(function ($q) {
                $q->where(function ($sub) {
                      $sub->where('status', 'proses')
                          ->whereNotNull('bukti_transfer')
                          ->where('bukti_transfer', '!=', '');
                  })
                  ->orWhere('status', 'siap_diambil');
            });
        } elseif ($activeTab === 'selesai') {
            $baseQuery->where('status', 'selesai');
        } elseif ($activeTab === 'ditolak') {
            $baseQuery->whereIn('status', ['tolak', 'batal']);
        }

        $orders = $baseQuery->latest()->paginate(10);

        return response()->json([
            'data' => $orders->items(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'total' => $orders->total(),
            'counts' => [
                'menunggu' => $menungguCount,
                'diproses' => $diprosesCount,
                'selesai' => $selesaiCount,
                'ditolak' => $ditolakCount
            ]
        ]);
    }

    public function show(string $id)
    {
        TransaksiPengepul::cancelExpiredTransactions(null, $id);

        $transaksi = TransaksiPengepul::with([
            'detailTransaksi.sampah.itemSampah',
            'detailTransaksi.sampah.gudang'
        ])->findOrFail($id);

        return response()->json($transaksi);
    }

    public function store(Request $request)
    {
        // 1. Hapus pengepul_id dari validasi agar klien tidak perlu mengirimkannya
        $request->validate([
            'pengepul_id' => 'required',
            'detail' => 'required|array',
            'detail' => 'required|array',
            'detail.*.sampah_id' => 'required|integer',
            'detail.*.berat' => 'required|numeric',
            'detail.*.harga' => 'required|numeric'
        ]);

        // simpan item jika ada
        foreach ($request->detail as $d) {
            $sampah = Sampah::lockForUpdate()->findOrFail($d['sampah_id']);

            if ($sampah->stok >= $d['berat']){
                $sampah->update([
                    'stok' => $sampah->stok - $d['berat'],
                ]);
            } else {
                throw new \Exception('Stok tidak cukup');
            }
        }

        $config = \App\Models\KonfigurasiWeb::first();
        $deadline = now()->addHours((int)($config->lama_deadline ?? 24));

        // 2. Ambil pengepul_id dari user yang sedang login menggunakan token
        $transaksi = TransaksiPengepul::create([
            'status' => 'proses',
            'pengepul_id' => $request->user()->pengepul_id, // <-- Diubah di sini
            'deadline' => $deadline,
            'ket_status' => 'Pesanan dibuat. Silakan lakukan pembayaran dan upload bukti transfer.'
        ]);

        foreach ($request->detail as $d) {
            $transaksi->detailTransaksi()->create([
                'sampah_id' => $d['sampah_id'],
                'berat' => $d['berat'] ?? 0,
                'harga' => $d['harga'] ?? 0,
                'status' => 'pending',
                'status_pembayaran' => 'pending',
                'status_pengambilan' => 'pending',
            ]);
        }

        return response()->json($transaksi->load('detailTransaksi'), 201);
    }

    public function update(Request $request, $id)
    {
        $transaksi = TransaksiPengepul::findOrFail($id);

        $validated = $request->validate([
            'bukti_transfer' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($request->hasFile('bukti_transfer')) {
            if ($transaksi->bukti_transfer && Storage::disk('public')->exists($transaksi->bukti_transfer)) {
                Storage::disk('public')->delete($transaksi->bukti_transfer);
            }

            $path = $request->file('bukti_transfer')->store('foto-bukti-transfer', 'public');

            $validated['bukti_transfer'] = $path;
        }

        $transaksi->update($validated);

        return response()->json([
            'data' => $transaksi
        ]);
    }

    public function exportExcel($pengepul_id)
    {
        return Excel::download(new TransaksiPengepulExport($pengepul_id), 'transaksi.xlsx');
    }

    public function exportPdf($transaksi_id)
    {
        $transaksi = TransaksiPengepul::with(['detailTransaksi.sampah.itemSampah', 'pengepul'])
            ->withSum('detailTransaksi', 'harga')
            ->findOrFail($transaksi_id);

        $pdf = Pdf::loadView('pdf.transaksi-pengepul', compact('transaksi'));

        return $pdf->stream();
    }

    public function cancel(string $id)
    {
        $transaksi = TransaksiPengepul::with('detailTransaksi')->findOrFail($id);

        if ($transaksi->status !== 'proses' && $transaksi->status !== 'pending') {
            return response()->json([
                'message' => 'Pesanan hanya dapat dibatalkan jika status pending atau proses.'
            ], 400);
        }

        // Return stock back to warehouse since order is cancelled
        foreach ($transaksi->detailTransaksi as $d) {
            $sampah = Sampah::find($d->sampah_id);
            if ($sampah) {
                $sampah->update([
                    'stok' => $sampah->stok + $d->berat,
                ]);
            }
        }

        $transaksi->update([
            'status' => 'batal',
            'ket_status' => 'Dibatalkan oleh pengepul'
        ]);

        return response()->json([
            'message' => 'Pesanan berhasil dibatalkan',
            'data' => $transaksi
        ]);
    }

    public function dashboardStats($pengepul_id)
    {
        $pengepulId = $pengepul_id;

        // 1. Total Pengeluaran (Total Expenses for this specific Pengepul)
        $totalPengeluaran = TransaksiPengepul::where('pengepul_id', $pengepulId)
            ->whereNotIn('transaksi_pengepuls.status', ['tolak', 'batal'])
            ->join('detail_transaksis', 'transaksi_pengepuls.transaksi_id', '=', 'detail_transaksis.transaksi_id')
            ->sum(\Illuminate\Support\Facades\DB::raw('detail_transaksis.harga * detail_transaksis.berat'));

        // 2. Total Sampah Terkumpul (for this specific Pengepul)
        $totalSampah = TransaksiPengepul::where('pengepul_id', $pengepulId)
            ->whereNotIn('transaksi_pengepuls.status', ['tolak', 'batal'])
            ->join('detail_transaksis', 'transaksi_pengepuls.transaksi_id', '=', 'detail_transaksis.transaksi_id')
            ->sum('detail_transaksis.berat');

        // 3. Pesanan Aktif (for this specific Pengepul)
        $pesananAktifCount = TransaksiPengepul::where('pengepul_id', $pengepulId)
            ->whereIn('status', ['pending', 'proses', 'siap_diambil'])
            ->count();

        // 4. Status Pesanan Aktif (List for this specific Pengepul)
        $pesananAktif = TransaksiPengepul::with('detailTransaksi.sampah.itemSampah')
            ->where('pengepul_id', $pengepulId)
            ->whereIn('status', ['pending', 'proses', 'siap_diambil'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($t) {
                return [
                    'transaksi_id' => $t->transaksi_id,
                    'status' => $t->status,
                    'bukti_transfer' => $t->bukti_transfer,
                    'items_summary' => $t->detailTransaksi->map(function ($d) {
                        return $d->sampah->itemSampah->nama ?? 'Sampah';
                    })->unique()->values()->implode(', '),
                    'total_berat' => (float)$t->detailTransaksi->sum('berat')
                ];
            });

        // 5. Tren Pembelian (Mingguan - Seluruh Pengepul Bulan Ini)
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $allTransactions = TransaksiPengepul::with(['detailTransaksi.sampah.itemSampah'])
            ->whereNotIn('transaksi_pengepuls.status', ['tolak', 'batal'])
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get();

        $weeks = [
            'Minggu 1' => 0,
            'Minggu 2' => 0,
            'Minggu 3' => 0,
            'Minggu 4' => 0,
        ];

        foreach ($allTransactions as $t) {
            $day = $t->created_at->day;
            if ($day <= 7) {
                $weekKey = 'Minggu 1';
            } elseif ($day <= 14) {
                $weekKey = 'Minggu 2';
            } elseif ($day <= 21) {
                $weekKey = 'Minggu 3';
            } else {
                $weekKey = 'Minggu 4';
            }

            foreach ($t->detailTransaksi as $detail) {
                $weeks[$weekKey] += (float)$detail->berat;
            }
        }

        // 6. Top 3 Items Dipesan dengan Distribusi Per Minggu (Grouped Bar Chart)
        $topItems = \App\Models\DetailTransaksi::join('transaksi_pengepuls', 'detail_transaksis.transaksi_id', '=', 'transaksi_pengepuls.transaksi_id')
            ->join('sampahs', 'detail_transaksis.sampah_id', '=', 'sampahs.sampah_id')
            ->join('item_sampahs', 'sampahs.item_id', '=', 'item_sampahs.item_id')
            ->whereNotIn('transaksi_pengepuls.status', ['tolak', 'batal'])
            ->select('item_sampahs.nama', 'item_sampahs.item_id', \Illuminate\Support\Facades\DB::raw('SUM(detail_transaksis.berat) as total_berat'))
            ->groupBy('item_sampahs.nama', 'item_sampahs.item_id')
            ->orderBy('total_berat', 'desc')
            ->take(3)
            ->get();

        $itemTrends = [];
        foreach ($topItems as $item) {
            $itemTrends[$item->nama] = [
                'Minggu 1' => 0.0,
                'Minggu 2' => 0.0,
                'Minggu 3' => 0.0,
                'Minggu 4' => 0.0,
            ];
        }

        while (count($itemTrends) < 3) {
            $itemTrends['N/A ' . (count($itemTrends) + 1)] = [
                'Minggu 1' => 0.0,
                'Minggu 2' => 0.0,
                'Minggu 3' => 0.0,
                'Minggu 4' => 0.0,
            ];
        }

        foreach ($allTransactions as $t) {
            $day = $t->created_at->day;
            if ($day <= 7) {
                $weekKey = 'Minggu 1';
            } elseif ($day <= 14) {
                $weekKey = 'Minggu 2';
            } elseif ($day <= 21) {
                $weekKey = 'Minggu 3';
            } else {
                $weekKey = 'Minggu 4';
            }

            foreach ($t->detailTransaksi as $detail) {
                $itemName = $detail->sampah->itemSampah->nama ?? null;
                if ($itemName && isset($itemTrends[$itemName])) {
                    $itemTrends[$itemName][$weekKey] += (float)$detail->berat;
                }
            }
        }

        $formattedTopItemsWeekly = [];
        foreach ($itemTrends as $name => $weekValues) {
            $formattedTopItemsWeekly[] = [
                'name' => $name,
                'data' => array_values($weekValues)
            ];
        }

        $chartData = [
            'categories' => array_keys($weeks),
            'berat' => array_values($weeks),
            'top_items_weekly' => $formattedTopItemsWeekly
        ];

        // 7. Dynamic Market Insights
        $ketersediaanTinggi = \App\Models\Sampah::with(['itemSampah', 'gudang'])
            ->where('stok', '>', 0)
            ->orderBy('stok', 'desc')
            ->first();

        $stokMenipis = \App\Models\Sampah::with(['itemSampah'])
            ->where('stok', '>', 0)
            ->orderBy('stok', 'asc')
            ->first();

        $hargaTurun = \App\Models\ItemSampah::where('diskon', '>', 0)
            ->orderBy('diskon', 'desc')
            ->first();

        $insightKetersediaan = null;
        if ($ketersediaanTinggi && $ketersediaanTinggi->itemSampah) {
            $insightKetersediaan = [
                'tipe' => 'ketersediaan_tinggi',
                'label' => 'Ketersediaan Tinggi',
                'nama' => $ketersediaanTinggi->itemSampah->nama,
                'info' => 'Gudang ' . $ketersediaanTinggi->gudang_id . ' - ' . number_format($ketersediaanTinggi->stok, 0) . 'kg',
            ];
        } else {
            $insightKetersediaan = [
                'tipe' => 'ketersediaan_tinggi',
                'label' => 'Ketersediaan Tinggi',
                'nama' => 'Kertas HVS',
                'info' => 'Gudang 1 - 800kg',
            ];
        }

        $insightStokMenipis = null;
        if ($stokMenipis && $stokMenipis->itemSampah) {
            $insightStokMenipis = [
                'tipe' => 'stok_menipis',
                'label' => 'Stok Menipis',
                'nama' => $stokMenipis->itemSampah->nama,
                'info' => 'Sisa: ' . number_format($stokMenipis->stok, 0) . 'kg',
            ];
        } else {
            $insightStokMenipis = [
                'tipe' => 'stok_menipis',
                'label' => 'Stok Menipis',
                'nama' => 'Kaleng Aluminium',
                'info' => 'Sisa: 50kg',
            ];
        }

        $insightHargaTurun = null;
        if ($hargaTurun) {
            $insightHargaTurun = [
                'tipe' => 'harga_turun',
                'label' => 'Harga Turun',
                'nama' => $hargaTurun->nama,
                'info' => 'Turun Rp ' . number_format($hargaTurun->diskon, 0, ',', '.') . '/kg',
            ];
        } else {
            $cheapestItem = \App\Models\ItemSampah::orderBy('harga_jual', 'asc')->first();
            if ($cheapestItem) {
                $insightHargaTurun = [
                    'tipe' => 'harga_turun',
                    'label' => 'Harga Murah',
                    'nama' => $cheapestItem->nama,
                    'info' => 'Harga Rp ' . number_format($cheapestItem->harga_jual, 0, ',', '.') . '/kg',
                ];
            } else {
                $insightHargaTurun = [
                    'tipe' => 'harga_turun',
                    'label' => 'Harga Turun',
                    'nama' => 'Botol Kaca',
                    'info' => 'Turun Rp 200/kg',
                ];
            }
        }

        $marketInsights = [
            $insightKetersediaan,
            $insightStokMenipis,
            $insightHargaTurun
        ];
        return response()->json([
            'total_pengeluaran' => (float)$totalPengeluaran,
            'total_sampah' => (float)$totalSampah,
            'pesanan_aktif_count' => $pesananAktifCount,
            'pesanan_aktif' => $pesananAktif,
            'chart_data' => $chartData,
            'market_insights' => $marketInsights
        ]);
    }
}