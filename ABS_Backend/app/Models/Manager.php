<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Manager extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $primaryKey = 'manager_id';

    protected $fillable = [
        'nama',
        'username',
        'email',
        'password'
    ];
}
