<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\KategoriSampah;
use App\Models\ItemSampah;
use App\Models\Sampah;

class SampahController extends Controller
{
    public function index()
    {
        return response()->json(
            KategoriSampah::with('itemSampah.sampah')->get()
        );
    }

    public function indexItem()
    {
        return response()->json(
            ItemSampah::all()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:50',
            'item' => 'array'
        ]);

        $kategori = KategoriSampah::create([
            'nama' => $request->nama,
        ]);

        // simpan item jika ada
        if ($request->has('item')) {
            foreach ($request->item as $index => $k) {
                $foto = null;

                // ambil file berdasarkan index array
                if ($request->hasFile("item.$index.foto")) {
                    $path = $request->file("item.$index.foto")
                                    ->store('foto-item-sampah', 'public');

                    $foto = $path;
                }

                $kategori->itemSampah()->create([
                    'nama' => $k['nama'],
                    'harga_beli' => $k['harga_beli'] ?? 0,
                    'harga_jual' => $k['harga_jual'] ?? 0,
                    'diskon' => $k['diskon'] ?? 0,
                    'foto' => $foto,
                ]);
            }
        }

        return response()->json($kategori->load('itemSampah'), 201);
    }

    // SHOW detail
    public function show($id)
    {
        $kategori = KategoriSampah::with('itemSampah')->findOrFail($id);
        return response()->json($kategori);
    }

    // UPDATE jenis + kategori
    public function update(Request $request, $id)
    {
        $kategori = KategoriSampah::findOrFail($id);

        $kategori->update([
            'nama' => $request->nama ?? $kategori->nama,
            'active' => $request->active ?? $kategori->active,
        ]);

        // update kategori jika dikirim
        if ($request->has('item')) {
            foreach ($request->item as $index => $k) {
                $item = null;

                if (isset($k['item_id'])) {
                    $item = itemSampah::find($k['item_id']);
                }

                $data = [
                    'nama' => $k['nama'],
                    'harga_beli' => $k['harga_beli'],
                    'harga_jual' => $k['harga_jual'],
                    'diskon' => $k['diskon'],
                    'active' => $k['active'],
                ];

                // kalau ada file baru
                if ($request->hasFile("item.$index.foto")) {

                    // hapus file lama kalau ada
                    if ($item && $item->foto && Storage::disk('public')->exists($item->foto)) {
                        Storage::disk('public')->delete($item->foto);
                    }

                    // simpan file baru
                    $path = $request->file("item.$index.foto")
                                    ->store('foto-item-sampah', 'public');

                    $data['foto'] = $path;
                }

                if ($item) {
                    // update
                    $item->update($data);
                } else {
                    // create baru
                    $kategori->itemSampah()->create($data);
                }
            }
        }

        return response()->json([
            'data' => $kategori->load('itemSampah')
        ]);
    }

    public function updateStatus(Request $request, $id) {
        $request->validate([
            'active' => 'required'
        ]);

        $kategori = KategoriSampah::with('itemSampah')->findOrFail($id);

        $kategori->update([
            'active' => $request->active,
        ]);

        foreach ($kategori->itemSampah as $k) {
            $item = itemSampah::with('sampah')->find($k['item_id']);

            if ($item){
                $item->update([
                    'active' => $request->active,
                ]);

                foreach ($item->sampah as $s) {
                    $sampah = Sampah::with('gudang')
                    ->whereHas('gudang', function ($q) {
                        $q->where('active', 1);
                    })
                    ->find($s['sampah_id']);

                    if ($sampah) {
                        $sampah->update([
                            'active' => $request->active,
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'data' => $kategori->load('itemSampah.sampah')
        ]);
    }

    // DELETE jenis (kategori ikut kehapus karena cascade)
    public function destroy($id)
    {
        $kategori = KategoriSampah::findOrFail($id);
        $kategori->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function destroyItem($id)
    {
        $item = ItemSampah::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Kategori deleted']);
    }
}
