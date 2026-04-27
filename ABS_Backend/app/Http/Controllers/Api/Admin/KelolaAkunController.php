<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use App\Models\Pengepul;
use App\Models\Nasabah;
use App\Models\Tukang;
use App\Models\Admin;
use App\Models\Manager;

class KelolaAkunController extends Controller
{
    /**
     * Menampilkan daftar semua petugas.
     */
    public function indexPetugas()
    {
        $petugas = Petugas::latest()->paginate(10);
        return response()->json($petugas, 200);
    }

    /**
     * Menampilkan detail petugas.
     */
    public function showPetugas(Petugas $petuga)
    {
        return response()->json(['data' => $petuga], 200);
    }

    /**
     * Menampilkan daftar semua pengepul.
     */
    public function indexPengepul()
    {
        $pengepul = Pengepul::latest()->paginate(10);
        return response()->json($pengepul, 200);
    }

    /**
     * Menampilkan daftar pengepul yang sedang menunggu konfirmasi (pending).
     */
    public function indexPendingPengepul()
    {
        $pengepul = Pengepul::where('status', 'pending')->latest()->paginate(10);
        return response()->json($pengepul, 200);
    }

    /**
     * Menampilkan detail pengepul.
     */
    public function showPengepul(Pengepul $pengepul)
    {
        return response()->json(['data' => $pengepul], 200);
    }

    /**
     * Menampilkan daftar semua nasabah.
     */
    public function indexNasabah()
    {
        $nasabah = Nasabah::latest()->paginate(10);
        return response()->json($nasabah, 200);
    }

    /**
     * Menampilkan daftar semua tukang.
     */
    public function indexTukang()
    {
        $tukang = Tukang::latest()->paginate(10);
        return response()->json($tukang, 200);
    }

    /**
     * Menampilkan daftar semua admin.
     */
    public function indexAdmin()
    {
        $admin = Admin::latest()->paginate(10);
        return response()->json($admin, 200);
    }

    /**
     * Menampilkan daftar semua manager.
     */
    public function indexManager()
    {
        $manager = Manager::latest()->paginate(10);
        return response()->json($manager, 200);
    }

    /**
     * Menampilkan detail manager.
     */
    public function showManager(Manager $manager)
    {
        return response()->json(['data' => $manager], 200);
    }
}
