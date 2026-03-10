<?php

use App\Http\Controllers\nasabahLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/login', [nasabahLoginController::class, 'login']);
