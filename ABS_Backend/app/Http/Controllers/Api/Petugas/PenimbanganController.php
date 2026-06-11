<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Penimbangan;
use Illuminate\Http\Request;
use App\Models\Sampah;
use Illuminate\Support\Facades\Mail;
use App\Mail\StatusPenjemputanMail;

class PenimbanganController extends Controller
{
public function penimbangan(Request $request)
    {
        // 1. Validasi Input (tipe_transaksi dihapus dari validasi)
        $request->validate([
            'penjemputan_id'        => 'required',
            'tukang_id'             => 'required',
            'items'                 => 'required|array|min:1',
            'items.*.sampah_id'     => 'required',
            'items.*.berat_timbang' => 'required|numeric|min:0.1',
            'items.*.foto'          => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $penjemputan = \App\Models\Penjemputan::findOrFail($request->penjemputan_id);

            // Buat Data Transaksi Baru
            $transaksi = new \App\Models\TransaksiNasabah();

            // ---> OTOMATIS DIISI 'dijemput' <---
            $transaksi->tipe_transaksi = 'dijemput';

            $transaksi->tanggal        = now();
            $transaksi->status         = 'selesai';
            $transaksi->petugas_id     = $request->user()->petugas_id;

            // Simpan saldo awal nasabah
            $nasabah = \App\Models\Nasabah::findOrFail($penjemputan->nasabah_id);
            $transaksi->saldo_sebelum = $nasabah->saldo;

            $transaksi->save();

            $total_semua_harga = 0;

            foreach ($request->items as $index => $itemData) {
                // Proses upload foto per baris
                $fotoPath = null;
                if ($request->hasFile("items.$index.foto")) {
                    $fotoPath = $request->file("items.$index.foto")->store('penimbangan_foto', 'public');
                }

                $penimbangan = new Penimbangan();
                $penimbangan->sampah_id      = $itemData['sampah_id'];
                $penimbangan->berat_timbang  = $itemData['berat_timbang'];
                $penimbangan->nasabah_id     = $penjemputan->nasabah_id;
                $penimbangan->transaksi_id   = $transaksi->transaksi_id;
                $penimbangan->foto           = $fotoPath; // gudang_id
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

            // Ubah Status Penjemputan
            $penjemputan->status = 'selesai';
            $penjemputan->save();

            // Update Saldo Nasabah
            $nasabah->saldo += $total_semua_harga;
            $nasabah->save();

            // Update data transaksi dengan total harga dan saldo sesudah
            $transaksi->total_harga = $total_semua_harga;
            $transaksi->saldo_sesudah = $nasabah->saldo;
            $transaksi->save();

            if ($penjemputan->nasabah && $penjemputan->nasabah->email) {
                Mail::to($penjemputan->nasabah->email)->send(new StatusPenjemputanMail($penjemputan, 'selesai'));
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaksi dan data penimbangan berhasil disimpan!',
                'total_keseluruhan' => $total_semua_harga,
                'transaksi_id' => $transaksi->transaksi_id,
                'transaksi' => $transaksi
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan data penimbangan.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Tambahkan fungsi ini di bawah fungsi listSampah()
    public function listKategori()
    {
        // Mengambil kategori yang aktif
        $kategori = \App\Models\KategoriSampah::where('active', 1)->get();

        return response()->json([
            'message' => 'Berhasil mengambil data kategori',
            'data' => $kategori
        ], 200);
    }

public function listSampah()
    {
        $petugas = Auth::user();
        $gudangId = $petugas->gudang_id;

        // Automatically ensure all active item_sampahs exist in the sampahs table for all gudangs (self-healing)
        $existing = Sampah::select('gudang_id', 'item_id')->get()->groupBy('gudang_id');
        $activeItems = \App\Models\ItemSampah::where('active', 1)->get();
        $gudangs = \App\Models\Gudang::all();
        
        $toInsert = [];
        foreach ($gudangs as $gudang) {
            $existingItems = isset($existing[$gudang->gudang_id]) 
                ? $existing[$gudang->gudang_id]->pluck('item_id')->toArray() 
                : [];
            foreach ($activeItems as $item) {
                if (!in_array($item->item_id, $existingItems)) {
                    $toInsert[] = [
                        'gudang_id' => $gudang->gudang_id,
                        'item_id' => $item->item_id,
                        'stok' => 0,
                        'active' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        if (!empty($toInsert)) {
            Sampah::insert($toInsert);
        }

        // Filter: only get waste types belonging to the officer's warehouse
        $sampah = Sampah::with('itemSampah')
                    ->where('gudang_id', $gudangId)
                    ->get();

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

    // --- 1. FUNGSI UNTUK MENGAMBIL DAFTAR NASABAH ---
    public function listNasabah()
    {
        // Mengambil semua nasabah, bisa disesuaikan jika ada filter status 'aktif'
        $nasabah = \App\Models\Nasabah::all();

        return response()->json([
            'message' => 'Berhasil mengambil data nasabah',
            'data' => $nasabah
        ], 200);
    }

    // --- 2. FUNGSI UNTUK MENYIMPAN PENIMBANGAN ANTAR SENDIRI ---
    public function penimbanganAntarSendiri(Request $request)
    {
        // Validasi: penjemputan_id tidak ada, tapi nasabah_id wajib
        $request->validate([
            'nasabah_id'            => 'required',
            'tukang_id'             => 'required',
            'items'                 => 'required|array|min:1',
            'items.*.sampah_id'     => 'required',
            'items.*.berat_timbang' => 'required|numeric|min:0.1',
            'items.*.foto'          => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        DB::beginTransaction();

        try {
            // Buat Data Transaksi Baru (Otomatis tipe 'antar_sendiri')
            $transaksi = new \App\Models\TransaksiNasabah();
            $transaksi->tipe_transaksi = 'antar_sendiri';
            $transaksi->tanggal        = now();
            $transaksi->status         = 'selesai';
            $transaksi->petugas_id     = $request->user()->petugas_id;

            // Simpan saldo awal nasabah
            $nasabah = \App\Models\Nasabah::findOrFail($request->nasabah_id);
            $transaksi->saldo_sebelum = $nasabah->saldo;

            $transaksi->save();

            $total_semua_harga = 0;

            foreach ($request->items as $index => $itemData) {
                // Proses upload foto per baris
                $fotoPath = null;
                if ($request->hasFile("items.$index.foto")) {
                    $fotoPath = $request->file("items.$index.foto")->store('penimbangan_foto', 'public');
                }

                $penimbangan = new Penimbangan();
                $penimbangan->sampah_id      = $itemData['sampah_id'];
                $penimbangan->berat_timbang  = $itemData['berat_timbang'];

                // Ambil ID nasabah langsung dari form (karena tidak ada penjemputan)
                $penimbangan->nasabah_id     = $request->nasabah_id;

                $penimbangan->transaksi_id   = $transaksi->transaksi_id;
                $penimbangan->foto           = $fotoPath;
                $penimbangan->tukang_id      = $request->tukang_id;

                // BIARKAN KOSONG KARENA ANTAR SENDIRI
                $penimbangan->penjemputan_id = null;

                $penimbangan->save();

                // Update Stok Sampah
                $sampah = \App\Models\Sampah::where('sampah_id', $itemData['sampah_id'])->first();
                if ($sampah) {
                    $sampah->stok += $itemData['berat_timbang'];
                    $sampah->save();

                    $sampah->load('itemSampah');
                    $harga_per_kg = $sampah->itemSampah ? $sampah->itemSampah->harga_beli : 0;
                    $total_semua_harga += ($harga_per_kg * $itemData['berat_timbang']);
                }
            }

            // Update Saldo Nasabah
            if ($nasabah) {
                $nasabah->saldo += $total_semua_harga;
                $nasabah->save();

                // Update data transaksi dengan total harga dan saldo sesudah
                $transaksi->total_harga = $total_semua_harga;
                $transaksi->saldo_sesudah = $nasabah->saldo;
                $transaksi->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaksi Antar Sendiri berhasil disimpan!',
                'total_keseluruhan' => $total_semua_harga,
                'transaksi_id' => $transaksi->transaksi_id,
                'transaksi' => $transaksi
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan data penimbangan.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }




}
