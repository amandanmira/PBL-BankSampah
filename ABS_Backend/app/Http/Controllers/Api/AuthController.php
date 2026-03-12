<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// Models
use App\Models\Nasabah;
use App\Models\Pengepul;
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
            'nama' => 'required|max:50'
        ]);

        $nasabah = Nasabah::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nama' => $request->nama,
            'status' => 'aktif'
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'data' => $nasabah
        ]);
    }

    public function registerPengepul(Request $request)
    {
        $request->validate([
            'username' => 'required|max:20|unique:nasabahs',
            'email' => 'required|email|max:50|unique:nasabahs',
            'password' => 'required|min:8',
            'nama' => 'required|max:50',
            'no_telp' => 'required|max:16',
            'nama_lembaga' => 'required|max:50',
            'alamat' => 'required',
        ]);

        $pengepul = Pengepul::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nama' => $request->nama,
            'no_telp' => $request->no_telp,
            'nama_lembaga' => $request->nama_lembaga,
            'alamat' => $request->alamat,
            'status' => 'aktif'
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'data' => $pengepul
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
            $token = $admin->createToken('api-token')->plainTextToken;

            return response()->json([
                'role' => 'admin',
                'user' => $admin,
                'token' => $token
            ]);
        }

        $manager = Manager::where('email', $email)->first();
        if ($manager && Hash::check($password, $manager->password)) {
            $token = $manager->createToken('api-token')->plainTextToken;

            return response()->json([
                'role' => 'manager',
                'user' => $manager,
                'token' => $token
            ]);
        }

        $petugas = Petugas::where('email', $email)->first();
        if ($petugas && Hash::check($password, $petugas->password)) {
            $token = $petugas->createToken('api-token')->plainTextToken;

            return response()->json([
                'role' => 'petugas',
                'user' => $petugas,
                'token' => $token
            ]);
        }

        $pengepul = Pengepul::where('email', $email)->first();
        if ($pengepul && Hash::check($password, $pengepul->password)) {
            $token = $pengepul->createToken('api-token')->plainTextToken;

            return response()->json([
                'role' => 'pengepul',
                'user' => $pengepul,
                'token' => $token
            ]);
        }

        $nasabah = Nasabah::where('email', $email)->first();
        if ($nasabah && Hash::check($password, $nasabah->password)) {
            $token = $nasabah->createToken('api-token')->plainTextToken;

            return response()->json([
                'role' => 'nasabah',
                'user' => $nasabah,
                'token' => $token
            ]);
        }

        return response()->json([
            'message' => 'Email atau password salah'
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}
