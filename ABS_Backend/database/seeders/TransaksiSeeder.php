<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        // =========================================================
        // LOGIKA COPY GAMBAR TUKANG DARI PUBLIC KE STORAGE
        // =========================================================
        Storage::disk('public')->makeDirectory('tukang_foto');

        $daftarGambarTukang = ['tukang3.jpeg', 'tukang4.jpeg', 'Untilted.jpeg'];
        $pathFotoTersedia = [];

        foreach ($daftarGambarTukang as $namaFile) {
            $sourcePath = public_path('dummy_images/tukang/' . $namaFile);
            if (File::exists($sourcePath)) {
                $destPath = 'tukang_foto/' . $namaFile;
                Storage::disk('public')->put($destPath, File::get($sourcePath));
                $pathFotoTersedia[] = $destPath; 
            }
        }

        // =========================================================
        // LOGIKA COPY GAMBAR BUKTI PENJEMPUTAN & TRANSFER
        // =========================================================
        Storage::disk('public')->makeDirectory('penjemputan_foto'); 
        $pathFotoPenjemputan = null;
        $sourcePenjemputan = public_path('dummy_images/penimbangan/timbang.jpeg');
        if (File::exists($sourcePenjemputan)) {
            $pathFotoPenjemputan = 'penjemputan_foto/timbang.jpeg';
            Storage::disk('public')->put($pathFotoPenjemputan, File::get($sourcePenjemputan));
        }

        Storage::disk('public')->makeDirectory('bukti_transfer'); 
        $pathBuktiTransfer = null;
        $sourceBukti = public_path('dummy_images/bukti/bukti.jpeg');
        if (File::exists($sourceBukti)) {
            $pathBuktiTransfer = 'bukti_transfer/bukti.jpeg';
            Storage::disk('public')->put($pathBuktiTransfer, File::get($sourceBukti));
        }

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
        $kategoriNames = ['Organik', 'Plastik', 'Kertas', 'Logam', 'Residu', 'Kaca & Beling', 'Rosok', 'Elektronik', 'Minyak'];
        $categories = [];
        foreach ($kategoriNames as $catName) {
            $categories[$catName] = KategoriSampah::firstOrCreate(['nama' => $catName]);
        }

        // Clean existing Data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ItemSampah::truncate();
        Sampah::truncate();
        Penimbangan::truncate();
        Penjemputan::truncate();
        TransaksiNasabah::truncate();
        DetailTransaksi::truncate();
        TransaksiPengepul::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 3. Ensure ItemSampah Exist (25 Item dengan Keyword Gambar yang Nyambung)
        $dataItem = [
            // Plastik
            ['nama' => 'Gelas Plastik Bening (PET)', 'kategori' => 'Plastik', 'beli' => 2000, 'jual' => 3000],
            ['nama' => 'Gelas Plastik Warna (PET)', 'kategori' => 'Plastik', 'beli' => 500, 'jual' => 1000],
            ['nama' => 'Botol Plastik Bening (PET)', 'kategori' => 'Plastik', 'beli' => 2000, 'jual' => 3500],
            ['nama' => 'Botol Plastik Warna', 'kategori' => 'Plastik', 'beli' => 1000, 'jual' => 1500],
            ['nama' => 'Plastik HD Bening', 'kategori' => 'Plastik', 'beli' => 1500, 'jual' => 2500],
            ['nama' => 'Plastik Kresek Campur', 'kategori' => 'Plastik', 'beli' => 200, 'jual' => 500],
            ['nama' => 'Plastik Multilayer (Kemasan)', 'kategori' => 'Plastik', 'beli' => 100, 'jual' => 300],
            ['nama' => 'Galon Plastik', 'kategori' => 'Plastik', 'beli' => 2000, 'jual' => 3000],
            
            // Kertas
            ['nama' => 'Kardus Bekas (Corrugated)', 'kategori' => 'Kertas', 'beli' => 5000, 'jual' => 6500],
            ['nama' => 'Kertas HVS/Buku Tulis', 'kategori' => 'Kertas', 'beli' => 1500, 'jual' => 2500],
            ['nama' => 'Kertas Koran', 'kategori' => 'Kertas', 'beli' => 1000, 'jual' => 2000],
            ['nama' => 'Kertas Duplek (Karton)', 'kategori' => 'Kertas', 'beli' => 300, 'jual' => 800],
            ['nama' => 'Kertas Semen', 'kategori' => 'Kertas', 'beli' => 2000, 'jual' => 3000],
            
            // Residu
            ['nama' => 'Sterofom', 'kategori' => 'Residu', 'beli' => 100, 'jual' => 300],
            ['nama' => 'Tisu Bekas', 'kategori' => 'Residu', 'beli' => 50, 'jual' => 100],
            ['nama' => 'Sapon (Sampah Sapuan)', 'kategori' => 'Residu', 'beli' => 50, 'jual' => 100],
            ['nama' => 'Kain Perca / Baju Bekas', 'kategori' => 'Residu', 'beli' => 300, 'jual' => 600],
            
            // Kaca & Beling
            ['nama' => 'Botol Kaca Bening', 'kategori' => 'Kaca & Beling', 'beli' => 300, 'jual' => 800],
            ['nama' => 'Botol Kaca Sirup', 'kategori' => 'Kaca & Beling', 'beli' => 200, 'jual' => 500],
            ['nama' => 'Pecahan Kaca Campur', 'kategori' => 'Kaca & Beling', 'beli' => 100, 'jual' => 300],

            // Logam
            ['nama' => 'Besi Padat/Super', 'kategori' => 'Logam', 'beli' => 4500, 'jual' => 5600],
            ['nama' => 'Besi Kopong/Tipis (Kaleng)', 'kategori' => 'Logam', 'beli' => 3000, 'jual' => 4000],
            ['nama' => 'Aluminium Kaleng', 'kategori' => 'Logam', 'beli' => 10000, 'jual' => 14000],
            ['nama' => 'Tembaga Super (Kabel)', 'kategori' => 'Logam', 'beli' => 50000, 'jual' => 65000],
            ['nama' => 'Kuningan', 'kategori' => 'Logam', 'beli' => 30000, 'jual' => 45000],

            // Rosok
            ['nama' => 'Plastik Keras (Ember/Baskom)', 'kategori' => 'Rosok', 'beli' => 1000, 'jual' => 2000],
            ['nama' => 'Sepatu/Sandal Karet', 'kategori' => 'Rosok', 'beli' => 200, 'jual' => 500],
            
            // Elektronik
            ['nama' => 'Handphone Rusak (Unit)', 'kategori' => 'Elektronik', 'beli' => 5000, 'jual' => 10000],
            ['nama' => 'Kabel Elektronik Campur', 'kategori' => 'Elektronik', 'beli' => 8000, 'jual' => 12000],
            ['nama' => 'Motherboard Komputer', 'kategori' => 'Elektronik', 'beli' => 15000, 'jual' => 25000],
            
            // Minyak & Organik
            ['nama' => 'Minyak Jelantah Bening', 'kategori' => 'Minyak', 'beli' => 2000, 'jual' => 3500],
            ['nama' => 'Sampah Dapur Organik', 'kategori' => 'Organik', 'beli' => 100, 'jual' => 300],
        ];

        // KAMUS KATA KUNCI AGAR GAMBAR NYAMBUNG
        $kategoriKeywords = [
            'Plastik' => 'plastic,bottle,waste',
            'Kertas' => 'cardboard,paper,waste',
            'Residu' => 'garbage,trash',
            'Kaca & Beling' => 'glass,bottle,broken',
            'Logam' => 'scrap,metal,steel',
            'Rosok' => 'junk,plastic',
            'Elektronik' => 'ewaste,electronic,motherboard',
            'Minyak' => 'cooking,oil,waste',
            'Organik' => 'compost,vegetable,waste',
        ];

        Storage::disk('public')->makeDirectory('sampah_foto');
        $items = [];

        foreach ($dataItem as $item) {
            $namaFileLokal = Str::slug($item['nama']) . '.jpg';
            $pathFotoLokal = 'sampah_foto/' . $namaFileLokal;

            if (!Storage::disk('public')->exists($pathFotoLokal)) {
                // Ambil keyword inggris berdasarkan kategori sampahnya
                $keyword = $kategoriKeywords[$item['kategori']] ?? 'recycle';
                
                // Gunakan id acak berdasarkan nama agar beda-beda tiap item tapi nyambung dengan kategorinya
                $lockId = crc32($item['nama']) % 1000 + 1; 
                
                // Panggil LoremFlickr (mengerti keyword)
                $urlGambarBermakna = "https://loremflickr.com/400/400/{$keyword}/all?lock={$lockId}";
                
                // Tambahkan User-Agent palsu karena kadang LoremFlickr menolak proses download dari script
                $context = stream_context_create([
                    'http' => [
                        'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
                    ]
                ]);

                $imageContent = @file_get_contents($urlGambarBermakna, false, $context);
                if ($imageContent) {
                    Storage::disk('public')->put($pathFotoLokal, $imageContent);
                }
            }
            
            $items[$item['nama']] = ItemSampah::create([
                'nama' => $item['nama'],
                'harga_beli' => $item['beli'],
                'harga_jual' => $item['jual'],
                'kategori_id' => $categories[$item['kategori']]->kategori_id,
                'active' => 1,
                'foto' => $pathFotoLokal
            ]);
        }

        // 4. Link Items to all Warehouses (Sampah model)
        $sampahMap = []; 
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

            $fotoTerpilih = null;
            if (count($pathFotoTersedia) > 0) {
                $fotoTerpilih = $pathFotoTersedia[array_rand($pathFotoTersedia)];
            }

            $tukangList[$gudangName] = Tukang::firstOrCreate(
                ['nama' => 'Tukang ' . $gudangName],
                [
                    'no_telp' => '0812' . rand(10000000, 99999999),
                    'active' => 1,
                    'gudang_id' => $g->gudang_id,
                    'foto' => $fotoTerpilih 
                ]
            );
        }

        // Clean existing Transaksi
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Penimbangan::truncate();
        Penjemputan::truncate();
        TransaksiNasabah::truncate();
        DetailTransaksi::truncate();
        TransaksiPengepul::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 8. Seed 20 Diverse Audit Transactions
        $now = Carbon::now();
        $auditSpecs = [
            [1, 0, 'Sampah Dapur Organik', 15.5, 'Jemput', 'Selesai', 'Gudang Pusat', 'Senin - Jumat', '08:00 - 12:00'],
            [1, 1, 'Botol Plastik Bening (PET)', 8.2, 'Setor Manual', 'Selesai', 'Gudang Pusat', 'Senin - Jumat', '13:00 - 17:00'],
            [2, 2, 'Kardus Bekas (Corrugated)', 12.0, 'Jemput', 'Selesai', 'Gudang Timur', 'Sabtu - Minggu', '08:00 - 12:00'],
            [2, 3, 'Besi Padat/Super', 6.5, 'Jemput', 'Tidak Terlaksana', 'Gudang Timur', 'Senin - Jumat', '13:00 - 17:00'],
            [3, 4, 'Sampah Dapur Organik', 18.3, 'Setor Manual', 'Selesai', 'Gudang Barat', 'Sabtu - Minggu', '13:00 - 17:00'],
            [4, 5, 'Botol Plastik Bening (PET)', 0.0, 'Jemput', 'Tidak Terlaksana', 'Gudang Barat', 'Senin - Jumat', '08:00 - 12:00'],
            [5, 6, 'Kardus Bekas (Corrugated)', 9.5, 'Jemput', 'Selesai', 'Gudang Selatan', 'Sabtu - Minggu', '08:00 - 12:00'],
            [6, 7, 'Besi Padat/Super', 11.2, 'Setor Manual', 'Selesai', 'Gudang Selatan', 'Senin - Jumat', '13:00 - 17:00'],
            [7, 8, 'Sampah Dapur Organik', 14.0, 'Jemput', 'Selesai', 'Gudang Surakarta', 'Setiap Hari', '08:00 - 16:00'],
            [8, 9, 'Botol Plastik Bening (PET)', 7.5, 'Jemput', 'Tidak Terlaksana', 'Gudang Surakarta', 'Setiap Hari', '08:00 - 16:00'],
            [9, 0, 'Kardus Bekas (Corrugated)', 22.1, 'Setor Manual', 'Selesai', 'Gudang Pusat', 'Senin - Jumat', '13:00 - 17:00'],
            [10, 1, 'Besi Padat/Super', 5.8, 'Jemput', 'Selesai', 'Gudang Timur', 'Sabtu - Minggu', '13:00 - 17:00'],
            [11, 2, 'Sampah Dapur Organik', 11.3, 'Jemput', 'Tidak Terlaksana', 'Gudang Barat', 'Senin - Jumat', '08:00 - 12:00'],
            [12, 3, 'Botol Plastik Bening (PET)', 13.4, 'Setor Manual', 'Selesai', 'Gudang Selatan', 'Senin - Jumat', '13:00 - 17:00'],
            [13, 4, 'Kardus Bekas (Corrugated)', 8.7, 'Jemput', 'Selesai', 'Gudang Surakarta', 'Sabtu - Minggu', '08:00 - 12:00'],
            [14, 5, 'Besi Padat/Super', 0.0, 'Jemput', 'Tidak Terlaksana', 'Gudang Pusat', 'Senin - Jumat', '08:00 - 12:00'],
            [15, 6, 'Sampah Dapur Organik', 19.1, 'Setor Manual', 'Selesai', 'Gudang Timur', 'Setiap Hari', '08:00 - 16:00'],
            [16, 7, 'Botol Plastik Bening (PET)', 10.8, 'Jemput', 'Selesai', 'Gudang Barat', 'Senin - Jumat', '13:00 - 17:00'],
            [17, 8, 'Kardus Bekas (Corrugated)', 0.0, 'Jemput', 'Tidak Terlaksana', 'Gudang Selatan', 'Sabtu - Minggu', '13:00 - 17:00'],
            [18, 9, 'Besi Padat/Super', 16.4, 'Setor Manual', 'Selesai', 'Gudang Surakarta', 'Senin - Jumat', '08:00 - 12:00'],
        ];

        foreach ($auditSpecs as $spec) {
            $date = $now->copy()->subDays($spec[0]);
            $nasabah = $nasabahs[$spec[1]];
            $itemName = $spec[2];
            $berat = $spec[3];
            $sumber = $spec[4];
            $status = $spec[5];
            $gudangName = $spec[6];
            $rentangHari = $spec[7];
            $rentanWaktu = $spec[8];

            $g = $gudangs[$gudangName];
            $petugas = $petugasList[$gudangName];
            $tukang = $tukangList[$gudangName];
            $sampah = $sampahMap[$gudangName][$itemName];

            if ($status === 'Tidak Terlaksana') {
                Penjemputan::create([
                    'deskripsi' => 'Penjemputan dibatalkan oleh pihak nasabah/petugas.',
                    'alamat' => $nasabah->alamat ?? 'Alamat Nasabah',
                    'jadwal' => $date,
                    'rentang_hari' => $rentangHari,
                    'rentan_waktu' => $rentanWaktu,
                    'estimasi_berat' => 10,
                    'status' => rand(0, 1) ? 'tolak' : 'batal',
                    'ket_status' => 'Dibatalkan oleh sistem/petugas.',
                    'tukang_id' => $tukang->tukang_id,
                    'nasabah_id' => $nasabah->nasabah_id,
                    'petugas_id' => $petugas->petugas_id,
                    'gudang_id' => $g->gudang_id,
                    'foto' => $pathFotoPenjemputan,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            } else {
                $priceBeli = $items[$itemName]->harga_beli;
                $totalHarga = $berat * $priceBeli;

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

                $penjemputanId = null;
                if ($sumber === 'Jemput') {
                    $pj = Penjemputan::create([
                        'deskripsi' => 'Minta penjemputan sampah rutin.',
                        'alamat' => $nasabah->alamat ?? 'Alamat Nasabah',
                        'jadwal' => $date,
                        'rentang_hari' => $rentangHari,
                        'rentan_waktu' => $rentanWaktu,
                        'estimasi_berat' => $berat,
                        'status' => 'selesai',
                        'tukang_id' => $tukang->tukang_id,
                        'nasabah_id' => $nasabah->nasabah_id,
                        'petugas_id' => $petugas->petugas_id,
                        'gudang_id' => $g->gudang_id,
                        'foto' => $pathFotoPenjemputan,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                    $penjemputanId = $pj->penjemputan_id;
                }

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

        // 9. Seed Pengepul Penjualan Transactions
        $pengepulSpecs = [
            [0, 'Sampah Dapur Organik', 35.2, 'Gudang Pusat', 8, 'selesai'],
            [0, 'Kardus Bekas (Corrugated)', 18.5, 'Gudang Pusat', 8, 'selesai'],
            [1, 'Botol Plastik Bening (PET)', 24.1, 'Gudang Pusat', 9, 'selesai'],
            [2, 'Besi Padat/Super', 11.7, 'Gudang Pusat', 10, 'batal'],
            [3, 'Sampah Dapur Organik', 25.0, 'Gudang Timur', 11, 'selesai'],
            [0, 'Kardus Bekas (Corrugated)', 20.0, 'Gudang Barat', 12, 'tolak'],
            [1, 'Besi Padat/Super', 15.0, 'Gudang Selatan', 13, 'selesai'],
            [2, 'Botol Plastik Bening (PET)', 30.0, 'Gudang Surakarta', 14, 'selesai'],
        ];

        $alasanBatalPengepul = [
            'Dibatalkan oleh pengepul karena perubahan harga pasar.',
            'Batas waktu pembayaran (deadline) telah berakhir secara otomatis.',
            'Dibatalkan oleh petugas gudang karena stok tidak mencukupi.',
            'Pengepul membatalkan pesanan secara sepihak.'
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
            $petugas = $petugasList[$gudangName];

            $ketStatus = null;
            $buktiTf = null;

            if ($status === 'selesai') {
                $ketStatus = 'Pembayaran telah diverifikasi dan barang sudah diserahkan.';
                $buktiTf = $pathBuktiTransfer; 
            } elseif ($status === 'batal' || $status === 'tolak') {
                $ketStatus = $alasanBatalPengepul[array_rand($alasanBatalPengepul)]; 
            } else {
                $ketStatus = 'Menunggu pembayaran dari pengepul.';
            }

            $tp = TransaksiPengepul::create([
                'status' => $status,
                'ket_status' => $ketStatus,
                'bukti_transfer' => $buktiTf,
                'deadline' => $date->copy()->addDays(2),
                'pengepul_id' => $pengepul->pengepul_id,
                'petugas_id' => $petugas->petugas_id,
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