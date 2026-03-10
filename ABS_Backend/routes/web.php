<?php

use Illuminate\Support\Facades\Route;

// use controller
use App\Http\Controllers\nasabahLoginController;
use App\Http\Controllers\Api\AuthController;

Route::get('/', function () {
    return view('welcome');
});

// API
Route::post('/login', [nasabahLoginController::class, 'login']);
Route::post('/register', [AuthController::class, 'registerNasabah']);
