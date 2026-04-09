<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Penimbangan;
use Illuminate\Http\Request;
use App\Models\Sampah;

class PenimbanganController extends Controller
{
public function penimbangan(Request $request)
    {
        // 1. Validasi Input (Ubah validasi foto menjadi per item)
        $request->validate([
            'penjemputan_id'        => 'required',
            'tukang_id'             => 'required',
            'tipe_transaksi'        => 'required|in:dijemput,antar_sendiri',
            'items'                 => 'required|array|min:1',
            'items.*.sampah_id'     => 'required',
            'items.*.berat_timbang' => 'required|numeric|min:0.1',
            'items.*.foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // Validasi foto per baris
        ]);

        DB::beginTransaction();

        try {
            $penjemputan = \App\Models\Penjemputan::findOrFail($request->penjemputan_id);

            // Buat Data Transaksi Baru
            $transaksi = new \App\Models\TransaksiNasabah();
            $transaksi->tipe_transaksi = $request->tipe_transaksi;
            $transaksi->tanggal        = now();
            $transaksi->status         = 'selesai';
            $transaksi->petugas_id     = $request->user()->petugas_id;
            $transaksi->save();

            $total_semua_harga = 0;

            foreach ($request->items as $index => $itemData) {
                // ... (Proses cek foto, simpan penimbangan, dan update stok sampah tetap sama) ...

                $fotoPath = null;
                if ($request->hasFile("items.$index.foto")) {
                    $fotoPath = $request->file("items.$index.foto")->store('penimbangan_foto', 'public');
                }

                $penimbangan = new Penimbangan();
                $penimbangan->sampah_id      = $itemData['sampah_id'];
                $penimbangan->berat_timbang  = $itemData['berat_timbang'];
                $penimbangan->nasabah_id     = $penjemputan->nasabah_id;
                $penimbangan->transaksi_id   = $transaksi->transaksi_id;
                $penimbangan->foto           = $fotoPath;
                $penimbangan->gudang_id      = $request->user()->gudang_id;
                $penimbangan->tukang_id      = $request->tukang_id;
                $penimbangan->penjemputan_id = $request->penjemputan_id;
                $penimbangan->save();

                $sampah = \App\Models\Sampah::where('sampah_id', $itemData['sampah_id'])->first();
                if ($sampah) {
                    $sampah->stok += $itemData['berat_timbang'];
                    $sampah->save();

                    $sampah->load('itemSampah');
                    $harga_per_kg = $sampah->itemSampah ? $sampah->itemSampah->harga_beli : 0;
                    $total_semua_harga += ($harga_per_kg * $itemData['berat_timbang']);
                }
            }

            // ---> TAMBAHKAN KODE INI UNTUK MENGUBAH STATUS PENJEMPUTAN <---
            $penjemputan->status = 'selesai';
            $penjemputan->save();
            // ---------------------------------------------------------------

            DB::commit();

            return response()->json([
                'message' => 'Transaksi dan data penimbangan berhasil disimpan!',
                'total_keseluruhan' => $total_semua_harga
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan data penimbangan.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

public function listSampah()
    {
        // Mengambil semua data dari tabel sampah, beserta data dari tabel item_sampah
        // Pastikan relasi 'itemSampah' sudah ada di model Sampah seperti yang kita buat sebelumnya
        $sampah = \App\Models\Sampah::with('itemSampah')->get();

        return response()->json([
            'message' => 'Berhasil mengambil data sampah',
            'data' => $sampah
        ], 200);
    }

public function listTukang(Request $request)
    {
        // 1. Ambil data petugas yang sedang login berdasarkan token
        $petugas = $request->user();

        // (Opsional) Keamanan tambahan: pastikan petugas punya gudang_id
        if (!$petugas || !$petugas->gudang_id) {
            return response()->json([
                'message' => 'Petugas tidak memiliki akses gudang.',
                'data' => []
            ], 403);
        }

        // 2. Filter tukang: Ambil yang aktif DAN gudang_id nya sama dengan gudang_id petugas
        $tukang = \App\Models\Tukang::where('active', 1)
                    ->where('gudang_id', $petugas->gudang_id)
                    ->get();

        return response()->json([
            'message' => 'Berhasil mengambil data tukang',
            'data' => $tukang
        ], 200);
    }


}
