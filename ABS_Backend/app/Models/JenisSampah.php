<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSampah extends Model
{
    protected $primaryKey = 'jenis_id';

    protected $fillable = [
        'nama',
        'stok_jenis'
    ];

    public function kategoriSampah()
    {
        return $this->hasMany(KategoriSampah::class,'jenis_id','jenis_id');
    }
}
