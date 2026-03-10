<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiPengepul extends Model
{
    protected $primaryKey = 'transaksi_id';

    protected $fillable = [
        'status',
        'ket_status',
        'harga_total',
        'deadline',
        'berat_total',
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
}
