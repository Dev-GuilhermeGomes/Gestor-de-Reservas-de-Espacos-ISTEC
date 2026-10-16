<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(auth()->check() ? '/painel' : '/login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'mostrar'])->name('login');
    Route::post('/login', [AuthController::class, 'entrar'])->middleware('throttle:5,1');
}); //o throttle limita o número de tentativas de login para 5 por minuto, ajudando a prevenir ataques de força bruta.

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'sair']);
    Route::view('/painel', 'painel');
    Route::view('/gestor', 'gestor.pedidos')
    ->middleware('perfil:GESTOR_ESPACOS,ADMINISTRADOR');
    Route::view('/admin', 'area', ['titulo' => 'Área da Administração'])
        ->middleware('perfil:ADMINISTRADOR');
});