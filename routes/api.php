<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ImagemController;
use Illuminate\Support\Facades\Route;

// Rota pública de login com credenciais JSON protegida contra força bruta (máx 10 tentativas por minuto)
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('api.login');

// Rotas protegidas por Token JWT
Route::middleware('jwt.auth')->group(function () {
    Route::post('/enviar', [ImagemController::class, 'enviar'])->name('api.enviar');
    Route::get('/historico', [ImagemController::class, 'historico'])->name('api.historico');
});
