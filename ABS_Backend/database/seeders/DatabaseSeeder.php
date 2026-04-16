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
use App\Models\Tukang;

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
            'kapasitas' => '1000',
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
                'kategori_id' => '1',
            ],
            [
                'nama' => 'Putihan',
                'kategori_id' => '1',
            ],
            [
                'nama' => 'Bening',
                'kategori_id' => '1',
            ],
            [
                'nama' => 'Multilayer',
                'kategori_id' => '1',
            ],
            [
                'nama' => 'Duplek',
                'kategori_id' => '2',
            ],
            [
                'nama' => 'Putihan',
                'kategori_id' => '2',
            ],
            [
                'nama' => 'Minyak',
                'kategori_id' => '2',
            ],
            [
                'nama' => 'Sterofom',
                'kategori_id' => '3',
            ],
            [
                'nama' => 'Tisu',
                'kategori_id' => '3',
            ],
            [
                'nama' => 'Sapon',
                'kategori_id' => '3',
            ],
            [
                'nama' => 'Kain',
                'kategori_id' => '3',
            ],
            [
                'nama' => 'Botol Bening',
                'kategori_id' => '4',
            ],
            [
                'nama' => 'Botol Gelap',
                'kategori_id' => '4',
            ],
            [
                'nama' => 'Pecahan Kaca',
                'kategori_id' => '4',
            ],
            [
                'nama' => 'Aluminium',
                'kategori_id' => '5',
            ],
            [
                'nama' => 'Kaleng',
                'kategori_id' => '5',
            ],
            [
                'nama' => 'Besi',
                'kategori_id' => '5',
            ],
            [
                'nama' => 'Tembaga',
                'kategori_id' => '5',
            ],
            [
                'nama' => 'Botol Air',
                'kategori_id' => '6',
            ],
            [
                'nama' => 'Plastik Keras',
                'kategori_id' => '6',
            ],
            [
                'nama' => 'Handphone',
                'kategori_id' => '7',
            ],
            [
                'nama' => 'Kabel',
                'kategori_id' => '7',
            ],
            [
                'nama' => 'Charger',
                'kategori_id' => '7',
            ],
            [
                'nama' => 'Computer',
                'kategori_id' => '7',
            ],
            [
                'nama' => 'TV/Radio',
                'kategori_id' => '7',
            ],
        ];

        foreach ($dataItem as $item) {
            ItemSampah::create([
                'nama' => $item['nama'],
                'harga_beli' => '500',
                'harga_jual' => '1000',
                'diskon' => '0',
                'kategori_id' => $item['kategori_id'],
            ]);
        }

        $dataSampah = [
            [ 'item_id' => 1 ],
            [ 'item_id' => 5 ],
            [ 'item_id' => 8 ],
            [ 'item_id' => 12 ],
            [ 'item_id' => 15, ],
        ];

        foreach ($dataSampah as $item) {
            Sampah::create([
                'stok' => 100,
                'item_id' => $item['item_id'],
                'gudang_id' => 1,
            ]);
        }

        $dataNasabah = [
            [
                'nama' => 'Nasabah',
                'username' => 'nasabah',
            ],
            [
                'nama' => 'Nasabah2',
                'username' => 'nasabah2',
            ],
            [
                'nama' => 'Nasabah3',
                'username' => 'nasabah3',
            ],
        ];

        foreach ($dataNasabah as $item) {
            Nasabah::create([
                'nama' => $item['nama'],
                'username' => $item['username'],
                'email' => $item['username'] . '@abs.com',
                'password' => Hash::make('nasabah123'),
                'status' => 'aktif',
            ]);
        }

        $dataPengepul = [
            [
                'nama' => 'Pengepul',
                'username' => 'pengepul',
            ],
            [
                'nama' => 'Pengepul2',
                'username' => 'pengepul2',
            ],
            [
                'nama' => 'Pengepul3',
                'username' => 'pengepul3',
            ],
        ];

        foreach ($dataPengepul as $item) {
            Pengepul::create([
                'nama' => $item['nama'],
                'username' => $item['username'],
                'email' => $item['username'] . '@abs.com',
                'password' => Hash::make('pengepul123'),
                'no_telp' => '081234567890',
                'nama_lembaga' => 'CV.Penegpul',
                'alamat' => 'Surakarta',
                'status' => 'aktif',
            ]);
        }

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

        Tukang::create([
            'nama' => 'Tukang',
            'no_telp' => '081212341234',
            'active' => 1,
            'gudang_id' => 1,
        ]);
    }
}
