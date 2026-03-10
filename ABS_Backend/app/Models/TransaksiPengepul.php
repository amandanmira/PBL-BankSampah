<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiPengepul extends Model
{
    protected $primaryKey = 'transaksi_id';

    protected $fillable = [
        'status',
        'ket_status',
        'subtotal',
        'deadline',
        'berat',
        'pengepul_id'
    ];
}
