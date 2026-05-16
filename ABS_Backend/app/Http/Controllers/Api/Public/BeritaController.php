<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;

class BeritaController extends Controller
{
    /**
     * Display a listing of the news.
     */
    public function index(Request $request)
    {
        $limit = $request->query('limit');
        $query = Berita::with('petugas')->latest();

        if ($limit) {
            $berita = $query->take($limit)->get();
        } else {
            $berita = $query->paginate(12);
        }

        return response()->json($berita);
    }

    /**
     * Display the specified news.
     */
    public function show($id)
    {
        $berita = Berita::with('petugas')->find($id);

        if (!$berita) {
            return response()->json(['message' => 'Berita tidak ditemukan'], 404);
        }

        return response()->json($berita);
    }
}
