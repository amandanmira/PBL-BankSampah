<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tukang extends Model
{
    protected $primaryKey = 'tukang_id';

    protected $fillable = [
        'nama',
        'no_telp'
    ];

    public function penjemputan()
    {
        return $this->hasMany(Penjemputan::class,'tukang_id','tukang_id');
    }

    public function penimbangan()
    {
        return $this->hasMany(Penimbangan::class,'tukang_id','tukang_id');
    }
}
