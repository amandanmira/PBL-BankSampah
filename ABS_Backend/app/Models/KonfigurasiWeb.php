<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonfigurasiWeb extends Model
{
    protected $primaryKey = 'config_id';

    protected $fillable = [
        'logo',
        'lama_deadline',
        'alamat'
    ];
}
