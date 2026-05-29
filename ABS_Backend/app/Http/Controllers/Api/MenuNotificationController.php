<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MenuNotificationController extends Controller
{
    public function getUpdates(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([]);
        }

        $role = null;
        if ($user instanceof \App\Models\Admin) $role = 'admin';
        elseif ($user instanceof \App\Models\Petugas) $role = 'petugas';
        elseif ($user instanceof \App\Models\Manager) $role = 'manager';
        elseif ($user instanceof \App\Models\Pengepul) $role = 'pengepul';
        elseif ($user instanceof \App\Models\Nasabah) $role = 'nasabah';

        $updates = [];

        if ($role === 'admin') {
            $updates['/dashboard-admin/kelola-users'] = DB::table('nasabahs')->max('updated_at');
            $updates['/dashboard-admin/verifikasi-pengepul'] = DB::table('pengepuls')->max('updated_at');
            $updates['/dashboard-admin/kelola-sampah'] = max(
                DB::table('item_sampahs')->max('updated_at') ?? '2000-01-01',
                DB::table('sampahs')->max('updated_at') ?? '2000-01-01'
            );
            $updates['/dashboard-admin/kelola-gudang'] = DB::table('gudangs')->max('updated_at');
            $updates['/dashboard-admin/deadline-pembayaran'] = DB::table('transaksi_pengepuls')->max('updated_at');
        } elseif ($role === 'petugas') {
            $gudangId = $user->gudang_id;
            $updates['/dashboard-petugas/listpenjemputan'] = DB::table('penjemputans')->where('gudang_id', $gudangId)->max('updated_at');
            $updates['/dashboard-petugas/penimbangan'] = DB::table('transaksi_nasabahs')->where('tipe_transaksi', 'antar_sendiri')->max('updated_at');
            $updates['/dashboard-petugas/listpenarikan'] = DB::table('penarikans')->max('updated_at');
            
            // For Pesanan Pengepul, we check transaksi_pengepuls that has details related to this gudang
            $updates['/dashboard-petugas/pesanan-pengepul'] = DB::table('transaksi_pengepuls')
                ->join('detail_transaksis', 'transaksi_pengepuls.transaksi_id', '=', 'detail_transaksis.transaksi_id')
                ->join('sampahs', 'detail_transaksis.sampah_id', '=', 'sampahs.sampah_id')
                ->where('sampahs.gudang_id', $gudangId)
                ->max('transaksi_pengepuls.updated_at');
            
            $updates['/dashboard-petugas/riwayat'] = max(
                DB::table('penjemputans')->where('gudang_id', $gudangId)->whereIn('status', ['selesai', 'tolak', 'batal'])->max('updated_at') ?? '2000-01-01',
                DB::table('transaksi_pengepuls')
                    ->join('detail_transaksis', 'transaksi_pengepuls.transaksi_id', '=', 'detail_transaksis.transaksi_id')
                    ->join('sampahs', 'detail_transaksis.sampah_id', '=', 'sampahs.sampah_id')
                    ->where('sampahs.gudang_id', $gudangId)
                    ->whereIn('transaksi_pengepuls.status', ['selesai', 'tolak', 'batal'])
                    ->max('transaksi_pengepuls.updated_at') ?? '2000-01-01'
            );
            $updates['/dashboard-petugas/kelola-berita'] = DB::table('beritas')->max('updated_at');
        } elseif ($role === 'pengepul') {
            $pengepulId = $user->pengepul_id;
            $updates['/dashboard-pengepul/beli-sampah'] = DB::table('sampahs')->max('updated_at');
            $updates['/dashboard-pengepul/pesanan-saya'] = DB::table('transaksi_pengepuls')->where('pengepul_id', $pengepulId)->max('updated_at');
        } elseif ($role === 'nasabah') {
            $nasabahId = $user->nasabah_id;
            $updates['/dashboard-nasabah/katalog'] = DB::table('item_sampahs')->max('updated_at');
            $updates['/dashboard-nasabah/request-penjemputan'] = DB::table('penjemputans')->where('nasabah_id', $nasabahId)->max('updated_at');
            $updates['/dashboard-nasabah/request-penarikan'] = DB::table('penarikans')->where('nasabah_id', $nasabahId)->max('updated_at');
            $updates['/dashboard-nasabah/penarikan-saya'] = DB::table('penarikans')->where('nasabah_id', $nasabahId)->max('updated_at');
            $updates['/dashboard-nasabah/sampah-saya'] = DB::table('transaksi_nasabahs')->where('nasabah_id', $nasabahId)->max('updated_at');
        } elseif ($role === 'manager') {
            $updates['/dashboard-manager/audit-data'] = max(
                DB::table('transaksi_nasabahs')->max('updated_at') ?? '2000-01-01',
                DB::table('transaksi_pengepuls')->max('updated_at') ?? '2000-01-01'
            );
        }

        // Clean up formatting
        $formattedUpdates = [];
        foreach ($updates as $path => $dateStr) {
            if ($dateStr && $dateStr !== '2000-01-01') {
                $formattedUpdates[$path] = Carbon::parse($dateStr)->timestamp;
            }
        }

        return response()->json($formattedUpdates);
    }
}
