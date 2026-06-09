<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KategoriSampah;
use App\Models\ItemSampah;

class SampahController extends Controller
{
    public function index()
    {
        // Get all active categories with their active items
        $categories = KategoriSampah::where('active', 1)
            ->with(['itemSampah' => function ($query) {
                $query->where('active', 1)->select('item_id', 'kategori_id', 'nama', 'harga_beli', 'harga_jual', 'foto');
            }])
            ->get(['kategori_id', 'nama', 'active']);

        return response()->json($categories);
    }
}
