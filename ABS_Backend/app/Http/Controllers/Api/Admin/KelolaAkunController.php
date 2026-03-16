<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Petugas;

class KelolaAkunController extends Controller
{
    /**
     * Menampilkan daftar semua petugas.
     */
    public function indexPetugas()
    {
        $petugas = Petugas::latest()->get();
        return response()->json(['data' => $petugas], 200);
    }

    /**
     * Menampilkan detail petugas.
     */
    public function showPetugas(Petugas $petuga)
    {
        return response()->json(['data' => $petuga], 200);
    }
}
