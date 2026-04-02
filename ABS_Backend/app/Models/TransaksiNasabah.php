<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiNasabah extends Model
{
    protected $primaryKey = 'transaksi_id';

    protected $fillable = [
        'tipe_transaksi',
        'tanggal',
        'status',
        'ket_status',
        'petugas_id',
    ];

    public function penimbangan()
    {
        return $this->hasMany(Penimbangan::class,'transaksi_id','transaksi_id');
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class,'petugas_id','petugas_id');
    }
}
