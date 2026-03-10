<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nasabah extends Model
{
    protected $primaryKey = 'nasabah_id';

    protected $fillable = [
        'username',
        'email',
        'password',
        'nama_nasabah',
        'alamat',
        'gmap',
        'no_telp',
        'status',
        'ket_status',
        'no_rekening',
        'nama_bank'
    ];
}
