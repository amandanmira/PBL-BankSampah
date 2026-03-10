<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $primaryKey = 'berita_id';

    protected $fillable = [
        'judul',
        'thumbnail',
        'isi',
        'tanggal',
        'petugas_id'
    ];

    public function petugas()
    {
        return $this->belongsTo(Petugas::class,'petugas_id','petugas_id');
    }
}
