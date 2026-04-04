<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sampah extends Model
{
    protected $primaryKey = 'sampah_id';

    protected $fillable = [
        'stok',
        'item_id',
        'gudang_id',
    ];

    public function itemSampah()
    {
        return $this->belongsTo(ItemSampah::class,'item_id','item_id');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class,'gudang_id','gudang_id');
    }

    public function penimbangan()
    {
        return $this->hasMany(Penimbangan::class,'sampah_id','sampah_id');
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class,'sampah_id','sampah_id');
    }
}
