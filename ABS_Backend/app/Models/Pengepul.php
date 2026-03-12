<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Pengepul extends Authenticatable
{
    use HasApiTokens, Notifiable;

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
