<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    protected $primaryKey = 'manager_id';

    protected $fillable = [
        'nama',
        'username',
        'email',
        'password'
    ];
}
