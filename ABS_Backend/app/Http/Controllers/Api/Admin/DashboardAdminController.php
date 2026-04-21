<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use App\Models\Nasabah;
use App\Models\Pengepul;
use App\Models\Gudang;
use App\Models\ItemSampah;
use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $totalPetugas = Petugas::count();
        $nasabahAktif = Nasabah::count(); 
        $pengepulVerifikasi = 28; 
        $totalGudang = 8; 

        // Recent activities (Mocking logic based on latest records)
        $activities = [];

        // Latest Petugas
        $latestPetugas = Petugas::latest()->first();
        if ($latestPetugas) {
            $activities[] = [
                'id' => 'p-' . $latestPetugas->petugas_id,
                'type' => 'petugas',
                'title' => 'Akun petugas baru ditambahkan',
                'description' => $latestPetugas->nama . ' - ' . ($latestPetugas->gudang->nama ?? 'Belum ada gudang'),
                'time' => $latestPetugas->created_at->format('H:i'),
                'date' => $latestPetugas->created_at->toDateString(),
            ];
        }

        // Latest Nasabah
        $latestNasabah = Nasabah::where('status', 'aktif')->latest()->first();
        if ($latestNasabah) {
            $activities[] = [
                'id' => 'n-' . $latestNasabah->nasabah_id,
                'type' => 'nasabah',
                'title' => 'Nasabah terverifikasi',
                'description' => $latestNasabah->nama,
                'time' => $latestNasabah->created_at->format('H:i'),
                'date' => $latestNasabah->created_at->toDateString(),
            ];
        }

        // Latest Gudang
        $latestGudang = Gudang::latest()->first();
        if ($latestGudang) {
            $activities[] = [
                'id' => 'g-' . $latestGudang->gudang_id,
                'type' => 'gudang',
                'title' => 'Gudang baru dibuat',
                'description' => $latestGudang->nama,
                'time' => $latestGudang->created_at->format('H:i'),
                'date' => $latestGudang->created_at->toDateString(),
            ];
        }

        // Latest Item Sampah
        $latestItem = ItemSampah::latest('updated_at')->first();
        if ($latestItem) {
            $activities[] = [
                'id' => 's-' . $latestItem->item_id,
                'type' => 'sampah',
                'title' => 'Harga sampah diperbarui',
                'description' => $latestItem->nama . ' - Rp ' . number_format($latestItem->harga_beli, 0, ',', '.') . '/kg',
                'time' => $latestItem->updated_at->format('H:i'),
                'date' => $latestItem->updated_at->toDateString(),
            ];
        }

        return response()->json([
            'stats' => [
                'total_petugas' => $totalPetugas,
                'nasabah_aktif' => $nasabahAktif,
                'pengepul_terverifikasi' => $pengepulVerifikasi,
                'total_gudang' => $totalGudang,
            ],
            'activities' => $activities
        ]);
    }
}
