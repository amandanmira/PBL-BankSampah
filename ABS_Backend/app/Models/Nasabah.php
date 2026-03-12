<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Nasabah extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $primaryKey = 'nasabah_id';

    protected $fillable = [
        'username',
        'email',
        'password',
        'nama',
        'alamat',
        'gmap',
        'no_telp',
        'status',
        'ket_status',
        'no_rekening',
        'nama_bank'
    ];

    public function penjemputan()
    {
        return $this->hasMany(Penjemputan::class,'nasabah_id','nasabah_id');
    }

    public function penarikan()
    {
        return $this->hasMany(Penarikan::class,'nasabah_id','nasabah_id');
    }

    public function penimbangan()
    {
        return $this->hasMany(Penimbangan::class,'nasabah_id','nasabah_id');
    }

    public function transaksiNasabah()
    {
        return $this->hasMany(TransaksiNasabah::class,'nasabah_id','nasabah_id');
    }
}
