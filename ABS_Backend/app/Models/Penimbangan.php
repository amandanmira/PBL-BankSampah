<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penimbangan extends Model
{
    protected $primaryKey = 'penimbangan_id';

    protected $fillable = [
        'berat_timbang',
        'foto',
        'nasabah_id',
        'transaksi_id',
        'kategori_id',
        'tukang_id'
    ];

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class,'nasabah_id','nasabah_id');
    }

    public function transaksi()
    {
        return $this->belongsTo(TransaksiNasabah::class,'transaksi_id','transaksi_id');
    }

    public function sampah()
    {
        return $this->belongsTo(KategoriSampah::class,'sampah_id','sampah_id');
    }

    public function tukang()
    {
        return $this->belongsTo(Tukang::class,'tukang_id','tukang_id');
    }
}
