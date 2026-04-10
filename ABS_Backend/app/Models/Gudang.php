<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
    protected $primaryKey = 'gudang_id';

    protected $fillable = [
        'alamat',
        'kapasitas',
        'active',
    ];

    public function penjemputan()
    {
        return $this->hasMany(Penjemputan::class,'gudang_id','gudang_id');
    }

    public function tukang()
    {
        return $this->hasMany(Tukang::class,'gudang_id','gudang_id');
    }

    public function sampah()
    {
        return $this->hasMany(Sampah::class,'gudang_id','gudang_id');
    }

    public function petugas()
    {
        return $this->hasMany(Petugas::class,'gudang_id','gudang_id');
    }

    public function penimbangan()
    {
        return $this->hasMany(Penimbangan::class,'gudang_id','gudang_id');
    }
}
