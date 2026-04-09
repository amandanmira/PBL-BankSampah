<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tukang extends Model
{
    protected $primaryKey = 'tukang_id';

    protected $fillable = [
        'nama',
        'no_telp',
        'gudang_id',
        'foto',
        'active',
    ];

    public function penjemputan()
    {
        return $this->hasMany(Penjemputan::class,'tukang_id','tukang_id');
    }

    public function penimbangan()
    {
        return $this->hasMany(Penimbangan::class,'tukang_id','tukang_id');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class,'gudang_id','gudang_id');
    }
}
