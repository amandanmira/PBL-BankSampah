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
        $gudang = Gudang::with(['sampah.itemSampah', 'tukang'])->get();
        return response()->json(['data' => $gudang], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'alamat' => 'required|string',
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
            'alamat' => 'sometimes|required|string',
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
