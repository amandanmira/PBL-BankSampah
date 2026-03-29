<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengepul;
use App\Models\Petugas;
use App\Models\Nasabah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; // Tambahkan ini
use App\Mail\PengepulDiterima;       // Tambahkan ini
use App\Mail\PengepulDitolak;        // Tambahkan ini

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

    public function deactivatePengepul(Pengepul $pengepul)
    {
        $pengepul->status = 'nonaktif';
        $pengepul->save();

        return response()->json(['message' => 'Pegepul berhasil dinonaktifkan'], 200);
    }

    public function activatePengepul(Pengepul $pengepul)
    {
        $pengepul->status = 'aktif';
        $pengepul->save();

        return response()->json(['message' => 'Pegepul berhasil diaktifkan'], 200);
    }

    public function deactivateNasabah(Nasabah $nasabah)
    {
        $nasabah->status = 'nonaktif';
        $nasabah->save();

        return response()->json(['message' => 'Nasabah berhasil dinonaktifkan'], 200);
    }

    public function activateNasabah(Nasabah $nasabah)
    {
        $nasabah->status = 'aktif';
        $nasabah->save();

        return response()->json(['message' => 'Nasabah berhasil diaktifkan'], 200);
    }

    public function terimaPengepul(Pengepul $pengepul)
    {
        $pengepul->status = 'aktif';
        $pengepul->save();

        // Kirim Email Diterima
        Mail::to($pengepul->email)->send(new PengepulDiterima($pengepul));

        return response()->json(['message' => 'Registrasi pengepul diterima dan notifikasi email terkirim'], 200);
    }

    /**
     * Menolak registrasi pengepul.
     */
    public function tolakPengepul(Pengepul $pengepul)
    {
        // 1. Simpan data yang dibutuhkan untuk email SEBELUM dihapus
        $emailPengepul = $pengepul->email;
        $namaPengepul = $pengepul->nama;

        // 2. Hapus data pengepul
        $pengepul->delete();

        // 3. Kirim Email Ditolak menggunakan data yang sudah disimpan
        Mail::to($emailPengepul)->send(new PengepulDitolak($namaPengepul));

        return response()->json(['message' => 'Registrasi pengepul ditolak, data dihapus, dan notifikasi email terkirim'], 200);
    }
}
