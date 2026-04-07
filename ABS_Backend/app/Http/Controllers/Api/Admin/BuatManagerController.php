<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BuatManagerController extends Controller
{
    public function buatManager(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:50',
            'username' => 'required|max:20|unique:petugas,username',
            'email' => 'required|email|max:50|unique:petugas,email',
            'password' => 'required|min:8',
        ]);

        $manager = Manager::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'data' => $manager
        ]);
    }
}
