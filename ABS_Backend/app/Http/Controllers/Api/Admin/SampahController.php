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
            KategoriSampah::with('itemSampah.sampah')->latest()->paginate(10)
        );
    }

    public function indexItem()
    {
        return response()->json(
            ItemSampah::latest()->paginate(10)
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:50',
            'items' => 'nullable|array',
            'items.*.nama' => 'required|string|max:50',
            'items.*.harga_beli' => 'required|numeric',
            'items.*.harga_jual' => 'required|numeric',
            'items.*.diskon' => 'nullable|numeric',
            'items.*.foto' => 'nullable|image|max:2048'
        ]);

        return \DB::transaction(function () use ($request) {
            $kategori = KategoriSampah::create([
                'nama' => $request->nama,
            ]);

            if ($request->has('items')) {
                foreach ($request->items as $index => $itemData) {
                    $foto = null;
                    if ($request->hasFile("items.$index.foto")) {
                        $foto = $request->file("items.$index.foto")->store('foto-item-sampah', 'public');
                    }

                    $kategori->itemSampah()->create([
                        'nama' => $itemData['nama'],
                        'harga_beli' => $itemData['harga_beli'],
                        'harga_jual' => $itemData['harga_jual'],
                        'diskon' => ($itemData['diskon'] ?? 0) / 100,
                        'foto' => $foto,
                    ]);
                }
            }

            return response()->json($kategori->load('itemSampah'), 201);
        });
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

        $request->validate([
            'nama' => 'nullable|string|max:50',
            'active' => 'nullable|boolean',
            'items' => 'nullable|array',
        ]);

        return \DB::transaction(function () use ($request, $kategori) {
            $kategori->update([
                'nama' => $request->nama ?? $kategori->nama,
                'active' => $request->active ?? $kategori->active,
            ]);

            if ($request->has('items')) {
                $sentItemIds = [];
                foreach ($request->items as $index => $itemData) {
                    $item = null;
                    if (isset($itemData['item_id'])) {
                        $item = ItemSampah::find($itemData['item_id']);
                        $sentItemIds[] = $itemData['item_id'];
                    }

                    $data = [
                        'nama' => $itemData['nama'],
                        'harga_beli' => $itemData['harga_beli'],
                        'harga_jual' => $itemData['harga_jual'],
                        'diskon' => ($itemData['diskon'] ?? 0) / 100,
                        'active' => $itemData['active'] ?? true,
                    ];

                    if ($request->hasFile("items.$index.foto")) {
                        if ($item && $item->foto && Storage::disk('public')->exists($item->foto)) {
                            Storage::disk('public')->delete($item->foto);
                        }
                        $data['foto'] = $request->file("items.$index.foto")->store('foto-item-sampah', 'public');
                    }

                    if ($item) {
                        $item->update($data);
                    } else {
                        $newItem = $kategori->itemSampah()->create($data);
                        $sentItemIds[] = $newItem->item_id;
                    }
                }

                // Optional: Delete items that were not sent in the update (careful with this!)
                // $kategori->itemSampah()->whereNotIn('item_id', $sentItemIds)->delete();
            }

            return response()->json([
                'data' => $kategori->load('itemSampah')
            ]);
        });
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
