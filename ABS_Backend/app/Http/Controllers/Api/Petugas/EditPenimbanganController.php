<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransaksiNasabah;
use App\Models\Penimbangan;
use App\Models\Sampah;
use App\Models\Nasabah;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditPenimbanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaksi = TransaksiNasabah::with([
            'penimbangan' => function ($query) {
                $query->with(['sampah.itemSampah']);
            },
            'nasabah'
        ])->find($id);

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        return response()->json(['data' => $transaksi]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'items'                 => 'required|array|min:1',
            'items.*.sampah_id'     => 'required|exists:sampahs,sampah_id',
            'items.*.berat_timbang' => 'required|numeric|min:0.1',
        ]);

        DB::beginTransaction();

        try {
            $transaksi = TransaksiNasabah::with('penimbangan')->findOrFail($id);
            
            // --- Cek Status ---
            if ($transaksi->status !== 'selesai') {
                return response()->json([
                    'message' => 'Hanya transaksi dengan status Selesai yang dapat diedit.'
                ], 400); 
            }

            // --- Cek Batas Waktu 12 Jam ---
            if ($transaksi->created_at->diffInHours(now()) >= 12) {
                return response()->json([
                    'message' => 'Batas waktu edit telah habis. Transaksi hanya dapat diedit maksimal 12 jam setelah dibuat.'
                ], 400);
            }

            // --- PERBAIKAN: Ambil ID penting dari penimbangan lama ---
            $originalPenimbangan = $transaksi->penimbangan->first();
            
            if (!$originalPenimbangan) {
                return response()->json(['message' => 'Data penimbangan tidak ditemukan'], 404);
            }

            $nasabah_id     = $originalPenimbangan->nasabah_id;
            $tukang_id      = $originalPenimbangan->tukang_id;
            $penjemputan_id = $originalPenimbangan->penjemputan_id;

            // Cari nasabah menggunakan nasabah_id yang benar
            $nasabah = Nasabah::findOrFail($nasabah_id);

            // 1. Kembalikan saldo nasabah ke sebelum transaksi ini
            $nasabah->saldo -= $transaksi->total_harga;
            $nasabah->save();

            // 2. Kembalikan stok sampah & hapus penimbangan lama
            foreach ($transaksi->penimbangan as $itemLama) {
                $sampah = Sampah::find($itemLama->sampah_id);
                if ($sampah) {
                    $sampah->stok -= $itemLama->berat_timbang;
                    $sampah->save();
                }
                $itemLama->delete();
            }

            // 3. Proses item baru dan hitung total harga baru
            $new_total_harga = 0;
            foreach ($request->items as $itemData) {
                $penimbangan = new Penimbangan();
                $penimbangan->sampah_id      = $itemData['sampah_id'];
                $penimbangan->berat_timbang  = $itemData['berat_timbang'];
                
                // --- PERBAIKAN: Gunakan variabel $nasabah_id dan $id ---
                $penimbangan->nasabah_id     = $nasabah_id; 
                $penimbangan->transaksi_id   = $id; 
                $penimbangan->tukang_id      = $tukang_id;
                $penimbangan->penjemputan_id = $penjemputan_id;
                $penimbangan->save();

                // Update stok sampah
                $sampah = Sampah::with('itemSampah')->find($itemData['sampah_id']);
                if ($sampah) {
                    $sampah->stok += $itemData['berat_timbang'];
                    $sampah->save();

                    $harga_per_kg = $sampah->itemSampah->harga_beli ?? 0;
                    $new_total_harga += ($harga_per_kg * $itemData['berat_timbang']);
                }
            }

            // 4. Update saldo nasabah dengan total harga baru
            $nasabah->saldo += $new_total_harga;
            $nasabah->save();

            // 5. Update transaksi dengan data baru
            $transaksi->total_harga = $new_total_harga;
            $transaksi->saldo_sebelum = $nasabah->saldo - $new_total_harga;
            $transaksi->saldo_sesudah = $nasabah->saldo;
            $transaksi->save();

            DB::commit();

            return response()->json([
                'message' => 'Data penimbangan berhasil diperbarui!',
                'data' => $transaksi->load('penimbangan.sampah.itemSampah')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui data penimbangan.',
                'error'   => $e->getMessage() 
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}