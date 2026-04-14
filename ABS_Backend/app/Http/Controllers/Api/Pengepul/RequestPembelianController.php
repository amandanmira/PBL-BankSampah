<?php

namespace App\Http\Controllers\Api\Pengepul;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\TransaksiPengepulExport;
use App\Models\TransaksiPengepul;
use App\Models\ItemSampah;
use App\Models\KategoriSampah;
use App\Models\Sampah;

class RequestPembelianController extends Controller
{
    public function indexSampah() {
        return response()->json(
            KategoriSampah::with('itemSampah.sampah.gudang')->get()
        );
    }

    public function index($pengepul_id) {
        TransaksiPengepul::where('pengepul_id', $pengepul_id)
            ->where('status', 'proses')
            ->where('deadline', '<', now())
            ->where(function ($query) {
                $query->whereNull('bukti_transfer')
                    ->orWhere('bukti_transfer', '');
            })
            ->update([
                'status' => 'tolak'
            ]);

        return response()->json(
            TransaksiPengepul::with('detailTransaksi.sampah.itemSampah')->where('pengepul_id', $pengepul_id)->get()
        );
    }

    public function show(string $id)
    {
        $transaksi = TransaksiPengepul::with('detailTransaksi')->findOrFail($id);

        $transaksi->where('status', 'proses')
            ->where('deadline', '<', now())
            ->where(function ($query) {
                $query->whereNull('bukti_transfer')
                    ->orWhere('bukti_transfer', '');
            })
            ->update([
                'status' => 'tolak'
            ]);

        return response()->json($transaksi);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pengepul_id' => 'required',
            'detail' => 'required|array'
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

        $transaksi = TransaksiPengepul::create([
            'status' => 'pending',
            'pengepul_id' => $request->pengepul_id,
        ]);

        foreach ($request->detail as $d) {
            $transaksi->detailTransaksi()->create([
                'sampah_id' => $d['sampah_id'],
                'berat' => $d['berat'] ?? 0,
                'harga' => $d['harga'] ?? 0,
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

    public function exportExcel()
    {
        return Excel::download(new TransaksiPengepulExport, 'transaksi.xlsx');
    }

    public function exportPdf($transaksi_id)
    {
        $transaksi = TransaksiPengepul::with('detailTransaksi.sampah.itemSampah')
                        ->findOrFail($transaksi_id);

        $pdf = Pdf::loadView('pdf.transaksi-pengepul', compact('transaksi'));

        return $pdf->stream();
    }
}
