<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Rota pública de login com credenciais JSON protegida contra força bruta (máx 10 tentativas por minuto)
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.login');

// Rotas protegidas por Token JWT
Route::middleware('jwt.auth')->group(function () {});
