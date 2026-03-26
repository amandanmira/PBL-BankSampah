<?php

use App\Http\Controllers\Api\Admin\AksiAdminController;
use App\Http\Controllers\Api\Admin\BuatPetugasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Gunakan Controller yang benar
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\KelolaAkunController;
use App\Http\Controllers\Api\Admin\GudangController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Route Admin
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/buatPetugas', [BuatPetugasController::class, 'buatPetugas']);
    Route::get('petugas', [KelolaAkunController::class, 'indexPetugas']);
    Route::get('petugas/{petuga}', [KelolaAkunController::class, 'showPetugas']);
    Route::put('petugas/{petuga}/deactivate', [AksiAdminController::class, 'deactivatePetugas']);
    Route::put('petugas/{petuga}/activate', [AksiAdminController::class, 'activatePetugas']);
    Route::get('pengepul', [KelolaAkunController::class, 'indexPengepul']);
    Route::get('pengepul/{pengepul}', [KelolaAkunController::class, 'showPengepul']);
    Route::get('nasabah', [KelolaAkunController::class, 'indexNasabah']);
    Route::get('tukang', [KelolaAkunController::class, 'indexTukang']);
    Route::get('admin', [KelolaAkunController::class, 'indexAdmin']);
    Route::get('manager', [KelolaAkunController::class, 'indexManager']);

    // API Kelola
    Route::apiResource('gudang', GudangController::class);
});
