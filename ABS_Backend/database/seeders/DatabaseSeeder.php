<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

// Models
use App\Models\User;
use App\Models\Gudang;
use App\Models\Nasabah;
use App\Models\Pengepul;
use App\Models\Petugas;
use App\Models\Manager;
use App\Models\Admin;
use App\Models\KategoriSampah;
use App\Models\ItemSampah;
use App\Models\Sampah;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        Gudang::create([
            'alamat' => 'Surakarta',
        ]);

        $dataKategori = [
            ['nama' => 'Plastik'],
            ['nama' => 'Kertas'],
            ['nama' => 'Residu'],
            ['nama' => 'Beling'],
            ['nama' => 'Logam'],
            ['nama' => 'Rosok'],
            ['nama' => 'Elektronik']
        ];

        foreach ($dataKategori as $item) {
            KategoriSampah::create($item);
        }

        $dataItem = [
            [
                'nama' => 'Kresek',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '1',
            ],
            [
                'nama' => 'Putihan',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '1',
            ],
            [
                'nama' => 'Bening',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '1',
            ],
            [
                'nama' => 'Multilayer',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '1',
            ],
            [
                'nama' => 'Duplek',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '2',
            ],
            [
                'nama' => 'Putihan',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '2',
            ],
            [
                'nama' => 'Minyak',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '2',
            ],
            [
                'nama' => 'Sterofom',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '3',
            ],
            [
                'nama' => 'Tisu',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '3',
            ],
            [
                'nama' => 'Sapon',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '3',
            ],
            [
                'nama' => 'Kain',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '3',
            ],
            [
                'nama' => 'Botol Bening',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '4',
            ],
            [
                'nama' => 'Botol Gelap',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '4',
            ],
            [
                'nama' => 'Pecahan Kaca',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '4',
            ],
            [
                'nama' => 'Aluminium',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '5',
            ],
            [
                'nama' => 'Kaleng',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '5',
            ],
            [
                'nama' => 'Besi',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '5',
            ],
            [
                'nama' => 'Tembaga',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '5',
            ],
            [
                'nama' => 'Botol Air',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '6',
            ],
            [
                'nama' => 'Plastik Keras',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '6',
            ],
            [
                'nama' => 'Handphone',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '7',
            ],
            [
                'nama' => 'Kabel',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '7',
            ],
            [
                'nama' => 'Charger',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '7',
            ],
            [
                'nama' => 'Computer',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '7',
            ],
            [
                'nama' => 'TV/Radio',
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => '7',
            ],
        ];

        foreach ($dataItem as $item) {
            ItemSampah::create($item);
        }

        $dataSampah = [
            [
                'stok' => 100,
                'item_id' => 1,
                'gudang_id' => 1,
            ],
            [
                'stok' => 100,
                'item_id' => 5,
                'gudang_id' => 1,
            ],
            [
                'stok' => 100,
                'item_id' => 8,
                'gudang_id' => 1,
            ],
            [
                'stok' => 100,
                'item_id' => 12,
                'gudang_id' => 1,
            ],
            [
                'stok' => 100,
                'item_id' => 15,
                'gudang_id' => 1,
            ],
        ];

        foreach ($dataSampah as $item) {
            Sampah::create($item);
        }

        Nasabah::create([
            'nama' => 'Nasabah',
            'username' => 'nasabah',
            'email' => 'nasabah@abs.com',
            'password' => Hash::make('nasabah123'),
            'status' => 'aktif',
        ]);

        Pengepul::create([
            'nama' => 'Pengepul',
            'username' => 'pengepul',
            'email' => 'pengepul@abs.com',
            'password' => Hash::make('pengepul123'),
            'no_telp' => '081234567890',
            'nama_lembaga' => 'CV.Penegpul',
            'alamat' => 'Surakarta',
            'status' => 'aktif',
        ]);

        Petugas::create([
            'nama' => 'Petugas',
            'username' => 'petugas',
            'email' => 'petugas@abs.com',
            'password' => Hash::make('petugas123'),
            'gudang_id' => '1',
        ]);

        Manager::create([
            'nama' => 'Manager',
            'username' => 'manager',
            'email' => 'manager@abs.com',
            'password' => Hash::make('manager123'),
        ]);

        Admin::create([
            'nama' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@abs.com',
            'password' => Hash::make('admin123'),
        ]);
    }
}
