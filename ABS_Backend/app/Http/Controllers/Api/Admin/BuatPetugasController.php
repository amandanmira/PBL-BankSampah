<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BuatPetugasController extends Controller
{
    public function buatPetugas(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:50',
            'no_telp' => 'required|string|max:50',
            'username' => 'required|max:20|unique:petugas,username',
            'email' => 'required|email|max:50|unique:petugas,email',
            'password' => 'required|min:8',
            'gudang_id' => 'required',
        ]);

        $petugas = Petugas::create([
            'nama' => $request->nama,
            'no_telp' => $request->no_telp,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gudang_id' => $request->gudang_id,
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'data' => $petugas
        ]);
    }
}
