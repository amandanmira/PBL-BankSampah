<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjemputan extends Model
{
    protected $primaryKey = 'penjemputan_id';

    protected $fillable = [
        'deskripsi',
        'alamat',
        'foto',
        'jadwal',
        'estimasi_berat',
        'rentang_hari',
        'rentan_waktu',
        'status',
        'ket_status',
        'tukang_id',
        'nasabah_id',
        'petugas_id',
        'gudang_id',
    ];

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class,'nasabah_id','nasabah_id');
    }

    public function tukang()
    {
        return $this->belongsTo(Tukang::class,'tukang_id','tukang_id');
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class,'petugas_id','petugas_id');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class,'gudang_id','gudang_id');
    }

    public function penimbangan()
    {
        return $this->hasMany(Penimbangan::class,'penjemputan_id','penjemputan_id');
    }

    public function detailPenjemputan()
    {
        return $this->hasMany(DetailPenjemputan::class,'penjemputan_id','penjemputan_id');
    }

    public function getFotoAttribute($value)
    {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
        return $value ? [$value] : [];
    }

    public function setFotoAttribute($value)
    {
        $this->attributes['foto'] = is_array($value) ? json_encode($value) : $value;
    }
}
