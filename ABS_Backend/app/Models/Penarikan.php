<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penarikan extends Model
{
    protected $primaryKey = 'penarikan_id';

    protected $fillable = [
        'jumlah',
        'status',
        'ket_status',
        'nasabah_id'
    ];

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class,'nasabah_id','nasabah_id');
    }
}
