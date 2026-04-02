<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'kategori' => 'array'
        ]);

        $jenis = JenisSampah::create([
            'nama' => $request->nama,
            'stok_jenis' => $request->stok_jenis ?? 0
        ]);

        // simpan kategori jika ada
        if ($request->has('kategori')) {
            foreach ($request->kategori as $index => $k) {
                $foto = null;

                // ambil file berdasarkan index array
                if ($request->hasFile("kategori.$index.foto")) {
                    $path = $request->file("kategori.$index.foto")
                                    ->store('foto-kategori', 'public');

                    $foto = $path;
                }

                $jenis->kategoriSampah()->create([
                    'nama' => $k['nama'],
                    'harga_beli' => $k['harga_beli'] ?? 0,
                    'harga_jual' => $k['harga_jual'] ?? 0,
                    'diskon' => $k['diskon'] ?? 0,
                    'foto' => $foto,
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
        ]);

        // update kategori jika dikirim
        if ($request->has('kategori')) {
            foreach ($request->kategori as $index => $k) {
                $kategori = null;

                if (isset($k['kategori_id'])) {
                    $kategori = KategoriSampah::find($k['kategori_id']);
                }

                $data = [
                    'nama' => $k['nama'],
                    'harga_beli' => $k['harga_beli'],
                    'harga_jual' => $k['harga_jual'],
                    'diskon' => $k['diskon'],
                ];

                // kalau ada file baru
                if ($request->hasFile("kategori.$index.foto")) {

                    // hapus file lama kalau ada
                    if ($kategori && $kategori->foto && Storage::disk('public')->exists($kategori->foto)) {
                        Storage::disk('public')->delete($kategori->foto);
                    }

                    // simpan file baru
                    $path = $request->file("kategori.$index.foto")
                                    ->store('foto-kategori', 'public');

                    $data['foto'] = $path;
                }

                if ($kategori) {
                    // update
                    $kategori->update($data);
                } else {
                    // create baru
                    $jenis->kategoriSampah()->create($data);
                }
            }
        }

        return response()->json([
            'tes' => $request->all(),
            'data' => $jenis->load('kategoriSampah')
        ]);
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
