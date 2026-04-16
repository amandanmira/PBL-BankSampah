<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

use App\Models\TransaksiPengepul;
use App\Models\TransaksiNasabah;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::now();

        for ($h = 1; $h <= 3; $h++) {
            for ($i = 0; $i <3; $i++) {
                $transaksiPengepul = TransaksiPengepul::create([
                    'status' => 'selesai',
                    'deadline' => $today,
                    'pengepul_id' => $h,
                ]);

                $transaksiNasabah = TransaksiNasabah::create([
                    'tanggal' => $today,
                    'status' => 'selesai',
                    'petugas_id' => 1,
                ]);

                for ($j = 0; $j <3; $j++) {
                    $berat = rand(1, 10);

                    $transaksiPengepul->detailTransaksi()->create([
                        'harga' => $berat * 1000,
                        'berat' => $berat,
                        'sampah_id' => rand(1, 5),
                    ]);

                    $transaksiNasabah->penimbangan()->create([
                        'berat_timbang' => $berat,
                        'nasabah_id' => $h,
                        'tukang_id' => 1,
                        'sampah_id' => rand(1, 5),
                    ]);
                }
            }
        }
    }
}
