<?php

namespace App\Http\Controllers\Api\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Penjemputan;
use Illuminate\Http\Request;

class RiwayatPenjemputanController extends Controller
{
    public function riwayatPenjemputan()
    {
        $riwayat = Penjemputan::with('petugas')->latest()->get();
        return response()->json(['data' => $riwayat], 200);
    }
}
