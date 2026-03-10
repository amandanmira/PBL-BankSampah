<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    protected $primaryKey = 'petugas_id';

    protected $fillable = [
        'nama',
        'email',
        'password'
    ];
}
