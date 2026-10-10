<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Admin\Condutores;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Relatorios;
use App\Livewire\Admin\Usuarios;
use App\Livewire\Auth\Login;
use App\Livewire\Portaria\Monitoramento;
use App\Livewire\Portaria\Visitantes;
use Illuminate\Support\Facades\Route;

// Autenticação (A aplicação sempre inicia pelo Login)
Route::redirect('/', '/login');
Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Só entra quem estiver logado
Route::middleware('auth')->group(function () {

    // Módulo Guarda / Portaria
    Route::get('/portaria', Monitoramento::class)->name('portaria.monitoramento');
    Route::get('/portaria/visitantes', Visitantes::class)->name('portaria.visitantes');

    // Módulo Administrativo
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/usuarios', Usuarios::class)->name('usuarios');
        Route::get('/condutores', Condutores::class)->name('condutores');
        Route::get('/relatorios', Relatorios::class)->name('relatorios');
    });
});