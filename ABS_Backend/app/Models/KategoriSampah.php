<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriSampah extends Model
{
    protected $primaryKey = 'kategori_id';

    protected $fillable = [
        'nama',
        'harga_beli',
        'harga_jual',
        'diskon',
        'jenis_id',
    ];

    public function sampah()
    {
        return $this->hasMany(Sampah::class,'kategori_id','kategori_id');
    }

    public function jenisSampah()
    {
        return $this->belongsTo(JenisSampah::class,'jenis_id','jenis_id');
    }
}
