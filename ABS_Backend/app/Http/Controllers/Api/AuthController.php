<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifikasiEmailNasabah;

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
        ], [
            'email.unique' => 'Email sudah terpakai',
            'username.unique' => 'Username sudah terpakai'
        ]);

        $nasabah = Nasabah::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nama' => $request->nama,
            'status' => 'pending'
        ]);

        // Buat dan simpan OTP verifikasi (6 digit angka)
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $nasabah->verification_token = $otp;
        $nasabah->save();

        // Kirim email verifikasi
        Mail::to($nasabah->email)->send(new VerifikasiEmailNasabah($nasabah, $otp));

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan cek email Anda untuk kode verifikasi.',
            'data' => [
                'email' => $nasabah->email
            ]
        ]);
    }

    public function registerPengepul(Request $request)
    {
        $request->validate([
            'username' => 'required|max:20|unique:pengepuls',
            'email' => 'required|email|max:50|unique:pengepuls',
            'password' => 'required|min:8',
            'nama' => 'required|max:50',
            'no_telp' => 'required|max:16',
            'nama_lembaga' => 'required|max:50',
            'alamat' => 'required',
            'ktp' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'npwp' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'email.unique' => 'Email sudah terpakai',
            'username.unique' => 'Username sudah terpakai'
        ]);

        $pathKtp = $request->file("ktp")
                        ->store('foto-ktp', 'public');
        $pathNpwp = $request->file("npwp")
                        ->store('foto-npwp', 'public');

        $ktp = $pathKtp;
        $npwp = $pathNpwp;

        $pengepul = Pengepul::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nama' => $request->nama,
            'no_telp' => $request->no_telp,
            'nama_lembaga' => $request->nama_lembaga,
            'alamat' => $request->alamat,
            'ktp' => $ktp,
            'npwp' => $npwp,
            'status' => 'pending'
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
            // Tambahkan pengecekan ini
            if (!$petugas->active) {
                return response()->json([
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi admin.'
                ], 403); // 403 Forbidden
            }

            $token = $petugas->createToken('api-token')->plainTextToken;

            return response()->json([
                'role' => 'petugas',
                'user' => $petugas,
                'token' => $token
            ]);
        }

        $pengepul = Pengepul::where('email', $email)->first();
        if ($pengepul && Hash::check($password, $pengepul->password)) {

            if ($pengepul->status !== 'aktif') {
                return response()->json([
                    'message' => 'Akun Anda belum aktif atau sedang dinonaktifkan. Silakan hubungi admin.'
                ], 403);
            }

            $token = $pengepul->createToken('api-token')->plainTextToken;

            return response()->json([
                'role' => 'pengepul',
                'user' => $pengepul,
                'token' => $token
            ]);
        }

        $nasabah = Nasabah::where('email', $email)->first();
        if ($nasabah && Hash::check($password, $nasabah->password)) {

            if ($nasabah->status !== 'aktif') {
                return response()->json([
                    'message' => 'Akun Anda belum aktif atau sedang dinonaktifkan. Silakan hubungi admin.'
                ], 403);
            }

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

    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:50',
        ]);

        $email = $request->email;

        $exists = Nasabah::where('email', $email)->exists() ||
            Pengepul::where('email', $email)->exists() ||
            Admin::where('email', $email)->exists() ||
            Petugas::where('email', $email)->exists() ||
            Manager::where('email', $email)->exists();

        return response()->json(['used' => $exists]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    public function verifyEmail($token)
    {
        $nasabah = Nasabah::where('verification_token', $token)->first();

        if (!$nasabah) {
            return response()->json(['message' => 'Kode verifikasi tidak valid.'], 404);
        }

        $nasabah->status = 'aktif';
        $nasabah->verification_token = null; // Hapus token setelah verifikasi
        $nasabah->save();

        return response()->json(['message' => 'Email berhasil diverifikasi. Akun Anda sekarang aktif.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6'
        ]);

        $nasabah = Nasabah::where('email', $request->email)
            ->where('verification_token', $request->otp)
            ->first();

        if (!$nasabah) {
            return response()->json(['message' => 'Kode verifikasi tidak valid.'], 422);
        }

        $nasabah->status = 'aktif';
        $nasabah->verification_token = null;
        $nasabah->save();

        return response()->json(['message' => 'Email berhasil diverifikasi. Akun Anda sekarang aktif.']);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $nasabah = Nasabah::where('email', $request->email)->first();

        if (!$nasabah) {
            return response()->json(['message' => 'User tidak ditemukan.'], 44);
        }

        if ($nasabah->status === 'aktif') {
            return response()->json(['message' => 'Akun sudah aktif.'], 422);
        }

        // Generate new OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $nasabah->verification_token = $otp;
        $nasabah->save();

        // Send Email
        Mail::to($nasabah->email)->send(new VerifikasiEmailNasabah($nasabah, $otp));

        return response()->json(['message' => 'Kode verifikasi baru telah dikirim ke email Anda.']);
    }
}
