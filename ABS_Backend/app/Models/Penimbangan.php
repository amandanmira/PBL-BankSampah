<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penimbangan extends Model
{
    protected $primaryKey = 'penimbangan_id';

    protected $fillable = [
        'berat_timbang',
        'foto',
        'nasabah_id',
        'transaksi_id',
        'sampah_id',
        'tukang_id'
    ];
}
