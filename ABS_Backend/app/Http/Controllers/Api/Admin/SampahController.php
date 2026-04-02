<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisSampah;
use App\Models\KategoriSampah;

class SampahController extends Controller
{
    // GET semua data + relasi
    public function index()
    {
        return response()->json(
            JenisSampah::with('kategoriSampah')->get()
        );
    }

    // STORE jenis + kategori (opsional)
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:50',
            'stok_jenis' => 'integer',
            'kategori' => 'array'
        ]);

        $jenis = JenisSampah::create([
            'nama' => $request->nama,
            'stok_jenis' => $request->stok_jenis ?? 0
        ]);

        // simpan kategori jika ada
        if ($request->has('kategori')) {
            foreach ($request->kategori as $k) {
                $jenis->kategoriSampah()->create([
                    'nama' => $k['nama'],
                    'harga_beli' => $k['harga_beli'] ?? 0,
                    'harga_jual' => $k['harga_jual'] ?? 0,
                    'diskon' => $k['diskon'] ?? 0,
                    'stok' => $k['stok'] ?? 0,
                ]);
            }
        }

        return response()->json($jenis->load('kategoriSampah'), 201);
    }

    // SHOW detail
    public function show($id)
    {
        $jenis = JenisSampah::with('kategoriSampah')->findOrFail($id);
        return response()->json($jenis);
    }

    // UPDATE jenis + kategori
    public function update(Request $request, $id)
    {
        $jenis = JenisSampah::findOrFail($id);

        $jenis->update([
            'nama' => $request->nama ?? $jenis->nama,
            'stok_jenis' => $request->stok_jenis ?? $jenis->stok_jenis,
        ]);

        // update kategori jika dikirim
        if ($request->has('kategori_sampah')) {
            foreach ($request->kategori_sampah as $k) {

                // kalau ada kategori_id → update
                if (isset($k['kategori_id'])) {
                    $kategori = KategoriSampah::find($k['kategori_id']);
                    if ($kategori) {
                        $kategori->update([
                            'nama' => $k['nama'],
                            'harga_beli' => $k['harga_beli'],
                            'harga_jual' => $k['harga_jual'],
                            'diskon' => $k['diskon'],
                            'stok' => $k['stok'],
                        ]);
                    }
                }
                // kalau tidak ada → create baru
                else {
                    $jenis->kategoriSampah()->create($k);
                }
            }
        }

        return response()->json($jenis->load('kategoriSampah'));
    }

    // DELETE jenis (kategori ikut kehapus karena cascade)
    public function destroy($id)
    {
        $jenis = JenisSampah::findOrFail($id);
        $jenis->delete();

        return response()->json(['message' => 'Deleted']);
    }

    // DELETE kategori saja
    public function destroyKategori($id)
    {
        $kategori = KategoriSampah::findOrFail($id);
        $kategori->delete();

        return response()->json(['message' => 'Kategori deleted']);
    }
}
