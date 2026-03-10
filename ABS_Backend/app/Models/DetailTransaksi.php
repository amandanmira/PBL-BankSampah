<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $primaryKey = 'detail_id';

    protected $fillable = [
        'harga',
        'berat',
        'sampah_id',
        'transaksi_id'
    ];
}
