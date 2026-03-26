<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengepul;
use App\Models\Petugas;
use Illuminate\Http\Request;

class AksiAdminController extends Controller
{
    /**
     * Menonaktifkan petugas.
     */
    public function deactivatePetugas(Petugas $petuga)
    {
        $petuga->active = false;
        $petuga->save();

        return response()->json(['message' => 'Petugas berhasil dinonaktifkan'], 200);
    }

    /**
     * Mengaktifkan petugas.
     */
    public function activatePetugas(Petugas $petuga)
    {
        $petuga->active = true;
        $petuga->save();

        return response()->json(['message' => 'Petugas berhasil diaktifkan'], 200);
    }

    /**
     * Menerima registrasi pengepul.
     */
    public function terimaPengepul(Pengepul $pengepul)
    {
        $pengepul->status = 'aktif';
        $pengepul->save();

        return response()->json(['message' => 'Registrasi pengepul diterima'], 200);
    }

    /**
     * Menolak registrasi pengepul.
     */
    public function tolakPengepul(Pengepul $pengepul)
    {
        $pengepul->delete();

        return response()->json(['message' => 'Registrasi pengepul ditolak dan data telah dihapus'], 200);
    }
}
