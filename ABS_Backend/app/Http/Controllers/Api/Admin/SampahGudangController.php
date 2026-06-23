<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\KategoriSampah;
use App\Models\ItemSampah;
use App\Models\Sampah;

class SampahController extends Controller
{
    public function index()
    {
        return response()->json(
            KategoriSampah::with('itemSampah.sampah')->latest()->get()
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
        // Validasi Unique ditambahkan di sini
        $request->validate([
            'nama' => 'required|string|max:50|unique:kategori_sampahs,nama',
            'items' => 'nullable|array',
            'items.*.nama' => 'required|string|max:50',
            'items.*.harga_beli' => 'required|numeric',
            'items.*.harga_jual' => 'required|numeric',
            'items.*.diskon' => 'nullable|numeric',
            'items.*.foto' => 'nullable|image|max:2048'
        ]);

        return DB::transaction(function () use ($request) {
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

    // SHOW detail kategori dan itemnya
    public function show($id)
    {
        $kategori = KategoriSampah::with('itemSampah')->findOrFail($id);
        return response()->json($kategori);
    }

    // UPDATE jenis + kategori (Secara Bersamaan)
    public function update(Request $request, $id)
    {
        $kategori = KategoriSampah::findOrFail($id);

        // Validasi Unique dengan pengecualian ID saat ini ditambahkan di sini
        $request->validate([
            'nama' => 'nullable|string|max:50|unique:kategori_sampahs,nama,' . $id . ',kategori_id',
            'active' => 'nullable|boolean',
            'items' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($request, $kategori) {
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
            }

            return response()->json([
                'data' => $kategori->load('itemSampah')
            ]);
        });
    }

    // ========================================================
    // UPDATE KHUSUS 1 ITEM SAMPAH (Endpoint Baru)
    // ========================================================
    public function updateItem(Request $request, $id)
    {
        $item = ItemSampah::findOrFail($id);

        $request->validate([
            'nama' => 'nullable|string|max:50',
            'harga_beli' => 'nullable|numeric',
            'harga_jual' => 'nullable|numeric',
            'diskon' => 'nullable|numeric',
            'active' => 'nullable|boolean',
            'foto' => 'nullable|image|max:2048'
        ]);

        return DB::transaction(function () use ($request, $item) {
            $data = $request->only(['nama', 'harga_beli', 'harga_jual', 'active']);
            
            if ($request->has('diskon')) {
                $data['diskon'] = $request->diskon / 100;
            }

            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada
                if ($item->foto && Storage::disk('public')->exists($item->foto)) {
                    Storage::disk('public')->delete($item->foto);
                }
                // Simpan foto baru
                $data['foto'] = $request->file('foto')->store('foto-item-sampah', 'public');
            }

            $item->update($data);

            return response()->json([
                'message' => 'Item berhasil diperbarui',
                'data' => $item
            ]);
        });
    }

    // UPDATE STATUS MASSAL (Kategori, Item, dan Stok Gudang)
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

    // DELETE khusus 1 Item
    public function destroyItem($id)
    {
        $item = ItemSampah::findOrFail($id);
        
        // Opsional: Jika ingin fotonya juga terhapus dari storage saat data dihapus
        if ($item->foto && Storage::disk('public')->exists($item->foto)) {
            Storage::disk('public')->delete($item->foto);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted']);
    }
}