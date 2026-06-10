<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Models
use App\Models\Gudang;
use App\Models\KategoriSampah;
use App\Models\ItemSampah;
use App\Models\Sampah;
use App\Models\Nasabah;
use App\Models\Pengepul;
use App\Models\Petugas;
use App\Models\Tukang;
use App\Models\Penjemputan;
use App\Models\TransaksiNasabah;
use App\Models\Penimbangan;
use App\Models\TransaksiPengepul;
use App\Models\DetailTransaksi;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Warehouses Exist
        $gudangs = [];
        $gudangNames = ['Gudang Pusat', 'Gudang Timur', 'Gudang Barat', 'Gudang Selatan', 'Gudang Surakarta'];
        foreach ($gudangNames as $name) {
            $gudangs[$name] = Gudang::firstOrCreate(
                ['alamat' => $name],
                ['kapasitas' => 5000]
            );
        }

        // 2. Ensure Categories Exist
        $categories = [
            'Organik' => KategoriSampah::firstOrCreate(['nama' => 'Organik']),
            'Plastik' => KategoriSampah::firstOrCreate(['nama' => 'Plastik']),
            'Kertas' => KategoriSampah::firstOrCreate(['nama' => 'Kertas']),
            'Logam' => KategoriSampah::firstOrCreate(['nama' => 'Logam']),
        ];

        // 3. Ensure ItemSampah Exist (Exactly matching screenshot names)
        $items = [
            'Organik' => ItemSampah::firstOrCreate(
                ['nama' => 'Organik'],
                ['harga_beli' => 1500, 'harga_jual' => 2800, 'kategori_id' => $categories['Organik']->kategori_id, 'active' => 1]
            ),
            'Plastik PET' => ItemSampah::firstOrCreate(
                ['nama' => 'Plastik PET'],
                ['harga_beli' => 2500, 'harga_jual' => 4500, 'kategori_id' => $categories['Plastik']->kategori_id, 'active' => 1]
            ),
            'Kertas' => ItemSampah::firstOrCreate(
                ['nama' => 'Kertas'],
                ['harga_beli' => 2000, 'harga_jual' => 3800, 'kategori_id' => $categories['Kertas']->kategori_id, 'active' => 1]
            ),
            'Logam' => ItemSampah::firstOrCreate(
                ['nama' => 'Logam'],
                ['harga_beli' => 5000, 'harga_jual' => 9500, 'kategori_id' => $categories['Logam']->kategori_id, 'active' => 1]
            ),
        ];

        // 4. Link Items to all Warehouses (Sampah model)
        $sampahMap = []; // [$gudangAlamat][$itemName] = $sampahModel
        foreach ($gudangs as $gudangName => $g) {
            foreach ($items as $itemName => $it) {
                $sampahMap[$gudangName][$itemName] = Sampah::firstOrCreate(
                    ['item_id' => $it->item_id, 'gudang_id' => $g->gudang_id],
                    ['stok' => 500, 'active' => 1]
                );
            }
        }

        // 5. Ensure diverse Nasabahs Exist
        $nasabahs = [];
        $nasabahNames = [
            'Siti Aminah', 'Bambang Sutejo', 'Rina Kusuma', 'Joko Widodo', 
            'Maria Ulfa', 'Agus Salim', 'Lilis Karlina', 'Budi Santoso', 
            'Ahmad Fauzi', 'Dewi Lestari'
        ];
        foreach ($nasabahNames as $index => $name) {
            $username = strtolower(str_replace(' ', '', $name));
            $nasabahs[] = Nasabah::firstOrCreate(
                ['username' => $username],
                [
                    'nama' => $name,
                    'email' => $username . '@abs.com',
                    'password' => Hash::make('nasabah123'),
                    'status' => 'aktif'
                ]
            );
        }

        // 6. Ensure diverse Pengepuls Exist
        $pengepuls = [];
        $pengepulNames = ['CV. Maju Bersama', 'UD. Plastik Jaya', 'PT. Logam Utama', 'CV. Bersih Sejahtera'];
        foreach ($pengepulNames as $name) {
            $username = strtolower(str_replace([' ', '.'], '', $name));
            $pengepuls[] = Pengepul::firstOrCreate(
                ['username' => $username],
                [
                    'nama' => $name,
                    'email' => $username . '@abs.com',
                    'password' => Hash::make('pengepul123'),
                    'no_telp' => '08123456789' . rand(0, 9),
                    'nama_lembaga' => $name,
                    'alamat' => 'Surakarta',
                    'status' => 'aktif'
                ]
            );
        }

        // 7. Ensure diverse Petugas and Tukang Exist for each Warehouse
        $petugasList = [];
        $tukangList = [];
        foreach ($gudangs as $gudangName => $g) {
            $petugasList[$gudangName] = Petugas::firstOrCreate(
                ['username' => 'petugas_' . $g->gudang_id],
                [
                    'nama' => 'Petugas ' . $gudangName,
                    'email' => 'petugas_' . $g->gudang_id . '@abs.com',
                    'password' => Hash::make('petugas123'),
                    'gudang_id' => $g->gudang_id
                ]
            );

            $tukangList[$gudangName] = Tukang::firstOrCreate(
                ['nama' => 'Tukang ' . $gudangName],
                [
                    'no_telp' => '0812' . rand(10000000, 99999999),
                    'active' => 1,
                    'gudang_id' => $g->gudang_id
                ]
            );
        }

        // Clean existing Penimbangan, Penjemputan, TransaksiNasabah, DetailTransaksi, TransaksiPengepul
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Penimbangan::truncate();
        Penjemputan::truncate();
        TransaksiNasabah::truncate();
        DetailTransaksi::truncate();
        TransaksiPengepul::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 8. Seed 20 Diverse Audit Transactions (Selesai and Tidak Terlaksana)
        $now = Carbon::now();
        $auditSpecs = [
            // Format: [DateOffsetDays, NasabahIndex, ItemName, Berat, Sumber, Status, GudangName]
            [1, 0, 'Organik', 15.5, 'Jemput', 'Selesai', 'Gudang Pusat'],
            [1, 1, 'Plastik PET', 8.2, 'Setor Manual', 'Selesai', 'Gudang Pusat'],
            [2, 2, 'Kertas', 12.0, 'Jemput', 'Selesai', 'Gudang Timur'],
            [2, 3, 'Logam', 6.5, 'Jemput', 'Tidak Terlaksana', 'Gudang Timur'],
            [3, 4, 'Organik', 18.3, 'Setor Manual', 'Selesai', 'Gudang Barat'],
            [4, 5, 'Plastik PET', 0.0, 'Jemput', 'Tidak Terlaksana', 'Gudang Barat'],
            [5, 6, 'Kertas', 9.5, 'Jemput', 'Selesai', 'Gudang Selatan'],
            [6, 7, 'Logam', 11.2, 'Setor Manual', 'Selesai', 'Gudang Selatan'],
            [7, 8, 'Organik', 14.0, 'Jemput', 'Selesai', 'Gudang Surakarta'],
            [8, 9, 'Plastik PET', 7.5, 'Jemput', 'Tidak Terlaksana', 'Gudang Surakarta'],
            [9, 0, 'Kertas', 22.1, 'Setor Manual', 'Selesai', 'Gudang Pusat'],
            [10, 1, 'Logam', 5.8, 'Jemput', 'Selesai', 'Gudang Timur'],
            [11, 2, 'Organik', 11.3, 'Jemput', 'Tidak Terlaksana', 'Gudang Barat'],
            [12, 3, 'Plastik PET', 13.4, 'Setor Manual', 'Selesai', 'Gudang Selatan'],
            [13, 4, 'Kertas', 8.7, 'Jemput', 'Selesai', 'Gudang Surakarta'],
            [14, 5, 'Logam', 0.0, 'Jemput', 'Tidak Terlaksana', 'Gudang Pusat'],
            [15, 6, 'Organik', 19.1, 'Setor Manual', 'Selesai', 'Gudang Timur'],
            [16, 7, 'Plastik PET', 10.8, 'Jemput', 'Selesai', 'Gudang Barat'],
            [17, 8, 'Kertas', 0.0, 'Jemput', 'Tidak Terlaksana', 'Gudang Selatan'],
            [18, 9, 'Logam', 16.4, 'Setor Manual', 'Selesai', 'Gudang Surakarta'],
        ];

        foreach ($auditSpecs as $spec) {
            $date = $now->copy()->subDays($spec[0]);
            $nasabah = $nasabahs[$spec[1]];
            $itemName = $spec[2];
            $berat = $spec[3];
            $sumber = $spec[4];
            $status = $spec[5];
            $gudangName = $spec[6];

            $g = $gudangs[$gudangName];
            $petugas = $petugasList[$gudangName];
            $tukang = $tukangList[$gudangName];
            $sampah = $sampahMap[$gudangName][$itemName];

            if ($status === 'Tidak Terlaksana') {
                // Creates a failed pickup (tolak/batal)
                Penjemputan::create([
                    'deskripsi' => 'Penjemputan dibatalkan oleh pihak nasabah/petugas.',
                    'alamat' => $nasabah->alamat ?? 'Alamat Nasabah',
                    'jadwal' => $date,
                    'estimasi_berat' => 10,
                    'status' => rand(0, 1) ? 'tolak' : 'batal',
                    'ket_status' => 'Dibatalkan oleh sistem/petugas.',
                    'tukang_id' => $tukang->tukang_id,
                    'nasabah_id' => $nasabah->nasabah_id,
                    'petugas_id' => $petugas->petugas_id,
                    'gudang_id' => $g->gudang_id,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            } else {
                // Completed Transaction
                $priceBeli = $items[$itemName]->harga_beli;
                $totalHarga = $berat * $priceBeli;

                // TransaksiNasabah
                $transName = TransaksiNasabah::create([
                    'tipe_transaksi' => ($sumber === 'Jemput') ? 'dijemput' : 'antar_sendiri',
                    'tanggal' => $date,
                    'status' => 'selesai',
                    'petugas_id' => $petugas->petugas_id,
                    'saldo_sebelum' => 50000,
                    'total_harga' => $totalHarga,
                    'saldo_sesudah' => 50000 + $totalHarga,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                // Create Penjemputan first if Sumber is Jemput
                $penjemputanId = null;
                if ($sumber === 'Jemput') {
                    $pj = Penjemputan::create([
                        'deskripsi' => 'Minta penjemputan sampah rutin.',
                        'alamat' => $nasabah->alamat ?? 'Alamat Nasabah',
                        'jadwal' => $date,
                        'estimasi_berat' => $berat,
                        'status' => 'selesai',
                        'tukang_id' => $tukang->tukang_id,
                        'nasabah_id' => $nasabah->nasabah_id,
                        'petugas_id' => $petugas->petugas_id,
                        'gudang_id' => $g->gudang_id,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                    $penjemputanId = $pj->penjemputan_id;
                }

                // Penimbangan
                Penimbangan::create([
                    'berat_timbang' => $berat,
                    'nasabah_id' => $nasabah->nasabah_id,
                    'transaksi_id' => $transName->transaksi_id,
                    'sampah_id' => $sampah->sampah_id,
                    'tukang_id' => $tukang->tukang_id,
                    'penjemputan_id' => $penjemputanId,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        // 9. Seed Pengepul Penjualan Transactions (so that the Sales report tables are populated)
        $pengepulSpecs = [
            // PengepulIdx, ItemName, Berat, GudangName, DateOffset, Status
            [0, 'Organik', 35.2, 'Gudang Pusat', 8, 'selesai'],
            [0, 'Kertas', 18.5, 'Gudang Pusat', 8, 'selesai'],
            [1, 'Plastik PET', 24.1, 'Gudang Pusat', 9, 'selesai'],
            [2, 'Logam', 11.7, 'Gudang Pusat', 10, 'batal'],
            [3, 'Organik', 25.0, 'Gudang Timur', 11, 'selesai'],
            [0, 'Kertas', 20.0, 'Gudang Barat', 12, 'tolak'],
            [1, 'Logam', 15.0, 'Gudang Selatan', 13, 'selesai'],
            [2, 'Plastik PET', 30.0, 'Gudang Surakarta', 14, 'selesai'],
        ];

        foreach ($pengepulSpecs as $spec) {
            $pengepul = $pengepuls[$spec[0]];
            $itemName = $spec[1];
            $berat = $spec[2];
            $gudangName = $spec[3];
            $date = $now->copy()->subDays($spec[4]);
            $status = $spec[5];

            $g = $gudangs[$gudangName];
            $sampah = $sampahMap[$gudangName][$itemName];
            $priceJual = $items[$itemName]->harga_jual;
            $totalHarga = $berat * $priceJual;

            $tp = TransaksiPengepul::create([
                'status' => $status,
                'deadline' => $date->copy()->addDays(2),
                'pengepul_id' => $pengepul->pengepul_id,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            DetailTransaksi::create([
                'harga' => $totalHarga,
                'berat' => $berat,
                'sampah_id' => $sampah->sampah_id,
                'transaksi_id' => $tp->transaksi_id,
                'status' => $status === 'selesai' ? 'setuju' : 'tolak',
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}
