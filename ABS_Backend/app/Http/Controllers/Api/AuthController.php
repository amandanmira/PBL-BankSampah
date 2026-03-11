<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// Models
use App\Models\Nasabah;
use App\Models\Admin;
use App\Models\Petugas;
use App\Models\Manager;

class AuthController extends Controller
{
    public function registerNasabah(Request $request)
    {
        $request->validate([
            'username' => 'required|max:20|unique:nasabahs',
            'email' => 'required|email|max:50|unique:nasabahs',
            'password' => 'required|min:8',
            'nama_nasabah' => 'required|max:50'
        ]);

        $nasabah = Nasabah::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:50',
            'password' => 'required|min:8',
        ]);

        $email = $request->email;
        $password = $request->password;

        $admin = Admin::where('email', $email)->first();
        if ($admin && Hash::check($password, $admin->password)) {
            return response()->json([
                'role' => 'admin',
                'user' => $admin
            ]);
        }

        $manager = Manager::where('email', $email)->first();
        if ($manager && Hash::check($password, $manager->password)) {
            return response()->json([
                'role' => 'manager',
                'user' => $manager
            ]);
        }

        $petugas = Petugas::where('email', $email)->first();
        if ($petugas && Hash::check($password, $petugas->password)) {
            return response()->json([
                'role' => 'petugas',
                'user' => $petugas
            ]);
        }

        $nasabah = Nasabah::where('email', $email)->first();
        if ($nasabah && Hash::check($password, $nasabah->password)) {
            return response()->json([
                'role' => 'nasabah',
                'user' => $nasabah
            ]);
        }

        return response()->json([
            'message' => 'Email atau password salah'
        ], 401);
    }
}
