<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tukang extends Model
{
    protected $primaryKey = 'tukang_id';

    protected $fillable = [
        'nama',
        'no_telp'
    ];
}
