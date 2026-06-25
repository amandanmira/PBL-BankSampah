<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KonfigurasiWeb;

class KonfigurasiWebSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kosongkan tabel agar tidak terjadi duplikasi saat seeder dijalankan berulang kali
        KonfigurasiWeb::truncate();

        // Copy logo from public to storage/app/public
        $sourcePath = public_path('bank.webp');
        $destinationDir = storage_path('app/public');
        $destinationPath = $destinationDir . '/bank.webp';

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        if (file_exists($sourcePath)) {
            copy($sourcePath, $destinationPath);
        }

        KonfigurasiWeb::create([
            'logo' => 'bank.webp',
            'quote' => 'Ubah sampahmu menjadi pundi-pundi rupiah yang berharga bersama kami.',
            'hero_quote_top' => 'Kelola Sampah, Raih Manfaat',
            'hero_quote_bottom' => 'Kelola Sampah Menjadi Tabungan Digital yang Transparan dan Terintegrasi',
            'instagram' => 'https://instagram.com/',
            'facebook' => 'https://facebook.com/',
            'linkedin' => 'https://linkedin.com/',
            'youtube' => 'https://youtube.com/',
            'no_telp' => '+6281234567890',
            'email' => 'halo@banksampah.com',
            'lama_deadline' => 7,
            'batas_waktu_edit' => 2,
            'alamat' => 'Jl. Ir Soekarno No. 9, Surakarta, Jawa Tengah',
            'tentang' => 'Bank Sampah ABS (Amanah Bersih Sejahtera) adalah platform modern untuk membantu masyarakat mendaur ulang sampah secara efisien dan menguntungkan.',
        ]);
    }
}
