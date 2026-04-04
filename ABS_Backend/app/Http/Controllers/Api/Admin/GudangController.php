<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gudang;

class GudangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gudang = Gudang::all();
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
        $gudang = Gudang::findOrFail($id);

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
