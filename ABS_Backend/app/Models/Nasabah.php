<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Nasabah extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $primaryKey = 'nasabah_id';

    protected $fillable = [
        'username',
        'email',
        'password',
        'nama',
        'alamat',
        'gmap',
        'no_telp',
        'verification_token',
        'status',
        'ket_status',
        'no_rekening',
        'nama_bank',
        'nama_rek',
        'saldo',
    ];

    protected $appends = ['saldo_tersedia', 'completion_percentage'];

    public function getSaldoTersediaAttribute()
    {
        $pendingPenarikan = $this->penarikan()->where('status', 'pending')->sum('jumlah');
        return $this->saldo - $pendingPenarikan;
    }

    public function getCompletionPercentageAttribute()
    {
        $fields = [
            'username', 'nama', 'email', 'no_telp', 
            'alamat', 'nama_bank', 'no_rekening', 'nama_rek'
        ];
        
        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }
        
        return round(($completed / count($fields)) * 100);
    }

    public function penjemputan()
    {
        return $this->hasMany(Penjemputan::class,'nasabah_id','nasabah_id');
    }

    public function penarikan()
    {
        return $this->hasMany(Penarikan::class,'nasabah_id','nasabah_id');
    }

    public function penimbangan()
    {
        return $this->hasMany(Penimbangan::class,'nasabah_id','nasabah_id');
    }
}
