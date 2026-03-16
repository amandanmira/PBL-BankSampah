<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Gunakan Controller yang benar
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\KelolaAkunController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register-nasabah', [AuthController::class, 'registerNasabah']);
Route::post('/register-pengepul', [AuthController::class, 'registerPengepul']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Route Admin
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('petugas', [KelolaAkunController::class, 'indexPetugas']);
    Route::get('petugas/{petuga}', [KelolaAkunController::class, 'showPetugas']);
});
