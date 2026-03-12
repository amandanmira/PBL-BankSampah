<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Manager extends Model
{
    use HasApiTokens;

    protected $primaryKey = 'manager_id';

    protected $fillable = [
        'nama',
        'username',
        'email',
        'password'
    ];
}
