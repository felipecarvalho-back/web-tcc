<?php

use App\Livewire\Admin\Condutores;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Relatorios;
use App\Livewire\Auth\Login;
use App\Livewire\Portaria\Monitoramento;
use App\Livewire\Portaria\Visitantes;
use Illuminate\Support\Facades\Route;

// Autenticação
Route::get('/login', Login::class)->name('login');

// Módulo Guarda / Portaria
Route::get('/', Monitoramento::class)->name('home');
Route::get('/portaria', Monitoramento::class)->name('portaria.monitoramento');
Route::get('/portaria/visitantes', Visitantes::class)->name('portaria.visitantes');

// Módulo Administrativo
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/condutores', Condutores::class)->name('condutores');
    Route::get('/relatorios', Relatorios::class)->name('relatorios');
});
