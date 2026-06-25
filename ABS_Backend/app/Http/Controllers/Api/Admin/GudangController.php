<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gudang;
use App\Models\Sampah;
use App\Models\Tukang;

class GudangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Automatically ensure all active item_sampahs exist in the sampahs table for all gudangs (self-healing)
        $existing = Sampah::select('gudang_id', 'item_id')->get()->groupBy('gudang_id');
        $activeItems = \App\Models\ItemSampah::where('active', 1)->get();
        $gudangs = Gudang::all();
        
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

        $gudang = Gudang::with(['sampah.itemSampah', 'tukang', 'petugas'])->get();
        return response()->json(['data' => $gudang], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'alamat' => 'required|string|unique:gudangs,alamat',
            'kapasitas' => 'required|integer',
        ]);

        $gudang = Gudang::create($validated);

        return response()->json($gudang, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $gudang = Gudang::with(['sampah.itemSampah.kategoriSampah', 'tukang'])->findOrFail($id);

        return response()->json($gudang);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $gudang = Gudang::findOrFail($id);

        $validated = $request->validate([
            'alamat' => 'sometimes|required|string|unique:gudangs,alamat,' . $gudang->gudang_id,
            'kapasitas' => 'sometimes|required|integer',
        ]);

        $gudang->update($validated);

        return response()->json($gudang);
    }

    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'active' => 'required',
        ]);

        $gudang = Gudang::with(['tukang', 'sampah'])->findOrFail($id);

        $gudang->update([
            'active' => $request->active,
        ]);

        foreach ($gudang->tukang as $k) {
            $item = Tukang::find($k['tukang_id']);

            if ($item) {
                $item->update([
                    'active' => $request->active,
                ]);
            }
        }

        foreach ($gudang->sampah as $k) {
            $item = Sampah::find($k['sampah_id']);

            if ($item) {
                $item->update([
                    'active' => $request->active,
                ]);
            }
        }

        return response()->json([
            'data' => $gudang->load(['sampah', 'tukang'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $gudang = Gudang::findOrFail($id);
        $gudang->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }
}
