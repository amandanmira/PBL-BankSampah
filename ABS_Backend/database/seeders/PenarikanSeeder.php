<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Penarikan;
use App\Models\Nasabah;
use App\Models\Petugas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PenarikanSeeder extends Seeder
{
    public function run(): void
    {
        // Get all nasabahs
        $nasabahs = Nasabah::all();
        if ($nasabahs->isEmpty()) {
            // Create some fallback nasabahs if none exist
            $nasabahNames = ['Siti Aminah', 'Bambang Sutejo', 'Rina Kusuma', 'Joko Widodo', 'Budi Santoso'];
            foreach ($nasabahNames as $name) {
                $username = strtolower(str_replace(' ', '', $name));
                $nasabahs[] = Nasabah::create([
                    'nama' => $name,
                    'username' => $username,
                    'email' => $username . '@abs.com',
                    'password' => bcrypt('nasabah123'),
                    'status' => 'aktif'
                ]);
            }
            $nasabahs = Nasabah::all();
        }

        // Get a petugas to process the withdrawals
        $petugas = Petugas::first();
        $petugasId = $petugas ? $petugas->petugas_id : null;

        $banks = ['BRI', 'BCA', 'DANA', 'Bank Jago', 'Bank Mandiri', 'BNI', 'OVO', 'GoPay', 'LinkAja', 'ShopeePay', 'CIMB Niaga', 'Bank Permata', 'BSI'];
        $statuses = ['selesai', 'tolak'];

        // Clean up existing penarikans to avoid clutter
        DB::table('penarikans')->truncate();

        for ($i = 1; $i <= 20; $i++) {
            $nasabah = $nasabahs->random();
            $status = $statuses[array_rand($statuses)];
            $bank = $banks[array_rand($banks)];
            $jumlah = rand(5, 50) * 10000; // Rp 50,000 to Rp 500,000

            $createdDate = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            $ketStatus = null;
            $buktiTf = null;
            $assignedPetugas = null;

            if ($status === 'selesai') {
                $ketStatus = 'Transfer berhasil dikirim ke rekening tujuan';
                $buktiTf = 'bukti_tf_' . $i . '.png';
                $assignedPetugas = $petugasId;
            } elseif ($status === 'tolak') {
                $ketStatus = 'Nomor rekening tidak valid atau saldo tidak mencukupi';
                $assignedPetugas = $petugasId;
            } else {
                $ketStatus = 'Menunggu konfirmasi petugas';
            }

            Penarikan::create([
                'jumlah' => $jumlah,
                'status' => $status,
                'ket_status' => $ketStatus,
                'nasabah_id' => $nasabah->nasabah_id,
                'bukti_tf' => $buktiTf,
                'no_rekening' => rand(1000000000, 9999999999),
                'nama_bank' => $bank,
                'nama_rek' => strtoupper($nasabah->nama),
                'petugas_id' => $assignedPetugas,
                'created_at' => $createdDate,
                'updated_at' => $createdDate,
            ]);
        }
    }
}
