<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiNasabah extends Model
{
    protected $primaryKey = 'transaksi_id';

    protected $fillable = [
        'tipe_transaksi',
        'berat_total',
        'harga_total',
        'tanggal',
        'status',
        'ket_status',
        'nasabah_id'
    ];
}
