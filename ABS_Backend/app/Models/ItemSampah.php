<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSampah extends Model
{
    protected $primaryKey = 'item_id';

    protected $fillable = [
        'nama',
        'harga_beli',
        'harga_jual',
        'diskon',
        'kategori_id',
        'foto',
        'active',
    ];

    public function sampah()
    {
        return $this->hasMany(Sampah::class,'item_id','item_id');
    }

    public function kategoriSampah()
    {
        return $this->belongsTo(KategoriSampah::class,'kategori_id','kategori_id');
    }
}
