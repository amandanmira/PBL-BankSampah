<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengepul extends Model
{
    protected $primaryKey = 'pengepul_id';

    protected $fillable = [
        'nama',
        'username',
        'email',
        'password',
        'no_telp',
        'nama_lembaga',
        'alamat',
        'status',
        'ket_status'
    ];

    public function transaksiPengepul()
    {
        return $this->hasMany(TransaksiPengepul::class,'pengepul_id','pengepul_id');
    }
}
