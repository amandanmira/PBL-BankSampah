<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Petugas extends Model
{
    use HasApiTokens;

    protected $primaryKey = 'petugas_id';

    protected $fillable = [
        'nama',
        'username',
        'email',
        'password'
    ];

    public function penjemputan()
    {
        return $this->hasMany(Penjemputan::class,'petugas_id','petugas_id');
    }

    public function transaksiNasabah()
    {
        return $this->hasMany(TransaksiNasabah::class,'petugas_id','petugas_id');
    }

    public function berita()
    {
        return $this->hasMany(Berita::class,'petugas_id','petugas_id');
    }
}
