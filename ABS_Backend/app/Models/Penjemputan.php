<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjemputan extends Model
{
    protected $primaryKey = 'penjemputan_id';

    protected $fillable = [
        'deskripsi',
        'alamat',
        'foto',
        'jadwal',
        'status',
        'ket_status',
        'tukang_id',
        'nasabah_id',
        'petugas_id'
    ];
}
