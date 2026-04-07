<?php

use App\Http\Controllers\Api\Admin\AksiAdminController;
use App\Http\Controllers\Api\Admin\BuatPetugasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Gunakan Controller yang benar
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;

use App\Http\Controllers\Api\Admin\KelolaAkunController;
use App\Http\Controllers\Api\Admin\GudangController;
use App\Http\Controllers\Api\Admin\SampahGudangController;
use App\Http\Controllers\Api\Admin\SampahController;
use App\Http\Controllers\Api\Petugas\BeritaController;

Route::get('verify-nasabah/{token}', [AuthController::class, 'verifyEmail']);
use App\Http\Controllers\Api\Admin\WebController;

// Nasabah
use App\Http\Controllers\Api\Nasabah\RequestPenjemputanController;
use App\Http\Controllers\Api\Nasabah\RequestPenarikanController;

// Pengepul
use App\Http\Controllers\Api\Pengepul\RequestPembelianController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::prefix('nasabah')->middleware(['auth:sanctum', 'role:nasabah'])->group(function () {
    Route::put('/edit-profile/{id}', [ProfileController::class, 'updateNasabah']);
    Route::get('/profile/{id}', [ProfileController::class, 'showNasabah']);

    Route::post('/request-penjemputan', [RequestPenjemputanController::class, 'store']);
    Route::post('/request-penarikan', [RequestPenarikanController::class, 'store']);
});

Route::prefix('pengepul')->middleware(['auth:sanctum', 'role:pengepul'])->group(function () {
    Route::put('/edit-profile/{id}', [ProfileController::class, 'updatePengepul']);
    Route::get('/profile/{id}', [ProfileController::class, 'showPengepul']);

    Route::get('/daftar-sampah', [RequestPembelianController::class, 'indexSampah']);
    Route::post('/request-pembelian', [RequestPembelianController::class, 'store']);
});

// Route Petugas
Route::prefix('petugas')->middleware(['auth:sanctum', 'role:petugas'])->group(function () {
    // CRUD Berita
    Route::get('/berita', [BeritaController::class, 'index']);
    Route::post('/berita', [BeritaController::class, 'store']);
    Route::get('/berita/{id}', [BeritaController::class, 'show']);
    Route::put('/berita/{id}', [BeritaController::class, 'update']);
    Route::delete('/berita/{id}', [BeritaController::class, 'destroy']);
    // Rute tambahan untuk handle update dengan file upload (thumbnail)
    Route::post('berita/{id}', [BeritaController::class, 'update']);
});

// Route Admin
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // API Kelola
    // Petugas
    Route::post('/buatPetugas', [BuatPetugasController::class, 'buatPetugas']);
    Route::get('petugas', [KelolaAkunController::class, 'indexPetugas']);
    Route::get('petugas/{petuga}', [KelolaAkunController::class, 'showPetugas']);
    Route::put('petugas/{petuga}/deactivate', [AksiAdminController::class, 'deactivatePetugas']);
    Route::put('petugas/{petuga}/activate', [AksiAdminController::class, 'activatePetugas']);

    // Pengepul
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

    // Gudang
    Route::apiResource('gudang', GudangController::class);

    Route::put('gudang/sampah/{id}', [SampahGudangController::class, 'update']);
    Route::delete('gudang/sampah/{id}', [SampahGudangController::class, 'destroy']);

    // Sampah
    Route::get('/kategori-sampah', [SampahController::class, 'index']);
    Route::post('/kategori-sampah', [SampahController::class, 'store']);
    Route::get('/kategori-sampah/{id}', [SampahController::class, 'show']);
    Route::put('/kategori-sampah/{id}', [SampahController::class, 'update']);
    Route::delete('/kategori-sampah/{id}', [SampahController::class, 'destroy']);

    // Item Sampah
    Route::get('/item-sampah', [SampahController::class, 'indexItem']);
    Route::delete('/item-sampah/{id}', [SampahController::class, 'destroyItem']);

    // Konfigurasi Web
    Route::get('/web-config', [WebController::class, 'show']);
    Route::put('/web-config', [WebController::class, 'update']);
});
