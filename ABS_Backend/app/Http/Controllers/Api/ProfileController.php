<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Pengepul;
use App\Models\Nasabah;
use App\Models\Petugas;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function showPengepul(string $id)
    {
        $pengepul = Pengepul::findOrFail($id);
        return response()->json($pengepul);
    }

    public function updatePengepul(Request $request, string $id)
    {
        $pengepul = Pengepul::findOrFail($id);

        $validated = $request->validate([
            'nama'         => 'required|string|max:50',
            'username'     => [
                'required', 'string', 'max:20',
                Rule::unique('pengepuls')->ignore($pengepul->pengepul_id, 'pengepul_id')
            ],
            'no_telp'      => 'required|string|max:16',
            'nama_lembaga' => 'required|string|max:50',
            'alamat'       => 'required|string',
        ]);

        $pengepul->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data'    => $pengepul
        ]);
    }

    public function showNasabah(string $id)
    {
        $nasabah = Nasabah::findOrFail($id);
        return response()->json($nasabah);
    }

    public function updateNasabah(Request $request, string $id)
    {
        $nasabah = Nasabah::findOrFail($id);

        $validated = $request->validate([
            'nama'        => 'required|string|max:50',
            'username'    => [
                'required', 'string', 'max:20',
                Rule::unique('nasabahs')->ignore($nasabah->nasabah_id, 'nasabah_id')
            ],
            'no_telp'     => 'nullable|string|max:16',
            'alamat'      => 'nullable|string',
            'gmap'        => 'nullable|string',
            'nama_bank'   => 'nullable|string|in:BRI,BCA,DANA,Bank Jago,Bank Mandiri,BNI,OVO,GoPay,LinkAja,ShopeePay,CIMB Niaga,Bank Permata,BSI',
            'no_rekening' => 'nullable|string|max:20',
            'nama_rek'    => 'nullable|string|max:50',
        ]);

        $nasabah->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data'    => $nasabah
        ]);
    }

    public function updatePetugas(Request $request, string $id)
    {
        $petugas = Petugas::findOrFail($id);

        $validated = $request->validate([
            'nama'      => 'required|string|max:50',
            'username'  => [
                'required', 'string', 'max:20',
                Rule::unique('petugas')->ignore($petugas->petugas_id, 'petugas_id')
            ],
            'no_telp'   => 'required|string|max:16',
            'gudang_id' => 'required|exists:gudangs,gudang_id',
        ]);

        $petugas->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data'    => $petugas
        ]);
    }

    public function updateManager(Request $request, string $id)
    {
        $manager = Manager::findOrFail($id);

        $validated = $request->validate([
            'nama'     => 'required|string|max:50',
            'username' => [
                'required', 'string', 'max:20',
                Rule::unique('managers')->ignore($manager->manager_id, 'manager_id')
            ],
        ]);

        $manager->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data'    => $manager
        ]);
    }

    public function updatePassword(Request $request, string $id)
    {
        $request->validate([
            'password_current' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = Nasabah::findOrFail($id);

        if (!Hash::check($request->password_current, $user->password)) {
            return response()->json([
                'message' => 'Password saat ini tidak cocok'
            ], 400);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json(['message' => 'Password berhasil diperbarui']);
    }

    /**
     * Ubah password Pengepul — wajib verifikasi password lama terlebih dahulu.
     */
    public function updatePasswordPengepul(Request $request, string $id)
    {
        $request->validate([
            'password_current' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        $user = Pengepul::findOrFail($id);

        // Verifikasi password lama
        if (!Hash::check($request->password_current, $user->password)) {
            return response()->json([
                'message' => 'Password lama yang Anda masukkan tidak cocok'
            ], 400);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json(['message' => 'Password berhasil diperbarui']);
    }

    public function uploadKtp(Request $request, string $id)
    {
        $request->validate([
            'ktp' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $pengepul = Pengepul::findOrFail($id);

        if ($request->hasFile('ktp')) {
            $pathKtp = $request->file('ktp')->store('foto-ktp', 'public');
            $pengepul->ktp = $pathKtp;
            $pengepul->save();

            return response()->json([
                'message' => 'KTP berhasil diunggah',
                'data'    => $pengepul
            ]);
        }

        return response()->json(['message' => 'File tidak ditemukan'], 400);
    }

    public function uploadNpwp(Request $request, string $id)
    {
        $request->validate([
            'npwp' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $pengepul = Pengepul::findOrFail($id);

        if ($request->hasFile('npwp')) {
            $pathNpwp = $request->file('npwp')->store('foto-npwp', 'public');
            $pengepul->npwp = $pathNpwp;
            $pengepul->save();

            return response()->json([
                'message' => 'NPWP berhasil diunggah',
                'data'    => $pengepul
            ]);
        }

        return response()->json(['message' => 'File tidak ditemukan'], 400);
    }
}