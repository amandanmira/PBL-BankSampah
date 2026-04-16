<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

use App\Models\TransaksiPengepul;

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
                $transaksi = TransaksiPengepul::create([
                    'status' => 'selesai',
                    'deadline' => $today,
                    'pengepul_id' => $h,
                ]);

                for ($j = 0; $j <3; $j++) {
                    $berat = rand(1, 10);
                    $transaksi->detailTransaksi()->create([
                        'harga' => $berat * 1000,
                        'berat' => $berat,
                        'sampah_id' => rand(1, 5),
                    ]);
                }
            }
        }
    }
}
