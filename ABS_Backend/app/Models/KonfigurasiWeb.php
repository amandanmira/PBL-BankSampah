<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonfigurasiWeb extends Model
{
    protected $primaryKey = 'config_id';

    protected $fillable = [
        'logo',
        'quote',
        'instagram',
        'facebook',
        'linkedin',
        'youtube',
        'no_telp',
        'email',
        'lama_deadline',
        'alamat',
        'tentang',
    ];
}
