<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Sentinela FATEC
|--------------------------------------------------------------------------
*/

// Rota pública de login com credenciais JSON
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Rotas protegidas por Token JWT
Route::middleware('jwt.auth')->group(function () {
    
});
