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

    /**
     * Menampilkan daftar semua pengepul.
     */
    public function indexPengepul()
    {
        $pengepul = Pengepul::latest()->get();
        return response()->json(['data' => $pengepul], 200);
    }

    /**
     * Menampilkan daftar pengepul yang sedang menunggu konfirmasi (pending).
     */
    public function indexPendingPengepul()
    {
        $pengepul = Pengepul::where('status', 'pending')->latest()->get();
        return response()->json(['data' => $pengepul], 200);
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
        $nasabah = Nasabah::latest()->get();
        return response()->json(['data' => $nasabah], 200);
    }

    /**
     * Menampilkan daftar semua tukang.
     */
    public function indexTukang()
    {
        $tukang = Tukang::latest()->get();
        return response()->json(['data' => $tukang], 200);
    }

    /**
     * Menampilkan daftar semua admin.
     */
    public function indexAdmin()
    {
        $admin = Admin::latest()->get();
        return response()->json(['data' => $admin], 200);
    }

    /**
     * Menampilkan daftar semua manager.
     */
    public function indexManager()
    {
        $manager = Manager::latest()->get();
        return response()->json(['data' => $manager], 200);
    }

    /**
     * Menampilkan detail manager.
     */
    public function showManager(Manager $manager)
    {
        return response()->json(['data' => $manager], 200);
    }
}
