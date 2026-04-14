<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Admin\AksiAdminController;
use App\Http\Controllers\Api\Admin\BuatManagerController;
use App\Http\Controllers\Api\Admin\BuatPetugasController;
use App\Http\Controllers\Api\Admin\GudangController;
use App\Http\Controllers\Api\Admin\KelolaAkunController;
use App\Http\Controllers\Api\Admin\SampahController;
use App\Http\Controllers\Api\Admin\WebController;
use App\Http\Controllers\Api\Admin\TukangController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Nasabah\RequestPenarikanController;
use App\Http\Controllers\Api\Nasabah\RequestPenjemputanController;
use App\Http\Controllers\Api\Petugas\BeritaController;
use App\Http\Controllers\Api\Petugas\KonfirmasiPenjemputanController;
use App\Http\Controllers\Api\ProfileController;

use App\Http\Controllers\Api\Admin\SampahGudangController;

// Pengepul
use App\Http\Controllers\Api\Pengepul\RequestPembelianController;
use App\Http\Controllers\Api\Petugas\KonfirmasiPenarikanController;
use App\Http\Controllers\Api\Petugas\PenimbanganController;
use App\Http\Controllers\Api\Petugas\RiwayatPenjemputanController;

Route::get('verify-nasabah/{token}', [AuthController::class, 'verifyEmail']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::prefix('nasabah')->middleware(['auth:sanctum', 'role:nasabah'])->group(function () {
    Route::put('/edit-profile/{id}', [ProfileController::class, 'updateNasabah']);
    Route::get('/profile/{id}', [ProfileController::class, 'showNasabah']);

    Route::get('/list-gudang', [GudangController::class, 'index']);
    Route::post('/request-penjemputan', [RequestPenjemputanController::class, 'store']);
    Route::post('/request-penarikan', [RequestPenarikanController::class, 'store']);
    Route::get('/penjemputan-nasabah', [KonfirmasiPenjemputanController::class, 'penjemputanNasabah']);
});

Route::prefix('manager')->middleware(['auth:sanctum', 'role:manager'])->group(function () {

    //Penarikan
    Route::get('/riwayat-penarikan', [KonfirmasiPenarikanController::class, 'riwayatPenarikan']);
    Route::get('/riwayat-penarikan/{id}', [KonfirmasiPenarikanController::class, 'show']);

    //Penjemputan
    Route::get('/riwayat-penjemputan', [RiwayatPenjemputanController::class, 'riwayatPenjemputan']);
    Route::get('/riwayat-penjemputan/{id}', [RiwayatPenjemputanController::class, 'show']);
});

Route::prefix('pengepul')->middleware(['auth:sanctum', 'role:pengepul'])->group(function () {
    Route::put('/edit-profile/{id}', [ProfileController::class, 'updatePengepul']);
    Route::get('/profile/{id}', [ProfileController::class, 'showPengepul']);

    Route::get('/daftar-sampah', [RequestPembelianController::class, 'indexSampah']);
    Route::get('/request-pembelian/{pengepul_id}', [RequestPembelianController::class, 'index']);
    Route::get('/request-pembelian/show/{id}', [RequestPembelianController::class, 'show']);
    Route::put('/request-pembelian/{id}', [RequestPembelianController::class, 'update']);
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

    //penarikan
    Route::get('/penarikan', [KonfirmasiPenarikanController::class, 'penarikan']);
    Route::put('penarikan/{penarikan}/terima', [KonfirmasiPenarikanController::class, 'terima']);
    Route::put('penarikan/{penarikan}/tolak', [KonfirmasiPenarikanController::class, 'tolak']);
    Route::get('/riwayat-penarikan', [KonfirmasiPenarikanController::class, 'riwayatPenarikan']);
    Route::get('/riwayat-penarikan/{id}', [KonfirmasiPenarikanController::class, 'show']);

    //penjemputan
    Route::get('/penjemputan', [KonfirmasiPenjemputanController::class, 'penjemputan']);
    Route::get('/riwayat-penjemputan', [RiwayatPenjemputanController::class, 'riwayatPenjemputan']);
    Route::get('/riwayat-penjemputan/{id}', [RiwayatPenjemputanController::class, 'show']);
    Route::put('/penjemputan/{penjemputan}/terima', [KonfirmasiPenjemputanController::class, 'terima']);
    Route::put('/penjemputan/{penjemputan}/tolak', [KonfirmasiPenjemputanController::class, 'tolak']);
    Route::get('/showpenjemputan/{penjemputan}', [KonfirmasiPenjemputanController::class, 'show']);
    Route::get('/tukang', [KonfirmasiPenjemputanController::class, 'getTukang']);

    //penimbangan
    Route::post('/penimbangan', [PenimbanganController::class, 'penimbangan']);
    Route::get('/list-sampah', [PenimbanganController::class, 'listSampah']);
    Route::get('/list-tukang', [PenimbanganController::class, 'listTukang']);
    Route::get('/list-kategori', [PenimbanganController::class, 'listKategori']);

    //penimbangan antar sendiri
    Route::get('/list-nasabah', [PenimbanganController::class, 'listNasabah']);
    Route::post('/penimbangan-antar-sendiri', [PenimbanganController::class, 'penimbanganAntarSendiri']);
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
    Route::put('/edit-petugas/{id}', [ProfileController::class, 'updatePetugas']);


    // Manager
    Route::post('/buatManager', [BuatManagerController::class,  'buatManager']);
    Route::get('manager', [KelolaAkunController::class, 'indexManager']);
    Route::get('manager/{manager}', [KelolaAkunController::class, 'showManager']);
    Route::put('manager/{manager}/deactivate', [AksiAdminController::class, 'deactivateManager']);
    Route::put('manager/{manager}/activate', [AksiAdminController::class, 'activateManager']);
    Route::put('/edit-manager/{id}', [ProfileController::class, 'updateManager']);

    // Pengepul
    Route::get('pengepul', [KelolaAkunController::class, 'indexPengepul']);
    Route::get('pengepul/{pengepul}', [KelolaAkunController::class, 'showPengepul']);
    Route::put('pengepul/{pengepul}/terima', [AksiAdminController::class, 'terimaPengepul']);
    Route::put('pengepul/{pengepul}/tolak', [AksiAdminController::class, 'tolakPengepul']);
    Route::put('pengepul/{pengepul}/deactivate', [AksiAdminController::class, 'deactivatePengepul']);
    Route::put('pengepul/{pengepul}/activate', [AksiAdminController::class, 'activatePengepul']);

    //Nasabah
    Route::get('nasabah', [KelolaAkunController::class, 'indexNasabah']);
    Route::put('nasabah/{nasabah}/deactivate', [AksiAdminController::class, 'deactivateNasabah']);
    Route::put('nasabah/{nasabah}/activate', [AksiAdminController::class, 'activateNasabah']);

    //tukang
    Route::get('tukang', [KelolaAkunController::class, 'indexTukang']);

    //Admin
    Route::get('admin', [KelolaAkunController::class, 'indexAdmin']);


    // Gudang
    Route::apiResource('gudang', GudangController::class);

    Route::put('gudang/sampah/{id}', [SampahGudangController::class, 'update']);
    Route::delete('gudang/sampah/{id}', [SampahGudangController::class, 'destroy']);

    Route::put('gudang/{id}/status', [GudangController::class, 'updateStatus']);

    // Sampah
    Route::get('/kategori-sampah', [SampahController::class, 'index']);
    Route::post('/kategori-sampah', [SampahController::class, 'store']);
    Route::get('/kategori-sampah/{id}', [SampahController::class, 'show']);
    Route::put('/kategori-sampah/{id}', [SampahController::class, 'update']);
    Route::put('/kategori-sampah/{id}/status', [SampahController::class, 'updateStatus']);
    Route::delete('/kategori-sampah/{id}', [SampahController::class, 'destroy']);

    // Item Sampah
    Route::get('/item-sampah', [SampahController::class, 'indexItem']);
    Route::delete('/item-sampah/{id}', [SampahController::class, 'destroyItem']);

    // Konfigurasi Web
    Route::get('/web-config', [WebController::class, 'show']);
    Route::put('/web-config', [WebController::class, 'update']);

    Route::put('/tukang/{gudang_id}', [TukangController::class, 'update']);
});
