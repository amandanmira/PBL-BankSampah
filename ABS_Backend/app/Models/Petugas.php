<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Petugas extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $primaryKey = 'petugas_id';

    protected $fillable = [
        'nama',
        'username',
        'email',
        'password',
        'gudang_id',
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

    public function gudang()
    {
        return $this->belongsTo(Gudang::class,'gudang_id','gudang_id');
    }
}
