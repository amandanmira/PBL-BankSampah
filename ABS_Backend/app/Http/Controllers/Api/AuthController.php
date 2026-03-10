<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Nasabah;

class AuthController extends Controller
{
    public function registerNasabah(Request $request)
    {
        $request->validate([
            'username' => 'required|max:20|unique:nasabahs',
            'email' => 'required|email|max:50|unique:nasabahs',
            'password' => 'required|min:6',
            'nama_nasabah' => 'required|max:50'
        ]);

        $nasabah = Nasabah::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'nama_nasabah' => $request->nama_nasabah,
            'alamat' => $request->alamat,
            'gmap' => $request->gmap,
            'no_telp' => $request->no_telp,
            'status' => 'aktif'
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'data' => $nasabah
        ]);
    }
}
