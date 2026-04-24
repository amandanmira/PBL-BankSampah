<?php

use Illuminate\Support\Facades\Route;

// Use Controller
// use App\Http\Controllers\nasabahLoginController;
use App\Http\Controllers\Api\AuthController;

// Route::get('/', function () {
//     return view('welcome');
// });

// API
Route::prefix('api')->group(function () {
    Route::post('/register-pengepul', [AuthController::class , 'registerPengepul']);
    Route::post('/register-nasabah', [AuthController::class , 'registerNasabah']);
    Route::post('/check-email', [AuthController::class , 'checkEmail']);
});
