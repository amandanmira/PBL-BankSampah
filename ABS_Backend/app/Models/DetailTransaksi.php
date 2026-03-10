<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $primaryKey = 'detail_id';

    protected $fillable = [
        'harga',
        'berat',
        'kategori_id',
        'transaksi_id'
    ];

    public function sampah()
    {
        return $this->belongsTo(KategoriSampah::class,'sampah_id','sampah_id');
    }

    public function transaksiPengepul()
    {
        return $this->belongsTo(TransaksiPengepul::class,'transaksi_id','transaksi_id');
    }
}
