<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiNasabah extends Model
{
    protected $primaryKey = 'transaksi_id';

    protected $fillable = [
        'tipe_transaksi',
        'berat',
        'subtotal',
        'tanggal',
        'status',
        'ket_status',
        'nasabah_id'
    ];
}
