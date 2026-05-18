<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengepul;
use App\Models\Petugas;
use App\Models\Nasabah;
use App\Models\Manager;
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

    /**
     * Menonaktifkan manager.
     */
    public function deactivateManager(Manager $manager)
    {
        $manager->status = 'nonaktif';
        $manager->save();

        return response()->json(['message' => 'Manager berhasil dinonaktifkan'], 200);
    }

    /**
     * Mengaktifkan manager.
     */
    public function activateManager(Manager $manager)
    {
        $manager->status = 'aktif';
        $manager->save();

        return response()->json(['message' => 'Manager berhasil diaktifkan'], 200);
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
        try {
            Mail::to($pengepul->email)->send(new PengepulDiterima($pengepul));
            $emailStatus = 'dan notifikasi email terkirim';
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mengirim email terima pengepul: " . $e->getMessage());
            $emailStatus = 'namun gagal mengirim notifikasi email';
        }

        return response()->json(['message' => 'Registrasi pengepul diterima ' . $emailStatus], 200);
    }

    /**
     * Menolak registrasi pengepul.
     */
    public function tolakPengepul(Request $request, Pengepul $pengepul)
    {
        // 1. Validasi request untuk memastikan alasan diisi
        $request->validate([
            'ket_status' => 'required|string|max:255',
        ]);

        // 2. Simpan alasan dan ubah status
        $pengepul->status = 'nonaktif';
        $pengepul->ket_status = $request->ket_status;
        $pengepul->save();

        // 3. Kirim Email Ditolak dengan menyertakan alasan
        try {
            Mail::to($pengepul->email)->send(new PengepulDitolak($pengepul->nama, $pengepul->ket_status));
            $emailStatus = 'dan notifikasi email terkirim';
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mengirim email tolak pengepul: " . $e->getMessage());
            $emailStatus = 'namun gagal mengirim notifikasi email';
        }

        return response()->json(['message' => 'Registrasi pengepul ditolak, status diubah menjadi nonaktif, ' . $emailStatus], 200);
    }
}
