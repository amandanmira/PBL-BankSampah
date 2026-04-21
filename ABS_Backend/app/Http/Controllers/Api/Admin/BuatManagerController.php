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
            'username' => 'required|max:20|unique:managers,username',
            'email' => 'required|email|max:50|unique:managers,email',
            'password' => 'required|min:8',
        ]);

        $manager = Manager::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'aktif',
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'data' => $manager
        ]);
    }
}
