<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiNasabah extends Model
{
    protected $primaryKey = 'transaksi_id';

    protected $fillable = [
        'tipe_transaksi',
        'berat_total',
        'harga_total',
        'tanggal',
        'status',
        'ket_status',
        'nasabah_id'
    ];

    public function penimbangan()
    {
        return $this->hasMany(Penimbangan::class,'transaksi_id','transaksi_id');
    }

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class,'nasabah_id','nasabah_id');
    }
}
