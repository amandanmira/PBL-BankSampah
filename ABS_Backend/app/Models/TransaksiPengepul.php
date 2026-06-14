<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiPengepul extends Model
{
    protected $primaryKey = 'transaksi_id';

    protected $fillable = [
        'status',
        'ket_status',
        'petugas_id',
        'deadline',
        'bukti_transfer',
        'pengepul_id'
    ];

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class,'transaksi_id','transaksi_id');
    }

    public function pengepul()
    {
        return $this->belongsTo(Pengepul::class,'pengepul_id','pengepul_id');
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'petugas_id', 'petugas_id');
    }

    public static function cancelExpiredTransactions($pengepul_id = null, $transaksi_id = null)
    {
        $query = self::with('detailTransaksi')
            ->where('status', 'proses')
            ->where('deadline', '<', now())
            ->where(function ($q) {
                $q->whereNull('bukti_transfer')
                  ->orWhere('bukti_transfer', '');
            });

        if ($pengepul_id) {
            $query->where('pengepul_id', $pengepul_id);
        }

        if ($transaksi_id) {
            $query->where('transaksi_id', $transaksi_id);
        }

        $expired = $query->get();

        foreach ($expired as $t) {
            // Restore stock
            foreach ($t->detailTransaksi as $d) {
                $sampah = Sampah::find($d->sampah_id);
                if ($sampah) {
                    $sampah->update([
                        'stok' => $sampah->stok + $d->berat,
                    ]);
                }
            }

            // Update status & ket_status
            $t->update([
                'status' => 'tolak',
                'ket_status' => 'Dibatalkan otomatis oleh sistem (melewati batas waktu pembayaran)'
            ]);

            // Update detail_transaksis status
            $t->detailTransaksi()->update(['status' => 'tolak']);
        }
    }
}
