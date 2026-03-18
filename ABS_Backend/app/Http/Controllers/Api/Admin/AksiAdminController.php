<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Petugas;
use Illuminate\Http\Request;

class AksiAdminController extends Controller
{
    /**
     * Menonaktifkan petugas.
     */
    public function deactivatePetugas(Petugas $petuga)
    {
        $petuga->active = false;
        $petuga->save();

        return response()->json(['message' => 'Petugas berhasil dinonaktifkan'], 200);
    }

    /**
     * Mengaktifkan petugas.
     */
    public function activatePetugas(Petugas $petuga)
    {
        $petuga->active = true;
        $petuga->save();

        return response()->json(['message' => 'Petugas berhasil diaktifkan'], 200);
    }
}
