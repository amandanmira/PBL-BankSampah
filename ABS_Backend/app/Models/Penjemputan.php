<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjemputan extends Model
{
    protected $primaryKey = 'penjemputan_id';

    protected $fillable = [
        'deskripsi',
        'alamat',
        'foto',
        'jadwal',
        'status',
        'ket_status',
        'tukang_id',
        'nasabah_id',
        'petugas_id'
    ];

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class,'nasabah_id','nasabah_id');
    }

    public function tukang()
    {
        return $this->belongsTo(Tukang::class,'tukang_id','tukang_id');
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class,'petugas_id','petugas_id');
    }
}
