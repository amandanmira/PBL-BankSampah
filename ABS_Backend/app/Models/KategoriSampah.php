<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriSampah extends Model
{
    protected $primaryKey = 'kategori_id';

    protected $fillable = [
        'nama',
    ];

    public function itemSampah()
    {
        return $this->hasMany(ItemSampah::class,'kategori_id','kategori_id');
    }
}
