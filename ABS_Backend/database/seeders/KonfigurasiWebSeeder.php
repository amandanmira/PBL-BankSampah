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
        KonfigurasiWeb::create([
            'logo' => url('bank.webp'),
            'quote' => 'Ubah sampahmu menjadi pundi-pundi rupiah yang berharga bersama kami.',
            'hero_quote_top' => 'Mari Bersama',
            'hero_quote_bottom' => 'Jaga Bumi Kita',
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
