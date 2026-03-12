<?php

use Illuminate\Support\Facades\Route;

// Use controller
// use App\Http\Controllers\nasabahLoginController;
use App\Http\Controllers\Api\AuthController;

// Route::get('/', function () {
//     return view('welcome');
// });

// API
Route::prefix('api')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register-nasabah', [AuthController::class, 'registerNasabah']);
    Route::post('/register-pengepul', [AuthController::class, 'registerPengepul']);
});
