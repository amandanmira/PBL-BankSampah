<?php

use App\Http\Controllers\Api\Admin\AksiAdminController;
use App\Http\Controllers\Api\Admin\BuatPetugasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Gunakan Controller yang benar
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\KelolaAkunController;



Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Route Admin
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {

    //Petugas
    Route::post('/buatPetugas', [BuatPetugasController::class, 'buatPetugas']);
    Route::get('petugas', [KelolaAkunController::class, 'indexPetugas']);
    Route::get('petugas/{petuga}', [KelolaAkunController::class, 'showPetugas']);
    Route::put('petugas/{petuga}/deactivate', [AksiAdminController::class, 'deactivatePetugas']);
    Route::put('petugas/{petuga}/activate', [AksiAdminController::class, 'activatePetugas']);

    //pengepul
    Route::get('pengepul', [KelolaAkunController::class, 'indexPengepul']);
    Route::get('pengepul/{pengepul}', [KelolaAkunController::class, 'showPengepul']);
    Route::put('pengepul/{pengepul}/terima', [AksiAdminController::class, 'terimaPengepul']);
    Route::put('pengepul/{pengepul}/tolak', [AksiAdminController::class, 'tolakPengepul']);
    Route::put('pengepul/{pengepul}/deactivate', [AksiAdminController::class, 'deactivatePengepul']);
    Route::put('pengepul/{pengepul}/activate', [AksiAdminController::class, 'activatePengepul']);


    Route::get('nasabah', [KelolaAkunController::class, 'indexNasabah']);
    Route::put('nasabah/{nasabah}/deactivate', [AksiAdminController::class, 'deactivateNasabah']);
    Route::put('nasabah/{nasabah}/activate', [AksiAdminController::class, 'activateNasabah']);
    Route::get('tukang', [KelolaAkunController::class, 'indexTukang']);
    Route::get('admin', [KelolaAkunController::class, 'indexAdmin']);
    Route::get('manager', [KelolaAkunController::class, 'indexManager']);
});
