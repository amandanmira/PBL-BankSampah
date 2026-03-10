<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriSampah extends Model
{
    protected $primaryKey = 'sampah_id';

    protected $fillable = [
        'nama',
        'satuan_berat',
        'harga_beli',
        'harga_jual',
        'diskon',
        'stok'
    ];
}
