<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenjemputan extends Model
{
    protected $primaryKey = 'detail_id';

    protected $fillable = [
        'sampah_id',
        'penjemputan_id'
    ];

    public function sampah()
    {
        return $this->belongsTo(Sampah::class,'sampah_id','sampah_id');
    }

    public function transaksiPengepul()
    {
        return $this->belongsTo(Penjemputan::class,'penjemputan_id','penjemputan_id');
    }
}
