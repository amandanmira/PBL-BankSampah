<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

// Models
use App\Models\User;
use App\Models\Nasabah;
use App\Models\Pengepul;
use App\Models\Petugas;
use App\Models\Manager;
use App\Models\Admin;

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
            'password' => Hash::make('nasabah123'),
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
