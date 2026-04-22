<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordMail;

// Models
use App\Models\Admin;
use App\Models\Manager;
use App\Models\Petugas;
use App\Models\Pengepul;
use App\Models\Nasabah;

class PasswordResetController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->email;
        $user = $this->findUserByEmail($email);

        if (!$user) {
            return response()->json([
                'message' => 'Kami tidak dapat menemukan pengguna dengan alamat email tersebut.'
            ], 404);
        }

        // Generate token
        $token = Str::random(64);

        // Store token in database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $token,
                'created_at' => now()
            ]
        );

        // Send Email
        Mail::to($email)->send(new ResetPasswordMail($token, $email, $user['name']));

        return response()->json([
            'message' => 'Link reset password telah dikirim ke email Anda.'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $resetData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetData) {
            return response()->json(['message' => 'Token reset password tidak valid atau sudah kadaluarsa.'], 422);
        }

        // Check if token is expired (e.g., 60 minutes)
        if (now()->diffInMinutes($resetData->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Token reset password sudah kadaluarsa.'], 422);
        }

        $userData = $this->findUserByEmail($request->email);

        if (!$userData) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        // Update password in the correct model
        $user = $userData['model']::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password Anda berhasil diperbarui.']);
    }

    private function findUserByEmail($email)
    {
        // Check Admin
        $admin = Admin::where('email', $email)->first();
        if ($admin) return ['model' => Admin::class, 'name' => $admin->nama ?? $admin->username ?? 'Admin'];

        // Check Manager
        $manager = Manager::where('email', $email)->first();
        if ($manager) return ['model' => Manager::class, 'name' => $manager->nama ?? $manager->username ?? 'Manager'];

        // Check Petugas
        $petugas = Petugas::where('email', $email)->first();
        if ($petugas) return ['model' => Petugas::class, 'name' => $petugas->nama ?? $petugas->username ?? 'Petugas'];

        // Check Pengepul
        $pengepul = Pengepul::where('email', $email)->first();
        if ($pengepul) return ['model' => Pengepul::class, 'name' => $pengepul->nama ?? $pengepul->username ?? 'Pengepul'];

        // Check Nasabah
        $nasabah = Nasabah::where('email', $email)->first();
        if ($nasabah) return ['model' => Nasabah::class, 'name' => $nasabah->nama ?? $nasabah->username ?? 'Nasabah'];

        return null;
    }
}
