<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

// Models
use App\Models\Berita;
use App\Models\Petugas;

class DummyDataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // =========================================================
        // 1. LOGIK COPY GAMBAR BERITA DARI PUBLIC KE STORAGE
        // =========================================================
        Storage::disk('public')->makeDirectory('thumbnail'); // Sesuaikan nama folder dengan controller anda

        $pathFotoBerita = null;
        // LALUAN BARU: Halakan ke fail gambar yang baru anda buat
        $sourceBerita = public_path('dummy_images/berita/berita1.jpeg');
        
        if (File::exists($sourceBerita)) {
            $pathFotoBerita = 'thumbnail/berita1.jpeg';
            // Salin gambar dari public/dummy_images... ke storage/app/public/thumbnail...
            Storage::disk('public')->put($pathFotoBerita, File::get($sourceBerita));
        }

        // =========================================================
        // 2. SEMAK / BINA AKAUN PETUGAS PENULIS BERITA
        // =========================================================
        // Menggunakan id gudang 1 sebagai default jika akaun ini belum wujud
        $petugasPembuatBerita = Petugas::firstOrCreate(
            ['email' => 'petugas@abs.com'],
            [
                'nama' => 'Petugas Utama',
                'username' => 'petugas',
                'password' => Hash::make('petugas123'),
                'gudang_id' => 1, 
            ]
        );

        // =========================================================
        // 3. JANA BERITA, ARTIKEL, DAN EVENT
        // =========================================================
        $dataBerita = [
            [
                'judul' => 'Pentingnya Daur Ulang Plastik di Era Modern',
                'kategori' => 'Artikel',
                'isi' => 'Plastik membutuhkan waktu ratusan tahun untuk terurai. Oleh karena itu, langkah kecil seperti memilah sampah plastik dari rumah sangat berarti bagi lingkungan. Mari mulai daur ulang sekarang bersama Bank Sampah.',
                'tanggal' => Carbon::now()->subDays(1)->format('Y-m-d'),
            ],
            [
                'judul' => 'Bank Sampah Sukses Gelar Sosialisasi Desa Bersih',
                'kategori' => 'Berita',
                'isi' => 'Pada hari minggu kemarin, Bank Sampah kami telah sukses menyelenggarakan sosialisasi pemilahan sampah di balai desa. Warga sangat antusias dan puluhan orang langsung mendaftar menjadi nasabah baru.',
                'tanggal' => Carbon::now()->subDays(3)->format('Y-m-d'),
            ],
            [
                'judul' => 'Event Lomba Kerajinan dari Barang Bekas',
                'kategori' => 'Event',
                'isi' => 'Ikuti event tahunan kami! Buat kerajinan tangan paling kreatif dari sampah daur ulang dan menangkan total hadiah uang tunai jutaan rupiah. Pendaftaran ditutup akhir bulan ini. Segera daftar!',
                'tanggal' => Carbon::now()->addDays(5)->format('Y-m-d'),
            ],
            [
                'judul' => 'Kabar Gembira: Harga Beli Logam Naik Bulan Ini!',
                'kategori' => 'Berita',
                'isi' => 'Bagi nasabah setia kami, harga beli untuk sampah kategori Logam (Besi, Seng, Kuningan) mengalami kenaikan signifikan bulan ini. Segera kumpulkan dan setorkan rongsokan Anda ke gudang terdekat kami.',
                'tanggal' => Carbon::now()->subDays(5)->format('Y-m-d'),
            ],
            [
                'judul' => 'Cara Mudah Membuat Kompos dari Sampah Dapur',
                'kategori' => 'Artikel',
                'isi' => 'Sampah organik seperti sisa sayuran dan kulit buah jangan langsung dibuang! Anda bisa mengolahnya menjadi pupuk kompos yang menyuburkan tanaman. Caranya cukup siapkan wadah tertutup, campurkan dengan tanah, dan aduk secara rutin.',
                'tanggal' => Carbon::now()->subDays(7)->format('Y-m-d'),
            ]
        ];

        foreach ($dataBerita as $berita) {
            // Gunakan updateOrCreate supaya jika judul sudah ada, gambar akan dikemaskini
            Berita::updateOrCreate(
                ['judul' => $berita['judul']],
                [
                    'kategori' => $berita['kategori'],
                    'isi' => $berita['isi'],
                    'thumbnail' => $pathFotoBerita, // Menggunakan laluan imej baru
                    'tanggal' => $berita['tanggal'],
                    'petugas_id' => $petugasPembuatBerita->petugas_id,
                ]
            );
        }
    }
}