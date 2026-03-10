<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriSampah extends Model
{
    protected $primaryKey = 'kategori_id';

    protected $fillable = [
        'nama',
        'satuan_berat',
        'harga_beli',
        'harga_jual',
        'diskon',
        'stok'
    ];

    public function penimbangan()
    {
        return $this->hasMany(Penimbangan::class,'sampah_id','sampah_id');
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class,'sampah_id','sampah_id');
    }
}
